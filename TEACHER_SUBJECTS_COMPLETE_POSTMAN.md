# Teacher Subjects API - Complete Postman Testing Guide

## Base URL
```
http://192.168.100.104:8003/api
```

## Authentication
All endpoints require authentication via headers (stateless authentication).

**Required Headers (All Endpoints):**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

---

## All Available Endpoints

### GET Endpoints

#### 1. Get All Teacher Subjects
**URL:** `GET /api/teacher/subjects`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**cURL:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

---

#### 2. Get Subject Students
**URL:** `GET /api/teacher/subjects/{classSubjectID}/students`

**Example:** `GET /api/teacher/subjects/123/students`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**cURL:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/students" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

---

#### 3. Get Examinations for Subject
**URL:** `GET /api/teacher/subjects/{classSubjectID}/examinations`

**Example:** `GET /api/teacher/subjects/123/examinations`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**cURL:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/examinations" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

---

#### 4. Get Subject Results (All)
**URL:** `GET /api/teacher/subjects/{classSubjectID}/results`

**Example:** `GET /api/teacher/subjects/123/results`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**cURL:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/results" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

---

#### 5. Get Subject Results (Specific Exam)
**URL:** `GET /api/teacher/subjects/{classSubjectID}/results/{examID}`

**Example:** `GET /api/teacher/subjects/123/results/50`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**cURL:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/results/50" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

---

#### 6. Download Excel Template
**URL:** `GET /api/teacher/subjects/{classSubjectID}/results/{examID}/download-excel-template`

**Example:** `GET /api/teacher/subjects/123/results/50/download-excel-template`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**cURL:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/results/50/download-excel-template" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456" \
  --output results_template.xlsx
```

**Note:** This endpoint returns a file (Excel), not JSON. The file will be downloaded.

---

#### 7. Get Session Attendance Data
**URL:** `GET /api/teacher/subjects/{classSubjectID}/session-attendance`

**Example:** `GET /api/teacher/subjects/123/session-attendance`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**Query Parameters:**
- `attendance_date` (string, optional) - Date filter: YYYY-MM-DD format
- `filter_type` (string, optional) - "date" or "month" (default: "date")
- `month` (string, optional) - Month filter: YYYY-MM format (required if filter_type is "month")

**Example with Date Filter:**
```
GET /api/teacher/subjects/123/session-attendance?attendance_date=2024-01-15&filter_type=date
```

**Example with Month Filter:**
```
GET /api/teacher/subjects/123/session-attendance?month=2024-01&filter_type=month
```

**cURL (Date Filter):**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/session-attendance?attendance_date=2024-01-15&filter_type=date" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

**cURL (Month Filter):**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/session-attendance?month=2024-01&filter_type=month" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

---

#### 8. Get Exam Attendance Data
**URL:** `GET /api/teacher/subjects/{classSubjectID}/exam-attendance`

**Example:** `GET /api/teacher/subjects/123/exam-attendance`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**Query Parameters:**
- `examID` (integer, required) - Examination ID
- `subjectID` (integer, required) - Subject ID

**Example:**
```
GET /api/teacher/subjects/123/exam-attendance?examID=50&subjectID=45
```

**cURL:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/exam-attendance?examID=50&subjectID=45" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

---

### POST Endpoints

#### 9. Save Subject Results
**URL:** `POST /api/teacher/subjects/results/save`

**Headers:**
```
Accept: application/json
Content-Type: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**Body (JSON):**
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

**cURL:**
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
      }
    ]
  }'
