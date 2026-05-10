<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartSnapshot extends Model
{
    protected $fillable = [
        'user_id', 'items', 'subtotal', 'last_seen_at', 'recovery_sent_at',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'last_seen_at' => 'datetime',
        'recovery_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
