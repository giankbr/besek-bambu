<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_lists_sitemap_and_blocks_private_paths(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap: '.url('/sitemap.xml'), false);
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Disallow: /checkout', false);
        $response->assertSee('Allow: /', false);
    }

    public function test_sitemap_includes_static_pages_categories_and_products(): void
    {
        $category = Category::create([
            'title' => 'Hantaran',
            'slug' => 'hantaran',
            'image_url' => '/images/placeholder.jpg',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'name' => 'Besek Medium',
            'slug' => 'besek-medium',
            'description' => 'Test',
            'icon' => '🧺',
            'price' => 50000,
            'stock' => 10,
            'rating' => 5,
            'color_class' => 'p-1',
            'sort_order' => 0,
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        $inactive = Product::create([
            'name' => 'Draft',
            'slug' => 'draft',
            'description' => 'Hidden',
            'icon' => '🧺',
            'price' => 10000,
            'stock' => 1,
            'rating' => 5,
            'color_class' => 'p-1',
            'sort_order' => 1,
            'is_active' => false,
            'category_id' => $category->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee(route('home'), false);
        $response->assertSee(route('shop.index'), false);
        $response->assertSee(route('about'), false);
        $response->assertSee(route('shop.category', $category), false);
        $response->assertSee(route('shop.product', $product), false);
        $response->assertDontSee(route('shop.product', $inactive), false);
    }
}
