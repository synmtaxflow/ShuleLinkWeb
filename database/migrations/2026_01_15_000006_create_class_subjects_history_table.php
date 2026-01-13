<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates class_subjects_history table to store historical class subjects data per academic year
     */
    public function up(): void
    {
        Schema::create('class_subjects_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_class_subjectID')->comment('Original class_subjectID from class_subjects table');
            $table->unsignedBigInteger('original_classID')->comment('Original classID');
            $table->unsignedBigInteger('subjectID')->comment('Subject ID');
            $table->unsignedBigInteger('teacherID')->nullable()->comment('Subject teacher ID');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('student_status', ['Compulsory', 'Optional'])->nullable()->comment('Student status for this subject');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_class_subjectID'], 'cs_history_year_subject_idx');
            $table->index(['academic_yearID', 'original_classID'], 'cs_history_year_class_idx');
            $table->index('academic_yearID', 'cs_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subjects_history');
    }
};

