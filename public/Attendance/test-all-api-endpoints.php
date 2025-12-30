<?php

/**
 * Comprehensive API Testing Script
 * Tests all API endpoints before release to developers
 * 
 * Usage: php test-all-api-endpoints.php
 */

$baseUrl = 'http://127.0.0.1:8000/api/v1'; // Laravel adds 'api' prefix automatically
$testResults = [];
$passed = 0;
$failed = 0;
$warnings = 0;

// Colors for output
$green = "\033[0;32m";
$red = "\033[0;31m";
$yellow = "\033[1;33m";
$blue = "\033[0;34m";
$reset = "\033[0m";

echo "\n";
echo "{$blue}========================================{$reset}\n";
echo "{$blue}  API Endpoint Testing - Pre-Release{$reset}\n";
echo "{$blue}========================================{$reset}\n\n";

/**
 * Test an endpoint
 */
function testEndpoint($name, $method, $url, $data = null, $expectedStatus = 200) {
    global $testResults, $passed, $failed, $warnings, $green, $red, $yellow, $blue, $reset;
    
    echo "{$yellow}Testing: {$name}{$reset}\n";
    echo "  {$blue}→ {$method} {$url}{$reset}\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POST, true);
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
        $result['message'] = $error;
        $failed++;
    } elseif ($httpCode === $expectedStatus) {
        echo "  {$green}✓ PASSED (HTTP {$httpCode}){$reset}\n";
        $json = json_decode($response, true);
        if ($json && isset($json['success'])) {
            if ($json['success']) {
                echo "  {$green}  → Response: success = true{$reset}\n";
            } else {
                echo "  {$yellow}  ⚠ Response: success = false{$reset}\n";
                $warnings++;
            }
        }
        $result['status'] = 'PASSED';
        $passed++;
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        echo "  {$green}✓ PASSED (HTTP {$httpCode}, expected {$expectedStatus}){$reset}\n";
        $result['status'] = 'PASSED';
        $warnings++;
        $passed++;
    } else {
        echo "  {$red}✗ FAILED (HTTP {$httpCode}, expected {$expectedStatus}){$reset}\n";
        $json = json_decode($response, true);
        if ($json && isset($json['message'])) {
            echo "  {$red}  → Error: {$json['message']}{$reset}\n";
        }
        $result['status'] = 'FAILED';
        $result['message'] = "Expected {$expectedStatus}, got {$httpCode}";
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
$testAttendanceId = null;

// ============================================
// USER MANAGEMENT TESTS
// ============================================

echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n";
echo "{$blue}  USER MANAGEMENT ENDPOINTS{$reset}\n";
echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n\n";

// Test 1: Register User
$registerResult = testEndpoint(
    '1. Register User',
    'POST',
    "{$baseUrl}/users/register",
    [
        'name' => 'API Test User ' . date('H:i:s'),
        'email' => $testEmail,
        'password' => 'password123',
        'enroll_id' => $testEnrollId,
        'auto_register_device' => true
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
if ($testEnrollId) {
    testEndpoint(
        '3. Get User by Enroll ID',
        'GET',
        "{$baseUrl}/users/enroll/{$testEnrollId}",
        null,
        200
    );
}

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
    "{$baseUrl}/users?registered=true&per_page=10",
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
            'device_ip' => config('zkteco.ip', '192.168.100.108'),
            'device_port' => config('zkteco.port', 4370)
        ],
        200
    );
}

// ============================================
// ATTENDANCE TESTS
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

// Test 10: Get Attendances with Date Range
$yesterday = date('Y-m-d', strtotime('-1 day'));
testEndpoint(
    '10. Get Attendances (with date range)',
    'GET',
    "{$baseUrl}/attendances?date_from={$yesterday}&date_to={$today}",
    null,
    200
);

// Test 11: Get Attendances with User Filter
if ($testUserId) {
    testEndpoint(
        '11. Get Attendances (with user filter)',
        'GET',
        "{$baseUrl}/attendances?user_id={$testUserId}",
        null,
        200
    );
}

// Test 12: Get Attendance by ID (if exists)
// Try to get first attendance record
$attendancesResult = testEndpoint(
    '12. Get Attendances (to find ID)',
    'GET',
    "{$baseUrl}/attendances?per_page=1",
    null,
    200
);

