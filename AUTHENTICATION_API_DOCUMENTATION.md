# Authentication API Documentation

## Base URL
```
http://192.168.100.104:8003/api
```

## Overview
This API uses **stateless authentication**. After successful login, the API returns user data (ID, user_type, schoolID, etc.) which must be stored by the client application and sent with subsequent API requests as headers or parameters.

**Important:** This API does NOT use session-based authentication. The client app is responsible for storing and managing user authentication data.

---

## Endpoints

### 1. Login

**Endpoint:** `POST /login`

**Description:** Authenticates a user (Admin, Teacher, or Parent) and returns user data including IDs and user type. The client app must store this data and use it for subsequent API requests.

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

**Success Response (200 OK):**

**For Teacher:**
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
    }
}
```

**For Admin:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 123,
            "name": "SCH001",
            "email": "admin@example.com",
            "user_type": "Admin"
        },
        "schoolID": 4,
        "school": {
            "schoolID": 4,
            "school_name": "Example School",
            "registration_number": "SCH001"
        }
    }
}
```

**For Parent:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 123,
            "name": "255614863345",
            "email": "parent@example.com",
            "user_type": "parent"
        },
        "parentID": 789,
        "schoolID": 4,
        "parent": {
            "parentID": 789,
            "phone": "255614863345",
            "first_name": "Jane",
            "last_name": "Doe"
        }
    }
}
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

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Teacher record not found."
}
```
or
```json
{
    "success": false,
    "message": "School not found for this admin account."
}
```

---

### 2. Logout

**Endpoint:** `POST /logout`

**Description:** Logs out the current user. This endpoint simply returns a success message. The client app should clear stored user data locally.

**Authentication:** Not required (stateless)

**Request Headers:**
```
Accept: application/json
```

**Response Format (200 OK):**
```json
{
    "success": true,
    "message": "Logged out successfully."
}
```

**Note:** The client app should clear all stored authentication data (user ID, schoolID, teacherID, etc.) after calling this endpoint.

---

## User Types and Login Details

### Admin Login
- **Username:** School registration number
- **Password:** Admin account password
- **Response Data:**
  - `data.user.id` - User ID
  - `data.user.user_type` - "Admin"
  - `data.schoolID` - School ID
  - `data.school` - School details object

### Teacher Login
- **Username:** Employee number (e.g., "EMP001")
- **Password:** Teacher account password
- **Response Data:**
  - `data.user.id` - User ID
  - `data.user.user_type` - "Teacher"
  - `data.schoolID` - School ID
  - `data.teacherID` - Teacher ID
  - `data.teacher` - Teacher details object
  - `data.teacher_roles` - Array of teacher roles (if Spatie permissions are installed)

### Parent Login
- **Username:** Phone number (e.g., "255614863345")
- **Password:** Parent account password
- **Response Data:**
  - `data.user.id` - User ID
  - `data.user.user_type` - "parent"
  - `data.parentID` - Parent ID
  - `data.schoolID` - School ID
  - `data.parent` - Parent details object

---

## How to Use Authentication Data

### Step 1: Login
Make a POST request to `/api/login` with username and password.

### Step 2: Store Authentication Data
Extract and store the following data from the response:
- `data.user.id` - User ID
- `data.user.user_type` - User type (Admin/Teacher/parent)
- `data.schoolID` - School ID
- `data.teacherID` - Teacher ID (for teachers only)
- `data.parentID` - Parent ID (for parents only)

**Storage Options:**
- SharedPreferences (Android)
- UserDefaults (iOS)
- AsyncStorage (React Native)
- LocalStorage (Web)
- Secure Storage (for sensitive data)

### Step 3: Use Authentication Data in Subsequent Requests
Include the stored data in all API requests. Different APIs may require different parameters:

**Option 1: Request Headers**
```
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**Option 2: Request Parameters (Query or Body)**
```
?user_id=123&user_type=Teacher&schoolID=4&teacherID=456
```

**Option 3: Request Body (for POST/PUT requests)**
```json
{
    "user_id": 123,
    "user_type": "Teacher",
    "schoolID": 4,
    "teacherID": 456,
    "other_data": "..."
}
```

**Note:** Check individual API documentation to see which method and parameters are required.

---

