# Teacher Dashboard API - Changes Documentation

## Overview
This document outlines all changes made to the Teacher Dashboard API to support stateless authentication and fix database table issues.

**Date:** January 2025

---

## Summary of Changes

### 1. Authentication System Change
**Changed from:** Session-based authentication  
**Changed to:** Stateless authentication using headers

### 2. Database Table Fixes
- Fixed `exam_approvals` table reference → Changed to `result_approvals`
- Fixed `class_teachers` table reference → Changed to `subclasses` and `classes` tables

### 3. Validation Improvements
- Added comprehensive parameter validation
- Added user type verification
- Added teacher existence verification

---

## Detailed Changes

### Change 1: Authentication System

#### Before (Session-Based)
```php
// Old code
$teacherID = Session::get('teacherID');
$schoolID = Session::get('schoolID');

if (!$teacherID || !$schoolID) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized. Please login first.'
    ], 401);
}
```

#### After (Stateless - Headers)
```php
// New code
$teacherID = $request->header('teacherID') ?? $request->input('teacherID');
$schoolID = $request->header('schoolID') ?? $request->input('schoolID');
$userID = $request->header('user_id') ?? $request->input('user_id');
$userType = $request->header('user_type') ?? $request->input('user_type');

// Validate required parameters
if (!$teacherID || !$schoolID || !$userID || !$userType) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
    ], 401);
}

// Validate user type
if ($userType !== 'Teacher') {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
    ], 403);
}

// Verify teacher exists and belongs to the school
$teacher = Teacher::where('id', $teacherID)
    ->where('schoolID', $schoolID)
    ->first();

if (!$teacher) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
    ], 404);
}
```

#### Impact
- **Breaking Change:** Yes - Clients must now send authentication headers instead of session cookies
- **Migration Required:** Yes - All API clients need to be updated
- **Backward Compatible:** No

---

### Change 2: Database Table Fixes

#### Fix 1: Pending Approvals Query

**Before:**
```php
// Old code - Used non-existent table
$pendingApprovals = DB::table('exam_approvals')
    ->where('teacherID', $teacherID)
    ->where('status', 'pending')
    ->count();
```

**After:**
```php
// New code - Uses correct tables with proper relationships
$pendingApprovalsCount = 0;

// Get teacher's roles
$teacherRoles = DB::table('role_user')
    ->where('teacher_id', $teacherID)
    ->pluck('role_id')
    ->toArray();

// Get teacher's subclasses (for class_teacher role)
$teacherSubclasses = DB::table('subclasses')
    ->where('teacherID', $teacherID)
    ->pluck('subclassID')
    ->toArray();

// Get teacher's main classes as coordinator
$teacherMainClasses = DB::table('classes')
    ->where('teacherID', $teacherID)
    ->where('schoolID', $schoolID)
    ->pluck('classID')
    ->toArray();

// Count regular role approvals
if (!empty($teacherRoles)) {
    $regularApprovals = DB::table('result_approvals')
        ->whereIn('role_id', $teacherRoles)
        ->where('status', 'pending')
        ->count();
    $pendingApprovalsCount += $regularApprovals;
}

// Count class teacher approvals
if (!empty($teacherSubclasses)) {
    $classTeacherApprovals = DB::table('result_approvals')
        ->join('class_teacher_approvals', 'result_approvals.result_approvalID', '=', 'class_teacher_approvals.result_approvalID')
        ->where('result_approvals.special_role_type', 'class_teacher')
        ->whereIn('class_teacher_approvals.subclassID', $teacherSubclasses)
        ->where('class_teacher_approvals.status', 'pending')
        ->distinct('result_approvals.result_approvalID')
        ->count('result_approvals.result_approvalID');
    $pendingApprovalsCount += $classTeacherApprovals;
}

// Count coordinator approvals
if (!empty($teacherMainClasses)) {
    $coordinatorApprovals = DB::table('result_approvals')
        ->join('coordinator_approvals', 'result_approvals.result_approvalID', '=', 'coordinator_approvals.result_approvalID')
        ->where('result_approvals.special_role_type', 'coordinator')
        ->whereIn('coordinator_approvals.mainclassID', $teacherMainClasses)
        ->where('coordinator_approvals.status', 'pending')
        ->distinct('result_approvals.result_approvalID')
        ->count('result_approvals.result_approvalID');
    $pendingApprovalsCount += $coordinatorApprovals;
}

$pendingApprovals = $pendingApprovalsCount;
```

**Impact:**
- Fixes SQL error: `Table 'exam_approvals' doesn't exist`
- Now correctly counts all types of pending approvals:
  - Regular role-based approvals
  - Class teacher approvals
  - Coordinator approvals

#### Fix 2: Class Teacher Check

**Before:**
```php
// Old code - Used non-existent table
$hasAssignedClass = DB::table('class_teachers')
    ->where('teacherID', $teacherID)
    ->exists();
```

**After:**
```php
// New code - Uses correct tables
$teacherSubclasses = DB::table('subclasses')
    ->where('teacherID', $teacherID)
    ->exists();

$hasAssignedClass = $teacherSubclasses || 
    DB::table('classes')
        ->where('teacherID', $teacherID)
        ->where('schoolID', $schoolID)
        ->exists();
```

