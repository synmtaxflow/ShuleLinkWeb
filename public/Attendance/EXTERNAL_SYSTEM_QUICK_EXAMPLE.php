<?php

/**
 * Quick Integration Example for External Laravel System
 * 
 * This file shows how to integrate with the Attendance System from another Laravel application.
 * Copy and adapt these examples to your external system.
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceIntegrationExample
{
    private $baseUrl = 'http://attendance-system.com'; // Change to your attendance system URL
    private $apiKey = null; // Optional: Add API key if authentication is enabled

    /**
     * Example 1: Register a user and automatically register to device
     */
    public function registerUserExample()
    {
        $response = Http::post("{$this->baseUrl}/api/v1/users/register", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'enroll_id' => '1001',
            'auto_register_device' => true, // Automatically register to device
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $user = $data['data'];
            
            Log::info("User registered: {$user['name']} (ID: {$user['id']})");
            return $user;
        }

        throw new \Exception('Failed to register user: ' . $response->body());
    }

    /**
     * Example 2: Configure webhook URL (one-time setup)
     */
    public function configureWebhookExample()
    {
        $webhookUrl = 'https://your-system.com/api/attendance/webhook';
        
        $response = Http::post("{$this->baseUrl}/api/v1/webhook/configure", [
            'webhook_url' => $webhookUrl,
            'api_key' => 'your-secret-key', // Optional: for authentication
        ]);

        if ($response->successful()) {
            Log::info("Webhook configured: {$webhookUrl}");
            return true;
        }

        throw new \Exception('Failed to configure webhook: ' . $response->body());
    }

    /**
     * Example 3: Get user information
     */
    public function getUserExample($userId)
    {
        $response = Http::get("{$this->baseUrl}/api/v1/users/{$userId}");

        if ($response->successful()) {
            return $response->json()['data'];
        }

        return null;
    }

    /**
     * Example 4: Get attendance records
     */
    public function getAttendancesExample($date = null)
    {
        $url = "{$this->baseUrl}/api/v1/attendances";
        
        $params = [];
        if ($date) {
            $params['date'] = $date;
        }

        $response = Http::get($url, $params);

        if ($response->successful()) {
            return $response->json()['data'];
        }

        return [];
    }
}

/**
 * Example Webhook Controller for External System
 * 
 * Create this controller in your external system to receive attendance webhooks
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceWebhookControllerExample
{
    /**
     * Handle incoming attendance webhooks
     * 
     * Route: POST /api/attendance/webhook
     */
    public function handle(Request $request)
    {
        // Optional: Verify API key
        $apiKey = $request->header('Authorization');
        if ($apiKey !== 'Bearer your-secret-key') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info("Received webhook: {$event}", $data);

        if ($event === 'attendance.created') {
            $this->processAttendance($data);
        }

        // Always return success to acknowledge receipt
        return response()->json(['success' => true]);
    }

    /**
     * Process attendance data
     */
    private function processAttendance($data)
    {
        // Example: Save to your database
        \DB::table('external_attendances')->insert([
            'external_user_id' => $data['user_id'],
            'external_attendance_id' => $data['id'],
            'user_name' => $data['user_name'],
            'user_email' => $data['user_email'],
            'enroll_id' => $data['enroll_id'],
            'attendance_date' => $data['attendance_date'],
            'check_in_time' => $data['check_in_time'],
            'check_out_time' => $data['check_out_time'],
            'status' => $data['status'],
            'device_ip' => $data['device_ip'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Attendance processed: {$data['user_name']} - {$data['attendance_date']}");
    }
}

/**
 * Example Route Setup (in routes/api.php of external system)
 * 
 * Route::post('/attendance/webhook', [AttendanceWebhookController::class, 'handle']);
 */

/**
 * Example Usage in Controller
 */
class UserControllerExample
{
    public function createUser(Request $request)
    {
        // Create user in your system
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            // ... other fields
        ]);

        // Register user to attendance system
        $attendanceService = new \App\Services\AttendanceIntegrationExample();
        
        try {
            $attendanceUser = $attendanceService->registerUserExample();
            // Store external user ID for reference
            $user->update(['external_user_id' => $attendanceUser['id']]);
        } catch (\Exception $e) {
            Log::error("Failed to register user to attendance system: " . $e->getMessage());
            // Handle error (maybe queue for retry)
        }

        return response()->json(['success' => true, 'user' => $user]);
    }
}


