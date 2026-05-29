<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('[ :store ] Kode diskon 10% untuk Anda', ['store' => store_name()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.welcome',
            with: [
                'subscriber' => $this->subscriber,
                'couponCode' => $this->subscriber->coupon?->code,
                'shopUrl' => route('shop.index'),
            ],
        );
    }
}
