<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index()
    {
        $items = DB::table('wishlist_items')
            ->where('user_id', Auth::id())
            ->latest()
            ->pluck('product_id');

        $products = Product::whereIn('id', $items)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($p) => $items->search($p->id))
            ->values();

        return view('account.wishlist', [
            'products' => $products,
        ]);
    }

    public function toggle(Product $product)
    {
        $userId = Auth::id();

        $existing = DB::table('wishlist_items')
            ->where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            DB::table('wishlist_items')->where('id', $existing->id)->delete();
            $message = 'Removed from your wishlist.';
        } else {
            DB::table('wishlist_items')->insert([
                'user_id' => $userId,
                'product_id' => $product->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $message = 'Added to your wishlist.';
        }

        return back()->with('status', $message);
    }
}
