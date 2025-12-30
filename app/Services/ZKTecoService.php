<?php

namespace App\Services;

use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use CodingLibs\ZktecoPhp\Libs\Services\Util;
use Illuminate\Support\Facades\Log;
use Exception;

class ZKTecoService
{
    protected ?ZKTeco $client = null;
    private string $ip;
    private int $port;
    private $password; // Can be int or string, will be converted to int

    public function __construct($ip = null, $port = null, $password = null)
    {
        $this->ip = $ip ?? config('zkteco.ip', env('ZKTECO_IP', '192.168.100.108'));
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
                timeout: 30, // 30 seconds timeout for better reliability
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
     * Test connection and return result
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
                $info['device_name'] = $client->deviceName() ?: null;
            } catch (\Throwable $e) {
                $info['device_name'] = null;
                Log::warning('Could not get device name: ' . $e->getMessage());
            }
            
            try {
                $info['device_id'] = $client->deviceId() ?: null;
            } catch (\Throwable $e) {
                $info['device_id'] = null;
                Log::warning('Could not get device ID: ' . $e->getMessage());
            }
            
            try {
                $info['serial_number'] = $client->serialNumber() ?: null;
            } catch (\Throwable $e) {
                $info['serial_number'] = null;
                Log::warning('Could not get serial number: ' . $e->getMessage());
            }
            
            try {
                $info['version'] = $client->version() ?: null;
            } catch (\Throwable $e) {
                $info['version'] = null;
                Log::warning('Could not get version: ' . $e->getMessage());
            }
            
            try {
                $info['platform'] = $client->platform() ?: null;
            } catch (\Throwable $e) {
                $info['platform'] = null;
                Log::warning('Could not get platform: ' . $e->getMessage());
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
    public function getAttendance()
    {
        return $this->getAttendances();
    }
    
    /**
     * Get attendance logs (plural alias)
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
     * Register user to device
     */
    public function registerUser($uid, $name, $privilege = 0, $password = '', $group = '', $user_id = '')
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Not connected to device');
            }

            $client = $this->getClient();
            
            // Ensure userid is numeric string (required by some devices)
            $userid = $user_id ?: (string)$uid;
            if (!is_numeric($userid)) {
                throw new Exception('Enroll ID must be numeric');
            }
            
            // Ensure uid is within valid range (1-65535)
            $uid = (int)$uid;
            if ($uid < 1 || $uid > 65535) {
                throw new Exception('UID must be between 1 and 65535');
            }
            
            // Truncate name to 24 characters (device limit)
            $name = substr(trim($name), 0, 24);
            
            Log::info("=== Attempting to register user to device ===");
            Log::info("UID={$uid}, UserID={$userid}, Name={$name}, Privilege={$privilege}");
            Log::info("Device IP: {$this->ip}, Port: {$this->port}, Comm Key: {$this->password}");
            
