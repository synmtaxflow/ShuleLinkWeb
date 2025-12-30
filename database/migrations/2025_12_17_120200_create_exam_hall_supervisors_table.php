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
        if (! Schema::hasTable('exam_hall_supervisors')) {
            Schema::create('exam_hall_supervisors', function (Blueprint $table) {
                $table->id('exam_hall_supervisorID');
                $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
                $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
                $table->foreignId('exam_hallID')->constrained('exam_halls', 'exam_hallID')->onDelete('cascade');
                $table->foreignId('teacherID')->constrained('teachers', 'id')->onDelete('cascade');
                $table->timestamps();

                $table->index(['examID']);
                $table->index(['exam_hallID']);
                $table->index(['teacherID']);
                // Note: Unique constraint for subjectID and exam_timetableID will be added in later migration
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_hall_supervisors');
    }
};


