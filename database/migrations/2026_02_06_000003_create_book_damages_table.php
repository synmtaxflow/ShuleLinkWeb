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
        Schema::create('book_damages', function (Blueprint $table) {
            $table->id('damageID');
            $table->foreignId('bookID')->constrained('books', 'bookID')->onDelete('cascade');
            $table->foreignId('studentID')->nullable()->constrained('students', 'studentID')->onDelete('set null');
            $table->enum('damaged_by', ['student', 'other'])->default('student');
            $table->text('description')->nullable();
            $table->enum('status', ['damaged', 'repaired'])->default('damaged');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->enum('payment_method', ['replace', 'cash'])->nullable();
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->date('reported_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_damages');
    }
};
