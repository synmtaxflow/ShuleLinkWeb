<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates classes_history table to store historical class data per academic year
     */
    public function up(): void
    {
        Schema::create('classes_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_classID')->comment('Original classID from classes table');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('teacherID')->nullable()->comment('Coordinator teacher ID');
            $table->string('class_name', 100);
            $table->string('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->boolean('has_subclasses')->default(true);
            $table->integer('total_subclasses')->default(0)->comment('Total number of subclasses in this class');
            $table->integer('total_students')->default(0)->comment('Total number of students in this class');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_classID'], 'cls_history_year_class_idx');
            $table->index('academic_yearID', 'cls_history_year_idx');
            $table->index('original_classID', 'cls_history_class_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes_history');
    }
};

