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
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id('class_subjectID');
            $table->foreignId('classID')->constrained('classes', 'classID')->onDelete('cascade');
            $table->foreignId('subjectID')->constrained('school_subjects', 'subjectID')->onDelete('cascade');
            $table->foreignId('teacherID')->nullable()->constrained('teachers')->onDelete('set null');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subjects');
    }
};
