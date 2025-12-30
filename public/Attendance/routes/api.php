<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExternalApiController;
use App\Http\Controllers\Api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes for External System Integration
|--------------------------------------------------------------------------
|
| These routes are for integrating with external systems (like another Laravel app).
| They allow external systems to:
| - Register users and automatically register them to the device
| - Receive attendance webhooks when users scan
|
*/

// API Authentication Middleware (Optional - you can add API key authentication)
// Route::middleware(['api', 'auth:sanctum'])->group(function () {
Route::prefix('v1')->middleware('api')->group(function () {
    
    // External System User Registration
    // POST /api/v1/users/register
    // This endpoint allows external system to register a user and automatically register to device
    Route::post('/users/register', [ExternalApiController::class, 'registerUser'])->name('api.users.register');
    
    // Get user information
    Route::get('/users/{id}', [ExternalApiController::class, 'getUser'])->name('api.users.get');
    
    // Get user by enroll_id
    Route::get('/users/enroll/{enrollId}', [ExternalApiController::class, 'getUserByEnrollId'])->name('api.users.getByEnrollId');
    
    // List all users
    Route::get('/users', [ExternalApiController::class, 'listUsers'])->name('api.users.list');
    
    // Update user
    Route::put('/users/{id}', [ExternalApiController::class, 'updateUser'])->name('api.users.update');
    
    // Delete user
    Route::delete('/users/{id}', [ExternalApiController::class, 'deleteUser'])->name('api.users.delete');
    
    // Register user to device (if not already registered)
    Route::post('/users/{id}/register-device', [ExternalApiController::class, 'registerUserToDevice'])->name('api.users.registerDevice');
    
    // Get attendance records
    Route::get('/attendances', [ExternalApiController::class, 'getAttendances'])->name('api.attendances.list');
    
    // Get attendance by ID
    Route::get('/attendances/{id}', [ExternalApiController::class, 'getAttendance'])->name('api.attendances.get');
    
    // Get daily attendance summary
    Route::get('/attendances/daily/{date}', [ExternalApiController::class, 'getDailyAttendance'])->name('api.attendances.daily');
    
    // Test webhook endpoint (for external system to test connectivity)
    Route::post('/webhook/test', [WebhookController::class, 'test'])->name('api.webhook.test');
    
    // Configure webhook URL (for external system to set its webhook endpoint)
    Route::post('/webhook/configure', [WebhookController::class, 'configure'])->name('api.webhook.configure');
    
    // Get webhook configuration
    Route::get('/webhook/config', [WebhookController::class, 'getConfig'])->name('api.webhook.config');
});

