<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'og_image',
        'author_name',
        'published_at',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            $slug = static::normalizeSlug($post->slug !== '' ? $post->slug : $post->title);

            if ($slug !== '') {
                $post->slug = $slug;
            }
        });

        $invalidate = function () {
            Cache::forget('sitemap.xml');
            Cache::forget('sitemap.index.xml');
            Cache::forget('sitemap.static.xml');
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function normalizeSlug(string $value): string
    {
        return Str::slug(trim($value));
    }

    public function resolvedMetaTitle(): string
    {
        $custom = trim((string) $this->meta_title);

        return $custom !== '' ? $custom : meta_title($this->title, store_name());
    }

    public function resolvedMetaDescription(): string
    {
        $custom = trim((string) $this->meta_description);

        if ($custom !== '') {
            return $custom;
        }

        $excerpt = trim((string) $this->excerpt);

        if ($excerpt !== '') {
            return Str::limit($excerpt, 155, '…');
        }

        return Str::limit(strip_tags($this->body), 155, '…');
    }

    public function resolvedOgImage(): ?string
    {
        return $this->og_image ? image_src((string) $this->og_image) : default_og_image_url();
    }
}
