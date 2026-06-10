<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSlugTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Sample Product',
            'slug' => 'sample-product',
            'icon' => '🧺',
            'price' => 100000,
            'stock' => 5,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => true,
            'sort_order' => 0,
        ], $attrs));
    }

    public function test_legacy_product_slug_redirects_to_canonical_url(): void
    {
        $product = $this->product([
            'name' => 'Tas Anyaman Bambu 8x8 Natural',
            'slug' => 'tas-anyaman-bambu-8x8-natural',
            'legacy_slugs' => ['Tas Anyaman Bambu 8x8 Natural '],
        ]);

        $this->get('/products/Tas%20Anyaman%20Bambu%208x8%20Natural%20')
            ->assertRedirect(route('shop.product', $product));
    }

    public function test_fix_slugs_command_normalizes_bad_slugs(): void
    {
        $product = $this->product([
            'name' => '(100pcs) Keranjang Bambu Reyeng Ukuran 16x6x3 / Wadah Ikan Pindang',
            'slug' => 'Keranjangikan',
        ]);

        $this->artisan('products:fix-slugs')
            ->assertSuccessful();

        $product->refresh();

        $this->assertSame('100pcs-keranjang-bambu-reyeng-ukuran-16x6x3-wadah-ikan-pindang', $product->slug);
        $this->assertContains('keranjangikan', $product->legacy_slugs);
    }

    public function test_saving_product_normalizes_slug(): void
    {
        $product = $this->product([
            'name' => 'Besek Bambu 20x10',
            'slug' => 'besek20x10 ',
        ]);

        $product->update(['slug' => 'besek20x10 ']);
        $product->refresh();

        $this->assertSame('besek20x10', $product->slug);
    }
}
