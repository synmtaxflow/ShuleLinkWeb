<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AttendanceApiController;
use App\Http\Controllers\AccomodationController;
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

// Teacher Dashboard endpoint
Route::get('/teacher/dashboard', [\App\Http\Controllers\TeachersController::class, 'getTeacherDashboardAPI'])->name('api.teacher.dashboard');

// Teacher Profile endpoints
Route::get('/teacher/profile', [\App\Http\Controllers\TeachersController::class, 'getTeacherProfileAPI'])->name('api.teacher.profile');
Route::post('/teacher/profile/update', [\App\Http\Controllers\TeachersController::class, 'updateTeacherProfileAPI'])->name('api.teacher.profile.update');
Route::post('/teacher/password/change', [\App\Http\Controllers\TeachersController::class, 'changeTeacherPasswordAPI'])->name('api.teacher.password.change');

// Teacher Subjects endpoints
Route::get('/teacher/subjects', [\App\Http\Controllers\TeachersController::class, 'getTeacherSubjectsAPI'])->name('api.teacher.subjects');
Route::get('/teacher/subjects/{classSubjectID}/students', [\App\Http\Controllers\TeachersController::class, 'getSubjectStudentsAPI'])->name('api.teacher.subjects.students');
Route::get('/teacher/subjects/{classSubjectID}/examinations', [\App\Http\Controllers\TeachersController::class, 'getExaminationsForSubjectAPI'])->name('api.teacher.subjects.examinations');
Route::get('/teacher/subjects/{classSubjectID}/results', [\App\Http\Controllers\TeachersController::class, 'getSubjectResultsAPI'])->name('api.teacher.subjects.results');
Route::get('/teacher/subjects/{classSubjectID}/results/{examID}', [\App\Http\Controllers\TeachersController::class, 'getSubjectResultsAPI'])->name('api.teacher.subjects.results.by_exam');
Route::post('/teacher/subjects/results/save', [\App\Http\Controllers\TeachersController::class, 'saveSubjectResultsAPI'])->name('api.teacher.subjects.results.save');
Route::post('/teacher/subjects/results/upload-excel', [\App\Http\Controllers\TeachersController::class, 'uploadExcelResultsAPI'])->name('api.teacher.subjects.results.upload_excel');
Route::get('/teacher/subjects/{classSubjectID}/results/{examID}/download-excel-template', [\App\Http\Controllers\TeachersController::class, 'downloadExcelTemplateAPI'])->name('api.teacher.subjects.results.download_excel_template');

// Teacher Session Attendance endpoints
Route::get('/teacher/subjects/{classSubjectID}/session-attendance', [\App\Http\Controllers\TeachersController::class, 'getSessionAttendanceDataAPI'])->name('api.teacher.subjects.session_attendance');

// Teacher Exam Attendance endpoints
Route::get('/teacher/subjects/{classSubjectID}/exam-attendance', [\App\Http\Controllers\TeachersController::class, 'getExamAttendanceDataAPI'])->name('api.teacher.subjects.exam_attendance');

// Teacher My Sessions endpoints
Route::get('/teacher/my-sessions', [\App\Http\Controllers\TeachersController::class, 'getTeacherMySessionsAPI'])->name('api.teacher.my_sessions');
Route::get('/teacher/my-sessions/students', [\App\Http\Controllers\TeachersController::class, 'getSessionStudentsAPI'])->name('api.teacher.my_sessions.students');
Route::get('/teacher/my-sessions/attendance', [\App\Http\Controllers\TeachersController::class, 'getSessionAttendanceForUpdateAPI'])->name('api.teacher.my_sessions.attendance');
Route::post('/teacher/my-sessions/attendance', [\App\Http\Controllers\TeachersController::class, 'collectSessionAttendanceAPI'])->name('api.teacher.my_sessions.attendance.save');
Route::post('/teacher/my-sessions/tasks', [\App\Http\Controllers\TeachersController::class, 'assignSessionTaskAPI'])->name('api.teacher.my_sessions.tasks.assign');

// Accommodation management (admin web session)
Route::middleware('web')->group(function () {
    Route::get('/accommodation/blocks', [AccomodationController::class, 'apiBlocksIndex']);
    Route::post('/accommodation/blocks', [AccomodationController::class, 'apiBlocksStore']);
    Route::put('/accommodation/blocks', [AccomodationController::class, 'apiBlocksUpdate']);
    Route::delete('/accommodation/blocks/{blockID}', [AccomodationController::class, 'apiBlocksDestroy']);

    Route::get('/accommodation/rooms', [AccomodationController::class, 'apiRoomsIndex']);
    Route::post('/accommodation/rooms', [AccomodationController::class, 'apiRoomsStore']);
    Route::put('/accommodation/rooms', [AccomodationController::class, 'apiRoomsUpdate']);
    Route::delete('/accommodation/rooms/{roomID}', [AccomodationController::class, 'apiRoomsDestroy']);

    Route::get('/accommodation/beds', [AccomodationController::class, 'apiBedsIndex']);
    Route::post('/accommodation/beds', [AccomodationController::class, 'apiBedsStore']);
    Route::put('/accommodation/beds', [AccomodationController::class, 'apiBedsUpdate']);
    Route::delete('/accommodation/beds/{bedID}', [AccomodationController::class, 'apiBedsDestroy']);
});


