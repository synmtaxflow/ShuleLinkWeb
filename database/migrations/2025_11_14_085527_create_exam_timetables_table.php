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
        Schema::create('exam_timetables', function (Blueprint $table) {
            $table->id('exam_timetableID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
            $table->foreignId('subclassID')->constrained('subclasses', 'subclassID')->onDelete('cascade');
            $table->foreignId('class_subjectID')->nullable()->constrained('class_subjects', 'class_subjectID')->onDelete('cascade');
            $table->foreignId('subjectID')->nullable()->constrained('school_subjects', 'subjectID')->onDelete('cascade');
            $table->foreignId('teacherID')->constrained('teachers', 'id')->onDelete('cascade');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('timetable_type', ['class_specific', 'school_wide'])->default('class_specific')->comment('class_specific: for specific class, school_wide: for entire school');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['examID', 'exam_date']);
            $table->index(['subclassID', 'exam_date']);
            $table->index(['teacherID', 'exam_date']);
            $table->index(['exam_date', 'start_time', 'end_time']);
            $table->index('timetable_type');

            // Unique constraint to prevent conflicts: same teacher, same time, same date
            $table->unique(['teacherID', 'exam_date', 'start_time', 'end_time'], 'unique_teacher_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_timetables');
    }
};
