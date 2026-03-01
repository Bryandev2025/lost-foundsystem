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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action'); // e.g. "ITEM_CREATED", "CLAIM_APPROVED"
            $table->string('entity_type'); // "item", "claim", "user"
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->json('meta')->nullable(); // store extra details

            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
