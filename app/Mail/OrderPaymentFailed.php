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
            ? 'Payment window expired'
            : 'Payment could not be completed';

        return new Envelope(
            subject: $subject.' — '.$this->order->number,
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
