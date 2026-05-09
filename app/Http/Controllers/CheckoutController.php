<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(CartService $cart)
    {
        if ($cart->items()->isEmpty()) {
            return redirect()->route('cart.show')->with('status', 'Your cart is empty.');
        }

        return view('checkout.show', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function store(Request $request, CheckoutService $checkout)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $order = $checkout->place($data);
        } catch (\DomainException $e) {
            return redirect()->route('cart.show')->with('status', $e->getMessage());
        }

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Order $order)
    {
        return view('checkout.confirmation', [
            'order' => $order->load('items'),
        ]);
    }
}
