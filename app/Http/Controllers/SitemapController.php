<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('shop.index'), 'priority' => '0.9'],
            ['loc' => route('gallery'), 'priority' => '0.7'],
            ['loc' => route('about'), 'priority' => '0.6'],
            ['loc' => route('faq'), 'priority' => '0.5'],
            ['loc' => route('contact'), 'priority' => '0.5'],
        ];

        foreach (Category::orderBy('id')->get() as $category) {
            $urls[] = [
                'loc' => route('shop.category', $category),
                'priority' => '0.7',
                'lastmod' => $category->updated_at->toAtomString(),
            ];
        }

        foreach (Product::where('is_active', true)->orderBy('id')->get() as $product) {
            $urls[] = [
                'loc' => route('shop.product', $product),
                'priority' => '0.8',
                'lastmod' => $product->updated_at->toAtomString(),
            ];
        }

        $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap', ['urls' => $urls])->render();

        return response($body, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /account\nDisallow: /cart\nDisallow: /checkout\nDisallow: /payment\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200)->header('Content-Type', 'text/plain');
    }
}
