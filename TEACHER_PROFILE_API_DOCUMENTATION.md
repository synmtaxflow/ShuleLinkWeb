# Teacher Profile API Documentation

## Base URL
```
http://192.168.100.104:8003/api
```

## Authentication
All endpoints require authentication via session. The teacher must be logged in through the web interface first, and the session cookie must be included in API requests.

---

## Endpoints

### 1. Get Teacher Profile

**Endpoint:** `GET /teacher/profile`

**Description:** Retrieves the authenticated teacher's profile information.

**Authentication:** Required (Session-based)

**Request Headers:**
```
Accept: application/json
Cookie: laravel_session=<session_token>
```

**Request Parameters:** None

**Response Format:**
```json
{
    "success": true,
    "data": {
        "id": 123,
        "school_id": 4,
        "first_name": "John",
        "middle_name": "Michael",
        "last_name": "Doe",
        "full_name": "John Michael Doe",
        "gender": "Male",
        "national_id": "1234567890123456",
        "employee_number": "EMP001",
        "email": "john.doe@example.com",
        "phone_number": "255614863345",
        "qualification": "Bachelor of Education",
        "specialization": "Mathematics",
        "experience": "5 years",
        "date_of_birth": "1990-05-15",
        "date_hired": "2019-01-15",
        "address": "123 Main Street, Dar es Salaam",
        "position": "Senior Teacher",
        "status": "Active",
        "image": "http://192.168.100.104:8003/userImages/1234567890_profile.jpg",
        "username": "EMP001",
        "has_password": true
    },
    "message": "Profile retrieved successfully"
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Unauthorized. Please login first."
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Teacher not found"
}
```

---

### 2. Update Teacher Profile

**Endpoint:** `POST /teacher/profile/update`

**Description:** Updates the authenticated teacher's profile information. Only provided fields will be updated.

**Authentication:** Required (Session-based)

**Request Headers:**
```
Accept: application/json
Content-Type: multipart/form-data (if uploading image) or application/json
Cookie: laravel_session=<session_token>
```

**Request Body (JSON):**
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "middle_name": "Michael",
    "gender": "Male",
    "email": "john.doe@example.com",
    "phone_number": "255614863345",
    "qualification": "Bachelor of Education",
    "specialization": "Mathematics",
    "experience": "5 years",
    "date_of_birth": "1990-05-15",
    "address": "123 Main Street, Dar es Salaam",
    "position": "Senior Teacher"
}
```

**Request Body (Form Data - for image upload):**
```
first_name: John
last_name: Doe
email: john.doe@example.com
phone_number: 255614863345
image: [file]
```

**Validation Rules:**

| Field | Rules | Notes |
|-------|-------|-------|
| `first_name` | sometimes\|required\|string\|max:255 | Required if provided |
| `last_name` | sometimes\|required\|string\|max:255 | Required if provided |
| `middle_name` | nullable\|string\|max:255 | Optional |
| `gender` | sometimes\|required\|in:Male,Female | Required if provided |
| `email` | sometimes\|required\|email\|unique:teachers,email,{teacherID} | Must be unique |
| `phone_number` | sometimes\|required\|unique:teachers,phone_number,{teacherID}\|regex:/^255\d{9}$/ | Must start with 255 and have 12 digits total |
| `qualification` | nullable\|string\|max:255 | Optional |
| `specialization` | nullable\|string\|max:255 | Optional |
| `experience` | nullable\|string\|max:255 | Optional |
| `date_of_birth` | nullable\|date | Optional, format: YYYY-MM-DD |
| `address` | nullable\|string\|max:500 | Optional |
| `position` | nullable\|string\|max:255 | Optional |
| `image` | nullable\|image\|mimes:jpg,jpeg,png\|max:2048 | Optional, max 2MB, JPG/PNG only |

**Response Format:**
```json
{
    "success": true,
    "data": {
        "id": 123,
        "first_name": "John",
        "middle_name": "Michael",
        "last_name": "Doe",
        "full_name": "John Michael Doe",
        "gender": "Male",
        "email": "john.doe@example.com",
        "phone_number": "255614863345",
        "qualification": "Bachelor of Education",
        "specialization": "Mathematics",
        "experience": "5 years",
        "date_of_birth": "1990-05-15",
        "address": "123 Main Street, Dar es Salaam",
        "position": "Senior Teacher",
        "image": "http://192.168.100.104:8003/userImages/1234567890_profile.jpg"
    },
    "message": "Profile updated successfully"
}
```

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": "This email is already taken by another teacher.",
        "phone_number": "Phone number must have 12 digits: start with 255 followed by 9 digits (e.g., 255614863345)"
    }
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Unauthorized. Please login first."
}
```

