# Teacher Subjects API Documentation

## Base URL
```
http://192.168.100.104:8003/api
```

## Authentication
All endpoints require authentication via session. The teacher must be logged in through the web interface first, and the session cookie must be included in API requests.

---

## Endpoints

### 1. Get Teacher Subjects

**Endpoint:** `GET /teacher/subjects`

**Description:** Retrieves all subjects assigned to the authenticated teacher with statistics.

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
    "data": [
        {
            "class_subject_id": 123,
            "subject_id": 45,
            "subject_name": "Mathematics",
            "subject_code": "MATH",
            "class_id": 10,
            "subclass_id": 25,
            "class_name": "Form 3 - Form 3A",
            "total_students": 35
        },
        {
            "class_subject_id": 124,
            "subject_id": 46,
            "subject_name": "Physics",
            "subject_code": "PHY",
            "class_id": 10,
            "subclass_id": null,
            "class_name": "Form 3 - All Subclasses",
            "total_students": 105
        }
    ],
    "message": "Subjects retrieved successfully"
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

### 2. Get Subject Students

**Endpoint:** `GET /teacher/subjects/{classSubjectID}/students`

**Description:** Retrieves all students enrolled in a specific subject.

**Authentication:** Required (Session-based)

**Request Headers:**
```
Accept: application/json
Cookie: laravel_session=<session_token>
```

**URL Parameters:**
- `classSubjectID` (integer, required) - The class subject ID

**Response Format:**
```json
{
    "success": true,
    "data": {
        "class_subject": {
            "class_subject_id": 123,
            "subject_id": 45,
            "subject_name": "Mathematics",
            "subject_code": "MATH"
        },
        "students": [
            {
                "student_id": 1001,
                "admission_number": "STU001",
                "first_name": "John",
                "middle_name": "Michael",
                "last_name": "Doe",
                "full_name": "John Michael Doe",
                "gender": "Male",
                "date_of_birth": "2010-05-15",
                "photo": "http://192.168.100.104:8003/userImages/photo.jpg",
                "subclass_id": 25,
                "subclass_name": "Form 3A",
                "class_name": "Form 3",
                "has_health_condition": false,
                "is_disabled": false,
                "has_epilepsy": false,
                "has_allergies": false
            }
        ],
        "total_students": 35
    },
    "message": "Students retrieved successfully"
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Class subject not found or unauthorized access."
}
```

---

### 3. Get Examinations for Subject

**Endpoint:** `GET /teacher/subjects/{classSubjectID}/examinations`

**Description:** Retrieves all examinations available for a specific subject where result entry is enabled.

**Authentication:** Required (Session-based)

**Request Headers:**
```
Accept: application/json
Cookie: laravel_session=<session_token>
```

**URL Parameters:**
- `classSubjectID` (integer, required) - The class subject ID

**Response Format:**
```json
{
    "success": true,
    "data": {
        "class_subject": {
            "class_subject_id": 123,
            "subject_id": 45,
            "subject_name": "Mathematics"
        },
        "examinations": [
            {
                "exam_id": 50,
                "exam_name": "Mid-Term Examination",
                "year": "2024",
                "status": "awaiting_results",
                "start_date": "2024-03-01",
                "end_date": "2024-03-05",
                "enter_result": true,
                "exam_category": "Mid-Term",
                "term": "first_term",
                "is_term_closed": false
            }
        ]
    },
    "message": "Examinations retrieved successfully"
}
```

**Note:** Only examinations with `enter_result: true` are returned.

---

### 4. Get Subject Results

**Endpoint:** `GET /teacher/subjects/{classSubjectID}/results`

**Description:** Retrieves all results for a subject (all examinations).

**Alternative Endpoint:** `GET /teacher/subjects/{classSubjectID}/results/{examID}`

**Description:** Retrieves results for a specific examination.

**Authentication:** Required (Session-based)

**Request Headers:**
```
Accept: application/json
Cookie: laravel_session=<session_token>
```

