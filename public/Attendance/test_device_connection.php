<?php

/**
 * ZKTeco Device Connection and Registration Test Script
 * Tests device connection with Comm Key 0 and Device ID 1
 * 
 * Usage: php test_device_connection.php [ip] [port]
 * 
 * Example: php test_device_connection.php 192.168.100.100 4370
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ZKTecoService;
use Illuminate\Support\Facades\Log;

// Get parameters from command line or use defaults
$ip = $argv[1] ?? '192.168.100.100';
$port = isset($argv[2]) ? (int)$argv[2] : 4370;
$commKey = 0; // Comm Key is 0
$deviceId = 1; // Device ID is 1

// Colors for output (Windows compatible)
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$green = $isWindows ? '' : "\033[0;32m";
$red = $isWindows ? '' : "\033[0;31m";
$yellow = $isWindows ? '' : "\033[1;33m";
$blue = $isWindows ? '' : "\033[0;34m";
$cyan = $isWindows ? '' : "\033[0;36m";
$reset = $isWindows ? '' : "\033[0m";

echo "\n";
echo "{$blue}========================================{$reset}\n";
echo "{$blue}  ZKTeco Device Connection Test{$reset}\n";
echo "{$blue}========================================{$reset}\n";
echo "{$cyan}Device IP: {$ip}{$reset}\n";
echo "{$cyan}Device Port: {$port}{$reset}\n";
echo "{$cyan}Comm Key: {$commKey}{$reset}\n";
echo "{$cyan}Device ID: {$deviceId}{$reset}\n";
echo "{$blue}========================================{$reset}\n\n";

$tests = [];
$passed = 0;
$failed = 0;

/**
 * Test function
 */
function runTest($name, $callback) {
    global $tests, $passed, $failed, $green, $red, $yellow, $reset;
    
    echo "{$yellow}Testing: {$name}...{$reset}";
    
    try {
        $result = $callback();
        if ($result['success']) {
            echo " {$green}✓ PASSED{$reset}\n";
            if (!empty($result['message'])) {
                echo "   {$result['message']}\n";
            }
            $tests[] = ['name' => $name, 'status' => 'PASSED', 'message' => $result['message'] ?? ''];
            $passed++;
        } else {
            echo " {$red}✗ FAILED{$reset}\n";
            echo "   {$red}Error: {$result['message']}{$reset}\n";
            $tests[] = ['name' => $name, 'status' => 'FAILED', 'message' => $result['message']];
            $failed++;
        }
    } catch (\Exception $e) {
        echo " {$red}✗ ERROR{$reset}\n";
        echo "   {$red}Exception: {$e->getMessage()}{$reset}\n";
        $tests[] = ['name' => $name, 'status' => 'ERROR', 'message' => $e->getMessage()];
        $failed++;
    }
    
    echo "\n";
}

