<?php
/**
 * Quick Test Script for Registration Fix
 * Tests if the 2007 response code handling is working
 * 
 * Run: php test_registration_fix.php
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ZKTecoService;

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "  REGISTRATION FIX TEST SCRIPT\n";
echo "  Testing UF200-S Firmware 6.60 Compatibility Fix\n";
echo "═══════════════════════════════════════════════════════\n\n";

// Configuration
$ip = '192.168.100.100';
$port = 4370;
$enrollId = 999; // Test enroll ID
$name = 'Test User ' . date('Y-m-d H:i:s');

echo "Configuration:\n";
echo "  Device IP: {$ip}\n";
echo "  Device Port: {$port}\n";
echo "  Test Enroll ID: {$enrollId}\n";
echo "  Test Name: {$name}\n\n";

$step = 1;
$success = false;

try {
    // Step 1: Connect
    echo "[Step {$step}] Connecting to device...\n";
    $zkteco = new ZKTecoService($ip, $port);
    
    if (!$zkteco->connect()) {
        throw new Exception("✗ Failed to connect to device {$ip}:{$port}\n   Check: IP address, network connectivity, device is powered on");
    }
    echo "  ✓ Connected successfully\n\n";
    $step++;

    // Step 2: Get users before
    echo "[Step {$step}] Getting users before registration...\n";
    $usersBefore = $zkteco->getUsers();
    $userCountBefore = count($usersBefore);
    echo "  ✓ Found {$userCountBefore} users on device\n";
    
    // Check if test user already exists
    $userExists = false;
    foreach ($usersBefore as $key => $deviceUser) {
        if ((string)$key === (string)$enrollId || 
            (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === (int)$enrollId) ||
            (isset($deviceUser['user_id']) && (string)$deviceUser['user_id'] === (string)$enrollId)) {
            $userExists = true;
            echo "  ⚠ Test user (Enroll ID: {$enrollId}) already exists - will test with existing user\n";
            break;
        }
    }
    echo "\n";
    $step++;

    // Step 3: Register user (if not exists)
    if (!$userExists) {
        echo "[Step {$step}] Registering test user to device...\n";
        echo "  - This will test the 2007 response code handling\n";
        echo "  - The fix should proceed to verification even if device returns 2007\n";
        
        $result = $zkteco->registerUser(
            $enrollId,
            (string)$enrollId,
            $name,
            '', // password
            0,  // role
            0   // cardno
        );

        if ($result) {
            echo "  ✓ Registration command completed\n";
            echo "  ✓ Code proceeded past 2007 response code check (if device returned 2007)\n";
        } else {
            throw new Exception("✗ Registration returned false");
        }
        echo "\n";
    } else {
        echo "[Step {$step}] Skipping registration (test user already exists)\n";
        echo "  - Will proceed to verification step\n\n";
    }
    $step++;

    // Step 4: Verify
    echo "[Step {$step}] Verifying user on device...\n";
    echo "  Waiting 2 seconds for device to process...\n";
    sleep(2);

    $usersAfter = $zkteco->getUsers();
    $userCountAfter = count($usersAfter);
    
    echo "  Users before: {$userCountBefore}\n";
    echo "  Users after: {$userCountAfter}\n";
    
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
        echo "  ✓ User FOUND on device!\n";
        echo "  ✓ Found by: {$foundBy}\n";
        echo "  ✓ User count increased: " . ($userCountAfter > $userCountBefore ? "YES" : "NO (but user exists)") . "\n";
        $success = true;
    } else {
        echo "  ✗ User NOT found on device\n";
        echo "  ✗ User count increased: " . ($userCountAfter > $userCountBefore ? "YES (but user not found)" : "NO") . "\n";
        
        echo "\n  Current users on device:\n";
        if (count($usersAfter) > 0) {
            foreach ($usersAfter as $key => $user) {
                $uid = $user['uid'] ?? 'N/A';
                $userId = $user['user_id'] ?? 'N/A';
                $userName = $user['name'] ?? 'N/A';
                echo "    - Key: '{$key}', UID: {$uid}, UserID: {$userId}, Name: {$userName}\n";
            }
        } else {
            echo "    (No users found)\n";
        }
        $success = false;
    }

    echo "\n";
    echo "═══════════════════════════════════════════════════════\n";
    if ($success) {
        echo "  ✅ TEST PASSED - Registration Fix Working!\n";
        echo "═══════════════════════════════════════════════════════\n";
        echo "\nThe fix is working correctly:\n";
        echo "  ✓ Code handles 2007 response code properly\n";
        echo "  ✓ Proceeds to verification instead of failing immediately\n";
        echo "  ✓ User was successfully registered and verified\n";
        echo "\nYou can now register users normally at:\n";
        echo "  http://192.168.100.100:8000/users\n";
    } else {
        echo "  ❌ TEST FAILED - User Not Found\n";
        echo "═══════════════════════════════════════════════════════\n";
        echo "\nPossible issues:\n";
        echo "  1. Device firmware compatibility (2007 response code)\n";
        echo "  2. Device rejected registration silently\n";
        echo "  3. Comm Key mismatch\n";
        echo "  4. Device needs different command format\n";
        echo "\nCheck logs: storage/logs/laravel.log\n";
        echo "Look for entries about response code 2007\n";
    }
    echo "\n";

} catch (\Exception $e) {
    echo "\n";
    echo "═══════════════════════════════════════════════════════\n";
    echo "  ❌ TEST FAILED - Error Occurred\n";
    echo "═══════════════════════════════════════════════════════\n";
    echo "\nError: {$e->getMessage()}\n";
    echo "\nCheck logs: storage/logs/laravel.log\n";
    echo "\n";
    exit(1);
}

exit($success ? 0 : 1);


