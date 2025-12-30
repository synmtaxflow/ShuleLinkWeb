# API Testing Guide

## Overview

This guide provides step-by-step instructions to test all API endpoints before sharing with external developers.

---

## Prerequisites

- Attendance system running at: `http://127.0.0.1:8000`
- ZKTeco device connected and accessible
- API routes registered (run `php artisan route:list` to verify)

---

## Testing Tools

### Option 1: cURL (Command Line)
- Available on all systems
- Good for quick tests
- Can be scripted

### Option 2: Postman (Recommended)
- Visual interface
- Easy to save and share requests
- Can create collections

### Option 3: Browser (GET requests only)
- Simple for testing GET endpoints
- Limited to GET requests

### Option 4: PHP Tinker
- Test from Laravel environment
- Good for integration testing

---

## Test Checklist

- [ ] User Registration API
- [ ] Get User by ID
- [ ] Get User by Enroll ID
- [ ] List Users
- [ ] Update User
- [ ] Delete User
- [ ] Register User to Device
- [ ] Get Attendances
- [ ] Get Attendance by ID
- [ ] Get Daily Attendance
- [ ] Configure Webhook
- [ ] Get Webhook Config
- [ ] Test Webhook

---

## 1. Test User Registration API

### Endpoint: `POST /api/v1/users/register`

### Test 1.1: Register User with Auto Device Registration

**Using cURL:**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User 1",
    "email": "test1@example.com",
    "password": "password123",
    "enroll_id": "1001",
    "auto_register_device": true
  }'
```

**Expected Response (201):**
```json
{
    "success": true,
    "message": "User created and registered to device successfully",
    "data": {
        "id": 1,
        "name": "Test User 1",
        "email": "test1@example.com",
        "enroll_id": "1001",
        "registered_on_device": true,
        "device_registered_at": "2025-11-30 14:00:00"
    }
}
```

**✅ Check:**
- Status code is 201
- `success` is `true`
- `registered_on_device` is `true`
- User appears in database
- User appears on device (verify manually)

### Test 1.2: Register User Without Auto Device Registration

```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User 2",
    "email": "test2@example.com",
    "password": "password123",
    "enroll_id": "1002",
    "auto_register_device": false
  }'
```

**Expected Response:**
- `registered_on_device` should be `false`

### Test 1.3: Validation Errors

**Test Duplicate Email:**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User 3",
    "email": "test1@example.com",
    "password": "password123",
    "enroll_id": "1003"
  }'
```

**Expected Response (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

**Test Duplicate Enroll ID:**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User 4",
    "email": "test4@example.com",
    "password": "password123",
    "enroll_id": "1001"
  }'
```

**Expected Response (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "enroll_id": ["The enroll id has already been taken."]
    }
}
```

**Test Invalid Enroll ID (non-numeric):**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User 5",
    "email": "test5@example.com",
    "password": "password123",
    "enroll_id": "abc"
  }'
```

**Expected Response (422):**
- Error about enroll_id format

---

## 2. Test Get User by ID

### Endpoint: `GET /api/v1/users/{id}`

**Using cURL:**
```bash
curl http://127.0.0.1:8000/api/v1/users/1
```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Test User 1",
        "email": "test1@example.com",
        "enroll_id": "1001",
        "registered_on_device": true,
        "device_registered_at": "2025-11-30 14:00:00",
        "attendances_count": 0,
        "created_at": "2025-11-30 14:00:00"
    }
}
```

**Test Non-Existent User:**
```bash
curl http://127.0.0.1:8000/api/v1/users/9999
```

**Expected Response (404):**
```json
{
    "success": false,
    "message": "User not found"
}
```

---

## 3. Test Get User by Enroll ID

### Endpoint: `GET /api/v1/users/enroll/{enrollId}`

```bash
curl http://127.0.0.1:8000/api/v1/users/enroll/1001
```

**Expected Response (200):**
- Same format as Get User by ID

---

## 4. Test List Users

### Endpoint: `GET /api/v1/users`

**Basic Request:**
```bash
curl http://127.0.0.1:8000/api/v1/users
```

**With Filters:**
```bash
# Filter by registered status
curl "http://127.0.0.1:8000/api/v1/users?registered=true"

# Search
curl "http://127.0.0.1:8000/api/v1/users?search=Test"

# Pagination
curl "http://127.0.0.1:8000/api/v1/users?per_page=10"
```

**Expected Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Test User 1",
            "email": "test1@example.com",
            "enroll_id": "1001",
            "registered_on_device": true,
            "attendances_count": 0
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

