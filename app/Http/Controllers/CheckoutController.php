<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function show(CartService $cart, ShippingService $shipping)
    {
        if ($cart->items()->isEmpty()) {
            return redirect()->route('cart.show')->with('status', 'Your cart is empty.');
        }

        $methods = enabled_payment_methods();

        if ($methods === []) {
            $methods = ['midtrans' => 'Online payment'];
        }

        return view('checkout.show', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'discount' => $cart->discount(),
            'coupon' => $cart->coupon(),
            'regions' => $shipping->regions(),
            'tax' => $cart->tax(),
            'taxRate' => $cart->taxRate(),
            'taxInclusive' => $cart->taxIsInclusive(),
            'paymentMethods' => $methods,
        ]);
    }

    public function store(Request $request, CheckoutService $checkout, MidtransService $midtrans, ShippingService $shipping)
    {
        $methods = enabled_payment_methods();
        $methodKeys = array_keys($methods);

        if ($methodKeys === []) {
            $methodKeys = ['midtrans'];
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'shipping_region' => ['required', 'string', 'in:'.implode(',', $shipping->regionKeys())],
            'payment_method' => ['nullable', 'string', 'in:'.implode(',', $methodKeys)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $chosenMethod = $data['payment_method'] ?? $methodKeys[0];
        $data['payment_method'] = $chosenMethod;

        try {
            $order = $checkout->place($data);
        } catch (\DomainException $e) {
            return redirect()->route('cart.show')->with('status', $e->getMessage());
        }

        if ($chosenMethod === 'midtrans' && config('services.midtrans.server_key')) {
            try {
                $midtrans->createSnapToken($order);

                return redirect()->route('payment.pay', $order);
            } catch (\Throwable $e) {
                Log::warning('Failed to create Midtrans snap token', ['error' => $e->getMessage()]);
            }
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
