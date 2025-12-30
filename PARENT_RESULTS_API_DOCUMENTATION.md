# Parent Results API Documentation

## Overview
This API allows parents to retrieve examination results and term reports for their children through a Flutter mobile application.

**Base URL:** `http://your-server-ip/api`

## Authentication
This API requires authentication. You need to login first using the `/api/login` endpoint to get parent credentials (parentID and schoolID).

## Endpoint

### Get Parent Results
Retrieve examination results or term reports for a parent's children.

**Endpoint:** `GET /api/parent/results` or `POST /api/parent/results`

**Authentication:** Required (parentID and schoolID from login)

---

## Request Parameters

### Query Parameters (GET) or Request Body (POST)

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `parentID` | integer | Yes | Parent ID obtained from login |
| `schoolID` | integer | Yes | School ID obtained from login |
| `student` | integer | Yes | Student ID to filter results |
| `year` | string | Yes | Academic year (e.g., "2024") |
| `term` | string | Yes | Term: `first_term`, `second_term`, or `third_term` |
| `type` | string | Yes | Type of results: `exam` or `report` |
| `exam` | integer | Conditional | Exam ID (required if `type="exam"`) |

### Example Request (GET)
```
GET /api/parent/results?parentID=4&schoolID=3&student=12&year=2024&term=first_term&type=exam&exam=26
```

### Example Request (POST)
```json
{
  "parentID": 4,
  "schoolID": 3,
  "student": 12,
  "year": "2024",
  "term": "first_term",
  "type": "exam",
  "exam": 26
}
```

---

