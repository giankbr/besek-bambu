<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_region')->nullable()->after('shipping_address');
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('shipping_region');
            $table->decimal('discount', 10, 2)->default(0)->after('shipping_cost');
            $table->string('coupon_code')->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_region', 'shipping_cost', 'discount', 'coupon_code']);
        });
    }
};
