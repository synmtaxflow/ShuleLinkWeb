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
        if (! Schema::hasTable('student_exam_halls')) {
            Schema::create('student_exam_halls', function (Blueprint $table) {
                $table->id('student_exam_hallID');
                $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
                $table->foreignId('exam_hallID')->constrained('exam_halls', 'exam_hallID')->onDelete('cascade');
                $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
                $table->foreignId('subclassID')->nullable()->constrained('subclasses', 'subclassID')->onDelete('set null');
                $table->timestamps();

                $table->index(['examID']);
                $table->index(['exam_hallID']);
                $table->index(['studentID']);
                $table->unique(['examID', 'studentID'], 'unique_student_per_exam_hall_assignment');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_exam_halls');
    }
};






