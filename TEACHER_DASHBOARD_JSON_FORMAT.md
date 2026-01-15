# Teacher Dashboard API - JSON Response Format

## Endpoint
```
GET /api/teacher/dashboard
```

## Authentication Requirements

**IMPORTANT:** This endpoint requires authentication via headers. You must send the following headers with every request:

### Required Headers

| Header Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `user_id` | integer | **Yes** | User ID from login response (`data.user.id`) | `123` |
| `user_type` | string | **Yes** | User type from login response (`data.user.user_type`) | `Teacher` |
| `schoolID` | integer | **Yes** | School ID from login response (`data.schoolID`) | `4` |
| `teacherID` | integer | **Yes** | Teacher ID from login response (`data.teacherID`) | `456` |
| `Accept` | string | **Yes** | Content type accepted | `application/json` |

### Request Headers Example
```
Accept: application/json
user_id: 123
user_type: Teacher
schoolID: 4
teacherID: 456
```

### Alternative: Request Parameters (Query or Body)

You can also send these as query parameters or in request body:

**Query Parameters:**
```
GET /api/teacher/dashboard?user_id=123&user_type=Teacher&schoolID=4&teacherID=456
```

**Request Body (for POST requests):**
```json
{
    "user_id": 123,
    "user_type": "Teacher",
    "schoolID": 4,
    "teacherID": 456
}
```

**Note:** Headers are preferred and recommended.

## Success Response (200 OK)

```json
{
    "success": true,
    "message": "Dashboard data retrieved successfully",
    "data": {
        "dashboard_stats": {
            "subjects_count": 5,
            "classes_count": 8,
            "sessions_per_week": 20,
            "teaching_subjects": [
                "Mathematics",
                "Physics",
                "Chemistry",
                "Biology",
                "English"
            ],
            "pending_approvals_count": 2,
            "supervise_exams_count": 3,
            "lesson_plans_count": 45,
            "lesson_plans_sent_count": 40
        },
        "notifications": [],
        "menu_items": {
            "main_menu": [
                {
                    "id": "dashboard",
                    "name": "Dashboard",
                    "icon": "fa-building",
                    "route": "teachersDashboard",
                    "type": "main"
                },
                {
                    "id": "my_sessions",
                    "name": "My Sessions",
                    "icon": "fa-clock-o",
                    "route": "teacher.mySessions",
                    "type": "main"
                },
                {
                    "id": "my_tasks",
                    "name": "My Tasks",
                    "icon": "fa-tasks",
                    "route": "teacher.myTasks",
                    "type": "main"
                },
                {
                    "id": "my_subjects",
                    "name": "My Subjects",
                    "icon": "fa-book",
                    "route": "teacherSubjects",
                    "type": "main"
                },
                {
                    "id": "scheme_of_work",
                    "name": "Scheme of Work",
                    "icon": "fa-file-text-o",
                    "route": "teacher.schemeOfWork",
                    "type": "main"
                },
                {
                    "id": "lesson_plans",
                    "name": "Lesson Plans",
                    "icon": "fa-book",
                    "route": "teacher.lessonPlans",
                    "type": "main"
                },
                {
                    "id": "calendar",
                    "name": "Calendar",
                    "icon": "fa-calendar",
                    "route": "teacher.calendar",
                    "type": "main"
                },
                {
                    "id": "supervise_exams",
                    "name": "My Supervise Exams",
                    "icon": "fa-graduation-cap",
                    "route": "supervise_exams",
                    "type": "main"
                },
                {
                    "id": "exam_papers",
                    "name": "My Exam Papers",
                    "icon": "fa-file-text",
                    "route": "exam_paper",
                    "type": "main"
                },
                {
                    "id": "my_class",
                    "name": "My Class",
                    "icon": "fa-users",
                    "route": "AdmitedClasses",
                    "type": "main"
                }
            ],
            "management_menu": [
                {
                    "id": "examination_management",
                    "name": "Examination Management",
                    "icon": "fa-pencil-square-o",
                    "route": "manageExamination",
                    "type": "management"
                },
                {
                    "id": "subject_management",
                    "name": "Subject Management",
                    "icon": "fa-bookmark",
                    "route": "manageSubjects",
                    "type": "management"
                },
                {
                    "id": "result_management",
                    "name": "Result Management",
                    "icon": "fa-trophy",
                    "route": "manageResults",
                    "type": "management"
                },
                {
                    "id": "attendance_management",
                    "name": "Attendance Management",
                    "icon": "fa-check-square-o",
                    "route": "manageAttendance",
                    "type": "management"
                },
                {
                    "id": "student_management",
                    "name": "Student Management",
                    "icon": "fa-user",
                    "route": "manage_student",
                    "type": "management"
                },
                {
                    "id": "parent_management",
                    "name": "Parent Management",
                    "icon": "fa-user-plus",
                    "route": "manage_parents",
                    "type": "management"
                },
                {
                    "id": "timetable_management",
                    "name": "Timetable Management",
                    "icon": "fa-clock-o",
                    "route": "timeTable",
                    "type": "management"
                },
                {
                    "id": "fees_management",
                    "name": "Fees Management",
                    "icon": "fa-money",
                    "route": "manage_fees",
                    "type": "management"
                },
                {
                    "id": "accommodation_management",
                    "name": "Accommodation Management",
                    "icon": "fa-bed",
                    "route": "manage_accomodation",
                    "type": "management"
                },
                {
                    "id": "library_management",
                    "name": "Library Management",
                    "icon": "fa-book",
                    "route": "manage_library",
                    "type": "management"
                },
                {
                    "id": "calendar_management",
                    "name": "Calendar Management",
                    "icon": "fa-calendar",
                    "route": "admin.calendar",
                    "type": "management"
                },
                {
                    "id": "fingerprint_settings",
                    "name": "Fingerprint Settings",
                    "icon": "fa-fingerprint",
                    "route": "fingerprint_device_settings",
                    "type": "management"
                },
                {
                    "id": "task_management",
                    "name": "Task Management",
                    "icon": "fa-tasks",
                    "route": "taskManagement",
                    "type": "management"
                },
                {
                    "id": "sms_notification",
                    "name": "SMS Information",
                    "icon": "fa-envelope",
                    "route": "sms_notification",
                    "type": "management"
                }
            ]
        }
    }
}
```

