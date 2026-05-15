<?php

namespace App\Services;

use Illuminate\Support\Str;

class ShippingService
{
    public const PROVIDER_FLAT = 'flat';

    public const PROVIDER_RAJAONGKIR = 'rajaongkir';

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

    public function provider(): string
    {
        return (string) setting('shipping_provider', self::PROVIDER_FLAT);
    }

    public function isRajaOngkir(): bool
    {
        return $this->provider() === self::PROVIDER_RAJAONGKIR;
    }

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
     * Couriers the admin has enabled, intersected with what the V2
     * RajaOngkir client knows about. Order is preserved by the
     * supported list so the dropdown always renders consistently.
     *
     * @return array<int, string>
     */
    public function enabledCouriers(): array
    {
        $configured = setting('shipping_couriers', []);

        if (! is_array($configured)) {
            return [];
        }

        $configured = array_map('strtolower', $configured);
        $supported = array_keys(RajaOngkirClient::SUPPORTED_COURIERS);

        return array_values(array_intersect($supported, $configured));
    }

    public function originId(): ?string
    {
        $value = setting('shipping_origin_city_id');

        return $value ? (string) $value : null;
    }

    public function originLabel(): ?string
    {
        $value = setting('shipping_origin_label');

        return $value ? (string) $value : null;
    }

    public function rajaOngkirClient(): RajaOngkirClient
    {
        return new RajaOngkirClient((string) setting('shipping_rajaongkir_api_key'));
    }

    public function pickupEnabled(): bool
    {
        return (bool) setting('pickup_enabled', false);
    }

    public function pickupAddress(): string
    {
        return (string) (setting('pickup_address') ?: setting('store_address') ?: '');
    }

    public function pickupNote(): string
    {
        return (string) setting('pickup_note', '');
    }

    /**
     * Re-fetch live RajaOngkir rates and ensure the submitted courier,
     * service, and cost match a real quote. Prevents clients from posting
     * a manipulated shipping_cost.
     *
     * @return array{cost: int, courier: string, service: string, etd: ?string}
     */
    public function verifyRajaOngkirSelection(
        string $destinationId,
        string $courier,
        string $service,
        int $weight,
        int $claimedCost,
    ): array {
        if (! $this->isRajaOngkir()) {
            throw new \DomainException('Live shipping is not enabled.');
        }

        $client = $this->rajaOngkirClient();

        if (! $client->isConfigured()) {
            throw new \DomainException('Shipping API is not configured.');
        }

        $origin = $this->originId();

        if (! $origin) {
            throw new \DomainException('Shipping origin is not configured.');
        }

        $courier = strtolower(trim($courier));
        $service = trim($service);
        $enabled = $this->enabledCouriers();

        if ($courier === '' || $service === '') {
            throw new \DomainException('Please select a shipping service.');
        }

        if (! in_array($courier, $enabled, true)) {
            throw new \DomainException('Selected courier is not available.');
        }

        $weight = max(1, $weight);

        try {
            $quotes = $client->cost($origin, $destinationId, $weight, $enabled);
        } catch (\Throwable $e) {
            throw new \DomainException('Could not verify shipping cost. Please try again.');
        }

        foreach ($quotes as $quote) {
            if (($quote['code'] ?? '') !== $courier) {
                continue;
            }

            if (($quote['service'] ?? '') !== $service) {
                continue;
            }

            $actualCost = (int) ($quote['cost'] ?? 0);

            if ($actualCost !== $claimedCost) {
                throw new \DomainException('Shipping cost has changed. Please refresh and select shipping again.');
            }

            $etd = trim((string) ($quote['etd'] ?? ''));

            return [
                'cost' => $actualCost,
                'courier' => $courier,
                'service' => $service,
                'etd' => $etd !== '' ? $etd : null,
            ];
        }

        throw new \DomainException('Selected shipping service is not available for this destination.');
    }

    /**
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
