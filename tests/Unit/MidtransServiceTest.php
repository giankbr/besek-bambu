<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_snap_token_reuses_existing_token_for_payable_order(): void
    {
        $order = Order::create([
            'number' => 'BSK-REUSE',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test',
            'subtotal' => 9900,
            'shipping_cost' => 12000,
            'total' => 21900,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'qris',
        ]);
        $order->forceFill(['payment_token' => 'snap-token-existing'])->save();
        $order->refresh();

        $token = app(MidtransService::class)->resolveSnapToken($order);

        $this->assertSame('snap-token-existing', $token);
    }
}
