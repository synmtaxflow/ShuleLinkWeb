# API Integration Examples

## Table of Contents
1. [REST API Examples](#rest-api-examples)
2. [Database Integration](#database-integration)
3. [Webhook Integration](#webhook-integration)
4. [Laravel Package Integration](#laravel-package-integration)

---

## REST API Examples

### Setup API Routes

First, create API routes in `routes/api.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\UserApiController;

Route::prefix('api/v1')->middleware('api')->group(function () {
    // Attendance endpoints
    Route::get('/attendances', [AttendanceApiController::class, 'index']);
    Route::get('/attendances/{id}', [AttendanceApiController::class, 'show']);
    Route::get('/attendances/daily/{date}', [AttendanceApiController::class, 'daily']);
    
    // User endpoints
    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/users/{id}', [UserApiController::class, 'show']);
    Route::post('/users', [UserApiController::class, 'store']);
});
```

### Create API Controllers

#### Attendance API Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    /**
     * Get attendance records
     * 
     * Query Parameters:
     * - date: YYYY-MM-DD
     * - user_id: Filter by user ID
     * - date_from: Start date
     * - date_to: End date
     */
    public function index(Request $request)
    {
        $query = Attendance::with('user');
        
        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('attendance_date', [
                $request->date_from,
                $request->date_to
            ]);
        }
        
        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by enroll_id
        if ($request->has('enroll_id')) {
            $query->where('enroll_id', $request->enroll_id);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate($request->get('per_page', 50));
        
        return response()->json([
            'success' => true,
            'data' => $attendances->items(),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'total' => $attendances->total(),
                'per_page' => $attendances->perPage(),
                'last_page' => $attendances->lastPage(),
            ]
        ]);
    }
    
    /**
     * Get single attendance record
     */
    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'user' => [
                    'id' => $attendance->user->id,
                    'name' => $attendance->user->name,
                    'enroll_id' => $attendance->user->enroll_id,
                ],
                'attendance_date' => $attendance->attendance_date,
                'check_in_time' => $attendance->check_in_time?->format('Y-m-d H:i:s'),
                'check_out_time' => $attendance->check_out_time?->format('Y-m-d H:i:s'),
                'status' => $attendance->status,
                'verify_mode' => $attendance->verify_mode,
                'device_ip' => $attendance->device_ip,
            ]
        ]);
    }
    
    /**
     * Get daily summary
     */
    public function daily($date)
    {
        $attendances = Attendance::with('user')
            ->whereDate('attendance_date', $date)
            ->get()
            ->groupBy('user_id')
            ->map(function ($group) {
                $attendance = $group->first();
                return [
                    'user' => [
                        'id' => $attendance->user->id,
                        'name' => $attendance->user->name,
                        'enroll_id' => $attendance->user->enroll_id,
                    ],
                    'date' => $attendance->attendance_date,
                    'check_in' => $attendance->check_in_time?->format('H:i:s'),
                    'check_out' => $attendance->check_out_time?->format('H:i:s'),
                    'duration' => $attendance->check_in_time && $attendance->check_out_time
                        ? $attendance->check_in_time->diff($attendance->check_out_time)->format('%h:%I:%S')
                        : null,
                ];
            })
            ->values();
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $attendances,
            'total' => $attendances->count(),
        ]);
    }
}
```

#### User API Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->has('registered')) {
            $query->where('registered_on_device', $request->registered === 'true');
        }
        
        $users = $query->withCount('attendances')
            ->orderBy('name')
            ->paginate($request->get('per_page', 50));
        
        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
            ]
        ]);
    }
    
    public function show($id)
    {
        $user = User::with('attendances')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'enroll_id' => $user->enroll_id,
                'registered_on_device' => $user->registered_on_device,
                'device_registered_at' => $user->device_registered_at?->format('Y-m-d H:i:s'),
                'attendances_count' => $user->attendances->count(),
            ]
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'enroll_id' => 'required|string|unique:users,enroll_id|regex:/^\d+$/',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'enroll_id' => $validated['enroll_id'],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'enroll_id' => $user->enroll_id,
            ]
        ], 201);
    }
}
```

### Usage Examples

#### JavaScript/Fetch

```javascript
// Get attendance for today
fetch('http://127.0.0.1:8000/api/v1/attendances?date=2025-11-30')
    .then(response => response.json())
    .then(data => {
        console.log('Attendances:', data.data);
    });

// Get daily summary
fetch('http://127.0.0.1:8000/api/v1/attendances/daily/2025-11-30')
    .then(response => response.json())
    .then(data => {
        console.log('Daily Summary:', data.data);
    });

// Create user
fetch('http://127.0.0.1:8000/api/v1/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
        enroll_id: '1001'
    })
})
.then(response => response.json())
.then(data => {
    console.log('User created:', data.data);
});
```

#### PHP/cURL

```php
// Get attendance records
$ch = curl_init('http://127.0.0.1:8000/api/v1/attendances?date=2025-11-30');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
print_r($data['data']);

// Create user
$ch = curl_init('http://127.0.0.1:8000/api/v1/users');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'password' => 'password123',
    'enroll_id' => '1002'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo "User created: " . $data['data']['name'];
```

#### Python/Requests

```python
import requests

# Get attendance records
response = requests.get(
    'http://127.0.0.1:8000/api/v1/attendances',
    params={'date': '2025-11-30'}
)
data = response.json()
print(data['data'])

# Create user
response = requests.post(
    'http://127.0.0.1:8000/api/v1/users',
    json={
        'name': 'Bob Smith',
        'email': 'bob@example.com',
        'password': 'password123',
        'enroll_id': '1003'
    }
)
data = response.json()
print(f"User created: {data['data']['name']}")
```

---

