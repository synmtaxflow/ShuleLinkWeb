# ZKTeco Fingerprint Device Setup Instructions

## PHP Sockets Extension Setup

The ZKTeco fingerprint device integration requires the PHP Sockets extension to be enabled.

### For XAMPP on Windows:

1. **Locate php.ini file:**
   - Navigate to: `C:\xampp\php\php.ini`
   - Or open XAMPP Control Panel → Click "Config" next to Apache → Select "PHP (php.ini)"

2. **Enable Sockets Extension:**
   - Open `php.ini` in a text editor
   - Search for: `;extension=sockets`
   - Remove the semicolon (`;`) at the beginning to uncomment it
   - Change from: `;extension=sockets`
   - To: `extension=sockets`

3. **Save and Restart:**
   - Save the `php.ini` file
   - Restart Apache from XAMPP Control Panel

4. **Verify Installation:**
   - Open Command Prompt
   - Run: `php -m | findstr sockets`
   - You should see `sockets` in the output

### For Linux/Ubuntu:

```bash
sudo apt-get install php-sockets
sudo systemctl restart apache2  # or nginx/php-fpm
```

### For macOS (Homebrew):

```bash
brew install php
# Edit php.ini and uncomment extension=sockets
brew services restart php
```

### Verify Extension is Loaded:

Run this command to check:
```bash
php -m | grep sockets
```

If you see `sockets` in the output, the extension is enabled.

## Device Configuration

After enabling the sockets extension, configure your ZKTeco device:

1. **Set Device IP Address:**
   - On device: Menu → System → Communication → Network
   - Set static IP (e.g., `192.168.100.108`)
   - Port: `4370` (default)

2. **Set Comm Key:**
   - On device: System → Communication → Comm Key
   - Set Comm Key (usually `0` for no password)
   - Update `.env` file: `ZKTECO_PASSWORD=0`

3. **Update .env File:**
   ```env
   ZKTECO_IP=192.168.100.108
   ZKTECO_PORT=4370
   ZKTECO_PASSWORD=0
   ```

## Troubleshooting

### Error: "Call to undefined function socket_create()"
- **Solution:** Enable PHP Sockets extension (see instructions above)

### Error: "Failed to connect to device"
- Check device IP address is correct
- Verify device is powered on and connected to network
- Ping the device IP: `ping 192.168.100.108`
- Check firewall settings

### Error: "Authentication failed"
- Verify Comm Key matches device settings
- Check `.env` file has correct `ZKTECO_PASSWORD` value

## Testing Connection

After setup, test the connection by registering a student. The system will:
1. Generate a unique fingerprint ID
2. Attempt to connect to the device
3. Register the student on the device
4. Show success message with fingerprint ID

If device connection fails, the student will still be registered in the system, but you'll need to manually register them on the device later.

