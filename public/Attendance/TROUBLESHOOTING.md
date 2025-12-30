# ZKTeco Device Troubleshooting Guide

## Common Issues and Solutions

### 1. Users Not Appearing on Device After Registration

**Possible Causes:**
- Device needs to be enabled before registration
- Enroll ID format is incorrect (must be numeric)
- Device connection issues
- Device needs to be refreshed

**Solutions:**
1. **Check Enroll ID Format:**
   - Enroll ID must be numeric only (e.g., 1, 2, 3...)
   - Must be between 1 and 65535
   - Maximum 9 digits

2. **Verify Device Connection:**
   - Use "Test Device Connection" to verify connectivity
   - Ensure device IP and port are correct (default: 192.168.100.127:4370)

3. **Check Device Users:**
   - Click "List Device Users" button to see all users on the device
   - This helps verify if registration was successful

4. **Device Settings:**
   - Ensure device is not in sleep mode
   - Some devices need to be "enabled" before accepting new users
   - Try restarting the device if issues persist

### 2. Fingerprint Enrollment Not Working

**Important:** Fingerprint enrollment **MUST** be done directly on the device. It cannot be done from the web interface.

**Steps to Enroll Fingerprints:**
1. On the ZKTeco device screen, press **MENU** button
2. Navigate to: **User Management** → **Enroll Fingerprint**
3. Enter the user's **Enroll ID** (e.g., 1, 2, 3...)
4. When prompted, place the finger on the scanner
5. Lift and place the same finger again (repeat 3 times)
6. The device will confirm successful enrollment

**After Enrollment:**
- Click "Check Fingerprints" button to verify enrollment
- The system will show how many fingerprints are enrolled

### 3. Registration Process

**Correct Workflow:**
1. **Create User** in the system with a unique numeric Enroll ID
2. **Register to Device** - This adds the user to the device
3. **Enroll Fingerprint** - Do this directly on the device
4. **Check Fingerprints** - Verify enrollment was successful
5. **Sync Attendance** - Pull attendance records from device

### 4. Testing Device Connection

If you're having issues:
1. Go to "Device Test" page
2. Enter device IP and port
3. Click "Test Connection"
4. If connection fails, check:
   - Network connectivity (ping the device IP)
   - Firewall settings
   - Device is powered on and connected to network

### 5. Device Requirements

- Device must be on the same network as your server
- Port 4370 must be open (default ZKTeco communication port)
- Device should have TCP/IP enabled
- Some devices require a "Comm Key" or password - set this in config/zkteco.php

## Need More Help?

- Check device logs in `storage/logs/laravel.log`
- Use "List Device Users" to see what's actually on the device
- Verify Enroll IDs are unique and numeric
- Ensure users are registered before trying to sync attendance







