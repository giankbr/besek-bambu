<?php

namespace App\Mail;

use App\Models\ProductReview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ProductReview $review) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New review: '.$this->review->product?->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reviews.received',
        );
    }
}
