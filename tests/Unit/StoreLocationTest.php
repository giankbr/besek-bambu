<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_area_is_parsed_from_store_address(): void
    {
        Setting::put('store_address', 'Kebanaran, Mandiraja, Banjarnegara, Jawa Tengah 53473');

        $this->assertSame('Mandiraja, Banjarnegara, Jawa Tengah', store_location_area());
    }

    public function test_location_area_uses_custom_label_when_set(): void
    {
        Setting::put('store_address', 'Kebanaran, Mandiraja, Banjarnegara, Jawa Tengah 53473');
        Setting::put('store_location_label', 'Banjarnegara, Jawa Tengah');

        $this->assertSame('Banjarnegara, Jawa Tengah', store_location_area());
    }

    public function test_postal_address_schema_splits_address_fields(): void
    {
        Setting::put('store_address', 'Kebanaran, Mandiraja, Banjarnegara, Jawa Tengah 53473');

        $schema = store_postal_address_schema();

        $this->assertSame('ID', $schema['addressCountry']);
        $this->assertSame('53473', $schema['postalCode']);
        $this->assertSame('Jawa Tengah', $schema['addressRegion']);
        $this->assertSame('Banjarnegara', $schema['addressLocality']);
        $this->assertSame('Kebanaran, Mandiraja', $schema['streetAddress']);
    }
}
