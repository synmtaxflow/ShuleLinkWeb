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
     * Changes teacherID from auto-increment to use fingerprintID
     */
    public function up(): void
    {
        // Step 1: Ensure all teachers have fingerprint_id (generate from id if missing)
        // Only if fingerprint_id column exists
        if (Schema::hasColumn('teachers', 'fingerprint_id')) {
            DB::statement("
                UPDATE teachers
                SET fingerprint_id = CAST(id AS CHAR)
                WHERE fingerprint_id IS NULL OR fingerprint_id = ''
            ");
        }

        // Step 2: Drop foreign key constraints from related tables
        $tables = [
            'subclasses' => 'teacherID',
            'classes' => 'teacherID',
            'class_subjects' => 'teacherID',
            'attendances' => 'teacherID',
            'exam_papers' => 'teacherID',
            'exam_timetable' => 'teacherID',
            'exam_supervise_teacher' => 'teacherID'
        ];

        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table)) {
                // Get actual constraint names
                $foreignKeys = DB::select(
                    "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? 
                     AND REFERENCED_TABLE_NAME = 'teachers'",
                    [$table, $column]
                );
                
                foreach ($foreignKeys as $fk) {
                    try {
                        DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                    } catch (\Exception $e) {
                        // Foreign key might not exist, continue
                        Log::info("Foreign key {$fk->CONSTRAINT_NAME} not found or already dropped");
                    }
                }
            }
        }

        // Step 3: Change teacherID foreign key columns in related tables to BIGINT UNSIGNED
        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
                    $tableBlueprint->bigInteger($column)->unsigned()->nullable()->change();
                });
            }
        }

        // Step 4: Update related tables to use fingerprint_id instead of old id
        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::statement("
                    UPDATE {$table} t
                    INNER JOIN teachers te ON t.{$column} = te.id
                    SET t.{$column} = CAST(te.fingerprint_id AS UNSIGNED)
                    WHERE te.fingerprint_id IS NOT NULL AND te.fingerprint_id != '' AND te.fingerprint_id REGEXP '^[0-9]+$'
                ");
            }
        }

        // Step 5: Change teacherID in teachers table from auto-increment to BIGINT UNSIGNED
        // First, remove auto-increment (but keep it as primary key temporarily)
        DB::statement('ALTER TABLE teachers MODIFY id BIGINT UNSIGNED NOT NULL');
        
        // Now drop primary key (after removing auto-increment)
        try {
            DB::statement('ALTER TABLE teachers DROP PRIMARY KEY');
        } catch (\Exception $e) {
            // Primary key might already be dropped or doesn't exist
            \Log::info("Primary key drop failed or already dropped: " . $e->getMessage());
        }
        
        // Update id = fingerprint_id for all teachers (only if fingerprint_id exists)
        if (Schema::hasColumn('teachers', 'fingerprint_id')) {
            DB::statement("
                UPDATE teachers
                SET id = CAST(fingerprint_id AS UNSIGNED)
                WHERE fingerprint_id IS NOT NULL AND fingerprint_id != '' AND fingerprint_id REGEXP '^[0-9]+$'
            ");
        }
        
        // Re-add primary key (id remains as primary key, but without auto increment)
        try {
            DB::statement('ALTER TABLE teachers ADD PRIMARY KEY (id)');
        } catch (\Exception $e) {
            // Primary key might already exist
            \Log::info("Primary key add failed or already exists: " . $e->getMessage());
        }

        // Step 6: Clean up invalid foreign key references before re-adding constraints
        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                // Set invalid foreign keys to NULL
                DB::statement("
                    UPDATE {$table} t
                    LEFT JOIN teachers te ON t.{$column} = te.id
                    SET t.{$column} = NULL
                    WHERE t.{$column} IS NOT NULL AND te.id IS NULL
                ");
            }
        }
        
        // Step 7: Re-add foreign key constraints
        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                try {
                    Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
                        $tableBlueprint->foreign($column)->references('id')->on('teachers')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                    // Foreign key might already exist or constraint might fail
                    \Log::info("Failed to add foreign key constraint for {$table}.{$column}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This is a complex rollback. In production, you might want to backup first.
        // For now, we'll just note that rollback would require restoring the original auto-increment structure
    }
};
