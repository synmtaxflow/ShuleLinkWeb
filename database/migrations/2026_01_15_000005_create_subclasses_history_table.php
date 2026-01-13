<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates subclasses_history table to store historical subclass data per academic year
     */
    public function up(): void
    {
        Schema::create('subclasses_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_subclassID')->comment('Original subclassID from subclasses table');
            $table->unsignedBigInteger('original_classID')->comment('Original classID from classes table');
            $table->unsignedBigInteger('teacherID')->nullable()->comment('Class teacher ID');
            $table->unsignedBigInteger('combieID')->nullable()->comment('Combie ID if applicable');
            $table->string('subclass_name', 50);
            $table->string('stream_code', 20)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->decimal('first_grade', 5, 2)->nullable()->comment('First grade requirement');
            $table->decimal('final_grade', 5, 2)->nullable()->comment('Final grade requirement');
            $table->integer('total_students')->default(0)->comment('Total number of students in this subclass');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_subclassID'], 'subcls_history_year_subcls_idx');
            $table->index(['academic_yearID', 'original_classID'], 'subcls_history_year_class_idx');
            $table->index('academic_yearID', 'subcls_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subclasses_history');
    }
};

