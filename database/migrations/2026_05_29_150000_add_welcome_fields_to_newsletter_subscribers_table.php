<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('locale')->constrained()->nullOnDelete();
            $table->timestamp('welcome_sent_at')->nullable()->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('welcome_sent_at');
        });
    }
};
