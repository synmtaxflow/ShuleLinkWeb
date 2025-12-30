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
     * Changes studentID from VARCHAR to INT (integer)
     */
    public function up(): void
    {
        // Step 1: Fix students with non-numeric studentID
        // Use fingerprint_id if it's numeric, otherwise generate a unique number
        $studentsWithNonNumericID = DB::select("
            SELECT studentID, fingerprint_id 
            FROM students 
            WHERE studentID NOT REGEXP '^[0-9]+$'
        ");
        
        foreach ($studentsWithNonNumericID as $student) {
            $newID = null;
            
            // Try to use fingerprint_id if it's numeric
            if ($student->fingerprint_id && preg_match('/^[0-9]+$/', $student->fingerprint_id)) {
                $newID = (int)$student->fingerprint_id;
            } else {
                // Generate a unique number (start from 10000 to avoid conflicts with 4-digit fingerprintIDs)
                $maxID = DB::selectOne("SELECT COALESCE(MAX(CAST(studentID AS UNSIGNED)), 0) as max_id FROM students WHERE studentID REGEXP '^[0-9]+$'");
                $newID = max(10000, (int)$maxID->max_id + 1);
                
                // Ensure uniqueness
                while (DB::table('students')->where('studentID', (string)$newID)->exists()) {
                    $newID++;
                }
            }
            
            // Update studentID
            if ($newID) {
                DB::statement("UPDATE students SET studentID = ? WHERE studentID = ?", [$newID, $student->studentID]);
            }
        }

        // Step 2: Drop foreign key constraints from related tables
        $tables = ['attendances', 'results', 'payments', 'book_borrows'];
        foreach ($tables as $table) {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'studentID' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table]);
            
            foreach ($constraints as $constraint) {
                try {
                    DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                } catch (\Exception $e) {
                    // Continue
                }
            }
        }

        // Step 3: Convert studentID values to integers in related tables
        DB::statement("
            UPDATE attendances 
            SET studentID = CAST(studentID AS UNSIGNED)
            WHERE studentID REGEXP '^[0-9]+$'
        ");

        DB::statement("
            UPDATE results 
            SET studentID = CAST(studentID AS UNSIGNED)
            WHERE studentID REGEXP '^[0-9]+$'
        ");

        DB::statement("
            UPDATE payments 
            SET studentID = CAST(studentID AS UNSIGNED)
            WHERE studentID REGEXP '^[0-9]+$'
        ");

        DB::statement("
            UPDATE book_borrows 
            SET studentID = CAST(studentID AS UNSIGNED)
            WHERE studentID REGEXP '^[0-9]+$'
        ");

        // Step 4: Change column types to INT in related tables
        DB::statement('ALTER TABLE attendances MODIFY studentID BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE results MODIFY studentID BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE payments MODIFY studentID BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE book_borrows MODIFY studentID BIGINT UNSIGNED NOT NULL');

        // Step 5: Change studentID in students table from VARCHAR to INT
        // Check if primary key exists and drop it if it does
        $hasPrimaryKey = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'students' 
            AND CONSTRAINT_TYPE = 'PRIMARY KEY'
        ");
        
        if (!empty($hasPrimaryKey) && $hasPrimaryKey[0]->count > 0) {
            DB::statement('ALTER TABLE students DROP PRIMARY KEY');
        }
        
        // Convert all studentID values to integers (they should all be numeric now)
        DB::statement("
            UPDATE students 
            SET studentID = CAST(studentID AS UNSIGNED)
            WHERE studentID REGEXP '^[0-9]+$'
        ");
        
        // Change column type to INT
        DB::statement('ALTER TABLE students MODIFY studentID BIGINT UNSIGNED NOT NULL');

        // Re-add primary key
        DB::statement('ALTER TABLE students ADD PRIMARY KEY (studentID)');

        // Step 6: Re-add foreign key constraints
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });

        Schema::table('book_borrows', function (Blueprint $table) {
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys
        $tables = ['attendances', 'results', 'payments', 'book_borrows'];
        foreach ($tables as $table) {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'studentID' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table]);
            
            foreach ($constraints as $constraint) {
                try {
                    DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                } catch (\Exception $e) {
                    // Continue
                }
            }
        }

        // Change back to VARCHAR
        DB::statement('ALTER TABLE students DROP PRIMARY KEY');
        DB::statement('ALTER TABLE students MODIFY studentID VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE students ADD PRIMARY KEY (studentID)');

        DB::statement('ALTER TABLE attendances MODIFY studentID VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE results MODIFY studentID VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE payments MODIFY studentID VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE book_borrows MODIFY studentID VARCHAR(50) NOT NULL');

        // Re-add foreign keys
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });

        Schema::table('book_borrows', function (Blueprint $table) {
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });
    }
};
