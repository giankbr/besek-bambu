<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('weight')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
            $table->index(['product_id', 'is_default']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->nullOnDelete();
            $table->string('variant_label')->nullable()->after('product_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn('variant_label');
        });
        Schema::dropIfExists('product_variants');
    }
};
