<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AttendanceApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| Base URL: http://192.168.100.104/api
|
*/

// Health check endpoint - No authentication required
Route::get('/health', [ApiController::class, 'healthCheck'])->name('api.health');

// Authentication endpoints - No authentication required
Route::post('/login', [ApiController::class, 'login'])->name('api.login');
Route::post('/logout', [ApiController::class, 'logout'])->name('api.logout');

// User profile endpoint
Route::get('/user/profile', [ApiController::class, 'getUserProfile'])->name('api.user.profile');

// Parent Dashboard endpoint
Route::get('/parent/dashboard', [ApiController::class, 'parentDashboard'])->name('api.parent.dashboard');

// Parent Results endpoint
Route::get('/parent/results', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentResults'])->name('api.parent.results');
Route::post('/parent/results', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentResults'])->name('api.parent.results.post');

// Parent Payments endpoint
Route::get('/parent/payments', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentPayments'])->name('api.parent.payments');
Route::post('/parent/payments', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentPayments'])->name('api.parent.payments.post');

// Parent Fees Summary endpoint
Route::get('/parent/fees-summary', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentFeesSummary'])->name('api.parent.fees.summary');
Route::post('/parent/fees-summary', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentFeesSummary'])->name('api.parent.fees.summary.post');

// Attendance API endpoint - No authentication required (for external biometric device)
Route::post('/attendance/record', [AttendanceApiController::class, 'recordAttendance'])->name('api.attendance.record');

// Sync attendance from external biometric system
Route::get('/attendance/sync-external', [AttendanceApiController::class, 'syncFromExternal'])->name('api.attendance.sync_external');

// List raw external attendance records (no local filtering)
Route::get('/attendance/external-list', [AttendanceApiController::class, 'listExternal'])->name('api.attendance.external_list');

// Get fingerprint attendance from local database (student_fingerprint_attendance table)
Route::get('/attendance/local-fingerprint', [AttendanceApiController::class, 'getLocalFingerprintAttendance'])->name('api.attendance.local_fingerprint');

// List external attendance records filtered to students that exist in this system
Route::get('/attendance/external-students', [AttendanceApiController::class, 'listExternalStudents'])->name('api.attendance.external_students');

// List external attendance records filtered to teachers that exist in this system
Route::get('/attendance/external-teachers', [AttendanceApiController::class, 'listExternalTeachers'])->name('api.attendance.external_teachers');

// Get all attendance records from external API filtered to teachers only
Route::get('/attendance/all-teachers', [AttendanceApiController::class, 'getAllAttendanceTeachers'])->name('api.attendance.all_teachers');

// Get teacher fingerprint attendance from local database (teacher_fingerprint_attendance table)
Route::get('/attendance/teachers-fingerprint-local', [AttendanceApiController::class, 'getTeacherFingerprintAttendanceLocal'])->name('api.attendance.teachers_fingerprint_local');

// Get teacher fingerprint attendance from local database
Route::get('/attendance/teachers-fingerprint', [AttendanceApiController::class, 'getTeacherFingerprintAttendance'])->name('api.attendance.teachers_fingerprint');


