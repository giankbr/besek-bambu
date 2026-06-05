<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderRefunded extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Pengembalian dana diproses: :number', ['number' => $this->order->number]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.refunded',
            with: ['order' => $this->order],
        );
    }
}
