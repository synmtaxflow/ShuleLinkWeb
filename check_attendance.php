<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$today = date('Y-m-d');
echo "Today: $today\n";

$attendance = DB::table('attendances')
    ->where('subclassID', 20)
    ->where('attendance_date', $today)
    ->get();

echo "Attendance for Subclass 20 on $today: " . $attendance->count() . " records\n";
foreach($attendance as $a) {
    echo "  Student ID: " . $a->studentID . ", Status: " . $a->status . "\n";
}

$all_attendance = DB::table('attendances')
    ->where('subclassID', 20)
    ->orderBy('attendance_date', 'desc')
    ->limit(10)
    ->get();

echo "\nLatest 10 attendance records for Subclass 20:\n";
foreach($all_attendance as $a) {
    echo "  Date: " . $a->attendance_date . ", Student ID: " . $a->studentID . ", Status: " . $a->status . "\n";
}
