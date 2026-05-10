<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('items');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('recovery_sent_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('last_seen_at');
            $table->index('recovery_sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_snapshots');
    }
};
