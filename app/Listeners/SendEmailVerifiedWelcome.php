<?php

namespace App\Listeners;

use App\Mail\EmailVerifiedWelcome;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailVerifiedWelcome
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        try {
            Mail::to($user->email)->send(new EmailVerifiedWelcome($user));
        } catch (\Throwable $e) {
            Log::warning('Failed to send welcome email', ['user' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
