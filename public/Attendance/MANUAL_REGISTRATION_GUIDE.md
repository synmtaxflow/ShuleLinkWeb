# Manual User Registration Guide

## Problem: Direct Registration Not Working

Your ZKTeco device (UF200-S, firmware 6.60) has a firmware compatibility issue that prevents direct registration from the web interface. The device responds to registration commands but doesn't actually add users.

## Solution: Register Users Directly on Device

Since direct registration doesn't work, register users **directly on the physical device**, then sync them to the system.

## Step-by-Step Process

### Option 1: Register on Device First, Then Sync (Recommended)

#### Step 1: Register User on Device

**On your ZKTeco device (192.168.100.109):**

1. Press **MENU** button
2. Navigate to: **User Management** → **User List** → **Add User** (or **New User**)
3. Enter:
   - **PIN** (Enroll ID): Use a number (e.g., 4, 5, 100)
   - **Name**: User's full name
   - **Password**: Leave empty (for fingerprint devices)
   - **Card**: Leave empty (if not using cards)
4. **Save** the user

#### Step 2: Enroll Fingerprint (Optional but Recommended)

1. Still on device: **User Management** → **Enroll Fingerprint**
2. Enter the **PIN** (Enroll ID) you just used
3. Place finger on scanner 3 times when prompted
4. Device confirms enrollment

#### Step 3: Sync User to System

**On the web interface:**

1. Go to **Users** page
2. Click **"Sync Users from Device"** button
3. Enter: IP `192.168.100.109`, Port `4370`
4. Click **"Sync"**
5. User will appear in the system automatically!

### Option 2: Create in System, Register on Device, Then Sync

#### Step 1: Create User in System

1. Go to **Users** page → **"Add New User"**
2. Fill in:
   - **Name**: User's name
   - **Email**: Unique email
   - **Password**: (required)
   - **Enroll ID**: Choose a number (e.g., 4, 5, 100)
3. Click **"Create User"**

#### Step 2: Register Same User on Device

**On device:**
1. **User Management** → **Add User**
2. Use the **SAME Enroll ID** you used in Step 1
3. Enter the same name
4. Save

#### Step 3: Sync to Link Them

1. Click **"Sync Users from Device"**
2. System will find the user on device and mark them as registered
3. User is now linked!

## Quick Reference

### Device Menu Paths (may vary by model):
- **User Management** → **User List** → **Add User**
- **Menu** → **User** → **New User**
- **Menu** → **User Management** → **Add**

### Enroll Fingerprint:
- **User Management** → **Enroll Fingerprint**
- **Menu** → **User** → **Fingerprint Enroll**
- **Menu** → **Enroll** → **Fingerprint**

## After Registration

Once user is registered on device and synced:

1. **Check Fingerprints**: Click "Check Fingerprints" to verify enrollment
2. **Test Punch**: User can now punch in/out using fingerprint
3. **View Attendance**: Attendance records will sync automatically (if ADMS configured) or use "Sync from Device"

## Why This Works

- Device registration works when done directly on device
- Sync function reads users from device and links them to system
- This bypasses the firmware compatibility issue with direct registration

## Troubleshooting

### "User not found after sync"
- Make sure user was actually saved on device
- Check device: **User Management** → **User List** to verify
- Try sync again

### "Enroll ID mismatch"
- Make sure Enroll ID on device matches Enroll ID in system
- If different, either:
  - Update Enroll ID in system to match device, OR
  - Delete and recreate with matching Enroll ID

### "Multiple users with same Enroll ID"
- Each Enroll ID must be unique
- Check device user list for duplicates
- Remove duplicates from device first

## Alternative: Use ADMS Push (When Configured)

If you configure ADMS on the device:
- Register users on device
- Device will automatically push user data to server
- Users appear in system automatically
- No manual sync needed!






