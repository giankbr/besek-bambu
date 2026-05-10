<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::create([
            'name' => 'Reviewable',
            'slug' => 'reviewable-'.uniqid(),
            'icon' => '🌿',
            'price' => 100000,
            'stock' => 10,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function deliveredOrder(User $user, Product $product): Order
    {
        $order = Order::create([
            'number' => 'TEST-'.uniqid(),
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '081',
            'shipping_address' => 'Jl. Test',
            'subtotal' => 100000,
            'total' => 100000,
            'status' => 'delivered',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_icon' => $product->icon,
            'price' => $product->price,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        return $order;
    }

    public function test_customer_with_delivered_order_can_post_review(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->product();
        $this->deliveredOrder($user, $product);

        $this->actingAs($user)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'title' => 'Great',
                'body' => 'Loved this product, highly recommended.',
            ])
            ->assertRedirect(route('shop.product', $product));

        $this->assertSame(1, ProductReview::where('product_id', $product->id)->count());
    }

    public function test_customer_without_purchase_cannot_review(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->product();

        $this->actingAs($user)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'body' => 'Trying to review without buying.',
            ])
            ->assertRedirect();

        $this->assertSame(0, ProductReview::where('product_id', $product->id)->count());
    }

    public function test_customer_cannot_review_twice(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->product();
        $this->deliveredOrder($user, $product);

        $this->actingAs($user);

        $this->post(route('reviews.store', $product), [
            'rating' => 5,
            'body' => 'First review here, very pleased.',
        ]);

        $this->post(route('reviews.store', $product), [
            'rating' => 4,
            'body' => 'Trying to review again, should fail.',
        ]);

        $this->assertSame(1, ProductReview::where('product_id', $product->id)->count());
    }
}
