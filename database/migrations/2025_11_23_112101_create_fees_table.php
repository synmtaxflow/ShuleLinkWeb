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
        Schema::create('fees', function (Blueprint $table) {
            $table->id('feeID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('classID')->constrained('classes', 'classID')->onDelete('cascade');
            $table->enum('fee_type', ['Tuition Fees', 'Other Fees']);
            $table->string('fee_name', 200);
            $table->decimal('amount', 10, 2);
            $table->enum('duration', ['Year', 'Month', 'Term', 'Semester', 'One-time']);
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