## Error Responses

### Error Response (401 Unauthorized) - Missing Parameters

```json
{
    "success": false,
    "message": "Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body."
}
```

**Cause:** One or more required authentication parameters are missing.

**Solution:** Ensure all required headers are included:
- `user_id`
- `user_type`
- `schoolID`
- `teacherID`

### Error Response (403 Forbidden) - Invalid User Type

```json
{
    "success": false,
    "message": "Unauthorized. Invalid user type. This endpoint is for Teachers only."
}
```

**Cause:** The `user_type` is not "Teacher".

**Solution:** Ensure `user_type` header is set to exactly `"Teacher"` (case-sensitive).

### Error Response (404 Not Found) - Teacher Not Found

```json
{
    "success": false,
    "message": "Unauthorized. Teacher not found or does not belong to the specified school."
}
```

**Cause:** The teacherID does not exist or does not belong to the specified schoolID.

**Solution:** Verify that:
- The `teacherID` is correct from login response
- The `schoolID` matches the teacher's school
- The teacher account is active

## Error Response (500 Internal Server Error)

```json
{
    "success": false,
    "message": "Failed to retrieve dashboard data",
    "error": "Error message details"
}
```

---

## Response Fields Description

### Root Level
- `success` (boolean): Indicates if the request was successful
- `message` (string): Response message
- `data` (object): Contains all dashboard data

### Dashboard Statistics (`data.dashboard_stats`)

| Field | Type | Description |
|-------|------|-------------|
| `subjects_count` | integer | Number of active subjects the teacher is teaching |
| `classes_count` | integer | Number of distinct classes/subclasses the teacher teaches |
| `sessions_per_week` | integer | Total number of sessions per week for the teacher |
| `teaching_subjects` | array[string] | List of subject names the teacher is teaching |
| `pending_approvals_count` | integer | Number of pending exam approvals |
| `supervise_exams_count` | integer | Number of upcoming exams the teacher is supervising |
| `lesson_plans_count` | integer | Total lesson plans created this year |
| `lesson_plans_sent_count` | integer | Number of lesson plans sent to admin this year |

### Notifications (`data.notifications`)

Array of notification objects. Currently returns empty array `[]`.

Each notification object (when available) would have:
- `type` (string): Notification type
- `icon` (string): Font Awesome icon class
- `color` (string): Bootstrap color class
- `title` (string): Notification title
- `message` (string): Notification message
- `date` (string): Notification date/time
- `link` (string): Route or URL

### Menu Items (`data.menu_items`)

#### Main Menu (`data.menu_items.main_menu`)

Array of main menu items. Each item contains:
- `id` (string): Unique identifier
- `name` (string): Display name
- `icon` (string): Font Awesome icon class
- `route` (string): Laravel route name
- `type` (string): Always "main"

**Note:** The `my_class` menu item only appears if the teacher has an assigned class.

#### Management Menu (`data.menu_items.management_menu`)

