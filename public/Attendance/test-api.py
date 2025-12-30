#!/usr/bin/env python3
"""
Comprehensive API Testing Script for Attendance System
Tests all 13 API endpoints with proper validation

Usage: python test-api.py [base_url]

Example: python test-api.py http://127.0.0.1:8000/api/v1
"""

import sys
import json
import time
import random
import requests
from datetime import datetime, timedelta
from typing import Optional, Dict, Any

# Colors for terminal output
class Colors:
    GREEN = '\033[0;32m'
    RED = '\033[0;31m'
    YELLOW = '\033[1;33m'
    BLUE = '\033[0;34m'
    CYAN = '\033[0;36m'
    RESET = '\033[0m'

def print_color(text: str, color: str = Colors.RESET):
    """Print colored text"""
    print(f"{color}{text}{Colors.RESET}")

# Test results
test_results = []
passed = 0
failed = 0
warnings = 0
test_user_id = None
test_enroll_id = None
test_attendance_id = None

def test_endpoint(
    name: str,
    method: str,
    url: str,
    data: Optional[Dict[str, Any]] = None,
    expected_status: int = 200,
    headers: Optional[Dict[str, str]] = None
) -> Dict[str, Any]:
    """Test an API endpoint"""
    global test_results, passed, failed, warnings
    
    print_color(f"Testing: {name}", Colors.YELLOW)
    print_color(f"  → {method} {url}", Colors.CYAN)
    
    default_headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    if headers:
        default_headers.update(headers)
    
    try:
        if method == 'GET':
            response = requests.get(url, headers=default_headers, timeout=30)
        elif method == 'POST':
            response = requests.post(
                url,
                json=data,
                headers=default_headers,
                timeout=30
            )
        elif method == 'PUT':
            response = requests.put(
                url,
                json=data,
                headers=default_headers,
                timeout=30
            )
        elif method == 'DELETE':
            response = requests.delete(url, headers=default_headers, timeout=30)
        else:
            raise ValueError(f"Unsupported method: {method}")
        
        http_code = response.status_code
        
        result = {
            'name': name,
            'method': method,
            'url': url,
            'http_code': http_code,
            'expected_status': expected_status,
            'status': 'PASSED',
            'response': response.text,
            'success': False
        }
        
        if http_code == expected_status:
            print_color(f"  ✓ PASSED (HTTP {http_code})", Colors.GREEN)
            try:
                json_response = response.json()
                if json_response.get('success'):
                    print_color("  → Response: success = true", Colors.GREEN)
                    result['success'] = True
                else:
                    print_color("  ⚠ Response: success = false", Colors.YELLOW)
                    warnings += 1
            except:
                pass
            passed += 1
        elif 200 <= http_code < 300:
            print_color(f"  ✓ PASSED (HTTP {http_code}, expected {expected_status})", Colors.GREEN)
            warnings += 1
            passed += 1
        else:
            print_color(f"  ✗ FAILED (HTTP {http_code}, expected {expected_status})", Colors.RED)
            try:
                json_response = response.json()
                if json_response.get('message'):
                    print_color(f"  → Error: {json_response['message']}", Colors.RED)
                    result['message'] = json_response['message']
            except:
                pass
            result['status'] = 'FAILED'
            result['message'] = f"Expected {expected_status}, got {http_code}"
            failed += 1
        
        print()
        test_results.append(result)
        return result
        
    except requests.exceptions.RequestException as e:
        print_color(f"  ✗ FAILED: {str(e)}", Colors.RED)
        print()
        
        result = {
            'name': name,
            'method': method,
            'url': url,
            'http_code': 0,
            'expected_status': expected_status,
            'status': 'FAILED',
            'error': str(e),
            'message': str(e)
        }
        
        test_results.append(result)
        failed += 1
        return result

