# Postman Quick Reference - All Endpoints

## Base URL
```
http://127.0.0.1:8000/api/v1
```

## Headers (for all requests)
```
Content-Type: application/json
Accept: application/json
```

---

## 📋 All Endpoints at a Glance

### User Management

| # | Method | URL | Body Required? |
|---|--------|-----|----------------|
| 1 | POST | `/users/register` | ✅ Yes |
| 2 | GET | `/users/{id}` | ❌ No |
| 3 | GET | `/users/enroll/{enrollId}` | ❌ No |
| 4 | GET | `/users` | ❌ No (query params optional) |
| 5 | PUT | `/users/{id}` | ✅ Yes (optional fields) |
| 6 | DELETE | `/users/{id}` | ❌ No |
| 7 | POST | `/users/{id}/register-device` | ✅ Yes |

### Attendance

| # | Method | URL | Body Required? |
|---|--------|-----|----------------|
| 8 | GET | `/attendances` | ❌ No (query params optional) |
| 9 | GET | `/attendances/{id}` | ❌ No |
| 10 | GET | `/attendances/daily/{date}` | ❌ No |

### Webhooks

| # | Method | URL | Body Required? |
|---|--------|-----|----------------|
| 11 | POST | `/webhook/configure` | ✅ Yes |
| 12 | GET | `/webhook/config` | ❌ No |
| 13 | POST | `/webhook/test` | ❌ No |

---

## Complete URLs with Examples

### 1. Register User
**POST** `http://127.0.0.1:8000/api/v1/users/register`
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "enroll_id": "1001",
    "auto_register_device": true
}
```

### 2. Get User by ID
**GET** `http://127.0.0.1:8000/api/v1/users/1`

### 3. Get User by Enroll ID
**GET** `http://127.0.0.1:8000/api/v1/users/enroll/1001`

### 4. List Users
**GET** `http://127.0.0.1:8000/api/v1/users`
**With filters:** `http://127.0.0.1:8000/api/v1/users?registered=true&search=John&per_page=20`

### 5. Update User
**PUT** `http://127.0.0.1:8000/api/v1/users/1`
```json
{
    "name": "John Updated",
    "email": "john.updated@example.com"
}
```

### 6. Delete User
**DELETE** `http://127.0.0.1:8000/api/v1/users/1`

### 7. Register User to Device
**POST** `http://127.0.0.1:8000/api/v1/users/1/register-device`
```json
{
    "device_ip": "192.168.100.108",
    "device_port": 4370
}
```

### 8. Get Attendances
**GET** `http://127.0.0.1:8000/api/v1/attendances`
**With filters:** `http://127.0.0.1:8000/api/v1/attendances?date=2025-11-30&user_id=1`

### 9. Get Attendance by ID
**GET** `http://127.0.0.1:8000/api/v1/attendances/1`

### 10. Get Daily Attendance
**GET** `http://127.0.0.1:8000/api/v1/attendances/daily/2025-11-30`

### 11. Configure Webhook
**POST** `http://127.0.0.1:8000/api/v1/webhook/configure`
```json
{
    "webhook_url": "https://webhook.site/your-unique-id",
    "api_key": "your-secret-key"
}
```

### 12. Get Webhook Config
**GET** `http://127.0.0.1:8000/api/v1/webhook/config`

### 13. Test Webhook
**POST** `http://127.0.0.1:8000/api/v1/webhook/test`

---

## Query Parameters Reference

### List Users (`GET /users`)
- `registered`: true/false
- `search`: string
- `per_page`: number (default: 50)

### Get Attendances (`GET /attendances`)
- `date`: YYYY-MM-DD
- `date_from`: YYYY-MM-DD
- `date_to`: YYYY-MM-DD
- `user_id`: number
- `enroll_id`: string
- `per_page`: number (default: 50)

---

## Quick Copy-Paste for Postman

### Register User
```
POST http://127.0.0.1:8000/api/v1/users/register
Content-Type: application/json

{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "enroll_id": "9999",
    "auto_register_device": true
}
```

### Get User
```
GET http://127.0.0.1:8000/api/v1/users/1
```

### List Users
```
GET http://127.0.0.1:8000/api/v1/users
```

### Configure Webhook
```
POST http://127.0.0.1:8000/api/v1/webhook/configure
Content-Type: application/json

{
    "webhook_url": "https://webhook.site/YOUR-ID"
}
```

### Test Webhook
```
POST http://127.0.0.1:8000/api/v1/webhook/test
```

---

**For detailed documentation, see:** `POSTMAN_COLLECTION_GUIDE.md`


