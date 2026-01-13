<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates fees_history table to store historical fees data per academic year
     */
    public function up(): void
    {
        Schema::create('fees_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_feeID')->comment('Original feeID from fees table');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('original_classID')->comment('Original classID');
            $table->enum('fee_type', ['Tuition Fees', 'Other Fees']);
            $table->string('fee_name', 200);
            $table->decimal('amount', 10, 2);
            $table->enum('duration', ['Year', 'Month', 'Term', 'Semester', 'One-time']);
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->boolean('has_installments')->default(false);
            $table->integer('total_installments')->default(0)->comment('Total number of installments');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_feeID'], 'fees_history_year_fee_idx');
            $table->index(['academic_yearID', 'original_classID'], 'fees_history_year_class_idx');
            $table->index('academic_yearID', 'fees_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_history');
    }
};

