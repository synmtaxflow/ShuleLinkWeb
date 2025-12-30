<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id(); // teacherID
            $table->unsignedBigInteger('schoolID')->nullable(); // link to schools table

            // Personal info
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('image')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('national_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->string('experience')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('date_hired')->nullable();

            // Contact info
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();

            // Employment details
            $table->string('position')->nullable(); // Teacher, Headmaster, Academic, Librarian
            $table->enum('status', ['Active','On Leave','Retired'])->nullable();

            $table->timestamps();

            // Foreign key only to schools
            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
