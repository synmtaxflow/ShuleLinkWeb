# Webhook Minimal Payload - Send Only User ID

## Overview

You can configure the webhook to send **only the user ID** (enroll_id) when a user scans their fingerprint, excluding all other details.

---

## Configuration

### Enable Minimal Payload

When configuring the webhook, set `minimal_payload: true`:

```bash
POST https://YOUR-SERVER-URL.com/api/v1/webhook/configure
Content-Type: application/json

{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Webhook configured successfully",
    "data": {
        "webhook_url": "https://your-app.com/api/attendance/webhook",
        "minimal_payload": true,
        "configured_at": "2025-12-01 08:00:00"
    }
}
```

---

## Minimal Payload Structure

When `minimal_payload: true` is enabled, your webhook will receive:

```json
{
    "event": "attendance.created",
    "data": {
        "id": "87"
    }
}
```

**That's it!** Only the user ID (enroll_id) is sent.

---

## Full Payload (Default)

When `minimal_payload: false` (or not set), you receive the full payload:

```json
{
    "event": "attendance.created",
    "data": {
        "id": 123,
        "user_id": 1,
        "enroll_id": "87",
        "user_name": "MWAKABANJE",
        "user_email": "user_87@attendance.local",
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

---

## Comparison

| Feature | Minimal Payload | Full Payload |
|---------|----------------|--------------|
| **User ID (enroll_id)** | ✅ Yes | ✅ Yes |
| **User Name** | ❌ No | ✅ Yes |
| **Check-in Time** | ❌ No | ✅ Yes |
| **Check-out Time** | ❌ No | ✅ Yes |
| **Attendance Date** | ❌ No | ✅ Yes |
| **Status** | ❌ No | ✅ Yes |
| **Device Info** | ❌ No | ✅ Yes |
| **Payload Size** | Small (~50 bytes) | Large (~300 bytes) |

---

## Use Cases

### Minimal Payload - Use When:
- ✅ You only need to know **which user** scanned
- ✅ You want to **reduce data transfer**
- ✅ You want **simpler webhook handling**
- ✅ You have the user details in your own system

### Full Payload - Use When:
- ✅ You need **complete attendance information**
- ✅ You want **check-in/check-out times**
- ✅ You need **device information**
- ✅ You want **all data in one request**

---

## Example: Minimal Payload Handler

**Laravel:**
```php
Route::post('/api/attendance/webhook', [AttendanceController::class, 'handleWebhook']);

public function handleWebhook(Request $request)
{
    $event = $request->input('event');
    $data = $request->input('data');
    
    if ($event === 'attendance.created') {
        // Only user ID is sent
        $userId = $data['id'];  // This is the enroll_id (e.g., "87")
        
        // Find user in your system
        $user = User::where('external_id', $userId)->first();
        
        if ($user) {
            // Mark user as present, log attendance, etc.
            // You can fetch additional details from your own system if needed
        }
    }
    
    return response()->json(['success' => true], 200);
}
```

**Node.js/Express:**
```javascript
app.post('/api/attendance/webhook', (req, res) => {
    const { event, data } = req.body;
    
    if (event === 'attendance.created') {
        // Only user ID is sent
        const userId = data.id;  // This is the enroll_id (e.g., "87")
        
        // Find user in your system
        const user = await User.findOne({ externalId: userId });
        
        if (user) {
            // Mark user as present, log attendance, etc.
        }
    }
    
    res.status(200).json({ success: true });
});
```

**Python/Flask:**
```python
@app.route('/api/attendance/webhook', methods=['POST'])
def handle_webhook():
    data = request.json
    event = data.get('event')
    webhook_data = data.get('data')
    
    if event == 'attendance.created':
        # Only user ID is sent
        user_id = webhook_data['id']  # This is the enroll_id (e.g., "87")
        
        # Find user in your system
        user = User.query.filter_by(external_id=user_id).first()
        
        if user:
            # Mark user as present, log attendance, etc.
            pass
    
    return jsonify({'success': True}), 200
```

---

## Check Current Configuration

To see if minimal payload is enabled:

```bash
GET https://YOUR-SERVER-URL.com/api/v1/webhook/config
```

**Response:**
```json
{
    "success": true,
    "data": {
        "webhook_url": "https://your-app.com/api/attendance/webhook",
        "has_api_key": false,
        "minimal_payload": true,
        "configured": true
    }
}
```

---

## Change Configuration

You can change between minimal and full payload anytime:

### Switch to Minimal (Only ID):
```bash
POST https://YOUR-SERVER-URL.com/api/v1/webhook/configure
Content-Type: application/json

{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": true
}
```

### Switch to Full (All Details):
```bash
POST https://YOUR-SERVER-URL.com/api/v1/webhook/configure
Content-Type: application/json

{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": false
}
```

---

## Summary

✅ **Minimal Payload** - Sends only user ID:
```json
{
    "event": "attendance.created",
    "data": {
        "id": "87"
    }
}
```

✅ **Full Payload** - Sends all attendance details (default)

**Configure with `minimal_payload: true` to receive only the user ID!**