```

---

#### 10. Upload Excel Results
**URL:** `POST /api/teacher/subjects/results/upload-excel`

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**Body (form-data):**
```
class_subject_id: 123
exam_id: 50
excel_file: [Select File]
```

**cURL:**
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

## Postman Collection JSON (Complete - Import Ready)

```json
{
  "info": {
    "name": "Teacher Subjects API - Complete",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "1. Get All Subjects",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123",
            "description": "Replace with actual user ID from login"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4",
            "description": "Replace with actual school ID from login"
          },
          {
            "key": "teacherID",
            "value": "456",
            "description": "Replace with actual teacher ID from login"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects"]
        }
      }
    },
    {
      "name": "2. Get Subject Students",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/:classSubjectID/students",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", ":classSubjectID", "students"],
          "variable": [
            {
              "key": "classSubjectID",
              "value": "123",
              "description": "Class Subject ID"
            }
          ]
        }
      }
    },
    {
      "name": "3. Get Examinations",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/:classSubjectID/examinations",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", ":classSubjectID", "examinations"],
          "variable": [
            {
              "key": "classSubjectID",
              "value": "123"
            }
          ]
        }
      }
    },
    {
      "name": "4. Get All Results",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/:classSubjectID/results",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", ":classSubjectID", "results"],
          "variable": [
            {
              "key": "classSubjectID",
              "value": "123"
            }
          ]
        }
      }
    },
    {
      "name": "5. Get Results for Specific Exam",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/:classSubjectID/results/:examID",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", ":classSubjectID", "results", ":examID"],
          "variable": [
            {
              "key": "classSubjectID",
              "value": "123"
            },
            {
              "key": "examID",
              "value": "50"
            }
          ]
        }
      }
    },
    {
      "name": "6. Download Excel Template",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/:classSubjectID/results/:examID/download-excel-template",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", ":classSubjectID", "results", ":examID", "download-excel-template"],
          "variable": [
            {
              "key": "classSubjectID",
              "value": "123"
            },
            {
              "key": "examID",
              "value": "50"
            }
          ]
        }
      },
      "response": []
    },
    {
      "name": "7. Get Session Attendance (Date Filter)",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/:classSubjectID/session-attendance?attendance_date=2024-01-15&filter_type=date",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", ":classSubjectID", "session-attendance"],
          "query": [
            {
              "key": "attendance_date",
              "value": "2024-01-15"
            },
            {
              "key": "filter_type",
              "value": "date"
            }
          ],
          "variable": [
            {
              "key": "classSubjectID",
              "value": "123"
            }
          ]
        }
      }
    },
    {
      "name": "8. Get Session Attendance (Month Filter)",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/:classSubjectID/session-attendance?month=2024-01&filter_type=month",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", ":classSubjectID", "session-attendance"],
          "query": [
            {
              "key": "month",
              "value": "2024-01"
            },
            {
              "key": "filter_type",
              "value": "month"
            }
          ],
          "variable": [
            {
              "key": "classSubjectID",
              "value": "123"
            }
          ]
        }
      }
    },
    {
      "name": "9. Get Exam Attendance",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/:classSubjectID/exam-attendance?examID=50&subjectID=45",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", ":classSubjectID", "exam-attendance"],
          "query": [
            {
              "key": "examID",
              "value": "50"
            },
            {
              "key": "subjectID",
              "value": "45"
            }
          ],
          "variable": [
            {
              "key": "classSubjectID",
              "value": "123"
            }
          ]
        }
      }
    },
    {
      "name": "10. Save Results",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n    \"class_subject_id\": 123,\n    \"exam_id\": 50,\n    \"results\": [\n        {\n            \"student_id\": 1001,\n            \"marks\": 85.5,\n            \"grade\": \"A\",\n            \"remark\": \"Excellent\"\n        }\n    ]\n}",
          "options": {
            "raw": {
              "language": "json"
            }
          }
        },
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/results/save",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", "results", "save"]
        }
      }
    },
    {
      "name": "11. Upload Excel Results",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "user_id",
            "value": "123"
          },
          {
            "key": "user_type",
            "value": "Teacher"
          },
          {
            "key": "schoolID",
            "value": "4"
          },
          {
            "key": "teacherID",
            "value": "456"
          }
        ],
        "body": {
          "mode": "formdata",
          "formdata": [
            {
              "key": "class_subject_id",
              "value": "123",
              "type": "text"
            },
            {
              "key": "exam_id",
              "value": "50",
              "type": "text"
            },
            {
              "key": "excel_file",
              "type": "file",
              "src": []
            }
          ]
        },
        "url": {
          "raw": "http://192.168.100.104:8003/api/teacher/subjects/results/upload-excel",
          "protocol": "http",
          "host": ["192", "168", "100", "104"],
          "port": "8003",
          "path": ["api", "teacher", "subjects", "results", "upload-excel"]
        }
      }
    }
  ]
}
```

---

## Step-by-Step Testing Flow

### Step 1: Login
1. Call `POST /api/login` with username and password
2. Extract from response:
   - `data.user.id` → `user_id`
   - `data.user.user_type` → `user_type`
   - `data.schoolID` → `schoolID`
   - `data.teacherID` → `teacherID`

### Step 2: Get All Subjects
1. Use `GET /api/teacher/subjects`
2. Note the `class_subject_id` values you want to use

### Step 3: Get Students (Optional)
1. Use `GET /api/teacher/subjects/{classSubjectID}/students`
2. Note the `student_id` values

### Step 4: Get Examinations (Optional)
1. Use `GET /api/teacher/subjects/{classSubjectID}/examinations`
2. Note the `exam_id` values

### Step 5: Get Results (Optional)
1. Use `GET /api/teacher/subjects/{classSubjectID}/results`
2. Or `GET /api/teacher/subjects/{classSubjectID}/results/{examID}`

### Step 6: Download Excel Template (Optional)
1. Use `GET /api/teacher/subjects/{classSubjectID}/results/{examID}/download-excel-template`
2. File will be downloaded automatically

### Step 7: Save Results
1. Use `POST /api/teacher/subjects/results/save`
2. Include results array in body

### Step 8: Upload Excel Results (Alternative)
1. Use `POST /api/teacher/subjects/results/upload-excel`
2. Select Excel file in form-data

### Step 9: Get Session Attendance
1. Use `GET /api/teacher/subjects/{classSubjectID}/session-attendance`
2. Add query parameters for filtering

### Step 10: Get Exam Attendance
1. Use `GET /api/teacher/subjects/{classSubjectID}/exam-attendance`
2. Add query parameters: `examID` and `subjectID`

---

## Quick Reference Table

| # | Method | Endpoint | Description |
|---|--------|----------|-------------|
| 1 | GET | `/api/teacher/subjects` | Get all subjects |
| 2 | GET | `/api/teacher/subjects/{id}/students` | Get students for subject |
| 3 | GET | `/api/teacher/subjects/{id}/examinations` | Get examinations for subject |
| 4 | GET | `/api/teacher/subjects/{id}/results` | Get all results |
| 5 | GET | `/api/teacher/subjects/{id}/results/{examID}` | Get results for specific exam |
| 6 | GET | `/api/teacher/subjects/{id}/results/{examID}/download-excel-template` | Download Excel template |
| 7 | GET | `/api/teacher/subjects/{id}/session-attendance` | Get session attendance data |
| 8 | GET | `/api/teacher/subjects/{id}/exam-attendance` | Get exam attendance data |
| 9 | POST | `/api/teacher/subjects/results/save` | Save results |
| 10 | POST | `/api/teacher/subjects/results/upload-excel` | Upload Excel results |

---

## Response Examples

### Get All Subjects Response
```json
{
    "success": true,
    "message": "Subjects retrieved successfully",
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
        }
    ]
}
```

### Get Students Response
```json
{
    "success": true,
    "message": "Students retrieved successfully",
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
                "last_name": "Doe",
                "full_name": "John Doe",
                "gender": "Male",
                "photo": "http://192.168.100.104:8003/userImages/photo.jpg",
                "subclass_id": 25,
                "subclass_name": "Form 3A",
                "class_name": "Form 3",
                "has_health_condition": false
            }
        ],
        "total_students": 35
    }
}
```

### Session Attendance Response (Date Filter)
```json
{
    "success": true,
    "filter_type": "date",
    "data": [
        {
            "session_timetableID": 100,
            "day": "Monday",
            "start_time": "08:00:00",
            "end_time": "09:00:00",
            "attendance": [
                {
                    "studentID": 1001,
                    "name": "John Doe",
                    "status": "Present",
                    "remark": null
                }
            ]
        }
    ],
    "date": "2024-01-15"
}
```

### Session Attendance Response (Month Filter)
```json
{
    "success": true,
    "filter_type": "month",
    "data": [
        {
            "studentID": 1001,
            "name": "John Doe",
            "total_sessions": 20,
            "attended_sessions": 18,
            "present": 16,
            "absent": 2,
            "late": 0,
            "excused": 0
        }
    ],
    "total_sessions": 20,
    "month": "2024-01"
}
```

### Exam Attendance Response
```json
{
    "success": true,
    "data": {
        "subclasses": [
            {
                "subclassID": 25,
                "subclass_name": "Form 3A",
                "class_name": "Form 3",
                "class_display": "Form 3 - Form 3A",
                "present": 30,
                "absent": 5,
                "total": 35
            }
        ],
        "students": [
            {
                "studentID": 1001,
                "name": "John Doe",
                "subclassID": 25,
                "subclass_name": "Form 3A",
                "class_name": "Form 3",
                "class_display": "Form 3 - Form 3A",
                "status": "Present"
            }
        ]
    }
}
```

---

## Common Error Responses

### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body."
}
```

