<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'code' => 'WELCOME10',
                'label' => 'Welcome — 10% off',
                'type' => 'percent',
                'value' => 10,
                'min_order' => 100_000,
                'usage_limit' => null,
                'is_active' => true,
            ],
            [
                'code' => 'BAMBU50K',
                'label' => 'Rp 50.000 off your order',
                'type' => 'fixed',
                'value' => 50_000,
                'min_order' => 250_000,
                'usage_limit' => 100,
                'is_active' => true,
            ],
        ];

        foreach ($rows as $row) {
            Coupon::firstOrCreate(['code' => $row['code']], $row);
        }
    }
}
