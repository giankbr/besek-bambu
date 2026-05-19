<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * @param  User  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $broker = config('auth.defaults.passwords');

        return (new MailMessage)
            ->greeting(mail_greeting($notifiable->name))
            ->subject(__('Reset kata sandi'))
            ->line(__('Anda menerima email ini karena ada permintaan reset kata sandi untuk akun Anda.'))
            ->action(__('Reset kata sandi'), $this->resetUrl($notifiable))
            ->line(__('Link reset berlaku :count menit.', ['count' => config("auth.passwords.{$broker}.expire")]))
            ->line(__('Jika Anda tidak meminta reset, abaikan email ini.'));
    }
}
