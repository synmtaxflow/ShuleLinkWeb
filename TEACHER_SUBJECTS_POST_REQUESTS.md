# Teacher Subjects API - POST Requests Documentation

## Base URL
```
http://192.168.100.104:8003/api
```

## Authentication
All POST endpoints require authentication via headers (stateless authentication).

**Required Headers:**
```
Accept: application/json
Content-Type: application/json (for JSON requests)
Content-Type: multipart/form-data (for file uploads)
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

---

## POST Endpoints

### 1. Save Subject Results

**Endpoint:** `POST /teacher/subjects/results/save`

**Description:** Saves or updates results for students in a subject and examination. This endpoint can create new results or update existing ones.

**Request Headers:**
```
Accept: application/json
Content-Type: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
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
        },
        {
            "student_id": 1003,
            "marks": 45.5,
            "grade": "C",
            "remark": "Good"
        }
    ]
}
```

**Request Parameters:**

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `class_subject_id` | integer | **Yes** | exists:class_subjects,class_subjectID | Class subject ID |
| `exam_id` | integer | **Yes** | exists:examinations,examID | Examination ID |
| `results` | array | **Yes** | array, min:1 | Array of result objects |
| `results.*.student_id` | integer | **Yes** | exists:students,studentID | Student ID |
| `results.*.marks` | float | No | nullable\|numeric\|min:0\|max:100 | Marks (0-100) |
| `results.*.grade` | string | No | nullable\|string\|max:10 | Grade (A, B, C, D, F) |
| `results.*.remark` | string | No | nullable\|string\|max:255 | Remark text |

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Successfully saved 3 result(s)!",
    "data": {
        "saved_count": 3
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

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "class_subject_id": ["The class subject id field is required."],
        "exam_id": ["The exam id field is required."],
        "results": ["The results field is required."],
        "results.0.student_id": ["The student id field is required."],
        "results.0.marks": ["The marks must be between 0 and 100."]
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

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Class subject not found or unauthorized access."
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Failed to save results",
    "error": "Error message details"
}
```

---

### 2. Upload Excel Results

**Endpoint:** `POST /teacher/subjects/results/upload-excel`

**Description:** Uploads results from an Excel file (.xlsx or .xls format). The file is processed and results are saved in bulk.

**Request Headers:**
```
Accept: application/json
Content-Type: multipart/form-data
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**Request Body (Form Data):**
```
class_subject_id: 123
exam_id: 50
excel_file: [file]
```

**Request Parameters:**

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `class_subject_id` | integer | **Yes** | exists:class_subjects,class_subjectID | Class subject ID |
| `exam_id` | integer | **Yes** | exists:examinations,examID | Examination ID |
| `excel_file` | file | **Yes** | mimes:xlsx,xls\|max:10240 | Excel file (max 10MB) |

**Excel File Format:**

The Excel file should have the following columns:

| Column | Field | Required | Description |
|--------|-------|----------|-------------|
| A | Student ID | **Yes** | Must exist in database |
| B | Admission Number | No | For reference only |
| C | Student Name | No | For reference only |
| D | Marks | **Yes** | Numeric value (0-100) |
| E | Grade | No | Will be auto-calculated if not provided |
| F | Remark | No | Optional remark text |

**Example Excel Content:**
```
Row 1 (Header - optional):
Student ID | Admission Number | Student Name | Marks | Grade | Remark

Row 2 (Data):
1001 | STU001 | John Doe | 85.5 | A | Excellent

Row 3 (Data):
1002 | STU002 | Jane Smith | 72.0 | B | Very Good

Row 4 (Data):
1003 | STU003 | Bob Johnson | 45.5 | C | Good
```

**Success Response (200 OK):**
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

**Success Response (with errors):**
```json
{
    "success": true,
    "message": "Successfully uploaded 30 result(s)!",
    "data": {
        "saved_count": 30,
        "errors": [
            "Row 5: Student ID 9999 not found.",
            "Row 12: Marks must be between 0 and 100.",
            "Row 18: Invalid student ID format."
        ]
    }
}
```

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "class_subject_id": ["The class subject id field is required."],
        "exam_id": ["The exam id field is required."],
        "excel_file": ["The excel file field is required."],
        "excel_file": ["The excel file must be a file of type: xlsx, xls."],
        "excel_file": ["The excel file may not be greater than 10240 kilobytes."]
    }
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Failed to upload Excel results",
    "error": "Error message details"
}
```

---

## Usage Examples

### cURL Examples

#### Save Results
```bash
curl -X POST "http://192.168.100.104:8003/api/teacher/subjects/results/save" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456" \
  -d '{
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
  }'
```

#### Upload Excel Results
```bash
curl -X POST "http://192.168.100.104:8003/api/teacher/subjects/results/upload-excel" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456" \
  -F "class_subject_id=123" \
  -F "exam_id=50" \
  -F "excel_file=@/path/to/results.xlsx"
```

---

### Flutter/Dart Examples

