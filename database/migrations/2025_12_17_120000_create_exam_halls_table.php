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
        if (! Schema::hasTable('exam_halls')) {
            Schema::create('exam_halls', function (Blueprint $table) {
                $table->id('exam_hallID');
                $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
                $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
                $table->foreignId('classID')->constrained('classes', 'classID')->onDelete('cascade');
                $table->string('hall_name');
                $table->unsignedInteger('capacity');
                $table->enum('gender_allowed', ['male', 'female', 'both'])->default('both');
                $table->timestamps();

                $table->index(['examID']);
                $table->index(['classID']);
                $table->unique(['examID', 'hall_name'], 'unique_hall_name_per_exam');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_halls');
    }
};






