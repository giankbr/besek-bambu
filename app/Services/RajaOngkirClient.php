<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the RajaOngkir / Komerce V2 API.
 *
 * Base URL: https://rajaongkir.komerce.id/api/v1
 *
 * The legacy api.rajaongkir.com endpoints were retired when RajaOngkir
 * migrated to Komerce. We use the typeahead-style "Search Destination"
 * endpoint instead of caching province/city/district hierarchies
 * locally, which keeps the data model lightweight.
 *
 * Reference: https://rajaongkir.com/docs
 */
class RajaOngkirClient
{
    /**
     * Couriers commonly available on RajaOngkir V2. The API will
     * silently skip any courier without rates for the given lane, so
     * exposing a wider list does not break anything.
     */
    public const SUPPORTED_COURIERS = [
        'jne' => 'JNE',
        'jnt' => 'J&T Express',
        'sicepat' => 'SiCepat Express',
        'anteraja' => 'AnterAja',
        'pos' => 'POS Indonesia',
        'tiki' => 'TIKI',
        'sap' => 'SAP Express',
        'ide' => 'ID Express',
        'ninja' => 'Ninja Xpress',
        'lion' => 'Lion Parcel',
        'wahana' => 'Wahana',
        'rpx' => 'RPX',
        'ncs' => 'NCS',
        'sentral' => 'Sentral Cargo',
        'star' => 'Star Cargo',
        'rex' => 'REX Kiriman Express',
        'dse' => '21 Express',
    ];

    private string $baseUrl = 'https://rajaongkir.komerce.id/api/v1';

    public function __construct(private readonly ?string $apiKey = null) {}

    public function isConfigured(): bool
    {
        return $this->apiKey !== null && $this->apiKey !== '';
    }

    /**
     * Typeahead search across all Indonesian destinations. Each match
     * is unique enough (district + city + province) to be picked from
     * a dropdown without further drilling.
     *
     * @return array<int, array{id: int, label: string, province_name: string, city_name: string, district_name: string, subdistrict_name: string, zip_code: string}>
     */
    public function searchDestinations(string $keyword, int $limit = 20, int $offset = 0): array
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return [];
        }

        $cacheKey = sprintf('rajaongkir.v2.search.%s.%d.%d', md5(strtolower($keyword)), $limit, $offset);

        return Cache::remember($cacheKey, now()->addHour(), function () use ($keyword, $limit, $offset) {
            $response = $this->request('GET', '/destination/domestic-destination', [
                'search' => $keyword,
                'limit' => $limit,
                'offset' => $offset,
            ]);

            return $this->extractData($response);
        });
    }

    /**
     * Calculate live shipping costs for the given lane and weight.
     *
     * @param  array<int, string>  $couriers  e.g. ['jne', 'pos', 'tiki']
     * @return array<int, array{name: string, code: string, service: string, description: string, cost: int, etd: string}>
     */
    public function cost(int|string $originId, int|string $destinationId, int $weight, array $couriers, string $price = 'lowest'): array
    {
        if ($weight < 1) {
            $weight = 1;
        }

        $couriers = array_values(array_unique(array_filter(array_map('strtolower', $couriers))));

        if ($couriers === []) {
            return [];
        }

        $courierString = implode(':', $couriers);
        $cacheKey = sprintf('rajaongkir.v2.cost.%s.%s.%d.%s.%s', $originId, $destinationId, $weight, $courierString, $price);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($originId, $destinationId, $weight, $courierString, $price) {
            $response = $this->request('POST', '/calculate/domestic-cost', [
                'origin' => (string) $originId,
                'destination' => (string) $destinationId,
                'weight' => $weight,
                'courier' => $courierString,
                'price' => $price,
            ]);

            $rows = $this->extractData($response);

            return array_values(array_filter(array_map(function (array $row): ?array {
                if (! isset($row['cost'])) {
                    return null;
                }

                return [
                    'name' => (string) ($row['name'] ?? ''),
                    'code' => strtolower((string) ($row['code'] ?? '')),
                    'service' => (string) ($row['service'] ?? ''),
                    'description' => (string) ($row['description'] ?? ''),
                    'cost' => (int) $row['cost'],
                    'etd' => trim((string) ($row['etd'] ?? '')),
                ];
            }, $rows)));
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function request(string $method, string $path, array $payload = []): Response
    {
        if (! $this->isConfigured()) {
            throw new \DomainException('RajaOngkir API key is not configured.');
        }

        try {
            $request = Http::withHeaders([
                'key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(15)->retry(2, 250, throw: false);

            $response = $method === 'GET'
                ? $request->get($this->baseUrl.$path, $payload)
                : $request->asForm()->post($this->baseUrl.$path, $payload);

            if (! $response->successful()) {
                $body = $response->body();
                Log::warning('RajaOngkir V2 request failed', [
                    'method' => $method,
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => mb_substr($body, 0, 500),
                ]);
                $message = $response->json('meta.message') ?? "HTTP {$response->status()}";
                throw new \RuntimeException("RajaOngkir error: {$message}");
            }

            return $response;
        } catch (\DomainException|\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir V2 transport error', [
                'method' => $method,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('RajaOngkir request failed: '.$e->getMessage(), previous: $e);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractData(Response $response): array
    {
        $payload = $response->json();
        $code = $payload['meta']['code'] ?? null;

        if ($code !== null && (int) $code !== 200) {
            $message = $payload['meta']['message'] ?? 'Unknown RajaOngkir error';
            throw new \RuntimeException("RajaOngkir error: {$message}");
        }

        $data = $payload['data'] ?? [];

        return is_array($data) ? array_values($data) : [];
    }
}
