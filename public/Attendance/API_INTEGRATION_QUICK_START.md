# API Integration Quick Start Guide

## For External Developers

This is a quick reference guide for integrating with the Attendance System API. The API has been simplified - you only need to send `id` and `name` to register users.

---

## 🚨 IMPORTANT: Use Full Server URL

**External developers MUST use the COMPLETE URL with the server address:**

✅ **CORRECT:**
```
POST https://YOUR-SERVER-URL.com/api/v1/users/register
```

❌ **INCORRECT:**
```
POST /api/v1/users/register
```

**You must replace `YOUR-SERVER-URL.com` with the actual server domain provided by the system administrator.**

**Example:**
- If your server URL is `attendance.company.com`
- Use: `https://attendance.company.com/api/v1/users/register`

---

## ⚠️ Important: Server URL

**You MUST use the FULL URL with your server address:**

```
https://YOUR-SERVER-URL.com/api/v1/users/register
```

**Replace `YOUR-SERVER-URL.com` with the actual server URL provided by the system administrator.**

**Example:**
- If your server is `attendance.company.com`, use:
  ```
  https://attendance.company.com/api/v1/users/register
  ```

---

## Base URL Format

```
https://YOUR-SERVER-URL.com/api/v1
```

**Note:** Replace `YOUR-SERVER-URL.com` with your actual server domain.

---

## Headers (All Requests)

```
Content-Type: application/json
Accept: application/json
```

---

## Quick Start - 3 Steps

### Step 1: Register a User

**Only 2 fields required: `id` and `name`**

**⚠️ IMPORTANT:**
- Use **POST** method (not GET)
- Send data as **JSON in the request body** (not query parameters)
- Use **Content-Type: application/json** header

**Full URL:**
```
POST http://192.168.100.100:8000/api/v1/users/register
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body (JSON):**
```json
{
    "id": "87",
    "name": "MWAKABANJE"
}
```

**❌ WRONG (Query Parameters - This will give 405 error):**
```
POST http://192.168.100.100:8000/api/v1/users/register?id=87&name=MWAKABANJE
```

**✅ CORRECT (JSON Body):**
```
POST http://192.168.100.100:8000/api/v1/users/register
Content-Type: application/json

{
    "id": "87",
    "name": "MWAKABANJE"
}
```

### How to Use in Postman:

1. **Method:** Select `POST`
2. **URL:** `http://192.168.100.100:8000/api/v1/users/register`
   - ❌ **DON'T** add `?id=87&name=MWAKABANJE` to the URL
3. **Headers Tab:** Add:
   - `Content-Type: application/json`
   - `Accept: application/json`
4. **Body Tab:**
   - Select `raw`
   - Select `JSON` from dropdown
   - Enter:
   ```json
   {
       "id": "87",
       "name": "MWAKABANJE"
   }
   ```
5. Click **Send**

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

✅ **That's it!** User is registered successfully.

---

### Step 2: Configure Webhook (Optional)

Set up webhook to receive real-time attendance data:

**Full URL:**
```
POST https://YOUR-SERVER-URL.com/api/v1/webhook/configure
```

**Request (Full Payload - All Details):**
```json
{
    "webhook_url": "https://your-app.com/api/attendance/webhook"
}
```

**Request (Minimal Payload - Only User ID):**
```json
{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": true
}
```

**Note:** Set `minimal_payload: true` to receive only the user ID. See `MINIMAL_PAYLOAD_DOCUMENTATION.md` for details.

---

### Step 3: Receive Attendance Data

When users scan on the device, your webhook will automatically receive:

**If minimal_payload: true:**
```json
{
    "event": "attendance.created",
    "data": {
        "id": "1001"
    }
}
```

**If minimal_payload: false (default):**
```json
{
    "event": "attendance.created",
    "data": {
        "enroll_id": "1001",
        "user_name": "John Doe",
        "check_in_time": "2025-12-01 08:00:00",
        "check_out_time": "2025-12-01 17:00:00"
    }
}
```

---

## All Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/users/register` | Register user (only id + name) |
| GET | `/users/enroll/{id}` | Get user by your ID |
| GET | `/users` | List all users |
| GET | `/attendances` | Get attendance records |
| GET | `/attendances/daily/{date}` | Get daily summary |
| POST | `/webhook/configure` | Set webhook URL |
| POST | `/webhook/test` | Test webhook |

---

## Code Examples

**⚠️ Replace `YOUR-SERVER-URL.com` with your actual server URL in all examples below.**

### PHP
```php
$baseUrl = 'https://YOUR-SERVER-URL.com/api/v1';

$response = Http::post($baseUrl . '/users/register', [
    'id' => '1001',
    'name' => 'John Doe'
]);
```

### JavaScript
```javascript
const baseUrl = 'https://YOUR-SERVER-URL.com/api/v1';

const response = await fetch(`${baseUrl}/users/register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        id: '1001',
        name: 'John Doe'
    })
});
```

### cURL
```bash
curl -X POST https://YOUR-SERVER-URL.com/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{"id":"1001","name":"John Doe"}'
```

### Python
```python
import requests

base_url = 'https://YOUR-SERVER-URL.com/api/v1'

response = requests.post(
    f'{base_url}/users/register',
    json={
        'id': '1001',
        'name': 'John Doe'
    },
    headers={'Content-Type': 'application/json'}
)
```

---

## Important Notes

1. **ID Requirements:**
   - Must be numeric (1-9 digits)
   - Must be unique
   - This is your user ID from your system

2. **Auto-Generated Fields:**
   - Email: `user_{id}@attendance.local`
   - Password: Secure random password

3. **Webhook:**
   - Must be publicly accessible (HTTPS in production)
   - Must return 200 status code
   - Process asynchronously

---

## Full Documentation

For complete documentation, see:
- **`DEVELOPER_API_INTEGRATION_GUIDE.md`** - Complete API reference
- **`POSTMAN_COLLECTION_GUIDE.md`** - Postman testing guide
- **`API_TEST_SCRIPTS_GUIDE.md`** - Automated testing scripts

---

## Support

For questions or issues, contact the system administrator.

---

**Ready to integrate!** Start with Step 1 above.

