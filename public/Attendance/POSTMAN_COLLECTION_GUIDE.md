# Postman Collection Guide - Attendance System API

## Quick Setup

1. **Create Environment:**
   - Variable: `base_url`
   - Value: `http://127.0.0.1:8000/api/v1`

2. **Set Headers (for all requests):**
   - `Content-Type: application/json`
   - `Accept: application/json`

---

## User Management Endpoints

### 1. Register User

**Method:** `POST`  
**URL:** `{{base_url}}/users/register`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON) - Simplified (only id and name required):**
```json
{
    "id": "1001",
    "name": "John Doe",
    "auto_register_device": true
}
```

**Required Fields:**
- `id`: Enroll ID from your system (numeric, 1-9 digits, must be unique)
- `name`: User's full name

**Optional Parameters:**
- `auto_register_device`: true/false (default: true)
- `device_ip`: "192.168.100.108"
- `device_port`: 4370

**Note:** Email and password are auto-generated if not provided.

**Expected Response (201):**
```json
{
    "success": true,
    "message": "User created and registered to device successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "enroll_id": "1001",
        "registered_on_device": true,
        "device_registered_at": "2025-11-30 14:00:00"
    }
}
```

**Note:** Email and password are auto-generated and not returned in the response for security.

---

### 2. Get User by ID

**Method:** `GET`  
**URL:** `{{base_url}}/users/1`

**Headers:**
```
Accept: application/json
```

**No Body Required**

**Expected Response (200):**
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
        "attendances_count": 0
    }
}
```

---

### 3. Get User by Enroll ID

**Method:** `GET`  
**URL:** `{{base_url}}/users/enroll/1001`

**Headers:**
```
Accept: application/json
```

**No Body Required**

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "enroll_id": "1001",
        "registered_on_device": true
    }
}
```

---

### 4. List Users

**Method:** `GET`  
**URL:** `{{base_url}}/users`

**Headers:**
```
Accept: application/json
```

**Query Parameters (Optional):**
- `registered`: true or false (filter by registration status)
- `search`: "John" (search by name, email, or enroll_id)
- `per_page`: 50 (results per page)

**Example with Query Parameters:**
```
{{base_url}}/users?registered=true&search=John&per_page=20
```

**Expected Response (200):**
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

### 5. Update User

**Method:** `PUT`  
**URL:** `{{base_url}}/users/1`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON) - All fields optional:**
```json
{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "password": "newpassword123"
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "User updated successfully",
    "data": {
        "id": 1,
        "name": "John Updated",
        "email": "john.updated@example.com",
        "enroll_id": "1001"
    }
}
```

---

### 6. Delete User

**Method:** `DELETE`  
**URL:** `{{base_url}}/users/1`

**Headers:**
```
Accept: application/json
```

**No Body Required**

**Expected Response (200):**
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

---

### 7. Register User to Device

**Method:** `POST`  
**URL:** `{{base_url}}/users/1/register-device`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "device_ip": "192.168.100.108",
    "device_port": 4370
}
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

---

## Attendance Endpoints

### 8. Get Attendance Records

**Method:** `GET`  
**URL:** `{{base_url}}/attendances`

**Headers:**
```
Accept: application/json
```

**Query Parameters (Optional):**
- `date`: "2025-11-30" (filter by specific date)
- `date_from`: "2025-11-01" (start date for range)
- `date_to`: "2025-11-30" (end date for range)
- `user_id`: 1 (filter by user ID)
- `enroll_id`: "1001" (filter by enroll ID)
- `per_page`: 50 (results per page)

**Example with Query Parameters:**
```
{{base_url}}/attendances?date=2025-11-30&per_page=20
```

**Or with Date Range:**
```
{{base_url}}/attendances?date_from=2025-11-01&date_to=2025-11-30
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
        "total": 1,
        "per_page": 50,
        "last_page": 1
    }
}
```

---

### 9. Get Attendance by ID

**Method:** `GET`  
**URL:** `{{base_url}}/attendances/1`

**Headers:**
```
Accept: application/json
```

**No Body Required**

**Expected Response (200):**
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
        "attendance_date": "2025-11-30",
        "check_in_time": "2025-11-30 08:00:00",
        "check_out_time": "2025-11-30 17:00:00",
        "status": 1,
        "verify_mode": "Fingerprint",
        "device_ip": "192.168.100.108"
    }
}
```

---

### 10. Get Daily Attendance Summary

**Method:** `GET`  
**URL:** `{{base_url}}/attendances/daily/2025-11-30`

**Headers:**
```
Accept: application/json
```

**Note:** Replace `2025-11-30` with the date you want (format: YYYY-MM-DD)

**No Body Required**

**Expected Response (200):**
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

## Webhook Endpoints

### 11. Configure Webhook

**Method:** `POST`  
**URL:** `{{base_url}}/webhook/configure`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "webhook_url": "https://webhook.site/your-unique-id",
    "api_key": "your-secret-key"
}
```

**Note:** 
- `webhook_url` is required
- `api_key` is optional
- Use https://webhook.site to get a test URL

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

---

### 12. Get Webhook Configuration

**Method:** `GET`  
**URL:** `{{base_url}}/webhook/config`

**Headers:**
```
Accept: application/json
```

**No Body Required**

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

### 13. Test Webhook

**Method:** `POST`  
**URL:** `{{base_url}}/webhook/test`

**Headers:**
```
Accept: application/json
```

**No Body Required**

**Expected Response (200):**
```json
{
    "success": true,
    "message": "Test webhook sent successfully"
}
```

**Note:** After running this, check your configured webhook URL (e.g., webhook.site) to see the test request.

