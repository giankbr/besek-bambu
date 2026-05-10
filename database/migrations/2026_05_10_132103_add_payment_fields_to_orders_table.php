<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status');
            $table->string('payment_status')->default('unpaid')->after('payment_method');
            $table->string('payment_token')->nullable()->after('payment_status');
            $table->string('payment_url')->nullable()->after('payment_token');
            $table->timestamp('paid_at')->nullable()->after('payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'payment_token', 'payment_url', 'paid_at']);
        });
    }
};
