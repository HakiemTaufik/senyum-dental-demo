<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\InitialContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(InitialContentSeeder::class);
    }

    // ---------- Search engines ----------

    public function test_pages_have_title_description_canonical_and_share_tags(): void
    {
        $this->get('/services/check-up')
            ->assertSee('<title>Check-up &amp; consultation | Senyum Dental Clinic</title>', escape: false)
            ->assertSee('<meta name="description" content="A thorough look', escape: false)
            ->assertSee('<link rel="canonical" href="'.url('/services/check-up').'">', escape: false)
            ->assertSee('<meta property="og:image"', escape: false);
    }

    public function test_home_page_uses_its_seo_title(): void
    {
        $this->get('/')->assertSee('<title>Senyum Dental Clinic | Family Dentist (Demo)</title>', escape: false);
    }

    public function test_the_demo_and_test_copy_are_hidden_from_search_engines(): void
    {
        config(['seo.indexable' => false]);

        $this->get('/')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', escape: false);

        $this->get('/robots.txt')->assertOk()->assertSee("Disallow: /\n", escape: false)->assertDontSee('Sitemap:');
    }

    public function test_a_live_real_site_can_be_indexed(): void
    {
        config(['seo.indexable' => true]);

        $this->get('/')
            ->assertHeaderMissing('X-Robots-Tag')
            ->assertSee('<meta name="robots" content="index, follow', escape: false);

        $this->get('/robots.txt')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));

        // Private pages stay out of search results even on the live site.
        $this->withSession(['booking_reference' => 'SNY-TEST22'])
            ->get('/book/thank-you')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', escape: false);
    }

    public function test_sitemap_lists_pages_and_services_but_not_hidden_ones(): void
    {
        Service::where('slug', 'teeth-whitening')->update(['is_published' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.url('/').'</loc>', escape: false)
            ->assertSee('<loc>'.url('/about').'</loc>', escape: false)
            ->assertSee('<loc>'.url('/services/check-up').'</loc>', escape: false)
            ->assertSee('<loc>'.url('/book').'</loc>', escape: false)
            ->assertDontSee('teeth-whitening')
            ->assertDontSee('/admin');
    }

    public function test_dentist_structured_data_is_on_the_home_page(): void
    {
        $html = $this->get('/')->getContent();

        preg_match('#<script type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $match);
        $data = json_decode($match[1] ?? '', true);

        $this->assertSame('Dentist', $data['@type'] ?? null);
        $this->assertSame('Senyum Dental Clinic', $data['name']);
        $this->assertSame('MY', $data['address']['addressCountry']);
        $this->assertSame('40000', $data['address']['postalCode']);
        $this->assertSame('Shah Alam', $data['address']['addressLocality']);
    }

    public function test_faq_and_breadcrumb_structured_data(): void
    {
        $this->get('/faq')->assertSee('"@type":"FAQPage"', escape: false);
        $this->get('/services/fillings')->assertSee('"@type":"BreadcrumbList"', escape: false);
    }

    public function test_friendly_404_page(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee("We couldn't find that page", escape: false);
    }

    // ---------- Security headers ----------

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_public_pages_have_a_content_security_policy_with_a_nonce(): void
    {
        $response = $this->get('/book');
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([A-Za-z0-9]+)'/", $csp);

        // Our own <script> tags carry the same nonce, so they are allowed to run.
        preg_match("/'nonce-([A-Za-z0-9]+)'/", $csp, $nonce);
        $response->assertSee('nonce="'.$nonce[1].'"', escape: false);
    }

    public function test_admin_panel_gets_its_own_policy_not_the_public_one(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();

        $response = $this->actingAs($owner)->get('/admin');

        // The admin policy has no nonce (Filament's page-to-page navigation can't carry one)
        // and no Google Analytics.
        $response->assertOk()->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('nonce-', $csp);
        $this->assertStringNotContainsString('googletagmanager', $csp);
    }
}
