# Developer API Integration Guide - Attendance System

## Overview

This guide provides everything you need to integrate your application with the Attendance System API. The API allows external systems to:

- **Register users** with just `id` and `name` (email and password auto-generated)
- **Manage users** (get, update, delete)
- **Retrieve attendance records** 
- **Receive real-time attendance data** via webhooks

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

Only `id` and `name` are required.

**⚠️ Use the FULL URL with your server address:**

```bash
POST https://YOUR-SERVER-URL.com/api/v1/users/register
Content-Type: application/json

{
    "id": "1001",
    "name": "John Doe"
}
```

**Replace `YOUR-SERVER-URL.com` with your actual server domain.**

**Response:**
```json
{
    "success": true,
    "message": "User created successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "enroll_id": "1001",
        "registered_on_device": false
    }
}
```

### Step 2: Configure Webhook

```bash
POST /api/v1/webhook/configure
Content-Type: application/json

{
    "webhook_url": "https://your-app.com/api/attendance/webhook"
}
```

### Step 3: Receive Attendance Webhooks

When users scan on the device, your webhook will automatically receive attendance data.

---

## API Base URL

**⚠️ Important: You MUST use the FULL URL with your server address.**

```
https://YOUR-SERVER-URL.com/api/v1
```

**Replace `YOUR-SERVER-URL.com` with the actual server domain provided by the system administrator.**

**Example:**
- If your server is `attendance.company.com`, your base URL is:
  ```
  https://attendance.company.com/api/v1
  ```

**All endpoints must use this full base URL:**
- ✅ `https://YOUR-SERVER-URL.com/api/v1/users/register`
- ✅ `https://YOUR-SERVER-URL.com/api/v1/users/enroll/1001`
- ❌ `/api/v1/users/register` (incomplete - missing server URL)

---

## Authentication

Currently, the API is open (no authentication required). For production environments, API key authentication may be enabled. Contact the system administrator for API credentials if required.

---

## User Management API

### 1. Register User

Register a new user with simplified data. Only `id` and `name` are required.

**Full URL:** `POST https://YOUR-SERVER-URL.com/api/v1/users/register`

**⚠️ Replace `YOUR-SERVER-URL.com` with your actual server domain.**

**Request Body:**
```json
{
    "id": "1001",              // Required: Enroll ID from your system (numeric, 1-9 digits)
    "name": "John Doe",         // Required: User's full name
    "auto_register_device": true,  // Optional: Auto-register to biometric device (default: true)
    "device_ip": "192.168.100.108", // Optional: Device IP (uses config default if not provided)
    "device_port": 4370            // Optional: Device port (uses config default if not provided)
}
```

**Note:** 
- `id` must be unique and contain only numbers (1-9 digits)
- Email is auto-generated as `user_{id}@attendance.local` if not provided
- Password is auto-generated securely if not provided

**Response (201 Created):**
```json
{
    "success": true,
    "message": "User created and registered to device successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "enroll_id": "1001",
        "registered_on_device": true,
        "device_registered_at": "2025-12-01 08:00:00"
    }
}
```

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "id": ["The id has already been taken."]
    }
}
```

---

### 2. Get User by ID

Get user information by internal system ID.

**Endpoint:** `GET /api/v1/users/{id}`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "enroll_id": "1001",
        "registered_on_device": true,
        "device_registered_at": "2025-12-01 08:00:00",
        "attendances_count": 15,
        "created_at": "2025-12-01 08:00:00"
    }
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "User not found"
}
```

---

### 3. Get User by Enroll ID

Get user information by enroll ID (the ID from your external system).

**Endpoint:** `GET /api/v1/users/enroll/{enrollId}`

**Example:** `GET /api/v1/users/enroll/1001`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "enroll_id": "1001",
        "registered_on_device": true,
        "device_registered_at": "2025-12-01 08:00:00",
        "attendances_count": 15,
        "created_at": "2025-12-01 08:00:00"
    }
}
```

---

### 4. List Users

Get a list of all users with optional filtering.

**Endpoint:** `GET /api/v1/users`

**Query Parameters:**
- `registered` (optional): Filter by device registration status (`true` or `false`)
- `search` (optional): Search by name or enroll_id
- `per_page` (optional): Results per page (default: 50)

**Examples:**
```
GET /api/v1/users
GET /api/v1/users?registered=true
GET /api/v1/users?search=John&per_page=20
GET /api/v1/users?registered=false&search=1001
```

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "enroll_id": "1001",
            "registered_on_device": true,
            "attendances_count": 15
        },
        {
            "id": 2,
            "name": "Jane Smith",
            "enroll_id": "1002",
            "registered_on_device": false,
            "attendances_count": 0
        }
    ],
    "pagination": {
        "current_page": 1,
        "total": 2,
        "per_page": 50,
        "last_page": 1
    }
}
```

