<?php

namespace App\Services;

use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use CodingLibs\ZktecoPhp\Libs\Services\Util;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class ZKTecoService
{
    protected ?ZKTeco $client = null;
    private string $ip;
    private int $port;
    private $password; // Can be int or string, will be converted to int

    public function __construct($ip = null, $port = null, $password = null)
    {
        $this->ip = $ip ?? config('zkteco.ip', env('ZKTECO_IP', '192.168.100.127'));
        $this->port = (int) ($port ?? config('zkteco.port', env('ZKTECO_PORT', 4370)));
        // Default password is 0 (no password/Comm Key) if not specified
        $configPassword = config('zkteco.password', env('ZKTECO_PASSWORD', 0));
        $this->password = $password ?? $configPassword;
        
        // Ensure password is numeric and convert to appropriate type
        if ($this->password !== null) {
            if (is_numeric($this->password)) {
                $this->password = (int)$this->password;
            } else {
                Log::warning("ZKTeco password is not numeric: {$this->password}, using 0 as default");
                $this->password = 0;
            }
        } else {
            $this->password = 0; // Default to 0 (no password)
        }
        
        // Log connection parameters
        Log::info("ZKTecoService initialized: IP={$this->ip}, Port={$this->port}, Comm Key={$this->password}");
    }

    /**
     * Get or create ZKTeco client
     */
    private function getClient(): ZKTeco
    {
        if ($this->client === null) {
            // Convert password to integer (library expects int, default is 0)
            $password = 0;
            if ($this->password !== null) {
                if (is_numeric($this->password)) {
                    $password = (int)$this->password;
                } else {
                    Log::warning("ZKTeco password is not numeric: {$this->password}. Using 0 (default).");
                    $password = 0;
                }
            }
            
            Log::info("Creating ZKTeco client: IP={$this->ip}, Port={$this->port}, Password={$password} (Comm Key)");
            
            $this->client = new ZKTeco(
                ip: $this->ip,
                port: $this->port,
                shouldPing: true,
                timeout: 30, // Increased from 25 to 30 seconds for better reliability
                password: $password
            );
        }
        return $this->client;
    }

    /**
     * Connect to the device
     */
    public function connect(): bool
    {
        try {
            Log::info("Attempting to connect to ZKTeco device at {$this->ip}:{$this->port}");
            
            $client = $this->getClient();
            
            // Try to connect with timeout awareness
            $startTime = microtime(true);
            $result = $client->connect();
            $connectionTime = microtime(true) - $startTime;
            
            if ($result) {
                Log::info("Successfully connected to ZKTeco device at {$this->ip}:{$this->port} (took " . round($connectionTime, 2) . " seconds)");
                
                // CRITICAL: Verify authentication by testing a command that requires auth
                // If Comm Key is wrong, connection might succeed but commands will fail
                try {
                    // Try to get device name - this requires authentication
                    $deviceName = $client->deviceName();
                    Log::info("Authentication verified - Device name: {$deviceName}");
                } catch (\Throwable $e) {
                    Log::error("Authentication check failed: " . $e->getMessage());
                    Log::error("This usually means Comm Key is wrong. Current Comm Key: {$this->password}");
                    Log::error("Please check device settings (System → Communication → Comm Key) and update ZKTECO_PASSWORD in .env");
                    // Don't throw here - let the actual command fail with better error
                }
            } else {
                Log::error("Failed to connect to ZKTeco device at {$this->ip}:{$this->port}");
                Log::error("Connection attempt took " . round($connectionTime, 2) . " seconds before failing");
                Log::error("Possible causes:");
                Log::error("1. Device is not powered on or not on the network");
                Log::error("2. IP address is incorrect (current: {$this->ip})");
                Log::error("3. Port is incorrect (current: {$this->port})");
                Log::error("4. Firewall is blocking the connection");
                Log::error("5. Device is busy with another operation");
            }
            
            return $result;
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            Log::error('ZKTeco connect error: ' . $errorMessage);
            Log::error('Connection attempt to: ' . $this->ip . ':' . $this->port);
            Log::error('Error class: ' . get_class($e));
            
            // Provide more helpful error message
            if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'timed out') !== false) {
                throw new Exception("Connection timeout: Device at {$this->ip}:{$this->port} did not respond. Check if device is powered on and network is working.");
            } elseif (strpos($errorMessage, 'refused') !== false || strpos($errorMessage, 'No route') !== false) {
                throw new Exception("Cannot reach device at {$this->ip}:{$this->port}. Check IP address and network connectivity.");
            } else {
                throw new Exception("Connection failed: {$errorMessage}. Check device IP ({$this->ip}), port ({$this->port}), and network connectivity.");
            }
        }
    }

    /**
     * Disconnect from the device
     */
    public function disconnect(): void
    {
        try {
            if ($this->client) {
                $this->client->disconnect();
            }
        } catch (\Throwable $e) {
            Log::error('ZKTeco disconnect error: ' . $e->getMessage());
        }
    }

    /**
     * Diagnostic function to check device communication
     */
    /**
     * Comprehensive diagnostic to identify why setUser is failing
     */
    public function diagnoseSetUserIssue(): array
    {
        $diagnostics = [
            'connection' => false,
            'authentication' => false,
            'can_read_users' => false,
            'can_enable_device' => false,
            'device_info' => [],
            'raw_setuser_test' => null,
            'errors' => []
        ];
        
        try {
            // Test 1: Connection
            if ($this->connect()) {
                $diagnostics['connection'] = true;
                $client = $this->getClient();
                
                // Test 2: Authentication - Can we read from device?
                try {
                    $users = $client->getUsers();
                    $diagnostics['can_read_users'] = true;
                    $diagnostics['authentication'] = true;
                    $diagnostics['device_info']['users_count'] = count($users);
                    $diagnostics['device_info']['users'] = $users;
                } catch (\Throwable $e) {
                    $diagnostics['errors'][] = 'Cannot read users: ' . $e->getMessage();
                    $diagnostics['authentication'] = false;
                }
                
                // Test 3: Get device info
                try {
                    $diagnostics['device_info']['name'] = $client->deviceName();
                    $diagnostics['device_info']['serial'] = $client->serialNumber();
                    $diagnostics['device_info']['version'] = $client->version();
                } catch (\Throwable $e) {
                    $diagnostics['errors'][] = 'Cannot get device info: ' . $e->getMessage();
                }
                
                // Test 4: Enable device
                try {
                    $enableResult = $client->enableDevice();
                    $diagnostics['can_enable_device'] = true;
                    $diagnostics['device_info']['enable_result'] = $enableResult;
                } catch (\Throwable $e) {
                    $diagnostics['errors'][] = 'Cannot enable device: ' . $e->getMessage();
                }
                
                // Test 5: Try a test setUser with minimal data
                // Use UID=9999 (within valid range 1-65535) to avoid conflicts
                try {
                    $testUid = 9999;
                    $testUserid = '9999';
                    
                    // Check if test user already exists
                    $existingTestUsers = $client->getUsers();
                    $testUserExists = false;
                    foreach ($existingTestUsers as $key => $user) {
                        if ($key == $testUserid || (isset($user['uid']) && $user['uid'] == $testUid)) {
                            $testUserExists = true;
                            // Remove it first
                            try {
                                $client->removeUser($testUid);
                                usleep(300000); // Wait 300ms
                            } catch (\Throwable $e) {
                                // Ignore
                            }
                            break;
                        }
                    }
                    
                    Log::info("Testing setUser with UID={$testUid}, UserID='{$testUserid}'");
                    $testResult = $client->setUser($testUid, $testUserid, 'TEST', '', 0, 0);
                    
                    // Get the actual response from device
                    $reflection = new \ReflectionClass($client);
                    $dataRecvProperty = $reflection->getProperty('_data_recv');
                    $dataRecvProperty->setAccessible(true);
                    $testDataRecv = $dataRecvProperty->getValue($client);
                    
                    // Check actual response code - try multiple parsing methods
                    $testResponseCode = null;
                    $testResponseHex = '';
                    $responseAnalysis = [];
                    
                    if (!empty($testDataRecv) && strlen($testDataRecv) >= 8) {
                        $testResponseHex = bin2hex(substr($testDataRecv, 0, 16));
                        $testResponseCode = Util::checkValid($testDataRecv);
                        
                        // Try to manually parse response code from different byte positions
                        // Some devices use different response formats
                        for ($offset = 0; $offset <= 6 && $offset + 2 <= strlen($testDataRecv); $offset += 2) {
                            // Little-endian
                            $bytes = @unpack('v', substr($testDataRecv, $offset, 2));
                            $leValue = $bytes[1] ?? null;
                            // Big-endian
                            $bytes2 = @unpack('n', substr($testDataRecv, $offset, 2));
                            $beValue = $bytes2[1] ?? null;
                            
                            if ($leValue == 2000 || $beValue == 2000) {
                                $responseAnalysis[] = "✓ Found 2000 (CMD_ACK_OK) at offset {$offset}";
                            } elseif ($leValue == 2001 || $beValue == 2001) {
                                $responseAnalysis[] = "✗ Found 2001 (CMD_ACK_ERROR) at offset {$offset}";
                            } elseif ($leValue == 2005 || $beValue == 2005) {
                                $responseAnalysis[] = "✗ Found 2005 (CMD_ACK_UNAUTH) at offset {$offset}";
                            } elseif ($leValue == 2007 || $beValue == 2007) {
                                $responseAnalysis[] = "⚠ Found 2007 (UNKNOWN - possibly device-specific code) at offset {$offset}";
                            }
                            
                            if ($offset == 0) {
                                $responseAnalysis[] = "Offset 0: LE={$leValue} (0x" . dechex($leValue) . "), BE={$beValue} (0x" . dechex($beValue) . ")";
                            } elseif ($offset <= 4) {
                                $responseAnalysis[] = "Offset {$offset}: LE={$leValue}, BE={$beValue}";
                            }
                        }
                        
                        // Check if 2007 might be a success code for this device model
                        if ($testResponseCode === false && ($leValue == 2007 || $beValue == 2007)) {
                            $responseAnalysis[] = "NOTE: Device returned 2007 instead of 2000. This might be a device-specific success code.";
                            $responseAnalysis[] = "Device: UF200-S, Firmware: Ver 6.60 - may use non-standard response codes.";
                        }
                    }
                    
                    $diagnostics['raw_setuser_test'] = [
                        'result' => $testResult,
                        'success' => $testResult !== false,
                        'response_code' => $testResponseCode,
                        'response_code_name' => $testResponseCode === Util::CMD_ACK_OK ? 'CMD_ACK_OK (2000)' : 
                                               ($testResponseCode === Util::CMD_ACK_ERROR ? 'CMD_ACK_ERROR (2001)' : 
                                               ($testResponseCode === Util::CMD_ACK_UNAUTH ? 'CMD_ACK_UNAUTH (2005)' : 'Unknown/Unrecognized Format')),
                        'response_hex' => $testResponseHex,
                        'response_analysis' => $responseAnalysis,
                        'response_length' => strlen($testDataRecv),
                        'uid_used' => $testUid,
                        'userid_used' => $testUserid
                    ];
                    
                    // Check if test user was added
                    usleep(2000000); // 2 seconds - give device more time
                    $usersAfter = $client->getUsers();
                    $testUserFound = false;
                    foreach ($usersAfter as $key => $user) {
                        if ($key == $testUserid || (isset($user['uid']) && $user['uid'] == $testUid)) {
                            $testUserFound = true;
                            break;
                        }
                    }
                    $diagnostics['raw_setuser_test']['user_added'] = $testUserFound;
                    $diagnostics['raw_setuser_test']['users_before'] = count($existingTestUsers);
                    $diagnostics['raw_setuser_test']['users_after'] = count($usersAfter);
                    $diagnostics['raw_setuser_test']['user_count_increased'] = count($usersAfter) > count($existingTestUsers);
                    
                    // Remove test user if added
                    if ($testUserFound) {
                        try {
                            $client->removeUser($testUid);
                            Log::info("Test user removed successfully");
                        } catch (\Throwable $e) {
                            Log::warning("Could not remove test user: " . $e->getMessage());
                        }
                    }
                } catch (\Throwable $e) {
                    $diagnostics['raw_setuser_test'] = [
                        'error' => $e->getMessage(),
                        'success' => false,
                        'error_type' => get_class($e)
                    ];
                    Log::error("setUser test failed: " . $e->getMessage());
                }
                
            } else {
                $diagnostics['errors'][] = 'Failed to connect to device';
            }
        } catch (\Throwable $e) {
            $diagnostics['errors'][] = 'Diagnostic error: ' . $e->getMessage();
        }
        
        return $diagnostics;
    }

    public function diagnoseConnection(): array
    {
        $diagnostics = [
            'connection' => false,
            'device_enabled' => false,
            'can_get_users' => false,
            'users_count' => 0,
            'can_get_time' => false,
            'device_time' => null,
            'errors' => []
        ];
        
        try {
            // Test 1: Connection
            if ($this->connect()) {
                $diagnostics['connection'] = true;
                
                $client = $this->getClient();
                
                // Test 2: Enable device
                try {
                    $client->enableDevice();
                    $diagnostics['device_enabled'] = true;
                } catch (\Throwable $e) {
                    $diagnostics['errors'][] = 'Enable device failed: ' . $e->getMessage();
                }
                
                // Test 3: Get users
                try {
                    $users = $client->getUsers();
                    $diagnostics['can_get_users'] = true;
                    $diagnostics['users_count'] = count($users);
                } catch (\Throwable $e) {
                    $diagnostics['errors'][] = 'Get users failed: ' . $e->getMessage();
                }
                
                // Test 4: Get time
                try {
                    $time = $client->getTime();
                    $diagnostics['can_get_time'] = true;
                    $diagnostics['device_time'] = $time;
                } catch (\Throwable $e) {
                    $diagnostics['errors'][] = 'Get time failed: ' . $e->getMessage();
                }
                
                $this->disconnect();
            } else {
                $diagnostics['errors'][] = 'Failed to connect to device';
            }
        } catch (\Throwable $e) {
            $diagnostics['errors'][] = 'Diagnostic error: ' . $e->getMessage();
        }
        
        return $diagnostics;
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        try {
            $connected = $this->connect();
            
            if (!$connected) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to device'
                ];
            }

            $deviceInfo = null;
            $time = null;
            $errors = [];

            // Try to get device info
            try {
                $deviceInfo = $this->getDeviceInfo();
            } catch (Exception $e) {
                $errors[] = 'Device Info: ' . $e->getMessage();
            }

            // Try to get time
            try {
                $time = $this->getTime();
            } catch (Exception $e) {
                $errors[] = 'Get Time: ' . $e->getMessage();
            }

            $this->disconnect();

            if ($deviceInfo || $time) {
                return [
                    'success' => true,
                    'device_info' => $deviceInfo,
                    'device_time' => $time,
                    'message' => 'Device connection successful!',
                    'warnings' => $errors
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Connected but failed to retrieve data. ' . implode(' | ', $errors)
                ];
            }
        } catch (Exception $e) {
            $this->disconnect();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get device information
     */
    public function getDeviceInfo(): ?array
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Not connected to device');
            }

            $client = $this->getClient();
            $info = [];
            
            // Get device information using library methods
            try {
                $info['device_name'] = $client->deviceName() ?: 'Unknown';
            } catch (\Throwable $e) {
                $info['device_name'] = 'Unknown';
            }
            
            try {
                $info['device_id'] = $client->deviceId() ?: 'Unknown';
            } catch (\Throwable $e) {
                $info['device_id'] = 'Unknown';
            }
            
            try {
                $info['serial_number'] = $client->serialNumber() ?: 'Unknown';
            } catch (\Throwable $e) {
                $info['serial_number'] = 'Unknown';
            }
            
            try {
                $info['version'] = $client->version() ?: 'Unknown';
            } catch (\Throwable $e) {
                $info['version'] = 'Unknown';
            }
            
            try {
                $info['platform'] = $client->platform() ?: 'Unknown';
            } catch (\Throwable $e) {
                $info['platform'] = 'Unknown';
            }

            $info['ip'] = $this->ip;
            $info['port'] = $this->port;
            $info['comm_key'] = $this->password ?? 0;

            return $info;
        } catch (Exception $e) {
            throw new Exception('Failed to get device info: ' . $e->getMessage());
        }
    }

    /**
     * Get device time
     */
    public function getTime(): ?string
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Not connected to device');
            }

            $client = $this->getClient();
            
            if (method_exists($client, 'getTime')) {
                return $client->getTime();
            }
            
            return date('Y-m-d H:i:s'); // Fallback to current time
        } catch (Exception $e) {
            throw new Exception('Failed to get time: ' . $e->getMessage());
        }
    }

    /**
     * Get attendance logs
     */
    public function getAttendances(): array
    {
        try {
            if (!$this->connect()) {
                Log::warning('Cannot get attendances: not connected to device');
                return [];
            }

            $client = $this->getClient();
            
            if (method_exists($client, 'getAttendances')) {
                $attendances = $client->getAttendances() ?? [];
                
                // Log what we actually got from device
                Log::info('Raw attendances from device: ' . count($attendances) . ' records');
                if (count($attendances) > 0) {
                    Log::info('First attendance record from device: ' . json_encode($attendances[0]));
                }
                
                return $attendances;
            }
            
            Log::warning('getAttendances method does not exist on ZKTeco client');
            return [];
        } catch (\Throwable $e) {
            Log::error('ZKTeco getAttendances error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get users from device
     */
    public function getUsers(): array
    {
        try {
            if (!$this->connect()) {
                Log::warning('Cannot get users: not connected to device');
                return [];
            }

            $client = $this->getClient();
            
            // Enable device to ensure it's ready
            try {
                $client->enableDevice();
            } catch (\Throwable $e) {
                Log::warning('Could not enable device when getting users: ' . $e->getMessage());
            }
            
            if (method_exists($client, 'getUsers')) {
                $users = $client->getUsers();
                
                // Handle different return types
                if ($users === null || $users === false) {
                    Log::warning('getUsers returned null or false');
                    return [];
                }
                
                if (!is_array($users)) {
                    Log::warning('getUsers returned non-array: ' . gettype($users));
                    return [];
                }
                
                // Log for debugging
                Log::info('Retrieved ' . count($users) . ' users from device');
                
                return $users;
            }
            
            Log::warning('getUsers method does not exist on ZKTeco client');
            return [];
        } catch (\Throwable $e) {
            Log::error('ZKTeco getUsers error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Verify user exists on device
     */
    public function verifyUserOnDevice($uid, $userid = null): bool
    {
        try {
            $users = $this->getUsers();
            
            foreach ($users as $deviceUser) {
                if (isset($deviceUser['uid']) && $deviceUser['uid'] == (int)$uid) {
                    return true;
                }
                if ($userid !== null && isset($deviceUser['user_id']) && $deviceUser['user_id'] == (int)$userid) {
                    return true;
                }
            }
            
            return false;
        } catch (\Throwable $e) {
            Log::error('ZKTeco verifyUserOnDevice error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Register user to device
     */
    public function registerUser($uid, $userid, $name, $password = '', $role = 0, $cardno = 0): bool
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Not connected to device');
            }

            $client = $this->getClient();
            
            // Track if device returned 2007 (UF200-S firmware 6.60 non-standard response code)
            $responseCode2007 = false;
            
            // STEP 1: Verify authentication by testing if we can read from device
            // This proves Comm Key is correct
            Log::info("=== STEP 1: Verifying Authentication ===");
            $authVerified = false;
            $authError = null;
            $canGetUsers = false;
            
            try {
                // Test: Get users (requires authentication)
                $testUsers = $client->getUsers();
                $canGetUsers = true;
                Log::info("✓ Authentication VERIFIED - Can get users (found " . count($testUsers) . ")");
                Log::info("✓ Comm Key {$this->password} is CORRECT");
                $authVerified = true;
            } catch (\Throwable $e) {
                $authError = $e->getMessage();
                Log::error("✗ Authentication FAILED - Cannot get users: {$authError}");
                Log::error("✗ Comm Key {$this->password} might be WRONG");
                
                // Try device name as backup test
                try {
                    $deviceName = $client->deviceName();
                    Log::info("BUT can get device name: {$deviceName} - authentication might be partial");
                    $authVerified = true; // Partial success
                } catch (\Throwable $e2) {
                    Log::error("Cannot get device name either: " . $e2->getMessage());
                }
            }
            
            // If authentication completely failed, throw error
            if (!$authVerified) {
                $errorMsg = "Device authentication COMPLETELY FAILED. Cannot read any data from device.";
                $errorMsg .= " Comm Key in system: {$this->password}";
                $errorMsg .= " Error: {$authError}";
                $errorMsg .= " SOLUTION: Check device Comm Key (System → Communication → Comm Key)";
                $errorMsg .= " and ensure ZKTECO_PASSWORD in .env matches exactly.";
                throw new Exception($errorMsg);
            }
            
            // STEP 2: Get users count BEFORE registration
            Log::info("=== STEP 2: Getting initial user count ===");
            $usersBefore = [];
            try {
                $usersBefore = $client->getUsers();
                Log::info("Pre-registration: Device has " . count($usersBefore) . " users");
            } catch (\Throwable $e) {
                Log::warning('Could not get users before registration: ' . $e->getMessage());
                $usersBefore = [];
            }
            $userCountBefore = count($usersBefore);
            
            // STEP 3: Enable device (CRITICAL - many devices require this)
            Log::info("=== STEP 3: Enabling device ===");
            try {
                Log::info("Enabling device before registration...");
                $enableResult = $client->enableDevice();
                Log::info("Device enable result: " . var_export($enableResult, true));
                
                // Wait longer - device needs time to be ready
                usleep(1000000); // 1 second
                Log::info("Device enabled, waited 1 second for device to be ready...");
            } catch (\Throwable $e) {
                Log::error('WARNING: Could not enable device: ' . $e->getMessage());
                Log::error('Some devices require enableDevice() before setUser. This might cause registration to fail.');
                // Continue anyway - some devices might not need this
            }
            
            // Ensure userid is numeric string (required by some devices)
            $userid = (string) $userid;
            if (!is_numeric($userid)) {
                throw new Exception('Enroll ID must be numeric');
            }
            
            // Ensure uid is within valid range (1-65535)
            $uid = (int) $uid;
            if ($uid < 1 || $uid > 65535) {
                throw new Exception('UID must be between 1 and 65535');
            }
            
            // Truncate name to 24 characters (device limit)
            $name = substr(trim($name), 0, 24);
            
            // Check if user already exists on device before attempting registration
            $existingUsers = $client->getUsers();
            $userExists = false;
            foreach ($existingUsers as $key => $deviceUser) {
                if ((string)$key === (string)$userid || 
                    (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid) ||
                    (isset($deviceUser['user_id']) && (string)$deviceUser['user_id'] === (string)$userid)) {
                    $userExists = true;
                    Log::warning("User with UID={$uid} or UserID={$userid} already exists on device. Array key: '{$key}'");
                    break;
                }
            }
            
            if ($userExists) {
                throw new Exception("User with Enroll ID '{$userid}' (UID: {$uid}) already exists on device. Please use a different Enroll ID or remove the existing user first.");
            }
            
            // Set user on device
            // Parameters: uid (internal ID), userid (PIN/enroll_id), name, password, role, cardno
            Log::info("Attempting to register user: UID={$uid}, UserID={$userid}, Name={$name}, Password='{$password}', Role={$role}, CardNo={$cardno}");
            Log::info("Device IP: {$this->ip}, Port: {$this->port}, Comm Key: {$this->password}");
            Log::info("Current users on device before registration: " . count($existingUsers));
            
            // Test device connection and authentication before attempting registration
            try {
                $deviceInfo = $client->deviceName();
                Log::info("Device name: {$deviceInfo}");
            } catch (\Throwable $e) {
                Log::warning("Could not get device name before registration: " . $e->getMessage());
            }
            
            // Some devices need a fresh connection for each user registration
            // Disconnect and reconnect to ensure clean state
            try {
                $client->disconnect();
                usleep(100000); // 100ms delay
                $this->connect();
                $client = $this->getClient();
                Log::info("Reconnected to device before registration");
            } catch (\Throwable $e) {
                Log::warning("Could not reconnect before registration: " . $e->getMessage());
            }
            
            // STEP 4: Call setUser command
            Log::info("=== STEP 4: Calling setUser command ===");
            Log::info("Parameters: UID={$uid}, UserID='{$userid}', Name='{$name}', Password='{$password}', Role={$role}, CardNo={$cardno}");
            Log::info("Comm Key: {$this->password}, Device ID: 1");
            
            $result = $client->setUser($uid, $userid, $name, $password, $role, $cardno);
            
            // Access the internal _data_recv to check actual response
            $reflection = new \ReflectionClass($client);
            $dataRecvProperty = $reflection->getProperty('_data_recv');
            $dataRecvProperty->setAccessible(true);
            $dataRecv = $dataRecvProperty->getValue($client);
            
            Log::info("setUser result: " . var_export($result, true));
            Log::info("Response data length: " . (is_string($dataRecv) ? strlen($dataRecv) : 'N/A'));
            
            // CRITICAL: Check the ACTUAL response code from device
            // The library returns response data, not boolean - we need to check the response code
            $actualResponseCode = null;
            if (!empty($dataRecv) && strlen($dataRecv) >= 8) {
                $actualResponseCode = Util::checkValid($dataRecv);
                Log::info("Device response code from setUser: {$actualResponseCode} (2000=OK, 2001=Error, 2005=Unauth)");
                
                if ($actualResponseCode === Util::CMD_ACK_OK) {
                    Log::info("✓ Device returned CMD_ACK_OK (2000) - command accepted by device");
                } elseif ($actualResponseCode === Util::CMD_ACK_ERROR) {
                    Log::error("✗ Device returned CMD_ACK_ERROR (2001) - device rejected the command");
                    throw new Exception('Device returned CMD_ACK_ERROR (2001) - command rejected. Possible reasons: Invalid user data, UID already exists, device memory full, or device firmware issue.');
                } elseif ($actualResponseCode === Util::CMD_ACK_UNAUTH) {
                    Log::error("✗ Device returned CMD_ACK_UNAUTH (2005) - authentication failed");
                    throw new Exception('Device returned CMD_ACK_UNAUTH (2005) - authentication required. Check Comm Key.');
                } else {
                    Log::warning("Device returned unexpected response code: {$actualResponseCode}");
                    // Log raw response for analysis
                    $responseHex = bin2hex(substr($dataRecv, 0, 16));
                    Log::warning("Raw response hex: {$responseHex}");
                    
                    // SPECIAL CASE: UF200-S firmware 6.60 returns 2007 instead of 2000
                    // This might be a device-specific response code
                    // We'll proceed with verification to see if user was actually added
                    if ($actualResponseCode === false && strlen($dataRecv) >= 8) {
                        // Check if response code is 2007 (device-specific code)
                        $u = unpack('H2h1/H2h2', substr($dataRecv, 0, 8));
                        $responseCodeValue = hexdec($u['h2'] . $u['h1']);
                        if ($responseCodeValue == 2007) {
                            $responseCode2007 = true; // Set flag for later error messages
                            Log::warning("Device returned 2007 (UF200-S firmware 6.60 specific code). Proceeding with verification...");
                            Log::warning("NOTE: This device may use non-standard response codes. We'll verify if user was actually added.");
                        }
                    }
                }
            }
            
            // CRITICAL: Check if result is actually false (command failed)
            if ($result === false) {
                Log::error("setUser returned FALSE - command failed!");
                Log::error("This means the device rejected the setUser command.");
                Log::error("Comm Key: {$this->password}, Device ID: 1");
                
                // Check if we can still get users (proves authentication works)
                try {
                    $testUsers = $client->getUsers();
                    Log::error("BUT we CAN get users (found " . count($testUsers) . "), so authentication IS working!");
                    Log::error("This means the problem is NOT Comm Key - it's something else with setUser command.");
                } catch (\Throwable $e) {
                    Log::error("Cannot get users either: " . $e->getMessage());
                    Log::error("This suggests authentication might be failing.");
                }
                
                throw new Exception("setUser command returned false. The device rejected the command. Comm Key is 0 (correct). Check: 1) Device is enabled, 2) User data format is valid, 3) Device is not in sleep mode, 4) Device firmware supports this command.");
            }
            
            // Wait a moment for device to process
            usleep(500000); // 500ms
            
            // IMMEDIATELY check if user count increased
            $usersAfter = $client->getUsers();
            $userCountAfter = count($usersAfter);
            Log::info("Users on device AFTER registration: {$userCountAfter} (was {$userCountBefore})");
            
            // Check if user is in the list
            $userFoundImmediately = false;
            foreach ($usersAfter as $key => $deviceUser) {
                if ((string)$key === (string)$userid || 
                    (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid) ||
                    (isset($deviceUser['user_id']) && ((string)$deviceUser['user_id'] === (string)$userid))) {
                    $userFoundImmediately = true;
                    Log::info("✓ User found immediately after registration!");
                    break;
                }
            }
            
            // If user found immediately, return success early
            if ($userFoundImmediately) {
                Log::info("✓ User found immediately after registration - registration successful!");
                return true;
            }
            
            // If user count didn't increase, registration likely failed
            if ($userCountAfter <= $userCountBefore) {
                Log::error("User count did not increase and user not found. Registration likely failed.");
                Log::error("Before: {$userCountBefore} users, After: {$userCountAfter} users");
                
                // Check if device returned 2007 (UF200-S firmware 6.60 specific code)
                $responseCodeValue = null;
                if ($actualResponseCode === false && !empty($dataRecv) && strlen($dataRecv) >= 8) {
                    $u = unpack('H2h1/H2h2', substr($dataRecv, 0, 8));
                    $responseCodeValue = hexdec($u['h2'] . $u['h1']);
                }
                
                // If device returned CMD_ACK_OK but user wasn't added, this is a device firmware issue
                if ($actualResponseCode === Util::CMD_ACK_OK) {
                    Log::error("CRITICAL: Device returned CMD_ACK_OK but user was NOT added!");
                    Log::error("This indicates a device firmware bug or device-specific issue.");
                    Log::error("Device: UF200-S, Firmware: Ver 6.60 Sep 27 2019");
                    Log::error("Possible causes: 1) Firmware bug, 2) Device needs different command format, 3) Device needs unlock mode");
                } elseif ($responseCodeValue == 2007) {
                    Log::error("CRITICAL: Device returned response code 2007 (UF200-S firmware 6.60 specific) but user was NOT added!");
                    Log::error("This confirms a protocol/firmware compatibility issue.");
                    Log::error("Device: UF200-S, Firmware: Ver 6.60 Sep 27 2019");
                    Log::error("The device is responding but not actually adding users. This is a known issue with this firmware version.");
                    Log::error("SOLUTION: 1) Update device firmware, 2) Use ZKTeco's official SDK, 3) Contact ZKTeco support");
                }
                // Continue to check response code for more details, but we'll fail in final check
            }
            
            // Check response code
            $responseCode = null;
            $gotAckOk = false;
            $responseHex = '';
            // $responseCode2007 is already declared at the start of the method
            
            if (!empty($dataRecv) && strlen($dataRecv) >= 8) {
                // Log raw response for debugging
                $responseHex = bin2hex(substr($dataRecv, 0, 16));
                Log::info("Raw response (first 16 bytes hex): {$responseHex}");
                
                $responseCode = Util::checkValid($dataRecv);
                Log::info("Device response code: {$responseCode} (2000=OK, 2001=Error, 2005=Unauth)");
                
                if ($responseCode === Util::CMD_ACK_OK) {
                    Log::info("✓ Device returned CMD_ACK_OK (2000) - command successful");
                    $gotAckOk = true;
                } elseif ($responseCode === Util::CMD_ACK_ERROR) {
                    throw new Exception('Device returned CMD_ACK_ERROR (2001) - command failed. Possible reasons: Invalid user data, UID already exists, device memory full, or device rejected the command. Check device logs.');
                } elseif ($responseCode === Util::CMD_ACK_UNAUTH) {
                    throw new Exception('Device returned CMD_ACK_UNAUTH (2005) - authentication required. Your device requires a password/Comm Key. Set ZKTECO_PASSWORD in .env file (e.g., ZKTECO_PASSWORD=12345) and restart the server.');
                } elseif ($responseCode === false) {
                    Log::error("checkValid returned false - response format invalid");
                    Log::error("Response length: " . strlen($dataRecv));
                    Log::error("Response hex (first 32 bytes): {$responseHex}");
                    
                    // Try to extract command from response manually using different methods
                    // Check multiple byte positions as response format may vary
                    if (strlen($dataRecv) >= 8) {
                        $foundResponse = false;
                        
                        // Try different byte offsets (0, 2, 4, 6)
                        // $responseCode2007 is already declared above
                        for ($offset = 0; $offset <= 6 && $offset + 2 <= strlen($dataRecv); $offset += 2) {
                            // Method 1: Try unpack as little-endian short (v)
                            $cmdBytes = @unpack('v', substr($dataRecv, $offset, 2));
                            $cmdValue = $cmdBytes[1] ?? null;
                            
                            // Method 2: Try big-endian
                            $cmdBytes2 = @unpack('n', substr($dataRecv, $offset, 2));
                            $cmdValue2 = $cmdBytes2[1] ?? null;
                            
                            Log::info("Offset {$offset} - Little-endian: {$cmdValue}, Big-endian: {$cmdValue2}");
                            
                            // Check for 2007 (UF200-S firmware 6.60 specific code)
                            if ($cmdValue == 2007 || $cmdValue2 == 2007) {
                                $responseCode2007 = true;
                                Log::error("⚠ Found response code 2007 at offset {$offset} - This is a known issue with UF200-S firmware 6.60");
                            }
                            
                            // Check if any method found a valid response code
                            if ($cmdValue == 2000 || $cmdValue2 == 2000) {
                                Log::info("✓ Found CMD_ACK_OK (2000) at offset {$offset}");
                                $gotAckOk = true;
                                $responseCode = Util::CMD_ACK_OK;
                                $foundResponse = true;
                                break;
                            } elseif ($cmdValue == 2001 || $cmdValue2 == 2001) {
                                Log::error("Found CMD_ACK_ERROR (2001) at offset {$offset}");
                                throw new Exception('Device returned error (2001). Command rejected. Possible reasons: Invalid user data, UID already exists, or device memory full.');
                            } elseif ($cmdValue == 2005 || $cmdValue2 == 2005) {
                                Log::error("Found CMD_ACK_UNAUTH (2005) at offset {$offset}");
                                throw new Exception('Device requires authentication (2005). Your device has a Comm Key/Password set. Please set ZKTECO_PASSWORD in .env file (e.g., ZKTECO_PASSWORD=12345) and restart the server.');
                            }
                        }
                        
                        // If we found 2007, treat it as a potential success (non-standard response code)
                        // UF200-S firmware 6.60 uses 2007 instead of 2000, but may still accept the command
                        // We'll proceed to verification to check if user was actually added
                        if ($responseCode2007 && !$foundResponse) {
                            Log::warning("⚠ Device returned response code 2007 (UF200-S firmware 6.60 non-standard code)");
                            Log::warning("This device uses non-standard response codes. Proceeding to verification...");
                            Log::info("If user is found on device, registration succeeded despite non-standard response code");
                            // Don't throw error - proceed to verification to check if user was actually added
                            // Set flag to indicate we got a response (even if non-standard)
                            $gotAckOk = false; // We'll verify manually
                        }
                        
                        // If we didn't find a standard response code, log all possible interpretations
                        if (!$foundResponse && strlen($dataRecv) >= 8) {
                            // Method 3: Try reading first 2 bytes as hex
                            $hexCmd = bin2hex(substr($dataRecv, 0, 2));
                            $hexCmdValue = hexdec($hexCmd);
                            Log::info("Method 3 - First 2 bytes hex: {$hexCmd}, Decimal: {$hexCmdValue}");
                            
                            // Check for 2007 again (in case it wasn't caught in the loop)
                            if ($responseCode2007 || $hexCmdValue == 2007) {
                                Log::warning("⚠ Device returned response code 2007 (UF200-S firmware 6.60 non-standard code)");
                                Log::warning("This device uses non-standard response codes. Proceeding to verification...");
                                Log::info("If user is found on device, registration succeeded despite non-standard response code");
                                // Don't throw error - proceed to verification to check if user was actually added
                                // Set flag to indicate we got a response (even if non-standard)
                                $gotAckOk = false; // We'll verify manually
                            }
                            
                            // Log full response for analysis
                            $fullHex = bin2hex($dataRecv);
                            Log::warning("Full response hex: {$fullHex}");
                            Log::warning("Response length: " . strlen($dataRecv) . " bytes");
                            
                            // Some devices might return success in a non-standard format
                            // If we got a response and result is not false, proceed to verification
                            if ($result !== false) {
                                Log::warning("Response format is non-standard, but setUser returned non-false. Proceeding to verification.");
                            }
                        } elseif (strlen($dataRecv) >= 8) {
                            // If we got a response (even if format is unexpected), the device is communicating
                            // Some devices might use different response formats
                            Log::warning("Unexpected response format but device responded. Response length: " . strlen($dataRecv));
                            Log::warning("Full response hex (first 64 bytes): " . bin2hex(substr($dataRecv, 0, 32)));
                            
                            // If we got any response data, the device is communicating
                            // Some devices use non-standard response formats
                            // Let's proceed and let verification determine if it worked
                            if (strlen($dataRecv) >= 8) {
                                Log::info("Device responded with data (length: " . strlen($dataRecv) . "). Response format unexpected but device is communicating. Proceeding to verification.");
                                // Don't throw error - proceed to verification
                                // Set a flag that we got a response (even if format is unexpected)
                                $gotAckOk = false; // We'll verify manually
                            } else {
                                $errorMsg = 'Device response too short or empty. ';
                                $errorMsg .= 'Response length: ' . strlen($dataRecv) . '. ';
                                $errorMsg .= 'This may indicate: 1) Network issue, 2) Device not responding, 3) Firewall blocking UDP.';
                                throw new Exception($errorMsg);
                            }
                        }
                    } else {
                        // No valid response at all
                        $errorMsg = 'Device did not respond or response too short. ';
                        $errorMsg .= 'Response length: ' . strlen($dataRecv) . '. ';
                        $errorMsg .= 'Check: 1) Network connectivity, 2) Device is powered on, 3) Firewall allows UDP port 4370, 4) Device IP is correct.';
                        throw new Exception($errorMsg);
                    }
                } else {
                    Log::warning("Unknown or unexpected response code: {$responseCode}");
                    // Continue anyway - verification will catch if it failed
                }
            } else {
                // No response or invalid response
                if ($result === false) {
                    $errorMsg = 'setUser returned false and no valid response from device. ';
                    if (empty($dataRecv)) {
                        $errorMsg .= 'No data received from device. ';
                    } else {
                        $errorMsg .= 'Response too short (length: ' . strlen($dataRecv) . '). ';
                    }
                    $errorMsg .= 'Possible issues: Network timeout, device not responding, connection lost, or device is not accepting commands.';
                    throw new Exception($errorMsg);
                }
                // If result is not false, continue - might be OK
                Log::warning("No response header but setUser didn't return false - response length: " . (is_string($dataRecv) ? strlen($dataRecv) : 'N/A'));
            }
            
            // Longer delay to allow device to process user registration
            // Some devices need more time, especially after first user
            usleep(1000000); // 1 second - increased for better reliability
            Log::info("Waiting 1 second for device to process registration...");
            
            // Verify user was added by checking if user exists on device
            // Try multiple times as device might need time to update
            $userFound = false;
            $maxAttempts = 5; // Increased attempts
            $foundBy = '';
            
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                Log::info("Verification attempt {$attempt} of {$maxAttempts}");
                $users = $client->getUsers();
                
                Log::info("Retrieved " . count($users) . " users from device on attempt {$attempt}");
                
                // getUsers() returns an associative array where key is userid (string) and value is user data array
                foreach ($users as $key => $deviceUser) {
                    // Check 1: The array key is the userid (string)
                    if ((string)$key === (string)$userid || $key == $userid) {
                        $userFound = true;
                        $foundBy = "array key (userid: '{$key}')";
                        Log::info("✓ User found on device by {$foundBy} (attempt {$attempt})");
                        break 2;
                    }
                    
                    // Check 2: UID in the data array
                    if (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid) {
                        $userFound = true;
                        $foundBy = "UID ({$uid}) in data";
                        Log::info("✓ User found on device by {$foundBy} (attempt {$attempt})");
                        break 2;
                    }
                    
                    // Check 3: user_id in the data array (can be int or string)
                    if (isset($deviceUser['user_id'])) {
                        $deviceUserId = (string)$deviceUser['user_id'];
                        $searchUserId = (string)$userid;
                        
                        if ($deviceUserId === $searchUserId || (int)$deviceUserId === (int)$searchUserId) {
                            $userFound = true;
                            $foundBy = "user_id ({$deviceUserId}) in data";
                            Log::info("✓ User found on device by {$foundBy} (attempt {$attempt})");
                            break 2;
                        }
                    }
                }
                
                if (!$userFound && $attempt < $maxAttempts) {
                    // Wait longer between attempts - some devices need more time
                    $waitTime = $attempt * 500000; // Increasing wait: 500ms, 1000ms, 1500ms, 2000ms, 2500ms
                    Log::info("User not found, waiting " . ($waitTime / 1000) . " seconds before next attempt...");
                    usleep($waitTime);
                    
                    // Reconnect before next attempt - some devices need fresh connection
                    try {
                        $client->disconnect();
                        usleep(200000); // 200ms
                        $this->connect();
                        $client = $this->getClient();
                        Log::info("Reconnected to device before verification attempt " . ($attempt + 1));
                    } catch (\Throwable $e) {
                        Log::warning("Could not reconnect before verification: " . $e->getMessage());
                    }
                }
            }
            
            if (!$userFound) {
                // Final attempt with longer delay and fresh connection
                // Some devices need significant time before user appears in getUsers()
                Log::warning("User not found after {$maxAttempts} attempts. Performing final check with extended delay...");
                
                // Disconnect, wait longer, reconnect
                try {
                    $client->disconnect();
                    usleep(2000000); // 2 seconds
                    $this->connect();
                    $client = $this->getClient();
                    Log::info("Final verification: Reconnected after 2 second delay");
                } catch (\Throwable $e) {
                    Log::warning("Could not reconnect for final verification: " . $e->getMessage());
                }
                
                // Final check
                $users = $client->getUsers();
                Log::info("Final verification: Retrieved " . count($users) . " users from device");
                
                foreach ($users as $key => $deviceUser) {
                    if ((string)$key === (string)$userid || $key == $userid ||
                        (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid) ||
                        (isset($deviceUser['user_id']) && ((string)$deviceUser['user_id'] === (string)$userid || (int)$deviceUser['user_id'] === (int)$userid))) {
                        $userFound = true;
                        Log::info("✓ User found on device in final verification check!");
                        break;
                    }
                }
                
                // Log detailed information for debugging
                Log::error("User registration verification failed after {$maxAttempts} attempts + final check.");
                Log::error("Searching for: UID={$uid}, UserID={$userid}, Name={$name}");
                Log::error("Device users count: " . count($users));
                
                // Log each user for comparison
                if (count($users) > 0) {
                    Log::error("Users on device:");
                    foreach ($users as $key => $deviceUser) {
                        $logUid = $deviceUser['uid'] ?? 'N/A';
                        $logUserId = $deviceUser['user_id'] ?? 'N/A';
                        $logName = $deviceUser['name'] ?? 'N/A';
                        Log::error("  - Array Key: '{$key}', UID: {$logUid}, UserID: {$logUserId}, Name: {$logName}");
                    }
                } else {
                    Log::error("No users found on device at all!");
                }
                
                // STRICT CHECK: If user count didn't increase and user not found, registration failed
                if (!$userFound && !$userFoundImmediately) {
                    // Check if user count increased - if not, registration definitely failed
                    if ($userCountAfter <= $userCountBefore) {
                        // Try alternative registration methods before giving up
                        Log::warning("Standard registration failed, trying alternative methods...");
                        $alternativeResult = $this->registerUserAlternative($uid, $userid, $name, $password, $role, $cardno);
                        
                        if ($alternativeResult) {
                            Log::info("✓ Alternative registration method succeeded!");
                            return true;
                        }
                        
                        $errorDetails = "Registration FAILED - User was NOT added to device.";
                        $errorDetails .= " User count did not increase (Before: {$userCountBefore}, After: {$userCountAfter}).";
                        $errorDetails .= " The device rejected the registration command.";
                        
                        if ($result === false) {
                            $errorDetails .= " setUser returned false.";
                        } else {
                            $errorDetails .= " Device responded but user was not added.";
                        }
                        
                        $errorDetails .= " POSSIBLE CAUSES: 1) Wrong Comm Key (check device: System → Communication → Comm Key), 2) UID already exists, 3) Device memory full, 4) Device in wrong mode, 5) Device needs restart.";
                        $errorDetails .= " SOLUTIONS: 1) Check device Comm Key and ensure ZKTECO_PASSWORD in .env matches exactly, 2) Restart the device, 3) Check device memory, 4) Try manual registration on device then sync.";
                        
                        throw new Exception($errorDetails);
                    }
                    
                    // If device responded with data (even if format is unexpected), assume registration succeeded
                    // We'll verify later when user actually uses the device
                    if ($result !== false && !empty($dataRecv) && strlen($dataRecv) >= 8) {
                        Log::warning("Device responded to registration (response length: " . strlen($dataRecv) . " bytes) but immediate verification failed.");
                        Log::warning("User count increased ({$userCountBefore} → {$userCountAfter}), so registration may have succeeded.");
                        Log::warning("Raw response: {$responseHex}");
                        Log::warning("Returning success - user will be verified when they use fingerprint scanner.");
                        
                        // Return success - we'll verify later via device user list or attendance logs
                        return true;
                    } else {
                        // No response or invalid response - registration likely failed
                        $errorDetails = "User was sent to device but verification failed - user not found on device after all attempts.";
                        $errorDetails .= " Device did not respond properly, so registration likely failed.";
                        
                        if ($gotAckOk) {
                            $errorDetails .= " Device returned CMD_ACK_OK, but user is not visible on device.";
                            $errorDetails .= " Possible reasons: 1) Device firmware issue, 2) User data format rejected, 3) Device memory full, 4) Device requires different parameters.";
                        } else {
                            if ($responseCode2007) {
                                $errorDetails .= " Device returned response code 2007 (UF200-S firmware 6.60 non-standard code).";
                                $errorDetails .= " This device uses non-standard response codes, but verification confirms user was NOT added.";
                            } elseif ($responseCode !== null) {
                                $errorDetails .= " Response code: {$responseCode} (expected 2000).";
                            } else {
                                $errorDetails .= " No valid response code received.";
                            }
                            if (!empty($responseHex)) {
                                $errorDetails .= " Raw response: {$responseHex}";
                            }
                        }
                        
                        $errorDetails .= " SOLUTION: Check device manually (User Management → User List). If user appears, manually mark as registered. If not, check Comm Key in device settings.";
                        throw new Exception($errorDetails);
                    }
                }
            } else {
                Log::info("✓ User successfully verified on device by {$foundBy}");
            }
            
            return true;
        } catch (\Throwable $e) {
            Log::error('ZKTeco registerUser error: ' . $e->getMessage());
            throw new Exception('Failed to register user: ' . $e->getMessage());
        }
    }

    /**
     * Remove user from device
     */
    public function removeUser($uid): bool
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Not connected to device');
            }

            $client = $this->getClient();
            return $client->removeUser($uid) !== false;
        } catch (\Throwable $e) {
            Log::error('ZKTeco removeUser error: ' . $e->getMessage());
            throw new Exception('Failed to remove user: ' . $e->getMessage());
        }
    }

    /**
     * Try alternative registration method - attempts multiple approaches
     * This is used when standard registration fails
     */
    public function registerUserAlternative($uid, $userid, $name, $password = '', $role = 0, $cardno = 0): bool
    {
        Log::info("=== ATTEMPTING ALTERNATIVE REGISTRATION METHOD ===");
        
        try {
            if (!$this->connect()) {
                throw new Exception('Not connected to device');
            }

            $client = $this->getClient();
            
            // Method 1: Try with device restart sequence
            Log::info("Alternative Method 1: Full device enable sequence");
            try {
                // Disconnect completely
                $client->disconnect();
                usleep(500000); // 500ms
                
                // Reconnect
                $this->connect();
                $client = $this->getClient();
                
                // Enable device multiple times
                for ($i = 0; $i < 3; $i++) {
                    try {
                        $client->enableDevice();
                        usleep(300000); // 300ms between enables
                    } catch (\Throwable $e) {
                        Log::warning("Enable attempt {$i} failed: " . $e->getMessage());
                    }
                }
                
                usleep(1000000); // 1 second wait
                
                // Try registration
                $result = $client->setUser($uid, $userid, $name, $password, $role, $cardno);
                
                if ($result !== false) {
                    // Wait and verify
                    usleep(2000000); // 2 seconds
                    $users = $client->getUsers();
                    foreach ($users as $key => $deviceUser) {
                        if ((string)$key === (string)$userid || 
                            (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid)) {
                            Log::info("✓ Alternative method 1 succeeded!");
                            return true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Alternative method 1 failed: " . $e->getMessage());
            }
            
            // Method 2: Try with different parameter order/format
            Log::info("Alternative Method 2: Different parameter format");
            try {
                $client->disconnect();
                usleep(500000);
                $this->connect();
                $client = $this->getClient();
                
                // Ensure name is clean and short
                $cleanName = mb_convert_encoding(substr(trim($name), 0, 20), 'ASCII', 'UTF-8');
                $cleanName = preg_replace('/[^\x20-\x7E]/', '', $cleanName); // Remove non-ASCII
                if (empty($cleanName)) {
                    $cleanName = "User{$userid}";
                }
                
                // Try with cleaned name
                $result = $client->setUser((int)$uid, (string)$userid, $cleanName, (string)$password, (int)$role, (int)$cardno);
                
                if ($result !== false) {
                    usleep(2000000);
                    $users = $client->getUsers();
                    foreach ($users as $key => $deviceUser) {
                        if ((string)$key === (string)$userid || 
                            (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid)) {
                            Log::info("✓ Alternative method 2 succeeded!");
                            return true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Alternative method 2 failed: " . $e->getMessage());
            }
            
            // Method 3: Try minimal registration (name only)
            Log::info("Alternative Method 3: Minimal registration");
            try {
                $client->disconnect();
                usleep(500000);
                $this->connect();
                $client = $this->getClient();
                
                $client->enableDevice();
                usleep(1000000);
                
                // Try with minimal name
                $minimalName = substr(trim($name), 0, 10);
                $result = $client->setUser((int)$uid, (string)$userid, $minimalName, '', 0, 0);
                
                if ($result !== false) {
                    usleep(2000000);
                    $users = $client->getUsers();
                    foreach ($users as $key => $deviceUser) {
                        if ((string)$key === (string)$userid || 
                            (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid)) {
                            Log::info("✓ Alternative method 3 succeeded!");
                            return true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Alternative method 3 failed: " . $e->getMessage());
            }
            
            Log::error("All alternative registration methods failed");
            return false;
            
        } catch (\Throwable $e) {
            Log::error('Alternative registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync and verify users from device
     * This method gets all users from device and marks them as registered in the system
     */
    public function syncUsersFromDevice(): array
    {
        try {
            // Set connection timeout
            $connectionTimeout = 10; // 10 seconds for connection
            
            Log::info("Attempting to connect to device {$this->ip}:{$this->port}");
            
            if (!$this->connect()) {
                throw new Exception('Not connected to device. Check IP address (192.168.100.108), port (4370), and network connectivity.');
            }

            Log::info("Connected successfully, fetching users from device...");
            
            // Get users with timeout protection
            try {
                $deviceUsers = $this->getUsers();
            } catch (\Throwable $e) {
                $this->disconnect();
                throw new Exception('Failed to get users from device: ' . $e->getMessage() . '. Device may be busy or not responding.');
            }
            
            if (!is_array($deviceUsers)) {
                $this->disconnect();
                throw new Exception('Invalid response from device. Expected array of users, got: ' . gettype($deviceUsers));
            }
            
            $verified = [];
            $notFound = [];

            // Log summary only (reduce logging overhead)
            Log::info('=== SYNC USERS FROM DEVICE ===');
            Log::info('Total users from device: ' . count($deviceUsers));

            foreach ($deviceUsers as $key => $deviceUser) {
                // Only log errors, not every user (reduces performance overhead)
                
                // The library returns users as: key = userid (string), value = array with uid, name, etc.
                // According to User.php line 127: $users[$userid] = $data;
                // So the KEY is the userid/enroll_id we need
                $enrollId = trim((string) $key);
                
                // Validate enroll_id
                if (empty($enrollId) || $enrollId === '0') {
                    // Fallback: try to get from user_id in data
                    if (isset($deviceUser['user_id']) && !empty($deviceUser['user_id'])) {
                        $enrollId = (string) $deviceUser['user_id'];
                    } else {
                        // Only log errors, not warnings for every invalid user
                        $notFound[] = [
                            'enroll_id' => 'UNKNOWN',
                            'name' => $deviceUser['name'] ?? 'N/A',
                            'device_uid' => $deviceUser['uid'] ?? 'N/A',
                            'error' => 'Could not extract enroll_id from user data',
                            'raw_key' => $key,
                            'raw_data' => $deviceUser
                        ];
                        continue;
                    }
                }

                // Find user in database by enroll_id
                $user = \App\Models\User::where('enroll_id', $enrollId)->first();

                if ($user) {
                    // Mark as registered if not already
                    if (!$user->registered_on_device) {
                        $user->update([
                            'registered_on_device' => true,
                            'device_registered_at' => now(),
                        ]);
                        $verified[] = [
                            'user' => $user->name,
                            'enroll_id' => $enrollId,
                            'action' => 'marked as registered'
                        ];
                    } else {
                        $verified[] = [
                            'user' => $user->name,
                            'enroll_id' => $enrollId,
                            'action' => 'already registered'
                        ];
                    }
                } else {
                    // User not in database - CREATE them from device data
                    $name = trim($deviceUser['name'] ?? "User {$enrollId}");
                    if (empty($name)) {
                        $name = "User {$enrollId}";
                    }
                    
                    // Generate unique email - use timestamp to avoid conflicts
                    $email = "user{$enrollId}_" . time() . "@device.local";
                    
                    // Check if enroll_id already exists (might have been created in parallel)
                    $existingUser = \App\Models\User::where('enroll_id', $enrollId)->first();
                    if ($existingUser) {
                        // User was created between check and create - mark as registered
                        if (!$existingUser->registered_on_device) {
                            $existingUser->update([
                                'registered_on_device' => true,
                                'device_registered_at' => now(),
                            ]);
                        }
                        $verified[] = [
                            'user' => $existingUser->name,
                            'enroll_id' => $enrollId,
                            'action' => 'marked as registered'
                        ];
                        continue;
                    }
                    
                    try {
                        // Clean name - remove null bytes and trim
                        $name = str_replace(["\0", "\x00"], '', $name);
                        $name = mb_convert_encoding($name, 'UTF-8', 'UTF-8');
                        $name = trim($name);
                        
                        if (empty($name) || strlen($name) === 0) {
                            $name = "User {$enrollId}";
                        }
                        
                        // Ensure email is unique by checking
                        $emailBase = "user{$enrollId}@device.local";
                        $counter = 0;
                        while (\App\Models\User::where('email', $emailBase)->exists() && $counter < 100) {
                            $emailBase = "user{$enrollId}_{$counter}@device.local";
                            $counter++;
                        }
                        $email = $emailBase;
                        
                        $newUser = \App\Models\User::create([
                            'enroll_id' => $enrollId,
                            'name' => $name,
                            'email' => $email,
                            'password' => bcrypt('device_user_' . $enrollId), // Random password
                            'registered_on_device' => true,
                            'device_registered_at' => now(),
                        ]);
                        
                        $verified[] = [
                            'user' => $name,
                            'enroll_id' => $enrollId,
                            'action' => 'created from device'
                        ];
                    } catch (\Exception $e) {
                        $errorMsg = $e->getMessage();
                        $notFound[] = [
                            'enroll_id' => $enrollId,
                            'name' => $name,
                            'device_uid' => $deviceUser['uid'] ?? 'N/A',
                            'error' => $errorMsg,
                            'raw_key' => $key,
                            'raw_data' => $deviceUser
                        ];
                        Log::error("Failed to create user from device (Enroll ID: {$enrollId}): {$errorMsg}");
                    }
                }
            }

            $result = [
                'verified' => count($verified),
                'created' => count(array_filter($verified, fn($v) => $v['action'] === 'created from device')),
                'marked_registered' => count(array_filter($verified, fn($v) => $v['action'] === 'marked as registered')),
                'already_registered' => count(array_filter($verified, fn($v) => $v['action'] === 'already registered')),
                'not_found' => count($notFound),
                'details' => $verified,
                'device_users_not_in_db' => $notFound,
                'total_device_users' => count($deviceUsers),
                'raw_device_users' => $deviceUsers // Include raw data for debugging
            ];
            
            // Log summary only
            Log::info("Sync completed: {$result['verified']} verified, {$result['created']} created, {$result['not_found']} errors");
            
            // Disconnect after sync
            $this->disconnect();
            
            return $result;
        } catch (\Throwable $e) {
            // Ensure we disconnect even on error
            try {
                $this->disconnect();
            } catch (\Throwable $disconnectError) {
                // Ignore disconnect errors
            }
            
            Log::error('ZKTeco syncUsersFromDevice error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw new Exception('Failed to sync users from device: ' . $e->getMessage());
        }
    }

    /**
     * Sync attendance logs to database
     * Also verifies users when they use the fingerprint scanner
     */
    public function syncAttendancesToDatabase(): array
    {
        try {
            Log::info('=== STARTING ATTENDANCE SYNC ===');
            Log::info('Device IP: ' . $this->ip . ', Port: ' . $this->port);
            
            // First, verify connection
            if (!$this->connect()) {
                Log::error('Failed to connect to device');
                throw new Exception('Failed to connect to device. Check IP, port, and network connectivity.');
            }
            
            Log::info('✓ Connected to device successfully');
            
            // Get attendances from device
            $attendances = $this->getAttendances();
            
            Log::info('=== ATTENDANCE SYNC RESULTS ===');
            Log::info('Total records from device: ' . count($attendances));
            
            if (count($attendances) === 0) {
                Log::warning('⚠️ No attendance records found on device!');
                Log::warning('This could mean:');
                Log::warning('1. No users have punched in/out yet');
                Log::warning('2. Device attendance log is empty');
                Log::warning('3. Device connection issue');
                return [
                    'synced' => 0,
                    'skipped' => 0,
                    'users_verified' => 0,
                    'verified_user_names' => [],
                    'details' => [],
                    'error' => 'No attendance records found on device. Make sure users have punched in/out on the device.'
                ];
            }
            
            $synced = [];
            $skipped = [];
            $usersVerified = [];

            Log::info('Processing ' . count($attendances) . ' attendance records from device');
            
            // Sort attendance records by timestamp to process in chronological order
            // This ensures we can properly determine check-in vs check-out based on sequence
            usort($attendances, function($a, $b) {
                $timeA = $a['record_time'] ?? $a['timestamp'] ?? $a['time'] ?? 0;
                $timeB = $b['record_time'] ?? $b['timestamp'] ?? $b['time'] ?? 0;
                if ($timeA == $timeB) return 0;
                return ($timeA < $timeB) ? -1 : 1;
            });
            
            // OPTIMIZATION: Pre-extract enroll IDs and batch load users
            // This prevents N+1 query problem
            $enrollIds = [];
            foreach ($attendances as $log) {
                $enrollId = $log['user_id'] ?? $log['uid'] ?? $log['pin'] ?? null;
                if ($enrollId) {
                    $enrollIds[] = (string)$enrollId;
                }
            }
            $enrollIds = array_unique($enrollIds);
            
            // Batch load all users at once
            $usersByEnrollId = \App\Models\User::whereIn('enroll_id', $enrollIds)
                ->get()
                ->keyBy('enroll_id');
            
            // Get all user IDs
            $userIds = $usersByEnrollId->pluck('id')->toArray();
            
            // Get last attendance for each user in one query (if we have users)
            $userLastStatus = [];
            if (!empty($userIds)) {
                $lastAttendances = \App\Models\Attendance::whereIn('user_id', $userIds)
                    ->select('user_id', 'status', 'punch_time')
                    ->orderBy('punch_time', 'desc')
                    ->get()
                    ->groupBy('user_id')
                    ->map(function($group) {
                        return $group->first(); // Get the most recent for each user
                    });
                
                // Track last status for each user to determine check-in/check-out
                foreach ($lastAttendances as $userId => $lastAttendance) {
                    $userLastStatus[$userId] = $lastAttendance->status;
                }
            }
            
            foreach ($attendances as $index => $log) {
                // Log raw data for debugging
                if ($index === 0) {
                    Log::info('Sample raw attendance log from device: ' . json_encode($log));
                }
                
                // Map library response to our database structure
                // Library returns: uid, user_id (badge ID), state, record_time, type, device_ip
                // According to Attendance.php: uid is the user ID, user_id is the badge ID (intval of id)
                // We need to use 'uid' as the enroll_id
                
                $enrollId = null;
                $timestamp = null;
                $status = null;
                $verifyMode = null;
                
                // Get enroll_id - IMPORTANT: Based on actual device data:
                //   - uid = sequential record number (1, 2, 3, 4...)
                //   - user_id = actual user's enroll ID (1, 1, 1, 4, 4, 2...)
                // We MUST use 'user_id' as the enroll_id, NOT 'uid'!
                if (isset($log['user_id'])) {
                    $enrollId = (string) $log['user_id'];
                } elseif (isset($log['uid'])) {
                    // Fallback: if user_id not available, try uid (but this is usually wrong)
                    $enrollId = (string) $log['uid'];
                } elseif (isset($log['pin'])) {
                    $enrollId = (string) $log['pin'];
                }
                
                // Get timestamp
                if (isset($log['record_time'])) {
                    $timestamp = $log['record_time'];
                } elseif (isset($log['timestamp'])) {
                    $timestamp = $log['timestamp'];
                } elseif (isset($log['time'])) {
                    $timestamp = $log['time'];
                }
                
                // Determine status based on sequence (alternating check-in/check-out)
                // First scan = Check In, Second scan = Check Out, Third = Check In, etc.
                // We'll determine this after we have the user and can check their last status
                $status = null; // Will be determined below
                
                // Get verify_mode
                // type: 0 = fingerprint, 255 = other mode, 15 = might be verify mode
                if (isset($log['type'])) {
                    $verifyMode = $log['type'];
                    // If type is 255 or 0, it's fingerprint verification
                    if ($verifyMode == 255 || $verifyMode == 0) {
                        $verifyMode = 'Fingerprint';
                    }
                } elseif (isset($log['verify_mode'])) {
                    $verifyMode = $log['verify_mode'];
                } else {
                    $verifyMode = null;
                }

                // Reduced logging for performance (only log first few)
                if ($index < 3) {
                    Log::info("Processing attendance: EnrollID={$enrollId}, Timestamp={$timestamp}, Status={$status}, VerifyMode={$verifyMode}");
                }

                if (!$enrollId || !$timestamp) {
                    Log::warning('Skipping attendance record - missing enroll_id or timestamp');
                    Log::warning('Raw log data: ' . json_encode($log, JSON_PRETTY_PRINT));
                    $skipped[] = array_merge($log, ['skip_reason' => 'missing_enroll_id_or_timestamp']);
                    continue;
                }

                // Find user by enroll_id (use pre-loaded users)
                $user = $usersByEnrollId[$enrollId] ?? null;

                if (!$user) {
                    // Only log first few missing users to avoid log spam
                    if (count($skipped) < 3) {
                        Log::warning("User not found in database for Enroll ID: {$enrollId}");
                    }
                    $skipped[] = array_merge($log, ['skip_reason' => 'user_not_found', 'enroll_id_searched' => $enrollId]);
                    continue;
                }

                // If user used fingerprint scanner, they're definitely on the device
                // Mark them as registered if not already
                if (!$user->registered_on_device) {
                    $user->update([
                        'registered_on_device' => true,
                        'device_registered_at' => now(),
                    ]);
                    $usersVerified[] = $user->name;
                }

                // Parse timestamp
                try {
                    $punchTime = is_string($timestamp) ? \Carbon\Carbon::parse($timestamp) : $timestamp;
                } catch (\Exception $e) {
                    $skipped[] = $log;
                    continue;
                }

                // Get attendance date
                $attendanceDate = $punchTime->format('Y-m-d');
                
                // Use database transaction with lock to prevent race conditions
                // Process the entire check-in/check-out logic INSIDE the transaction
                $result = DB::transaction(function() use ($user, $enrollId, $attendanceDate, $punchTime, $verifyMode) {
                    // Find existing attendance record for this user on this date WITH LOCK
                    // Try to find by attendance_date first, then by punch_time date (for old records)
                    $attendance = \App\Models\Attendance::where('user_id', $user->id)
                        ->where(function($query) use ($attendanceDate) {
                            $query->where('attendance_date', $attendanceDate)
                                  ->orWhere(function($q) use ($attendanceDate) {
                                      // For old records without attendance_date, check by punch_time date
                                      $q->whereNull('attendance_date')
                                        ->whereDate('punch_time', $attendanceDate);
                                  });
                        })
                        ->lockForUpdate() // Lock the row to prevent concurrent updates
                        ->first();
                    
                    // If not found, create a new record
                    if (!$attendance) {
                        $attendance = \App\Models\Attendance::create([
                            'user_id' => $user->id,
                            'enroll_id' => $enrollId,
                            'punch_time' => $punchTime, // Keep for backward compatibility
                            'status' => 1, // Default to Check In
                            'verify_mode' => $verifyMode,
                            'device_ip' => $this->ip,
                            'check_in_time' => null,
                            'check_out_time' => null,
                            'attendance_date' => $attendanceDate,
                        ]);
                    } else {
                        // Update attendance_date if it's missing (for old records)
                        if (!$attendance->attendance_date) {
                            $attendance->attendance_date = $attendanceDate;
                            $attendance->save();
                        }
                    }
                    
                    // Refresh to get latest data
                    $attendance->refresh();
                    
                    // Log current state for debugging
                    $checkInTimeStr = $attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d H:i:s') : 'NULL';
                    $checkOutTimeStr = $attendance->check_out_time ? $attendance->check_out_time->format('Y-m-d H:i:s') : 'NULL';
                    Log::info("Processing scan for User {$user->name} at {$punchTime->format('Y-m-d H:i:s')}. Current state: check_in_time={$checkInTimeStr}, check_out_time={$checkOutTimeStr}");
                    
                    // Use explicit null checks to avoid issues with Carbon instances
                    $hasCheckIn = !is_null($attendance->check_in_time);
                    $hasCheckOut = !is_null($attendance->check_out_time);
                    
                    // Check if user already has both check-in and check-out for today
                    if ($hasCheckIn && $hasCheckOut) {
                        // User already checked in and out today - reject additional scans
                        Log::info("User {$user->name} already checked in and out today - rejecting additional scan");
                        return ['action' => 'rejected', 'reason' => 'already_checked_in_and_out', 'check_in_time' => $checkInTimeStr, 'check_out_time' => $checkOutTimeStr];
                    }
                    
                    // CRITICAL: Check if this scan time matches an already processed time
                    // This prevents the device from sending the same scan multiple times
                    if ($hasCheckIn && $attendance->check_in_time && abs($punchTime->diffInSeconds($attendance->check_in_time)) <= 2) {
                        // This scan time is the same as (or very close to) the check-in time
                        // This means the device is sending the first scan again
                        // Skip it - we don't want to overwrite check-in with the same time
                        Log::warning("User {$user->name} scan at {$punchTime->format('Y-m-d H:i:s')} matches existing check-in time ({$checkInTimeStr}). Skipping duplicate scan.");
                        return ['action' => 'rejected', 'reason' => 'duplicate_scan_time', 'existing_time' => $checkInTimeStr];
                    }
                    
                    if ($hasCheckOut && $attendance->check_out_time && abs($punchTime->diffInSeconds($attendance->check_out_time)) <= 2) {
                        // This scan time is the same as (or very close to) the check-out time
                        // Skip it - we don't want to overwrite check-out with the same time
                        Log::warning("User {$user->name} scan at {$punchTime->format('Y-m-d H:i:s')} matches existing check-out time ({$checkOutTimeStr}). Skipping duplicate scan.");
                        return ['action' => 'rejected', 'reason' => 'duplicate_scan_time', 'existing_time' => $checkOutTimeStr];
                    }
                    
                    // Determine if this is check-in or check-out
                    if (!$hasCheckIn) {
                        // First scan of the day = Check In
                        $attendance->update([
                            'check_in_time' => $punchTime,
                            'punch_time' => $punchTime, // Update punch_time to latest
                            'status' => 1, // Check In
                            'verify_mode' => $verifyMode,
                            'attendance_date' => $attendanceDate, // Ensure date is set
                        ]);
                        Log::info("✓ User {$user->name} checked IN at {$punchTime->format('Y-m-d H:i:s')}");
                        return ['action' => 'check_in', 'success' => true];
                    } elseif (!$hasCheckOut) {
                        // Second scan of the day = Check Out
                        // IMPORTANT: Use the actual scan time, but ensure it's after check_in_time
                        // If timestamps are the same or check_out is before check_in, add 1 second
                        if ($punchTime->lte($attendance->check_in_time)) {
                            $checkOutTime = $attendance->check_in_time->copy()->addSecond();
                            Log::warning("User {$user->name} check-out time ({$punchTime->format('Y-m-d H:i:s')}) is same or before check-in ({$attendance->check_in_time->format('Y-m-d H:i:s')}). Adjusting to: {$checkOutTime->format('Y-m-d H:i:s')}");
                        } else {
                            // Use the actual scan time - this is the real second scan
                            $checkOutTime = $punchTime;
                        }
                        
                        // IMPORTANT: Only update check_out_time, preserve check_in_time
                        $updateResult = $attendance->update([
                            'check_out_time' => $checkOutTime,
                            'punch_time' => $checkOutTime, // Update punch_time to latest
                            'status' => 0, // Check Out
                            'verify_mode' => $verifyMode,
                            'attendance_date' => $attendanceDate, // Ensure date is set
                            // Note: check_in_time is NOT in the update array, so it will be preserved
                        ]);
                        
                        // Verify the update succeeded
                        $attendance->refresh();
                        if ($attendance->check_out_time) {
                            Log::info("✓ User {$user->name} checked OUT at {$checkOutTime->format('Y-m-d H:i:s')} (Check In was at: " . $attendance->check_in_time->format('Y-m-d H:i:s') . ") - UPDATE SUCCESS");
                            return ['action' => 'check_out', 'success' => true, 'check_out_time' => $checkOutTime->format('Y-m-d H:i:s')];
                        } else {
                            Log::error("✗ User {$user->name} check-out UPDATE FAILED! check_out_time is still NULL after update.");
                            return ['action' => 'check_out', 'success' => false, 'error' => 'update_failed'];
                        }
                    } else {
                        // This shouldn't happen, but log it if it does
                        Log::warning("User {$user->name} scan at {$punchTime->format('Y-m-d H:i:s')} - unexpected state: check_in_time=" . ($hasCheckIn ? 'SET' : 'NULL') . ", check_out_time=" . ($hasCheckOut ? 'SET' : 'NULL'));
                        return ['action' => 'unexpected_state', 'has_check_in' => $hasCheckIn, 'has_check_out' => $hasCheckOut];
                    }
                });
                
                // If the scan was rejected, add to skipped
                if (isset($result['action']) && $result['action'] === 'rejected') {
                    $skipped[] = array_merge($log, [
                        'skip_reason' => 'already_checked_in_and_out_today',
                        'check_in_time' => $result['check_in_time'] ?? 'NULL',
                        'check_out_time' => $result['check_out_time'] ?? 'NULL'
                    ]);
                    continue;
                }

                $synced[] = [
                    'user' => $user->name,
                    'enroll_id' => $enrollId,
                    'punch_time' => $punchTime->format('Y-m-d H:i:s'),
                ];
            }

            $result = [
                'synced' => count($synced),
                'skipped' => count($skipped),
                'users_verified' => count($usersVerified),
                'verified_user_names' => $usersVerified,
                'details' => $synced,
                'skipped_details' => $skipped, // Include skipped records for debugging
            ];
            
            Log::info('=== SYNC COMPLETE ===');
            Log::info("Synced: " . count($synced) . " records");
            Log::info("Skipped: " . count($skipped) . " records");
            if (count($skipped) > 0) {
                Log::warning("Skipped records: " . json_encode($skipped, JSON_PRETTY_PRINT));
            }
            
            return $result;
        } catch (\Throwable $e) {
            Log::error('ZKTeco syncAttendancesToDatabase error: ' . $e->getMessage());
            throw new Exception('Failed to sync attendances: ' . $e->getMessage());
        }
    }

    /**
     * Check if user has fingerprints enrolled
     */
    public function checkFingerprints($uid): array
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Not connected to device');
            }

            $client = $this->getClient();
            $fingerprints = $client->getFingerprint((int) $uid);
            
            $enrolledFingers = [];
            foreach ($fingerprints as $fingerId => $data) {
                if (!empty($data)) {
                    $enrolledFingers[] = $fingerId;
                }
            }

            return [
                'has_fingerprints' => count($enrolledFingers) > 0,
                'enrolled_fingers' => $enrolledFingers,
                'count' => count($enrolledFingers)
            ];
        } catch (\Throwable $e) {
            Log::error('ZKTeco checkFingerprints error: ' . $e->getMessage());
            throw new Exception('Failed to check fingerprints: ' . $e->getMessage());
        }
    }

    /**
     * Get enrollment instructions for the device
     */
    public function getEnrollmentInstructions(): string
    {
        return "To enroll fingerprints on the ZKTeco device:\n\n" .
               "1. On the device, go to Menu > User Management > Enroll Fingerprint\n" .
               "2. Enter the user's Enroll ID (PIN)\n" .
               "3. Place the finger on the scanner 3 times as prompted\n" .
               "4. The device will confirm successful enrollment\n\n" .
               "Alternatively, you can use the device's admin menu to enroll users.";
    }

    /**
     * Set connection parameters
     */
    public function setConnection($ip, $port = 4370, $password = null): void
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->password = $password;
        $this->client = null; // Reset client to use new settings
    }

    /**
     * Clear all attendance records from the device
     * 
     * @return array
     * @throws Exception
     */
    public function clearDeviceAttendance(): array
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Not connected to device');
            }

            Log::info('=== CLEARING DEVICE ATTENDANCE ===');
            Log::info('Device IP: ' . $this->ip . ', Port: ' . $this->port);

            $client = $this->getClient();
            
            // Get count before clearing
            $beforeCount = count($this->getAttendances());
            Log::info("Attendance records on device before clearing: {$beforeCount}");

            if ($beforeCount === 0) {
                return [
                    'success' => true,
                    'message' => 'Device already has no attendance records',
                    'records_before' => 0,
                    'records_after' => 0,
                    'cleared' => 0
                ];
            }

            // Clear attendance from device
            $result = $client->clearAttendance();
            
            // Log the raw result
            Log::info("Clear attendance raw result: " . var_export($result, true));
            Log::info("Result type: " . gettype($result));
            
            // Check the actual response code from the device
            $responseCode = null;
            $responseHex = '';
            try {
                $reflection = new \ReflectionClass($client);
                $dataRecvProperty = $reflection->getProperty('_data_recv');
                $dataRecvProperty->setAccessible(true);
                $dataRecv = $dataRecvProperty->getValue($client);
                
                if (!empty($dataRecv) && strlen($dataRecv) >= 8) {
                    $responseHex = bin2hex(substr($dataRecv, 0, 16));
                    $responseCode = Util::checkValid($dataRecv);
                    Log::info("Device response code: {$responseCode} (2000=OK, 2001=Error, 2005=Unauth)");
                    Log::info("Response hex: {$responseHex}");
                }
            } catch (\Throwable $e) {
                Log::warning("Could not read device response: " . $e->getMessage());
            }

            // Wait for device to process the command
            usleep(1000000); // Wait 1 second for device to process
            
            // Verify deletion by checking attendance count again
            $afterCount = count($this->getAttendances());
            Log::info("Attendance records on device after clearing: {$afterCount}");

            // Determine success based on:
            // 1. Response code (2000 = OK)
            // 2. Result value (true/1 = OK)
            // 3. Actual verification (count decreased)
            $successByCode = ($responseCode === Util::CMD_ACK_OK || $responseCode === 2000);
            $successByResult = ($result === true || $result === 1 || $result === '1');
            $successByVerification = ($afterCount < $beforeCount || $afterCount === 0);

            if ($successByCode || ($successByResult && $successByVerification)) {
                $cleared = $beforeCount - $afterCount;
                return [
                    'success' => true,
                    'message' => "Successfully cleared {$cleared} attendance record(s) from device",
                    'records_before' => $beforeCount,
                    'records_after' => $afterCount,
                    'cleared' => $cleared,
                    'response_code' => $responseCode,
                    'response_hex' => $responseHex
                ];
            } elseif ($successByVerification) {
                // Even if response was unclear, verification shows it worked
                $cleared = $beforeCount - $afterCount;
                Log::info("Clear succeeded by verification (count decreased from {$beforeCount} to {$afterCount})");
                return [
                    'success' => true,
                    'message' => "Successfully cleared {$cleared} attendance record(s) from device (verified by count)",
                    'records_before' => $beforeCount,
                    'records_after' => $afterCount,
                    'cleared' => $cleared,
                    'response_code' => $responseCode,
                    'response_hex' => $responseHex,
                    'note' => 'Device response was unclear, but verification confirms records were cleared'
                ];
            } else {
                // Failed - provide detailed error
                $errorMsg = 'Device clear may have failed. ';
                if ($responseCode === Util::CMD_ACK_ERROR || $responseCode === 2001) {
                    $errorMsg .= 'Device returned error (2001). ';
                } elseif ($responseCode === Util::CMD_ACK_UNAUTH || $responseCode === 2005) {
                    $errorMsg .= 'Authentication required (2005). Check Comm Key. ';
                } elseif ($responseCode !== null) {
                    $errorMsg .= "Device returned code: {$responseCode}. ";
                }
                $errorMsg .= "Records before: {$beforeCount}, after: {$afterCount}.";
                
                Log::warning("Clear attendance failed: {$errorMsg}");
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'records_before' => $beforeCount,
                    'records_after' => $afterCount,
                    'response_code' => $responseCode,
                    'response_hex' => $responseHex,
                    'raw_result' => $result
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Clear device attendance error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw new Exception('Failed to clear device attendance: ' . $e->getMessage());
        }
    }
}
