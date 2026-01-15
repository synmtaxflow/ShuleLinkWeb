# Teacher Subjects API - Postman Testing Guide

## Base URL
```
http://192.168.100.104:8003/api
```

---

## 1. Save Subject Results

### Request Details

**Method:** `POST`

**URL:**
```
http://192.168.100.104:8003/api/teacher/subjects/results/save
```

**Headers:**
```
Accept: application/json
Content-Type: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

**Body (raw JSON):**
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

### Postman Setup Steps

1. **Method:** Select `POST`
2. **URL:** Enter `http://192.168.100.104:8003/api/teacher/subjects/results/save`
3. **Headers Tab:**
   - Add header: `Accept` = `application/json`
   - Add header: `Content-Type` = `application/json`
   - Add header: `user_id` = `123` (replace with actual user ID)
   - Add header: `user_type` = `Teacher`
   - Add header: `schoolID` = `4` (replace with actual school ID)
   - Add header: `teacherID` = `456` (replace with actual teacher ID)
4. **Body Tab:**
   - Select `raw`
   - Select `JSON` from dropdown
   - Paste the JSON body above (replace values with actual data)

### Example Request (Copy-Paste Ready)

**cURL Format:**
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

---

## 2. Upload Excel Results

### Request Details

**Method:** `POST`

**URL:**
```
http://192.168.100.104:8003/api/teacher/subjects/results/upload-excel
```

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

### Postman Setup Steps

1. **Method:** Select `POST`
2. **URL:** Enter `http://192.168.100.104:8003/api/teacher/subjects/results/upload-excel`
3. **Headers Tab:**
   - Add header: `Accept` = `application/json`
   - Add header: `user_id` = `123` (replace with actual user ID)
   - Add header: `user_type` = `Teacher`
   - Add header: `schoolID` = `4` (replace with actual school ID)
   - Add header: `teacherID` = `456` (replace with actual teacher ID)
   - **Note:** Don't set `Content-Type` - Postman will set it automatically for form-data
4. **Body Tab:**
   - Select `form-data`
   - Add key: `class_subject_id` (Type: Text) = `123`
   - Add key: `exam_id` (Type: Text) = `50`
   - Add key: `excel_file` (Type: File) = Select your Excel file (.xlsx or .xls)

### Excel File Format

Your Excel file should have these columns:

| Column A | Column B | Column C | Column D | Column E | Column F |
|----------|----------|-----------|-----------|-----------|-----------|
| Student ID | Admission Number | Student Name | Marks | Grade | Remark |
| 1001 | STU001 | John Doe | 85.5 | A | Excellent |
| 1002 | STU002 | Jane Smith | 72.0 | B | Very Good |

**Required Columns:**
- Column A: Student ID (required)
- Column D: Marks (required, 0-100)

**Optional Columns:**
- Column B: Admission Number (for reference)
- Column C: Student Name (for reference)
- Column E: Grade (auto-calculated if not provided)
- Column F: Remark (optional)

### Example Request (cURL Format)

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

## Quick Reference - Parameter Formats

### Save Results - JSON Body Format

```json
{
    "class_subject_id": <integer>,
    "exam_id": <integer>,
    "results": [
        {
            "student_id": <integer>,
            "marks": <float 0-100>,
            "grade": "<string>",
            "remark": "<string>"
        }
    ]
}
```

**Field Descriptions:**

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `class_subject_id` | integer | Yes | Class subject ID | `123` |
| `exam_id` | integer | Yes | Examination ID | `50` |
| `results` | array | Yes | Array of result objects | `[...]` |
| `results[].student_id` | integer | Yes | Student ID | `1001` |
| `results[].marks` | float | No | Marks (0-100) | `85.5` |
| `results[].grade` | string | No | Grade (A, B, C, D, F) | `"A"` |
| `results[].remark` | string | No | Remark text | `"Excellent"` |

### Upload Excel - Form Data Format

```
class_subject_id: <integer>
exam_id: <integer>
excel_file: <file>
```

**Field Descriptions:**

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `class_subject_id` | integer | Yes | Class subject ID | `123` |
| `exam_id` | integer | Yes | Examination ID | `50` |
| `excel_file` | file | Yes | Excel file (.xlsx or .xls, max 10MB) | `results.xlsx` |

---

