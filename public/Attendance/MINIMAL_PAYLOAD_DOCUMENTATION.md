# Minimal Payload Documentation - Send Only User ID

## Overview

The Attendance System API supports **minimal payload mode** for webhooks. When enabled, the webhook will send **only the user ID** (enroll_id) when a user scans their fingerprint, excluding all other attendance details.

---

## Quick Start

### Enable Minimal Payload

Configure your webhook with `minimal_payload: true`:

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

## What You Receive

### Minimal Payload (minimal_payload: true)

When a user scans their fingerprint, you receive:

```json
{
    "event": "attendance.created",
    "data": {
        "id": "87"
    }
}
```

**Only the user ID (enroll_id) is sent** - no other information.

### Full Payload (minimal_payload: false or not set)

When minimal payload is disabled, you receive complete attendance data:

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

## API Endpoints

### 1. Configure Webhook with Minimal Payload

**Endpoint:** `POST /api/v1/webhook/configure`

**Request Body:**
```json
{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": true,
    "api_key": "optional-api-key"
}
```

**Parameters:**
- `webhook_url` (required): Your webhook endpoint URL
- `minimal_payload` (optional): Set to `true` to enable minimal payload (default: `false`)
- `api_key` (optional): API key for webhook authentication

**Response (200 OK):**
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

### 2. Get Webhook Configuration

**Endpoint:** `GET /api/v1/webhook/config`

**Response (200 OK):**
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

**Fields:**
- `minimal_payload`: `true` if minimal payload is enabled, `false` otherwise
- `configured`: `true` if webhook is configured

---

### 3. Test Webhook

**Endpoint:** `POST /api/v1/webhook/test`

Tests the webhook connection. Sends a test payload to verify your endpoint is working.

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Test webhook sent successfully"
}
```

---

## Code Examples

### PHP (Laravel)

```php
use Illuminate\Support\Facades\Http;

// Configure webhook with minimal payload
$response = Http::post('https://YOUR-SERVER-URL.com/api/v1/webhook/configure', [
    'webhook_url' => 'https://your-app.com/api/attendance/webhook',
    'minimal_payload' => true,
]);

// Handle minimal webhook payload
Route::post('/api/attendance/webhook', [AttendanceController::class, 'handleWebhook']);

public function handleWebhook(Request $request)
{
    $event = $request->input('event');
    $data = $request->input('data');
    
    if ($event === 'attendance.created') {
        // Only user ID is sent
        $userId = $data['id'];  // e.g., "87"
        
        // Find user in your system
        $user = User::where('external_id', $userId)->first();
        
        if ($user) {
            // Process attendance - mark as present, log, etc.
            // You have the user ID, fetch other details from your system if needed
        }
    }
    
    return response()->json(['success' => true], 200);
}
```

---

### JavaScript (Node.js/Express)

```javascript
const axios = require('axios');

// Configure webhook with minimal payload
await axios.post('https://YOUR-SERVER-URL.com/api/v1/webhook/configure', {
    webhook_url: 'https://your-app.com/api/attendance/webhook',
    minimal_payload: true
});

// Handle minimal webhook payload
app.post('/api/attendance/webhook', async (req, res) => {
    const { event, data } = req.body;
    
    if (event === 'attendance.created') {
        // Only user ID is sent
        const userId = data.id;  // e.g., "87"
        
        // Find user in your system
        const user = await User.findOne({ externalId: userId });
        
        if (user) {
            // Process attendance
            // You have the user ID, fetch other details from your system if needed
        }
    }
    
    res.status(200).json({ success: true });
});
```

---

### Python (Flask)

```python
import requests

# Configure webhook with minimal payload
response = requests.post(
    'https://YOUR-SERVER-URL.com/api/v1/webhook/configure',
    json={
        'webhook_url': 'https://your-app.com/api/attendance/webhook',
        'minimal_payload': True
    }
)

# Handle minimal webhook payload
@app.route('/api/attendance/webhook', methods=['POST'])
def handle_webhook():
    data = request.json
    event = data.get('event')
    webhook_data = data.get('data')
    
    if event == 'attendance.created':
        # Only user ID is sent
        user_id = webhook_data['id']  # e.g., "87"
        
        # Find user in your system
        user = User.query.filter_by(external_id=user_id).first()
        
        if user:
            # Process attendance
            # You have the user ID, fetch other details from your system if needed
            pass
    
    return jsonify({'success': True}), 200
```

---

### cURL

```bash
# Configure webhook with minimal payload
curl -X POST https://YOUR-SERVER-URL.com/api/v1/webhook/configure \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": true
  }'

# Check configuration
curl -X GET https://YOUR-SERVER-URL.com/api/v1/webhook/config \
  -H "Accept: application/json"

# Test webhook
curl -X POST https://YOUR-SERVER-URL.com/api/v1/webhook/test \
  -H "Accept: application/json"
