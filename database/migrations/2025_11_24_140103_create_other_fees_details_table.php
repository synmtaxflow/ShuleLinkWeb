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
        Schema::create('other_fees_details', function (Blueprint $table) {
            $table->id('detailID');
            $table->foreignId('feeID')->constrained('fees', 'feeID')->onDelete('cascade');
            $table->string('fee_detail_name', 200); // e.g., "Food", "Study Tour", "Library Fee"
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
            
            // Index for faster queries
            $table->index('feeID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_fees_details');
    }
};
