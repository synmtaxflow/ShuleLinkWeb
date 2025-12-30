<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

try {
    $now = Carbon::now();
    
    echo "Starting to insert grade definitions...\n\n";
    
    // O-Level Grading System (Form 1-4): 75-100=A, 65-74=B, 45-64=C, 30-44=D, 0-29=F
    $oLevelGrades = [
        ['first' => 75.00, 'last' => 100.00, 'grade' => 'A'],
        ['first' => 65.00, 'last' => 74.00, 'grade' => 'B'],
        ['first' => 45.00, 'last' => 64.00, 'grade' => 'C'],
        ['first' => 30.00, 'last' => 44.00, 'grade' => 'D'],
        ['first' => 0.00, 'last' => 29.00, 'grade' => 'F'],
    ];

    // A-Level Grading System (Form 5-6): >=80=A, >=70=B, >=60=C, >=50=D, >=40=E, <40=S/F
    $aLevelGrades = [
        ['first' => 80.00, 'last' => 100.00, 'grade' => 'A'],
        ['first' => 70.00, 'last' => 79.00, 'grade' => 'B'],
        ['first' => 60.00, 'last' => 69.00, 'grade' => 'C'],
        ['first' => 50.00, 'last' => 59.00, 'grade' => 'D'],
        ['first' => 40.00, 'last' => 49.00, 'grade' => 'E'],
        ['first' => 0.00, 'last' => 39.00, 'grade' => 'S/F'],
    ];

    // O-Level Classes: FORM ONE (6), FORM TWO (15), FORM THREE (13), FORM FOUR (14)
    $oLevelClassIDs = [6, 15, 13, 14];
    
    // A-Level Classes: FORM FIVE (10), FORM SIX (11)
    $aLevelClassIDs = [10, 11];

    $insertedCount = 0;
    $updatedCount = 0;

    // Insert O-Level grade definitions
    foreach ($oLevelClassIDs as $classID) {
        $className = DB::table('classes')->where('classID', $classID)->value('class_name');
        echo "Processing O-Level: {$className} (classID: {$classID})...\n";
        
        foreach ($oLevelGrades as $gradeDef) {
            $existing = DB::table('grade_definitions')
                ->where('classID', $classID)
                ->where('grade', $gradeDef['grade'])
                ->first();
            
            if ($existing) {
                DB::table('grade_definitions')
                    ->where('classID', $classID)
                    ->where('grade', $gradeDef['grade'])
                    ->update([
                        'first' => $gradeDef['first'],
                        'last' => $gradeDef['last'],
                        'updated_at' => $now,
                    ]);
                $updatedCount++;
                echo "  Updated: Grade {$gradeDef['grade']} ({$gradeDef['first']}-{$gradeDef['last']})\n";
            } else {
                DB::table('grade_definitions')->insert([
                    'classID' => $classID,
                    'first' => $gradeDef['first'],
                    'last' => $gradeDef['last'],
                    'grade' => $gradeDef['grade'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $insertedCount++;
                echo "  Inserted: Grade {$gradeDef['grade']} ({$gradeDef['first']}-{$gradeDef['last']})\n";
            }
        }
        echo "\n";
    }

    // Insert A-Level grade definitions
    foreach ($aLevelClassIDs as $classID) {
        $className = DB::table('classes')->where('classID', $classID)->value('class_name');
        echo "Processing A-Level: {$className} (classID: {$classID})...\n";
        
        foreach ($aLevelGrades as $gradeDef) {
            $existing = DB::table('grade_definitions')
                ->where('classID', $classID)
                ->where('grade', $gradeDef['grade'])
                ->first();
            
            if ($existing) {
                DB::table('grade_definitions')
                    ->where('classID', $classID)
                    ->where('grade', $gradeDef['grade'])
                    ->update([
                        'first' => $gradeDef['first'],
                        'last' => $gradeDef['last'],
                        'updated_at' => $now,
                    ]);
                $updatedCount++;
                echo "  Updated: Grade {$gradeDef['grade']} ({$gradeDef['first']}-{$gradeDef['last']})\n";
            } else {
                DB::table('grade_definitions')->insert([
                    'classID' => $classID,
                    'first' => $gradeDef['first'],
                    'last' => $gradeDef['last'],
                    'grade' => $gradeDef['grade'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $insertedCount++;
                echo "  Inserted: Grade {$gradeDef['grade']} ({$gradeDef['first']}-{$gradeDef['last']})\n";
            }
        }
        echo "\n";
    }

    echo "========================================\n";
    echo "✅ Grade definitions inserted successfully!\n";
    echo "   Inserted: {$insertedCount} records\n";
    echo "   Updated: {$updatedCount} records\n";
    echo "   Total: " . ($insertedCount + $updatedCount) . " records\n";
    echo "========================================\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}











