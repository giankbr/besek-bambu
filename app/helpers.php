<?php

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\URL;

if (! function_exists('idr')) {
    function idr(int|float|string|null $amount): string
    {
        return 'Rp '.number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('mail_greeting')) {
    function mail_greeting(string $name): string
    {
        return __('Halo, :name!', ['name' => $name]);
    }
}

if (! function_exists('store_name')) {
    function store_name(): string
    {
        $value = setting('store_name');
        if ($value) {
            return trim((string) $value);
        }

        $appName = trim((string) config('app.name', 'Besek Bambu'));

        return $appName === 'Laravel' ? 'Besek Bambu' : $appName;
    }
}

if (! function_exists('store_email_subject')) {
    /** Branded subject prefix: [Store Name] without stray spaces inside brackets. */
    function store_email_subject(string $text): string
    {
        return '['.store_name().'] '.$text;
    }
}

if (! function_exists('meta_title')) {
    function meta_title(string ...$parts): string
    {
        return implode(', ', array_filter($parts, static fn ($part) => $part !== '' && $part !== null));
    }
}

if (! function_exists('store_logo_url')) {
    function store_logo_url(): ?string
    {
        $logo = setting('store_logo');

        return $logo ? image_src((string) $logo) : null;
    }
}

if (! function_exists('store_email')) {
    function store_email(): ?string
    {
        $value = setting('store_email');

        return $value ? (string) $value : null;
    }
}

if (! function_exists('store_phone')) {
    function store_phone(): ?string
    {
        $value = setting('store_phone');

        return $value ? (string) $value : null;
    }
}

if (! function_exists('whatsapp_digits')) {
    /**
     * WhatsApp number digits for wa.me links (order number, store phone, or social link).
     */
    function whatsapp_digits(): ?string
    {
        $raw = (string) (setting('whatsapp_order_number') ?: setting('store_phone') ?: '');
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits !== '') {
            return $digits;
        }

        $social = trim((string) setting('social_whatsapp'));

        if ($social !== '') {
            if (preg_match('#wa\.me/(\d+)#i', $social, $matches)) {
                return $matches[1];
            }

            if (preg_match('#phone=(\d+)#i', $social, $matches)) {
                return $matches[1];
            }
        }

        $envDigits = preg_replace('/\D+/', '', (string) config('store.whatsapp_number', ''));

        return $envDigits !== '' ? $envDigits : null;
    }
}

if (! function_exists('store_address')) {
    function store_address(): ?string
    {
        $value = setting('store_address');

        return $value ? (string) $value : null;
    }
}

if (! function_exists('store_socials')) {
    /**
     * @return array<string, string> map of platform key => url for non-empty links
     */
    function store_socials(): array
    {
        return collect([
            'instagram' => setting('social_instagram'),
            'facebook' => setting('social_facebook'),
            'tiktok' => setting('social_tiktok'),
            'whatsapp' => setting('social_whatsapp'),
        ])
            ->map(fn ($v) => is_string($v) ? trim($v) : '')
            ->filter(fn ($v) => $v !== '')
            ->all();
    }
}

if (! function_exists('midtrans_payment_logos')) {
    /**
     * Official payment provider logos from Midtrans (veritrans/logo).
     *
     * @return list<array{file: string, alt: string}>
     */
    function midtrans_payment_logos(): array
    {
        return [
            ['file' => 'visa.png', 'alt' => 'Visa'],
            ['file' => 'mastercard.png', 'alt' => 'Mastercard'],
            ['file' => 'jcb.png', 'alt' => 'JCB'],
            ['file' => 'amex.png', 'alt' => 'American Express'],
            ['file' => 'bca.png', 'alt' => 'BCA'],
            ['file' => 'bni.png', 'alt' => 'BNI'],
            ['file' => 'mandiri.png', 'alt' => 'Bank Mandiri'],
            ['file' => 'permata.png', 'alt' => 'PermataBank'],
            ['file' => 'cimb.png', 'alt' => 'CIMB Niaga'],
            ['file' => 'bri.png', 'alt' => 'BRI'],
            ['file' => 'gopay.png', 'alt' => 'GoPay'],
            ['file' => 'shopee.png', 'alt' => 'ShopeePay'],
            ['file' => 'qris.png', 'alt' => 'QRIS'],
            ['file' => 'dana.png', 'alt' => 'DANA'],
        ];
    }
}

