<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public const TYPES = ['fixed', 'percent'];

    protected $fillable = [
        'code', 'label', 'type', 'value', 'min_order',
        'usage_limit', 'used_count', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isUsable(float $subtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return $subtotal >= (float) $this->min_order;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (! $this->isUsable($subtotal)) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return min($discount, $subtotal);
    }
}
