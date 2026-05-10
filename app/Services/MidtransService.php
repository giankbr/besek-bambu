<?php

namespace App\Services;

use App\Mail\OrderPaid;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = (string) config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized');
        Config::$is3ds = (bool) config('services.midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): string
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $order->number,
                'gross_amount' => (int) round((float) $order->total),
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'shipping_address' => [
                    'address' => $order->shipping_address,
                ],
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id' => (string) $item->product_id,
                'name' => mb_substr($item->product_name, 0, 50),
                'price' => (int) round((float) $item->price),
                'quantity' => (int) $item->quantity,
            ])->all(),
            'callbacks' => [
                'finish' => route('checkout.confirmation', $order),
            ],
        ];

        $token = Snap::getSnapToken($payload);
        $url = Snap::getSnapUrl($payload);

        $order->update([
            'payment_token' => $token,
            'payment_url' => $url,
            'payment_status' => 'pending',
        ]);

        return $token;
    }

    public function handleNotification(): Notification
    {
        return new Notification;
    }

    public function applyNotification(Order $order, Notification $notification): void
    {
        $transactionStatus = $notification->transaction_status ?? null;
        $fraudStatus = $notification->fraud_status ?? null;
        $paymentType = $notification->payment_type ?? null;

        $paymentStatus = match (true) {
            $transactionStatus === 'capture' && $fraudStatus === 'challenge' => 'pending',
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
            $transactionStatus === 'settlement' => 'paid',
            $transactionStatus === 'pending' => 'pending',
            $transactionStatus === 'deny' => 'failed',
            $transactionStatus === 'expire' => 'expired',
            $transactionStatus === 'cancel' => 'failed',
            $transactionStatus === 'refund', $transactionStatus === 'partial_refund' => 'refunded',
            default => $order->payment_status,
        };

        $update = [
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentType ?: $order->payment_method,
        ];

        $becamePaid = $paymentStatus === 'paid' && $order->payment_status !== 'paid';

        if ($becamePaid) {
            $update['paid_at'] = now();
            if ($order->status === 'pending') {
                $update['status'] = 'paid';
            }
        }

        $order->update($update);

        if ($becamePaid) {
            try {
                Mail::to($order->customer_email)->send(new OrderPaid($order->fresh('items')));
            } catch (\Throwable $e) {
                Log::warning('Failed to send order paid email', ['order' => $order->number, 'error' => $e->getMessage()]);
            }
        }
    }
}
