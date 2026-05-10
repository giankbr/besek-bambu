<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

    public const PAYMENT_STATUSES = ['unpaid', 'pending', 'paid', 'failed', 'expired', 'refunded'];

    protected $fillable = [
        'number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'notes', 'subtotal', 'total', 'status',
        'payment_method', 'payment_status', 'payment_token', 'payment_url', 'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

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
