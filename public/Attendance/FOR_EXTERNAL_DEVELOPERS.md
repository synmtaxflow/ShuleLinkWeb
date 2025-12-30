# API Integration Guide for External Developers

## 📋 Quick Overview

This document provides everything you need to integrate your application with the ZKTeco Attendance System API.

**What you can do:**
- Register users and automatically register them to the biometric device
- Receive real-time attendance data via webhooks when users scan

---

## 🚀 Quick Start (3 Steps)

### 1. Register a User

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
// User is now registered in the system AND on the device!
```

### 2. Configure Webhook

```php
Http::post('https://attendance-system.com/api/v1/webhook/configure', [
    'webhook_url' => 'https://your-app.com/api/attendance/webhook',
]);
```

### 3. Receive Webhooks

```php
// routes/api.php
Route::post('/attendance/webhook', function(Request $request) {
    $data = $request->input('data');
    
    // Process attendance
    // $data['check_in_time']
    // $data['check_out_time']
    // etc.
    
    return response()->json(['success' => true]);
});
```

**Done!** When users scan on the device, your app receives webhooks automatically.

---

## 📚 Complete Documentation

For detailed documentation, see: **`DEVELOPER_INTEGRATION_GUIDE.md`**

This includes:
- ✅ Complete API reference
- ✅ All endpoints with examples
- ✅ Webhook integration guide
- ✅ Error handling
- ✅ Code examples in PHP, JavaScript, Python
- ✅ Testing guide
- ✅ Troubleshooting

---

## 🔗 API Base URL

```
Production: https://attendance-system.com/api/v1
Development: http://127.0.0.1:8000/api/v1
```

**Note:** Replace `attendance-system.com` with the actual domain provided by your system administrator.

---

## 📡 Main Endpoints

### User Management

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/users/register` | POST | Register new user (auto-registers to device) |
| `/api/v1/users/{id}` | GET | Get user by ID |
| `/api/v1/users` | GET | List all users |
| `/api/v1/users/{id}` | PUT | Update user |
| `/api/v1/users/{id}` | DELETE | Delete user |

### Attendance

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/attendances` | GET | List attendance records |
| `/api/v1/attendances/daily/{date}` | GET | Get daily summary |

### Webhooks

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/webhook/configure` | POST | Configure webhook URL |
| `/api/v1/webhook/config` | GET | Get webhook configuration |
| `/api/v1/webhook/test` | POST | Test webhook connection |

---

## 📨 Webhook Payload

When a user scans on the device, your webhook receives:

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

---

## ✅ Requirements

### Webhook Endpoint Requirements

Your webhook endpoint must:
- ✅ Accept **POST requests**
- ✅ Return **200 status code** (to acknowledge receipt)
- ✅ Be **publicly accessible** (not localhost)
- ✅ Use **HTTPS** in production
- ✅ Process data **asynchronously** (return quickly)

---

## 🧪 Testing

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

### Test Webhook

Use [webhook.site](https://webhook.site) to get a test URL:

```bash
curl -X POST https://attendance-system.com/api/v1/webhook/configure \
  -H "Content-Type: application/json" \
  -d '{
    "webhook_url": "https://webhook.site/your-unique-id"
  }'
```

Then scan on the device and check webhook.site for the data!

---

## 📖 Full Documentation

**See `DEVELOPER_INTEGRATION_GUIDE.md` for:**
- Complete API reference
- All request/response examples
- Error handling
- Code examples in multiple languages
- Troubleshooting guide
- Best practices

---

## 🆘 Support

For API access, credentials, or questions:
- Contact the system administrator
- Check `DEVELOPER_INTEGRATION_GUIDE.md` for detailed documentation
- Review error messages and status codes

---

## 📝 Important Notes

1. **Enroll ID**: Must be numeric, unique, and between 1-65535
2. **Webhook URL**: Must be publicly accessible (use ngrok for local testing)
3. **HTTPS**: Required for production webhooks
4. **Error Handling**: Always handle API errors gracefully
5. **Idempotency**: Make webhook endpoints idempotent (handle duplicates)

---

**Ready to integrate?** Start with the Quick Start above, then refer to `DEVELOPER_INTEGRATION_GUIDE.md` for complete details.

---

**Last Updated:** November 30, 2025  
**API Version:** 1.0.0


