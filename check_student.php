<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$student = DB::table('students')->where('subclassID', 20)->first();
if ($student) {
    echo "Student ID: " . $student->studentID . "\n";
    echo "Gender: [" . $student->gender . "]\n";
} else {
    echo "No students found in subclass 20\n";
}
