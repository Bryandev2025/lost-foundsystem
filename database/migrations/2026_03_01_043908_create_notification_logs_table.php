<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('channel', ['email', 'sms']);
            $table->string('purpose'); // e.g. item_matched, claim_approved, pickup_reminder
            $table->string('recipient'); // email or phone
            $table->text('message');

            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['channel', 'status']);
            $table->index(['purpose']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
