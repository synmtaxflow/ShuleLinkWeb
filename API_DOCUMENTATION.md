# ShuleLink API Documentation

## Base URL
```
http://10.0.2.2:8000/api
```

**Note:** For Flutter Android Emulator, use `10.0.2.2` to access the host machine's localhost. For physical devices, use your actual server IP address.

---

## Parent Dashboard API

### Endpoint
```
GET /parent/dashboard
```

### Description
This endpoint returns all dashboard data for a parent including parent details, students list, statistics, recent results, attendance records, book borrows, upcoming exams, and notifications.

**Important:** All data is filtered by `parentID` and `schoolID` to ensure data security and proper multi-school support.

### Request Parameters

#### Query Parameters (Recommended)
- `parentID` (integer, required) - The parent's ID
- `schoolID` (integer, required) - The school's ID

#### Alternative: HTTP Headers
You can also send these parameters as HTTP headers:
- `parentID` (integer, required)
- `schoolID` (integer, required)

### Example Request

#### Using Query Parameters
```http
GET http://192.168.100.104:8000/api/parent/dashboard?parentID=1&schoolID=1
```

#### Using cURL
```bash
curl -X GET "http://192.168.100.104:8000/api/parent/dashboard?parentID=1&schoolID=1" \
  -H "Accept: application/json"
```

#### Using HTTP Headers
```http
GET http://192.168.100.104:8000/api/parent/dashboard
Headers:
  parentID: 1
  schoolID: 1
```

