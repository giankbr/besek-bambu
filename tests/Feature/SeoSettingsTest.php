<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_admin_can_save_seo_settings(): void
    {
        Livewire::actingAs($this->admin())
            ->test('pages::admin.settings.index')
            ->set('tab', 'seo')
            ->set('seo_default_meta_description', 'Besek bambu handmade untuk hantaran dan kemasan.')
            ->set('seo_home_meta_title', 'Besek Bambu, Hantaran & Kemasan')
            ->set('social_twitter', 'besekbambu')
            ->set('seo_google_analytics_id', 'G-TEST123456')
            ->set('seo_google_site_verification', 'google-verify-token')
            ->call('saveSeo')
            ->assertHasNoErrors();

        $this->assertSame('Besek bambu handmade untuk hantaran dan kemasan.', Setting::get('seo_default_meta_description'));
        $this->assertSame('Besek Bambu, Hantaran & Kemasan', Setting::get('seo_home_meta_title'));
        $this->assertSame('besekbambu', Setting::get('social_twitter'));
        $this->assertSame('G-TEST123456', Setting::get('seo_google_analytics_id'));
        $this->assertSame('google-verify-token', Setting::get('seo_google_site_verification'));
    }

    public function test_homepage_renders_seo_settings_and_hreflang(): void
    {
        Setting::put('seo_default_meta_description', 'Default deskripsi toko besek bambu.');
        Setting::put('seo_home_meta_title', 'Besek Bambu Magetan');
        Setting::put('seo_google_site_verification', 'verify-home-123');
        Setting::put('seo_google_analytics_id', 'G-HOMEPAGE01');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<title>Besek Bambu Magetan</title>', false);
        $response->assertSee('content="Default deskripsi toko besek bambu."', false);
        $response->assertSee('name="google-site-verification" content="verify-home-123"', false);
        $response->assertSee('rel="alternate" hreflang="id"', false);
        $response->assertSee('rel="alternate" hreflang="en"', false);
        $response->assertSee('https://www.googletagmanager.com/gtag/js?id=G-HOMEPAGE01', false);
    }

    public function test_lang_query_sets_storefront_locale(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSessionHas('locale', 'en');
    }

    public function test_navbar_language_links_use_direct_urls_not_lang_switch_route(): void
    {
        $html = $this->get('/grosir')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#href="[^"]+/grosir"[^>]*>\\s*<span class="nav-lang__dot"#s', $html);
        $this->assertMatchesRegularExpression('#href="[^"]+/grosir\\?lang=en"#', $html);
        $this->assertStringNotContainsString('/lang/id', $html);
        $this->assertStringNotContainsString('/lang/en', $html);
    }

    public function test_lang_switch_route_redirects_to_localized_home_without_referer(): void
    {
        $home = url('/');
        $homeEn = rtrim(url('/'), '/').'/?lang=en';

        $this->get('/lang/en')
            ->assertRedirect($homeEn)
            ->assertSessionHas('locale', 'en');

        $this->get('/lang/id')
            ->assertRedirect($home)
            ->assertSessionHas('locale', 'id');
    }

    public function test_lang_switch_route_redirects_to_localized_referer_page(): void
    {
        $this->from(url('/blog/besek-test'))
            ->get('/lang/en')
            ->assertRedirect(url('/blog/besek-test?lang=en'));
    }

    public function test_default_locale_hreflang_id_self_references_without_lang_param(): void
    {
        $response = $this->get('/syarat-ketentuan');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '#<link rel="canonical" href="[^"]+/syarat-ketentuan" />#',
            $html
        );
        $this->assertMatchesRegularExpression(
            '#<link rel="alternate" hreflang="id" href="[^"]+/syarat-ketentuan" />#',
            $html
        );
        $this->assertMatchesRegularExpression(
            '#<link rel="alternate" hreflang="en" href="[^"]+/syarat-ketentuan\?lang=en" />#',
            $html
        );
        $this->assertMatchesRegularExpression(
            '#<link rel="alternate" hreflang="x-default" href="[^"]+/syarat-ketentuan" />#',
            $html
        );
    }

    public function test_english_locale_hreflang_en_self_references_with_lang_param(): void
    {
        $response = $this->withSession(['locale' => 'en'])
            ->get('/syarat-ketentuan?lang=en');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '#<link rel="canonical" href="[^"]+/syarat-ketentuan\?lang=en" />#',
            $html
        );
        $this->assertMatchesRegularExpression(
            '#<link rel="alternate" hreflang="en" href="[^"]+/syarat-ketentuan\?lang=en" />#',
            $html
        );
        $this->assertMatchesRegularExpression(
            '#<link rel="alternate" hreflang="id" href="[^"]+/syarat-ketentuan" />#',
            $html
        );
    }

    public function test_store_email_link_uses_mailto_with_cloudflare_obfuscation_disabled(): void
    {
        Setting::put('store_email', 'hello@besek.test');

        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('<!--email_off-->', false);
        $response->assertSee('href="mailto:hello@besek.test"', false);
        $response->assertDontSee('/cdn-cgi/l/email-protection', false);
    }

    public function test_paginated_blog_hreflang_preserves_page_query(): void
    {
        $response = $this->get('/blog?page=2');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '#<link rel="canonical" href="[^"]+/blog\?page=2" />#',
            $html
        );
        $this->assertMatchesRegularExpression(
            '#<link rel="alternate" hreflang="id" href="[^"]+/blog\?page=2" />#',
            $html
        );
        $this->assertMatchesRegularExpression(
            '#<link rel="alternate" hreflang="en" href="[^"]+/blog\?page=2&amp;lang=en" />#',
            $html
        );
    }
}