**URL Parameters:**
- `classSubjectID` (integer, required) - The class subject ID
- `examID` (integer, optional) - The examination ID (for specific exam results)

**Response Format:**
```json
{
    "success": true,
    "data": {
        "class_subject": {
            "class_subject_id": 123,
            "subject_id": 45,
            "subject_name": "Mathematics"
        },
        "results": [
            {
                "result_id": 5001,
                "student_id": 1001,
                "student_name": "John Michael Doe",
                "admission_number": "STU001",
                "photo": "http://192.168.100.104:8003/userImages/photo.jpg",
                "exam_id": 50,
                "exam_name": "Mid-Term Examination",
                "marks": 85.5,
                "grade": "A",
                "remark": "Excellent",
                "has_health_condition": false
            }
        ],
        "total_results": 35
    },
    "message": "Results retrieved successfully"
}
```

---

### 5. Save Subject Results

**Endpoint:** `POST /teacher/subjects/results/save`

**Description:** Saves or updates results for students in a subject and examination.

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
    "class_subject_id": 123,
    "exam_id": 50,
    "results": [
        {
            "student_id": 1001,
            "marks": 85.5,
            "grade": "A",
            "remark": "Excellent"
        },
        {
            "student_id": 1002,
            "marks": 72.0,
            "grade": "B",
            "remark": "Very Good"
        }
    ]
}
```

**Validation Rules:**

| Field | Rules | Notes |
|-------|-------|-------|
| `class_subject_id` | required\|exists:class_subjects,class_subjectID | Must be a valid class subject ID |
| `exam_id` | required\|exists:examinations,examID | Must be a valid examination ID |
| `results` | required\|array | Array of result objects |
| `results.*.student_id` | required\|exists:students,studentID | Must be a valid student ID |
| `results.*.marks` | nullable\|numeric\|min:0\|max:100 | Marks between 0 and 100 |
| `results.*.grade` | nullable\|string\|max:10 | Grade letter (A, B, C, etc.) |
| `results.*.remark` | nullable\|string\|max:255 | Remark text |

**Response Format:**
```json
{
    "success": true,
    "message": "Successfully saved 2 result(s)!",
    "data": {
        "saved_count": 2
    }
}
```

**Error Response (403 Forbidden - Term Closed):**
```json
{
    "success": false,
    "message": "You are not allowed to edit results for this term. The term has been closed."
}
```

**Error Response (403 Forbidden - Result Entry Disabled):**
```json
{
    "success": false,
    "message": "You are not allowed to enter results for this examination. Result entry has been disabled."
}
```

---

### 6. Upload Excel Results

**Endpoint:** `POST /teacher/subjects/results/upload-excel`

**Description:** Uploads results from an Excel file (.xlsx or .xls format).

**Authentication:** Required (Session-based)

**Request Headers:**
```
Accept: application/json
Content-Type: multipart/form-data
Cookie: laravel_session=<session_token>
```

**Request Body (Form Data):**
```
class_subject_id: 123
exam_id: 50
excel_file: [file]
```

**Excel File Format:**
The Excel file should have the following columns:
- Column A: Student ID
- Column B: Admission Number (optional, for reference)
- Column C: Student Name (optional, for reference)
- Column D: Marks (numeric, 0-100)
- Column E: Grade (optional, will be auto-calculated if not provided)
- Column F: Remark (optional)

**Validation Rules:**

| Field | Rules | Notes |
|-------|-------|-------|
| `class_subject_id` | required\|exists:class_subjects,class_subjectID | Must be a valid class subject ID |
| `exam_id` | required\|exists:examinations,examID | Must be a valid examination ID |
| `excel_file` | required\|mimes:xlsx,xls\|max:10240 | Excel file, max 10MB |

**Response Format:**
```json
{
    "success": true,
    "message": "Successfully uploaded 35 result(s)!",
    "data": {
        "saved_count": 35,
        "errors": []
    }
}
```

**Error Response (with validation errors):**
```json
{
    "success": true,
    "message": "Successfully uploaded 30 result(s)!",
    "data": {
        "saved_count": 30,
        "errors": [
            "Row 5: Student ID 9999 not found.",
            "Row 12: Marks must be between 0 and 100."
        ]
    }
}
```

---

## Usage Examples

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';

class TeacherSubjectsAPI {
  final String baseUrl = 'http://192.168.100.104:8003/api';
  final String sessionCookie;
  
  TeacherSubjectsAPI(this.sessionCookie);
  
  // Get Subjects
  Future<Map<String, dynamic>> getSubjects() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/teacher/subjects'),
        headers: {
          'Accept': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to load subjects: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching subjects: $e');
    }
  }
  
  // Get Students
  Future<Map<String, dynamic>> getStudents(int classSubjectID) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/teacher/subjects/$classSubjectID/students'),
        headers: {
          'Accept': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to load students: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching students: $e');
    }
  }
  
  // Get Examinations
  Future<Map<String, dynamic>> getExaminations(int classSubjectID) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/teacher/subjects/$classSubjectID/examinations'),
        headers: {
          'Accept': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to load examinations: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching examinations: $e');
    }
  }
  
  // Get Results
  Future<Map<String, dynamic>> getResults(int classSubjectID, {int? examID}) async {
    try {
      String url = '$baseUrl/teacher/subjects/$classSubjectID/results';
      if (examID != null) {
        url += '/$examID';
      }
      
      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Accept': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to load results: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching results: $e');
    }
  }
  
  // Save Results
  Future<Map<String, dynamic>> saveResults({
    required int classSubjectID,
    required int examID,
    required List<Map<String, dynamic>> results,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/teacher/subjects/results/save'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
        body: json.encode({
          'class_subject_id': classSubjectID,
          'exam_id': examID,
          'results': results,
        }),
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        final error = json.decode(response.body);
        throw Exception(error['message'] ?? 'Failed to save results');
      }
    } catch (e) {
      throw Exception('Error saving results: $e');
    }
  }
  
  // Upload Excel Results
  Future<Map<String, dynamic>> uploadExcelResults({
    required int classSubjectID,
    required int examID,
    required File excelFile,
  }) async {
    try {
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$baseUrl/teacher/subjects/results/upload-excel'),
      );
      
      request.headers.addAll({
        'Accept': 'application/json',
        'Cookie': 'laravel_session=$sessionCookie',
      });
      
      request.fields['class_subject_id'] = classSubjectID.toString();
      request.fields['exam_id'] = examID.toString();
      request.files.add(
        await http.MultipartFile.fromPath('excel_file', excelFile.path),
      );
      
      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        final error = json.decode(response.body);
        throw Exception(error['message'] ?? 'Failed to upload Excel');
      }
    } catch (e) {
      throw Exception('Error uploading Excel: $e');
    }
  }
}
```

