# API Test Scripts Guide - Attendance System

## Overview

This guide provides comprehensive test scripts to test all 13 API endpoints of the Attendance System. Multiple script formats are available for different platforms and preferences.

---

## Available Test Scripts

### 1. **test-api.php** (PHP Script) ⭐ Recommended
- **Platform:** Windows, Linux, macOS
- **Requirements:** PHP with cURL extension
- **Usage:** `php test-api.php [base_url]`
- **Features:**
  - Tests all 13 endpoints
  - Color-coded output
  - Detailed test results
  - Windows compatible
  - Automatic test data generation

### 2. **test-api-comprehensive.php** (Comprehensive PHP Script)
- **Platform:** Windows, Linux, macOS
- **Requirements:** PHP with cURL extension
- **Usage:** `php test-api-comprehensive.php [base_url]`
- **Features:**
  - All features of test-api.php
  - Additional error handling tests
  - More detailed validation
  - Extended test coverage

### 3. **test-api.ps1** (PowerShell Script)
- **Platform:** Windows
- **Requirements:** PowerShell 5.1+
- **Usage:** `.\test-api.ps1 [base_url]`
- **Features:**
  - Native Windows support
  - Color-coded output
  - Full endpoint coverage

### 4. **test-api.py** (Python Script)
- **Platform:** Windows, Linux, macOS
- **Requirements:** Python 3.6+ with `requests` library
- **Usage:** `python test-api.py [base_url]`
- **Install:** `pip install requests`
- **Features:**
  - Cross-platform compatibility
  - Clean Python syntax
  - Full endpoint coverage

### 5. **test-api.sh** (Bash Script)
- **Platform:** Linux, macOS
- **Requirements:** Bash, curl, jq (optional)
- **Usage:** `./test-api.sh` or `bash test-api.sh`
- **Features:**
  - Unix/Linux native
  - Simple and fast
  - Basic endpoint coverage

---

## Quick Start

### PHP Script (Recommended)

```bash
# Basic usage (uses default URL)
php test-api.php

# With custom base URL
php test-api.php http://127.0.0.1:8000/api/v1
```

### PowerShell Script (Windows)

```powershell
# Basic usage
.\test-api.ps1

# With custom base URL
.\test-api.ps1 http://127.0.0.1:8000/api/v1
```

### Python Script

```bash
# Install requests library first
pip install requests

# Run the script
python test-api.py

# With custom base URL
python test-api.py http://127.0.0.1:8000/api/v1
```

### Bash Script (Linux/macOS)

```bash
# Make executable
chmod +x test-api.sh

# Run
./test-api.sh
```

---

## Test Coverage

All scripts test the following **13 endpoints**:

### User Management (7 endpoints)
1. ✅ **POST** `/users/register` - Register User
2. ✅ **GET** `/users/{id}` - Get User by ID
3. ✅ **GET** `/users/enroll/{enrollId}` - Get User by Enroll ID
4. ✅ **GET** `/users` - List Users
5. ✅ **PUT** `/users/{id}` - Update User
6. ✅ **DELETE** `/users/{id}` - Delete User (comprehensive script only)
7. ✅ **POST** `/users/{id}/register-device` - Register User to Device

### Attendance (3 endpoints)
8. ✅ **GET** `/attendances` - Get Attendances
9. ✅ **GET** `/attendances/{id}` - Get Attendance by ID (comprehensive script only)
10. ✅ **GET** `/attendances/daily/{date}` - Get Daily Attendance Summary

### Webhooks (3 endpoints)
11. ✅ **POST** `/webhook/configure` - Configure Webhook
12. ✅ **GET** `/webhook/config` - Get Webhook Configuration
13. ✅ **POST** `/webhook/test` - Test Webhook

---

## Expected Output

### Successful Test Run

```
========================================
  Attendance System API Testing
  Base URL: http://127.0.0.1:8000/api/v1
========================================

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  USER MANAGEMENT ENDPOINTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Testing: 1. Register User
  → POST http://127.0.0.1:8000/api/v1/users/register
  ✓ PASSED (HTTP 201)
  → Response: success = true
  → Test User ID: 1
  → Test Enroll ID: 1234

...

========================================
  TEST SUMMARY
========================================

Total Tests: 13
Passed: 13
Failed: 0

========================================
✓ ALL TESTS PASSED!
API is ready for use.
```

### Failed Test Example

```
Testing: 1. Register User
  → POST http://127.0.0.1:8000/api/v1/users/register
  ✗ FAILED (HTTP 422, expected 201)
  → Error: The email has already been taken.

========================================
✗ SOME TESTS FAILED
Please review the failed tests above.
```

---

## Test Script Details

### test-api.php

**Features:**
- ✅ Tests all 13 core endpoints
- ✅ Automatic unique test data generation
- ✅ Color-coded output (Windows compatible)
- ✅ Detailed test summary
- ✅ Exit codes for CI/CD integration

**Test Flow:**
1. Registers a test user with unique email/enroll_id
2. Tests user retrieval endpoints
3. Tests user listing and filtering
4. Tests user update
5. Tests device registration
6. Tests attendance endpoints
7. Tests webhook configuration and testing

