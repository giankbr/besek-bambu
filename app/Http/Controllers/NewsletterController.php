<?php

namespace App\Http\Controllers;

use App\Enums\NewsletterWelcomeStatus;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterWelcomeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function __construct(
        protected NewsletterWelcomeService $welcomeService,
    ) {}

    public function subscribe(Request $request): RedirectResponse
    {
        if (filled($request->input('company'))) {
            return $this->redirectWithStatus(__('Terima kasih! Cek inbox Anda untuk email konfirmasi.'));
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($data['email']);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $email]);
        $wasExisting = $subscriber->exists;

        $subscriber->locale = app()->getLocale();
        $subscriber->save();

        $status = $this->welcomeService->ensureWelcome($subscriber);

        $message = match ($status) {
            NewsletterWelcomeStatus::Sent => __('Terima kasih! Cek inbox Anda. Kami sudah kirim email konfirmasi.'),
            NewsletterWelcomeStatus::AlreadySent => __('Email ini sudah terdaftar.'),
            NewsletterWelcomeStatus::Failed => $wasExisting
                ? __('Email ini sudah terdaftar.')
                : __('Terima kasih! Email tercatat. Jika belum ada email, hubungi kami.'),
        };

        return $this->redirectWithStatus($message);
    }

    private function redirectWithStatus(string $message): RedirectResponse
    {
        return back()
            ->withFragment('newsletter')
            ->with('newsletter_status', $message);
    }
}
