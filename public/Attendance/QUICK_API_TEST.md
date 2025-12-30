# Quick API Testing Guide

## Fast Testing (5 Minutes)

### Step 1: Test User Registration

Open terminal and run:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "enroll_id": "9999",
    "auto_register_device": true
  }'
```

**✅ Expected:** JSON response with `"success": true` and user data

### Step 2: Test Get User

```bash
curl http://127.0.0.1:8000/api/v1/users/1
```

**✅ Expected:** JSON response with user details

### Step 3: Test List Users

```bash
curl http://127.0.0.1:8000/api/v1/users
```

**✅ Expected:** JSON array of users with pagination

### Step 4: Test Webhook Configuration

1. Go to https://webhook.site and copy your unique URL
2. Run:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/webhook/configure \
  -H "Content-Type: application/json" \
  -d '{
    "webhook_url": "https://webhook.site/YOUR-UNIQUE-ID"
  }'
```

**✅ Expected:** `"success": true`

### Step 5: Test Webhook

```bash
curl -X POST http://127.0.0.1:8000/api/v1/webhook/test
```

**✅ Expected:** Check webhook.site - you should see a test request!

### Step 6: Test Real Webhook (Optional)

1. Scan a user's fingerprint on the device
2. Check webhook.site - you should see attendance data!

---

## Automated Testing

### Option 1: Run PHP Test Script

```bash
php test-api.php
```

### Option 2: Run Bash Test Script (Linux/Mac)

```bash
chmod +x test-api.sh
./test-api.sh
```

---

## Using Postman

1. **Import Collection:**
   - Create new collection
   - Add environment variable: `base_url = http://127.0.0.1:8000`

2. **Add Requests:**
   - `POST {{base_url}}/api/v1/users/register`
   - `GET {{base_url}}/api/v1/users/1`
   - `GET {{base_url}}/api/v1/users`
   - `POST {{base_url}}/api/v1/webhook/configure`
   - `POST {{base_url}}/api/v1/webhook/test`

3. **Set Headers:**
   - `Content-Type: application/json`
   - `Accept: application/json`

4. **Run Collection**

---

## Common Issues

**404 Not Found:**
- Check server is running: `php artisan serve`
- Check route exists: `php artisan route:list | grep api`

**422 Validation Error:**
- Check request body format
- Check required fields are present

**500 Error:**
- Check logs: `tail -f storage/logs/laravel.log`

---

## Full Testing Guide

For complete testing instructions, see: **`API_TESTING_GUIDE.md`**