## 5. Test Update User

### Endpoint: `PUT /api/v1/users/{id}`

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/users/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "email": "updated@example.com"
  }'
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "User updated successfully",
    "data": {
        "id": 1,
        "name": "Updated Name",
        "email": "updated@example.com",
        "enroll_id": "1001"
    }
}
```

**✅ Verify:**
- Check database to confirm changes
- Check response data matches update

---

## 6. Test Delete User

### Endpoint: `DELETE /api/v1/users/{id}`

**⚠️ Warning: This will delete the user permanently!**

```bash
curl -X DELETE http://127.0.0.1:8000/api/v1/users/2
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

**✅ Verify:**
- User no longer exists in database
- User no longer appears in list

---

## 7. Test Register User to Device

### Endpoint: `POST /api/v1/users/{id}/register-device`

```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/1/register-device \
  -H "Content-Type: application/json" \
  -d '{
    "device_ip": "192.168.100.108",
    "device_port": 4370
  }'
```

**Expected Response (200):**
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

**✅ Verify:**
- Check device to confirm user is registered
- Check database: `registered_on_device` is `true`

---

## 8. Test Get Attendances

### Endpoint: `GET /api/v1/attendances`

**Basic Request:**
```bash
curl http://127.0.0.1:8000/api/v1/attendances
```

**With Date Filter:**
```bash
curl "http://127.0.0.1:8000/api/v1/attendances?date=2025-11-30"
```

**With Date Range:**
```bash
curl "http://127.0.0.1:8000/api/v1/attendances?date_from=2025-11-01&date_to=2025-11-30"
```

**With User Filter:**
```bash
curl "http://127.0.0.1:8000/api/v1/attendances?user_id=1"
```

