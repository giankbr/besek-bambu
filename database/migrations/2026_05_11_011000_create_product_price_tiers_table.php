<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // When a product has variants, the same tier rule still applies
            // across all of them (kept simple for MVP). Variant-specific
            // pricing can be a future migration.
            $table->unsignedInteger('min_quantity');
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();

            $table->index(['product_id', 'min_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_tiers');
    }
};
