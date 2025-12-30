# Developer Integration Guide - ZKTeco Attendance System API

## Overview

This guide provides everything you need to integrate your application with the ZKTeco Attendance System. The integration allows you to:

- **Register users** and automatically register them to the biometric device
- **Receive real-time attendance data** via webhooks when users scan on the device

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [API Base URL](#api-base-url)
3. [Authentication](#authentication)
4. [User Management API](#user-management-api)
5. [Attendance API](#attendance-api)
6. [Webhook Integration](#webhook-integration)
7. [Error Handling](#error-handling)
8. [Code Examples](#code-examples)
9. [Testing](#testing)
10. [Support](#support)

---

## Quick Start

### Step 1: Register a User

```php
use Illuminate\Support\Facades\Http;

$response = Http::post('https://attendance-system.com/api/v1/users/register', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123',
    'enroll_id' => '1001',
    'auto_register_device' => true,
]);

$user = $response->json()['data'];
// User is now registered in the system AND on the biometric device!
```

### Step 2: Configure Webhook

```php
Http::post('https://attendance-system.com/api/v1/webhook/configure', [
    'webhook_url' => 'https://your-app.com/api/attendance/webhook',
    'api_key' => 'your-secret-key', // Optional
]);
```

### Step 3: Receive Attendance Webhooks

```php
// In your routes/api.php
Route::post('/attendance/webhook', [AttendanceController::class, 'handleWebhook']);

// In your controller
public function handleWebhook(Request $request)
{
    $data = $request->input('data');
    
    // Process attendance
    // $data['check_in_time']
    // $data['check_out_time']
    // etc.
    
    return response()->json(['success' => true]);
}
```

**That's it!** When users scan on the device, your app will receive webhooks automatically.

---

## API Base URL

```
Production: https://attendance-system.com/api/v1
Development: http://127.0.0.1:8000/api/v1
```

Replace `attendance-system.com` with the actual domain provided by the system administrator.

---

## Authentication

Currently, the API is open (no authentication required). For production environments, API key authentication may be enabled. Contact the system administrator for API credentials if required.

### If API Key Authentication is Enabled

```php
$response = Http::withHeaders([
    'X-API-Key' => 'your-api-key',
])->post('https://attendance-system.com/api/v1/users/register', [...]);
```

---

## User Management API

### Register User

Register a new user and optionally register them to the biometric device automatically.

**Endpoint:** `POST /api/v1/users/register`

**Request Body:**
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

**Parameters:**
- `name` (required, string, max 255): User's full name
- `email` (required, string, email, unique): User's email address
- `password` (required, string, min 8): User's password
- `enroll_id` (required, string, numeric, unique, 1-9 digits): Unique numeric ID for the device
- `auto_register_device` (optional, boolean, default: true): Automatically register user to device
- `device_ip` (optional, string): Device IP address (uses default if not provided)
- `device_port` (optional, integer): Device port (uses default if not provided)

**Response (Success - 201):**
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

**Response (Error - 422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "enroll_id": ["The enroll id has already been taken."]
    }
}
```

**PHP Example:**
```php
$response = Http::post('https://attendance-system.com/api/v1/users/register', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123',
    'enroll_id' => '1001',
    'auto_register_device' => true,
]);

if ($response->successful()) {
    $user = $response->json()['data'];
    echo "User registered: {$user['name']} (ID: {$user['id']})";
} else {
    $errors = $response->json()['errors'];
    // Handle errors
}
```

**JavaScript Example:**
```javascript
const response = await fetch('https://attendance-system.com/api/v1/users/register', {
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
});

const data = await response.json();
if (data.success) {
    console.log('User registered:', data.data);
} else {
    console.error('Errors:', data.errors);
}
```

**Python Example:**
```python
import requests

response = requests.post('https://attendance-system.com/api/v1/users/register', json={
    'name': 'John Doe',
    'email': 'john@example.com',
    'password': 'password123',
    'enroll_id': '1001',
    'auto_register_device': True
})

if response.status_code == 201:
    data = response.json()
    print(f"User registered: {data['data']['name']}")
else:
    errors = response.json()['errors']
    print(f"Errors: {errors}")
```

### Get User by ID

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
        "attendances_count": 5,
        "created_at": "2025-11-30 10:00:00"
    }
}
```

### Get User by Enroll ID

**Endpoint:** `GET /api/v1/users/enroll/{enrollId}`

**Example:**
```php
$response = Http::get('https://attendance-system.com/api/v1/users/enroll/1001');
$user = $response->json()['data'];
```

### List Users

**Endpoint:** `GET /api/v1/users`

**Query Parameters:**
- `registered` (optional): Filter by registration status (`true`/`false`)
- `search` (optional): Search by name, email, or enroll_id
- `per_page` (optional): Results per page (default: 50)

**Example:**
```php
$response = Http::get('https://attendance-system.com/api/v1/users', [
    'registered' => 'true',
    'search' => 'John',
    'per_page' => 20
]);

$users = $response->json()['data'];
$pagination = $response->json()['pagination'];
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "enroll_id": "1001",
            "registered_on_device": true,
            "attendances_count": 5
        }
    ],
    "pagination": {
        "current_page": 1,
        "total": 10,
        "per_page": 50,
        "last_page": 1
    }
}
```

### Update User

**Endpoint:** `PUT /api/v1/users/{id}`

**Request Body:**
```json
{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "password": "newpassword123"
}
```

**Note:** All fields are optional. Only include fields you want to update.

### Delete User

**Endpoint:** `DELETE /api/v1/users/{id}`

**Response:**
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

### Register User to Device (Manual)

**Endpoint:** `POST /api/v1/users/{id}/register-device`

Use this if you need to manually register a user to the device after creation.

**Request Body:**
```json
{
    "device_ip": "192.168.100.108",
    "device_port": 4370
}
```

---

## Attendance API

### Get Attendance Records

**Endpoint:** `GET /api/v1/attendances`

**Query Parameters:**
- `date` (optional): Filter by specific date (YYYY-MM-DD)
- `date_from` (optional): Start date for range (YYYY-MM-DD)
- `date_to` (optional): End date for range (YYYY-MM-DD)
- `user_id` (optional): Filter by user ID
- `enroll_id` (optional): Filter by enroll ID
- `per_page` (optional): Results per page (default: 50)

**Example:**
```php
$response = Http::get('https://attendance-system.com/api/v1/attendances', [
    'date' => '2025-11-30',
    'per_page' => 20
]);

$attendances = $response->json()['data'];
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "user": {
                "id": 1,
                "name": "John Doe",
                "enroll_id": "1001"
            },
            "attendance_date": "2025-11-30",
            "check_in_time": "2025-11-30 08:00:00",
            "check_out_time": "2025-11-30 17:00:00",
            "status": 1,
            "verify_mode": "Fingerprint",
            "device_ip": "192.168.100.108"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total": 50,
        "per_page": 50,
        "last_page": 1
    }
}
```

### Get Attendance by ID

**Endpoint:** `GET /api/v1/attendances/{id}`

### Get Daily Attendance Summary

**Endpoint:** `GET /api/v1/attendances/daily/{date}`

**Example:**
```php
$response = Http::get('https://attendance-system.com/api/v1/attendances/daily/2025-11-30');
$summary = $response->json()['data'];
```

**Response:**
```json
{
    "success": true,
    "date": "2025-11-30",
    "data": [
        {
            "user": {
                "id": 1,
                "name": "John Doe",
                "enroll_id": "1001"
            },
            "date": "2025-11-30",
            "check_in": "08:00:00",
            "check_out": "17:00:00",
            "duration": "09:00:00"
        }
    ],
    "total": 1
}
```

---

## Webhook Integration

### How Webhooks Work

1. **Configure your webhook URL** - Tell the system where to send attendance data
2. **User scans on device** - Attendance is automatically created
3. **Webhook is sent** - Your endpoint receives the attendance data in real-time

### Configure Webhook URL

**Endpoint:** `POST /api/v1/webhook/configure`

**Request Body:**
```json
{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "api_key": "your-secret-key"
}
```

**Parameters:**
- `webhook_url` (required, URL): Your webhook endpoint URL
- `api_key` (optional, string): API key for authentication (sent in Authorization header)

**Response:**
```json
{
    "success": true,
    "message": "Webhook configured successfully",
    "data": {
        "webhook_url": "https://your-app.com/api/attendance/webhook",
        "configured_at": "2025-11-30 14:00:00"
    }
}
```

**Important:**
- Webhook URL must be **publicly accessible** (not localhost)
- Webhook URL must accept **POST requests**
- Webhook URL must return **200 status code** to acknowledge receipt
- Use **HTTPS** in production

### Get Webhook Configuration

**Endpoint:** `GET /api/v1/webhook/config`

**Response:**
```json
{
    "success": true,
    "data": {
        "webhook_url": "https://your-app.com/api/attendance/webhook",
        "has_api_key": true,
        "configured": true
    }
}
```

### Test Webhook

**Endpoint:** `POST /api/v1/webhook/test`

Tests if your webhook URL is reachable and working.

**Response:**
```json
{
    "success": true,
    "message": "Test webhook sent successfully"
}
```

### Webhook Payload

When a user scans on the device, your webhook endpoint will receive a POST request with the following payload:

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
  - Attendance record is updated

### Webhook Endpoint Requirements

Your webhook endpoint must:

1. **Accept POST requests**
2. **Return 200 status code** (to acknowledge receipt)
3. **Process data asynchronously** (return quickly, process in background)
4. **Handle duplicate requests** (webhooks may be retried)
5. **Validate data** (verify required fields)

### Laravel Webhook Controller Example

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Optional: Verify API key
        $apiKey = $request->header('Authorization');
        if ($apiKey !== 'Bearer your-secret-key') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        // Process in background (queue)
        dispatch(function() use ($data) {
            $this->processAttendance($data);
        })->afterResponse();

        // Return immediately to acknowledge receipt
        return response()->json(['success' => true]);
    }

    private function processAttendance($data)
    {
        // Find or create user
        $user = \App\Models\User::firstOrCreate(
            ['external_user_id' => $data['user_id']],
            [
                'name' => $data['user_name'],
                'email' => $data['user_email'],
            ]
        );

        // Create or update attendance
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

        Log::info("Attendance processed: {$data['user_name']} - {$data['attendance_date']}");
    }
}
```

**Route Setup:**
```php
// routes/api.php
Route::post('/attendance/webhook', [AttendanceWebhookController::class, 'handle']);
```

---

## Error Handling

### HTTP Status Codes

- **200 OK**: Request successful
- **201 Created**: Resource created successfully
- **400 Bad Request**: Invalid request
- **401 Unauthorized**: Authentication required
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation failed
- **500 Internal Server Error**: Server error

### Error Response Format

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field_name": ["Error message for field"]
    }
}
```

### Handling Errors

```php
$response = Http::post('https://attendance-system.com/api/v1/users/register', [...]);

if ($response->successful()) {
    $data = $response->json()['data'];
} else {
    if ($response->status() === 422) {
        $errors = $response->json()['errors'];
        // Handle validation errors
    } else {
        $message = $response->json()['message'];
        // Handle other errors
    }
}
```

---

## Code Examples

### Complete Integration Example (Laravel)

```php
<?php

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
            'api_key' => config('attendance.webhook_api_key'),
        ]);

        return $response->successful();
    }

    /**
     * Get attendance records
     */
    public function getAttendances($date = null)
    {
        $params = $date ? ['date' => $date] : [];
        
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->get("{$this->baseUrl}/api/v1/attendances", $params);

        if ($response->successful()) {
            return $response->json()['data'];
        }

        return [];
    }
}
```

### Usage

```php
$service = new AttendanceIntegrationService();

// Register user
$user = $service->registerUser(
    'John Doe',
    'john@example.com',
    'password123',
    '1001'
);

// Configure webhook (one-time setup)
$service->configureWebhook('https://your-app.com/api/attendance/webhook');

// Get today's attendance
$attendances = $service->getAttendances('2025-11-30');
```

---

## Testing

### Test User Registration

```bash
curl -X POST https://attendance-system.com/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "enroll_id": "9999",
    "auto_register_device": true
  }'
```

### Test Webhook Configuration

```bash
curl -X POST https://attendance-system.com/api/v1/webhook/configure \
  -H "Content-Type: application/json" \
  -d '{
    "webhook_url": "https://webhook.site/your-unique-id"
  }'
```

**Tip:** Use [webhook.site](https://webhook.site) to test webhooks without setting up a server.

### Test Webhook Delivery

```bash
curl -X POST https://attendance-system.com/api/v1/webhook/test
```

### Test Complete Flow

1. Register a user via API
2. Scan the user's fingerprint on the device
3. Check your webhook endpoint - you should receive the attendance data

---

## Support

### Documentation

- **Complete Integration Guide**: See `EXTERNAL_SYSTEM_INTEGRATION.md`
- **Quick Examples**: See `EXTERNAL_SYSTEM_QUICK_EXAMPLE.php`
- **API Reference**: This document

### Common Issues

**Webhook not receiving data:**
- Verify webhook URL is configured: `GET /api/v1/webhook/config`
- Test webhook: `POST /api/v1/webhook/test`
- Ensure webhook URL is publicly accessible (not localhost)
- Check webhook endpoint returns 200 status code

**User registration fails:**
- Verify `enroll_id` is numeric and unique
- Check `enroll_id` is within range (1-65535)
- Ensure device is connected and accessible

**API returns 500 error:**
- Contact system administrator
- Check system logs

### Contact

For API access, credentials, or support, contact the system administrator.

---

## API Reference Summary

### User Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/users/register` | Register new user |
| GET | `/api/v1/users/{id}` | Get user by ID |
| GET | `/api/v1/users/enroll/{enrollId}` | Get user by enroll ID |
| GET | `/api/v1/users` | List users |
| PUT | `/api/v1/users/{id}` | Update user |
| DELETE | `/api/v1/users/{id}` | Delete user |
| POST | `/api/v1/users/{id}/register-device` | Register user to device |

### Attendance Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/attendances` | List attendance records |
| GET | `/api/v1/attendances/{id}` | Get attendance by ID |
| GET | `/api/v1/attendances/daily/{date}` | Get daily summary |

### Webhook Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/webhook/configure` | Configure webhook URL |
| GET | `/api/v1/webhook/config` | Get webhook configuration |
| POST | `/api/v1/webhook/test` | Test webhook connection |

---

**Last Updated:** November 30, 2025  
**API Version:** 1.0.0


