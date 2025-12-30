# Quick Start Guide - ZKTeco Attendance System

## 5-Minute Setup

### Step 1: Configure Device (2 minutes)
1. **Set Device IP**: Menu → System → Communication → Network
   - IP: `192.168.100.108`
   - Port: `4370`
   - Comm Key: `0`

2. **Enable Push SDK**: Menu → System → Communication → ADMS
   - Enable: **ON**
   - Server IP: Your server IP (e.g., `192.168.56.1`)
   - Server Port: `8000`
   - Server Path: `/iclock/getrequest`

### Step 2: Configure System (1 minute)
1. Edit `.env`:
   ```env
   ZKTECO_IP=192.168.100.108
   ZKTECO_PORT=4370
   ZKTECO_PASSWORD=0
   ```

2. Run migrations:
   ```bash
   php artisan migrate
   ```

### Step 3: Create First User (1 minute)
1. Go to: **Users → Add New User**
2. Fill:
   - Name: `John Doe`
   - Email: `john@example.com`
   - Password: `password123`
   - Enroll ID: `1001`
3. Click **Create User**

### Step 4: Register to Device (1 minute)
1. Click **📤 Register to Device** button
2. Confirm IP/port
3. Wait for success message

### Step 5: Test Attendance (1 minute)
1. On device: Scan fingerprint (Enroll ID: 1001)
2. Go to: **Attendance** page
3. Record should appear automatically!

---

## Common Workflows

### Workflow A: New User Setup
```
1. Create User (System)
   ↓
2. Register to Device
   ↓
3. Enroll Fingerprint (Device)
   ↓
4. Test Scan → Attendance Appears
```

### Workflow B: Existing Device Users
```
1. Sync from Device
   ↓
2. Users Appear in System
   ↓
3. Update User Details (Optional)
   ↓
4. Ready to Use
```

### Workflow C: Daily Operations
```
1. Users Scan on Device
   ↓
2. Attendance Appears Automatically (Push SDK)
   ↓
3. View on Attendance Page
   ↓
4. Generate Reports (Optional)
```

---

## Quick Commands

### Test Device Connection
```bash
# Via Web UI
Go to: Device Test → Enter IP/Port → Test Connection

# Via Tinker
php artisan tinker
$zkteco = new \App\Services\ZKTecoService('192.168.100.108', 4370);
$zkteco->connect();
```

### Sync Attendance
```bash
# Via Web UI
Attendance → Quick Sync

# Via API
POST /attendances/sync
{
    "ip": "192.168.100.108",
    "port": 4370
}
```

### Register Users
```bash
# Single User
POST /users/{id}/register-device

# Batch (All Unregistered)
POST /users/sync-to-device
```

---

## Troubleshooting Quick Fixes

| Problem | Quick Fix |
|---------|-----------|
| Can't connect | Check IP, ping device, verify port 4370 |
| Auth failed | Check Comm Key matches device |
| User not registering | Try manual registration on device, then sync |
| No attendance | Verify users scanned, check Push SDK config |
| Duplicate records | System prevents automatically, clear and re-sync if needed |

---

## Key URLs

- **Dashboard**: `http://127.0.0.1:8000/dashboard`
- **Users**: `http://127.0.0.1:8000/users`
- **Attendance**: `http://127.0.0.1:8000/attendances`
- **Device Test**: `http://127.0.0.1:8000/zkteco/test`
- **Push Setup**: `http://127.0.0.1:8000/zkteco/push-setup`

---

## Important Notes

✅ **DO:**
- Use numeric Enroll IDs (1-65535)
- Keep Comm Key in sync between device and system
- Test connection before batch operations
- Enable Push SDK for real-time attendance

❌ **DON'T:**
- Use duplicate Enroll IDs
- Change device IP without updating system
- Register users manually if system registration works
- Skip fingerprint enrollment (optional but recommended)

---

**For detailed documentation, see:** `ATTENDANCE_SYSTEM_DOCUMENTATION.md`
