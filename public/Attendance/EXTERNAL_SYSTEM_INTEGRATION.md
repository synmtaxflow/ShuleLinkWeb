# External System Integration Guide

## Overview

This guide explains how to integrate another Laravel system (or any system) with the ZKTeco Attendance System through REST API. The integration allows:

1. **User Registration**: Register users from external system and automatically register them to the ZKTeco device
2. **Real-time Attendance**: Receive attendance data via webhooks when users scan on the device

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [API Authentication](#api-authentication)
3. [User Management API](#user-management-api)
4. [Webhook Configuration](#webhook-configuration)
5. [Receiving Attendance Webhooks](#receiving-attendance-webhooks)
6. [Complete Integration Example](#complete-integration-example)
7. [API Reference](#api-reference)
8. [Troubleshooting](#troubleshooting)

---

## Quick Start

### Step 1: Register a User from External System

```php
// In your external Laravel system
use Illuminate\Support\Facades\Http;

$response = Http::post('http://attendance-system.com/api/v1/users/register', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123',
    'enroll_id' => '1001',
    'auto_register_device' => true, // Automatically register to device
]);

$data = $response->json();
if ($data['success']) {
    echo "User created and registered to device!";
}
```

### Step 2: Configure Webhook URL

```php
// Configure where attendance data should be sent
$response = Http::post('http://attendance-system.com/api/v1/webhook/configure', [
    'webhook_url' => 'https://your-system.com/api/attendance/webhook',
    'api_key' => 'your-secret-api-key', // Optional
]);
```

### Step 3: Receive Attendance Webhooks

```php
// In your external system - routes/api.php
Route::post('/attendance/webhook', [AttendanceWebhookController::class, 'handle']);

// In your controller
public function handle(Request $request)
{
    $data = $request->all();
    
    if ($data['event'] === 'attendance.created') {
        $attendance = $data['data'];
        
        // Process attendance
        // $attendance['user_id']
        // $attendance['enroll_id']
        // $attendance['check_in_time']
        // $attendance['check_out_time']
        // etc.
    }
    
    return response()->json(['success' => true]);
}
```

**That's it!** Now when users scan on the device, your system will receive webhooks automatically.

---

## API Authentication

Currently, the API is open (no authentication required). For production, you should add API key authentication.

### Option 1: Add API Key Middleware (Recommended)

```php
// In routes/api.php of attendance system
Route::middleware(['api', 'api.key'])->group(function () {
    // Your API routes
});

// Create middleware: app/Http/Middleware/ApiKeyMiddleware.php
public function handle($request, Closure $next)
{
    $apiKey = $request->header('X-API-Key');
    
    if ($apiKey !== config('app.api_key')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    return $next($request);
}
```

### Option 2: Use Laravel Sanctum

```php
// In routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    // Your API routes
});
```

---

## User Management API

### Register User

**Endpoint:** `POST /api/v1/users/register`

**Request:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "enroll_id": "1001",
    "auto_register_device": true,
    "device_ip": "192.168.100.108",
    "device_port": 4370
}
```

**Response:**
```json
{
    "success": true,
    "message": "User created and registered to device successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "enroll_id": "1001",
        "registered_on_device": true,
        "device_registered_at": "2025-11-30 14:00:00"
    }
}
```

**PHP Example:**
```php
use Illuminate\Support\Facades\Http;

$response = Http::post('http://attendance-system.com/api/v1/users/register', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123',
    'enroll_id' => '1001',
    'auto_register_device' => true,
]);

if ($response->successful()) {
    $data = $response->json();
    $userId = $data['data']['id'];
    // User is now registered in attendance system and on device
}
```

**JavaScript Example:**
```javascript
fetch('http://attendance-system.com/api/v1/users/register', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
        enroll_id: '1001',
        auto_register_device: true
    })
})
.then(response => response.json())
.then(data => {
    console.log('User registered:', data.data);
});
```

### Get User

**Endpoint:** `GET /api/v1/users/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "enroll_id": "1001",
        "registered_on_device": true,
        "device_registered_at": "2025-11-30 14:00:00",
        "attendances_count": 5
    }
}
```

### Get User by Enroll ID

**Endpoint:** `GET /api/v1/users/enroll/{enrollId}`

**Example:**
```php
$response = Http::get('http://attendance-system.com/api/v1/users/enroll/1001');
$user = $response->json()['data'];
```

### List Users

**Endpoint:** `GET /api/v1/users`

**Query Parameters:**
- `registered` - Filter by registration status (true/false)
- `search` - Search by name, email, or enroll_id
- `per_page` - Results per page (default: 50)

**Example:**
```php
$response = Http::get('http://attendance-system.com/api/v1/users', [
    'registered' => 'true',
    'search' => 'John',
    'per_page' => 20
]);
```

### Update User

**Endpoint:** `PUT /api/v1/users/{id}`

**Request:**
```json
{
    "name": "John Updated",
    "email": "john.updated@example.com"
}
```

### Delete User

**Endpoint:** `DELETE /api/v1/users/{id}`

### Register User to Device (Manual)

**Endpoint:** `POST /api/v1/users/{id}/register-device`

**Request:**
```json
{
    "device_ip": "192.168.100.108",
    "device_port": 4370
}
```

---

## Webhook Configuration

### Configure Webhook URL

**Endpoint:** `POST /api/v1/webhook/configure`

**Request:**
```json
{
    "webhook_url": "https://your-system.com/api/attendance/webhook",
    "api_key": "your-secret-key"
}
```

**PHP Example:**
```php
$response = Http::post('http://attendance-system.com/api/v1/webhook/configure', [
    'webhook_url' => 'https://your-system.com/api/attendance/webhook',
    'api_key' => 'your-secret-key',
]);
```

### Get Webhook Configuration

**Endpoint:** `GET /api/v1/webhook/config`

**Response:**
```json
{
    "success": true,
    "data": {
        "webhook_url": "https://your-system.com/api/attendance/webhook",
        "has_api_key": true,
        "configured": true
    }
}
```

### Test Webhook

**Endpoint:** `POST /api/v1/webhook/test`

Tests if the webhook URL is reachable and working.

---

## Receiving Attendance Webhooks

### Webhook Payload

When a user scans on the device, your system will receive a POST request to your configured webhook URL with the following payload:

```json
{
    "event": "attendance.created",
    "data": {
        "id": 123,
        "user_id": 1,
        "enroll_id": "1001",
        "user_name": "John Doe",
        "user_email": "john@example.com",
        "attendance_date": "2025-11-30",
        "check_in_time": "2025-11-30 08:00:00",
        "check_out_time": "2025-11-30 17:00:00",
        "status": 1,
        "verify_mode": "Fingerprint",
        "device_ip": "192.168.100.108",
        "timestamp": "2025-11-30 17:00:00"
    }
}
```

### Webhook Events

- **`attendance.created`**: Fired when:
  - User checks in (first scan of the day)
  - User checks out (second scan of the day)
  - Attendance record is updated (check-out time added)

### Laravel Controller Example

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify API key if configured
        $apiKey = $request->header('Authorization');
        if ($apiKey !== 'Bearer your-secret-key') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'attendance.created') {
            // Process attendance
            $this->processAttendance($data);
        }

        return response()->json(['success' => true]);
    }

    private function processAttendance($data)
    {
        // Find or create user in your system
        $user = \App\Models\User::firstOrCreate(
            ['external_user_id' => $data['user_id']],
            [
                'name' => $data['user_name'],
                'email' => $data['user_email'],
            ]
        );

        // Create or update attendance record
        \App\Models\Attendance::updateOrCreate(
            [
                'external_attendance_id' => $data['id'],
                'user_id' => $user->id,
                'attendance_date' => $data['attendance_date'],
            ],
            [
                'check_in_time' => $data['check_in_time'],
                'check_out_time' => $data['check_out_time'],
                'status' => $data['status'],
                'device_ip' => $data['device_ip'],
            ]
        );

        Log::info("Attendance processed: User {$data['user_name']} - {$data['attendance_date']}");
    }
}
```

