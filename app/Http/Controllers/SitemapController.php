<?php

namespace App\Http\Controllers;

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

        // Sitemap index when the catalog gets too big to fit in a
        // single XML file. This points at /sitemap-N.xml chunks.
        if ($totalProducts > self::CHUNK_SIZE) {
            $body = Cache::remember('sitemap.index.xml', now()->addMinutes(30), function () use ($totalProducts) {
                $chunks = (int) ceil($totalProducts / self::CHUNK_SIZE);
                $entries = [
                    ['loc' => url('/sitemap-static.xml'), 'lastmod' => now()->toAtomString()],
                ];

                for ($i = 1; $i <= $chunks; $i++) {
                    $entries[] = [
                        'loc' => url("/sitemap-products-{$i}.xml"),
                        'lastmod' => now()->toAtomString(),
                    ];
                }

                return '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap-index', ['entries' => $entries])->render();
            });

            return response($body, 200)->header('Content-Type', 'application/xml');
        }

        $body = Cache::remember('sitemap.xml', now()->addMinutes(30), function () {
            return $this->renderSitemap(array_merge(
                $this->staticUrls(),
                $this->categoryUrls(),
                $this->productUrls(1),
            ));
        });

        return response($body, 200)->header('Content-Type', 'application/xml');
    }

    public function staticChunk(): Response
    {
        $body = Cache::remember('sitemap.static.xml', now()->addMinutes(30), function () {
            return $this->renderSitemap(array_merge($this->staticUrls(), $this->categoryUrls()));
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
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /account\nDisallow: /cart\nDisallow: /checkout\nDisallow: /payment\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200)->header('Content-Type', 'text/plain');
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
            ['loc' => route('gallery'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('faq'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('contact'), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];
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
