<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function items(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        return collect($cart)
            ->map(function (int $qty, int $id) use ($products) {
                $product = $products->get($id);

                if (! $product) {
                    return null;
                }

                return (object) [
                    'product' => $product,
                    'quantity' => $qty,
                    'line_total' => round($product->price * $qty, 2),
                ];
            })
            ->filter()
            ->values();
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->raw();
        $current = $cart[$product->id] ?? 0;
        $next = max(1, $current + $quantity);

        if ($product->stock > 0) {
            $next = min($next, $product->stock);
        }

        $cart[$product->id] = $next;
        session([self::SESSION_KEY => $cart]);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->raw();

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $product = Product::find($productId);
            if ($product && $product->stock > 0) {
                $quantity = min($quantity, $product->stock);
            }
            $cart[$productId] = $quantity;
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    private function raw(): array
    {
        return (array) session(self::SESSION_KEY, []);
    }
}
