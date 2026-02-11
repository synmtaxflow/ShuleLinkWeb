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
        Schema::create('student_fee_payments', function (Blueprint $table) {
            $table->id('payment_detail_id');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
            $table->foreignId('feeID')->constrained('fees', 'feeID')->onDelete('cascade');
            
            // Fee details (denormalized for historical tracking)
            $table->string('fee_name', 200);
            $table->decimal('fee_total_amount', 10, 2); // Total amount for this specific fee
            $table->decimal('amount_paid', 10, 2)->default(0); // Amount paid towards this fee
            $table->decimal('balance', 10, 2)->default(0); // Remaining for this fee
            
            // Priority tracking
            $table->boolean('is_required')->default(false); // Copy of must_start_pay
            $table->integer('display_order')->default(0); // For sorting
            
            // Tracking
            $table->timestamp('last_payment_date')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['studentID', 'feeID']);
            $table->index(['studentID', 'is_required']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fee_payments');
    }
};