**Expected Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user": {
                "id": 1,
                "name": "Test User 1",
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
        "total": 1,
        "per_page": 50,
        "last_page": 1
    }
}
```

---

## 9. Test Get Attendance by ID

### Endpoint: `GET /api/v1/attendances/{id}`

```bash
curl http://127.0.0.1:8000/api/v1/attendances/1
```

**Expected Response (200):**
- Single attendance object (same format as in list)

---

## 10. Test Get Daily Attendance

### Endpoint: `GET /api/v1/attendances/daily/{date}`

```bash
curl http://127.0.0.1:8000/api/v1/attendances/daily/2025-11-30
```

**Expected Response (200):**
```json
{
    "success": true,
    "date": "2025-11-30",
    "data": [
        {
            "user": {
                "id": 1,
                "name": "Test User 1",
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

## 11. Test Configure Webhook

### Endpoint: `POST /api/v1/webhook/configure`

**Using webhook.site for testing:**
1. Go to https://webhook.site
2. Copy your unique URL
3. Configure webhook:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/webhook/configure \
  -H "Content-Type: application/json" \
  -d '{
    "webhook_url": "https://webhook.site/your-unique-id",
    "api_key": "test-key-123"
  }'
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "Webhook configured successfully",
    "data": {
        "webhook_url": "https://webhook.site/your-unique-id",
        "configured_at": "2025-11-30 14:00:00"
    }
}
```

**✅ Verify:**
- Webhook URL is stored (check cache or database)

---

## 12. Test Get Webhook Config

### Endpoint: `GET /api/v1/webhook/config`

```bash
curl http://127.0.0.1:8000/api/v1/webhook/config
```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "webhook_url": "https://webhook.site/your-unique-id",
        "has_api_key": true,
        "configured": true
    }
}
```

---

## 13. Test Webhook Delivery

### Endpoint: `POST /api/v1/webhook/test`

```bash
curl -X POST http://127.0.0.1:8000/api/v1/webhook/test
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "Test webhook sent successfully"
}
```

**✅ Verify:**
- Check webhook.site - you should see a test request
- Check the payload matches expected format

### Test Real Webhook (Scan on Device)

1. Configure webhook URL (step 11)
2. Register a user (step 1)
3. Scan the user's fingerprint on the device
4. Check webhook.site - you should see attendance data!

**Expected Webhook Payload:**
```json
{
    "event": "attendance.created",
    "data": {
        "id": 1,
        "user_id": 1,
        "enroll_id": "1001",
        "user_name": "Test User 1",
        "user_email": "test1@example.com",
        "attendance_date": "2025-11-30",
        "check_in_time": "2025-11-30 14:30:00",
        "check_out_time": null,
        "status": 1,
        "verify_mode": "Fingerprint",
        "device_ip": "192.168.100.108",
        "timestamp": "2025-11-30 14:30:00"
    }
}
```

---

## Testing with Postman

### Create Postman Collection

1. **Import Collection:**
   - Create new collection: "Attendance System API"
   - Add environment: `base_url = http://127.0.0.1:8000`

2. **Add Requests:**
   - User Registration: `POST {{base_url}}/api/v1/users/register`
   - Get User: `GET {{base_url}}/api/v1/users/1`
   - List Users: `GET {{base_url}}/api/v1/users`
   - Configure Webhook: `POST {{base_url}}/api/v1/webhook/configure`
   - Test Webhook: `POST {{base_url}}/api/v1/webhook/test`

3. **Set Headers:**
   - `Content-Type: application/json`
   - `Accept: application/json`

4. **Save and Test:**
   - Run collection
   - Check responses

---

## Testing with PHP Tinker

```php
php artisan tinker
```

```php
// Test user registration
use Illuminate\Support\Facades\Http;

$response = Http::post('http://127.0.0.1:8000/api/v1/users/register', [
    'name' => 'Tinker Test',
    'email' => 'tinker@example.com',
    'password' => 'password123',
    'enroll_id' => '9999',
    'auto_register_device' => true,
]);

$response->json();

// Test get user
$response = Http::get('http://127.0.0.1:8000/api/v1/users/1');
$response->json();

// Test webhook config
$response = Http::post('http://127.0.0.1:8000/api/v1/webhook/configure', [
    'webhook_url' => 'https://webhook.site/test',
]);
$response->json();
```

---

## Testing Checklist

### User Management
- [ ] Register user successfully
- [ ] Register user with auto device registration
- [ ] Validation errors (duplicate email)
- [ ] Validation errors (duplicate enroll_id)
- [ ] Validation errors (invalid enroll_id)
- [ ] Get user by ID
- [ ] Get user by enroll ID
- [ ] Get non-existent user (404)
- [ ] List users
- [ ] List users with filters
- [ ] Update user
- [ ] Delete user
- [ ] Register user to device

### Attendance
- [ ] Get attendances
- [ ] Get attendances with date filter
- [ ] Get attendances with date range
- [ ] Get attendances with user filter
- [ ] Get attendance by ID
- [ ] Get daily attendance summary

### Webhooks
- [ ] Configure webhook URL
- [ ] Get webhook configuration
- [ ] Test webhook (sends test request)
- [ ] Real webhook (scan on device)
- [ ] Verify webhook payload format
- [ ] Verify webhook is sent on check-in
- [ ] Verify webhook is sent on check-out

---

## Common Issues

### Issue: 404 Not Found
**Solution:** Check route is registered: `php artisan route:list | grep api`

### Issue: 422 Validation Error
**Solution:** Check request body format and required fields

### Issue: 500 Internal Server Error
**Solution:** Check logs: `tail -f storage/logs/laravel.log`

### Issue: Webhook Not Received
**Solution:**
- Verify webhook URL is configured
- Check webhook URL is publicly accessible
- Verify webhook endpoint returns 200
- Check logs for errors

### Issue: User Not Registered to Device
**Solution:**
- Check device connection
- Verify device IP and port
- Check device is enabled
- Check logs for errors

---

## Automated Testing Script

Create a test script: `test-api.sh`

```bash
#!/bin/bash

BASE_URL="http://127.0.0.1:8000/api/v1"

echo "Testing User Registration..."
curl -X POST $BASE_URL/users/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","enroll_id":"9999","auto_register_device":true}'

echo -e "\n\nTesting Get User..."
curl $BASE_URL/users/1

echo -e "\n\nTesting List Users..."
curl $BASE_URL/users

echo -e "\n\nTesting Webhook Config..."
curl -X POST $BASE_URL/webhook/configure \
  -H "Content-Type: application/json" \
  -d '{"webhook_url":"https://webhook.site/test"}'

echo -e "\n\nTesting Webhook..."
curl -X POST $BASE_URL/webhook/test

echo -e "\n\nDone!"
```

**Make executable:**
```bash
chmod +x test-api.sh
./test-api.sh
```

---

## Next Steps

1. ✅ Test all endpoints using this guide
2. ✅ Verify responses match expected format
3. ✅ Test error cases
4. ✅ Test webhook delivery
5. ✅ Document any issues found
6. ✅ Share `DEVELOPER_INTEGRATION_GUIDE.md` with developers

---

**Ready to test!** Start with User Registration and work through each endpoint systematically.