## Usage Examples

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class AuthAPI {
  final String baseUrl = 'http://192.168.100.104:8003/api';
  Map<String, dynamic>? authData;
  
  // Login
  Future<Map<String, dynamic>> login(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'username': username,
          'password': password,
        }),
      );
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          // Store authentication data
          authData = data['data'];
          await _saveAuthData(authData!);
          
          return data;
        } else {
          throw Exception(data['message'] ?? 'Login failed');
        }
      } else {
        final error = json.decode(response.body);
        throw Exception(error['message'] ?? 'Login failed');
      }
    } catch (e) {
      throw Exception('Error during login: $e');
    }
  }
  
  // Save auth data to SharedPreferences
  Future<void> _saveAuthData(Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('user_id', data['user']['id']);
    await prefs.setString('user_type', data['user']['user_type']);
    await prefs.setInt('schoolID', data['schoolID']);
    
    if (data['teacherID'] != null) {
      await prefs.setInt('teacherID', data['teacherID']);
    }
    if (data['parentID'] != null) {
      await prefs.setInt('parentID', data['parentID']);
    }
  }
  
  // Get auth headers for API requests
  Future<Map<String, String>> getAuthHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    
    final userId = prefs.getInt('user_id');
    final userType = prefs.getString('user_type');
    final schoolID = prefs.getInt('schoolID');
    
    if (userId != null) headers['user_id'] = userId.toString();
    if (userType != null) headers['user_type'] = userType;
    if (schoolID != null) headers['schoolID'] = schoolID.toString();
    
    final teacherID = prefs.getInt('teacherID');
    if (teacherID != null) headers['teacherID'] = teacherID.toString();
    
    final parentID = prefs.getInt('parentID');
    if (parentID != null) headers['parentID'] = parentID.toString();
    
    return headers;
  }
  
  // Make authenticated request
  Future<Map<String, dynamic>> getAuthenticatedData(String endpoint) async {
    final headers = await getAuthHeaders();
    
    try {
      final response = await http.get(
        Uri.parse('$baseUrl$endpoint'),
        headers: headers,
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
    try {
      await http.post(
        Uri.parse('$baseUrl/logout'),
        headers: {'Accept': 'application/json'},
      );
    } catch (e) {
      // Ignore errors on logout
    } finally {
      // Clear stored auth data
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('user_id');
      await prefs.remove('user_type');
      await prefs.remove('schoolID');
      await prefs.remove('teacherID');
      await prefs.remove('parentID');
      authData = null;
    }
  }
  
  // Check if user is logged in
  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt('user_id') != null;
  }
}
```

### JavaScript/React Native Example

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

class AuthAPI {
  constructor() {
    this.baseUrl = 'http://192.168.100.104:8003/api';
    this.authData = null;
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
        body: JSON.stringify({
          username: username,
          password: password,
        }),
      });
      
      if (response.ok) {
        const data = await response.json();
        
        if (data.success) {
          // Store authentication data
          this.authData = data.data;
          await this.saveAuthData(data.data);
          
          return data;
        } else {
          throw new Error(data.message || 'Login failed');
        }
      } else {
        const error = await response.json();
        throw new Error(error.message || 'Login failed');
      }
    } catch (error) {
      throw new Error(`Login error: ${error.message}`);
    }
  }
  
  // Save auth data to AsyncStorage
  async saveAuthData(data) {
    try {
      await AsyncStorage.setItem('user_id', data.user.id.toString());
      await AsyncStorage.setItem('user_type', data.user.user_type);
      await AsyncStorage.setItem('schoolID', data.schoolID.toString());
      
      if (data.teacherID) {
        await AsyncStorage.setItem('teacherID', data.teacherID.toString());
      }
      if (data.parentID) {
        await AsyncStorage.setItem('parentID', data.parentID.toString());
      }
    } catch (error) {
      console.error('Error saving auth data:', error);
    }
  }
  
  // Get auth headers for API requests
  async getAuthHeaders() {
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    
    const userId = await AsyncStorage.getItem('user_id');
    const userType = await AsyncStorage.getItem('user_type');
    const schoolID = await AsyncStorage.getItem('schoolID');
    
    if (userId) headers['user_id'] = userId;
    if (userType) headers['user_type'] = userType;
    if (schoolID) headers['schoolID'] = schoolID;
    
    const teacherID = await AsyncStorage.getItem('teacherID');
    if (teacherID) headers['teacherID'] = teacherID;
    
    const parentID = await AsyncStorage.getItem('parentID');
    if (parentID) headers['parentID'] = parentID;
    
    return headers;
  }
  
  // Make authenticated request
  async getAuthenticatedData(endpoint) {
    const headers = await this.getAuthHeaders();
    
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'GET',
        headers: headers,
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
    try {
      await fetch(`${this.baseUrl}/logout`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
        },
      });
    } catch (error) {
      // Ignore errors on logout
    } finally {
      // Clear stored auth data
      await AsyncStorage.multiRemove([
        'user_id',
        'user_type',
        'schoolID',
        'teacherID',
        'parentID'
      ]);
      this.authData = null;
    }
  }
  
  // Check if user is logged in
  async isLoggedIn() {
    const userId = await AsyncStorage.getItem('user_id');
    return userId !== null;
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
  }'
```

