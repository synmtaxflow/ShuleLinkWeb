<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

$schoolID = DB::table('classes')->where('class_name', 'FORM THREE')->first()->schoolID;
Session::put('schoolID', $schoolID);

$date = '2026-02-16';

$controller = new \App\Http\Controllers\TeacherDutyController();
$request = new \Illuminate\Http\Request(['date' => $date]);
$response = $controller->getDailyReport($request);

$data = json_decode($response->getContent(), true);
if (isset($data['system_attendance'][13])) {
    echo "Class 13 (FORM THREE) Data:\n";
    print_r($data['system_attendance'][13]);
} else {
    echo "Class 13 not found in response!\n";
    echo "Available keys: " . implode(', ', array_keys($data['system_attendance'] ?? [])) . "\n";
}
