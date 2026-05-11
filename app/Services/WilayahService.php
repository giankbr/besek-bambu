<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WilayahService
{
    private const BASE_URL = 'https://wilayah.id/api';

    public function provinces(): array
    {
        return $this->getCached('provinces', '/provinces.json');
    }

    public function regencies(string $provinceCode): array
    {
        return $this->getCached(
            sprintf('regencies.%s', $provinceCode),
            sprintf('/regencies/%s.json', $provinceCode),
        );
    }

    public function districts(string $regencyCode): array
    {
        return $this->getCached(
            sprintf('districts.%s', $regencyCode),
            sprintf('/districts/%s.json', $regencyCode),
        );
    }

    public function villages(string $districtCode): array
    {
        return $this->getCached(
            sprintf('villages.%s', $districtCode),
            sprintf('/villages/%s.json', $districtCode),
        );
    }

    private function getCached(string $key, string $path): array
    {
        return Cache::remember(
            'wilayah.id.'.$key,
            now()->addDays(30),
            function () use ($path): array {
                $response = Http::acceptJson()
                    ->timeout(20)
                    ->retry(2, 250, throw: false)
                    ->get(self::BASE_URL.$path);

                if (! $response->successful()) {
                    return [];
                }

                $data = $response->json('data');

                if (! is_array($data)) {
                    return [];
                }

                return array_values(array_map(function ($row): array {
                    return [
                        'code' => (string) ($row['code'] ?? ''),
                        'name' => (string) ($row['name'] ?? ''),
                    ];
                }, $data));
            }
        );
    }
}
