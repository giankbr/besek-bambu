<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends VerifyEmailNotification
{
    /**
     * @param  User  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return (new MailMessage)
            ->greeting(mail_greeting($notifiable->name))
            ->subject(__('Verifikasi alamat email Anda'))
            ->line(__('Silakan klik tombol di bawah untuk memverifikasi alamat email Anda.'))
            ->action(__('Verifikasi alamat email'), $verificationUrl)
            ->line(__('Jika Anda tidak membuat akun, abaikan email ini.'));
    }
}
