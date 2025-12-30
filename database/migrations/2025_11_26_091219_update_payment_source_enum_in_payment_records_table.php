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
        // Update existing 'Manual' records to 'Cash'
        DB::table('payment_records')
            ->where('payment_source', 'Manual')
            ->update(['payment_source' => 'Cash']);
        
        // Update enum to use 'Cash' and 'Bank'
        DB::statement("ALTER TABLE payment_records MODIFY COLUMN payment_source ENUM('Cash', 'Bank') DEFAULT 'Cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update existing 'Cash' records to 'Manual'
        DB::table('payment_records')
            ->where('payment_source', 'Cash')
            ->update(['payment_source' => 'Manual']);
        
        // Revert enum to use 'Manual' and 'Bank'
        DB::statement("ALTER TABLE payment_records MODIFY COLUMN payment_source ENUM('Bank', 'Manual') DEFAULT 'Manual'");
    }
};
