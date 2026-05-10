<?php

namespace App\Services;

class ShippingService
{
    public const REGIONS = [
        'java' => ['label' => 'Java (within Indonesia)', 'cost' => 25_000],
        'sumatra' => ['label' => 'Sumatra', 'cost' => 45_000],
        'kalimantan' => ['label' => 'Kalimantan', 'cost' => 55_000],
        'sulawesi' => ['label' => 'Sulawesi', 'cost' => 65_000],
        'eastern' => ['label' => 'Bali, Nusa Tenggara, Maluku, Papua', 'cost' => 85_000],
        'international' => ['label' => 'International', 'cost' => 350_000],
    ];

    public function regions(): array
    {
        return self::REGIONS;
    }

    public function costFor(?string $region): int
    {
        if (! $region || ! isset(self::REGIONS[$region])) {
            return 0;
        }

        return self::REGIONS[$region]['cost'];
    }

    public function labelFor(?string $region): string
    {
        return self::REGIONS[$region]['label'] ?? '—';
    }
}
