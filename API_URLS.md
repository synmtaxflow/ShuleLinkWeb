# ShuleLink API URLs - Quick Reference

## Base URL
```
http://10.0.2.2:8000/api
```

**Note:** 
- `10.0.2.2` is for Flutter Android Emulator (maps to host machine's localhost)
- For physical devices, use your actual server IP (e.g., `192.168.x.x`)
- Port `8000` is Laravel's default development server port

## Available Endpoints

### 1. Health Check
```
GET http://10.0.2.2:8000/api/health
```

### 2. Login
```
POST http://10.0.2.2:8000/api/login
Body: {
  "username": "string",
  "password": "string"
}
```

### 3. Logout
```
POST http://10.0.2.2:8000/api/logout
```

### 4. Get User Profile
```
GET http://10.0.2.2:8000/api/user/profile
Headers: 
  user_id: integer
  user_type: string (Admin|Teacher|parent)
```

### 5. Parent Dashboard
```
GET http://10.0.2.2:8000/api/parent/dashboard?parentID=1&schoolID=1
POST http://10.0.2.2:8000/api/parent/dashboard
```

### 6. Parent Results
```
GET http://10.0.2.2:8000/api/parent/results?parentID=1&schoolID=1
POST http://10.0.2.2:8000/api/parent/results
```

### 7. Parent Attendance
```
GET http://10.0.2.2:8000/api/parent/attendance?parentID=1&schoolID=1
POST http://10.0.2.2:8000/api/parent/attendance
```

---

## Postman Quick Test

### 1. Test Login

**Method:** POST  
**URL:** `http://10.0.2.2:8000/api/login`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```
**Body (raw JSON):**
```json
{
  "username": "your_username_here",
  "password": "your_password_here"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "username",
      "email": "user@example.com",
      "user_type": "parent"
    },
    "schoolID": 1,
    "parentID": 1
  }
}
```

---

### 2. Get Parent Results

**Method:** GET  
**URL:** `http://10.0.2.2:8000/api/parent/results?parentID=1&schoolID=1`  
**Headers:**
```
Accept: application/json
```

**With Filters:**
```
http://10.0.2.2:8000/api/parent/results?parentID=1&schoolID=1&student=5&year=2024&exam=10
```