---

### 5. Update User

Update user information.

**Endpoint:** `PUT /api/v1/users/{id}`

**Request Body (all fields optional):**
```json
{
    "name": "John Updated",
    "enroll_id": "1001"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "User updated successfully",
    "data": {
        "id": 1,
        "name": "John Updated",
        "enroll_id": "1001"
    }
}
```

---

### 6. Delete User

Delete a user from the system.

**Endpoint:** `DELETE /api/v1/users/{id}`

**Response (200 OK):**
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

---

### 7. Register User to Device

Manually register a user to the biometric device.

**Endpoint:** `POST /api/v1/users/{id}/register-device`

**Request Body:**
```json
{
    "device_ip": "192.168.100.108",  // Optional: Uses config default if not provided
    "device_port": 4370              // Optional: Uses config default if not provided
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "User registered to device successfully",
    "data": {
        "id": 1,
        "enroll_id": "1001",
        "registered_on_device": true
    }
}
```

---

## Attendance API

### 8. Get Attendance Records

Get attendance records with optional filtering.

**Endpoint:** `GET /api/v1/attendances`

**Query Parameters:**
- `date` (optional): Filter by specific date (format: `YYYY-MM-DD`)
- `date_from` (optional): Start date for range (format: `YYYY-MM-DD`)
- `date_to` (optional): End date for range (format: `YYYY-MM-DD`)
- `user_id` (optional): Filter by user ID
- `enroll_id` (optional): Filter by enroll ID
- `per_page` (optional): Results per page (default: 50)

**Examples:**
```
GET /api/v1/attendances
GET /api/v1/attendances?date=2025-12-01
GET /api/v1/attendances?date_from=2025-12-01&date_to=2025-12-31
GET /api/v1/attendances?enroll_id=1001&per_page=20
```

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user": {
                "id": 1,
                "name": "John Doe",
                "enroll_id": "1001"
            },
            "attendance_date": "2025-12-01",
            "check_in_time": "2025-12-01 08:00:00",
            "check_out_time": "2025-12-01 17:00:00",
            "status": 1,
            "verify_mode": "Fingerprint",
            "device_ip": "192.168.100.108"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total": 1,
        "per_page": 50,
        "last_page": 1
    }
}
```

---

### 9. Get Attendance by ID

Get a specific attendance record by ID.

**Endpoint:** `GET /api/v1/attendances/{id}`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user": {
            "id": 1,
            "name": "John Doe",
            "enroll_id": "1001"
        },
        "attendance_date": "2025-12-01",
        "check_in_time": "2025-12-01 08:00:00",
        "check_out_time": "2025-12-01 17:00:00",
        "status": 1,
        "verify_mode": "Fingerprint",
        "device_ip": "192.168.100.108"
    }
}
```

---

### 10. Get Daily Attendance Summary

Get attendance summary for a specific date.

**Endpoint:** `GET /api/v1/attendances/daily/{date}`

**Example:** `GET /api/v1/attendances/daily/2025-12-01`

**Response (200 OK):**
```json
{
    "success": true,
    "date": "2025-12-01",
    "data": [
        {
            "user": {
                "id": 1,
                "name": "John Doe",
                "enroll_id": "1001"
            },
            "date": "2025-12-01",
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

### 11. Configure Webhook

Set up your webhook URL to receive real-time attendance notifications.

**Endpoint:** `POST /api/v1/webhook/configure`

**Request Body:**
```json
{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": true,  // Optional: Send only user ID (default: false)
    "api_key": "your-secret-key"  // Optional: For webhook authentication
}
```

**Note:** Set `minimal_payload: true` to receive only the user ID. See `MINIMAL_PAYLOAD_DOCUMENTATION.md` for details.

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Webhook configured successfully",
    "data": {
        "webhook_url": "https://your-app.com/api/attendance/webhook",
        "configured_at": "2025-12-01 08:00:00"
    }
}
```

---

### 12. Get Webhook Configuration

Get the current webhook configuration.

**Endpoint:** `GET /api/v1/webhook/config`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "webhook_url": "https://your-app.com/api/attendance/webhook",
        "has_api_key": true,
        "minimal_payload": true,
        "configured": true
    }
}
```

---

### 13. Test Webhook

Test the webhook connection by sending a test request.

**Endpoint:** `POST /api/v1/webhook/test`

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Test webhook sent successfully"
}
```