#### Save Results
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class TeacherSubjectsAPI {
  final String baseUrl = 'http://192.168.100.104:8003/api';
  
  // Save Results
  Future<Map<String, dynamic>> saveResults({
    required int classSubjectID,
    required int examID,
    required List<Map<String, dynamic>> results,
    required Map<String, String> authHeaders,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/teacher/subjects/results/save'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          ...authHeaders, // user_id, user_type, schoolID, teacherID
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
  
  // Usage Example
  Future<void> saveStudentResults() async {
    final authHeaders = {
      'user_id': '123',
      'user_type': 'Teacher',
      'schoolID': '4',
      'teacherID': '456',
    };
    
    final results = [
      {
        'student_id': 1001,
        'marks': 85.5,
        'grade': 'A',
        'remark': 'Excellent',
      },
      {
        'student_id': 1002,
        'marks': 72.0,
        'grade': 'B',
        'remark': 'Very Good',
      },
    ];
    
    try {
      final response = await saveResults(
        classSubjectID: 123,
        examID: 50,
        results: results,
        authHeaders: authHeaders,
      );
      
      print('Success: ${response['message']}');
      print('Saved count: ${response['data']['saved_count']}');
    } catch (e) {
      print('Error: $e');
    }
  }
}
```

#### Upload Excel Results
```dart
import 'package:http/http.dart' as http;
import 'dart:io';

class TeacherSubjectsAPI {
  final String baseUrl = 'http://192.168.100.104:8003/api';
  
  // Upload Excel Results
  Future<Map<String, dynamic>> uploadExcelResults({
    required int classSubjectID,
    required int examID,
    required File excelFile,
    required Map<String, String> authHeaders,
  }) async {
    try {
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$baseUrl/teacher/subjects/results/upload-excel'),
      );
      
      request.headers.addAll({
        'Accept': 'application/json',
        ...authHeaders, // user_id, user_type, schoolID, teacherID
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
  
  // Usage Example
  Future<void> uploadResultsFromExcel() async {
    final authHeaders = {
      'user_id': '123',
      'user_type': 'Teacher',
      'schoolID': '4',
      'teacherID': '456',
    };
    
    final excelFile = File('/path/to/results.xlsx');
    
    try {
      final response = await uploadExcelResults(
        classSubjectID: 123,
        examID: 50,
        excelFile: excelFile,
        authHeaders: authHeaders,
      );
      
      print('Success: ${response['message']}');
      print('Saved count: ${response['data']['saved_count']}');
      
      if (response['data']['errors'].isNotEmpty) {
        print('Errors:');
        response['data']['errors'].forEach((error) {
          print('  - $error');
        });
      }
    } catch (e) {
      print('Error: $e');
    }
  }
}
```

---

### JavaScript/React Native Examples

#### Save Results
```javascript
const TeacherSubjectsAPI = {
  baseUrl: 'http://192.168.100.104:8003/api',
  
  // Save Results
  async saveResults(authHeaders, classSubjectID, examID, results) {
    try {
      const response = await fetch(`${this.baseUrl}/teacher/subjects/results/save`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          ...authHeaders, // user_id, user_type, schoolID, teacherID
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
  
  // Usage Example
  async saveStudentResults() {
    const authHeaders = {
      'user_id': '123',
      'user_type': 'Teacher',
      'schoolID': '4',
      'teacherID': '456',
    };
    
    const results = [
      {
        student_id: 1001,
        marks: 85.5,
        grade: 'A',
        remark: 'Excellent',
      },
      {
        student_id: 1002,
        marks: 72.0,
        grade: 'B',
        remark: 'Very Good',
      },
    ];
    
    try {
      const response = await this.saveResults(
        authHeaders,
        123, // classSubjectID
        50,  // examID
        results
      );
      
      console.log('Success:', response.message);
      console.log('Saved count:', response.data.saved_count);
    } catch (error) {
      console.error('Error:', error.message);
    }
  },
};
```

#### Upload Excel Results
```javascript
const TeacherSubjectsAPI = {
  baseUrl: 'http://192.168.100.104:8003/api',
  
  // Upload Excel Results
  async uploadExcelResults(authHeaders, classSubjectID, examID, excelFile) {
    try {
      const formData = new FormData();
      formData.append('class_subject_id', classSubjectID);
      formData.append('exam_id', examID);
      formData.append('excel_file', excelFile);
      
      const response = await fetch(`${this.baseUrl}/teacher/subjects/results/upload-excel`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          ...authHeaders, // user_id, user_type, schoolID, teacherID
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
  
  // Usage Example (React Native)
  async uploadResultsFromExcel() {
    const authHeaders = {
      'user_id': '123',
      'user_type': 'Teacher',
      'schoolID': '4',
      'teacherID': '456',
    };
    
    // For React Native, use react-native-document-picker or similar
    const excelFile = {
      uri: 'file:///path/to/results.xlsx',
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      name: 'results.xlsx',
    };
    
    try {
      const response = await this.uploadExcelResults(
        authHeaders,
        123, // classSubjectID
        50,  // examID
        excelFile
      );
      
      console.log('Success:', response.message);
      console.log('Saved count:', response.data.saved_count);
      
      if (response.data.errors && response.data.errors.length > 0) {
        console.log('Errors:');
        response.data.errors.forEach(error => {
          console.log('  -', error);
        });
      }
    } catch (error) {
      console.error('Error:', error.message);
    }
  },
};
```

---

## Request Body Examples

### Example 1: Save Single Student Result
```json
{
    "class_subject_id": 123,
    "exam_id": 50,
    "results": [
        {
            "student_id": 1001,
            "marks": 85.5,
            "grade": "A",
            "remark": "Excellent performance"
        }
    ]
}
```

### Example 2: Save Multiple Students Results
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
        },
        {
            "student_id": 1003,
            "marks": 45.5,
            "grade": "C",
            "remark": "Good"
        },
        {
            "student_id": 1004,
            "marks": 30.0,
            "grade": "D",
            "remark": "Pass"
        },
        {
            "student_id": 1005,
            "marks": 25.0,
            "grade": "F",
            "remark": "Fail"
        }
    ]
}
```

### Example 3: Save Results with Only Marks (Grade Auto-calculated)
```json
{
    "class_subject_id": 123,
    "exam_id": 50,
    "results": [
        {
            "student_id": 1001,
            "marks": 85.5
        },
        {
            "student_id": 1002,
            "marks": 72.0
        },
        {
            "student_id": 1003,
            "marks": 45.5
        }
    ]
}
```

### Example 4: Update Existing Results (Partial Update)
```json
{
    "class_subject_id": 123,
    "exam_id": 50,
    "results": [
        {
            "student_id": 1001,
            "marks": 90.0,
            "grade": "A",
            "remark": "Updated: Excellent work"
        }
    ]
}
```

---

## Grade Calculation

If grade is not provided, it will be automatically calculated based on marks:

| Marks Range | Grade | Description |
|-------------|-------|-------------|
| 75 - 100 | A | Excellent |
| 65 - 74 | B | Very Good |
| 45 - 64 | C | Good |
| 30 - 44 | D | Pass |
| 0 - 29 | F | Fail |

---

## Important Notes

1. **Authentication:** All requests require authentication headers (user_id, user_type, schoolID, teacherID)

2. **Authorization:** Teachers can only save results for subjects assigned to them

3. **Result Entry:** Results can only be saved for examinations where `enter_result` is `true`

4. **Term Closure:** Results cannot be edited for examinations in closed terms. New results can still be added

5. **Partial Updates:** When saving results, only provided fields are updated. Fields not included remain unchanged

6. **Excel Upload:** 
   - Maximum file size: 10MB
   - Supported formats: .xlsx, .xls
   - Invalid rows will be skipped and reported in the `errors` array
   - First row can be headers (will be skipped)

7. **Transaction Safety:** All operations are wrapped in database transactions. If any error occurs, all changes are rolled back

8. **Duplicate Results:** If a result already exists for a student, it will be updated. Otherwise, a new result will be created

9. **Validation:** All student IDs must exist in the database. Invalid student IDs will cause validation errors

10. **Marks Range:** Marks must be between 0 and 100. Values outside this range will cause validation errors

---

## Error Handling

### Common Errors and Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| 401 Unauthorized | Missing or invalid authentication headers | Include all required headers: user_id, user_type, schoolID, teacherID |
| 403 Forbidden - Term Closed | Attempting to edit results in closed term | Check term status before attempting to edit |
| 403 Forbidden - Result Entry Disabled | Result entry is disabled for this exam | Verify exam has `enter_result: true` |
| 404 Not Found | Class subject not found or unauthorized | Verify class_subject_id belongs to the teacher |
| 422 Validation Error | Invalid request data | Check validation rules and ensure all required fields are provided |
| 500 Internal Server Error | Server-side error | Check error message for details, contact support if issue persists |

---

## Testing Checklist

- [ ] Save single student result
- [ ] Save multiple students results
- [ ] Update existing result
- [ ] Save result with only marks (grade auto-calculated)
- [ ] Save result with all fields
- [ ] Upload valid Excel file
- [ ] Upload Excel file with errors (verify error reporting)
- [ ] Test with invalid class_subject_id
- [ ] Test with invalid exam_id
- [ ] Test with invalid student_id
- [ ] Test with marks outside 0-100 range
- [ ] Test with missing required fields
- [ ] Test with closed term (should fail)
- [ ] Test with result entry disabled (should fail)
- [ ] Test authentication (missing headers should fail)

---

## Support

For issues or questions regarding POST requests:
1. Check validation errors in response
2. Verify authentication headers are correct
3. Ensure all required fields are provided
4. Check examination status (enter_result, term closure)
5. Contact the development team

**Last Updated:** January 2025