### 403 Forbidden
```json
{
    "success": false,
    "message": "Unauthorized. Invalid user type. This endpoint is for Teachers only."
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Class subject not found or unauthorized access."
}
```

### 422 Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "class_subject_id": ["The class subject id field is required."]
    }
}
```

---

## Testing Checklist

### GET Endpoints
- [ ] Get all subjects
- [ ] Get subject students
- [ ] Get examinations
- [ ] Get all results
- [ ] Get results for specific exam
- [ ] Download Excel template (file download)
- [ ] Get session attendance (date filter)
- [ ] Get session attendance (month filter)
- [ ] Get exam attendance

### POST Endpoints
- [ ] Save results (single student)
- [ ] Save results (multiple students)
- [ ] Save results (partial update)
- [ ] Upload Excel results

### Error Handling
- [ ] Test with missing headers
- [ ] Test with invalid user type
- [ ] Test with invalid class_subject_id
- [ ] Test with invalid exam_id
- [ ] Test with invalid student_id
- [ ] Test with marks outside 0-100 range

---

## Tips for Postman Testing

1. **Create Environment Variables:**
   - `base_url`: `http://192.168.100.104:8003/api`
   - `user_id`: `123`
   - `user_type`: `Teacher`
   - `schoolID`: `4`
   - `teacherID`: `456`

2. **Use Variables in URLs:**
   - `{{base_url}}/teacher/subjects`
   - `{{base_url}}/teacher/subjects/{{classSubjectID}}/students`

3. **Save Responses:**
   - Save `class_subject_id` from Get All Subjects response
   - Save `exam_id` from Get Examinations response
   - Save `student_id` from Get Students response

4. **Test Sequence:**
   - Always start with Get All Subjects
   - Use returned IDs for subsequent requests
   - Test GET endpoints before POST endpoints

---

**Last Updated:** January 2025
