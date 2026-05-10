<?php

namespace App\Services;

use App\Models\CartSnapshot;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartService
{
    private const SESSION_KEY = 'cart';

    private const COUPON_KEY = 'cart_coupon';

    /**
     * Build the deterministic cart key for a (product, variant) pair.
     * Variant-less products keep their plain product id so that
     * existing single-product orders carry over without surprises.
     */
    public static function key(int $productId, ?int $variantId = null): string
    {
        return $variantId ? "{$productId}-{$variantId}" : (string) $productId;
    }

    public function items(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        $productIds = array_unique(array_map(fn ($e) => (int) ($e['product_id'] ?? 0), $cart));
        $variantIds = array_unique(array_filter(array_map(fn ($e) => (int) ($e['variant_id'] ?? 0), $cart)));

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $variants = $variantIds
            ? ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id')
            : collect();

        return collect($cart)
            ->map(function (array $entry, string $key) use ($products, $variants) {
                $product = $products->get((int) $entry['product_id']);
                if (! $product) {
                    return null;
                }

                $variant = isset($entry['variant_id'])
                    ? $variants->get((int) $entry['variant_id'])
                    : null;

                $price = $variant ? $variant->effectivePrice() : (float) $product->price;
                $qty = (int) $entry['quantity'];

                return (object) [
                    'key' => $key,
                    'product' => $product,
                    'variant' => $variant,
                    'variant_label' => $variant?->label,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'line_total' => round($price * $qty, 2),
                ];
            })
            ->filter()
            ->values();
    }

    public function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): void
    {
        $cart = $this->raw();
        $key = self::key($product->id, $variant?->id);
        $current = (int) ($cart[$key]['quantity'] ?? 0);
        $moq = max(1, (int) ($product->min_order_quantity ?? 1));

        if ($current === 0 && $quantity < $moq) {
            $quantity = $moq;
        }

        $next = max(1, $current + $quantity);

        $stockCap = $variant ? (int) $variant->stock : (int) $product->stock;
        if ($stockCap > 0) {
            $next = min($next, $stockCap);
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $next,
        ];

        session([self::SESSION_KEY => $cart]);
        $this->snapshot();
    }

    public function update(string $key, int $quantity): void
    {
        $cart = $this->raw();

        if (! isset($cart[$key])) {
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$key]);
        } else {
            $entry = $cart[$key];
            $product = Product::find((int) $entry['product_id']);
            $variant = isset($entry['variant_id'])
                ? ProductVariant::find((int) $entry['variant_id'])
                : null;

            if ($product) {
                $moq = max(1, (int) ($product->min_order_quantity ?? 1));
                if ($quantity < $moq) {
                    $quantity = $moq;
                }
                $stockCap = $variant ? (int) $variant->stock : (int) $product->stock;
                if ($stockCap > 0) {
                    $quantity = min($quantity, $stockCap);
                }
            }

            $cart[$key]['quantity'] = $quantity;
        }

        session([self::SESSION_KEY => $cart]);
        $this->snapshot();
    }

    public function remove(string $key): void
    {
        $cart = $this->raw();
        unset($cart[$key]);
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
        return array_sum(array_map(fn ($e) => (int) ($e['quantity'] ?? 0), $this->raw()));
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
            $weight = $item->variant
                ? (int) $item->variant->effectiveWeight()
                : (int) ($item->product->weight ?? 0);

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

    public function total(): float
    {
        $base = max(0.0, $this->subtotal() - $this->discount());

        if ($this->taxIsInclusive()) {
            return round($base, 2);
        }

        return round($base + $this->tax(), 2);
    }

    /**
     * Read the raw cart, normalising any legacy `[product_id => qty]`
     * scalar entries from older sessions into the structured shape so
     * the rest of the service can stay simple.
     */
    private function raw(): array
    {
        $cart = (array) session(self::SESSION_KEY, []);

        $changed = false;
        $normalized = [];
        foreach ($cart as $key => $value) {
            if (is_array($value) && isset($value['product_id'], $value['quantity'])) {
                $normalized[(string) $key] = $value;

                continue;
            }

            // Legacy entry: scalar quantity keyed by product id.
            $pid = (int) $key;
            $qty = (int) $value;
            if ($pid > 0 && $qty > 0) {
                $normalized[(string) $pid] = [
                    'product_id' => $pid,
                    'variant_id' => null,
                    'quantity' => $qty,
                ];
                $changed = true;
            }
        }

        if ($changed) {
            session([self::SESSION_KEY => $normalized]);
        }

        return $normalized;
    }
}
