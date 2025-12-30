<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify exam_category enum to include 'test'
        DB::statement("ALTER TABLE examinations MODIFY COLUMN exam_category ENUM('school_exams', 'test', 'special_exams') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert exam_category enum to original values
        DB::statement("ALTER TABLE examinations MODIFY COLUMN exam_category ENUM('school_exams', 'special_exams') NULL");
    }
};
