# ZKTeco Attendance System - Complete Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Installation & Setup](#installation--setup)
3. [Device Configuration](#device-configuration)
4. [User Management](#user-management)
5. [Attendance Tracking](#attendance-tracking)
6. [API Endpoints](#api-endpoints)
7. [Integration Guide](#integration-guide)
8. [Troubleshooting](#troubleshooting)
9. [Advanced Features](#advanced-features)

---

## System Overview

This is a comprehensive attendance management system built with Laravel that integrates with ZKTeco biometric devices. The system supports bidirectional synchronization between the web application and ZKTeco devices, real-time attendance tracking via Push SDK, and comprehensive reporting.

### Key Features
- **Bidirectional User Sync**: Register users from system to device OR from device to system
- **Real-time Attendance**: Automatic attendance tracking via ZKTeco Push SDK (ADMS protocol)
- **Manual Sync**: Pull attendance records from device on demand
- **Daily Summary**: Consolidated check-in/check-out records per user per day
- **User Management**: Complete CRUD operations for users
- **Device Management**: Connect, configure, and manage ZKTeco devices
- **Reporting**: Daily attendance reports and user summaries

### Supported Devices
- ZKTeco UF200-S (Firmware 6.60+)
- Other ZKTeco devices with ADMS/Push SDK support
- Devices supporting standard ZKTeco protocol (port 4370)

---

## Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Laravel 10.x or higher
- MySQL/SQLite database
- Composer
- ZKTeco device connected to network

### Installation Steps

1. **Clone/Download the Project**
   ```bash
   cd /path/to/project
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Database**
   Edit `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=attendance
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Configure ZKTeco Device Settings**
   ```env
   ZKTECO_IP=192.168.100.108
   ZKTECO_PORT=4370
   ZKTECO_PASSWORD=0
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Start Development Server**
   ```bash
   php artisan serve
   ```

8. **Access the Application**
   - Open browser: `http://127.0.0.1:8000`
   - Default route redirects to Dashboard

---

## Device Configuration

### Initial Device Setup

#### Step 1: Physical Connection
1. Connect ZKTeco device to network (Ethernet or WiFi)
2. Power on the device
3. Note the device IP address (displayed on device screen or via device menu)

#### Step 2: Network Configuration
1. On device: Press **MENU** button
2. Navigate to: **System → Communication → Network**
3. Configure:
   - **IP Address**: Set static IP (e.g., `192.168.100.108`)
   - **Subnet Mask**: Usually `255.255.255.0`
   - **Gateway**: Your router IP
   - **Port**: `4370` (default ZKTeco port)

#### Step 3: Comm Key Configuration
1. On device: **System → Communication → Comm Key**
2. Set Comm Key (usually `0` for no password)
3. **IMPORTANT**: Update `.env` file with the same Comm Key:
   ```env
   ZKTECO_PASSWORD=0
   ```

#### Step 4: Enable ADMS (Push SDK) - For Real-time Attendance
1. On device: **System → Communication → ADMS** (or **Push Server**)
2. Enable ADMS: **ON**
3. Configure:
   - **Server IP**: Your web server IP address (e.g., `192.168.56.1`)
   - **Server Port**: `8000` (or your Laravel server port)
   - **Server Path**: `/iclock/getrequest`
4. Save settings

#### Step 5: Verify Device Connection
1. Go to: **Device Test** page (`/zkteco/test`)
2. Enter device IP and port
3. Click **Test Connection**
4. Verify device info is displayed

---

## User Management

### Workflow Options

The system supports **two workflows** for user management:

#### Workflow 1: System → Device (Recommended for New Systems)

**Step 1: Create User in System**
1. Navigate to: **Users → Add New User**
2. Fill in:
   - **Name**: User's full name
   - **Email**: Unique email address
   - **Password**: User's login password
   - **Enroll ID**: Numeric ID (1-65535) - **MUST be unique**
3. Click **Create User**

**Step 2: Register User to Device**
1. On Users page, find the user
2. Click **Register to Device** button
3. Confirm device IP/port (default: `192.168.100.108:4370`)
4. Wait for registration to complete
5. User status changes to "✓ Registered"

**Step 3: Enroll Fingerprint (Optional)**
1. On device: **User Management → Enroll Fingerprint**
2. Enter Enroll ID (same as in system)
3. Follow device prompts to enroll fingerprint
4. Repeat for additional fingers if needed

**Step 4: Verify Registration**
1. Click **Check Fingerprints** button on user's row
2. Verify fingerprint count is displayed

#### Workflow 2: Device → System (For Existing Devices)

**Step 1: Register User on Device**
1. On device: **User Management → Add User**
2. Enter:
   - **Enroll ID**: Numeric ID (1-65535)
   - **Name**: User's name
   - Save

**Step 2: Sync Users from Device**
1. On Users page, click **📥 Sync from Device**
2. Confirm device IP/port
3. Wait for sync to complete
4. Users appear in system automatically

**Step 3: Update User Details (Optional)**
1. Click **View** on user row
2. Edit email, password, etc.
3. Save changes

### Batch Operations

#### Register All Unregistered Users to Device
1. Click **📤 Register to Device** button
2. System finds all users with `registered_on_device = false`
3. Registers them one by one to device
4. Shows summary: registered, failed, skipped

#### Sync All Users from Device
1. Click **📥 Sync from Device** button
2. System imports all users from device
3. Creates new users or updates existing ones
4. Shows summary of imported users

### User Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | String | Yes | User's full name (max 255 chars) |
| `email` | String | Yes | Unique email address |
| `password` | String | Yes | Hashed password (min 8 chars) |
| `enroll_id` | String | Yes | Numeric ID (1-65535), unique |
| `registered_on_device` | Boolean | No | Auto-set when registered |
| `device_registered_at` | Timestamp | No | Registration timestamp |

### Important Notes
- **Enroll ID** must be numeric and unique
- **Enroll ID** range: 1-65535
- Device name limit: 24 characters (truncated automatically)
- Users can be registered on multiple devices (tracked per device)

---

## Attendance Tracking

### Real-time Attendance (Push SDK - Recommended)

#### Setup Push SDK
1. **Configure Device ADMS** (see Device Configuration section)
2. **Verify Server Endpoints**:
   - Ping endpoint: `GET /iclock/getrequest?SN=XXXXXXXXXX`
   - Data endpoint: `POST /iclock/cdata?SN=XXXXXXXXXX&table=ATTLOG`

#### How It Works
1. User scans fingerprint on device
2. Device sends HTTP POST to server with attendance data
3. System processes and stores attendance record
4. Record appears in system immediately (no manual sync needed)

#### Attendance Record Structure
- **First scan of the day** → Check In (`check_in_time` set)
- **Second scan of the day** → Check Out (`check_out_time` set)
- **Additional scans** → Rejected (both times already set)

#### View Real-time Attendance
1. Navigate to: **Attendance** page
2. Records appear automatically as users scan
3. Auto-refresh every 10 seconds (if auto-sync enabled)

### Manual Sync (Pull Mode)

#### When to Use
- Push SDK not configured
- Need to sync historical records
- Device was offline

#### How to Sync
1. Navigate to: **Attendance → Sync**
2. Enter device IP/port (default: `192.168.100.108:4370`)
3. Click **Sync Attendance**
4. Wait for sync to complete (30-60 seconds)
5. Records appear on Attendance page

#### Quick Sync
1. On Attendance page, click **Quick Sync** button
2. Uses default device settings
3. Syncs all records from device

### Attendance Data Structure

#### Database Schema
```sql
attendances
├── id (Primary Key)
├── user_id (Foreign Key → users.id)
├── enroll_id (String, matches user's enroll_id)
├── attendance_date (Date, YYYY-MM-DD)
├── check_in_time (Timestamp, NULL if not checked in)
├── check_out_time (Timestamp, NULL if not checked out)
├── punch_time (Timestamp, latest scan time)
├── status (Integer: 1=Check In, 0=Check Out)
├── verify_mode (String: Fingerprint, Card, etc.)
└── device_ip (String, device IP address)
```

#### Daily Summary View
- **One record per user per day**
- Shows both check-in and check-out times
- Calculates duration automatically
- Filters by date range, user, status

### Attendance Status

| Status | Value | Description |
|--------|-------|-------------|
| Check In | 1 | User scanned in |
| Check Out | 0 | User scanned out |

### Verify Mode

| Mode | Value | Description |
|------|-------|-------------|
| Fingerprint | 0, 255 | Fingerprint verification |
| Card | Varies | RFID/Card verification |
| Password | Varies | PIN/Password verification |

---

## API Endpoints

### User Management Endpoints

#### Create User
```
POST /users
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "enroll_id": "1001"
}
```

#### Register User to Device
```
POST /users/{id}/register-device
Content-Type: application/json

{
    "ip": "192.168.100.108",
    "port": 4370
}
```

#### Sync Users from Device
```
POST /users/sync-from-device
Content-Type: application/json

{
    "ip": "192.168.100.108",
    "port": 4370
}
```

#### Sync Users to Device (Batch Register)
```
POST /users/sync-to-device
Content-Type: application/json

{
    "ip": "192.168.100.108",
    "port": 4370
}
```

#### Get All Users
```
GET /users
```

#### Get User Details
```
GET /users/{id}
```

### Attendance Endpoints

#### Sync Attendance from Device
```
POST /attendances/sync
Content-Type: application/json

{
    "ip": "192.168.100.108",
    "port": 4370
}
```

#### Get Attendance Records
```
GET /attendances
Query Parameters:
    - page: Page number
    - user_id: Filter by user
    - date_from: Start date (YYYY-MM-DD)
    - date_to: End date (YYYY-MM-DD)
    - status: Filter by status (1=Check In, 0=Check Out)
```

#### Get Daily Summary
```
GET /attendances
View: Consolidated daily summary (default)
```

### Device Endpoints

#### Test Device Connection
```
POST /zkteco/test-connection
Content-Type: application/json

{
    "ip": "192.168.100.108",
    "port": 4370
}
```

#### Get Device Info
```
POST /zkteco/device-info
Content-Type: application/json

{
    "ip": "192.168.100.108",
    "port": 4370
}
```

### Push SDK Endpoints (Called by Device)

#### Device Ping/Command Request
```
GET /iclock/getrequest?SN=XXXXXXXXXX
Response: OK
```

#### Device Data Push (Attendance/Users)
```
POST /iclock/cdata?SN=XXXXXXXXXX&table=ATTLOG&c=log
Content-Type: text/plain

PIN=1001	DateTime=2025-11-30 14:32:13	Verified=0	Status=0
```

---

## Integration Guide

### Integrating with Another System

#### Option 1: Database Integration

**Direct Database Access**
```php
// Get attendance records
$attendances = \App\Models\Attendance::with('user')
    ->whereDate('attendance_date', '2025-11-30')
    ->get();

// Get user with attendance
$user = \App\Models\User::with('attendances')
    ->where('enroll_id', '1001')
    ->first();
```

**Database Schema Reference**
- Table: `users` - User information
- Table: `attendances` - Attendance records
- Relationship: `users.id` → `attendances.user_id`

#### Option 2: REST API Integration

**Create API Controller**
```php
// app/Http/Controllers/Api/AttendanceController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user');
        
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        return response()->json($query->get());
    }
}
```

**Add API Routes**
```php
// routes/api.php
use App\Http\Controllers\Api\AttendanceController;

Route::prefix('api/v1')->group(function () {
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::get('/users', [UserController::class, 'index']);
});
```

#### Option 3: Webhook Integration

**Create Webhook Controller**
```php
// app/Http/Controllers/WebhookController.php
namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function attendanceCreated(Request $request)
    {
        // Triggered when new attendance record is created
        $attendance = Attendance::with('user')->find($request->attendance_id);
        
        // Send to external system
        // HTTP::post('https://external-system.com/webhook', $attendance);
        
        return response()->json(['success' => true]);
    }
}
```

**Trigger Webhook on Attendance Creation**
```php
// app/Models/Attendance.php
protected static function booted()
{
    static::created(function ($attendance) {
        // Trigger webhook
        dispatch(new SendWebhookJob($attendance));
    });
}
```

### Data Export

#### Export to CSV
```php
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;

$attendances = Attendance::with('user')
    ->whereBetween('attendance_date', ['2025-11-01', '2025-11-30'])
    ->get();

$csv = "User,Enroll ID,Date,Check In,Check Out\n";
foreach ($attendances as $att) {
    $csv .= sprintf(
        "%s,%s,%s,%s,%s\n",
        $att->user->name,
        $att->enroll_id,
        $att->attendance_date,
        $att->check_in_time ? $att->check_in_time->format('H:i:s') : '',
        $att->check_out_time ? $att->check_out_time->format('H:i:s') : ''
    );
}

Storage::put('attendance_export.csv', $csv);
```

#### Export to JSON
```php
$attendances = Attendance::with('user')
    ->whereDate('attendance_date', '2025-11-30')
    ->get()
    ->map(function ($att) {
        return [
            'user' => $att->user->name,
            'enroll_id' => $att->enroll_id,
            'date' => $att->attendance_date,
            'check_in' => $att->check_in_time?->format('Y-m-d H:i:s'),
            'check_out' => $att->check_out_time?->format('Y-m-d H:i:s'),
        ];
    });

return response()->json($attendances);
```

---

## Troubleshooting

### Common Issues

#### 1. Device Connection Failed

**Symptoms:**
- "Failed to connect to device"
- "Connection timeout"

**Solutions:**
1. Verify device IP address is correct
2. Check network connectivity (ping device IP)
3. Verify device port (default: 4370)
4. Check firewall settings
5. Ensure device is powered on and not in sleep mode

#### 2. Authentication Failed

**Symptoms:**
- "CMD_ACK_UNAUTH (2005)"
- "Authentication failed"

**Solutions:**
1. Check Comm Key on device: **System → Communication → Comm Key**
2. Update `.env` file: `ZKTECO_PASSWORD=0` (or your Comm Key)
3. Verify Comm Key matches exactly (case-sensitive)
4. Try reconnecting to device

#### 3. User Registration Failed

**Symptoms:**
- "setUser command returned false"
- "User not found after registration"

**Solutions:**
1. Check Enroll ID is numeric and within range (1-65535)
2. Verify Enroll ID is unique (not already on device)
3. Ensure device is enabled (not in sleep mode)
4. Try manual registration on device, then sync
5. Check device firmware version (some versions have bugs)

#### 4. Attendance Not Syncing

**Symptoms:**
- No attendance records appearing
- "No attendance records found on device"

**Solutions:**
1. Verify users have actually scanned on device
2. Check device attendance log: **Data Management → Attendance Records**
3. Ensure Push SDK is configured correctly (for real-time)
4. Try manual sync: **Attendance → Sync**
5. Check device date/time is correct

#### 5. Push SDK Not Working

**Symptoms:**
- Real-time attendance not appearing
- Device not sending data

**Solutions:**
1. Verify ADMS is enabled on device
2. Check server IP/port in device settings
3. Verify server is accessible from device network
4. Check server logs: `storage/logs/laravel.log`
5. Test endpoint manually: `GET /iclock/getrequest?SN=XXXXXXXXXX`

#### 6. Duplicate Attendance Records

**Symptoms:**
- Multiple records for same user on same day
- Check-in and check-out times identical

**Solutions:**
1. System automatically prevents duplicates
2. If duplicates exist, clear and re-sync
3. Check for race conditions in Push SDK processing
4. Verify database constraints are in place

### Diagnostic Tools

#### Device Diagnostics
1. Navigate to: **Users → Diagnose**
2. Enter device IP/port
3. Review diagnostic results:
   - Connection status
   - Authentication status
   - User read capability
   - Device enable status
   - setUser test results

#### Check Logs
```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Search for specific errors
grep "ERROR" storage/logs/laravel.log
grep "ZKTeco" storage/logs/laravel.log
```

#### Test Device Connection
```php
// In Tinker
php artisan tinker

$zkteco = new \App\Services\ZKTecoService('192.168.100.108', 4370);
$connected = $zkteco->connect();
var_dump($connected);

$deviceInfo = $zkteco->getDeviceInfo();
print_r($deviceInfo);
```

---

## Advanced Features

### Custom Attendance Rules

#### Modify Check-in/Check-out Logic
```php
// app/Services/ZKTecoService.php
// In syncAttendancesToDatabase() method

// Custom rule: Only allow check-in between 8 AM and 9 AM
if (!$hasCheckIn) {
    $hour = $punchTime->hour;
    if ($hour < 8 || $hour > 9) {
        Log::warning("Check-in outside allowed time");
        continue; // Skip this record
    }
    // Proceed with check-in
}
```

### Automated Reports

#### Daily Attendance Report
```php
// Create Artisan command
php artisan make:command GenerateDailyReport

// app/Console/Commands/GenerateDailyReport.php
public function handle()
{
    $date = $this->option('date') ?? now()->format('Y-m-d');
    
    $attendances = Attendance::with('user')
        ->whereDate('attendance_date', $date)
        ->get();
    
    // Generate report (email, PDF, etc.)
}
```

### Notifications

#### Email Attendance Summary
```php
// app/Notifications/DailyAttendanceSummary.php
use Illuminate\Notifications\Notification;

class DailyAttendanceSummary extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }
    
    public function toMail($notifiable)
    {
        $attendances = Attendance::whereDate('attendance_date', now())
            ->with('user')
            ->get();
        
        return (new MailMessage)
            ->subject('Daily Attendance Summary')
            ->view('emails.attendance-summary', ['attendances' => $attendances]);
    }
}
```

### Multi-Device Support

#### Register Multiple Devices
```php
// config/zkteco.php
return [
    'devices' => [
        'main' => [
            'ip' => '192.168.100.108',
            'port' => 4370,
            'password' => 0,
        ],
        'backup' => [
            'ip' => '192.168.100.109',
            'port' => 4370,
            'password' => 0,
        ],
    ],
];
```

---

## Configuration Reference

### Environment Variables

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance
DB_USERNAME=root
DB_PASSWORD=

# ZKTeco Device
ZKTECO_IP=192.168.100.108
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0

# Application
APP_NAME="Attendance System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Configuration File

```php
// config/zkteco.php
return [
    'ip' => env('ZKTECO_IP', '192.168.100.108'),
    'port' => env('ZKTECO_PORT', 4370),
    'password' => env('ZKTECO_PASSWORD', 0),
];
```

---

## Best Practices

### Security
1. **Change default passwords** for users
2. **Use HTTPS** in production
3. **Restrict device network access** (firewall rules)
4. **Regular backups** of database
5. **Monitor logs** for suspicious activity

### Performance
1. **Index database** on frequently queried columns
2. **Use caching** for device info and user lists
3. **Batch operations** for multiple users
4. **Optimize queries** (avoid N+1 problems)
5. **Schedule cleanup** of old attendance records

### Maintenance
1. **Regular sync** to keep data current
2. **Monitor device status** (connection health)
3. **Backup database** daily
4. **Update firmware** on devices when available
5. **Review logs** weekly for errors

---

## Support & Resources

### Documentation Files
- `ATTENDANCE_SYSTEM_DOCUMENTATION.md` - This file
- `COMM_KEY_TROUBLESHOOTING.md` - Comm Key issues
- `FIRMWARE_COMPATIBILITY_SOLUTION.md` - Firmware issues
- `MANUAL_REGISTRATION_GUIDE.md` - Manual registration

### Log Files
- `storage/logs/laravel.log` - Application logs
- Check logs for detailed error messages and stack traces

### Device Documentation
- ZKTeco Official Documentation
- Device User Manual
- ADMS Protocol Specification

---

## Version History

### Current Version: 1.0.0

**Features:**
- Bidirectional user synchronization
- Real-time attendance tracking (Push SDK)
- Manual attendance sync
- Daily attendance summary
- User management (CRUD)
- Device diagnostics
- Comprehensive reporting

**Known Issues:**
- Some firmware versions (UF200-S 6.60) have setUser compatibility issues
- Workaround: Manual registration on device, then sync

---

## License

[Your License Here]

---

## Contact & Support

For issues, questions, or contributions:
- Check logs: `storage/logs/laravel.log`
- Review troubleshooting section
- Check device documentation
- Contact system administrator

---

**Last Updated:** November 30, 2025
**System Version:** 1.0.0
**Laravel Version:** 10.x
**PHP Version:** 8.1+