Array of management menu items based on teacher permissions. Each item contains:
- `id` (string): Unique identifier
- `name` (string): Display name
- `icon` (string): Font Awesome icon class
- `route` (string): Laravel route name
- `type` (string): Always "management"

**Note:** Management menu items are dynamically generated based on the teacher's role permissions. Only categories where the teacher has permissions will appear.

---

## Example Usage

### cURL

**Using Headers (Recommended):**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/dashboard" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```

**Using Query Parameters:**
```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/dashboard?user_id=123&user_type=Teacher&schoolID=4&teacherID=456" \
  -H "Accept: application/json"
```

**Debugging - Check Response:**
```bash
curl -v -X GET "http://192.168.100.104:8003/api/teacher/dashboard" \
  -H "Accept: application/json" \
  -H "user_id: 123" \
  -H "user_type: Teacher" \
  -H "schoolID: 4" \
  -H "teacherID: 456"
```
The `-v` flag shows request and response headers for debugging.

### Flutter/Dart
```dart
final response = await http.get(
  Uri.parse('http://192.168.100.104:8003/api/teacher/dashboard'),
  headers: {
    'Accept': 'application/json',
    'user_id': '123',
    'user_type': 'Teacher',
    'schoolID': '4',
    'teacherID': '456',
  },
);
```

### JavaScript/React Native
```javascript
const response = await fetch('http://192.168.100.104:8003/api/teacher/dashboard', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'user_id': '123',
    'user_type': 'Teacher',
    'schoolID': '4',
    'teacherID': '456',
  },
});
```

---

## Validation Rules

### Required Parameters Validation

The API validates the following:

1. **All parameters must be present:**
   - `user_id` - Must be provided
   - `user_type` - Must be provided
   - `schoolID` - Must be provided
   - `teacherID` - Must be provided

2. **User Type Validation:**
   - `user_type` must be exactly `"Teacher"` (case-sensitive)
   - Other user types (Admin, parent) will be rejected

3. **Teacher Verification:**
   - `teacherID` must exist in the database
   - `teacherID` must belong to the specified `schoolID`
   - Teacher account must be active

### Common Issues and Solutions

#### Issue 1: "Missing required authentication parameters"
**Problem:** One or more headers are missing or not sent correctly.

**Solution:**
- Check that all 4 headers are included: `user_id`, `user_type`, `schoolID`, `teacherID`
- Verify header names are correct (case-sensitive in some HTTP clients)
- Check if your HTTP client supports custom headers
- Try using query parameters as alternative

#### Issue 2: "Invalid user type"
**Problem:** `user_type` is not exactly "Teacher".

**Solution:**
- Ensure `user_type` is exactly `"Teacher"` (capital T, lowercase rest)
- Check for extra spaces: `"Teacher "` or `" Teacher"` will fail
- Verify you're using the value from login response: `data.user.user_type`

#### Issue 3: "Teacher not found"
**Problem:** Teacher ID doesn't exist or doesn't match school.

**Solution:**
- Verify `teacherID` from login response: `data.teacherID`
- Verify `schoolID` from login response: `data.schoolID`
- Ensure teacher account is active in the system
- Check if teacher was moved to a different school

### Testing Your Request

1. **Verify Login Response:**
   First, make sure login returns valid data:
   ```json
   {
       "success": true,
       "data": {
           "user": {
               "id": 123,
               "user_type": "Teacher"
           },
           "schoolID": 4,
           "teacherID": 456
       }
   }
   ```

2. **Extract Values:**
   - `user_id` = `data.user.id` = `123`
   - `user_type` = `data.user.user_type` = `"Teacher"`
   - `schoolID` = `data.schoolID` = `4`
   - `teacherID` = `data.teacherID` = `456`

3. **Send Headers:**
   ```
   user_id: 123
   user_type: Teacher
   schoolID: 4
   teacherID: 456
   ```

4. **Check Response:**
   - Success: `{"success": true, ...}`
   - Error: Check the error message for specific issue

### Example: Complete Flow

**Step 1: Login**
```bash
POST /api/login
{
    "username": "EMP001",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 123,
            "user_type": "Teacher"
        },
        "schoolID": 4,
        "teacherID": 456
    }
}
```

**Step 2: Store Data**
```javascript
const authData = response.data;
// Store: authData.user.id, authData.user.user_type, authData.schoolID, authData.teacherID
```

**Step 3: Call Dashboard API**
```bash
GET /api/teacher/dashboard
Headers:
  user_id: 123
  user_type: Teacher
  schoolID: 4
  teacherID: 456
```

---

**Last Updated:** January 2025