```

---

## Comparison Table

| Feature | Minimal Payload | Full Payload |
|---------|----------------|--------------|
| **User ID (enroll_id)** | ✅ Yes | ✅ Yes |
| **User Name** | ❌ No | ✅ Yes |
| **User Email** | ❌ No | ✅ Yes |
| **Check-in Time** | ❌ No | ✅ Yes |
| **Check-out Time** | ❌ No | ✅ Yes |
| **Attendance Date** | ❌ No | ✅ Yes |
| **Status** | ❌ No | ✅ Yes |
| **Verify Mode** | ❌ No | ✅ Yes |
| **Device IP** | ❌ No | ✅ Yes |
| **Timestamp** | ❌ No | ✅ Yes |
| **Payload Size** | ~50 bytes | ~300 bytes |
| **Data Transfer** | Minimal | Full |

---

## Use Cases

### When to Use Minimal Payload

✅ **Use minimal payload when:**
- You only need to know **which user** scanned
- You want to **reduce data transfer**
- You want **simpler webhook handling**
- You have user details stored in your own system
- You want to **minimize bandwidth usage**
- You process attendance data from your own database

### When to Use Full Payload

✅ **Use full payload when:**
- You need **complete attendance information** immediately
- You want **check-in/check-out times** in the webhook
- You need **device information**
- You want **all data in one request**
- You don't store attendance data in your system

---

## Switching Between Modes

You can switch between minimal and full payload anytime by reconfiguring the webhook:

### Switch to Minimal Payload

```bash
POST https://YOUR-SERVER-URL.com/api/v1/webhook/configure
{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": true
}
```

### Switch to Full Payload

```bash
POST https://YOUR-SERVER-URL.com/api/v1/webhook/configure
{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": false
}
```

---

## Example Workflow

### Scenario: Minimal Payload Workflow

1. **Configure webhook with minimal payload:**
   ```json
   {
       "webhook_url": "https://your-app.com/api/attendance/webhook",
       "minimal_payload": true
   }
   ```

2. **User scans fingerprint on device**

3. **Your webhook receives:**
   ```json
   {
       "event": "attendance.created",
       "data": {
           "id": "87"
       }
   }
   ```

4. **Your system processes:**
   - Extract user ID: `"87"`
   - Find user in your database using this ID
   - Mark user as present
   - Log attendance with current timestamp
   - Fetch additional details from your own system if needed

5. **Return 200 OK** to acknowledge receipt

---

## Best Practices

1. **Always return 200 status code** - Acknowledge receipt quickly
2. **Process asynchronously** - Don't block the webhook response
3. **Handle errors gracefully** - Log failures but return 200
4. **Validate user ID** - Check if user exists in your system
5. **Idempotent processing** - Handle duplicate webhooks gracefully
6. **Use HTTPS** - Always use secure connections in production

---

## Troubleshooting

### Issue: Minimal payload not working

**Solution:**
1. Check configuration: `GET /api/v1/webhook/config`
2. Verify `minimal_payload` is set to `true`
3. Reconfigure if needed: `POST /api/v1/webhook/configure` with `minimal_payload: true`

### Issue: Still receiving full payload

**Solution:**
- Make sure you set `minimal_payload: true` when configuring
- Check the configuration response to confirm it's enabled
- Clear cache if using Laravel cache (optional)

### Issue: Webhook not receiving data

**Solution:**
1. Test webhook: `POST /api/v1/webhook/test`
2. Verify webhook URL is publicly accessible
3. Check webhook endpoint returns 200 status
4. Verify user is registered and scanned on device

---

## Testing

### Test Configuration

```bash
# Configure
POST /api/v1/webhook/configure
{
    "webhook_url": "https://webhook.site/YOUR-UNIQUE-ID",
    "minimal_payload": true
}

# Check
GET /api/v1/webhook/config

# Test
POST /api/v1/webhook/test
```

### Test with Real Attendance

1. Register a user: `POST /api/v1/users/register` with `{"id": "87", "name": "Test User"}`
2. Have user scan fingerprint on device
3. Check webhook.site - you should see only: `{"event":"attendance.created","data":{"id":"87"}}`

---

## Summary

### Minimal Payload Configuration

```json
{
    "webhook_url": "https://your-app.com/api/attendance/webhook",
    "minimal_payload": true
}
```

### Minimal Payload Received

```json
{
    "event": "attendance.created",
    "data": {
        "id": "87"
    }
}
```

### Benefits

- ✅ **Reduced data transfer** - Only essential information
- ✅ **Simpler processing** - Just user ID to handle
- ✅ **Lower bandwidth** - Smaller payload size
- ✅ **Faster processing** - Less data to parse

---

## Related Documentation

- **`WEBHOOK_PAYLOAD_GUIDE.md`** - Complete webhook payload documentation
- **`DEVELOPER_API_INTEGRATION_GUIDE.md`** - Full API integration guide
- **`API_INTEGRATION_QUICK_START.md`** - Quick start guide

---

**Ready to use minimal payload!** Configure your webhook with `minimal_payload: true` to receive only user IDs.


