<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_subscriber_id')
                ->constrained('newsletter_subscribers')
                ->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['newsletter_subscriber_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_email_logs');
    }
};
