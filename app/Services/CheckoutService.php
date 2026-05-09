<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(private readonly CartService $cart) {}

    public function place(array $customer): Order
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            throw new \DomainException('Cart is empty.');
        }

        $subtotal = $this->cart->subtotal();

        return DB::transaction(function () use ($items, $subtotal, $customer) {
            $order = Order::create([
                'number' => $this->generateNumber(),
                'user_id' => Auth::id(),
                'customer_name' => $customer['customer_name'],
                'customer_email' => $customer['customer_email'],
                'customer_phone' => $customer['customer_phone'],
                'shipping_address' => $customer['shipping_address'],
                'notes' => $customer['notes'] ?? null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'product_icon' => $item->product->icon,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                ]);

                if ($item->product->stock > 0) {
                    $item->product->decrement('stock', min($item->quantity, $item->product->stock));
                }
            }

            $this->cart->clear();

            return $order->fresh('items');
        });
    }

    private function generateNumber(): string
    {
        do {
            $candidate = 'BSK-'.strtoupper(Str::random(8));
        } while (Order::where('number', $candidate)->exists());

        return $candidate;
    }
}
