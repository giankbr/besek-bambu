<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'image_url',
        'price', 'stock', 'is_active', 'category_id',
        'rating', 'color_class', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'integer',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
