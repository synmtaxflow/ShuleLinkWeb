<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::withCount('attendances')->orderBy('created_at', 'desc')->paginate(20);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'enroll_id' => 'required|string|unique:users,enroll_id|regex:/^\d+$/|min:1|max:9',
        ], [
            'enroll_id.regex' => 'Enroll ID must contain only numbers (e.g., 1, 2, 3...)',
            'enroll_id.max' => 'Enroll ID must be maximum 9 digits',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'enroll_id' => $validated['enroll_id'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    /**
     * Check device status and readiness for registration
     */
    public function checkDeviceStatus(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip');
        $port = $request->input('port');

        try {
            $zkteco = new ZKTecoService($ip, $port);
            $status = [
                'connection' => false,
                'authentication' => false,
                'can_read_users' => false,
                'users_count' => 0,
                'device_name' => null,
                'device_info' => null,
                'issues' => [],
                'recommendations' => []
            ];

            // Test connection
            if ($zkteco->connect()) {
                $status['connection'] = true;
                
                try {
                    $client = $zkteco->getClient();
                    
                    // Test authentication by getting device name
                    try {
                        $deviceName = $client->deviceName();
                        $status['device_name'] = $deviceName;
                        $status['authentication'] = true;
                    } catch (\Exception $e) {
                        $status['issues'][] = 'Cannot get device name - Comm Key might be wrong';
                        $status['recommendations'][] = 'Check Comm Key in device settings and .env file';
                    }
                    
                    // Test reading users
                    try {
                        $users = $zkteco->getUsers();
                        $status['can_read_users'] = true;
                        $status['users_count'] = count($users);
                        
                        if (count($users) >= 1000) {
                            $status['issues'][] = 'Device has many users (' . count($users) . ') - memory might be full';
                            $status['recommendations'][] = 'Consider clearing old users or restarting device';
                        }
                    } catch (\Exception $e) {
                        $status['issues'][] = 'Cannot read users from device: ' . $e->getMessage();
                    }
                    
                    // Try to enable device
                    try {
                        $client->enableDevice();
                        $status['device_enabled'] = true;
                    } catch (\Exception $e) {
                        $status['issues'][] = 'Cannot enable device: ' . $e->getMessage();
                        $status['recommendations'][] = 'Device might need to be unlocked or restarted';
                    }
                    
                } catch (\Exception $e) {
                    $status['issues'][] = 'Error accessing device: ' . $e->getMessage();
                }
            } else {
                $status['issues'][] = 'Cannot connect to device';
                $status['recommendations'][] = 'Check IP, port, network connectivity, and firewall settings';
            }

            return response()->json([
                'success' => true,
                'status' => $status,
                'ready_for_registration' => $status['connection'] && $status['authentication'] && $status['can_read_users'] && empty($status['issues'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking device status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register user to ZKTeco device
     */
    public function registerToDevice(Request $request, $id)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $user = User::findOrFail($id);

        if ($user->registered_on_device) {
            return response()->json([
                'success' => false,
                'message' => 'User is already registered on device'
            ], 400);
        }

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            $zkteco = new ZKTecoService($ip, $port);
            
            // Pre-registration check: Verify device is accessible and Enroll ID is available
            try {
                if (!$zkteco->connect()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot connect to device. Check IP, port, and network connectivity.',
                        'troubleshooting' => "🔌 CONNECTION ISSUE:\n\n1. Check device IP: {$ip}\n2. Check device port: {$port}\n3. Ensure device is powered on\n4. Check network connectivity\n5. Check firewall settings (UDP port 4370)"
                    ], 500);
                }
                
                // Check if Enroll ID already exists on device
                $deviceUsers = $zkteco->getUsers();
                foreach ($deviceUsers as $key => $deviceUser) {
                    $deviceEnrollId = (string)($key ?? $deviceUser['user_id'] ?? $deviceUser['uid'] ?? '');
                    if ($deviceEnrollId === (string)$user->enroll_id) {
                        return response()->json([
                            'success' => false,
                            'message' => "Enroll ID '{$user->enroll_id}' already exists on device",
                            'troubleshooting' => "⚠️ ENROLL ID CONFLICT:\n\nEnroll ID {$user->enroll_id} is already registered on the device.\n\n✅ SOLUTIONS:\n1. Use a different Enroll ID for this user\n2. Remove the existing user from device first\n3. Click 'Sync Users from Device' to update status if this is the same user"
                        ], 400);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Pre-registration check failed: ' . $e->getMessage());
                // Continue with registration attempt anyway
            }
            
            // Validate enroll_id is numeric
            if (!is_numeric($user->enroll_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enroll ID must be numeric (e.g., 1, 2, 3...)'
                ], 400);
            }
            
            // Register user to device
            // UID is typically the enroll_id, userid is also enroll_id, name is user's name
            $uid = (int) $user->enroll_id;
            
            if ($uid < 1 || $uid > 65535) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enroll ID must be between 1 and 65535'
                ], 400);
            }
            
            $result = $zkteco->registerUser(
                $uid,
                $user->enroll_id,
                $user->name,
                '', // password (empty for fingerprint devices)
                0,  // role (0 = user, 14 = admin)
                0   // cardno
            );

            if ($result) {
                $user->update([
                    'registered_on_device' => true,
                    'device_registered_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User registered to device successfully and verified!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register user to device'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Register user to device error: ' . $e->getMessage());
            
            $errorMessage = $e->getMessage();
            
            // Check if error message suggests user might be on device
            $mightBeRegistered = strpos($errorMessage, 'MAY have been registered') !== false || 
                                strpos($errorMessage, 'device responded') !== false;
            
            // Check for specific error patterns to provide targeted solutions
            $isCommKeyIssue = strpos($errorMessage, 'Comm Key') !== false || 
                             strpos($errorMessage, 'authentication') !== false ||
                             strpos($errorMessage, 'CMD_ACK_UNAUTH') !== false;
            
            $isDeviceRejected = strpos($errorMessage, 'rejected') !== false ||
                               strpos($errorMessage, 'User count did not increase') !== false ||
                               strpos($errorMessage, 'CMD_ACK_ERROR') !== false;
            
            $isFirmwareIssue = strpos($errorMessage, '2007') !== false ||
                              strpos($errorMessage, 'firmware') !== false;
            
            // Build comprehensive troubleshooting guide
            $troubleshooting = [];
            
            if ($isCommKeyIssue) {
                $troubleshooting[] = "🔑 COMM KEY ISSUE:";
                $troubleshooting[] = "1. Check device Comm Key: On device → System → Communication → Comm Key";
                $troubleshooting[] = "2. Update .env file: ZKTECO_PASSWORD=<your_comm_key>";
                $troubleshooting[] = "3. If Comm Key is 0, set ZKTECO_PASSWORD=0 in .env";
                $troubleshooting[] = "4. Restart the server after changing .env";
            }
            
            if ($isDeviceRejected) {
                $troubleshooting[] = "";
                $troubleshooting[] = "❌ DEVICE REJECTED REGISTRATION:";
                $troubleshooting[] = "Possible causes:";
                $troubleshooting[] = "1. Enroll ID already exists on device (check device user list)";
                $troubleshooting[] = "2. Device memory is full";
                $troubleshooting[] = "3. Device is locked or in wrong mode";
                $troubleshooting[] = "4. Device needs to be enabled/unlocked";
                $troubleshooting[] = "";
                $troubleshooting[] = "✅ QUICK FIXES:";
                $troubleshooting[] = "• Check device: User Management → User List (see if Enroll ID {$user->enroll_id} exists)";
                $troubleshooting[] = "• If user exists on device, click 'Sync Users from Device' to update status";
                $troubleshooting[] = "• Try a different Enroll ID if current one is taken";
            }
            
            if ($isFirmwareIssue || !$isCommKeyIssue && !$isDeviceRejected) {
                $troubleshooting[] = "";
                $troubleshooting[] = "⚠️ FIRMWARE COMPATIBILITY ISSUE:";
                $troubleshooting[] = "Your device may have firmware compatibility issues.";
                $troubleshooting[] = "";
                $troubleshooting[] = "✅ WORKAROUND - Manual Registration:";
                $troubleshooting[] = "1. On device: User Management → Add User";
                $troubleshooting[] = "   • Enroll ID: {$user->enroll_id}";
                $troubleshooting[] = "   • Name: {$user->name}";
                $troubleshooting[] = "   • Save";
                $troubleshooting[] = "";
                $troubleshooting[] = "2. On web: Click 'Sync Users from Device' button";
                $troubleshooting[] = "   • User will be synced automatically!";
                $troubleshooting[] = "";
                $troubleshooting[] = "3. Enroll fingerprint (optional):";
                $troubleshooting[] = "   • On device: User Management → Enroll Fingerprint";
                $troubleshooting[] = "   • Enter Enroll ID: {$user->enroll_id}";
            }
            
            $troubleshooting[] = "";
            $troubleshooting[] = "🔄 DEVICE RESTART (If it was working before):";
            $troubleshooting[] = "1. Power cycle the device (turn off, wait 10 seconds, turn on)";
            $troubleshooting[] = "2. Wait 30 seconds for device to fully boot";
            $troubleshooting[] = "3. Try registration again";
            $troubleshooting[] = "";
            $troubleshooting[] = "🔧 DIAGNOSTIC TOOLS:";
            $troubleshooting[] = "• Click '🔧 Diagnose' button to check device connection and settings";
            $troubleshooting[] = "• Click '📋 List Device Users' to see all users currently on device";
            $troubleshooting[] = "• Check device IP and port are correct (current: {$ip}:{$port})";
            $troubleshooting[] = "";
            $troubleshooting[] = "✅ WORKAROUND (If registration still fails):";
            $troubleshooting[] = "1. Register user manually on device: User Management → Add User";
            $troubleshooting[] = "   • Enroll ID: {$user->enroll_id}";
            $troubleshooting[] = "   • Name: {$user->name}";
            $troubleshooting[] = "2. Click 'Sync Users from Device' button on this page";
            $troubleshooting[] = "3. User will be synced automatically!";
            
            $troubleshootingText = implode("\n", $troubleshooting);
            
            return response()->json([
                'success' => false,
                'message' => 'Registration Failed: ' . $errorMessage,
                'error_type' => [
                    'comm_key_issue' => $isCommKeyIssue,
                    'device_rejected' => $isDeviceRejected,
                    'firmware_issue' => $isFirmwareIssue,
                ],
                'might_be_registered' => $mightBeRegistered,
                'troubleshooting' => $troubleshootingText,
                'quick_solution' => $mightBeRegistered ? 
                    "The device responded. Check device manually (User Management → User List). If user appears, click 'Sync Users from Device' or manually mark as registered." : 
                    "Try manual registration on device, then sync. See troubleshooting guide below.",
                'user_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'enroll_id' => $user->enroll_id,
                ],
                'device_info' => [
                    'ip' => $ip,
                    'port' => $port,
                ]
            ], 500);
        }
    }

    /**
     * Remove user from device
     */
    public function removeFromDevice(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            $zkteco = new ZKTecoService($ip, $port);
            $uid = (int) $user->enroll_id;
            $result = $zkteco->removeUser($uid);

            if ($result) {
                $user->update([
                    'registered_on_device' => false,
                    'device_registered_at' => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User removed from device successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove user from device'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Remove user from device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with('attendances')->findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Diagnose setUser issue specifically
     */
    public function diagnoseSetUserIssue(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            $zkteco = new ZKTecoService($ip, $port);
            $diagnostics = $zkteco->diagnoseSetUserIssue();

            return response()->json([
                'success' => true,
                'diagnostics' => $diagnostics,
                'summary' => $this->generateSetUserDiagnosticSummary($diagnostics)
            ]);
        } catch (\Exception $e) {
            Log::error('Diagnose setUser issue error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function generateSetUserDiagnosticSummary($diagnostics): string
    {
        $summary = [];
        
        if (!$diagnostics['connection']) {
            $summary[] = "✗ Cannot connect to device";
        } else {
            $summary[] = "✓ Can connect to device";
        }
        
        if (!$diagnostics['authentication']) {
            $summary[] = "✗ Authentication FAILED - Comm Key might be wrong";
        } else {
            $summary[] = "✓ Authentication works - Comm Key is correct";
        }
        
        if (!$diagnostics['can_read_users']) {
            $summary[] = "✗ Cannot read users from device";
        } else {
            $summary[] = "✓ Can read users (found " . ($diagnostics['device_info']['users_count'] ?? 0) . ")";
        }
        
        if (!$diagnostics['can_enable_device']) {
            $summary[] = "⚠ Cannot enable device (might be required)";
        } else {
            $summary[] = "✓ Can enable device";
        }
        
        if (isset($diagnostics['raw_setuser_test'])) {
            if ($diagnostics['raw_setuser_test']['success'] ?? false) {
                if ($diagnostics['raw_setuser_test']['user_added'] ?? false) {
                    $summary[] = "✓ setUser command WORKS - test user was added";
                } else {
                    $summary[] = "⚠ setUser returned success but user not found (timing issue?)";
                }
            } else {
                $summary[] = "✗ setUser command FAILED: " . ($diagnostics['raw_setuser_test']['error'] ?? 'Unknown error');
            }
        }
        
        return implode("\n", $summary);
    }

    /**
     * Diagnose device connection and communication
     */
    public function diagnoseDevice(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            $zkteco = new ZKTecoService($ip, $port);
            $diagnostics = $zkteco->diagnoseConnection();

            return response()->json([
                'success' => true,
                'diagnostics' => $diagnostics
            ]);
        } catch (\Exception $e) {
            Log::error('Diagnose device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync and verify users from device
     * Gets all users from device and marks them as registered in the system
     */
    /**
     * Delete all users from database
     */
    public function deleteAll(Request $request)
    {
        try {
            $count = User::count();
            
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users to delete'
                ], 400);
            }

            // Delete all users (this will also delete related attendances due to cascade)
            User::query()->delete();

            Log::info("Deleted all {$count} users from database");

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} user(s) from database",
                'deleted_count' => $count
            ]);
        } catch (\Throwable $e) {
            Log::error('Delete all users error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all users from device
     */
    public function deleteAllFromDevice(Request $request)
    {
        // Increase execution time for device operations
        set_time_limit(300); // 5 minutes
        
        $ip = $request->input('ip', config('zkteco.ip', '192.168.100.100'));
        $port = $request->input('port', config('zkteco.port', 4370));
        
        // Validate only if provided
        if ($request->has('ip')) {
            $request->validate(['ip' => 'ip']);
        }
        if ($request->has('port')) {
            $request->validate(['port' => 'integer|min:1|max:65535']);
        }

        try {
            Log::info("Starting delete all users from device {$ip}:{$port}");

            $zkteco = new ZKTecoService($ip, $port);
            
            // Test connection first
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get all users from device
            $deviceUsers = $zkteco->getUsers();
            
            if (empty($deviceUsers) || count($deviceUsers) === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No users found on device to delete',
                    'deleted_count' => 0
                ]);
            }

            Log::info("Found " . count($deviceUsers) . " user(s) on device to delete");

            $deleted = 0;
            $failed = 0;
            $errors = [];

            // Loop through all users and remove them from device
            foreach ($deviceUsers as $key => $deviceUser) {
                try {
                    // Extract UID - the key is typically the userid/enroll_id
                    // Also check uid in the user data array
                    $uid = null;
                    
                    if (is_numeric($key)) {
                        $uid = (int) $key;
                    } elseif (isset($deviceUser['uid']) && is_numeric($deviceUser['uid'])) {
                        $uid = (int) $deviceUser['uid'];
                    } elseif (isset($deviceUser['user_id']) && is_numeric($deviceUser['user_id'])) {
                        $uid = (int) $deviceUser['user_id'];
                    }
                    
                    if ($uid === null || $uid < 1) {
                        $failed++;
                        $errors[] = "Could not extract valid UID for user: " . ($deviceUser['name'] ?? 'Unknown');
                        Log::warning("Skipping user - invalid UID. Key: {$key}, Data: " . json_encode($deviceUser));
                        continue;
                    }

                    Log::info("Removing user from device - UID: {$uid}, Name: " . ($deviceUser['name'] ?? 'Unknown'));

                    // Remove user from device
                    $result = $zkteco->removeUser($uid);

                    if ($result) {
                        $deleted++;
                        Log::info("✓ Successfully removed user from device - UID: {$uid}");
                        
                        // Update database if user exists
                        $user = User::where('enroll_id', (string)$uid)->first();
                        if ($user) {
                            $user->update([
                                'registered_on_device' => false,
                                'device_registered_at' => null,
                            ]);
                            Log::info("Updated database record for user ID: {$user->id}");
                        }
                    } else {
                        $failed++;
                        $errorMsg = "Failed to remove user from device - UID: {$uid}, Name: " . ($deviceUser['name'] ?? 'Unknown');
                        $errors[] = $errorMsg;
                        Log::warning($errorMsg);
                    }

                    // Small delay between deletions to avoid overwhelming the device
                    usleep(500000); // 500ms

                } catch (\Exception $e) {
                    $failed++;
                    $errorMsg = "Error removing user (UID: " . ($uid ?? 'unknown') . "): " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error($errorMsg);
                }
            }

            $message = "Deletion complete: {$deleted} user(s) removed from device";
            if ($failed > 0) {
                $message .= ", {$failed} failed";
            }

            Log::info("Delete all users from device completed: {$message}");

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'total' => count($deviceUsers),
                    'deleted' => $deleted,
                    'failed' => $failed,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Delete all users from device error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users from device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register all unregistered users from system to device
     */
    public function syncUsersToDevice(Request $request)
    {
        // Increase execution time for device operations
        set_time_limit(300); // 5 minutes
        
        $ip = $request->input('ip', config('zkteco.ip', '192.168.100.108'));
        $port = $request->input('port', config('zkteco.port', 4370));
        
        // Validate only if provided
        if ($request->has('ip')) {
            $request->validate(['ip' => 'ip']);
        }
        if ($request->has('port')) {
            $request->validate(['port' => 'integer|min:1|max:65535']);
        }

        try {
            Log::info("Starting batch user registration to device {$ip}:{$port}");

            // Get all users that are NOT registered on device
            $unregisteredUsers = User::where('registered_on_device', false)
                ->whereNotNull('enroll_id')
                ->where('enroll_id', '!=', '')
                ->get();

            if ($unregisteredUsers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'All users are already registered on device.',
                    'data' => [
                        'total' => 0,
                        'registered' => 0,
                        'failed' => 0,
                        'skipped' => 0
                    ]
                ]);
            }

            Log::info("Found {$unregisteredUsers->count()} unregistered users to register");

            $zkteco = new ZKTecoService($ip, $port);
            
            $registered = 0;
            $failed = 0;
            $skipped = 0;
            $errors = [];

            foreach ($unregisteredUsers as $user) {
                try {
                    // Validate enroll_id
                    if (!is_numeric($user->enroll_id)) {
                        $skipped++;
                        $errors[] = "User {$user->name} (ID: {$user->id}): Enroll ID '{$user->enroll_id}' is not numeric";
                        Log::warning("Skipping user {$user->name}: Enroll ID is not numeric");
                        continue;
                    }

                    $uid = (int) $user->enroll_id;
                    
                    if ($uid < 1 || $uid > 65535) {
                        $skipped++;
                        $errors[] = "User {$user->name} (ID: {$user->id}): Enroll ID {$uid} is out of range (1-65535)";
                        Log::warning("Skipping user {$user->name}: Enroll ID out of range");
                        continue;
                    }

                    Log::info("Registering user: {$user->name} (Enroll ID: {$user->enroll_id})");

                    // Attempt to register user to device
                    $result = $zkteco->registerUser(
                        $uid,
                        $user->enroll_id,
                        $user->name,
                        '', // password (empty for fingerprint devices)
                        0,  // role (0 = user, 14 = admin)
                        0   // cardno
                    );

                    if ($result) {
                        // Mark as registered
                        $user->update([
                            'registered_on_device' => true,
                            'device_registered_at' => now(),
                        ]);
                        $registered++;
                        Log::info("✓ Successfully registered user: {$user->name}");
                    } else {
                        $failed++;
                        $errorMsg = "Failed to register user {$user->name} (Enroll ID: {$user->enroll_id})";
                        $errors[] = $errorMsg;
                        Log::warning($errorMsg);
                    }

                    // Small delay between registrations to avoid overwhelming the device
                    usleep(500000); // 500ms

                } catch (\Exception $e) {
                    $failed++;
                    $errorMsg = "User {$user->name} (Enroll ID: {$user->enroll_id}): " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error("Error registering user {$user->name}: " . $e->getMessage());
                    
                    // Check if error suggests user might already be on device
                    if (strpos($e->getMessage(), 'already exists') !== false || 
                        strpos($e->getMessage(), 'MAY have been registered') !== false) {
                        // Mark as registered anyway (user might be on device)
                        $user->update([
                            'registered_on_device' => true,
                            'device_registered_at' => now(),
                        ]);
                        $registered++;
                        $failed--; // Don't count as failed
                        Log::info("User {$user->name} appears to be on device - marking as registered");
                    }
                }
            }

            $message = "Registration complete: {$registered} registered, {$failed} failed, {$skipped} skipped";
            if ($failed > 0 || $skipped > 0) {
                $message .= "\n\n⚠️ Some users could not be registered. Check logs for details.";
            }

            Log::info("Batch registration completed: {$message}");

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'total' => $unregisteredUsers->count(),
                    'registered' => $registered,
                    'failed' => $failed,
                    'skipped' => $skipped,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Batch register users to device error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register users to device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync users from device to database
     */
    public function syncUsersFromDevice(Request $request)
    {
        // Increase execution time for device operations (can take 30-60 seconds)
        set_time_limit(120); // 2 minutes
        
        // IP and port are optional - use config defaults if not provided
        $ip = $request->input('ip', config('zkteco.ip', '192.168.100.108'));
        $port = $request->input('port', config('zkteco.port', 4370));
        
        // Validate only if provided
        if ($request->has('ip')) {
            $request->validate(['ip' => 'ip']);
        }
        if ($request->has('port')) {
            $request->validate(['port' => 'integer|min:1|max:65535']);
        }

        try {
            Log::info("Starting user sync from device {$ip}:{$port}");
            Log::info("PHP execution time limit: " . ini_get('max_execution_time') . " seconds");

            $zkteco = new ZKTecoService($ip, $port);
            
            // Add timeout protection - if getUsers takes too long, it might hang
            $startTime = microtime(true);
            $result = $zkteco->syncUsersFromDevice();
            $elapsedTime = microtime(true) - $startTime;
            
            Log::info("User sync completed in " . round($elapsedTime, 2) . " seconds");

            $message = "Synced {$result['verified']} user(s) from device. ";
            if ($result['created'] > 0) {
                $message .= "Created {$result['created']} new user(s) from device. ";
            }
            if ($result['marked_registered'] > 0) {
                $message .= "Marked {$result['marked_registered']} user(s) as registered. ";
            }
            if ($result['not_found'] > 0) {
                $message .= "{$result['not_found']} device user(s) could not be processed.";
            }
            
            Log::info("User sync completed: {$message}");
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();
            
            Log::error('=== SYNC USERS FROM DEVICE ERROR ===');
            Log::error('Error message: ' . $errorMessage);
            Log::error('Error class: ' . get_class($e));
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack trace: ' . $errorTrace);
            
            // Provide more helpful error messages based on error type
            $userFriendlyMessage = $errorMessage;
            
            if (strpos($errorMessage, 'Not connected') !== false || strpos($errorMessage, 'Connection failed') !== false) {
                $userFriendlyMessage = 'Cannot connect to device. Please check:\n• Device IP address (192.168.100.108)\n• Device is powered on\n• Network connectivity\n• Device port (4370)';
            } elseif (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'timed out') !== false) {
                $userFriendlyMessage = 'Device connection timed out. The device may be busy or not responding. Try again in a few seconds.';
            } elseif (strpos($errorMessage, 'getUsers') !== false) {
                $userFriendlyMessage = 'Failed to retrieve users from device. The device may be busy or not responding.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $userFriendlyMessage,
                'error_details' => config('app.debug') ? [
                    'error' => $errorMessage,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    /**
     * List users from device (for debugging)
     */
    public function listDeviceUsers(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            $zkteco = new ZKTecoService($ip, $port);
            
            // Test connection first
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check IP, port, and network connectivity.'
                ], 500);
            }
            
            $users = $zkteco->getUsers();
            
            // Format users for better display
            $formattedUsers = [];
            foreach ($users as $key => $user) {
                $formattedUsers[] = [
                    'uid' => $user['uid'] ?? 'N/A',
                    'user_id' => $user['user_id'] ?? 'N/A',
                    'name' => $user['name'] ?? 'N/A',
                    'role' => $user['role'] ?? 'N/A',
                    'card_no' => $user['card_no'] ?? 'N/A',
                    'raw_data' => $user // Include raw data for debugging
                ];
            }

            return response()->json([
                'success' => true,
                'users' => $formattedUsers,
                'raw_users' => $users, // Include raw for debugging
                'count' => count($users),
                'message' => count($users) > 0 
                    ? "Found " . count($users) . " user(s) on device" 
                    : "No users found on device. Make sure device is connected and users are registered."
            ]);
        } catch (\Exception $e) {
            Log::error('List device users error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage() . '. Check logs for details.'
            ], 500);
        }
    }

    /**
     * Manually mark user as registered (if user confirms user is on device)
     */
    public function markAsRegistered($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->registered_on_device) {
            return response()->json([
                'success' => false,
                'message' => 'User is already marked as registered on device'
            ], 400);
        }
        
        $user->update([
            'registered_on_device' => true,
            'device_registered_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'User marked as registered on device. Please ensure the user is actually on the device before using this option.'
        ]);
    }

    /**
     * Check fingerprints for a user
     */
    public function checkFingerprints(Request $request, $id)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $user = User::findOrFail($id);

        if (!$user->registered_on_device) {
            return response()->json([
                'success' => false,
                'message' => 'User must be registered on device first'
            ], 400);
        }

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            $zkteco = new ZKTecoService($ip, $port);
            $result = $zkteco->checkFingerprints($user->enroll_id);

            return response()->json([
                'success' => true,
                'has_fingerprints' => $result['has_fingerprints'],
                'enrolled_fingers' => $result['enrolled_fingers'],
                'count' => $result['count'],
                'message' => $result['has_fingerprints'] 
                    ? "User has {$result['count']} fingerprint(s) enrolled" 
                    : 'No fingerprints enrolled yet'
            ]);
        } catch (\Exception $e) {
            Log::error('Check fingerprints error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test registration with detailed logging
     */
    public function testRegistration(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'enroll_id' => 'required|integer|min:1|max:65535',
            'name' => 'required|string|max:255',
        ]);

        $ip = $request->input('ip');
        $port = $request->input('port');
        $enrollId = $request->input('enroll_id');
        $name = $request->input('name');

        $results = [
            'timestamp' => now()->toDateTimeString(),
            'device' => ['ip' => $ip, 'port' => $port],
            'test_user' => ['enroll_id' => $enrollId, 'name' => $name],
            'steps' => [],
            'success' => false,
            'error' => null,
        ];

        try {
            // Step 1: Connect
            $results['steps'][] = ['step' => 1, 'name' => 'Connect to Device', 'status' => 'running'];
            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                throw new \Exception('Failed to connect to device');
            }
            $results['steps'][0]['status'] = 'success';
            $results['steps'][0]['message'] = 'Connected successfully';

            // Step 2: Get device info
            $results['steps'][] = ['step' => 2, 'name' => 'Get Device Info', 'status' => 'running'];
            try {
                $deviceInfo = $zkteco->getDeviceInfo();
                $results['steps'][1]['status'] = 'success';
                $results['steps'][1]['message'] = 'Device info retrieved';
                $results['steps'][1]['data'] = $deviceInfo;
            } catch (\Exception $e) {
                $results['steps'][1]['status'] = 'warning';
                $results['steps'][1]['message'] = 'Could not get device info: ' . $e->getMessage();
            }

            // Step 3: Get users before
            $results['steps'][] = ['step' => 3, 'name' => 'Get Users Before Registration', 'status' => 'running'];
            $usersBefore = $zkteco->getUsers();
            $userCountBefore = count($usersBefore);
            
            $userExists = false;
            foreach ($usersBefore as $key => $deviceUser) {
                if ((string)$key === (string)$enrollId || 
                    (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === (int)$enrollId) ||
                    (isset($deviceUser['user_id']) && (string)$deviceUser['user_id'] === (string)$enrollId)) {
                    $userExists = true;
                    break;
                }
            }

            if ($userExists) {
                $results['steps'][2]['status'] = 'warning';
                $results['steps'][2]['message'] = "User with Enroll ID {$enrollId} already exists on device";
            } else {
                $results['steps'][2]['status'] = 'success';
                $results['steps'][2]['message'] = "Found {$userCountBefore} users on device";
            }
            $results['steps'][2]['data'] = ['user_count' => $userCountBefore];

            // Step 4: Register user
            $results['steps'][] = ['step' => 4, 'name' => 'Register User to Device', 'status' => 'running'];
            if ($userExists) {
                $results['steps'][3]['status'] = 'skipped';
                $results['steps'][3]['message'] = 'User already exists, skipping registration';
            } else {
                $result = $zkteco->registerUser(
                    (int)$enrollId,
                    (string)$enrollId,
                    $name,
                    '',
                    0,
                    0
                );

                if ($result) {
                    $results['steps'][3]['status'] = 'success';
                    $results['steps'][3]['message'] = 'User registration command sent successfully';
                } else {
                    throw new \Exception('Registration returned false');
                }
            }

            // Step 5: Verify
            $results['steps'][] = ['step' => 5, 'name' => 'Verify User on Device', 'status' => 'running'];
            sleep(2);

            $usersAfter = $zkteco->getUsers();
            $userCountAfter = count($usersAfter);
            
            $userFound = false;
            $foundBy = null;
            
            foreach ($usersAfter as $key => $deviceUser) {
                if ((string)$key === (string)$enrollId) {
                    $userFound = true;
                    $foundBy = "array key (userid: '{$key}')";
                    break;
                }
                if (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === (int)$enrollId) {
                    $userFound = true;
                    $foundBy = "UID ({$enrollId})";
                    break;
                }
                if (isset($deviceUser['user_id']) && (string)$deviceUser['user_id'] === (string)$enrollId) {
                    $userFound = true;
                    $foundBy = "user_id ({$deviceUser['user_id']})";
                    break;
                }
            }

            if ($userFound) {
                $results['steps'][4]['status'] = 'success';
                $results['steps'][4]['message'] = "User found on device (found by: {$foundBy})";
                $results['steps'][4]['data'] = [
                    'user_count_before' => $userCountBefore,
                    'user_count_after' => $userCountAfter,
                    'user_count_increased' => $userCountAfter > $userCountBefore,
                    'found_by' => $foundBy,
                ];
                $results['success'] = true;
            } else {
                $results['steps'][4]['status'] = 'failed';
                $results['steps'][4]['message'] = 'User NOT found on device after registration';
                $results['steps'][4]['data'] = [
                    'user_count_before' => $userCountBefore,
                    'user_count_after' => $userCountAfter,
                    'user_count_increased' => $userCountAfter > $userCountBefore,
                ];
                throw new \Exception('User registration verification failed - user not found on device');
            }

            $results['steps'][] = [
                'step' => 6,
                'name' => 'Final Device Users List',
                'status' => 'success',
                'message' => "Total users on device: {$userCountAfter}",
            ];

        } catch (\Exception $e) {
            $results['success'] = false;
            $results['error'] = $e->getMessage();
            
            if (!empty($results['steps'])) {
                $lastStep = &$results['steps'][count($results['steps']) - 1];
                if ($lastStep['status'] === 'running') {
                    $lastStep['status'] = 'failed';
                    $lastStep['message'] = $e->getMessage();
                }
            }
        }

        return response()->json($results, $results['success'] ? 200 : 500);
    }
}
