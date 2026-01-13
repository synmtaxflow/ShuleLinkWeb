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
     * Adds bank_name column and makes reference_number nullable
     */
    public function up(): void
    {
        Schema::table('payment_records', function (Blueprint $table) {
            // Drop unique constraint on reference_number first
            $table->dropUnique(['reference_number']);
            
            // Make reference_number nullable
            $table->string('reference_number', 100)->nullable()->change();
            
            // Add bank_name column
            $table->string('bank_name', 200)->nullable()->after('payment_source');
            
            // Re-add unique constraint only for non-null reference numbers
            // Note: MySQL doesn't support partial unique indexes directly, so we'll use a workaround
            // We'll add a unique index that allows multiple NULLs
            $table->unique('reference_number', 'payment_records_reference_number_unique');
        });
        
        // For MySQL, we need to handle the unique constraint differently
        // MySQL allows multiple NULLs in a unique column, so this should work
        // But we'll also add a check to ensure uniqueness only for non-null values
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_records', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('payment_records_reference_number_unique');
            
            // Drop bank_name column
            $table->dropColumn('bank_name');
            
            // Make reference_number not nullable and re-add unique constraint
            $table->string('reference_number', 100)->nullable(false)->change();
            $table->unique('reference_number');
        });
    }
};
