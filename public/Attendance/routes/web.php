<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZKTecoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\ZKTecoPushController;
use App\Http\Controllers\PushSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResetController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('welcome');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Database Verification
Route::get('/database/verify', [DatabaseController::class, 'verify'])->name('database.verify');

// ZKTeco Device Testing Routes
Route::get('/zkteco/test', [ZKTecoController::class, 'index'])->name('zkteco.test');
Route::get('/zkteco/push-test', function() { return view('zkteco.push-test'); })->name('zkteco.push-test');
Route::get('/zkteco/push-setup', function() { return view('zkteco.push-setup'); })->name('zkteco.push-setup');

// Push SDK Setup Routes
Route::get('/push/setup/server-info', [PushSetupController::class, 'getServerInfo'])->name('push.setup.server-info');
Route::post('/push/setup/test-connection', [PushSetupController::class, 'testDeviceConnection'])->name('push.setup.test-connection');
Route::get('/push/setup/check-activity', function() {
    $usersCount = \App\Models\User::count();
    $attendancesCount = \App\Models\Attendance::count();
    $recentUsers = \App\Models\User::orderBy('created_at', 'desc')->limit(5)->pluck('name')->implode(', ');
    $recentAttendances = \App\Models\Attendance::orderBy('created_at', 'desc')->limit(5)->count();
    
    return response()->json([
        'success' => true,
        'users_count' => $usersCount,
        'attendances_count' => $attendancesCount,
        'recent_users' => $recentUsers ?: 'None',
        'recent_attendances' => $recentAttendances
    ]);
})->name('push.setup.check-activity');
Route::post('/zkteco/test-connection', [ZKTecoController::class, 'testConnection'])->name('zkteco.test-connection');
Route::post('/zkteco/device-info', [ZKTecoController::class, 'getDeviceInfo'])->name('zkteco.device-info');
Route::post('/zkteco/attendance', [ZKTecoController::class, 'getAttendance'])->name('zkteco.attendance');

// User Management Routes
Route::resource('users', UserController::class);
Route::post('/users/{id}/register-device', [UserController::class, 'registerToDevice'])->name('users.register-device');
Route::post('/users/{id}/mark-registered', [UserController::class, 'markAsRegistered'])->name('users.mark-registered');
Route::post('/users/{id}/remove-device', [UserController::class, 'removeFromDevice'])->name('users.remove-device');
Route::post('/users/{id}/check-fingerprints', [UserController::class, 'checkFingerprints'])->name('users.check-fingerprints');
Route::post('/users/sync-from-device', [UserController::class, 'syncUsersFromDevice'])->name('users.sync-from-device');
Route::post('/users/sync-to-device', [UserController::class, 'syncUsersToDevice'])->name('users.sync-to-device');
Route::post('/users/delete-all', [UserController::class, 'deleteAll'])->name('users.delete-all');
Route::post('/users/delete-all-device', [UserController::class, 'deleteAllFromDevice'])->name('users.delete-all-device');
Route::post('/users/list-device-users', [UserController::class, 'listDeviceUsers'])->name('users.list-device-users');
Route::post('/users/diagnose-device', [UserController::class, 'diagnoseDevice'])->name('users.diagnose-device');
Route::post('/users/diagnose-setuser', [UserController::class, 'diagnoseSetUserIssue'])->name('users.diagnose-setuser');
Route::post('/users/test-registration', [UserController::class, 'testRegistration'])->name('users.test-registration');
Route::post('/users/check-device-status', [UserController::class, 'checkDeviceStatus'])->name('users.check-device-status');

// Attendance Routes
Route::get('/attendances/sync', [AttendanceController::class, 'syncPage'])->name('attendances.sync-page');
Route::post('/attendances/sync', [AttendanceController::class, 'sync'])->name('attendances.sync');
Route::post('/attendances/test-device-data', [AttendanceController::class, 'testDeviceData'])->name('attendances.test-device-data');
Route::post('/attendances/delete-all', [AttendanceController::class, 'deleteAll'])->name('attendances.delete-all');
Route::post('/attendances/clear-device', [AttendanceController::class, 'clearDeviceAttendance'])->name('attendances.clear-device');
Route::post('/attendances/clear-all', [AttendanceController::class, 'clearAll'])->name('attendances.clear-all');
Route::resource('attendances', AttendanceController::class)->only(['index', 'show']);

// Reports
Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
Route::get('/reports/user/{userId}', [ReportController::class, 'userSummary'])->name('reports.user-summary');

// Reset / Fresh Start Routes
Route::get('/reset', [ResetController::class, 'index'])->name('reset.index');
Route::post('/reset/delete-attendances', [ResetController::class, 'deleteAttendances'])->name('reset.delete-attendances');
Route::post('/reset/delete-users', [ResetController::class, 'deleteUsers'])->name('reset.delete-users');
Route::post('/reset/delete-all', [ResetController::class, 'deleteAll'])->name('reset.delete-all');

// ZKTeco Push SDK Routes (Device pushes data to server)
// These routes are public and CSRF-exempt because the device calls them directly
Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->group(function () {
    Route::get('/iclock/getrequest', [ZKTecoPushController::class, 'getRequest'])->name('zkteco.push.getrequest');
    Route::post('/iclock/cdata', [ZKTecoPushController::class, 'cdata'])->name('zkteco.push.cdata');
});
