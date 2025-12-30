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
        Schema::create('fee_installments', function (Blueprint $table) {
            $table->id('installmentID');
            $table->foreignId('feeID')->constrained('fees', 'feeID')->onDelete('cascade');
            $table->string('installment_name', 100); // e.g., "Semester 1", "Month 1", "Term 1"
            $table->enum('installment_type', ['Semester', 'Month', 'Two Months', 'Term', 'Quarter', 'One-time'])->default('Month');
            $table->integer('installment_number'); // e.g., 1, 2, 3 for Semester 1, 2, 3
            $table->decimal('amount', 10, 2); // Amount for this installment
            $table->date('due_date')->nullable(); // Optional due date
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
            
            // Index for faster queries
            $table->index('feeID');
            $table->index(['feeID', 'installment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_installments');
    }
};
