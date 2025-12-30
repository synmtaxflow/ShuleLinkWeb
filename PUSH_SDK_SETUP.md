# ZKTeco Push SDK Connection Setup Guide

## ⚠️ IMPORTANT: Attendance Tracking is DISABLED
**Currently, we are only testing connection between device and server.**
**Attendance records will NOT be saved until connection is verified.**

## Server Configuration

**Server IP:** `192.168.100.105`  
**Server Port:** `8000`

### ⚠️ IMPORTANT: Running Laravel Server

**Laravel server MUST run on `0.0.0.0` (not `127.0.0.1`) to accept requests from network devices.**

**Correct command:**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Wrong command (will NOT work with devices):**
```bash
php artisan serve  # Defaults to 127.0.0.1 - only accessible from localhost
```

**Why?**
- `127.0.0.1` (localhost) - Only accessible from the same computer
- `0.0.0.0` - Accessible from any network interface (allows device to connect)

## Push SDK Endpoints

System ime-setup endpoints zifuatazo kwa ajili ya Push SDK:

1. **Device Ping Endpoint:**
   - URL: `http://192.168.100.105:8000/iclock/getrequest?SN=XXXXXXXXXX`
   - Method: `GET`
   - Purpose: Device inatumia hii ku-check kama server ina-respond

2. **Attendance Data Endpoint:**
   - URL: `http://192.168.100.105:8000/iclock/cdata?SN=XXXXXXXXXX&table=ATTLOG&c=log`
   - Method: `POST`
   - Purpose: Device inatumia hii ku-send attendance records

## Device Configuration Steps

### Step 1: Enable ADMS (Push SDK) on Device

1. On device: Press **MENU** button
2. Navigate to: **System → Communication → ADMS** (or **Push Server**)
3. Enable ADMS: **ON**

### Step 2: Configure Server Settings

1. **Server IP:** `192.168.100.105`
2. **Server Port:** `8000`
3. **Server Path:** `/iclock/getrequest`
4. Save settings

### Step 3: Verify Connection

1. Device ita-ping server automatically
2. Check server logs: `storage/logs/laravel.log`
3. Look for: `ZKTeco Push: Device ping received`

## How It Works

1. **Student scans fingerprint** on device
2. **Device sends HTTP POST** to server with attendance data
3. **System processes** and stores attendance record automatically
4. **Record appears** in system immediately (no manual sync needed)

## Attendance Data Format

Device ina-send data kwa format hii:

```
PIN=1001	DateTime=2025-11-30 14:32:13	Verified=0	Status=0
```

System ina-process hii na:
- Match `PIN` (fingerprint_id) na student
- Create attendance record automatically
- Mark as "Present"
- Store scan time kwenye remark

## Testing

### Test 1: Check if endpoints are accessible

```bash
# Test ping endpoint
curl http://192.168.100.105:8000/iclock/getrequest?SN=TEST123

# Should return: OK
```

### Test 2: Check server logs

```bash
# View logs
tail -f storage/logs/laravel.log

# Search for Push SDK messages
grep "ZKTeco Push" storage/logs/laravel.log
```

### Test 3: Test from device

1. Configure device with server settings above
2. Scan fingerprint on device
3. Check logs for attendance record
4. Verify attendance appears in system

## Troubleshooting

### Device cannot connect to server

1. **Check network:**
   - Verify device na server ziko kwenye same network
   - Ping server from device network: `ping 192.168.100.105`

2. **Check firewall:**
   - Ensure port 8000 is open
   - Check Windows Firewall settings

3. **Check server:**
   - Verify Laravel server is running on port 8000
   - Check if server is accessible: `http://192.168.100.105:8000`

### Attendance not appearing

1. **Check logs:**
   - View `storage/logs/laravel.log`
   - Look for "ZKTeco Push" messages
   - Check for errors

2. **Verify fingerprint_id:**
   - Ensure student has `fingerprint_id` set
   - Verify `fingerprint_id` matches device PIN

3. **Check device settings:**
   - Verify ADMS is enabled
   - Verify server IP/port are correct
   - Check device logs (if available)

## Environment Variables

Add these to your `.env` file (optional):

```env
# Server Configuration for Push SDK
APP_SERVER_IP=192.168.100.105
APP_SERVER_PORT=8000

# ZKTeco Device Configuration
ZKTECO_IP=192.168.100.108
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0
```

## Notes

- **No authentication required** - Routes ziko public kwa sababu device ina-call moja kwa moja
- **Automatic attendance** - System ina-create attendance records automatically
- **Real-time** - Records zina-appear immediately baada ya scan
- **Logging** - All Push SDK activity ina-log kwenye `storage/logs/laravel.log`

## Support

Kama una issues:
1. Check logs: `storage/logs/laravel.log`
2. Verify device settings
3. Test endpoints manually
4. Check network connectivity

---

**Last Updated:** December 2024  
**Server IP:** 192.168.100.105  
**Server Port:** 8000

