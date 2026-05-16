<?php

namespace Tests\Feature;

use App\Mail\OrderPaid;
use App\Mail\OrderPaymentFailed;
use App\Mail\OrderRefunded;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Midtrans\Notification;
use Tests\TestCase;

class MidtransNotificationMailTest extends TestCase
{
    use RefreshDatabase;

    private function orderWithTotal(string $total = '225000.00'): Order
    {
        return Order::create([
            'number' => 'BSK-'.strtoupper(uniqid()),
            'customer_name' => 'Buyer',
            'customer_email' => 'buyer@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test',
            'subtotal' => $total,
            'total' => $total,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    private function notification(string $status, string $grossAmount): Notification
    {
        $notification = \Mockery::mock(Notification::class);
        $notification->transaction_status = $status;
        $notification->gross_amount = $grossAmount;
        $notification->fraud_status = 'accept';
        $notification->payment_type = 'bank_transfer';

        return $notification;
    }

    public function test_settlement_sends_paid_email(): void
    {
        Mail::fake();

        $order = $this->orderWithTotal();
        app(MidtransService::class)->applyNotification($order, $this->notification('settlement', '225000.00'));

        Mail::assertSent(OrderPaid::class, fn (OrderPaid $mail) => $mail->hasTo('buyer@example.com'));
    }

    public function test_deny_sends_payment_failed_email(): void
    {
        Mail::fake();

        $order = $this->orderWithTotal();
        app(MidtransService::class)->applyNotification($order, $this->notification('deny', '225000.00'));

        Mail::assertSent(OrderPaymentFailed::class, function (OrderPaymentFailed $mail) {
            return $mail->hasTo('buyer@example.com') && $mail->reason === 'failed';
        });
    }

    public function test_expire_sends_payment_expired_email(): void
    {
        Mail::fake();

        $order = $this->orderWithTotal();
        app(MidtransService::class)->applyNotification($order, $this->notification('expire', '225000.00'));

        Mail::assertSent(OrderPaymentFailed::class, function (OrderPaymentFailed $mail) {
            return $mail->hasTo('buyer@example.com') && $mail->reason === 'expired';
        });
    }

    public function test_refund_sends_refunded_email(): void
    {
        Mail::fake();

        $order = $this->orderWithTotal();
        $order->update(['payment_status' => 'paid', 'status' => 'paid']);

        app(MidtransService::class)->applyNotification($order, $this->notification('refund', '225000.00'));

        Mail::assertSent(OrderRefunded::class, fn (OrderRefunded $mail) => $mail->hasTo('buyer@example.com'));
    }

    public function test_paid_order_does_not_receive_duplicate_failed_email(): void
    {
        Mail::fake();

        $order = $this->orderWithTotal();
        $order->update(['payment_status' => 'paid', 'status' => 'paid']);

        app(MidtransService::class)->applyNotification($order, $this->notification('deny', '225000.00'));

        Mail::assertNothingSent();
    }
}
