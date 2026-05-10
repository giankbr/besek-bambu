<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Sample',
            'slug' => 'sample-'.uniqid(),
            'icon' => '🎋',
            'price' => 100000,
            'stock' => 10,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => true,
            'sort_order' => 0,
        ], $attrs));
    }

    public function test_shop_index_filters_by_min_price(): void
    {
        $cheap = $this->makeProduct(['name' => 'Cheap', 'price' => 50000]);
        $pricey = $this->makeProduct(['name' => 'Pricey', 'price' => 500000]);

        $this->get('/shop?min_price=200000')
            ->assertOk()
            ->assertSee('Pricey')
            ->assertDontSee('Cheap');
    }

    public function test_shop_index_filters_by_min_rating(): void
    {
        $three = $this->makeProduct(['name' => 'Three star', 'rating' => 3]);
        $five = $this->makeProduct(['name' => 'Five star', 'rating' => 5]);

        $this->get('/shop?min_rating=5')
            ->assertOk()
            ->assertSee('Five star')
            ->assertDontSee('Three star');
    }

    public function test_shop_index_sorts_by_price_ascending(): void
    {
        $this->makeProduct(['name' => 'Expensive', 'price' => 999999]);
        $this->makeProduct(['name' => 'Cheap', 'price' => 1000]);

        $response = $this->get('/shop?sort=price-asc');
        $response->assertOk();

        $body = $response->getContent();
        $this->assertLessThan(strpos($body, 'Expensive'), strpos($body, 'Cheap'));
    }

    public function test_shop_index_filters_by_category(): void
    {
        $cat = Category::create(['title' => 'Bowls', 'slug' => 'bowls', 'image_url' => 'https://x', 'sort_order' => 0]);
        $this->makeProduct(['name' => 'Cat product', 'category_id' => $cat->id]);
        $this->makeProduct(['name' => 'Other product']);

        $this->get('/shop?category=bowls')
            ->assertOk()
            ->assertSee('Cat product')
            ->assertDontSee('Other product');
    }
}
