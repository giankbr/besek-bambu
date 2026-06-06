<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappOrderTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'number' => 'BSK-TEST123',
            'customer_name' => 'Gian',
            'customer_email' => 'gian@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Magetan',
            'subtotal' => 6600000,
            'total' => 6600000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_method' => 'bri_va',
        ], $overrides));
    }

    public function test_whatsapp_order_message_includes_order_details(): void
    {
        config(['store.whatsapp_number' => '6281234567890']);

        $order = $this->order();
        $message = whatsapp_order_message($order);

        $this->assertStringContainsString('BSK-TEST123', $message);
        $this->assertStringContainsString('Gian', $message);
        $this->assertStringContainsString('Rp 6.600.000', $message);
        $this->assertStringContainsString(__('Lunas'), $message);
        $this->assertStringContainsString(__('VA BRI'), $message);
    }

    public function test_whatsapp_order_url_builds_wa_me_link(): void
    {
        config(['store.whatsapp_number' => '6281234567890']);

        $order = $this->order();
        $url = whatsapp_order_url($order);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
    }

    public function test_whatsapp_order_url_returns_null_without_number(): void
    {
        config(['store.whatsapp_number' => '']);

        $order = $this->order();

        $this->assertNull(whatsapp_order_url($order));
    }
}