### JavaScript/React Native Example

```javascript
const TeacherSubjectsAPI = {
  baseUrl: 'http://192.168.100.104:8003/api',
  
  // Get Subjects
  async getSubjects(sessionCookie) {
    try {
      const response = await fetch(`${this.baseUrl}/teacher/subjects`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Cookie': `laravel_session=${sessionCookie}`,
        },
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        throw new Error(`Failed to load subjects: ${response.status}`);
      }
    } catch (error) {
      throw new Error(`Error fetching subjects: ${error.message}`);
    }
  },
  
  // Get Students
  async getStudents(sessionCookie, classSubjectID) {
    try {
      const response = await fetch(`${this.baseUrl}/teacher/subjects/${classSubjectID}/students`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Cookie': `laravel_session=${sessionCookie}`,
        },
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        throw new Error(`Failed to load students: ${response.status}`);
      }
    } catch (error) {
      throw new Error(`Error fetching students: ${error.message}`);
    }
  },
  
  // Get Examinations
  async getExaminations(sessionCookie, classSubjectID) {
    try {
      const response = await fetch(`${this.baseUrl}/teacher/subjects/${classSubjectID}/examinations`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Cookie': `laravel_session=${sessionCookie}`,
        },
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        throw new Error(`Failed to load examinations: ${response.status}`);
      }
    } catch (error) {
      throw new Error(`Error fetching examinations: ${error.message}`);
    }
  },
  
  // Get Results
  async getResults(sessionCookie, classSubjectID, examID = null) {
    try {
      let url = `${this.baseUrl}/teacher/subjects/${classSubjectID}/results`;
      if (examID) {
        url += `/${examID}`;
      }
      
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Cookie': `laravel_session=${sessionCookie}`,
        },
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        throw new Error(`Failed to load results: ${response.status}`);
      }
    } catch (error) {
      throw new Error(`Error fetching results: ${error.message}`);
    }
  },
  
  // Save Results
  async saveResults(sessionCookie, classSubjectID, examID, results) {
    try {
      const response = await fetch(`${this.baseUrl}/teacher/subjects/results/save`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Cookie': `laravel_session=${sessionCookie}`,
        },
        body: JSON.stringify({
          class_subject_id: classSubjectID,
          exam_id: examID,
          results: results,
        }),
      });
      
      if (response.ok) {
        return await response.json();
      } else {
        const error = await response.json();
        throw new Error(error.message || 'Failed to save results');
      }
    } catch (error) {
      throw new Error(`Error saving results: ${error.message}`);
    }
  },
  
  // Upload Excel Results
  async uploadExcelResults(sessionCookie, classSubjectID, examID, excelFile) {
    try {
      const formData = new FormData();
      formData.append('class_subject_id', classSubjectID);
      formData.append('exam_id', examID);
      formData.append('excel_file', excelFile);
      
      const response = await fetch(`${this.baseUrl}/teacher/subjects/results/upload-excel`, {
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
        throw new Error(error.message || 'Failed to upload Excel');
      }
    } catch (error) {
      throw new Error(`Error uploading Excel: ${error.message}`);
    }
  },
};
```

