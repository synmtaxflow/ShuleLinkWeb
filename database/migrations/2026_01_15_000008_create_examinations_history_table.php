<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates examinations_history table to store historical examinations data per academic year
     */
    public function up(): void
    {
        Schema::create('examinations_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_examID')->comment('Original examID from examinations table');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->string('exam_name', 200);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['wait_approval', 'scheduled', 'ongoing', 'awaiting_results', 'results_available'])->default('ongoing');
            $table->enum('exam_type', [
                'school_wide_all_subjects',
                'specific_classes_all_subjects',
                'school_wide_specific_subjects',
                'specific_classes_specific_subjects'
            ]);
            $table->enum('exam_category', ['school_exams', 'test', 'special_exams'])->nullable();
            $table->string('term', 50)->nullable();
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('rejection_reason')->nullable();
            $table->year('year')->comment('Year from original examination');
            $table->text('details')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('enter_result')->default(false);
            $table->boolean('publish_result')->default(false);
            $table->boolean('upload_paper')->default(false);
            $table->enum('student_shifting_status', ['none', 'internal', 'external'])->nullable();
            $table->integer('total_students')->default(0)->comment('Total students who took this exam');
            $table->integer('total_results')->default(0)->comment('Total results recorded');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_examID'], 'exam_history_year_exam_idx');
            $table->index('academic_yearID', 'exam_history_year_idx');
            $table->index('original_examID', 'exam_history_exam_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examinations_history');
    }
};

