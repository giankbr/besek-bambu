<?php

namespace App\Services;

use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly ShippingService $shipping,
    ) {}

    public function place(array $customer): Order
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            throw new \DomainException('Cart is empty.');
        }

        foreach ($items as $item) {
            if ($item->product->stock < $item->quantity) {
                throw new \DomainException("Sorry, only {$item->product->stock} of {$item->product->name} are available.");
            }
        }

        $subtotal = $this->cart->subtotal();
        $coupon = $this->cart->coupon();
        $discount = $this->cart->discount();
        $shippingCost = $this->shipping->costFor($customer['shipping_region'] ?? null);
        $total = max(0, $subtotal - $discount) + $shippingCost;

        $order = DB::transaction(function () use ($items, $subtotal, $discount, $shippingCost, $total, $coupon, $customer) {
            $order = Order::create([
                'number' => $this->generateNumber(),
                'user_id' => Auth::id(),
                'customer_name' => $customer['customer_name'],
                'customer_email' => $customer['customer_email'],
                'customer_phone' => $customer['customer_phone'],
                'shipping_address' => $customer['shipping_address'],
                'shipping_region' => $customer['shipping_region'] ?? null,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'coupon_code' => $coupon?->code,
                'notes' => $customer['notes'] ?? null,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => 'pending',
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            foreach ($items as $item) {
                $product = Product::query()
                    ->whereKey($item->product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < $item->quantity) {
                    throw new \DomainException("Sorry, only {$product->stock} of {$product->name} are available.");
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_icon' => $product->icon,
                    'price' => $product->price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                ]);

                $product->decrement('stock', $item->quantity);
            }

            $this->cart->clear();

            return $order->fresh('items');
        });

        try {
            Mail::to($order->customer_email)->send(new OrderPlaced($order));
        } catch (\Throwable $e) {
            Log::warning('Failed to send order placed email', ['order' => $order->number, 'error' => $e->getMessage()]);
        }

        return $order;
    }

    private function generateNumber(): string
    {
        do {
            $candidate = 'BSK-'.strtoupper(Str::random(8));
        } while (Order::where('number', $candidate)->exists());

        return $candidate;
    }
}
