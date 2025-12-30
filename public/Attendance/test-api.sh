#!/bin/bash

# API Testing Script for Attendance System
# Usage: ./test-api.sh

BASE_URL="http://127.0.0.1:8000/api/v1"

echo "=========================================="
echo "Attendance System API Testing Script"
echo "=========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counter
PASSED=0
FAILED=0

# Function to test endpoint
test_endpoint() {
    local name=$1
    local method=$2
    local url=$3
    local data=$4
    
    echo -e "${YELLOW}Testing: $name${NC}"
    
    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "\n%{http_code}" "$url")
    else
        response=$(curl -s -w "\n%{http_code}" -X "$method" "$url" \
            -H "Content-Type: application/json" \
            -d "$data")
    fi
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$http_code" -ge 200 ] && [ "$http_code" -lt 300 ]; then
        echo -e "${GREEN}✓ PASSED (HTTP $http_code)${NC}"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
        ((PASSED++))
    else
        echo -e "${RED}✗ FAILED (HTTP $http_code)${NC}"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
        ((FAILED++))
    fi
    echo ""
}

echo "1. Testing User Registration..."
test_endpoint "Register User" "POST" "$BASE_URL/users/register" '{
    "name": "API Test User",
    "email": "apitest@example.com",
    "password": "password123",
    "enroll_id": "8888",
    "auto_register_device": true
}'

echo "2. Testing Get User by ID..."
test_endpoint "Get User" "GET" "$BASE_URL/users/1" ""

echo "3. Testing Get User by Enroll ID..."
test_endpoint "Get User by Enroll ID" "GET" "$BASE_URL/users/enroll/8888" ""

echo "4. Testing List Users..."
test_endpoint "List Users" "GET" "$BASE_URL/users" ""

echo "5. Testing List Users with Filter..."
test_endpoint "List Users (Registered)" "GET" "$BASE_URL/users?registered=true" ""

echo "6. Testing Get Attendances..."
test_endpoint "Get Attendances" "GET" "$BASE_URL/attendances" ""

echo "7. Testing Get Daily Attendance..."
TODAY=$(date +%Y-%m-%d)
test_endpoint "Get Daily Attendance" "GET" "$BASE_URL/attendances/daily/$TODAY" ""

echo "8. Testing Configure Webhook..."
test_endpoint "Configure Webhook" "POST" "$BASE_URL/webhook/configure" '{
    "webhook_url": "https://webhook.site/test",
    "api_key": "test-key"
}'

echo "9. Testing Get Webhook Config..."
test_endpoint "Get Webhook Config" "GET" "$BASE_URL/webhook/config" ""

echo "10. Testing Webhook Test..."
test_endpoint "Test Webhook" "POST" "$BASE_URL/webhook/test" ""

echo "=========================================="
echo "Test Summary"
echo "=========================================="
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo "=========================================="

if [ $FAILED -eq 0 ]; then
    exit 0
else
    exit 1
fi

