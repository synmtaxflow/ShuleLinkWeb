<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Result;
use App\Models\Student;

$studentID = '7011';
$examID = '1';
$week = 'Week 1';

$student = Student::with(['parent', 'subclass.class'])->find($studentID);
if(!$student) {
    die("Student not found\n");
}
echo "Student: " . $student->first_name . " " . $student->last_name . " (Phone: " . ($student->parent->phone ?? 'N/A') . ")\n";

$results = Result::where('studentID', $studentID)->get();

echo "Total results for student: " . $results->count() . "\n";
foreach($results as $r) {
    echo "- Exam ID: " . $r->examID . ", Week: " . $r->test_week . ", Marks: " . $r->marks . ", Status: " . $r->status . "\n";
}