// Test 1: Initialize ZKTecoService
runTest('Initialize ZKTecoService', function() use ($ip, $port, $commKey) {
    try {
        $zkteco = new ZKTecoService($ip, $port, $commKey);
        return [
            'success' => true,
            'message' => 'ZKTecoService initialized successfully'
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
});

// Test 2: Connect to device
runTest('Connect to Device', function() use ($ip, $port, $commKey) {
    try {
        $zkteco = new ZKTecoService($ip, $port, $commKey);
        $connected = $zkteco->connect();
        
        if ($connected) {
            return [
                'success' => true,
                'message' => 'Successfully connected to device'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
            ];
        }
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
});

// Test 3: Get device information
runTest('Get Device Information', function() use ($ip, $port, $commKey) {
    try {
        $zkteco = new ZKTecoService($ip, $port, $commKey);
        
        if (!$zkteco->connect()) {
            return [
                'success' => false,
                'message' => 'Cannot connect to device'
            ];
        }
        
        $deviceInfo = $zkteco->getDeviceInfo();
        
        if ($deviceInfo) {
            $info = [];
            foreach ($deviceInfo as $key => $value) {
                $info[] = "{$key}: {$value}";
            }
            return [
                'success' => true,
                'message' => implode(', ', $info)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Could not get device information'
            ];
        }
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
});

// Test 4: Get users from device
runTest('Get Users from Device', function() use ($ip, $port, $commKey) {
    try {
        $zkteco = new ZKTecoService($ip, $port, $commKey);
        
        if (!$zkteco->connect()) {
            return [
                'success' => false,
                'message' => 'Cannot connect to device'
            ];
        }
        
        $users = $zkteco->getUsers();
        $userCount = count($users);
        
        return [
            'success' => true,
            'message' => "Found {$userCount} user(s) on device"
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
});

// Test 5: Test user registration (with test user)
runTest('Test User Registration', function() use ($ip, $port, $commKey) {
    try {
        $zkteco = new ZKTecoService($ip, $port, $commKey);
        
        if (!$zkteco->connect()) {
            return [
                'success' => false,
                'message' => 'Cannot connect to device'
            ];
        }
        
        // Use a test UID that's unlikely to exist (9999)
        $testUid = 9999;
        $testUserid = '9999';
        $testName = 'TEST_USER_' . date('His');
        
        // Check if test user already exists
        $existingUsers = $zkteco->getUsers();
        $userExists = false;
        foreach ($existingUsers as $key => $deviceUser) {
            if ((string)$key === $testUserid || 
                (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $testUid)) {
                $userExists = true;
                // Try to remove it first
                try {
                    $zkteco->removeUser($testUid);
                    sleep(1); // Wait for removal
                } catch (\Exception $e) {
                    // Ignore removal errors
                }
                break;
            }
        }
        
        // Get user count before
        $usersBefore = $zkteco->getUsers();
        $countBefore = count($usersBefore);
        
        // Try to register test user
        $result = $zkteco->registerUser(
            $testUid,
            $testUserid,
            $testName,
            '', // password
            0,  // role
            0   // cardno
        );
        
        if ($result) {
            // Wait a moment
            sleep(2);
            
            // Verify user was added
            $usersAfter = $zkteco->getUsers();
            $countAfter = count($usersAfter);
            
            $userFound = false;
            foreach ($usersAfter as $key => $deviceUser) {
                if ((string)$key === $testUserid || 
                    (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $testUid)) {
                    $userFound = true;
                    break;
                }
            }
            
            if ($userFound || $countAfter > $countBefore) {
                // Clean up - remove test user
                try {
                    $zkteco->removeUser($testUid);
                } catch (\Exception $e) {
                    // Ignore cleanup errors
                }
                
                return [
                    'success' => true,
                    'message' => "Registration successful! User count: {$countBefore} → {$countAfter}"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Registration command sent but user not found. Count: {$countBefore} → {$countAfter}"
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Registration returned false - device rejected the command'
            ];
        }
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
});

// Test 6: Test authentication (Comm Key verification)
runTest('Verify Comm Key (Authentication)', function() use ($ip, $port, $commKey) {
    try {
        $zkteco = new ZKTecoService($ip, $port, $commKey);
        
        if (!$zkteco->connect()) {
            return [
                'success' => false,
                'message' => 'Cannot connect to device'
            ];
        }
        
        // Try to get device info - this requires authentication
        $deviceInfo = $zkteco->getDeviceInfo();
        
        // Try to get users - this also requires authentication
        $users = $zkteco->getUsers();
        
        return [
            'success' => true,
            'message' => "Comm Key {$commKey} is correct. Can read device data."
        ];
    } catch (\Exception $e) {
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, '2005') !== false) {
            return [
                'success' => false,
                'message' => "Comm Key {$commKey} is WRONG. Check device settings: System → Communication → Comm Key"
            ];
        }
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
});

// Test 7: Test device enable (using reflection to access private method)
runTest('Test Device Enable', function() use ($ip, $port, $commKey) {
    try {
        $zkteco = new ZKTecoService($ip, $port, $commKey);
        
        if (!$zkteco->connect()) {
            return [
                'success' => false,
                'message' => 'Cannot connect to device'
            ];
        }
        
        // Use reflection to access private getClient method
        $reflection = new \ReflectionClass($zkteco);
        $getClientMethod = $reflection->getMethod('getClient');
        $getClientMethod->setAccessible(true);
        $client = $getClientMethod->invoke($zkteco);
        
        $enableResult = $client->enableDevice();
        
        return [
            'success' => true,
            'message' => 'Device enable command executed successfully'
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
});

// Print summary
echo "\n";
echo "{$blue}========================================{$reset}\n";
echo "{$blue}  Test Summary{$reset}\n";
echo "{$blue}========================================{$reset}\n";
echo "{$green}Passed: {$passed}{$reset}\n";
echo "{$red}Failed: {$failed}{$reset}\n";
echo "Total: " . ($passed + $failed) . "\n";
echo "\n";

// Print detailed results
if (!empty($tests)) {
    echo "{$blue}Detailed Results:{$reset}\n";
    echo "{$blue}----------------------------------------{$reset}\n";
    foreach ($tests as $test) {
        $statusColor = $test['status'] === 'PASSED' ? $green : $red;
        echo "{$statusColor}[{$test['status']}]{$reset} {$test['name']}\n";
        if (!empty($test['message'])) {
            echo "   {$test['message']}\n";
        }
    }
    echo "\n";
}

// Final recommendations
if ($failed > 0) {
    echo "{$yellow}Recommendations:{$reset}\n";
    echo "{$yellow}----------------------------------------{$reset}\n";
    
    $hasConnectionIssue = false;
    $hasAuthIssue = false;
    $hasRegistrationIssue = false;
    
    foreach ($tests as $test) {
        if ($test['status'] !== 'PASSED') {
            if (strpos($test['name'], 'Connect') !== false) {
                $hasConnectionIssue = true;
            }
            if (strpos($test['name'], 'Comm Key') !== false || strpos($test['name'], 'Authentication') !== false) {
                $hasAuthIssue = true;
            }
            if (strpos($test['name'], 'Registration') !== false) {
                $hasRegistrationIssue = true;
            }
        }
    }
    
    if ($hasConnectionIssue) {
        echo "• Check device IP and port are correct\n";
        echo "• Ensure device is powered on\n";
        echo "• Check network connectivity\n";
        echo "• Check firewall settings (UDP port 4370)\n";
    }
    
    if ($hasAuthIssue) {
        echo "• Check Comm Key on device: System → Communication → Comm Key\n";
        echo "• Ensure ZKTECO_PASSWORD in .env matches device Comm Key\n";
        echo "• Restart server after changing .env\n";
    }
    
    if ($hasRegistrationIssue) {
        echo "• Try restarting the device\n";
        echo "• Check if device memory is full\n";
        echo "• Verify device is not in locked mode\n";
        echo "• Try manual registration on device, then sync\n";
    }
    
    echo "\n";
}

echo "{$blue}========================================{$reset}\n";
echo "Test completed!\n";
echo "\n";

