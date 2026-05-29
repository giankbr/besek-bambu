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

    public const PLACEHOLDER_NAME = '{NAMA}';

    public const PLACEHOLDER_COUPON = '{KODE_KUPON}';

    public function __construct(
        protected NewsletterEmailLogService $emailLogService,
    ) {}

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

                $mailable = new NewsletterWelcome($subscriber);
                Mail::to($subscriber->email)->send($mailable);

                $this->emailLogService->record(
                    $subscriber,
                    $mailable->envelope()->subject,
                    $this->welcomeEmailBodyForLog($subscriber),
                );

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

    public function welcomeTemplateBody(?string $couponCode = null, ?NewsletterSubscriber $subscriber = null): string
    {
        $lines = [
            __('Halo, :name, terima kasih sudah mendaftar newsletter', [
                'name' => self::PLACEHOLDER_NAME,
            ]).' '.store_name().'.',
            __('Gunakan kode berikut untuk diskon :percent% pada pembelian besek berikutnya:', ['percent' => self::DISCOUNT_PERCENT]),
        ];

        if ($couponCode) {
            $lines[] = $couponCode;
        } else {
            $lines[] = self::PLACEHOLDER_COUPON;
        }

        $lines[] = __('Kode berlaku sekali pakai. Masukkan saat checkout di keranjang belanja.');

        $body = implode("\n\n", $lines);

        if ($subscriber) {
            $body = $this->personalizeBody($subscriber, $body);
        }

        return $body;
    }

    public function personalizeBody(NewsletterSubscriber $subscriber, string $body): string
    {
        if (str_contains($body, self::PLACEHOLDER_NAME)) {
            $body = str_replace(self::PLACEHOLDER_NAME, $subscriber->displayName(), $body);
        }

        if (str_contains($body, self::PLACEHOLDER_COUPON)) {
            $body = str_replace(
                self::PLACEHOLDER_COUPON,
                $this->couponCodeForSubscriber($subscriber),
                $body,
            );
        }

        return $body;
    }

    public function resendWelcome(NewsletterSubscriber $subscriber): NewsletterWelcomeStatus
    {
        try {
            DB::transaction(function () use ($subscriber) {
                $subscriber = NewsletterSubscriber::query()
                    ->whereKey($subscriber->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $mailable = new NewsletterWelcome($subscriber);
                Mail::to($subscriber->email)->send($mailable);

                $this->emailLogService->record(
                    $subscriber,
                    $mailable->envelope()->subject,
                    $this->welcomeEmailBodyForLog($subscriber),
                );

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

    protected function welcomeEmailBodyForLog(NewsletterSubscriber $subscriber): string
    {
        return implode("\n\n", [
            __('Terima kasih sudah berlangganan!'),
            __('Halo, :name, terima kasih sudah mendaftar newsletter', [
                'name' => $subscriber->displayName(),
            ]).' '.store_name().'.',
            __('Kami akan mengirim kabar produk, tips packing hantaran, dan cerita dari pengrajin kami ke email ini.'),
            __('Tanpa spam. Hanya yang relevan untuk Anda.'),
        ]);
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
