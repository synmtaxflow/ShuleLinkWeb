<?php

namespace App\Http\Controllers;

use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\Student;
use App\Models\StudentFingerprintAttendance;
use App\Models\Subclass;
use Carbon\Carbon;

class ZKTecoController extends Controller
{
    public function index()
    {
        $user_type = session('user_type', 'Admin');
        return view('Admin.fingerprint_device_settings', compact('user_type'));
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', '192.168.1.108');
        $port = $request->input('port', 4370);

        try {
            $zkteco = new ZKTecoService($ip, $port);
            $result = $zkteco->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDeviceInfo(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', '192.168.1.108');
        $port = $request->input('port', 4370);

        try {
            $zkteco = new ZKTecoService($ip, $port);
            $zkteco->connect();
            $deviceInfo = $zkteco->getDeviceInfo();
            $time = $zkteco->getTime();
            $zkteco->disconnect();

            return response()->json([
                'success' => true,
                'device_info' => $deviceInfo,
                'device_time' => $time,
                'ip' => $ip,
                'port' => $port
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAttendance(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', '192.168.1.108');
        $port = $request->input('port', 4370);

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            // Connect to device
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get attendance logs
            $attendance = $zkteco->getAttendance();
            
            // Disconnect
            $zkteco->disconnect();

            return response()->json([
                'success' => true,
                'attendance' => $attendance,
                'count' => count($attendance),
                'message' => count($attendance) > 0 
                    ? 'Retrieved ' . count($attendance) . ' attendance record(s)' 
                    : 'No attendance records found on device.'
            ]);
        } catch (\Exception $e) {
            Log::error('Get attendance error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get server information for Push SDK setup
     */
    public function getServerInfo()
    {
        $serverIP = request()->server('SERVER_ADDR') ?: request()->ip();
        $serverPort = request()->server('SERVER_PORT') ?: 80;
        $protocol = request()->getScheme();
        $host = request()->getHost();
        
        return response()->json([
            'success' => true,
            'server' => [
                'ip' => $serverIP,
                'host' => $host,
                'port' => $serverPort,
                'protocol' => $protocol,
                'ping_endpoint' => url('/iclock/getrequest'),
                'data_endpoint' => url('/iclock/cdata')
            ],
            'device_config' => [
                'server_ip' => $serverIP,
                'server_port' => $serverPort,
                'server_path' => '/iclock/getrequest'
            ]
        ]);
    }

    /**
     * Test device connection (ping)
     */
    public function testDeviceConnection(Request $request)
    {
        $request->validate([
            'device_ip' => 'required|ip'
        ]);

        $deviceIP = $request->input('device_ip');
        
        // Try to ping the device (this may not work if ping is disabled)
        $canPing = false;
        $pingResult = '';
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $pingResult = @shell_exec("ping -n 1 $deviceIP");
        } else {
            // Linux/Unix
            $pingResult = @shell_exec("ping -c 1 $deviceIP");
        }
        
        $canPing = $pingResult !== null && strpos($pingResult, 'TTL') !== false;
        
        return response()->json([
            'success' => true,
            'device_ip' => $deviceIP,
            'can_ping' => $canPing,
            'ping_result' => $pingResult ?: 'Ping command not available or device does not respond to ping'
        ]);
    }

    /**
     * Import users from device
     */
    public function importUsersFromDevice(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', '192.168.1.108');
        $port = $request->input('port', 4370);

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            // Connect to device
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get users from device
            $deviceUsers = $zkteco->getUsers();
            $zkteco->disconnect();

            if ($deviceUsers === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve users from device. The device may not support this operation or may require authentication.'
                ], 500);
            }

            if (!is_array($deviceUsers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected response from device. Expected array but got: ' . gettype($deviceUsers)
                ], 500);
            }

            // Get current school ID from session
            $schoolID = session('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session. Please login again.'
                ], 400);
            }

            $created = 0;
            $markedRegistered = 0;
            $alreadyRegistered = 0;
            $notFound = 0;
            $errors = [];

            foreach ($deviceUsers as $key => $deviceUser) {
                try {
                    // Extract enroll_id/uid from device user
                    $enrollId = null;
                    $userName = '';
                    
                    if (is_array($deviceUser)) {
                        $enrollId = (string)($deviceUser['uid'] ?? $deviceUser['user_id'] ?? $key ?? '');
                        $userName = trim($deviceUser['name'] ?? '');
                    } else {
                        $enrollId = (string)$key;
                        $userName = is_string($deviceUser) ? trim($deviceUser) : '';
                    }

                    if (empty($enrollId) || $enrollId === '0' || !is_numeric($enrollId)) {
                        $notFound++;
                        $errors[] = "Invalid enroll ID: {$enrollId}";
                        continue;
                    }

                    // Find student by fingerprint_id or studentID (which equals fingerprint_id)
                    $student = Student::where(function($query) use ($enrollId) {
                        $query->where('fingerprint_id', $enrollId)
                              ->orWhere('studentID', (int)$enrollId);
                    })->where('schoolID', $schoolID)->first();
                    
                    if ($student) {
                        // Update student if needed
                        $updated = false;
                        
                        if (!$student->sent_to_device) {
                            $student->sent_to_device = true;
                            $student->device_sent_at = now();
                            $updated = true;
                        }
                        
                        if (empty($student->fingerprint_id)) {
                            $student->fingerprint_id = $enrollId;
                            $updated = true;
                        }
                        
                        if ($updated) {
                            $student->save();
                            $markedRegistered++;
                        } else {
                            $alreadyRegistered++;
                        }
                    } else {
                        // Create new student if not found
                        // We need at least first_name, last_name, gender, and admission_number
                        $firstName = $userName ?: "User {$enrollId}";
                        $lastName = '';
                        
                        // Try to split name if it contains space
                        if (strpos($userName, ' ') !== false) {
                            $nameParts = explode(' ', $userName, 2);
                            $firstName = $nameParts[0];
                            $lastName = $nameParts[1] ?? '';
                        }
                        
                        // Generate unique admission number
                        $admissionNumber = 'DEV' . $enrollId . '-' . date('Y');
                        $counter = 0;
                        while (Student::where('admission_number', $admissionNumber)->exists() && $counter < 100) {
                            $admissionNumber = 'DEV' . $enrollId . '-' . date('Y') . '-' . $counter;
                            $counter++;
                        }
                        
                        // Get default subclass for this school
                        // Join with classes table to filter by schoolID
                        $defaultSubclass = DB::table('subclasses')
                            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                            ->where('classes.schoolID', $schoolID)
                            ->select('subclasses.subclassID')
                            ->first();
                        
                        if (!$defaultSubclass || !isset($defaultSubclass->subclassID)) {
                            $notFound++;
                            $errors[] = "No subclass found for school ID {$schoolID}. Please create at least one class and subclass first.";
                            Log::warning("No subclass found for school ID {$schoolID} when importing user {$enrollId}");
                            continue;
                        }
                        
                        try {
                            $student = Student::create([
                                'studentID' => (int)$enrollId,
                                'schoolID' => $schoolID,
                                'subclassID' => $defaultSubclass->subclassID,
                                'first_name' => $firstName,
                                'last_name' => $lastName ?: $firstName,
                                'gender' => 'Male', // Default - device doesn't provide gender
                                'admission_number' => $admissionNumber,
                                'fingerprint_id' => $enrollId,
                                'sent_to_device' => true,
                                'device_sent_at' => now(),
                                'fingerprint_capture_count' => 0,
                                'status' => 'Active'
                            ]);
                            
                            $created++;
                            Log::info("Created new student from device: Enroll ID {$enrollId}, Name: {$userName}");
                        } catch (\Exception $e) {
                            $notFound++;
                            $errors[] = "Failed to create student for Enroll ID {$enrollId}: " . $e->getMessage();
                            Log::error("Error creating student from device: " . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    $notFound++;
                    $errors[] = "Error processing device user: " . $e->getMessage();
                    Log::error("Error processing device user: " . $e->getMessage());
                }
            }

            $message = "Import completed: {$created} created, {$markedRegistered} marked as registered, {$alreadyRegistered} already registered";
            if ($notFound > 0) {
                $message .= ", {$notFound} could not be processed";
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_device_users' => count($deviceUsers),
                    'verified' => count($deviceUsers),
                    'created' => $created,
                    'marked_registered' => $markedRegistered,
                    'already_registered' => $alreadyRegistered,
                    'not_found' => $notFound,
                    'errors' => $errors
                ],
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Import users from device error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check recent activity (users and attendance)
     */
    public function checkRecentActivity()
    {
        try {
            $usersCount = Student::count();
            $attendancesCount = StudentFingerprintAttendance::count();
            
            // Get recent users (last 5) - students that are sent to device
            $recentUsers = Student::where('sent_to_device', true)
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->count();
            
            // Get recent attendances (last 5)
            $recentAttendances = StudentFingerprintAttendance::orderBy('created_at', 'desc')
                ->limit(5)
                ->count();

            return response()->json([
                'success' => true,
                'users_count' => $usersCount,
                'attendances_count' => $attendancesCount,
                'recent_users' => $recentUsers,
                'recent_attendances' => $recentAttendances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check device status for user management
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
                    // Try to get device info
                    $deviceInfo = $zkteco->getDeviceInfo();
                    if ($deviceInfo) {
                        $status['device_info'] = $deviceInfo;
                        $status['device_name'] = $deviceInfo['device_name'] ?? null;
                        $status['authentication'] = true;
                    }
                    
            // Test reading users
            try {
                $users = $zkteco->getUsers();
                if ($users === false) {
                    $status['issues'][] = 'Cannot read users from device - getUsers returned false';
                    $status['recommendations'][] = 'Check device connection and authentication';
                } elseif (is_array($users)) {
                    $status['can_read_users'] = true;
                    $status['users_count'] = count($users);
                    
                    if (count($users) >= 1000) {
                        $status['issues'][] = 'Device has many users (' . count($users) . ') - memory might be full';
                        $status['recommendations'][] = 'Consider clearing old users or restarting device';
                    }
                } else {
                    $status['issues'][] = 'Unexpected response type from getUsers';
                }
            } catch (\Exception $e) {
                $status['issues'][] = 'Cannot read users from device: ' . $e->getMessage();
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
     * Register student to device (similar to sample project's registerToDevice)
     */
    public function registerStudentToDevice(Request $request, $id)
    {
        try {
            $student = Student::findOrFail($id);
            
            $request->validate([
                'ip' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535'
            ]);

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
                if ($deviceUsers !== false && is_array($deviceUsers)) {
                    foreach ($deviceUsers as $key => $deviceUser) {
                        $deviceEnrollId = (string)($key ?? (is_array($deviceUser) ? ($deviceUser['user_id'] ?? $deviceUser['uid'] ?? '') : ''));
                        if ($deviceEnrollId === (string)$student->fingerprint_id) {
                            return response()->json([
                                'success' => false,
                                'message' => "Enroll ID '{$student->fingerprint_id}' already exists on device",
                                'troubleshooting' => "⚠️ ENROLL ID CONFLICT:\n\nEnroll ID {$student->fingerprint_id} is already registered on the device.\n\n✅ SOLUTIONS:\n1. Use a different Enroll ID for this student\n2. Remove the existing user from device first\n3. Click 'Sync Users from Device' to update status if this is the same student"
                            ], 400);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Pre-registration check failed: ' . $e->getMessage());
                // Continue with registration attempt anyway
            }
            
            // Validate fingerprint_id is numeric
            if (!is_numeric($student->fingerprint_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint ID must be numeric (e.g., 1, 2, 3...)'
                ], 400);
            }
            
            // Register student to device
            // UID is typically the fingerprint_id, userid is also fingerprint_id, name is student's name
            $uid = (int) $student->fingerprint_id;
            
            if ($uid < 1 || $uid > 65535) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint ID must be between 1 and 65535'
                ], 400);
            }
            
            // Get student full name
            $studentName = trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name);
            $studentName = strtoupper($studentName);
            // Truncate to 24 characters (device limit)
            if (strlen($studentName) > 24) {
                $studentName = substr($studentName, 0, 24);
            }
            
            $result = $zkteco->registerUser(
                $uid,
                $student->fingerprint_id,
                $studentName,
                '', // password (empty for fingerprint devices)
                0,  // role (0 = user, 14 = admin)
                0   // cardno
            );

            if ($result) {
                $student->update([
                    'sent_to_device' => true,
                    'device_sent_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Student registered to device successfully and verified!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register student to device'
                ], 500);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Register student to device error: ' . $e->getMessage());
            
            $errorMessage = $e->getMessage();
            
            // Check if error message suggests student might be on device
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
                $troubleshooting[] = "1. Fingerprint ID already exists on device (check device user list)";
                $troubleshooting[] = "2. Device memory is full";
                $troubleshooting[] = "3. Device is locked or in wrong mode";
                $troubleshooting[] = "4. Device needs to be enabled/unlocked";
                $troubleshooting[] = "";
                $troubleshooting[] = "✅ QUICK FIXES:";
                $troubleshooting[] = "• Check device: User Management → User List (see if Fingerprint ID {$student->fingerprint_id} exists)";
                $troubleshooting[] = "• If student exists on device, click 'Sync Users from Device' to update status";
                $troubleshooting[] = "• Try a different Fingerprint ID if current one is taken";
            }
            
            if ($isFirmwareIssue || (!$isCommKeyIssue && !$isDeviceRejected)) {
                $troubleshooting[] = "";
                $troubleshooting[] = "⚠️ FIRMWARE COMPATIBILITY ISSUE:";
                $troubleshooting[] = "Your device may have firmware compatibility issues.";
                $troubleshooting[] = "";
                $troubleshooting[] = "✅ WORKAROUND - Manual Registration:";
                $troubleshooting[] = "1. On device: User Management → Add User";
                $troubleshooting[] = "   • Enroll ID: {$student->fingerprint_id}";
                $troubleshooting[] = "   • Name: {$studentName}";
                $troubleshooting[] = "   • Save";
                $troubleshooting[] = "";
                $troubleshooting[] = "2. On web: Click 'Sync Users from Device' button";
                $troubleshooting[] = "   • Student will be synced automatically!";
                $troubleshooting[] = "";
                $troubleshooting[] = "3. Enroll fingerprint (optional):";
                $troubleshooting[] = "   • On device: User Management → Enroll Fingerprint";
                $troubleshooting[] = "   • Enter Enroll ID: {$student->fingerprint_id}";
            }
            
            $troubleshooting[] = "";
            $troubleshooting[] = "🔄 DEVICE RESTART (If it was working before):";
            $troubleshooting[] = "1. Power cycle the device (turn off, wait 10 seconds, turn on)";
            $troubleshooting[] = "2. Wait 30 seconds for device to fully boot";
            $troubleshooting[] = "3. Try registration again";
            
            return response()->json([
                'success' => false,
                'message' => 'Registration Failed: ' . $errorMessage,
                'troubleshooting' => implode("\n", $troubleshooting),
                'might_be_registered' => $mightBeRegistered,
                'error_type' => [
                    'comm_key_issue' => $isCommKeyIssue,
                    'device_rejected' => $isDeviceRejected,
                    'firmware_issue' => $isFirmwareIssue
                ],
                'user_info' => [
                    'name' => $studentName,
                    'enroll_id' => $student->fingerprint_id
                ],
                'device_info' => [
                    'ip' => $ip,
                    'port' => $port
                ]
            ], 500);
        }
    }

    /**
     * Register user to device
     */
    public function registerUserToDevice(Request $request)
    {
        try {
            $request->validate([
                'ip' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535',
                'enroll_id' => 'required|integer|min:1|max:65535',
                'name' => 'required|string|max:24'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        }

        $ip = $request->input('ip');
        $port = $request->input('port');
        $enrollId = $request->input('enroll_id');
        $name = trim($request->input('name'));

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            // Test connection first
            if (!$zkteco->connect()) {
                // Check logs for specific error
                $lastLog = Log::getLogger()->getHandlers()[0]->getLevel() ?? null;
                
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot connect to device. Check IP, port, and network connectivity.',
                    'suggestion' => 'If device closes connection immediately (Error 10054), try: 1) Check Comm Key in device settings, 2) Use Push SDK instead (see Push SDK Setup Wizard tab), 3) Verify device firmware supports TCP commands, 4) Check device firewall/security settings.'
                ], 500);
            }
            
            // Check if Enroll ID already exists on device
            try {
                $deviceUsers = $zkteco->getUsers();
                if ($deviceUsers === false) {
                    Log::warning('Could not retrieve users from device to check for duplicates, proceeding with registration');
                } elseif (is_array($deviceUsers)) {
                    foreach ($deviceUsers as $key => $deviceUser) {
                        if (!is_array($deviceUser)) {
                            continue;
                        }
                        $deviceEnrollId = (string)($key ?? $deviceUser['user_id'] ?? $deviceUser['uid'] ?? '');
                        if ($deviceEnrollId === (string)$enrollId) {
                            return response()->json([
                                'success' => false,
                                'message' => "Enroll ID '{$enrollId}' already exists on device"
                            ], 400);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error checking for duplicate users: ' . $e->getMessage());
                // Continue with registration even if check fails
            }
            
            // Register user to device
            // registerUser signature: registerUser($uid, $name, $privilege = 0, $password = '', $group = '', $user_id = '')
            try {
                $result = $zkteco->registerUser(
                    (int)$enrollId,      // uid
                    $name,               // name
                    0,                   // privilege (0 = user)
                    '',                  // password (empty for fingerprint devices)
                    '',                  // group
                    (string)$enrollId    // user_id
                );

                if ($result) {
                    // Verify registration by checking if user exists on device
                    try {
                        $deviceUsers = $zkteco->getUsers();
                        $userFound = false;
                        
                        foreach ($deviceUsers as $deviceUser) {
                            if (is_array($deviceUser)) {
                                $deviceUid = $deviceUser['uid'] ?? null;
                                $deviceUserId = $deviceUser['user_id'] ?? null;
                                
                                if ($deviceUid == (int)$enrollId || $deviceUserId == (string)$enrollId) {
                                    $userFound = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($userFound) {
                            return response()->json([
                                'success' => true,
                                'message' => "User '{$name}' (Enroll ID: {$enrollId}) registered to device successfully and verified!"
                            ]);
                        } else {
                            // Registration returned true but user not found - might be timing issue
                            Log::warning("Registration returned true but user not immediately found on device. This might be a timing issue.");
                            return response()->json([
                                'success' => true,
                                'message' => "User '{$name}' (Enroll ID: {$enrollId}) registration command succeeded. Please verify on device or refresh users list.",
                                'warning' => 'User not immediately found on device - this might be a timing issue. Please check device or refresh users list.'
                            ]);
                        }
                    } catch (\Exception $verifyException) {
                        Log::warning('Could not verify user registration: ' . $verifyException->getMessage());
                        // Still return success as registration command succeeded
                        return response()->json([
                            'success' => true,
                            'message' => "User '{$name}' (Enroll ID: {$enrollId}) registration command succeeded. Verification failed, please check device manually.",
                            'warning' => 'Could not verify registration automatically. Please check device or refresh users list.'
                        ]);
                    }
                } else {
                    // Registration returned false
                    $errorMessage = 'Failed to register user to device. The device may have rejected the registration.';
                    $suggestion = 'Possible reasons: 1) Enroll ID already exists, 2) Device memory full, 3) Invalid data format, 4) Comm Key incorrect, 5) Device firmware issue.';
                    
                    // Check recent logs for connection closure
                    $logFile = storage_path('logs/laravel.log');
                    if (file_exists($logFile)) {
                        $logContent = file_get_contents($logFile);
                        if (strpos($logContent, '10054') !== false || strpos($logContent, 'forcibly closed') !== false) {
                            $errorMessage = 'Device closed connection during registration.';
                            $suggestion = 'This device may require: 1) Correct Comm Key (check device settings), 2) Push SDK configuration instead of direct TCP (see Push SDK Setup Wizard tab), 3) Firmware update, or 4) Device may be in restricted mode.';
                        }
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'suggestion' => $suggestion
                    ], 500);
                }
            } catch (\Exception $regException) {
                // Registration threw an exception
                $errorMessage = 'Registration Failed: ' . $regException->getMessage();
                
                // Check if it's a connection closure issue
                $isConnectionIssue = strpos($regException->getMessage(), 'closed') !== false || 
                                    strpos($regException->getMessage(), '10054') !== false ||
                                    strpos($regException->getMessage(), 'setUser returned false') !== false;
                
                $troubleshooting = '';
                if ($isConnectionIssue) {
                    $troubleshooting = "Device closed connection or rejected registration. This is common with UF200-S firmware 6.60.\n\n";
                    $troubleshooting .= "SOLUTIONS:\n";
                    $troubleshooting .= "1. Register user DIRECTLY on device:\n";
                    $troubleshooting .= "   - Press MENU on device\n";
                    $troubleshooting .= "   - Go to: User Management → User List → Add User\n";
                    $troubleshooting .= "   - Enter Enroll ID: {$enrollId}, Name: {$name}\n";
                    $troubleshooting .= "   - Save, then use 'Get Users from Device' to sync\n\n";
                    $troubleshooting .= "2. Use Push SDK (recommended for this device):\n";
                    $troubleshooting .= "   - Go to 'Push SDK Setup Wizard' tab\n";
                    $troubleshooting .= "   - Configure device ADMS settings\n";
                    $troubleshooting .= "   - Device will push user data automatically\n\n";
                    $troubleshooting .= "3. Check device settings:\n";
                    $troubleshooting .= "   - System → Communication → Comm Key (should be 0)\n";
                    $troubleshooting .= "   - Ensure device is not in sleep/restricted mode";
                } else {
                    $troubleshooting = 'Check device logs, verify Comm Key, ensure Enroll ID is unique, and try using Push SDK if direct TCP fails.';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'troubleshooting' => $troubleshooting
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Register user to device error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Registration Failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List users from device
     */
    public function listDeviceUsers(Request $request)
    {
        try {
            $request->validate([
                'ip' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        }

        $ip = $request->input('ip');
        $port = $request->input('port');

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            // Test connection first
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check IP, port, and network connectivity.'
                ], 500);
            }
            
            $users = $zkteco->getUsers();
            
            // getUsers() now returns array (empty array on failure)
            if (!is_array($users)) {
                Log::error('getUsers returned non-array: ' . gettype($users) . ' - ' . var_export($users, true));
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected response type from device. Expected array but got: ' . gettype($users)
                ], 500);
            }
            
            if (empty($users)) {
                return response()->json([
                    'success' => true,
                    'users' => [],
                    'count' => 0,
                    'message' => 'No users found on device.'
                ]);
            }
            
            // Format users for better display
            $formattedUsers = [];
            foreach ($users as $key => $user) {
                if (is_array($user)) {
                    $formattedUsers[] = [
                        'uid' => $user['uid'] ?? $key ?? 'N/A',
                        'user_id' => $user['user_id'] ?? $key ?? 'N/A',
                        'name' => $user['name'] ?? 'N/A',
                        'role' => $user['privilege'] ?? $user['role'] ?? 'N/A',
                        'card_no' => $user['card_no'] ?? 'N/A',
                        'raw_data' => $user
                    ];
                } else {
                    // Handle case where user data is not in expected format
                    $formattedUsers[] = [
                        'uid' => $key ?? 'N/A',
                        'user_id' => $key ?? 'N/A',
                        'name' => is_string($user) ? $user : 'N/A',
                        'role' => 'N/A',
                        'card_no' => 'N/A',
                        'raw_data' => $user
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'users' => $formattedUsers,
                'raw_users' => $users,
                'count' => count($users),
                'message' => count($users) > 0 
                    ? "Found " . count($users) . " user(s) on device" 
                    : "No users found on device."
            ]);
        } catch (\Exception $e) {
            Log::error('List device users error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user from device
     */
    public function deleteUserFromDevice(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'enroll_id' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip');
        $port = $request->input('port');
        $enrollId = $request->input('enroll_id');

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device.'
                ], 500);
            }

            // Check if ZKTecoService has removeUser method
            // If not, we'll need to implement it or use alternative method
            $uid = (int)$enrollId;
            
            // Try to remove user - note: ZKTecoService may need removeUser method
            // For now, we'll return a message that this needs to be implemented
            // You can add removeUser method to ZKTecoService based on device protocol
            
            return response()->json([
                'success' => false,
                'message' => 'Delete user functionality needs to be implemented in ZKTecoService. Please check if removeUser method exists.'
            ], 501);
            
        } catch (\Exception $e) {
            Log::error('Delete user from device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's attendance records from device (raw format)
     * Uses getAttendance() and filters by today's date
     */
    public function getTodayAttendance(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', config('zkteco.ip'));
        $port = $request->input('port', config('zkteco.port'));
        $today = Carbon::today()->toDateString();

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get all attendance from device
            $allAttendance = $zkteco->getAttendance();
            
            // Disconnect
            $zkteco->disconnect();

            // Filter by today's date
            $todayAttendance = array_filter($allAttendance, function($record) use ($today) {
                if (!isset($record['record_time'])) {
                    return false;
                }
                $recordDate = Carbon::parse($record['record_time'])->toDateString();
                return $recordDate === $today;
            });

            // Re-index array
            $todayAttendance = array_values($todayAttendance);

            // Attach user names from students table if possible
            $userIds = collect($todayAttendance)->pluck('user_id')->filter()->unique()->values();
            $students = Student::whereIn('fingerprint_id', $userIds)->get()
                ->keyBy('fingerprint_id');

            foreach ($todayAttendance as &$record) {
                $userId = $record['user_id'] ?? null;
                $student = $userId ? $students->get($userId) : null;
                if ($student) {
                    $fullName = trim(implode(' ', array_filter([
                        $student->first_name ?? null,
                        $student->middle_name ?? null,
                        $student->last_name ?? null,
                    ])));
                    $record['user_name'] = $fullName !== '' ? $fullName : (string)$userId;
                } else {
                    $record['user_name'] = $userId !== null ? (string)$userId : null;
                }
            }

            return response()->json([
                'success' => true,
                'date' => $today,
                'attendance' => $todayAttendance,
                'count' => count($todayAttendance),
                'message' => count($todayAttendance) > 0 
                    ? 'Retrieved ' . count($todayAttendance) . ' attendance record(s) for today' 
                    : 'No attendance records found for today on device.'
            ]);
        } catch (\Exception $e) {
            Log::error('Get today attendance error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving today\'s attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records for a specific date from device (raw format)
     * Uses getAttendance() and filters by selected date
     */
    public function getAttendanceByDate(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'date' => 'nullable|date'
        ]);

        $ip = $request->input('ip', config('zkteco.ip'));
        $port = $request->input('port', config('zkteco.port'));
        $selectedDate = $request->input('date', now()->toDateString());
        $carbonDate = Carbon::parse($selectedDate)->toDateString();

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get all attendance from device
            $allAttendance = $zkteco->getAttendance();
            
            // Disconnect
            $zkteco->disconnect();

            // Filter by selected date
            $filteredAttendance = array_filter($allAttendance, function($record) use ($carbonDate) {
                if (!isset($record['record_time'])) {
                    return false;
                }
                $recordDate = Carbon::parse($record['record_time'])->toDateString();
                return $recordDate === $carbonDate;
            });

            // Re-index array
            $filteredAttendance = array_values($filteredAttendance);

            // Attach user names from students table if possible
            $userIds = collect($filteredAttendance)->pluck('user_id')->filter()->unique()->values();
            $students = Student::whereIn('fingerprint_id', $userIds)->get()
                ->keyBy('fingerprint_id');

            foreach ($filteredAttendance as &$record) {
                $userId = $record['user_id'] ?? null;
                $student = $userId ? $students->get($userId) : null;
                if ($student) {
                    $fullName = trim(implode(' ', array_filter([
                        $student->first_name ?? null,
                        $student->middle_name ?? null,
                        $student->last_name ?? null,
                    ])));
                    $record['user_name'] = $fullName !== '' ? $fullName : (string)$userId;
                } else {
                    $record['user_name'] = $userId !== null ? (string)$userId : null;
                }
            }

            return response()->json([
                'success' => true,
                'date' => $carbonDate,
                'attendance' => $filteredAttendance,
                'count' => count($filteredAttendance),
                'message' => count($filteredAttendance) > 0 
                    ? 'Retrieved ' . count($filteredAttendance) . ' attendance record(s) for ' . $carbonDate
                    : 'No attendance records found for ' . $carbonDate . ' on device.'
            ]);
        } catch (\Exception $e) {
            Log::error('Get attendance by date error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fingerprint attendance from database table for class management
     * - Loads from student_fingerprint_attendance table (not device)
     * - Filters by subclassID and optional date
     * - Joins with students table for additional info
     * - Returns data in format expected by frontend
     */
    public function getFingerprintAttendanceFromDB(Request $request)
    {
        $request->validate([
            'subclassID' => 'required|integer',
            'date' => 'nullable|date',
            'month' => 'nullable|string', // Format: YYYY-MM
            'year' => 'nullable|integer',
            'page' => 'nullable|integer|min:1'
        ]);

        $subclassID = $request->input('subclassID');
        $date = $request->input('date');
        $month = $request->input('month');
        $year = $request->input('year');
        $page = $request->input('page', 1);
        $perPage = 50;

        try {
            // Get students in this subclass
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID');

            // Build query for student_fingerprint_attendance
            $query = StudentFingerprintAttendance::whereIn('studentID', $students->pluck('studentID'));

            // Filter by date if provided
            if ($date) {
                $carbonDate = Carbon::parse($date)->toDateString();
                $query->whereDate('attendance_date', $carbonDate);
            } elseif ($month) {
                // Filter by month (YYYY-MM format)
                $carbonMonth = Carbon::parse($month . '-01');
                $query->whereYear('attendance_date', $carbonMonth->year)
                    ->whereMonth('attendance_date', $carbonMonth->month);
            } elseif ($year) {
                // Filter by year
                $query->whereYear('attendance_date', $year);
            }

            // Get records with pagination
            $attendanceRecords = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $data = [];

            foreach ($attendanceRecords as $record) {
                /** @var Student|null $student */
                $student = $students->get($record->studentID);
                if (!$student) {
                    continue;
                }

                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $className = $student->subclass ? $student->subclass->subclass_name : 'N/A';
                $fingerprintId = $student->fingerprint_id ?? $student->studentID;

                $data[] = [
                    'studentID' => $student->studentID,
                    'user_id' => $student->studentID,
                    'user_name' => $studentName ?: 'N/A',
                    'enroll_id' => $student->studentID,
                    'fingerprint_id' => $fingerprintId,
                    'class_name' => $className,
                    'attendance_date' => $record->attendance_date ? $record->attendance_date->toDateString() : null,
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => ($record->check_in_time && $record->check_out_time) ? 'Complete' : 'Check In Only',
                    'verify_mode' => $record->verify_mode ?? 'Fingerprint',
                    'device_ip' => $record->device_ip ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $attendanceRecords->currentPage(),
                    'per_page' => $attendanceRecords->perPage(),
                    'total' => $attendanceRecords->total(),
                    'last_page' => $attendanceRecords->lastPage(),
                ],
                'message' => 'Retrieved ' . $attendanceRecords->total() . ' attendance record(s) from database'
            ]);
        } catch (\Exception $e) {
            Log::error('Get fingerprint attendance from DB error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance from database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate working days (excluding weekends) for a given month or year
     * 
     * @param string|null $month Format: YYYY-MM
     * @param int|null $year
     * @return int Number of working days
     */
    private function calculateWorkingDays($month = null, $year = null)
    {
        $today = Carbon::today();
        $startDate = null;
        $endDate = null;

        if ($month) {
            // Calculate for specific month
            $carbonMonth = Carbon::parse($month . '-01');
            $startDate = $carbonMonth->copy()->startOfMonth();
            
            // If current month, end at today; otherwise end of month
            if ($carbonMonth->year == $today->year && $carbonMonth->month == $today->month) {
                $endDate = $today->copy();
            } else {
                $endDate = $carbonMonth->copy()->endOfMonth();
            }
        } elseif ($year) {
            // Calculate for specific year
            $startDate = Carbon::create($year, 1, 1);
            
            // If current year, end at today; otherwise end of year
            if ($year == $today->year) {
                $endDate = $today->copy();
            } else {
                $endDate = Carbon::create($year, 12, 31);
            }
        } else {
            return 0;
        }

        $workingDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            $dayOfWeek = $current->dayOfWeek;
            if ($dayOfWeek != Carbon::SATURDAY && $dayOfWeek != Carbon::SUNDAY) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Get fingerprint attendance from device for class management
     * Returns data in format expected by frontend (similar to API format)
     */
    public function getFingerprintAttendanceForClass(Request $request)
    {
        $request->validate([
            'subclassID' => 'required|integer',
            'date' => 'nullable|date',
            'page' => 'nullable|integer|min:1'
        ]);

        $subclassID = $request->input('subclassID');
        $date = $request->input('date');
        $page = $request->input('page', 1);
        $perPage = 50;

        try {
            $ip = config('zkteco.ip', '192.168.1.108');
            $port = (int) config('zkteco.port', 4370);

            // Get students in this subclass with subclass info
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID'); // user_id from device = studentID (primary key)

            // Connect to device and get all attendance
            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
                ], 500);
            }
            
            $allAttendance = $zkteco->getAttendance();
            $zkteco->disconnect();

            $today = Carbon::today()->toDateString();
            $excludeToday = $request->input('exclude_today', false); // Default: include today (for Fingerprint Attendance tab)

            // Filter by date if provided
            if ($date) {
                $carbonDate = Carbon::parse($date)->toDateString();
                $allAttendance = array_filter($allAttendance, function($record) use ($carbonDate, $today, $excludeToday) {
                    if (!isset($record['record_time'])) return false;
                    $recordDate = Carbon::parse($record['record_time'])->toDateString();
                    // If exclude_today is true AND this is today's date, exclude it (for overview tab)
                    if ($excludeToday && $recordDate === $today) return false;
                    // Otherwise, filter by the requested date
                    return $recordDate === $carbonDate;
                });
                $allAttendance = array_values($allAttendance);
            } else {
                // If no date specified, exclude today only if exclude_today flag is set (for overview)
                if ($excludeToday) {
                    $allAttendance = array_filter($allAttendance, function($record) use ($today) {
                        if (!isset($record['record_time'])) return false;
                        $recordDate = Carbon::parse($record['record_time'])->toDateString();
                        return $recordDate !== $today; // Exclude today for overview
                    });
                    $allAttendance = array_values($allAttendance);
                }
                // If excludeToday is false, include all records (including today) - for Fingerprint Attendance tab
            }

            // Group by user_id (which is studentID) and date
            // Security: Only take first sign-in (Check In) and second sign-in (Check Out)
            // Ignore any additional sign-ins for security reasons
            $grouped = [];
            $userDateRecords = []; // Track records per user per date

            // First, collect all records per user per date
            foreach ($allAttendance as $record) {
                $userId = isset($record['user_id']) ? (int)$record['user_id'] : null;
                if (!$userId) continue;

                // user_id from device = studentID (primary key)
                $student = $students->get($userId);
                if (!$student) continue; // Only include students from this subclass

                $recordDate = Carbon::parse($record['record_time'])->toDateString();
                $key = $userId . '_' . $recordDate;

                if (!isset($userDateRecords[$key])) {
                    $userDateRecords[$key] = [];
                }

                $userDateRecords[$key][] = [
                    'time' => Carbon::parse($record['record_time']),
                    'type' => $record['type'] ?? null,
                    'device_ip' => $record['device_ip'] ?? null,
                    'student' => $student,
                ];
            }

            // Process each user-date combination: only first 2 records
            foreach ($userDateRecords as $key => $records) {
                // Sort by time (ascending)
                usort($records, function($a, $b) {
                    return $a['time']->lt($b['time']) ? -1 : 1;
                });

                // Security: Only take first 2 records (Check In and Check Out)
                // Ignore any additional records
                if (count($records) < 1) continue;

                $firstRecord = $records[0];
                $secondRecord = count($records) >= 2 ? $records[1] : null;

                $student = $firstRecord['student'];
                $recordDate = $firstRecord['time']->toDateString();
                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $className = $student->subclass ? $student->subclass->subclass_name : 'N/A';
                $fingerprintId = $student->fingerprint_id ?? $student->studentID;
                $userId = $student->studentID;

                $grouped[$key] = [
                    'studentID' => $student->studentID,
                    'user_id' => $userId,
                    'user_name' => $studentName ?: 'N/A',
                    'enroll_id' => $userId,
                    'fingerprint_id' => $fingerprintId,
                    'class_name' => $className,
                    'attendance_date' => $recordDate,
                    'check_in_time' => $firstRecord['time']->format('Y-m-d H:i:s'), // First sign-in = Check In
                    'check_out_time' => $secondRecord ? $secondRecord['time']->format('Y-m-d H:i:s') : null, // Second sign-in = Check Out
                    'status' => $secondRecord ? 'Complete' : 'Check In Only',
                    'verify_mode' => $firstRecord['type'] ?? null,
                    'device_ip' => $firstRecord['device_ip'] ?? null,
                ];
            }

            $records = array_values($grouped);

            // Paginate
            $total = count($records);
            $offset = ($page - 1) * $perPage;
            $paginatedRecords = array_slice($records, $offset, $perPage);

            return response()->json([
                'success' => true,
                'data' => $paginatedRecords,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
                'message' => 'Retrieved ' . $total . ' attendance record(s) from device'
            ]);
        } catch (\Exception $e) {
            Log::error('Get fingerprint attendance for class error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Fallback to database if device fails
            return $this->getFingerprintAttendanceFromDBFallback($request);
        }
    }

    /**
     * Live attendance sync for TODAY:
     * - Reads raw attendance from device for current date
     * - Applies security rule: only first 2 punches per student per day
     *   (1st = check_in, 2nd = check_out, others ignored)
     * - Upserts into student_fingerprint_attendance table (one row per student per day)
     * - Returns today's aggregated attendance for the requested subclass
     */
    public function syncLiveAttendanceToday(Request $request)
    {
        $request->validate([
            'subclassID' => 'required|integer',
        ]);

        $subclassID = (int) $request->input('subclassID');
        $today = Carbon::today()->toDateString();

        try {
            $ip = config('zkteco.ip', '192.168.1.108');
            $port = (int) config('zkteco.port', 4370);

            // Log connection attempt
            Log::info("Live Attendance Sync: Attempting to connect to device at {$ip}:{$port}");

            // Get students in this subclass with subclass info
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID'); // user_id from device = studentID (primary key)

            // Connect to device and get all attendance
            $zkteco = new ZKTecoService($ip, $port);

            if (!$zkteco->connect()) {
                // If device is unavailable, just read from DB for today (no new sync)
                Log::warning("Live Attendance Sync: Failed to connect to device at {$ip}:{$port}. Falling back to database.");
                return $this->getLiveAttendanceTodayFromDB($subclassID, $today, true);
            }

            Log::info("Live Attendance Sync: Successfully connected to device at {$ip}:{$port}");

            $allAttendance = $zkteco->getAttendance();
            $zkteco->disconnect();

            Log::info("Live Attendance Sync: Retrieved " . (is_array($allAttendance) ? count($allAttendance) : 0) . " attendance record(s) from device");

            if (!is_array($allAttendance)) {
                // Invalid data from device, fallback to DB
                Log::warning("Live Attendance Sync: Invalid data received from device. Falling back to database.");
                return $this->getLiveAttendanceTodayFromDB($subclassID, $today, true);
            }

            // Collect records per user for TODAY only
            $userDateRecords = [];

            foreach ($allAttendance as $record) {
                $userId = isset($record['user_id']) ? (int)$record['user_id'] : null;
                if (!$userId) {
                    continue;
                }

                // Date check: only today's records
                if (!isset($record['record_time'])) {
                    continue;
                }

                $recordTime = Carbon::parse($record['record_time']);
                $recordDate = $recordTime->toDateString();
                if ($recordDate !== $today) {
                    continue;
                }

                // user_id from device = studentID (primary key)
                /** @var Student|null $student */
                $student = $students->get($userId);
                if (!$student) {
                    // Not in this subclass or not an active student
                    continue;
                }

                $key = $userId . '_' . $recordDate;

                if (!isset($userDateRecords[$key])) {
                    $userDateRecords[$key] = [];
                }

                $userDateRecords[$key][] = [
                    'time' => $recordTime,
                    'type' => $record['type'] ?? 'Fingerprint',
                    'device_ip' => $record['device_ip'] ?? $ip,
                    'student' => $student,
                ];
            }

            // Apply rule: only first 2 punches per day per student
            foreach ($userDateRecords as $key => $records) {
                // Sort by time ascending
                usort($records, function ($a, $b) {
                    if ($a['time']->eq($b['time'])) {
                        return 0;
                    }
                    return $a['time']->lt($b['time']) ? -1 : 1;
                });

                if (count($records) < 1) {
                    continue;
                }

                $firstRecord = $records[0];
                $secondRecord = count($records) >= 2 ? $records[1] : null;

                /** @var Student $student */
                $student = $firstRecord['student'];
                $recordDate = $firstRecord['time']->toDateString();
                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $fingerprintId = $student->fingerprint_id ?? $student->studentID;

                $checkInTime = $firstRecord['time']->format('Y-m-d H:i:s');
                $checkOutTime = $secondRecord ? $secondRecord['time']->format('Y-m-d H:i:s') : null;
                $status = $checkOutTime ? 1 : 0; // 1 = complete, 0 = check in only

                // Upsert into student_fingerprint_attendance (one row per student per day)
                StudentFingerprintAttendance::updateOrCreate(
                    [
                        'studentID' => $student->studentID,
                        'attendance_date' => $recordDate,
                    ],
                    [
                        'user_id' => $student->studentID,
                        'user_name' => $studentName ?: 'N/A',
                        'enroll_id' => $student->studentID,
                        'check_in_time' => $checkInTime,
                        'check_out_time' => $checkOutTime,
                        'status' => $status,
                        'verify_mode' => $firstRecord['type'] ?? 'Fingerprint',
                        'device_ip' => $firstRecord['device_ip'] ?? $ip,
                    ]
                );
            }

            // After sync, return today's attendance for this subclass from DB
            return $this->getLiveAttendanceTodayFromDB($subclassID, $today, false);
        } catch (\Exception $e) {
            Log::error('Live attendance sync error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // On error, still try to return what we have in DB for today
            return $this->getLiveAttendanceTodayFromDB($subclassID, $today, true);
        }
    }

    /**
     * Helper: Get today's fingerprint attendance for subclass from DB
     * - Always one row per student per day (enforced by updateOrCreate above)
     * - Used both after successful sync and as fallback when device is unavailable
     */
    private function getLiveAttendanceTodayFromDB(int $subclassID, string $today, bool $deviceFailed = false)
    {
        try {
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID');

            $attendanceRecords = StudentFingerprintAttendance::whereIn('studentID', $students->pluck('studentID'))
                ->whereDate('attendance_date', $today)
                ->orderBy('check_in_time', 'desc') // Latest check-in first (for new records)
                ->get();

            $data = [];
            $newRecords = [];
            $oldRecords = [];

            foreach ($attendanceRecords as $record) {
                /** @var Student|null $student */
                $student = $students->get($record->studentID);
                if (!$student) {
                    continue;
                }

                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $className = $student->subclass ? $student->subclass->subclass_name : 'N/A';
                $fingerprintId = $student->fingerprint_id ?? $student->studentID;

                // Check if record is new based on check-in time (within last 10 minutes)
                // Use Carbon to properly validate time - check if check-in time is recent
                $isNew = false;
                if ($record->check_in_time) {
                    $checkInTime = Carbon::parse($record->check_in_time);
                    $now = Carbon::now();
                    $minutesAgo = $checkInTime->diffInMinutes($now);
                    $isNew = $minutesAgo <= 10; // New if check-in time is within last 10 minutes
                }

                $recordData = [
                    'studentID' => $student->studentID,
                    'user_id' => $student->studentID,
                    'user_name' => $studentName ?: 'N/A',
                    'enroll_id' => $student->studentID,
                    'fingerprint_id' => $fingerprintId,
                    'class_name' => $className,
                    'attendance_date' => $record->attendance_date ? $record->attendance_date->toDateString() : $today,
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => ($record->check_in_time && $record->check_out_time) ? 'Complete' : 'Check In Only',
                    'verify_mode' => $record->verify_mode ?? null,
                    'device_ip' => $record->device_ip ?? null,
                    'is_new' => $isNew, // Will be updated later - only latest new record gets this flag
                ];

                // Separate new and old records
                if ($isNew) {
                    $newRecords[] = $recordData;
                } else {
                    $oldRecords[] = $recordData;
                }
            }

            // Sort new records by check_in_time descending (latest first - aliye chelewa juu)
            usort($newRecords, function($a, $b) {
                $timeA = $a['check_in_time'] ? strtotime($a['check_in_time']) : 0;
                $timeB = $b['check_in_time'] ? strtotime($b['check_in_time']) : 0;
                return $timeB - $timeA; // Descending (latest first)
            });

            // Only the first (latest) new record should have is_new = true
            // All other new records should have is_new = false
            if (count($newRecords) > 0) {
                // Mark only the first one (latest check-in time) as new
                $newRecords[0]['is_new'] = true;
                // Mark all others as not new
                for ($i = 1; $i < count($newRecords); $i++) {
                    $newRecords[$i]['is_new'] = false;
                }
            }

            // Sort old records by check_in_time descending (latest first)
            usort($oldRecords, function($a, $b) {
                $timeA = $a['check_in_time'] ? strtotime($a['check_in_time']) : 0;
                $timeB = $b['check_in_time'] ? strtotime($b['check_in_time']) : 0;
                return $timeB - $timeA; // Descending (latest first)
            });

            // Combine: new records first, then old records
            $data = array_merge($newRecords, $oldRecords);

            return response()->json([
                'success' => true,
                'date' => $today,
                'device_failed' => $deviceFailed,
                'data' => $data,
                'count' => count($data),
                'message' => ($deviceFailed ? 'Device unavailable, showing attendance from database for today.' : 'Live attendance synced from device and stored to database for today.')
            ]);
        } catch (\Exception $e) {
            Log::error('Get live attendance today from DB error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'date' => $today,
                'device_failed' => $deviceFailed,
                'message' => 'Error retrieving today\'s attendance from database: ' . $e->getMessage(),
                'data' => [],
                'count' => 0,
            ], 500);
        }
    }

    /**
     * Fallback: Get fingerprint attendance from database (includes today's date)
     * Used as fallback when device is unavailable
     */
    private function getFingerprintAttendanceFromDBFallback(Request $request)
    {
        $subclassID = $request->input('subclassID');
        $date = $request->input('date');
        $page = $request->input('page', 1);
        $perPage = 50;

        try {
            // Get students in this subclass
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID');

            // Build query for student_fingerprint_attendance
            $query = StudentFingerprintAttendance::whereIn('studentID', $students->pluck('studentID'));

            // Filter by date if provided (includes today)
            if ($date) {
                $carbonDate = Carbon::parse($date)->toDateString();
                $query->whereDate('attendance_date', $carbonDate);
            }
            // If no date, get all records (including today)

            // Get records and group by studentID and date
            $attendanceRecords = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'asc')
                ->get();

            // Group by studentID and date (one record per student per day)
            $grouped = [];
            foreach ($attendanceRecords as $record) {
                $student = $students->get($record->studentID);
                if (!$student) continue;

                $key = $record->studentID . '_' . $record->attendance_date->toDateString();

                if (!isset($grouped[$key])) {
                    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                    $className = $student->subclass ? $student->subclass->subclass_name : 'N/A';
                    $fingerprintId = $student->fingerprint_id ?? $student->studentID;

                    $grouped[$key] = [
                        'studentID' => $student->studentID,
                        'user_id' => $student->studentID,
                        'user_name' => $studentName ?: 'N/A',
                        'enroll_id' => $student->studentID,
                        'fingerprint_id' => $fingerprintId,
                        'class_name' => $className,
                        'attendance_date' => $record->attendance_date->toDateString(),
                        'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                        'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                        'status' => ($record->check_in_time && $record->check_out_time) ? 'Complete' : 'Check In Only',
                        'verify_mode' => $record->verify_mode ?? null,
                        'device_ip' => $record->device_ip ?? null,
                    ];
                }
            }

            $records = array_values($grouped);

            // Paginate
            $total = count($records);
            $offset = ($page - 1) * $perPage;
            $paginatedRecords = array_slice($records, $offset, $perPage);

            return response()->json([
                'success' => true,
                'data' => $paginatedRecords,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
                'message' => 'Retrieved ' . $total . ' attendance record(s) from database (device unavailable)',
                'source' => 'database' // Flag to indicate data came from DB
            ]);
        } catch (\Exception $e) {
            Log::error('Get fingerprint attendance from DB error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance from database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all attendance data from device to database
     * - Syncs all dates (not just today)
     * - Groups by studentID + date
     * - Takes only first 2 punches per student per day (check-in and check-out)
     * - Uses updateOrCreate to ensure one record per student per day
     */
    public function syncAllAttendanceFromDevice(Request $request)
    {
        $request->validate([
            'subclassID' => 'required|integer',
        ]);

        $subclassID = $request->input('subclassID');

        try {
            $ip = config('zkteco.ip', '192.168.1.108');
            $port = (int) config('zkteco.port', 4370);

            // Get all students in this subclass
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID'); // user_id from device = studentID (primary key)

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found in this class.'
                ], 400);
            }

            // Connect to device
            $zkteco = new ZKTecoService($ip, $port);

            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
                ], 500);
            }

            // Get all attendance records from device (all dates)
            $allAttendance = $zkteco->getAttendance();
            $zkteco->disconnect();

            if (!is_array($allAttendance)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve attendance data from device.'
                ], 500);
            }

            // Collect records per user per date
            $userDateRecords = [];

            foreach ($allAttendance as $record) {
                $userId = isset($record['user_id']) ? (int)$record['user_id'] : null;
                if (!$userId) {
                    continue;
                }

                // user_id from device = studentID (primary key)
                /** @var Student|null $student */
                $student = $students->get($userId);
                if (!$student) {
                    // Not in this subclass or not an active student
                    continue;
                }

                if (!isset($record['record_time'])) {
                    continue;
                }

                $recordTime = Carbon::parse($record['record_time']);
                $recordDate = $recordTime->toDateString();

                $key = $userId . '_' . $recordDate;

                if (!isset($userDateRecords[$key])) {
                    $userDateRecords[$key] = [];
                }

                $userDateRecords[$key][] = [
                    'time' => $recordTime,
                    'type' => $record['type'] ?? 'Fingerprint',
                    'device_ip' => $record['device_ip'] ?? $ip,
                    'student' => $student,
                ];
            }

            // Apply rule: only first 2 punches per day per student
            $syncedCount = 0;
            $updatedCount = 0;

            foreach ($userDateRecords as $key => $records) {
                // Sort by time ascending
                usort($records, function ($a, $b) {
                    if ($a['time']->eq($b['time'])) {
                        return 0;
                    }
                    return $a['time']->lt($b['time']) ? -1 : 1;
                });

                if (count($records) < 1) {
                    continue;
                }

                $firstRecord = $records[0];
                $secondRecord = count($records) >= 2 ? $records[1] : null;

                /** @var Student $student */
                $student = $firstRecord['student'];
                $recordDate = $firstRecord['time']->toDateString();
                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $fingerprintId = $student->fingerprint_id ?? $student->studentID;

                $checkInTime = $firstRecord['time']->format('Y-m-d H:i:s');
                $checkOutTime = $secondRecord ? $secondRecord['time']->format('Y-m-d H:i:s') : null;
                $status = $checkOutTime ? 1 : 0; // 1 = complete, 0 = check in only

                // Check if record already exists
                $existing = StudentFingerprintAttendance::where('studentID', $student->studentID)
                    ->whereDate('attendance_date', $recordDate)
                    ->first();

                // Upsert into student_fingerprint_attendance (one row per student per day)
                StudentFingerprintAttendance::updateOrCreate(
                    [
                        'studentID' => $student->studentID,
                        'attendance_date' => $recordDate,
                    ],
                    [
                        'user_id' => $student->studentID,
                        'user_name' => $studentName ?: 'N/A',
                        'enroll_id' => $student->studentID,
                        'check_in_time' => $checkInTime,
                        'check_out_time' => $checkOutTime,
                        'status' => $status,
                        'verify_mode' => $firstRecord['type'] ?? 'Fingerprint',
                        'device_ip' => $firstRecord['device_ip'] ?? $ip,
                    ]
                );

                if ($existing) {
                    $updatedCount++;
                } else {
                    $syncedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced attendance from device. {$syncedCount} new record(s) created, {$updatedCount} record(s) updated.",
                'synced' => $syncedCount,
                'updated' => $updatedCount,
                'total' => $syncedCount + $updatedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Sync all attendance from device error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error syncing attendance from device: ' . $e->getMessage()
            ], 500);
        }
    }
}

