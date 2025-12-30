# ZKTeco Device Setup Guide

## Critical Issue: Device Not Responding to Commands

If you're seeing:
- "Device response code: " (empty)
- "No users found on device"
- Registration fails

**This usually means your device requires a password/Comm Key.**

## Step-by-Step Solution

### 1. Check Device Comm Key/Password

On your ZKTeco device:
1. Press **MENU** button
2. Go to: **System** → **Communication** → **Comm Key** (or **Password**)
3. Note the value shown (common values: `0`, `12345`, `54321`, `123456`, etc.)
4. If it shows `0` or is blank, try common defaults

### 2. Set Password in Laravel

**Option A: Using .env file (Recommended)**
1. Open `.env` file in your project root
2. Add or update this line:
   ```env
   ZKTECO_PASSWORD=12345
   ```
   (Replace `12345` with your device's actual Comm Key)
3. Save the file
4. **Restart your Laravel server** (important!)

**Option B: If .env doesn't exist**
1. Create `.env` file from `.env.example`
2. Add: `ZKTECO_PASSWORD=your_password`
3. Restart server

### 3. Test Again

1. Click **"Diagnose Device"** button first
2. This will test:
   - Connection
   - Device enable
   - Getting users
   - Getting time
3. If it shows errors, check the specific error message

### 4. Common Password Values to Try

If you don't know your device password, try these common defaults:
- `0` (no password)
- `12345`
- `54321`
- `123456`
- `8888`
- `0000`

Try each one by updating `.env` and restarting the server.

## Verification

After setting the password:
1. Restart Laravel server
2. Click "Diagnose Device"
3. Should show:
   - ✓ Connection: OK
   - ✓ Can Get Users: OK
   - Users Count: (should show users if any exist)

## Still Not Working?

1. **Check device network settings:**
   - Ensure device IP is correct (192.168.100.127)
   - Ensure device is on same network
   - Ping the device: `ping 192.168.100.127`

2. **Check device status:**
   - Device should be in "Normal" mode (not sleep)
   - Try restarting the device

3. **Check firewall:**
   - Ensure port 4370 (UDP) is not blocked
   - Windows Firewall might be blocking UDP packets

4. **Check logs:**
   - View `storage/logs/laravel.log`
   - Look for specific error messages
   - Check response codes and hex data

## Important Notes

- Password must be **numeric** (integers only)
- After changing `.env`, **always restart the server**
- Some devices require password even if it's set to `0`
- UDP communication can be blocked by firewalls







