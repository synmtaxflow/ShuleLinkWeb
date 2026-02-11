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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id('expenseID');
            $table->unsignedBigInteger('schoolID')->nullable();
            $table->string('voucher_number');
            $table->date('date');
            $table->string('voucher_type'); // Petty Cash, Payment Voucher
            $table->string('expense_category'); // Academic, Personnel, etc.
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('payment_account')->nullable(); // Cash, Bank Name
            $table->unsignedBigInteger('entered_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
