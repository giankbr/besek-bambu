<?php

namespace App\Models;

use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title', 'slug', 'image_url', 'sort_order',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getLoggableAttributes(): array
    {
        return ['title', 'slug', 'sort_order'];
    }

    protected static function booted(): void
    {
        $invalidate = fn () => Cache::forget('sitemap.xml');
        static::saved($invalidate);
        static::deleted($invalidate);
    }
}
