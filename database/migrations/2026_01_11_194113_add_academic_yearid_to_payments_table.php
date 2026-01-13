<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds academic_yearID column to payments table
     * This allows tracking which academic year each payment belongs to
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add academic_yearID column (nullable for now to handle existing records)
            $table->foreignId('academic_yearID')->nullable()->after('schoolID')->constrained('academic_years', 'academic_yearID')->onDelete('set null');
            
            // Add index for better query performance
            $table->index('academic_yearID', 'payments_academic_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['academic_yearID']);
            
            // Drop index
            $table->dropIndex('payments_academic_year_idx');
            
            // Drop column
            $table->dropColumn('academic_yearID');
        });
    }
};
