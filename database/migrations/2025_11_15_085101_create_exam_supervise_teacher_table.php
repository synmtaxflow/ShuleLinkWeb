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
        if (!Schema::hasTable('exam_supervise_teacher')) {
            Schema::create('exam_supervise_teacher', function (Blueprint $table) {
                $table->id('exam_supervise_teacherID');
                $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
                $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
                $table->foreignId('exam_timetableID')->constrained('exam_timetable', 'exam_timetableID')->onDelete('cascade');
                $table->foreignId('subclassID')->constrained('subclasses', 'subclassID')->onDelete('cascade');
                $table->foreignId('teacherID')->constrained('teachers', 'id')->onDelete('cascade');
                $table->timestamps();

                // Indexes for better query performance
                $table->index(['examID']);
                $table->index(['exam_timetableID']);
                $table->index(['subclassID']);
                $table->index(['teacherID']);
                
                // Unique constraint: one teacher per class per exam timetable entry
                $table->unique(['exam_timetableID', 'subclassID', 'teacherID'], 'unique_supervisor_per_class');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_supervise_teacher');
    }
};