## Response Format

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Results retrieved successfully",
  "data": {
    "parent": {
      "parentID": 4,
      "first_name": "John",
      "middle_name": "Doe",
      "last_name": "Smith",
      "phone": "+255123456789",
      "email": "john@example.com"
    },
    "school": {
      "schoolID": 3,
      "school_name": "Example School",
      "school_type": "Secondary"
    },
    "students": [
      {
        "studentID": 12,
        "first_name": "Jane",
        "middle_name": "",
        "last_name": "Smith",
        "admission_number": "ST001",
        "photo": "http://server-url/userImages/photo.jpg",
        "class": "Form One",
        "subclass": "A"
      }
    ],
    "examinations": [
      {
        "examID": 26,
        "exam_name": "Midterm Examination",
        "year": "2024",
        "term": "first_term",
        "start_date": "2024-03-01",
        "end_date": "2024-03-05"
      }
    ],
    "years": ["2024", "2023"],
    "terms": ["first_term", "second_term", "third_term"],
    "filters": {
      "student": "12",
      "year": "2024",
      "term": "first_term",
      "type": "exam",
      "exam": "26"
    },
    "statistics": {
      "total_students": 1,
      "total_examinations": 5,
      "total_results": 1
    },
    "results": [
      {
        "type": "exam",
        "exam": {
          "examID": 26,
          "exam_name": "Midterm Examination",
          "year": "2024",
          "term": "first_term",
          "start_date": "2024-03-01",
          "end_date": "2024-03-05"
        },
        "student": {
          "studentID": 12,
          "first_name": "Jane",
          "middle_name": "",
          "last_name": "Smith",
          "admission_number": "ST001",
          "photo": "http://server-url/userImages/photo.jpg",
          "class": "Form One",
          "subclass": "A"
        },
        "summary": {
          "subject_count": 7,
          "total_marks": 485,
          "average_marks": 69.3,
          "total_points": 14,
          "total_division": "II.14",
          "total_grade": null,
          "display_label": "Division",
          "position": 5,
          "total_students": 30
        },
        "subjects": [
          {
            "resultID": 123,
            "subject": {
              "subjectID": 5,
              "subject_name": "Mathematics"
            },
            "marks": 75,
            "grade": "A",
            "division": null,
            "points": 1,
            "remark": "Excellent"
          }
        ]
      }
    ]
  }
}
```

### For Report Type Response

When `type="report"`, the response structure is slightly different:

```json
{
  "success": true,
  "message": "Results retrieved successfully",
  "data": {
    "results": [
      {
        "type": "report",
        "student": {
          "studentID": 12,
          "first_name": "Jane",
          "middle_name": "",
          "last_name": "Smith",
          "admission_number": "ST001",
          "photo": "http://server-url/userImages/photo.jpg",
          "class": "Form One",
          "subclass": "A"
        },
        "term": "first_term",
        "year": "2024",
        "summary": {
          "overall_average": 68.5,
          "overall_grade": "B",
          "overall_division": null,
          "display_label": "Grade"
        },
        "exams": [
          {
            "examID": 26,
            "exam_name": "Midterm Examination",
            "year": "2024",
            "term": "first_term",
            "start_date": "2024-03-01",
            "end_date": "2024-03-05",
            "average": 69.3,
            "grade": "B",
            "division": null
          }
        ],
        "subjects": [
          {
            "subject_name": "Mathematics",
            "exams": [
              {
                "exam_name": "Midterm Examination",
                "marks": 75,
                "grade": "A"
              }
            ],
            "average": 75.0,
            "grade": "A"
          }
        ]
      }
    ]
  }
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "parentID and schoolID are required"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Parent not found"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Error retrieving results: [error details]"
}
```

---

## Response Fields Explanation

### Parent Object
- `parentID`: Unique parent identifier
- `first_name`, `middle_name`, `last_name`: Parent's name
- `phone`, `email`: Contact information

### School Object
- `schoolID`: Unique school identifier
- `school_name`: Name of the school
- `school_type`: "Primary" or "Secondary"

### Student Object
- `studentID`: Unique student identifier
- `first_name`, `middle_name`, `last_name`: Student's name
- `admission_number`: Student admission number
- `photo`: URL to student photo (if available)
- `class`: Main class name (e.g., "Form One")
- `subclass`: Subclass name (e.g., "A")

### Exam Object
- `examID`: Unique exam identifier
- `exam_name`: Name of the examination
- `year`: Academic year
- `term`: Term (first_term, second_term, third_term)
- `start_date`, `end_date`: Exam dates (YYYY-MM-DD format)

### Result Object (Exam Type)
- `type`: Always "exam" for exam results
- `exam`: Exam details
- `student`: Student details
- `summary`: Overall statistics
  - `subject_count`: Number of subjects
  - `total_marks`: Sum of all marks
  - `average_marks`: Average marks
  - `total_points`: Total points (for Secondary)
  - `total_division`: Division (e.g., "I.14", "II.21")
  - `total_grade`: Grade (A, B, C, D, E, F)
  - `display_label`: "Division" or "Grade"
  - `position`: Student position in class
  - `total_students`: Total students in class
- `subjects`: Array of subject results
  - `subject`: Subject details
  - `marks`: Marks obtained
  - `grade`: Grade (A, B, C, D, E, F)
  - `division`: Division (for Primary)
  - `points`: Points (for Secondary O-Level/A-Level)
  - `remark`: Teacher's remark

### Result Object (Report Type)
- `type`: Always "report" for term reports
- `student`: Student details
- `term`: Term name
- `year`: Academic year
- `summary`: Overall statistics
  - `overall_average`: Average across all exams
  - `overall_grade`: Overall grade
  - `overall_division`: Overall division
  - `display_label`: "Division" or "Grade"
- `exams`: Array of examinations in the term
  - Exam details with averages and grades
- `subjects`: Array of subject results across all exams
  - `subject_name`: Name of subject
  - `exams`: Array of exam results for this subject
    - `exam_name`: Name of exam
    - `marks`: Marks obtained
    - `grade`: Grade
  - `average`: Average marks across all exams
  - `grade`: Overall grade for the subject

---

## Grade/Division System

### Primary Schools
- **Divisions:** Division One, Division Two, Division Three, Division Four
- **Grade Range:**
  - Division One: 75-100
  - Division Two: 50-74
  - Division Three: 30-49
  - Division Four: 0-29

### Secondary Schools (O-Level: Form 1-4)
- **Divisions:** I.X, II.X, III.X, IV.X, 0.X (where X is total points)
- **Grades:** A, B, C, D, E, F
- **Points:** 1 (best) to 6 (worst)
- **Division Calculation:** Based on best 7 subjects
  - I: 7-17 points
  - II: 18-21 points
  - III: 22-25 points
  - IV: 26-33 points
  - 0: 34+ points

### Secondary Schools (A-Level: Form 5-6)
- **Divisions:** I.X, II.X, III.X, IV.X, 0.X (where X is total points)
- **Grades:** A, B, C, D, E, S/F
- **Points:** 5 (best) to 0 (worst)
- **Division Calculation:** Based on best 3 principal subjects
  - I: 12-15 points
  - II: 9-11 points
  - III: 6-8 points
  - IV: 3-5 points
  - 0: 0-2 points

---

## Flutter Integration Example

### Using HTTP Package

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ParentResultsAPI {
  final String baseUrl = 'http://your-server-ip/api';
  
  Future<Map<String, dynamic>> getParentResults({
    required int parentID,
    required int schoolID,
    required int studentID,
    required String year,
    required String term,
    required String type,
    int? examID,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/parent/results').replace(
        queryParameters: {
          'parentID': parentID.toString(),
          'schoolID': schoolID.toString(),
          'student': studentID.toString(),
          'year': year,
          'term': term,
          'type': type,
          if (examID != null) 'exam': examID.toString(),
        },
      );
      
      final response = await http.get(uri);
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          return data['data'];
        } else {
          throw Exception(data['message'] ?? 'Failed to fetch results');
        }
      } else {
        throw Exception('HTTP Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
}
```

### Using Dio Package

