# Authentication API Documentation

## Base URL
```
http://192.168.100.104:8003/api
```

## Overview
This API uses Laravel session-based authentication. After successful login, a session cookie is returned which must be included in all subsequent API requests.

---

## Endpoints

### 1. Login

**Endpoint:** `POST /login`

**Description:** Authenticates a user (Admin, Teacher, or Parent) and creates a session. Returns user data and session cookie information.

**Authentication:** Not required

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "username": "EMP001",
    "password": "password123"
}
```

**Request Parameters:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `username` | string | Yes | Username (employee_number for Teacher, registration_number for Admin, phone for Parent) |
| `password` | string | Yes | User password |

**Response Format (200 OK):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 123,
            "name": "EMP001",
            "email": "teacher@example.com",
            "user_type": "Teacher"
        },
        "schoolID": 4,
        "teacherID": 456,
        "teacher": {
            "id": 456,
            "first_name": "John",
            "last_name": "Doe",
            "employee_number": "EMP001",
            "full_name": "John Doe"
        },
        "teacher_roles": ["Class Teacher", "Subject Teacher"]
    },
    "session": {
        "cookie_name": "laravel_session",
        "session_id": "abc123def456ghi789...",
        "cookie_value": "abc123def456ghi789...",
        "cookie_header": "laravel_session=abc123def456ghi789..."
    }
}
```

**Response Headers:**
The response will include a `Set-Cookie` header with the session cookie:
```
Set-Cookie: laravel_session=abc123def456ghi789...; path=/; httponly; samesite=lax
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Incorrect username or password!"
}
```

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "username": ["The username field is required."],
        "password": ["The password field is required."]
    }
}
```

**Error Response (429 Too Many Requests):**
```json
{
    "success": false,
    "message": "Too many login attempts. Please try again in 45 seconds."
}
```

---

### 2. Logout

**Endpoint:** `POST /logout`

**Description:** Logs out the current user and invalidates the session.

**Authentication:** Required (Session-based)

**Request Headers:**
```
Accept: application/json
Cookie: laravel_session=<session_token>
```

**Response Format (200 OK):**
```json
{
    "success": true,
    "message": "Logged out successfully."
}
```

---

## User Types and Login Details

### Admin Login
- **Username:** School registration number
- **Password:** Admin account password
- **Session Data Set:**
  - `schoolID`
  - `user_type` = "Admin"
  - `userID`
  - `user_name`
  - `user_email`

### Teacher Login
- **Username:** Employee number (e.g., "EMP001")
- **Password:** Teacher account password
- **Session Data Set:**
  - `schoolID`
  - `teacherID`
  - `user_type` = "Teacher"
  - `userID`
  - `user_name`
  - `user_email`
  - `teacher_name`
  - `teacher_roles` (if Spatie permissions are installed)

### Parent Login
- **Username:** Phone number (e.g., "255614863345")
- **Password:** Parent account password
- **Session Data Set:**
  - `parentID`
  - `schoolID`
  - `user_type` = "parent"

---

## How to Use Session Cookie

### Step 1: Login
Make a POST request to `/api/login` with username and password.

### Step 2: Extract Session Cookie
The response will include:
1. **Set-Cookie Header:** Automatically set by the browser
2. **Session Information in JSON:** For manual cookie handling

### Step 3: Use Session Cookie in Subsequent Requests
Include the session cookie in all API requests:

**Option 1: Automatic (Browser/HTTP Client)**
Most HTTP clients automatically handle cookies from `Set-Cookie` headers.

**Option 2: Manual Cookie Header**
```
Cookie: laravel_session=<session_id>
```

---

## Usage Examples

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:cookie_jar/cookie_jar.dart';
import 'package:dio/dio.dart';
import 'package:dio_cookie_manager/dio_cookie_manager.dart';

class AuthAPI {
  final String baseUrl = 'http://192.168.100.104:8003/api';
  String? sessionCookie;
  
  // Login
  Future<Map<String, dynamic>> login(String username, String password) async {
    try {
      final dio = Dio();
      final cookieJar = CookieJar();
      dio.interceptors.add(CookieManager(cookieJar));
      
      final response = await dio.post(
        '$baseUrl/login',
        data: {
          'username': username,
          'password': password,
        },
        options: Options(
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        ),
      );
      
      if (response.statusCode == 200) {
        final data = response.data;
        
        // Extract session cookie from cookies
        final cookies = cookieJar.loadForRequest(Uri.parse(baseUrl));
        for (var cookie in cookies) {
          if (cookie.name == 'laravel_session') {
            sessionCookie = cookie.value;
            break;
          }
        }
        
        // Or extract from response data
        if (data['session'] != null) {
          sessionCookie = data['session']['cookie_value'];
        }
        
        return data;
      } else {
        throw Exception('Login failed: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error during login: $e');
    }
  }
  
  // Make authenticated request
  Future<Map<String, dynamic>> getAuthenticatedData(String endpoint) async {
    if (sessionCookie == null) {
      throw Exception('Not logged in. Please login first.');
    }
    
    try {
      final response = await http.get(
        Uri.parse('$baseUrl$endpoint'),
        headers: {
          'Accept': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Request failed: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
  
  // Logout
  Future<void> logout() async {
    if (sessionCookie == null) return;
    
    try {
      await http.post(
        Uri.parse('$baseUrl/logout'),
        headers: {
          'Accept': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
      );
      
      sessionCookie = null;
    } catch (e) {
      // Ignore errors on logout
    }
  }
}
```

