<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Each sitemap chunk holds at most this many URLs. The 0.9
     * sitemap protocol caps a single file at 50,000, so 5,000 keeps
     * us well under both that ceiling and Google's preferred size.
     */
    private const CHUNK_SIZE = 5000;

    public function index(): Response
    {
        $totalProducts = Product::query()->where('is_active', true)->count();

        $body = Cache::remember('sitemap.index.xml', now()->addMinutes(30), function () use ($totalProducts) {
            $entries = [
                ['loc' => url('/sitemap-static.xml'), 'lastmod' => now()->toAtomString()],
                ['loc' => url('/sitemap-blog.xml'), 'lastmod' => now()->toAtomString()],
            ];

            if ($totalProducts > self::CHUNK_SIZE) {
                $chunks = (int) ceil($totalProducts / self::CHUNK_SIZE);

                for ($i = 1; $i <= $chunks; $i++) {
                    $entries[] = [
                        'loc' => url("/sitemap-products-{$i}.xml"),
                        'lastmod' => now()->toAtomString(),
                    ];
                }
            }

            return $this->renderSitemapIndex($entries);
        });

        return response($body, 200)->header('Content-Type', 'application/xml');
    }

    public function staticChunk(): Response
    {
        $body = Cache::remember('sitemap.static.xml', now()->addMinutes(30), function () {
            $urls = array_merge($this->staticUrls(), $this->categoryUrls());

            $totalProducts = Product::query()->where('is_active', true)->count();

            if ($totalProducts <= self::CHUNK_SIZE) {
                $urls = array_merge($urls, $this->productUrls(1));
            }

            return $this->renderSitemap($urls);
        });

        return response($body, 200)->header('Content-Type', 'application/xml');
    }

    public function blogChunk(): Response
    {
        $body = Cache::remember('sitemap.blog.xml', now()->addMinutes(30), function () {
            return $this->renderSitemap($this->blogUrls());
        });

        return response($body, 200)->header('Content-Type', 'application/xml');
    }

    public function productChunk(int $page): Response
    {
        $page = max(1, $page);
        $key = "sitemap.products.{$page}.xml";

        $body = Cache::remember($key, now()->addMinutes(30), function () use ($page) {
            return $this->renderSitemap($this->productUrls($page));
        });

        return response($body, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $disallow = [
            '/admin',
            '/account',
            '/cart',
            '/checkout',
            '/payment',
            '/shipping',
            '/dashboard',
            '/settings',
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/email/',
            '/two-factor-challenge',
            '/user/',
        ];

        $lines = [
            'User-agent: *',
            'Allow: /',
            ...array_map(fn (string $path) => "Disallow: {$path}", $disallow),
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function renderSitemapIndex(array $entries): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap-index', ['entries' => $entries])->render();
    }

    private function renderSitemap(array $urls): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap', ['urls' => $urls])->render();
    }

    private function staticUrls(): array
    {
        return [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('shop.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('wholesale'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('blog.index'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => route('gallery'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('faq'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('contact'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('privacy'), 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => route('terms'), 'priority' => '0.4', 'changefreq' => 'yearly'],
        ];
    }

    private function blogUrls(): array
    {
        return BlogPost::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (BlogPost $post) => [
                'loc' => route('blog.show', $post),
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => optional($post->updated_at)->toAtomString(),
            ])
            ->all();
    }

    private function categoryUrls(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Category $c) => [
                'loc' => route('shop.category', $c),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => optional($c->updated_at)->toAtomString(),
            ])
            ->all();
    }

    private function productUrls(int $page): array
    {
        return Product::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->forPage($page, self::CHUNK_SIZE)
            ->get()
            ->map(fn (Product $p) => [
                'loc' => route('shop.product', $p),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => optional($p->updated_at)->toAtomString(),
            ])
            ->all();
    }
}
