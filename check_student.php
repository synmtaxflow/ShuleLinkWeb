<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$student = Student::orderBy('created_at', 'desc')->first();
if ($student) {
    echo "Last Student: " . $student->first_name . " " . $student->last_name . "\n";
    echo "ID: " . $student->studentID . "\n";
    echo "Sponsor ID: " . ($student->sponsor_id ?? 'NULL') . "\n";
    echo "Sponsorship %: " . ($student->sponsorship_percentage ?? 'NULL') . "\n";
} else {
    echo "No students found.\n";
}