### JavaScript/React Native Example

```javascript
class AuthAPI {
  constructor() {
    this.baseUrl = 'http://192.168.100.104:8003/api';
    this.sessionCookie = null;
  }
  
  // Login
  async login(username, password) {
    try {
      const response = await fetch(`${this.baseUrl}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'include', // Important: Include cookies
        body: JSON.stringify({
          username: username,
          password: password,
        }),
      });
      
      if (response.ok) {
        const data = await response.json();
        
        // Extract session cookie from Set-Cookie header
        const setCookieHeader = response.headers.get('Set-Cookie');
        if (setCookieHeader) {
          const match = setCookieHeader.match(/laravel_session=([^;]+)/);
          if (match) {
            this.sessionCookie = match[1];
          }
        }
        
        // Or extract from response data
        if (data.session) {
          this.sessionCookie = data.session.cookie_value;
        }
        
        return data;
      } else {
        const error = await response.json();
        throw new Error(error.message || 'Login failed');
      }
    } catch (error) {
      throw new Error(`Login error: ${error.message}`);
    }
  }
  
  // Make authenticated request
  async getAuthenticatedData(endpoint) {
    if (!this.sessionCookie) {
      throw new Error('Not logged in. Please login first.');
    }
    
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Cookie': `laravel_session=${this.sessionCookie}`,
        },
        credentials: 'include',
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        throw new Error(`Request failed: ${response.status}`);
      }
    } catch (error) {
      throw new Error(`Request error: ${error.message}`);
    }
  }
  
  // Logout
  async logout() {
    if (!this.sessionCookie) return;
    
    try {
      await fetch(`${this.baseUrl}/logout`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Cookie': `laravel_session=${this.sessionCookie}`,
        },
        credentials: 'include',
      });
      
      this.sessionCookie = null;
    } catch (error) {
      // Ignore errors on logout
    }
  }
}
```

### cURL Examples

**Login:**
```bash
curl -X POST "http://192.168.100.104:8003/api/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "EMP001",
    "password": "password123"
  }' \
  -c cookies.txt \
  -v
```

**Using Session Cookie:**
```bash
# Extract session cookie from cookies.txt or response
# Then use it in subsequent requests:

curl -X GET "http://192.168.100.104:8003/api/teacher/dashboard" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_ID" \
  -b cookies.txt
```

**Login and Save Cookie:**
```bash
# Login and save cookie to file
curl -X POST "http://192.168.100.104:8003/api/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"EMP001","password":"password123"}' \
  -c cookies.txt

# Use saved cookie for authenticated requests
curl -X GET "http://192.168.100.104:8003/api/teacher/dashboard" \
  -b cookies.txt
