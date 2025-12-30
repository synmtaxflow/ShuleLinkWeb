<?php
/**
 * Standalone Test Script for User Registration
 * 
 * This script tests the user registration functionality with UF200-S firmware 6.60
 * Run from command line: php test_registration.php
 * 
 * Usage:
 *   php test_registration.php [IP] [PORT] [ENROLL_ID] [NAME]
 * 
 * Example:
 *   php test_registration.php 192.168.100.100 4370 999 "Test User"
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ZKTecoService;
use Illuminate\Support\Facades\Log;

// Get parameters from command line or use defaults
$ip = $argv[1] ?? '192.168.100.100';
$port = (int)($argv[2] ?? 4370);
$enrollId = (int)($argv[3] ?? 999);
$name = $argv[4] ?? 'Test User ' . time();

echo "\n";
echo "========================================\n";
echo "  USER REGISTRATION TEST SCRIPT\n";
echo "========================================\n";
echo "Device IP: {$ip}\n";
echo "Device Port: {$port}\n";
echo "Enroll ID: {$enrollId}\n";
echo "Name: {$name}\n";
echo "========================================\n\n";

$step = 1;

try {
    // Step 1: Connect
    echo "[Step {$step}] Connecting to device...\n";
    $zkteco = new ZKTecoService($ip, $port);
    
    if (!$zkteco->connect()) {
        throw new Exception("Failed to connect to device {$ip}:{$port}");
    }
    echo "✓ Connected successfully\n\n";
    $step++;

    // Step 2: Get device info
    echo "[Step {$step}] Getting device information...\n";
    try {
        $deviceInfo = $zkteco->getDeviceInfo();
        echo "✓ Device Info:\n";
        foreach ($deviceInfo as $key => $value) {
            echo "  - {$key}: {$value}\n";
        }
    } catch (\Exception $e) {
        echo "⚠ Could not get device info: {$e->getMessage()}\n";
    }
    echo "\n";
    $step++;

    // Step 3: Get users before
    echo "[Step {$step}] Getting users before registration...\n";
    $usersBefore = $zkteco->getUsers();
    $userCountBefore = count($usersBefore);
    echo "✓ Found {$userCountBefore} users on device\n";
    
    // Check if user exists
    $userExists = false;
    foreach ($usersBefore as $key => $deviceUser) {
        if ((string)$key === (string)$enrollId || 
            (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === (int)$enrollId) ||
            (isset($deviceUser['user_id']) && (string)$deviceUser['user_id'] === (string)$enrollId)) {
            $userExists = true;
            echo "⚠ User with Enroll ID {$enrollId} already exists on device\n";
            break;
        }
    }
    echo "\n";
    $step++;

    // Step 4: Register user
    if (!$userExists) {
        echo "[Step {$step}] Registering user to device...\n";
        echo "  - UID: {$enrollId}\n";
        echo "  - UserID: {$enrollId}\n";
        echo "  - Name: {$name}\n";
        echo "  - Password: (empty)\n";
        echo "  - Role: 0 (user)\n";
        echo "  - CardNo: 0\n";
        
        $result = $zkteco->registerUser(
            $enrollId,
            (string)$enrollId,
            $name,
            '', // password
            0,  // role
            0   // cardno
        );

        if ($result) {
            echo "✓ Registration command sent successfully\n";
        } else {
            throw new Exception("Registration returned false");
        }
        echo "\n";
    } else {
        echo "[Step {$step}] Skipping registration (user already exists)\n\n";
    }
    $step++;

    // Step 5: Verify
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
        echo "✓ User FOUND on device!\n";
        echo "  Found by: {$foundBy}\n";
        echo "  User count increased: " . ($userCountAfter > $userCountBefore ? "YES ✓" : "NO ✗") . "\n";
        echo "\n";
        echo "========================================\n";
        echo "  ✓ TEST PASSED - Registration Successful!\n";
        echo "========================================\n";
    } else {
        echo "✗ User NOT found on device\n";
        echo "  User count increased: " . ($userCountAfter > $userCountBefore ? "YES (but user not found)" : "NO") . "\n";
        echo "\n";
        echo "Current users on device:\n";
        foreach ($usersAfter as $key => $user) {
            $uid = $user['uid'] ?? 'N/A';
            $userId = $user['user_id'] ?? 'N/A';
            $userName = $user['name'] ?? 'N/A';
            echo "  - Key: '{$key}', UID: {$uid}, UserID: {$userId}, Name: {$userName}\n";
        }
        echo "\n";
        echo "========================================\n";
        echo "  ✗ TEST FAILED - User Not Found\n";
        echo "========================================\n";
        echo "\n";
        echo "Possible reasons:\n";
        echo "  1. Device firmware compatibility issue (2007 response code)\n";
        echo "  2. Device rejected the registration silently\n";
        echo "  3. Comm Key mismatch\n";
        echo "  4. Device needs different command format\n";
        echo "\n";
        echo "Check logs: storage/logs/laravel.log\n";
        exit(1);
    }

} catch (\Exception $e) {
    echo "\n";
    echo "========================================\n";
    echo "  ✗ TEST FAILED - Error Occurred\n";
    echo "========================================\n";
    echo "Error: {$e->getMessage()}\n";
    echo "\n";
    echo "Check logs: storage/logs/laravel.log\n";
    exit(1);
}

echo "\n";


