# Check In / Check Out Guide

## How Check In/Check Out Works on ZKTeco Devices

### Understanding the Device Behavior

ZKTeco devices can operate in different modes:

1. **Single Punch Mode** (Default): Every fingerprint scan creates a record, regardless of whether it's check in or check out
2. **Check In/Check Out Mode**: Device tracks whether user is checking in or checking out

### How Your Device Works

Based on your device data:
- **State = 1** → Check In ✓ (Green badge)
- **State = 15** → Check Out ✗ (Red badge)

### How to Perform Check In/Check Out

#### Method 1: Automatic (Device Determines)
1. User places finger on scanner
2. Device automatically determines if it's Check In or Check Out based on:
   - Time of day (if configured)
   - Last punch time
   - Device mode settings

#### Method 2: Manual Selection (If Device Supports)
1. User places finger on scanner
2. Device shows menu: "Check In" or "Check Out"
3. User selects option

### Device Configuration

To enable proper Check In/Check Out tracking:

1. **On the Device:**
   - Press MENU button
   - Go to: **System → Attendance → Work Code** (or similar)
   - Enable "Check In/Check Out Mode" or "Work Code Mode"
   - Set work schedule if needed

2. **Common Settings:**
   - **Work Code Mode**: ON
   - **Auto Check In/Out**: Enabled
   - **Work Schedule**: Set your work hours

### Why You Might Only See Check In

If you're only seeing Check In records:

1. **Device is in Single Punch Mode**: Every scan is treated as a punch, not specifically check in/out
2. **No Work Schedule Configured**: Device doesn't know when to expect check out
3. **Users Always Checking In**: Users might be scanning multiple times during the day (all treated as check in)

### How to See Check Out Records

1. **Configure Device for Check In/Out Mode** (see above)
2. **Set Work Schedule** on the device
3. **Users Must Check Out**: After checking in, users need to scan again to check out
4. **Sync Attendance**: Use "Sync Attendance" button to get latest records

### Viewing Check In/Out in the System

In the Attendance page:
- **Green "✓ Check In" badge** = User checked in
- **Red "✗ Check Out" badge** = User checked out

### Tips

- **First scan of the day** = Usually Check In
- **Second scan** (after check in) = Usually Check Out
- **Multiple scans** = Device may treat all as Check In if not configured properly

### If Check Out Still Not Working

1. Check device settings (Work Code Mode)
2. Verify users are scanning twice (once for in, once for out)
3. Sync attendance records from device
4. Check if device firmware supports Check In/Out mode






