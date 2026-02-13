<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$count = \Illuminate\Support\Facades\DB::table('examinations')->where('examID', 1)->count();
echo "Count for examID 1: " . $count . "\n";

$all = \Illuminate\Support\Facades\DB::table('examinations')->limit(5)->get();
print_r($all);
