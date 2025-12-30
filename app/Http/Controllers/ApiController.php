<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Teacher;
use App\Models\ParentModel;
use App\Models\User;
use App\Models\Student;
use App\Models\Result;
use App\Models\Attendance;
use App\Models\BookBorrow;
use App\Models\Examination;
use App\Models\ExamTimetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;

class ApiController extends Controller
{
    /**
     * Base URL for API endpoints
     * Server IP: 192.168.100.104
     * Usage: http://192.168.100.104/api/{endpoint}
     */
    
    /**
     * Login API
     * 
     * This endpoint authenticates users (Admin, Teacher, or Parent) and returns user data with token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Request Body:
     * {
     *   "username": "string (required)",
     *   "password": "string (required)"
     * }
     * 
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Login successful",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "username",
     *       "email": "user@example.com",
     *       "user_type": "Admin|Teacher|parent"
     *     },
     *     "schoolID": 1,
     *     "additional_data": {...}
     *   }
     * }
     * 
     * Error Response (401/422):
     * {
     *   "success": false,
     *   "message": "Error message",
     *   "errors": {...}
     * }
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:1',
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $username = $request->username;
        $password = $request->password;

        // Rate limiting - prevent brute force attacks
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds."
            ], 429);
        }

        // User Authentication
        $userLogin = User::where('name', $username)->first();

        // Check if user exists and password is correct
        if (!$userLogin || !Hash::check($password, $userLogin->password)) {
            RateLimiter::hit($key, 60); // 60 seconds lockout
            return response()->json([
                'success' => false,
                'message' => 'Incorrect username or password!'
            ], 401);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // Handle different user types
        $responseData = [
            'user' => [
                'id' => $userLogin->id,
                'name' => $userLogin->name,
                'email' => $userLogin->email,
                'user_type' => $userLogin->user_type,
            ]
        ];

        switch ($userLogin->user_type) {
            case 'Admin':
                $school = School::where('registration_number', $username)->first();

                if (!$school) {
                    return response()->json([
                        'success' => false,
                        'message' => 'School not found for this admin account.'
                    ], 404);
                }

                $responseData['schoolID'] = $school->schoolID;
                $responseData['school'] = [
                    'schoolID' => $school->schoolID,
                    'school_name' => $school->school_name ?? null,
                    'registration_number' => $school->registration_number ?? null,
                ];
                break;

            case 'Teacher':
                $teacher = Teacher::where('employee_number', $username)->first();

                if (!$teacher) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Teacher record not found.'
                    ], 404);
                }

                $responseData['schoolID'] = $teacher->schoolID;
                $responseData['teacherID'] = $teacher->id;
                $responseData['teacher'] = [
                    'id' => $teacher->id,
                    'first_name' => $teacher->first_name ?? null,
                    'last_name' => $teacher->last_name ?? null,
                    'employee_number' => $teacher->employee_number ?? null,
                ];

                // Load teacher roles if Spatie is installed
                if (class_exists(\Spatie\Permission\Models\Permission::class) && method_exists($teacher, 'roles')) {
                    $roles = $teacher->roles()->pluck('name')->toArray();
                    $responseData['teacher_roles'] = $roles;
                }
                break;

            case 'parent':
                $parent = ParentModel::where('phone', $username)->first();

                if (!$parent) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parent record not found.'
                    ], 404);
                }

                $responseData['parentID'] = $parent->parentID;
                $responseData['schoolID'] = $parent->schoolID;
                $responseData['parent'] = [
                    'parentID' => $parent->parentID,
                    'phone' => $parent->phone ?? null,
                ];
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user type.'
                ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $responseData
        ], 200);
    }

    /**
     * Logout API
     * 
     * This endpoint logs out the current user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Logged out successfully"
     * }
     */
    public function logout(Request $request)
    {
        // For API, we might want to invalidate tokens in the future
        // For now, just return success
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.'
        ], 200);
    }

    /**
     * Get User Profile API
     * 
     * This endpoint returns the authenticated user's profile information
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Request Headers:
     * - user_id: integer (required) - User ID from login response
     * - user_type: string (required) - User type (Admin|Teacher|parent)
     * 
     * Success Response (200):
     * {
     *   "success": true,
     *   "data": {
     *     "user": {...},
     *     "profile": {...}
     *   }
     * }
     */
    public function getUserProfile(Request $request)
    {
        $userId = $request->header('user_id') ?? $request->input('user_id');
        $userType = $request->header('user_type') ?? $request->input('user_type');

        if (!$userId || !$userType) {
            return response()->json([
                'success' => false,
                'message' => 'User ID and User Type are required'
            ], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $responseData = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
            ]
        ];

        // Add type-specific data
        switch ($userType) {
            case 'Admin':
                $school = School::where('registration_number', $user->name)->first();
                if ($school) {
                    $responseData['school'] = [
                        'schoolID' => $school->schoolID,
                        'school_name' => $school->school_name ?? null,
                        'registration_number' => $school->registration_number ?? null,
                    ];
                }
                break;

            case 'Teacher':
                $teacher = Teacher::where('employee_number', $user->name)->first();
                if ($teacher) {
                    $responseData['teacher'] = [
                        'id' => $teacher->id,
                        'first_name' => $teacher->first_name ?? null,
                        'last_name' => $teacher->last_name ?? null,
                        'employee_number' => $teacher->employee_number ?? null,
                        'schoolID' => $teacher->schoolID,
                    ];
                }
                break;

            case 'parent':
                $parent = ParentModel::where('phone', $user->name)->first();
                if ($parent) {
                    $responseData['parent'] = [
                        'parentID' => $parent->parentID,
                        'phone' => $parent->phone ?? null,
                        'schoolID' => $parent->schoolID,
                    ];
                }
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $responseData
        ], 200);
    }

    /**
     * Parent Dashboard API
     * 
     * This endpoint returns all dashboard data for a parent including:
     * - Parent details
     * - Students list
     * - Statistics (total students, active students, gender distribution)
     * - Recent results
     * - Recent attendance
     * - Attendance statistics per student
     * - Active book borrows
     * - Upcoming exams
     * - Notifications
     * - School details
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Request Parameters (Query or Headers):
     * - parentID: integer (required) - Parent ID
     * - schoolID: integer (required) - School ID
     * 
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Parent dashboard data retrieved successfully",
     *   "data": {
     *     "parent": {...},
     *     "school": {...},
     *     "statistics": {...},
     *     "students": [...],
     *     "recent_results": [...],
     *     "recent_attendance": [...],
     *     "attendance_stats": {...},
     *     "active_book_borrows": [...],
     *     "upcoming_exams": [...],
     *     "notifications": [...]
     *   }
     * }
     * 
     * Error Response (400/404):
     * {
     *   "success": false,
     *   "message": "Error message"
     * }
     */
    public function parentDashboard(Request $request)
    {
        // Get parentID and schoolID from request (query params or headers)
        $parentID = $request->input('parentID') ?? $request->header('parentID');
        $schoolID = $request->input('schoolID') ?? $request->header('schoolID');

        // Validate required parameters
        if (!$parentID || !$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'Parent ID and School ID are required'
            ], 400);
        }

        // Get parent details - filtered by parentID and schoolID
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Parent not found'
            ], 404);
        }

        // Get all students of this parent - filtered by parentID and schoolID
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class', 'subclass.classTeacher'])
            ->get();

        // Statistics
        $totalStudents = $students->count();
        $activeStudents = $students->where('status', 'Active')->count();
        $maleStudents = $students->where('gender', 'Male')->count();
        $femaleStudents = $students->where('gender', 'Female')->count();

        // Get recent results (last 5) - filtered by student IDs and schoolID
        $recentResults = Result::whereIn('studentID', $students->pluck('studentID'))
            ->whereHas('student', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })
            ->with(['student', 'examination', 'classSubject.subject'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent attendance (last 7 days) - filtered by student IDs and schoolID
        $recentAttendance = Attendance::whereIn('studentID', $students->pluck('studentID'))
            ->whereHas('student', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })
            ->where('attendance_date', '>=', Carbon::now()->subDays(7))
            ->with(['student', 'subclass.class'])
            ->orderBy('attendance_date', 'desc')
            ->limit(10)
            ->get();

        // Calculate attendance statistics per student
        $attendanceStats = [];
        foreach ($students as $student) {
            $totalDays = Attendance::where('studentID', $student->studentID)
                ->where('attendance_date', '>=', Carbon::now()->startOfMonth())
                ->count();
            
            $presentDays = Attendance::where('studentID', $student->studentID)
                ->where('attendance_date', '>=', Carbon::now()->startOfMonth())
                ->where('status', 'Present')
                ->count();
            
            $attendanceStats[$student->studentID] = [
                'total' => $totalDays,
                'present' => $presentDays,
                'percentage' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0
            ];
        }

        // Get active book borrows - filtered by student IDs and schoolID
        $activeBookBorrows = BookBorrow::whereIn('studentID', $students->pluck('studentID'))
            ->whereHas('student', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })
            ->where('status', 'borrowed')
            ->with(['student', 'book'])
            ->orderBy('borrow_date', 'desc')
            ->get();

        // Get upcoming exams (next 30 days) - filtered by subclass IDs and schoolID
        $upcomingExams = ExamTimetable::whereIn('subclassID', $students->pluck('subclassID'))
            ->whereHas('subclass', function($q) use ($schoolID) {
                $q->whereHas('class', function($q2) use ($schoolID) {
                    $q2->where('schoolID', $schoolID);
                });
            })
            ->where('exam_date', '>=', Carbon::now())
            ->where('exam_date', '<=', Carbon::now()->addDays(30))
            ->with(['examination', 'subclass.class', 'subject'])
            ->orderBy('exam_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(10)
            ->get();

        // Get recent examinations - filtered by schoolID
        $recentExaminations = Examination::where('schoolID', $schoolID)
            ->where('status', 'results_available')
            ->orderBy('end_date', 'desc')
            ->limit(5)
            ->get();

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        // Build notifications
        $notifications = collect();
        
        // New results notifications
        foreach ($recentResults as $result) {
            $notifications->push([
                'type' => 'result',
                'icon' => 'bi-trophy',
                'color' => 'success',
                'title' => 'New Result Available',
                'message' => $result->student->first_name . ' ' . $result->student->last_name . ' - ' . ($result->examination->exam_name ?? 'Exam'),
                'date' => $result->created_at->toDateTimeString(),
                'link' => '#'
            ]);
        }

        // Absent/Late attendance notifications (today)
        $todayAttendance = Attendance::whereIn('studentID', $students->pluck('studentID'))
            ->where('attendance_date', Carbon::today())
            ->whereIn('status', ['Absent', 'Late'])
            ->with('student')
            ->get();

        foreach ($todayAttendance as $attendance) {
            $notifications->push([
                'type' => 'attendance',
                'icon' => 'bi-exclamation-triangle',
                'color' => $attendance->status == 'Absent' ? 'danger' : 'warning',
                'title' => 'Attendance Alert',
                'message' => $attendance->student->first_name . ' ' . $attendance->student->last_name . ' was ' . $attendance->status,
                'date' => $attendance->attendance_date ? Carbon::parse($attendance->attendance_date)->toDateTimeString() : now()->toDateTimeString(),
                'link' => '#'
            ]);
        }

        // Upcoming exams notifications
        foreach ($upcomingExams->take(5) as $exam) {
            $notifications->push([
                'type' => 'exam',
                'icon' => 'bi-calendar-event',
                'color' => 'info',
                'title' => 'Upcoming Exam',
                'message' => ($exam->examination->exam_name ?? 'Exam') . ' - ' . ($exam->subject->subject_name ?? 'Subject'),
                'date' => $exam->exam_date ? Carbon::parse($exam->exam_date)->toDateTimeString() : now()->toDateTimeString(),
                'link' => '#'
            ]);
        }

        // Sort notifications by date
        $notifications = $notifications->sortByDesc('date')->take(10)->values();

        // Format response data
        $responseData = [
            'parent' => [
                'parentID' => $parent->parentID,
                'first_name' => $parent->first_name ?? null,
                'middle_name' => $parent->middle_name ?? null,
                'last_name' => $parent->last_name ?? null,
                'phone' => $parent->phone ?? null,
                'email' => $parent->email ?? null,
                'occupation' => $parent->occupation ?? null,
                'address' => $parent->address ?? null,
                'photo' => $parent->photo ? url('userImages/' . $parent->photo) : null,
                'schoolID' => $parent->schoolID,
            ],
            'school' => $school ? [
                'schoolID' => $school->schoolID,
                'school_name' => $school->school_name ?? null,
                'registration_number' => $school->registration_number ?? null,
            ] : null,
            'statistics' => [
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'male_students' => $maleStudents,
                'female_students' => $femaleStudents,
                'recent_results_count' => $recentResults->count(),
                'upcoming_exams_count' => $upcomingExams->count(),
            ],
            'students' => $students->map(function($student) use ($attendanceStats) {
                return [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name ?? null,
                    'middle_name' => $student->middle_name ?? null,
                    'last_name' => $student->last_name ?? null,
                    'admission_number' => $student->admission_number ?? null,
                    'gender' => $student->gender ?? null,
                    'status' => $student->status ?? null,
                    'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                    'class' => $student->subclass && $student->subclass->class ? [
                        'class_name' => $student->subclass->class->class_name ?? null,
                        'subclass_name' => $student->subclass->subclass_name ?? null,
                    ] : null,
                    'attendance_percentage' => $attendanceStats[$student->studentID]['percentage'] ?? 0,
                ];
            }),
            'recent_results' => $recentResults->map(function($result) {
                return [
                    'resultID' => $result->resultID ?? null,
                    'student' => [
                        'studentID' => $result->student->studentID ?? null,
                        'first_name' => $result->student->first_name ?? null,
                        'last_name' => $result->student->last_name ?? null,
                    ],
                    'examination' => [
                        'examID' => $result->examination->examID ?? null,
                        'exam_name' => $result->examination->exam_name ?? null,
                    ],
                    'subject' => $result->classSubject && $result->classSubject->subject ? [
                        'subject_name' => $result->classSubject->subject->subject_name ?? null,
                    ] : null,
                    'marks' => $result->marks ?? null,
                    'grade' => $result->grade ?? null,
                    'date' => $result->created_at ? $result->created_at->format('Y-m-d') : null,
                ];
            }),
            'recent_attendance' => $recentAttendance->map(function($attendance) {
                return [
                    'attendanceID' => $attendance->attendanceID ?? null,
                    'student' => [
                        'studentID' => $attendance->student->studentID ?? null,
                        'first_name' => $attendance->student->first_name ?? null,
                        'last_name' => $attendance->student->last_name ?? null,
                    ],
                    'class' => $attendance->subclass && $attendance->subclass->class ? [
                        'class_name' => $attendance->subclass->class->class_name ?? null,
                        'subclass_name' => $attendance->subclass->subclass_name ?? null,
                    ] : null,
                    'attendance_date' => $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : null,
                    'status' => $attendance->status ?? null,
                    'remark' => $attendance->remark ?? null,
                ];
            }),
            'attendance_stats' => $attendanceStats,
            'active_book_borrows' => $activeBookBorrows->map(function($borrow) {
                return [
                    'borrowID' => $borrow->borrowID ?? null,
                    'student' => [
                        'studentID' => $borrow->student->studentID ?? null,
                        'first_name' => $borrow->student->first_name ?? null,
                        'last_name' => $borrow->student->last_name ?? null,
                    ],
                    'book' => [
                        'book_title' => $borrow->book->book_title ?? null,
                    ],
                    'borrow_date' => $borrow->borrow_date ? $borrow->borrow_date->format('Y-m-d') : null,
                    'expected_return_date' => $borrow->expected_return_date ? $borrow->expected_return_date->format('Y-m-d') : null,
                    'status' => $borrow->status ?? null,
                    'is_overdue' => $borrow->expected_return_date && $borrow->expected_return_date < now(),
                ];
            }),
            'upcoming_exams' => $upcomingExams->map(function($exam) {
                return [
                    'exam_timetableID' => $exam->exam_timetableID ?? null,
                    'examination' => [
                        'examID' => $exam->examination->examID ?? null,
                        'exam_name' => $exam->examination->exam_name ?? null,
                    ],
                    'subject' => [
                        'subject_name' => $exam->subject->subject_name ?? null,
                    ],
                    'class' => $exam->subclass && $exam->subclass->class ? [
                        'class_name' => $exam->subclass->class->class_name ?? null,
                        'subclass_name' => $exam->subclass->subclass_name ?? null,
                    ] : null,
                    'exam_date' => $exam->exam_date ? $exam->exam_date->format('Y-m-d') : null,
                    'start_time' => $exam->start_time ?? null,
                    'end_time' => $exam->end_time ?? null,
                ];
            }),
            'notifications' => $notifications->map(function($notification) {
                return [
                    'type' => $notification['type'] ?? null,
                    'icon' => $notification['icon'] ?? null,
                    'color' => $notification['color'] ?? null,
                    'title' => $notification['title'] ?? null,
                    'message' => $notification['message'] ?? null,
                    'date' => $notification['date'] ?? null,
                    'link' => $notification['link'] ?? null,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Parent dashboard data retrieved successfully',
            'data' => $responseData
        ], 200);
    }

    /**
     * API Health Check
     * 
     * This endpoint checks if the API is running
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "API is running",
     *   "server_ip": "192.168.100.104",
     *   "timestamp": "2024-01-01 12:00:00"
     * }
     */
    public function healthCheck()
    {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'server_ip' => '192.168.100.104',
            'timestamp' => now()->toDateTimeString()
        ], 200);
    }
}

