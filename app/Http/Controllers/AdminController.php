<?php

namespace App\Http\Controllers;

use App\Models\SessionTask;
use App\Models\Teacher;
use App\Models\ClassSessionTimetable;
use App\Models\School;
use App\Models\ClassSubject;
use App\Models\SchemeOfWork;
use App\Models\SchemeOfWorkItem;
use App\Models\LessonPlan;
use App\Models\SchoolSubject;
use App\Models\PermissionRequest;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\SchoolVisitor;
use App\Models\SchoolVisitorSmsLog;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function AdminDashboard()
    {
         $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');
        
        // Dashboard Statistics
        $dashboardStats = [];
        if ($schoolID) {
            // Count all active subjects in school
            $subjectsCount = DB::table('school_subjects')
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->count();
            
            // Count all active classes (subclasses) in school
            $classesCount = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->where('subclasses.status', 'Active')
                ->count();
            
            // Count all active students in school
            $studentsCount = DB::table('students')
                ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->where('students.status', 'Active')
                ->count();
            
            // Count all parents in school
            $parentsCount = DB::table('parents')
                ->where('schoolID', $schoolID)
                ->count();
            
            // Count all teachers in school
            $teachersCount = DB::table('teachers')
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->count();
            
            // Count all examinations in school
            $examinationsCount = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->count();
            
            // Count all fees records
            $feesCount = DB::table('fees')
                ->where('schoolID', $schoolID)
                ->count();
            
            // Get active session timetable definition
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();
            
            // Count sessions per week (Monday-Friday) - all sessions in school
            $sessionsPerWeek = 0;
            if ($definition) {
                $sessionsPerWeek = DB::table('class_session_timetables')
                    ->where('definitionID', $definition->definitionID)
                    ->whereIn('day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
                    ->count();
            }
            
            // Count sessions per year (excluding holidays and events)
            $sessionsPerYear = 0;
            if ($definition) {
                $currentYear = Carbon::now()->year;
                $yearStart = Carbon::create($currentYear, 1, 1);
                $yearEnd = Carbon::create($currentYear, 12, 31);
                
                // Get all holidays for the year
                $yearHolidays = DB::table('holidays')
                    ->where('schoolID', $schoolID)
                    ->where(function($query) use ($yearStart, $yearEnd) {
                        $query->whereBetween('start_date', [$yearStart, $yearEnd])
                            ->orWhereBetween('end_date', [$yearStart, $yearEnd])
                            ->orWhere(function($q) use ($yearStart, $yearEnd) {
                                $q->where('start_date', '<=', $yearStart)
                                  ->where('end_date', '>=', $yearEnd);
                            });
                    })
                    ->get();
                
                // Get non-working events
                $yearEvents = DB::table('events')
                    ->where('schoolID', $schoolID)
                    ->whereYear('event_date', $currentYear)
                    ->where('is_non_working_day', true)
                    ->get();
                
                // Calculate total working days in year
                $totalWorkingDays = 0;
                $current = $yearStart->copy();
                while ($current <= $yearEnd) {
                    // Check if it's a weekday (Monday-Friday)
                    if (in_array($current->dayOfWeek, [Carbon::MONDAY, Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::FRIDAY])) {
                        $dateStr = $current->format('Y-m-d');
                        $isHoliday = false;
                        
                        // Check holidays
                        foreach ($yearHolidays as $holiday) {
                            $holidayStart = Carbon::parse($holiday->start_date);
                            $holidayEnd = Carbon::parse($holiday->end_date);
                            if ($current->between($holidayStart, $holidayEnd)) {
                                $isHoliday = true;
                                break;
                            }
                        }
                        
                        // Check events
                        if (!$isHoliday) {
                            foreach ($yearEvents as $event) {
                                if ($current->format('Y-m-d') === Carbon::parse($event->event_date)->format('Y-m-d')) {
                                    $isHoliday = true;
                                    break;
                                }
                            }
                        }
                        
                        if (!$isHoliday) {
                            $totalWorkingDays++;
                        }
                    }
                    $current->addDay();
                }
                
                // Sessions per year = sessions per week * (total working days / 5)
                if ($sessionsPerWeek > 0) {
                    $sessionsPerYear = (int)($sessionsPerWeek * ($totalWorkingDays / 5));
                }
            }
            
            // Count approved sessions (sessions with approved tasks) - all teachers
            $approvedSessionsCount = 0;
            if ($definition) {
                $approvedSessionsCount = DB::table('session_tasks')
                    ->join('class_session_timetables', 'session_tasks.session_timetableID', '=', 'class_session_timetables.session_timetableID')
                    ->where('session_tasks.status', 'approved')
                    ->where('class_session_timetables.definitionID', $definition->definitionID)
                    ->distinct('session_tasks.session_timetableID')
                    ->count('session_tasks.session_timetableID');
            }
            
            $dashboardStats = [
                'subjects_count' => $subjectsCount,
                'classes_count' => $classesCount,
                'students_count' => $studentsCount,
                'parents_count' => $parentsCount,
                'teachers_count' => $teachersCount,
                'examinations_count' => $examinationsCount,
                'fees_count' => $feesCount,
                'sessions_per_week' => $sessionsPerWeek,
                'sessions_per_year' => $sessionsPerYear,
                'approved_sessions_count' => $approvedSessionsCount,
            ];
        }

        // Return admin dashboard view
        // Note: school_details is already shared via AppServiceProvider
        return view('Admin.dashboard', compact('dashboardStats'));
    }

    /**
     * Task Management page for Admin
     */
    public function taskManagement()
    {
        $user = Session::get('user_type');
        $taskPermissions = ['task_create', 'task_update', 'task_delete', 'task_read_only'];
        if (!$user || ($user !== 'Admin' && ! $this->staffHasAnyPermission($taskPermissions))) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        // Get all teachers for filter
        $teachers = Teacher::where('schoolID', $schoolID)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('Admin.task_management', compact('teachers'));
    }

    /**
     * Get teacher tasks with filters
     */
    public function getTeacherTasks(Request $request)
    {
        try {
            $user = Session::get('user_type');
            $taskPermissions = ['task_create', 'task_update', 'task_delete', 'task_read_only'];
            if (!$user || ($user !== 'Admin' && ! $this->staffHasAnyPermission($taskPermissions))) {
                return response()->json(['success' => false, 'error' => 'Unauthorized access'], 403);
            }

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired'], 401);
            }

            $teacherID = $request->input('teacherID');
            $date = $request->input('date');
            $status = $request->input('status');

            $query = SessionTask::with([
                'teacher', 
                'sessionTimetable.subject',
                'sessionTimetable.classSubject.subject',
                'sessionTimetable.subclass.class'
            ])
                ->where('schoolID', $schoolID);

            if ($teacherID) {
                $query->where('teacherID', $teacherID);
            }

            if ($date) {
                $query->where('task_date', $date);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $tasks = $query->orderBy('task_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($task) {
                    $startTime = $task->sessionTimetable->start_time ?? null;
                    $endTime = $task->sessionTimetable->end_time ?? null;
                    $day = $task->sessionTimetable->day ?? 'N/A';
                    
                    // Get subject name - try from subject relationship first, then from classSubject
                    $subjectName = 'N/A';
                    if ($task->sessionTimetable) {
                        if ($task->sessionTimetable->subject && $task->sessionTimetable->subject->subject_name) {
                            $subjectName = $task->sessionTimetable->subject->subject_name;
                        } elseif ($task->sessionTimetable->classSubject && 
                                  $task->sessionTimetable->classSubject->subject && 
                                  $task->sessionTimetable->classSubject->subject->subject_name) {
                            $subjectName = $task->sessionTimetable->classSubject->subject->subject_name;
                        }
                    }
                    
                    return [
                        'session_taskID' => $task->session_taskID,
                        'teacher_name' => $task->teacher ? ($task->teacher->first_name . ' ' . $task->teacher->last_name) : 'N/A',
                        'subject_name' => $subjectName,
                        'class_name' => $task->sessionTimetable->subclass ? 
                            ($task->sessionTimetable->subclass->class->class_name ?? '') . ' - ' . ($task->sessionTimetable->subclass->subclass_name ?? '') : 'N/A',
                        'task_date' => $task->task_date->format('Y-m-d'),
                        'task_date_display' => $task->task_date->format('F d, Y'),
                        'day' => $day,
                        'start_time' => $startTime ? \Carbon\Carbon::parse($startTime)->format('h:i A') : 'N/A',
                        'end_time' => $endTime ? \Carbon\Carbon::parse($endTime)->format('h:i A') : 'N/A',
                        'time_display' => ($startTime && $endTime) ? 
                            \Carbon\Carbon::parse($startTime)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($endTime)->format('h:i A') : 'N/A',
                        'topic' => ($task->topic && trim($task->topic)) ? $task->topic : 'N/A',
                        'subtopic' => ($task->subtopic && trim($task->subtopic)) ? $task->subtopic : 'N/A',
                        'task_description' => $task->task_description,
                        'status' => $task->status,
                        'admin_comment' => $task->admin_comment,
                        'approved_at' => $task->approved_at ? $task->approved_at->format('Y-m-d H:i') : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve a task
     */
    public function approveTask($taskID)
    {
        try {
            $user = Session::get('user_type');
            if (!$user || $user !== 'Admin') {
                return response()->json(['success' => false, 'error' => 'Unauthorized access'], 403);
            }

            $validator = Validator::make(request()->all(), [
                'admin_comment' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $task = SessionTask::with([
                'teacher',
                'sessionTimetable.subject',
                'sessionTimetable.classSubject.subject',
                'sessionTimetable.subclass.class'
            ])->findOrFail($taskID);
            
            // Verify task belongs to admin's school
            $schoolID = Session::get('schoolID');
            if ($task->schoolID != $schoolID) {
                return response()->json(['success' => false, 'error' => 'Unauthorized access'], 403);
            }

            $task->update([
                'status' => 'approved',
                'admin_comment' => request()->input('admin_comment'),
                'approved_by' => Session::get('user_id'), // Assuming user_id is stored in session
                'approved_at' => now(),
            ]);

            // Send SMS to teacher
            try {
                $teacher = $task->teacher;
                $school = \App\Models\School::find($schoolID);
                $schoolName = $school ? $school->school_name : 'ShuleLink';
                
                // Get subject and class info
                $subjectName = 'N/A';
                $className = 'N/A';
                if ($task->sessionTimetable) {
                    if ($task->sessionTimetable->subject) {
                        $subjectName = $task->sessionTimetable->subject->subject_name;
                    } elseif ($task->sessionTimetable->classSubject && $task->sessionTimetable->classSubject->subject) {
                        $subjectName = $task->sessionTimetable->classSubject->subject->subject_name;
                    }
                    
                    if ($task->sessionTimetable->subclass) {
                        $class = $task->sessionTimetable->subclass->class;
                        $subclassName = trim($task->sessionTimetable->subclass->subclass_name);
                        $className = $class->class_name;
                        if ($subclassName !== '') {
                            $className .= ' - ' . $subclassName;
                        }
                    }
                }
                
                $taskDate = $task->task_date->format('d/m/Y');
                $comment = request()->input('admin_comment');
                $topic = $task->topic ?? '';
                $subtopic = $task->subtopic ?? '';
                
                // Build SMS message
                $message = "{$schoolName}. Task yako imeidhinishwa. Somo: {$subjectName}, Darasa: {$className}, Tarehe: {$taskDate}";
                if ($topic) {
                    $message .= ". Topic: {$topic}";
                }
                if ($subtopic) {
                    $message .= ", Subtopic: {$subtopic}";
                }
                if ($comment && trim($comment)) {
                    $message .= ". Maoni: " . trim($comment);
                }
                $message .= ". Asante";
                
                if ($teacher && $teacher->phone_number) {
                    $smsService = new \App\Services\SmsService();
                    $smsResult = $smsService->sendSms($teacher->phone_number, $message);
                    
                    if (!$smsResult['success']) {
                        \Illuminate\Support\Facades\Log::warning("Failed to send approval SMS to teacher {$teacher->id}: " . ($smsResult['message'] ?? 'Unknown error'));
                    }
                }
            } catch (\Exception $smsException) {
                \Illuminate\Support\Facades\Log::error('Error sending approval SMS to teacher: ' . $smsException->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Task approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject a task
     */
    public function rejectTask($taskID)
    {
        try {
            $user = Session::get('user_type');
            if (!$user || $user !== 'Admin') {
                return response()->json(['success' => false, 'error' => 'Unauthorized access'], 403);
            }

            $validator = Validator::make(request()->all(), [
                'admin_comment' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $task = SessionTask::with([
                'teacher',
                'sessionTimetable.subject',
                'sessionTimetable.classSubject.subject',
                'sessionTimetable.subclass.class'
            ])->findOrFail($taskID);
            
            // Verify task belongs to admin's school
            $schoolID = Session::get('schoolID');
            if ($task->schoolID != $schoolID) {
                return response()->json(['success' => false, 'error' => 'Unauthorized access'], 403);
            }

            $task->update([
                'status' => 'rejected',
                'admin_comment' => request()->input('admin_comment'),
                'approved_by' => Session::get('user_id'),
                'approved_at' => now(),
            ]);

            // Send SMS to teacher with rejection reason
            try {
                $teacher = $task->teacher;
                $school = \App\Models\School::find($schoolID);
                $schoolName = $school ? $school->school_name : 'ShuleLink';
                
                // Get subject and class info
                $subjectName = 'N/A';
                $className = 'N/A';
                if ($task->sessionTimetable) {
                    if ($task->sessionTimetable->subject) {
                        $subjectName = $task->sessionTimetable->subject->subject_name;
                    } elseif ($task->sessionTimetable->classSubject && $task->sessionTimetable->classSubject->subject) {
                        $subjectName = $task->sessionTimetable->classSubject->subject->subject_name;
                    }
                    
                    if ($task->sessionTimetable->subclass) {
                        $class = $task->sessionTimetable->subclass->class;
                        $subclassName = trim($task->sessionTimetable->subclass->subclass_name);
                        $className = $class->class_name;
                        if ($subclassName !== '') {
                            $className .= ' - ' . $subclassName;
                        }
                    }
                }
                
                $taskDate = $task->task_date->format('d/m/Y');
                $reason = request()->input('admin_comment'); // This is the rejection reason
                $topic = $task->topic ?? '';
                $subtopic = $task->subtopic ?? '';
                
                // Build SMS message
                $message = "{$schoolName}. Task yako imekataliwa. Somo: {$subjectName}, Darasa: {$className}, Tarehe: {$taskDate}";
                if ($topic) {
                    $message .= ". Topic: {$topic}";
                }
                if ($subtopic) {
                    $message .= ", Subtopic: {$subtopic}";
                }
                if ($reason && trim($reason)) {
                    $message .= ". Sababu: " . trim($reason);
                }
                $message .= ". Asante";
                
                if ($teacher && $teacher->phone_number) {
                    $smsService = new \App\Services\SmsService();
                    $smsResult = $smsService->sendSms($teacher->phone_number, $message);
                    
                    if (!$smsResult['success']) {
                        \Illuminate\Support\Facades\Log::warning("Failed to send rejection SMS to teacher {$teacher->id}: " . ($smsResult['message'] ?? 'Unknown error'));
                    }
                }
            } catch (\Exception $smsException) {
                \Illuminate\Support\Facades\Log::error('Error sending rejection SMS to teacher: ' . $smsException->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Task rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function adminSchemeOfWork()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        if (!$schoolID) {
            return redirect()->route('AdminDashboard')->with('error', 'School ID not found');
        }

        // Get all class subjects with scheme of work for this school
        $currentYear = date('Y');
        
        $classSubjectsWithSchemes = ClassSubject::with([
                'subject',
                'class',
                'subclass.class',
                'teacher',
                'schemeOfWork' => function($query) use ($currentYear) {
                    $query->where('year', $currentYear)->orderBy('year', 'desc');
                },
                'schemeOfWork.items',
                'schemeOfWork.createdBy'
            ])
            ->whereHas('class', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID)->where('status', 'Active');
            })
            ->where('status', 'Active')
            ->get()
            ->map(function($classSubject) use ($currentYear) {
                // Get current year scheme
                $currentScheme = $classSubject->schemeOfWork->where('year', $currentYear)->first();
                
                // Calculate progress (percentage of items marked as done)
                $progress = 0;
                $totalItems = 0;
                $doneItems = 0;
                
                if ($currentScheme) {
                    $totalItems = $currentScheme->items->count();
                    $doneItems = $currentScheme->items->where('remarks', 'done')->count();
                    if ($totalItems > 0) {
                        $progress = round(($doneItems / $totalItems) * 100, 2);
                    }
                }
                
                return [
                    'class_subjectID' => $classSubject->class_subjectID,
                    'subject_name' => $classSubject->subject->subject_name ?? 'N/A',
                    'class_name' => $classSubject->subclass && $classSubject->subclass->class 
                        ? $classSubject->subclass->class->class_name . ' ' . $classSubject->subclass->subclass_name
                        : ($classSubject->class ? $classSubject->class->class_name : 'N/A'),
                    'teacher_name' => $classSubject->teacher 
                        ? $classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name
                        : 'Not Assigned',
                    'teacherID' => $classSubject->teacherID,
                    'scheme' => $currentScheme,
                    'progress' => $progress,
                    'totalItems' => $totalItems,
                    'doneItems' => $doneItems,
                    'year' => $currentYear
                ];
            })
            ->sortBy('subject_name')
            ->values();

        return view('Admin.scheme_of_work', compact('classSubjectsWithSchemes'));
    }

    public function adminViewSchemeOfWork($schemeOfWorkID)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        if (!$schoolID) {
            return redirect()->route('AdminDashboard')->with('error', 'School ID not found');
        }

        // Get scheme of work with relationships
        $scheme = SchemeOfWork::with(['classSubject.subject', 'classSubject.class', 'classSubject.subclass.class', 
                                      'items' => function($query) {
                                          $query->orderBy('month')->orderBy('row_order');
                                      }, 
                                      'learningObjectives' => function($query) {
                                          $query->orderBy('order');
                                      },
                                      'createdBy'])
            ->where('scheme_of_workID', $schemeOfWorkID)
            ->first();

        if (!$scheme) {
            return redirect()->route('admin.schemeOfWork')->with('error', 'Scheme of work not found');
        }

        // Verify scheme belongs to admin's school
        $classSubject = $scheme->classSubject;
        $schemeSchoolID = null;
        
        if ($classSubject->subclass && $classSubject->subclass->class) {
            $schemeSchoolID = $classSubject->subclass->class->schoolID;
        } elseif ($classSubject->class) {
            $schemeSchoolID = $classSubject->class->schoolID;
        }

        if ($schemeSchoolID != $schoolID) {
            return redirect()->route('admin.schemeOfWork')->with('error', 'You do not have access to this scheme of work');
        }

        // Get school info
        $school = School::where('schoolID', $schoolID)->first();

        return view('Teacher.view_scheme_of_work', compact('scheme', 'school'));
    }

    /**
     * Admin view lesson plans
     */
    public function adminLessonPlans()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        // Get all subjects for the school
        $subjects = DB::table('school_subjects')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('subject_name')
            ->get();

        // Get all classes for the school
        $classes = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('classes.schoolID', $schoolID)
            ->where('subclasses.status', 'Active')
            ->select('subclasses.subclassID', 'subclasses.subclass_name', 'classes.class_name')
            ->orderBy('classes.class_name')
            ->orderBy('subclasses.subclass_name')
            ->get();

        // Get school info
        $school = School::where('schoolID', $schoolID)->first();
        $schoolType = $school && $school->school_type ? strtolower($school->school_type) : 'primary';
        $isPrimary = strpos($schoolType, 'primary') !== false || strpos($schoolType, 'pre') !== false;
        $schoolTypeDisplay = $isPrimary ? 'PRE AND PRIMARY SCHOOL' : 'SECONDARY SCHOOL';

        return view('Admin.lesson_plans', compact('subjects', 'classes', 'schoolTypeDisplay'));
    }

    /**
     * Manage Other Staff - Redirect to ManageOtherStaffController
     */
    public function manageOtherStaff()
    {
        // Delegate to ManageOtherStaffController
        $controller = new ManageOtherStaffController();
        return $controller->manageOtherStaff();
    }

    /**
     * Manage Revenue
     */
    public function manageRevenue()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        $revenuePermissions = ['revenue_create', 'revenue_update', 'revenue_delete', 'revenue_read_only'];
        if (!$user || ($user !== 'Admin' && ! $this->staffHasAnyPermission($revenuePermissions))) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $revenueSources = \App\Models\RevenueSource::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('source_name')
            ->get();

        return view('Admin.manage_revenue', compact('schoolID', 'revenueSources'));
    }

    /**
     * Store revenue source
     */
    public function storeRevenueSource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'source_name' => 'required|string|max:255',
            'source_type' => 'required|in:fixed,per_item,variable',
            'default_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        \App\Models\RevenueSource::create([
            'schoolID' => $schoolID,
            'source_name' => $validated['source_name'],
            'source_type' => $validated['source_type'],
            'default_amount' => $validated['default_amount'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => 'Active',
        ]);

        return redirect()->route('manage_revenue')->with('success', 'Source of income added successfully.');
    }

    /**
     * Update revenue source
     */
    public function updateRevenueSource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'revenue_sourceID' => 'required|exists:revenue_sources,revenue_sourceID',
            'source_name' => 'required|string|max:255',
            'source_type' => 'required|in:fixed,per_item,variable',
            'default_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $source = \App\Models\RevenueSource::where('revenue_sourceID', $validated['revenue_sourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $source->update([
            'source_name' => $validated['source_name'],
            'source_type' => $validated['source_type'],
            'default_amount' => $validated['default_amount'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('manage_revenue')->with('success', 'Source of income updated successfully.');
    }

    public function deleteRevenueSource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'revenue_sourceID' => 'required|exists:revenue_sources,revenue_sourceID',
        ]);

        $source = \App\Models\RevenueSource::where('revenue_sourceID', $validated['revenue_sourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $source->update(['status' => 'Inactive']);

        return redirect()->route('manage_revenue')->with('success', 'Source of income deleted successfully.');
    }

    /**
     * Store revenue record
     */
    public function storeRevenueRecord(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'record_date' => 'required|date',
            'revenue_sourceID' => 'required|exists:revenue_sources,revenue_sourceID',
            'quantity' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $source = \App\Models\RevenueSource::where('revenue_sourceID', $validated['revenue_sourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $quantity = $validated['quantity'] ?? null;
        $amountInput = $validated['amount'] ?? null;
        $unitAmount = null;
        $totalAmount = 0;

        if ($source->source_type === 'per_item') {
            $unitAmount = $source->default_amount ?? 0;
            $totalAmount = ($unitAmount) * ((int) ($quantity ?? 0));
        } elseif ($source->source_type === 'fixed') {
            $unitAmount = $source->default_amount ?? 0;
            $totalAmount = $unitAmount;
        } else {
            $unitAmount = $amountInput ?? 0;
            $totalAmount = $unitAmount;
        }

        if ($totalAmount <= 0) {
            return redirect()->route('manage_revenue')->with('error', 'Amount must be greater than 0.');
        }

        \App\Models\RevenueRecord::create([
            'schoolID' => $schoolID,
            'revenue_sourceID' => $source->revenue_sourceID,
            'record_date' => $validated['record_date'],
            'unit_amount' => $unitAmount,
            'quantity' => $quantity,
            'total_amount' => $totalAmount,
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('manage_revenue')->with('success', 'Revenue recorded successfully.');
    }

    /**
     * Get revenue report data for other sources
     */
    public function revenueReportData(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $year = $request->input('year', date('Y'));

        $totals = \App\Models\RevenueRecord::where('schoolID', $schoolID)
            ->whereYear('record_date', $year)
            ->select('revenue_sourceID', \DB::raw('SUM(total_amount) as total_amount'))
            ->groupBy('revenue_sourceID')
            ->get()
            ->pluck('total_amount', 'revenue_sourceID');

        return response()->json([
            'success' => true,
            'data' => $totals,
        ]);
    }

    /**
     * Manage Expenses
     */
    public function manageExpenses()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        $expensePermissions = ['expenses_create', 'expenses_update', 'expenses_delete', 'expenses_read_only'];
        if (!$user || ($user !== 'Admin' && ! $this->staffHasAnyPermission($expensePermissions))) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $year = request()->input('year', date('Y'));

        $totalRevenue = $this->getTotalRevenueForYear($schoolID, $year);
        $budget = \App\Models\ExpenseBudget::where('schoolID', $schoolID)
            ->where('year', $year)
            ->first();

        $expenses = \App\Models\ExpenseRecord::where('schoolID', $schoolID)
            ->whereYear('expense_date', $year)
            ->orderBy('expense_date', 'desc')
            ->limit(50)
            ->get();

        return view('Admin.manage_expenses', compact('schoolID', 'year', 'totalRevenue', 'budget', 'expenses'));
    }

    private function getTotalRevenueForYear($schoolID, $year)
    {
        $feesRevenue = \DB::table('payment_records')
            ->join('payments', 'payment_records.paymentID', '=', 'payments.paymentID')
            ->join('academic_years', 'payments.academic_yearID', '=', 'academic_years.academic_yearID')
            ->where('payments.schoolID', $schoolID)
            ->where('academic_years.year', $year)
            ->sum('payment_records.paid_amount');

        $otherRevenue = \App\Models\RevenueRecord::where('schoolID', $schoolID)
            ->whereYear('record_date', $year)
            ->sum('total_amount');

        return (float) $feesRevenue + (float) $otherRevenue;
    }

    public function storeExpenseBudget(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        $totalRevenue = $this->getTotalRevenueForYear($schoolID, $validated['year']);
        if ($validated['total_amount'] > $totalRevenue) {
            return redirect()->route('manage_expenses', ['year' => $validated['year']])
                ->with('error', 'Budget cannot exceed total revenue for the selected year.');
        }

        $existing = \App\Models\ExpenseBudget::where('schoolID', $schoolID)
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return redirect()->route('manage_expenses', ['year' => $validated['year']])
                ->with('error', 'Budget for this year already exists. Use update instead.');
        }

        \App\Models\ExpenseBudget::create([
            'schoolID' => $schoolID,
            'year' => $validated['year'],
            'total_amount' => $validated['total_amount'],
            'remaining_amount' => $validated['total_amount'],
            'status' => 'Active',
        ]);

        return redirect()->route('manage_expenses', ['year' => $validated['year']])
            ->with('success', 'Budget created successfully.');
    }

    public function updateExpenseBudget(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'expense_budgetID' => 'required|exists:expense_budgets,expense_budgetID',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        $budget = \App\Models\ExpenseBudget::where('expense_budgetID', $validated['expense_budgetID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $totalRevenue = $this->getTotalRevenueForYear($schoolID, $budget->year);
        if ($validated['total_amount'] > $totalRevenue) {
            return redirect()->route('manage_expenses', ['year' => $budget->year])
                ->with('error', 'Budget cannot exceed total revenue for the selected year.');
        }

        $spent = \App\Models\ExpenseRecord::where('schoolID', $schoolID)
            ->where('expense_budgetID', $budget->expense_budgetID)
            ->sum('amount');

        if ($validated['total_amount'] < $spent) {
            return redirect()->route('manage_expenses', ['year' => $budget->year])
                ->with('error', 'Budget cannot be less than total expenses already recorded.');
        }

        $budget->update([
            'total_amount' => $validated['total_amount'],
            'remaining_amount' => $validated['total_amount'] - $spent,
        ]);

        return redirect()->route('manage_expenses', ['year' => $budget->year])
            ->with('success', 'Budget updated successfully.');
    }

    public function storeExpenseRecord(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'expense_budgetID' => 'required|exists:expense_budgets,expense_budgetID',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
        ]);

        $budget = \App\Models\ExpenseBudget::where('expense_budgetID', $validated['expense_budgetID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        if ($validated['amount'] > $budget->remaining_amount) {
            return redirect()->route('manage_expenses', ['year' => $budget->year])
                ->with('error', 'Expense exceeds remaining budget.');
        }

        \DB::transaction(function () use ($validated, $schoolID, $budget) {
            \App\Models\ExpenseRecord::create([
                'schoolID' => $schoolID,
                'expense_budgetID' => $budget->expense_budgetID,
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
            ]);

            $budget->update([
                'remaining_amount' => $budget->remaining_amount - $validated['amount'],
            ]);
        });

        return redirect()->route('manage_expenses', ['year' => $budget->year])
            ->with('success', 'Expense recorded successfully.');
    }

    public function updateExpenseRecord(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'expense_recordID' => 'required|exists:expense_records,expense_recordID',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
        ]);

        $record = \App\Models\ExpenseRecord::where('expense_recordID', $validated['expense_recordID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $budget = \App\Models\ExpenseBudget::where('expense_budgetID', $record->expense_budgetID)
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $available = $budget->remaining_amount + $record->amount;
        if ($validated['amount'] > $available) {
            return redirect()->route('manage_expenses', ['year' => $budget->year])
                ->with('error', 'Updated amount exceeds remaining budget.');
        }

        \DB::transaction(function () use ($record, $budget, $validated) {
            $budget->update([
                'remaining_amount' => ($budget->remaining_amount + $record->amount) - $validated['amount'],
            ]);

            $record->update([
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
            ]);
        });

        return redirect()->route('manage_expenses', ['year' => $budget->year])
            ->with('success', 'Expense updated successfully.');
    }

    public function deleteExpenseRecord(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'expense_recordID' => 'required|exists:expense_records,expense_recordID',
        ]);

        $record = \App\Models\ExpenseRecord::where('expense_recordID', $validated['expense_recordID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $budget = \App\Models\ExpenseBudget::where('expense_budgetID', $record->expense_budgetID)
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        \DB::transaction(function () use ($record, $budget) {
            $budget->update([
                'remaining_amount' => $budget->remaining_amount + $record->amount,
            ]);
            $record->delete();
        });

        return redirect()->route('manage_expenses', ['year' => $budget->year])
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * School Resources Management Methods
     */
    public function manageIncomingResources()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        $resourcePermissions = ['resources_create', 'resources_update', 'resources_delete', 'resources_read_only'];
        if (!$user || ($user !== 'Admin' && ! $this->staffHasAnyPermission($resourcePermissions))) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $resources = \App\Models\SchoolResource::where('schoolID', $schoolID)
            ->orderBy('resource_name')
            ->get();

        $incomingRecords = \App\Models\IncomingResourceRecord::where('schoolID', $schoolID)
            ->orderBy('received_date', 'desc')
            ->limit(50)
            ->get();

        return view('Admin.manage_incoming_resources', compact('schoolID', 'resources', 'incomingRecords'));
    }

    public function storeSchoolResources(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'resources' => 'required|array|min:1',
            'resources.*.resource_name' => 'required|string|max:255',
            'resources.*.resource_type' => 'required|string|max:100',
            'resources.*.requires_quantity' => 'required|in:0,1',
            'resources.*.requires_price' => 'required|in:0,1',
            'resources.*.quantity' => 'nullable|integer|min:0',
            'resources.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated['resources'] as $resource) {
            $requiresQuantity = (bool) $resource['requires_quantity'];
            $requiresPrice = (bool) $resource['requires_price'];
            $quantity = $requiresQuantity ? (int) ($resource['quantity'] ?? 0) : null;
            $unitPrice = $requiresPrice ? (float) ($resource['unit_price'] ?? 0) : 0;

            \App\Models\SchoolResource::create([
                'schoolID' => $schoolID,
                'resource_name' => $resource['resource_name'],
                'resource_type' => $resource['resource_type'],
                'requires_quantity' => $requiresQuantity,
                'requires_price' => $requiresPrice,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ]);
        }

        return redirect()->route('manage_incoming_resources')->with('success', 'Resources saved successfully.');
    }

    public function updateSchoolResource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'resourceID' => 'required|exists:school_resources,resourceID',
            'resource_name' => 'required|string|max:255',
            'resource_type' => 'required|string|max:100',
            'requires_quantity' => 'required|in:0,1',
            'requires_price' => 'required|in:0,1',
            'quantity' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $resource = \App\Models\SchoolResource::where('resourceID', $validated['resourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $validated['requires_quantity'];
        $requiresPrice = (bool) $validated['requires_price'];
        $quantity = $requiresQuantity ? (int) ($validated['quantity'] ?? 0) : null;
        $unitPrice = $requiresPrice ? (float) ($validated['unit_price'] ?? 0) : 0;

        $resource->update([
            'resource_name' => $validated['resource_name'],
            'resource_type' => $validated['resource_type'],
            'requires_quantity' => $requiresQuantity,
            'requires_price' => $requiresPrice,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);

        return redirect()->route('manage_incoming_resources')->with('success', 'Resource updated successfully.');
    }

    public function deleteSchoolResource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'resourceID' => 'required|exists:school_resources,resourceID',
        ]);

        $resource = \App\Models\SchoolResource::where('resourceID', $validated['resourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $hasIncoming = \App\Models\IncomingResourceRecord::where('schoolID', $schoolID)
            ->where('resourceID', $resource->resourceID)
            ->exists();
        $hasOutgoing = \App\Models\OutgoingResourceRecord::where('schoolID', $schoolID)
            ->where('resourceID', $resource->resourceID)
            ->exists();
        $hasDamaged = \App\Models\DamagedLostRecord::where('schoolID', $schoolID)
            ->where('resourceID', $resource->resourceID)
            ->exists();

        if ($hasIncoming || $hasOutgoing || $hasDamaged) {
            return redirect()->route('manage_incoming_resources')
                ->with('error', 'Cannot delete resource with existing records.');
        }

        $resource->delete();

        return redirect()->route('manage_incoming_resources')->with('success', 'Resource deleted successfully.');
    }

    public function storeIncomingResource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'resourceID' => 'required|exists:school_resources,resourceID',
            'received_date' => 'required|date',
            'quantity' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $resource = \App\Models\SchoolResource::where('resourceID', $validated['resourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $requiresPrice = (bool) $resource->requires_price;
        $quantity = $requiresQuantity ? (int) ($validated['quantity'] ?? 0) : null;
        $unitPrice = $requiresPrice ? (float) ($validated['unit_price'] ?? $resource->unit_price) : 0;
        $totalPrice = $requiresPrice ? ($requiresQuantity ? ($unitPrice * $quantity) : $unitPrice) : 0;

        \DB::transaction(function () use ($schoolID, $resource, $validated, $quantity, $unitPrice, $totalPrice, $requiresQuantity) {
            \App\Models\IncomingResourceRecord::create([
                'schoolID' => $schoolID,
                'resourceID' => $resource->resourceID,
                'received_date' => $validated['received_date'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'note' => $validated['note'] ?? null,
            ]);

            if ($requiresQuantity) {
                $resource->update([
                    'quantity' => ($resource->quantity ?? 0) + $quantity,
                ]);
            }
        });

        return redirect()->route('manage_incoming_resources')->with('success', 'Incoming resource recorded successfully.');
    }

    public function manageOutgoingResources()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        $resourcePermissions = ['resources_create', 'resources_update', 'resources_delete', 'resources_read_only'];
        if (!$user || ($user !== 'Admin' && ! $this->staffHasAnyPermission($resourcePermissions))) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $resources = \App\Models\SchoolResource::where('schoolID', $schoolID)
            ->orderBy('resource_name')
            ->get();

        $outgoingRecords = \App\Models\OutgoingResourceRecord::where('schoolID', $schoolID)
            ->orderBy('outgoing_date', 'desc')
            ->limit(50)
            ->get();

        $outgoingSummary = \App\Models\OutgoingResourceRecord::where('schoolID', $schoolID)
            ->select('resourceID', \DB::raw('SUM(quantity) as total_quantity'), \DB::raw('SUM(total_price) as total_price'))
            ->groupBy('resourceID')
            ->get()
            ->keyBy('resourceID');

        return view('Admin.manage_outgoing_resources', compact('schoolID', 'resources', 'outgoingRecords', 'outgoingSummary'));
    }

    public function storeOutgoingResource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'resourceID' => 'required|exists:school_resources,resourceID',
            'outgoing_date' => 'required|date',
            'outgoing_type' => 'required|in:permanent,temporary',
            'quantity' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'description' => 'required|string',
        ]);

        $resource = \App\Models\SchoolResource::where('resourceID', $validated['resourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $requiresPrice = (bool) $resource->requires_price;
        $quantity = $requiresQuantity ? (int) ($validated['quantity'] ?? 0) : null;
        $unitPrice = $requiresPrice ? (float) ($validated['unit_price'] ?? $resource->unit_price) : 0;
        $totalPrice = $requiresPrice ? ($requiresQuantity ? ($unitPrice * $quantity) : $unitPrice) : 0;

        if ($requiresQuantity && $quantity > ($resource->quantity ?? 0)) {
            return redirect()->route('manage_outgoing_resources')
                ->with('error', 'Outgoing quantity exceeds available stock.');
        }

        \DB::transaction(function () use ($schoolID, $resource, $validated, $quantity, $unitPrice, $totalPrice, $requiresQuantity) {
            \App\Models\OutgoingResourceRecord::create([
                'schoolID' => $schoolID,
                'resourceID' => $resource->resourceID,
                'outgoing_date' => $validated['outgoing_date'],
                'outgoing_type' => $validated['outgoing_type'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'description' => $validated['description'],
            ]);

            if ($requiresQuantity) {
                $resource->update([
                    'quantity' => max(0, ($resource->quantity ?? 0) - $quantity),
                ]);
            }
        });

        return redirect()->route('manage_outgoing_resources')->with('success', 'Outgoing resource recorded successfully.');
    }

    public function updateOutgoingResource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'outgoing_resourceID' => 'required|exists:outgoing_resource_records,outgoing_resourceID',
            'outgoing_date' => 'required|date',
            'outgoing_type' => 'required|in:permanent,temporary',
            'quantity' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'description' => 'required|string',
        ]);

        $record = \App\Models\OutgoingResourceRecord::where('outgoing_resourceID', $validated['outgoing_resourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        if ($record->is_returned) {
            return redirect()->route('manage_outgoing_resources')
                ->with('error', 'Returned records cannot be edited.');
        }

        $resource = \App\Models\SchoolResource::where('resourceID', $record->resourceID)
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $requiresPrice = (bool) $resource->requires_price;
        $newQty = $requiresQuantity ? (int) ($validated['quantity'] ?? 0) : null;
        $unitPrice = $requiresPrice ? (float) ($validated['unit_price'] ?? $resource->unit_price) : 0;
        $totalPrice = $requiresPrice ? ($requiresQuantity ? ($unitPrice * $newQty) : $unitPrice) : 0;

        if ($requiresQuantity) {
            $available = ($resource->quantity ?? 0) + ($record->quantity ?? 0);
            if ($newQty > $available) {
                return redirect()->route('manage_outgoing_resources')
                    ->with('error', 'Outgoing quantity exceeds available stock.');
            }
        }

        \DB::transaction(function () use ($record, $resource, $validated, $requiresQuantity, $newQty, $unitPrice, $totalPrice) {
            if ($requiresQuantity) {
                $restored = ($resource->quantity ?? 0) + ($record->quantity ?? 0);
                $resource->update([
                    'quantity' => max(0, $restored - $newQty),
                ]);
            }

            $record->update([
                'outgoing_date' => $validated['outgoing_date'],
                'outgoing_type' => $validated['outgoing_type'],
                'quantity' => $newQty,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'description' => $validated['description'],
            ]);
        });

        return redirect()->route('manage_outgoing_resources')->with('success', 'Outgoing resource updated successfully.');
    }

    public function deleteOutgoingResource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'outgoing_resourceID' => 'required|exists:outgoing_resource_records,outgoing_resourceID',
        ]);

        $record = \App\Models\OutgoingResourceRecord::where('outgoing_resourceID', $validated['outgoing_resourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $resource = \App\Models\SchoolResource::where('resourceID', $record->resourceID)
            ->where('schoolID', $schoolID)
            ->first();

        \DB::transaction(function () use ($record, $resource) {
            if ($resource && $resource->requires_quantity && !$record->is_returned) {
                $resource->update([
                    'quantity' => ($resource->quantity ?? 0) + ($record->quantity ?? 0),
                ]);
            }
            $record->delete();
        });

        return redirect()->route('manage_outgoing_resources')->with('success', 'Outgoing resource deleted successfully.');
    }

    public function returnOutgoingResource(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'outgoing_resourceID' => 'required|exists:outgoing_resource_records,outgoing_resourceID',
            'returned_at' => 'required|date',
            'return_quantity' => 'nullable|integer|min:0',
            'return_description' => 'required|string',
        ]);

        $record = \App\Models\OutgoingResourceRecord::where('outgoing_resourceID', $validated['outgoing_resourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        if ($record->outgoing_type !== 'temporary') {
            return redirect()->route('manage_outgoing_resources')
                ->with('error', 'Only temporary outgoing resources can be returned.');
        }

        if ($record->is_returned) {
            return redirect()->route('manage_outgoing_resources')
                ->with('error', 'This outgoing record was already returned.');
        }

        $resource = \App\Models\SchoolResource::where('resourceID', $record->resourceID)
            ->where('schoolID', $schoolID)
            ->first();

        $requiresQuantity = $resource && $resource->requires_quantity;
        $recordQty = (int) ($record->quantity ?? 0);
        $alreadyReturned = (int) ($record->returned_quantity ?? 0);
        $remaining = max(0, $recordQty - $alreadyReturned);
        $returnQty = $requiresQuantity ? (int) ($validated['return_quantity'] ?? 0) : 0;

        if ($requiresQuantity) {
            if ($returnQty <= 0) {
                return redirect()->route('manage_outgoing_resources')
                    ->with('error', 'Return quantity must be greater than zero.');
            }
            if ($returnQty > $remaining) {
                return redirect()->route('manage_outgoing_resources')
                    ->with('error', 'Return quantity exceeds remaining quantity.');
            }
        }

        \DB::transaction(function () use ($record, $resource, $validated, $requiresQuantity, $returnQty, $remaining) {
            if ($resource && $resource->requires_quantity) {
                $resource->update([
                    'quantity' => ($resource->quantity ?? 0) + $returnQty,
                ]);
            }

            $newReturned = $requiresQuantity ? (($record->returned_quantity ?? 0) + $returnQty) : 0;
            $record->update([
                'returned_at' => $validated['returned_at'],
                'returned_quantity' => $newReturned,
                'is_returned' => $requiresQuantity ? ($newReturned >= ($record->quantity ?? 0)) : true,
                'return_description' => $validated['return_description'],
            ]);
        });

        return redirect()->route('manage_outgoing_resources')->with('success', 'Resource returned successfully.');
    }

    public function manageBuildingsInfrastructure()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Admin.manage_buildings_infrastructure', compact('schoolID'));
    }

    public function manageDesks()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Admin.manage_desks', compact('schoolID'));
    }

    public function manageChairs()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Admin.manage_chairs', compact('schoolID'));
    }

    public function manageChalk()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Admin.manage_chalk', compact('schoolID'));
    }

    public function manageBooks()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Admin.manage_books', compact('schoolID'));
    }

    public function manageTeachingAids()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Admin.manage_teaching_aids', compact('schoolID'));
    }

    public function inventoryList()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Admin.inventory_list', compact('schoolID'));
    }

    public function manageDamagedLostItems()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $resources = \App\Models\SchoolResource::where('schoolID', $schoolID)
            ->orderBy('resource_name')
            ->get();

        $records = \App\Models\DamagedLostRecord::where('schoolID', $schoolID)
            ->orderBy('record_date', 'desc')
            ->limit(50)
            ->get();

        $summary = \App\Models\DamagedLostRecord::where('schoolID', $schoolID)
            ->select(
                'resourceID',
                \DB::raw("SUM(CASE WHEN record_type = 'damaged' THEN quantity ELSE 0 END) as total_damaged"),
                \DB::raw("SUM(CASE WHEN record_type = 'lost' THEN quantity ELSE 0 END) as total_lost"),
                \DB::raw("SUM(CASE WHEN record_type = 'used_up' THEN quantity ELSE 0 END) as total_used_up")
            )
            ->groupBy('resourceID')
            ->get()
            ->keyBy('resourceID');

        return view('Admin.manage_damaged_lost_items', compact('schoolID', 'resources', 'records', 'summary'));
    }

    public function manageTeacherFeedbackAdmin(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $tab = $request->query('tab');
        $section = $request->query('section', 'view');
        if (!$tab) {
            $routeName = optional($request->route())->getName();
            $tab = $routeName === 'admin.incidents' ? 'incidents' : 'suggestions';
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $teacherName = $request->query('teacher_name');

        $feedbackQuery = \App\Models\TeacherFeedback::where('schoolID', $schoolID);

        if ($dateFrom) {
            $feedbackQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $feedbackQuery->whereDate('created_at', '<=', $dateTo);
        }

        $teacherMap = \App\Models\Teacher::where('schoolID', $schoolID)
            ->get()
            ->keyBy('id');

        if ($teacherName) {
            $teacherIds = $teacherMap
                ->filter(function ($teacher) use ($teacherName) {
                    $fullName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''));
                    return stripos($fullName, $teacherName) !== false;
                })
                ->keys()
                ->toArray();

            if (count($teacherIds) === 0) {
                $feedback = collect();
            } else {
                $feedbackQuery->whereIn('teacherID', $teacherIds);
                $feedback = $feedbackQuery->orderBy('created_at', 'desc')->get();
            }
        } else {
            $feedback = $feedbackQuery->orderBy('created_at', 'desc')->get();
        }

        $feedback = $feedback->map(function ($item) use ($teacherMap) {
            $teacher = $teacherMap->get($item->teacherID);
            $name = $teacher ? trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')) : 'Teacher';
            $item->teacher_name = $name;
            $item->person_name = $name;
            return $item;
        });

        $suggestions = $feedback->where('type', 'suggestion')->values();
        $incidents = $feedback->where('type', 'incident')->values();

        $suggestionStats = [
            'total' => $suggestions->count(),
            'pending' => $suggestions->where('status', 'pending')->count(),
            'approved' => $suggestions->where('status', 'approved')->count(),
            'rejected' => $suggestions->where('status', 'rejected')->count(),
        ];

        $incidentStats = [
            'total' => $incidents->count(),
            'pending' => $incidents->where('status', 'pending')->count(),
            'approved' => $incidents->where('status', 'approved')->count(),
            'rejected' => $incidents->where('status', 'rejected')->count(),
        ];

        $readType = $tab === 'incidents' ? 'incident' : 'suggestion';
        \App\Models\TeacherFeedback::where('schoolID', $schoolID)
            ->where('type', $readType)
            ->update(['is_read_by_admin' => true]);

        return view('Admin.manage_teacher_feedback', [
            'activeTab' => $tab,
            'activeSection' => $section,
            'suggestions' => $suggestions,
            'incidents' => $incidents,
            'suggestionStats' => $suggestionStats,
            'incidentStats' => $incidentStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'teacherName' => $teacherName,
            'adminFeedbackContext' => 'teacher',
        ]);
    }

    public function manageStaffFeedbackAdmin(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $tab = $request->query('tab');
        $section = $request->query('section', 'view');
        if (!$tab) {
            $routeName = optional($request->route())->getName();
            $tab = $routeName === 'admin.staff.incidents' ? 'incidents' : 'suggestions';
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $staffName = $request->query('staff_name');

        $feedbackQuery = \App\Models\StaffFeedback::where('schoolID', $schoolID);

        if ($dateFrom) {
            $feedbackQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $feedbackQuery->whereDate('created_at', '<=', $dateTo);
        }

        $staffMap = \App\Models\OtherStaff::where('schoolID', $schoolID)
            ->get()
            ->keyBy('id');

        if ($staffName) {
            $staffIds = $staffMap
                ->filter(function ($staff) use ($staffName) {
                    $fullName = trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''));
                    return stripos($fullName, $staffName) !== false;
                })
                ->keys()
                ->toArray();

            if (count($staffIds) === 0) {
                $feedback = collect();
            } else {
                $feedbackQuery->whereIn('staffID', $staffIds);
                $feedback = $feedbackQuery->orderBy('created_at', 'desc')->get();
            }
        } else {
            $feedback = $feedbackQuery->orderBy('created_at', 'desc')->get();
        }

        $feedback = $feedback->map(function ($item) use ($staffMap) {
            $staff = $staffMap->get($item->staffID);
            $name = $staff ? trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')) : 'Staff';
            $item->person_name = $name;
            return $item;
        });

        $suggestions = $feedback->where('type', 'suggestion')->values();
        $incidents = $feedback->where('type', 'incident')->values();

        $suggestionStats = [
            'total' => $suggestions->count(),
            'pending' => $suggestions->where('status', 'pending')->count(),
            'approved' => $suggestions->where('status', 'approved')->count(),
            'rejected' => $suggestions->where('status', 'rejected')->count(),
        ];

        $incidentStats = [
            'total' => $incidents->count(),
            'pending' => $incidents->where('status', 'pending')->count(),
            'approved' => $incidents->where('status', 'approved')->count(),
            'rejected' => $incidents->where('status', 'rejected')->count(),
        ];

        $readType = $tab === 'incidents' ? 'incident' : 'suggestion';
        \App\Models\StaffFeedback::where('schoolID', $schoolID)
            ->where('type', $readType)
            ->update(['is_read_by_admin' => true]);

        return view('Admin.manage_teacher_feedback', [
            'activeTab' => $tab,
            'activeSection' => $section,
            'suggestions' => $suggestions,
            'incidents' => $incidents,
            'suggestionStats' => $suggestionStats,
            'incidentStats' => $incidentStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'staffName' => $staffName,
            'adminFeedbackContext' => 'staff',
        ]);
    }

    public function approveStaffFeedback(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'feedbackID' => 'required|integer',
            'admin_response' => 'nullable|string',
            'response_due_date' => 'nullable|date',
        ]);

        $feedback = \App\Models\StaffFeedback::where('feedbackID', $validated['feedbackID'])
            ->where('schoolID', $schoolID)
            ->first();

        if (!$feedback) {
            return response()->json(['success' => false, 'message' => 'Feedback not found'], 404);
        }

        $feedback->status = 'approved';
        $feedback->admin_response = $validated['admin_response'] ?? null;
        $feedback->response_due_date = $validated['response_due_date'] ?? null;
        $feedback->responded_at = now();
        $feedback->is_read_by_staff = false;
        $feedback->save();

        return response()->json(['success' => true, 'message' => 'Feedback approved successfully.']);
    }

    public function rejectStaffFeedback(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'feedbackID' => 'required|integer',
            'admin_response' => 'nullable|string',
        ]);

        $feedback = \App\Models\StaffFeedback::where('feedbackID', $validated['feedbackID'])
            ->where('schoolID', $schoolID)
            ->first();

        if (!$feedback) {
            return response()->json(['success' => false, 'message' => 'Feedback not found'], 404);
        }

        $feedback->status = 'rejected';
        $feedback->admin_response = $validated['admin_response'] ?? null;
        $feedback->responded_at = now();
        $feedback->is_read_by_staff = false;
        $feedback->save();

        return response()->json(['success' => true, 'message' => 'Feedback rejected successfully.']);
    }

    public function performanceDashboard()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teachers = \App\Models\Teacher::where('schoolID', $schoolID)->orderBy('first_name')->get();
        $subjects = \App\Models\SchoolSubject::where('schoolID', $schoolID)->orderBy('subject_name')->get();
        $exams = \App\Models\Examination::where('schoolID', $schoolID)->orderBy('created_at', 'desc')->limit(50)->get();
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->orderBy('class_name')->get();

        return view('Admin.performance', compact('teachers', 'subjects', 'exams', 'classes'));
    }

    private function calculatePassFailCounts($results)
    {
        $pass = 0;
        $fail = 0;
        foreach ($results as $result) {
            $remark = strtolower(trim($result->remark ?? ''));
            if ($remark === 'fail' || $remark === 'failed') {
                $fail++;
                continue;
            }
            if ($result->marks !== null) {
                if ($result->marks >= 30) {
                    $pass++;
                } else {
                    $fail++;
                }
                continue;
            }
            $grade = strtoupper(trim($result->grade ?? ''));
            if (in_array($grade, ['A', 'B', 'C', 'D'])) {
                $pass++;
            } else {
                $fail++;
            }
        }

        $total = $pass + $fail;
        $passRate = $total > 0 ? round(($pass / $total) * 100, 1) : 0;
        $failRate = $total > 0 ? round(($fail / $total) * 100, 1) : 0;

        $comment = 'Needs improvement.';
        if ($passRate >= 75) {
            $comment = 'Excellent performance. Keep going!';
        } elseif ($passRate >= 50) {
            $comment = 'Good effort. Keep improving.';
        } elseif ($passRate >= 30) {
            $comment = 'Fair performance. Work harder.';
        }

        return [
            'total' => $total,
            'pass_count' => $pass,
            'fail_count' => $fail,
            'pass_rate' => $passRate,
            'fail_rate' => $failRate,
            'comment' => $comment,
        ];
    }

    public function teacherTermPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|integer',
            'subject_id' => 'required',
            'term' => 'required|string',
            'year' => 'required|integer',
        ]);

        $subjectId = $validated['subject_id'];
        $resultsQuery = \App\Models\Result::query()
            ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('examinations', 'results.examID', '=', 'examinations.examID')
            ->where('examinations.schoolID', $schoolID)
            ->where('examinations.term', $validated['term'])
            ->where('examinations.year', $validated['year'])
            ->where('class_subjects.teacherID', $validated['teacher_id']);
        if ($subjectId !== 'all') {
            $resultsQuery->where('class_subjects.subjectID', $subjectId);
        }
        $results = $resultsQuery->select('results.marks', 'results.remark', 'results.grade')->get();

        return response()->json([
            'success' => true,
            'data' => $this->calculatePassFailCounts($results),
        ]);
    }

    public function teacherExamPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|integer',
            'subject_id' => 'required',
            'exam_id' => 'required|integer',
        ]);

        $subjectId = $validated['subject_id'];
        $resultsQuery = \App\Models\Result::query()
            ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('examinations', 'results.examID', '=', 'examinations.examID')
            ->where('examinations.schoolID', $schoolID)
            ->where('examinations.examID', $validated['exam_id'])
            ->where('class_subjects.teacherID', $validated['teacher_id']);
        if ($subjectId !== 'all') {
            $resultsQuery->where('class_subjects.subjectID', $subjectId);
        }
        $results = $resultsQuery->select('results.marks', 'results.remark', 'results.grade')->get();

        return response()->json([
            'success' => true,
            'data' => $this->calculatePassFailCounts($results),
        ]);
    }

    public function teacherYearPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|integer',
            'subject_id' => 'required',
            'year' => 'required|integer',
        ]);

        $subjectId = $validated['subject_id'];
        $resultsQuery = \App\Models\Result::query()
            ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('examinations', 'results.examID', '=', 'examinations.examID')
            ->where('examinations.schoolID', $schoolID)
            ->where('examinations.year', $validated['year'])
            ->where('class_subjects.teacherID', $validated['teacher_id']);
        if ($subjectId !== 'all') {
            $resultsQuery->where('class_subjects.subjectID', $subjectId);
        }
        $results = $resultsQuery->select('results.marks', 'results.remark', 'results.grade')->get();

        return response()->json([
            'success' => true,
            'data' => $this->calculatePassFailCounts($results),
        ]);
    }

    public function teacherSubjectsForPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|integer',
        ]);

        $subjects = \App\Models\ClassSubject::with('subject')
            ->where('teacherID', $validated['teacher_id'])
            ->where('status', 'Active')
            ->get()
            ->map(function ($cs) {
                return [
                    'id' => $cs->subjectID,
                    'name' => $cs->subject->subject_name ?? 'N/A',
                ];
            })
            ->unique('id')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }

    public function classSubjectsForPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
        ]);

        $subjects = \App\Models\ClassSubject::with('subject')
            ->where('classID', $validated['class_id'])
            ->where('status', 'Active')
            ->get()
            ->map(function ($cs) {
                return [
                    'id' => $cs->subjectID,
                    'name' => $cs->subject->subject_name ?? 'N/A',
                ];
            })
            ->unique('id')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }

    public function classSubclassesForPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
        ]);

        $subclasses = \App\Models\Subclass::where('classID', $validated['class_id'])
            ->orderBy('subclass_name')
            ->get()
            ->map(function ($subclass) {
                return [
                    'id' => $subclass->subclassID,
                    'name' => $subclass->display_name ?? $subclass->subclass_name,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $subclasses,
        ]);
    }

    public function classStudentsForPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'subclass_id' => 'nullable|integer',
        ]);

        $studentsQuery = \App\Models\Student::query()
            ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('classes.schoolID', $schoolID)
            ->where('classes.classID', $validated['class_id'])
            ->where('students.status', 'Active');

        if (!empty($validated['subclass_id'])) {
            $studentsQuery->where('students.subclassID', $validated['subclass_id']);
        }

        $students = $studentsQuery
            ->select('students.studentID', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_number')
            ->orderBy('students.first_name')
            ->get()
            ->map(function ($student) {
                $name = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $label = $name;
                if (!empty($student->admission_number)) {
                    $label .= ' (' . $student->admission_number . ')';
                }
                return [
                    'id' => $student->studentID,
                    'name' => $label,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    public function studentTermPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'subclass_id' => 'nullable|integer',
            'subject_id' => 'required',
            'student_id' => 'nullable|integer',
            'term' => 'required|string',
            'year' => 'required|integer',
        ]);

        $subjectId = $validated['subject_id'];
        $examIds = \App\Models\Examination::where('schoolID', $schoolID)
            ->where('term', $validated['term'])
            ->where('year', $validated['year'])
            ->where('approval_status', 'Approved')
            ->pluck('examID');

        $resultsQuery = \App\Models\Result::query()
            ->join('students', 'results.studentID', '=', 'students.studentID')
            ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->where('classes.schoolID', $schoolID)
            ->whereIn('results.examID', $examIds)
            ->where('results.status', 'allowed')
            ->whereNotNull('results.marks')
            ->where('classes.classID', $validated['class_id']);

        if (!empty($validated['subclass_id'])) {
            $resultsQuery->where('subclasses.subclassID', $validated['subclass_id']);
        }
        if (!empty($validated['student_id'])) {
            $resultsQuery->where('results.studentID', $validated['student_id']);
        }
        if ($subjectId !== 'all') {
            $resultsQuery->where('class_subjects.subjectID', $subjectId);
        }

        $results = $resultsQuery->select('results.marks', 'results.remark', 'results.grade')->get();

        return response()->json([
            'success' => true,
            'data' => $this->calculatePassFailCounts($results),
        ]);
    }

    public function studentExamPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'subclass_id' => 'nullable|integer',
            'subject_id' => 'required',
            'student_id' => 'nullable|integer',
            'exam_id' => 'required|integer',
        ]);

        $subjectId = $validated['subject_id'];
        $examApproved = \App\Models\Examination::where('schoolID', $schoolID)
            ->where('examID', $validated['exam_id'])
            ->where('approval_status', 'Approved')
            ->exists();
        if (!$examApproved) {
            return response()->json([
                'success' => true,
                'data' => $this->calculatePassFailCounts(collect()),
            ]);
        }

        $resultsQuery = \App\Models\Result::query()
            ->join('students', 'results.studentID', '=', 'students.studentID')
            ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->where('classes.schoolID', $schoolID)
            ->where('results.examID', $validated['exam_id'])
            ->where('results.status', 'allowed')
            ->whereNotNull('results.marks')
            ->where('classes.classID', $validated['class_id']);

        if (!empty($validated['subclass_id'])) {
            $resultsQuery->where('subclasses.subclassID', $validated['subclass_id']);
        }
        if (!empty($validated['student_id'])) {
            $resultsQuery->where('results.studentID', $validated['student_id']);
        }
        if ($subjectId !== 'all') {
            $resultsQuery->where('class_subjects.subjectID', $subjectId);
        }

        $results = $resultsQuery->select('results.marks', 'results.remark', 'results.grade')->get();

        return response()->json([
            'success' => true,
            'data' => $this->calculatePassFailCounts($results),
        ]);
    }

    public function studentYearPerformance(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'subclass_id' => 'nullable|integer',
            'subject_id' => 'required',
            'student_id' => 'nullable|integer',
            'year' => 'required|integer',
        ]);

        $subjectId = $validated['subject_id'];
        $examIds = \App\Models\Examination::where('schoolID', $schoolID)
            ->where('year', $validated['year'])
            ->where('approval_status', 'Approved')
            ->pluck('examID');

        $resultsQuery = \App\Models\Result::query()
            ->join('students', 'results.studentID', '=', 'students.studentID')
            ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->where('classes.schoolID', $schoolID)
            ->whereIn('results.examID', $examIds)
            ->where('results.status', 'allowed')
            ->whereNotNull('results.marks')
            ->where('classes.classID', $validated['class_id']);

        if (!empty($validated['subclass_id'])) {
            $resultsQuery->where('subclasses.subclassID', $validated['subclass_id']);
        }
        if (!empty($validated['student_id'])) {
            $resultsQuery->where('results.studentID', $validated['student_id']);
        }
        if ($subjectId !== 'all') {
            $resultsQuery->where('class_subjects.subjectID', $subjectId);
        }

        $results = $resultsQuery->select('results.marks', 'results.remark', 'results.grade')->get();

        return response()->json([
            'success' => true,
            'data' => $this->calculatePassFailCounts($results),
        ]);
    }

    public function approveTeacherFeedback(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'feedbackID' => 'required|exists:teacher_feedbacks,feedbackID',
            'admin_response' => 'required|string',
            'response_due_date' => 'required|date',
        ]);

        $feedback = \App\Models\TeacherFeedback::where('feedbackID', $validated['feedbackID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $feedback->update([
            'status' => 'approved',
            'admin_response' => $validated['admin_response'],
            'response_due_date' => $validated['response_due_date'],
            'responded_at' => now(),
            'is_read_by_teacher' => false,
        ]);

        $teacher = \App\Models\Teacher::where('id', $feedback->teacherID)->first();
        if ($teacher && $teacher->phone_number) {
            $typeLabel = $feedback->type === 'incident' ? 'Incident' : 'Suggestion';
            $message = "{$typeLabel} approved. Planned date: {$validated['response_due_date']}. {$validated['admin_response']}";
            $smsService = new \App\Services\SmsService();
            $smsService->sendSms($teacher->phone_number, $message);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Feedback approved successfully.']);
        }
        return redirect()->back()->with('success', 'Feedback approved successfully.');
    }

    public function rejectTeacherFeedback(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'feedbackID' => 'required|exists:teacher_feedbacks,feedbackID',
            'admin_response' => 'required|string',
        ]);

        $feedback = \App\Models\TeacherFeedback::where('feedbackID', $validated['feedbackID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $feedback->update([
            'status' => 'rejected',
            'admin_response' => $validated['admin_response'],
            'responded_at' => now(),
            'is_read_by_teacher' => false,
        ]);

        $teacher = \App\Models\Teacher::where('id', $feedback->teacherID)->first();
        if ($teacher && $teacher->phone_number) {
            $typeLabel = $feedback->type === 'incident' ? 'Incident' : 'Suggestion';
            $message = "{$typeLabel} rejected. Reason: {$validated['admin_response']}";
            $smsService = new \App\Services\SmsService();
            $smsService->sendSms($teacher->phone_number, $message);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Feedback rejected successfully.']);
        }
        return redirect()->back()->with('success', 'Feedback rejected successfully.');
    }

    public function managePermissionsAdmin(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $tab = $request->get('tab', 'teacher');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = trim($request->get('search', ''));

        $query = PermissionRequest::with(['teacher', 'student', 'parent', 'staff'])
            ->where('schoolID', $schoolID)
            ->where('requester_type', $tab === 'student' ? 'student' : ($tab === 'staff' ? 'staff' : 'teacher'));

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()]);
        }

        if ($search !== '') {
            if ($tab === 'student') {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            } elseif ($tab === 'staff') {
                $query->whereHas('staff', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%");
                });
            } else {
                $query->whereHas('teacher', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%");
                });
            }
        }

        $permissions = $query->orderBy('created_at', 'desc')->get();

        $pendingCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('status', 'pending')
            ->where('is_read_by_admin', false)
            ->count();
        $approvedCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('status', 'approved')
            ->where('is_read_by_admin', false)
            ->count();
        $rejectedCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('status', 'rejected')
            ->where('is_read_by_admin', false)
            ->count();

        $tabCounts = [
            'teacher' => PermissionRequest::where('schoolID', $schoolID)
                ->where('requester_type', 'teacher')
                ->where('status', 'pending')
                ->where('is_read_by_admin', false)
                ->count(),
            'student' => PermissionRequest::where('schoolID', $schoolID)
                ->where('requester_type', 'student')
                ->where('status', 'pending')
                ->where('is_read_by_admin', false)
                ->count(),
            'staff' => PermissionRequest::where('schoolID', $schoolID)
                ->where('requester_type', 'staff')
                ->where('status', 'pending')
                ->where('is_read_by_admin', false)
                ->count(),
        ];

        PermissionRequest::where('schoolID', $schoolID)->update(['is_read_by_admin' => true]);

        if ($request->ajax() || $request->expectsJson()) {
            $data = $permissions->map(function ($permission) use ($tab) {
                $requesterName = 'N/A';
                if ($tab === 'student' && $permission->student) {
                    $requesterName = trim(($permission->student->first_name ?? '') . ' ' . ($permission->student->last_name ?? ''));
                }
                if ($tab === 'staff' && $permission->staff) {
                    $requesterName = trim(($permission->staff->first_name ?? '') . ' ' . ($permission->staff->last_name ?? ''));
                }
                if ($tab === 'teacher' && $permission->teacher) {
                    $requesterName = trim(($permission->teacher->first_name ?? '') . ' ' . ($permission->teacher->last_name ?? ''));
                }
                return [
                    'permissionID' => $permission->permissionID,
                    'requesterName' => $requesterName,
                    'timeMode' => $permission->time_mode,
                    'daysCount' => $permission->days_count,
                    'startDate' => $permission->start_date ? $permission->start_date->format('Y-m-d') : null,
                    'endDate' => $permission->end_date ? $permission->end_date->format('Y-m-d') : null,
                    'startTime' => $permission->start_time,
                    'endTime' => $permission->end_time,
                    'reasonType' => $permission->reason_type,
                    'status' => $permission->status,
                    'attachment' => $permission->attachment_path ? route('admin.permissions.attachment', $permission->permissionID) : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pendingCount' => $pendingCount,
                'approvedCount' => $approvedCount,
                'rejectedCount' => $rejectedCount,
            ]);
        }

        return view('Admin.manage_permissions', [
            'activeTab' => $tab,
            'permissions' => $permissions,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'tabCounts' => $tabCounts,
        ]);
    }

    public function viewPermissionAttachment($permissionID)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin' || !$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $permission = PermissionRequest::where('schoolID', $schoolID)
            ->where('permissionID', $permissionID)
            ->firstOrFail();

        if (!$permission->attachment_path) {
            abort(404);
        }

        $path = $permission->attachment_path;
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    public function approvePermission(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'permissionID' => 'required|exists:permission_requests,permissionID',
            'admin_response' => 'required|string',
            'admin_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $permission = PermissionRequest::where('schoolID', $schoolID)
            ->where('permissionID', $validated['permissionID'])
            ->firstOrFail();

        $attachmentPath = $permission->admin_attachment_path;
        if ($request->hasFile('admin_attachment')) {
            $attachmentPath = $request->file('admin_attachment')->store('permission_admin_attachments', 'public');
        }

        $updateData = [
            'status' => 'approved',
            'admin_response' => $validated['admin_response'],
            'reviewed_at' => now(),
            'is_read_by_requester' => false,
        ];
        if (Schema::hasColumn('permission_requests', 'admin_attachment_path')) {
            $updateData['admin_attachment_path'] = $attachmentPath;
        }
        $permission->update($updateData);

        $smsService = new SmsService();
        if ($permission->requester_type === 'teacher') {
            $teacher = Teacher::where('id', $permission->teacherID)->first();
            if ($teacher && $teacher->phone_number) {
                $smsService->sendSms($teacher->phone_number, 'Your permission request has been approved.');
            }
        } else {
            $parent = ParentModel::where('parentID', $permission->parentID)->first();
            if ($parent && $parent->phone) {
                $smsService->sendSms($parent->phone, 'Your permission request has been approved.');
            }
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Permission approved successfully.']);
        }
        return redirect()->back()->with('success', 'Permission approved successfully.');
    }

    public function rejectPermission(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'permissionID' => 'required|exists:permission_requests,permissionID',
            'admin_response' => 'required|string',
        ]);

        $permission = PermissionRequest::where('schoolID', $schoolID)
            ->where('permissionID', $validated['permissionID'])
            ->firstOrFail();

        $permission->update([
            'status' => 'rejected',
            'admin_response' => $validated['admin_response'],
            'reviewed_at' => now(),
            'is_read_by_requester' => false,
        ]);

        $smsService = new SmsService();
        if ($permission->requester_type === 'teacher') {
            $teacher = Teacher::where('id', $permission->teacherID)->first();
            if ($teacher && $teacher->phone_number) {
                $smsService->sendSms($teacher->phone_number, 'Your permission request has been rejected.');
            }
        } else {
            $parent = ParentModel::where('parentID', $permission->parentID)->first();
            if ($parent && $parent->phone) {
                $smsService->sendSms($parent->phone, 'Your permission request has been rejected.');
            }
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Permission rejected successfully.']);
        }
        return redirect()->back()->with('success', 'Permission rejected successfully.');
    }

    public function manageSchoolVisitors()
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');
        $schoolName = School::where('schoolID', $schoolID)->value('school_name') ?? 'School';
        Session::put('visitors_last_seen', now()->toDateTimeString());

        return view('Admin.manage_school_visitors', [
            'schoolName' => $schoolName,
        ]);
    }

    public function storeSchoolVisitors(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$user || $user !== 'Admin' || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $names = $request->input('name', []);
        $contacts = $request->input('contact', []);
        $occupations = $request->input('occupation', []);
        $reasons = $request->input('reason', []);
        $signatures = $request->input('signature', []);

        $today = Carbon::today()->toDateString();
        $rows = [];

        foreach ($names as $index => $name) {
            $trimmed = trim((string) $name);
            if ($trimmed === '') {
                continue;
            }
            $rows[] = [
                'schoolID' => $schoolID,
                'visit_date' => $today,
                'name' => $trimmed,
                'contact' => isset($contacts[$index]) ? trim((string) $contacts[$index]) : null,
                'occupation' => isset($occupations[$index]) ? trim((string) $occupations[$index]) : null,
                'reason' => isset($reasons[$index]) ? trim((string) $reasons[$index]) : null,
                'signature' => isset($signatures[$index]) ? $signatures[$index] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($rows)) {
            return response()->json(['success' => false, 'message' => 'Please enter at least one visitor.'], 422);
        }

        DB::table('school_visitors')->insert($rows);

        return response()->json(['success' => true, 'message' => 'Visitors recorded successfully.']);
    }

    public function todaySchoolVisitors()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$user || $user !== 'Admin' || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $today = Carbon::today()->toDateString();
        $visitors = SchoolVisitor::where('schoolID', $schoolID)
            ->whereDate('visit_date', $today)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $visitors->map(function ($visitor) {
                return [
                    'visitorID' => $visitor->visitorID,
                    'visitDate' => $visitor->visit_date ? $visitor->visit_date->format('Y-m-d') : null,
                    'name' => $visitor->name,
                    'contact' => $visitor->contact,
                    'occupation' => $visitor->occupation,
                    'reason' => $visitor->reason,
                    'signature' => $visitor->signature,
                ];
            }),
        ]);
    }

    public function listSchoolVisitors(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$user || $user !== 'Admin' || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = SchoolVisitor::where('schoolID', $schoolID);
        if ($dateFrom && $dateTo) {
            $query->whereBetween('visit_date', [$dateFrom, $dateTo]);
        }

        $visitors = $query->orderBy('visit_date', 'desc')->orderBy('created_at', 'desc')->get();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $usedCount = SchoolVisitorSmsLog::where('schoolID', $schoolID)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('recipient_count');

        return response()->json([
            'success' => true,
            'data' => $visitors->map(function ($visitor) {
                return [
                    'visitorID' => $visitor->visitorID,
                    'visitDate' => $visitor->visit_date ? $visitor->visit_date->format('Y-m-d') : null,
                    'name' => $visitor->name,
                    'contact' => $visitor->contact,
                    'occupation' => $visitor->occupation,
                    'reason' => $visitor->reason,
                    'signature' => $visitor->signature,
                ];
            }),
            'used' => $usedCount,
        ]);
    }

    public function updateSchoolVisitor(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$user || $user !== 'Admin' || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'visitorID' => 'required|integer',
            'name' => 'required|string',
            'contact' => 'nullable|string',
            'occupation' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $visitor = SchoolVisitor::where('schoolID', $schoolID)
            ->where('visitorID', $validated['visitorID'])
            ->firstOrFail();

        $visitor->update([
            'name' => $validated['name'],
            'contact' => $validated['contact'],
            'occupation' => $validated['occupation'],
            'reason' => $validated['reason'],
        ]);

        return response()->json(['success' => true, 'message' => 'Visitor updated successfully.']);
    }

    public function deleteSchoolVisitor(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$user || $user !== 'Admin' || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'visitorID' => 'required|integer',
        ]);

        $visitor = SchoolVisitor::where('schoolID', $schoolID)
            ->where('visitorID', $validated['visitorID'])
            ->firstOrFail();
        $visitor->delete();

        return response()->json(['success' => true, 'message' => 'Visitor deleted successfully.']);
    }

    public function sendVisitorSms(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$user || $user !== 'Admin' || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'visitor_ids' => 'required|array|min:1',
            'visitor_ids.*' => 'integer',
        ]);

        $schoolName = School::where('schoolID', $schoolID)->value('school_name') ?? 'School';
        $prefix = trim($schoolName) !== '' ? trim($schoolName) . ': ' : '';
        $message = $validated['message'];
        if (strpos($message, $prefix) !== 0) {
            $message = $prefix . $message;
        }
        if (mb_strlen($message) > 163) {
            return response()->json(['success' => false, 'message' => 'SMS message must be 163 characters or less.'], 422);
        }
        if (!preg_match('/^[\x00-\x7F]+$/', $message)) {
            return response()->json(['success' => false, 'message' => 'SMS must not contain emojis or non-ASCII characters.'], 422);
        }

        $visitorIds = $validated['visitor_ids'];
        $visitors = SchoolVisitor::where('schoolID', $schoolID)
            ->whereIn('visitorID', $visitorIds)
            ->get();

        if ($visitors->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid visitors selected.'], 422);
        }

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $usedCount = SchoolVisitorSmsLog::where('schoolID', $schoolID)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('recipient_count');

        $recipientCount = $visitors->count();
        if ($usedCount + $recipientCount > 200) {
            return response()->json(['success' => false, 'message' => 'Monthly SMS limit (200) exceeded.'], 422);
        }

        set_time_limit(60);
        $smsService = new SmsService();
        $sent = 0;
        $skipped = 0;

        foreach ($visitors as $visitor) {
            $phone = $visitor->contact;
            if (!$phone || !preg_match('/\d{7,}/', $phone)) {
                $skipped++;
                continue;
            }
            $result = $smsService->sendSms($phone, $message);
            if ($result['success']) {
                $sent++;
            } else {
                $skipped++;
            }
        }

        SchoolVisitorSmsLog::create([
            'schoolID' => $schoolID,
            'message' => $message,
            'recipient_count' => $sent,
            'recipient_ids' => $visitors->pluck('visitorID')->values()->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "SMS sent: {$sent}. Skipped: {$skipped}.",
            'used' => $usedCount + $sent,
        ]);
    }

    public function storeDamagedLostRecord(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'resourceID' => 'required|exists:school_resources,resourceID',
            'record_date' => 'required|date',
            'record_type' => 'required|in:damaged,lost,used_up',
            'quantity' => 'nullable|integer|min:0',
            'description' => 'required|string',
        ]);

        $resource = \App\Models\SchoolResource::where('resourceID', $validated['resourceID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $quantity = $requiresQuantity ? (int) ($validated['quantity'] ?? 0) : null;

        if ($requiresQuantity && $quantity > ($resource->quantity ?? 0)) {
            return redirect()->route('manage_damaged_lost_items')
                ->with('error', 'Recorded quantity exceeds available stock.');
        }

        \DB::transaction(function () use ($schoolID, $resource, $validated, $quantity, $requiresQuantity) {
            \App\Models\DamagedLostRecord::create([
                'schoolID' => $schoolID,
                'resourceID' => $resource->resourceID,
                'record_date' => $validated['record_date'],
                'record_type' => $validated['record_type'],
                'quantity' => $quantity,
                'description' => $validated['description'],
            ]);

            if ($requiresQuantity) {
                $resource->update([
                    'quantity' => max(0, ($resource->quantity ?? 0) - $quantity),
                ]);
            }
        });

        return redirect()->route('manage_damaged_lost_items')->with('success', 'Record saved successfully.');
    }

    public function updateDamagedLostRecord(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'damaged_lostID' => 'required|exists:damaged_lost_records,damaged_lostID',
            'record_date' => 'required|date',
            'record_type' => 'required|in:damaged,lost,used_up',
            'quantity' => 'nullable|integer|min:0',
            'description' => 'required|string',
        ]);

        $record = \App\Models\DamagedLostRecord::where('damaged_lostID', $validated['damaged_lostID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $resource = \App\Models\SchoolResource::where('resourceID', $record->resourceID)
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $newQty = $requiresQuantity ? (int) ($validated['quantity'] ?? 0) : null;

        if ($requiresQuantity) {
            $available = ($resource->quantity ?? 0) + ($record->quantity ?? 0);
            if ($newQty > $available) {
                return redirect()->route('manage_damaged_lost_items')
                    ->with('error', 'Recorded quantity exceeds available stock.');
            }
        }

        \DB::transaction(function () use ($record, $resource, $validated, $requiresQuantity, $newQty) {
            if ($requiresQuantity) {
                $restored = ($resource->quantity ?? 0) + ($record->quantity ?? 0);
                $resource->update([
                    'quantity' => max(0, $restored - $newQty),
                ]);
            }

            $record->update([
                'record_date' => $validated['record_date'],
                'record_type' => $validated['record_type'],
                'quantity' => $newQty,
                'description' => $validated['description'],
            ]);
        });

        return redirect()->route('manage_damaged_lost_items')->with('success', 'Record updated successfully.');
    }

    public function deleteDamagedLostRecord(Request $request)
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'damaged_lostID' => 'required|exists:damaged_lost_records,damaged_lostID',
        ]);

        $record = \App\Models\DamagedLostRecord::where('damaged_lostID', $validated['damaged_lostID'])
            ->where('schoolID', $schoolID)
            ->firstOrFail();

        $resource = \App\Models\SchoolResource::where('resourceID', $record->resourceID)
            ->where('schoolID', $schoolID)
            ->first();

        \DB::transaction(function () use ($record, $resource) {
            if ($resource && $resource->requires_quantity) {
                $resource->update([
                    'quantity' => ($resource->quantity ?? 0) + ($record->quantity ?? 0),
                ]);
            }
            $record->delete();
        });

        return redirect()->route('manage_damaged_lost_items')->with('success', 'Record deleted successfully.');
    }

    public function resourceReport()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        $resourcePermissions = ['resources_create', 'resources_update', 'resources_delete', 'resources_read_only'];
        if (!$user || ($user !== 'Admin' && ! $this->staffHasAnyPermission($resourcePermissions))) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        return view('Admin.resource_report', compact('schoolID'));
    }

    public function usageReport()
    {
        $user = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        $resourcePermissions = ['resources_create', 'resources_update', 'resources_delete', 'resources_read_only'];
        if (!$user || ($user !== 'Admin' && ! $this->staffHasAnyPermission($resourcePermissions))) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        return view('Admin.usage_report', compact('schoolID'));
    }

    /**
     * Get lesson plans sent to admin by subject and class
     */
    public function getLessonPlansForAdmin(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $subjectID = $request->input('subjectID');
            $classID = $request->input('classID');

            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            if (!$subjectID || !$classID) {
                return response()->json(['success' => false, 'error' => 'Please select both subject and class']);
            }

            // Get subject name
            $subject = DB::table('school_subjects')
                ->where('subjectID', $subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json(['success' => false, 'error' => 'Subject not found']);
            }

            // Get class name
            $subclass = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('subclasses.subclassID', $classID)
                ->where('classes.schoolID', $schoolID)
                ->select('subclasses.subclass_name', 'classes.class_name')
                ->first();

            if (!$subclass) {
                return response()->json(['success' => false, 'error' => 'Class not found']);
            }

            // Get session timetables for this subject and class
            $sessionTimetableIDs = DB::table('class_session_timetables')
                ->join('class_subjects', function($join) use ($subjectID, $classID) {
                    $join->on('class_session_timetables.class_subjectID', '=', 'class_subjects.class_subjectID')
                         ->where('class_subjects.subjectID', '=', $subjectID)
                         ->where('class_subjects.subclassID', '=', $classID);
                })
                ->pluck('class_session_timetables.session_timetableID')
                ->toArray();

            if (empty($sessionTimetableIDs)) {
                return response()->json([
                    'success' => true,
                    'lesson_plans' => [],
                    'subject_name' => $subject->subject_name,
                    'class_name' => $subclass->class_name . ' - ' . $subclass->subclass_name
                ]);
            }

            // Get lesson plans sent to admin
            $lessonPlans = LessonPlan::whereIn('session_timetableID', $sessionTimetableIDs)
                ->where('schoolID', $schoolID)
                ->where('sent_to_admin', true)
                ->with('teacher')
                ->orderBy('lesson_date', 'desc')
                ->get();

            // Format data
            $formattedPlans = $lessonPlans->map(function($plan) {
                $teacherName = 'N/A';
                if ($plan->teacher) {
                    $teacherName = trim(($plan->teacher->first_name ?? '') . ' ' . ($plan->teacher->last_name ?? ''));
                }
                
                return [
                    'lesson_planID' => $plan->lesson_planID,
                    'lesson_date' => $plan->lesson_date,
                    'subject' => $plan->subject,
                    'class_name' => $plan->class_name,
                    'teacher_name' => $teacherName,
                    'lesson_time_start' => $plan->lesson_time_start,
                    'lesson_time_end' => $plan->lesson_time_end,
                    'sent_at' => $plan->sent_at,
                    'supervisor_signature' => $plan->supervisor_signature,
                ];
            });

            return response()->json([
                'success' => true,
                'lesson_plans' => $formattedPlans,
                'subject_name' => $subject->subject_name,
                'class_name' => $subclass->class_name . ' - ' . $subclass->subclass_name
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting lesson plans for admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get single lesson plan for admin
     */
    public function getLessonPlanForAdmin(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $lessonPlanID = $request->input('lesson_planID');

            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            $lessonPlan = LessonPlan::where('lesson_planID', $lessonPlanID)
                ->where('schoolID', $schoolID)
                ->where('sent_to_admin', true)
                ->with('teacher')
                ->first();

            if (!$lessonPlan) {
                return response()->json(['success' => false, 'error' => 'Lesson plan not found']);
            }

            // Get teacher name
            $teacherName = 'N/A';
            if ($lessonPlan->teacher) {
                $teacherName = trim(($lessonPlan->teacher->first_name ?? '') . ' ' . ($lessonPlan->teacher->last_name ?? ''));
            }

            $data = $lessonPlan->toArray();
            $data['teacher_name'] = $teacherName;

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting lesson plan for admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Sign lesson plan as supervisor
     */
    public function signLessonPlan(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $lessonPlanID = $request->input('lesson_planID');
            $supervisorSignature = $request->input('supervisor_signature');

            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            if (!$supervisorSignature) {
                return response()->json(['success' => false, 'error' => 'Please provide supervisor signature']);
            }

            $lessonPlan = LessonPlan::where('lesson_planID', $lessonPlanID)
                ->where('schoolID', $schoolID)
                ->where('sent_to_admin', true)
                ->first();

            if (!$lessonPlan) {
                return response()->json(['success' => false, 'error' => 'Lesson plan not found']);
            }

            $lessonPlan->update([
                'supervisor_signature' => $supervisorSignature
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson plan signed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error signing lesson plan: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove supervisor signature from lesson plan
     */
    public function removeLessonPlanSignature(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $lessonPlanID = $request->input('lesson_planID');

            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            $lessonPlan = LessonPlan::where('lesson_planID', $lessonPlanID)
                ->where('schoolID', $schoolID)
                ->where('sent_to_admin', true)
                ->first();

            if (!$lessonPlan) {
                return response()->json(['success' => false, 'error' => 'Lesson plan not found']);
            }

            if (!$lessonPlan->supervisor_signature) {
                return response()->json(['success' => false, 'error' => 'No signature found to remove']);
            }

            $lessonPlan->update([
                'supervisor_signature' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Signature removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing lesson plan signature: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getVisitorNotificationCount()
    {
        $isAdmin = Session::get('user_type') === 'Admin';
        $schoolID = Session::get('schoolID');
        if (!$isAdmin || !$schoolID) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $lastSeen = Session::get('visitors_last_seen');
        $lastSeenAt = $lastSeen ? Carbon::parse($lastSeen) : Carbon::createFromTimestamp(0);

        $count = DB::table('school_visitors')
            ->where('schoolID', $schoolID)
            ->where('created_at', '>', $lastSeenAt)
            ->count();

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function markVisitorNotificationsRead()
    {
        $isAdmin = Session::get('user_type') === 'Admin';
        if (!$isAdmin) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        Session::put('visitors_last_seen', now()->toDateTimeString());
        return response()->json(['success' => true]);
    }

    public function getRecentVisitors()
    {
        $isAdmin = Session::get('user_type') === 'Admin';
        $schoolID = Session::get('schoolID');
        if (!$isAdmin || !$schoolID) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $lastSeenAt = Session::get('visitors_last_seen');
        
        $visitors = DB::table('school_visitors')
            ->where('schoolID', $schoolID)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($visitor) use ($lastSeenAt) {
                $created = Carbon::parse($visitor->created_at);
                $visitor->time_label = $created->isToday() 
                    ? $created->diffForHumans() 
                    : $created->format('d M Y');
                $visitor->is_new = $lastSeenAt ? $created->gt(Carbon::parse($lastSeenAt)) : true;
                return $visitor;
            });

        return response()->json(['success' => true, 'visitors' => $visitors]);
    }
}