## Database Integration

### Direct Database Queries

```php
// Get today's attendance
$today = now()->format('Y-m-d');
$attendances = \DB::table('attendances')
    ->join('users', 'attendances.user_id', '=', 'users.id')
    ->whereDate('attendances.attendance_date', $today)
    ->select(
        'users.name',
        'users.enroll_id',
        'attendances.check_in_time',
        'attendances.check_out_time'
    )
    ->get();

// Get user attendance summary
$summary = \DB::table('attendances')
    ->where('user_id', 1)
    ->whereBetween('attendance_date', ['2025-11-01', '2025-11-30'])
    ->selectRaw('
        COUNT(*) as total_days,
        SUM(CASE WHEN check_in_time IS NOT NULL THEN 1 ELSE 0 END) as check_ins,
        SUM(CASE WHEN check_out_time IS NOT NULL THEN 1 ELSE 0 END) as check_outs
    ')
    ->first();
```

### Using Eloquent Models

```php
use App\Models\User;
use App\Models\Attendance;

// Get user with attendance
$user = User::with(['attendances' => function($query) {
    $query->whereDate('attendance_date', '2025-11-30');
}])->find(1);

// Get attendance with user
$attendance = Attendance::with('user')
    ->whereDate('attendance_date', '2025-11-30')
    ->get();

// Daily summary query
$summary = Attendance::selectRaw('
        user_id,
        attendance_date,
        MIN(check_in_time) as first_check_in,
        MAX(check_out_time) as last_check_out
    ')
    ->whereDate('attendance_date', '2025-11-30')
    ->groupBy('user_id', 'attendance_date')
    ->with('user')
    ->get();
```

---

## Webhook Integration

### Create Webhook Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    public function handleAttendanceCreated(Attendance $attendance)
    {
        // Send to external system
        $response = Http::post('https://external-system.com/webhook/attendance', [
            'user_id' => $attendance->user_id,
            'enroll_id' => $attendance->enroll_id,
            'date' => $attendance->attendance_date,
            'check_in' => $attendance->check_in_time?->format('Y-m-d H:i:s'),
            'check_out' => $attendance->check_out_time?->format('Y-m-d H:i:s'),
        ]);
        
        return response()->json(['success' => $response->successful()]);
    }
}
```

### Trigger Webhook on Attendance Creation

```php
// app/Models/Attendance.php
use Illuminate\Support\Facades\Log;

protected static function booted()
{
    static::created(function ($attendance) {
        // Dispatch webhook job
        dispatch(new \App\Jobs\SendAttendanceWebhook($attendance));
    });
    
    static::updated(function ($attendance) {
        // If check-out was just set, send webhook
        if ($attendance->wasChanged('check_out_time') && $attendance->check_out_time) {
            dispatch(new \App\Jobs\SendAttendanceWebhook($attendance));
        }
    });
}
```

### Create Webhook Job

```php
<?php

namespace App\Jobs;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class SendAttendanceWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public Attendance $attendance)
    {
    }

    public function handle()
    {
        $webhookUrl = config('services.webhook.url');
        
        if (!$webhookUrl) {
            return;
        }
        
        Http::post($webhookUrl, [
            'event' => 'attendance.created',
            'data' => [
                'id' => $this->attendance->id,
                'user_id' => $this->attendance->user_id,
                'enroll_id' => $this->attendance->enroll_id,
                'date' => $this->attendance->attendance_date,
                'check_in' => $this->attendance->check_in_time?->format('Y-m-d H:i:s'),
                'check_out' => $this->attendance->check_out_time?->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
```

---

## Laravel Package Integration

### Create Service Provider

```php
<?php

namespace YourCompany\AttendanceSystem;

use Illuminate\Support\ServiceProvider;

class AttendanceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('attendance', function ($app) {
            return new AttendanceService();
        });
    }
    
    public function boot()
    {
        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'attendance-migrations');
        
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}
```

### Create Service Class

```php
<?php

namespace YourCompany\AttendanceSystem;

use App\Models\Attendance;
use App\Models\User;

class AttendanceService
{
    public function getTodayAttendance()
    {
        return Attendance::with('user')
            ->whereDate('attendance_date', now())
            ->get();
    }
    
    public function getUserAttendance($userId, $dateFrom, $dateTo)
    {
        return Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->get();
    }
    
    public function registerUserToDevice($userId, $deviceIp, $devicePort)
    {
        $user = User::findOrFail($userId);
        $zkteco = new \App\Services\ZKTecoService($deviceIp, $devicePort);
        
        return $zkteco->registerUser(
            (int)$user->enroll_id,
            $user->enroll_id,
            $user->name,
            '',
            0,
            0
        );
    }
}
```

### Usage

```php
use YourCompany\AttendanceSystem\AttendanceService;

$attendance = app('attendance');

// Get today's attendance
$today = $attendance->getTodayAttendance();

// Get user attendance
$userAttendance = $attendance->getUserAttendance(1, '2025-11-01', '2025-11-30');

// Register user
$result = $attendance->registerUserToDevice(1, '192.168.100.108', 4370);
```

---

## Authentication (Optional)

### Add API Authentication

```php
// routes/api.php
Route::prefix('api/v1')->middleware(['api', 'auth:sanctum'])->group(function () {
    Route::get('/attendances', [AttendanceApiController::class, 'index']);
    // ... other routes
});
```

### Generate API Token

```php
// In your controller or command
$user = User::find(1);
$token = $user->createToken('api-token')->plainTextToken;
```

### Use Token in Requests

```javascript
fetch('http://127.0.0.1:8000/api/v1/attendances', {
    headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
    }
});
```

---

**For complete documentation, see:** `ATTENDANCE_SYSTEM_DOCUMENTATION.md`



