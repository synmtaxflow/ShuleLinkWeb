<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates table to store fingerprint attendance data from external API
     * Each student can have only one record per day
     */
    public function up(): void
    {
        Schema::create('student_fingerprint_attendance', function (Blueprint $table) {
            $table->id('id');
            $table->bigInteger('studentID')->unsigned()->comment('Fingerprint ID (studentID = fingerprintID)');
            $table->string('user_id', 50)->nullable()->comment('User ID from external API');
            $table->string('user_name', 255)->nullable()->comment('User name from external API');
            $table->string('enroll_id', 50)->nullable()->comment('Enroll ID (Fingerprint ID) from external API');
            $table->date('attendance_date');
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->string('status', 50)->nullable()->comment('Status from external API (1 = Present, etc.)');
            $table->string('verify_mode', 50)->nullable()->comment('Verify mode (Fingerprint, etc.)');
            $table->string('device_ip', 50)->nullable()->comment('Device IP address');
            $table->bigInteger('external_id')->nullable()->comment('ID from external API');
            $table->timestamps();

            // Unique constraint: one record per student per day
            $table->unique(['studentID', 'attendance_date'], 'unique_student_date');
            
            // Index for faster queries
            $table->index('studentID');
            $table->index('attendance_date');
            $table->index(['studentID', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fingerprint_attendance');
    }
};
