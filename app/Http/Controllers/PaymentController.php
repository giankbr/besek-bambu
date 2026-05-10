<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function pay(Order $order, MidtransService $midtrans)
    {
        if (! $order->canBePaid()) {
            return redirect()->route('checkout.confirmation', $order)
                ->with('status', 'This order cannot be paid.');
        }

        if (! $order->payment_token) {
            $midtrans->createSnapToken($order);
            $order->refresh();
        }

        return view('checkout.pay', [
            'order' => $order,
            'snapToken' => $order->payment_token,
            'clientKey' => config('services.midtrans.client_key'),
            'isProduction' => (bool) config('services.midtrans.is_production'),
        ]);
    }

    public function notification(Request $request, MidtransService $midtrans): JsonResponse
    {
        try {
            $notification = $midtrans->handleNotification();
        } catch (\Throwable $e) {
            Log::warning('Midtrans notification could not be parsed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'invalid payload'], 400);
        }

        $order = Order::where('number', $notification->order_id)->first();

        if (! $order) {
            return response()->json(['message' => 'order not found'], 404);
        }

        $midtrans->applyNotification($order, $notification);

        return response()->json(['message' => 'ok']);
    }
}
