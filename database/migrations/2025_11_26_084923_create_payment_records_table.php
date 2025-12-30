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
        Schema::create('payment_records', function (Blueprint $table) {
            $table->id('recordID');
            $table->foreignId('paymentID')->constrained('payments', 'paymentID')->onDelete('cascade');
            $table->decimal('paid_amount', 10, 2);
            $table->string('reference_number', 100)->unique();
            $table->date('payment_date');
            $table->enum('payment_source', ['Cash', 'Bank'])->default('Cash');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['paymentID']);
            $table->index(['reference_number']);
            $table->index(['payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};
