<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ShippingCity;
use App\Models\ShippingProvince;
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
                && $shipping->originCityId()
                && ShippingProvince::query()->exists(),
            'provinces' => $shipping->isRajaOngkir()
                ? ShippingProvince::orderBy('name')->get(['id', 'name'])
                : collect(),
            'totalWeight' => $cart->totalWeight(),
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

        $usingRajaOngkir = $shipping->isRajaOngkir() && $request->input('shipping_mode') === 'rajaongkir';

        if ($usingRajaOngkir) {
            $rules['shipping_city_id'] = ['required', Rule::exists('shipping_cities', 'id')];
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
        $data['shipping_mode'] = $usingRajaOngkir ? 'rajaongkir' : 'flat';

        if ($usingRajaOngkir) {
            $city = ShippingCity::find($data['shipping_city_id']);
            if ($city) {
                $data['shipping_city_name'] = trim(($city->type ? $city->type.' ' : '').$city->name);
                $data['shipping_province'] = $city->province_name;
            }
        }

        try {
            $order = $checkout->place($data);
        } catch (\DomainException $e) {
            return redirect()->route('cart.show')->with('status', $e->getMessage());
        }

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