if ($attendancesResult['status'] === 'PASSED') {
    $json = json_decode($attendancesResult['response'], true);
    if ($json && isset($json['data'][0]['id'])) {
        $testAttendanceId = $json['data'][0]['id'];
        testEndpoint(
            '13. Get Attendance by ID',
            'GET',
            "{$baseUrl}/attendances/{$testAttendanceId}",
            null,
            200
        );
    } else {
        echo "  {$yellow}⚠ No attendance records found to test Get by ID{$reset}\n\n";
    }
}

// Test 14: Get Daily Attendance Summary
testEndpoint(
    '14. Get Daily Attendance Summary',
    'GET',
    "{$baseUrl}/attendances/daily/{$today}",
    null,
    200
);

// ============================================
// WEBHOOK TESTS
// ============================================

echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n";
echo "{$blue}  WEBHOOK ENDPOINTS{$reset}\n";
echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n\n";

// Test 15: Configure Webhook
$webhookUrl = 'https://webhook.site/' . bin2hex(random_bytes(8));
testEndpoint(
    '15. Configure Webhook',
    'POST',
    "{$baseUrl}/webhook/configure",
    [
        'webhook_url' => $webhookUrl,
        'api_key' => 'test-api-key-' . time()
    ],
    200
);

// Test 16: Get Webhook Configuration
testEndpoint(
    '16. Get Webhook Configuration',
    'GET',
    "{$baseUrl}/webhook/config",
    null,
    200
);

// Test 17: Test Webhook
testEndpoint(
    '17. Test Webhook',
    'POST',
    "{$baseUrl}/webhook/test",
    null,
    200
);

// ============================================
// ERROR HANDLING TESTS
// ============================================

echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n";
echo "{$blue}  ERROR HANDLING TESTS{$reset}\n";
echo "{$blue}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$reset}\n\n";

// Test 18: Register User with Duplicate Email (should fail)
testEndpoint(
    '18. Register User (duplicate email - should fail)',
    'POST',
    "{$baseUrl}/users/register",
    [
        'name' => 'Duplicate Test',
        'email' => $testEmail, // Same email as before
        'password' => 'password123',
        'enroll_id' => '99999'
    ],
    422
);

// Test 19: Register User with Duplicate Enroll ID (should fail)
testEndpoint(
    '19. Register User (duplicate enroll_id - should fail)',
    'POST',
    "{$baseUrl}/users/register",
    [
        'name' => 'Duplicate Test 2',
        'email' => 'duplicate2@example.com',
        'password' => 'password123',
        'enroll_id' => $testEnrollId // Same enroll_id as before
    ],
    422
);

// Test 20: Get Non-existent User (should return 404)
testEndpoint(
    '20. Get User (non-existent - should return 404)',
    'GET',
    "{$baseUrl}/users/999999",
    null,
    404
);

// Test 21: Get Non-existent Attendance (should return 404)
testEndpoint(
    '21. Get Attendance (non-existent - should return 404)',
    'GET',
    "{$baseUrl}/attendances/999999",
    null,
    404
);

// ============================================
// SUMMARY
// ============================================

echo "\n";
echo "{$blue}========================================{$reset}\n";
echo "{$blue}  TEST SUMMARY{$reset}\n";
echo "{$blue}========================================{$reset}\n\n";

echo "Total Tests: " . count($testResults) . "\n";
echo "{$green}Passed: {$passed}{$reset}\n";
echo "{$red}Failed: {$failed}{$reset}\n";
if ($warnings > 0) {
    echo "{$yellow}Warnings: {$warnings}{$reset}\n";
}
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
    if (isset($result['message'])) {
        echo "   Message: {$result['message']}\n";
    }
    echo "\n";
}

// Final Status
echo "{$blue}========================================{$reset}\n";
if ($failed === 0) {
    echo "{$green}✓ ALL TESTS PASSED!{$reset}\n";
    echo "{$green}API is ready for release to developers.{$reset}\n";
    exit(0);
} else {
    echo "{$red}✗ SOME TESTS FAILED{$reset}\n";
    echo "{$yellow}Please fix the issues before releasing to developers.{$reset}\n";
    exit(1);
}

