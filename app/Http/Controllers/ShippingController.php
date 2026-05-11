<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\ShippingService;
use App\Services\WilayahService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    public function provinces(WilayahService $wilayah): JsonResponse
    {
        return response()->json(['results' => $wilayah->provinces()]);
    }

    public function regencies(string $provinceCode, WilayahService $wilayah): JsonResponse
    {
        return response()->json(['results' => $wilayah->regencies($provinceCode)]);
    }

    public function districts(string $regencyCode, WilayahService $wilayah): JsonResponse
    {
        return response()->json(['results' => $wilayah->districts($regencyCode)]);
    }

    public function villages(string $districtCode, WilayahService $wilayah): JsonResponse
    {
        return response()->json(['results' => $wilayah->villages($districtCode)]);
    }

    /**
     * Resolve RajaOngkir destination id from selected wilayah values.
     */
    public function resolveDestination(Request $request, ShippingService $shipping): JsonResponse
    {
        $data = $request->validate([
            'province_name' => ['required', 'string', 'max:120'],
            'regency_name' => ['required', 'string', 'max:120'],
            'district_name' => ['required', 'string', 'max:120'],
        ]);

        if (! $shipping->isRajaOngkir()) {
            return response()->json(['destination' => null, 'reason' => 'provider_not_rajaongkir']);
        }

        $client = $shipping->rajaOngkirClient();

        if (! $client->isConfigured()) {
            return response()->json(['destination' => null, 'reason' => 'api_key_missing']);
        }

        $queries = [
            $data['district_name'].' '.$data['regency_name'],
            $data['regency_name'].' '.$data['province_name'],
            $data['regency_name'],
        ];

        try {
            $rows = [];
            foreach ($queries as $query) {
                $rows = $client->searchDestinations($query, 25);
                if ($rows !== []) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir destination resolve failed', [
                'query' => $queries[0] ?? '',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'destination' => null,
                'reason' => 'api_error',
                'message' => $e->getMessage(),
            ]);
        }

        $picked = $this->pickBestDestination(
            $rows,
            $data['province_name'],
            $data['regency_name'],
            $data['district_name'],
        );

        return response()->json(['destination' => $picked]);
    }

    /**
     * Typeahead destination search backed by RajaOngkir V2. Used by
     * both the admin origin picker and the storefront checkout.
     */
    public function searchDestinations(Request $request, ShippingService $shipping): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $client = $shipping->rajaOngkirClient();

        if (! $client->isConfigured()) {
            return response()->json(['results' => [], 'reason' => 'api_key_missing'], 200);
        }

        try {
            $results = $client->searchDestinations(
                $data['q'],
                (int) ($data['limit'] ?? 15),
            );
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir destination search failed', [
                'q' => $data['q'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'results' => [],
                'reason' => 'api_error',
                'message' => $e->getMessage(),
            ], 200);
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Compute live RajaOngkir costs for the current cart from the
     * configured origin to the requested destination.
     */
    public function cost(Request $request, ShippingService $shipping, CartService $cart): JsonResponse
    {
        $data = $request->validate([
            'destination_id' => ['required', 'string', 'max:32'],
            'weight' => ['nullable', 'integer', 'min:1'],
        ]);

        if (! $shipping->isRajaOngkir()) {
            return response()->json(['services' => [], 'reason' => 'provider_not_rajaongkir']);
        }

        $client = $shipping->rajaOngkirClient();

        if (! $client->isConfigured()) {
            return response()->json(['services' => [], 'reason' => 'api_key_missing']);
        }

        $origin = $shipping->originId();

        if (! $origin) {
            return response()->json(['services' => [], 'reason' => 'origin_missing']);
        }

        $couriers = $shipping->enabledCouriers();

        if ($couriers === []) {
            return response()->json(['services' => [], 'reason' => 'no_couriers_enabled']);
        }

        $weight = (int) ($data['weight'] ?? $cart->totalWeight());

        try {
            $services = $client->cost($origin, $data['destination_id'], $weight, $couriers);
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir cost lookup failed', [
                'origin' => $origin,
                'destination' => $data['destination_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'services' => [],
                'reason' => 'api_error',
                'message' => $e->getMessage(),
            ], 200);
        }

        return response()->json([
            'origin_id' => $origin,
            'destination_id' => $data['destination_id'],
            'weight' => $weight,
            'services' => $services,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>|null
     */
    private function pickBestDestination(array $rows, string $province, string $regency, string $district): ?array
    {
        if ($rows === []) {
            return null;
        }

        $provinceNeedle = $this->normalizeWilayahName($province);
        $regencyNeedle = $this->normalizeWilayahName($regency);
        $districtNeedle = $this->normalizeWilayahName($district);

        $score = function (array $row) use ($provinceNeedle, $regencyNeedle, $districtNeedle): int {
            $provinceName = $this->normalizeWilayahName((string) ($row['province_name'] ?? ''));
            $cityName = $this->normalizeWilayahName((string) ($row['city_name'] ?? ''));
            $districtName = $this->normalizeWilayahName((string) ($row['district_name'] ?? ''));
            $subdistrictName = $this->normalizeWilayahName((string) ($row['subdistrict_name'] ?? ''));

            $value = 0;

            if ($provinceNeedle !== '' && str_contains($provinceName, $provinceNeedle)) {
                $value += 3;
            }

            if ($regencyNeedle !== '' && str_contains($cityName, $regencyNeedle)) {
                $value += 6;
            }

            if ($districtNeedle !== '' && (str_contains($districtName, $districtNeedle) || str_contains($subdistrictName, $districtNeedle))) {
                $value += 8;
            }

            return $value;
        };

        usort($rows, fn (array $a, array $b): int => $score($b) <=> $score($a));

        $top = $rows[0] ?? null;

        if (! is_array($top) || ! isset($top['id'])) {
            return null;
        }

        return [
            'id' => (string) $top['id'],
            'label' => (string) ($top['label'] ?? ''),
            'province_name' => (string) ($top['province_name'] ?? ''),
            'city_name' => (string) ($top['city_name'] ?? ''),
            'district_name' => (string) ($top['district_name'] ?? ''),
            'subdistrict_name' => (string) ($top['subdistrict_name'] ?? ''),
        ];
    }

    private function normalizeWilayahName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        $name = str_replace(
            ['kota administrasi', 'kabupaten', 'kota', 'kab.', 'kec.', '.'],
            '',
            $name,
        );

        return trim($name);
    }
}
