# Teacher Dashboard API Documentation

## Base URL
```
http://192.168.100.104:8003/api
```

## Authentication
All endpoints require authentication via session. The teacher must be logged in through the web interface first, and the session cookie must be included in API requests.

---

## Endpoints

### 1. Get Teacher Dashboard Data

**Endpoint:** `GET /teacher/dashboard`

**Description:** Retrieves comprehensive dashboard data for the authenticated teacher, including statistics, notifications, and menu items.

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
        "dashboard_stats": {
            "subjects_count": 5,
            "classes_count": 8,
            "sessions_per_week": 20,
            "teaching_subjects": [
                "Mathematics",
                "Physics",
                "Chemistry"
            ],
            "pending_approvals_count": 2,
            "supervise_exams_count": 3,
            "lesson_plans_count": 45,
            "lesson_plans_sent_count": 40
        },
        "notifications": [
            {
                "type": "session_time",
                "icon": "fa-clock-o",
                "color": "warning",
                "title": "Session Time",
                "message": "Session yako imefika: Mathematics - Form 3A",
                "date": "2024-01-15 10:30:00",
                "link": "/teacher/my-sessions"
            }
        ],
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
                }
            ]
        }
    },
    "message": "Dashboard data retrieved successfully"
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Unauthorized. Please login first."
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Failed to retrieve dashboard data",
    "error": "Error message details"
}
```

---

## Response Fields Description

### Dashboard Statistics (`dashboard_stats`)

| Field | Type | Description |
|-------|------|-------------|
| `subjects_count` | integer | Number of active subjects the teacher is teaching |
| `classes_count` | integer | Number of distinct classes/subclasses the teacher teaches |
| `sessions_per_week` | integer | Total number of sessions per week for the teacher |
| `teaching_subjects` | array | List of subject names the teacher is teaching |
| `pending_approvals_count` | integer | Number of pending exam approvals |
| `supervise_exams_count` | integer | Number of upcoming exams the teacher is supervising |
| `lesson_plans_count` | integer | Total lesson plans created this year |
| `lesson_plans_sent_count` | integer | Number of lesson plans sent to admin this year |

### Notifications (`notifications`)

| Field | Type | Description |
|-------|------|-------------|
| `type` | string | Notification type (e.g., "session_time", "exam_rejected", "approval_pending") |
| `icon` | string | Font Awesome icon class name |
| `color` | string | Bootstrap color class (e.g., "warning", "danger", "info") |
| `title` | string | Notification title |
| `message` | string | Notification message |
| `date` | string | Notification date/time (YYYY-MM-DD HH:MM:SS) |
| `link` | string | Route or URL to navigate when notification is clicked |

### Menu Items (`menu_items`)

#### Main Menu (`main_menu`)

Standard menu items available to all teachers:

| Field | Type | Description |
|-------|------|-------------|
| `id` | string | Unique identifier for the menu item |
| `name` | string | Display name of the menu item |
| `icon` | string | Font Awesome icon class name |
| `route` | string | Laravel route name |
| `type` | string | Always "main" for main menu items |

**Available Main Menu Items:**
- `dashboard` - Dashboard
- `my_sessions` - My Sessions
- `my_tasks` - My Tasks
- `my_subjects` - My Subjects
- `scheme_of_work` - Scheme of Work
- `lesson_plans` - Lesson Plans
- `calendar` - Calendar
- `supervise_exams` - My Supervise Exams
- `exam_papers` - My Exam Papers
- `my_class` - My Class (only shown if teacher has assigned class)

#### Management Menu (`management_menu`)

Menu items based on teacher permissions. Only shown if teacher has permissions in that category:

**Available Management Categories:**
- `examination` - Examination Management
- `classes` - Classes Management
- `subject` - Subject Management
- `result` - Result Management
- `attendance` - Attendance Management
- `student` - Student Management
- `parent` - Parent Management
- `timetable` - Timetable Management
- `fees` - Fees Management
- `accommodation` - Accommodation Management
- `library` - Library Management
- `calendar` - Calendar Management
- `fingerprint` - Fingerprint Settings
- `task` - Task Management
- `sms` - SMS Information

---

## Usage Examples

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class TeacherDashboardAPI {
  final String baseUrl = 'http://192.168.100.104:8003/api';
  final String sessionCookie; // Get from login response
  
  Future<Map<String, dynamic>> getDashboard() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/teacher/dashboard'),
        headers: {
          'Accept': 'application/json',
          'Cookie': 'laravel_session=$sessionCookie',
        },
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else if (response.statusCode == 401) {
        throw Exception('Unauthorized. Please login first.');
      } else {
        throw Exception('Failed to load dashboard: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching dashboard: $e');
    }
  }
}
```

### JavaScript/React Native Example

```javascript
const getTeacherDashboard = async (sessionCookie) => {
  try {
    const response = await fetch('http://192.168.100.104:8003/api/teacher/dashboard', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Cookie': `laravel_session=${sessionCookie}`,
      },
    });
    
    if (response.ok) {
      const data = await response.json();
      return data;
    } else if (response.status === 401) {
      throw new Error('Unauthorized. Please login first.');
    } else {
      throw new Error(`Failed to load dashboard: ${response.status}`);
    }
  } catch (error) {
    throw new Error(`Error fetching dashboard: ${error.message}`);
  }
};
```

### cURL Example

```bash
curl -X GET "http://192.168.100.104:8003/api/teacher/dashboard" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_TOKEN"
```

---

## Notes

1. **Session Authentication**: This API uses Laravel session-based authentication. The teacher must be logged in through the web interface first.

2. **Menu Items**: The `my_class` menu item only appears if the teacher has an assigned class (is a class teacher).

3. **Management Menu**: Management menu items are dynamically generated based on the teacher's role permissions. If a teacher doesn't have permissions in a category, those menu items won't appear.

4. **Notifications**: Notifications are limited to the 10 most recent items.

5. **Data Freshness**: Dashboard statistics are calculated in real-time based on current database state.

6. **Error Handling**: Always check the `success` field in the response before accessing data. Handle errors appropriately based on status codes.

---

## Support

For issues or questions regarding this API, please contact the development team.

**Last Updated:** January 2024
