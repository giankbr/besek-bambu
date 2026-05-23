<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappDigitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_falls_back_to_env_when_settings_and_social_link_are_empty(): void
    {
        config(['store.whatsapp_number' => '6281234567890']);

        $this->assertSame('6281234567890', whatsapp_digits());
    }

    public function test_prefers_whatsapp_order_number_setting_over_env(): void
    {
        config(['store.whatsapp_number' => '6281111111111']);
        Setting::put('whatsapp_order_number', '6289999999999');

        $this->assertSame('6289999999999', whatsapp_digits());
    }
}
