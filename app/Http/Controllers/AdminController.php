<?php

namespace App\Http\Controllers;

use App\Models\SessionTask;
use App\Models\Teacher;
use App\Models\ClassSessionTimetable;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
}