---

### 3. Change Teacher Password

**Endpoint:** `POST /teacher/password/change`

**Description:** Changes the authenticated teacher's password.

**Authentication:** Required (Session-based)

**Request Headers:**
```
Accept: application/json
Content-Type: application/json
Cookie: laravel_session=<session_token>
```

**Request Body:**
```json
{
    "current_password": "oldpassword123",
    "new_password": "newpassword456",
    "confirm_password": "newpassword456"
}
```

**Validation Rules:**

| Field | Rules | Notes |
|-------|-------|-------|
| `current_password` | required\|string | Current password for verification |
| `new_password` | required\|string\|min:6 | Must be at least 6 characters |
| `confirm_password` | required\|string\|same:new_password | Must match new_password |

**Response Format:**
```json
{
    "success": true,
    "message": "Password changed successfully"
}
```

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "current_password": "Current password is required",
        "new_password": "New password must be at least 6 characters",
        "confirm_password": "Password confirmation does not match new password"
    }
}
```

**Error Response (422 Incorrect Password):**
```json
{
    "success": false,
    "message": "Current password is incorrect",
    "errors": {
        "current_password": "Current password is incorrect"
    }
}
```

**Error Response (422 Same Password):**
```json
{
    "success": false,
    "message": "New password must be different from current password",
    "errors": {
        "new_password": "New password must be different from current password"
    }
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Unauthorized. Please login first."
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "User account not found"
}
```

---

## Usage Examples

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';

class TeacherProfileAPI {
  final String baseUrl = 'http://192.168.100.104:8003/api';
  final String sessionCookie;
  
  TeacherProfileAPI(this.sessionCookie);
  
  // Get Profile
  Future<Map<String, dynamic>> getProfile() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/teacher/profile'),
        headers: {
          'Accept': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to load profile: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching profile: $e');
    }
  }
  
  // Update Profile
  Future<Map<String, dynamic>> updateProfile({
    String? firstName,
    String? lastName,
    String? email,
    String? phoneNumber,
    File? image,
  }) async {
    try {
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$baseUrl/teacher/profile/update'),
      );
      
      request.headers.addAll({
        'Accept': 'application/json',
        'Cookie': 'laravel_session=$sessionCookie',
      });
      
      if (firstName != null) request.fields['first_name'] = firstName;
      if (lastName != null) request.fields['last_name'] = lastName;
      if (email != null) request.fields['email'] = email;
      if (phoneNumber != null) request.fields['phone_number'] = phoneNumber;
      
      if (image != null) {
        request.files.add(
          await http.MultipartFile.fromPath('image', image.path),
        );
      }
      
      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        final error = json.decode(response.body);
        throw Exception(error['message'] ?? 'Failed to update profile');
      }
    } catch (e) {
      throw Exception('Error updating profile: $e');
    }
  }
  
  // Change Password
  Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/teacher/password/change'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
        body: json.encode({
          'current_password': currentPassword,
          'new_password': newPassword,
          'confirm_password': confirmPassword,
        }),
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        final error = json.decode(response.body);
        throw Exception(error['message'] ?? 'Failed to change password');
      }
    } catch (e) {
      throw Exception('Error changing password: $e');
    }
  }
}
```

### JavaScript/React Native Example