```dart
import 'package:dio/dio.dart';

class ParentResultsAPI {
  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://your-server-ip/api',
    connectTimeout: 5000,
    receiveTimeout: 3000,
  ));
  
  Future<Map<String, dynamic>> getParentResults({
    required int parentID,
    required int schoolID,
    required int studentID,
    required String year,
    required String term,
    required String type,
    int? examID,
  }) async {
    try {
      final response = await dio.get(
        '/parent/results',
        queryParameters: {
          'parentID': parentID,
          'schoolID': schoolID,
          'student': studentID,
          'year': year,
          'term': term,
          'type': type,
          if (examID != null) 'exam': examID,
        },
      );
      
      if (response.data['success'] == true) {
        return response.data['data'];
      } else {
        throw Exception(response.data['message'] ?? 'Failed to fetch results');
      }
    } on DioException catch (e) {
      throw Exception('Error: ${e.message}');
    }
  }
}
```

### Example Usage in Flutter

```dart
class ResultsScreen extends StatefulWidget {
  final int parentID;
  final int schoolID;
  final int studentID;
  
  @override
  _ResultsScreenState createState() => _ResultsScreenState();
}

class _ResultsScreenState extends State<ResultsScreen> {
  final api = ParentResultsAPI();
  Map<String, dynamic>? resultsData;
  bool isLoading = true;
  
  @override
  void initState() {
    super.initState();
    loadResults();
  }
  
  Future<void> loadResults() async {
    try {
      setState(() => isLoading = true);
      final data = await api.getParentResults(
        parentID: widget.parentID,
        schoolID: widget.schoolID,
        studentID: widget.studentID,
        year: '2024',
        term: 'first_term',
        type: 'exam',
        examID: 26,
      );
      setState(() {
        resultsData = data;
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Center(child: CircularProgressIndicator());
    }
    
    if (resultsData == null) {
      return Center(child: Text('No results available'));
    }
    
    final results = resultsData!['results'] as List;
    
    return ListView.builder(
      itemCount: results.length,
      itemBuilder: (context, index) {
        final result = results[index];
        if (result['type'] == 'exam') {
          return _buildExamResult(result);
        } else {
          return _buildReportResult(result);
        }
      },
    );
  }
  
  Widget _buildExamResult(Map<String, dynamic> result) {
    final summary = result['summary'];
    final subjects = result['subjects'] as List;
    
    return Card(
      child: Column(
        children: [
          Text(result['exam']['exam_name']),
          Text('Average: ${summary['average_marks']}'),
          Text('Position: ${summary['position']}/${summary['total_students']}'),
          ...subjects.map((subject) => ListTile(
            title: Text(subject['subject']['subject_name']),
            trailing: Text('${subject['marks']} (${subject['grade']})'),
          )),
        ],
      ),
    );
  }
  
  Widget _buildReportResult(Map<String, dynamic> result) {
    final summary = result['summary'];
    final subjects = result['subjects'] as List;
    
    return Card(
      child: Column(
        children: [
          Text('Term Report: ${result['term']} ${result['year']}'),
          Text('Overall Average: ${summary['overall_average']}'),
          ...subjects.map((subject) => ExpansionTile(
            title: Text(subject['subject_name']),
            subtitle: Text('Average: ${subject['average']} (${subject['grade']})'),
            children: (subject['exams'] as List).map((exam) => 
              ListTile(
                title: Text(exam['exam_name']),
                trailing: Text('${exam['marks']} (${exam['grade']})'),
              )
            ).toList(),
          )),
        ],
      ),
    );
  }
}
```

---

## Common Use Cases

### 1. Get Exam Results
```dart
final data = await api.getParentResults(
  parentID: 4,
  schoolID: 3,
  studentID: 12,
  year: '2024',
  term: 'first_term',
  type: 'exam',
  examID: 26,
);
```

### 2. Get Term Report
```dart
final data = await api.getParentResults(
  parentID: 4,
  schoolID: 3,
  studentID: 12,
  year: '2024',
  term: 'first_term',
  type: 'report',
);
```

### 3. Get Available Years and Terms
First call without filters to get available options:
```dart
// Get all results to see available years and terms
final allData = await api.getParentResults(
  parentID: 4,
  schoolID: 3,
  studentID: 12,
  year: '',
  term: '',
  type: 'exam',
);
final years = allData['years'] as List;
final terms = allData['terms'] as List;
```

---

## Notes

1. **Authentication:** You must login first to get `parentID` and `schoolID`
2. **Status Codes:** Results are only returned for exams with status "allowed" or "approved"
3. **Photo URLs:** Photo URLs are absolute URLs. Make sure your Flutter app can handle them
4. **Date Format:** All dates are in YYYY-MM-DD format
5. **Term Values:** Use exactly: `first_term`, `second_term`, or `third_term`
6. **Error Handling:** Always check `success` field in response
7. **Null Values:** Some fields may be null (e.g., photo, division, grade). Handle nulls appropriately

---

## Support

For issues or questions, please contact the API administrator or refer to the main API documentation.

