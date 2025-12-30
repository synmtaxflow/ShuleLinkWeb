# Get Attendance Data - Step by Step Instructions

## Server URL
```
http://192.168.100.100:8000
```

## Base API URL
```
http://192.168.100.100:8000/api/v1
```

---

## ⚠️ REQUIRED RESPONSE FIELDS

**All attendance responses MUST include these fields (lines 41-46):**

1. ✅ `attendance_date` - Date of attendance (YYYY-MM-DD) - **ALWAYS RETURNED**
2. ✅ `check_in_time` - Check-in time (YYYY-MM-DD HH:MM:SS) - **ALWAYS RETURNED** (may be null)
3. ✅ `check_out_time` - Check-out time (YYYY-MM-DD HH:MM:SS) - **ALWAYS RETURNED** (may be null)
4. ✅ `status` - Status code (1 = Check In, 0 = Check Out) - **ALWAYS RETURNED**
5. ✅ `verify_mode` - Verification method (e.g., "Fingerprint") - **ALWAYS RETURNED**
6. ✅ `device_ip` - IP address of the biometric device - **ALWAYS RETURNED**

**These 6 fields are guaranteed to be present in every attendance response.**

---

## How to Get Attendance Data

### Method 1: Get All Attendance Records

**Full URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances
```

**Headers:**
```
Accept: application/json
```

**Example Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user": {
                "id": 1,
                "name": "MWAKABANJE",
                "enroll_id": "87"
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

**⚠️ IMPORTANT: All attendance responses MUST include these fields:**
- `attendance_date` - Always returned (format: YYYY-MM-DD)
- `check_in_time` - Always returned (format: YYYY-MM-DD HH:MM:SS, or null if not checked in)
- `check_out_time` - Always returned (format: YYYY-MM-DD HH:MM:SS, or null if not checked out)
- `status` - Always returned (1 = Check In, 0 = Check Out)
- `verify_mode` - Always returned (e.g., "Fingerprint")
- `device_ip` - Always returned (IP address of the device)

---

### Method 2: Get Attendance by Specific Date

**Full URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances?date=2025-12-01
```

**Headers:**
```
Accept: application/json
```

**Date Format:** `YYYY-MM-DD` (e.g., `2025-12-01`)

**Example:**
```
GET http://192.168.100.100:8000/api/v1/attendances?date=2025-12-01
```

---

### Method 3: Get Attendance by Date Range

**Full URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances?date_from=2025-12-01&date_to=2025-12-31
```

**Headers:**
```
Accept: application/json
```

**Query Parameters:**
- `date_from`: Start date (YYYY-MM-DD)
- `date_to`: End date (YYYY-MM-DD)

**Example:**
```
GET http://192.168.100.100:8000/api/v1/attendances?date_from=2025-12-01&date_to=2025-12-31
```

---

### Method 4: Get Attendance by User ID (Your External ID)

**Full URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances?enroll_id=87
```

**Headers:**
```
Accept: application/json
```

**Query Parameters:**
- `enroll_id`: Your user ID (the ID you sent when registering)

**Example:**
```
GET http://192.168.100.100:8000/api/v1/attendances?enroll_id=87
```

---

### Method 5: Get Daily Attendance Summary

