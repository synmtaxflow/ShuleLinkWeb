# ZKTeco Push SDK Setup Guide

## Overview

This system now supports **ZKTeco Push SDK**, which allows the device to automatically push data (user registrations and attendance logs) to the server via HTTP requests. This is more reliable than pulling data from the device.

## How It Works

Instead of the server connecting to the device (UDP/TCP), the device connects to the server (HTTP):
- Device sends HTTP requests to your Laravel server
- Server receives and processes the data automatically
- No need to manually sync - data comes in real-time

## Server Endpoints

Your Laravel server provides these endpoints:

1. **Device Ping/Check-in:**
   - `GET http://YOUR-SERVER-IP/iclock/getrequest?SN=DEVICE_SERIAL`
   - Device calls this to check if server is available
   - Server responds: `OK`

2. **User Registration Data:**
   - `POST http://YOUR-SERVER-IP/iclock/cdata?SN=DEVICE_SERIAL&table=OPERLOG&Stamp=9999`
   - Device sends user registration data
   - Server automatically creates/updates users in database

3. **Attendance Log Data:**
   - `POST http://YOUR-SERVER-IP/iclock/cdata?SN=DEVICE_SERIAL&table=ATTLOG&Stamp=9999`
   - Device sends attendance records (punch in/out)
   - Server automatically creates attendance records in database

## Device Configuration Steps

### Step 1: Find Your Server IP Address

Your Laravel server needs to be accessible from the device. Find your server's IP:

**Option A: If server is on same network as device:**
- Windows: Open Command Prompt, type `ipconfig`, look for "IPv4 Address"
- Example: `192.168.100.50`

**Option B: If server is on internet:**
- Use your public IP or domain name
- Make sure port 80 (or your Laravel port) is accessible

### Step 2: Configure Device for Push Mode

On your ZKTeco device (UF200-S):

1. **Press MENU button** on device
2. Go to: **System** → **Communication** → **ADMS** (or **Push Server**)
3. Enable **ADMS** (set to **ON**)
4. Set **Server IP**: Your Laravel server IP (e.g., `192.168.100.50`)
5. Set **Server Port**: `80` (or your Laravel port, usually 80 for HTTP)
6. Set **Server Path**: `/iclock/getrequest` (or leave default)
7. **Save** settings

**Note:** Some devices may have different menu paths:
- **System** → **Network** → **Push Server**
- **System** → **ADMS Settings**
- **Communication** → **Push Mode**

### Step 3: Verify Device Can Reach Server

1. On device, go to **System** → **Communication** → **Test Connection**
2. Device should show "Connection OK" or similar
3. If it fails, check:
   - Server IP is correct
   - Server is running
   - Firewall allows HTTP connections
   - Device and server are on same network (if using local IP)

### Step 4: Test Push Functionality

1. **Register a user on the device:**
   - Go to **User Management** → **User List** → **Add User**
   - Enter PIN (Enroll ID), Name, etc.
   - Save

2. **Check Laravel logs:**
   - Open `storage/logs/laravel.log`
   - Look for: `=== ZKTECO DEVICE DATA PUSH ===`
   - Should see user registration data

3. **Punch in/out on device:**
   - Use fingerprint scanner to punch in
   - Check logs again - should see attendance data

4. **Check database:**
   - Go to **Users** page - user should appear
   - Go to **Attendance** page - attendance record should appear

## Troubleshooting

### Device Not Connecting to Server

**Problem:** Device shows "Connection Failed" or doesn't push data

**Solutions:**
1. **Check server is running:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=80
   ```
   (Use `--host=0.0.0.0` to allow external connections)

2. **Check firewall:**
   - Windows Firewall: Allow port 80
   - Router firewall: Allow HTTP connections

3. **Test from device network:**
   - From another device on same network, open browser
   - Go to: `http://YOUR-SERVER-IP/iclock/getrequest?SN=TEST`
   - Should see "OK" response

4. **Check device IP settings:**
   - Device IP: `192.168.100.109`
   - Server IP: Must be on same network (e.g., `192.168.100.50`)

### Data Not Appearing in Database

**Problem:** Device connects but data doesn't save

**Solutions:**
1. **Check Laravel logs:**
   - `storage/logs/laravel.log`
   - Look for errors or warnings

2. **Verify database:**
   - Click "Verify Database" button on home page
   - Ensure tables exist

3. **Check CSRF protection:**
   - Push routes should be CSRF-exempt (already configured)
   - If issues persist, check `app/Http/Middleware/VerifyCsrfToken.php`

### Device Shows "Server Not Found"

**Problem:** Device can't find server

**Solutions:**
1. **Use IP address, not domain name** (for local networks)
2. **Check port number** - use `80` for HTTP
3. **Verify server is accessible:**
   ```bash
   # From device network, test:
   curl http://YOUR-SERVER-IP/iclock/getrequest?SN=TEST
   ```

## Current Configuration

- **Device IP:** `192.168.100.109`
- **Device Port:** `4370` (for direct connection, if needed)
- **Server Endpoints:**
  - `GET /iclock/getrequest` - Device ping
  - `POST /iclock/cdata` - Data push

## Data Format

### User Registration (OPERLOG)
```
PIN=2\tName=John Doe\tPri=0\tPasswd=\tCard=\tGrp=1\tTZ=0000000100000000\tVerify=0\tViceCard=\tStartDatetime=0\tEndDatetime=0\n
```

### Attendance Log (ATTLOG)
```
2\t2022-07-12 16:00:20\t1\t15\t\t0\t0\t\t\t43\n
```
Fields: PIN, DateTime, Status (0=Check In, 1=Check Out), VerifyMode, WorkCode, Reserved fields

## Benefits of Push SDK

✅ **Real-time data** - No need to manually sync  
✅ **More reliable** - Device pushes when data is available  
✅ **Automatic** - Works in background  
✅ **No connection issues** - Device initiates connection  
✅ **Works with firewall** - Uses standard HTTP  

## Switching Between Push and Pull Mode

- **Push Mode (Recommended):** Device pushes data automatically
- **Pull Mode:** Use "Sync from Device" button to manually pull data

You can use both modes simultaneously - push for real-time updates, pull for manual sync.






