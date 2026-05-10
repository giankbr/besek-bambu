<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('sort_order')->get();

        $sort = $request->string('sort')->toString() ?: 'featured';
        $minPrice = $request->integer('min_price');
        $maxPrice = $request->integer('max_price');
        $minRating = $request->integer('min_rating');

        $products = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->when($request->string('category')->toString(), function ($q, $slug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
            })
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where('name', 'like', "%{$term}%");
            })
            ->when($minPrice > 0, fn ($q) => $q->where('price', '>=', $minPrice))
            ->when($maxPrice > 0, fn ($q) => $q->where('price', '<=', $maxPrice))
            ->when($minRating > 0, fn ($q) => $q->where('rating', '>=', $minRating))
            ->when($sort === 'price-asc', fn ($q) => $q->orderBy('price'))
            ->when($sort === 'price-desc', fn ($q) => $q->orderByDesc('price'))
            ->when($sort === 'newest', fn ($q) => $q->latest())
            ->when($sort === 'rating', fn ($q) => $q->orderByDesc('rating'))
            ->when(! in_array($sort, ['price-asc', 'price-desc', 'newest', 'rating'], true), fn ($q) => $q->orderBy('sort_order'))
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $request->string('category')->toString(),
            'searchTerm' => $request->string('q')->toString(),
            'sort' => $sort,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'minRating' => $minRating,
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load('category');

        $related = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $reviews = $product->approvedReviews()
            ->with('user:id,name')
            ->latest()
            ->take(20)
            ->get();

        $averageRating = $product->averageRating();
        $reviewsCount = $reviews->count();

        $canReview = false;
        $eligibleOrder = null;
        $hasReviewed = false;

        if (Auth::check()) {
            $hasReviewed = ProductReview::where('product_id', $product->id)
                ->where('user_id', Auth::id())
                ->exists();

            $eligibleOrder = Order::where('user_id', Auth::id())
                ->whereIn('status', ['delivered', 'paid', 'shipped'])
                ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
                ->latest()
                ->first();

            $canReview = ! $hasReviewed && $eligibleOrder !== null;
        }

        return view('shop.product', [
            'product' => $product,
            'related' => $related,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'reviewsCount' => $reviewsCount,
            'canReview' => $canReview,
            'eligibleOrder' => $eligibleOrder,
            'hasReviewed' => $hasReviewed,
        ]);
    }

    public function category(Category $category)
    {
        $products = $category->products()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->paginate(12);

        return view('shop.category', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