            // STEP 1: Verify authentication by testing if we can read from device
            Log::info("=== STEP 1: Verifying Authentication ===");
            try {
                $testUsers = $client->getUsers();
                Log::info("✓ Authentication VERIFIED - Can get users (found " . count($testUsers) . ")");
            } catch (\Throwable $e) {
                Log::error("✗ Authentication FAILED - Cannot get users: " . $e->getMessage());
                throw new Exception("Device authentication failed. Cannot read users from device. Check Comm Key.");
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
            
            // Check if user already exists on device before attempting registration
            $userExists = false;
            foreach ($usersBefore as $key => $deviceUser) {
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
            Log::info("Parameters: UID={$uid}, UserID='{$userid}', Name='{$name}', Password='{$password}', Role={$privilege}, CardNo=0");
            Log::info("Comm Key: {$this->password}, Device ID: 1");
            
            // Use library's setUser method
            // Library signature: setUser($uid, $userid, $name, $password = '', $role = 0, $cardno = 0)
            $result = $client->setUser(
                $uid,
                $userid,
                $name,
                $password,
                $privilege,
                0 // cardno
            );
            
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
                    
                    // Check if response code is 2007 (UF200-S firmware 6.60 specific code)
                    if ($actualResponseCode === false && strlen($dataRecv) >= 8) {
                        $u = unpack('H2h1/H2h2', substr($dataRecv, 0, 8));
                        $responseCodeValue = hexdec($u['h2'] . $u['h1']);
                        if ($responseCodeValue == 2007) {
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
                    Log::info("Extracted response code value: {$responseCodeValue}");
                }
                
                // SPECIAL CASE: UF200-S firmware 6.60 returns 2007 instead of 2000
                // Sometimes device actually adds user but returns 2007
                // Let's wait a bit longer and check again
                if ($responseCodeValue == 2007 || $actualResponseCode === false) {
                    Log::warning("Device returned response code 2007 or unexpected code. Waiting longer and re-checking...");
                    usleep(1000000); // Wait 1 more second
                    
                    // Check again
                    try {
                        $usersAfterRetry = $client->getUsers();
                        $userCountAfterRetry = count($usersAfterRetry);
                        Log::info("Users on device AFTER retry: {$userCountAfterRetry} (was {$userCountBefore})");
                        
                        // Check if user is in the list after retry
                        foreach ($usersAfterRetry as $key => $deviceUser) {
                            if ((string)$key === (string)$userid || 
                                (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid) ||
                                (isset($deviceUser['user_id']) && ((string)$deviceUser['user_id'] === (string)$userid))) {
                                Log::info("✓ User found after retry! Registration was successful despite response code 2007.");
                return true;
                            }
                        }
                        
                        // If user count increased but user not found by ID, might be timing issue
                        if ($userCountAfterRetry > $userCountBefore) {
                            Log::warning("User count increased but user not found by ID - might be timing issue or different ID format");
                            // Check by name as fallback
                            foreach ($usersAfterRetry as $deviceUser) {
                                if (is_array($deviceUser) && isset($deviceUser['name']) && $deviceUser['name'] === $name) {
                                    Log::info("✓ User found by name after retry! Registration was successful.");
                                    return true;
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::error("Could not re-check users after retry: " . $e->getMessage());
                    }
                    
                    // If still not found after retry, throw error
                    Log::error("CRITICAL: Device returned response code 2007 (UF200-S firmware 6.60 specific) but user was NOT added!");
                    Log::error("This confirms a protocol/firmware compatibility issue.");
                    throw new Exception("Device returned response code 2007 but user was not added. This is a known issue with UF200-S firmware 6.60. Please register user directly on device or use Push SDK.");
                }
                
                // If device returned CMD_ACK_OK but user wasn't added, this is a device firmware issue
                if ($actualResponseCode === Util::CMD_ACK_OK) {
                    Log::error("CRITICAL: Device returned CMD_ACK_OK but user was NOT added!");
                    Log::error("This indicates a device firmware bug or device-specific issue.");
                    throw new Exception("Device returned success but user was not added. This is a known firmware issue with UF200-S firmware 6.60. Please register user directly on device or use Push SDK.");
                }
                
                throw new Exception("Registration failed: User count did not increase. Device may have rejected the registration silently.");
            }
            
            // If we got here, user count increased but user not found immediately - might be timing issue
            Log::warning("User count increased but user not found immediately - might be timing issue");
            return true;
        } catch (\Throwable $e) {
            Log::error('ZKTeco registerUser error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
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
            
            if (method_exists($client, 'removeUser')) {
                $result = $client->removeUser((int)$uid);
                
                if ($result) {
                    Log::info("Successfully removed user from device: UID={$uid}");
                return true;
                } else {
                    Log::error("Failed to remove user from device: UID={$uid}");
            return false;
                }
            }
            
            Log::warning('removeUser method does not exist on ZKTeco client');
            return false;
        } catch (\Throwable $e) {
            Log::error('ZKTeco removeUser error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
                return false;
            }
    }

    /**
     * Test connection only (without authentication)
     */
    public function testConnectionOnly()
    {
        try {
            $client = $this->getClient();
            return $client->connect();
        } catch (\Throwable $e) {
            Log::error('ZKTeco Test Connection Error: ' . $e->getMessage());
            return false;
        }
    }
}
