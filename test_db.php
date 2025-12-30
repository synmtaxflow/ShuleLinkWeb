<?php

// Redirect output to file
$logFile = __DIR__ . '/populate_log.txt';
$log = fopen($logFile, 'w');

function logMessage($message, $logFile) {
    echo $message . "\n";
    file_put_contents($logFile, $message . "\n", FILE_APPEND);
}

logMessage("Starting script...", $logFile);

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

logMessage("Database connection established.", $logFile);

// Check how many records need updating
try {
    $count = DB::table('results')
        ->whereNull('marks')
        ->where('status', 'not_allowed')
        ->count();
    
    logMessage("Records to update: $count", $logFile);
} catch (\Exception $e) {
    logMessage("Error checking records: " . $e->getMessage(), $logFile);
    exit(1);
}

if ($count > 0) {
    logMessage("Updating marks...", $logFile);
    
    // Update marks in batches
    DB::table('results')
        ->whereNull('marks')
        ->where('status', 'not_allowed')
        ->orderBy('resultID')
        ->chunk(500, function ($results) {
            foreach ($results as $result) {
                $rand = mt_rand(1, 100);
                if ($rand <= 30) {
                    $marks = mt_rand(0, 29);
                } elseif ($rand <= 70) {
                    $marks = mt_rand(30, 64);
                } else {
                    $marks = mt_rand(65, 100);
                }
                
                DB::table('results')
                    ->where('resultID', $result->resultID)
                    ->update(['marks' => $marks]);
            }
        });
    
    logMessage("Marks updated. Now updating grades...", $logFile);
    
    // Update grades
    DB::statement("
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
    
    logMessage("Grades updated. Now updating remarks...", $logFile);
    
    // Update remarks
    DB::table('results')
        ->whereNotNull('marks')
        ->whereNull('remark')
        ->update([
            'remark' => DB::raw("CASE WHEN marks >= 30 THEN 'Pass' ELSE 'Fail' END")
        ]);
    
    logMessage("Remarks updated. Now updating status...", $logFile);
    
    // Update status
    DB::table('results')
        ->whereNotNull('marks')
        ->where('status', 'not_allowed')
        ->update(['status' => 'allowed']);
    
    logMessage("Status updated.", $logFile);
    
    // Show summary
    $summary = DB::table('results')
        ->selectRaw('
            COUNT(*) as total,
            COUNT(marks) as with_marks,
            COUNT(CASE WHEN marks >= 30 THEN 1 END) as passed,
            COUNT(CASE WHEN marks < 30 THEN 1 END) as failed
        ')
        ->first();
    
    logMessage("\nSummary:", $logFile);
    logMessage("Total: {$summary->total}", $logFile);
    logMessage("With marks: {$summary->with_marks}", $logFile);
    logMessage("Passed: {$summary->passed}", $logFile);
    logMessage("Failed: {$summary->failed}", $logFile);
} else {
    logMessage("No records to update.", $logFile);
}

fclose($log);
logMessage("Script completed. Check populate_log.txt for details.", $logFile);



