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
        // Get foreign key constraint names
        $fkConstraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'exam_attendance' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        $fkNames = array_map(function($fk) {
            return $fk->CONSTRAINT_NAME;
        }, $fkConstraints);
        
        // Drop foreign keys temporarily
        foreach ($fkNames as $fkName) {
            try {
                DB::statement("ALTER TABLE `exam_attendance` DROP FOREIGN KEY `{$fkName}`");
            } catch (\Exception $e) {
                // Ignore if doesn't exist
            }
        }
        
        // Drop the unique constraint
        try {
            DB::statement('ALTER TABLE `exam_attendance` DROP INDEX `unique_exam_student`');
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
        
        // Add subjectID column
        Schema::table('exam_attendance', function (Blueprint $table) {
            $table->foreignId('subjectID')->nullable()->after('studentID');
        });
        
        // Recreate foreign keys
        Schema::table('exam_attendance', function (Blueprint $table) {
            $table->foreign('examID')->references('examID')->on('examinations')->onDelete('cascade');
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('subjectID')->references('subjectID')->on('school_subjects')->onDelete('cascade');
        });
        
        // Add new unique constraint
        Schema::table('exam_attendance', function (Blueprint $table) {
            $table->unique(['examID', 'studentID', 'subjectID'], 'unique_exam_student_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attendance', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('unique_exam_student_subject');
            
            // Drop subjectID column
            $table->dropForeign(['subjectID']);
            $table->dropColumn('subjectID');
            
            // Restore the old unique constraint
            $table->unique(['examID', 'studentID'], 'unique_exam_student');
        });
    }
};
