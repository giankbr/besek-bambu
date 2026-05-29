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
            subject: store_email_subject(__('Terima kasih sudah berlangganan')),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.welcome',
            with: [
                'subscriber' => $this->subscriber,
            ],
        );
    }
}
