<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('claimer_id')->constrained('users')->cascadeOnDelete();

            $table->enum('status', ['pending', 'approved', 'denied', 'released'])->default('pending');

            // Proof
            $table->text('proof_details')->nullable();
            $table->string('proof_image_path')->nullable();

            // Staff/Admin approval
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Release tracking
            $table->timestamp('released_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
