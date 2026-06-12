<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_page_is_accessible(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Kebijakan Privasi', false)
            ->assertSee('Data yang kami kumpulkan', false);
    }

    public function test_terms_page_is_accessible(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Syarat &amp; Ketentuan', false)
            ->assertSee('Pengembalian &amp; pembatalan', false);
    }

    public function test_footer_links_to_legal_pages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('privacy'), false)
            ->assertSee(route('terms'), false);
    }

    public function test_checkout_requires_terms_acceptance(): void
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

        app(CartService::class)->add($product, 1);

        $payload = [
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'customer_phone' => '081111111',
            'shipping_address' => 'Jl. Test',
            'shipping_region' => 'java',
            'payment_method' => 'midtrans',
        ];

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('accept_terms');

        $this->post(route('checkout.store'), array_merge($payload, ['accept_terms' => '1']))
            ->assertSessionHasNoErrors()
            ->assertRedirect();
    }
}
