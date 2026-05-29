<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        if (filled($request->input('company'))) {
            return $this->redirectWithStatus(__('Terima kasih! Cek inbox Anda untuk info promo dan diskon.'));
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($data['email']);
        $alreadyRegistered = NewsletterSubscriber::where('email', $email)->exists();

        NewsletterSubscriber::updateOrCreate(
            ['email' => $email],
            ['locale' => app()->getLocale()],
        );

        $message = $alreadyRegistered
            ? __('Email ini sudah terdaftar. Nanti kami kirim promo ke inbox Anda.')
            : __('Terima kasih! Cek inbox Anda untuk info promo dan diskon.');

        return $this->redirectWithStatus($message);
    }

    private function redirectWithStatus(string $message): RedirectResponse
    {
        return back()
            ->withFragment('newsletter')
            ->with('newsletter_status', $message);
    }
}
