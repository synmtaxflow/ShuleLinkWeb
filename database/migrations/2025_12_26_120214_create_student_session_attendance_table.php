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
        if (Schema::hasTable('student_session_attendance')) {
            return; // Table already exists, skip migration
        }
        
        Schema::create('student_session_attendance', function (Blueprint $table) {
            $table->id('session_attendanceID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('session_timetableID');
            $table->foreign('session_timetableID')->references('session_timetableID')->on('class_session_timetables')->onDelete('cascade');
            $table->unsignedBigInteger('studentID');
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreignId('teacherID')->constrained('teachers', 'id')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['Present', 'Absent', 'Late', 'Excused'])->default('Present');
            $table->text('remark')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['session_timetableID', 'attendance_date']);
            $table->index(['studentID', 'attendance_date']);
            $table->index('attendance_date');
            
            // Unique constraint: one attendance record per student per session per date
            $table->unique(['session_timetableID', 'studentID', 'attendance_date'], 'unique_session_student_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_session_attendance');
    }
};
