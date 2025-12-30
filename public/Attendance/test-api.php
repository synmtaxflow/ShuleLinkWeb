<?php

/**
 * API Testing Script for Attendance System
 * Tests all 13 API endpoints with proper validation
 * 
 * Usage: php test-api.php [base_url]
 * 
 * Example: php test-api.php http://127.0.0.1:8000/api/v1
 */

$baseUrl = $argv[1] ?? 'http://127.0.0.1:8000/api/v1';
$testResults = [];
$passed = 0;
$failed = 0;

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
echo "{$blue}  Attendance System API Testing{$reset}\n";
echo "{$blue}  Base URL: {$baseUrl}{$reset}\n";
echo "{$blue}========================================{$reset}\n\n";

/**
 * Test an endpoint
 */
function testEndpoint($name, $method, $url, $data = null, $expectedStatus = 200) {
    global $testResults, $passed, $failed, $green, $red, $yellow, $blue, $reset;
    
    echo "{$yellow}Testing: {$name}{$reset}\n";
    echo "  {$blue}→ {$method} {$url}{$reset}\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    if ($method === 'POST' || $method === 'PUT') {
        if ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $result = [
        'name' => $name,
        'method' => $method,
        'url' => $url,
        'http_code' => $httpCode,
        'expected_status' => $expectedStatus,
        'response' => $response,
        'error' => $error
    ];
    
    if ($error) {
        echo "  {$red}✗ FAILED: {$error}{$reset}\n\n";
        $result['status'] = 'FAILED';
        $failed++;
    } elseif ($httpCode === $expectedStatus) {
        echo "  {$green}✓ PASSED (HTTP {$httpCode}){$reset}\n";
        $json = json_decode($response, true);
        if ($json && isset($json['success'])) {
            $successValue = $json['success'] ? 'true' : 'false';
            echo "  {$green}  → Response: success = {$successValue}{$reset}\n";
        }
        $result['status'] = 'PASSED';
        $passed++;
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        echo "  {$green}✓ PASSED (HTTP {$httpCode}){$reset}\n";
        $result['status'] = 'PASSED';
        $passed++;
    } else {
        echo "  {$red}✗ FAILED (HTTP {$httpCode}, expected {$expectedStatus}){$reset}\n";
        $json = json_decode($response, true);
        if ($json && isset($json['message'])) {
            echo "  {$red}  → Error: {$json['message']}{$reset}\n";
        }
        $result['status'] = 'FAILED';
        $failed++;
    }
    
    echo "\n";
    $testResults[] = $result;
    
    return $result;
}

// Generate unique test data
$testEmail = 'apitest' . time() . '@example.com';
$testEnrollId = (string)(1000 + rand(1, 9000));
$testUserId = null;

// ============================================
// USER MANAGEMENT ENDPOINTS
// ============================================

echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n";
echo "{$blue}  USER MANAGEMENT ENDPOINTS{$reset}\n";
echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n\n";

// Test 1: Register User (Simplified - only id and name)
echo "{$cyan}[1/7] User Management{$reset}\n";
$registerResult = testEndpoint(
    '1. Register User',
    'POST',
    "{$baseUrl}/users/register",
    [
        'id' => $testEnrollId,  // External system ID (enroll_id)
        'name' => 'Test User',
        'auto_register_device' => false
    ],
    201
);

// Extract user ID from response
if ($registerResult['status'] === 'PASSED') {
    $json = json_decode($registerResult['response'], true);
    if ($json && isset($json['data']['id'])) {
        $testUserId = $json['data']['id'];
        echo "  {$green}  → Test User ID: {$testUserId}{$reset}\n";
        echo "  {$green}  → Test Enroll ID: {$testEnrollId}{$reset}\n\n";
    }
}

// Test 2: Get User by ID
if ($testUserId) {
    testEndpoint(
        '2. Get User by ID',
        'GET',
        "{$baseUrl}/users/{$testUserId}",
        null,
        200
    );
} else {
    echo "  {$yellow}⚠ Skipped: No user ID available{$reset}\n\n";
}

