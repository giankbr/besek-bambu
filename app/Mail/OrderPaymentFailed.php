<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaymentFailed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $reason = 'failed',
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->reason === 'expired'
            ? __('Batas waktu pembayaran habis: :number', ['number' => $this->order->number])
            : __('Pembayaran gagal: :number', ['number' => $this->order->number]);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.payment-failed',
            with: [
                'order' => $this->order,
                'reason' => $this->reason,
            ],
        );
    }
}
