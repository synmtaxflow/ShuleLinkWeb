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
     * Fixes payment_status enum in payments_history to match payments table
     * Changes from ['Pending', 'Partial', 'Paid', 'Overpaid'] to ['Pending', 'Partial', 'Incomplete Payment', 'Paid', 'Overpaid']
     */
    public function up(): void
    {
        if (Schema::hasTable('payments_history')) {
            // Modify payment_status enum to match payments table
            DB::statement("ALTER TABLE payments_history MODIFY COLUMN payment_status ENUM('Pending', 'Partial', 'Incomplete Payment', 'Paid', 'Overpaid') DEFAULT 'Pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payments_history')) {
            // Revert to original enum values
            DB::statement("ALTER TABLE payments_history MODIFY COLUMN payment_status ENUM('Pending', 'Partial', 'Paid', 'Overpaid') DEFAULT 'Pending'");
        }
    }
};
