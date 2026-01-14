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
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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
        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
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
            if (!$user || $user !== 'Admin') {
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
}
