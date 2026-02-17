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
        Schema::create('daily_duty_reports', function (Blueprint $table) {
            $table->id('reportID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('teacherID');
            $table->date('report_date');
            
            // Grid data for class-wise attendance
            $table->longText('attendance_data')->nullable(); // JSON object
            
            // Specific report fields from the image
            $table->decimal('attendance_percentage', 5, 2)->nullable();
            $table->text('school_environment')->nullable();
            $table->text('pupils_cleanliness')->nullable();
            $table->text('teachers_attendance')->nullable(); // JSON or text
            $table->text('timetable_status')->nullable();
            $table->text('outside_activities')->nullable();
            $table->text('special_events')->nullable();
            $table->text('teacher_comments')->nullable();
            
            // Admin feedback
            $table->text('admin_comments')->nullable();
            $table->string('status')->default('Draft'); // Draft, Sent
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_duty_reports');
    }
};