**Using Authentication Data in Headers:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/dashboard" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

**Using Authentication Data in Query Parameters:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/dashboard?user_id=123&user_type=Teacher&schoolID=4&teacherID=456" \
  -H "Accept: application/json"
```

---

## Authentication Data Structure

### Response Data Fields

After successful login, the following data is returned in `data` object:

**For Teacher:**
```json
{
    "user": {
        "id": 123,              // User ID - REQUIRED for all requests
        "name": "EMP001",
        "email": "teacher@example.com",
        "user_type": "Teacher"  // REQUIRED for all requests
    },
    "schoolID": 4,              // REQUIRED for all requests
    "teacherID": 456,           // REQUIRED for teacher-specific requests
    "teacher": {
        "id": 456,
        "first_name": "John",
        "last_name": "Doe",
        "employee_number": "EMP001",
        "full_name": "John Doe"
    },
    "teacher_roles": ["Class Teacher", "Subject Teacher"]  // Optional
}
```

**For Admin:**
```json
{
    "user": {
        "id": 123,              // User ID - REQUIRED for all requests
        "name": "SCH001",
        "email": "admin@example.com",
        "user_type": "Admin"    // REQUIRED for all requests
    },
    "schoolID": 4,              // REQUIRED for all requests
    "school": {
        "schoolID": 4,
        "school_name": "Example School",
        "registration_number": "SCH001"
    }
}
```

**For Parent:**
```json
{
    "user": {
        "id": 123,              // User ID - REQUIRED for all requests
        "name": "255614863345",
        "email": "parent@example.com",
        "user_type": "parent"   // REQUIRED for all requests
    },
    "parentID": 789,             // REQUIRED for parent-specific requests
    "schoolID": 4,               // REQUIRED for all requests
    "parent": {
        "parentID": 789,
        "phone": "255614863345",
        "first_name": "Jane",
        "last_name": "Doe"
    }
}
```

---

## Security Considerations

1. **Rate Limiting:** Login attempts are rate-limited to 5 attempts per IP address. After 5 failed attempts, the IP is locked for 60 seconds.

2. **Password Hashing:** Passwords are hashed using bcrypt before storage.

3. **Secure Storage:** Store authentication data securely on the client:
   - Use secure storage mechanisms (Keychain on iOS, Keystore on Android)
   - Never store passwords
   - Clear data on logout

4. **HTTPS:** In production, always use HTTPS to encrypt data in transit.

5. **Token Expiry:** Consider implementing token-based authentication with expiry for enhanced security.

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

2. **Server validates credentials**
   - Validates username and password
   - Checks rate limiting
   - Retrieves user and related data

3. **Server returns response with user data**
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
           "teacher": {...}
       }
   }
   ```

4. **Client stores authentication data**
   - Extract `data.user.id`, `data.user.user_type`, `data.schoolID`, etc.
   - Store in secure local storage
   - Use for subsequent API requests

5. **Client uses authentication data in subsequent requests**
   ```
   GET /api/teacher/dashboard
   Headers:
     user_id: 123
     user_type: Teacher
     schoolID: 4
     teacherID: 456
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
   - Response body contains user data
   - Extract and note: `user.id`, `user.user_type`, `schoolID`, `teacherID` (if teacher)

3. **Use Authentication Data in Next Request:**
   - Add headers to subsequent requests:
     ```
     user_id: 123
     user_type: Teacher
     schoolID: 4
     teacherID: 456
     ```
   - Or add as query parameters or in request body (check API documentation)

---

## Notes

1. **No Session Management:** This API does not use server-side sessions. All authentication data must be managed by the client application.

2. **Data Persistence:** The client app should persist authentication data to allow users to stay logged in across app restarts.

3. **Logout:** Logout simply returns a success message. The client app must clear all stored authentication data.

4. **Multiple Devices:** Each device maintains its own authentication state. There is no server-side session to invalidate.

5. **API Requirements:** Different API endpoints may require different authentication parameters. Always check the specific API documentation for required headers/parameters.

6. **Data Validation:** Always validate that required authentication data exists before making API requests.

---

## Support

For issues or questions regarding authentication, please contact the development team.

**Last Updated:** January 2025
