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
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['lost', 'found']);
            $table->string('title'); // e.g. "Black Wallet"
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // gadget, id, bag, etc.
            $table->string('brand')->nullable();
            $table->string('color')->nullable();

            $table->string('location')->nullable(); // where lost/found
            $table->date('date_incident')->nullable();

            $table->enum('status', ['pending', 'matched', 'claimed', 'archived'])->default('pending');

            // Image handling (simple: 1 main image)
            $table->string('image_path')->nullable();

            // QR support (backend)
            $table->string('qr_token')->unique()->nullable();
            $table->timestamp('qr_generated_at')->nullable();

            // Ownership / reporter
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();

            // If found item reported by someone else
            $table->foreignId('found_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
