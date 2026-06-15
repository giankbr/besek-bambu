<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyUrlRedirectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_common_legacy_paths_redirect_to_canonical_urls(): void
    {
        $this->get('/kontak')->assertRedirect(route('contact'));
        $this->get('/toko')->assertRedirect(route('shop.index'));
        $this->get('/about-us')->assertRedirect(route('about'));
        $this->get('/privacy-policy')->assertRedirect(route('privacy'));
        $this->get('/terms-of-service')->assertRedirect(route('terms'));
        $this->get('/sitemap_index.xml')->assertRedirect(route('sitemap'));
        $this->get('/index.php')->assertRedirect(route('home'));
    }

    public function test_legacy_product_paths_redirect_to_canonical_product_url(): void
    {
        $product = Product::create([
            'name' => 'Besek Test',
            'slug' => 'besek-test',
            'icon' => '🧺',
            'price' => 50000,
            'stock' => 5,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->get('/product/besek-test')->assertRedirect(route('shop.product', $product));
        $this->get('/produk/besek-test')->assertRedirect(route('shop.product', $product));
    }

    public function test_legacy_category_path_redirects_to_canonical_category_url(): void
    {
        $category = Category::create([
            'title' => '10x10',
            'slug' => '10x10',
            'image_url' => '/images/placeholder.jpg',
            'sort_order' => 1,
        ]);

        $this->get('/category/10x10')->assertRedirect(route('shop.category', $category));
    }

    public function test_inactive_product_redirects_to_category_inst_of_404(): void
    {
        $category = Category::create([
            'title' => '10x10',
            'slug' => '10x10',
            'image_url' => '/images/placeholder.jpg',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'name' => 'Inactive Besek',
            'slug' => 'inactive-besek',
            'icon' => '🧺',
            'price' => 50000,
            'stock' => 0,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => false,
            'sort_order' => 0,
            'category_id' => $category->id,
        ]);

        $this->get(route('shop.product', $product))
            ->assertRedirect(route('shop.category', $category));

        $this->get('/product/inactive-besek')
            ->assertRedirect(route('shop.category', $category));
    }
}
