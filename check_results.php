<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking results table...\n\n";

$stats = DB::table('results')
    ->selectRaw('
        COUNT(*) as total,
        COUNT(marks) as with_marks,
        COUNT(grade) as with_grades,
        COUNT(remark) as with_remarks,
        COUNT(CASE WHEN status = "allowed" THEN 1 END) as allowed_status,
        COUNT(CASE WHEN status = "not_allowed" THEN 1 END) as not_allowed_status
    ')
    ->first();

echo "Total records: " . $stats->total . "\n";
echo "With marks: " . $stats->with_marks . "\n";
echo "With grades: " . $stats->with_grades . "\n";
echo "With remarks: " . $stats->with_remarks . "\n";
echo "Status 'allowed': " . $stats->allowed_status . "\n";
echo "Status 'not_allowed': " . $stats->not_allowed_status . "\n\n";

// Show sample records
echo "Sample records:\n";
$samples = DB::table('results')
    ->select('resultID', 'studentID', 'examID', 'marks', 'grade', 'remark', 'status')
    ->limit(5)
    ->get();

foreach ($samples as $sample) {
    echo "ID: {$sample->resultID}, Marks: " . ($sample->marks ?? 'NULL') . ", Grade: " . ($sample->grade ?? 'NULL') . ", Remark: " . ($sample->remark ?? 'NULL') . ", Status: {$sample->status}\n";
}



