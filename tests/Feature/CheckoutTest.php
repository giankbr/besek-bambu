<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Bamboo bowl',
            'slug' => 'bamboo-bowl-'.uniqid(),
            'icon' => '🥣',
            'price' => 100000,
            'stock' => 10,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => true,
            'sort_order' => 0,
        ], $attrs));
    }

    private function customer(): array
    {
        return [
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'customer_phone' => '081111111',
            'shipping_address' => 'Jl. Test',
            'shipping_region' => 'java',
        ];
    }

    public function test_place_order_decrements_stock_and_records_total(): void
    {
        Mail::fake();

        $product = $this->product(['stock' => 5]);
        app(CartService::class)->add($product, 2);

        $order = app(CheckoutService::class)->place($this->customer());

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertEquals('200000.00', $order->subtotal);
        $this->assertEquals('25000.00', $order->shipping_cost);
        $this->assertEquals('225000.00', $order->total);
        $this->assertCount(1, $order->items);
    }

    public function test_place_order_rejects_when_stock_insufficient(): void
    {
        Mail::fake();

        $product = $this->product(['stock' => 1]);
        app(CartService::class)->add($product, 1);
        // Manually push session quantity higher than stock
        session(['cart' => [$product->id => 5]]);

        $this->expectException(\DomainException::class);
        app(CheckoutService::class)->place($this->customer());
    }

    public function test_place_order_applies_coupon_discount(): void
    {
        Mail::fake();

        $product = $this->product(['price' => 200000, 'stock' => 5]);
        Coupon::create([
            'code' => 'SAVE20',
            'type' => 'percent',
            'value' => 20,
            'min_order' => 0,
            'is_active' => true,
        ]);

        $cart = app(CartService::class);
        $cart->add($product, 1);
        $cart->applyCoupon('SAVE20');

        $order = app(CheckoutService::class)->place($this->customer());

        $this->assertEquals('40000.00', $order->discount);
        $this->assertSame('SAVE20', $order->coupon_code);
        // 200000 - 40000 + 25000 = 185000
        $this->assertEquals('185000.00', $order->total);
    }

    public function test_authenticated_customer_can_cancel_pending_order(): void
    {
        Mail::fake();

        $product = $this->product(['stock' => 5]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        app(CartService::class)->add($product, 2);
        $order = app(CheckoutService::class)->place($this->customer());

        $this->assertSame(3, $product->fresh()->stock);

        $this->post(route('account.orders.cancel', $order))
            ->assertRedirect(route('account.orders.show', $order));

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }
}
