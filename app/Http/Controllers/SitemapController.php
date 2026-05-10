<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // Cache the rendered XML so a crawler hitting us repeatedly
        // does not run two full table scans on every request. The
        // cache is intentionally short so editors see updates fast.
        $body = Cache::remember('sitemap.xml', now()->addMinutes(30), function () {
            $urls = [
                ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
                ['loc' => route('shop.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
                ['loc' => route('gallery'), 'priority' => '0.7', 'changefreq' => 'monthly'],
                ['loc' => route('about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => route('faq'), 'priority' => '0.5', 'changefreq' => 'monthly'],
                ['loc' => route('contact'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ];

            Category::query()
                ->orderBy('sort_order')
                ->get()
                ->each(function (Category $category) use (&$urls) {
                    $urls[] = [
                        'loc' => route('shop.category', $category),
                        'priority' => '0.7',
                        'changefreq' => 'weekly',
                        'lastmod' => optional($category->updated_at)->toAtomString(),
                    ];
                });

            Product::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get()
                ->each(function (Product $product) use (&$urls) {
                    $urls[] = [
                        'loc' => route('shop.product', $product),
                        'priority' => '0.8',
                        'changefreq' => 'weekly',
                        'lastmod' => optional($product->updated_at)->toAtomString(),
                    ];
                });

            return '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap', ['urls' => $urls])->render();
        });

        return response($body, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /account\nDisallow: /cart\nDisallow: /checkout\nDisallow: /payment\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200)->header('Content-Type', 'text/plain');
    }
}
