<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->string('image_url')->nullable()->after('icon');
            $table->unsignedInteger('stock')->default(0)->after('price');
            $table->boolean('is_active')->default(true)->after('stock');
            $table->foreignId('category_id')->nullable()->after('is_active')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['slug', 'description', 'image_url', 'stock', 'is_active', 'category_id']);
        });
    }
};
