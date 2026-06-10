<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_url_redirects_to_shop(): void
    {
        $this->get('/products')
            ->assertRedirect('/shop');

        $this->get('/products/')
            ->assertRedirect('/shop');
    }

    public function test_homepage_outputs_linked_schema_graph(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('"@graph"', false);
        $response->assertSee('"@type":"Organization"', false);
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertSee('"@type":"WebPage"', false);
        $response->assertSee('"alternateName"', false);
        $response->assertSee('Besek Bambu', false);
    }

    public function test_homepage_product_schema_includes_merchant_listing_fields(): void
    {
        Product::create([
            'name' => 'Besek Test Schema',
            'slug' => 'besek-test-schema',
            'icon' => '🧺',
            'price' => 100000,
            'stock' => 5,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('"@type":"Product"', false);
        $response->assertSee('Besek Test Schema', false);
        $response->assertSee('"shippingDetails"', false);
        $response->assertSee('"hasMerchantReturnPolicy"', false);
    }

    public function test_shop_page_targets_besek_bambu_keywords(): void
    {
        $response = $this->get('/shop');

        $response->assertOk();
        $response->assertSee('<h1', false);
        $response->assertSee('Produk Besek Bambu', false);
        $response->assertSee('Jelajahi produk besek bambu handmade kami.', false);
        $response->assertSee('"@type":"ItemList"', false);
    }

    public function test_meta_title_does_not_double_encode_ampersands(): void
    {
        config(['app.name' => 'Besek Bambu']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('&amp;amp;', false);
        $response->assertSee('Hantaran &amp; Kemasan', false);
    }
}
