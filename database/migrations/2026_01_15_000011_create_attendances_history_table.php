<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates attendances_history table to store historical attendance data per academic year
     */
    public function up(): void
    {
        Schema::create('attendances_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_attendanceID')->comment('Original attendanceID from attendances table');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('subclassID')->comment('Subclass ID');
            $table->unsignedBigInteger('studentID')->comment('Student ID');
            $table->unsignedBigInteger('teacherID')->nullable();
            $table->date('attendance_date');
            $table->enum('status', ['Present', 'Absent', 'Late', 'Excused'])->default('Present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_attendanceID'], 'att_history_year_att_idx');
            $table->index(['academic_yearID', 'studentID', 'attendance_date'], 'att_history_year_student_date_idx');
            $table->index(['academic_yearID', 'subclassID', 'attendance_date'], 'att_history_year_subcls_date_idx');
            $table->index('academic_yearID', 'att_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances_history');
    }
};

