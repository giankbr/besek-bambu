<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'title_en',
        'slug',
        'excerpt',
        'excerpt_en',
        'body',
        'body_en',
        'meta_title',
        'meta_title_en',
        'meta_description',
        'meta_description_en',
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
            Cache::forget('sitemap.blog.xml');
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

    public function localizedTitle(): string
    {
        return $this->localizedField('title', 'title_en');
    }

    public function localizedExcerpt(): string
    {
        return $this->localizedField('excerpt', 'excerpt_en');
    }

    public function localizedBody(): string
    {
        return $this->localizedField('body', 'body_en');
    }

    public function resolvedMetaTitle(): string
    {
        if (app()->getLocale() === 'en') {
            $custom = trim((string) $this->meta_title_en);

            if ($custom !== '') {
                return $custom;
            }
        }

        $custom = trim((string) $this->meta_title);

        return $custom !== '' ? $custom : meta_title($this->localizedTitle(), store_name());
    }

    public function resolvedMetaDescription(): string
    {
        if (app()->getLocale() === 'en') {
            $custom = trim((string) $this->meta_description_en);

            if ($custom !== '') {
                return $custom;
            }
        }

        $custom = trim((string) $this->meta_description);

        if ($custom !== '') {
            return $custom;
        }

        $excerpt = trim($this->localizedExcerpt());

        if ($excerpt !== '') {
            return Str::limit($excerpt, 155, '…');
        }

        return Str::limit(strip_tags($this->localizedBody()), 155, '…');
    }

    public function resolvedOgImage(): ?string
    {
        return $this->og_image ? image_src((string) $this->og_image) : default_og_image_url();
    }

    private function localizedField(string $base, string $english): string
    {
        if (app()->getLocale() === 'en') {
            $value = trim((string) $this->{$english});

            if ($value !== '') {
                return $value;
            }
        }

        return (string) $this->{$base};
    }
}
