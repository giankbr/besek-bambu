<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
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
}