**Impact:**
- Fixes SQL error: `Table 'class_teachers' doesn't exist`
- Now correctly checks if teacher has assigned class:
  - As subclass teacher (from `subclasses` table)
  - As main class coordinator (from `classes` table)

---

## New Request Requirements

### Required Headers

All requests to `/api/teacher/dashboard` must include:

| Header | Type | Required | Description | Example |
|--------|------|----------|-------------|---------|
| `user_id` | integer | **Yes** | User ID from login response | `123` |
| `user_type` | string | **Yes** | Must be exactly "Teacher" | `Teacher` |
| `schoolID` | integer | **Yes** | School ID from login response | `4` |
| `teacherID` | integer | **Yes** | Teacher ID from login response | `456` |
| `Accept` | string | **Yes** | Content type | `application/json` |

### Alternative: Query Parameters or Request Body

You can also send these as:
- **Query Parameters:** `?user_id=123&user_type=Teacher&schoolID=4&teacherID=456`
- **Request Body:** JSON body with the same fields

---

## Error Responses

### 401 Unauthorized - Missing Parameters
```json
{
    "success": false,
    "message": "Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body."
}
```

### 403 Forbidden - Invalid User Type
```json
{
    "success": false,
    "message": "Unauthorized. Invalid user type. This endpoint is for Teachers only."
}
```

### 404 Not Found - Teacher Not Found
```json
{
    "success": false,
    "message": "Unauthorized. Teacher not found or does not belong to the specified school."
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Failed to retrieve dashboard data",
    "error": "Error message details"
}
```

---

## Migration Guide for API Clients

### Step 1: Update Login Flow

**Before:**
```javascript
// Old - Store session cookie
const response = await login(username, password);
const sessionCookie = response.session.cookie_value;
// Store cookie for subsequent requests
```

**After:**
```javascript
// New - Store authentication data
const response = await login(username, password);
const authData = {
    user_id: response.data.user.id,
    user_type: response.data.user.user_type,
    schoolID: response.data.schoolID,
    teacherID: response.data.teacherID
};
// Store authData in secure storage
```

### Step 2: Update Dashboard API Call

**Before:**
```javascript
// Old - Use session cookie
const response = await fetch('/api/teacher/dashboard', {
    headers: {
        'Cookie': `laravel_session=${sessionCookie}`
    }
});
```

**After:**
```javascript
// New - Use authentication headers
const response = await fetch('/api/teacher/dashboard', {
    headers: {
        'Accept': 'application/json',
        'user_id': authData.user_id.toString(),
        'user_type': authData.user_type,
        'schoolID': authData.schoolID.toString(),
        'teacherID': authData.teacherID.toString()
    }
});
```

### Step 3: Handle New Error Responses

Update error handling to check for new error messages:
- Missing parameters (401)
- Invalid user type (403)
- Teacher not found (404)

---

## Testing Checklist

- [ ] Login API returns correct authentication data
- [ ] Dashboard API accepts headers correctly
- [ ] Dashboard API validates all required parameters
- [ ] Dashboard API rejects invalid user types
- [ ] Dashboard API verifies teacher exists
- [ ] Pending approvals count is calculated correctly
- [ ] Class teacher check works correctly
- [ ] Menu items are returned correctly
- [ ] All error responses are handled properly

---

## Code Changes Summary

### Files Modified

1. **`app/Http/Controllers/TeachersController.php`**
   - Method: `getTeacherDashboardAPI()`
   - Changes:
     - Replaced session-based auth with header-based auth
     - Added parameter validation
     - Added user type verification
     - Added teacher existence check
     - Fixed `exam_approvals` → `result_approvals`
     - Fixed `class_teachers` → `subclasses`/`classes`
     - Improved pending approvals counting logic

### Database Tables Used

**Correct Tables:**
- ✅ `result_approvals` - For exam result approvals
- ✅ `class_teacher_approvals` - For class teacher specific approvals
- ✅ `coordinator_approvals` - For coordinator specific approvals
- ✅ `subclasses` - For checking subclass assignments
- ✅ `classes` - For checking main class coordinator assignments
- ✅ `role_user` - For checking teacher roles
- ✅ `exam_hall_supervisors` - For supervise exam count
- ✅ `lesson_plans` - For lesson plans count

**Removed References:**
- ❌ `exam_approvals` - Does not exist
- ❌ `class_teachers` - Does not exist

---

## Benefits of Changes

1. **Stateless Authentication**
   - No server-side session management
   - Better scalability
   - Works better with mobile apps
   - Easier to implement token-based auth in future

2. **Fixed Database Errors**
   - No more SQL errors for non-existent tables
   - Correct data retrieval
   - Accurate statistics

3. **Better Security**
   - Explicit parameter validation
   - User type verification
   - Teacher existence verification

4. **Better Error Messages**
   - Clear error messages for debugging
   - Specific error codes for different issues

---

## Backward Compatibility

**⚠️ Breaking Changes:**
- Old session-based authentication no longer works
- All API clients must be updated
- No backward compatibility maintained

**Migration Required:**
- All existing API clients must update to use headers
- Update authentication flow
- Update error handling

---

## Support

For issues or questions regarding these changes:
1. Check the updated API documentation: `TEACHER_DASHBOARD_JSON_FORMAT.md`
2. Review authentication documentation: `AUTHENTICATION_API_DOCUMENTATION.md`
3. Contact the development team

---

**Last Updated:** January 2025  
**Version:** 2.0.0