---

## Postman Collection Setup

### Step 1: Create Environment

1. Click **Environments** → **+**
2. Name: `Attendance System`
3. Add variable:
   - Variable: `base_url`
   - Initial Value: `http://127.0.0.1:8000/api/v1`
   - Current Value: `http://127.0.0.1:8000/api/v1`
4. Click **Save**

### Step 2: Create Collection

1. Click **Collections** → **+**
2. Name: `Attendance System API`
3. Click **...** → **Edit**
4. Go to **Variables** tab
5. Add variable:
   - Variable: `base_url`
   - Value: `http://127.0.0.1:8000/api/v1`
6. Click **Save**

### Step 3: Add Requests

Create folders and requests:

**Folder: User Management**
- Register User (POST)
- Get User by ID (GET)
- Get User by Enroll ID (GET)
- List Users (GET)
- Update User (PUT)
- Delete User (DELETE)
- Register User to Device (POST)

**Folder: Attendance**
- Get Attendances (GET)
- Get Attendance by ID (GET)
- Get Daily Attendance (GET)

**Folder: Webhooks**
- Configure Webhook (POST)
- Get Webhook Config (GET)
- Test Webhook (POST)

### Step 4: Set Collection Headers

1. Click on collection name
2. Go to **Headers** tab
3. Add:
   - Key: `Content-Type`, Value: `application/json`
   - Key: `Accept`, Value: `application/json`

### Step 5: Use Environment Variable

In each request URL, use: `{{base_url}}/users/register`

---

## Quick Test Sequence

### Test 1: Register User
1. Use endpoint #1 (Register User)
2. Change `id` to a unique number (e.g., 9999)
3. Send request with only `id` and `name`
4. **Expected:** 201 status, user created

### Test 2: Get User
1. Use endpoint #2 (Get User by ID)
2. Change ID to the user ID from Test 1
3. Send request
4. **Expected:** 200 status, user data returned

### Test 3: List Users
1. Use endpoint #4 (List Users)
2. Send request
3. **Expected:** 200 status, array of users

### Test 4: Configure Webhook
1. Go to https://webhook.site
2. Copy your unique URL
3. Use endpoint #11 (Configure Webhook)
4. Paste URL in `webhook_url` field
5. Send request
6. **Expected:** 200 status, webhook configured

### Test 5: Test Webhook
1. Use endpoint #13 (Test Webhook)
2. Send request
3. Check webhook.site - you should see a test request!

---

## Error Testing

### Test Validation Error (Duplicate Email)

**Endpoint:** Register User  
**Body:**
```json
{
    "id": "1001",
    "name": "Test User"
}
```
(Use an ID that already exists)

**Expected:** 422 status with validation errors

### Test 404 Error (Non-existent User)

**Endpoint:** Get User by ID  
**URL:** `{{base_url}}/users/9999`

**Expected:** 404 status with "User not found" message

---

## Postman Collection JSON

You can import this collection directly into Postman:

```json
{
    "info": {
        "name": "Attendance System API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "variable": [
        {
            "key": "base_url",
            "value": "http://127.0.0.1:8000/api/v1"
        }
    ],
    "item": [
        {
            "name": "User Management",
            "item": [
                {
                    "name": "Register User",
                    "request": {
                        "method": "POST",
                        "header": [
                            {
                                "key": "Content-Type",
                                "value": "application/json"
                            }
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": "{\n    \"id\": \"1001\",\n    \"name\": \"John Doe\",\n    \"auto_register_device\": true\n}"
                        },
                        "url": {
                            "raw": "{{base_url}}/users/register",
                            "host": ["{{base_url}}"],
                            "path": ["users", "register"]
                        }
                    }
                },
                {
                    "name": "Get User by ID",
                    "request": {
                        "method": "GET",
                        "url": {
                            "raw": "{{base_url}}/users/1",
                            "host": ["{{base_url}}"],
                            "path": ["users", "1"]
                        }
                    }
                },
                {
                    "name": "List Users",
                    "request": {
                        "method": "GET",
                        "url": {
                            "raw": "{{base_url}}/users",
                            "host": ["{{base_url}}"],
                            "path": ["users"]
                        }
                    }
                }
            ]
        },
        {
            "name": "Webhooks",
            "item": [
                {
                    "name": "Configure Webhook",
                    "request": {
                        "method": "POST",
                        "header": [
                            {
                                "key": "Content-Type",
                                "value": "application/json"
                            }
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": "{\n    \"webhook_url\": \"https://webhook.site/your-unique-id\"\n}"
                        },
                        "url": {
                            "raw": "{{base_url}}/webhook/configure",
                            "host": ["{{base_url}}"],
                            "path": ["webhook", "configure"]
                        }
                    }
                },
                {
                    "name": "Test Webhook",
                    "request": {
                        "method": "POST",
                        "url": {
                            "raw": "{{base_url}}/webhook/test",
                            "host": ["{{base_url}}"],
                            "path": ["webhook", "test"]
                        }
                    }
                }
            ]
        }
    ]
}
```

**To Import:**
1. Open Postman
2. Click **Import**
3. Paste the JSON above
4. Click **Import**

---

## Tips

1. **Save Responses:** Right-click response → Save Response → Save as Example
2. **Use Variables:** Use `{{base_url}}` in all URLs
3. **Test Scripts:** Add test scripts to verify responses automatically
4. **Pre-request Scripts:** Set dynamic values (like current date)
5. **Collection Runner:** Run all requests in sequence

---

**Ready to test!** Import the collection or create requests manually using the URLs and parameters above.

