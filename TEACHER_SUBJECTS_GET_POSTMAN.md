# Teacher Subjects API - GET Requests (Postman Testing)

## Base URL
```
http://192.168.100.104:8003/api
```

---

## 1. Get All Teacher Subjects

### Request Details

**Method:** `GET`

**URL:**
```
http://192.168.100.104:8003/api/teacher/subjects
```

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**Request Parameters:** None

### Postman Setup Steps

1. **Method:** Select `GET`
2. **URL:** Enter `http://192.168.100.104:8003/api/teacher/subjects`
3. **Headers Tab:**
   - Add header: `Accept` = `application/json`
   - Add header: `user_id` = `123` (replace with actual user ID from login)
   - Add header: `user_type` = `Teacher`
   - Add header: `schoolID` = `4` (replace with actual school ID from login)
   - Add header: `teacherID` = `456` (replace with actual teacher ID from login)
4. **Body Tab:** Not needed (GET request)

### Expected Response (200 OK)

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
        },
        {
            "class_subject_id": 125,
            "subject_id": 47,
            "subject_name": "Chemistry",
            "subject_code": "CHEM",
            "class_id": 11,
            "subclass_id": 26,
            "class_name": "Form 4 - Form 4B",
            "total_students": 42
        }
    ]
}
```

### Response Fields Description

| Field | Type | Description |
|-------|------|-------------|
| `class_subject_id` | integer | Unique identifier for the class-subject assignment |
| `subject_id` | integer | Subject ID |
| `subject_name` | string | Name of the subject |
| `subject_code` | string | Subject code (e.g., "MATH", "PHY") |
| `class_id` | integer | Class ID |
| `subclass_id` | integer\|null | Subclass ID (null if assigned to all subclasses) |
| `class_name` | string | Full class name (e.g., "Form 3 - Form 3A" or "Form 3 - All Subclasses") |
| `total_students` | integer | Total number of active students enrolled in this subject |

### Error Response (401 Unauthorized)

```json
{
    "success": false,
    "message": "Unauthorized. Please login first."
}
```

### Error Response (500 Internal Server Error)

```json
{
    "success": false,
    "message": "Failed to retrieve subjects",
    "error": "Error message details"
}
```

---

## 2. Get Subject Students

### Request Details

**Method:** `GET`

**URL:**
```
http://192.168.100.104:8003/api/teacher/subjects/{classSubjectID}/students
```

**Example URL:**
```
http://192.168.100.104:8003/api/teacher/subjects/123/students
```

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**URL Parameters:**
- `classSubjectID` (integer, required) - The class subject ID from the subjects list

### Postman Setup Steps

1. **Method:** Select `GET`
2. **URL:** Enter `http://192.168.100.104:8003/api/teacher/subjects/123/students` (replace 123 with actual class_subject_id)
3. **Headers Tab:**
   - Add header: `Accept` = `application/json`
   - Add header: `user_id` = `123`
   - Add header: `user_type` = `Teacher`
   - Add header: `schoolID` = `4`
   - Add header: `teacherID` = `456`

### Expected Response (200 OK)

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
    }
}
```

---

## 3. Get Examinations for Subject

### Request Details

**Method:** `GET`

**URL:**
```
http://192.168.100.104:8003/api/teacher/subjects/{classSubjectID}/examinations
```

**Example URL:**
```
http://192.168.100.104:8003/api/teacher/subjects/123/examinations
```

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

### Expected Response (200 OK)

```json
{
    "success": true,
    "message": "Examinations retrieved successfully",
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
    }
}
```

---

## 4. Get Subject Results

### Request Details

**Method:** `GET`

**URL (All Results):**
```
http://192.168.100.104:8003/api/teacher/subjects/{classSubjectID}/results
```

**URL (Specific Exam):**
```
http://192.168.100.104:8003/api/teacher/subjects/{classSubjectID}/results/{examID}
```

**Example URLs:**
```
http://192.168.100.104:8003/api/teacher/subjects/123/results
http://192.168.100.104:8003/api/teacher/subjects/123/results/50
```

**Headers:**
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

### Expected Response (200 OK)

```json
{
    "success": true,
    "message": "Results retrieved successfully",
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
    }
}
```

---

## Quick Reference - All GET Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/teacher/subjects` | GET | Get all subjects assigned to teacher |
| `/api/teacher/subjects/{classSubjectID}/students` | GET | Get students for a specific subject |
| `/api/teacher/subjects/{classSubjectID}/examinations` | GET | Get examinations for a specific subject |
| `/api/teacher/subjects/{classSubjectID}/results` | GET | Get all results for a subject |
| `/api/teacher/subjects/{classSubjectID}/results/{examID}` | GET | Get results for a specific exam |

---

## Required Headers (All GET Endpoints)

| Header Name | Value | Required | Description |
|-------------|-------|----------|-------------|
| `Accept` | `application/json` | Yes | Response format |
| `user_id` | `<integer>` | Yes | User ID from login response |
| `user_type` | `Teacher` | Yes | Must be exactly "Teacher" |
| `schoolID` | `<integer>` | Yes | School ID from login response |
| `teacherID` | `<integer>` | Yes | Teacher ID from login response |

---

## cURL Examples

### Get All Subjects
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

### Get Subject Students
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/students" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

### Get Examinations
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/examinations" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

### Get All Results
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/results" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

### Get Results for Specific Exam
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/subjects/123/results/50" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

---

## Postman Collection JSON (Import Ready)

```json
{
  "info": {
    "name": "Teacher Subjects API - GET Requests",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get All Subjects",
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
      "name": "Get Subject Students",
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
      "name": "Get Examinations",
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
      "name": "Get All Results",
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
      "name": "Get Results for Specific Exam",
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
    }
  ]
}
```

**To Import:**
1. Copy the JSON above
2. In Postman, click "Import"
3. Select "Raw text"
4. Paste the JSON
5. Click "Import"

---

## Testing Flow

### Step 1: Login
1. Call Login API to get authentication data
2. Extract: `user_id`, `user_type`, `schoolID`, `teacherID`

### Step 2: Get All Subjects
1. Use GET `/api/teacher/subjects`
2. Note the `class_subject_id` values you want to use

### Step 3: Get Students (Optional)
1. Use GET `/api/teacher/subjects/{classSubjectID}/students`
2. Note the `student_id` values

### Step 4: Get Examinations (Optional)
1. Use GET `/api/teacher/subjects/{classSubjectID}/examinations`
2. Note the `exam_id` values

### Step 5: Get Results (Optional)
1. Use GET `/api/teacher/subjects/{classSubjectID}/results`
2. Or GET `/api/teacher/subjects/{classSubjectID}/results/{examID}`

---

## Quick Test Values

Replace these with your actual values from login:

```
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
classSubjectID: 123 (from Get All Subjects response)
examID: 50 (from Get Examinations response)
```

---

## Troubleshooting

### Issue: 401 Unauthorized
**Solution:** Check that all headers are set correctly:
- `user_id` is a valid integer
- `user_type` is exactly "Teacher" (case-sensitive)
- `schoolID` is a valid integer
- `teacherID` is a valid integer

### Issue: 404 Not Found
**Solution:** Check:
- `classSubjectID` exists and belongs to the teacher
- URL path is correct
- Subject is active

### Issue: Empty Results
**Solution:** Check:
- Teacher has subjects assigned
- Subjects are active
- Students are enrolled and active

---

**Last Updated:** January 2025
