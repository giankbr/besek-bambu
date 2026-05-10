<?php

namespace App\Http\Controllers;

use App\Models\ShippingCity;
use App\Models\ShippingProvince;
use App\Services\CartService;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    /**
     * Public province list for the checkout cascade.
     */
    public function provinces(): JsonResponse
    {
        return response()->json(
            ShippingProvince::orderBy('name')->get(['id', 'name'])
        );
    }

    public function cities(string $provinceId): JsonResponse
    {
        $cities = ShippingCity::where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return response()->json($cities);
    }

    /**
     * Compute live RajaOngkir costs for the current cart from the
     * configured origin to the requested destination city. Returns the
     * full set of services across the enabled couriers.
     */
    public function cost(Request $request, ShippingService $shipping, CartService $cart): JsonResponse
    {
        $data = $request->validate([
            'destination_city_id' => ['required', 'string', 'max:32'],
        ]);

        if (! $shipping->isRajaOngkir()) {
            return response()->json(['services' => [], 'reason' => 'provider_not_rajaongkir']);
        }

        $client = $shipping->rajaOngkirClient();

        if (! $client->isConfigured()) {
            return response()->json(['services' => [], 'reason' => 'api_key_missing']);
        }

        $origin = $shipping->originCityId();

        if (! $origin) {
            return response()->json(['services' => [], 'reason' => 'origin_missing']);
        }

        $weight = $cart->totalWeight();
        $services = [];

        foreach ($shipping->enabledCouriers() as $courier) {
            try {
                $rows = $client->cost($origin, $data['destination_city_id'], $weight, $courier);

                foreach ($rows as $row) {
                    $cost = $row['cost'][0] ?? null;
                    if (! $cost) {
                        continue;
                    }
                    $services[] = [
                        'courier' => $courier,
                        'service' => (string) ($row['service'] ?? ''),
                        'description' => (string) ($row['description'] ?? ''),
                        'cost' => (int) ($cost['value'] ?? 0),
                        'etd' => trim((string) ($cost['etd'] ?? '')),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('RajaOngkir cost lookup failed', [
                    'courier' => $courier,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'origin_city_id' => $origin,
            'destination_city_id' => $data['destination_city_id'],
            'weight' => $weight,
            'services' => $services,
        ]);
    }
}
