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
        Schema::table('examinations', function (Blueprint $table) {
            // Add approval_status column
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending')->after('status');
            // Modify status to include 'scheduled'
            // Note: We'll need to update existing enum values
        });
        
        // Update status enum to include 'scheduled' and 'wait_approval'
        DB::statement("ALTER TABLE examinations MODIFY COLUMN status ENUM('wait_approval', 'scheduled', 'ongoing', 'awaiting_results', 'results_available') DEFAULT 'wait_approval'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
        
        // Revert status enum
        DB::statement("ALTER TABLE examinations MODIFY COLUMN status ENUM('ongoing', 'awaiting_results', 'results_available') DEFAULT 'ongoing'");
    }
};