**Full URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances/daily/2025-12-01
```

**Headers:**
```
Accept: application/json
```

**Date Format:** `YYYY-MM-DD` (replace `2025-12-01` with your date)

**Example Response:**
```json
{
    "success": true,
    "date": "2025-12-01",
    "data": [
        {
            "user": {
                "id": 1,
                "name": "MWAKABANJE",
                "enroll_id": "87"
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

### Method 6: Get Attendance by Internal User ID

**Full URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances?user_id=1
```

**Headers:**
```
Accept: application/json
```

**Query Parameters:**
- `user_id`: Internal system user ID

---

## Complete Examples

### Example 1: Get Today's Attendance

**URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances?date=2025-12-01
```

**cURL:**
```bash
curl -X GET "http://192.168.100.100:8000/api/v1/attendances?date=2025-12-01" \
  -H "Accept: application/json"
```

**PHP:**
```php
$response = Http::get('http://192.168.100.100:8000/api/v1/attendances', [
    'date' => '2025-12-01'
]);

$attendances = $response->json()['data'];
```

**JavaScript:**
```javascript
const response = await fetch(
    'http://192.168.100.100:8000/api/v1/attendances?date=2025-12-01',
    {
        headers: { 'Accept': 'application/json' }
    }
);

const data = await response.json();
const attendances = data.data;
```

**Python:**
```python
import requests

response = requests.get(
    'http://192.168.100.100:8000/api/v1/attendances',
    params={'date': '2025-12-01'},
    headers={'Accept': 'application/json'}
)

attendances = response.json()['data']
```

---

### Example 2: Get Attendance for Specific User

**URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances?enroll_id=87
```

**cURL:**
```bash
curl -X GET "http://192.168.100.100:8000/api/v1/attendances?enroll_id=87" \
  -H "Accept: application/json"
```

**PHP:**
```php
$response = Http::get('http://192.168.100.100:8000/api/v1/attendances', [
    'enroll_id' => '87'
]);

$attendances = $response->json()['data'];
```

**JavaScript:**
```javascript
const response = await fetch(
    'http://192.168.100.100:8000/api/v1/attendances?enroll_id=87',
    {
        headers: { 'Accept': 'application/json' }
    }
);

const data = await response.json();
const attendances = data.data;
```

---

### Example 3: Get Attendance for Date Range

**URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances?date_from=2025-12-01&date_to=2025-12-31
```

**cURL:**
```bash
curl -X GET "http://192.168.100.100:8000/api/v1/attendances?date_from=2025-12-01&date_to=2025-12-31" \
  -H "Accept: application/json"
```

**PHP:**
```php
$response = Http::get('http://192.168.100.100:8000/api/v1/attendances', [
    'date_from' => '2025-12-01',
    'date_to' => '2025-12-31'
]);

$attendances = $response->json()['data'];
```

---

### Example 4: Get Daily Summary

**URL:**
```
GET http://192.168.100.100:8000/api/v1/attendances/daily/2025-12-01
```

**cURL:**
```bash
curl -X GET "http://192.168.100.100:8000/api/v1/attendances/daily/2025-12-01" \
  -H "Accept: application/json"
```

**PHP:**
```php
$date = '2025-12-01';
$response = Http::get("http://192.168.100.100:8000/api/v1/attendances/daily/{$date}");

$summary = $response->json()['data'];
```

---

## Query Parameters Reference

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `date` | string | Filter by specific date (YYYY-MM-DD) | `?date=2025-12-01` |
| `date_from` | string | Start date for range (YYYY-MM-DD) | `?date_from=2025-12-01` |
| `date_to` | string | End date for range (YYYY-MM-DD) | `?date_to=2025-12-31` |
| `user_id` | integer | Filter by internal user ID | `?user_id=1` |
| `enroll_id` | string | Filter by your user ID | `?enroll_id=87` |
| `per_page` | integer | Results per page (default: 50) | `?per_page=20` |

---

## Combined Filters

You can combine multiple filters:

**Example: Get attendance for user 87 on specific date:**
```
GET http://192.168.100.100:8000/api/v1/attendances?enroll_id=87&date=2025-12-01
```

**Example: Get attendance for date range with pagination:**
```
GET http://192.168.100.100:8000/api/v1/attendances?date_from=2025-12-01&date_to=2025-12-31&per_page=20
```

---

## Response Structure

### Success Response (200 OK)

**⚠️ REQUIRED FIELDS - All attendance responses MUST include these fields:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user": {
                "id": 1,
                "name": "MWAKABANJE",
                "enroll_id": "87"
            },
            "attendance_date": "2025-12-01",        // ✅ REQUIRED - Always returned
            "check_in_time": "2025-12-01 08:00:00", // ✅ REQUIRED - Always returned (or null)
            "check_out_time": "2025-12-01 17:00:00", // ✅ REQUIRED - Always returned (or null)
            "status": 1,                            // ✅ REQUIRED - Always returned
            "verify_mode": "Fingerprint",            // ✅ REQUIRED - Always returned
            "device_ip": "192.168.100.108"          // ✅ REQUIRED - Always returned
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

**All fields marked with ✅ REQUIRED are guaranteed to be present in every response.**

### Field Descriptions

**All fields below are ALWAYS returned in every attendance response:**

| Field | Required | Description | Example |
|-------|----------|-------------|---------|
| `id` | ✅ Always | Attendance record ID | `1` |
| `user.id` | ✅ Always | Internal user ID | `1` |
| `user.name` | ✅ Always | User's name | `"MWAKABANJE"` |
| `user.enroll_id` | ✅ Always | **Your user ID** (the ID you sent when registering) | `"87"` |
| `attendance_date` | ✅ Always | Date of attendance (YYYY-MM-DD) | `"2025-12-01"` |
| `check_in_time` | ✅ Always | Check-in time (YYYY-MM-DD HH:MM:SS) or null | `"2025-12-01 08:00:00"` or `null` |
| `check_out_time` | ✅ Always | Check-out time (YYYY-MM-DD HH:MM:SS) or null | `"2025-12-01 17:00:00"` or `null` |
| `status` | ✅ Always | 1 = Check In, 0 = Check Out | `1` or `0` |
| `verify_mode` | ✅ Always | Verification method | `"Fingerprint"` |
| `device_ip` | ✅ Always | IP address of the biometric device | `"192.168.100.108"` |

**Note:** All these fields are **guaranteed** to be present in every attendance response. Some fields may be `null` if data is not available (e.g., `check_out_time` is `null` if user hasn't checked out yet).

---

## Quick Reference

### Most Common Requests

1. **Get all attendance:**
   ```
   GET http://192.168.100.100:8000/api/v1/attendances
   ```

2. **Get today's attendance:**
   ```
   GET http://192.168.100.100:8000/api/v1/attendances?date=2025-12-01
   ```

3. **Get attendance for specific user:**
   ```
   GET http://192.168.100.100:8000/api/v1/attendances?enroll_id=87
   ```

4. **Get daily summary:**
   ```
   GET http://192.168.100.100:8000/api/v1/attendances/daily/2025-12-01
   ```

---

## Testing in Postman

1. **Method:** Select `GET`
2. **URL:** `http://192.168.100.100:8000/api/v1/attendances`
3. **Headers Tab:** Add `Accept: application/json`
4. **Params Tab (Optional):** Add query parameters:
   - `date`: `2025-12-01`
   - `enroll_id`: `87`
5. **Click Send**

---

## Testing with cURL

```bash
# Get all attendance
curl -X GET "http://192.168.100.100:8000/api/v1/attendances" \
  -H "Accept: application/json"

# Get today's attendance
curl -X GET "http://192.168.100.100:8000/api/v1/attendances?date=2025-12-01" \
  -H "Accept: application/json"

# Get attendance for user 87
curl -X GET "http://192.168.100.100:8000/api/v1/attendances?enroll_id=87" \
  -H "Accept: application/json"

# Get daily summary
curl -X GET "http://192.168.100.100:8000/api/v1/attendances/daily/2025-12-01" \
  -H "Accept: application/json"
```

---

## Important Notes

1. **Date Format:** Always use `YYYY-MM-DD` format (e.g., `2025-12-01`)
2. **User ID:** Use `enroll_id` parameter with your user ID (e.g., `87`)
3. **Headers:** Always include `Accept: application/json`
4. **Pagination:** Results are paginated (default: 50 per page)
5. **No Authentication:** Currently no API key required

---

## Error Responses

### No Attendance Found

**Response (200 OK):**
```json
{
    "success": true,
    "data": [],
    "pagination": {
        "current_page": 1,
        "total": 0,
        "per_page": 50,
        "last_page": 1
    }
}
```

### Invalid Date Format

**Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "date": ["The date does not match the format Y-m-d."]
    }
}
```

---

## Complete Code Examples

### PHP (Laravel)

```php
use Illuminate\Support\Facades\Http;

$baseUrl = 'http://192.168.100.100:8000/api/v1';

// Get all attendance
$response = Http::get("{$baseUrl}/attendances");
$attendances = $response->json()['data'];

// Get today's attendance
$response = Http::get("{$baseUrl}/attendances", [
    'date' => date('Y-m-d')
]);
$todayAttendance = $response->json()['data'];

// Get attendance for user 87
$response = Http::get("{$baseUrl}/attendances", [
    'enroll_id' => '87'
]);
$userAttendance = $response->json()['data'];

// Get daily summary
$response = Http::get("{$baseUrl}/attendances/daily/2025-12-01");
$summary = $response->json()['data'];
```

### JavaScript (Fetch API)

```javascript
const baseUrl = 'http://192.168.100.100:8000/api/v1';

// Get all attendance
const response = await fetch(`${baseUrl}/attendances`, {
    headers: { 'Accept': 'application/json' }
});
const data = await response.json();
const attendances = data.data;

// Get today's attendance
const today = new Date().toISOString().split('T')[0];
const todayResponse = await fetch(`${baseUrl}/attendances?date=${today}`, {
    headers: { 'Accept': 'application/json' }
});
const todayData = await todayResponse.json();

// Get attendance for user 87
const userResponse = await fetch(`${baseUrl}/attendances?enroll_id=87`, {
    headers: { 'Accept': 'application/json' }
});
const userData = await userResponse.json();

// Get daily summary
const summaryResponse = await fetch(`${baseUrl}/attendances/daily/2025-12-01`, {
    headers: { 'Accept': 'application/json' }
});
const summaryData = await summaryResponse.json();
```

### Python (Requests)

```python
import requests
from datetime import datetime

base_url = 'http://192.168.100.100:8000/api/v1'

# Get all attendance
response = requests.get(f'{base_url}/attendances', headers={'Accept': 'application/json'})
attendances = response.json()['data']

# Get today's attendance
today = datetime.now().strftime('%Y-%m-%d')
response = requests.get(f'{base_url}/attendances', params={'date': today}, headers={'Accept': 'application/json'})
today_attendance = response.json()['data']

# Get attendance for user 87
response = requests.get(f'{base_url}/attendances', params={'enroll_id': '87'}, headers={'Accept': 'application/json'})
user_attendance = response.json()['data']

# Get daily summary
response = requests.get(f'{base_url}/attendances/daily/2025-12-01', headers={'Accept': 'application/json'})
summary = response.json()['data']
```

---

## Summary

**Base URL:** `http://192.168.100.100:8000/api/v1`

**Main Endpoint:** `GET /attendances`

**Common Queries:**
- All: `GET /attendances`
- By date: `GET /attendances?date=2025-12-01`
- By user: `GET /attendances?enroll_id=87`
- Daily summary: `GET /attendances/daily/2025-12-01`

**Always include header:** `Accept: application/json`

---

**Ready to get attendance data!** Use the URLs above with your server address.

