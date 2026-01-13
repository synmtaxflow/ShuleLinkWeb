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
     * Fixes all enum columns in examinations_history to match examinations table:
     * - approval_status: ['pending', 'approved', 'rejected'] -> ['Pending', 'Approved', 'Rejected']
     * - status: ['ongoing', 'awaiting_results', 'results_available'] -> ['wait_approval', 'scheduled', 'ongoing', 'awaiting_results', 'results_available']
     * - student_shifting_status: ['pending', 'completed'] -> ['none', 'internal', 'external']
     */
    public function up(): void
    {
        if (Schema::hasTable('examinations_history')) {
            // Fix approval_status enum (capitalize first letter)
            DB::statement("ALTER TABLE examinations_history MODIFY COLUMN approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'");
            
            // Fix status enum (add missing values)
            DB::statement("ALTER TABLE examinations_history MODIFY COLUMN status ENUM('wait_approval', 'scheduled', 'ongoing', 'awaiting_results', 'results_available') DEFAULT 'ongoing'");
            
            // Fix student_shifting_status enum (change to match examinations table)
            DB::statement("ALTER TABLE examinations_history MODIFY COLUMN student_shifting_status ENUM('none', 'internal', 'external') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('examinations_history')) {
            // Revert approval_status enum
            DB::statement("ALTER TABLE examinations_history MODIFY COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
            
            // Revert status enum
            DB::statement("ALTER TABLE examinations_history MODIFY COLUMN status ENUM('ongoing', 'awaiting_results', 'results_available') DEFAULT 'ongoing'");
            
            // Revert student_shifting_status enum
            DB::statement("ALTER TABLE examinations_history MODIFY COLUMN student_shifting_status ENUM('pending', 'completed') NULL");
        }
    }
};
