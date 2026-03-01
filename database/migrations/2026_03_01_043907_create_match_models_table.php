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
        Schema::create('match_models', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lost_item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('found_item_id')->constrained('items')->cascadeOnDelete();

            $table->foreignId('matched_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('matched_at')->useCurrent();
            $table->text('notes')->nullable();

            $table->timestamps();

            // prevent duplicate pairings
            $table->unique(['lost_item_id', 'found_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_models');
    }
};
