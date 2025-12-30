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
        Schema::create('results', function (Blueprint $table) {
            $table->id('resultID');
            $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
            $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
            $table->foreignId('subclassID')->nullable()->constrained('subclasses', 'subclassID')->onDelete('cascade');
            $table->foreignId('class_subjectID')->nullable()->constrained('class_subjects', 'class_subjectID')->onDelete('cascade');
            $table->decimal('marks', 5, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
