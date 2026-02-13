<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\Examination::all() as $e) {
    echo $e->examID . ": " . $e->exam_name . " (School: " . $e->schoolID . ")\n";
}
