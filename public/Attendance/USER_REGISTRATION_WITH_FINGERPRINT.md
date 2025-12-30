# User Registration with Fingerprint - Step by Step Guide

## Overview

To register a new user with fingerprint, you need to:
1. **Create user in the system** (web interface)
2. **Register user to device** (web interface - adds user to device)
3. **Enroll fingerprint on device** (must be done directly on the physical device)

## Step 1: Create User in System

### Via Web Interface:

1. Go to **Users** page
2. Click **"Create New User"**
3. Fill in:
   - **Name**: User's full name
   - **Email**: User's email (must be unique)
   - **Enroll ID**: **MUST be numeric** (e.g., 1, 2, 3, 100, 1001)
     - This is the PIN/User ID on the device
     - Must be between 1 and 65535
     - Must be unique
4. Click **"Create User"**

### Important Notes:
- **Enroll ID must be numeric** - no letters or special characters
- Enroll ID is what the device uses to identify the user
- This Enroll ID will be used when enrolling fingerprints

## Step 2: Register User to Device

After creating the user in the system:

1. On the **Users** page, find your newly created user
2. Click **"Register to Device"** button
3. Enter:
   - **Device IP**: `192.168.100.109` (your device IP)
   - **Device Port**: `4370` (default)
4. Click **"Register"**

### What This Does:
- Connects to the device
- Adds the user to the device's user list
- User can now be found on the device
- **This does NOT enroll fingerprints** - that's step 3

### Verification:
- Click **"List Device Users"** to verify user appears on device
- Or check device directly: **User Management** → **User List**

## Step 3: Enroll Fingerprint on Device

**⚠️ IMPORTANT: Fingerprint enrollment MUST be done directly on the physical device. It cannot be done from the web interface.**

### On the ZKTeco Device (192.168.100.109):

1. **Press MENU** button on device
2. Navigate to: **User Management** → **Enroll Fingerprint**
   - (Some devices: **Menu** → **User** → **Fingerprint Enroll**)
   - (Some devices: **Menu** → **Enroll** → **Fingerprint**)
3. **Enter the Enroll ID** (the numeric ID you used in Step 1)
   - Example: If you created user with Enroll ID `1001`, enter `1001`
4. **Place finger on scanner** when prompted
5. **Lift finger** and place again (device will prompt 3 times)
6. Device will show **"Enrollment Successful"** or similar

### Finger Enrollment Tips:
- Use the **same finger** all 3 times
- Place finger **firmly** on scanner
- **Clean finger** before enrolling
- **Common fingers**: Index finger or thumb work best
- Device may allow multiple fingers per user (enroll 2-3 fingers for reliability)

### After Enrollment:
- Device will confirm successful enrollment
- You can enroll additional fingers if needed
- Return to web interface to verify

## Step 4: Verify Fingerprint Enrollment

Back on the web interface:

1. Go to **Users** page
2. Click on the user you just enrolled
3. Click **"Check Fingerprints"** button
4. Enter device IP and port
5. System will show:
   - How many fingerprints are enrolled
   - Which finger IDs are enrolled (0-9)

### What You Should See:
- **"✓ Fingerprints found!"**
- **"Enrolled fingers: 0, 1"** (example)
- **"Total: 2 fingerprint(s)"**

## Complete Workflow Summary

```
1. Create User (Web) 
   ↓
2. Register to Device (Web)
   ↓
3. Enroll Fingerprint (Device - Physical)
   ↓
4. Verify Enrollment (Web)
   ↓
5. User can now punch in/out with fingerprint!
```

## Troubleshooting

### "Enroll ID must be numeric"
- Make sure Enroll ID contains only numbers (0-9)
- No letters, spaces, or special characters

### "User not found on device"
- Make sure you completed Step 2 (Register to Device)
- Check device connection
- Verify user appears in "List Device Users"

### "No fingerprints enrolled"
- Make sure you completed Step 3 on the physical device
- Verify you used the correct Enroll ID
- Try enrolling again on the device

### "Fingerprint enrollment failed on device"
- Clean the scanner surface
- Clean your finger
- Try a different finger
- Make sure finger is placed firmly
- Some devices require 3-4 attempts

## Alternative: Register User Directly on Device

If you prefer to register users directly on the device:

1. **On Device**: **User Management** → **Add User**
   - Enter PIN (Enroll ID)
   - Enter Name
   - Save
2. **Enroll Fingerprint** on device (same as Step 3 above)
3. **Sync from Device** (Web interface):
   - Go to Users page
   - Click **"Sync Users from Device"**
   - User will appear in system automatically

This method works if ADMS is configured, or you can use manual sync.

## Quick Reference

- **Device IP**: `192.168.100.109`
- **Device Port**: `4370`
- **Enroll ID Format**: Numeric only (1-65535)
- **Fingerprint Enrollment**: Must be done on physical device
- **Verification**: Use "Check Fingerprints" button

## Next Steps After Registration

Once user is registered with fingerprint:

1. **Test Punch In/Out**: Use fingerprint on device
2. **Sync Attendance**: Attendance records will appear automatically (if ADMS configured) or use "Sync from Device"
3. **View Records**: Check Attendance page to see punch records






