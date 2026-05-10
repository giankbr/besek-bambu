<?php

namespace App\Services;

use App\Models\CartSnapshot;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartService
{
    private const SESSION_KEY = 'cart';

    private const COUPON_KEY = 'cart_coupon';

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
        $moq = max(1, (int) ($product->min_order_quantity ?? 1));

        // Snap to MOQ on first add so the customer never ends up
        // below the minimum the artisan accepts.
        if ($current === 0 && $quantity < $moq) {
            $quantity = $moq;
        }

        $next = max(1, $current + $quantity);

        if ($product->stock > 0) {
            $next = min($next, $product->stock);
        }

        $cart[$product->id] = $next;
        session([self::SESSION_KEY => $cart]);
        $this->snapshot();
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->raw();

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $product = Product::find($productId);
            if ($product) {
                $moq = max(1, (int) ($product->min_order_quantity ?? 1));
                if ($quantity < $moq) {
                    $quantity = $moq;
                }
                if ($product->stock > 0) {
                    $quantity = min($quantity, $product->stock);
                }
            }
            $cart[$productId] = $quantity;
        }

        session([self::SESSION_KEY => $cart]);
        $this->snapshot();
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        session([self::SESSION_KEY => $cart]);
        $this->snapshot();
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
        session()->forget(self::COUPON_KEY);
        $this->forgetSnapshot();
    }

    /**
     * Persist the current cart for the authenticated user so that an
     * abandoned-cart job can recover it later. Anonymous carts are
     * not stored because we have no contact channel for them.
     */
    private function snapshot(): void
    {
        $userId = Auth::id();
        if (! $userId) {
            return;
        }

        $items = $this->raw();

        try {
            if (empty($items)) {
                CartSnapshot::query()->where('user_id', $userId)->delete();

                return;
            }

            CartSnapshot::query()->updateOrCreate(
                ['user_id' => $userId],
                [
                    'items' => $items,
                    'subtotal' => $this->subtotal(),
                    'last_seen_at' => now(),
                    'recovery_sent_at' => null,
                ],
            );
        } catch (\Throwable $e) {
            // Snapshotting must never break the user-facing flow.
            Log::warning('Cart snapshot failed', ['error' => $e->getMessage()]);
        }
    }

    private function forgetSnapshot(): void
    {
        $userId = Auth::id();
        if (! $userId) {
            return;
        }

        try {
            CartSnapshot::query()->where('user_id', $userId)->delete();
        } catch (\Throwable $e) {
            Log::warning('Cart snapshot delete failed', ['error' => $e->getMessage()]);
        }
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    /**
     * Total cart weight in grams. Items without a weight contribute the
     * default fallback so RajaOngkir always receives a positive value.
     */
    public function totalWeight(int $defaultPerItem = 1000): int
    {
        return (int) $this->items()->reduce(function (int $carry, $item) use ($defaultPerItem) {
            $weight = (int) ($item->product->weight ?? 0);

            if ($weight <= 0) {
                $weight = $defaultPerItem;
            }

            return $carry + ($weight * (int) $item->quantity);
        }, 0);
    }

    public function applyCoupon(string $code): Coupon
    {
        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (! $coupon) {
            throw new \DomainException('Coupon code is invalid.');
        }

        if (! $coupon->isUsable($this->subtotal())) {
            throw new \DomainException('Coupon cannot be applied to this order.');
        }

        session([self::COUPON_KEY => $coupon->code]);

        return $coupon;
    }

    public function clearCoupon(): void
    {
        session()->forget(self::COUPON_KEY);
    }

    public function coupon(): ?Coupon
    {
        $code = session(self::COUPON_KEY);

        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isUsable($this->subtotal())) {
            $this->clearCoupon();

            return null;
        }

        return $coupon;
    }

    public function discount(): float
    {
        $coupon = $this->coupon();

        if (! $coupon) {
            return 0.0;
        }

        return (float) $coupon->calculateDiscount($this->subtotal());
    }

    public function taxRate(): float
    {
        return (float) setting('tax_rate', 0);
    }

    public function taxIsInclusive(): bool
    {
        return (bool) setting('tax_inclusive', false);
    }

    /**
     * Tax amount for the current cart, after discount, before shipping.
     * If prices already include tax, this returns the tax portion of the
     * net amount (gross / (1+rate) * rate). Otherwise it returns the
     * tax added on top.
     */
    public function tax(): float
    {
        $rate = $this->taxRate();

        if ($rate <= 0) {
            return 0.0;
        }

        $base = max(0.0, $this->subtotal() - $this->discount());
        $multiplier = $rate / 100;

        if ($this->taxIsInclusive()) {
            return round($base - ($base / (1 + $multiplier)), 2);
        }

        return round($base * $multiplier, 2);
    }

    /**
     * Cart total before shipping. Includes tax for added-tax mode and
     * leaves the gross total untouched for inclusive-tax mode.
     */
    public function total(): float
    {
        $base = max(0.0, $this->subtotal() - $this->discount());

        if ($this->taxIsInclusive()) {
            return round($base, 2);
        }

        return round($base + $this->tax(), 2);
    }

    private function raw(): array
    {
        return (array) session(self::SESSION_KEY, []);
    }
}
