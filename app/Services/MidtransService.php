<?php

namespace App\Services;

use App\Mail\OrderPaid;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
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

    /**
     * Verify the signature_key sent by Midtrans matches the SHA-512
     * digest of order_id + status_code + gross_amount + server_key.
     * This is the only reliable way to confirm the webhook actually
     * came from Midtrans and was not forged. See
     * https://docs.midtrans.com/reference/notification-webhooks#signature-key
     */
    public function verifySignature(Notification $notification): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            return false;
        }

        $expected = hash('sha512',
            ($notification->order_id ?? '')
            .($notification->status_code ?? '')
            .($notification->gross_amount ?? '')
            .$serverKey
        );

        $received = (string) ($notification->signature_key ?? '');

        return hash_equals($expected, $received);
    }

    /**
     * Apply the notification to the order while taking a row lock so
     * concurrent retries from Midtrans cannot race each other into
     * sending duplicate emails or downgrading a paid order.
     */
    public function applyNotification(Order $order, Notification $notification): void
    {
        DB::transaction(function () use ($order, $notification) {
            /** @var Order $fresh */
            $fresh = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            // Reject obvious tampering: the gross amount in the
            // notification must equal what we recorded on the order.
            $expectedAmount = (int) round((float) $fresh->total);
            $receivedAmount = (int) round((float) ($notification->gross_amount ?? 0));

            if ($expectedAmount !== $receivedAmount) {
                Log::warning('Midtrans notification amount mismatch', [
                    'order' => $fresh->number,
                    'expected' => $expectedAmount,
                    'received' => $receivedAmount,
                ]);

                return;
            }

            $transactionStatus = $notification->transaction_status ?? null;
            $fraudStatus = $notification->fraud_status ?? null;
            $paymentType = $notification->payment_type ?? null;

            $newStatus = match (true) {
                $transactionStatus === 'capture' && $fraudStatus === 'challenge' => 'pending',
                $transactionStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
                $transactionStatus === 'settlement' => 'paid',
                $transactionStatus === 'pending' => 'pending',
                $transactionStatus === 'deny' => 'failed',
                $transactionStatus === 'expire' => 'expired',
                $transactionStatus === 'cancel' => 'failed',
                $transactionStatus === 'refund', $transactionStatus === 'partial_refund' => 'refunded',
                default => null,
            };

            if ($newStatus === null) {
                Log::info('Midtrans notification with unknown transaction status', [
                    'order' => $fresh->number,
                    'transaction_status' => $transactionStatus,
                ]);

                return;
            }

            // Status regression guard: do not flip a paid or refunded
            // order back to pending or failed because of a delayed
            // retry of an older notification.
            if ($fresh->payment_status === 'paid' && in_array($newStatus, ['pending', 'failed', 'expired'], true)) {
                Log::info('Ignoring stale Midtrans notification', [
                    'order' => $fresh->number,
                    'current' => $fresh->payment_status,
                    'incoming' => $newStatus,
                ]);

                return;
            }

            $becamePaid = $newStatus === 'paid' && $fresh->payment_status !== 'paid';

            $update = [
                'payment_status' => $newStatus,
                'payment_method' => $paymentType ?: $fresh->payment_method,
            ];

            if ($becamePaid) {
                $update['paid_at'] = now();
                if ($fresh->status === 'pending') {
                    $update['status'] = 'paid';
                }
            }

            $fresh->update($update);

            if ($becamePaid) {
                try {
                    Mail::to($fresh->customer_email)->send(new OrderPaid($fresh->fresh('items')));
                } catch (\Throwable $e) {
                    Log::warning('Failed to send order paid email', ['order' => $fresh->number, 'error' => $e->getMessage()]);
                }
            }

            Log::info('Midtrans notification applied', [
                'order' => $fresh->number,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_status' => $newStatus,
            ]);
        });
    }
}
