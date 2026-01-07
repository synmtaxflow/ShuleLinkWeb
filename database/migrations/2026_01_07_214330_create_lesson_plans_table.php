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
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id('lesson_planID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('session_timetableID');
            $table->foreign('session_timetableID')->references('session_timetableID')->on('class_session_timetables')->onDelete('cascade');
            $table->foreignId('teacherID')->constrained('teachers', 'id')->onDelete('cascade');
            $table->date('lesson_date');
            $table->time('lesson_time_start');
            $table->time('lesson_time_end');
            $table->string('subject')->nullable();
            $table->string('class_name')->nullable();
            $table->integer('year');
            
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
            $table->json('lesson_stages')->nullable()->comment('Stages: Introduction, Competence development, Design, Realization with time, activities, and assessment');
            
            // Remarks
            $table->text('remarks')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'completed', 'archived'])->default('draft');
            
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['session_timetableID', 'lesson_date']);
            $table->index(['teacherID', 'lesson_date']);
            $table->index('lesson_date');
            $table->index('year');
            
            // Unique constraint: one lesson plan per session per date
            $table->unique(['session_timetableID', 'lesson_date'], 'unique_session_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};
