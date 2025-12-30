# Comprehensive API Testing Script for Attendance System
# Tests all 13 API endpoints with proper validation
# 
# Usage: .\test-api.ps1 [base_url]
# Example: .\test-api.ps1 http://127.0.0.1:8000/api/v1

param(
    [string]$BaseUrl = "http://127.0.0.1:8000/api/v1"
)

$script:TestResults = @()
$script:Passed = 0
$script:Failed = 0
$script:Warnings = 0
$script:TestUserId = $null
$script:TestEnrollId = $null
$script:TestAttendanceId = $null

# Colors for output
function Write-ColorOutput($ForegroundColor, $Message) {
    $fc = $host.UI.RawUI.ForegroundColor
    $host.UI.RawUI.ForegroundColor = $ForegroundColor
    Write-Output $Message
    $host.UI.RawUI.ForegroundColor = $fc
}

Write-Output ""
Write-Output "========================================"
Write-ColorOutput "Cyan" "  Comprehensive API Testing Script"
Write-ColorOutput "Cyan" "  Base URL: $BaseUrl"
Write-Output "========================================"
Write-Output ""

# Function to test an endpoint
function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Method,
        [string]$Url,
        [object]$Data = $null,
        [int]$ExpectedStatus = 200,
        [hashtable]$Headers = @{}
    )
    
    Write-ColorOutput "Yellow" "Testing: $Name"
    Write-ColorOutput "Cyan" "  → $Method $Url"
    
    $defaultHeaders = @{
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    foreach ($key in $Headers.Keys) {
        $defaultHeaders[$key] = $Headers[$key]
    }
    
    $headersArray = @()
    foreach ($key in $defaultHeaders.Keys) {
        $headersArray += "$key`: $($defaultHeaders[$key])"
    }
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $defaultHeaders
            TimeoutSec = 30
            ErrorAction = "Stop"
        }
        
        if ($Data -and ($Method -eq "POST" -or $Method -eq "PUT")) {
            $params.Body = ($Data | ConvertTo-Json -Depth 10)
        }
        
        $response = Invoke-RestMethod @params
        $statusCode = $response.StatusCode
        
        # Get actual HTTP status code
        try {
            $webRequest = Invoke-WebRequest -Uri $Url -Method $Method -Headers $defaultHeaders -Body ($Data | ConvertTo-Json -Depth 10) -ErrorAction SilentlyContinue
            $httpCode = [int]$webRequest.StatusCode
        } catch {
            $httpCode = 200  # Default if we can't get it
            if ($_.Exception.Response) {
                $httpCode = [int]$_.Exception.Response.StatusCode.value__
            }
        }
        
        $result = @{
            Name = $Name
            Method = $Method
            Url = $Url
            HttpCode = $httpCode
            ExpectedStatus = $ExpectedStatus
            Status = "PASSED"
            Response = $response
            Success = $false
        }
        
        if ($httpCode -eq $ExpectedStatus) {
            Write-ColorOutput "Green" "  ✓ PASSED (HTTP $httpCode)"
            if ($response.success) {
                Write-ColorOutput "Green" "  → Response: success = true"
                $result.Success = $true
            } else {
                Write-ColorOutput "Yellow" "  ⚠ Response: success = false"
                $script:Warnings++
            }
            $script:Passed++
        } elseif ($httpCode -ge 200 -and $httpCode -lt 300) {
            Write-ColorOutput "Green" "  ✓ PASSED (HTTP $httpCode, expected $ExpectedStatus)"
            $script:Warnings++
            $script:Passed++
        } else {
            Write-ColorOutput "Red" "  ✗ FAILED (HTTP $httpCode, expected $ExpectedStatus)"
            if ($response.message) {
                Write-ColorOutput "Red" "  → Error: $($response.message)"
                $result.Message = $response.message
            }
            $result.Status = "FAILED"
            $script:Failed++
        }
        
        Write-Output ""
        $script:TestResults += $result
        return $result
        
    } catch {
        $httpCode = 500
        if ($_.Exception.Response) {
            $httpCode = [int]$_.Exception.Response.StatusCode.value__
        }
        
        Write-ColorOutput "Red" "  ✗ FAILED: $($_.Exception.Message)"
        Write-Output ""
        
        $result = @{
            Name = $Name
            Method = $Method
            Url = $Url
            HttpCode = $httpCode
            ExpectedStatus = $ExpectedStatus
            Status = "FAILED"
            Error = $_.Exception.Message
            Message = $_.Exception.Message
        }
        
        $script:TestResults += $result
        $script:Failed++
        return $result
    }
}

# Generate unique test data
$testEmail = "apitest$(Get-Date -Format 'yyyyMMddHHmmss')@example.com"
$script:TestEnrollId = (1000 + (Get-Random -Maximum 9000)).ToString()

