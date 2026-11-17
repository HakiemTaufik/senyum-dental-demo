<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use App\Settings\ClinicSettings;
use Database\Seeders\InitialContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(InitialContentSeeder::class);
    }

    public static function publicPages(): array
    {
        return [
            'home' => ['/', 'Dental care that puts you at ease'],
            'about' => ['/about', 'A neighbourhood clinic'],
            'services' => ['/services', 'Scaling &amp; polishing'],
            'service detail' => ['/services/check-up', 'Check-up &amp; consultation'],
            'dentists' => ['/dentists', 'Dr. Aisyah Rahman'],
            'faq' => ['/faq', 'How do I book an appointment?'],
            'contact' => ['/contact', 'Opening hours'],
            'privacy' => ['/privacy-policy', 'Privacy policy'],
            'book' => ['/book', 'Request an appointment'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_load(string $url, string $expected): void
    {
        $this->get($url)
            ->assertOk()
            ->assertSee($expected, escape: false)
            ->assertSee('Skip to main content')
            ->assertSee('Demo website');
    }

    public function test_the_home_page_has_exactly_one_main_heading(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
    }

    public function test_home_slug_redirects_to_the_front_page(): void
    {
        $this->get('/home')->assertRedirect('/')->assertStatus(301);
    }

    public function test_unpublished_content_is_hidden(): void
    {
        Page::where('slug', 'about')->update(['is_published' => false]);
        Service::where('slug', 'check-up')->update(['is_published' => false]);
        Faq::where('question', 'How do I book an appointment?')->update(['is_published' => false]);

        $this->get('/about')->assertNotFound();
        $this->get('/services/check-up')->assertNotFound();
        $this->get('/faq')->assertDontSee('How do I book an appointment?');
        $this->get('/')->assertDontSee('href="'.url('/about').'"', escape: false);
    }

    public function test_unknown_pages_return_not_found(): void
    {
        $this->get('/does-not-exist')->assertNotFound();
    }

    public function test_dangerous_html_in_rich_text_is_removed(): void
    {
        Page::factory()->create([
            'slug' => 'test-page',
            'content' => [['type' => 'rich_text', 'data' => ['heading' => null, 'body' => '<p>Hello</p><script>alert(1)</script><img src=x onerror="alert(2)">']]],
        ]);

        $this->get('/test-page')
            ->assertOk()
            ->assertSee('Hello')
            ->assertDontSee('<script>alert(1)</script>', escape: false)
            ->assertDontSee('onerror', escape: false);
    }

    public function test_unsafe_button_links_are_not_rendered(): void
    {
        Page::factory()->create([
            'slug' => 'test-page',
            'content' => [['type' => 'cta', 'data' => ['heading' => 'Hi', 'button_label' => 'Click', 'button_url' => 'javascript:alert(1)', 'show_whatsapp' => false]]],
        ]);

        $this->get('/test-page')->assertOk()->assertDontSee('javascript:', escape: false);
    }

    public function test_clinic_details_from_settings_appear_on_the_site(): void
    {
        $settings = app(ClinicSettings::class);
        $settings->phone = '+60 3-1234 5678';
        $settings->whatsapp = '60123456789';
        $settings->save();

        $this->get('/contact')
            ->assertSee('+60 3-1234 5678')
            ->assertSee('https://wa.me/60123456789', escape: false);
    }

    public function test_demo_notice_can_be_switched_off(): void
    {
        $settings = app(ClinicSettings::class);
        $settings->show_demo_notice = false;
        $settings->save();

        $this->get('/')->assertDontSee('Demo website.');
    }

    public function test_content_seeder_never_overwrites_edits(): void
    {
        Service::where('slug', 'check-up')->update(['name' => 'Edited by owner']);

        $this->seed(InitialContentSeeder::class);

        $this->assertSame('Edited by owner', Service::firstWhere('slug', 'check-up')->name);
        $this->assertSame(1, Service::where('slug', 'check-up')->count());
    }

    public static function contentScreens(): array
    {
        return [['/admin/pages'], ['/admin/services'], ['/admin/dentists'], ['/admin/faqs']];
    }

    #[DataProvider('contentScreens')]
    public function test_owner_and_content_editor_can_edit_content(string $url): void
    {
        foreach ([Role::Owner, Role::ContentEditor] as $role) {
            $user = User::factory()->role($role)->withTwoFactor()->create();
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    #[DataProvider('contentScreens')]
    public function test_front_desk_and_dentists_cannot_edit_content(string $url): void
    {
        foreach ([Role::FrontDesk, Role::Dentist] as $role) {
            $user = User::factory()->role($role)->withTwoFactor()->create();
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_page_editor_loads_for_the_home_page(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();
        $home = Page::firstWhere('slug', 'home');

        $this->actingAs($owner)->get("/admin/pages/{$home->slug}/edit")->assertOk();
    }

    public function test_system_pages_cannot_be_deleted(): void
    {
        $owner = User::factory()->owner()->create();

        $this->assertFalse($owner->can('delete', Page::firstWhere('slug', 'home')));
        $this->assertTrue($owner->can('delete', Page::factory()->create()));
    }

    public function test_only_the_owner_can_change_clinic_details(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();
        $editor = User::factory()->role(Role::ContentEditor)->withTwoFactor()->create();

        $this->actingAs($owner)->get('/admin/manage-clinic-settings')->assertOk();
        $this->actingAs($editor)->get('/admin/manage-clinic-settings')->assertForbidden();
    }
}
