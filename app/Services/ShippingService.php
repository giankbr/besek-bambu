<?php

namespace App\Services;

use Illuminate\Support\Str;

class ShippingService
{
    /**
     * Built-in fallback regions used when no custom zones are configured
     * via Settings → Shipping in the admin panel.
     */
    public const REGIONS = [
        'java' => ['label' => 'Java (within Indonesia)', 'cost' => 25_000],
        'sumatra' => ['label' => 'Sumatra', 'cost' => 45_000],
        'kalimantan' => ['label' => 'Kalimantan', 'cost' => 55_000],
        'sulawesi' => ['label' => 'Sulawesi', 'cost' => 65_000],
        'eastern' => ['label' => 'Bali, Nusa Tenggara, Maluku, Papua', 'cost' => 85_000],
        'international' => ['label' => 'International', 'cost' => 350_000],
    ];

    /**
     * @return array<string, array{label: string, cost: int}>
     */
    public function regions(): array
    {
        $custom = $this->customZones();

        return $custom !== [] ? $custom : self::REGIONS;
    }

    public function costFor(?string $region): int
    {
        $regions = $this->regions();

        if (! $region || ! isset($regions[$region])) {
            return 0;
        }

        return (int) $regions[$region]['cost'];
    }

    public function labelFor(?string $region): string
    {
        return $this->regions()[$region]['label'] ?? '—';
    }

    /**
     * @return array<int, string>
     */
    public function regionKeys(): array
    {
        return array_keys($this->regions());
    }

    /**
     * Reads shipping_zones from Settings and converts each into a
     * ['key' => ['label' => ..., 'cost' => ...]] entry.
     *
     * @return array<string, array{label: string, cost: int}>
     */
    private function customZones(): array
    {
        $zones = setting('shipping_zones', []);

        if (! is_array($zones) || empty($zones)) {
            return [];
        }

        $out = [];
        foreach ($zones as $zone) {
            $name = trim((string) ($zone['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $key = Str::slug($name);
            if ($key === '') {
                $key = 'zone-'.count($out);
            }

            $original = $key;
            $i = 1;
            while (isset($out[$key])) {
                $key = $original.'-'.$i;
                $i++;
            }

            $out[$key] = [
                'label' => $name,
                'cost' => (int) round((float) ($zone['cost'] ?? 0)),
            ];
        }

        return $out;
    }
}