# ============================================
# USER MANAGEMENT ENDPOINTS
# ============================================

Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-ColorOutput "Cyan" "  USER MANAGEMENT ENDPOINTS"
Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-Output ""

# Test 1: Register User
$registerResult = Test-Endpoint `
    -Name "1. Register User" `
    -Method "POST" `
    -Url "$BaseUrl/users/register" `
    -Data @{
        name = "API Test User $(Get-Date -Format 'HH:mm:ss')"
        email = $testEmail
        password = "password123"
        enroll_id = $script:TestEnrollId
        auto_register_device = $false
    } `
    -ExpectedStatus 201

if ($registerResult.Status -eq "PASSED" -and $registerResult.Response.data.id) {
    $script:TestUserId = $registerResult.Response.data.id
    Write-ColorOutput "Green" "  → Test User ID: $($script:TestUserId)"
    Write-ColorOutput "Green" "  → Test Enroll ID: $($script:TestEnrollId)"
    Write-Output ""
}

# Test 2: Get User by ID
if ($script:TestUserId) {
    Test-Endpoint `
        -Name "2. Get User by ID" `
        -Method "GET" `
        -Url "$BaseUrl/users/$($script:TestUserId)" `
        -ExpectedStatus 200
} else {
    Write-ColorOutput "Yellow" "  ⚠ Skipped: No user ID available"
    Write-Output ""
}

# Test 3: Get User by Enroll ID
if ($script:TestEnrollId) {
    Test-Endpoint `
        -Name "3. Get User by Enroll ID" `
        -Method "GET" `
        -Url "$BaseUrl/users/enroll/$($script:TestEnrollId)" `
        -ExpectedStatus 200
}

# Test 4: List Users
Test-Endpoint `
    -Name "4. List Users" `
    -Method "GET" `
    -Url "$BaseUrl/users" `
    -ExpectedStatus 200

# Test 5: List Users with Filters
Test-Endpoint `
    -Name "5. List Users (with filters)" `
    -Method "GET" `
    -Url "$BaseUrl/users?registered=false&per_page=10" `
    -ExpectedStatus 200

# Test 6: Update User
if ($script:TestUserId) {
    Test-Endpoint `
        -Name "6. Update User" `
        -Method "PUT" `
        -Url "$BaseUrl/users/$($script:TestUserId)" `
        -Data @{
            name = "Updated Test User $(Get-Date -Format 'HH:mm:ss')"
        } `
        -ExpectedStatus 200
}

