# ZKTeco ADMS Configuration Guide

## What is ADMS?

ADMS = **Automatic Data Master Server** (also called **Attendance Device Management System**)

It's the protocol that allows ZKTeco devices to automatically push attendance logs and user data to your server via HTTP.

## Finding ADMS Settings on Your Device

The location of ADMS settings varies by device model and firmware. Here are common locations:

### Method 1: Communication → ADMS
1. Press **MENU** button
2. Go to: **System** → **Communication** → **ADMS**
3. Enable **ADMS: ON**
4. Set **Server IP**: Your Laravel server IP
5. Set **Server Port**: Your server port (usually 80 or 8000)
6. Set **Server Path**: `/iclock/getrequest` (or leave default)

### Method 2: Network → Push Server
1. Press **MENU** button
2. Go to: **System** → **Network** → **Push Server**
3. Enable **Push Server: ON**
4. Configure server settings

### Method 3: Communication → Push Mode
1. Press **MENU** button
2. Go to: **System** → **Communication** → **Push Mode**
3. Enable push mode
4. Configure server settings

### Method 4: Advanced Settings
1. Press **MENU** button
2. Go to: **System** → **Advanced** → **Server Settings**
3. Look for **ADMS**, **Push Server**, or **Data Server** options

### Method 5: Using ZKTeco Software
If you can't find it on the device:
1. Install **ZKTeco Time Attendance Software** (free download from ZKTeco website)
2. Connect to device via software
3. Go to: **Device Settings** → **Communication** → **ADMS Settings**
4. Configure and upload settings to device

## Configuration Values

Based on your setup:

- **Server IP**: `192.168.56.1` (your Laravel server)
- **Server Port**: `8000` (your Laravel port)
- **Server Path**: `/iclock/getrequest`
- **Protocol**: HTTP (not HTTPS unless you've configured SSL)

## Alternative: Using Device Serial Number

Some devices use the device serial number (SN) for identification. Your device SN should be visible in:
- Device menu: **System** → **Information** → **Serial Number**
- Or check logs when device connects (SN will be in the URL)

## Testing ADMS Connection

After configuring:

1. **Device should show**: "Connection OK" or "Server Connected"
2. **Check Laravel logs**: `storage/logs/laravel.log`
   - Look for: `=== ZKTECO DEVICE PING ===`
   - Should see device SN in logs

3. **Test from device** (if available):
   - Device menu: **System** → **Communication** → **Test Connection**
   - Should show success

## If ADMS Option is Not Available

Some older devices or certain firmware versions don't support ADMS. In that case:

1. **Use Manual Sync**: Use the "Sync from Device" button to pull data manually
2. **Update Firmware**: Check ZKTeco website for firmware updates
3. **Check Device Model**: Not all ZKTeco models support ADMS
   - Supported: iClock, ZKTime, most modern models
   - May not support: Very old models, some basic models

## ADMS Protocol Endpoints

Your server provides these endpoints (already configured):

1. **Device Ping/Commands**: 
   - `GET /iclock/getrequest?SN=DEVICE_SERIAL`
   - Returns: `OK` or commands like `USER ADD PIN=...`

2. **User Data Push**:
   - `POST /iclock/cdata?SN=DEVICE_SERIAL&table=USER&c=data`
   - Device sends: `PIN=1001\tName=John Doe\tPrivilege=0\tCard=12345678`

3. **Attendance Log Push**:
   - `POST /iclock/cdata?SN=DEVICE_SERIAL&table=ATTLOG&c=log`
   - Device sends: `PIN=1001\tDateTime=2025-09-02 14:32:11\tVerified=1\tStatus=0`

## Troubleshooting

### Device Shows "Connection Failed"
- Check server is running: `php artisan serve --host=0.0.0.0 --port=8000`
- Verify firewall allows port 8000
- Test from browser: `http://192.168.56.1:8000/iclock/getrequest?SN=TEST`
- Should see "OK" response

### Device Connects But No Data
- Check Laravel logs for push activity
- Verify device has users and attendance records
- Some devices need time to sync (check after a few minutes)

### Can't Find ADMS Settings
- Try all menu paths listed above
- Check device manual/documentation
- Use ZKTeco software to configure
- Contact ZKTeco support for your specific model

## Your Current Setup

- **Device IP**: `192.168.100.109`
- **Device Port**: `4370` (for direct connection)
- **Server IP**: `192.168.56.1`
- **Server Port**: `8000`
- **Push Endpoints**: Configured and working

## Next Steps

1. Find ADMS settings on your device (try methods above)
2. Configure with server IP and port
3. Test connection
4. Register a new user on device
5. Check if user appears automatically in system
6. Punch in/out and check attendance appears automatically






