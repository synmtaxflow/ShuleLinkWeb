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
     * Makes to_academic_yearID nullable in student_promotions table
     * This allows promotions to be recorded before the new academic year is created
     */
    public function up(): void
    {
        // Check if table exists
        if (Schema::hasTable('student_promotions')) {
            // Drop the foreign key constraint first
            Schema::table('student_promotions', function (Blueprint $table) {
                // Get the constraint name (MySQL naming convention)
                $foreignKeyName = 'student_promotions_to_academic_yearid_foreign';
                
                // Drop foreign key if it exists
                $table->dropForeign($foreignKeyName);
            });
            
            // Modify the column to be nullable
            Schema::table('student_promotions', function (Blueprint $table) {
                $table->unsignedBigInteger('to_academic_yearID')->nullable()->change();
            });
            
            // Re-add the foreign key constraint with nullable support
            Schema::table('student_promotions', function (Blueprint $table) {
                $table->foreign('to_academic_yearID')
                    ->references('academic_yearID')
                    ->on('academic_years')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('student_promotions')) {
            // Drop the foreign key constraint
            Schema::table('student_promotions', function (Blueprint $table) {
                $foreignKeyName = 'student_promotions_to_academic_yearid_foreign';
                $table->dropForeign($foreignKeyName);
            });
            
            // Make column not nullable again
            // First, set any null values to a default value (or handle as needed)
            DB::table('student_promotions')
                ->whereNull('to_academic_yearID')
                ->update(['to_academic_yearID' => DB::raw('from_academic_yearID')]);
            
            // Modify the column to be not nullable
            Schema::table('student_promotions', function (Blueprint $table) {
                $table->unsignedBigInteger('to_academic_yearID')->nullable(false)->change();
            });
            
            // Re-add the foreign key constraint
            Schema::table('student_promotions', function (Blueprint $table) {
                $table->foreign('to_academic_yearID')
                    ->references('academic_yearID')
                    ->on('academic_years')
                    ->onDelete('cascade');
            });
        }
    }
};
