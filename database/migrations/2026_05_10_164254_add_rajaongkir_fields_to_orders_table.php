<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_province')->nullable()->after('shipping_address');
            $table->string('shipping_city_id')->nullable()->after('shipping_province');
            $table->string('shipping_city_name')->nullable()->after('shipping_city_id');
            $table->string('shipping_courier')->nullable()->after('shipping_city_name');
            $table->string('shipping_service')->nullable()->after('shipping_courier');
            $table->string('shipping_etd')->nullable()->after('shipping_service');
            $table->unsignedInteger('shipping_weight')->nullable()->after('shipping_etd');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_province',
                'shipping_city_id',
                'shipping_city_name',
                'shipping_courier',
                'shipping_service',
                'shipping_etd',
                'shipping_weight',
            ]);
        });
    }
};
