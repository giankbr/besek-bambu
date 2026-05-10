<?php

namespace App\Console\Commands\Shipping;

use App\Models\ShippingCity;
use App\Models\ShippingProvince;
use App\Services\RajaOngkirClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncRajaOngkirCommand extends Command
{
    protected $signature = 'shipping:sync-rajaongkir {--key= : Override the API key from settings}';

    protected $description = 'Sync RajaOngkir provinces and cities into the local database';

    public function handle(): int
    {
        $apiKey = $this->option('key') ?: (string) setting('shipping_rajaongkir_api_key');

        if (! $apiKey) {
            $this->error('No RajaOngkir API key configured. Set it in admin → settings → shipping or pass --key=...');

            return self::FAILURE;
        }

        Cache::forget('rajaongkir.provinces');
        Cache::forget('rajaongkir.cities.all');

        $client = new RajaOngkirClient($apiKey);

        $this->info('Fetching provinces…');
        $provinces = collect($client->provinces());

        if ($provinces->isEmpty()) {
            $this->error('No provinces returned. Aborting.');

            return self::FAILURE;
        }

        $provinces->each(function (array $row) {
            ShippingProvince::updateOrCreate(
                ['id' => (string) $row['province_id']],
                ['name' => (string) $row['province']],
            );
        });
        $this->info("Synced {$provinces->count()} provinces.");

        $this->info('Fetching cities…');
        $cities = collect($client->cities());
        $cities->each(function (array $row) {
            ShippingCity::updateOrCreate(
                ['id' => (string) $row['city_id']],
                [
                    'province_id' => (string) $row['province_id'],
                    'province_name' => (string) ($row['province'] ?? ''),
                    'type' => (string) ($row['type'] ?? ''),
                    'name' => (string) ($row['city_name'] ?? ''),
                    'postal_code' => (string) ($row['postal_code'] ?? ''),
                ],
            );
        });
        $this->info("Synced {$cities->count()} cities.");

        return self::SUCCESS;
    }
}