if (! function_exists('enabled_payment_methods')) {
    /**
     * @return array<string, string> map of method key => label for enabled methods
     */
    function enabled_payment_methods(): array
    {
        $candidates = [
            'midtrans' => [
                'enabled' => (bool) setting('payment_midtrans', true) && (bool) config('services.midtrans.server_key'),
                'label' => __('Bayar online'),
            ],
            'manual_transfer' => [
                'enabled' => (bool) setting('payment_manual_transfer', false),
                'label' => 'Manual bank transfer',
            ],
            'cod' => [
                'enabled' => (bool) setting('payment_cod', false),
                'label' => 'Cash on delivery',
            ],
        ];

        $out = [];
        foreach ($candidates as $key => $info) {
            if ($info['enabled']) {
                $out[$key] = $info['label'];
            }
        }

        return $out;
    }
}

if (! function_exists('image_src')) {
    function image_src(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset('storage/'.ltrim($value, '/'));
    }
}

if (! function_exists('grant_order_session_access')) {
    function grant_order_session_access(Order $order): void
    {
        $numbers = session('accessible_order_numbers', []);

        if (! is_array($numbers)) {
            $numbers = [];
        }

        $numbers[] = $order->number;
        $numbers = array_values(array_unique($numbers));

        if (count($numbers) > 20) {
            $numbers = array_slice($numbers, -20);
        }

        session(['accessible_order_numbers' => $numbers]);
    }
}

if (! function_exists('order_signed_url')) {
    function order_signed_url(string $routeName, Order $order, ?DateTimeInterface $expires = null): string
    {
        return URL::temporarySignedRoute(
            $routeName,
            $expires ?? now()->addDays(30),
            ['order' => $order],
        );
    }
}

if (! function_exists('order_status_label')) {
    /**
     * Label status pesanan dalam Bahasa Indonesia (bukan nilai teknis Inggris).
     */
    function order_status_label(string $status): string
    {
        return match ($status) {
            'pending' => __('Menunggu'),
            'paid' => __('Sedang diproses'),
            'shipped' => __('Dikirim'),
            'delivered' => __('Sampai'),
            'cancelled' => __('Dibatalkan'),
            default => ucfirst($status),
        };
    }
}

if (! function_exists('payment_status_label')) {
    function payment_status_label(string $status): string
    {
        return match ($status) {
            'unpaid' => __('Belum dibayar'),
            'pending' => __('Menunggu pembayaran'),
            'paid' => __('Lunas'),
            'failed' => __('Gagal'),
            'expired' => __('Kedaluwarsa'),
            'refunded' => __('Dikembalikan'),
            default => ucfirst($status),
        };
    }
}

if (! function_exists('payment_method_label')) {
    function payment_method_label(?string $method): string
    {
        if ($method === null || $method === '') {
            return '—';
        }

        return match ($method) {
            'midtrans' => __('Bayar online'),
            'manual_transfer' => __('Transfer bank manual'),
            'cod' => __('Bayar di tempat (COD)'),
            'bank_transfer' => __('Transfer bank'),
            'bca_va' => __('VA BCA'),
            'bni_va' => __('VA BNI'),
            'bri_va' => __('VA BRI'),
            'permata_va' => __('VA Permata'),
            'other_va' => __('Virtual Account'),
            'echannel' => __('Mandiri Bill Payment'),
            'credit_card' => __('Kartu kredit/debit'),
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'qris' => 'QRIS',
            'dana' => 'DANA',
            'akulaku' => 'Akulaku',
            'kredivo' => 'Kredivo',
            'cstore', 'indomaret', 'alfamart' => __('Gerai retail'),
            default => ucwords(str_replace('_', ' ', $method)),
        };
    }
}

if (! function_exists('default_meta_description')) {
    function default_meta_description(): string
    {
        $custom = trim((string) setting('seo_default_meta_description', ''));

        if ($custom !== '') {
            return $custom;
        }

        return __('Peralatan dapur bambu buatan tangan dari Indonesia. Berkelanjutan, mudah terurai, dan dibuat oleh pengrajin.');
    }
}

if (! function_exists('default_og_image_url')) {
    function default_og_image_url(): ?string
    {
        $og = setting('seo_default_og_image');

        if ($og) {
            return image_src((string) $og);
        }

        return store_logo_url();
    }
}

if (! function_exists('localized_url')) {
    function localized_url(string $locale): string
    {
        return request()->fullUrlWithQuery(array_merge(request()->query(), ['lang' => $locale]));
    }
}

if (! function_exists('twitter_handle')) {
    function twitter_handle(): ?string
    {
        $handle = trim((string) setting('social_twitter', ''));

        if ($handle === '') {
            return null;
        }

        return str_starts_with($handle, '@') ? $handle : '@'.$handle;
    }
}
