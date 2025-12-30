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
        Schema::create('grade_definitions', function (Blueprint $table) {
            $table->id('gradeDefinitionID');
            $table->foreignId('classID')->constrained('classes', 'classID')->onDelete('cascade');
            $table->decimal('first', 5, 2)->comment('Minimum marks for this grade');
            $table->decimal('last', 5, 2)->comment('Maximum marks for this grade');
            $table->string('grade', 10)->comment('Grade letter (A, B, C, D, E, F)');
            $table->timestamps();
            
            // Ensure unique grade per class
            $table->unique(['classID', 'grade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_definitions');
    }
};