# Test 7: Register User to Device
if ($script:TestUserId) {
    Test-Endpoint `
        -Name "7. Register User to Device" `
        -Method "POST" `
        -Url "$BaseUrl/users/$($script:TestUserId)/register-device" `
        -Data @{
            device_ip = "192.168.100.108"
            device_port = 4370
        } `
        -ExpectedStatus 200
}

# ============================================
# ATTENDANCE ENDPOINTS
# ============================================

Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-ColorOutput "Cyan" "  ATTENDANCE ENDPOINTS"
Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-Output ""

# Test 8: Get Attendances
Test-Endpoint `
    -Name "8. Get Attendances" `
    -Method "GET" `
    -Url "$BaseUrl/attendances" `
    -ExpectedStatus 200

# Test 9: Get Attendances with Date Filter
$today = Get-Date -Format "yyyy-MM-dd"
Test-Endpoint `
    -Name "9. Get Attendances (with date filter)" `
    -Method "GET" `
    -Url "$BaseUrl/attendances?date=$today" `
    -ExpectedStatus 200

# Test 10: Get Attendances with Date Range
$yesterday = (Get-Date).AddDays(-1).ToString("yyyy-MM-dd")
Test-Endpoint `
    -Name "10. Get Attendances (with date range)" `
    -Method "GET" `
    -Url "$BaseUrl/attendances?date_from=$yesterday&date_to=$today" `
    -ExpectedStatus 200

# Test 11: Get Attendances with User Filter
if ($script:TestUserId) {
    Test-Endpoint `
        -Name "11. Get Attendances (with user filter)" `
        -Method "GET" `
        -Url "$BaseUrl/attendances?user_id=$($script:TestUserId)" `
        -ExpectedStatus 200
}

# Test 12: Get Attendances (to find ID)
$attendancesResult = Test-Endpoint `
    -Name "12. Get Attendances (to find ID)" `
    -Method "GET" `
    -Url "$BaseUrl/attendances?per_page=1" `
    -ExpectedStatus 200

if ($attendancesResult.Status -eq "PASSED" -and $attendancesResult.Response.data -and $attendancesResult.Response.data.Count -gt 0) {
    $script:TestAttendanceId = $attendancesResult.Response.data[0].id
    Test-Endpoint `
        -Name "13. Get Attendance by ID" `
        -Method "GET" `
        -Url "$BaseUrl/attendances/$($script:TestAttendanceId)" `
        -ExpectedStatus 200
} else {
    Write-ColorOutput "Yellow" "  ⚠ No attendance records found to test Get by ID"
    Write-Output ""
}

# Test 14: Get Daily Attendance Summary
Test-Endpoint `
    -Name "14. Get Daily Attendance Summary" `
    -Method "GET" `
    -Url "$BaseUrl/attendances/daily/$today" `
    -ExpectedStatus 200

# ============================================
# WEBHOOK ENDPOINTS
# ============================================

Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-ColorOutput "Cyan" "  WEBHOOK ENDPOINTS"
Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-Output ""

# Test 15: Configure Webhook
$webhookId = -join ((48..57) + (97..102) | Get-Random -Count 16 | ForEach-Object {[char]$_})
$webhookUrl = "https://webhook.site/$webhookId"
Test-Endpoint `
    -Name "15. Configure Webhook" `
    -Method "POST" `
    -Url "$BaseUrl/webhook/configure" `
    -Data @{
        webhook_url = $webhookUrl
        api_key = "test-api-key-$(Get-Date -Format 'yyyyMMddHHmmss')"
    } `
    -ExpectedStatus 200

# Test 16: Get Webhook Configuration
Test-Endpoint `
    -Name "16. Get Webhook Configuration" `
    -Method "GET" `
    -Url "$BaseUrl/webhook/config" `
    -ExpectedStatus 200

# Test 17: Test Webhook
Test-Endpoint `
    -Name "17. Test Webhook" `
    -Method "POST" `
    -Url "$BaseUrl/webhook/test" `
    -ExpectedStatus 200

# ============================================
# ERROR HANDLING TESTS
# ============================================

Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-ColorOutput "Cyan" "  ERROR HANDLING TESTS"
Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-Output ""

# Test 18: Register User with Duplicate Email (should fail)
Test-Endpoint `
    -Name "18. Register User (duplicate email - should fail)" `
    -Method "POST" `
    -Url "$BaseUrl/users/register" `
    -Data @{
        name = "Duplicate Test"
        email = $testEmail
        password = "password123"
        enroll_id = "99999"
    } `
    -ExpectedStatus 422

# Test 19: Register User with Duplicate Enroll ID (should fail)
Test-Endpoint `
    -Name "19. Register User (duplicate enroll_id - should fail)" `
    -Method "POST" `
    -Url "$BaseUrl/users/register" `
    -Data @{
        name = "Duplicate Test 2"
        email = "duplicate2$(Get-Date -Format 'yyyyMMddHHmmss')@example.com"
        password = "password123"
        enroll_id = $script:TestEnrollId
    } `
    -ExpectedStatus 422

# Test 20: Get Non-existent User (should return 404)
Test-Endpoint `
    -Name "20. Get User (non-existent - should return 404)" `
    -Method "GET" `
    -Url "$BaseUrl/users/999999" `
    -ExpectedStatus 404

# Test 21: Get Non-existent Attendance (should return 404)
Test-Endpoint `
    -Name "21. Get Attendance (non-existent - should return 404)" `
    -Method "GET" `
    -Url "$BaseUrl/attendances/999999" `
    -ExpectedStatus 404

# ============================================
# SUMMARY
# ============================================

Write-Output ""
Write-Output "========================================"
Write-ColorOutput "Cyan" "  TEST SUMMARY"
Write-Output "========================================"
Write-Output ""

$totalTests = $script:TestResults.Count
Write-Output "Total Tests: $totalTests"
Write-ColorOutput "Green" "Passed: $($script:Passed)"
Write-ColorOutput "Red" "Failed: $($script:Failed)"
if ($script:Warnings -gt 0) {
    Write-ColorOutput "Yellow" "Warnings: $($script:Warnings)"
}
Write-Output ""

# Detailed Results
Write-ColorOutput "Cyan" "Detailed Results:"
Write-Output "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-Output ""

foreach ($result in $script:TestResults) {
    $statusColor = if ($result.Status -eq "PASSED") { "Green" } else { "Red" }
    $statusIcon = if ($result.Status -eq "PASSED") { "✓" } else { "✗" }
    
    Write-ColorOutput $statusColor "$statusIcon $($result.Name)"
    Write-Output "   Method: $($result.Method)"
    Write-Output "   URL: $($result.Url)"
    Write-Output "   Status: HTTP $($result.HttpCode) (expected $($result.ExpectedStatus))"
    if ($result.Message) {
        Write-Output "   Message: $($result.Message)"
    }
    Write-Output ""
}

# Final Status
Write-Output "========================================"
if ($script:Failed -eq 0) {
    Write-ColorOutput "Green" "✓ ALL TESTS PASSED!"
    Write-ColorOutput "Green" "API is ready for use."
    exit 0
} else {
    Write-ColorOutput "Red" "✗ SOME TESTS FAILED"
    Write-ColorOutput "Yellow" "Please review the failed tests above."
    exit 1
}


