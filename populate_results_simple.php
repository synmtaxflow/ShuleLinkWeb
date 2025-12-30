<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Starting to populate results...\n";
    
    // Step 1: Update marks
    echo "Step 1: Updating marks...\n";
    $marksUpdated = DB::statement("
        UPDATE results r
        SET r.marks = CASE 
            WHEN RAND() < 0.3 THEN FLOOR(RAND() * 30)
            WHEN RAND() < 0.7 THEN 30 + FLOOR(RAND() * 35)
            ELSE 65 + FLOOR(RAND() * 36)
        END
        WHERE r.marks IS NULL AND r.status = 'not_allowed'
    ");
    echo "Marks updated.\n";
    
    // Step 2: Update grades
    echo "Step 2: Updating grades...\n";
    $gradesUpdated = DB::statement("
        UPDATE results r
        INNER JOIN examinations e ON r.examID = e.examID
        INNER JOIN subclasses s ON r.subclassID = s.subclassID
        INNER JOIN classes c ON s.classID = c.classID
        INNER JOIN schools sch ON c.schoolID = sch.schoolID
        SET r.grade = CASE 
            WHEN sch.school_type = 'Secondary' AND LOWER(REPLACE(REPLACE(c.class_name, ' ', '_'), '-', '_')) IN ('form_one', 'form_two', 'form_three', 'form_four', 'form_1', 'form_2', 'form_3', 'form_4') THEN
                CASE 
                    WHEN r.marks >= 75 THEN 'A'
                    WHEN r.marks >= 65 THEN 'B'
                    WHEN r.marks >= 45 THEN 'C'
                    WHEN r.marks >= 30 THEN 'D'
                    WHEN r.marks >= 20 THEN 'E'
                    ELSE 'F'
                END
            WHEN sch.school_type = 'Secondary' AND LOWER(REPLACE(REPLACE(c.class_name, ' ', '_'), '-', '_')) IN ('form_five', 'form_six', 'form_5', 'form_6') THEN
                CASE 
                    WHEN r.marks >= 80 THEN 'A'
                    WHEN r.marks >= 70 THEN 'B'
                    WHEN r.marks >= 60 THEN 'C'
                    WHEN r.marks >= 50 THEN 'D'
                    WHEN r.marks >= 40 THEN 'E'
                    ELSE 'S/F'
                END
            WHEN sch.school_type = 'Primary' THEN
                CASE 
                    WHEN r.marks >= 75 THEN 'A'
                    WHEN r.marks >= 65 THEN 'B'
                    WHEN r.marks >= 45 THEN 'C'
                    WHEN r.marks >= 30 THEN 'D'
                    ELSE 'F'
                END
            ELSE 
                CASE 
                    WHEN r.marks >= 75 THEN 'A'
                    WHEN r.marks >= 65 THEN 'B'
                    WHEN r.marks >= 45 THEN 'C'
                    WHEN r.marks >= 30 THEN 'D'
                    ELSE 'F'
                END
        END
        WHERE r.marks IS NOT NULL AND r.grade IS NULL
    ");
    echo "Grades updated.\n";
    
    // Step 3: Update remarks
    echo "Step 3: Updating remarks...\n";
    $remarksUpdated = DB::statement("
        UPDATE results r
        SET r.remark = CASE 
            WHEN r.marks >= 30 THEN 'Pass'
            ELSE 'Fail'
        END
        WHERE r.marks IS NOT NULL AND r.remark IS NULL
    ");
    echo "Remarks updated.\n";
    
    // Step 4: Update status
    echo "Step 4: Updating status...\n";
    $statusUpdated = DB::statement("
        UPDATE results r
        SET r.status = 'allowed'
        WHERE r.marks IS NOT NULL AND r.status = 'not_allowed'
    ");
    echo "Status updated.\n";
    
    // Show summary
    echo "\nSummary:\n";
    $summary = DB::table('results')
        ->selectRaw('
            COUNT(*) as total,
            COUNT(marks) as with_marks,
            COUNT(grade) as with_grades,
            COUNT(CASE WHEN marks >= 30 THEN 1 END) as passed,
            COUNT(CASE WHEN marks < 30 THEN 1 END) as failed
        ')
        ->first();
    
    echo "Total records: " . $summary->total . "\n";
    echo "With marks: " . $summary->with_marks . "\n";
    echo "With grades: " . $summary->with_grades . "\n";
    echo "Passed (>=30): " . $summary->passed . "\n";
    echo "Failed (<30): " . $summary->failed . "\n";
    
    echo "\nDone!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}



