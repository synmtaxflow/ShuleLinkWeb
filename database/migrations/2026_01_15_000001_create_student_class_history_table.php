<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates student_class_history table to track student class assignments per academic year
     */
    public function up(): void
    {
        Schema::create('student_class_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->foreignId('classID')->constrained('classes', 'classID')->onDelete('cascade');
            $table->foreignId('subclassID')->constrained('subclasses', 'subclassID')->onDelete('cascade');
            $table->enum('student_status', ['Active', 'Transferred', 'Graduated', 'Inactive'])->default('Active');
            $table->date('joined_date')->nullable();
            $table->date('left_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['studentID', 'academic_yearID']);
            $table->index(['academic_yearID', 'classID']);
            $table->index(['academic_yearID', 'subclassID']);
            $table->index('student_status');
            
            // Unique constraint: one record per student per academic year
            $table->unique(['studentID', 'academic_yearID'], 'unique_student_academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_history');
    }
};