## Required Headers (Both Endpoints)

| Header Name | Value | Required | Description |
|-------------|-------|----------|-------------|
| `Accept` | `application/json` | Yes | Response format |
| `Content-Type` | `application/json` | Yes (Save only) | Request body format |
| `user_id` | `<integer>` | Yes | User ID from login |
| `user_type` | `Teacher` | Yes | Must be exactly "Teacher" |
| `schoolID` | `<integer>` | Yes | School ID from login |
| `teacherID` | `<integer>` | Yes | Teacher ID from login |

**Note:** For Excel upload, don't set `Content-Type` header - Postman will set it automatically.

---

## Expected Responses

### Success Response (200 OK)

**Save Results:**
```json
{
    "success": true,
    "message": "Successfully saved 2 result(s)!",
    "data": {
        "saved_count": 2
    }
}
```

**Upload Excel:**
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

### Error Responses

**401 Unauthorized:**
```json
{
    "success": false,
    "message": "Unauthorized. Please login first."
}
```

**422 Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "class_subject_id": ["The class subject id field is required."],
        "results.0.student_id": ["The student id field is required."]
    }
}
```

**403 Forbidden:**
```json
{
    "success": false,
    "message": "You are not allowed to enter results for this examination. Result entry has been disabled."
}
```

---

## Testing Steps in Postman

### Step 1: Get Authentication Data
1. First, login using the Login API to get:
   - `user_id` (from `data.user.id`)
   - `user_type` (from `data.user.user_type`)
   - `schoolID` (from `data.schoolID`)
   - `teacherID` (from `data.teacherID`)

### Step 2: Get Class Subject ID
1. Call `GET /api/teacher/subjects` to get list of subjects
2. Note the `class_subject_id` you want to use

### Step 3: Get Exam ID
1. Call `GET /api/teacher/subjects/{classSubjectID}/examinations`
2. Note the `exam_id` you want to use

### Step 4: Get Student IDs
1. Call `GET /api/teacher/subjects/{classSubjectID}/students`
2. Note the `student_id` values you want to use

### Step 5: Test Save Results
1. Use the values from steps 1-4
2. Create POST request as described above
3. Send request and check response

### Step 6: Test Upload Excel
1. Create an Excel file with the format described above
2. Use the values from steps 1-3
3. Create POST request as described above
4. Select the Excel file
5. Send request and check response

---

## Postman Collection JSON (Import Ready)

```json
{
  "info": {
    "name": "Teacher Subjects API - POST Requests",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Save Subject Results",
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
        "body": {
          "mode": "raw",
          "raw": "{\n    \"class_subject_id\": 123,\n    \"exam_id\": 50,\n    \"results\": [\n        {\n            \"student_id\": 1001,\n            \"marks\": 85.5,\n            \"grade\": \"A\",\n            \"remark\": \"Excellent\"\n        },\n        {\n            \"student_id\": 1002,\n            \"marks\": 72.0,\n            \"grade\": \"B\",\n            \"remark\": \"Very Good\"\n        }\n    ]\n}",
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
      "name": "Upload Excel Results",
      "request": {
        "method": "POST",
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

**To Import:**
1. Copy the JSON above
2. In Postman, click "Import"
3. Select "Raw text"
4. Paste the JSON
5. Click "Import"

---

## Quick Test Values

Replace these with your actual values:

```
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
class_subject_id: 123
exam_id: 50
student_id: 1001, 1002, 1003
```

---

## Troubleshooting

### Issue: 401 Unauthorized
**Solution:** Check that all headers are set correctly:
- `user_id` is a valid integer
- `user_type` is exactly "Teacher" (case-sensitive)
- `schoolID` is a valid integer
- `teacherID` is a valid integer

### Issue: 422 Validation Error
**Solution:** Check:
- All required fields are present
- `class_subject_id` exists in database
- `exam_id` exists in database
- `student_id` values exist in database
- `marks` are between 0 and 100

### Issue: 403 Forbidden
**Solution:** Check:
- Teacher owns the class subject
- Examination has `enter_result: true`
- Term is not closed (for editing existing results)

### Issue: Excel Upload Fails
**Solution:** Check:
- File is .xlsx or .xls format
- File size is less than 10MB
- Excel file has correct column format
- Student IDs in Excel exist in database

---

**Last Updated:** January 2025