### Success Response (200 OK)

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
      "email": "john.smith@example.com",
      "occupation": "Engineer",
      "address": "123 Main Street",
      "photo": "http://192.168.100.104:8000/userImages/parent_photo.jpg",
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
        "photo": "http://192.168.100.104:8000/userImages/student_photo.jpg",
        "class": {
          "class_name": "Form One",
          "subclass_name": "A"
        },
        "attendance_percentage": 85.5
      }
    ],
    "recent_results": [
      {
        "resultID": 1,
        "student": {
          "studentID": 1,
          "first_name": "Jane",
          "last_name": "Smith"
        },
        "examination": {
          "examID": 1,
          "exam_name": "Midterm Examination"
        },
        "subject": {
          "subject_name": "Mathematics"
        },
        "marks": "85.50",
        "grade": "A",
        "date": "2024-01-15"
      }
    ],
    "recent_attendance": [
      {
        "attendanceID": 1,
        "student": {
          "studentID": 1,
          "first_name": "Jane",
          "last_name": "Smith"
        },
        "class": {
          "class_name": "Form One",
          "subclass_name": "A"
        },
        "attendance_date": "2024-01-20",
        "status": "Present",
        "remark": null
      }
    ],
    "attendance_stats": {
      "1": {
        "total": 20,
        "present": 17,
        "percentage": 85.0
      }
    },
    "active_book_borrows": [
      {
        "borrowID": 1,
        "student": {
          "studentID": 1,
          "first_name": "Jane",
          "last_name": "Smith"
        },
        "book": {
          "book_title": "Mathematics Textbook"
        },
        "borrow_date": "2024-01-10",
        "expected_return_date": "2024-01-25",
        "status": "borrowed",
        "is_overdue": false
      }
    ],
    "upcoming_exams": [
      {
        "exam_timetableID": 1,
        "examination": {
          "examID": 1,
          "exam_name": "Final Examination"
        },
        "subject": {
          "subject_name": "Mathematics"
        },
        "class": {
          "class_name": "Form One",
          "subclass_name": "A"
        },
        "exam_date": "2024-02-15",
        "start_time": "09:00:00",
        "end_time": "11:00:00"
      }
    ],
    "notifications": [
      {
        "type": "result",
        "icon": "bi-trophy",
        "color": "success",
        "title": "New Result Available",
        "message": "Jane Smith - Midterm Examination",
        "date": "2024-01-15 10:30:00",
        "link": "#"
      },
      {
        "type": "attendance",
        "icon": "bi-exclamation-triangle",
        "color": "warning",
        "title": "Attendance Alert",
        "message": "Jane Smith was Late",
        "date": "2024-01-20 08:00:00",
        "link": "#"
      },
      {
        "type": "exam",
        "icon": "bi-calendar-event",
        "color": "info",
        "title": "Upcoming Exam",
        "message": "Final Examination - Mathematics",
        "date": "2024-02-15 09:00:00",
        "link": "#"
      }
    ]
  }
}
```

### Error Responses

#### 400 Bad Request - Missing Parameters
```json
{
  "success": false,
  "message": "Parent ID and School ID are required"
}
```

#### 404 Not Found - Parent Not Found
```json
{
  "success": false,
  "message": "Parent not found"
}
```

### Data Filtering

All data returned by this API is filtered to ensure:
1. **Parent-specific data**: Only data related to the specified `parentID` is returned
2. **School-specific data**: All data is filtered by `schoolID` to support multi-school environments
3. **Security**: Parents can only access data for their own children and their school

### Data Included

1. **Parent Information**: Complete parent profile including photo URL
2. **School Information**: School details for the parent's school
3. **Statistics**: 
   - Total number of children
   - Active students count
   - Gender distribution (male/female)
   - Recent results count
   - Upcoming exams count
4. **Students List**: All children of the parent with class information and attendance percentage
5. **Recent Results**: Last 5 results for all children
6. **Recent Attendance**: Last 7 days attendance records (up to 10 records)
7. **Attendance Statistics**: Monthly attendance percentage per student
8. **Active Book Borrows**: All currently borrowed books by children
9. **Upcoming Exams**: Exams scheduled in the next 30 days
10. **Notifications**: Combined notifications from results, attendance alerts, and upcoming exams

### Notes for Flutter Integration

1. **Photo URLs**: Photo URLs are returned as full URLs. Make sure your Flutter app can handle these URLs properly.
2. **Date Formats**: All dates are returned in `Y-m-d` format (e.g., "2024-01-15")
3. **Time Formats**: Times are returned as strings in `H:i:s` format (e.g., "09:00:00")
4. **Null Values**: Some fields may be `null` if data is not available. Always check for null values in your Flutter app.
5. **Pagination**: Currently, the API returns limited records (e.g., last 5 results, last 10 attendance records). For full data, you may need additional endpoints.
6. **Authentication**: This endpoint currently doesn't require authentication tokens. Consider adding authentication in production.

---

## Other Available Endpoints

### Health Check
```
GET /health
```
Check if the API is running.

### Login
```
POST /login
```
Authenticate a user (Admin, Teacher, or Parent).

### Logout
```
POST /logout
```
Logout the current user.

### Get User Profile
```
GET /user/profile
```
Get authenticated user's profile information.

---

## Parent Results API

### Endpoint
```
GET /api/parent/results
POST /api/parent/results
```

### Description
This endpoint returns all examination results for a parent's students. It includes detailed results with divisions, grades, points, positions, and subject-wise breakdowns. Results are grouped by examination and student.

### Request Parameters

#### Required Parameters
- `parentID` (integer, required) - The parent's ID
- `schoolID` (integer, required) - The school's ID

#### Optional Filter Parameters
- `student` (integer, optional) - Filter by specific student ID
- `year` (string, optional) - Filter by examination year (e.g., "2024")
- `exam` (integer, optional) - Filter by specific examination ID

### Example Request

#### Using GET with Query Parameters
```http
GET http://10.0.2.2:8000/api/parent/results?parentID=1&schoolID=3
```

#### Using GET with Filters
```http
GET http://10.0.2.2:8000/api/parent/results?parentID=1&schoolID=3&student=5&year=2024
```

#### Using POST with JSON Body
```http
POST http://10.0.2.2:8000/api/parent/results
Content-Type: application/json

{
  "parentID": 1,
  "schoolID": 3,
  "student": 5,
  "year": "2024",
  "exam": 10
}
```

#### Using cURL (GET)
```bash
curl -X GET "http://10.0.2.2:8000/api/parent/results?parentID=1&schoolID=3" \
  -H "Accept: application/json"
