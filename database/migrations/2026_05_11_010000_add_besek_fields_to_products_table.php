<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Pengrajin besek umumnya menjual minimum per pak / lusin,
            // bukan satuan. Default 1 untuk produk lain.
            $table->unsignedInteger('min_order_quantity')->default(1)->after('stock');
            // Anyaman handmade butuh waktu produksi untuk bulk order.
            $table->unsignedSmallInteger('production_lead_days')->default(0)->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['min_order_quantity', 'production_lead_days']);
        });
    }
};