### cURL Examples

**Get Subjects:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN"
```

**Get Students:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/students" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN"
```

**Get Examinations:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/examinations" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN"
```

**Get Results (All):**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/results" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN"
```

**Get Results (Specific Exam):**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/results/50" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN"
```

**Save Results:**
```bash
curl -X POST "http://192.168.100.104:8003/api/teacher/subjects/results/save" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN" \
  -d '{
    "class_subject_id": 123,
    "exam_id": 50,
    "results": [
      {
        "student_id": 1001,
        "marks": 85.5,
        "grade": "A",
        "remark": "Excellent"
      }
    ]
  }'
```

**Upload Excel Results:**
```bash
curl -X POST "http://192.168.100.104:8003/api/teacher/subjects/results/upload-excel" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN" \
  -F "class_subject_id=123" \
  -F "exam_id=50" \
  -F "excel_file=@/path/to/results.xlsx"
```

---

## Response Fields Description

### Subject Data

| Field | Type | Description |
|-------|------|-------------|
| `class_subject_id` | integer | Unique identifier for the class-subject assignment |
| `subject_id` | integer | Subject ID |
| `subject_name` | string | Name of the subject |
| `subject_code` | string | Subject code (e.g., "MATH", "PHY") |
| `class_id` | integer | Class ID |
| `subclass_id` | integer\|null | Subclass ID (null if assigned to all subclasses) |
| `class_name` | string | Full class name (e.g., "Form 3 - Form 3A") |
| `total_students` | integer | Total number of students enrolled in this subject |

### Student Data

| Field | Type | Description |
|-------|------|-------------|
| `student_id` | integer | Student ID |
| `admission_number` | string | Student admission number |
| `first_name` | string | First name |
| `middle_name` | string\|null | Middle name |
| `last_name` | string | Last name |
| `full_name` | string | Full name (first + middle + last) |
| `gender` | string | Gender (Male/Female) |
| `date_of_birth` | string\|null | Date of birth (YYYY-MM-DD) |
| `photo` | string | Full URL to student photo |
| `subclass_id` | integer | Subclass ID |
| `subclass_name` | string | Subclass name |
| `class_name` | string | Class name |
| `has_health_condition` | boolean | Whether student has any health condition |
| `is_disabled` | boolean | Whether student is disabled |
| `has_epilepsy` | boolean | Whether student has epilepsy |
| `has_allergies` | boolean | Whether student has allergies |

### Examination Data

| Field | Type | Description |
|-------|------|-------------|
| `exam_id` | integer | Examination ID |
| `exam_name` | string | Examination name |
| `year` | string | Academic year |
| `status` | string | Examination status (e.g., "awaiting_results", "ongoing") |
| `start_date` | string\|null | Start date (YYYY-MM-DD) |
| `end_date` | string\|null | End date (YYYY-MM-DD) |
| `enter_result` | boolean | Whether result entry is enabled |
| `exam_category` | string | Examination category |
| `term` | string | Term (e.g., "first_term", "second_term") |
| `is_term_closed` | boolean | Whether the term is closed |

### Result Data

| Field | Type | Description |
|-------|------|-------------|
| `result_id` | integer | Result ID |
| `student_id` | integer | Student ID |
| `student_name` | string | Student full name |
| `admission_number` | string | Student admission number |
| `photo` | string | Full URL to student photo |
| `exam_id` | integer | Examination ID |
| `exam_name` | string | Examination name |
| `marks` | float\|null | Marks obtained (0-100) |
| `grade` | string\|null | Grade (A, B, C, D, F) |
| `remark` | string\|null | Remark text |
| `has_health_condition` | boolean | Whether student has health condition |

---

## Grade Calculation

Grades are automatically calculated based on marks if not provided:

- **A**: 75 - 100 (Excellent)
- **B**: 65 - 74 (Very Good)
- **C**: 45 - 64 (Good)
- **D**: 30 - 44 (Pass)
- **F**: 0 - 29 (Fail)

---

## Notes

1. **Session Authentication**: All endpoints use Laravel session-based authentication. The teacher must be logged in through the web interface first.

2. **Authorization**: Teachers can only access subjects assigned to them. Unauthorized access attempts will return 404 errors.

3. **Active Status**: Only subjects and students with "Active" status are returned.

4. **Result Entry**: Results can only be entered for examinations where `enter_result` is `true`.

5. **Term Closure**: Results cannot be edited for examinations in closed terms. New results can still be added.

6. **Excel Upload**: Excel files must follow the specified format. Invalid rows will be skipped and reported in the `errors` array.

7. **Health Conditions**: Students with health conditions (disabled, epilepsy, allergies) are flagged in the response for safety awareness.

8. **Photo URLs**: Student photos are returned as full URLs. If no photo exists, a default photo based on gender is returned.

9. **Partial Updates**: When saving results, only provided fields are updated. Fields not included remain unchanged.

10. **Error Handling**: Always check the `success` field in the response before accessing data. Handle errors appropriately based on status codes.

---

## Excel File Format

When uploading Excel results, the file must have the following structure:

| Column | Field | Required | Description |
|--------|-------|----------|-------------|
| A | Student ID | Yes | The student ID (must exist in database) |
| B | Admission Number | No | For reference only |
| C | Student Name | No | For reference only |
| D | Marks | Yes | Numeric value (0-100) |
| E | Grade | No | Will be auto-calculated if not provided |
| F | Remark | No | Optional remark text |

**Example Excel Content:**
```
A          | B        | C              | D    | E | F
-----------|----------|----------------|------|---|----------
1001       | STU001   | John Doe       | 85.5 | A | Excellent
1002       | STU002   | Jane Smith     | 72.0 | B | Very Good
```

---

## Support

For issues or questions regarding this API, please contact the development team.

**Last Updated:** January 2024
