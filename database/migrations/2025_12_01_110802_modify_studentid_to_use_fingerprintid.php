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
     * Changes studentID from auto-increment bigint to string (fingerprintID)
     * Sets studentID = fingerprintID for all records
     */
    public function up(): void
    {
        // Step 1: Ensure all students have fingerprint_id
        // For students without fingerprint_id, generate one from their studentID (as string)
        DB::statement("
            UPDATE students
            SET fingerprint_id = CAST(studentID AS CHAR)
            WHERE fingerprint_id IS NULL OR fingerprint_id = ''
        ");

        // Step 2: Drop foreign key constraints from related tables (get actual constraint names)
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
                    // Foreign key might not exist, continue
                }
            }
        }

        // Step 3: Change studentID foreign key columns in related tables to string
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('studentID', 50)->change();
        });

        Schema::table('results', function (Blueprint $table) {
            $table->string('studentID', 50)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('studentID', 50)->change();
        });

        Schema::table('book_borrows', function (Blueprint $table) {
            $table->string('studentID', 50)->change();
        });

        // Step 4: Update related tables to use fingerprint_id instead of old studentID
        // We need to do this before changing students.studentID
        DB::statement("
            UPDATE attendances a
            INNER JOIN students s ON CAST(a.studentID AS CHAR) = CAST(s.studentID AS CHAR)
            SET a.studentID = s.fingerprint_id
        ");

        DB::statement("
            UPDATE results r
            INNER JOIN students s ON CAST(r.studentID AS CHAR) = CAST(s.studentID AS CHAR)
            SET r.studentID = s.fingerprint_id
        ");

        DB::statement("
            UPDATE payments p
            INNER JOIN students s ON CAST(p.studentID AS CHAR) = CAST(s.studentID AS CHAR)
            SET p.studentID = s.fingerprint_id
        ");

        DB::statement("
            UPDATE book_borrows b
            INNER JOIN students s ON CAST(b.studentID AS CHAR) = CAST(s.studentID AS CHAR)
            SET b.studentID = s.fingerprint_id
        ");

        // Step 5: Change studentID in students table from bigint to string
        // First, remove auto-increment from studentID (must be done before dropping primary key)
        DB::statement('ALTER TABLE students MODIFY studentID BIGINT UNSIGNED NOT NULL');
        
        // Drop primary key
        DB::statement('ALTER TABLE students DROP PRIMARY KEY');
        
        // Change column type to string
        DB::statement('ALTER TABLE students MODIFY studentID VARCHAR(50) NOT NULL');

        // Update studentID = fingerprint_id for all students
        DB::statement("
            UPDATE students
            SET studentID = fingerprint_id
        ");

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
     * 
     * WARNING: This rollback may cause data loss if studentID values were changed
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

        // Change back to bigint (auto-increment)
        DB::statement('ALTER TABLE students DROP PRIMARY KEY');
        
        Schema::table('students', function (Blueprint $table) {
            $table->bigInteger('studentID')->unsigned()->autoIncrement()->change();
        });

        DB::statement('ALTER TABLE students ADD PRIMARY KEY (studentID)');

        // Change foreign key columns back to bigint
        Schema::table('attendances', function (Blueprint $table) {
            $table->bigInteger('studentID')->unsigned()->change();
        });

        Schema::table('results', function (Blueprint $table) {
            $table->bigInteger('studentID')->unsigned()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('studentID')->unsigned()->change();
        });

        Schema::table('book_borrows', function (Blueprint $table) {
            $table->bigInteger('studentID')->unsigned()->change();
        });

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
