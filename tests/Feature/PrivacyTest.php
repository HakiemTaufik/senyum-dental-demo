<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Pages\PrivacyRequests;
use App\Models\BookingRequest;
use App\Models\User;
use App\Settings\ClinicSettings;
use App\Support\Html;
use Database\Seeders\InitialContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(InitialContentSeeder::class);
    }

    private function setAnalytics(?string $id): void
    {
        $settings = app(ClinicSettings::class);
        $settings->analytics_id = $id;
        $settings->save();
    }

    // ---------- Privacy notice ----------

    public function test_privacy_notice_is_in_english_and_bahasa_melayu(): void
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Your rights')
            ->assertSee('Hak anda')
            ->assertSee('Personal Data Protection Act 2010')
            ->assertSee('Akta Perlindungan Data Peribadi 2010');
    }

    public function test_placeholders_show_current_clinic_details(): void
    {
        $settings = app(ClinicSettings::class);
        $settings->dpo_email = 'dpo@clinic.test';
        $settings->booking_retention_months = 9;
        $settings->save();

        $this->get('/privacy-policy')
            ->assertSee('dpo@clinic.test')
            ->assertSee('deleted automatically 9 months', escape: false)
            ->assertDontSee('[dpo-email]')
            ->assertDontSee('[clinic-name]');
    }

    public function test_placeholder_values_are_escaped(): void
    {
        $settings = app(ClinicSettings::class);
        $settings->dpo_name = '<script>alert(1)</script>';
        $settings->save();

        $this->assertStringNotContainsString('<script>', Html::replaceTokens('<p>[dpo-name]</p>'));
    }

    public function test_booking_consent_mentions_health_information_and_links_the_policy(): void
    {
        $this->get('/book')
            ->assertSee('including any health information I choose to share')
            ->assertSee(url('/privacy-policy'), escape: false);
    }

    // ---------- Cookies & analytics ----------

    public function test_no_analytics_and_no_banner_when_analytics_is_off(): void
    {
        $this->setAnalytics(null);

        $this->get('/')
            ->assertDontSee('name="analytics-id"', escape: false)
            ->assertDontSee('gtag(', escape: false)
            ->assertDontSee('Cookie settings');
    }

    public function test_analytics_never_loads_before_consent(): void
    {
        $this->setAnalytics('G-TEST12345');

        $html = $this->get('/')->assertOk()->getContent();

        // The ID is available to the consent script, consent defaults to "denied" ...
        $this->assertStringContainsString('<meta name="analytics-id" content="G-TEST12345">', $html);
        $this->assertStringContainsString("analytics_storage: 'denied'", $html);
        $this->assertStringContainsString('data-cc="show-preferencesModal"', $html);

        // ... and Google's script is NOT on the page. It is only added by JavaScript after "Accept".
        $this->assertStringNotContainsString('googletagmanager.com/gtag/js', $html);
    }

    public function test_invalid_analytics_ids_are_ignored(): void
    {
        $this->setAnalytics('"><script>alert(1)</script>');

        $this->get('/')->assertDontSee('name="analytics-id"', escape: false);
    }

    public function test_admin_pages_never_include_analytics(): void
    {
        $this->setAnalytics('G-TEST12345');
        $owner = User::factory()->owner()->withTwoFactor()->create();

        $this->actingAs($owner)->get('/admin')->assertOk()->assertDontSee('G-TEST12345');
    }

    // ---------- Privacy requests (access / erasure) ----------

    public function test_only_the_owner_can_open_privacy_requests(): void
    {
        $this->actingAs(User::factory()->owner()->withTwoFactor()->create())->get('/admin/privacy-requests')->assertOk();

        foreach ([Role::FrontDesk, Role::ContentEditor, Role::Dentist] as $role) {
            $this->actingAs(User::factory()->role($role)->withTwoFactor()->create())->get('/admin/privacy-requests')->assertForbidden();
        }
    }

    public function test_owner_can_export_a_persons_data(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();
        BookingRequest::factory()->count(2)->create(['email' => 'ali@example.com']);
        BookingRequest::factory()->create(['email' => 'someone-else@example.com']);

        $this->actingAs($owner);
        Livewire::test(PrivacyRequests::class)
            ->fillForm(['email' => 'ALI@example.com'])
            ->call('search')
            ->assertSee('Results for ali@example.com')
            ->callAction('export')
            ->assertFileDownloaded();

        $this->assertTrue(Activity::where('log_name', 'privacy')->where('description', 'like', '%exported%')->exists());
    }

    public function test_owner_can_erase_a_persons_data(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();
        BookingRequest::factory()->count(2)->create(['email' => 'ali@example.com']);
        $other = BookingRequest::factory()->create(['email' => 'someone-else@example.com']);

        $this->actingAs($owner);
        Livewire::test(PrivacyRequests::class)
            ->fillForm(['email' => 'ali@example.com'])
            ->call('search')
            ->callAction('delete');

        $this->assertSame(0, BookingRequest::where('email', 'ali@example.com')->count());
        $this->assertModelExists($other);

        $log = Activity::where('log_name', 'privacy')->latest('id')->first();
        $this->assertSame('erasure', $log->properties['request']);
        // The log records which requests were deleted, but not the person's email.
        $this->assertStringNotContainsString('ali@example.com', json_encode($log->properties));
    }
}