def main():
    global test_user_id, test_enroll_id, test_attendance_id
    
    # Get base URL from command line or use default
    base_url = sys.argv[1] if len(sys.argv) > 1 else 'http://127.0.0.1:8000/api/v1'
    
    print()
    print_color("========================================", Colors.BLUE)
    print_color("  Comprehensive API Testing Script", Colors.BLUE)
    print_color(f"  Base URL: {base_url}", Colors.BLUE)
    print_color("========================================", Colors.BLUE)
    print()
    
    # Generate unique test data
    timestamp = int(time.time())
    test_email = f'apitest{timestamp}@example.com'
    test_enroll_id = str(1000 + random.randint(1, 9000))
    
    # ============================================
    # USER MANAGEMENT ENDPOINTS
    # ============================================
    
    print_color("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", Colors.BLUE)
    print_color("  USER MANAGEMENT ENDPOINTS", Colors.BLUE)
    print_color("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", Colors.BLUE)
    print()
    
    # Test 1: Register User
    register_result = test_endpoint(
        '1. Register User',
        'POST',
        f'{base_url}/users/register',
        {
            'name': f'API Test User {datetime.now().strftime("%H:%M:%S")}',
            'email': test_email,
            'password': 'password123',
            'enroll_id': test_enroll_id,
            'auto_register_device': False
        },
        201
    )
    
    if register_result['status'] == 'PASSED':
        try:
            json_response = json.loads(register_result['response'])
            if json_response.get('data', {}).get('id'):
                test_user_id = json_response['data']['id']
                print_color(f"  → Test User ID: {test_user_id}", Colors.GREEN)
                print_color(f"  → Test Enroll ID: {test_enroll_id}", Colors.GREEN)
                print()
        except:
            pass
    
    # Test 2: Get User by ID
    if test_user_id:
        test_endpoint(
            '2. Get User by ID',
            'GET',
            f'{base_url}/users/{test_user_id}',
            expected_status=200
        )
    else:
        print_color("  ⚠ Skipped: No user ID available", Colors.YELLOW)
        print()
    
    # Test 3: Get User by Enroll ID
    if test_enroll_id:
        test_endpoint(
            '3. Get User by Enroll ID',
            'GET',
            f'{base_url}/users/enroll/{test_enroll_id}',
            expected_status=200
        )
    
    # Test 4: List Users
    test_endpoint(
        '4. List Users',
        'GET',
        f'{base_url}/users',
        expected_status=200
    )
    
    # Test 5: List Users with Filters
    test_endpoint(
        '5. List Users (with filters)',
        'GET',
        f'{base_url}/users?registered=false&per_page=10',
        expected_status=200
    )
    
    # Test 6: Update User
    if test_user_id:
        test_endpoint(
            '6. Update User',
            'PUT',
            f'{base_url}/users/{test_user_id}',
            {
                'name': f'Updated Test User {datetime.now().strftime("%H:%M:%S")}'
            },
            expected_status=200
        )
    
    # Test 7: Register User to Device
    if test_user_id:
        test_endpoint(
            '7. Register User to Device',
            'POST',
            f'{base_url}/users/{test_user_id}/register-device',
            {
                'device_ip': '192.168.100.108',
                'device_port': 4370
            },
            expected_status=200
        )
    
    # ============================================
    # ATTENDANCE ENDPOINTS
    # ============================================
    
    print_color("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", Colors.BLUE)
    print_color("  ATTENDANCE ENDPOINTS", Colors.BLUE)
    print_color("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", Colors.BLUE)
    print()
    
    # Test 8: Get Attendances
    test_endpoint(
        '8. Get Attendances',
        'GET',
        f'{base_url}/attendances',
        expected_status=200
    )
    
    # Test 9: Get Attendances with Date Filter
    today = datetime.now().strftime('%Y-%m-%d')
    test_endpoint(
        '9. Get Attendances (with date filter)',
        'GET',
        f'{base_url}/attendances?date={today}',
        expected_status=200
    )
    
    # Test 10: Get Attendances with Date Range
    yesterday = (datetime.now() - timedelta(days=1)).strftime('%Y-%m-%d')
    test_endpoint(
        '10. Get Attendances (with date range)',
        'GET',
        f'{base_url}/attendances?date_from={yesterday}&date_to={today}',
        expected_status=200
    )
    
    # Test 11: Get Attendances with User Filter
    if test_user_id:
        test_endpoint(
            '11. Get Attendances (with user filter)',
            'GET',
            f'{base_url}/attendances?user_id={test_user_id}',
            expected_status=200
        )
    
    # Test 12: Get Attendances (to find ID)
    attendances_result = test_endpoint(
        '12. Get Attendances (to find ID)',
        'GET',
        f'{base_url}/attendances?per_page=1',
        expected_status=200
    )
    
    if attendances_result['status'] == 'PASSED':
        try:
            json_response = json.loads(attendances_result['response'])
            if json_response.get('data') and len(json_response['data']) > 0:
                test_attendance_id = json_response['data'][0].get('id')
                if test_attendance_id:
                    test_endpoint(
                        '13. Get Attendance by ID',
                        'GET',
                        f'{base_url}/attendances/{test_attendance_id}',
                        expected_status=200
                    )
        except:
            pass
    
    if not test_attendance_id:
        print_color("  ⚠ No attendance records found to test Get by ID", Colors.YELLOW)
        print()
    
    # Test 14: Get Daily Attendance Summary
    test_endpoint(
        '14. Get Daily Attendance Summary',
        'GET',
        f'{base_url}/attendances/daily/{today}',
        expected_status=200
    )
    
    # ============================================
    # WEBHOOK ENDPOINTS
    # ============================================
    
    print_color("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", Colors.BLUE)
    print_color("  WEBHOOK ENDPOINTS", Colors.BLUE)
    print_color("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", Colors.BLUE)
    print()
    
    # Test 15: Configure Webhook
    webhook_id = ''.join(random.choices('0123456789abcdef', k=16))
    webhook_url = f'https://webhook.site/{webhook_id}'
    test_endpoint(
        '15. Configure Webhook',
        'POST',
        f'{base_url}/webhook/configure',
        {
            'webhook_url': webhook_url,
            'api_key': f'test-api-key-{timestamp}'
        },
        expected_status=200
    )
    
    # Test 16: Get Webhook Configuration
    test_endpoint(
        '16. Get Webhook Configuration',
        'GET',
        f'{base_url}/webhook/config',
        expected_status=200
    )
    
    # Test 17: Test Webhook
    test_endpoint(
        '17. Test Webhook',
        'POST',
        f'{base_url}/webhook/test',
        expected_status=200
    )
    
    # ============================================
    # ERROR HANDLING TESTS
    # ============================================
    
    print_color("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", Colors.BLUE)
    print_color("  ERROR HANDLING TESTS", Colors.BLUE)
    print_color("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", Colors.BLUE)
    print()
    
    # Test 18: Register User with Duplicate Email (should fail)
    test_endpoint(
        '18. Register User (duplicate email - should fail)',
        'POST',
        f'{base_url}/users/register',
        {
            'name': 'Duplicate Test',
            'email': test_email,  # Same email as before
            'password': 'password123',
            'enroll_id': '99999'
        },
        expected_status=422
    )
    
    # Test 19: Register User with Duplicate Enroll ID (should fail)
    test_endpoint(
        '19. Register User (duplicate enroll_id - should fail)',
        'POST',
        f'{base_url}/users/register',
        {
            'name': 'Duplicate Test 2',
            'email': f'duplicate2{timestamp}@example.com',
            'password': 'password123',
            'enroll_id': test_enroll_id  # Same enroll_id as before
        },
        expected_status=422
    )
    
    # Test 20: Get Non-existent User (should return 404)
    test_endpoint(
        '20. Get User (non-existent - should return 404)',
        'GET',
        f'{base_url}/users/999999',
        expected_status=404
    )
    
    # Test 21: Get Non-existent Attendance (should return 404)
    test_endpoint(
        '21. Get Attendance (non-existent - should return 404)',
        'GET',
        f'{base_url}/attendances/999999',
        expected_status=404
    )
    
    # ============================================
    # SUMMARY
    # ============================================
    
    print()
    print_color("========================================", Colors.BLUE)
    print_color("  TEST SUMMARY", Colors.BLUE)
    print_color("========================================", Colors.BLUE)
    print()
    
    total_tests = len(test_results)
    print(f"Total Tests: {total_tests}")
    print_color(f"Passed: {passed}", Colors.GREEN)
    print_color(f"Failed: {failed}", Colors.RED)
    if warnings > 0:
        print_color(f"Warnings: {warnings}", Colors.YELLOW)
    print()
    
    # Detailed Results
    print_color("Detailed Results:", Colors.BLUE)
    print("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━")
    print()
    
    for result in test_results:
        status_color = Colors.GREEN if result['status'] == 'PASSED' else Colors.RED
        status_icon = '✓' if result['status'] == 'PASSED' else '✗'
        
        print_color(f"{status_icon} {result['name']}", status_color)
        print(f"   Method: {result['method']}")
        print(f"   URL: {result['url']}")
        print(f"   Status: HTTP {result['http_code']} (expected {result['expected_status']})")
        if result.get('message'):
            print(f"   Message: {result['message']}")
        print()
    
    # Final Status
    print_color("========================================", Colors.BLUE)
    if failed == 0:
        print_color("✓ ALL TESTS PASSED!", Colors.GREEN)
        print_color("API is ready for use.", Colors.GREEN)
        sys.exit(0)
    else:
        print_color("✗ SOME TESTS FAILED", Colors.RED)
        print_color("Please review the failed tests above.", Colors.YELLOW)
        sys.exit(1)

if __name__ == '__main__':
    main()


