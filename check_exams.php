<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Examination;

$exam = Examination::find(1);
if($exam) {
    echo "Exam 1: " . $exam->exam_name . " (Year: " . $exam->year . ")\n";
} else {
    echo "Exam 1 not found\n";
}

$exams = Examination::orderBy('examID', 'desc')->limit(10)->get();
foreach($exams as $ex) {
    echo "- Exam ID: " . $ex->examID . ": " . $ex->exam_name . " (" . $ex->year . ")\n";
}