---

## Webhook Payload

When a user scans on the device, your webhook endpoint will receive a POST request with attendance data.

**Note:** If you configured `minimal_payload: true`, you'll receive only the user ID. See `MINIMAL_PAYLOAD_DOCUMENTATION.md` for details.

### Full Payload (Default)

When `minimal_payload: false` or not set, you receive:

```json
{
    "event": "attendance.created",
    "data": {
        "id": 123,
        "user_id": 1,
        "enroll_id": "1001",
        "user_name": "John Doe",
        "attendance_date": "2025-12-01",
        "check_in_time": "2025-12-01 08:00:00",
        "check_out_time": "2025-12-01 17:00:00",
        "status": 1,
        "verify_mode": "Fingerprint",
        "device_ip": "192.168.100.108",
        "timestamp": "2025-12-01 17:00:00"
    }
}
```

### Minimal Payload (minimal_payload: true)

When `minimal_payload: true` is configured, you receive only:

```json
{
    "event": "attendance.created",
    "data": {
        "id": "87"
    }
}
```

**Only the user ID (enroll_id) is sent** - no other details.

See **`MINIMAL_PAYLOAD_DOCUMENTATION.md`** for complete documentation on minimal payload mode.

### Webhook Endpoint Requirements

Your webhook endpoint must:
- ✅ Accept **POST requests**
- ✅ Return **200 status code** (to acknowledge receipt)
- ✅ Be **publicly accessible** (not localhost)
- ✅ Use **HTTPS** in production
- ✅ Process data **asynchronously** (return quickly)

### Example Webhook Handler

**Laravel:**
```php
Route::post('/api/attendance/webhook', [AttendanceController::class, 'handleWebhook']);

public function handleWebhook(Request $request)
{
    $data = $request->input('data');
    
    // Process attendance
    // $data['enroll_id'] - Your user ID
    // $data['check_in_time']
    // $data['check_out_time']
    
    return response()->json(['success' => true], 200);
}
```

**Node.js/Express:**
```javascript
app.post('/api/attendance/webhook', (req, res) => {
    const data = req.body.data;
    
    // Process attendance
    console.log('Attendance received:', data);
    
    res.status(200).json({ success: true });
});
```

---

## Error Handling

### HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

### Error Response Format

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field_name": ["Error message 1", "Error message 2"]
    }
}
```

### Common Errors

**Duplicate ID:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "id": ["The id has already been taken."]
    }
}
```

**Invalid ID Format:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "id": ["ID must contain only numbers"]
    }
}
```

**User Not Found:**
```json
{
    "success": false,
    "message": "User not found"
}
```

---

## Code Examples

### PHP (Laravel)

```php
use Illuminate\Support\Facades\Http;

// ⚠️ Replace YOUR-SERVER-URL.com with your actual server domain
$baseUrl = 'https://YOUR-SERVER-URL.com/api/v1';

// Register User
$response = Http::post($baseUrl . '/users/register', [
    'id' => '1001',
    'name' => 'John Doe',
    'auto_register_device' => true,
]);

$user = $response->json()['data'];

// Get User by Enroll ID
$user = Http::get($baseUrl . "/users/enroll/1001")
    ->json()['data'];

// Get Attendances
$attendances = Http::get($baseUrl . '/attendances', [
    'date' => '2025-12-01',
    'enroll_id' => '1001',
])->json()['data'];

// Configure Webhook
Http::post($baseUrl . '/webhook/configure', [
    'webhook_url' => 'https://your-app.com/api/attendance/webhook',
]);
```

### PHP (cURL)

```php
// ⚠️ Replace YOUR-SERVER-URL.com with your actual server domain
$baseUrl = 'https://YOUR-SERVER-URL.com/api/v1';

// Register User
$ch = curl_init($baseUrl . '/users/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'id' => '1001',
    'name' => 'John Doe'
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
```

### JavaScript (Fetch API)

```javascript
// ⚠️ Replace YOUR-SERVER-URL.com with your actual server domain
const baseUrl = 'https://YOUR-SERVER-URL.com/api/v1';

// Register User
const response = await fetch(`${baseUrl}/users/register`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        id: '1001',
        name: 'John Doe'
    })
});

const data = await response.json();

// Get User by Enroll ID
const userResponse = await fetch(`${baseUrl}/users/enroll/1001`);
const user = await userResponse.json();

