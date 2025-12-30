# Webhook Payload Guide - What Data is Sent When User Scans Fingerprint

## Overview

When a user places their finger on the biometric scanner, the Attendance System automatically sends a **POST request** to your configured webhook URL with attendance data.

---

## When Webhook is Sent

The webhook is sent automatically when:

1. ✅ **User checks in** (first scan of the day)
2. ✅ **User checks out** (second scan of the day)
3. ✅ **Attendance record is updated** (check-out time added)

---

## Webhook Request Details

### HTTP Method
```
POST
```

### Headers
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer YOUR-API-KEY (if configured)
```

### Request URL
Your configured webhook URL (e.g., `https://your-app.com/api/attendance/webhook`)

---

## Webhook Payload (JSON Body)

### Complete Payload Structure

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

## Field Descriptions

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| **event** | string | Event type (always `"attendance.created"`) | `"attendance.created"` |
| **data.id** | integer | Internal attendance record ID | `123` |
| **data.user_id** | integer | Internal user ID in attendance system | `1` |
| **data.enroll_id** | string | **Your user ID** (the ID you sent when registering) | `"87"` |
| **data.user_name** | string | User's name | `"MWAKABANJE"` |
| **data.user_email** | string | User's email (auto-generated) | `"user_87@attendance.local"` |
| **data.attendance_date** | string | Date of attendance (YYYY-MM-DD) | `"2025-12-01"` |
| **data.check_in_time** | string | Check-in time (YYYY-MM-DD HH:MM:SS) | `"2025-12-01 08:00:00"` |
| **data.check_out_time** | string | Check-out time (YYYY-MM-DD HH:MM:SS) or `null` | `"2025-12-01 17:00:00"` |
| **data.status** | integer | Status code (1 = Check In, 0 = Check Out) | `1` |
| **data.verify_mode** | string | Verification method | `"Fingerprint"` |
| **data.device_ip** | string | IP address of the biometric device | `"192.168.100.108"` |
| **data.timestamp** | string | When webhook was sent (YYYY-MM-DD HH:MM:SS) | `"2025-12-01 17:00:00"` |

---

## Example Scenarios

### Scenario 1: User Checks In (First Scan)

**When:** User scans fingerprint at 8:00 AM

**Webhook Payload:**
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
        "check_out_time": null,
        "status": 1,
        "verify_mode": "Fingerprint",
        "device_ip": "192.168.100.108",
        "timestamp": "2025-12-01 08:00:00"
    }
}
```

**Note:** `check_out_time` is `null` because user hasn't checked out yet.

---

### Scenario 2: User Checks Out (Second Scan)

**When:** Same user scans fingerprint again at 5:00 PM

**Webhook Payload:**
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
        "status": 0,
        "verify_mode": "Fingerprint",
        "device_ip": "192.168.100.108",
        "timestamp": "2025-12-01 17:00:00"
    }
}
```

**Note:** This is an **update** to the same attendance record. The `check_out_time` is now filled in.

---

## Important Fields for External System

### Most Important Fields

1. **`enroll_id`** - This is **YOUR user ID** that you sent when registering the user
   - Use this to identify the user in your system
   - Example: `"87"` matches the ID you sent: `{"id": "87", "name": "MWAKABANJE"}`

2. **`check_in_time`** - When user checked in
   - Format: `"YYYY-MM-DD HH:MM:SS"`
   - Example: `"2025-12-01 08:00:00"`

3. **`check_out_time`** - When user checked out
   - Format: `"YYYY-MM-DD HH:MM:SS"` or `null`
   - Example: `"2025-12-01 17:00:00"` or `null`

4. **`attendance_date`** - Date of attendance
   - Format: `"YYYY-MM-DD"`
   - Example: `"2025-12-01"`

---

## How to Handle Webhook in Your System

### Step 1: Create Webhook Endpoint

**Laravel Example:**
```php
Route::post('/api/attendance/webhook', [AttendanceController::class, 'handleWebhook']);

public function handleWebhook(Request $request)
{
    $event = $request->input('event');
    $data = $request->input('data');
    
    if ($event === 'attendance.created') {
        // Find user by enroll_id (your user ID)
        $user = User::where('external_id', $data['enroll_id'])->first();
        
        if ($user) {
            // Create or update attendance record
            Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'attendance_date' => $data['attendance_date']
                ],
                [
                    'check_in_time' => $data['check_in_time'],
                    'check_out_time' => $data['check_out_time'],
                    'status' => $data['status']
                ]
            );
        }
    }
    
    // Always return 200 to acknowledge receipt
    return response()->json(['success' => true], 200);
}
```

