<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'label', 'sku', 'price', 'stock',
        'weight', 'sort_order', 'is_default',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'weight' => 'integer',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function effectivePrice(): float
    {
        return (float) ($this->price ?? $this->product?->price ?? 0);
    }

    public function effectiveWeight(): int
    {
        return (int) ($this->weight ?? $this->product?->weight ?? 0);
    }
}
