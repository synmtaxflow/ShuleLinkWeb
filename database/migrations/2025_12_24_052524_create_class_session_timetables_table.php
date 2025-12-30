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
        Schema::create('class_session_timetables', function (Blueprint $table) {
            $table->bigIncrements('session_timetableID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('definitionID');
            $table->foreign('definitionID')->references('definitionID')->on('session_timetable_definitions')->onDelete('cascade');
            $table->foreignId('subclassID')->constrained('subclasses', 'subclassID')->onDelete('cascade');
            $table->foreignId('class_subjectID')->nullable()->constrained('class_subjects', 'class_subjectID')->onDelete('cascade');
            $table->foreignId('subjectID')->nullable()->constrained('school_subjects', 'subjectID')->onDelete('cascade');
            $table->foreignId('teacherID')->constrained('teachers', 'id')->onDelete('cascade');
            $table->foreignId('session_typeID')->constrained('session_types', 'session_typeID')->onDelete('cascade');
            $table->enum('day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])->comment('Day of the week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_prepo')->default(false)->comment('Whether this is a prepo session');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['definitionID', 'subclassID']);
            $table->index(['subclassID', 'day']);
            $table->index(['teacherID', 'day', 'start_time']);
            $table->index('day');

            // Unique constraint: same teacher, same time, same day
            $table->unique(['teacherID', 'day', 'start_time', 'end_time'], 'unique_teacher_day_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_session_timetables');
    }
};
