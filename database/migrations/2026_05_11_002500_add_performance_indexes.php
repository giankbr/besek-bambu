<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Storefront filters always combine these.
            $table->index(['is_active', 'sort_order'], 'products_active_sort_idx');
            $table->index(['is_active', 'category_id'], 'products_active_category_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Admin orders index relies on these filters and sorts.
            $table->index('status', 'orders_status_idx');
            $table->index('payment_status', 'orders_payment_status_idx');
            $table->index('created_at', 'orders_created_at_idx');
            $table->index(['user_id', 'created_at'], 'orders_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_sort_idx');
            $table->dropIndex('products_active_category_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_idx');
            $table->dropIndex('orders_payment_status_idx');
            $table->dropIndex('orders_created_at_idx');
            $table->dropIndex('orders_user_created_idx');
        });
    }
};