```

#### Using cURL (POST)
```bash
curl -X POST "http://10.0.2.2:8000/api/parent/results" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "parentID": 1,
    "schoolID": 3,
    "student": 5,
    "year": "2024"
  }'
```

### Response Format

#### Success Response (200 OK)
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
      "schoolID": 3,
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
    "years": ["2024", "2023", "2022"]
  }
}
```

#### Error Response (400 Bad Request)
```json
{
  "success": false,
  "message": "parentID and schoolID are required"
}
```

#### Error Response (404 Not Found)
```json
{
  "success": false,
  "message": "Parent not found"
}
```

#### Error Response (500 Internal Server Error)
```json
{
  "success": false,
  "message": "Error retrieving results: [error details]"
}
```

### Response Fields Description

#### Parent Object
- `parentID` - Unique parent identifier
- `first_name` - Parent's first name
- `middle_name` - Parent's middle name (optional)
- `last_name` - Parent's last name
- `phone` - Parent's phone number
- `email` - Parent's email address

#### School Object
- `schoolID` - Unique school identifier
- `school_name` - Name of the school
- `school_type` - Type of school (Primary or Secondary)

#### Student Object
- `studentID` - Unique student identifier
- `first_name` - Student's first name
- `middle_name` - Student's middle name (optional)
- `last_name` - Student's last name
- `admission_number` - Student's admission number
- `class` - Student's class name (e.g., "form_one", "form_two")
- `subclass` - Student's subclass/stream (e.g., "A", "B")

#### Result Object
- `exam` - Examination details
  - `examID` - Unique examination identifier
  - `exam_name` - Name of the examination
  - `year` - Year of the examination
  - `start_date` - Examination start date
  - `end_date` - Examination end date
- `student` - Student details (same structure as Student Object above)
- `summary` - Overall result summary
  - `subject_count` - Number of subjects taken
  - `total_marks` - Sum of all marks
  - `average_marks` - Average marks across all subjects
  - `total_points` - Total points (calculated based on best subjects)
  - `total_division` - Overall division (e.g., "I.15", "II.18") - for Secondary schools
  - `total_grade` - Overall grade (e.g., "A", "B") - for Primary schools
  - `display_label` - Label to display ("Division" or "Grade")
  - `position` - Student's position in class
  - `total_students` - Total number of students who took the exam
- `subjects` - Array of subject results
  - `resultID` - Unique result identifier
  - `subject` - Subject details object
    - `subjectID` - Unique subject identifier
    - `subject_name` - Name of the subject
  - `marks` - Marks obtained in the subject
  - `grade` - Grade obtained (A, B, C, D, E, F) - for Secondary schools
  - `division` - Division for Primary schools (Division One, Two, Three, Four, Zero)
  - `points` - Points for the subject (1-6 for O-Level, 0-5 for A-Level)
  - `remark` - Teacher's remark

#### Examinations Array
List of all examinations that have approved results for the parent's students.

#### Years Array
List of unique years from all examinations.

### Grading System

#### Primary School
- **Division One**: 75% and above
- **Division Two**: 50% - 74%
- **Division Three**: 30% - 49%
- **Division Four**: 0% - 29%
- **Division Zero**: No marks or below 0%

#### Secondary School - O-Level (Form 1-4)
- **Grade A**: 75% and above (1 point)
- **Grade B**: 65% - 74% (2 points)
- **Grade C**: 45% - 64% (3 points)
- **Grade D**: 30% - 44% (4 points)
- **Grade E**: 20% - 29% (5 points)
- **Grade F**: Below 20% (6 points)

**Division Calculation (Best 7 subjects):**
- **Division I**: 7-17 points
- **Division II**: 18-21 points
- **Division III**: 22-25 points
- **Division IV**: 26-33 points
- **Division 0**: Above 33 points

#### Secondary School - A-Level (Form 5-6)
- **Grade A**: 80% and above (5 points)
- **Grade B**: 70% - 79% (4 points)
- **Grade C**: 60% - 69% (3 points)
- **Grade D**: 50% - 59% (2 points)
- **Grade E**: 40% - 49% (1 point)
- **Grade S/F**: Below 40% (0 points)

