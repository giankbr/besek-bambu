<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            'shippingProvider' => $shipping->provider(),
            'rajaOngkirReady' => $shipping->isRajaOngkir()
                && $shipping->rajaOngkirClient()->isConfigured()
                && $shipping->originId()
                && $shipping->enabledCouriers() !== [],
            'totalWeight' => $cart->totalWeight(),
            'pickupEnabled' => $shipping->pickupEnabled(),
            'pickupAddress' => $shipping->pickupAddress(),
            'pickupNote' => $shipping->pickupNote(),
        ]);
    }

    public function store(Request $request, CheckoutService $checkout, MidtransService $midtrans, ShippingService $shipping)
    {
        $methods = enabled_payment_methods();
        $methodKeys = array_keys($methods);

        if ($methodKeys === []) {
            $methodKeys = ['midtrans'];
        }

        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['nullable', 'string', 'in:'.implode(',', $methodKeys)],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        $mode = (string) $request->input('shipping_mode', '');
        $usingRajaOngkir = $shipping->isRajaOngkir() && $mode === 'rajaongkir';
        $usingPickup = $shipping->pickupEnabled() && $mode === 'pickup';

        if ($usingPickup) {
            $rules['shipping_address'] = ['nullable', 'string', 'max:1000'];
            $rules['shipping_region'] = ['nullable', 'string'];
        } elseif ($usingRajaOngkir) {
            $rules['shipping_province'] = ['required', 'string', 'max:255'];
            $rules['shipping_city_id'] = ['required', 'string', 'max:32'];
            $rules['shipping_city_name'] = ['required', 'string', 'max:255'];
            $rules['shipping_courier'] = ['required', 'string', Rule::in($shipping->enabledCouriers())];
            $rules['shipping_service'] = ['required', 'string', 'max:64'];
            $rules['shipping_cost'] = ['required', 'integer', 'min:0'];
            $rules['shipping_etd'] = ['nullable', 'string', 'max:64'];
            $rules['shipping_region'] = ['nullable', 'string'];
        } else {
            $rules['shipping_region'] = ['required', 'string', 'in:'.implode(',', $shipping->regionKeys())];
        }

        $data = $request->validate($rules);
        $data['payment_method'] = $data['payment_method'] ?? $methodKeys[0];
        $data['shipping_mode'] = $usingPickup ? 'pickup' : ($usingRajaOngkir ? 'rajaongkir' : 'flat');

        if ($usingPickup) {
            $data['shipping_address'] = $shipping->pickupAddress();
            $data['shipping_region'] = 'pickup';
        }

        if ($usingRajaOngkir) {
            try {
                $verified = $shipping->verifyRajaOngkirSelection(
                    (string) $data['shipping_city_id'],
                    (string) $data['shipping_courier'],
                    (string) $data['shipping_service'],
                    app(CartService::class)->totalWeight(),
                    (int) $data['shipping_cost'],
                );

                $data['shipping_cost'] = $verified['cost'];
                $data['shipping_courier'] = $verified['courier'];
                $data['shipping_service'] = $verified['service'];
                $data['shipping_etd'] = $verified['etd'] ?? ($data['shipping_etd'] ?? null);
            } catch (\DomainException $e) {
                return back()->withInput()->with('status', $e->getMessage());
            }
        }

        try {
            $order = $checkout->place($data);
        } catch (\DomainException $e) {
            return redirect()->route('cart.show')->with('status', $e->getMessage());
        }

        grant_order_session_access($order);

        if ($data['payment_method'] === 'midtrans' && config('services.midtrans.server_key')) {
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
