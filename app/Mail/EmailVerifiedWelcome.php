<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerifiedWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Akun Anda sudah aktif — :store', ['store' => store_name()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.verified',
            with: ['user' => $this->user],
        );
    }
}
