<?php

namespace App\Services;

use App\Enums\NewsletterWelcomeStatus;
use App\Mail\NewsletterWelcome;
use App\Models\Coupon;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterWelcomeService
{
    public const DISCOUNT_PERCENT = 10;

    public const COUPON_VALID_DAYS = 90;

    public function ensureWelcome(NewsletterSubscriber $subscriber): NewsletterWelcomeStatus
    {
        try {
            return DB::transaction(function () use ($subscriber) {
                $subscriber = NewsletterSubscriber::query()
                    ->whereKey($subscriber->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($subscriber->welcome_sent_at !== null) {
                    return NewsletterWelcomeStatus::AlreadySent;
                }

                Mail::to($subscriber->email)->send(new NewsletterWelcome($subscriber));

                $subscriber->update(['welcome_sent_at' => now()]);

                return NewsletterWelcomeStatus::Sent;
            });
        } catch (\Throwable $e) {
            Log::warning('Newsletter welcome email failed', [
                'subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'error' => $e->getMessage(),
            ]);

            return NewsletterWelcomeStatus::Failed;
        }
    }

    public function couponCodeForSubscriber(NewsletterSubscriber $subscriber): string
    {
        return DB::transaction(function () use ($subscriber) {
            $subscriber = NewsletterSubscriber::query()
                ->whereKey($subscriber->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $subscriber->coupon_id) {
                $coupon = $this->createWelcomeCoupon($subscriber);
                $subscriber->update(['coupon_id' => $coupon->id]);
            }

            $subscriber->load('coupon');

            return (string) $subscriber->coupon?->code;
        });
    }

    public function welcomeTemplateBody(?string $couponCode = null): string
    {
        $lines = [
            __('Halo, terima kasih sudah mendaftar newsletter').' '.store_name().'.',
            __('Gunakan kode berikut untuk diskon :percent% pada pembelian besek berikutnya:', ['percent' => self::DISCOUNT_PERCENT]),
        ];

        if ($couponCode) {
            $lines[] = $couponCode;
        } else {
            $lines[] = '{KODE_KUPON}';
        }

        $lines[] = __('Kode berlaku sekali pakai. Masukkan saat checkout di keranjang belanja.');

        return implode("\n\n", $lines);
    }

    public function resendWelcome(NewsletterSubscriber $subscriber): NewsletterWelcomeStatus
    {
        try {
            DB::transaction(function () use ($subscriber) {
                $subscriber = NewsletterSubscriber::query()
                    ->whereKey($subscriber->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                Mail::to($subscriber->email)->send(new NewsletterWelcome($subscriber));

                $subscriber->update(['welcome_sent_at' => now()]);
            });
        } catch (\Throwable $e) {
            Log::warning('Newsletter welcome resend failed', [
                'subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'error' => $e->getMessage(),
            ]);

            return NewsletterWelcomeStatus::Failed;
        }

        return NewsletterWelcomeStatus::Sent;
    }

    protected function createWelcomeCoupon(NewsletterSubscriber $subscriber): Coupon
    {
        do {
            $code = 'WELCOME-'.strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return Coupon::create([
            'code' => $code,
            'label' => __('Newsletter welcome · :email', ['email' => $subscriber->email]),
            'type' => 'percent',
            'value' => self::DISCOUNT_PERCENT,
            'min_order' => 0,
            'usage_limit' => 1,
            'used_count' => 0,
            'expires_at' => now()->addDays(self::COUPON_VALID_DAYS),
            'is_active' => true,
        ]);
    }
}
