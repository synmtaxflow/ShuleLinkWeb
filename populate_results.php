<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting to populate results...\n";

// Get all results that need to be populated
$results = DB::table('results')
    ->whereNull('marks')
    ->where('status', 'not_allowed')
    ->get();

echo "Found " . $results->count() . " results to populate\n";

$updated = 0;
$batchSize = 1000;

foreach ($results->chunk($batchSize) as $chunk) {
    foreach ($chunk as $result) {
        // Generate random marks with distribution:
        // 30% fail (0-29), 40% average (30-64), 30% pass well (65-100)
        $rand = mt_rand(1, 100);
        if ($rand <= 30) {
            $marks = mt_rand(0, 29); // 30% will fail
        } elseif ($rand <= 70) {
            $marks = mt_rand(30, 64); // 40% will get average
        } else {
            $marks = mt_rand(65, 100); // 30% will pass well
        }
        
        // Get school type and class name for grading
        $resultInfo = DB::table('results as r')
            ->join('examinations as e', 'r.examID', '=', 'e.examID')
            ->join('subclasses as s', 'r.subclassID', '=', 's.subclassID')
            ->join('classes as c', 's.classID', '=', 'c.classID')
            ->join('schools as sch', 'c.schoolID', '=', 'sch.schoolID')
            ->where('r.resultID', $result->resultID)
            ->select('sch.school_type', 'c.class_name')
            ->first();
        
        // Calculate grade based on school type and class
        $grade = null;
        if ($resultInfo) {
            $schoolType = $resultInfo->school_type;
            $className = strtolower(str_replace([' ', '-'], '_', $resultInfo->class_name));
            
            if ($schoolType == 'Secondary') {
                // Check if it's Form One-Four (O-Level) or Form Five-Six (A-Level)
                if (in_array($className, ['form_one', 'form_two', 'form_three', 'form_four', 'form_1', 'form_2', 'form_3', 'form_4'])) {
                    // O-Level grading
                    if ($marks >= 75) $grade = 'A';
                    elseif ($marks >= 65) $grade = 'B';
                    elseif ($marks >= 45) $grade = 'C';
                    elseif ($marks >= 30) $grade = 'D';
                    elseif ($marks >= 20) $grade = 'E';
                    else $grade = 'F';
                } elseif (in_array($className, ['form_five', 'form_six', 'form_5', 'form_6'])) {
                    // A-Level grading
                    if ($marks >= 80) $grade = 'A';
                    elseif ($marks >= 70) $grade = 'B';
                    elseif ($marks >= 60) $grade = 'C';
                    elseif ($marks >= 50) $grade = 'D';
                    elseif ($marks >= 40) $grade = 'E';
                    else $grade = 'S/F';
                } else {
                    // Default secondary grading
                    if ($marks >= 75) $grade = 'A';
                    elseif ($marks >= 65) $grade = 'B';
                    elseif ($marks >= 45) $grade = 'C';
                    elseif ($marks >= 30) $grade = 'D';
                    else $grade = 'F';
                }
            } elseif ($schoolType == 'Primary') {
                // Primary grading
                if ($marks >= 75) $grade = 'A';
                elseif ($marks >= 65) $grade = 'B';
                elseif ($marks >= 45) $grade = 'C';
                elseif ($marks >= 30) $grade = 'D';
                else $grade = 'F';
            } else {
                // Default grading
                if ($marks >= 75) $grade = 'A';
                elseif ($marks >= 65) $grade = 'B';
                elseif ($marks >= 45) $grade = 'C';
                elseif ($marks >= 30) $grade = 'D';
                else $grade = 'F';
            }
        } else {
            // Fallback grading if we can't determine school type
            if ($marks >= 75) $grade = 'A';
            elseif ($marks >= 65) $grade = 'B';
            elseif ($marks >= 45) $grade = 'C';
            elseif ($marks >= 30) $grade = 'D';
            else $grade = 'F';
        }
        
        // Determine remark
        $remark = $marks >= 30 ? 'Pass' : 'Fail';
        
        // Update the result
        DB::table('results')
            ->where('resultID', $result->resultID)
            ->update([
                'marks' => $marks,
                'grade' => $grade,
                'remark' => $remark,
                'status' => 'allowed'
            ]);
        
        $updated++;
        
        if ($updated % 100 == 0) {
            echo "Updated $updated results...\n";
        }
    }
}

echo "Completed! Updated $updated results.\n";

// Show summary
$summary = DB::table('results')
    ->selectRaw('
        COUNT(*) as total,
        COUNT(CASE WHEN marks >= 30 THEN 1 END) as passed,
        COUNT(CASE WHEN marks < 30 THEN 1 END) as failed,
        COUNT(CASE WHEN marks >= 30 AND marks < 65 THEN 1 END) as average
    ')
    ->whereNotNull('marks')
    ->first();

echo "\nSummary:\n";
echo "Total results with marks: " . $summary->total . "\n";
echo "Passed (>=30): " . $summary->passed . "\n";
echo "Failed (<30): " . $summary->failed . "\n";
echo "Average (30-64): " . $summary->average . "\n";



