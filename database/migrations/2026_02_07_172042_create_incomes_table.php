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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id('incomeID');
            $table->unsignedBigInteger('schoolID')->nullable();
            $table->string('receipt_number');
            $table->date('date');
            $table->string('income_category'); // Tuition, Boarding, etc.
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method'); // Cash, Bank, Mobile Money
            $table->string('payment_account')->nullable(); // Specific bank account or cash account
            $table->string('payer_name')->nullable(); // Student Name or Organization
            $table->unsignedBigInteger('entered_by');
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