### Route Setup

```php
// routes/api.php
Route::post('/attendance/webhook', [AttendanceWebhookController::class, 'handle']);
```

---

## Complete Integration Example

### External System Setup

#### 1. Create Migration

```php
// database/migrations/xxxx_create_external_attendances_table.php
Schema::create('external_attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('external_user_id');
    $table->string('external_attendance_id');
    $table->date('attendance_date');
    $table->timestamp('check_in_time')->nullable();
    $table->timestamp('check_out_time')->nullable();
    $table->string('status');
    $table->string('device_ip')->nullable();
    $table->timestamps();
    
    $table->unique(['external_attendance_id']);
});
```

#### 2. Create Model

```php
// app/Models/ExternalAttendance.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalAttendance extends Model
{
    protected $fillable = [
        'user_id',
        'external_user_id',
        'external_attendance_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'device_ip',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];
}
```

#### 3. Create Service

```php
// app/Services/AttendanceIntegrationService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceIntegrationService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('attendance.api_url');
        $this->apiKey = config('attendance.api_key');
    }

    /**
     * Register user to attendance system
     */
    public function registerUser($name, $email, $password, $enrollId)
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v1/users/register", [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'enroll_id' => $enrollId,
            'auto_register_device' => true,
        ]);

        if ($response->successful()) {
            return $response->json()['data'];
        }

        throw new \Exception('Failed to register user: ' . $response->body());
    }

    /**
     * Configure webhook
     */
    public function configureWebhook($webhookUrl)
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v1/webhook/configure", [
            'webhook_url' => $webhookUrl,
            'api_key' => $this->apiKey,
        ]);

        return $response->successful();
    }
}
```

