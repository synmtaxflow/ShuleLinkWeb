<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Mock session
Session::put('schoolID', 1); // Assuming schoolID 1, but I should check what's common.
// Let's find a valid schoolID from classes
$schoolID = DB::table('classes')->first()->schoolID;
Session::put('schoolID', $schoolID);

$date = '2026-02-16';

$controller = new \App\Http\Controllers\TeacherDutyController();
$request = new \Illuminate\Http\Request(['date' => $date]);
$response = $controller->getDailyReport($request);

print_r(json_decode($response->getContent(), true));
