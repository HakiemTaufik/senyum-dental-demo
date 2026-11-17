<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Models\User;
use App\Support\Html;
use Database\Seeders\InitialContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Fixes from the WebDev-standard review (Nov 2026). */
class HardeningTest extends TestCase
{
    use RefreshDatabase;

    private string $securityLog;

    protected function setUp(): void
    {
        parent::setUp();

        // Send the security log to a throwaway file we can read back.
        $this->securityLog = storage_path('logs/test-security.log');
        File::delete($this->securityLog);
        config(['logging.channels.security' => ['driver' => 'single', 'path' => $this->securityLog, 'level' => 'info']]);

        Filament::setCurrentPanel('admin');
    }

    protected function tearDown(): void
    {
        File::delete($this->securityLog);

        parent::tearDown();
    }

    // ---------- Admin Content Security Policy ----------

    public function test_admin_pages_have_a_content_security_policy(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();

        foreach ([$this->get('/admin/login'), $this->actingAs($owner)->get('/admin')] as $response) {
            $csp = (string) $response->assertOk()->headers->get('Content-Security-Policy');

            $this->assertStringContainsString("frame-ancestors 'self'", $csp);
            $this->assertStringContainsString("object-src 'none'", $csp);
            $this->assertStringContainsString("base-uri 'self'", $csp);
            $this->assertStringContainsString("form-action 'self'", $csp);
            $this->assertStringContainsString("connect-src 'self'", $csp);
            // Scripts only from this site: no other web addresses.
            $this->assertMatchesRegularExpression("/script-src 'self'( '[a-z-]+')*;/", $csp);
        }
    }

    // ---------- Sign-in logging and account lock ----------

    public function test_failed_admin_sign_in_is_logged_without_the_password(): void
    {
        $user = User::factory()->owner()->withTwoFactor()->create(['email' => 'owner@example.com']);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'owner@example.com', 'password' => 'Wrong-Secret-123'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $log = File::get($this->securityLog);
        $this->assertStringContainsString('Admin sign-in failed', $log);
        $this->assertStringContainsString('"user_id":'.$user->id, $log);
        $this->assertStringNotContainsString('Wrong-Secret-123', $log);
        $this->assertStringNotContainsString('owner@example.com', $log);
    }

    public function test_an_account_locks_after_repeated_failures_even_from_different_addresses(): void
    {
        User::factory()->owner()->withTwoFactor()->create(['email' => 'owner@example.com', 'password' => 'Correct-Horse-9']);

        // Ten failures, as if a botnet tried from ten different IP addresses.
        for ($i = 0; $i < 10; $i++) {
            event(new Failed('web', null, ['email' => 'Owner@Example.com ', 'password' => 'guess-'.$i]));
        }

        // Even the right password is refused while the account is locked.
        Livewire::test(Login::class)
            ->fillForm(['email' => 'owner@example.com', 'password' => 'Correct-Horse-9'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
        $this->assertStringContainsString('Admin account locked', File::get($this->securityLog));
    }

    public function test_the_lock_does_not_reveal_whether_an_account_exists(): void
    {
        for ($i = 0; $i < 10; $i++) {
            event(new Failed('web', null, ['email' => 'nobody@example.com', 'password' => 'x']));
        }

        // Same response for an address that has no account.
        Livewire::test(Login::class)
            ->fillForm(['email' => 'nobody@example.com', 'password' => 'whatever'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);
    }

    public function test_a_few_failures_do_not_lock_the_account(): void
    {
        User::factory()->owner()->create(['email' => 'owner@example.com', 'password' => 'Correct-Horse-9']);

        for ($i = 0; $i < 3; $i++) {
            event(new Failed('web', null, ['email' => 'owner@example.com', 'password' => 'x']));
        }

        Livewire::test(Login::class)
            ->fillForm(['email' => 'owner@example.com', 'password' => 'Correct-Horse-9'])
            ->call('authenticate')
            ->assertHasNoFormErrors();
    }

    // ---------- Button links ----------

    public static function unsafeLinks(): array
    {
        return [
            'another site (//)' => ['//evil.example/page'],
            'another site (/\\)' => ['/\\evil.example/page'],
            'script' => ['javascript:alert(1)'],
            'plain http' => ['http://example.com'],
        ];
    }

    #[DataProvider('unsafeLinks')]
    public function test_button_links_cannot_point_somewhere_unsafe(string $url): void
    {
        $this->assertNull(Html::safeUrl($url));
    }

    public function test_normal_button_links_still_work(): void
    {
        foreach (['/book', '/services/check-up', '#contact', 'https://maps.google.com/x', 'tel:+60312345678', 'mailto:hello@example.com'] as $url) {
            $this->assertSame($url, Html::safeUrl($url));
        }
    }

    // ---------- Accessibility ----------

    public function test_public_pages_do_not_skip_heading_levels(): void
    {
        $this->seed(InitialContentSeeder::class);

        foreach (['/', '/about', '/services', '/dentists', '/faq', '/contact', '/book', '/services/check-up'] as $path) {
            preg_match_all('/<h([1-6])\b/', $this->get($path)->assertOk()->getContent(), $matches);

            $previous = 0;
            foreach (array_map('intval', $matches[1]) as $level) {
                $this->assertLessThanOrEqual($previous + 1, $level, "{$path}: an h{$level} follows an h{$previous}");
                $previous = $level;
            }
        }
    }

    public function test_booking_button_shows_it_is_sending(): void
    {
        $this->seed(InitialContentSeeder::class);

        $this->get('/book')
            ->assertSee('data-submit-once', escape: false)
            ->assertSee('data-sending-text="Sending…"', escape: false);
    }
}