**Method:** POST  
**URL:** `http://10.0.2.2:8000/api/parent/results`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```
**Body (raw JSON):**
```json
{
  "parentID": 1,
  "schoolID": 1,
  "student": 5,
  "year": "2024",
  "exam": 10
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Results retrieved successfully",
  "data": {
    "parent": {
      "parentID": 1,
      "first_name": "John",
      "middle_name": "Doe",
      "last_name": "Smith",
      "phone": "255712345678",
      "email": "john@example.com"
    },
    "school": {
      "schoolID": 1,
      "school_name": "Example Secondary School",
      "school_type": "Secondary"
    },
    "students": [
      {
        "studentID": 5,
        "first_name": "Jane",
        "middle_name": "Doe",
        "last_name": "Smith",
        "admission_number": "STU001",
        "class": "form_one",
        "subclass": "A"
      }
    ],
    "examinations": [
      {
        "examID": 10,
        "exam_name": "Mid-Term Examination",
        "year": "2024",
        "start_date": "2024-03-01",
        "end_date": "2024-03-05"
      }
    ],
    "years": ["2024", "2023", "2022"],
    "filters": {
      "student": "5",
      "year": "2024",
      "exam": "10"
    },
    "statistics": {
      "total_students": 1,
      "total_examinations": 1,
      "total_results": 1
    },
    "results": [
      {
        "exam": {
          "examID": 10,
          "exam_name": "Mid-Term Examination",
          "year": "2024",
          "start_date": "2024-03-01",
          "end_date": "2024-03-05"
        },
        "student": {
          "studentID": 5,
          "first_name": "Jane",
          "middle_name": "Doe",
          "last_name": "Smith",
          "admission_number": "STU001",
          "class": "form_one",
          "subclass": "A"
        },
        "summary": {
          "subject_count": 8,
          "total_marks": 628,
          "average_marks": 78.5,
          "total_points": 15,
          "total_division": "I.15",
          "total_grade": null,
          "display_label": "Division",
          "position": 3,
          "total_students": 45
        },
        "subjects": [
          {
            "resultID": 101,
            "subject": {
              "subjectID": 1,
              "subject_name": "Mathematics"
            },
            "marks": 85,
            "grade": "A",
            "division": null,
            "points": 1,
            "remark": "Excellent"
          },
          {
            "resultID": 102,
            "subject": {
              "subjectID": 2,
              "subject_name": "English"
            },
            "marks": 78,
            "grade": "B",
            "division": null,
            "points": 2,
            "remark": "Good"
          }
        ]
      }
    ]
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "parentID and schoolID are required"
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Parent not found"
}
```

---

### 3. Get Parent Attendance

**Method:** GET  
**URL:** `http://10.0.2.2:8000/api/parent/attendance?parentID=1&schoolID=1&student=5&year=2024&month=03`  
**Headers:**
```
Accept: application/json
```

**Method:** POST  
**URL:** `http://10.0.2.2:8000/api/parent/attendance`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```
**Body (raw JSON):**
```json
{
  "parentID": 1,
  "schoolID": 1,
  "student": 5,
  "year": "2024",
  "month": "03",
  "date": "2024-03-15",
  "search_type": "month"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Attendance records retrieved successfully",
  "data": {
    "parent": {
      "parentID": 1,
      "first_name": "John",
      "middle_name": "Doe",
      "last_name": "Smith",
      "phone": "255712345678",
      "email": "john@example.com"
    },
    "school": {
      "schoolID": 1,
      "school_name": "Example Secondary School",
      "school_type": "Secondary"
    },
    "students": [
      {
        "studentID": 5,
        "first_name": "Jane",
        "middle_name": "Doe",
        "last_name": "Smith",
        "admission_number": "STU001",
        "class": "form_one",
        "subclass": "A"
      }
    ],
    "years": ["2024", "2023"],
    "filters": {
      "student": "5",
      "year": "2024",
      "month": "03",
      "date": "",
      "search_type": "month"
    },
    "overview": {
      "total_days": 20,
      "present": 18,
      "absent": 1,
      "late": 1,
      "excused": 0,
      "attendance_rate": 90.0
    },
    "daily_records": [
      {
        "attendanceID": 101,
        "student": {
          "studentID": 5,
          "first_name": "Jane",
          "middle_name": "Doe",
          "last_name": "Smith",
          "admission_number": "STU001",
          "class": "form_one",
          "subclass": "A"
        },
        "attendance_date": "2024-03-15",
        "status": "Present",
        "remark": null,
        "created_at": "2024-03-15 08:00:00"
      }
    ],
    "grouped_records": [
      {
        "date": "2024-03-15",
        "date_formatted": "Friday, March 15, 2024",
        "status": "Present",
        "remark": null,
        "students": [
          {
            "studentID": 5,
            "first_name": "Jane",
            "middle_name": "Doe",
            "last_name": "Smith",
            "admission_number": "STU001",
            "status": "Present",
            "remark": null
          }
        ]
      }
    ]
  }
}
```

---

### 4. Get Parent Dashboard

**Method:** GET  
**URL:** `http://10.0.2.2:8000/api/parent/dashboard?parentID=1&schoolID=1`  
**Headers:**
```
Accept: application/json
```

**Method:** POST  
**URL:** `http://10.0.2.2:8000/api/parent/dashboard`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```
**Body (raw JSON):**
```json
{
  "parentID": 1,
  "schoolID": 1
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Parent dashboard data retrieved successfully",
  "data": {
    "parent": {
      "parentID": 1,
      "first_name": "John",
      "middle_name": "Doe",
      "last_name": "Smith",
      "phone": "255712345678",
      "email": "john@example.com",
      "occupation": "Engineer",
      "address": "123 Main Street",
      "photo": "http://10.0.2.2:8000/userImages/parent_photo.jpg",
      "schoolID": 1
    },
    "school": {
      "schoolID": 1,
      "school_name": "Example Primary School",
      "registration_number": "REG123456"
    },
    "statistics": {
      "total_students": 3,
      "active_students": 2,
      "male_students": 2,
      "female_students": 1,
      "recent_results_count": 5,
      "upcoming_exams_count": 3
    },
    "students": [
      {
        "studentID": 1,
        "first_name": "Jane",
        "middle_name": "Doe",
        "last_name": "Smith",
        "admission_number": "ADM001",
        "gender": "Female",
        "status": "Active",
        "photo": "http://10.0.2.2:8000/userImages/student_photo.jpg",
        "class": {
          "class_name": "Form One",
          "subclass_name": "A"
        },
        "attendance_percentage": 85.5
      }
    ],
    "recent_results": [
      {
        "resultID": 101,
        "marks": 85,
        "grade": "A",
        "student": {
          "studentID": 1,
          "first_name": "Jane",
          "last_name": "Smith"
        },
        "examination": {
          "examID": 10,
          "exam_name": "Mid-Term Examination",
          "year": "2024"
        },
        "subject": {
          "subjectID": 1,
          "subject_name": "Mathematics"
        }
      }
    ],
    "recent_attendance": [],
    "attendance_stats": {
      "1": {
        "total": 20,
        "present": 18,
        "percentage": 90.0
      }
    },
    "active_book_borrows": [],
    "upcoming_exams": [],
    "notifications": []
  }
}
```

---

## Flutter Usage

### Base URL Setup
```dart
// For Android Emulator
const String baseUrl = 'http://10.0.2.2:8000/api';

// For Physical Device (replace with your server IP)
// const String baseUrl = 'http://192.168.1.100:8000/api';
```

### 1. Login Example
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

Future<Map<String, dynamic>> login(String username, String password) async {
  final response = await http.post(
    Uri.parse('$baseUrl/login'),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: jsonEncode({
      'username': username,
      'password': password,
    }),
  );
  
  if (response.statusCode == 200) {
    return json.decode(response.body);
  } else {
    throw Exception('Login failed: ${response.statusCode}');
  }
}
```

### 2. Get Parent Results Example
```dart
Future<Map<String, dynamic>> getParentResults({
  required int parentID,
  required int schoolID,
  int? studentID,
  String? year,
  int? examID,
}) async {
  final queryParams = {
    'parentID': parentID.toString(),
    'schoolID': schoolID.toString(),
  };
  
  if (studentID != null) queryParams['student'] = studentID.toString();
  if (year != null) queryParams['year'] = year;
  if (examID != null) queryParams['exam'] = examID.toString();
  
  final uri = Uri.parse('$baseUrl/parent/results')
      .replace(queryParameters: queryParams);
  
  final response = await http.get(
    uri,
    headers: {'Accept': 'application/json'},
  );
  
  if (response.statusCode == 200) {
    return json.decode(response.body);
  } else {
    throw Exception('Failed to load results: ${response.statusCode}');
  }
}

// Usage
final results = await getParentResults(
  parentID: 1,
  schoolID: 1,
  student: 5,
  year: '2024',
);
```

### 3. Get Parent Attendance Example
```dart
Future<Map<String, dynamic>> getParentAttendance({
  required int parentID,
  required int schoolID,
  int? studentID,
  String? year,
  String? month,
  String? date,
  String searchType = 'month',
}) async {
  final queryParams = {
    'parentID': parentID.toString(),
    'schoolID': schoolID.toString(),
    'search_type': searchType,
  };
  
  if (studentID != null) queryParams['student'] = studentID.toString();
  if (year != null) queryParams['year'] = year;
  if (month != null) queryParams['month'] = month;
  if (date != null) queryParams['date'] = date;
  
  final uri = Uri.parse('$baseUrl/parent/attendance')
      .replace(queryParameters: queryParams);
  
  final response = await http.get(
    uri,
    headers: {'Accept': 'application/json'},
  );
  
  if (response.statusCode == 200) {
    return json.decode(response.body);
  } else {
    throw Exception('Failed to load attendance: ${response.statusCode}');
  }
}

// Usage
final attendance = await getParentAttendance(
  parentID: 1,
  schoolID: 1,
  student: 5,
  year: '2024',
  month: '03',
  searchType: 'month',
);
```

### 4. Get Parent Dashboard Example
```dart
Future<Map<String, dynamic>> getParentDashboard({
  required int parentID,
  required int schoolID,
}) async {
  final uri = Uri.parse('$baseUrl/parent/dashboard')
      .replace(queryParameters: {
        'parentID': parentID.toString(),
        'schoolID': schoolID.toString(),
      });
  
  final response = await http.get(
    uri,
    headers: {'Accept': 'application/json'},
  );
  
  if (response.statusCode == 200) {
    return json.decode(response.body);
  } else {
    throw Exception('Failed to load dashboard: ${response.statusCode}');
  }
}

// Usage
final dashboard = await getParentDashboard(
  parentID: 1,
  schoolID: 1,
);
```

---

For detailed documentation, see `API_DOCUMENTATION.md`

