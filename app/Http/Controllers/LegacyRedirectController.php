<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LegacyRedirectController extends Controller
{
    public function product(string $slug): RedirectResponse|Response
    {
        $product = Product::findBySlugOrLegacy($slug);

        if (! $product) {
            abort(404);
        }

        return $this->redirectForProduct($product);
    }

    public function category(string $slug): RedirectResponse|Response
    {
        $category = Category::query()->where('slug', $slug)->first();

        if (! $category) {
            abort(404);
        }

        return redirect()->route('shop.category', $category, 301);
    }

    public static function redirectForProduct(Product $product): RedirectResponse
    {
        if ($product->is_active) {
            return redirect()->route('shop.product', $product, 301);
        }

        $product->loadMissing('category');

        if ($product->category) {
            return redirect()->route('shop.category', $product->category, 301);
        }

        return redirect()->route('shop.index', [], 301);
    }
}
