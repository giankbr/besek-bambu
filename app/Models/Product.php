<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): ?string
    {
        $primary = $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();

        return $primary?->path ?? $this->image_url;
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    public function averageRating(): float
    {
        return round((float) $this->approvedReviews()->avg('rating'), 1);
    }

    public function reviewsCount(): int
    {
        return $this->approvedReviews()->count();
    }

    public function isInWishlistOf(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return DB::table('wishlist_items')
            ->where('user_id', $userId)
            ->where('product_id', $this->id)
            ->exists();
    }
}
