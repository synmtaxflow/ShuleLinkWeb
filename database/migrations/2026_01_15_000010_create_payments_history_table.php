<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates payments_history table to store historical payments data per academic year
     */
    public function up(): void
    {
        Schema::create('payments_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_paymentID')->comment('Original paymentID from payments table');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('studentID')->comment('Student ID');
            $table->unsignedBigInteger('original_feeID')->nullable();
            $table->string('control_number', 50);
            $table->decimal('amount_required', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->enum('payment_status', ['Pending', 'Partial', 'Incomplete Payment', 'Paid', 'Overpaid'])->default('Pending');
            $table->enum('fee_type', ['Tuition Fees', 'Other Fees'])->nullable();
            $table->enum('sms_sent', ['Yes', 'No'])->default('No');
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->text('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_paymentID'], 'pay_history_year_payment_idx');
            $table->index(['academic_yearID', 'studentID'], 'pay_history_year_student_idx');
            $table->index('academic_yearID', 'pay_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_history');
    }
};

