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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('paymentID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
            $table->foreignId('feeID')->nullable()->constrained('fees', 'feeID')->onDelete('set null');
            $table->string('control_number', 50)->unique();
            $table->decimal('amount_required', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->enum('payment_status', ['Pending', 'Partial', 'Paid', 'Overpaid'])->default('Pending');
            $table->enum('sms_sent', ['Yes', 'No'])->default('No');
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->text('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['studentID']);
            $table->index(['control_number']);
            $table->index(['payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
