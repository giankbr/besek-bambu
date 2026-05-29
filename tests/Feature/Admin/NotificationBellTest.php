<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\NotificationBell;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    private function unpaidOrder(): Order
    {
        return Order::create([
            'number' => 'BSK-'.strtoupper(uniqid()),
            'user_id' => null,
            'customer_name' => 'Guest Buyer',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test 1',
            'subtotal' => 100000,
            'total' => 125000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_bell_shows_new_orders_until_explicitly_marked_read(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $this->unpaidOrder();

        Livewire::actingAs($admin)
            ->test(NotificationBell::class)
            ->assertSet('newOrdersCount', 1)
            ->assertSet('totalCount', 1)
            ->call('markOrdersSeen')
            ->assertSet('newOrdersCount', 0)
            ->assertSet('totalCount', 0);
    }

    public function test_render_without_mark_seen_keeps_new_order_count(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $this->unpaidOrder();

        $component = Livewire::actingAs($admin)->test(NotificationBell::class);

        $component->assertSet('newOrdersCount', 1);

        // Re-render (simulates opening the dropdown / polling) without markOrdersSeen.
        $component->call('$refresh')->assertSet('newOrdersCount', 1);
    }
}
