<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$attendance = DB::table('attendances')->where('subclassID', 20)->first();
if ($attendance) {
    echo "Attendance ID: " . $attendance->attendanceID . "\n";
    echo "School ID: " . $attendance->schoolID . "\n";
} else {
    echo "No attendance found for subclass 20\n";
}
echo "Session School ID: " . Session::get('schoolID') . "\n";
