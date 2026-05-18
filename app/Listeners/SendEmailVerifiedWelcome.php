<?php

namespace App\Listeners;

use App\Mail\EmailVerifiedWelcome;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;

class SendEmailVerifiedWelcome
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        Mail::to($user->email)->send(new EmailVerifiedWelcome($user));
    }
}