#### 4. Create Webhook Controller

```php
// app/Http/Controllers/AttendanceWebhookController.php
namespace App\Http\Controllers;

use App\Models\ExternalAttendance;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify API key
        $apiKey = $request->header('Authorization');
        if ($apiKey !== 'Bearer ' . config('attendance.webhook_api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'attendance.created') {
            $this->processAttendance($data);
        }

        return response()->json(['success' => true]);
    }

    private function processAttendance($data)
    {
        // Find user by external_user_id
        $user = User::where('external_user_id', $data['user_id'])->first();

        if (!$user) {
            // Create user if not exists
            $user = User::create([
                'external_user_id' => $data['user_id'],
                'name' => $data['user_name'],
                'email' => $data['user_email'],
            ]);
        }

        // Create or update attendance
        ExternalAttendance::updateOrCreate(
            [
                'external_attendance_id' => $data['id'],
            ],
            [
                'user_id' => $user->id,
                'external_user_id' => $data['user_id'],
                'attendance_date' => $data['attendance_date'],
                'check_in_time' => $data['check_in_time'],
                'check_out_time' => $data['check_out_time'],
                'status' => $data['status'],
                'device_ip' => $data['device_ip'],
            ]
        );
    }
}
```

#### 5. Configure Routes

```php
// routes/api.php
Route::post('/attendance/webhook', [AttendanceWebhookController::class, 'handle']);
```

#### 6. Configuration

```php
// config/attendance.php
return [
    'api_url' => env('ATTENDANCE_API_URL', 'http://attendance-system.com'),
    'api_key' => env('ATTENDANCE_API_KEY'),
    'webhook_api_key' => env('ATTENDANCE_WEBHOOK_API_KEY'),
];
```

```env
# .env
ATTENDANCE_API_URL=http://attendance-system.com
ATTENDANCE_API_KEY=your-api-key
ATTENDANCE_WEBHOOK_API_KEY=your-webhook-key
```

#### 7. Usage

```php
// Register a user
$service = new \App\Services\AttendanceIntegrationService();

$user = $service->registerUser(
    'John Doe',
    'john@example.com',
    'password123',
    '1001'
);

// Configure webhook (one time setup)
$service->configureWebhook('https://your-system.com/api/attendance/webhook');
```

---

## API Reference

### Base URL
```
http://attendance-system.com/api/v1
```

### User Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/users/register` | Register new user and optionally register to device |
| GET | `/users/{id}` | Get user by ID |
| GET | `/users/enroll/{enrollId}` | Get user by enroll ID |
| GET | `/users` | List all users |
| PUT | `/users/{id}` | Update user |
| DELETE | `/users/{id}` | Delete user |
| POST | `/users/{id}/register-device` | Register user to device |

### Attendance Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/attendances` | List attendance records |
| GET | `/attendances/{id}` | Get attendance by ID |
| GET | `/attendances/daily/{date}` | Get daily attendance summary |

### Webhook Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/webhook/configure` | Configure webhook URL |
| GET | `/webhook/config` | Get webhook configuration |
| POST | `/webhook/test` | Test webhook connection |

---

## Troubleshooting

### Webhook Not Receiving Data

1. **Check webhook URL is configured:**
   ```php
   $response = Http::get('http://attendance-system.com/api/v1/webhook/config');
   ```

2. **Test webhook:**
   ```php
   $response = Http::post('http://attendance-system.com/api/v1/webhook/test');
   ```

3. **Check logs:**
   - Attendance system: `storage/logs/laravel.log`
   - Your system: Check webhook endpoint logs

4. **Verify webhook endpoint is accessible:**
   - Must be publicly accessible (not localhost)
   - Must accept POST requests
   - Must return 200 status code

### User Registration Fails

1. **Check device connection:**
   - Verify device IP and port
   - Test device connection manually

2. **Check enroll_id:**
   - Must be numeric
   - Must be unique
   - Range: 1-65535

3. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Webhook Timeout

If webhook requests timeout:

1. **Increase timeout in WebhookController:**
   ```php
   curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Increase from 10 to 30
   ```

2. **Process webhook asynchronously:**
   - Use Laravel queues
   - Return 200 immediately, process in background

---

## Best Practices

1. **Error Handling**: Always handle API errors gracefully
2. **Retry Logic**: Implement retry logic for failed webhook deliveries
3. **Idempotency**: Make webhook endpoints idempotent (handle duplicates)
4. **Logging**: Log all webhook requests for debugging
5. **Security**: Use API keys for authentication
6. **Validation**: Validate all incoming webhook data
7. **Queue Processing**: Process webhooks in background queues for better performance

---

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review API documentation
- Test endpoints using Postman or curl

---

**Last Updated:** November 30, 2025


