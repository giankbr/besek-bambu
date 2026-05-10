<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin HTTP wrapper around the RajaOngkir Starter API.
 *
 * The Starter tier exposes:
 *   - GET /province
 *   - GET /city
 *   - POST /cost (JNE, POS, TIKI only)
 *
 * Reference: https://rajaongkir.com/dokumentasi/starter
 */
class RajaOngkirClient
{
    public const TIER_STARTER = 'starter';

    public const COURIER_STARTER = ['jne', 'pos', 'tiki'];

    private string $baseUrl;

    public function __construct(
        private readonly ?string $apiKey = null,
        string $tier = self::TIER_STARTER,
    ) {
        $this->baseUrl = "https://api.rajaongkir.com/{$tier}";
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== null && $this->apiKey !== '';
    }

    /**
     * @return array<int, array{province_id: string, province: string}>
     */
    public function provinces(): array
    {
        return Cache::remember('rajaongkir.provinces', now()->addDay(), function () {
            $response = $this->request('GET', '/province');

            return $this->extractResults($response);
        });
    }

    /**
     * @return array<int, array{city_id: string, province_id: string, province: string, type: string, city_name: string, postal_code: string}>
     */
    public function cities(?string $provinceId = null): array
    {
        $cacheKey = 'rajaongkir.cities.'.($provinceId ?? 'all');

        return Cache::remember($cacheKey, now()->addDay(), function () use ($provinceId) {
            $response = $this->request('GET', '/city', $provinceId ? ['province' => $provinceId] : []);

            return $this->extractResults($response);
        });
    }

    /**
     * Returns shipping cost services from a single courier.
     *
     * @return array<int, array{service: string, description: string, cost: array<int, array{value: int, etd: string, note: string}>}>
     */
    public function cost(string $originCityId, string $destinationCityId, int $weight, string $courier): array
    {
        if ($weight < 1) {
            $weight = 1;
        }

        $cacheKey = sprintf('rajaongkir.cost.%s.%s.%d.%s', $originCityId, $destinationCityId, $weight, $courier);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($originCityId, $destinationCityId, $weight, $courier) {
            $response = $this->request('POST', '/cost', [
                'origin' => $originCityId,
                'destination' => $destinationCityId,
                'weight' => $weight,
                'courier' => $courier,
            ]);

            $results = $this->extractResults($response);

            return $results[0]['costs'] ?? [];
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
            ])->timeout(10)->retry(2, 250);

            $response = $method === 'GET'
                ? $request->get($this->baseUrl.$path, $payload)
                : $request->asForm()->post($this->baseUrl.$path, $payload);

            if (! $response->successful()) {
                Log::warning('RajaOngkir request failed', [
                    'method' => $method,
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException("RajaOngkir HTTP {$response->status()}");
            }

            return $response;
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir transport error', [
                'method' => $method,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('RajaOngkir request failed: '.$e->getMessage(), previous: $e);
        }
    }

    private function extractResults(Response $response): array
    {
        $payload = $response->json();
        $status = $payload['rajaongkir']['status']['code'] ?? null;

        if ($status !== 200) {
            $message = $payload['rajaongkir']['status']['description'] ?? 'Unknown RajaOngkir error';
            throw new \RuntimeException("RajaOngkir error: {$message}");
        }

        $results = $payload['rajaongkir']['results'] ?? [];

        return is_array($results) ? array_values($results) : [];
    }
}
