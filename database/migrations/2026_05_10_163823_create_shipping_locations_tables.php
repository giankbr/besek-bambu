<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_provinces', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('shipping_cities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('province_id');
            $table->string('province_name')->nullable();
            $table->string('type')->nullable();
            $table->string('name');
            $table->string('postal_code')->nullable();
            $table->timestamps();

            $table->index('province_id');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_cities');
        Schema::dropIfExists('shipping_provinces');
    }
};
