<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates lesson_plans_history table to store historical lesson plans data per academic year
     */
    public function up(): void
    {
        Schema::create('lesson_plans_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_lesson_planID')->comment('Original lesson_planID from lesson_plans table');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('session_timetableID')->comment('Session timetable ID');
            $table->unsignedBigInteger('teacherID')->comment('Teacher ID');
            $table->date('lesson_date');
            $table->time('lesson_time_start');
            $table->time('lesson_time_end');
            $table->string('subject')->nullable();
            $table->string('class_name')->nullable();
            $table->integer('year')->comment('Year from original lesson plan');
            
            // Attendance Statistics
            $table->integer('registered_girls')->default(0);
            $table->integer('registered_boys')->default(0);
            $table->integer('registered_total')->default(0);
            $table->integer('present_girls')->default(0);
            $table->integer('present_boys')->default(0);
            $table->integer('present_total')->default(0);
            
            // Competence and Activities
            $table->text('main_competence')->nullable();
            $table->text('specific_competence')->nullable();
            $table->text('main_activity')->nullable();
            $table->text('specific_activity')->nullable();
            
            // Resources and References
            $table->text('teaching_learning_resources')->nullable();
            $table->text('references')->nullable();
            
            // Lesson Development Stages (stored as JSON)
            $table->json('lesson_stages')->nullable();
            
            // Remarks and Reflection
            $table->text('remarks')->nullable();
            $table->text('reflection')->nullable();
            $table->text('evaluation')->nullable();
            $table->string('reflection_signature')->nullable();
            $table->string('evaluation_signature')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'completed', 'archived'])->default('draft');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_lesson_planID'], 'lp_history_year_lp_idx');
            $table->index(['academic_yearID', 'teacherID', 'lesson_date'], 'lp_history_year_teacher_date_idx');
            $table->index('academic_yearID', 'lp_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_plans_history');
    }
};

