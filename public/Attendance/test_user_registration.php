<?php

/**
 * Test User Registration Script
 * Tests registration for a specific user (ID 93, Enroll ID 4546)
 * 
 * Usage: php test_user_registration.php [ip] [port]
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ZKTecoService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

// Get parameters from command line or use defaults
$ip = $argv[1] ?? '192.168.100.100';
$port = isset($argv[2]) ? (int)$argv[2] : 4370;
$userId = 93; // User ID from the system
$commKey = 0;

// Colors for output
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$green = $isWindows ? '' : "\033[0;32m";
$red = $isWindows ? '' : "\033[0;31m";
$yellow = $isWindows ? '' : "\033[1;33m";
$blue = $isWindows ? '' : "\033[0;34m";
$cyan = $isWindows ? '' : "\033[0;36m";
$reset = $isWindows ? '' : "\033[0m";

echo "\n";
echo "{$blue}========================================{$reset}\n";
echo "{$blue}  User Registration Diagnostic Test{$reset}\n";
echo "{$blue}========================================{$reset}\n";

// Get user from database
try {
    $user = User::find($userId);
    
    if (!$user) {
        echo "{$red}✗ User ID {$userId} not found in database{$reset}\n";
        exit(1);
    }
    
    echo "{$cyan}User Information:{$reset}\n";
    echo "  ID: {$user->id}\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Enroll ID: {$user->enroll_id}\n";
    echo "  Registered on Device: " . ($user->registered_on_device ? 'Yes' : 'No') . "\n";
    echo "\n";
    
    echo "{$cyan}Device Information:{$reset}\n";
    echo "  IP: {$ip}\n";
    echo "  Port: {$port}\n";
    echo "  Comm Key: {$commKey}\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "{$red}✗ Error loading user: {$e->getMessage()}{$reset}\n";
    exit(1);
}

// Test 1: Connect to device
echo "{$yellow}[Test 1] Connecting to device...{$reset}\n";
try {
    $zkteco = new ZKTecoService($ip, $port, $commKey);
    
    if (!$zkteco->connect()) {
        echo "{$red}✗ FAILED: Cannot connect to device{$reset}\n";
        echo "  Check: IP, port, network connectivity, firewall\n";
        exit(1);
    }
    echo "{$green}✓ Connected successfully{$reset}\n\n";
} catch (\Exception $e) {
    echo "{$red}✗ FAILED: {$e->getMessage()}{$reset}\n";
    exit(1);
}

// Test 2: Get device info
echo "{$yellow}[Test 2] Getting device information...{$reset}\n";
try {
    $deviceInfo = $zkteco->getDeviceInfo();
    if ($deviceInfo) {
        echo "{$green}✓ Device Info:{$reset}\n";
        foreach ($deviceInfo as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    } else {
        echo "{$yellow}⚠ Could not get device info{$reset}\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "{$yellow}⚠ Could not get device info: {$e->getMessage()}{$reset}\n\n";
}

// Test 3: Check current users on device
echo "{$yellow}[Test 3] Checking users on device...{$reset}\n";
try {
    $deviceUsers = $zkteco->getUsers();
    $userCount = count($deviceUsers);
    echo "{$green}✓ Found {$userCount} user(s) on device{$reset}\n";
    
    // Check if Enroll ID 4546 already exists
    $enrollIdExists = false;
    foreach ($deviceUsers as $key => $deviceUser) {
        $deviceEnrollId = (string)($key ?? $deviceUser['user_id'] ?? $deviceUser['uid'] ?? '');
        if ($deviceEnrollId === (string)$user->enroll_id) {
            $enrollIdExists = true;
            echo "{$yellow}⚠ Enroll ID {$user->enroll_id} already exists on device!{$reset}\n";
            echo "  Array Key: '{$key}'\n";
            echo "  UID: " . ($deviceUser['uid'] ?? 'N/A') . "\n";
            echo "  User ID: " . ($deviceUser['user_id'] ?? 'N/A') . "\n";
            echo "  Name: " . ($deviceUser['name'] ?? 'N/A') . "\n";
            break;
        }
    }
    
    if (!$enrollIdExists) {
        echo "{$green}✓ Enroll ID {$user->enroll_id} is available{$reset}\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "{$red}✗ FAILED: {$e->getMessage()}{$reset}\n\n";
}

// Test 4: Validate Enroll ID
echo "{$yellow}[Test 4] Validating Enroll ID...{$reset}\n";
$enrollId = $user->enroll_id;
$uid = (int)$enrollId;

if (!is_numeric($enrollId)) {
    echo "{$red}✗ FAILED: Enroll ID '{$enrollId}' is not numeric{$reset}\n";
    exit(1);
}

if ($uid < 1 || $uid > 65535) {
    echo "{$red}✗ FAILED: Enroll ID {$uid} is out of range (must be 1-65535){$reset}\n";
    exit(1);
}

echo "{$green}✓ Enroll ID is valid: {$uid}{$reset}\n";
echo "  UID: {$uid}\n";
echo "  UserID: {$enrollId}\n";
echo "  Name: {$user->name}\n";
echo "\n";

// Test 5: Attempt registration
if ($enrollIdExists) {
    echo "{$yellow}[Test 5] Skipping registration (Enroll ID already exists){$reset}\n";
    echo "{$yellow}  Recommendation: Remove existing user from device or use different Enroll ID{$reset}\n\n";
} else {
    echo "{$yellow}[Test 5] Attempting user registration...{$reset}\n";
    echo "  UID: {$uid}\n";
    echo "  UserID: {$enrollId}\n";
    echo "  Name: {$user->name}\n";
    echo "  Password: (empty)\n";
    echo "  Role: 0 (user)\n";
    echo "  CardNo: 0\n";
    echo "\n";
    
    try {
        // Get user count before
        $usersBefore = $zkteco->getUsers();
        $countBefore = count($usersBefore);
        echo "  Users before registration: {$countBefore}\n";
        
        // Attempt registration
        $result = $zkteco->registerUser(
            $uid,
            $enrollId,
            $user->name,
            '', // password
            0,  // role
            0   // cardno
        );
        
        if ($result) {
            echo "{$green}✓ Registration command returned success{$reset}\n";
            
            // Wait and verify
            echo "  Waiting 3 seconds for device to process...\n";
            sleep(3);
            
            $usersAfter = $zkteco->getUsers();
            $countAfter = count($usersAfter);
            echo "  Users after registration: {$countAfter}\n";
            
            // Check if user was added
            $userFound = false;
            $foundBy = null;
            
            foreach ($usersAfter as $key => $deviceUser) {
                if ((string)$key === (string)$enrollId) {
                    $userFound = true;
                    $foundBy = "array key (userid: '{$key}')";
                    break;
                }
                if (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === $uid) {
                    $userFound = true;
                    $foundBy = "UID ({$uid})";
                    break;
                }
                if (isset($deviceUser['user_id']) && (string)$deviceUser['user_id'] === (string)$enrollId) {
                    $userFound = true;
                    $foundBy = "user_id ({$deviceUser['user_id']})";
                    break;
                }
            }
            
            if ($userFound) {
                echo "{$green}✓ User FOUND on device!{$reset}\n";
                echo "  Found by: {$foundBy}\n";
                echo "  User count increased: " . ($countAfter > $countBefore ? "YES" : "NO") . "\n";
                echo "\n";
                echo "{$blue}========================================{$reset}\n";
                echo "{$green}  ✓ REGISTRATION SUCCESSFUL!{$reset}\n";
                echo "{$blue}========================================{$reset}\n";
            } else {
                echo "{$red}✗ User NOT found on device{$reset}\n";
                echo "  User count increased: " . ($countAfter > $countBefore ? "YES (but user not found)" : "NO") . "\n";
                echo "\n";
                echo "{$blue}========================================{$reset}\n";
                echo "{$red}  ✗ REGISTRATION FAILED{$reset}\n";
                echo "{$blue}========================================{$reset}\n";
                echo "\n";
                echo "{$yellow}Possible reasons:{$reset}\n";
                echo "  1. Device firmware compatibility issue\n";
                echo "  2. Device rejected the registration silently\n";
                echo "  3. Device needs restart\n";
                echo "  4. Device memory full\n";
                echo "\n";
                echo "{$yellow}Check logs: storage/logs/laravel.log{$reset}\n";
            }
        } else {
            echo "{$red}✗ Registration returned false{$reset}\n";
            echo "  Device rejected the registration command\n";
            echo "\n";
            echo "{$yellow}Possible reasons:{$reset}\n";
            echo "  1. Wrong Comm Key\n";
            echo "  2. Device in wrong mode\n";
            echo "  3. Device memory full\n";
            echo "  4. Device needs restart\n";
        }
    } catch (\Exception $e) {
        echo "{$red}✗ Registration failed with error:{$reset}\n";
        echo "  {$e->getMessage()}\n";
        echo "\n";
        echo "{$yellow}Full error details:{$reset}\n";
        echo "  " . str_replace("\n", "\n  ", $e->getTraceAsString()) . "\n";
    }
}

echo "\n";
echo "{$blue}========================================{$reset}\n";
echo "Test completed!\n";
echo "\n";


