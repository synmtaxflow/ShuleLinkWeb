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
        // Add 'all_terms' to term enum in examinations table
        DB::statement("ALTER TABLE examinations MODIFY COLUMN term ENUM('first_term', 'second_term', 'all_terms') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'all_terms' from term enum
        // Note: This will fail if there are records using 'all_terms'
        DB::statement("ALTER TABLE examinations MODIFY COLUMN term ENUM('first_term', 'second_term') NULL");
    }
};
