<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('sort_order')->get();

        $products = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->when($request->string('category')->toString(), function ($q, $slug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
            })
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where('name', 'like', "%{$term}%");
            })
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $request->string('category')->toString(),
            'searchTerm' => $request->string('q')->toString(),
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

        return view('shop.product', [
            'product' => $product,
            'related' => $related,
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