```

---

## Session Management

### Session Configuration

Sessions are configured in `config/session.php`:

- **Driver:** Database (default)
- **Lifetime:** 120 minutes (2 hours)
- **Cookie Name:** `laravel_session`
- **Path:** `/`
- **HttpOnly:** `true` (cookie not accessible via JavaScript)
- **SameSite:** `lax`

### Session Data Structure

After login, the following session data is stored:

**For Teacher:**
```php
[
    'schoolID' => 4,
    'teacherID' => 456,
    'user_type' => 'Teacher',
    'userID' => 123,
    'user_name' => 'EMP001',
    'user_email' => 'teacher@example.com',
    'teacher_name' => 'John Doe',
    'teacher_roles' => ['Class Teacher', 'Subject Teacher'] // if Spatie installed
]
```

**For Admin:**
```php
[
    'schoolID' => 4,
    'user_type' => 'Admin',
    'userID' => 123,
    'user_name' => 'SCH001',
    'user_email' => 'admin@example.com'
]
```

**For Parent:**
```php
[
    'parentID' => 789,
    'schoolID' => 4,
    'user_type' => 'parent'
]
```

---

## Security Considerations

1. **Rate Limiting:** Login attempts are rate-limited to 5 attempts per IP address. After 5 failed attempts, the IP is locked for 60 seconds.

2. **Session Regeneration:** Session ID is regenerated after successful login for security.

3. **HttpOnly Cookies:** Session cookies are marked as HttpOnly, preventing JavaScript access (XSS protection).

4. **Password Hashing:** Passwords are hashed using bcrypt before storage.

5. **Session Lifetime:** Sessions expire after 120 minutes of inactivity.

---

## Error Handling

### Common Errors

| Status Code | Error | Description |
|------------|-------|-------------|
| 401 | Unauthorized | Incorrect username or password |
| 422 | Validation Error | Missing or invalid request parameters |
| 429 | Too Many Requests | Too many login attempts |
| 404 | Not Found | User record not found (for specific user types) |
| 400 | Bad Request | Invalid user type |

### Error Response Format
```json
{
    "success": false,
    "message": "Error message description",
    "errors": {
        "field_name": ["Error message for this field"]
    }
}
```

---

## Complete Login Flow Example

### Step-by-Step Flow

1. **User submits login credentials**
   ```json
   POST /api/login
   {
       "username": "EMP001",
       "password": "password123"
   }
   ```

2. **Server validates and creates session**
   - Validates username and password
   - Creates session in database
   - Sets session data
   - Regenerates session ID

3. **Server returns response with session info**
   ```json
   {
       "success": true,
       "message": "Login successful",
       "data": {...},
       "session": {
           "cookie_name": "laravel_session",
           "session_id": "abc123...",
           "cookie_value": "abc123...",
           "cookie_header": "laravel_session=abc123..."
       }
   }
   ```

4. **Client stores session cookie**
   - Extract from `Set-Cookie` header (automatic)
   - Or extract from response JSON (manual)

5. **Client uses session cookie in subsequent requests**
   ```
   GET /api/teacher/dashboard
   Cookie: laravel_session=abc123...
   ```

---

## Testing with Postman

1. **Login Request:**
   - Method: POST
   - URL: `http://192.168.100.104:8003/api/login`
   - Headers:
     - `Content-Type: application/json`
     - `Accept: application/json`
   - Body (raw JSON):
     ```json
     {
         "username": "EMP001",
         "password": "password123"
     }
     ```

2. **Check Response:**
   - Status: 200 OK
   - Response body contains session information
   - Cookies tab shows `laravel_session` cookie

3. **Use Cookie in Next Request:**
   - Postman automatically includes cookies from previous requests
   - Or manually add header:
     ```
     Cookie: laravel_session=<session_id>
     ```

---

## Notes

1. **Session Persistence:** Sessions are stored in the database. Ensure the `sessions` table exists and is properly configured.

2. **Cookie Domain:** By default, cookies are set for the current domain. For cross-domain requests, you may need to configure CORS and cookie settings.

3. **Session Expiry:** Sessions expire after 120 minutes of inactivity. Users need to login again after expiry.

4. **Multiple Devices:** Each device/browser gets its own session. Logging in from multiple devices creates separate sessions.

5. **Logout:** Logout invalidates the session. The session cookie should be cleared on the client side.

---

## Support

For issues or questions regarding authentication, please contact the development team.

**Last Updated:** January 2024
