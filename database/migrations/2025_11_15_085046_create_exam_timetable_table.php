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
        Schema::create('exam_timetable', function (Blueprint $table) {
            $table->id('exam_timetableID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
            $table->date('exam_date');
            $table->string('day', 20); // Monday, Tuesday, etc.
            $table->foreignId('subjectID')->constrained('school_subjects', 'subjectID')->onDelete('cascade');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['examID', 'exam_date']);
            $table->index(['exam_date', 'day']);
            $table->index(['subjectID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_timetable');
    }
};
