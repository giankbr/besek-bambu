<?php

use App\Models\Setting;

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

if (! function_exists('store_name')) {
    function store_name(): string
    {
        $value = setting('store_name');
        if ($value) {
            return (string) $value;
        }

        $appName = (string) config('app.name', 'Besek Bambu');

        return $appName === 'Laravel' ? 'Besek Bambu' : $appName;
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

if (! function_exists('enabled_payment_methods')) {
    /**
     * @return array<string, string> map of method key => label for enabled methods
     */
    function enabled_payment_methods(): array
    {
        $candidates = [
            'midtrans' => [
                'enabled' => (bool) setting('payment_midtrans', true) && (bool) config('services.midtrans.server_key'),
                'label' => 'Online payment (card / bank transfer / e-wallet / QRIS)',
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
