<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(CartService $cart)
    {
        return view('cart.show', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'coupon' => $cart->coupon(),
            'discount' => $cart->discount(),
            'tax' => $cart->tax(),
            'taxRate' => $cart->taxRate(),
            'taxInclusive' => $cart->taxIsInclusive(),
            'preTotal' => $cart->total(),
        ]);
    }

    public function add(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        $product = Product::with('variants')->findOrFail($data['product_id']);
        abort_unless($product->is_active, 422, 'Product unavailable.');

        $variant = null;
        if ($product->hasVariants()) {
            abort_unless(! empty($data['variant_id']), 422, 'Please choose a size first.');
            $variant = ProductVariant::where('product_id', $product->id)
                ->whereKey($data['variant_id'])
                ->firstOrFail();
            abort_unless($variant->stock > 0, 422, 'Selected size is sold out.');
        } else {
            abort_unless($product->stock > 0, 422, 'Product unavailable.');
        }

        $cart->add($product, (int) ($data['quantity'] ?? 1), $variant);

        return redirect()->route('cart.show')->with('status', 'Added to cart.');
    }

    public function update(Request $request, string $key, CartService $cart)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $cart->update($key, (int) $data['quantity']);

        return redirect()->route('cart.show');
    }

    public function destroy(string $key, CartService $cart)
    {
        $cart->remove($key);

        return redirect()->route('cart.show')->with('status', 'Item removed.');
    }

    public function applyCoupon(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        try {
            $coupon = $cart->applyCoupon(strtoupper(trim($data['code'])));
        } catch (\DomainException $e) {
            return back()->with('status', $e->getMessage());
        }

        return back()->with('status', "Coupon {$coupon->code} applied.");
    }

    public function removeCoupon(CartService $cart)
    {
        $cart->clearCoupon();

        return back()->with('status', 'Coupon removed.');
    }
}
