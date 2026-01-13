<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fixes exam_category enum in examinations_history to match examinations table
     * Changes from ['Test', 'Midterm', 'Final', 'Other'] to ['school_exams', 'test', 'special_exams']
     */
    public function up(): void
    {
        if (Schema::hasTable('examinations_history')) {
            // Modify exam_category enum to match examinations table
            DB::statement("ALTER TABLE examinations_history MODIFY COLUMN exam_category ENUM('school_exams', 'test', 'special_exams') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('examinations_history')) {
            // Revert to original enum values
            // Note: This might cause data loss if there are records with new values
            DB::statement("ALTER TABLE examinations_history MODIFY COLUMN exam_category ENUM('Test', 'Midterm', 'Final', 'Other') NULL");
        }
    }
};