```javascript
const TeacherProfileAPI = {
  baseUrl: 'http://192.168.100.104:8003/api',
  
  // Get Profile
  async getProfile(sessionCookie) {
    try {
      const response = await fetch(`${this.baseUrl}/teacher/profile`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Cookie': `laravel_session=${sessionCookie}`,
        },
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        throw new Error(`Failed to load profile: ${response.status}`);
      }
    } catch (error) {
      throw new Error(`Error fetching profile: ${error.message}`);
    }
  },
  
  // Update Profile
  async updateProfile(sessionCookie, profileData, imageFile = null) {
    try {
      const formData = new FormData();
      
      Object.keys(profileData).forEach(key => {
        if (profileData[key] !== null && profileData[key] !== undefined) {
          formData.append(key, profileData[key]);
        }
      });
      
      if (imageFile) {
        formData.append('image', imageFile);
      }
      
      const response = await fetch(`${this.baseUrl}/teacher/profile/update`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Cookie': `laravel_session=${sessionCookie}`,
        },
        body: formData,
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        const error = await response.json();
        throw new Error(error.message || 'Failed to update profile');
      }
    } catch (error) {
      throw new Error(`Error updating profile: ${error.message}`);
    }
  },
  
  // Change Password
  async changePassword(sessionCookie, currentPassword, newPassword, confirmPassword) {
    try {
      const response = await fetch(`${this.baseUrl}/teacher/password/change`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Cookie': `laravel_session=${sessionCookie}`,
        },
        body: JSON.stringify({
          current_password: currentPassword,
          new_password: newPassword,
          confirm_password: confirmPassword,
        }),
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        const error = await response.json();
        throw new Error(error.message || 'Failed to change password');
      }
    } catch (error) {
      throw new Error(`Error changing password: ${error.message}`);
    }
  },
};
```

### cURL Examples

**Get Profile:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/profile" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN"
```

**Update Profile (JSON):**
```bash
curl -X POST "http://192.168.100.104:8003/api/teacher/profile/update" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone_number": "255614863345"
  }'
```

**Update Profile with Image:**
```bash
curl -X POST "http://192.168.100.104:8003/api/teacher/profile/update" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN" \
  -F "first_name=John" \
  -F "last_name=Doe" \
  -F "email=john.doe@example.com" \
  -F "image=@/path/to/image.jpg"
```

**Change Password:**
```bash
curl -X POST "http://192.168.100.104:8003/api/teacher/password/change" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN" \
  -d '{
    "current_password": "oldpassword123",
    "new_password": "newpassword456",
    "confirm_password": "newpassword456"
  }'
```

---

## Response Fields Description

### Profile Data Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Teacher ID |
| `school_id` | integer | School ID |
| `first_name` | string | Teacher's first name |
| `middle_name` | string | Teacher's middle name (nullable) |
| `last_name` | string | Teacher's last name |
| `full_name` | string | Full name (first + middle + last) |
| `gender` | string | Gender (Male/Female) |
| `national_id` | string | National ID number |
| `employee_number` | string | Employee number (used as username) |
| `email` | string | Email address |
| `phone_number` | string | Phone number (format: 255XXXXXXXXX) |
| `qualification` | string | Educational qualification |
| `specialization` | string | Subject specialization |
| `experience` | string | Years of experience |
| `date_of_birth` | string | Date of birth (YYYY-MM-DD) |
| `date_hired` | string | Date hired (YYYY-MM-DD) |
| `address` | string | Physical address |
| `position` | string | Job position/title |
| `status` | string | Employment status (Active/Inactive) |
| `image` | string | Full URL to profile image |
| `username` | string | Username (employee_number) |
| `has_password` | boolean | Whether user has a password set |

---

## Notes

1. **Session Authentication**: All endpoints use Laravel session-based authentication. The teacher must be logged in through the web interface first.

2. **Partial Updates**: For profile updates, only provided fields will be updated. Fields not included in the request will remain unchanged.

3. **Image Upload**: When uploading an image, use `multipart/form-data` content type. The old image will be automatically deleted when a new one is uploaded.

4. **Email Update**: If email is updated, the corresponding User account email will also be updated automatically.

5. **Password Requirements**: 
   - Minimum 6 characters
   - Must be different from current password
   - Must match confirmation password

6. **Phone Number Format**: Must start with 255 followed by 9 digits (total 12 digits). Example: `255614863345`

7. **Error Handling**: Always check the `success` field in the response before accessing data. Handle errors appropriately based on status codes.

8. **Image Formats**: Only JPG, JPEG, and PNG formats are allowed. Maximum file size is 2MB.

---

## Security Considerations

1. **Password Security**: Passwords are hashed using bcrypt before storage. Never send passwords in plain text.

2. **Session Management**: Session tokens should be stored securely and not exposed in client-side code.

3. **Input Validation**: Always validate input on the client side before sending requests.

4. **HTTPS**: In production, use HTTPS to encrypt data in transit.

---

## Support

For issues or questions regarding this API, please contact the development team.

**Last Updated:** January 2024