// Get Attendances
const attendancesResponse = await fetch(
    `${baseUrl}/attendances?date=2025-12-01&enroll_id=1001`
);
const attendances = await attendancesResponse.json();
```

### Python (Requests)

```python
import requests

# ⚠️ Replace YOUR-SERVER-URL.com with your actual server domain
base_url = 'https://YOUR-SERVER-URL.com/api/v1'

# Register User
response = requests.post(
    f'{base_url}/users/register',
    json={
        'id': '1001',
        'name': 'John Doe'
    },
    headers={
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
)

data = response.json()

# Get User by Enroll ID
user_response = requests.get(f'{base_url}/users/enroll/1001')
user = user_response.json()

# Get Attendances
attendances_response = requests.get(
    f'{base_url}/attendances',
    params={
        'date': '2025-12-01',
        'enroll_id': '1001'
    }
)
attendances = attendances_response.json()
```

### cURL

```bash
# ⚠️ Replace YOUR-SERVER-URL.com with your actual server domain
BASE_URL="https://YOUR-SERVER-URL.com/api/v1"

# Register User
curl -X POST ${BASE_URL}/users/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": "1001",
    "name": "John Doe"
  }'

# Get User by Enroll ID
curl -X GET ${BASE_URL}/users/enroll/1001 \
  -H "Accept: application/json"

# Get Attendances
curl -X GET "${BASE_URL}/attendances?date=2025-12-01&enroll_id=1001" \
  -H "Accept: application/json"
```

---

## Testing

### Test Script

A comprehensive test script is available to test all endpoints:

```bash
# Run PHP test script
php test-api.php

# With custom base URL
php test-api.php http://127.0.0.1:8000/api/v1
```

### Manual Testing with Postman

See `POSTMAN_COLLECTION_GUIDE.md` for detailed Postman setup and testing instructions.

### Quick Test Sequence

1. **Register a user:**
   ```bash
   POST /api/v1/users/register
   {
       "id": "9999",
       "name": "Test User"
   }
   ```

2. **Get the user:**
   ```bash
   GET /api/v1/users/enroll/9999
   ```

3. **Configure webhook:**
   ```bash
   POST /api/v1/webhook/configure
   {
       "webhook_url": "https://webhook.site/your-unique-id"
   }
   ```

4. **Test webhook:**
   ```bash
   POST /api/v1/webhook/test
   ```

---

## API Endpoints Summary

| # | Method | Endpoint | Description |
|---|--------|----------|-------------|
| 1 | POST | `/users/register` | Register user (only id and name required) |
| 2 | GET | `/users/{id}` | Get user by internal ID |
| 3 | GET | `/users/enroll/{enrollId}` | Get user by enroll ID |
| 4 | GET | `/users` | List users (with filters) |
| 5 | PUT | `/users/{id}` | Update user |
| 6 | DELETE | `/users/{id}` | Delete user |
| 7 | POST | `/users/{id}/register-device` | Register user to device |
| 8 | GET | `/attendances` | Get attendance records (with filters) |
| 9 | GET | `/attendances/{id}` | Get attendance by ID |
| 10 | GET | `/attendances/daily/{date}` | Get daily attendance summary |
| 11 | POST | `/webhook/configure` | Configure webhook URL |
| 12 | GET | `/webhook/config` | Get webhook configuration |
| 13 | POST | `/webhook/test` | Test webhook connection |

---

## Best Practices

1. **Always use HTTPS** in production
2. **Handle errors gracefully** - Check response status codes
3. **Implement retry logic** for failed requests
4. **Process webhooks asynchronously** - Return 200 quickly
5. **Validate data** before sending to API
6. **Use unique IDs** - Ensure enroll IDs are unique
7. **Monitor webhook delivery** - Log webhook receipts
8. **Test thoroughly** - Use test scripts before production

---

## Support

For issues or questions:
1. Check this documentation
2. Review error messages in API responses
3. Test with the provided test scripts
4. Contact the system administrator

---

## Quick Reference

### Simplified Registration (Required Fields Only)

```json
{
    "id": "1001",      // Required: Your user ID (numeric, 1-9 digits)
    "name": "John Doe" // Required: User's name
}
```

### Headers (All Requests)

```
Content-Type: application/json
Accept: application/json
```

### Base URL

**⚠️ Replace `YOUR-SERVER-URL.com` with your actual server domain.**

```
https://YOUR-SERVER-URL.com/api/v1
```

**Example:**
- If your server is `attendance.company.com`:
  ```
  https://attendance.company.com/api/v1
  ```

---

**Ready to integrate!** Start with the simplified registration endpoint to create your first user.

