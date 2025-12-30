# Attendance Sync Guide - How to See User Sign In/Out Records

## Overview

When a user (like User #1) punches in/out on the device using their fingerprint, the attendance record is stored on the device. You need to sync it to the system to view it.

## Step-by-Step: View User #1's Attendance

### Step 1: User Punches In/Out on Device

**On the ZKTeco device (192.168.100.109):**

1. User #1 places their finger on the fingerprint scanner
2. Device recognizes the fingerprint
3. Device shows "Check In" or "Check Out" message
4. Record is saved on the device

### Step 2: Sync Attendance from Device

**On the web interface:**

1. Go to **Attendance** page (or click "Sync from Device" button)
2. Click **"Sync from Device"** button
3. Enter:
   - **Device IP**: `192.168.100.109`
   - **Device Port**: `4370`
4. Click **"Sync"**
5. Attendance records will appear!

### Step 3: View Attendance Records

After syncing:

1. Go to **Attendance** page
2. You'll see all attendance records including User #1's
3. Records show:
   - **User Name**: User #1's name
   - **Enroll ID**: 1
   - **Punch Time**: Date and time of punch
   - **Status**: Check In (1) or Check Out (0)
   - **Verify Mode**: Fingerprint verification mode

## Quick Sync Methods

### Method 1: From Attendance Page
1. Go to **Attendance** → **View Attendance**
2. Click **"Sync from Device"** button
3. Enter device IP and port
4. Click **"Sync"**

### Method 2: From Sync Page
1. Go to **Attendance** → **Sync from Device**
2. Enter device IP: `192.168.100.109`
3. Enter device port: `4370`
4. Click **"Sync Attendance"**

### Method 3: Automatic (If ADMS Configured)
If you've configured ADMS on the device:
- Attendance records appear automatically
- No manual sync needed
- Records appear in real-time

## Viewing Specific User's Attendance

### Option 1: Filter on Attendance Page
- All attendance records are shown
- Look for User #1's name in the list
- Records are sorted by most recent first

### Option 2: View User Details
1. Go to **Users** page
2. Click on **User #1**
3. See user details and attendance count
4. Click **"View"** to see full attendance history

## Troubleshooting

### "No attendance records found"
**Possible causes:**
1. User hasn't punched in/out yet
2. Device attendance log is empty
3. Connection issue

**Solutions:**
1. Make sure user actually punched in/out on device
2. Check device: **Data Management** → **Attendance Records**
3. Verify device connection
4. Try syncing again

### "User not found in attendance"
**Possible causes:**
1. User's Enroll ID doesn't match
2. User not registered in system

**Solutions:**
1. Check user's Enroll ID matches device PIN
2. Sync users from device first
3. Make sure user is registered in system

### "Attendance not syncing"
**Possible causes:**
1. Device connection failed
2. Device has no records
3. Firmware compatibility issue

**Solutions:**
1. Test device connection first
2. Check device has attendance records
3. Try manual sync
4. Check logs: `storage/logs/laravel.log`

## Testing Attendance Sync

### Test Process:
1. **User #1 punches in** on device (place finger on scanner)
2. **Wait a few seconds** for device to save
3. **Sync attendance** from web interface
4. **Check Attendance page** - record should appear!

### Expected Result:
- Attendance record shows:
  - User: User #1's name
  - Enroll ID: 1
  - Punch Time: Current date/time
  - Status: 1 (Check In) or 0 (Check Out)
  - Verify Mode: Fingerprint code

## Quick Reference

- **Device IP**: `192.168.100.109`
- **Device Port**: `4370`
- **Sync Location**: Attendance → Sync from Device
- **View Location**: Attendance → View Attendance
- **Auto Sync**: Configure ADMS for automatic sync

## Next Steps

1. **Test it**: Have User #1 punch in/out
2. **Sync**: Click "Sync from Device"
3. **View**: Check Attendance page
4. **Verify**: Record should show User #1's name and punch time

That's it! Your attendance system is working! 🎉






