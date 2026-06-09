<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransChannelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_logos_use_default_channels_when_setting_missing(): void
    {
        $logos = midtrans_payment_logos();

        $this->assertNotSame([], $logos);
        $this->assertContains(['file' => 'bni.png', 'alt' => 'BNI'], $logos);
        $this->assertNotContains(['file' => 'visa.png', 'alt' => 'Visa'], $logos);
    }

    public function test_payment_logos_follow_admin_setting(): void
    {
        Setting::put('payment_midtrans_display_channels', ['credit_card', 'dana']);

        $logos = midtrans_payment_logos();

        $this->assertContains(['file' => 'visa.png', 'alt' => 'Visa'], $logos);
        $this->assertContains(['file' => 'dana.png', 'alt' => 'DANA'], $logos);
        $this->assertNotContains(['file' => 'bni.png', 'alt' => 'BNI'], $logos);
    }

    public function test_display_channel_keys_can_be_cleared(): void
    {
        Setting::put('payment_midtrans_display_channels', []);

        $this->assertSame([], midtrans_display_channel_keys());
        $this->assertSame([], midtrans_payment_logos());
    }
}
