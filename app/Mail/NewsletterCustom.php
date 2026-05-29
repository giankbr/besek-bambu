<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterCustom extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
        public string $mailSubject,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.custom',
            with: [
                'body' => $this->body,
                'shopUrl' => route('shop.index'),
            ],
        );
    }
}
