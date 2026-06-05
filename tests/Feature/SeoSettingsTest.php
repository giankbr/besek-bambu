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
            ->set('seo_home_meta_title', 'Besek Bambu — Hantaran & Kemasan')
            ->set('social_twitter', 'besekbambu')
            ->set('seo_google_analytics_id', 'G-TEST123456')
            ->set('seo_google_site_verification', 'google-verify-token')
            ->call('saveSeo')
            ->assertHasNoErrors();

        $this->assertSame('Besek bambu handmade untuk hantaran dan kemasan.', Setting::get('seo_default_meta_description'));
        $this->assertSame('Besek Bambu — Hantaran & Kemasan', Setting::get('seo_home_meta_title'));
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
}
