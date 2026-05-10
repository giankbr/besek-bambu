<?php

if (! function_exists('idr')) {
    function idr(int|float|string|null $amount): string
    {
        return 'Rp '.number_format((float) ($amount ?? 0), 0, ',', '.');
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
