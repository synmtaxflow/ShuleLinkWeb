<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$date = '2026-02-16';
$subclassID = 20;

$attendance = DB::table('attendances')
    ->where('subclassID', $subclassID)
    ->where('attendance_date', $date)
    ->get();

foreach($attendance as $a) {
    $student = DB::table('students')->where('studentID', $a->studentID)->first();
    if (!$student) {
        echo "WARNING: Student ID " . $a->studentID . " in attendance but NOT in students table!\n";
    } else {
        echo "Student ID: " . $a->studentID . ", Gender: " . $student->gender . "\n";
    }
}
