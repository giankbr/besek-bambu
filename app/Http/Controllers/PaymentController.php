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

        if (! setting('payment_midtrans', true) || ! config('services.midtrans.server_key')) {
            return redirect()->route('checkout.confirmation', $order)
                ->with('status', 'Online payment is currently disabled. Please follow the instructions on this page.');
        }

        try {
            // Snap token expires; always mint a fresh one when opening the pay page.
            $midtrans->createSnapToken($order);
            $order->refresh();
        } catch (\Throwable $e) {
            Log::warning('Failed to create Midtrans snap token', [
                'order' => $order->number,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('checkout.confirmation', $order)
                ->with('status', __('Gagal menyiapkan pembayaran. Silakan coba lagi atau hubungi kami.'));
        }

        return view('checkout.pay', [
            'order' => $order->load('items'),
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
            Log::warning('Midtrans notification could not be parsed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'invalid payload'], 400);
        }

        if (! $midtrans->verifySignature($notification)) {
            Log::warning('Midtrans notification signature mismatch', [
                'order_id' => $notification->order_id ?? null,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'invalid signature'], 401);
        }

        $order = Order::where('number', $notification->order_id)->first();

        if (! $order) {
            Log::warning('Midtrans notification for unknown order', [
                'order_id' => $notification->order_id ?? null,
            ]);

            return response()->json(['message' => 'order not found'], 404);
        }

        $midtrans->applyNotification($order, $notification);

        return response()->json(['message' => 'ok']);
    }
}
