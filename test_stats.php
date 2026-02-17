<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$date = '2026-02-16';
$subclassIDs = [20];

$stats = DB::table('attendances')
    ->join('students', 'attendances.studentID', '=', 'students.studentID')
    ->whereIn('attendances.subclassID', $subclassIDs)
    ->whereDate('attendances.attendance_date', $date)
    ->select(
        DB::raw('SUM(CASE WHEN students.gender = "Male" AND attendances.status = "Present" THEN 1 ELSE 0 END) as present_boys'),
        DB::raw('SUM(CASE WHEN students.gender = "Female" AND attendances.status = "Present" THEN 1 ELSE 0 END) as present_girls')
    )
    ->first();

print_r($stats);
