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
        // Drop table if it exists from previous failed migration
        Schema::dropIfExists('other_staff');
        
        Schema::create('other_staff', function (Blueprint $table) {
            $table->id(); // staffID (same as fingerprint_id - non-auto-increment)
            $table->unsignedBigInteger('schoolID')->nullable(); // link to schools table
            $table->unsignedBigInteger('profession_id')->nullable(); // link to staff_professions table

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
            $table->string('bank_account_number')->nullable(); // New field

            // Employment details
            $table->string('position')->nullable(); // IT, Accountant, HR, etc.
            $table->enum('status', ['Active','On Leave','Retired'])->nullable();
            $table->string('fingerprint_id')->nullable(); // 4-digit unique ID

            $table->timestamps();

            // Foreign keys
            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('set null');
            // Note: profession_id foreign key will be added after staff_professions table is created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_staff');
    }
};
