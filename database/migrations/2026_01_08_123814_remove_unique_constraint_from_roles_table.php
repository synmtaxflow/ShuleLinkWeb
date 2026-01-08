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
        // Drop the unique constraint on name and guard_name
        // This allows multiple roles with the same name
        Schema::table('roles', function (Blueprint $table) {
            try {
                // Try to drop the unique constraint
                // The constraint name in MySQL is typically: roles_name_guard_name_unique
                $table->dropUnique(['name', 'guard_name']);
            } catch (\Exception $e) {
                // If constraint doesn't exist or has different name, try alternative
                // Check MySQL for the actual constraint name
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'roles' 
                    AND CONSTRAINT_TYPE = 'UNIQUE'
                    AND CONSTRAINT_NAME LIKE '%name%guard%'
                ");
                
                if (!empty($constraints)) {
                    $constraintName = $constraints[0]->CONSTRAINT_NAME;
                    DB::statement("ALTER TABLE roles DROP INDEX {$constraintName}");
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the unique constraint if rolling back
        Schema::table('roles', function (Blueprint $table) {
            // Check if constraint already exists
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'roles' 
                AND CONSTRAINT_TYPE = 'UNIQUE'
                AND CONSTRAINT_NAME LIKE '%name%guard%'
            ");
            
            if (empty($constraints)) {
                $table->unique(['name', 'guard_name']);
            }
        });
    }
};
