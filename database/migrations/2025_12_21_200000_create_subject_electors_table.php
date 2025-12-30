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
        Schema::create('subject_electors', function (Blueprint $table) {
            $table->id('electorID');
            $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
            $table->foreignId('classSubjectID')->constrained('class_subjects', 'class_subjectID')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint: a student can only elect a subject once
            $table->unique(['studentID', 'classSubjectID'], 'unique_student_class_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_electors');
    }
};