**Division Calculation (Best 3 subjects):**
- **Division I**: 12-15 points
- **Division II**: 9-11 points
- **Division III**: 6-8 points
- **Division IV**: 3-5 points
- **Division 0**: Below 3 points

### Postman Testing

#### Collection Setup
1. Create a new request in Postman
2. Set method to **GET** or **POST**
3. Set URL: `http://10.0.2.2:8000/api/parent/results`

#### For GET Request:
- Go to **Params** tab
- Add parameters:
  - Key: `parentID`, Value: `1`
  - Key: `schoolID`, Value: `3`
  - Key: `student` (optional), Value: `5`
  - Key: `year` (optional), Value: `2024`
  - Key: `exam` (optional), Value: `10`

#### For POST Request:
- Go to **Body** tab
- Select **raw** and **JSON**
- Enter JSON:
```json
{
  "parentID": 1,
  "schoolID": 3,
  "student": 5,
  "year": "2024",
  "exam": 10
}
```

#### Headers
- Add header: `Accept: application/json`
- For POST: `Content-Type: application/json`

### Flutter Usage Example

#### 1. Add HTTP Package
Add to `pubspec.yaml`:
```yaml
dependencies:
  http: ^1.1.0
```

#### 2. Create API Service Class
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ParentResultsAPI {
  static const String baseUrl = 'http://10.0.2.2:8000/api/parent/results';
  
  Future<Map<String, dynamic>> getResults({
    required int parentID,
    required int schoolID,
    int? studentID,
    String? year,
    int? examID,
  }) async {
    try {
      // Build query parameters
      final queryParams = {
        'parentID': parentID.toString(),
        'schoolID': schoolID.toString(),
      };
      
      if (studentID != null) queryParams['student'] = studentID.toString();
      if (year != null) queryParams['year'] = year;
      if (examID != null) queryParams['exam'] = examID.toString();
      
      final uri = Uri.parse(baseUrl).replace(queryParameters: queryParams);
      
      final response = await http.get(
        uri,
        headers: {
          'Accept': 'application/json',
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
}
```

#### 3. Create Model Classes
```dart
class ParentResult {
  final bool success;
  final String message;
  final ResultData data;
  
  ParentResult({
    required this.success,
    required this.message,
    required this.data,
  });
  
  factory ParentResult.fromJson(Map<String, dynamic> json) {
    return ParentResult(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: ResultData.fromJson(json['data'] ?? {}),
    );
  }
}

class ResultData {
  final ParentInfo parent;
  final SchoolInfo school;
  final List<StudentInfo> students;
  final List<ExamInfo> examinations;
  final List<String> years;
  final Map<String, dynamic> filters;
  final Map<String, dynamic> statistics;
  final List<ExamResult> results;
  
  ResultData({
    required this.parent,
    required this.school,
    required this.students,
    required this.examinations,
    required this.years,
    required this.filters,
    required this.statistics,
    required this.results,
  });
  
  factory ResultData.fromJson(Map<String, dynamic> json) {
    return ResultData(
      parent: ParentInfo.fromJson(json['parent'] ?? {}),
      school: SchoolInfo.fromJson(json['school'] ?? {}),
      students: (json['students'] as List? ?? [])
          .map((e) => StudentInfo.fromJson(e))
          .toList(),
      examinations: (json['examinations'] as List? ?? [])
          .map((e) => ExamInfo.fromJson(e))
          .toList(),
      years: (json['years'] as List? ?? []).map((e) => e.toString()).toList(),
      filters: json['filters'] ?? {},
      statistics: json['statistics'] ?? {},
      results: (json['results'] as List? ?? [])
          .map((e) => ExamResult.fromJson(e))
          .toList(),
    );
  }
}

class ExamResult {
  final ExamInfo exam;
  final StudentInfo student;
  final ResultSummary summary;
  final List<SubjectResult> subjects;
  
  ExamResult({
    required this.exam,
    required this.student,
    required this.summary,
    required this.subjects,
  });
  
  factory ExamResult.fromJson(Map<String, dynamic> json) {
    return ExamResult(
      exam: ExamInfo.fromJson(json['exam'] ?? {}),
      student: StudentInfo.fromJson(json['student'] ?? {}),
      summary: ResultSummary.fromJson(json['summary'] ?? {}),
      subjects: (json['subjects'] as List? ?? [])
          .map((e) => SubjectResult.fromJson(e))
          .toList(),
    );
  }
}

class ResultSummary {
  final int subjectCount;
  final double totalMarks;
  final double averageMarks;
  final int totalPoints;
  final String? totalDivision;
  final String? totalGrade;
  final String displayLabel;
  final int? position;
  final int totalStudents;
  
  ResultSummary({
    required this.subjectCount,
    required this.totalMarks,
    required this.averageMarks,
    required this.totalPoints,
    this.totalDivision,
    this.totalGrade,
    required this.displayLabel,
    this.position,
    required this.totalStudents,
  });
  
  factory ResultSummary.fromJson(Map<String, dynamic> json) {
    return ResultSummary(
      subjectCount: json['subject_count'] ?? 0,
      totalMarks: (json['total_marks'] ?? 0).toDouble(),
      averageMarks: (json['average_marks'] ?? 0).toDouble(),
      totalPoints: json['total_points'] ?? 0,
      totalDivision: json['total_division'],
      totalGrade: json['total_grade'],
      displayLabel: json['display_label'] ?? 'Grade',
      position: json['position'],
      totalStudents: json['total_students'] ?? 0,
    );
  }
}

class SubjectResult {
  final int? resultID;
  final SubjectInfo subject;
  final double? marks;
  final String? grade;
  final String? division;
  final int? points;
  final String? remark;
  
  SubjectResult({
    this.resultID,
    required this.subject,
    this.marks,
    this.grade,
    this.division,
    this.points,
    this.remark,
  });
  
  factory SubjectResult.fromJson(Map<String, dynamic> json) {
    return SubjectResult(
      resultID: json['resultID'],
      subject: SubjectInfo.fromJson(json['subject'] ?? {}),
      marks: json['marks']?.toDouble(),
      grade: json['grade'],
      division: json['division'],
      points: json['points'],
      remark: json['remark'],
    );
  }
}

// Add other model classes (ParentInfo, SchoolInfo, StudentInfo, ExamInfo, SubjectInfo)
// based on the API response structure
```

#### 4. Use in Widget
```dart
import 'package:flutter/material.dart';

class ResultsPage extends StatefulWidget {
  final int parentID;
  final int schoolID;
  
  const ResultsPage({
    Key? key,
    required this.parentID,
    required this.schoolID,
  }) : super(key: key);
  
  @override
  _ResultsPageState createState() => _ResultsPageState();
}

class _ResultsPageState extends State<ResultsPage> {
  final ParentResultsAPI _api = ParentResultsAPI();
  ParentResult? _result;
  bool _isLoading = true;
  String? _error;
  
  @override
  void initState() {
    super.initState();
    _loadResults();
  }
  
  Future<void> _loadResults() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    
    try {
      final response = await _api.getResults(
        parentID: widget.parentID,
        schoolID: widget.schoolID,
      );
      
      setState(() {
        _result = ParentResult.fromJson(response);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }
  
  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }
    
    if (_error != null) {
      return Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text('Error: $_error'),
              ElevatedButton(
                onPressed: _loadResults,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }
    
    if (_result == null || _result!.data.results.isEmpty) {
      return const Scaffold(
        body: Center(child: Text('No results found')),
      );
    }
    
    return Scaffold(
      appBar: AppBar(title: const Text('Results')),
      body: ListView.builder(
        itemCount: _result!.data.results.length,
        itemBuilder: (context, index) {
          final examResult = _result!.data.results[index];
          return Card(
            margin: const EdgeInsets.all(8),
            child: ExpansionTile(
              title: Text('${examResult.exam.examName} - ${examResult.student.firstName}'),
              subtitle: Text('Year: ${examResult.exam.year}'),
              children: [
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Total Marks: ${examResult.summary.totalMarks}'),
                      Text('Average: ${examResult.summary.averageMarks}'),
                      if (examResult.summary.position != null)
                        Text('Position: ${examResult.summary.position}/${examResult.summary.totalStudents}'),
                      const SizedBox(height: 16),
                      const Text('Subjects:', style: TextStyle(fontWeight: FontWeight.bold)),
                      ...examResult.subjects.map((subject) => ListTile(
                        title: Text(subject.subject.subjectName),
                        trailing: Text('${subject.marks ?? 0} (${subject.grade ?? subject.division ?? "N/A"})'),
                      )),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
```

### Notes
- Only **approved** results are returned
- Results are grouped by examination and student
- Position is calculated based on all students in the same subclass who took the exam
- Total points calculation uses best 7 subjects for O-Level and best 3 for A-Level
- All dates are returned in YYYY-MM-DD format
- For Flutter Android Emulator, use `10.0.2.2` as the server IP
- For physical devices, replace `10.0.2.2` with your actual server IP address

---

## Parent Attendance API

### Endpoint
```
GET /api/parent/attendance
POST /api/parent/attendance
```

### Description
This endpoint returns attendance records for a parent's students. It includes overview statistics (present, absent, late, excused counts and attendance rate) and detailed daily records. Records can be filtered by student, year, month, or specific date.

### Request Parameters

#### Required Parameters
- `parentID` (integer, required) - The parent's ID
- `schoolID` (integer, required) - The school's ID

#### Optional Filter Parameters
- `student` (integer, optional) - Filter by specific student ID
- `year` (string, optional) - Filter by year (e.g., "2024"). Defaults to current year
- `month` (string, optional) - Filter by month (1-12). Defaults to current month. Required when `search_type` is "month"
- `date` (string, optional) - Filter by specific date (YYYY-MM-DD format). Required when `search_type` is "date"
- `search_type` (string, optional) - Type of search: "month", "year", or "date". Defaults to "month"

### Example Request

#### Using GET with Query Parameters (Month Search)
```http
GET http://192.168.100.104:8000/api/parent/attendance?parentID=1&schoolID=3&student=5&year=2024&month=3&search_type=month
```

#### Using GET with Year Search
```http
GET http://192.168.100.104:8000/api/parent/attendance?parentID=1&schoolID=3&year=2024&search_type=year
```

#### Using GET with Date Search
```http
GET http://192.168.100.104:8000/api/parent/attendance?parentID=1&schoolID=3&date=2024-03-15&search_type=date
```

#### Using POST with JSON Body
```http
POST http://192.168.100.104:8000/api/parent/attendance
Content-Type: application/json

{
  "parentID": 1,
  "schoolID": 3,
  "student": 5,
  "year": "2024",
  "month": "3",
  "search_type": "month"
}
```

#### Using cURL (GET)
```bash
curl -X GET "http://192.168.100.104:8000/api/parent/attendance?parentID=1&schoolID=3&year=2024&month=3&search_type=month" \
  -H "Accept: application/json"
```

#### Using cURL (POST)
```bash
curl -X POST "http://192.168.100.104:8000/api/parent/attendance" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "parentID": 1,
    "schoolID": 3,
    "student": 5,
    "year": "2024",
    "month": "3",
    "search_type": "month"
  }'
```

### Response Format

#### Success Response (200 OK)
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
      "schoolID": 3,
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
    "years": ["2024", "2023", "2022"],
    "filters": {
      "student": "5",
      "year": "2024",
      "month": "3",
      "date": "",
      "search_type": "month"
    },
    "overview": {
      "total_days": 22,
      "present": 18,
      "absent": 2,
      "late": 1,
      "excused": 1,
      "attendance_rate": 81.82
    },
    "daily_records": [
      {
        "attendanceID": 123,
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
        "remark": "On time",
        "created_at": "2024-03-15 08:30:00"
      }
    ],
    "grouped_records": [
      {
        "date": "2024-03-15",
        "date_formatted": "Friday, March 15, 2024",
        "status": "Present",
        "remark": "On time",
        "students": [
          {
            "studentID": 5,
            "first_name": "Jane",
            "middle_name": "Doe",
            "last_name": "Smith",
            "admission_number": "STU001",
            "status": "Present",
            "remark": "On time"
          }
        ]
      }
    ]
  }
}
```

#### Error Response (400 Bad Request)
```json
{
  "success": false,
  "message": "parentID and schoolID are required"
}
```

#### Error Response (404 Not Found)
```json
{
  "success": false,
  "message": "Parent not found"
}
```

#### Error Response (500 Internal Server Error)
```json
{
  "success": false,
  "message": "Error retrieving attendance records: [error details]"
}
```

### Response Fields Description

#### Parent Object
- `parentID` - Unique parent identifier
- `first_name` - Parent's first name
- `middle_name` - Parent's middle name (optional)
- `last_name` - Parent's last name
- `phone` - Parent's phone number
- `email` - Parent's email address

#### School Object
- `schoolID` - Unique school identifier
- `school_name` - Name of the school
- `school_type` - Type of school (Primary or Secondary)

#### Students Array
List of all students belonging to the parent.

#### Years Array
List of unique years from all attendance records.

#### Filters Object
Shows the filters that were applied to the query.

#### Overview Object
Statistics summary for the filtered period:
- `total_days` - Total number of attendance records
- `present` - Number of days marked as Present
- `absent` - Number of days marked as Absent
- `late` - Number of days marked as Late
- `excused` - Number of days marked as Excused
- `attendance_rate` - Percentage of present days (calculated as: (present / total_days) * 100)

#### Daily Records Array
Detailed list of all attendance records with full student information.

#### Grouped Records Array
Attendance records grouped by date. Useful for displaying daily attendance summaries.

### Attendance Status Values
- **Present** - Student was present
- **Absent** - Student was absent
- **Late** - Student arrived late
- **Excused** - Student was excused (e.g., medical leave)

### Postman Testing

#### Collection Setup
1. Create a new request in Postman
2. Set method to **GET** or **POST**
3. Set URL: `http://192.168.100.104:8000/api/parent/attendance`

#### For GET Request (Month Search):
- Go to **Params** tab
- Add parameters:
  - Key: `parentID`, Value: `1`
  - Key: `schoolID`, Value: `3`
  - Key: `student` (optional), Value: `5`
  - Key: `year`, Value: `2024`
  - Key: `month`, Value: `3`
  - Key: `search_type`, Value: `month`

#### For GET Request (Year Search):
- Go to **Params** tab
- Add parameters:
  - Key: `parentID`, Value: `1`
  - Key: `schoolID`, Value: `3`
  - Key: `year`, Value: `2024`
  - Key: `search_type`, Value: `year`

#### For GET Request (Date Search):
- Go to **Params** tab
- Add parameters:
  - Key: `parentID`, Value: `1`
  - Key: `schoolID`, Value: `3`
  - Key: `date`, Value: `2024-03-15`
  - Key: `search_type`, Value: `date`

#### For POST Request:
- Go to **Body** tab
- Select **raw** and **JSON**
- Enter JSON:
```json
{
  "parentID": 1,
  "schoolID": 3,
  "student": 5,
  "year": "2024",
  "month": "3",
  "search_type": "month"
}
```

#### Headers
- Add header: `Accept: application/json`
- For POST: `Content-Type: application/json`

### Notes
- If no filters are applied, only parent, school, students, and years data will be returned (no attendance records)
- `search_type` must be one of: "month", "year", or "date"
- When `search_type` is "month", both `year` and `month` should be provided
- When `search_type` is "year", `year` should be provided
- When `search_type` is "date", `date` should be provided in YYYY-MM-DD format
- All dates are returned in YYYY-MM-DD format
- `date_formatted` in grouped_records provides a human-readable date format
- Attendance rate is calculated as: (Present days / Total days) × 100

---

## Server Information

- **Server IP**: 192.168.100.104
- **Port**: 8000
- **Base URL**: http://192.168.100.104:8000/api

---

## Support

For issues or questions, please contact the development team.
