<?php

namespace Tests\Feature;

use App\Mail\NewOrderReceived;
use App\Mail\OrderPlaced;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderAdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::create([
            'name' => 'Bamboo bowl',
            'slug' => 'bamboo-bowl-'.uniqid(),
            'icon' => '🥣',
            'price' => 100000,
            'stock' => 10,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function customer(): array
    {
        return [
            'customer_name' => 'Test Buyer',
            'customer_email' => 'buyer@example.com',
            'customer_phone' => '081111111',
            'shipping_address' => 'Jl. Test',
            'shipping_region' => 'java',
        ];
    }

    public function test_checkout_sends_admin_notification_when_email_configured(): void
    {
        Mail::fake();

        Setting::put('order_notification_email', 'admin@besekbambu.com');

        $product = $this->product();
        app(CartService::class)->add($product, 1);

        app(CheckoutService::class)->place($this->customer());

        Mail::assertSent(NewOrderReceived::class, function (NewOrderReceived $mail) {
            return $mail->hasTo('admin@besekbambu.com');
        });

        Mail::assertSent(OrderPlaced::class);
    }

    public function test_checkout_skips_admin_notification_when_no_recipient(): void
    {
        Mail::fake();

        config(['mail.admin_address' => null]);
        Setting::put('order_notification_email', '');
        Setting::put('store_email', '');

        $product = $this->product();
        app(CartService::class)->add($product, 1);

        app(CheckoutService::class)->place($this->customer());

        Mail::assertNotSent(NewOrderReceived::class);
        Mail::assertSent(OrderPlaced::class);
    }

    public function test_admin_notification_falls_back_to_mail_admin_address(): void
    {
        Mail::fake();

        config(['mail.admin_address' => 'fallback@besekbambu.com']);
        Setting::put('order_notification_email', '');

        $product = $this->product();
        app(CartService::class)->add($product, 1);

        app(CheckoutService::class)->place($this->customer());

        Mail::assertSent(NewOrderReceived::class, function (NewOrderReceived $mail) {
            return $mail->hasTo('fallback@besekbambu.com');
        });
    }

    public function test_checkout_sends_admin_notification_to_multiple_comma_separated_emails(): void
    {
        Mail::fake();

        Setting::put('order_notification_email', 'admin@besekbambu.com, owner@besekbambu.com');

        $product = $this->product();
        app(CartService::class)->add($product, 1);

        app(CheckoutService::class)->place($this->customer());

        Mail::assertSent(NewOrderReceived::class, function (NewOrderReceived $mail) {
            return $mail->hasTo('admin@besekbambu.com') && $mail->hasTo('owner@besekbambu.com');
        });
    }
}
