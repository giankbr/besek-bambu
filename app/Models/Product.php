<?php

namespace App\Models;

use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'image_url',
        'price', 'stock', 'weight', 'is_active', 'category_id',
        'rating', 'color_class', 'sort_order',
        'meta_title', 'meta_description', 'og_image',
        'low_stock_notified_at',
        'min_order_quantity', 'production_lead_days',
    ];

    public function getLoggableAttributes(): array
    {
        return ['name', 'slug', 'price', 'stock', 'weight', 'is_active', 'category_id', 'sort_order'];
    }

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'integer',
        'stock' => 'integer',
        'weight' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'low_stock_notified_at' => 'datetime',
        'min_order_quantity' => 'integer',
        'production_lead_days' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            // Reset the low-stock notification flag when stock is
            // topped back up above the configured threshold so the
            // next dip can trigger another alert.
            $threshold = (int) (function_exists('setting') ? setting('stock_alert_threshold', 5) : 5);
            if ($threshold > 0 && (int) $product->stock > $threshold) {
                $product->low_stock_notified_at = null;
            }
        });

        $invalidate = function () {
            Cache::forget('sitemap.xml');
            Cache::forget('sitemap.index.xml');
            Cache::forget('sitemap.static.xml');
            // Per-page product chunks share a numeric suffix; flush a
            // generous range so editors never see stale data.
            for ($i = 1; $i <= 50; $i++) {
                Cache::forget("sitemap.products.{$i}.xml");
            }
        };
        static::saved($invalidate);
        static::deleted($invalidate);
    }

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

        // Memoise the user's full wishlist set once per request so a
        // grid of N product cards does not trigger N exists() queries.
        static $cache = [];
        if (! array_key_exists($userId, $cache)) {
            $cache[$userId] = DB::table('wishlist_items')
                ->where('user_id', $userId)
                ->pluck('product_id')
                ->all();
        }

        return in_array($this->id, $cache[$userId], true);
    }
}
