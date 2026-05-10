<?php

namespace App\Models;

use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use LogsActivity;

    public const STATUSES = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

    public const PAYMENT_STATUSES = ['unpaid', 'pending', 'paid', 'failed', 'expired', 'refunded'];

    protected $fillable = [
        'number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'shipping_region', 'shipping_cost',
        'shipping_province', 'shipping_city_id', 'shipping_city_name',
        'shipping_courier', 'shipping_service', 'shipping_etd', 'shipping_weight',
        'tracking_number', 'shipped_at', 'delivered_at',
        'notes', 'subtotal', 'discount', 'tax', 'tax_rate', 'tax_inclusive',
        'coupon_code', 'total', 'status',
        'payment_method', 'payment_status', 'payment_token', 'payment_url', 'paid_at',
    ];

    public function getLoggableAttributes(): array
    {
        return ['status', 'payment_status', 'payment_method', 'paid_at', 'total', 'shipping_cost', 'tracking_number'];
    }

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function hasTracking(): bool
    {
        return ! empty($this->tracking_number) && ! empty($this->shipping_courier);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canBePaid(): bool
    {
        return in_array($this->payment_status, ['unpaid', 'pending'], true)
            && $this->status !== 'cancelled';
    }
}
