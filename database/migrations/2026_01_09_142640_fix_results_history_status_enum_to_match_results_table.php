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
     * Fixes status enum in results_history to match results table
     * Changes from ['Draft', 'Published'] to ['not_allowed', 'allowed', 'under_review', 'approved']
     */
    public function up(): void
    {
        if (Schema::hasTable('results_history')) {
            // Modify status enum to match results table
            DB::statement("ALTER TABLE results_history MODIFY COLUMN status ENUM('not_allowed', 'allowed', 'under_review', 'approved') DEFAULT 'not_allowed'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('results_history')) {
            // Revert to original enum values
            DB::statement("ALTER TABLE results_history MODIFY COLUMN status ENUM('Draft', 'Published') DEFAULT 'Draft'");
        }
    }
};
