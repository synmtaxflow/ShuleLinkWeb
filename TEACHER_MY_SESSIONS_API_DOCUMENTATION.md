# Teacher My Sessions API Documentation

Base URL: `http://<SERVER_IP>/api`

All endpoints below require teacher authentication headers (stateless):

Required headers (or request body equivalents where noted):
- `user_id`
- `user_type` (must be `Teacher`)
- `schoolID`
- `teacherID`

If any of these are missing, the API returns `401 Unauthorized`.

---

## 1) Get Weekly Sessions

`GET /teacher/my-sessions`

Query params:
- `week` (optional, integer). `0` = current week, `1` = next week, `-1` = previous week.

Example:
```
GET /api/teacher/my-sessions?week=0
```

Success Response:
```json
{
  "success": true,
  "data": {
    "week_offset": 0,
    "week_start": "2024-06-10",
    "week_end": "2024-06-16",
    "holiday_dates": ["2024-06-12"],
    "days": [
      {
        "day": "Monday",
        "date": "2024-06-10",
        "is_holiday": false,
        "is_weekend": false,
        "sessions": [
          {
            "session_timetableID": 123,
            "day": "Monday",
            "date": "2024-06-10",
            "start_time": "08:00:00",
            "end_time": "09:00:00",
            "start_time_formatted": "08:00 AM",
            "end_time_formatted": "09:00 AM",
            "subject_name": "Mathematics",
            "class_name": "Form 1",
            "subclass_name": "A",
            "class_label": "Form 1 - A",
            "is_prepo": false,
            "is_session_time": false,
            "is_past": false,
            "can_interact": false,
            "task": {
              "session_taskID": 55,
              "status": "approved",
              "topic": "Algebra",
              "subtopic": "Linear Equations",
              "task_description": "Reviewed basics of linear equations."
            },
            "has_approved_task": true
          }
        ]
      }
    ]
  }
}
```

Error Responses:
```json
{ "success": false, "error": "Unauthorized. Missing required authentication parameters..." }
```
```json
{ "success": false, "error": "No timetable definition found. Please contact admin." }
```

---

## 2) Get Session Students (for Attendance)

`GET /teacher/my-sessions/students`

Query params:
- `session_timetableID` (required)
- `attendance_date` (required, `YYYY-MM-DD`)

Example:
```
GET /api/teacher/my-sessions/students?session_timetableID=123&attendance_date=2024-06-10
```

Success Response:
```json
{
  "success": true,
  "students": [
    {
      "studentID": 9,
      "name": "John Doe",
      "status": "Present",
      "remark": null
    }
  ],
  "attendance_exists": false,
  "can_collect": true
}
```

---

## 3) Get Session Attendance for Update

`GET /teacher/my-sessions/attendance`

Query params:
- `session_timetableID` (required)
- `attendance_date` (required, `YYYY-MM-DD`)

Example:
```
GET /api/teacher/my-sessions/attendance?session_timetableID=123&attendance_date=2024-06-10
```

Success Response:
```json
{
  "success": true,
  "data": [
    {
      "attendanceID": 77,
      "studentID": 9,
      "student_name": "John Doe",
      "status": "Present",
      "remark": ""
    }
  ]
}
```

---

## 4) Collect / Update Session Attendance

`POST /teacher/my-sessions/attendance`

Body (JSON or form-data):
```json
{
  "session_timetableID": 123,
  "attendance_date": "2024-06-10",
  "is_update": false,
  "attendance": [
    { "studentID": 9, "status": "Present", "remark": "" },
    { "studentID": 10, "status": "Absent", "remark": "Sick" }
  ]
}
```

Success Response:
```json
{
  "success": true,
  "message": "Attendance collected successfully"
}
```

Error Examples:
```json
{
  "success": false,
  "error": "Attendance for this session on this date has already been collected. Please use \"Update Attendance\" to modify it."
}
```
```json
{
  "success": false,
  "errors": {
    "attendance.0.status": ["The attendance.0.status field is required."]
  }
}
```

---

## 5) Assign Session Task

`POST /teacher/my-sessions/tasks`

Body (JSON or form-data):
```json
{
  "session_timetableID": 123,
  "task_date": "2024-06-10",
  "topic": "Algebra",
  "subtopic": "Linear Equations",
  "task_description": "Reviewed basics of linear equations."
}
```

Success Response:
```json
{
  "success": true,
  "message": "Task assigned successfully",
  "task": {
    "session_taskID": 55,
    "schoolID": 1,
    "session_timetableID": 123,
    "teacherID": 5,
    "task_date": "2024-06-10",
    "topic": "Algebra",
    "subtopic": "Linear Equations",
    "task_description": "Reviewed basics of linear equations.",
    "status": "pending"
  }
}
```

Error Example:
```json
{
  "success": false,
  "error": "Task already assigned for this session on this date"
}
```

---

## Authentication Notes

- For Flutter, include the headers on every request:
  - `user_id`, `user_type`, `schoolID`, `teacherID`
- `user_type` must be exactly `Teacher`.