**Example Usage:**
```bash
# Run with default URL
php test-api.php

# Run with custom URL
php test-api.php http://localhost:8000/api/v1

# Check exit code
php test-api.php && echo "All tests passed!" || echo "Some tests failed"
```

---

## Configuration

### Base URL

All scripts support custom base URLs:

- **Default:** `http://127.0.0.1:8000/api/v1`
- **Custom:** Pass as first argument/parameter

### Test Data

Scripts automatically generate unique test data:
- **Email:** `apitest{timestamp}@example.com`
- **Enroll ID:** Random number between 1000-9999
- **User ID:** Extracted from registration response

---

## Troubleshooting

### Common Issues

#### 1. Connection Refused
```
✗ FAILED: Connection refused
```
**Solution:** Make sure Laravel server is running:
```bash
php artisan serve
```

#### 2. cURL Not Available (PHP)
```
Fatal error: Call to undefined function curl_init()
```
**Solution:** Enable cURL extension in PHP:
- Windows: Uncomment `extension=curl` in `php.ini`
- Linux: `sudo apt-get install php-curl`
- macOS: Usually pre-installed

#### 3. PowerShell Execution Policy (Windows)
```
.\test-api.ps1 : File cannot be loaded because running scripts is disabled
```
**Solution:** Run PowerShell as Administrator and execute:
```powershell
Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
```

#### 4. Python Requests Not Found
```
ModuleNotFoundError: No module named 'requests'
```
**Solution:** Install requests:
```bash
pip install requests
```

#### 5. jq Not Found (Bash)
```
jq: command not found
```
**Solution:** Install jq (optional, for JSON formatting):
- Ubuntu/Debian: `sudo apt-get install jq`
- macOS: `brew install jq`
- Or remove `| jq '.'` from script

---

## Integration with CI/CD

### GitHub Actions Example

```yaml
name: API Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: curl
    
    - name: Run API Tests
      run: php test-api.php http://localhost:8000/api/v1
      env:
        APP_ENV: testing
```

### GitLab CI Example

```yaml
test_api:
  image: php:8.1-cli
  script:
    - php test-api.php http://localhost:8000/api/v1
```

---

## Comparison with Postman

| Feature | Test Scripts | Postman |
|---------|-------------|---------|
| **Automation** | ✅ Fully automated | ⚠️ Manual/Collection Runner |
| **CI/CD Integration** | ✅ Easy | ⚠️ Requires Newman |
| **Setup Time** | ✅ Quick (1 command) | ⚠️ Manual setup |
| **Visual Interface** | ❌ CLI only | ✅ GUI |
| **Debugging** | ⚠️ CLI output | ✅ Visual debugging |
| **Test Data Management** | ✅ Automatic | ⚠️ Manual variables |
| **Bulk Testing** | ✅ Built-in | ✅ Collection Runner |

**Recommendation:**
- Use **test scripts** for automated testing, CI/CD, and quick validation
- Use **Postman** for manual testing, debugging, and API exploration

---

## Best Practices

1. **Run tests before deployment**
   ```bash
   php test-api.php && echo "Ready to deploy!"
   ```

2. **Use in CI/CD pipeline**
   - Automate API testing on every commit
   - Fail builds if tests don't pass

3. **Test with different base URLs**
   ```bash
   # Development
   php test-api.php http://dev.example.com/api/v1
   
   # Staging
   php test-api.php http://staging.example.com/api/v1
   
   # Production (be careful!)
   php test-api.php http://api.example.com/api/v1
   ```

4. **Combine with Postman**
   - Use scripts for automation
   - Use Postman for manual testing and debugging

---

## Files Created

- ✅ `test-api.php` - Main PHP test script (recommended)
- ✅ `test-api-comprehensive.php` - Extended PHP test script
- ✅ `test-api.ps1` - PowerShell script for Windows
- ✅ `test-api.py` - Python script for cross-platform
- ✅ `test-api.sh` - Bash script for Linux/macOS
- ✅ `API_TEST_SCRIPTS_GUIDE.md` - This guide

---

## Quick Reference

### All Endpoints Tested

| # | Method | Endpoint | Tested By |
|---|--------|----------|-----------|
| 1 | POST | `/users/register` | ✅ All scripts |
| 2 | GET | `/users/{id}` | ✅ All scripts |
| 3 | GET | `/users/enroll/{enrollId}` | ✅ All scripts |
| 4 | GET | `/users` | ✅ All scripts |
| 5 | PUT | `/users/{id}` | ✅ All scripts |
| 6 | DELETE | `/users/{id}` | ✅ Comprehensive only |
| 7 | POST | `/users/{id}/register-device` | ✅ All scripts |
| 8 | GET | `/attendances` | ✅ All scripts |
| 9 | GET | `/attendances/{id}` | ✅ Comprehensive only |
| 10 | GET | `/attendances/daily/{date}` | ✅ All scripts |
| 11 | POST | `/webhook/configure` | ✅ All scripts |
| 12 | GET | `/webhook/config` | ✅ All scripts |
| 13 | POST | `/webhook/test` | ✅ All scripts |

---

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review the Postman guide for endpoint details
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify API server is running: `php artisan serve`

---

**Ready to test!** Start with `php test-api.php` to verify all endpoints are working correctly.


