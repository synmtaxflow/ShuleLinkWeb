<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates exam_timetables_history table to store historical exam timetables data per academic year
     */
    public function up(): void
    {
        Schema::create('exam_timetables_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_exam_timetableID')->comment('Original exam_timetableID from exam_timetables table');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('original_examID')->comment('Original examID');
            $table->unsignedBigInteger('subclassID')->comment('Subclass ID');
            $table->unsignedBigInteger('original_class_subjectID')->nullable();
            $table->unsignedBigInteger('subjectID')->nullable();
            $table->unsignedBigInteger('teacherID')->comment('Teacher ID');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('timetable_type', ['class_specific', 'school_wide'])->default('class_specific');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_exam_timetableID'], 'ett_history_year_ett_idx');
            $table->index(['academic_yearID', 'original_examID', 'exam_date'], 'ett_history_year_exam_date_idx');
            $table->index(['academic_yearID', 'subclassID', 'exam_date'], 'ett_history_year_subcls_date_idx');
            $table->index('academic_yearID', 'ett_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_timetables_history');
    }
};