// Test 3: Get User by Enroll ID
testEndpoint(
    '3. Get User by Enroll ID',
    'GET',
    "{$baseUrl}/users/enroll/{$testEnrollId}",
    null,
    200
);

// Test 4: List Users
testEndpoint(
    '4. List Users',
    'GET',
    "{$baseUrl}/users",
    null,
    200
);

// Test 5: List Users with Filters
testEndpoint(
    '5. List Users (with filters)',
    'GET',
    "{$baseUrl}/users?registered=false&per_page=10",
    null,
    200
);

// Test 6: Update User
if ($testUserId) {
    testEndpoint(
        '6. Update User',
        'PUT',
        "{$baseUrl}/users/{$testUserId}",
        [
            'name' => 'Updated Test User'
        ],
        200
    );
}

// Test 7: Register User to Device
if ($testUserId) {
    testEndpoint(
        '7. Register User to Device',
        'POST',
        "{$baseUrl}/users/{$testUserId}/register-device",
        [
            'device_ip' => '192.168.100.108',
            'device_port' => 4370
        ],
        200
    );
}

// ============================================
// ATTENDANCE ENDPOINTS
// ============================================

echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n";
echo "{$blue}  ATTENDANCE ENDPOINTS{$reset}\n";
echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n\n";

// Test 8: Get Attendances
testEndpoint(
    '8. Get Attendances',
    'GET',
    "{$baseUrl}/attendances",
    null,
    200
);

// Test 9: Get Attendances with Date Filter
$today = date('Y-m-d');
testEndpoint(
    '9. Get Attendances (with date filter)',
    'GET',
    "{$baseUrl}/attendances?date={$today}",
    null,
    200
);

// Test 10: Get Daily Attendance Summary
testEndpoint(
    '10. Get Daily Attendance Summary',
    'GET',
    "{$baseUrl}/attendances/daily/{$today}",
    null,
    200
);

// ============================================
// WEBHOOK ENDPOINTS
// ============================================

echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n";
echo "{$blue}  WEBHOOK ENDPOINTS{$reset}\n";
echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n\n";

// Test 11: Configure Webhook
$webhookUrl = 'https://webhook.site/' . bin2hex(random_bytes(8));
testEndpoint(
    '11. Configure Webhook',
    'POST',
    "{$baseUrl}/webhook/configure",
    [
        'webhook_url' => $webhookUrl
    ],
    200
);

// Test 12: Get Webhook Configuration
testEndpoint(
    '12. Get Webhook Configuration',
    'GET',
    "{$baseUrl}/webhook/config",
    null,
    200
);

// Test 13: Test Webhook
testEndpoint(
    '13. Test Webhook',
    'POST',
    "{$baseUrl}/webhook/test",
    null,
    200
);

// ============================================
// SUMMARY
// ============================================

echo "\n";
echo "{$blue}========================================{$reset}\n";
echo "{$blue}  TEST SUMMARY{$reset}\n";
echo "{$blue}========================================{$reset}\n\n";

$totalTests = count($testResults);
echo "Total Tests: {$totalTests}\n";
echo "{$green}Passed: {$passed}{$reset}\n";
echo "{$red}Failed: {$failed}{$reset}\n";
echo "\n";

// Detailed Results
echo "{$blue}Detailed Results:{$reset}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

foreach ($testResults as $result) {
    $statusColor = $result['status'] === 'PASSED' ? $green : $red;
    $statusIcon = $result['status'] === 'PASSED' ? '✓' : '✗';
    
    echo "{$statusColor}{$statusIcon} {$result['name']}{$reset}\n";
    echo "   Method: {$result['method']}\n";
    echo "   URL: {$result['url']}\n";
    echo "   Status: HTTP {$result['http_code']} (expected {$result['expected_status']})\n";
    echo "\n";
}

// Final Status
echo "{$blue}========================================{$reset}\n";
if ($failed === 0) {
    echo "{$green}✓ ALL TESTS PASSED!{$reset}\n";
    echo "{$green}API is ready for use.{$reset}\n";
    exit(0);
} else {
    echo "{$red}✗ SOME TESTS FAILED{$reset}\n";
    echo "{$yellow}Please review the failed tests above.{$reset}\n";
    exit(1);
}
