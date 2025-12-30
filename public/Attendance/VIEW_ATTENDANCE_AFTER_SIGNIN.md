# How to View Attendance After User Signs In

## Quick Steps

### Step 1: User Signs In on Device
- User #1 places finger on scanner
- Device shows "Check In" or "Check Out"
- Record is saved on device

### Step 2: Sync to System
**Option A: Quick Sync (Fastest)**
1. Go to: **Attendance** page
2. Click **"Quick Sync"** button
3. Done!

**Option B: Full Sync Page**
1. Go to: `http://127.0.0.1:8000/attendances/sync`
2. Click **"Sync Attendance"** button
3. Wait for success message

### Step 3: View the Record

**Where to View:**

1. **Attendance Page** (All Records):
   - Go to: `http://127.0.0.1:8000/attendances`
   - See ALL attendance records
   - Look for User #1's name
   - Records sorted by most recent first

2. **User #1's Detail Page** (User-Specific):
   - Go to: **Users** page
   - Click on **User #1**
   - Scroll to **"Attendance Records"** section
   - See only User #1's records

## What You'll See

Each attendance record shows:
- **User Name**: User #1's name
- **Enroll ID**: 1
- **Punch Time**: Date and time (e.g., 2025-01-15 14:30:25)
- **Status**: 1 (Check In) or 0 (Check Out)
- **Verify Mode**: Fingerprint code
- **Device IP**: 192.168.100.109

## Quick Reference

- **Sync Page**: `http://127.0.0.1:8000/attendances/sync`
- **View All**: `http://127.0.0.1:8000/attendances`
- **View User #1**: `http://127.0.0.1:8000/users/1` (replace 1 with user ID)

## Tips

- Records appear at the **TOP** of the list (most recent first)
- After syncing, refresh the page to see new records
- Each sync gets ALL records from device (not just new ones)
- Duplicate records are automatically prevented






