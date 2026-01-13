<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates results_history table to store historical results data per academic year
     */
    public function up(): void
    {
        Schema::create('results_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_resultID')->comment('Original resultID from results table');
            $table->unsignedBigInteger('studentID')->comment('Student ID');
            $table->unsignedBigInteger('original_examID')->comment('Original examID');
            $table->unsignedBigInteger('subclassID')->nullable();
            $table->unsignedBigInteger('original_class_subjectID')->nullable();
            $table->decimal('marks', 5, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->text('remark')->nullable();
            $table->enum('status', ['not_allowed', 'allowed', 'under_review', 'approved'])->default('not_allowed');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_resultID'], 'res_history_year_result_idx');
            $table->index(['academic_yearID', 'studentID'], 'res_history_year_student_idx');
            $table->index(['academic_yearID', 'original_examID'], 'res_history_year_exam_idx');
            $table->index('academic_yearID', 'res_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results_history');
    }
};

