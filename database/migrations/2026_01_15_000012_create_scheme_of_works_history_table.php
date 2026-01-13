<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates scheme_of_works_history table to store historical scheme of works data per academic year
     */
    public function up(): void
    {
        Schema::create('scheme_of_works_history', function (Blueprint $table) {
            $table->id('historyID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->unsignedBigInteger('original_scheme_of_workID')->comment('Original scheme_of_workID from scheme_of_works table');
            $table->unsignedBigInteger('original_class_subjectID')->comment('Original class_subjectID');
            $table->integer('year')->comment('Year from original scheme');
            $table->enum('status', ['Draft', 'Active', 'Archived'])->default('Draft');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Teacher ID who created');
            $table->integer('total_items')->default(0)->comment('Total number of scheme items');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'original_scheme_of_workID'], 'sow_history_year_sow_idx');
            $table->index(['academic_yearID', 'original_class_subjectID'], 'sow_history_year_cs_idx');
            $table->index('academic_yearID', 'sow_history_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_of_works_history');
    }
};

