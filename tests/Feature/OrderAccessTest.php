<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use RefreshDatabase;

    private function order(?int $userId = null): Order
    {
        return Order::create([
            'number' => 'BSK-'.strtoupper(uniqid()),
            'user_id' => $userId,
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

    public function test_guest_cannot_view_confirmation_without_access(): void
    {
        $order = $this->order();

        $this->get(route('checkout.confirmation', $order))->assertNotFound();
    }

    public function test_guest_can_view_confirmation_with_session_grant(): void
    {
        $order = $this->order();

        $this->withSession(['accessible_order_numbers' => [$order->number]])
            ->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertSee($order->number);
    }

    public function test_confirmation_shows_whatsapp_button_when_number_configured(): void
    {
        config(['store.whatsapp_number' => '6281234567890']);

        $order = $this->order();

        $this->withSession(['accessible_order_numbers' => [$order->number]])
            ->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertSee(__('Kabari admin via WhatsApp'), false)
            ->assertSee('wa.me/6281234567890', false);
    }

    public function test_confirmation_hides_whatsapp_button_without_number(): void
    {
        config(['store.whatsapp_number' => '']);

        $order = $this->order();

        $this->withSession(['accessible_order_numbers' => [$order->number]])
            ->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertDontSee(__('Kabari admin via WhatsApp'), false);
    }

    public function test_signed_url_allows_guest_to_view_confirmation(): void
    {
        $order = $this->order();

        $url = URL::temporarySignedRoute(
            'checkout.confirmation',
            now()->addHour(),
            ['order' => $order],
        );

        $this->get($url)->assertOk()->assertSee($order->number);
    }

    public function test_guest_sees_pay_button_when_midtrans_method_is_qris(): void
    {
        config(['services.midtrans.server_key' => 'SB-Mid-server-test']);

        $order = Order::create([
            'number' => 'BSK-QRISTEST',
            'customer_name' => 'Guest Buyer',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test 1',
            'subtotal' => 9900,
            'shipping_cost' => 12000,
            'total' => 21900,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'qris',
            'payment_token' => 'snap-token-test',
        ]);

        $this->withSession(['accessible_order_numbers' => [$order->number]])
            ->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertSee(__('Bayar sekarang'), false);
    }

    public function test_owner_can_view_payment_page_without_signed_url(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->order($user->id);

        $this->actingAs($user)
            ->get(route('payment.pay', $order))
            ->assertRedirect(route('checkout.confirmation', $order));
    }

    public function test_stranger_cannot_view_another_users_payment_page(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $stranger = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->order($owner->id);

        $this->actingAs($stranger)
            ->get(route('payment.pay', $order))
            ->assertNotFound();
    }
}
