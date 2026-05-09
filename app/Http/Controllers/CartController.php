<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(CartService $cart)
    {
        return view('cart.show', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function add(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        abort_unless($product->is_active && $product->stock > 0, 422, 'Product unavailable.');

        $cart->add($product, (int) ($data['quantity'] ?? 1));

        return redirect()->route('cart.show')->with('status', 'Added to cart.');
    }

    public function update(Request $request, int $product, CartService $cart)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $cart->update($product, (int) $data['quantity']);

        return redirect()->route('cart.show');
    }

    public function destroy(int $product, CartService $cart)
    {
        $cart->remove($product);

        return redirect()->route('cart.show')->with('status', 'Item removed.');
    }
}
