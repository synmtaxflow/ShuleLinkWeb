<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates class_session_timetables_history table to store historical session timetables data per academic year
     */
    public function up(): void
    {
        Schema::create('class_session_timetables_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_session_timetableID')->comment('Original session_timetableID from class_session_timetables table');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('definitionID')->comment('Definition ID');
            $table->unsignedBigInteger('subclassID')->comment('Subclass ID');
            $table->unsignedBigInteger('original_class_subjectID')->nullable();
            $table->unsignedBigInteger('subjectID')->nullable();
            $table->unsignedBigInteger('teacherID')->comment('Teacher ID');
            $table->unsignedBigInteger('session_typeID')->comment('Session type ID');
            $table->enum('day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_prepo')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_session_timetableID'], 'cst_history_year_st_idx');
            $table->index(['academic_yearID', 'subclassID', 'day'], 'cst_history_year_subcls_day_idx');
            $table->index(['academic_yearID', 'teacherID', 'day'], 'cst_history_year_teacher_day_idx');
            $table->index('academic_yearID', 'cst_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_session_timetables_history');
    }
};