**Node.js/Express Example:**
```javascript
app.post('/api/attendance/webhook', (req, res) => {
    const { event, data } = req.body;
    
    if (event === 'attendance.created') {
        // Find user by enroll_id (your user ID)
        const user = await User.findOne({ externalId: data.enroll_id });
        
        if (user) {
            // Create or update attendance record
            await Attendance.findOneAndUpdate(
                {
                    userId: user._id,
                    attendanceDate: data.attendance_date
                },
                {
                    checkInTime: data.check_in_time,
                    checkOutTime: data.check_out_time,
                    status: data.status
                },
                { upsert: true, new: true }
            );
        }
    }
    
    // Always return 200 to acknowledge receipt
    res.status(200).json({ success: true });
});
```

**Python/Flask Example:**
```python
@app.route('/api/attendance/webhook', methods=['POST'])
def handle_webhook():
    data = request.json
    event = data.get('event')
    webhook_data = data.get('data')
    
    if event == 'attendance.created':
        # Find user by enroll_id (your user ID)
        user = User.query.filter_by(external_id=webhook_data['enroll_id']).first()
        
        if user:
            # Create or update attendance record
            attendance = Attendance.query.filter_by(
                user_id=user.id,
                attendance_date=webhook_data['attendance_date']
            ).first()
            
            if attendance:
                attendance.check_in_time = webhook_data['check_in_time']
                attendance.check_out_time = webhook_data['check_out_time']
                attendance.status = webhook_data['status']
            else:
                attendance = Attendance(
                    user_id=user.id,
                    attendance_date=webhook_data['attendance_date'],
                    check_in_time=webhook_data['check_in_time'],
                    check_out_time=webhook_data['check_out_time'],
                    status=webhook_data['status']
                )
                db.session.add(attendance)
            
            db.session.commit()
    
    # Always return 200 to acknowledge receipt
    return jsonify({'success': True}), 200
```

---

## Webhook Requirements

Your webhook endpoint **MUST**:

1. ✅ Accept **POST requests**
2. ✅ Return **200 status code** (to acknowledge receipt)
3. ✅ Be **publicly accessible** (not localhost)
4. ✅ Use **HTTPS** in production
5. ✅ Process data **asynchronously** (return quickly, process in background)
6. ✅ Handle **duplicate requests** (webhooks may be retried)

---

## Testing Webhook

### Step 1: Configure Webhook URL

```bash
POST https://YOUR-SERVER-URL.com/api/v1/webhook/configure
Content-Type: application/json

{
    "webhook_url": "https://your-app.com/api/attendance/webhook"
}
```

### Step 2: Test Webhook

```bash
POST https://YOUR-SERVER-URL.com/api/v1/webhook/test
```

This sends a test payload to verify your webhook is working.

### Step 3: Real Test

Have a user scan their fingerprint on the device. Your webhook will automatically receive the attendance data.

---

## Complete Flow

```
1. User places finger on scanner
   ↓
2. Device recognizes fingerprint
   ↓
3. Attendance System creates/updates attendance record
   ↓
4. Webhook is automatically sent to your URL
   ↓
5. Your system receives POST request with JSON payload
   ↓
6. Your system processes the attendance data
   ↓
7. Your system returns 200 OK
```

---

## Summary

**When a user scans their fingerprint, your webhook receives:**

- ✅ **enroll_id**: Your user ID (e.g., `"87"`)
- ✅ **user_name**: User's name (e.g., `"MWAKABANJE"`)
- ✅ **check_in_time**: When user checked in
- ✅ **check_out_time**: When user checked out (or null)
- ✅ **attendance_date**: Date of attendance
- ✅ **status**: Check-in (1) or Check-out (0)
- ✅ **timestamp**: When webhook was sent

**Use `enroll_id` to match the user in your system!**

---

**Ready to receive webhooks!** Configure your webhook URL and start receiving real-time attendance data.


