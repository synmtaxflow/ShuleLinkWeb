<?php

namespace App\Http\Controllers;

use App\Models\ClassSubject;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\Student;
use App\Models\Examination;
use App\Models\School;
use App\Models\ExamPaper;
use App\Models\GradeDefinition;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\SchoolSubject;
use App\Models\ClassTeacherApproval;
use App\Models\CoordinatorApproval;
use App\Models\ClassSessionTimetable;
use App\Models\StudentSessionAttendance;
use App\Models\SessionTask;
use App\Models\Holiday;
use App\Models\Event;
use App\Models\SchemeOfWork;
use App\Models\SchemeOfWorkItem;
use App\Models\SchemeOfWorkLearningObjective;
use App\Models\LessonPlan;
use App\Models\PermissionRequest;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
// PhpSpreadsheet will be used with fully qualified names if available

class TeachersController extends Controller
{
    public function teachersDashboard()
    {
           $user = Session::get('user_type');

        if (!$user)
        {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }
        
        // Check for exam rejection notifications
        $teacherID = Session::get('teacherID');
        $rejectionNotifications = [];
        if ($teacherID) {
            // Get all session keys for this teacher's notifications
            $sessionKeys = Session::all();
            foreach ($sessionKeys as $key => $value) {
                if (strpos($key, "teacher_notification_{$teacherID}_exam_rejected_") === 0) {
                    $rejectionNotifications[] = $value;
                }
            }
        }
        
        // Check for pending result approvals
        $pendingApprovals = [];
        $specialRoleApprovals = []; // For class_teacher and coordinator
        $hasAssignedClass = false; // Check if teacher has assigned class
        
        if ($teacherID) {
            $schoolID = Session::get('schoolID');
            
            // Get teacher's regular roles
            $teacherRoles = DB::table('role_user')
                ->where('teacher_id', $teacherID)
                ->pluck('role_id')
                ->toArray();

            // Get teacher's subclasses (class_teacher)
            $teacherSubclasses = DB::table('subclasses')
                ->where('teacherID', $teacherID)
                ->pluck('subclassID')
                ->toArray();
            
            // Check if teacher has assigned class (either as subclass teacher or main class coordinator)
            $hasAssignedClass = !empty($teacherSubclasses) || 
                DB::table('classes')
                    ->where('teacherID', $teacherID)
                    ->where('schoolID', $schoolID)
                    ->exists();
            
            // Get teacher's main classes as coordinator
            $teacherMainClasses = DB::table('classes')
                ->where('teacherID', $teacherID)
                ->where('schoolID', $schoolID)
                ->pluck('classID')
                ->toArray();

            // Get pending/rejected approvals for this teacher's regular roles
            $pendingApprovals = [];
            $waitingApprovals = []; // Approvals waiting for previous steps

            if (!empty($teacherRoles)) {
                $allTeacherApprovals = ResultApproval::with(['examination', 'role'])
                ->whereIn('role_id', $teacherRoles)
                    ->whereIn('status', ['pending', 'rejected'])
                    ->get();
                
                foreach ($allTeacherApprovals as $approval) {
                    $examID = $approval->examID;
                    $approvalOrder = $approval->approval_order;
                    
                    // Get all previous approvals for this exam
                    $previousApprovals = ResultApproval::where('examID', $examID)
                        ->where('approval_order', '<', $approvalOrder)
                        ->with(['role', 'approver'])
                        ->get();
                    
                    // Check if all previous approvals are approved
                    $canProceed = $previousApprovals->isEmpty() || 
                        $previousApprovals->every(function($prev) {
                            return $prev->status === 'approved';
                        });
                    
                    if ($canProceed) {
                        $pendingApprovals[] = $approval;
                    } else {
                        // Get the current approver (first pending approval in chain)
                        $currentApprover = $previousApprovals->firstWhere('status', 'pending');
                        if (!$currentApprover) {
                            $currentApprover = $previousApprovals->firstWhere('status', 'rejected');
                        }
                        
                        $waitingApprovals[] = [
                            'approval' => $approval,
                            'current_approver' => $currentApprover,
                            'pending_count' => $previousApprovals->where('status', 'pending')->count(),
                        ];
                    }
                }
                
                $pendingApprovals = collect($pendingApprovals)->values();
            }
            
            // Check for special role approvals (class_teacher and coordinator)
            // Get all pending approvals with special_role_type
            $schoolExamIDs = Examination::where('schoolID', $schoolID)
                ->pluck('examID')
                ->toArray();
            
            $specialRoleResultApprovals = ResultApproval::with(['examination', 'classTeacherApprovals', 'coordinatorApprovals'])
                ->whereIn('status', ['pending', 'rejected'])
                ->whereIn('examID', $schoolExamIDs)
                ->whereNotNull('special_role_type')
                ->get();
            
            foreach ($specialRoleResultApprovals as $approval) {
                $examID = $approval->examID;
                $approvalOrder = $approval->approval_order;
                
                // Check if all previous approvals are approved
                $previousApprovals = ResultApproval::where('examID', $examID)
                    ->where('approval_order', '<', $approvalOrder)
                    ->get();
                
                $canProceed = $previousApprovals->isEmpty() || 
                    $previousApprovals->every(function($prev) {
                        return $prev->status === 'approved';
                    });
                
                if (!$canProceed) {
                    continue; // Skip if previous approvals not completed
                }
                
                // Check based on special_role_type
                if ($approval->special_role_type === 'class_teacher') {
                    // Check if teacher has any subclass that needs approval
                    if (!empty($teacherSubclasses)) {
                        // Get class_teacher_approvals for this result_approval that match teacher's subclasses
                        $matchingApprovals = ClassTeacherApproval::where('result_approvalID', $approval->result_approvalID)
                            ->whereIn('subclassID', $teacherSubclasses)
                            ->whereIn('status', ['pending', 'rejected'])
                            ->get();
                        
                        if ($matchingApprovals->count() > 0) {
                            $specialRoleApprovals[] = [
                                'approval' => $approval,
                                'type' => 'class_teacher',
                                'exam' => $approval->examination,
                                'matching_approvals' => $matchingApprovals,
                            ];
                        }
                    }
                } elseif ($approval->special_role_type === 'coordinator') {
                    // Check if teacher has any mainclass that needs approval
                    if (!empty($teacherMainClasses)) {
                        // Get coordinator_approvals for this result_approval that match teacher's mainclasses
                        $matchingApprovals = CoordinatorApproval::where('result_approvalID', $approval->result_approvalID)
                            ->whereIn('mainclassID', $teacherMainClasses)
                            ->whereIn('status', ['pending', 'rejected'])
                            ->get();
                        
                        if ($matchingApprovals->count() > 0) {
                            $specialRoleApprovals[] = [
                                'approval' => $approval,
                                'type' => 'coordinator',
                                'exam' => $approval->examination,
                                'matching_approvals' => $matchingApprovals,
                            ];
                        }
                    }
                }
            }
            
            // Get all exams where teacher is in approval chain (for approval chain visualization)
            $approvalChainExams = [];
            $allExamApprovals = ResultApproval::with(['examination', 'role', 'classTeacherApprovals.subclass', 'coordinatorApprovals.mainclass'])
                ->whereIn('examID', $schoolExamIDs)
                ->orderBy('examID')
                ->orderBy('approval_order')
                ->get()
                ->groupBy('examID');
            
            foreach ($allExamApprovals as $examID => $approvals) {
                $exam = $approvals->first()->examination;
                if (!$exam) continue;
                
                // Check if teacher is in this approval chain
                $teacherInChain = false;
                $teacherApprovalOrder = null;
                
                foreach ($approvals as $approval) {
                    // Check regular roles
                    if ($approval->role_id && in_array($approval->role_id, $teacherRoles)) {
                        $teacherInChain = true;
                        $teacherApprovalOrder = $approval->approval_order;
                        break;
                    }
                    
                    // Check special roles
                    if ($approval->special_role_type === 'class_teacher' && !empty($teacherSubclasses)) {
                        $classTeacherApprovals = ClassTeacherApproval::where('result_approvalID', $approval->result_approvalID)
                            ->whereIn('subclassID', $teacherSubclasses)
                            ->exists();
                        if ($classTeacherApprovals) {
                            $teacherInChain = true;
                            $teacherApprovalOrder = $approval->approval_order;
                            break;
                        }
                    }
                    
                    if ($approval->special_role_type === 'coordinator' && !empty($teacherMainClasses)) {
                        $coordinatorApprovals = CoordinatorApproval::where('result_approvalID', $approval->result_approvalID)
                            ->whereIn('mainclassID', $teacherMainClasses)
                            ->exists();
                        if ($coordinatorApprovals) {
                            $teacherInChain = true;
                            $teacherApprovalOrder = $approval->approval_order;
                            break;
                        }
                    }
                }
                
                if ($teacherInChain) {
                    // Check if teacher has already approved their step
                    $teacherHasApproved = false;
                    
                    foreach ($approvals as $approval) {
                        $isTeacherStep = false;
                        
                        // Check regular roles
                        if ($approval->role_id && in_array($approval->role_id, $teacherRoles)) {
                            $isTeacherStep = true;
                            if ($approval->status === 'approved') {
                                $teacherHasApproved = true;
                                break;
                            }
                        }
                        
                        // Check special roles
                        if ($approval->special_role_type === 'class_teacher' && !empty($teacherSubclasses)) {
                            $matchingApprovals = ClassTeacherApproval::where('result_approvalID', $approval->result_approvalID)
                                ->whereIn('subclassID', $teacherSubclasses)
                                ->get();
                            
                            if ($matchingApprovals->count() > 0) {
                                $isTeacherStep = true;
                                // Check if all matching approvals are approved
                                if ($matchingApprovals->every(function($cta) {
                                    return $cta->status === 'approved';
                                })) {
                                    $teacherHasApproved = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($approval->special_role_type === 'coordinator' && !empty($teacherMainClasses)) {
                            $matchingApprovals = CoordinatorApproval::where('result_approvalID', $approval->result_approvalID)
                                ->whereIn('mainclassID', $teacherMainClasses)
                                ->get();
                            
                            if ($matchingApprovals->count() > 0) {
                                $isTeacherStep = true;
                                // Check if all matching approvals are approved
                                if ($matchingApprovals->every(function($ca) {
                                    return $ca->status === 'approved';
                                })) {
                                    $teacherHasApproved = true;
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Only add if teacher hasn't approved yet (status is pending or rejected)
                    if (!$teacherHasApproved) {
                        // Build approval chain
                        $chain = [];
                        foreach ($approvals->sortBy('approval_order') as $approval) {
                            $roleName = 'N/A';
                            if ($approval->role_id) {
                                $role = DB::table('roles')->where('id', $approval->role_id)->first();
                                $roleName = $role->name ?? $role->role_name ?? 'N/A';
                            } elseif ($approval->special_role_type === 'class_teacher') {
                                $roleName = 'Class Teacher';
                            } elseif ($approval->special_role_type === 'coordinator') {
                                $roleName = 'Coordinator';
                            }
                            
                            $chain[] = [
                                'result_approvalID' => $approval->result_approvalID,
                                'approval_order' => $approval->approval_order,
                                'role_name' => $roleName,
                                'status' => $approval->status,
                                'special_role_type' => $approval->special_role_type,
                                'is_teacher_step' => ($approval->role_id && in_array($approval->role_id, $teacherRoles)) ||
                                    ($approval->special_role_type === 'class_teacher' && !empty($teacherSubclasses) && 
                                     ClassTeacherApproval::where('result_approvalID', $approval->result_approvalID)
                                         ->whereIn('subclassID', $teacherSubclasses)->exists()) ||
                                    ($approval->special_role_type === 'coordinator' && !empty($teacherMainClasses) &&
                                     CoordinatorApproval::where('result_approvalID', $approval->result_approvalID)
                                         ->whereIn('mainclassID', $teacherMainClasses)->exists()),
                            ];
                        }
                        
                        $approvalChainExams[] = [
                            'exam' => $exam,
                            'examID' => $examID,
                            'chain' => $chain,
                            'teacher_approval_order' => $teacherApprovalOrder,
                        ];
                    }
                }
            }
        }
        
        // Get count of new supervise exam assignments (exams not ended)
        $superviseExamCount = 0;
        if ($teacherID && $schoolID) {
            $superviseExamCount = DB::table('exam_hall_supervisors')
                ->join('examinations', 'exam_hall_supervisors.examID', '=', 'examinations.examID')
                ->where('exam_hall_supervisors.teacherID', $teacherID)
                ->where('exam_hall_supervisors.schoolID', $schoolID)
                ->where('examinations.schoolID', $schoolID)
                ->where('examinations.end_date', '>=', now()->toDateString())
                ->where('examinations.approval_status', 'Approved')
                ->distinct('examinations.examID')
                ->count('examinations.examID');
        }
        
        // Dashboard Statistics
        $dashboardStats = [];
        if ($teacherID && $schoolID) {
            // Get active session timetable definition
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();
            
            // Count subjects teaching
            $subjectsCount = ClassSubject::where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->distinct('subjectID')
                ->count('subjectID');
            
            // Count classes teaching (distinct subclasses)
            $classesCount = ClassSubject::where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->distinct('subclassID')
                ->count('subclassID');
            
            // Count sessions per week (Monday-Friday)
            $sessionsPerWeek = 0;
            if ($definition) {
                $sessionsPerWeek = ClassSessionTimetable::where('teacherID', $teacherID)
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
                $yearHolidays = Holiday::where('schoolID', $schoolID)
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
                $yearEvents = Event::where('schoolID', $schoolID)
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
            
            // Count approved sessions (sessions with approved tasks)
            $approvedSessionsCount = 0;
            if ($definition) {
                $approvedSessionsCount = SessionTask::where('teacherID', $teacherID)
                    ->where('status', 'approved')
                    ->whereHas('sessionTimetable', function($query) use ($definition) {
                        $query->where('definitionID', $definition->definitionID);
                    })
                    ->distinct('session_timetableID')
                    ->count('session_timetableID');
            }
            
            // Get subjects teaching (for display)
            $teachingSubjects = ClassSubject::with(['subject', 'subclass.class'])
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->get()
                ->groupBy('subjectID')
                ->map(function($group) {
                    $first = $group->first();
                    return [
                        'subjectID' => $first->subjectID,
                        'subject_name' => $first->subject->subject_name ?? 'N/A',
                        'subject_code' => $first->subject->subject_code ?? null,
                    ];
                })
                ->values()
                ->take(5); // Show first 5, rest via "View More"
            
            // Count subclasses managed as class teacher
            $classTeacherSubclassesCount = 0;
            if ($teacherID && $schoolID) {
                $classTeacherSubclassesCount = DB::table('subclasses')
                    ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                    ->where('subclasses.teacherID', $teacherID)
                    ->where('classes.schoolID', $schoolID)
                    ->distinct('subclasses.subclassID')
                    ->count('subclasses.subclassID');
            }
            
            // Count main classes managed as coordinator (only classes with more than one subclass)
            $coordinatorClassesCount = 0;
            $coordinatorClasses = [];
            if ($teacherID && $schoolID) {
                // Get main classes where teacher is coordinator
                $mainClasses = DB::table('classes')
                    ->where('teacherID', $teacherID)
                    ->where('schoolID', $schoolID)
                    ->get();
                
                // Filter: only count classes that have more than one subclass
                foreach ($mainClasses as $mainClass) {
                    $subclassCount = DB::table('subclasses')
                        ->where('classID', $mainClass->classID)
                        ->where('status', 'Active')
                        ->count();
                    
                    if ($subclassCount > 1) {
                        $coordinatorClasses[] = $mainClass;
                        $coordinatorClassesCount++;
                    }
                }
            }
            
            // Count Scheme of Work
            $schemeOfWorkCount = 0;
            if ($teacherID) {
                $currentYear = Carbon::now()->year;
                $schemeOfWorkCount = SchemeOfWork::whereHas('classSubject', function($query) use ($teacherID) {
                    $query->where('teacherID', $teacherID)
                          ->where('status', 'Active');
                })
                ->where('year', $currentYear)
                ->count();
            }
            
            // Count Lesson Plans
            $lessonPlansCount = 0;
            $lessonPlansSentCount = 0;
            if ($teacherID) {
                $currentYear = Carbon::now()->year;
                $lessonPlansCount = LessonPlan::where('teacherID', $teacherID)
                    ->whereYear('lesson_date', $currentYear)
                    ->count();
                
                $lessonPlansSentCount = LessonPlan::where('teacherID', $teacherID)
                    ->where('sent_to_admin', true)
                    ->whereYear('lesson_date', $currentYear)
                    ->count();
            }
            
            $dashboardStats = [
                'subjects_count' => $subjectsCount,
                'classes_count' => $classesCount,
                'sessions_per_week' => $sessionsPerWeek,
                'sessions_per_year' => $sessionsPerYear,
                'approved_sessions_count' => $approvedSessionsCount,
                'teaching_subjects' => $teachingSubjects,
                'class_teacher_subclasses_count' => $classTeacherSubclassesCount,
                'coordinator_classes_count' => $coordinatorClassesCount,
                'coordinator_classes' => $coordinatorClasses, // Pass coordinator classes list
                'scheme_of_work_count' => $schemeOfWorkCount,
                'lesson_plans_count' => $lessonPlansCount,
                'lesson_plans_sent_count' => $lessonPlansSentCount,
            ];
        }
        
        // Get Notifications
        $notifications = collect();
        
        // Exam rejection notifications (already in $rejectionNotifications)
        foreach ($rejectionNotifications as $notification) {
            if (is_array($notification) && isset($notification['type']) && $notification['type'] === 'exam_rejected') {
                $notifications->push([
                    'type' => 'exam_rejected',
                    'icon' => 'fa-times-circle',
                    'color' => 'danger',
                    'title' => 'Exam Rejected',
                    'message' => $notification['message'] ?? 'Your examination has been rejected',
                    'date' => $notification['created_at'] ?? now()->toDateTimeString(),
                    'link' => '#'
                ]);
            }
        }
        
        // Add pending approvals notifications (they will disappear once approved)
        if (isset($pendingApprovals) && count($pendingApprovals) > 0) {
            foreach ($pendingApprovals as $approval) {
                $notifications->push([
                    'type' => 'approval_pending',
                    'icon' => $approval->status === 'rejected' ? 'fa-times-circle' : 'fa-exclamation-triangle',
                    'color' => $approval->status === 'rejected' ? 'danger' : 'warning',
                    'title' => $approval->status === 'rejected' ? 'Approval Rejected' : 'Approval Pending',
                    'message' => ($approval->examination->exam_name ?? 'N/A') . ' - ' . ($approval->role->name ?? $approval->role->role_name ?? 'N/A') . ' (Step ' . $approval->approval_order . ')',
                    'date' => $approval->updated_at ? $approval->updated_at->toDateTimeString() : now()->toDateTimeString(),
                    'link' => $approval->examination && $approval->examination->examID ? route('approve_result', $approval->examination->examID) : '#'
                ]);
            }
        }
        
        // Add special role approvals notifications
        if (isset($specialRoleApprovals) && count($specialRoleApprovals) > 0) {
            foreach ($specialRoleApprovals as $specialApproval) {
                $approval = $specialApproval['approval'];
                $type = $specialApproval['type'];
                $exam = $specialApproval['exam'];
                
                $notifications->push([
                    'type' => 'special_approval_pending',
                    'icon' => $approval->status === 'rejected' ? 'fa-times-circle' : ($type === 'class_teacher' ? 'fa-users' : 'fa-diagram-3'),
                    'color' => $approval->status === 'rejected' ? 'danger' : ($type === 'class_teacher' ? 'warning' : 'info'),
                    'title' => $approval->status === 'rejected' ? ucfirst($type) . ' Approval Rejected' : ucfirst(str_replace('_', ' ', $type)) . ' Approval Pending',
                    'message' => ($exam->exam_name ?? 'N/A') . ' - Step ' . $approval->approval_order,
                    'date' => $approval->updated_at ? $approval->updated_at->toDateTimeString() : now()->toDateTimeString(),
                    'link' => $exam && $exam->examID ? route('approve_result', $exam->examID) : '#'
                ]);
            }
        }
        
        // New examinations notifications (recent examinations created)
        if ($teacherID && $schoolID) {
            $recentExams = Examination::where('schoolID', $schoolID)
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->where('approval_status', 'Approved')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            foreach ($recentExams as $exam) {
                $notifications->push([
                    'type' => 'new_exam',
                    'icon' => 'fa-calendar-check-o',
                    'color' => 'info',
                    'title' => 'New Examination',
                    'message' => $exam->exam_name . ' - ' . ($exam->year ?? ''),
                    'date' => $exam->created_at->toDateTimeString(),
                    'link' => route('supervise_exams')
                ]);
            }
            
            // Session time notifications (sessions happening today)
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();
            
            if ($definition) {
                $today = Carbon::today(config('app.timezone'));
                $todayDayName = $today->format('l'); // Monday, Tuesday, etc.
                
                $todaySessions = ClassSessionTimetable::with(['subject', 'classSubject.subject', 'subclass.class'])
                    ->where('teacherID', $teacherID)
                    ->where('definitionID', $definition->definitionID)
                    ->where('day', $todayDayName)
                    ->get();
                
                $now = Carbon::now(config('app.timezone'));
                foreach ($todaySessions as $session) {
                    // Parse time and combine with today's date
                    // Handle both time string and datetime object
                    $startTimeStr = $session->start_time instanceof \DateTime 
                        ? $session->start_time->format('H:i:s') 
                        : (is_string($session->start_time) ? $session->start_time : '00:00:00');
                    $endTimeStr = $session->end_time instanceof \DateTime 
                        ? $session->end_time->format('H:i:s') 
                        : (is_string($session->end_time) ? $session->end_time : '00:00:00');
                    
                    $sessionTime = $today->copy()->setTimeFromTimeString($startTimeStr);
                    $sessionEndTime = $today->copy()->setTimeFromTimeString($endTimeStr);
                    
                    // Check if session time has arrived (current time >= session start time) or is happening now
                    // Also check if it's not a holiday or weekend
                    $isHoliday = false;
                    $isWeekend = $today->isWeekend();
                    
                    // Check for holidays
                    $holidays = \App\Models\Holiday::where('schoolID', $schoolID)
                        ->where(function($query) use ($today) {
                            $query->whereDate('start_date', '<=', $today)
                                  ->whereDate('end_date', '>=', $today);
                        })
                        ->exists();
                    
                    $events = \App\Models\Event::where('schoolID', $schoolID)
                        ->whereDate('event_date', $today)
                        ->where('is_non_working_day', true)
                        ->exists();
                    
                    $isHoliday = $holidays || $events;
                    
                    // Show notification ONLY when session time has arrived and is still active
                    // Notification should disappear once session time ends
                    if (!$isHoliday && !$isWeekend && $now >= $sessionTime && $now <= $sessionEndTime) {
                        // Get subject name - check classSubject first, then subject
                        $subjectName = 'N/A';
                        if($session->classSubject && $session->classSubject->subject && $session->classSubject->subject->subject_name) {
                            $subjectName = $session->classSubject->subject->subject_name;
                        } elseif($session->subject && $session->subject->subject_name) {
                            $subjectName = $session->subject->subject_name;
                        }
                        if($session->is_prepo) {
                            $subjectName .= ' (Prepo)';
                        }
                        
                        $className = $session->subclass ? ($session->subclass->class->class_name ?? '') . ' - ' . ($session->subclass->subclass_name ?? '') : 'N/A';
                        
                        $notifications->push([
                            'type' => 'session_time',
                            'icon' => 'fa-clock-o',
                            'color' => 'warning',
                            'title' => 'Session Time',
                            'message' => 'Session yako imefika: ' . $subjectName . ' - ' . $className,
                            'date' => $now->toDateTimeString(),
                            'link' => route('teacher.mySessions')
                        ]);
                    }
                }
            }
        }
        
        // Sort notifications by date (most recent first)
        $notifications = $notifications->sortByDesc(function($notification) {
            return $notification['date'];
        })->values()->take(10);

        // Add feedback response notifications (suggestions/incidents)
        if ($teacherID && $schoolID) {
            $feedbackResponses = \App\Models\TeacherFeedback::where('schoolID', $schoolID)
                ->where('teacherID', $teacherID)
                ->where('is_read_by_teacher', false)
                ->whereIn('status', ['approved', 'rejected'])
                ->orderBy('responded_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($feedbackResponses as $feedback) {
                $typeLabel = $feedback->type === 'incident' ? 'Incident' : 'Suggestion';
                $statusLabel = ucfirst($feedback->status);
                $color = $feedback->status === 'approved' ? 'success' : 'danger';
                $link = $feedback->type === 'incident'
                    ? route('teacher.incidents', ['tab' => 'incidents', 'section' => 'view'])
                    : route('teacher.suggestions', ['tab' => 'suggestions', 'section' => 'view']);

                $notifications->push([
                    'type' => 'feedback_response',
                    'icon' => 'fa-comments',
                    'color' => $color,
                    'title' => "{$typeLabel} {$statusLabel}",
                    'message' => $feedback->admin_response ? $feedback->admin_response : "{$typeLabel} has been {$feedback->status}.",
                    'date' => $feedback->responded_at ? $feedback->responded_at->toDateTimeString() : $feedback->updated_at->toDateTimeString(),
                    'link' => $link,
                ]);
            }

            // Re-sort after adding feedback notifications
            $notifications = $notifications->sortByDesc(function($notification) {
                return $notification['date'];
            })->values()->take(10);
        }
        
        // Get graph data
        $graphData = $this->getDashboardGraphData($teacherID, $schoolID);
        
        // Get management permissions for dashboard widgets
        $managementPermissions = [];
        if ($teacherID) {
            // Get teacher's roles
            $teacherRoles = DB::table('role_user')
                ->where('teacher_id', $teacherID)
                ->pluck('role_id')
                ->toArray();
            
            // Get permissions grouped by category
            $teacherPermissionsByCategory = collect();
            if (!empty($teacherRoles)) {
                $permissionsData = DB::table('permissions')
                    ->whereIn('role_id', $teacherRoles)
                    ->select('name', 'permission_category')
                    ->get();
                
                $teacherPermissionsByCategory = $permissionsData->groupBy('permission_category')
                    ->map(function($perms) {
                        return $perms->pluck('name')->unique()->values();
                    });
            }
            
            $categoryRoutes = [
                'examination' => ['route' => 'manageExamination', 'name' => 'Examination Management', 'icon' => 'fa-pencil-square-o', 'color' => '#dc3545'],
                'classes' => ['route' => 'manageClasses', 'name' => 'Classes Management', 'icon' => 'fa-users', 'color' => '#17a2b8'],
                'subject' => ['route' => 'manageSubjects', 'name' => 'Subject Management', 'icon' => 'fa-bookmark', 'color' => '#28a745'],
                'result' => ['route' => 'manageResults', 'name' => 'Result Management', 'icon' => 'fa-trophy', 'color' => '#ffc107'],
                'attendance' => ['route' => 'manageAttendance', 'name' => 'Attendance Management', 'icon' => 'fa-check-square-o', 'color' => '#17a2b8'],
                'student' => ['route' => 'manage_student', 'name' => 'Student Management', 'icon' => 'fa-user', 'color' => '#28a745'],
                'parent' => ['route' => 'manage_parents', 'name' => 'Parent Management', 'icon' => 'fa-user-plus', 'color' => '#ffc107'],
                'timetable' => ['route' => 'timeTable', 'name' => 'Timetable Management', 'icon' => 'fa-clock-o', 'color' => '#007bff'],
                'fees' => ['route' => 'manage_fees', 'name' => 'Fees Management', 'icon' => 'fa-money', 'color' => '#28a745'],
                'accommodation' => ['route' => 'manage_accomodation', 'name' => 'Accommodation Management', 'icon' => 'fa-bed', 'color' => '#17a2b8'],
                'library' => ['route' => 'manage_library', 'name' => 'Library Management', 'icon' => 'fa-book', 'color' => '#6f42c1'],
                'calendar' => ['route' => 'admin.calendar', 'name' => 'Calendar Management', 'icon' => 'fa-calendar', 'color' => '#dc3545'],
                'fingerprint' => ['route' => 'fingerprint_device_settings', 'name' => 'Fingerprint Settings', 'icon' => 'fa-fingerprint', 'color' => '#ffc107'],
                'task' => ['route' => 'taskManagement', 'name' => 'Task Management', 'icon' => 'fa-tasks', 'color' => '#17a2b8'],
                'sms' => ['route' => 'sms_notification', 'name' => 'SMS Information', 'icon' => 'fa-envelope', 'color' => '#28a745'],
            ];
            
            foreach ($categoryRoutes as $category => $config) {
                if ($teacherPermissionsByCategory->has($category) && $teacherPermissionsByCategory->get($category)->count() > 0) {
                    $managementPermissions[] = $config;
                }
            }
        }
        
        // Pass teacherNotifications to nav
        $teacherNotifications = $notifications;
        
        return view('Teacher.dashboard', compact('rejectionNotifications', 'pendingApprovals', 'specialRoleApprovals', 'waitingApprovals', 'approvalChainExams', 'superviseExamCount', 'hasAssignedClass', 'dashboardStats', 'notifications', 'graphData', 'teacherNotifications', 'managementPermissions'));
    }

    public function manageTeacherFeedback(Request $request)
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Teacher') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        $tab = $request->query('tab');
        $section = $request->query('section', 'send');
        if (!$tab) {
            $routeName = optional($request->route())->getName();
            $tab = $routeName === 'teacher.incidents' ? 'incidents' : 'suggestions';
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $feedbackQuery = \App\Models\TeacherFeedback::where('teacherID', $teacherID)
            ->where('schoolID', $schoolID);

        if ($dateFrom) {
            $feedbackQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $feedbackQuery->whereDate('created_at', '<=', $dateTo);
        }

        $feedback = $feedbackQuery->orderBy('created_at', 'desc')->get();

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
        \App\Models\TeacherFeedback::where('teacherID', $teacherID)
            ->where('schoolID', $schoolID)
            ->where('type', $readType)
            ->update(['is_read_by_teacher' => true]);

        return view('Teacher.manage_feedback', [
            'activeTab' => $tab,
            'activeSection' => $section,
            'suggestions' => $suggestions,
            'incidents' => $incidents,
            'suggestionStats' => $suggestionStats,
            'incidentStats' => $incidentStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function storeTeacherFeedback(Request $request)
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Teacher') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID || !$schoolID) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired'], 403);
            }
            return redirect()->route('login')->with('error', 'Session expired');
        }

        $validated = $request->validate([
            'type' => 'required|in:suggestion,incident',
            'message' => 'required|string',
        ]);

        \App\Models\TeacherFeedback::create([
            'schoolID' => $schoolID,
            'teacherID' => $teacherID,
            'type' => $validated['type'],
            'message' => $validated['message'],
            'status' => 'pending',
            'is_read_by_admin' => false,
            'is_read_by_teacher' => true,
        ]);

        $teacher = \App\Models\Teacher::where('id', $teacherID)->first();
        $school = \App\Models\School::where('schoolID', $schoolID)->first();
        $adminPhone = $school->phone ?? null;

        if ($adminPhone) {
            $typeLabel = $validated['type'] === 'incident' ? 'Incident' : 'Suggestion';
            $teacherName = $teacher ? trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')) : 'Teacher';
            $message = "New {$typeLabel} from {$teacherName}. Please review in the system.";
            $smsService = new \App\Services\SmsService();
            $smsService->sendSms($adminPhone, $message);
        }

        $routeName = $validated['type'] === 'incident' ? 'teacher.incidents' : 'teacher.suggestions';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Message sent successfully.']);
        }

        return redirect()->route($routeName)->with('success', 'Message sent successfully.');
    }

    /**
     * Get teacher dashboard data for API (Flutter app)
     */
    public function getTeacherDashboardAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
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

            // Get dashboard statistics
            $dashboardStats = [];
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();
            
            // Count subjects teaching
            $subjectsCount = ClassSubject::where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->distinct('subjectID')
                ->count('subjectID');
            
            // Count classes teaching
            $classesCount = ClassSubject::where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->distinct('subclassID')
                ->count('subclassID');
            
            // Count sessions per week
            $sessionsPerWeek = 0;
            if ($definition) {
                $sessionsPerWeek = ClassSessionTimetable::where('teacherID', $teacherID)
                    ->where('definitionID', $definition->definitionID)
                    ->count();
            }
            
            // Get teaching subjects list
            $teachingSubjects = ClassSubject::where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->with(['subject' => function($query) {
                    $query->where('status', 'Active');
                }])
                ->get()
                ->pluck('subject.subject_name')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
            
            // Get notifications
            $notifications = collect();
            
            // Get pending approvals count
            // This includes regular role approvals, class teacher approvals, and coordinator approvals
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
            
            // Get supervise exam count
            $superviseExamCount = DB::table('exam_hall_supervisors')
                ->join('examinations', 'exam_hall_supervisors.examID', '=', 'examinations.examID')
                ->where('exam_hall_supervisors.teacherID', $teacherID)
                ->where('examinations.end_date', '>=', now()->toDateString())
                ->where('examinations.approval_status', 'Approved')
                ->distinct('examinations.examID')
                ->count('examinations.examID');
            
            // Get lesson plans count
            $currentYear = Carbon::now()->year;
            $lessonPlansCount = LessonPlan::where('teacherID', $teacherID)
                ->whereYear('lesson_date', $currentYear)
                ->count();
            
            $lessonPlansSentCount = LessonPlan::where('teacherID', $teacherID)
                ->where('sent_to_admin', true)
                ->whereYear('lesson_date', $currentYear)
                ->count();
            
            $dashboardStats = [
                'subjects_count' => $subjectsCount,
                'classes_count' => $classesCount,
                'sessions_per_week' => $sessionsPerWeek,
                'teaching_subjects' => $teachingSubjects,
                'pending_approvals_count' => $pendingApprovals,
                'supervise_exams_count' => $superviseExamCount,
                'lesson_plans_count' => $lessonPlansCount,
                'lesson_plans_sent_count' => $lessonPlansSentCount,
            ];
            
            // Get menu items
            $menuItems = $this->getTeacherMenuItems($teacherID);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'dashboard_stats' => $dashboardStats,
                    'notifications' => $notifications->take(10)->values()->toArray(),
                    'menu_items' => $menuItems,
                ],
                'message' => 'Dashboard data retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting teacher dashboard API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher menu items for API
     */
    private function getTeacherMenuItems($teacherID)
    {
        $menuItems = [
            [
                'id' => 'dashboard',
                'name' => 'Dashboard',
                'icon' => 'fa-building',
                'route' => 'teachersDashboard',
                'type' => 'main'
            ],
            [
                'id' => 'my_sessions',
                'name' => 'My Sessions',
                'icon' => 'fa-clock-o',
                'route' => 'teacher.mySessions',
                'type' => 'main'
            ],
            [
                'id' => 'my_tasks',
                'name' => 'My Tasks',
                'icon' => 'fa-tasks',
                'route' => 'teacher.myTasks',
                'type' => 'main'
            ],
            [
                'id' => 'my_subjects',
                'name' => 'My Subjects',
                'icon' => 'fa-book',
                'route' => 'teacherSubjects',
                'type' => 'main'
            ],
            [
                'id' => 'scheme_of_work',
                'name' => 'Scheme of Work',
                'icon' => 'fa-file-text-o',
                'route' => 'teacher.schemeOfWork',
                'type' => 'main'
            ],
            [
                'id' => 'lesson_plans',
                'name' => 'Lesson Plans',
                'icon' => 'fa-book',
                'route' => 'teacher.lessonPlans',
                'type' => 'main'
            ],
            [
                'id' => 'calendar',
                'name' => 'Calendar',
                'icon' => 'fa-calendar',
                'route' => 'teacher.calendar',
                'type' => 'main'
            ],
            [
                'id' => 'supervise_exams',
                'name' => 'My Supervise Exams',
                'icon' => 'fa-graduation-cap',
                'route' => 'supervise_exams',
                'type' => 'main'
            ],
            [
                'id' => 'exam_papers',
                'name' => 'My Exam Papers',
                'icon' => 'fa-file-text',
                'route' => 'exam_paper',
                'type' => 'main'
            ],
        ];
        
        // Check if teacher has assigned class (either as subclass teacher or main class coordinator)
        $teacherSubclasses = DB::table('subclasses')
            ->where('teacherID', $teacherID)
            ->exists();
        
        $hasAssignedClass = $teacherSubclasses || 
            DB::table('classes')
                ->where('teacherID', $teacherID)
                ->where('schoolID', $schoolID)
                ->exists();
        
        if ($hasAssignedClass) {
            $menuItems[] = [
                'id' => 'my_class',
                'name' => 'My Class',
                'icon' => 'fa-users',
                'route' => 'AdmitedClasses',
                'type' => 'main'
            ];
        }
        
        // Get management permissions
        $teacherRoles = DB::table('role_user')
            ->where('teacher_id', $teacherID)
            ->pluck('role_id')
            ->toArray();
        
        $managementMenuItems = [];
        if (!empty($teacherRoles)) {
            $permissionsData = DB::table('permissions')
                ->whereIn('role_id', $teacherRoles)
                ->select('name', 'permission_category')
                ->get();
            
            $teacherPermissionsByCategory = $permissionsData->groupBy('permission_category')
                ->map(function($perms) {
                    return $perms->pluck('name')->unique()->values();
                });
            
            $categoryRoutes = [
                'examination' => ['id' => 'examination_management', 'name' => 'Examination Management', 'icon' => 'fa-pencil-square-o', 'route' => 'manageExamination'],
                'classes' => ['id' => 'classes_management', 'name' => 'Classes Management', 'icon' => 'fa-users', 'route' => 'manageClasses'],
                'subject' => ['id' => 'subject_management', 'name' => 'Subject Management', 'icon' => 'fa-bookmark', 'route' => 'manageSubjects'],
                'result' => ['id' => 'result_management', 'name' => 'Result Management', 'icon' => 'fa-trophy', 'route' => 'manageResults'],
                'attendance' => ['id' => 'attendance_management', 'name' => 'Attendance Management', 'icon' => 'fa-check-square-o', 'route' => 'manageAttendance'],
                'student' => ['id' => 'student_management', 'name' => 'Student Management', 'icon' => 'fa-user', 'route' => 'manage_student'],
                'parent' => ['id' => 'parent_management', 'name' => 'Parent Management', 'icon' => 'fa-user-plus', 'route' => 'manage_parents'],
                'timetable' => ['id' => 'timetable_management', 'name' => 'Timetable Management', 'icon' => 'fa-clock-o', 'route' => 'timeTable'],
                'fees' => ['id' => 'fees_management', 'name' => 'Fees Management', 'icon' => 'fa-money', 'route' => 'manage_fees'],
                'accommodation' => ['id' => 'accommodation_management', 'name' => 'Accommodation Management', 'icon' => 'fa-bed', 'route' => 'manage_accomodation'],
                'library' => ['id' => 'library_management', 'name' => 'Library Management', 'icon' => 'fa-book', 'route' => 'manage_library'],
                'calendar' => ['id' => 'calendar_management', 'name' => 'Calendar Management', 'icon' => 'fa-calendar', 'route' => 'admin.calendar'],
                'fingerprint' => ['id' => 'fingerprint_settings', 'name' => 'Fingerprint Settings', 'icon' => 'fa-fingerprint', 'route' => 'fingerprint_device_settings'],
                'task' => ['id' => 'task_management', 'name' => 'Task Management', 'icon' => 'fa-tasks', 'route' => 'taskManagement'],
                'sms' => ['id' => 'sms_notification', 'name' => 'SMS Information', 'icon' => 'fa-envelope', 'route' => 'sms_notification'],
            ];
            
            foreach ($categoryRoutes as $category => $config) {
                if ($teacherPermissionsByCategory->has($category) && $teacherPermissionsByCategory->get($category)->count() > 0) {
                    $managementMenuItems[] = array_merge($config, ['type' => 'management']);
                }
            }
        }
        
        return [
            'main_menu' => $menuItems,
            'management_menu' => $managementMenuItems
        ];
    }

    /**
     * Get teacher profile for API
     */
    public function getTeacherProfileAPI(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login first.'
                ], 401);
            }

            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found'
                ], 404);
            }

            // Get user account info
            $user = User::where('name', $teacher->employee_number)->first();

            $profileData = [
                'id' => $teacher->id,
                'school_id' => $teacher->schoolID,
                'first_name' => $teacher->first_name,
                'middle_name' => $teacher->middle_name,
                'last_name' => $teacher->last_name,
                'full_name' => trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? '')),
                'gender' => $teacher->gender,
                'national_id' => $teacher->national_id,
                'employee_number' => $teacher->employee_number,
                'email' => $teacher->email,
                'phone_number' => $teacher->phone_number,
                'qualification' => $teacher->qualification,
                'specialization' => $teacher->specialization,
                'experience' => $teacher->experience,
                'date_of_birth' => $teacher->date_of_birth ? $teacher->date_of_birth->format('Y-m-d') : null,
                'date_hired' => $teacher->date_hired ? $teacher->date_hired->format('Y-m-d') : null,
                'address' => $teacher->address,
                'position' => $teacher->position,
                'status' => $teacher->status,
                'image' => $teacher->image ? asset('userImages/' . $teacher->image) : ($teacher->gender == 'Female' ? asset('images/female.png') : asset('images/male.png')),
                'username' => $user ? $user->name : $teacher->employee_number,
                'has_password' => $user ? true : false,
            ];

            return response()->json([
                'success' => true,
                'data' => $profileData,
                'message' => 'Profile retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting teacher profile API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update teacher profile for API
     */
    public function updateTeacherProfileAPI(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login first.'
                ], 401);
            }

            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found'
                ], 404);
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'gender' => 'sometimes|required|in:Male,Female',
                'email' => 'sometimes|required|email|unique:teachers,email,' . $teacherID,
                'phone_number' => [
                    'sometimes',
                    'required',
                    'unique:teachers,phone_number,' . $teacherID,
                    'regex:/^255\d{9}$/'
                ],
                'qualification' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'experience' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'address' => 'nullable|string|max:500',
                'position' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ], [
                'phone_number.regex' => 'Phone number must have 12 digits: start with 255 followed by 9 digits (e.g., 255614863345)',
                'email.unique' => 'This email is already taken by another teacher.',
                'phone_number.unique' => 'This phone number is already taken by another teacher.',
                'image.max' => 'Image must not exceed 2MB.',
                'image.mimes' => 'Only JPG and PNG formats are allowed.',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 422);
            }

            // Store original email before update
            $oldEmail = $teacher->email;

            // Handle Image Upload - only if new image is provided
            $imageName = $teacher->image; // Keep existing image by default
            if ($request->hasFile('image')) {
                $uploadPath = public_path('userImages');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Delete old image if exists
                if ($teacher->image && file_exists($uploadPath . '/' . $teacher->image)) {
                    unlink($uploadPath . '/' . $teacher->image);
                }

                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->move($uploadPath, $imageName);
            }

            // Update teacher with only provided fields
            $updateData = [];
            $allowedFields = [
                'first_name', 'middle_name', 'last_name', 'gender', 'email',
                'phone_number', 'qualification', 'specialization', 'experience',
                'date_of_birth', 'address', 'position'
            ];

            foreach ($allowedFields as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }

            if ($request->hasFile('image')) {
                $updateData['image'] = $imageName;
            }

            $teacher->update($updateData);

            // Update user account if email changed
            if ($request->has('email') && $oldEmail != $request->email) {
                $user = User::where('email', $oldEmail)->first();
                if ($user) {
                    $user->update([
                        'email' => $request->email,
                    ]);
                }
            }

            // Reload teacher to get updated data
            $teacher->refresh();

            $profileData = [
                'id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'middle_name' => $teacher->middle_name,
                'last_name' => $teacher->last_name,
                'full_name' => trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? '')),
                'gender' => $teacher->gender,
                'email' => $teacher->email,
                'phone_number' => $teacher->phone_number,
                'qualification' => $teacher->qualification,
                'specialization' => $teacher->specialization,
                'experience' => $teacher->experience,
                'date_of_birth' => $teacher->date_of_birth ? $teacher->date_of_birth->format('Y-m-d') : null,
                'address' => $teacher->address,
                'position' => $teacher->position,
                'image' => $teacher->image ? asset('userImages/' . $teacher->image) : ($teacher->gender == 'Female' ? asset('images/female.png') : asset('images/male.png')),
            ];

            return response()->json([
                'success' => true,
                'data' => $profileData,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating teacher profile API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change teacher password for API
     */
    public function changeTeacherPasswordAPI(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login first.'
                ], 401);
            }

            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found'
                ], 404);
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6',
                'confirm_password' => 'required|string|same:new_password',
            ], [
                'current_password.required' => 'Current password is required',
                'new_password.required' => 'New password is required',
                'new_password.min' => 'New password must be at least 6 characters',
                'confirm_password.required' => 'Password confirmation is required',
                'confirm_password.same' => 'Password confirmation does not match new password',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 422);
            }

            // Get user account
            $user = User::where('name', $teacher->employee_number)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User account not found'
                ], 404);
            }

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                    'errors' => ['current_password' => 'Current password is incorrect']
                ], 422);
            }

            // Check if new password is same as current
            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from current password',
                    'errors' => ['new_password' => 'New password must be different from current password']
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error changing teacher password API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher subjects for API (Flutter app)
     */
    public function getTeacherSubjectsAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
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

            // Get all class subjects assigned to this teacher with statistics
            $classSubjects = ClassSubject::with(['subject' => function($query) {
                    $query->where('status', 'Active');
                }, 'class', 'subclass.class'])
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->get()
                ->filter(function($classSubject) {
                    return $classSubject->subject && $classSubject->subject->status === 'Active';
                })
                ->map(function($classSubject) {
                    // Get total students for this class subject
                    $subclassID = $classSubject->subclassID;
                    $classID = $classSubject->classID;

                    $studentCount = 0;
                    if ($subclassID) {
                        $studentCount = Student::where('subclassID', $subclassID)
                            ->where('status', 'Active')
                            ->count();
                    } else {
                        $subclassIds = DB::table('subclasses')
                            ->where('classID', $classID)
                            ->pluck('subclassID')
                            ->toArray();
                        
                        if (!empty($subclassIds)) {
                            $studentCount = Student::whereIn('subclassID', $subclassIds)
                                ->where('status', 'Active')
                                ->count();
                        }
                    }

                    $className = 'N/A';
                    if ($classSubject->subclass) {
                        $className = ($classSubject->subclass->class->class_name ?? '') . ' - ' . ($classSubject->subclass->subclass_name ?? '');
                    } elseif ($classSubject->class) {
                        $className = ($classSubject->class->class_name ?? '') . ' - All Subclasses';
                    }

                    return [
                        'class_subject_id' => $classSubject->class_subjectID,
                        'subject_id' => $classSubject->subjectID,
                        'subject_name' => $classSubject->subject->subject_name ?? 'N/A',
                        'subject_code' => $classSubject->subject->subject_code ?? '',
                        'class_id' => $classID,
                        'subclass_id' => $subclassID,
                        'class_name' => $className,
                        'total_students' => $studentCount,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $classSubjects,
                'message' => 'Subjects retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting teacher subjects API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subjects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject students for API
     */
    public function getSubjectStudentsAPI(Request $request, $classSubjectID)
    {
        try {
            // Get authentication data from headers (stateless authentication)
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

            // Verify teacher owns this class subject
            $classSubject = ClassSubject::where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Get students based on subclass or class
            $subclassID = $classSubject->subclassID;
            $classID = $classSubject->classID;

            if ($subclassID) {
                $students = Student::with(['subclass.class', 'parent'])
                    ->where('subclassID', $subclassID)
                    ->where('status', 'Active')
                    ->get();
            } else {
                $subclassIds = DB::table('subclasses')
                    ->where('classID', $classID)
                    ->pluck('subclassID')
                    ->toArray();
                
                $students = Student::with(['subclass.class', 'parent'])
                    ->whereIn('subclassID', $subclassIds)
                    ->where('status', 'Active')
                    ->get();
            }

            $formattedStudents = $students->map(function($student) {
                $baseUrl = asset('');
                $photoUrl = $student->photo 
                    ? $baseUrl . 'userImages/' . $student->photo
                    : ($student->gender === 'Female' 
                        ? $baseUrl . 'images/female.png' 
                        : $baseUrl . 'images/male.png');

                return [
                    'student_id' => $student->studentID,
                    'admission_number' => $student->admission_number,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'full_name' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'gender' => $student->gender,
                    'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : null,
                    'photo' => $photoUrl,
                    'subclass_id' => $student->subclassID,
                    'subclass_name' => $student->subclass ? $student->subclass->subclass_name : null,
                    'class_name' => $student->subclass && $student->subclass->class ? $student->subclass->class->class_name : null,
                    'has_health_condition' => ($student->is_disabled == 1) || ($student->has_epilepsy == 1) || ($student->has_allergies == 1),
                    'is_disabled' => $student->is_disabled == 1,
                    'has_epilepsy' => $student->has_epilepsy == 1,
                    'has_allergies' => $student->has_allergies == 1,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'class_subject' => [
                        'class_subject_id' => $classSubject->class_subjectID,
                        'subject_id' => $classSubject->subjectID,
                        'subject_name' => $classSubject->subject->subject_name ?? 'N/A',
                        'subject_code' => $classSubject->subject->subject_code ?? '',
                    ],
                    'students' => $formattedStudents,
                    'total_students' => $formattedStudents->count()
                ],
                'message' => 'Students retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting subject students API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get examinations for subject for API
     */
    public function getExaminationsForSubjectAPI(Request $request, $classSubjectID)
    {
        try {
            // Get authentication data from headers (stateless authentication)
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

            // Verify teacher owns this class subject
            $classSubject = ClassSubject::where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Get examinations where enter_result = true only
            $examinations = Examination::where('schoolID', $schoolID)
                ->where('enter_result', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($exam) use ($schoolID) {
                    // Check if term is closed
                    $isTermClosed = false;
                    if ($exam->term && $exam->year) {
                        $termNumber = null;
                        if ($exam->term === 'first_term' || $exam->term === '1') {
                            $termNumber = 1;
                        } elseif ($exam->term === 'second_term' || $exam->term === '2') {
                            $termNumber = 2;
                        }
                        
                        if ($termNumber) {
                            $term = DB::table('terms')
                                ->where('schoolID', $schoolID)
                                ->where('year', $exam->year)
                                ->where('term_number', $termNumber)
                                ->where('status', 'Closed')
                                ->first();
                            $isTermClosed = $term ? true : false;
                        }
                    }

                    return [
                        'exam_id' => $exam->examID,
                        'exam_name' => $exam->exam_name,
                        'year' => $exam->year,
                        'status' => $exam->status,
                        'start_date' => $exam->start_date ? $exam->start_date->format('Y-m-d') : null,
                        'end_date' => $exam->end_date ? $exam->end_date->format('Y-m-d') : null,
                        'enter_result' => $exam->enter_result,
                        'exam_category' => $exam->exam_category,
                        'term' => $exam->term,
                        'is_term_closed' => $isTermClosed,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'class_subject' => [
                        'class_subject_id' => $classSubject->class_subjectID,
                        'subject_id' => $classSubject->subjectID,
                        'subject_name' => $classSubject->subject->subject_name ?? 'N/A',
                    ],
                    'examinations' => $examinations
                ],
                'message' => 'Examinations retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting examinations for subject API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve examinations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject results for API
     */
    public function getSubjectResultsAPI(Request $request, $classSubjectID, $examID = null)
    {
        try {
            // Get authentication data from headers (stateless authentication)
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

            // Verify teacher owns this class subject
            $classSubject = ClassSubject::with(['subject' => function($query) {
                    $query->where('status', 'Active');
                }, 'class', 'subclass'])
                ->where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            $query = Result::with(['student', 'examination'])
                ->where('class_subjectID', $classSubjectID);

            if ($examID) {
                $query->where('examID', $examID);
            }

            $results = $query->get();

            $baseUrl = asset('');
            $formattedResults = $results->map(function($result) use ($baseUrl) {
                $student = $result->student ?? null;
                $photoUrl = $student && $student->photo 
                    ? $baseUrl . 'userImages/' . $student->photo
                    : ($student && $student->gender === 'Female' 
                        ? $baseUrl . 'images/female.png' 
                        : $baseUrl . 'images/male.png');

                return [
                    'result_id' => $result->resultID,
                    'student_id' => $result->studentID,
                    'student_name' => $student ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) : 'N/A',
                    'admission_number' => $student ? $student->admission_number : 'N/A',
                    'photo' => $photoUrl,
                    'exam_id' => $result->examID,
                    'exam_name' => $result->examination ? $result->examination->exam_name : 'N/A',
                    'marks' => $result->marks,
                    'grade' => $result->grade,
                    'remark' => $result->remark,
                    'has_health_condition' => $student ? (($student->is_disabled == 1) || ($student->has_epilepsy == 1) || ($student->has_allergies == 1)) : false,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'class_subject' => [
                        'class_subject_id' => $classSubject->class_subjectID,
                        'subject_id' => $classSubject->subjectID,
                        'subject_name' => $classSubject->subject->subject_name ?? 'N/A',
                    ],
                    'results' => $formattedResults,
                    'total_results' => $formattedResults->count()
                ],
                'message' => 'Results retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting subject results API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve results',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save subject results for API
     */
    public function saveSubjectResultsAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
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

            $validator = Validator::make($request->all(), [
                'class_subject_id' => 'required|exists:class_subjects,class_subjectID',
                'exam_id' => 'required|exists:examinations,examID',
                'results' => 'required|array',
                'results.*.student_id' => 'required|exists:students,studentID',
                'results.*.marks' => 'nullable|numeric|min:0|max:100',
                'results.*.grade' => 'nullable|string|max:10',
                'results.*.remark' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify teacher owns this class subject
            $classSubject = ClassSubject::where('class_subjectID', $request->class_subject_id)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Check if enter_result is enabled
            $examination = Examination::find($request->exam_id);
            if (!$examination) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examination not found.'
                ], 404);
            }

            if (!$examination->enter_result) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to enter results for this examination. Result entry has been disabled.'
                ], 403);
            }

            // Check if term is closed
            if ($examination->term && $examination->year) {
                $termNumber = null;
                if ($examination->term === 'first_term' || $examination->term === '1') {
                    $termNumber = 1;
                } elseif ($examination->term === 'second_term' || $examination->term === '2') {
                    $termNumber = 2;
                }
                
                if ($termNumber) {
                    $term = DB::table('terms')
                        ->where('schoolID', $schoolID)
                        ->where('year', $examination->year)
                        ->where('term_number', $termNumber)
                        ->where('status', 'Closed')
                        ->first();

                    if ($term) {
                        // Check if this is an edit operation
                        $isEdit = false;
                        foreach ($request->results as $resultData) {
                            $existingResult = Result::where('studentID', $resultData['student_id'])
                                ->where('examID', $request->exam_id)
                                ->where('class_subjectID', $request->class_subject_id)
                                ->first();
                            if ($existingResult) {
                                $isEdit = true;
                                break;
                            }
                        }

                        if ($isEdit) {
                            return response()->json([
                                'success' => false,
                                'message' => 'You are not allowed to edit results for this term. The term has been closed.'
                            ], 403);
                        }
                    }
                }
            }

            DB::beginTransaction();

            $savedCount = 0;
            foreach ($request->results as $resultData) {
                $result = Result::where('studentID', $resultData['student_id'])
                    ->where('examID', $request->exam_id)
                    ->where('class_subjectID', $request->class_subject_id)
                    ->first();

                if ($result) {
                    $result->update([
                        'marks' => $resultData['marks'] ?? $result->marks,
                        'grade' => $resultData['grade'] ?? $result->grade,
                        'remark' => $resultData['remark'] ?? $result->remark,
                    ]);
                } else {
                    $student = Student::find($resultData['student_id']);
                    if ($student) {
                        Result::create([
                            'studentID' => $resultData['student_id'],
                            'examID' => $request->exam_id,
                            'class_subjectID' => $request->class_subject_id,
                            'subclassID' => $student->subclassID,
                            'marks' => $resultData['marks'] ?? null,
                            'grade' => $resultData['grade'] ?? null,
                            'remark' => $resultData['remark'] ?? null,
                        ]);
                    }
                }
                $savedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully saved {$savedCount} result(s)!",
                'data' => [
                    'saved_count' => $savedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving subject results API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save results',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload Excel results for API
     */
    public function uploadExcelResultsAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
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

            $validator = Validator::make($request->all(), [
                'class_subject_id' => 'required|exists:class_subjects,class_subjectID',
                'exam_id' => 'required|exists:examinations,examID',
                'excel_file' => 'required|mimes:xlsx,xls|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify teacher owns this class subject
            $classSubject = ClassSubject::where('class_subjectID', $request->class_subject_id)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Check examination
            $examination = Examination::find($request->exam_id);
            if (!$examination) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examination not found.'
                ], 404);
            }

            if ($examination->status !== 'awaiting_results') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to perform this action. Wait for Academic Permission.'
                ], 403);
            }

            // Check if PhpSpreadsheet is available
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                return response()->json([
                    'success' => false,
                    'message' => 'PhpSpreadsheet library is not installed.'
                ], 500);
            }

            // Load Excel file
            $file = $request->file('excel_file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            $results = [];
            $errors = [];
            $successCount = 0;

            // Get school type for grade calculation
            $school = School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $studentID = $worksheet->getCell('A' . $row)->getValue();
                $marks = $worksheet->getCell('D' . $row)->getValue();
                $grade = $worksheet->getCell('E' . $row)->getValue();
                $remark = $worksheet->getCell('F' . $row)->getValue();

                // Skip empty rows
                if (empty($studentID)) {
                    continue;
                }

                // Validate student exists
                $student = Student::find($studentID);
                if (!$student) {
                    $errors[] = "Row $row: Student ID $studentID not found.";
                    continue;
                }

                // Validate marks
                if ($marks !== null && $marks !== '') {
                    $marks = (float)$marks;
                    if ($marks < 0 || $marks > 100) {
                        $errors[] = "Row $row: Marks must be between 0 and 100.";
                        continue;
                    }

                    // Calculate grade if not provided
                    if (empty($grade) || is_numeric($grade)) {
                        $grade = $this->calculateGrade($marks, $schoolType);
                    }
                } else {
                    $marks = null;
                    $grade = null;
                }

                $results[] = [
                    'studentID' => $studentID,
                    'marks' => $marks,
                    'grade' => $grade,
                    'remark' => $remark ?: null,
                ];
            }

            if (empty($results)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid results found in the Excel file.'
                ], 422);
            }

            DB::beginTransaction();

            foreach ($results as $resultData) {
                $result = Result::where('studentID', $resultData['studentID'])
                    ->where('examID', $request->exam_id)
                    ->where('class_subjectID', $request->class_subject_id)
                    ->first();

                if ($result) {
                    $result->update([
                        'marks' => $resultData['marks'],
                        'grade' => $resultData['grade'],
                        'remark' => $resultData['remark'],
                    ]);
                } else {
                    $student = Student::find($resultData['studentID']);
                    if ($student) {
                        Result::create([
                            'studentID' => $resultData['studentID'],
                            'examID' => $request->exam_id,
                            'class_subjectID' => $request->class_subject_id,
                            'subclassID' => $student->subclassID,
                            'marks' => $resultData['marks'],
                            'grade' => $resultData['grade'],
                            'remark' => $resultData['remark'],
                        ]);
                    }
                }
                $successCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully uploaded {$successCount} result(s)!",
                'data' => [
                    'saved_count' => $successCount,
                    'errors' => $errors
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading Excel results API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload Excel results',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get graph data for dashboard
     */
    private function getDashboardGraphData($teacherID, $schoolID)
    {
        $graphData = [
            'sessions_by_day' => [],
            'subject_performance' => [],
            'classes_sessions' => []
        ];
        
        if (!$teacherID || !$schoolID) {
            return $graphData;
        }
        
        try {
            // Get active session timetable definition
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();
            
            if (!$definition) {
                return $graphData;
            }
            
            // Graph 1: Sessions per week by day (from approved tasks)
            $approvedTasks = SessionTask::where('teacherID', $teacherID)
                ->where('status', 'approved')
                ->whereHas('sessionTimetable', function($query) use ($definition) {
                    $query->where('definitionID', $definition->definitionID);
                })
                ->with('sessionTimetable')
                ->get();
            
            $sessionsByDay = [
                'Monday' => 0,
                'Tuesday' => 0,
                'Wednesday' => 0,
                'Thursday' => 0,
                'Friday' => 0
            ];
            
            foreach ($approvedTasks as $task) {
                $day = $task->sessionTimetable->day ?? null;
                if ($day && isset($sessionsByDay[$day])) {
                    $sessionsByDay[$day]++;
                }
            }
            
            $graphData['sessions_by_day'] = $sessionsByDay;
            
            // Graph 2: Subject performance (pass/fail rates) - for classes teacher teaches
            $classSubjects = ClassSubject::where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->with(['subject', 'subclass.class'])
                ->get();
            
            $subjectPerformance = [];
            $processedSubjects = [];
            
            foreach ($classSubjects as $classSubject) {
                $subjectID = $classSubject->subjectID;
                $subjectName = $classSubject->subject->subject_name ?? 'N/A';
                
                if (!isset($processedSubjects[$subjectID])) {
                    $processedSubjects[$subjectID] = true;
                    
                    // Get results for this subject in classes teacher teaches
                    $subclassIDs = $classSubjects->where('subjectID', $subjectID)->pluck('subclassID')->unique();
                    $classSubjectIDs = $classSubjects->where('subjectID', $subjectID)->pluck('class_subjectID')->unique();
                    
                    $results = DB::table('results')
                        ->join('examinations', 'results.examID', '=', 'examinations.examID')
                        ->join('students', 'results.studentID', '=', 'students.studentID')
                        ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                        ->where('class_subjects.subjectID', $subjectID)
                        ->where('examinations.schoolID', $schoolID)
                        ->whereIn('students.subclassID', $subclassIDs)
                        ->where('examinations.approval_status', 'Approved')
                        ->whereNotNull('results.marks')
                        ->where('results.status', 'allowed')
                        ->select('results.marks', 'results.grade')
                        ->get();
                    
                    $totalResults = $results->count();
                    $passedCount = $results->where('grade', '!=', 'F')->count();
                    $failedCount = $results->where('grade', 'F')->count();
                    $averageMarks = $totalResults > 0 ? $results->avg('marks') : 0;
                    
                    if ($totalResults > 0) {
                        $subjectPerformance[] = [
                            'subject_name' => $subjectName,
                            'total_students' => $totalResults,
                            'passed' => $passedCount,
                            'failed' => $failedCount,
                            'pass_rate' => round(($passedCount / $totalResults) * 100, 1),
                            'fail_rate' => round(($failedCount / $totalResults) * 100, 1),
                            'average_marks' => round($averageMarks, 1)
                        ];
                    }
                }
            }
            
            // Sort by pass rate (highest to lowest)
            usort($subjectPerformance, function($a, $b) {
                return $b['pass_rate'] <=> $a['pass_rate'];
            });
            
            $graphData['subject_performance'] = array_values($subjectPerformance);
            
            // Graph 3: Classes with most sessions
            $classSessions = [];
            foreach ($classSubjects as $classSubject) {
                $className = ($classSubject->subclass->class->class_name ?? '') . ' - ' . ($classSubject->subclass->subclass_name ?? '');
                
                if (!isset($classSessions[$className])) {
                    $sessionCount = SessionTask::where('teacherID', $teacherID)
                        ->where('status', 'approved')
                        ->whereHas('sessionTimetable', function($query) use ($classSubject, $definition) {
                            $query->where('definitionID', $definition->definitionID)
                                  ->where('class_subjectID', $classSubject->class_subjectID);
                        })
                        ->count();
                    
                    $classSessions[$className] = $sessionCount;
                }
            }
            
            // Sort by session count (highest to lowest)
            arsort($classSessions);
            $graphData['classes_sessions'] = $classSessions;
            
        } catch (\Exception $e) {
            Log::error('Error getting dashboard graph data: ' . $e->getMessage());
        }
        
        return $graphData;
    }
    
    /**
     * View approval chain status for an exam (without results)
     */
    public function viewApprovalChain($examID)
    {
        $user = Session::get('user_type');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }
        
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');
        
        if (!$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Teacher ID or School ID not found');
        }
        
        // Get examination
        $examination = Examination::where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->first();
        
        if (!$examination) {
            return redirect()->route('teachersDashboard')->with('error', 'Examination not found');
        }
        
        // Get all approvals for this exam
        $approvals = ResultApproval::with(['role', 'approver', 'classTeacherApprovals.subclass.class', 'coordinatorApprovals.mainclass'])
            ->where('examID', $examID)
            ->orderBy('approval_order')
            ->get();
        
        // Get teacher's roles
        $teacherRoles = DB::table('role_user')
            ->where('teacher_id', $teacherID)
            ->pluck('role_id')
            ->toArray();
        
        // Get teacher's subclasses
        $teacherSubclasses = DB::table('subclasses')
            ->where('teacherID', $teacherID)
            ->pluck('subclassID')
            ->toArray();
        
        // Get teacher's main classes
        $teacherMainClasses = DB::table('classes')
            ->where('teacherID', $teacherID)
            ->where('schoolID', $schoolID)
            ->pluck('classID')
            ->toArray();
        
        // Build approval chain
        $chain = [];
        foreach ($approvals as $approval) {
            $roleName = 'N/A';
            if ($approval->role_id) {
                $role = DB::table('roles')->where('id', $approval->role_id)->first();
                $roleName = $role->name ?? $role->role_name ?? 'N/A';
            } elseif ($approval->special_role_type === 'class_teacher') {
                $roleName = 'Class Teacher';
            } elseif ($approval->special_role_type === 'coordinator') {
                $roleName = 'Coordinator';
            }
            
            $isTeacherStep = false;
            if ($approval->role_id && in_array($approval->role_id, $teacherRoles)) {
                $isTeacherStep = true;
            } elseif ($approval->special_role_type === 'class_teacher' && !empty($teacherSubclasses)) {
                $isTeacherStep = ClassTeacherApproval::where('result_approvalID', $approval->result_approvalID)
                    ->whereIn('subclassID', $teacherSubclasses)
                    ->exists();
            } elseif ($approval->special_role_type === 'coordinator' && !empty($teacherMainClasses)) {
                $isTeacherStep = CoordinatorApproval::where('result_approvalID', $approval->result_approvalID)
                    ->whereIn('mainclassID', $teacherMainClasses)
                    ->exists();
            }
            
            $chain[] = [
                'result_approvalID' => $approval->result_approvalID,
                'approval_order' => $approval->approval_order,
                'role_name' => $roleName,
                'status' => $approval->status,
                'special_role_type' => $approval->special_role_type,
                'is_teacher_step' => $isTeacherStep,
                'approver' => $approval->approver ? [
                    'name' => $approval->approver->first_name . ' ' . $approval->approver->last_name,
                    'phone' => $approval->approver->phone ?? null,
                ] : null,
                'approved_at' => $approval->approved_at,
                'rejection_reason' => $approval->rejection_reason,
            ];
        }
        
        return view('Teacher.view_approval_chain', compact('examination', 'chain', 'teacherID'));
    }
    
    /**
     * Get class teacher approvals details for an exam
     */
    public function getClassTeacherApprovals($examID)
    {
        $user = Session::get('user_type');
        if (!$user) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
        
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');
        
        if (!$teacherID || !$schoolID) {
            return response()->json(['error' => 'Teacher ID or School ID not found'], 401);
        }
        
        // Get examination
        $examination = Examination::where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->first();
        
        if (!$examination) {
            return response()->json(['error' => 'Examination not found'], 404);
        }
        
        // Get class teacher approval for this exam
        $classTeacherApproval = ResultApproval::where('examID', $examID)
            ->where('special_role_type', 'class_teacher')
            ->first();
        
        if (!$classTeacherApproval) {
            return response()->json(['error' => 'Class teacher approval not found for this exam'], 404);
        }
        
        // Get all class teacher approvals with subclass and teacher details
        $classTeacherApprovals = ClassTeacherApproval::with([
            'subclass.class',
            'approver'
        ])
            ->where('result_approvalID', $classTeacherApproval->result_approvalID)
            ->get();
        
        $details = [];
        foreach ($classTeacherApprovals as $cta) {
            $subclass = $cta->subclass;
            $class = $subclass ? $subclass->class : null;
            
            // Get class teacher for this subclass
            $classTeacher = null;
            if ($subclass && $subclass->teacherID) {
                $classTeacher = Teacher::find($subclass->teacherID);
            }
            
            $details[] = [
                'subclassID' => $cta->subclassID,
                'subclass_name' => $subclass ? $subclass->subclass_name : 'N/A',
                'class_name' => $class ? $class->class_name : 'N/A',
                'class_teacher' => $classTeacher ? [
                    'name' => $classTeacher->first_name . ' ' . $classTeacher->last_name,
                    'phone' => $classTeacher->phone_number ?? $classTeacher->phone ?? null,
                    'email' => $classTeacher->email ?? null,
                ] : null,
                'status' => $cta->status,
                'approved_by' => $cta->approver ? [
                    'name' => $cta->approver->first_name . ' ' . $cta->approver->last_name,
                    'phone' => $cta->approver->phone_number ?? $cta->approver->phone ?? null,
                ] : null,
                'approved_at' => $cta->approved_at,
                'rejection_reason' => $cta->rejection_reason,
            ];
        }
        
        return response()->json([
            'success' => true,
            'exam_name' => $examination->exam_name,
            'details' => $details,
        ]);
    }
    
    /**
     * Get coordinator approvals details for an exam
     */
    public function getCoordinatorApprovals($examID)
    {
        $user = Session::get('user_type');
        if (!$user) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
        
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');
        
        if (!$teacherID || !$schoolID) {
            return response()->json(['error' => 'Teacher ID or School ID not found'], 401);
        }
        
        // Get examination
        $examination = Examination::where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->first();
        
        if (!$examination) {
            return response()->json(['error' => 'Examination not found'], 404);
        }
        
        // Get coordinator approval for this exam
        $coordinatorApproval = ResultApproval::where('examID', $examID)
            ->where('special_role_type', 'coordinator')
            ->first();
        
        if (!$coordinatorApproval) {
            return response()->json(['error' => 'Coordinator approval not found for this exam'], 404);
        }
        
        // Get all coordinator approvals with mainclass and coordinator details
        $coordinatorApprovals = CoordinatorApproval::with([
            'mainclass',
            'approver'
        ])
            ->where('result_approvalID', $coordinatorApproval->result_approvalID)
            ->get();
        
        $details = [];
        foreach ($coordinatorApprovals as $ca) {
            $mainclass = $ca->mainclass;
            
            // Get coordinator for this mainclass
            $coordinator = null;
            if ($mainclass && $mainclass->teacherID) {
                $coordinator = Teacher::find($mainclass->teacherID);
            }
            
            $details[] = [
                'mainclassID' => $ca->mainclassID,
                'class_name' => $mainclass ? $mainclass->class_name : 'N/A',
                'coordinator' => $coordinator ? [
                    'name' => $coordinator->first_name . ' ' . $coordinator->last_name,
                    'phone' => $coordinator->phone_number ?? $coordinator->phone ?? null,
                    'email' => $coordinator->email ?? null,
                ] : null,
                'status' => $ca->status,
                'approved_by' => $ca->approver ? [
                    'name' => $ca->approver->first_name . ' ' . $ca->approver->last_name,
                    'phone' => $ca->approver->phone_number ?? $ca->approver->phone ?? null,
                ] : null,
                'approved_at' => $ca->approved_at,
                'rejection_reason' => $ca->rejection_reason,
            ];
        }
        
        return response()->json([
            'success' => true,
            'exam_name' => $examination->exam_name,
            'details' => $details,
        ]);
    }

    public function teacherSubjects(){
           $user = Session::get('user_type');

        if (!$user)
        {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');

        if (!$teacherID) {
            return redirect()->route('login')->with('error', 'Teacher ID not found');
        }

        // Get all class subjects assigned to this teacher with statistics
        // Only show subjects where both ClassSubject and SchoolSubject have status = Active
        $classSubjects = ClassSubject::with(['subject' => function($query) {
                $query->where('status', 'Active');
            }, 'class', 'subclass.class'])
            ->where('teacherID', $teacherID)
            ->where('status', 'Active')
            ->whereHas('subject', function($query) {
                $query->where('status', 'Active');
            })
            ->get()
            ->filter(function($classSubject) {
                // Additional filter: ensure subject relationship exists and has Active status
                return $classSubject->subject && $classSubject->subject->status === 'Active';
            })
            ->map(function($classSubject) {
                // Get total students for this class subject
                // Students who take this subject (based on subclass or class)
                $subclassID = $classSubject->subclassID;
                $classID = $classSubject->classID;

                $studentCount = 0;
                if ($subclassID) {
                    // Subject is assigned to specific subclass
                    $studentCount = Student::where('subclassID', $subclassID)
                        ->where('status', 'Active')
                        ->count();
                } else {
                    // Subject is assigned to whole class - get all students in all subclasses of this class
                    $subclassIds = DB::table('subclasses')
                        ->where('classID', $classID)
                        ->pluck('subclassID')
                        ->toArray();
                    
                    if (!empty($subclassIds)) {
                        $studentCount = Student::whereIn('subclassID', $subclassIds)
                            ->where('status', 'Active')
                            ->count();
                    }
                }

                $classSubject->total_students = $studentCount;
                return $classSubject;
            })
            ->filter(function($classSubject) {
                // Final filter: ensure subject still exists and is Active after mapping
                return $classSubject->subject && $classSubject->subject->status === 'Active';
            })
            ->values(); // Re-index array after filtering

        return view('Teacher.teacher_subjects', compact('classSubjects'));
    }

    public function schemeOfWork()
    {
        $user = Session::get('user_type');

        if (!$user)
        {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');

        if (!$teacherID) {
            return redirect()->route('login')->with('error', 'Teacher ID not found');
        }

        // Get all class subjects assigned to this teacher
        // Only show subjects where both ClassSubject and SchoolSubject have status = Active
        $teacherSubjects = ClassSubject::with(['subject' => function($query) {
                $query->where('status', 'Active');
            }, 'class', 'subclass.class'])
            ->where('teacherID', $teacherID)
            ->where('status', 'Active')
            ->whereHas('subject', function($query) {
                $query->where('status', 'Active');
            })
            ->get()
            ->filter(function($classSubject) {
                // Additional filter: ensure subject relationship exists and has Active status
                return $classSubject->subject && $classSubject->subject->status === 'Active';
            })
            ->values(); // Re-index array after filtering

        return view('Teacher.scheme_of_work', compact('teacherSubjects'));
    }

    public function checkExistingScheme(Request $request)
    {
        $teacherID = Session::get('teacherID');
        $classSubjectID = $request->input('class_subjectID');
        $year = $request->input('year', date('Y'));

        if (!$teacherID || !$classSubjectID) {
            return response()->json([
                'exists' => false,
                'message' => 'Invalid parameters'
            ]);
        }

        $existingScheme = SchemeOfWork::where('class_subjectID', $classSubjectID)
            ->where('year', $year)
            ->first();

        return response()->json([
            'exists' => $existingScheme !== null,
            'scheme' => $existingScheme ? [
                'id' => $existingScheme->scheme_of_workID,
                'status' => $existingScheme->status,
                'created_by' => $existingScheme->created_by
            ] : null
        ]);
    }

    public function createNewScheme($classSubjectID)
    {
        $user = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$user || !$teacherID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        // Get class subject details
        $classSubject = ClassSubject::with(['subject', 'class', 'subclass.class'])
            ->where('class_subjectID', $classSubjectID)
            ->where('teacherID', $teacherID)
            ->where('status', 'Active')
            ->first();

        if (!$classSubject) {
            return redirect()->route('teacher.schemeOfWork')->with('error', 'Subject not found or you are not assigned to teach it');
        }

        // Check if scheme already exists for current year
        $currentYear = date('Y');
        $existingScheme = SchemeOfWork::where('class_subjectID', $classSubjectID)
            ->where('year', $currentYear)
            ->first();

        if ($existingScheme) {
            return redirect()->route('teacher.schemeOfWork')->with('error', 'Scheme of work already exists for this subject and year. Please use "Manage" to edit it.');
        }

        // Get holidays for the year (including those that span across years)
        $holidays = Holiday::where('schoolID', $schoolID)
            ->where(function($query) use ($currentYear) {
                $query->whereYear('start_date', $currentYear)
                      ->orWhereYear('end_date', $currentYear)
                      ->orWhere(function($q) use ($currentYear) {
                          $q->whereYear('start_date', '<', $currentYear)
                            ->whereYear('end_date', '>', $currentYear);
                      });
            })
            ->get()
            ->map(function($holiday) use ($currentYear) {
                // Format dates properly
                $startDate = \Carbon\Carbon::parse($holiday->start_date);
                $endDate = \Carbon\Carbon::parse($holiday->end_date);
                
                return [
                    'name' => $holiday->holiday_name,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'start_month' => $startDate->format('F'),
                    'end_month' => $endDate->format('F'),
                    'start_day' => $startDate->format('j'),
                    'end_day' => $endDate->format('j'),
                    'type' => $holiday->type
                ];
            });

        // Get non-working events and format them as holidays
        $nonWorkingEvents = Event::where('schoolID', $schoolID)
            ->whereYear('event_date', $currentYear)
            ->where('is_non_working_day', true)
            ->get()
            ->map(function($event) {
                $eventDate = \Carbon\Carbon::parse($event->event_date);
                return [
                    'name' => $event->event_name,
                    'start_date' => $eventDate->format('Y-m-d'),
                    'end_date' => $eventDate->format('Y-m-d'),
                    'start_month' => $eventDate->format('F'),
                    'end_month' => $eventDate->format('F'),
                    'start_day' => $eventDate->format('j'),
                    'end_day' => $eventDate->format('j'),
                    'type' => $event->type
                ];
            });
        
        // Merge holidays and non-working events
        $allHolidays = $holidays->merge($nonWorkingEvents);

        return view('Teacher.create_scheme_of_work', compact('classSubject', 'currentYear', 'holidays', 'nonWorkingEvents', 'allHolidays'));
    }

    public function storeNewScheme(Request $request)
    {
        $teacherID = Session::get('teacherID');
        $classSubjectID = $request->input('class_subjectID');
        $year = $request->input('year', date('Y'));

        if (!$teacherID || !$classSubjectID) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters'
            ], 400);
        }

        // Validate class subject belongs to teacher
        $classSubject = ClassSubject::where('class_subjectID', $classSubjectID)
            ->where('teacherID', $teacherID)
            ->where('status', 'Active')
            ->first();

        if (!$classSubject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found or unauthorized'
            ], 403);
        }

        // Check if scheme already exists
        $existingScheme = SchemeOfWork::where('class_subjectID', $classSubjectID)
            ->where('year', $year)
            ->first();

        if ($existingScheme) {
            return response()->json([
                'success' => false,
                'message' => 'Scheme of work already exists for this subject and year'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create scheme of work
            $scheme = SchemeOfWork::create([
                'class_subjectID' => $classSubjectID,
                'year' => $year,
                'status' => 'Draft',
                'created_by' => $teacherID
            ]);

            // Store learning objectives
            $objectives = $request->input('learning_objectives', []);
            foreach ($objectives as $index => $objective) {
                if (!empty(trim($objective))) {
                    SchemeOfWorkLearningObjective::create([
                        'scheme_of_workID' => $scheme->scheme_of_workID,
                        'objective_text' => trim($objective),
                        'order' => $index
                    ]);
                }
            }

            // Store scheme items
            $items = $request->input('items', []);
            foreach ($items as $item) {
                if (!empty($item['month'])) {
                    SchemeOfWorkItem::create([
                        'scheme_of_workID' => $scheme->scheme_of_workID,
                        'month' => $item['month'],
                        'main_competence' => $item['main_competence'] ?? '',
                        'specific_competences' => $item['specific_competences'] ?? '',
                        'learning_activities' => $item['learning_activities'] ?? '',
                        'specific_activities' => $item['specific_activities'] ?? '',
                        'week' => !empty($item['week']) ? $item['week'] : null,
                        'number_of_periods' => !empty($item['number_of_periods']) ? (int)$item['number_of_periods'] : 0,
                        'teaching_methods' => $item['teaching_methods'] ?? '',
                        'teaching_resources' => $item['teaching_resources'] ?? '',
                        'assessment_tools' => $item['assessment_tools'] ?? '',
                        'references' => $item['references'] ?? '',
                        'remarks' => (isset($item['is_done']) && $item['is_done']) ? 'done' : (isset($item['remark']) && !empty($item['remark']) ? $item['remark'] : ''),
                        'row_order' => $item['row_order'] ?? 0
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Scheme of work created successfully',
                'scheme_id' => $scheme->scheme_of_workID,
                'redirect' => route('teacher.schemeOfWork')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating scheme of work: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create scheme of work: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewSchemeOfWork($schemeOfWorkID)
    {
        $user = Session::get('user_type');
        $teacherID = Session::get('teacherID');

        if (!$user || !$teacherID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
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
            return redirect()->route('teacher.schemeOfWork')->with('error', 'Scheme of work not found');
        }

        // Verify teacher has access (either created it or is assigned to the subject)
        $classSubject = $scheme->classSubject;
        if ($classSubject->teacherID != $teacherID && $scheme->created_by != $teacherID) {
            return redirect()->route('teacher.schemeOfWork')->with('error', 'You do not have access to this scheme of work');
        }

        // Get school info
        $school = School::where('schoolID', Session::get('schoolID'))->first();

        return view('Teacher.view_scheme_of_work', compact('scheme', 'school'));
    }

    public function manageSchemeOfWork($schemeOfWorkID)
    {
        $user = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$user || !$teacherID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
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
            return redirect()->route('teacher.schemeOfWork')->with('error', 'Scheme of work not found');
        }

        // Verify teacher has access
        $classSubject = $scheme->classSubject;
        if ($classSubject->teacherID != $teacherID && $scheme->created_by != $teacherID) {
            return redirect()->route('teacher.schemeOfWork')->with('error', 'You do not have access to this scheme of work');
        }

        // Get holidays for the year
        $currentYear = $scheme->year;
        $holidays = Holiday::where('schoolID', $schoolID)
            ->where(function($query) use ($currentYear) {
                $query->whereYear('start_date', $currentYear)
                      ->orWhereYear('end_date', $currentYear);
            })
            ->get()
            ->map(function($holiday) use ($currentYear) {
                $startDate = \Carbon\Carbon::parse($holiday->start_date);
                $endDate = \Carbon\Carbon::parse($holiday->end_date);
                
                return [
                    'name' => $holiday->holiday_name,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'start_month' => $startDate->format('F'),
                    'end_month' => $endDate->format('F'),
                    'start_day' => $startDate->format('j'),
                    'end_day' => $endDate->format('j'),
                    'type' => $holiday->type
                ];
            });

        $nonWorkingEvents = Event::where('schoolID', $schoolID)
            ->whereYear('event_date', $currentYear)
            ->where('is_non_working_day', true)
            ->get()
            ->map(function($event) {
                $eventDate = \Carbon\Carbon::parse($event->event_date);
                return [
                    'name' => $event->event_name,
                    'start_date' => $eventDate->format('Y-m-d'),
                    'end_date' => $eventDate->format('Y-m-d'),
                    'start_month' => $eventDate->format('F'),
                    'end_month' => $eventDate->format('F'),
                    'start_day' => $eventDate->format('j'),
                    'end_day' => $eventDate->format('j'),
                    'type' => $event->type
                ];
            });
        
        $allHolidays = $holidays->merge($nonWorkingEvents);

        return view('Teacher.manage_scheme_of_work', compact('scheme', 'allHolidays', 'currentYear'));
    }

    public function updateSchemeOfWork(Request $request, $schemeOfWorkID)
    {
        $teacherID = Session::get('teacherID');

        if (!$teacherID) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $scheme = SchemeOfWork::find($schemeOfWorkID);
        if (!$scheme) {
            return response()->json([
                'success' => false,
                'message' => 'Scheme of work not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Update learning objectives
            if ($request->has('learning_objectives')) {
                // Delete existing objectives
                SchemeOfWorkLearningObjective::where('scheme_of_workID', $schemeOfWorkID)->delete();
                
                // Add new objectives
                $objectives = $request->input('learning_objectives', []);
                foreach ($objectives as $index => $objective) {
                    if (!empty(trim($objective))) {
                        SchemeOfWorkLearningObjective::create([
                            'scheme_of_workID' => $schemeOfWorkID,
                            'objective_text' => trim($objective),
                            'order' => $index
                        ]);
                    }
                }
            }

            // Update scheme items
            if ($request->has('items')) {
                // Get existing items to update or delete
                $existingItems = SchemeOfWorkItem::where('scheme_of_workID', $schemeOfWorkID)->get()->keyBy('itemID');
                
                // Process items from request
                $items = $request->input('items', []);
                $processedItemIds = [];
                
                foreach ($items as $itemData) {
                    // Check if this is an existing item (has itemID) or new item
                    if (isset($itemData['itemID']) && is_numeric($itemData['itemID']) && $existingItems->has($itemData['itemID'])) {
                        // Update existing item
                        $itemId = $itemData['itemID'];
                        $existingItem = $existingItems->get($itemId);
                        $existingItem->update([
                            'month' => $itemData['month'] ?? $existingItem->month,
                            'main_competence' => $itemData['main_competence'] ?? '',
                            'specific_competences' => $itemData['specific_competences'] ?? '',
                            'learning_activities' => $itemData['learning_activities'] ?? '',
                            'specific_activities' => $itemData['specific_activities'] ?? '',
                            'week' => !empty($itemData['week']) ? $itemData['week'] : null,
                            'number_of_periods' => !empty($itemData['number_of_periods']) ? (int)$itemData['number_of_periods'] : 0,
                            'teaching_methods' => $itemData['teaching_methods'] ?? '',
                            'teaching_resources' => $itemData['teaching_resources'] ?? '',
                            'assessment_tools' => $itemData['assessment_tools'] ?? '',
                            'references' => $itemData['references'] ?? '',
                            'remarks' => (isset($itemData['is_done']) && $itemData['is_done']) ? 'done' : (isset($itemData['remark']) && !empty($itemData['remark']) ? $itemData['remark'] : ''),
                            'row_order' => $existingItem->row_order
                        ]);
                        $processedItemIds[] = $itemId;
                    } else {
                        // Create new item
                        if (!empty($itemData['month'])) {
                            // Get max row_order for this month
                            $maxOrder = SchemeOfWorkItem::where('scheme_of_workID', $schemeOfWorkID)
                                ->where('month', $itemData['month'])
                                ->max('row_order') ?? 0;
                            
                            SchemeOfWorkItem::create([
                                'scheme_of_workID' => $schemeOfWorkID,
                                'month' => $itemData['month'],
                                'main_competence' => $itemData['main_competence'] ?? '',
                                'specific_competences' => $itemData['specific_competences'] ?? '',
                                'learning_activities' => $itemData['learning_activities'] ?? '',
                                'specific_activities' => $itemData['specific_activities'] ?? '',
                                'week' => !empty($itemData['week']) ? $itemData['week'] : null,
                                'number_of_periods' => !empty($itemData['number_of_periods']) ? (int)$itemData['number_of_periods'] : 0,
                                'teaching_methods' => $itemData['teaching_methods'] ?? '',
                                'teaching_resources' => $itemData['teaching_resources'] ?? '',
                                'assessment_tools' => $itemData['assessment_tools'] ?? '',
                                'references' => $itemData['references'] ?? '',
                                'remarks' => (isset($itemData['is_done']) && $itemData['is_done']) ? 'done' : (isset($itemData['remark']) && !empty($itemData['remark']) ? $itemData['remark'] : ''),
                                'row_order' => $maxOrder + 1
                            ]);
                        }
                    }
                }
                
                // Delete items that were not in the request (removed by user)
                $itemsToDelete = $existingItems->keys()->diff($processedItemIds);
                if ($itemsToDelete->count() > 0) {
                    SchemeOfWorkItem::whereIn('itemID', $itemsToDelete)->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Scheme of work updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating scheme of work: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update scheme of work: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSchemeOfWorkRemark(Request $request, $schemeOfWorkID)
    {
        $teacherID = Session::get('teacherID');

        if (!$teacherID) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $scheme = SchemeOfWork::find($schemeOfWorkID);
        if (!$scheme) {
            return response()->json([
                'success' => false,
                'message' => 'Scheme of work not found'
            ], 404);
        }

        // Verify teacher has access
        $classSubject = $scheme->classSubject;
        if ($classSubject->teacherID != $teacherID && $scheme->created_by != $teacherID) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this scheme of work'
            ], 403);
        }

        $itemId = $request->input('item_id');
        $remark = $request->input('remark', '');

        try {
            // Check if item exists
            if (is_numeric($itemId)) {
                $item = SchemeOfWorkItem::where('itemID', $itemId)
                    ->where('scheme_of_workID', $schemeOfWorkID)
                    ->first();

                if ($item) {
                    $item->update([
                        'remarks' => $remark
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Remarks updated successfully'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Item not found'
                    ], 404);
                }
            } else {
                // New item - can't update remark for new items that don't exist yet
                return response()->json([
                    'success' => false,
                    'message' => 'Please save the scheme first before updating remarks'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error updating remark: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update remarks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function useExistingSchemes($classSubjectID)
    {
        $user = Session::get('user_type');
        $teacherID = Session::get('teacherID');

        if (!$user || !$teacherID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        // Get class subject
        $classSubject = ClassSubject::with(['subject', 'class', 'subclass.class'])
            ->where('class_subjectID', $classSubjectID)
            ->where('teacherID', $teacherID)
            ->where('status', 'Active')
            ->first();

        if (!$classSubject) {
            return redirect()->route('teacher.schemeOfWork')->with('error', 'Subject not found or you are not assigned to teach it');
        }

        // Get all existing schemes for this subject (across all years and class subjects with same subject)
        $subjectID = $classSubject->subjectID;
        
        // Get all schemes for this subject (from any class subject with same subjectID)
        // Include schemes from scheme_of_works table (current, past, and archived schemes)
        $existingSchemes = SchemeOfWork::with(['classSubject.subject', 'classSubject.class', 'classSubject.subclass.class', 'createdBy', 'items', 'learningObjectives'])
            ->whereHas('classSubject', function($query) use ($subjectID) {
                $query->where('subjectID', $subjectID);
            })
            ->orderBy('year', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Also get schemes from history table for closed academic years (same subject)
        // Join with scheme_of_works table using original_scheme_of_workID
        // Schemes are marked as Archived (not deleted) so they can still be accessed
        $historySchemes = DB::table('scheme_of_works_history')
            ->join('class_subjects', 'scheme_of_works_history.original_class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('scheme_of_works', 'scheme_of_works_history.original_scheme_of_workID', '=', 'scheme_of_works.scheme_of_workID')
            ->where('class_subjects.subjectID', $subjectID)
            ->whereNotNull('scheme_of_works.scheme_of_workID')
            ->select('scheme_of_works.scheme_of_workID')
            ->distinct()
            ->pluck('scheme_of_workID')
            ->toArray();
        
        // Get schemes from history that are not already in existing schemes
        $historySchemeModels = SchemeOfWork::with(['classSubject.subject', 'classSubject.class', 'classSubject.subclass.class', 'createdBy', 'items', 'learningObjectives'])
            ->whereIn('scheme_of_workID', $historySchemes)
            ->whereNotIn('scheme_of_workID', $existingSchemes->pluck('scheme_of_workID')->toArray())
            ->orderBy('year', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Merge existing schemes with history schemes
        $allSchemes = $existingSchemes->merge($historySchemeModels)
            ->sortByDesc('year')
            ->sortByDesc('created_at')
            ->values();

        return view('Teacher.use_existing_schemes', compact('classSubject', 'existingSchemes', 'allSchemes'));
    }

    public function useThisScheme(Request $request, $schemeOfWorkID)
    {
        $teacherID = Session::get('teacherID');
        $classSubjectID = $request->input('class_subjectID');

        if (!$teacherID || !$classSubjectID) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters'
            ], 400);
        }

        // Check if class subject already has a scheme for current year
        $currentYear = date('Y');
        $existingScheme = SchemeOfWork::where('class_subjectID', $classSubjectID)
            ->where('year', $currentYear)
            ->first();

        if ($existingScheme) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a scheme of work for this subject and year. Please go to "Manage" to edit it.',
                'manageUrl' => route('teacher.manageSchemeOfWork', $existingScheme->scheme_of_workID)
            ], 400);
        }

        // Get the scheme to copy
        $sourceScheme = SchemeOfWork::with(['items', 'learningObjectives'])
            ->where('scheme_of_workID', $schemeOfWorkID)
            ->first();

        if (!$sourceScheme) {
            return response()->json([
                'success' => false,
                'message' => 'Scheme of work not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Create new scheme for this class subject
            $newScheme = SchemeOfWork::create([
                'class_subjectID' => $classSubjectID,
                'year' => $currentYear,
                'status' => 'Draft',
                'created_by' => $teacherID
            ]);

            // Copy learning objectives
            foreach ($sourceScheme->learningObjectives as $objective) {
                SchemeOfWorkLearningObjective::create([
                    'scheme_of_workID' => $newScheme->scheme_of_workID,
                    'objective_text' => $objective->objective_text,
                    'order' => $objective->order
                ]);
            }

            // Copy scheme items
            foreach ($sourceScheme->items as $item) {
                SchemeOfWorkItem::create([
                    'scheme_of_workID' => $newScheme->scheme_of_workID,
                    'month' => $item->month,
                    'main_competence' => $item->main_competence,
                    'specific_competences' => $item->specific_competences,
                    'learning_activities' => $item->learning_activities,
                    'specific_activities' => $item->specific_activities,
                    'week' => $item->week,
                    'number_of_periods' => $item->number_of_periods,
                    'teaching_methods' => $item->teaching_methods,
                    'teaching_resources' => $item->teaching_resources,
                    'assessment_tools' => $item->assessment_tools,
                    'references' => $item->references,
                    'remarks' => '',
                    'row_order' => $item->row_order
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Scheme of work copied successfully!',
                'redirect' => route('teacher.manageSchemeOfWork', $newScheme->scheme_of_workID)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error using existing scheme: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to use scheme: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteSchemeOfWork($schemeOfWorkID)
    {
        $teacherID = Session::get('teacherID');

        if (!$teacherID) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $scheme = SchemeOfWork::find($schemeOfWorkID);
        if (!$scheme) {
            return response()->json([
                'success' => false,
                'message' => 'Scheme of work not found'
            ], 404);
        }

        // Verify teacher has access
        $classSubject = $scheme->classSubject;
        if ($classSubject->teacherID != $teacherID && $scheme->created_by != $teacherID) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this scheme of work'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Delete related data (cascade should handle this, but being explicit)
            SchemeOfWorkLearningObjective::where('scheme_of_workID', $schemeOfWorkID)->delete();
            SchemeOfWorkItem::where('scheme_of_workID', $schemeOfWorkID)->delete();

            // Delete scheme
            $scheme->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Scheme of work deleted successfully!',
                'redirect' => route('teacher.schemeOfWork')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting scheme of work: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete scheme: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportSchemeOfWorkPDF($schemeOfWorkID)
    {
        $teacherID = Session::get('teacherID');
        
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
            return redirect()->back()->with('error', 'Scheme of work not found');
        }

        $school = School::where('schoolID', Session::get('schoolID'))->first();
        $teacher = Teacher::find($teacherID);

        $data = [
            'scheme' => $scheme,
            'school' => $school,
            'teacher' => $teacher
        ];

        $pdf = PDF::loadView('Teacher.pdf.scheme_of_work', $data);
        $pdf->setPaper('A4', 'landscape');

        $filename = 'Scheme_of_Work_' . str_replace(' ', '_', $scheme->classSubject->subject->subject_name ?? 'Subject') . '_' . $scheme->year . '.pdf';

        return $pdf->download($filename);
    }

    public function exportSchemeOfWorkExcel($schemeOfWorkID)
    {
        $teacherID = Session::get('teacherID');
        
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
            return redirect()->back()->with('error', 'Scheme of work not found');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = ['#', 'Main Competence', 'Specific Competences', 'Learning Activities', 'Specific Activities', 
                   'Month', 'Week', 'Number of Periods', 'Teaching and Learning Methods', 
                   'Teaching and Learning Resources', 'Assessment Tools', 'References', 'Remarks'];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Add data rows
        $row = 2;
        $rowNum = 1;
        foreach ($scheme->items as $item) {
            $sheet->setCellValue('A' . $row, $rowNum);
            $sheet->setCellValue('B' . $row, $item->main_competence ?? '');
            $sheet->setCellValue('C' . $row, $item->specific_competences ?? '');
            $sheet->setCellValue('D' . $row, $item->learning_activities ?? '');
            $sheet->setCellValue('E' . $row, $item->specific_activities ?? '');
            $sheet->setCellValue('F' . $row, $item->month ?? '');
            $sheet->setCellValue('G' . $row, $item->week ?? '');
            $sheet->setCellValue('H' . $row, $item->number_of_periods ?? '');
            $sheet->setCellValue('I' . $row, $item->teaching_methods ?? '');
            $sheet->setCellValue('J' . $row, $item->teaching_resources ?? '');
            $sheet->setCellValue('K' . $row, $item->assessment_tools ?? '');
            $sheet->setCellValue('L' . $row, $item->references ?? '');
            $sheet->setCellValue('M' . $row, $item->remarks ?? '');
            $row++;
            $rowNum++;
        }

        // Auto-size columns
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Scheme_of_Work_' . str_replace(' ', '_', $scheme->classSubject->subject->subject_name ?? 'Subject') . '_' . $scheme->year . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function getSubjectStudents($classSubjectID)
    {
        try {
            $teacherID = Session::get('teacherID');

            if (!$teacherID) {
                return response()->json([
                    'error' => 'Teacher ID not found in session.'
                ], 400);
            }

            // Verify teacher owns this class subject and both ClassSubject and Subject have status = Active
            $classSubject = ClassSubject::where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Get students based on subclass or class
            $subclassID = $classSubject->subclassID;
            $classID = $classSubject->classID;

            if ($subclassID) {
                $students = Student::with(['subclass.class', 'parent'])
                    ->where('subclassID', $subclassID)
                    ->where('status', 'Active')
                    ->get();
            } else {
                $subclassIds = DB::table('subclasses')
                    ->where('classID', $classID)
                    ->pluck('subclassID')
                    ->toArray();
                
                $students = Student::with(['subclass.class', 'parent'])
                    ->whereIn('subclassID', $subclassIds)
                    ->where('status', 'Active')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'students' => $students,
                'class_subject' => $classSubject
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSubjectResults($classSubjectID, $examID = null)
    {
        try {
            $teacherID = Session::get('teacherID');

            if (!$teacherID) {
                return response()->json([
                    'error' => 'Teacher ID not found in session.'
                ], 400);
            }

            // Verify teacher owns this class subject and both ClassSubject and Subject have status = Active
            $classSubject = ClassSubject::with(['subject' => function($query) {
                    $query->where('status', 'Active');
                }, 'class', 'subclass'])
                ->where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            $query = Result::with(['student', 'examination'])
                ->where('class_subjectID', $classSubjectID);

            if ($examID) {
                $query->where('examID', $examID);
            }

            $results = $query->get();

            return response()->json([
                'success' => true,
                'results' => $results,
                'class_subject' => $classSubject
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getExaminationsForSubject($classSubjectID)
    {
        try {
            $teacherID = Session::get('teacherID');

            if (!$teacherID) {
                return response()->json([
                    'error' => 'Teacher ID not found in session.'
                ], 400);
            }

            // Verify teacher owns this class subject and both ClassSubject and Subject have status = Active
            $classSubject = ClassSubject::where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Get examinations - ONLY exams with enter_result = true (no other logic)
            $schoolID = Session::get('schoolID');
            
            // Get examinations where enter_result = true only - no other checks
            $examinations = Examination::where('schoolID', $schoolID)
                ->where('enter_result', true) // Only exams with enter_result = true
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($exam) use ($schoolID) {
                    // Check if term is closed
                    $isTermClosed = false;
                    if ($exam->term && $exam->year) {
                        // Convert term string to term_number (first_term = 1, second_term = 2)
                        $termNumber = null;
                        if ($exam->term === 'first_term' || $exam->term === '1') {
                            $termNumber = 1;
                        } elseif ($exam->term === 'second_term' || $exam->term === '2') {
                            $termNumber = 2;
                        }
                        
                        if ($termNumber) {
                            $term = DB::table('terms')
                                ->where('schoolID', $schoolID)
                                ->where('year', $exam->year)
                                ->where('term_number', $termNumber)
                                ->where('status', 'Closed')
                                ->first();
                            $isTermClosed = $term ? true : false;
                        }
                    }

                    return [
                        'examID' => $exam->examID,
                        'exam_name' => $exam->exam_name,
                        'year' => $exam->year,
                        'status' => $exam->status,
                        'start_date' => $exam->start_date,
                        'end_date' => $exam->end_date,
                        'enter_result' => $exam->enter_result,
                        'exam_category' => $exam->exam_category,
                        'term' => $exam->term,
                        'is_term_closed' => $isTermClosed,
                    ];
                });

            return response()->json([
                'success' => true,
                'examinations' => $examinations
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveSubjectResults(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');

            if (!$teacherID) {
                return response()->json([
                    'error' => 'Teacher ID not found in session.'
                ], 400);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'class_subjectID' => 'required|exists:class_subjects,class_subjectID',
                'examID' => 'required|exists:examinations,examID',
                'results' => 'required|array',
                'results.*.studentID' => 'required|exists:students,studentID',
                'results.*.marks' => 'nullable|numeric|min:0|max:100',
                'results.*.grade' => 'nullable|string|max:10',
                'results.*.remark' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Verify teacher owns this class subject and both ClassSubject and Subject have status = Active
            $classSubject = ClassSubject::where('class_subjectID', $request->class_subjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Check if enter_result is enabled - only check this
            $examination = \App\Models\Examination::find($request->examID);
            if (!$examination) {
                return response()->json([
                    'error' => 'Examination not found.'
                ], 404);
            }

            // Check ONLY if enter_result is enabled - no other checks
            if (!$examination->enter_result) {
                return response()->json([
                    'error' => 'You are not allowed to enter results for this examination. Result entry has been disabled.'
                ], 403);
            }

            // Check if term is closed - prevent editing results for closed terms
            if ($examination->term && $examination->year) {
                $term = DB::table('terms')
                    ->where('schoolID', $schoolID)
                    ->where('year', $examination->year)
                    ->where('term_number', $examination->term)
                    ->where('status', 'Closed')
                    ->first();

                if ($term) {
                    // Check if this is an edit operation (result already exists)
                    $isEdit = false;
                    foreach ($request->results as $resultData) {
                        $existingResult = \App\Models\Result::where('studentID', $resultData['studentID'])
                            ->where('examID', $request->examID)
                            ->where('class_subjectID', $request->class_subjectID)
                            ->first();
                        if ($existingResult) {
                            $isEdit = true;
                            break;
                        }
                    }

                    if ($isEdit) {
                        return response()->json([
                            'error' => 'You are not allowed to edit results for this term. The term has been closed.'
                        ], 403);
                    }
                }
            }

            DB::beginTransaction();

            $savedCount = 0;
            foreach ($request->results as $resultData) {
                // Find existing result or create new one
                $result = Result::where('studentID', $resultData['studentID'])
                    ->where('examID', $request->examID)
                    ->where('class_subjectID', $request->class_subjectID)
                    ->first();

                if ($result) {
                    // Update existing result
                    $result->update([
                        'marks' => $resultData['marks'] ?? $result->marks,
                        'grade' => $resultData['grade'] ?? $result->grade,
                        'remark' => $resultData['remark'] ?? $result->remark,
                    ]);
                } else {
                    // Create new result
                    $student = Student::find($resultData['studentID']);
                    if ($student) {
                        Result::create([
                            'studentID' => $resultData['studentID'],
                            'examID' => $request->examID,
                            'class_subjectID' => $request->class_subjectID,
                            'subclassID' => $student->subclassID,
                            'marks' => $resultData['marks'] ?? null,
                            'grade' => $resultData['grade'] ?? null,
                            'remark' => $resultData['remark'] ?? null,
                        ]);
                    }
                }
                $savedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => "Successfully saved {$savedCount} result(s)!",
                'saved_count' => $savedCount
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel template for API
     */
    public function downloadExcelTemplateAPI(Request $request, $classSubjectID, $examID)
    {
        try {
            // Get authentication data from headers (stateless authentication)
            $teacherID = $request->header('teacherID') ?? $request->input('teacherID');
            $schoolID = $request->header('schoolID') ?? $request->input('schoolID');
            $userID = $request->header('user_id') ?? $request->input('user_id');
            $userType = $request->header('user_type') ?? $request->input('user_type');

            // Validate required parameters
            if (!$teacherID || !$schoolID || !$userID || !$userType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
                ], 401);
            }

            // Validate user type
            if ($userType !== 'Teacher') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
                ], 403);
            }

            // Verify teacher exists and belongs to the school
            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
                ], 404);
            }

            // Verify teacher owns this class subject
            $classSubject = ClassSubject::with(['subject' => function($query) {
                    $query->where('status', 'Active');
                }, 'class', 'subclass'])
                ->where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'success' => false,
                    'error' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Get examination
            $examination = Examination::find($examID);
            if (!$examination || $examination->schoolID != $schoolID) {
                return response()->json([
                    'success' => false,
                    'error' => 'Examination not found or unauthorized access.'
                ], 404);
            }

            // Check examination status
            if ($examination->status !== 'awaiting_results') {
                return response()->json([
                    'success' => false,
                    'error' => 'You are not allowed to perform this action. Wait for Academic Permission.'
                ], 403);
            }

            // Get students for this class subject
            $subclassID = $classSubject->subclassID;
            $classID = $classSubject->classID;

            if ($subclassID) {
                $students = Student::where('subclassID', $subclassID)
                    ->where('status', 'Active')
                    ->orderBy('admission_number')
                    ->get();
            } else {
                $subclassIds = DB::table('subclasses')
                    ->where('classID', $classID)
                    ->pluck('subclassID')
                    ->toArray();
                
                $students = Student::whereIn('subclassID', $subclassIds)
                    ->where('status', 'Active')
                    ->orderBy('admission_number')
                    ->get();
            }

            // Get school type for grading
            $school = School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Check if PhpSpreadsheet is available
            if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                // Fallback to CSV if PhpSpreadsheet is not installed
                return $this->downloadCsvTemplate($classSubjectID, $examID, $students, $schoolType, $classSubject, $examination);
            }

            // Create spreadsheet using PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Results Template');
            
            // Enable calculation
            $spreadsheet->getCalculationEngine()->setCalculationCacheEnabled(true);

            // Headers
            $sheet->setCellValue('A1', 'Student ID');
            $sheet->setCellValue('B1', 'Admission Number');
            $sheet->setCellValue('C1', 'Student Name');
            $sheet->setCellValue('D1', 'Marks');
            $sheet->setCellValue('E1', 'Grade');
            $sheet->setCellValue('F1', 'Remark');

            // Style headers
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '940000']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(30);

            // Add students data
            $row = 2;
            foreach ($students as $student) {
                $sheet->setCellValue('A' . $row, $student->studentID);
                $sheet->setCellValue('B' . $row, $student->admission_number);
                $sheet->setCellValue('C' . $row, trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name));
                
                // Get existing result if any
                $existingResult = Result::where('studentID', $student->studentID)
                    ->where('examID', $examID)
                    ->where('class_subjectID', $classSubjectID)
                    ->first();
                
                // Always add formulas, even if existing result exists (formulas will override if marks are entered)
                // Add formula for Grade (Column E) based on Marks (Column D)
                if ($schoolType === 'Primary') {
                    // Primary: Division One (75-100), Division Two (50-74), Division Three (30-49), Division Four (0-29), Division Zero (null)
                    $gradeFormula = 'IF(D' . $row . '="","",IF(D' . $row . '>=75,"Division One",IF(D' . $row . '>=50,"Division Two",IF(D' . $row . '>=30,"Division Three",IF(D' . $row . '>=0,"Division Four","Division Zero")))))';
                    $sheet->setCellValueExplicit('E' . $row, $gradeFormula, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                    
                    // Add formula for Remark (Column F) for Primary
                    $remarkFormula = 'IF(D' . $row . '="","",IF(D' . $row . '>=75,"Excellent",IF(D' . $row . '>=50,"Very Good",IF(D' . $row . '>=30,"Good",IF(D' . $row . '>=0,"Pass","Fail")))))';
                    $sheet->setCellValueExplicit('F' . $row, $remarkFormula, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                } else {
                    // Secondary: A (75-100), B (65-74), C (45-64), D (30-44), F (0-29)
                    $gradeFormula = 'IF(D' . $row . '>=75,"A",IF(D' . $row . '>=65,"B",IF(D' . $row . '>=45,"C",IF(D' . $row . '>=30,"D","F"))))';
                    $sheet->setCellValueExplicit('E' . $row, $gradeFormula, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                    
                    // Add formula for Remark (Column F) for Secondary
                    $remarkFormula = 'IF(E' . $row . '="A","excellent",IF(E' . $row . '="B","very good",IF(E' . $row . '="C","good",IF(E' . $row . '="D","good","fail"))))';
                    $sheet->setCellValueExplicit('F' . $row, $remarkFormula, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                }
                
                // If existing result exists, populate marks (formulas will auto-calculate grade and remark)
                if ($existingResult && $existingResult->marks !== null) {
                    $sheet->setCellValue('D' . $row, $existingResult->marks);
                }
                
                $row++;
            }

            // Recalculate formulas before saving
            $spreadsheet->getActiveSheet()->getCellCollection()->clear();
            $spreadsheet->getCalculationEngine()->clearCalculationCache();
            
            // Create writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false); // Let Excel calculate formulas
            
            // Set headers for download
            $filename = 'Results_Template_' . $classSubject->subject->subject_name . '_' . $examination->exam_name . '_' . date('Y-m-d') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Error downloading Excel template API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to download Excel template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadExcelTemplate($classSubjectID, $examID)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json([
                    'error' => 'Teacher ID or School ID not found in session.'
                ], 400);
            }

            // Verify teacher owns this class subject and both ClassSubject and Subject have status = Active
            $classSubject = ClassSubject::with(['subject' => function($query) {
                    $query->where('status', 'Active');
                }, 'class', 'subclass'])
                ->where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Get examination
            $examination = Examination::find($examID);
            if (!$examination || $examination->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Examination not found or unauthorized access.'
                ], 404);
            }

            // Check examination status
            if ($examination->status !== 'awaiting_results') {
                return response()->json([
                    'error' => 'You are not allowed to perform this action. Wait for Academic Permission.'
                ], 403);
            }

            // Get students for this class subject
            $subclassID = $classSubject->subclassID;
            $classID = $classSubject->classID;

            if ($subclassID) {
                $students = Student::where('subclassID', $subclassID)
                    ->where('status', 'Active')
                    ->orderBy('admission_number')
                    ->get();
            } else {
                $subclassIds = DB::table('subclasses')
                    ->where('classID', $classID)
                    ->pluck('subclassID')
                    ->toArray();
                
                $students = Student::whereIn('subclassID', $subclassIds)
                    ->where('status', 'Active')
                    ->orderBy('admission_number')
                    ->get();
            }

            // Get school type for grading
            $school = School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Check if PhpSpreadsheet is available
            if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                // Fallback to CSV if PhpSpreadsheet is not installed
                return $this->downloadCsvTemplate($classSubjectID, $examID, $students, $schoolType, $classSubject, $examination);
            }

            // Create spreadsheet using PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Results Template');
            
            // Enable calculation
            $spreadsheet->getCalculationEngine()->setCalculationCacheEnabled(true);

            // Headers
            $sheet->setCellValue('A1', 'Student ID');
            $sheet->setCellValue('B1', 'Admission Number');
            $sheet->setCellValue('C1', 'Student Name');
            $sheet->setCellValue('D1', 'Marks');
            $sheet->setCellValue('E1', 'Grade');
            $sheet->setCellValue('F1', 'Remark');

            // Style headers
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '940000']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(30);

            // Add students data
            $row = 2;
            foreach ($students as $student) {
                $sheet->setCellValue('A' . $row, $student->studentID);
                $sheet->setCellValue('B' . $row, $student->admission_number);
                $sheet->setCellValue('C' . $row, trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name));
                
                // Get existing result if any
                $existingResult = Result::where('studentID', $student->studentID)
                    ->where('examID', $examID)
                    ->where('class_subjectID', $classSubjectID)
                    ->first();
                
                // Always add formulas, even if existing result exists (formulas will override if marks are entered)
                // Add formula for Grade (Column E) based on Marks (Column D)
                if ($schoolType === 'Primary') {
                    // Primary: Division One (75-100), Division Two (50-74), Division Three (30-49), Division Four (0-29), Division Zero (null)
                    // Formula: =IF(D2="","",IF(D2>=75,"Division One",IF(D2>=50,"Division Two",IF(D2>=30,"Division Three",IF(D2>=0,"Division Four","Division Zero")))))
                    $gradeFormula = 'IF(D' . $row . '="","",IF(D' . $row . '>=75,"Division One",IF(D' . $row . '>=50,"Division Two",IF(D' . $row . '>=30,"Division Three",IF(D' . $row . '>=0,"Division Four","Division Zero")))))';
                    $sheet->setCellValueExplicit('E' . $row, $gradeFormula, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                    
                    // Add formula for Remark (Column F) for Primary
                    // Formula: =IF(D2="","",IF(D2>=75,"Excellent",IF(D2>=50,"Very Good",IF(D2>=30,"Good",IF(D2>=0,"Pass","Fail")))))
                    $remarkFormula = 'IF(D' . $row . '="","",IF(D' . $row . '>=75,"Excellent",IF(D' . $row . '>=50,"Very Good",IF(D' . $row . '>=30,"Good",IF(D' . $row . '>=0,"Pass","Fail")))))';
                    $sheet->setCellValueExplicit('F' . $row, $remarkFormula, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                } else {
                    // Secondary: A (75-100), B (65-74), C (45-64), D (30-44), F (0-29)
                    // Simplified formula: =IF(D2>=75,"A",IF(D2>=65,"B",IF(D2>=45,"C",IF(D2>=30,"D","F"))))
                    $gradeFormula = 'IF(D' . $row . '>=75,"A",IF(D' . $row . '>=65,"B",IF(D' . $row . '>=45,"C",IF(D' . $row . '>=30,"D","F"))))';
                    $sheet->setCellValueExplicit('E' . $row, $gradeFormula, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                    
                    // Add formula for Remark (Column F) for Secondary - based on Grade (Column E)
                    // Formula: =IF(E2="A","excellent",IF(E2="B","very good",IF(E2="C","good",IF(E2="D","good","fail"))))
                    $remarkFormula = 'IF(E' . $row . '="A","excellent",IF(E' . $row . '="B","very good",IF(E' . $row . '="C","good",IF(E' . $row . '="D","good","fail"))))';
                    $sheet->setCellValueExplicit('F' . $row, $remarkFormula, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                }
                
                // If existing result exists, populate marks (formulas will auto-calculate grade and remark)
                if ($existingResult && $existingResult->marks !== null) {
                    $sheet->setCellValue('D' . $row, $existingResult->marks);
                    // Grade and Remark will be auto-calculated by formulas above
                }
                
                $row++;
            }

            // Note: 
            // - Column D (Marks): User enters marks manually
            // - Column E (Grade): Auto-calculated using formula =IF(D2 >= 75,"A",IF(D2 >= 65,"B",IF(D2 >= 45,"C",IF(D2 >= 30,"D","F"))))
            // - Column F (Remark): Auto-calculated using formula =IF(E2 = "A","excellent",IF(E2 = "B","very good",IF(E2 = "C","good",IF(E2 = "D","good","fail"))))
            // Users only need to enter marks in column D, and formulas will automatically calculate grade and remark

            // Recalculate formulas before saving
            $spreadsheet->getActiveSheet()->getCellCollection()->clear();
            $spreadsheet->getCalculationEngine()->clearCalculationCache();
            
            // Create writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false); // Let Excel calculate formulas
            
            // Set headers for download
            $filename = 'Results_Template_' . $classSubject->subject->subject_name . '_' . $examination->exam_name . '_' . date('Y-m-d') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadExcelResults(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json([
                    'error' => 'Teacher ID or School ID not found in session.'
                ], 400);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'class_subject_id' => 'required|exists:class_subjects,class_subjectID',
                'exam_id' => 'required|exists:examinations,examID',
                'excel_file' => 'required|mimes:xlsx,xls|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Verify teacher owns this class subject and both ClassSubject and Subject have status = Active
            $classSubject = ClassSubject::where('class_subjectID', $request->class_subject_id)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Check examination status
            $examination = Examination::find($request->exam_id);
            if (!$examination) {
                return response()->json([
                    'error' => 'Examination not found.'
                ], 404);
            }

            if ($examination->status !== 'awaiting_results') {
                return response()->json([
                    'error' => 'You are not allowed to perform this action. Wait for Academic Permission.'
                ], 403);
            }

            // Check if PhpSpreadsheet is available
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                return response()->json([
                    'error' => 'PhpSpreadsheet library is not installed. Please install it using: composer require phpoffice/phpspreadsheet --ignore-platform-reqs'
                ], 500);
            }

            // Load Excel file
            $file = $request->file('excel_file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            $results = [];
            $errors = [];
            $successCount = 0;

            // Get school type for grade calculation
            $school = School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $studentID = $worksheet->getCell('A' . $row)->getValue();
                $marks = $worksheet->getCell('D' . $row)->getValue();
                $grade = $worksheet->getCell('E' . $row)->getValue();
                $remark = $worksheet->getCell('F' . $row)->getValue();

                // Skip empty rows
                if (empty($studentID)) {
                    continue;
                }

                // Validate student exists
                $student = Student::find($studentID);
                if (!$student) {
                    $errors[] = "Row $row: Student ID $studentID not found.";
                    continue;
                }

                // Validate marks
                if ($marks !== null && $marks !== '') {
                    $marks = (float)$marks;
                    if ($marks < 0 || $marks > 100) {
                        $errors[] = "Row $row: Marks must be between 0 and 100.";
                        continue;
                    }

                    // Calculate grade if not provided or recalculate
                    if (empty($grade) || is_numeric($grade)) {
                        $grade = $this->calculateGrade($marks, $schoolType);
                    }
                } else {
                    $marks = null;
                    $grade = null;
                }

                $results[] = [
                    'studentID' => $studentID,
                    'marks' => $marks,
                    'grade' => $grade,
                    'remark' => $remark ?: null,
                ];
            }

            if (empty($results)) {
                return response()->json([
                    'error' => 'No valid results found in the Excel file.'
                ], 422);
            }

            // Check results status - must be 'allowed'
            $resultsStatus = Result::where('examID', $request->exam_id)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->orderByDesc('count')
                ->first();
            
            $currentStatus = $resultsStatus ? $resultsStatus->status : 'not_allowed';
            
            if ($currentStatus !== 'allowed') {
                // Check if this is an edit operation (result already exists)
                $isEdit = false;
                foreach ($results as $resultData) {
                    $existingResult = Result::where('studentID', $resultData['studentID'])
                        ->where('examID', $request->exam_id)
                        ->where('class_subjectID', $request->class_subject_id)
                        ->first();
                    if ($existingResult) {
                        $isEdit = true;
                        break;
                    }
                }
                
                if ($isEdit) {
                    return response()->json([
                        'error' => 'You are not allowed to edit results.'
                    ], 403);
                } else {
                    return response()->json([
                        'error' => 'You are not allowed to add results.'
                    ], 403);
                }
            }

            // Save results
            DB::beginTransaction();

            foreach ($results as $resultData) {
                $result = Result::where('studentID', $resultData['studentID'])
                    ->where('examID', $request->exam_id)
                    ->where('class_subjectID', $request->class_subject_id)
                    ->first();

                if ($result) {
                    $result->update([
                        'marks' => $resultData['marks'],
                        'grade' => $resultData['grade'],
                        'remark' => $resultData['remark'],
                    ]);
                } else {
                    $student = Student::find($resultData['studentID']);
                    if ($student) {
                        Result::create([
                            'studentID' => $resultData['studentID'],
                            'examID' => $request->exam_id,
                            'class_subjectID' => $request->class_subject_id,
                            'subclassID' => $student->subclassID,
                            'marks' => $resultData['marks'],
                            'grade' => $resultData['grade'],
                            'remark' => $resultData['remark'],
                        ]);
                    }
                }
                $successCount++;
            }

            DB::commit();

            $message = "Successfully uploaded {$successCount} result(s)!";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " error(s) occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'saved_count' => $successCount,
                'errors' => $errors
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateGrade($marks, $schoolType)
    {
        if ($schoolType === 'Primary') {
            if ($marks >= 75) {
                return 'Division One';
            } elseif ($marks >= 50) {
                return 'Division Two';
            } elseif ($marks >= 30) {
                return 'Division Three';
            } elseif ($marks >= 0) {
                return 'Division Four';
            } else {
                return 'Division Zero';
            }
        } else {
            if ($marks >= 75) {
                return 'A';
            } elseif ($marks >= 65) {
                return 'B';
            } elseif ($marks >= 45) {
                return 'C';
            } elseif ($marks >= 30) {
                return 'D';
            } else {
                return 'F';
            }
        }
    }

    // Fallback CSV download method (used when PhpSpreadsheet is not installed)
    private function downloadCsvTemplate($classSubjectID, $examID, $students, $schoolType, $classSubject, $examination)
    {
        $filename = 'Results_Template_' . $classSubject->subject->subject_name . '_' . $examination->exam_name . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, ['Student ID', 'Admission Number', 'Student Name', 'Marks', 'Grade', 'Remark']);
        
        // Student data
        foreach ($students as $student) {
            $existingResult = Result::where('studentID', $student->studentID)
                ->where('examID', $examID)
                ->where('class_subjectID', $classSubjectID)
                ->first();
            
            $marks = $existingResult && $existingResult->marks !== null ? $existingResult->marks : '';
            $grade = $existingResult && $existingResult->grade ? $existingResult->grade : '';
            $remark = $existingResult && $existingResult->remark ? $existingResult->remark : '';
            
            fputcsv($output, [
                $student->studentID,
                $student->admission_number,
                trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name),
                $marks,
                $grade,
                $remark
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Dismiss exam rejection notification
     */
    public function dismissExamRejectionNotification(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            
            if (!$teacherID) {
                return response()->json([
                    'error' => 'Teacher ID not found in session.'
                ], 400);
            }

            $examName = $request->input('exam_name');
            
            if ($examName) {
                // Remove all notifications for this exam name
                $sessionKeys = Session::all();
                foreach ($sessionKeys as $key => $value) {
                    if (strpos($key, "teacher_notification_{$teacherID}_exam_rejected_") === 0) {
                        if (isset($value['exam_name']) && $value['exam_name'] === $examName) {
                            Session::forget($key);
                        }
                    }
                }
            } else {
                // Remove all rejection notifications for this teacher
                $sessionKeys = Session::all();
                foreach ($sessionKeys as $key => $value) {
                    if (strpos($key, "teacher_notification_{$teacherID}_exam_rejected_") === 0) {
                        Session::forget($key);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification dismissed successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View results for approval
     */
    public function approveResult($examID)
    {
        $user = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$user || !$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        // Get examination
        $examination = Examination::find($examID);
        if (!$examination || $examination->schoolID != $schoolID) {
            return redirect()->route('teachersDashboard')->with('error', 'Examination not found');
        }

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

        // Get ALL approvals this teacher is eligible for (regular roles AND special roles)
        // Then pick the one with the lowest approval_order that is pending
        $eligibleApprovals = [];
        
        // Check for regular role approvals
        if (!empty($teacherRoles)) {
            $regularRoleApprovals = ResultApproval::with(['examination', 'role'])
            ->where('examID', $examID)
            ->whereIn('role_id', $teacherRoles)
                ->whereIn('status', ['pending', 'rejected'])
                ->get();
            
            foreach ($regularRoleApprovals as $approval) {
                $eligibleApprovals[] = $approval;
            }
        }
        
        // Check for class_teacher approvals
            if (!empty($teacherSubclasses)) {
            $classTeacherApprovals = ResultApproval::with(['examination', 'classTeacherApprovals'])
                    ->where('examID', $examID)
                    ->where('special_role_type', 'class_teacher')
                    ->whereIn('status', ['pending', 'rejected'])
                    ->whereHas('classTeacherApprovals', function($query) use ($teacherSubclasses) {
                        $query->whereIn('subclassID', $teacherSubclasses)
                            ->whereIn('status', ['pending', 'rejected']);
                    })
                ->get();
                
            foreach ($classTeacherApprovals as $approval) {
                $eligibleApprovals[] = $approval;
                }
            }
            
        // Check for coordinator approvals
        if (!empty($teacherMainClasses)) {
            $coordinatorApprovals = ResultApproval::with(['examination', 'coordinatorApprovals'])
                    ->where('examID', $examID)
                    ->where('special_role_type', 'coordinator')
                    ->whereIn('status', ['pending', 'rejected'])
                    ->whereHas('coordinatorApprovals', function($query) use ($teacherMainClasses) {
                        $query->whereIn('mainclassID', $teacherMainClasses)
                            ->whereIn('status', ['pending', 'rejected']);
                    })
                ->get();
            
            foreach ($coordinatorApprovals as $approval) {
                $eligibleApprovals[] = $approval;
            }
        }
        
        // Get all approvals for this exam to check previous approvals
        $allApprovals = ResultApproval::where('examID', $examID)
                    ->orderBy('approval_order')
            ->get();
        
        // Filter eligible approvals: only include those where all previous approvals are completed
        // OR where the teacher already approved previous steps
        $validApprovals = [];
        foreach ($eligibleApprovals as $approval) {
            $previousApprovals = $allApprovals->where('approval_order', '<', $approval->approval_order);
            
            // Check if all previous approvals are completed (approved)
            $allPreviousCompleted = $previousApprovals->every(function($prev) use ($teacherID, $teacherRoles, $teacherSubclasses, $teacherMainClasses) {
                // If previous approval is already approved, it's fine
                if ($prev->status === 'approved') {
                    return true;
                }
                
                // If previous approval is pending but this teacher is the approver and already approved it, it's fine
                // Check if teacher already approved this previous step
                if ($prev->role_id && in_array($prev->role_id, $teacherRoles) && $prev->approved_by == $teacherID) {
                    return true;
                }
                
                // For special roles, check if teacher already approved
                if ($prev->special_role_type === 'class_teacher' && !empty($teacherSubclasses)) {
                    $teacherApproved = ClassTeacherApproval::where('result_approvalID', $prev->result_approvalID)
                        ->whereIn('subclassID', $teacherSubclasses)
                        ->where('approved_by', $teacherID)
                        ->exists();
                    if ($teacherApproved) {
                        return true;
                    }
                }
                
                if ($prev->special_role_type === 'coordinator' && !empty($teacherMainClasses)) {
                    $teacherApproved = CoordinatorApproval::where('result_approvalID', $prev->result_approvalID)
                        ->whereIn('mainclassID', $teacherMainClasses)
                        ->where('approved_by', $teacherID)
                        ->exists();
                    if ($teacherApproved) {
                        return true;
                    }
                }
                
                // If previous approval is still pending and teacher hasn't approved it, check if teacher can approve it
                // If teacher can approve previous step, they should do that first (return false to exclude current step)
                if ($prev->status === 'pending') {
                    // Check if teacher is eligible for previous approval
                    $teacherEligibleForPrev = false;
                    if ($prev->role_id && in_array($prev->role_id, $teacherRoles)) {
                        $teacherEligibleForPrev = true;
                    } elseif ($prev->special_role_type === 'class_teacher' && !empty($teacherSubclasses)) {
                    $teacherEligibleForPrev = ClassTeacherApproval::where('result_approvalID', $prev->result_approvalID)
                        ->whereIn('subclassID', $teacherSubclasses)
                        ->whereIn('status', ['pending', 'rejected'])
                        ->exists();
                    } elseif ($prev->special_role_type === 'coordinator' && !empty($teacherMainClasses)) {
                    $teacherEligibleForPrev = CoordinatorApproval::where('result_approvalID', $prev->result_approvalID)
                        ->whereIn('mainclassID', $teacherMainClasses)
                        ->whereIn('status', ['pending', 'rejected'])
                        ->exists();
                }
                    
                    if ($teacherEligibleForPrev) {
                        return false; // Teacher should approve previous step first
                    }
                }
                
                return false; // Previous step not completed
            });
            
            if ($allPreviousCompleted) {
                $validApprovals[] = $approval;
            }
        }
        
        // Pick the approval with the lowest approval_order
        if (!empty($validApprovals)) {
            $resultApproval = collect($validApprovals)->sortBy('approval_order')->first();
        } else {
            $resultApproval = null;
        }

        if (!$resultApproval) {
            return redirect()->route('teachersDashboard')->with('error', 'No pending approval found for this examination');
        }

        // Check if all previous approvals are completed
        $previousApprovals = ResultApproval::where('examID', $examID)
            ->where('approval_order', '<', $resultApproval->approval_order)
            ->with(['role', 'approver'])
            ->get();

        $canProceed = $previousApprovals->isEmpty() || 
            $previousApprovals->every(function($prev) {
            return $prev->status === 'approved';
            });
        
        // If cannot proceed, show only roadmap (no results)
        $showOnlyRoadmap = !$canProceed;
        
        // Determine if this is a special role approval
        $isClassTeacherApproval = $resultApproval->special_role_type === 'class_teacher';
        $isCoordinatorApproval = $resultApproval->special_role_type === 'coordinator';
        
        // Get teacher's available subclasses/mainclasses for this approval
        $availableSubclasses = [];
        $availableMainClasses = [];
        
        if ($isClassTeacherApproval) {
            // Get subclasses that need approval for this teacher
            $classTeacherApprovals = ClassTeacherApproval::where('result_approvalID', $resultApproval->result_approvalID)
                ->whereIn('subclassID', $teacherSubclasses)
                ->whereIn('status', ['pending', 'rejected'])
                ->with('subclass.class')
                ->get();
            
            foreach ($classTeacherApprovals as $cta) {
                if ($cta->subclass) {
                    $availableSubclasses[] = [
                        'subclassID' => $cta->subclassID,
                        'subclass_name' => $cta->subclass->subclass_name,
                        'class_name' => $cta->subclass->class ? $cta->subclass->class->class_name : 'N/A',
                        'classID' => $cta->subclass->classID,
                    ];
                }
            }
        } elseif ($isCoordinatorApproval) {
            // Get mainclasses that need approval for this teacher
            $coordinatorApprovals = CoordinatorApproval::where('result_approvalID', $resultApproval->result_approvalID)
                ->whereIn('mainclassID', $teacherMainClasses)
                ->whereIn('status', ['pending', 'rejected'])
                ->with('mainclass')
                ->get();
            
            foreach ($coordinatorApprovals as $ca) {
                if ($ca->mainclass) {
                    $availableMainClasses[] = [
                        'classID' => $ca->mainclassID,
                        'class_name' => $ca->mainclass->class_name,
                    ];
                }
            }
        }

        // Get all result approvals for this exam to show the chain
        $allApprovals = ResultApproval::with('role')
            ->where('examID', $examID)
            ->orderBy('approval_order')
            ->get();

        // Get school details for statistics
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : 'Secondary';

        // Get participating classes for this examination based on exam category
        $participatingClassIds = [];
        
        if ($examination->exam_category === 'school_exams' || $examination->exam_category === 'test') {
            // For school_exams and test: all classes except excluded ones
            $allClasses = \App\Models\ClassModel::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->pluck('classID')
                ->toArray();
            
            $exceptClassIds = $examination->except_class_ids ?? [];
            if (!empty($exceptClassIds) && is_array($exceptClassIds)) {
                $participatingClassIds = array_diff($allClasses, $exceptClassIds);
            } else {
                $participatingClassIds = $allClasses;
            }
        } elseif ($examination->exam_category === 'special_exams') {
            // For special_exams: get from results or exam_timetables
            $participatingSubclassIds = DB::table('results')
                ->where('examID', $examID)
                ->distinct()
                ->pluck('subclassID')
                ->toArray();
            
            if (!empty($participatingSubclassIds)) {
                $participatingClassIds = \App\Models\Subclass::whereIn('subclassID', $participatingSubclassIds)
                    ->distinct()
                    ->pluck('classID')
                    ->toArray();
            }
            
            // If no results yet, try exam_timetables
            if (empty($participatingClassIds)) {
                $participatingSubclassIds = DB::table('exam_timetables')
                    ->where('examID', $examID)
                    ->distinct()
                    ->pluck('subclassID')
                    ->toArray();
                
                if (!empty($participatingSubclassIds)) {
                    $participatingClassIds = \App\Models\Subclass::whereIn('subclassID', $participatingSubclassIds)
                        ->distinct()
                        ->pluck('classID')
                        ->toArray();
                }
            }
        }
        
        // Get participating classes with their details
        $participatingClasses = \App\Models\ClassModel::whereIn('classID', $participatingClassIds)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();
        
        // Get all subclasses for participating classes
        $participatingSubclasses = \App\Models\Subclass::whereIn('classID', $participatingClassIds)
            ->with('class')
            ->orderBy('subclass_name')
            ->get();

        // Get all results for this examination
        $allResults = Result::with(['student.subclass.class', 'classSubject.subject'])
            ->where('examID', $examID)
            ->whereNotNull('marks')
            ->get();

        // Group results by class and subclass
        $results = $allResults->groupBy(function($result) {
            if (!$result->student || !$result->student->subclass) {
                return 'Unknown|0|Unknown';
            }
            $class = $result->student->subclass->class ?? null;
            $subclass = $result->student->subclass ?? null;
            if ($class && $subclass) {
                return $class->class_name . '|' . $subclass->subclassID . '|' . ($subclass->subclass_name ?? '');
            }
            return 'Unknown|0|Unknown';
        });

        // Get all classes and subclasses that have results
        $classes = [];
        $subclasses = [];
        $classSubclassMap = [];

        foreach ($results as $key => $classResults) {
            $parts = explode('|', $key);
            $className = $parts[0];
            $subclassID = $parts[1] ?? null;
            $subclassName = $parts[2] ?? null;

            if (!in_array($className, $classes)) {
                $classes[] = $className;
            }

            if ($subclassID && $subclassID != '0') {
                $subclassKey = $subclassID;
                if (!isset($subclasses[$subclassKey])) {
                    $subclasses[$subclassKey] = [
                        'subclassID' => $subclassID,
                        'subclass_name' => $subclassName,
                        'class_name' => $className
                    ];
                }
                if (!isset($classSubclassMap[$className])) {
                    $classSubclassMap[$className] = [];
                }
                $classSubclassMap[$className][] = $subclassKey;
            }
        }

        sort($classes);

        // Calculate statistics for the entire exam
        $overallStatistics = $this->calculateExamStatistics($allResults, $schoolType, $schoolID);

        // Calculate statistics per class
        $classStatistics = [];
        foreach ($results as $key => $classResults) {
            $parts = explode('|', $key);
            $className = $parts[0];
            if (!isset($classStatistics[$className])) {
                $classStatistics[$className] = $this->calculateExamStatistics($classResults, $schoolType, $schoolID);
            } else {
                // Merge statistics if class has multiple subclasses
                $existingStats = $classStatistics[$className];
                $newStats = $this->calculateExamStatistics($classResults, $schoolType, $schoolID);
                // You can merge or recalculate here if needed
            }
        }

        // Get attendance statistics if exam has ended
        $attendanceStats = null;
        $attendanceBySubject = [];
        $examHasEnded = false;
        
        try {
            $today = \Carbon\Carbon::today();
            $endDate = \Carbon\Carbon::parse($examination->end_date);
            $isWeeklyTest = $examination->exam_name === 'Weekly Test' || $examination->start_date === 'every_week' || $examination->end_date === 'every_week';
            $isMonthlyTest = $examination->exam_name === 'Monthly Test' || $examination->start_date === 'every_month' || $examination->end_date === 'every_month';
            
            // For weekly/monthly tests, consider them as ended if they have results
            if ($isWeeklyTest || $isMonthlyTest) {
                $examHasEnded = $allResults->isNotEmpty();
            } else {
                $examHasEnded = $endDate->lte($today);
            }
            
            if ($examHasEnded) {
                // Get all unique students who should take the exam
                $expectedStudents = DB::table('exam_attendance')
                    ->where('examID', $examID)
                    ->select(DB::raw('COUNT(DISTINCT studentID) as count'))
                    ->first()
                    ->count ?? 0;
                
                // Get unique students who were present (attended at least one subject)
                $presentStudentIDs = DB::table('exam_attendance')
                    ->where('examID', $examID)
                    ->where('status', 'Present')
                    ->distinct()
                    ->pluck('studentID')
                    ->toArray();
                
                $presentStudents = count($presentStudentIDs);
                $absentStudents = $expectedStudents - $presentStudents;
                
                // Get attendance by subject
                $attendanceBySubjectRaw = DB::table('exam_attendance')
                    ->join('school_subjects', 'exam_attendance.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_attendance.examID', $examID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        DB::raw('COUNT(DISTINCT exam_attendance.studentID) as expected'),
                        DB::raw('COUNT(DISTINCT CASE WHEN exam_attendance.status = "Present" THEN exam_attendance.studentID END) as present'),
                        DB::raw('COUNT(DISTINCT CASE WHEN exam_attendance.status = "Absent" THEN exam_attendance.studentID END) as absent')
                    )
                    ->groupBy('school_subjects.subjectID', 'school_subjects.subject_name')
                    ->orderBy('school_subjects.subject_name')
                    ->get();
                
                foreach ($attendanceBySubjectRaw as $subject) {
                    $attendanceBySubject[] = [
                        'subjectID' => $subject->subjectID,
                        'subject_name' => $subject->subject_name,
                        'expected' => $subject->expected ?? 0,
                        'present' => $subject->present ?? 0,
                        'absent' => $subject->absent ?? 0,
                    ];
                }
                
                $attendanceStats = [
                    'expected' => $expectedStudents,
                    'present' => $presentStudents,
                    'absent' => $absentStudents,
                    'by_subject' => $attendanceBySubject,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching attendance statistics: ' . $e->getMessage());
        }

        return view('Teacher.approve_result', compact(
            'examination', 
            'resultApproval', 
            'allApprovals', 
            'results', 
            'classes',
            'showOnlyRoadmap',
            'previousApprovals', 
            'subclasses', 
            'classSubclassMap', 
            'overallStatistics', 
            'classStatistics', 
            'schoolType', 
            'participatingClasses', 
            'participatingSubclasses', 
            'attendanceStats', 
            'examHasEnded',
            'isClassTeacherApproval',
            'isCoordinatorApproval',
            'availableSubclasses',
            'availableMainClasses'
        ));
    }

    /**
     * Get filtered results for approval (AJAX)
     */
    public function getFilteredResultsForApproval(Request $request, $examID)
    {
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
        
        $examination = Examination::find($examID);
        if (!$examination || $examination->schoolID != $schoolID) {
            return response()->json(['error' => 'Examination not found'], 404);
        }
        
        // Get current result approval for this teacher
        $resultApproval = ResultApproval::where('examID', $examID)
            ->whereIn('status', ['pending', 'rejected'])
            ->orderBy('approval_order')
            ->first();
        
        // Check if this is a special role approval
        $isClassTeacherApproval = false;
        $isCoordinatorApproval = false;
        $allowedSubclassIDs = [];
        $allowedMainClassIDs = [];
        
        if ($resultApproval && $resultApproval->special_role_type === 'class_teacher') {
            $isClassTeacherApproval = true;
            // Get teacher's subclasses that need approval
            $teacherSubclasses = DB::table('subclasses')
                ->where('teacherID', $teacherID)
                ->pluck('subclassID')
                ->toArray();
            
            $classTeacherApprovals = ClassTeacherApproval::where('result_approvalID', $resultApproval->result_approvalID)
                ->whereIn('subclassID', $teacherSubclasses)
                ->whereIn('status', ['pending', 'rejected'])
                ->pluck('subclassID')
                ->toArray();
            
            $allowedSubclassIDs = $classTeacherApprovals;
        } elseif ($resultApproval && $resultApproval->special_role_type === 'coordinator') {
            $isCoordinatorApproval = true;
            // Get teacher's mainclasses that need approval
            $teacherMainClasses = DB::table('classes')
                ->where('teacherID', $teacherID)
                ->where('schoolID', $schoolID)
                ->pluck('classID')
                ->toArray();
            
            $coordinatorApprovals = CoordinatorApproval::where('result_approvalID', $resultApproval->result_approvalID)
                ->whereIn('mainclassID', $teacherMainClasses)
                ->whereIn('status', ['pending', 'rejected'])
                ->pluck('mainclassID')
                ->toArray();
            
            $allowedMainClassIDs = $coordinatorApprovals;
        }
        
        $mainClassID = $request->input('main_class_id');
        $subclassID = $request->input('subclass_id');
        
        // Enforce filters for special roles
        if ($isClassTeacherApproval) {
            // Class teacher can only see their own subclass
            if (!$subclassID || $subclassID === 'all' || !in_array($subclassID, $allowedSubclassIDs)) {
                // If no subclass selected or invalid, use first allowed subclass
                if (!empty($allowedSubclassIDs)) {
                    $subclassID = $allowedSubclassIDs[0];
                } else {
                    return response()->json(['error' => 'No subclass assigned for approval'], 403);
                }
            }
            $mainClassID = 'all'; // Class teacher doesn't filter by mainclass
        } elseif ($isCoordinatorApproval) {
            // Coordinator can only see their own mainclass
            if (!$mainClassID || $mainClassID === 'all' || !in_array($mainClassID, $allowedMainClassIDs)) {
                // If no mainclass selected or invalid, use first allowed mainclass
                if (!empty($allowedMainClassIDs)) {
                    $mainClassID = $allowedMainClassIDs[0];
                } else {
                    return response()->json(['error' => 'No mainclass assigned for approval'], 403);
                }
            }
            // Subclass filter is optional for coordinator
        }
        
        // Get all results for this examination where examID matches
        // Use join to ensure we get all results even if relationships are missing
        $query = DB::table('results')
            ->leftJoin('students', 'results.studentID', '=', 'students.studentID')
            ->leftJoin('subclasses', 'results.subclassID', '=', 'subclasses.subclassID')
            ->leftJoin('classes', 'subclasses.classID', '=', 'classes.classID')
            ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
            ->where('results.examID', $examID)
            ->whereNotNull('results.marks')
            ->select(
                'results.*',
                'students.admission_number',
                'students.first_name',
                'students.middle_name',
                'students.last_name',
                'students.gender',
                'subclasses.subclassID as subclass_id',
                'subclasses.subclass_name',
                'subclasses.classID',
                'classes.class_name',
                'classes.classID as main_class_id',
                'school_subjects.subject_name',
                'class_subjects.class_subjectID'
            );
        
        // Filter by main class
        if ($mainClassID && $mainClassID !== 'all') {
            $query->where('subclasses.classID', $mainClassID);
        }
        
        // Filter by subclass
        if ($subclassID && $subclassID !== 'all') {
            $query->where('results.subclassID', $subclassID);
        }
        
        $allResults = $query->get();
        
        // Group results by class and subclass
        $results = $allResults->groupBy(function($result) {
            $className = $result->class_name ?? 'Unknown';
            $subclassID = $result->subclass_id ?? 0;
            $subclassName = $result->subclass_name ?? 'Unknown';
            return $className . '|' . $subclassID . '|' . $subclassName;
        });
        
        // Format results for display - Group by student (like exam results display)
        $formattedResults = [];
        foreach ($results as $key => $classResults) {
            $parts = explode('|', $key);
            $className = $parts[0];
            $subclassID = $parts[1] ?? null;
            $subclassName = $parts[2] ?? null;
            
            // Group results by student
            $studentsResults = [];
            foreach ($classResults as $result) {
                $studentID = $result->studentID ?? null;
                if (!$studentID) continue;
                
                // Get classID from result
                $classID = $result->main_class_id ?? $result->classID ?? null;
                $marks = $result->marks ?? null;
                
                // Get grade from grade_definitions table
                $grade = 'N/A';
                if ($classID && $marks !== null && $marks !== '') {
                    $marksNum = (float) $marks;
                    $gradeDefinition = GradeDefinition::where('classID', $classID)
                        ->where('first', '<=', $marksNum)
                        ->where('last', '>=', $marksNum)
                        ->first();
                    
                    if ($gradeDefinition) {
                        $grade = $gradeDefinition->grade;
                    } else {
                        // Fallback to result's grade if no definition found
                        $grade = $result->grade ?? 'N/A';
                    }
                } else {
                    // Fallback to result's grade if no classID or marks
                    $grade = $result->grade ?? 'N/A';
                }
                
                // Format student name
                $studentName = trim(
                    ($result->first_name ?? '') . ' ' . 
                    ($result->middle_name ?? '') . ' ' . 
                    ($result->last_name ?? '')
                );
                if (empty(trim($studentName))) {
                    $studentName = 'N/A';
                }
                
                if (!isset($studentsResults[$studentID])) {
                    $studentsResults[$studentID] = [
                        'studentID' => $studentID,
                        'admission_number' => $result->admission_number ?? 'N/A',
                        'student_name' => $studentName,
                        'gender' => $result->gender ?? 'Unknown',
                        'subjects' => []
                    ];
                }
                
                $originalMarks = $result->marks !== null ? (float)$result->marks : null;
                
                $studentsResults[$studentID]['subjects'][] = [
                    'subject_name' => $result->subject_name ?? 'N/A',
                    'marks' => $originalMarks !== null ? round($originalMarks) : 'N/A',
                    'original_marks' => $originalMarks, // Store original for calculations
                    'grade' => $grade,
                    'remark' => $result->remark ?? 'N/A',
                ];
            }
            
            // Get school type and class name for calculations
            $school = School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';
            $className = $className ?? 'Unknown';
            
            // Get classID for this class/subclass (use first result's classID)
            $classIDForCalculation = null;
            if (!empty($classResults)) {
                $firstResult = $classResults->first();
                $classIDForCalculation = $firstResult->main_class_id ?? $firstResult->classID ?? null;
            }
            
            // Get all student genders at once for efficiency
            $studentIds = array_keys($studentsResults);
            $studentGenders = [];
            if (!empty($studentIds)) {
                $genderResults = DB::table('students')
                    ->whereIn('studentID', $studentIds)
                    ->select('studentID', 'gender')
                    ->get();
                
                foreach ($genderResults as $genderResult) {
                    // Normalize gender immediately - database stores 'Male' or 'Female'
                    $rawGender = $genderResult->gender ?? null;
                    if ($rawGender) {
                        $rawGender = trim((string)$rawGender);
                        if (strcasecmp($rawGender, 'Male') === 0) {
                            $studentGenders[$genderResult->studentID] = 'Male';
                        } elseif (strcasecmp($rawGender, 'Female') === 0) {
                            $studentGenders[$genderResult->studentID] = 'Female';
                        } else {
                            $studentGenders[$genderResult->studentID] = 'Unknown';
                        }
                    } else {
                        $studentGenders[$genderResult->studentID] = 'Unknown';
                    }
                }
            }
            
            // Convert to array and calculate totals, grade/division
            $studentsArray = [];
            foreach ($studentsResults as $studentID => $studentData) {
                $totalMarks = 0;
                $subjectCount = 0;
                $subjectPoints = [];
                
                foreach ($studentData['subjects'] as $subject) {
                    // Use original_marks if available, otherwise parse formatted marks
                    $marks = $subject['original_marks'] ?? null;
                    if ($marks === null && $subject['marks'] !== 'N/A') {
                        $marks = (float) str_replace(',', '', $subject['marks']);
                    }
                    
                    if ($marks !== null) {
                        $totalMarks += $marks;
                        $subjectCount++;
                        
                        // Calculate points for secondary schools
                        if ($schoolType === 'Secondary' && $classIDForCalculation) {
                            $gradeResult = $this->calculateGradePointsForResult($marks, $schoolType, $className, $classIDForCalculation);
                            if ($gradeResult && isset($gradeResult['points'])) {
                                $subjectPoints[] = [
                                    'points' => $gradeResult['points'],
                                    'marks' => $marks
                                ];
                            }
                        }
                    }
                }
                $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
                
                // Calculate grade/division
                $grade = 'N/A';
                $division = null;
                $totalPoints = 0;
                
                if ($schoolType === 'Primary') {
                    // Primary: Get grade from grade_definitions based on average
                    if ($classIDForCalculation && $averageMarks > 0) {
                        $gradeResult = $this->getGradeFromDefinition($averageMarks, $classIDForCalculation);
                        $grade = $gradeResult['grade'] ?? 'N/A';
                    }
                } else {
                    // Secondary: Calculate division from points
                    if (!empty($subjectPoints)) {
                        $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));
                        
                        if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                            // O-Level: Best 7 subjects (lowest points)
                            usort($subjectPoints, function($a, $b) {
                                if ($a['points'] != $b['points']) {
                                    return $a['points'] <=> $b['points'];
                                }
                                return $b['marks'] <=> $a['marks'];
                            });
                            $bestSubjects = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
                            foreach ($bestSubjects as $subject) {
                                $totalPoints += $subject['points'];
                            }
                        } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
                            // A-Level: Best 3 subjects (highest points)
                            usort($subjectPoints, function($a, $b) {
                                if ($a['points'] != $b['points']) {
                                    return $b['points'] <=> $a['points'];
                                }
                                return $b['marks'] <=> $a['marks'];
                            });
                            $bestSubjects = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                            foreach ($bestSubjects as $subject) {
                                $totalPoints += $subject['points'];
                            }
                        }
                        
                        // Calculate division from total points
                        $division = $this->calculateDivisionFromPoints($totalPoints, $classNameLower);
                    }
                }
                
                // Get student gender - database stores 'Male' or 'Female' as ENUM
                $rawGender = $studentGenders[$studentID] ?? null;
                $studentGender = 'Unknown';
                if ($rawGender) {
                    $rawGender = trim((string)$rawGender);
                    // Database uses exact 'Male' or 'Female', but handle variations just in case
                    if (strcasecmp($rawGender, 'Male') === 0 || strcasecmp($rawGender, 'M') === 0) {
                        $studentGender = 'Male';
                    } elseif (strcasecmp($rawGender, 'Female') === 0 || strcasecmp($rawGender, 'F') === 0) {
                        $studentGender = 'Female';
                    } else {
                        // Fallback: check if contains the word
                        if (stripos($rawGender, 'male') !== false && stripos($rawGender, 'female') === false) {
                            $studentGender = 'Male';
                        } elseif (stripos($rawGender, 'female') !== false) {
                            $studentGender = 'Female';
                        }
                    }
                }
                
                $studentsArray[] = [
                    'studentID' => $studentData['studentID'],
                    'admission_number' => $studentData['admission_number'],
                    'student_name' => $studentData['student_name'],
                    'gender' => $studentGender,
                    'subjects' => $studentData['subjects'],
                    'total_marks' => $totalMarks,
                    'average_marks' => $averageMarks,
                    'subject_count' => $subjectCount,
                    'grade' => $grade,
                    'division' => $division,
                    'school_type' => $schoolType
                ];
            }
            
            // Sort by total marks descending
            usort($studentsArray, function($a, $b) {
                return $b['total_marks'] <=> $a['total_marks'];
            });
            
            // Add position
            foreach ($studentsArray as $index => &$student) {
                $student['position'] = $index + 1;
            }
            
            $formattedResults[] = [
                'class_name' => $className,
                'subclassID' => $subclassID,
                'subclass_name' => $subclassName,
                'students' => $studentsArray
            ];
        }
        
        // Get school type for response
        $school = School::find($schoolID);
        $schoolType = $school ? $school->school_type : 'Secondary';
        
        // Calculate statistics
        try {
            $statistics = $this->calculateResultStatistics($formattedResults, $schoolType, $allResults, $schoolID);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Illuminate\Support\Facades\Log::error('Error calculating statistics: ' . $e->getMessage());
            $statistics = [
                'grade_stats' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0],
                'division_stats' => ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0],
                'grade_gender_stats' => [],
                'division_gender_stats' => [],
                'class_stats' => [],
                'class_pass_rates' => [],
                'subject_stats' => [],
                'school_type' => $schoolType
            ];
        }
        
        return response()->json([
            'success' => true,
            'results' => $formattedResults,
            'total_count' => $allResults->count(),
            'school_type' => $schoolType,
            'statistics' => $statistics
        ]);
    }

    /**
     * Approve or reject result approval
     */
    public function submitResultApproval(Request $request, $examID)
    {
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID || !$schoolID) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'approval_comment' => 'nullable|string|max:500',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500',
        ], [
            'rejection_reason.required_if' => 'Please provide a reason for rejecting the results.',
            'rejection_reason.string' => 'Rejection reason must be a string.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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

        // Get pending/rejected approval - check both regular roles and special roles
        $resultApproval = null;
        
        // First, check for regular role approvals
        if (!empty($teacherRoles)) {
        $resultApproval = ResultApproval::where('examID', $examID)
            ->whereIn('role_id', $teacherRoles)
                ->whereIn('status', ['pending', 'rejected'])
            ->orderBy('approval_order')
            ->first();
        }
        
        // If no regular role approval, check for special role approvals
        if (!$resultApproval) {
            // Check for class_teacher approval
            if (!empty($teacherSubclasses)) {
                $classTeacherApproval = ResultApproval::where('examID', $examID)
                    ->where('special_role_type', 'class_teacher')
                    ->whereIn('status', ['pending', 'rejected'])
                    ->whereHas('classTeacherApprovals', function($query) use ($teacherSubclasses) {
                        $query->whereIn('subclassID', $teacherSubclasses)
                            ->whereIn('status', ['pending', 'rejected']);
                    })
                    ->orderBy('approval_order')
                    ->first();
                
                if ($classTeacherApproval) {
                    $resultApproval = $classTeacherApproval;
                }
            }
            
            // Check for coordinator approval
            if (!$resultApproval && !empty($teacherMainClasses)) {
                $coordinatorApproval = ResultApproval::where('examID', $examID)
                    ->where('special_role_type', 'coordinator')
                    ->whereIn('status', ['pending', 'rejected'])
                    ->whereHas('coordinatorApprovals', function($query) use ($teacherMainClasses) {
                        $query->whereIn('mainclassID', $teacherMainClasses)
                            ->whereIn('status', ['pending', 'rejected']);
                    })
                    ->orderBy('approval_order')
                    ->first();
                
                if ($coordinatorApproval) {
                    $resultApproval = $coordinatorApproval;
                }
            }
        }

        if (!$resultApproval) {
            return response()->json(['error' => 'No pending approval found'], 404);
        }
        
        // Determine if this is a special role approval
        $isClassTeacherApproval = $resultApproval->special_role_type === 'class_teacher';
        $isCoordinatorApproval = $resultApproval->special_role_type === 'coordinator';
        
        // Get selected subclass/mainclass from request (for special roles)
        $selectedSubclassID = $request->input('subclass_id');
        $selectedMainClassID = $request->input('main_class_id');

        // Check if all previous approvals are completed
        $previousApprovals = ResultApproval::where('examID', $examID)
            ->where('approval_order', '<', $resultApproval->approval_order)
            ->get();

        if ($previousApprovals->isNotEmpty() && !$previousApprovals->every(function($prev) {
            return $prev->status === 'approved';
        })) {
            return response()->json(['error' => 'Previous approvals in the chain must be completed first'], 403);
        }

        DB::beginTransaction();
        try {
            if ($request->action === 'approve') {
                // Handle special role approvals
                if ($isClassTeacherApproval) {
                    // Update specific class_teacher_approval
                    if ($selectedSubclassID && in_array($selectedSubclassID, $teacherSubclasses)) {
                        ClassTeacherApproval::where('result_approvalID', $resultApproval->result_approvalID)
                            ->where('subclassID', $selectedSubclassID)
                            ->update([
                                'status' => 'approved',
                                'approved_by' => $teacherID,
                                'approved_at' => now(),
                                'approval_comment' => $request->approval_comment ?? null,
                                'rejection_reason' => null,
                            ]);
                    } else {
                        return response()->json(['error' => 'Invalid subclass selected'], 403);
                    }
                    
                    // Check if all class_teacher_approvals for this result_approval are approved
                    $allClassTeacherApprovals = ClassTeacherApproval::where('result_approvalID', $resultApproval->result_approvalID)
                        ->get();
                    
                    $allApproved = $allClassTeacherApprovals->every(function($cta) {
                        return $cta->status === 'approved';
                    });
                    
                    if ($allApproved) {
                        // All class teachers have approved, mark result_approval as approved
                        $resultApproval->update([
                            'status' => 'approved',
                            'approved_by' => $teacherID,
                            'approved_at' => now(),
                            'approval_comment' => $request->approval_comment ?? null,
                            'rejection_reason' => null,
                        ]);
                    } else {
                        // Not all class teachers have approved yet, keep result_approval as pending
                        DB::commit();
                        return response()->json([
                            'success' => true,
                            'message' => 'Your approval has been recorded. Waiting for other class teachers to approve.',
                            'sent_count' => 0,
                            'failed_count' => 0
                        ], 200);
                    }
                } elseif ($isCoordinatorApproval) {
                    // Update specific coordinator_approval
                    if ($selectedMainClassID && in_array($selectedMainClassID, $teacherMainClasses)) {
                        CoordinatorApproval::where('result_approvalID', $resultApproval->result_approvalID)
                            ->where('mainclassID', $selectedMainClassID)
                            ->update([
                                'status' => 'approved',
                                'approved_by' => $teacherID,
                                'approved_at' => now(),
                                'approval_comment' => $request->approval_comment ?? null,
                                'rejection_reason' => null,
                            ]);
                    } else {
                        return response()->json(['error' => 'Invalid mainclass selected'], 403);
                    }
                    
                    // Check if all coordinator_approvals for this result_approval are approved
                    $allCoordinatorApprovals = CoordinatorApproval::where('result_approvalID', $resultApproval->result_approvalID)
                        ->get();
                    
                    $allApproved = $allCoordinatorApprovals->every(function($ca) {
                        return $ca->status === 'approved';
                    });
                    
                    if ($allApproved) {
                        // All coordinators have approved, mark result_approval as approved
                        $resultApproval->update([
                            'status' => 'approved',
                            'approved_by' => $teacherID,
                            'approved_at' => now(),
                            'approval_comment' => $request->approval_comment ?? null,
                            'rejection_reason' => null,
                        ]);
                    } else {
                        // Not all coordinators have approved yet, keep result_approval as pending
                        DB::commit();
                        return response()->json([
                            'success' => true,
                            'message' => 'Your approval has been recorded. Waiting for other coordinators to approve.',
                            'sent_count' => 0,
                            'failed_count' => 0
                        ], 200);
                    }
                } else {
                    // Regular role approval
                $resultApproval->update([
                    'status' => 'approved',
                    'approved_by' => $teacherID,
                    'approved_at' => now(),
                    'approval_comment' => $request->approval_comment ?? null,
                    'rejection_reason' => null, // Clear rejection reason if any
                ]);
                }

                // Reset all subsequent approvals to pending (in case they were rejected before)
                ResultApproval::where('examID', $examID)
                    ->where('approval_order', '>', $resultApproval->approval_order)
                    ->update([
                        'status' => 'pending',
                        'approved_by' => null,
                        'approved_at' => null,
                        'approval_comment' => null,
                        'rejection_reason' => null,
                    ]);

                // Get examination details
                $examination = Examination::find($examID);
                $school = School::find($schoolID);
                $schoolName = $school ? $school->school_name : 'ShuleLink';
                $examName = $examination ? $examination->exam_name : 'Examination';

                // Get all active teachers in the school
                $allTeachers = Teacher::where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->whereNotNull('phone_number')
                    ->where('phone_number', '!=', '')
                    ->get();

                // Send SMS to all teachers about approval
                $smsService = new SmsService();
                $sentCount = 0;
                $failedCount = 0;

                $approvalMessage = "{$schoolName}. Matokeo ya mtihani '{$examName}' yameidhinishwa kikamilifu. Asante!";

                foreach ($allTeachers as $teacher) {
                    try {
                        $result = $smsService->sendSms($teacher->phone_number, $approvalMessage);
                        if ($result['success']) {
                            $sentCount++;
                        } else {
                            $failedCount++;
                            Log::warning("Failed to send approval SMS to teacher {$teacher->id}: " . ($result['message'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Error sending approval SMS to teacher {$teacher->id}: " . $e->getMessage());
                    }
                }

                // Also send SMS to next approver if exists
                $nextApproval = ResultApproval::where('examID', $examID)
                    ->where('approval_order', $resultApproval->approval_order + 1)
                    ->first();

                if ($nextApproval) {
                    $teachersWithNextRole = DB::table('role_user')
                        ->join('teachers', 'role_user.teacher_id', '=', 'teachers.id')
                        ->where('role_user.role_id', $nextApproval->role_id)
                        ->where('teachers.schoolID', $schoolID)
                        ->where('teachers.status', 'Active')
                        ->select('teachers.id', 'teachers.phone_number', 'teachers.first_name', 'teachers.last_name')
                        ->get();

                    foreach ($teachersWithNextRole as $teacher) {
                        $message = "Habari {$teacher->first_name}, umeteuliwa kuapprove matokeo ya mtihani: {$examName}. Tafadhali fanya approval wakwanza ili wengine waweze kuendelea.";
                        try {
                            $smsService->sendSms($teacher->phone_number, $message);
                        } catch (\Exception $e) {
                            Log::error('Failed to send SMS to next approver: '.$e->getMessage());
                        }
                    }
                }

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Results approved successfully! SMS sent to ' . $sentCount . ' teacher(s).',
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount
                ], 200);
            } else {
                // Rejection: Handle special role rejections
                if ($isClassTeacherApproval) {
                    // Update specific class_teacher_approval
                    if ($selectedSubclassID && in_array($selectedSubclassID, $teacherSubclasses)) {
                        ClassTeacherApproval::where('result_approvalID', $resultApproval->result_approvalID)
                            ->where('subclassID', $selectedSubclassID)
                            ->update([
                                'status' => 'rejected',
                                'approved_by' => $teacherID,
                                'approved_at' => now(),
                                'rejection_reason' => $request->rejection_reason ?? null,
                                'approval_comment' => null,
                            ]);
                        
                        // Mark result_approval as rejected if any class_teacher_approval is rejected
                        $resultApproval->update([
                            'status' => 'rejected',
                            'approved_by' => $teacherID,
                            'approved_at' => now(),
                            'rejection_reason' => $request->rejection_reason ?? null,
                            'approval_comment' => null,
                        ]);
                    } else {
                        return response()->json(['error' => 'Invalid subclass selected'], 403);
                    }
                } elseif ($isCoordinatorApproval) {
                    // Update specific coordinator_approval
                    if ($selectedMainClassID && in_array($selectedMainClassID, $teacherMainClasses)) {
                        CoordinatorApproval::where('result_approvalID', $resultApproval->result_approvalID)
                            ->where('mainclassID', $selectedMainClassID)
                            ->update([
                                'status' => 'rejected',
                                'approved_by' => $teacherID,
                                'approved_at' => now(),
                                'rejection_reason' => $request->rejection_reason ?? null,
                                'approval_comment' => null,
                            ]);
                        
                        // Mark result_approval as rejected if any coordinator_approval is rejected
                        $resultApproval->update([
                            'status' => 'rejected',
                            'approved_by' => $teacherID,
                            'approved_at' => now(),
                            'rejection_reason' => $request->rejection_reason ?? null,
                            'approval_comment' => null,
                        ]);
                    } else {
                        return response()->json(['error' => 'Invalid mainclass selected'], 403);
                    }
                } else {
                    // Regular role rejection
                $resultApproval->update([
                    'status' => 'rejected',
                    'approved_by' => $teacherID,
                    'approved_at' => now(),
                    'rejection_reason' => $request->rejection_reason ?? null,
                    'approval_comment' => null, // Clear approval comment
                ]);
                }

                // Reset all subsequent approvals to pending
                ResultApproval::where('examID', $examID)
                    ->where('approval_order', '>', $resultApproval->approval_order)
                    ->update([
                        'status' => 'pending',
                        'approved_by' => null,
                        'approved_at' => null,
                        'approval_comment' => null,
                        'rejection_reason' => null,
                    ]);

                // Get examination details
                $examination = Examination::find($examID);
                $school = School::find($schoolID);
                $schoolName = $school ? $school->school_name : 'ShuleLink';
                $examName = $examination ? $examination->exam_name : 'Examination';
                $rejectionReason = $request->rejection_reason ?? 'No reason provided';

                // Get all active teachers in the school
                $allTeachers = Teacher::where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->whereNotNull('phone_number')
                    ->where('phone_number', '!=', '')
                    ->get();

                // Send SMS to all teachers about rejection
                $smsService = new SmsService();
                $sentCount = 0;
                $failedCount = 0;

                $rejectionMessage = "{$schoolName}. Matokeo ya mtihani '{$examName}' yamekataliwa. Sababu: {$rejectionReason}. Tafadhali fanya mabadiliko yaliyoombwa kisha subiri kuapprove tena.";

                foreach ($allTeachers as $teacher) {
                    try {
                        $result = $smsService->sendSms($teacher->phone_number, $rejectionMessage);
                        if ($result['success']) {
                            $sentCount++;
                        } else {
                            $failedCount++;
                            Log::warning("Failed to send rejection SMS to teacher {$teacher->id}: " . ($result['message'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Error sending rejection SMS to teacher {$teacher->id}: " . $e->getMessage());
                    }
                }

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Results rejected. SMS sent to ' . $sentCount . ' teacher(s). Wait for improvement to approve.',
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate statistics for examination results
     */
    private function calculateExamStatistics($results, $schoolType, $schoolID)
    {
        if ($results->isEmpty()) {
            return [
                'total_students' => 0,
                'total_results' => 0,
                'average_marks' => 0,
                'male_average' => 0,
                'female_average' => 0,
                'grade_stats' => [],
                'division_stats' => [],
                'male_grade_stats' => [],
                'female_grade_stats' => [],
                'male_division_stats' => [],
                'female_division_stats' => [],
                'pass_rate' => 0,
                'fail_rate' => 0,
            ];
        }

        // Get unique students
        $students = $results->pluck('student')->unique('studentID');
        $totalStudents = $students->count();
        $totalResults = $results->count();

        // Calculate total marks and averages
        $totalMarks = 0;
        $marksCount = 0;
        $maleTotalMarks = 0;
        $maleCount = 0;
        $femaleTotalMarks = 0;
        $femaleCount = 0;

        // Statistics arrays
        $gradeStats = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
        $divisionStats = ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0];
        $maleGradeStats = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
        $femaleGradeStats = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
        $maleDivisionStats = ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0];
        $femaleDivisionStats = ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0];

        // Group results by student to calculate per-student statistics
        $studentResults = $results->groupBy('studentID');
        $passedCount = 0;
        $failedCount = 0;

        foreach ($studentResults as $studentID => $studentResultList) {
            $firstResult = $studentResultList->first();
            if (!$firstResult) {
                continue;
            }
            $student = $firstResult->student;
            if (!$student) {
                continue;
            }
            $gender = $student->gender ?? '';

            // Calculate student's total marks and average
            $studentTotalMarks = 0;
            $studentSubjectCount = 0;
            foreach ($studentResultList as $result) {
                if ($result->marks !== null && $result->marks !== '') {
                    $marks = (float)$result->marks;
                    $studentTotalMarks += $marks;
                    $studentSubjectCount++;
                    $totalMarks += $marks;
                    $marksCount++;

                    if ($gender === 'Male') {
                        $maleTotalMarks += $marks;
                        $maleCount++;
                    } elseif ($gender === 'Female') {
                        $femaleTotalMarks += $marks;
                        $femaleCount++;
                    }
                }
            }

            if ($studentSubjectCount > 0) {
                $studentAverage = $studentTotalMarks / $studentSubjectCount;

                // Calculate grade/division for student
                if ($schoolType === 'Primary') {
                    // Primary: Grade based on average
                    if ($studentAverage >= 75) {
                        $grade = 'A';
                    } elseif ($studentAverage >= 65) {
                        $grade = 'B';
                    } elseif ($studentAverage >= 45) {
                        $grade = 'C';
                    } elseif ($studentAverage >= 30) {
                        $grade = 'D';
                    } else {
                        $grade = 'F';
                    }

                    $gradeStats[$grade]++;
                    if ($gender === 'Male') {
                        $maleGradeStats[$grade]++;
                    } elseif ($gender === 'Female') {
                        $femaleGradeStats[$grade]++;
                    }

                    // Pass/Fail for Primary
                    if ($studentAverage >= 30) {
                        $passedCount++;
                    } else {
                        $failedCount++;
                    }
                } else {
                    // Secondary: Calculate division based on points
                    // Get class name
                    $className = '';
                    if ($student && $student->subclass && $student->subclass->class) {
                        $className = $student->subclass->class->class_name ?? '';
                    }

                    // Calculate points for each subject
                    $subjectPoints = [];
                    foreach ($studentResultList as $result) {
                        if ($result->marks !== null && $result->marks !== '') {
                            $points = $this->calculateGradePointsForResult($result->marks, $schoolType, $className);
                            if ($points !== null) {
                                $subjectPoints[] = $points;
                            }
                        }
                    }

                    // Calculate total points (best 7 for O-Level, best 3 for A-Level)
                    $totalPoints = 0;
                    $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));
                    if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        // O-Level: Best 7
                        sort($subjectPoints);
                        $bestSubjects = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
                        $totalPoints = array_sum($bestSubjects);
                    } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
                        // A-Level: Best 3
                        rsort($subjectPoints);
                        $bestSubjects = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                        $totalPoints = array_sum($bestSubjects);
                    } else {
                        $totalPoints = array_sum($subjectPoints);
                    }

                    // Calculate division
                    $division = $this->calculateDivisionFromPoints($totalPoints, $classNameLower);
                    $divisionNum = $this->extractDivisionNumber($division);
                    $divisionStats[$divisionNum]++;
                    
                    if ($gender === 'Male') {
                        $maleDivisionStats[$divisionNum]++;
                    } elseif ($gender === 'Female') {
                        $femaleDivisionStats[$divisionNum]++;
                    }

                    // Pass/Fail for Secondary
                    if (preg_match('/^[IVX]+\./', $division)) {
                        $passedCount++;
                    } elseif (preg_match('/^0\./', $division)) {
                        $failedCount++;
                    }
                }
            }
        }

        $averageMarks = $marksCount > 0 ? $totalMarks / $marksCount : 0;
        $maleAverage = $maleCount > 0 ? $maleTotalMarks / $maleCount : 0;
        $femaleAverage = $femaleCount > 0 ? $femaleTotalMarks / $femaleCount : 0;
        $passRate = $totalStudents > 0 ? ($passedCount / $totalStudents) * 100 : 0;
        $failRate = $totalStudents > 0 ? ($failedCount / $totalStudents) * 100 : 0;

        return [
            'total_students' => $totalStudents,
            'total_results' => $totalResults,
            'average_marks' => $averageMarks,
            'male_average' => $maleAverage,
            'female_average' => $femaleAverage,
            'grade_stats' => $gradeStats,
            'division_stats' => $divisionStats,
            'male_grade_stats' => $maleGradeStats,
            'female_grade_stats' => $femaleGradeStats,
            'male_division_stats' => $maleDivisionStats,
            'female_division_stats' => $femaleDivisionStats,
            'pass_rate' => $passRate,
            'fail_rate' => $failRate,
        ];
    }

    /**
     * Get grade from grade_definitions table based on classID and marks
     */
    private function getGradeFromDefinition($marks, $classID)
    {
        if ($marks === null || $marks === '' || !$classID) {
            return ['grade' => null, 'points' => null];
        }

        $marksNum = (float) $marks;

        // Get grade definition from database
        $gradeDefinition = GradeDefinition::where('classID', $classID)
            ->where('first', '<=', $marksNum)
            ->where('last', '>=', $marksNum)
            ->first();

        if (!$gradeDefinition) {
            return ['grade' => null, 'points' => null];
        }

        // Calculate points based on grade (for backward compatibility)
        $grade = $gradeDefinition->grade;
        $points = null;

        // Points calculation for O-Level and A-Level (maintain existing logic)
        $className = ClassModel::find($classID)->class_name ?? '';
        $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));

        if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
            // O-Level points
            $pointsMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'F' => 5];
            $points = $pointsMap[$grade] ?? 5;
        } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
            // A-Level points
            $pointsMap = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'S/F' => 0];
            $points = $pointsMap[$grade] ?? 0;
        }

        return ['grade' => $grade, 'points' => $points];
    }

    /**
     * Calculate grade points for a result
     */
    private function calculateGradePointsForResult($marks, $schoolType, $className = '', $classID = null)
    {
        if ($marks === null || $marks === '') {
            return null;
        }

        if ($schoolType === 'Primary') {
            // Primary doesn't use points
            return null;
        }

        // If classID is provided, use grade_definitions table
        if ($classID) {
            $gradeResult = $this->getGradeFromDefinition($marks, $classID);
            return $gradeResult;
        }

        // Fallback to old logic if classID not provided
        $marksNum = (float)$marks;
        $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));

        // Secondary: Calculate points based on marks
        if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
            // O-Level points
        if ($marksNum >= 75) {
                return ['grade' => 'A', 'points' => 1];
        } elseif ($marksNum >= 65) {
                return ['grade' => 'B', 'points' => 2];
        } elseif ($marksNum >= 45) {
                return ['grade' => 'C', 'points' => 3];
        } elseif ($marksNum >= 30) {
                return ['grade' => 'D', 'points' => 4];
        } else {
                return ['grade' => 'F', 'points' => 5];
            }
        } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
            // A-Level points
            if ($marksNum >= 80) {
                return ['grade' => 'A', 'points' => 5];
            } elseif ($marksNum >= 70) {
                return ['grade' => 'B', 'points' => 4];
            } elseif ($marksNum >= 60) {
                return ['grade' => 'C', 'points' => 3];
            } elseif ($marksNum >= 50) {
                return ['grade' => 'D', 'points' => 2];
            } elseif ($marksNum >= 40) {
                return ['grade' => 'E', 'points' => 1];
            } else {
                return ['grade' => 'S/F', 'points' => 0];
            }
        }

        return null;
    }

    /**
     * Calculate division from total points
     */
    private function calculateDivisionFromPoints($totalPoints, $classNameLower)
    {
        if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
            // O-Level Division
            if ($totalPoints >= 7 && $totalPoints <= 17) {
                return 'I.' . $totalPoints;
            } elseif ($totalPoints >= 18 && $totalPoints <= 21) {
                return 'II.' . $totalPoints;
            } elseif ($totalPoints >= 22 && $totalPoints <= 25) {
                return 'III.' . $totalPoints;
            } elseif ($totalPoints >= 26 && $totalPoints <= 33) {
                return 'IV.' . $totalPoints;
            } else {
                return '0.' . $totalPoints;
            }
        } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
            // A-Level Division (matching ResultManagementController logic)
            if ($totalPoints >= 12 && $totalPoints <= 15) {
                return 'I.' . $totalPoints;
            } elseif ($totalPoints >= 9 && $totalPoints <= 11) {
                return 'II.' . $totalPoints;
            } elseif ($totalPoints >= 6 && $totalPoints <= 8) {
                return 'III.' . $totalPoints;
            } elseif ($totalPoints >= 3 && $totalPoints <= 5) {
                return 'IV.' . $totalPoints;
            } else {
                return '0.' . $totalPoints;
            }
        } else {
            // Default: use O-Level logic
            if ($totalPoints >= 7 && $totalPoints <= 17) {
                return 'I.' . $totalPoints;
            } elseif ($totalPoints >= 18 && $totalPoints <= 21) {
                return 'II.' . $totalPoints;
            } elseif ($totalPoints >= 22 && $totalPoints <= 25) {
                return 'III.' . $totalPoints;
            } elseif ($totalPoints >= 26 && $totalPoints <= 33) {
                return 'IV.' . $totalPoints;
            } else {
                return '0.' . $totalPoints;
            }
        }
    }

    /**
     * Extract division number from division string (e.g., "I.7" -> "I", "0.34" -> "0")
     */
    private function extractDivisionNumber($division)
    {
        if (preg_match('/^([IVX0]+)\./', $division, $matches)) {
            return $matches[1];
        }
        return '0';
    }

    /**
     * Calculate comprehensive statistics for results
     */
    private function calculateResultStatistics($formattedResults, $schoolType, $allResults, $schoolID)
    {
        try {
            // Initialize statistics arrays
            $gradeStats = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
            $divisionStats = ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0];
            $gradeGenderStats = [
                'A' => ['Male' => 0, 'Female' => 0],
                'B' => ['Male' => 0, 'Female' => 0],
                'C' => ['Male' => 0, 'Female' => 0],
                'D' => ['Male' => 0, 'Female' => 0],
                'E' => ['Male' => 0, 'Female' => 0],
                'F' => ['Male' => 0, 'Female' => 0]
            ];
            $divisionGenderStats = [
                'I' => ['Male' => 0, 'Female' => 0],
                'II' => ['Male' => 0, 'Female' => 0],
                'III' => ['Male' => 0, 'Female' => 0],
                'IV' => ['Male' => 0, 'Female' => 0],
                '0' => ['Male' => 0, 'Female' => 0]
            ];
            $classStats = [];
            
            // Get student genders from formatted results (already included in the data)
            
            // Process each class
            foreach ($formattedResults as $classResult) {
            $className = $classResult['class_name'];
            $subclassName = $classResult['subclass_name'];
            $classKey = $className . ' - ' . $subclassName;
            
            // Initialize class statistics
            if (!isset($classStats[$classKey])) {
                $classStats[$classKey] = [
                    'class_name' => $className,
                    'subclass_name' => $subclassName,
                    'grades' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0],
                    'divisions' => ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0],
                    'total_students' => 0
                ];
            }
            
            // Process each student in the class
            foreach ($classResult['students'] as $student) {
                $studentID = $student['studentID'];
                // Normalize gender - handle different formats
                $rawGender = $student['gender'] ?? null;
                $studentGender = 'Unknown';
                
                if ($rawGender) {
                    $rawGender = trim((string)$rawGender);
                    // Database uses exact 'Male' or 'Female' as ENUM
                    if (strcasecmp($rawGender, 'Male') === 0 || strcasecmp($rawGender, 'M') === 0) {
                        $studentGender = 'Male';
                    } elseif (strcasecmp($rawGender, 'Female') === 0 || strcasecmp($rawGender, 'F') === 0) {
                        $studentGender = 'Female';
                    } else {
                        // Fallback: check if contains the word
                        if (stripos($rawGender, 'male') !== false && stripos($rawGender, 'female') === false) {
                            $studentGender = 'Male';
                        } elseif (stripos($rawGender, 'female') !== false) {
                            $studentGender = 'Female';
                        } else {
                            // Log for debugging if we get unexpected values
                            \Illuminate\Support\Facades\Log::debug("Unexpected gender value: '{$rawGender}' for student ID: {$studentID}");
                        }
                    }
                } else {
                    // If gender is null/empty in student array, try to get it from database directly
                    try {
                        $studentRecord = DB::table('students')
                            ->where('studentID', $studentID)
                            ->select('gender')
                            ->first();
                        if ($studentRecord && $studentRecord->gender) {
                            $rawGender = trim((string)$studentRecord->gender);
                            if (strcasecmp($rawGender, 'Male') === 0) {
                                $studentGender = 'Male';
                            } elseif (strcasecmp($rawGender, 'Female') === 0) {
                                $studentGender = 'Female';
                            }
                        }
                    } catch (\Exception $e) {
                        // Silently continue if we can't get gender
                    }
                }
                
                $classStats[$classKey]['total_students']++;
                
                if ($schoolType === 'Primary') {
                    // Primary school: use grades
                    $grade = $student['grade'] ?? 'N/A';
                    if (isset($gradeStats[$grade])) {
                        $gradeStats[$grade]++;
                        $classStats[$classKey]['grades'][$grade]++;
                        
                        // Count gender statistics
                        if ($studentGender === 'Male') {
                            $gradeGenderStats[$grade]['Male']++;
                        } elseif ($studentGender === 'Female') {
                            $gradeGenderStats[$grade]['Female']++;
                        }
                    }
                } else {
                    // Secondary school: use divisions
                    $division = $student['division'] ?? null;
                    if ($division) {
                        $divNum = $this->extractDivisionNumber($division);
                        if (isset($divisionStats[$divNum])) {
                            $divisionStats[$divNum]++;
                            $classStats[$classKey]['divisions'][$divNum]++;
                            
                            // Count gender statistics - include Unknown to match totals
                            if ($studentGender === 'Male') {
                                $divisionGenderStats[$divNum]['Male']++;
                            } elseif ($studentGender === 'Female') {
                                $divisionGenderStats[$divNum]['Female']++;
                            }
                            // Note: If gender is Unknown, it's counted in divisionStats but not in gender breakdown
                            // This ensures division total matches Male + Female + Unknown
                        }
                    }
                    
                    // Also track grades for secondary (subject-level grades)
                    // But for overall statistics, we focus on divisions
                }
            }
        }
        
        // Calculate average marks per class (for bar graph when filtering all classes)
        // Formula: For each student, calculate average marks (total marks / number of subjects)
        // Then calculate class average (sum of student averages / number of students)
        $classPassRates = [];
        
        // Group students by class from formattedResults
        $classStudentsData = [];
        foreach ($formattedResults as $classResult) {
            $subclassID = $classResult['subclassID'] ?? null;
            $classKey = $subclassID ? 'subclass_' . $subclassID : 'class_' . ($classResult['classID'] ?? 'unknown');
            $className = $classResult['class_name'] ?? 'Unknown';
            $subclassName = $classResult['subclass_name'] ?? null;
            
            if (!isset($classStudentsData[$classKey])) {
                $classStudentsData[$classKey] = [
                    'class_name' => $className,
                    'subclass_name' => $subclassName,
                    'students' => []
                ];
            }
            
            if (isset($classResult['students']) && is_array($classResult['students'])) {
                foreach ($classResult['students'] as $student) {
                    $totalMarks = $student['total_marks'] ?? 0;
                    $subjectCount = $student['subject_count'] ?? 0;
                    
                    // Calculate average marks for this student
                    $studentAverage = 0;
                    if ($subjectCount > 0 && $totalMarks > 0) {
                        $studentAverage = $totalMarks / $subjectCount;
                    }
                    
                    $classStudentsData[$classKey]['students'][] = [
                        'studentID' => $student['studentID'] ?? null,
                        'total_marks' => $totalMarks,
                        'subject_count' => $subjectCount,
                        'average_marks' => $studentAverage
                    ];
                }
            }
        }
        
        // Calculate class average for each class
        foreach ($classStudentsData as $classKey => $classData) {
            $students = $classData['students'];
            $totalStudents = count($students);
            
            if ($totalStudents > 0) {
                // Sum all student averages
                $sumOfAverages = 0;
                $validStudents = 0;
                
                foreach ($students as $student) {
                    if ($student['subject_count'] > 0 && $student['average_marks'] > 0) {
                        $sumOfAverages += $student['average_marks'];
                        $validStudents++;
                    }
                }
                
                // Calculate class average
                $classAverage = 0;
                if ($validStudents > 0) {
                    $classAverage = $sumOfAverages / $validStudents;
                }
                
                $classPassRates[$classKey] = [
                    'class_name' => $classData['class_name'],
                    'subclass_name' => $classData['subclass_name'],
                    'pass_rate' => round($classAverage, 2), // Using pass_rate field to store average marks
                    'average_marks' => round($classAverage, 2),
                    'passed' => $validStudents,
                    'total' => $totalStudents
                ];
            }
        }
        
            // Sort class pass rates by pass rate (highest to lowest)
            usort($classPassRates, function($a, $b) {
                return $b['pass_rate'] <=> $a['pass_rate'];
            });
            
            // Calculate per-subject statistics using formattedResults (more reliable - has subject data)
            try {
                \Illuminate\Support\Facades\Log::debug("Starting calculateSubjectStatisticsFromFormatted. formattedResults count: " . count($formattedResults));
                $subjectStats = $this->calculateSubjectStatisticsFromFormatted($formattedResults, $schoolID);
                \Illuminate\Support\Facades\Log::debug("calculateSubjectStatisticsFromFormatted completed. Stats count: " . count($subjectStats));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error calculating subject statistics: ' . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | Trace: ' . $e->getTraceAsString());
                $subjectStats = [];
            }
            
            return [
                'grade_stats' => $gradeStats,
                'division_stats' => $divisionStats,
                'grade_gender_stats' => $gradeGenderStats,
                'division_gender_stats' => $divisionGenderStats,
                'class_stats' => $classStats,
                'class_pass_rates' => $classPassRates,
                'subject_stats' => $subjectStats,
                'school_type' => $schoolType
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in calculateResultStatistics: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            // Return empty statistics on error
            return [
                'grade_stats' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0],
                'division_stats' => ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0],
                'grade_gender_stats' => [
                    'A' => ['Male' => 0, 'Female' => 0],
                    'B' => ['Male' => 0, 'Female' => 0],
                    'C' => ['Male' => 0, 'Female' => 0],
                    'D' => ['Male' => 0, 'Female' => 0],
                    'E' => ['Male' => 0, 'Female' => 0],
                    'F' => ['Male' => 0, 'Female' => 0]
                ],
                'division_gender_stats' => [
                    'I' => ['Male' => 0, 'Female' => 0],
                    'II' => ['Male' => 0, 'Female' => 0],
                    'III' => ['Male' => 0, 'Female' => 0],
                    'IV' => ['Male' => 0, 'Female' => 0],
                    '0' => ['Male' => 0, 'Female' => 0]
                ],
                'class_stats' => [],
                'class_pass_rates' => [],
                'subject_stats' => [],
                'school_type' => $schoolType
            ];
        }
    }

    /**
     * Calculate statistics per subject (grade distribution with gender breakdown)
     */
    private function calculateSubjectStatistics($allResults, $schoolID)
    {
        $subjectStats = [];
        
        // Log initial state
        \Illuminate\Support\Facades\Log::debug("calculateSubjectStatistics: Total results: " . $allResults->count());
        
        if ($allResults->isEmpty()) {
            \Illuminate\Support\Facades\Log::debug("calculateSubjectStatistics: No results to process");
            return $subjectStats;
        }
        
        // Debug: Check first few results to see their structure
        $sampleCount = min(3, $allResults->count());
        for ($i = 0; $i < $sampleCount; $i++) {
            $sample = $allResults->get($i);
            if ($sample) {
                \Illuminate\Support\Facades\Log::debug("Sample result #{$i}: subject_name=" . ($sample->subject_name ?? 'NULL') . ", marks=" . ($sample->marks ?? 'NULL') . ", studentID=" . ($sample->studentID ?? 'NULL') . ", classID=" . ($sample->main_class_id ?? $sample->classID ?? 'NULL'));
            }
        }
        
        // Get all student IDs and their genders
        $studentIds = [];
        foreach ($allResults as $result) {
            try {
                $studentID = $result->studentID ?? null;
                if ($studentID) {
                    $studentIds[] = $studentID;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        $studentIds = array_unique($studentIds);
        \Illuminate\Support\Facades\Log::debug("calculateSubjectStatistics: Unique student IDs: " . count($studentIds));
        
        $studentGenders = [];
        if (!empty($studentIds)) {
            $genderResults = DB::table('students')
                ->whereIn('studentID', $studentIds)
                ->select('studentID', 'gender')
                ->get();
            
            foreach ($genderResults as $genderResult) {
                $rawGender = $genderResult->gender ?? null;
                if ($rawGender) {
                    $rawGender = trim((string)$rawGender);
                    if (strcasecmp($rawGender, 'Male') === 0) {
                        $studentGenders[$genderResult->studentID] = 'Male';
                    } elseif (strcasecmp($rawGender, 'Female') === 0) {
                        $studentGenders[$genderResult->studentID] = 'Female';
                    } else {
                        $studentGenders[$genderResult->studentID] = 'Unknown';
                    }
                } else {
                    $studentGenders[$genderResult->studentID] = 'Unknown';
                }
            }
        }
        
        // Group results by subject
        $resultsBySubject = [];
        $skippedCount = 0;
        $processedCount = 0;
        
        foreach ($allResults as $result) {
            try {
                // $allResults is a collection from DB query, so it's an object
                $subjectName = $result->subject_name ?? null;
                $marks = $result->marks ?? null;
                $classID = $result->main_class_id ?? $result->classID ?? null;
                $studentID = $result->studentID ?? null;
                
                // Debug: log if subject_name is missing
                if (!$subjectName || trim($subjectName) === '') {
                    $skippedCount++;
                    if ($skippedCount <= 5) { // Log first 5 only to avoid spam
                        \Illuminate\Support\Facades\Log::debug("Subject name missing. studentID: {$studentID}, marks: {$marks}, classID: {$classID}");
                    }
                    continue;
                }
                
                if ($marks === null || $classID === null || !$studentID) {
                    $skippedCount++;
                    continue;
                }
                
                // Trim and validate subject name
                $subjectName = trim($subjectName);
                if (empty($subjectName) || $subjectName === 'Unknown') {
                    $skippedCount++;
                    continue;
                }
                
                if (!isset($resultsBySubject[$subjectName])) {
                    $resultsBySubject[$subjectName] = [
                        'subject_name' => $subjectName,
                        'results' => [],
                        'classID' => $classID
                    ];
                }
                
                $resultsBySubject[$subjectName]['results'][] = [
                    'marks' => (float)$marks,
                    'classID' => $classID,
                    'studentID' => $studentID
                ];
                $processedCount++;
            } catch (\Exception $e) {
                $skippedCount++;
                \Illuminate\Support\Facades\Log::error("Error processing result in calculateSubjectStatistics: " . $e->getMessage());
                continue;
            }
        }
        
        \Illuminate\Support\Facades\Log::debug("calculateSubjectStatistics: Processed: {$processedCount}, Skipped: {$skippedCount}, Subjects found: " . count($resultsBySubject));
        
        // Calculate grade distribution for each subject with gender breakdown
        foreach ($resultsBySubject as $subjectName => $subjectData) {
            $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
            $gradeGenderCounts = [
                'A' => ['Male' => 0, 'Female' => 0],
                'B' => ['Male' => 0, 'Female' => 0],
                'C' => ['Male' => 0, 'Female' => 0],
                'D' => ['Male' => 0, 'Female' => 0],
                'E' => ['Male' => 0, 'Female' => 0],
                'F' => ['Male' => 0, 'Female' => 0]
            ];
            $classID = $subjectData['classID'];
            
            foreach ($subjectData['results'] as $resultData) {
                $marks = $resultData['marks'];
                $resultClassID = $resultData['classID'] ?? $classID;
                $studentID = $resultData['studentID'];
                $gender = $studentGenders[$studentID] ?? 'Unknown';
                
                // Get grade from grade_definitions table
                $gradeDefinition = GradeDefinition::where('classID', $resultClassID)
                    ->where('first', '<=', $marks)
                    ->where('last', '>=', $marks)
                    ->first();
                
                $grade = null;
                if ($gradeDefinition) {
                    $grade = strtoupper(trim($gradeDefinition->grade));
                    if (!isset($gradeCounts[$grade])) {
                        // If grade is not A-F, try to map it
                        if (in_array($grade, ['A+', 'A-'])) {
                            $grade = 'A';
                        } elseif (in_array($grade, ['B+', 'B-'])) {
                            $grade = 'B';
                        } elseif (in_array($grade, ['C+', 'C-'])) {
                            $grade = 'C';
                        } elseif (in_array($grade, ['D+', 'D-'])) {
                            $grade = 'D';
                        } elseif (in_array($grade, ['E+', 'E-'])) {
                            $grade = 'E';
                        } else {
                            $grade = 'F';
                        }
                    }
                } else {
                    // Fallback: calculate grade based on marks if no definition found
                    if ($marks >= 75) {
                        $grade = 'A';
                    } elseif ($marks >= 65) {
                        $grade = 'B';
                    } elseif ($marks >= 45) {
                        $grade = 'C';
                    } elseif ($marks >= 30) {
                        $grade = 'D';
                    } elseif ($marks >= 20) {
                        $grade = 'E';
                    } else {
                        $grade = 'F';
                    }
                }
                
                if ($grade && isset($gradeCounts[$grade])) {
                    $gradeCounts[$grade]++;
                    if ($gender === 'Male' || $gender === 'Female') {
                        $gradeGenderCounts[$grade][$gender]++;
                    }
                }
            }
            
            $totalStudents = array_sum($gradeCounts);
            \Illuminate\Support\Facades\Log::debug("Subject '{$subjectName}': Total students = {$totalStudents}, Results count = " . count($subjectData['results']));
            
            if ($totalStudents > 0) {
                $subjectStats[] = [
                    'subject_name' => $subjectName,
                    'grade_counts' => $gradeCounts,
                    'grade_gender_counts' => $gradeGenderCounts,
                    'total_students' => $totalStudents
                ];
            } else {
                \Illuminate\Support\Facades\Log::debug("Subject '{$subjectName}' skipped: totalStudents = 0");
            }
        }
        
        // Sort by subject name
        usort($subjectStats, function($a, $b) {
            return strcmp($a['subject_name'], $b['subject_name']);
        });
        
        // Log for debugging
        if (empty($subjectStats)) {
            \Illuminate\Support\Facades\Log::debug("No subject stats calculated. Total results: " . $allResults->count() . ", Processed: {$processedCount}, Skipped: {$skippedCount}, Subjects grouped: " . count($resultsBySubject));
            // Log sample of first result to see what we're getting
            if ($allResults->count() > 0) {
                $firstResult = $allResults->first();
                \Illuminate\Support\Facades\Log::debug("Sample result - subject_name: " . ($firstResult->subject_name ?? 'NULL') . ", marks: " . ($firstResult->marks ?? 'NULL') . ", classID: " . ($firstResult->main_class_id ?? $firstResult->classID ?? 'NULL'));
            }
        } else {
            \Illuminate\Support\Facades\Log::debug("Subject stats calculated: " . count($subjectStats) . " subjects");
        }
        
        return $subjectStats;
    }

    /**
     * Calculate subject statistics from formatted results (more reliable)
     */
    private function calculateSubjectStatisticsFromFormatted($formattedResults, $schoolID)
    {
        try {
            $subjectStats = [];
            $resultsBySubject = [];
            
            \Illuminate\Support\Facades\Log::debug("calculateSubjectStatisticsFromFormatted: formattedResults count = " . count($formattedResults));
            
            if (empty($formattedResults)) {
                \Illuminate\Support\Facades\Log::debug("calculateSubjectStatisticsFromFormatted: No formatted results");
                return $subjectStats;
            }
            
            // Debug: Check structure of first class result
            if (!empty($formattedResults)) {
                $firstClass = $formattedResults[0];
                \Illuminate\Support\Facades\Log::debug("First class result keys: " . implode(', ', array_keys($firstClass)));
                if (isset($firstClass['students']) && !empty($firstClass['students'])) {
                    $firstStudent = $firstClass['students'][0];
                    \Illuminate\Support\Facades\Log::debug("First student keys: " . implode(', ', array_keys($firstStudent)));
                    if (isset($firstStudent['subjects'])) {
                        \Illuminate\Support\Facades\Log::debug("First student has " . count($firstStudent['subjects']) . " subjects");
                        if (!empty($firstStudent['subjects'])) {
                            $firstSubject = $firstStudent['subjects'][0];
                            \Illuminate\Support\Facades\Log::debug("First subject keys: " . implode(', ', array_keys($firstSubject)));
                            \Illuminate\Support\Facades\Log::debug("First subject name: " . ($firstSubject['subject_name'] ?? 'NULL'));
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::debug("First student does NOT have 'subjects' key");
                    }
                }
            }
        
        // Get all student IDs and their genders
        $studentIds = [];
        $studentsWithSubjects = 0;
        foreach ($formattedResults as $classResult) {
            if (isset($classResult['students']) && is_array($classResult['students'])) {
                foreach ($classResult['students'] as $student) {
                    if (isset($student['studentID'])) {
                        $studentIds[] = $student['studentID'];
                        if (isset($student['subjects']) && is_array($student['subjects']) && count($student['subjects']) > 0) {
                            $studentsWithSubjects++;
                        }
                    }
                }
            }
        }
        
        \Illuminate\Support\Facades\Log::debug("calculateSubjectStatisticsFromFormatted: Total students = " . count(array_unique($studentIds)) . ", Students with subjects = " . $studentsWithSubjects);
        
        $studentIds = array_unique($studentIds);
        $studentGenders = [];
        if (!empty($studentIds)) {
            $genderResults = DB::table('students')
                ->whereIn('studentID', $studentIds)
                ->select('studentID', 'gender')
                ->get();
            
            foreach ($genderResults as $genderResult) {
                $rawGender = $genderResult->gender ?? null;
                if ($rawGender) {
                    $rawGender = trim((string)$rawGender);
                    if (strcasecmp($rawGender, 'Male') === 0) {
                        $studentGenders[$genderResult->studentID] = 'Male';
                    } elseif (strcasecmp($rawGender, 'Female') === 0) {
                        $studentGenders[$genderResult->studentID] = 'Female';
                    } else {
                        $studentGenders[$genderResult->studentID] = 'Unknown';
                    }
                } else {
                    $studentGenders[$genderResult->studentID] = 'Unknown';
                }
            }
        }
        
        // Extract subject data from formatted results
        $subjectsFound = 0;
        foreach ($formattedResults as $classResult) {
            $subclassID = $classResult['subclassID'] ?? null;
            $resultClassID = null;
            
            // Get classID from subclass
            if ($subclassID) {
                $subclass = DB::table('subclasses')
                    ->where('subclassID', $subclassID)
                    ->select('classID')
                    ->first();
                if ($subclass) {
                    $resultClassID = $subclass->classID;
                }
            }
            
            if (isset($classResult['students']) && is_array($classResult['students'])) {
                foreach ($classResult['students'] as $student) {
                    $studentID = $student['studentID'] ?? null;
                    $gender = $studentGenders[$studentID] ?? ($student['gender'] ?? 'Unknown');
                    
                    if (isset($student['subjects']) && is_array($student['subjects'])) {
                        foreach ($student['subjects'] as $subject) {
                            $subjectName = $subject['subject_name'] ?? null;
                            $subjectGrade = $subject['grade'] ?? null; // Grade is already in the subject array!
                            
                            if (!$subjectName || !$subjectGrade) {
                                continue;
                            }
                            
                            // Normalize grade to uppercase
                            $subjectGrade = strtoupper(trim($subjectGrade));
                            
                            // Skip if grade is not A-F, try to map variations
                            if (!in_array($subjectGrade, ['A', 'B', 'C', 'D', 'E', 'F'])) {
                                if (in_array($subjectGrade, ['A+', 'A-'])) {
                                    $subjectGrade = 'A';
                                } elseif (in_array($subjectGrade, ['B+', 'B-'])) {
                                    $subjectGrade = 'B';
                                } elseif (in_array($subjectGrade, ['C+', 'C-'])) {
                                    $subjectGrade = 'C';
                                } elseif (in_array($subjectGrade, ['D+', 'D-'])) {
                                    $subjectGrade = 'D';
                                } elseif (in_array($subjectGrade, ['E+', 'E-'])) {
                                    $subjectGrade = 'E';
                                } else {
                                    continue; // Skip unknown grades
                                }
                            }
                            
                            if (!isset($resultsBySubject[$subjectName])) {
                                $resultsBySubject[$subjectName] = [
                                    'subject_name' => $subjectName,
                                    'results' => []
                                ];
                            }
                            
                            $resultsBySubject[$subjectName]['results'][] = [
                                'grade' => $subjectGrade,
                                'studentID' => $studentID,
                                'gender' => $gender
                            ];
                            $subjectsFound++;
                        }
                    }
                }
            }
        }
        
        \Illuminate\Support\Facades\Log::debug("calculateSubjectStatisticsFromFormatted: Subjects found = " . $subjectsFound . ", Unique subjects = " . count($resultsBySubject));
        
        // Calculate grade distribution for each subject with gender breakdown
        foreach ($resultsBySubject as $subjectName => $subjectData) {
            $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
            $gradeGenderCounts = [
                'A' => ['Male' => 0, 'Female' => 0],
                'B' => ['Male' => 0, 'Female' => 0],
                'C' => ['Male' => 0, 'Female' => 0],
                'D' => ['Male' => 0, 'Female' => 0],
                'E' => ['Male' => 0, 'Female' => 0],
                'F' => ['Male' => 0, 'Female' => 0]
            ];
            
            foreach ($subjectData['results'] as $resultData) {
                $grade = $resultData['grade'] ?? null;
                $gender = $resultData['gender'] ?? 'Unknown';
                
                if ($grade && isset($gradeCounts[$grade])) {
                    $gradeCounts[$grade]++;
                    if ($gender === 'Male' || $gender === 'Female') {
                        $gradeGenderCounts[$grade][$gender]++;
                    }
                }
            }
            
            $totalStudents = array_sum($gradeCounts);
            if ($totalStudents > 0) {
                $subjectStats[] = [
                    'subject_name' => $subjectName,
                    'grade_counts' => $gradeCounts,
                    'grade_gender_counts' => $gradeGenderCounts,
                    'total_students' => $totalStudents
                ];
            }
            }
            
            // Sort by subject name
            usort($subjectStats, function($a, $b) {
                return strcmp($a['subject_name'], $b['subject_name']);
            });
            
            \Illuminate\Support\Facades\Log::debug("calculateSubjectStatisticsFromFormatted: Final subject stats count = " . count($subjectStats));
            if (count($subjectStats) > 0) {
                \Illuminate\Support\Facades\Log::debug("Sample subject: " . $subjectStats[0]['subject_name'] . " with " . $subjectStats[0]['total_students'] . " students");
            }
            
            return $subjectStats;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in calculateSubjectStatisticsFromFormatted: ' . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | Trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Check exam paper status for a specific exam and class subject
     */
    public function checkExamPaperStatus($examID, $classSubjectID)
    {
        try {
            $teacherID = Session::get('teacherID');

            if (!$teacherID) {
                return response()->json([
                    'error' => 'Teacher ID not found in session.'
                ], 400);
            }

            // Verify teacher owns this class subject
            $classSubject = ClassSubject::where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->whereHas('subject', function($query) {
                    $query->where('status', 'Active');
                })
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found or unauthorized access.'
                ], 404);
            }

            // Check exam paper status
            $examPaper = ExamPaper::where('examID', $examID)
                ->where('class_subjectID', $classSubjectID)
                ->first();

            if (!$examPaper) {
                return response()->json([
                    'success' => true,
                    'exam_paper_exists' => false,
                    'status' => null,
                    'message' => 'Exam paper for this subject has not been uploaded/created yet.'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'exam_paper_exists' => true,
                'status' => $examPaper->status,
                'approved' => $examPaper->status === 'approved',
                'exam_paper' => $examPaper
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teachers teaching a specific subject based on filters
     */
    public function getTeachersForSubject(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $subjectName = $request->input('subject_name');
            $examID = $request->input('exam_id');
            $mainClassID = $request->input('main_class_id');
            $subclassID = $request->input('subclass_id');

            // Log for debugging
            Log::debug("getTeachersForSubject - subject: {$subjectName}, mainClassID: {$mainClassID}, subclassID: {$subclassID}");

            if (!$subjectName) {
                return response()->json(['success' => false, 'message' => 'Subject name is required'], 400);
            }

            // Get subject ID from subject name
            $subject = SchoolSubject::where('schoolID', $schoolID)
                ->where('subject_name', $subjectName)
                ->where('status', 'Active')
                ->first();

            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Subject not found'], 404);
            }

            $teachers = [];

            // Check if subclassID is provided and not 'all' or empty
            if (!empty($subclassID) && $subclassID !== 'all' && $subclassID !== '') {
                // Filter by specific subclass - get only ONE teacher (first one found)
                Log::debug("Filtering by subclassID: {$subclassID}, subjectID: {$subject->subjectID}");
                
                // First, verify the subclass exists and get its classID
                $subclass = DB::table('subclasses')
                    ->where('subclassID', $subclassID)
                    ->first();
                
                if (!$subclass) {
                    Log::warning("Subclass {$subclassID} not found");
                    return response()->json([
                        'success' => true,
                        'teachers' => []
                    ]);
                }
                
                // Get teacher for this specific subclass and subject
                // Make sure we're filtering by BOTH subclassID AND ensuring it's not a class-level assignment
                $classSubject = ClassSubject::where('subjectID', $subject->subjectID)
                    ->where('subclassID', $subclassID)
                    ->where('status', 'Active')
                    ->whereNotNull('teacherID')
                    ->first(); // Get first matching record

                if ($classSubject && $classSubject->teacherID) {
                    $teacher = Teacher::find($classSubject->teacherID);
                    if ($teacher) {
                        $teachers[] = [
                            'teacher_id' => $teacher->id,
                            'first_name' => $teacher->first_name,
                            'last_name' => $teacher->last_name,
                            'phone_number' => $teacher->phone_number,
                            'class_name' => $subclass->subclass_name ?? 'N/A'
                        ];
                        
                        Log::debug("Added teacher: {$teacher->first_name} {$teacher->last_name} for subclass {$subclassID} (subclass_name: {$subclass->subclass_name})");
                    } else {
                        Log::warning("Teacher with ID {$classSubject->teacherID} not found");
                    }
                } else {
                    Log::debug("No class subject found for subjectID: {$subject->subjectID}, subclassID: {$subclassID}");
                }
            } elseif ($mainClassID && $mainClassID !== 'all') {
                // Filter by main class - get teacher for main class (not subclass specific)
                // First try to get class-level subject assignment
                $classSubject = ClassSubject::with(['teacher', 'class'])
                    ->where('subjectID', $subject->subjectID)
                    ->where('classID', $mainClassID)
                    ->whereNull('subclassID')
                    ->where('status', 'Active')
                    ->first();
                
                if ($classSubject && $classSubject->teacher) {
                    // Found class-level teacher
                    $teachers[] = [
                        'teacher_id' => $classSubject->teacher->id,
                        'first_name' => $classSubject->teacher->first_name,
                        'last_name' => $classSubject->teacher->last_name,
                        'phone_number' => $classSubject->teacher->phone_number,
                        'class_name' => $classSubject->class ? $classSubject->class->class_name : 'N/A'
                    ];
                } else {
                    // Fallback: get all teachers from subclasses (but show main class name)
                    $subclassIDs = DB::table('subclasses')
                        ->where('classID', $mainClassID)
                        ->pluck('subclassID')
                        ->toArray();

                    $classSubjects = ClassSubject::with(['teacher', 'class'])
                        ->where('subjectID', $subject->subjectID)
                        ->whereIn('subclassID', $subclassIDs)
                        ->where('status', 'Active')
                        ->get();

                    $teacherIds = [];
                    foreach ($classSubjects as $classSubject) {
                        if ($classSubject->teacher && !in_array($classSubject->teacher->id, $teacherIds)) {
                            $teacherIds[] = $classSubject->teacher->id;
                            $classModel = ClassModel::find($mainClassID);
                            $teachers[] = [
                                'teacher_id' => $classSubject->teacher->id,
                                'first_name' => $classSubject->teacher->first_name,
                                'last_name' => $classSubject->teacher->last_name,
                                'phone_number' => $classSubject->teacher->phone_number,
                                'class_name' => $classModel ? $classModel->class_name : 'N/A'
                            ];
                        }
                    }
                }
            } else {
                // All classes - get all teachers teaching this subject
                $classSubjects = ClassSubject::with(['teacher', 'subclass.class'])
                    ->where('subjectID', $subject->subjectID)
                    ->where('status', 'Active')
                    ->get();

                $teacherIds = [];
                foreach ($classSubjects as $classSubject) {
                    if ($classSubject->teacher && !in_array($classSubject->teacher->id, $teacherIds)) {
                        $teacherIds[] = $classSubject->teacher->id;
                        $teachers[] = [
                            'teacher_id' => $classSubject->teacher->id,
                            'first_name' => $classSubject->teacher->first_name,
                            'last_name' => $classSubject->teacher->last_name,
                            'phone_number' => $classSubject->teacher->phone_number,
                            'class_name' => $classSubject->subclass ? $classSubject->subclass->subclass_name : 'N/A'
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'teachers' => $teachers
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting teachers for subject: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error getting teachers'], 500);
        }
    }

    /**
     * Send message to teachers teaching a specific subject
     */
    public function sendMessageToTeachers(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $subjectName = $request->input('subject_name');
            $message = $request->input('message');
            $examID = $request->input('exam_id');
            $mainClassID = $request->input('main_class_id');
            $subclassID = $request->input('subclass_id');

            if (!$subjectName || !$message) {
                return response()->json(['success' => false, 'message' => 'Subject name and message are required'], 400);
            }

            // Get exam name
            $exam = Examination::find($examID);
            $examName = $exam ? $exam->exam_name : 'Exam';

            // Get school name
            $school = School::find($schoolID);
            $schoolName = $school ? $school->school_name : 'ShuleLink';

            // Get teachers (reuse the same logic)
            $subject = SchoolSubject::where('schoolID', $schoolID)
                ->where('subject_name', $subjectName)
                ->where('status', 'Active')
                ->first();

            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Subject not found'], 404);
            }

            $teachers = [];
            
            // Check if subclassID is provided and not 'all' or empty
            if (!empty($subclassID) && $subclassID !== 'all' && $subclassID !== '') {
                // Filter by specific subclass - get only ONE teacher (first one found)
                Log::debug("sendMessageToTeachers - Filtering by subclassID: {$subclassID}, subjectID: {$subject->subjectID}");
                
                // First, verify the subclass exists
                $subclass = DB::table('subclasses')
                    ->where('subclassID', $subclassID)
                    ->first();
                
                if (!$subclass) {
                    Log::warning("sendMessageToTeachers - Subclass {$subclassID} not found");
                    return response()->json([
                        'success' => true,
                        'sent_count' => 0,
                        'failed_count' => 0,
                        'message' => "Subclass not found"
                    ]);
                }
                
                // Get teacher for this specific subclass and subject
                $classSubject = ClassSubject::where('subjectID', $subject->subjectID)
                    ->where('subclassID', $subclassID)
                    ->where('status', 'Active')
                    ->whereNotNull('teacherID')
                    ->first(); // Get first matching record

                if ($classSubject && $classSubject->teacherID) {
                    $teacher = Teacher::find($classSubject->teacherID);
                    if ($teacher) {
                        $teachers[] = $teacher;
                        Log::debug("sendMessageToTeachers - Added teacher: {$teacher->first_name} {$teacher->last_name} for subclass {$subclassID}");
                    } else {
                        Log::warning("sendMessageToTeachers - Teacher with ID {$classSubject->teacherID} not found");
                    }
                } else {
                    Log::debug("sendMessageToTeachers - No class subject found for subjectID: {$subject->subjectID}, subclassID: {$subclassID}");
                }
            } elseif ($mainClassID && $mainClassID !== 'all') {
                $subclassIDs = DB::table('subclasses')
                    ->where('classID', $mainClassID)
                    ->pluck('subclassID')
                    ->toArray();

                $classSubjects = ClassSubject::with(['teacher'])
                    ->where('subjectID', $subject->subjectID)
                    ->whereIn('subclassID', $subclassIDs)
                    ->where('status', 'Active')
                    ->get();

                $teacherIds = [];
                foreach ($classSubjects as $classSubject) {
                    if ($classSubject->teacher && !in_array($classSubject->teacher->id, $teacherIds)) {
                        $teacherIds[] = $classSubject->teacher->id;
                        $teachers[] = $classSubject->teacher;
                    }
                }
            } else {
                $classSubjects = ClassSubject::with(['teacher'])
                    ->where('subjectID', $subject->subjectID)
                    ->where('status', 'Active')
                    ->get();

                $teacherIds = [];
                foreach ($classSubjects as $classSubject) {
                    if ($classSubject->teacher && !in_array($classSubject->teacher->id, $teacherIds)) {
                        $teacherIds[] = $classSubject->teacher->id;
                        $teachers[] = $classSubject->teacher;
                    }
                }
            }

            // Send SMS to each teacher
            $smsService = new SmsService();
            $sentCount = 0;
            $failedCount = 0;

            $fullMessage = "{$schoolName}. {$examName} - {$subjectName}: {$message}";

            foreach ($teachers as $teacher) {
                if ($teacher->phone_number) {
                    try {
                        $result = $smsService->sendSms($teacher->phone_number, $fullMessage);
                        if ($result['success']) {
                            $sentCount++;
                        } else {
                            $failedCount++;
                            Log::warning("Failed to send SMS to teacher {$teacher->id}: " . ($result['message'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Error sending SMS to teacher {$teacher->id}: " . $e->getMessage());
                    }
                } else {
                    $failedCount++;
                    Log::warning("Teacher {$teacher->id} has no phone number");
                }
            }

            return response()->json([
                'success' => true,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'message' => "Message sent to {$sentCount} teacher(s)"
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending message to teachers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error sending message'], 500);
        }
    }

    public function examAttendance($classSubjectID)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $userType = Session::get('user_type');

            if (!$teacherID || !$schoolID || $userType !== 'Teacher') {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Get class subject and verify teacher owns it
            $classSubject = ClassSubject::with(['subject', 'class', 'subclass'])
                ->where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->first();

            if (!$classSubject) {
                return '<div class="alert alert-danger">Class subject not found or unauthorized access.</div>';
            }

            $subjectID = $classSubject->subject->subjectID ?? null;
            if (!$subjectID) {
                return '<div class="alert alert-danger">Subject not found.</div>';
            }

            // Get available years from examinations
            $years = Examination::where('schoolID', $schoolID)
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            // Get available terms
            $terms = Examination::where('schoolID', $schoolID)
                ->whereNotNull('term')
                ->distinct()
                ->orderBy('term', 'asc')
                ->pluck('term')
                ->toArray();

            return view('Teacher.exam_attendance', compact('classSubject', 'subjectID', 'years', 'terms'));
        } catch (\Exception $e) {
            Log::error('Error loading exam attendance: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error loading exam attendance: ' . $e->getMessage() . '</div>';
        }
    }

    public function getTermsForYear(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $year = $request->input('year');

            if (!$year || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Year and school ID required']);
            }

            $terms = Examination::where('schoolID', $schoolID)
                ->where('year', $year)
                ->whereNotNull('term')
                ->distinct()
                ->orderBy('term', 'asc')
                ->pluck('term')
                ->toArray();

            return response()->json(['success' => true, 'terms' => $terms]);
        } catch (\Exception $e) {
            Log::error('Error getting terms for year: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getExamsForYearTerm(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $year = $request->input('year');
            $term = $request->input('term');
            $subjectID = $request->input('subjectID');

            if (!$year || !$term || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Year, term and school ID required']);
            }

            // Get exams for year and term
            $examIDs = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->where('year', $year)
                ->where('term', $term)
                ->pluck('examID')
                ->toArray();

            if (empty($examIDs)) {
                return response()->json(['success' => true, 'exams' => []]);
            }

            // If subjectID is provided, filter exams that have this subject in exam_timetable or exam_timetables
            if ($subjectID) {
                $examIDsWithSubject = DB::table('exam_timetable')
                    ->whereIn('examID', $examIDs)
                    ->where('subjectID', $subjectID)
                    ->distinct()
                    ->pluck('examID')
                    ->toArray();
                
                // Also check exam_timetables table
                $examIDsWithSubject2 = DB::table('exam_timetables')
                    ->whereIn('examID', $examIDs)
                    ->where('subjectID', $subjectID)
                    ->distinct()
                    ->pluck('examID')
                    ->toArray();
                
                $allExamIDsWithSubject = array_unique(array_merge($examIDsWithSubject, $examIDsWithSubject2));
                $examIDs = array_intersect($examIDs, $allExamIDsWithSubject);
            }

            if (empty($examIDs)) {
                return response()->json(['success' => true, 'exams' => []]);
            }

            $query = Examination::whereIn('examID', $examIDs);

            $exams = $query->orderBy('start_date', 'desc')
                ->get(['examID', 'exam_name', 'start_date', 'end_date']);

            return response()->json(['success' => true, 'exams' => $exams]);
        } catch (\Exception $e) {
            Log::error('Error getting exams for year and term: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getExamAttendanceDataAPI(Request $request, $classSubjectID)
    {
        try {
            // Get authentication data from headers (stateless authentication)
            $teacherID = $request->header('teacherID') ?? $request->input('teacherID');
            $schoolID = $request->header('schoolID') ?? $request->input('schoolID');
            $userID = $request->header('user_id') ?? $request->input('user_id');
            $userType = $request->header('user_type') ?? $request->input('user_type');
            
            $examID = $request->input('examID');
            $subjectID = $request->input('subjectID');

            // Validate required parameters
            if (!$teacherID || !$schoolID || !$userID || !$userType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
                ], 401);
            }

            // Validate user type
            if ($userType !== 'Teacher') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
                ], 403);
            }

            // Verify teacher exists and belongs to the school
            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
                ], 404);
            }

            if (!$examID || !$subjectID) {
                return response()->json(['success' => false, 'error' => 'Missing required parameters: examID and subjectID are required']);
            }
            
            // Call existing method with classSubjectID
            $request->merge(['classSubjectID' => $classSubjectID]);
            return $this->getExamAttendanceData($request);
        } catch (\Exception $e) {
            Log::error('Error getting exam attendance data API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve exam attendance data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getExamAttendanceData(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $examID = $request->input('examID');
            $subjectID = $request->input('subjectID');
            $classSubjectID = $request->input('classSubjectID');

            if (!$examID || !$subjectID || !$classSubjectID) {
                return response()->json(['success' => false, 'error' => 'Missing required parameters']);
            }

            // Verify teacher owns this class subject
            $classSubject = ClassSubject::where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->where('status', 'Active')
                ->first();

            if (!$classSubject) {
                return response()->json(['success' => false, 'error' => 'Unauthorized access']);
            }

            // Get all subclasses for this teacher's subject
            // If subclassID exists, get that specific subclass, otherwise get all subclasses in the class
            $subclassIDs = [];
            if ($classSubject->subclassID) {
                $subclassIDs = [$classSubject->subclassID];
            } else {
                $subclassIDs = DB::table('subclasses')
                    ->where('classID', $classSubject->classID)
                    ->pluck('subclassID')
                    ->toArray();
            }

            if (empty($subclassIDs)) {
                return response()->json(['success' => true, 'data' => ['subclasses' => []]]);
            }

            // Get exam attendance data grouped by subclass and all students
            $attendanceData = [];
            $allStudents = [];

            foreach ($subclassIDs as $subclassID) {
                $subclass = DB::table('subclasses')
                    ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                    ->where('subclasses.subclassID', $subclassID)
                    ->select('subclasses.subclass_name', 'classes.class_name', 'subclasses.subclassID')
                    ->first();

                if (!$subclass) continue;

                // Get all students in this subclass
                $students = DB::table('students')
                    ->where('subclassID', $subclassID)
                    ->where('status', 'Active')
                    ->select('studentID', 'first_name', 'last_name', 'middle_name')
                    ->get();

                if ($students->isEmpty()) {
                    $attendanceData[] = [
                        'subclassID' => $subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'class_name' => $subclass->class_name,
                        'class_display' => $subclass->class_name . ' - ' . $subclass->subclass_name,
                        'present' => 0,
                        'absent' => 0,
                        'total' => 0
                    ];
                    continue;
                }

                $studentIDs = $students->pluck('studentID')->toArray();

                // Get attendance for these students in this exam and subject
                $attendanceRecords = DB::table('exam_attendance')
                    ->where('examID', $examID)
                    ->where('subjectID', $subjectID)
                    ->whereIn('studentID', $studentIDs)
                    ->pluck('status', 'studentID')
                    ->toArray();

                $present = 0;
                $absent = 0;

                // Process each student
                foreach ($students as $student) {
                    $status = $attendanceRecords[$student->studentID] ?? 'Absent';
                    $fullName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                    
                    $allStudents[] = [
                        'studentID' => $student->studentID,
                        'name' => $fullName,
                        'subclassID' => $subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'class_name' => $subclass->class_name,
                        'class_display' => $subclass->class_name . ' - ' . $subclass->subclass_name,
                        'status' => $status
                    ];

                    if ($status === 'Present') {
                        $present++;
                    } else {
                        $absent++;
                    }
                }

                $total = count($students);

                $attendanceData[] = [
                    'subclassID' => $subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'class_name' => $subclass->class_name,
                    'class_display' => $subclass->class_name . ' - ' . $subclass->subclass_name,
                    'present' => $present,
                    'absent' => $absent,
                    'total' => $total
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'subclasses' => $attendanceData,
                    'students' => $allStudents
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting exam attendance data: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display My Sessions page for teacher
     */
    public function mySessions()
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Teacher') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        // Get active session timetable definition
        $definition = DB::table('session_timetable_definitions')
            ->where('schoolID', $schoolID)
            ->first();

        if (!$definition) {
            return view('Teacher.my_sessions', [
                'sessions' => [],
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'message' => 'No timetable definition found. Please contact admin.'
            ]);
        }

        // Get all sessions for this teacher for current week
        $currentDate = Carbon::now();
        $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        
        $sessions = ClassSessionTimetable::with(['subclass.class', 'subject', 'classSubject.subject'])
            ->where('teacherID', $teacherID)
            ->where('definitionID', $definition->definitionID)
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')")
            ->orderBy('start_time')
            ->get();

        // Group sessions by day
        $sessionsByDay = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        foreach ($days as $day) {
            $sessionsByDay[$day] = $sessions->where('day', $day)->values();
        }

        // Get holidays and events for current week
        $weekStart = $startOfWeek->format('Y-m-d');
        $weekEnd = $startOfWeek->copy()->endOfWeek()->format('Y-m-d');
        
        $holidays = Holiday::where('schoolID', $schoolID)
            ->where(function($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('start_date', [$weekStart, $weekEnd])
                    ->orWhereBetween('end_date', [$weekStart, $weekEnd])
                    ->orWhere(function($q) use ($weekStart, $weekEnd) {
                        $q->where('start_date', '<=', $weekStart)
                          ->where('end_date', '>=', $weekEnd);
                    });
            })
            ->get();

        $events = Event::where('schoolID', $schoolID)
            ->whereBetween('event_date', [$weekStart, $weekEnd])
            ->where('is_non_working_day', true)
            ->get();

        // Create holiday dates array
        $holidayDates = [];
        foreach ($holidays as $holiday) {
            $start = Carbon::parse($holiday->start_date);
            $end = Carbon::parse($holiday->end_date);
            while ($start <= $end) {
                $holidayDates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }
        foreach ($events as $event) {
            $holidayDates[] = Carbon::parse($event->event_date)->format('Y-m-d');
        }

        return view('Teacher.my_sessions', compact(
            'sessionsByDay',
            'days',
            'currentDate',
            'startOfWeek',
            'holidayDates',
            'definition'
        ));
    }

    /**
     * Get teacher weekly sessions for API (Flutter app)
     */
    public function getTeacherMySessionsAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
            $teacherID = $request->header('teacherID') ?? $request->input('teacherID');
            $schoolID = $request->header('schoolID') ?? $request->input('schoolID');
            $userID = $request->header('user_id') ?? $request->input('user_id');
            $userType = $request->header('user_type') ?? $request->input('user_type');

            // Validate required parameters
            if (!$teacherID || !$schoolID || !$userID || !$userType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
                ], 401);
            }

            // Validate user type
            if ($userType !== 'Teacher') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
                ], 403);
            }

            // Verify teacher exists and belongs to the school
            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
                ], 404);
            }

            $weekOffset = (int) ($request->input('week') ?? 0);

            // Get active session timetable definition
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json([
                    'success' => false,
                    'error' => 'No timetable definition found. Please contact admin.'
                ], 404);
            }

            $currentDate = Carbon::now(config('app.timezone'));
            $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY)->addWeeks($weekOffset);
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            $sessions = ClassSessionTimetable::with(['subclass.class', 'subject', 'classSubject.subject'])
                ->where('teacherID', $teacherID)
                ->where('definitionID', $definition->definitionID)
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')")
                ->orderBy('start_time')
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

            // Get holidays and events for current week
            $weekStart = $startOfWeek->format('Y-m-d');
            $weekEnd = $endOfWeek->format('Y-m-d');

            $holidays = Holiday::where('schoolID', $schoolID)
                ->where(function($query) use ($weekStart, $weekEnd) {
                    $query->whereBetween('start_date', [$weekStart, $weekEnd])
                        ->orWhereBetween('end_date', [$weekStart, $weekEnd])
                        ->orWhere(function($q) use ($weekStart, $weekEnd) {
                            $q->where('start_date', '<=', $weekStart)
                              ->where('end_date', '>=', $weekEnd);
                        });
                })
                ->get();

            $events = Event::where('schoolID', $schoolID)
                ->whereBetween('event_date', [$weekStart, $weekEnd])
                ->where('is_non_working_day', true)
                ->get();

            $holidayDates = [];
            foreach ($holidays as $holiday) {
                $start = Carbon::parse($holiday->start_date);
                $end = Carbon::parse($holiday->end_date);
                while ($start <= $end) {
                    $holidayDates[] = $start->format('Y-m-d');
                    $start->addDay();
                }
            }
            foreach ($events as $event) {
                $holidayDates[] = Carbon::parse($event->event_date)->format('Y-m-d');
            }

            $now = Carbon::now(config('app.timezone'));
            $todayDayName = $now->format('l');

            $daysData = [];
            foreach ($days as $day) {
                $dayDate = $startOfWeek->copy()->addDays(array_search($day, $days));
                $dayDateStr = $dayDate->format('Y-m-d');
                $isHoliday = in_array($dayDateStr, $holidayDates);
                $isWeekend = ($dayDate->dayOfWeek === Carbon::SATURDAY || $dayDate->dayOfWeek === Carbon::SUNDAY);
                $daySessions = $sessions->where('day', $day)->values();

                $sessionsData = [];
                foreach ($daySessions as $session) {
                    $startTimeStr = is_string($session->start_time)
                        ? $session->start_time
                        : ($session->start_time instanceof \DateTime
                            ? $session->start_time->format('H:i:s')
                            : '00:00:00');
                    $endTimeStr = is_string($session->end_time)
                        ? $session->end_time
                        : ($session->end_time instanceof \DateTime
                            ? $session->end_time->format('H:i:s')
                            : '00:00:00');

                    if (strlen($startTimeStr) === 5) {
                        $startTimeStr .= ':00';
                    }
                    if (strlen($endTimeStr) === 5) {
                        $endTimeStr .= ':00';
                    }

                    $sessionDateTime = $dayDate->copy()->setTimeFromTimeString($startTimeStr);
                    $sessionEndDateTime = $dayDate->copy()->setTimeFromTimeString($endTimeStr);

                    $hasReachedStartTime = $now >= $sessionDateTime;
                    $isBeforeEndTime = $now <= $sessionEndDateTime;
                    $isWithinSessionTime = $hasReachedStartTime && $isBeforeEndTime;
                    $isTodaySession = $dayDate->isToday() && ($session->day === $todayDayName);
                    $isSessionTime = $now >= $sessionDateTime && $now <= $sessionEndDateTime;
                    $isPast = $now > $sessionEndDateTime;
                    $canInteract = !$isHoliday && !$isWeekend && $isTodaySession && $isWithinSessionTime;

                    $subjectName = 'N/A';
                    if ($session->classSubject && $session->classSubject->subject && $session->classSubject->subject->subject_name) {
                        $subjectName = $session->classSubject->subject->subject_name;
                    } elseif ($session->subject && $session->subject->subject_name) {
                        $subjectName = $session->subject->subject_name;
                    }
                    if ($session->is_prepo) {
                        $subjectName .= ' (Prepo)';
                    }

                    $className = $session->subclass->class->class_name ?? '';
                    $subclassName = $session->subclass->subclass_name ?? '';
                    $classLabel = trim($className . ($className && $subclassName ? ' - ' : '') . $subclassName);

                    $task = SessionTask::where('session_timetableID', $session->session_timetableID)
                        ->where('task_date', $dayDateStr)
                        ->first();

                    $sessionsData[] = [
                        'session_timetableID' => $session->session_timetableID,
                        'day' => $session->day,
                        'date' => $dayDateStr,
                        'start_time' => $startTimeStr,
                        'end_time' => $endTimeStr,
                        'start_time_formatted' => Carbon::parse($startTimeStr)->format('h:i A'),
                        'end_time_formatted' => Carbon::parse($endTimeStr)->format('h:i A'),
                        'subject_name' => $subjectName,
                        'class_name' => $className,
                        'subclass_name' => $subclassName,
                        'class_label' => $classLabel,
                        'is_prepo' => (bool) $session->is_prepo,
                        'is_session_time' => $isSessionTime,
                        'is_past' => $isPast,
                        'can_interact' => $canInteract,
                        'task' => $task ? [
                            'session_taskID' => $task->session_taskID ?? null,
                            'status' => $task->status,
                            'topic' => $task->topic,
                            'subtopic' => $task->subtopic,
                            'task_description' => $task->task_description,
                        ] : null,
                        'has_approved_task' => $task && $task->status === 'approved',
                    ];
                }

                $daysData[] = [
                    'day' => $day,
                    'date' => $dayDateStr,
                    'is_holiday' => $isHoliday,
                    'is_weekend' => $isWeekend,
                    'sessions' => $sessionsData,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'week_offset' => $weekOffset,
                    'week_start' => $startOfWeek->format('Y-m-d'),
                    'week_end' => $endOfWeek->format('Y-m-d'),
                    'days' => $daysData,
                    'holiday_dates' => array_values(array_unique($holidayDates)),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting teacher sessions API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve teacher sessions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students for a session (API)
     */
    public function getSessionStudentsAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
            $teacherID = $request->header('teacherID') ?? $request->input('teacherID');
            $schoolID = $request->header('schoolID') ?? $request->input('schoolID');
            $userID = $request->header('user_id') ?? $request->input('user_id');
            $userType = $request->header('user_type') ?? $request->input('user_type');
            $sessionTimetableID = $request->input('session_timetableID');
            $attendanceDate = $request->input('attendance_date');

            // Validate required parameters
            if (!$teacherID || !$schoolID || !$userID || !$userType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
                ], 401);
            }

            // Validate user type
            if ($userType !== 'Teacher') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
                ], 403);
            }

            // Verify teacher exists and belongs to the school
            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
                ], 404);
            }

            if (!$sessionTimetableID) {
                return response()->json(['success' => false, 'error' => 'session_timetableID is required'], 422);
            }

            if (!$attendanceDate) {
                return response()->json(['success' => false, 'error' => 'attendance_date is required'], 422);
            }

            // Get session
            $session = ClassSessionTimetable::with(['subclass', 'subject'])
                ->where('session_timetableID', $sessionTimetableID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$session) {
                return response()->json(['success' => false, 'error' => 'Session not found'], 404);
            }

            // Check if attendance already exists for this session and date
            $existingAttendance = StudentSessionAttendance::where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $attendanceDate)
                ->exists();

            // Get students for this subclass
            $students = Student::where('subclassID', $session->subclassID)
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            // Get existing attendance records if any
            $existingRecords = StudentSessionAttendance::where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $attendanceDate)
                ->get()
                ->keyBy('studentID');

            $studentData = [];
            foreach ($students as $student) {
                $existing = $existingRecords->get($student->studentID);
                $studentData[] = [
                    'studentID' => $student->studentID,
                    'name' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'status' => $existing ? $existing->status : 'Present',
                    'remark' => $existing ? $existing->remark : null,
                ];
            }

            return response()->json([
                'success' => true,
                'students' => $studentData,
                'attendance_exists' => $existingAttendance,
                'can_collect' => !$existingAttendance
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting session students API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve session students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Collect/update attendance for a session (API)
     */
    public function collectSessionAttendanceAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
            $teacherID = $request->header('teacherID') ?? $request->input('teacherID');
            $schoolID = $request->header('schoolID') ?? $request->input('schoolID');
            $userID = $request->header('user_id') ?? $request->input('user_id');
            $userType = $request->header('user_type') ?? $request->input('user_type');

            // Validate required parameters
            if (!$teacherID || !$schoolID || !$userID || !$userType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
                ], 401);
            }

            // Validate user type
            if ($userType !== 'Teacher') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
                ], 403);
            }

            // Verify teacher exists and belongs to the school
            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'session_timetableID' => 'required|exists:class_session_timetables,session_timetableID',
                'attendance_date' => 'required|date',
                'attendance' => 'required|array',
                'attendance.*.studentID' => 'required|exists:students,studentID',
                'attendance.*.status' => 'required|in:Present,Absent,Late,Excused',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $sessionTimetableID = $request->input('session_timetableID');
            $attendanceDate = $request->input('attendance_date');
            $attendanceData = $request->input('attendance');
            $isUpdate = $request->input('is_update', false);

            $session = ClassSessionTimetable::where('session_timetableID', $sessionTimetableID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$session) {
                return response()->json(['success' => false, 'error' => 'Session not found'], 404);
            }

            $existingAttendance = StudentSessionAttendance::where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $attendanceDate)
                ->exists();

            if ($existingAttendance && !$isUpdate) {
                return response()->json([
                    'success' => false,
                    'error' => 'Attendance for this session on this date has already been collected. Please use "Update Attendance" to modify it.'
                ], 422);
            }

            DB::beginTransaction();

            foreach ($attendanceData as $attendance) {
                StudentSessionAttendance::updateOrCreate(
                    [
                        'session_timetableID' => $sessionTimetableID,
                        'studentID' => $attendance['studentID'],
                        'attendance_date' => $attendanceDate,
                    ],
                    [
                        'schoolID' => $schoolID,
                        'teacherID' => $teacherID,
                        'status' => $attendance['status'],
                        'remark' => $attendance['remark'] ?? null,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance collected successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error collecting session attendance API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to collect attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get session attendance data for update (API)
     */
    public function getSessionAttendanceForUpdateAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
            $teacherID = $request->header('teacherID') ?? $request->input('teacherID');
            $schoolID = $request->header('schoolID') ?? $request->input('schoolID');
            $userID = $request->header('user_id') ?? $request->input('user_id');
            $userType = $request->header('user_type') ?? $request->input('user_type');

            // Validate required parameters
            if (!$teacherID || !$schoolID || !$userID || !$userType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
                ], 401);
            }

            // Validate user type
            if ($userType !== 'Teacher') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
                ], 403);
            }

            // Verify teacher exists and belongs to the school
            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
                ], 404);
            }

            $sessionTimetableID = $request->input('session_timetableID');
            $attendanceDate = $request->input('attendance_date');

            if (!$sessionTimetableID || !$attendanceDate) {
                return response()->json(['success' => false, 'error' => 'Missing required parameters']);
            }

            $session = ClassSessionTimetable::where('session_timetableID', $sessionTimetableID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$session) {
                return response()->json(['success' => false, 'error' => 'Session not found'], 404);
            }

            $attendanceRecords = StudentSessionAttendance::with(['student'])
                ->where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $attendanceDate)
                ->where('teacherID', $teacherID)
                ->get();

            $data = [];
            foreach ($attendanceRecords as $record) {
                $data[] = [
                    'attendanceID' => $record->session_attendanceID,
                    'studentID' => $record->studentID,
                    'student_name' => trim(($record->student->first_name ?? '') . ' ' . ($record->student->middle_name ?? '') . ' ' . ($record->student->last_name ?? '')),
                    'status' => $record->status,
                    'remark' => $record->remark,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting session attendance for update API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign task for a session (API)
     */
    public function assignSessionTaskAPI(Request $request)
    {
        try {
            // Get authentication data from headers (stateless authentication)
            $teacherID = $request->header('teacherID') ?? $request->input('teacherID');
            $schoolID = $request->header('schoolID') ?? $request->input('schoolID');
            $userID = $request->header('user_id') ?? $request->input('user_id');
            $userType = $request->header('user_type') ?? $request->input('user_type');

            // Validate required parameters
            if (!$teacherID || !$schoolID || !$userID || !$userType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
                ], 401);
            }

            // Validate user type
            if ($userType !== 'Teacher') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
                ], 403);
            }

            // Verify teacher exists and belongs to the school
            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'session_timetableID' => 'required|exists:class_session_timetables,session_timetableID',
                'task_date' => 'required|date',
                'topic' => 'required|string|max:255',
                'subtopic' => 'nullable|string|max:255',
                'task_description' => 'required|string|min:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $sessionTimetableID = $request->input('session_timetableID');

            $session = ClassSessionTimetable::where('session_timetableID', $sessionTimetableID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$session) {
                return response()->json(['success' => false, 'error' => 'Session not found'], 404);
            }

            $existingTask = SessionTask::where('session_timetableID', $sessionTimetableID)
                ->where('task_date', $request->input('task_date'))
                ->first();

            if ($existingTask) {
                return response()->json([
                    'success' => false,
                    'error' => 'Task already assigned for this session on this date'
                ], 422);
            }

            $task = SessionTask::create([
                'schoolID' => $schoolID,
                'session_timetableID' => $sessionTimetableID,
                'teacherID' => $teacherID,
                'task_date' => $request->input('task_date'),
                'topic' => $request->input('topic'),
                'subtopic' => $request->input('subtopic'),
                'task_description' => $request->input('task_description'),
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task assigned successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning session task API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to assign task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students for a session
     */
    public function getSessionStudents(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $sessionTimetableID = $request->input('session_timetableID');

            if (!$teacherID || !$schoolID || !$sessionTimetableID) {
                return response()->json(['success' => false, 'error' => 'Missing required parameters']);
            }

            // Get session
            $session = ClassSessionTimetable::with(['subclass', 'subject'])
                ->where('session_timetableID', $sessionTimetableID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$session) {
                return response()->json(['success' => false, 'error' => 'Session not found'], 404);
            }

            // Get attendance date from request
            $attendanceDate = $request->input('attendance_date');
            if (!$attendanceDate) {
                return response()->json(['success' => false, 'error' => 'Attendance date is required']);
            }

            // Check if attendance already exists for this session and date
            $existingAttendance = StudentSessionAttendance::where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $attendanceDate)
                ->exists();

            // Get students for this subclass
            $students = Student::where('subclassID', $session->subclassID)
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            // Get existing attendance records if any
            $existingRecords = StudentSessionAttendance::where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $attendanceDate)
                ->get()
                ->keyBy('studentID');

            $studentData = [];
            foreach ($students as $student) {
                $existing = $existingRecords->get($student->studentID);
                $studentData[] = [
                    'studentID' => $student->studentID,
                    'name' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'status' => $existing ? $existing->status : 'Present', // Use existing status or default
                    'remark' => $existing ? $existing->remark : null,
                ];
            }

            return response()->json([
                'success' => true,
                'students' => $studentData,
                'attendance_exists' => $existingAttendance,
                'can_collect' => !$existingAttendance // Can only collect if not already collected
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting session students: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Collect attendance for a session
     */
    public function collectSessionAttendance(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            $validator = Validator::make($request->all(), [
                'session_timetableID' => 'required|exists:class_session_timetables,session_timetableID',
                'attendance_date' => 'required|date',
                'attendance' => 'required|array',
                'attendance.*.studentID' => 'required|exists:students,studentID',
                'attendance.*.status' => 'required|in:Present,Absent,Late,Excused',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $sessionTimetableID = $request->input('session_timetableID');
            $attendanceDate = $request->input('attendance_date');
            $attendanceData = $request->input('attendance');
            $isUpdate = $request->input('is_update', false); // Flag to allow updates

            // Check if attendance already exists for this session and date
            $existingAttendance = StudentSessionAttendance::where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $attendanceDate)
                ->exists();

            // If attendance exists and this is not an update, reject
            if ($existingAttendance && !$isUpdate) {
                return response()->json([
                    'success' => false,
                    'error' => 'Attendance for this session on this date has already been collected. Please use "Update Attendance" to modify it.'
                ], 422);
            }

            DB::beginTransaction();

            foreach ($attendanceData as $attendance) {
                StudentSessionAttendance::updateOrCreate(
                    [
                        'session_timetableID' => $sessionTimetableID,
                        'studentID' => $attendance['studentID'],
                        'attendance_date' => $attendanceDate,
                    ],
                    [
                        'schoolID' => $schoolID,
                        'teacherID' => $teacherID,
                        'status' => $attendance['status'],
                        'remark' => $attendance['remark'] ?? null,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance collected successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error collecting session attendance: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Assign task for a session
     */
    public function assignSessionTask(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            $validator = Validator::make($request->all(), [
                'session_timetableID' => 'required|exists:class_session_timetables,session_timetableID',
                'task_date' => 'required|date',
                'topic' => 'required|string|max:255',
                'subtopic' => 'nullable|string|max:255',
                'task_description' => 'required|string|min:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Check if task already exists for this session and date
            $existingTask = SessionTask::where('session_timetableID', $request->input('session_timetableID'))
                ->where('task_date', $request->input('task_date'))
                ->first();

            if ($existingTask) {
                return response()->json([
                    'success' => false,
                    'error' => 'Task already assigned for this session on this date'
                ], 422);
            }

            $task = SessionTask::create([
                'schoolID' => $schoolID,
                'session_timetableID' => $request->input('session_timetableID'),
                'teacherID' => $teacherID,
                'task_date' => $request->input('task_date'),
                'topic' => $request->input('topic'),
                'subtopic' => $request->input('subtopic'),
                'task_description' => $request->input('task_description'),
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task assigned successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning session task: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update and resubmit a rejected task
     */
    public function updateSessionTask(Request $request, $taskID)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            $validator = Validator::make($request->all(), [
                'session_timetableID' => 'required|exists:class_session_timetables,session_timetableID',
                'task_date' => 'required|date',
                'topic' => 'required|string|max:255',
                'subtopic' => 'nullable|string|max:255',
                'task_description' => 'required|string|min:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Find the task and verify ownership
            $task = SessionTask::findOrFail($taskID);
            
            if ($task->teacherID != $teacherID || $task->schoolID != $schoolID) {
                return response()->json(['success' => false, 'error' => 'Unauthorized access'], 403);
            }

            // Only allow updating rejected tasks
            if ($task->status !== 'rejected') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only rejected tasks can be updated'
                ], 422);
            }

            // Update the task and reset status to pending
            $task->update([
                'topic' => $request->input('topic'),
                'subtopic' => $request->input('subtopic'),
                'task_description' => $request->input('task_description'),
                'status' => 'pending',
                'admin_comment' => null, // Clear previous admin comment
                'approved_by' => null,
                'approved_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task updated and resubmitted successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating session task: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * View teacher's assigned tasks
     */
    public function myTasks(Request $request)
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Teacher') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        // Build query with filters
        $query = SessionTask::with([
            'sessionTimetable.subject',
            'sessionTimetable.subclass.class',
            'approver'
        ])
        ->where('teacherID', $teacherID)
        ->where('schoolID', $schoolID);

        // Apply status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Apply date filter (single date, not range)
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('task_date', $request->date);
        }

        $tasks = $query->orderBy('task_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Teacher.my_tasks', compact('tasks'));
    }

    /**
     * View session attendance for a subject
     */
    public function sessionAttendance($classSubjectID)
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Teacher') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        // Get class subject
        $classSubject = ClassSubject::with(['subject', 'subclass.class'])
            ->where('class_subjectID', $classSubjectID)
            ->where('teacherID', $teacherID)
            ->first();

        if (!$classSubject) {
            return response()->json(['success' => false, 'error' => 'Class subject not found'], 404);
        }

        return view('Teacher.session_attendance', compact('classSubject'));
    }

    /**
     * Get session attendance data filtered by date or month
     */
    public function getSessionAttendanceDataAPI(Request $request, $classSubjectID)
    {
        try {
            // Get authentication data from headers (stateless authentication)
            $teacherID = $request->header('teacherID') ?? $request->input('teacherID');
            $schoolID = $request->header('schoolID') ?? $request->input('schoolID');
            $userID = $request->header('user_id') ?? $request->input('user_id');
            $userType = $request->header('user_type') ?? $request->input('user_type');
            
            $attendanceDate = $request->input('attendance_date');
            $filterType = $request->input('filter_type', 'date'); // 'date' or 'month'
            $month = $request->input('month'); // Format: YYYY-MM

            // Validate required parameters
            if (!$teacherID || !$schoolID || !$userID || !$userType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Missing required authentication parameters. Please provide: user_id, user_type, schoolID, and teacherID in headers or request body.'
                ], 401);
            }

            // Validate user type
            if ($userType !== 'Teacher') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Invalid user type. This endpoint is for Teachers only.'
                ], 403);
            }

            // Verify teacher exists and belongs to the school
            $teacher = Teacher::where('id', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Teacher not found or does not belong to the specified school.'
                ], 404);
            }

            if (!$classSubjectID) {
                return response()->json(['success' => false, 'error' => 'Class subject ID is required']);
            }
            
            // Call existing method with classSubjectID
            $request->merge(['classSubjectID' => $classSubjectID]);
            return $this->getSessionAttendanceData($request);
        } catch (\Exception $e) {
            Log::error('Error getting session attendance data API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve session attendance data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSessionAttendanceData(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $classSubjectID = $request->input('classSubjectID');
            $attendanceDate = $request->input('attendance_date');
            $filterType = $request->input('filter_type', 'date'); // 'date' or 'month'
            $month = $request->input('month'); // Format: YYYY-MM

            if (!$teacherID || !$schoolID || !$classSubjectID) {
                return response()->json(['success' => false, 'error' => 'Missing required parameters']);
            }

            if ($filterType === 'date' && !$attendanceDate) {
                return response()->json(['success' => false, 'error' => 'Attendance date is required for date filter']);
            }

            if ($filterType === 'month' && !$month) {
                return response()->json(['success' => false, 'error' => 'Month is required for month filter']);
            }

            // Get class subject
            $classSubject = ClassSubject::with(['subject', 'subclass'])
                ->where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$classSubject) {
                return response()->json(['success' => false, 'error' => 'Class subject not found'], 404);
            }

            // Get sessions for this class subject
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => false, 'error' => 'Timetable definition not found']);
            }

            $sessions = ClassSessionTimetable::where('definitionID', $definition->definitionID)
                ->where('class_subjectID', $classSubjectID)
                ->where('teacherID', $teacherID)
                ->get();

            if ($sessions->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No sessions found for this subject'
                ]);
            }

            // Get students for this subclass
            $students = Student::where('subclassID', $classSubject->subclassID)
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            if ($filterType === 'date') {
                // Date filter - show detailed attendance for specific date
                // Get all attendance records (don't filter by status here, we'll filter on frontend)
            $attendanceRecords = StudentSessionAttendance::whereIn('session_timetableID', $sessions->pluck('session_timetableID'))
                ->where('attendance_date', $attendanceDate)
                ->get()
                ->keyBy(function($record) {
                    return $record->session_timetableID . '_' . $record->studentID;
                });

            $attendanceData = [];
            foreach ($sessions as $session) {
                $sessionAttendance = [];
                foreach ($students as $student) {
                    $key = $session->session_timetableID . '_' . $student->studentID;
                    $record = $attendanceRecords->get($key);
                    
                    $sessionAttendance[] = [
                        'studentID' => $student->studentID,
                        'name' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                        'status' => $record ? $record->status : null,
                        'remark' => $record ? $record->remark : null,
                    ];
                }

                    // Format time for display
                    $startTime = is_string($session->start_time) ? $session->start_time : ($session->start_time ? $session->start_time->format('H:i:s') : 'N/A');
                    $endTime = is_string($session->end_time) ? $session->end_time : ($session->end_time ? $session->end_time->format('H:i:s') : 'N/A');

                $attendanceData[] = [
                    'session_timetableID' => $session->session_timetableID,
                    'day' => $session->day,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    'attendance' => $sessionAttendance,
                ];
            }

            return response()->json([
                'success' => true,
                    'filter_type' => 'date',
                'data' => $attendanceData,
                'date' => $attendanceDate
            ]);
            } else {
                // Month filter - show statistics per student
                $monthStart = Carbon::parse($month . '-01')->startOfMonth();
                $monthEnd = Carbon::parse($month . '-01')->endOfMonth();
                
                // Get holidays and events for the month
                $holidays = Holiday::where('schoolID', $schoolID)
                    ->where(function($query) use ($monthStart, $monthEnd) {
                        $query->whereBetween('start_date', [$monthStart, $monthEnd])
                            ->orWhereBetween('end_date', [$monthStart, $monthEnd])
                            ->orWhere(function($q) use ($monthStart, $monthEnd) {
                                $q->where('start_date', '<=', $monthStart)
                                  ->where('end_date', '>=', $monthEnd);
                            });
                    })
                    ->get();

                $events = Event::where('schoolID', $schoolID)
                    ->whereBetween('event_date', [$monthStart, $monthEnd])
                    ->where('is_non_working_day', true)
                    ->get();

                // Create holiday dates array
                $holidayDates = [];
                foreach ($holidays as $holiday) {
                    $start = Carbon::parse($holiday->start_date);
                    $end = Carbon::parse($holiday->end_date);
                    while ($start <= $end) {
                        $holidayDates[] = $start->format('Y-m-d');
                        $start->addDay();
                    }
                }
                foreach ($events as $event) {
                    $holidayDates[] = Carbon::parse($event->event_date)->format('Y-m-d');
                }
                $holidayDates = array_unique($holidayDates);

                // Calculate total sessions in the month (excluding weekends and holidays)
                $totalSessions = 0;
                $sessionDates = [];
                $currentDate = $monthStart->copy();
                
                while ($currentDate <= $monthEnd) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 6 = Saturday
                    
                    // Skip weekends and holidays
                    if ($dayOfWeek != 0 && $dayOfWeek != 6 && !in_array($dateStr, $holidayDates)) {
                        // Check if this date matches any session day
                        $dayName = $currentDate->format('l'); // Monday, Tuesday, etc.
                        foreach ($sessions as $session) {
                            if ($session->day === $dayName) {
                                $totalSessions++;
                                // Format time for display (but not needed in this context, just keep original)
                                $sessionDates[] = [
                                    'date' => $dateStr,
                                    'session_timetableID' => $session->session_timetableID,
                                    'day' => $session->day,
                                    'start_time' => $session->start_time ? (is_string($session->start_time) ? $session->start_time : $session->start_time->format('H:i:s')) : 'N/A',
                                    'end_time' => $session->end_time ? (is_string($session->end_time) ? $session->end_time : $session->end_time->format('H:i:s')) : 'N/A',
                                ];
                            }
                        }
                    }
                    $currentDate->addDay();
                }

                // Get attendance records for the month
                $attendanceRecords = StudentSessionAttendance::whereIn('session_timetableID', $sessions->pluck('session_timetableID'))
                    ->whereBetween('attendance_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                    ->get();

                // Calculate statistics per student
                $studentStats = [];
                foreach ($students as $student) {
                    $attendedCount = 0;
                    $presentCount = 0;
                    $absentCount = 0;
                    $lateCount = 0;
                    $excusedCount = 0;
                    
                    foreach ($sessionDates as $sessionDate) {
                        // Find matching attendance record - ensure date format matches
                        $attended = $attendanceRecords->filter(function($record) use ($student, $sessionDate) {
                            $recordDate = is_string($record->attendance_date) 
                                ? $record->attendance_date 
                                : Carbon::parse($record->attendance_date)->format('Y-m-d');
                            
                            return $record->studentID == $student->studentID 
                                && $record->session_timetableID == $sessionDate['session_timetableID']
                                && $recordDate == $sessionDate['date'];
                        })->first();
                        
                        if ($attended) {
                            $attendedCount++;
                            $status = $attended->status;
                            if ($status === 'Present') {
                                $presentCount++;
                            } elseif ($status === 'Absent') {
                                $absentCount++;
                            } elseif ($status === 'Late') {
                                $lateCount++;
                            } elseif ($status === 'Excused') {
                                $excusedCount++;
                            }
                        }
                    }
                    
                    $studentStats[] = [
                        'studentID' => $student->studentID,
                        'name' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                        'total_sessions' => $totalSessions,
                        'attended_sessions' => $attendedCount,
                        'present' => $presentCount,
                        'absent' => $absentCount,
                        'late' => $lateCount,
                        'excused' => $excusedCount,
                    ];
                }

                return response()->json([
                    'success' => true,
                    'filter_type' => 'month',
                    'data' => $studentStats,
                    'total_sessions' => $totalSessions,
                    'month' => $month
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error getting session attendance data: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Show update attendance page
     */
    public function updateAttendance()
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Teacher') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        return view('Teacher.update_attendance');
    }

    /**
     * Get collected attendance data for update attendance page
     */
    public function getCollectedAttendance(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            // Get all collected attendance for this teacher
            $attendance = StudentSessionAttendance::with([
                'sessionTimetable.classSubject.subject',
                'sessionTimetable.classSubject.subclass.class',
                'sessionTimetable.subclass.class',
                'student'
            ])
            ->where('teacherID', $teacherID)
            ->where('schoolID', $schoolID)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->session_timetableID . '_' . $item->attendance_date->format('Y-m-d');
            })
            ->map(function($group) {
                $first = $group->first();
                $session = $first->sessionTimetable;
                
                // Try to get subject from classSubject first, then from session directly
                $subject = null;
                $class = null;
                $subclass = null;
                
                if ($session) {
                    if ($session->classSubject) {
                        $subject = $session->classSubject->subject ?? null;
                        $subclass = $session->classSubject->subclass ?? null;
                        if ($subclass) {
                            $class = $subclass->class ?? null;
                        }
                    }
                    
                    // Fallback to session's direct relationships
                    if (!$subject && $session->subject) {
                        $subject = $session->subject;
                    }
                    if (!$subclass && $session->subclass) {
                        $subclass = $session->subclass;
                        if ($subclass) {
                            $class = $subclass->class ?? null;
                        }
                    }
                }
                
                $className = 'N/A';
                if ($class && $subclass) {
                    $className = $class->class_name ?? 'N/A';
                    if ($subclass->subclass_name) {
                        $className .= ' - ' . $subclass->subclass_name;
                    }
                }
                
                // Format time
                $startTime = 'N/A';
                $endTime = 'N/A';
                if ($session) {
                    if ($session->start_time) {
                        $startTime = is_string($session->start_time) ? $session->start_time : $session->start_time->format('H:i:s');
                    }
                    if ($session->end_time) {
                        $endTime = is_string($session->end_time) ? $session->end_time : $session->end_time->format('H:i:s');
                    }
                }
                
                return [
                    'session_timetableID' => $first->session_timetableID,
                    'attendance_date' => $first->attendance_date->format('Y-m-d'),
                    'attendance_date_formatted' => $first->attendance_date->format('d/m/Y'),
                    'day' => $session->day ?? 'N/A',
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'subject' => $subject->subject_name ?? 'N/A',
                    'class' => $className,
                    'students_count' => $group->count(),
                    'created_at' => $first->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

            return response()->json([
                'success' => true,
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting collected attendance: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get session attendance data for update tab in modal
     */
    public function getSessionAttendanceForUpdate(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $sessionTimetableID = $request->input('session_timetableID');
            $attendanceDate = $request->input('attendance_date');

            if (!$teacherID || !$schoolID || !$sessionTimetableID || !$attendanceDate) {
                return response()->json(['success' => false, 'error' => 'Missing required parameters']);
            }

            // Get attendance records for this session and date
            $attendanceRecords = StudentSessionAttendance::with(['student'])
                ->where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $attendanceDate)
                ->where('teacherID', $teacherID)
                ->get();

            $data = [];
            foreach ($attendanceRecords as $record) {
                $data[] = [
                    'attendanceID' => $record->session_attendanceID,
                    'studentID' => $record->studentID,
                    'student_name' => trim(($record->student->first_name ?? '') . ' ' . ($record->student->middle_name ?? '') . ' ' . ($record->student->last_name ?? '')),
                    'status' => $record->status,
                    'remark' => $record->remark,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting session attendance for update: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display Lesson Plans Management page
     */
    public function lessonPlans()
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Teacher') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        // Get school info
        $school = School::find($schoolID);
        $schoolType = $school && $school->school_type ? strtolower($school->school_type) : 'primary';
        $isPrimary = strpos($schoolType, 'primary') !== false || strpos($schoolType, 'pre') !== false;

        // Get active session timetable definition
        $definition = DB::table('session_timetable_definitions')
            ->where('schoolID', $schoolID)
            ->first();

        if (!$definition) {
            return view('Teacher.lesson_plans', [
                'sessions' => collect(),
                'message' => 'No timetable definition found. Please contact admin.',
                'schoolType' => $isPrimary ? 'PRE AND PRIMARY SCHOOL' : 'SECONDARY SCHOOL',
                'currentYear' => Carbon::now()->year,
            ]);
        }

        // Get unique subjects that this teacher teaches
        $subjects = ClassSubject::with(['subject'])
            ->where('teacherID', $teacherID)
            ->where('status', 'Active')
            ->whereHas('subject', function($query) {
                $query->where('status', 'Active');
            })
            ->get()
            ->groupBy('subjectID')
            ->map(function($group) {
                $first = $group->first();
                return [
                    'subjectID' => $first->subjectID,
                    'subject_name' => $first->subject->subject_name ?? 'N/A',
                ];
            })
            ->values()
            ->sortBy('subject_name');

        return view('Teacher.lesson_plans', [
            'subjects' => $subjects,
            'schoolType' => $isPrimary ? 'PRE AND PRIMARY SCHOOL' : 'SECONDARY SCHOOL',
            'currentYear' => Carbon::now()->year,
            'teacherID' => $teacherID,
            'schoolID' => $schoolID,
        ]);
    }

    /**
     * Check if date is weekend or holiday
     */
    private function checkDateStatus($date, $schoolID)
    {
        $dateObj = Carbon::parse($date);
        
        // Check if weekend
        if ($dateObj->isWeekend()) {
            return ['status' => 'weekend', 'message' => 'This date is on weekend'];
        }
        
        // Check if holiday
        $holiday = Holiday::where('schoolID', $schoolID)
            ->where(function($query) use ($date) {
                $query->whereDate('start_date', '<=', $date)
                      ->whereDate('end_date', '>=', $date);
            })
            ->first();
        
        if ($holiday) {
            return ['status' => 'holiday', 'message' => 'This date is on holiday: ' . $holiday->holiday_name];
        }
        
        // Check if event (non-working day)
        $event = Event::where('schoolID', $schoolID)
            ->whereDate('event_date', $date)
            ->where('is_non_working_day', true)
            ->first();
        
        if ($event) {
            return ['status' => 'holiday', 'message' => 'This date is on holiday: ' . $event->event_name];
        }
        
        return ['status' => 'valid', 'message' => ''];
    }

    /**
     * Get attendance statistics for a session on a specific date
     */
    public function getSessionAttendanceStats(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $sessionTimetableID = $request->input('session_timetableID');
            $date = $request->input('date');

            if (!$teacherID || !$schoolID || !$sessionTimetableID || !$date) {
                return response()->json(['success' => false, 'error' => 'Missing required parameters']);
            }

            // Check if date is weekend or holiday
            $dateStatus = $this->checkDateStatus($date, $schoolID);
            if ($dateStatus['status'] !== 'valid') {
                return response()->json([
                    'success' => false,
                    'error' => $dateStatus['message'],
                    'date_status' => $dateStatus['status']
                ]);
            }

            // Get session details
            $session = ClassSessionTimetable::with(['subclass.class', 'subject', 'classSubject.subject'])
                ->where('session_timetableID', $sessionTimetableID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$session) {
                return response()->json(['success' => false, 'error' => 'Session not found']);
            }

            // Get subclass students
            $students = Student::where('subclassID', $session->subclassID)
                ->where('status', 'Active')
                ->get();

            // Count registered students
            $registeredGirls = $students->where('gender', 'Female')->count();
            $registeredBoys = $students->where('gender', 'Male')->count();
            $registeredTotal = $students->count();

            // Get attendance records for this date from student_session_attendance table
            $attendanceRecords = StudentSessionAttendance::where('session_timetableID', $sessionTimetableID)
                ->where('attendance_date', $date)
                ->get();

            // Count present students (include 'present', 'late', and 'excused' as they all attended)
            // Only exclude 'absent' status
            $presentGirls = 0;
            $presentBoys = 0;
            $presentTotal = 0;

            foreach ($attendanceRecords as $record) {
                $student = $students->where('studentID', $record->studentID)->first();
                if ($student) {
                    // Convert status to lowercase for comparison (database stores lowercase)
                    $status = strtolower($record->status ?? '');
                    // Count as present if status is 'present', 'late', or 'excused' (not 'absent')
                    if (in_array($status, ['present', 'late', 'excused'])) {
                        if ($student->gender === 'Female') {
                            $presentGirls++;
                        } else {
                            $presentBoys++;
                        }
                        $presentTotal++;
                    }
                }
            }

            // Get subject name
            $subjectName = 'N/A';
            if ($session->classSubject && $session->classSubject->subject) {
                $subjectName = $session->classSubject->subject->subject_name;
            } elseif ($session->subject) {
                $subjectName = $session->subject->subject_name;
            }

            // Get class name
            $className = 'N/A';
            if ($session->subclass && $session->subclass->class) {
                $className = $session->subclass->class->class_name;
            }

            // Get teacher name
            $teacherName = 'N/A';
            if ($session->teacher) {
                $teacherName = trim(($session->teacher->first_name ?? '') . ' ' . ($session->teacher->last_name ?? ''));
            } elseif ($session->teacherID) {
                $teacher = \App\Models\Teacher::find($session->teacherID);
                if ($teacher) {
                    $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''));
                }
            }

            // Check if session exists for this day
            $dateObj = Carbon::parse($date);
            $dayName = $dateObj->format('l'); // Monday, Tuesday, etc.
            
            if ($session->day !== $dayName) {
                return response()->json([
                    'success' => false,
                    'error' => 'No session available for this date. This session is scheduled for ' . $session->day . '.',
                    'date_status' => 'no_session'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'registered_girls' => $registeredGirls,
                    'registered_boys' => $registeredBoys,
                    'registered_total' => $registeredTotal,
                    'present_girls' => $presentGirls,
                    'present_boys' => $presentBoys,
                    'present_total' => $presentTotal,
                    'subject' => $subjectName,
                    'class_name' => $className,
                    'teacher_name' => $teacherName,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting attendance stats: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Store new lesson plan
     */
    public function storeLessonPlan(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            $validator = Validator::make($request->all(), [
                'session_timetableID' => 'required|exists:class_session_timetables,session_timetableID',
                'lesson_date' => 'required|date',
                'main_competence' => 'nullable|string',
                'specific_competence' => 'nullable|string',
                'main_activity' => 'nullable|string',
                'specific_activity' => 'nullable|string',
                'teaching_learning_resources' => 'nullable|string',
                'references' => 'nullable|string',
                'lesson_stages' => 'nullable|array',
                'remarks' => 'nullable|string',
                'reflection' => 'nullable|string',
                'evaluation' => 'nullable|string',
                'teacher_signature' => 'nullable|string',
                'supervisor_signature' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()->first()]);
            }

            // Check if lesson plan already exists
            $existing = LessonPlan::where('session_timetableID', $request->input('session_timetableID'))
                ->where('lesson_date', $request->input('lesson_date'))
                ->first();

            if ($existing) {
                return response()->json(['success' => false, 'error' => 'Lesson plan already exists for this session and date']);
            }

            // Get session details
            $session = ClassSessionTimetable::find($request->input('session_timetableID'));
            if (!$session || $session->teacherID != $teacherID) {
                return response()->json(['success' => false, 'error' => 'Session not found or unauthorized']);
            }

            // Get attendance stats
            $attendanceStats = $this->getAttendanceStatsForDate($request->input('session_timetableID'), $request->input('lesson_date'));

            // Get subject and class
            $subjectName = 'N/A';
            if ($session->classSubject && $session->classSubject->subject) {
                $subjectName = $session->classSubject->subject->subject_name;
            } elseif ($session->subject) {
                $subjectName = $session->subject->subject_name;
            }

            $className = 'N/A';
            if ($session->subclass && $session->subclass->class) {
                $className = $session->subclass->class->class_name;
            }

            $lessonPlan = LessonPlan::create([
                'schoolID' => $schoolID,
                'session_timetableID' => $request->input('session_timetableID'),
                'teacherID' => $teacherID,
                'lesson_date' => $request->input('lesson_date'),
                'lesson_time_start' => $session->start_time,
                'lesson_time_end' => $session->end_time,
                'subject' => $subjectName,
                'class_name' => $className,
                'year' => Carbon::now()->year,
                'registered_girls' => $attendanceStats['registered_girls'],
                'registered_boys' => $attendanceStats['registered_boys'],
                'registered_total' => $attendanceStats['registered_total'],
                'present_girls' => $attendanceStats['present_girls'],
                'present_boys' => $attendanceStats['present_boys'],
                'present_total' => $attendanceStats['present_total'],
                'main_competence' => $request->input('main_competence'),
                'specific_competence' => $request->input('specific_competence'),
                'main_activity' => $request->input('main_activity'),
                'specific_activity' => $request->input('specific_activity'),
                'teaching_learning_resources' => $request->input('teaching_learning_resources'),
                'references' => $request->input('references'),
                'lesson_stages' => $request->input('lesson_stages'),
                'remarks' => $request->input('remarks'),
                'reflection' => $request->input('reflection'),
                'evaluation' => $request->input('evaluation'),
                'teacher_signature' => $request->input('teacher_signature'),
                'supervisor_signature' => $request->input('supervisor_signature'),
                'status' => 'draft',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson plan created successfully',
                'lesson_planID' => $lessonPlan->lesson_planID
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing lesson plan: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get sessions for a specific subject
     */
    public function getSessionsBySubject(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $subjectID = $request->input('subjectID');

            Log::info('Getting sessions by subject', [
                'teacherID' => $teacherID,
                'schoolID' => $schoolID,
                'subjectID' => $subjectID
            ]);

            if (!$teacherID || !$schoolID || !$subjectID) {
                Log::warning('Missing required parameters', [
                    'teacherID' => $teacherID,
                    'schoolID' => $schoolID,
                    'subjectID' => $subjectID
                ]);
                return response()->json(['success' => false, 'error' => 'Missing required parameters']);
            }

            // Get session timetable definition (get the most recent one for the school)
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$definition) {
                Log::warning('No timetable definition found', ['schoolID' => $schoolID]);
                return response()->json(['success' => false, 'error' => 'No timetable definition found']);
            }

            // Get all sessions for this teacher and subject
            // Check both through classSubject relationship and direct subjectID
            $sessions = ClassSessionTimetable::with(['subclass.class', 'subject', 'classSubject.subject'])
                ->where('teacherID', $teacherID)
                ->where('definitionID', $definition->definitionID)
                ->where(function($query) use ($subjectID) {
                    // Check through classSubject relationship
                    $query->whereHas('classSubject', function($q) use ($subjectID) {
                        $q->where('subjectID', $subjectID);
                    })
                    // Or check direct subjectID field
                    ->orWhere('subjectID', $subjectID);
                })
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')")
                ->orderBy('start_time')
                ->get();

            Log::info('Query executed', [
                'sessions_count' => $sessions->count(),
                'subjectID' => $subjectID,
                'definitionID' => $definition->definitionID,
                'teacherID' => $teacherID
            ]);

            Log::info('Sessions found', ['count' => $sessions->count()]);

            if ($sessions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No session available for this subject',
                    'sessions' => []
                ]);
            }

            // Format sessions data
            $formattedSessions = $sessions->map(function($session) {
                $subjectName = 'N/A';
                if($session->classSubject && $session->classSubject->subject) {
                    $subjectName = $session->classSubject->subject->subject_name;
                } elseif($session->subject) {
                    $subjectName = $session->subject->subject_name;
                }
                if($session->is_prepo) {
                    $subjectName .= ' (Prepo)';
                }

                $className = '';
                $subclassName = '';
                if($session->subclass) {
                    if($session->subclass->class) {
                        $className = $session->subclass->class->class_name ?? '';
                    }
                    $subclassName = $session->subclass->subclass_name ?? '';
                }

                return [
                    'session_timetableID' => $session->session_timetableID,
                    'day' => $session->day ?? 'N/A',
                    'start_time' => $session->start_time ?? '',
                    'end_time' => $session->end_time ?? '',
                    'subject_name' => $subjectName,
                    'class_name' => $className,
                    'subclass_name' => $subclassName,
                    'is_prepo' => $session->is_prepo ?? false,
                ];
            });

            Log::info('Returning formatted sessions', ['count' => $formattedSessions->count()]);
            
            return response()->json([
                'success' => true,
                'sessions' => $formattedSessions->toArray(),
                'count' => $formattedSessions->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting sessions by subject', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'teacherID' => Session::get('teacherID'),
                'schoolID' => Session::get('schoolID'),
                'subjectID' => $request->input('subjectID')
            ]);
            return response()->json([
                'success' => false, 
                'error' => 'Failed to load sessions: ' . $e->getMessage(),
                'message' => 'An error occurred while loading sessions. Please try again.'
            ]);
        }
    }

    /**
     * Get all sessions for a teacher within a year (excluding weekends and holidays)
     */
    public function getAllSessionsForYear(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $year = $request->input('year', Carbon::now()->year);
            $sessionTimetableID = $request->input('session_timetableID');

            if (!$teacherID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            // Get session details
            $session = ClassSessionTimetable::with(['subclass.class', 'subject', 'classSubject.subject'])
                ->where('session_timetableID', $sessionTimetableID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$session) {
                return response()->json(['success' => false, 'error' => 'Session not found']);
            }

            // Get all holidays for the year
            $holidays = Holiday::where('schoolID', $schoolID)
                ->where(function($query) use ($year) {
                    $query->whereYear('start_date', $year)
                          ->orWhereYear('end_date', $year);
                })
                ->get();

            // Get all non-working events for the year
            $events = Event::where('schoolID', $schoolID)
                ->whereYear('event_date', $year)
                ->where('is_non_working_day', true)
                ->get();

            // Create holiday dates array
            $holidayDates = [];
            foreach ($holidays as $holiday) {
                $start = Carbon::parse($holiday->start_date);
                $end = Carbon::parse($holiday->end_date);
                while ($start <= $end) {
                    if ($start->year == $year) {
                        $holidayDates[] = $start->format('Y-m-d');
                    }
                    $start->addDay();
                }
            }
            foreach ($events as $event) {
                $holidayDates[] = Carbon::parse($event->event_date)->format('Y-m-d');
            }
            $holidayDates = array_unique($holidayDates);

            // Get all dates for the year that match the session day
            $yearStart = Carbon::create($year, 1, 1);
            $yearEnd = Carbon::create($year, 12, 31);
            $sessionDates = [];
            $currentDate = $yearStart->copy();

            while ($currentDate <= $yearEnd) {
                $dayName = $currentDate->format('l'); // Monday, Tuesday, etc.
                $dateStr = $currentDate->format('Y-m-d');
                
                // Check if it matches the session day and is not weekend or holiday
                if ($dayName === $session->day && !$currentDate->isWeekend() && !in_array($dateStr, $holidayDates)) {
                    // Check if lesson plan exists for this date
                    $lessonPlan = LessonPlan::where('session_timetableID', $sessionTimetableID)
                        ->where('lesson_date', $dateStr)
                        ->first();
                    
                    $sessionDates[] = [
                        'date' => $dateStr,
                        'formatted_date' => $currentDate->format('d/m/Y'),
                        'day_name' => $dayName,
                        'has_lesson_plan' => $lessonPlan ? true : false,
                        'lesson_plan_id' => $lessonPlan ? $lessonPlan->lesson_planID : null,
                    ];
                }
                
                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'session' => [
                        'session_timetableID' => $session->session_timetableID,
                        'day' => $session->day,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                    ],
                    'dates' => $sessionDates,
                    'total_sessions' => count($sessionDates),
                    'year' => $year,
                    'period' => 'year'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all sessions for year: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Check if lesson plan exists for a session and date
     */
    public function checkLessonPlanExists(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $sessionTimetableID = $request->input('session_timetableID');
            $date = $request->input('date');

            if (!$teacherID || !$sessionTimetableID || !$date) {
                return response()->json(['success' => false, 'exists' => false]);
            }

            $lessonPlan = LessonPlan::where('session_timetableID', $sessionTimetableID)
                ->where('lesson_date', $date)
                ->where('teacherID', $teacherID)
                ->first();

            return response()->json([
                'success' => true,
                'exists' => $lessonPlan ? true : false,
                'lesson_plan_id' => $lessonPlan ? $lessonPlan->lesson_planID : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking lesson plan exists: ' . $e->getMessage());
            return response()->json(['success' => false, 'exists' => false]);
        }
    }

    /**
     * Get existing lesson plan
     */
    public function getLessonPlan(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $sessionTimetableID = $request->input('session_timetableID');
            $date = $request->input('date');

            $lessonPlan = LessonPlan::where('session_timetableID', $sessionTimetableID)
                ->where('lesson_date', $date)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$lessonPlan) {
                return response()->json(['success' => false, 'error' => 'Lesson plan not found']);
            }

            // Get teacher name
            $teacherName = 'N/A';
            if ($lessonPlan->teacher) {
                $teacherName = trim(($lessonPlan->teacher->first_name ?? '') . ' ' . ($lessonPlan->teacher->last_name ?? ''));
            } elseif ($lessonPlan->teacherID) {
                $teacher = \App\Models\Teacher::find($lessonPlan->teacherID);
                if ($teacher) {
                    $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''));
                }
            }

            $data = $lessonPlan->toArray();
            $data['teacher_name'] = $teacherName;

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting lesson plan: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Download lesson plan as PDF
     */
    public function downloadLessonPlanPDF($lessonPlanID)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return redirect()->back()->with('error', 'Session expired');
            }

            // Get lesson plan
            $lessonPlan = LessonPlan::where('lesson_planID', $lessonPlanID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$lessonPlan) {
                return redirect()->back()->with('error', 'Lesson plan not found');
            }

            // Get school info
            $school = School::find($schoolID);
            $schoolType = $school && $school->school_type ? strtolower($school->school_type) : 'primary';
            $isPrimary = strpos($schoolType, 'primary') !== false || strpos($schoolType, 'pre') !== false;
            $schoolTypeDisplay = $isPrimary ? 'PRE AND PRIMARY SCHOOL' : 'SECONDARY SCHOOL';

            // Get teacher name
            $teacherName = 'N/A';
            if ($lessonPlan->teacher) {
                $teacherName = trim(($lessonPlan->teacher->first_name ?? '') . ' ' . ($lessonPlan->teacher->last_name ?? ''));
            }

            // Get school logo path and convert to base64 if exists
            $schoolLogoBase64 = null;
            if ($school && $school->school_logo) {
                $logoPath = public_path($school->school_logo);
                // Check if file exists and convert to base64
                if (file_exists($logoPath)) {
                    $imageData = file_get_contents($logoPath);
                    // Detect mime type from file extension
                    $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                    $mimeTypes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp'
                    ];
                    $mimeType = $mimeTypes[$extension] ?? 'image/png';
                    $schoolLogoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            }

            // Convert signatures to base64 if they exist
            $teacherSignatureBase64 = null;
            if ($lessonPlan->teacher_signature) {
                if (strpos($lessonPlan->teacher_signature, 'data:image') === 0) {
                    // Already base64 encoded
                    $teacherSignatureBase64 = $lessonPlan->teacher_signature;
                } elseif (file_exists($lessonPlan->teacher_signature)) {
                    // File path - convert to base64
                    $imageData = file_get_contents($lessonPlan->teacher_signature);
                    $extension = strtolower(pathinfo($lessonPlan->teacher_signature, PATHINFO_EXTENSION));
                    $mimeTypes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp'
                    ];
                    $mimeType = $mimeTypes[$extension] ?? 'image/png';
                    $teacherSignatureBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                } else {
                    // Assume it's already a data URL
                    $teacherSignatureBase64 = $lessonPlan->teacher_signature;
                }
            }
            
            $supervisorSignatureBase64 = null;
            if ($lessonPlan->supervisor_signature) {
                if (strpos($lessonPlan->supervisor_signature, 'data:image') === 0) {
                    // Already base64 encoded
                    $supervisorSignatureBase64 = $lessonPlan->supervisor_signature;
                } elseif (file_exists($lessonPlan->supervisor_signature)) {
                    // File path - convert to base64
                    $imageData = file_get_contents($lessonPlan->supervisor_signature);
                    $extension = strtolower(pathinfo($lessonPlan->supervisor_signature, PATHINFO_EXTENSION));
                    $mimeTypes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp'
                    ];
                    $mimeType = $mimeTypes[$extension] ?? 'image/png';
                    $supervisorSignatureBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                } else {
                    // Assume it's already a data URL
                    $supervisorSignatureBase64 = $lessonPlan->supervisor_signature;
                }
            }
            
            // Prepare data for PDF
            $data = [
                'lessonPlan' => $lessonPlan,
                'school' => $school,
                'schoolType' => $schoolTypeDisplay,
                'teacherName' => $teacherName,
                'schoolLogoBase64' => $schoolLogoBase64,
                'teacherSignatureBase64' => $teacherSignatureBase64,
                'supervisorSignatureBase64' => $supervisorSignatureBase64,
            ];

            // Generate PDF - Use Dompdf directly to avoid GD extension issues
            try {
                // Render the view to HTML
                $html = view('Teacher.pdf.lesson_plan', $data)->render();
                
                if (empty($html)) {
                    throw new \Exception('PDF view returned empty HTML');
                }
                
                // Use Dompdf directly
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                
                // Set options to avoid GD extension requirement
                $options = $dompdf->getOptions();
                $options->set('enable-local-file-access', false); // Disable since we use base64
                $options->set('isRemoteEnabled', true);
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isPhpEnabled', false);
                $options->set('chroot', public_path());
                $dompdf->setOptions($options);
                
                // Render PDF
                $dompdf->render();
                
                $subjectName = $lessonPlan->subject ? str_replace(' ', '_', $lessonPlan->subject) : 'Lesson_Plan';
                $filename = 'Lesson_Plan_' . $subjectName . '_' . Carbon::parse($lessonPlan->lesson_date)->format('Y-m-d') . '.pdf';
                $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
                
                // Return PDF as download
                return response()->streamDownload(function() use ($dompdf) {
                    echo $dompdf->output();
                }, $filename, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            } catch (\Exception $pdfError) {
                Log::error('Error generating PDF: ' . $pdfError->getMessage());
                Log::error('PDF Error Stack: ' . $pdfError->getTraceAsString());
                
                // Return error response
                return redirect()->back()->with('error', 'Failed to generate PDF: ' . $pdfError->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error downloading lesson plan PDF: ' . $e->getMessage());
            Log::error('Error Stack: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Update lesson plan
     */
    public function updateLessonPlan(Request $request, $lessonPlanID)
    {
        try {
            $teacherID = Session::get('teacherID');

            $lessonPlan = LessonPlan::where('lesson_planID', $lessonPlanID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$lessonPlan) {
                return response()->json(['success' => false, 'error' => 'Lesson plan not found']);
            }

            $validator = Validator::make($request->all(), [
                'main_competence' => 'nullable|string',
                'specific_competence' => 'nullable|string',
                'main_activity' => 'nullable|string',
                'specific_activity' => 'nullable|string',
                'teaching_learning_resources' => 'nullable|string',
                'references' => 'nullable|string',
                'lesson_stages' => 'nullable|array',
                'remarks' => 'nullable|string',
                'reflection' => 'nullable|string',
                'evaluation' => 'nullable|string',
                'teacher_signature' => 'nullable|string',
                'supervisor_signature' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()->first()]);
            }

            // Update attendance stats
            $attendanceStats = $this->getAttendanceStatsForDate($lessonPlan->session_timetableID, $lessonPlan->lesson_date);

            $lessonPlan->update([
                'registered_girls' => $attendanceStats['registered_girls'],
                'registered_boys' => $attendanceStats['registered_boys'],
                'registered_total' => $attendanceStats['registered_total'],
                'present_girls' => $attendanceStats['present_girls'],
                'present_boys' => $attendanceStats['present_boys'],
                'present_total' => $attendanceStats['present_total'],
                'main_competence' => $request->input('main_competence', $lessonPlan->main_competence),
                'specific_competence' => $request->input('specific_competence', $lessonPlan->specific_competence),
                'main_activity' => $request->input('main_activity', $lessonPlan->main_activity),
                'specific_activity' => $request->input('specific_activity', $lessonPlan->specific_activity),
                'teaching_learning_resources' => $request->input('teaching_learning_resources', $lessonPlan->teaching_learning_resources),
                'references' => $request->input('references', $lessonPlan->references),
                'lesson_stages' => $request->input('lesson_stages', $lessonPlan->lesson_stages),
                'remarks' => $request->input('remarks', $lessonPlan->remarks),
                'reflection' => $request->input('reflection', $lessonPlan->reflection),
                'evaluation' => $request->input('evaluation', $lessonPlan->evaluation),
                'teacher_signature' => $request->input('teacher_signature', $lessonPlan->teacher_signature),
                'supervisor_signature' => $request->input('supervisor_signature', $lessonPlan->supervisor_signature),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson plan updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating lesson plan: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Helper method to get attendance stats for a date
     */
    private function getAttendanceStatsForDate($sessionTimetableID, $date)
    {
        $session = ClassSessionTimetable::find($sessionTimetableID);
        if (!$session) {
            return [
                'registered_girls' => 0,
                'registered_boys' => 0,
                'registered_total' => 0,
                'present_girls' => 0,
                'present_boys' => 0,
                'present_total' => 0,
            ];
        }

        $students = Student::where('subclassID', $session->subclassID)
            ->where('status', 'Active')
            ->get();

        $registeredGirls = $students->where('gender', 'Female')->count();
        $registeredBoys = $students->where('gender', 'Male')->count();
        $registeredTotal = $students->count();

        // Get attendance records from student_session_attendance table
        $attendanceRecords = StudentSessionAttendance::where('session_timetableID', $sessionTimetableID)
            ->where('attendance_date', $date)
            ->get();

        $presentGirls = 0;
        $presentBoys = 0;
        $presentTotal = 0;

        foreach ($attendanceRecords as $record) {
            $student = $students->where('studentID', $record->studentID)->first();
            if ($student) {
                // Convert status to lowercase for comparison (database stores lowercase)
                $status = strtolower($record->status ?? '');
                // Count as present if status is 'present', 'late', or 'excused' (not 'absent')
                if (in_array($status, ['present', 'late', 'excused'])) {
                    if ($student->gender === 'Female') {
                        $presentGirls++;
                    } else {
                        $presentBoys++;
                    }
                    $presentTotal++;
                }
            }
        }

        return [
            'registered_girls' => $registeredGirls,
            'registered_boys' => $registeredBoys,
            'registered_total' => $registeredTotal,
            'present_girls' => $presentGirls,
            'present_boys' => $presentBoys,
            'present_total' => $presentTotal,
        ];
    }

    /**
     * Get lesson plans by filter (date range or year)
     */
    public function getLessonPlansByFilter(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $sessionTimetableID = $request->input('session_timetableID');
            $filterType = $request->input('filter_type');

            if (!$teacherID || !$sessionTimetableID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            $query = LessonPlan::where('session_timetableID', $sessionTimetableID)
                ->where('teacherID', $teacherID);

            if ($filterType === 'date_range') {
                $fromDate = $request->input('from_date');
                $toDate = $request->input('to_date');
                
                if (!$fromDate || !$toDate) {
                    return response()->json(['success' => false, 'error' => 'Please provide both from and to dates']);
                }
                
                $query->whereBetween('lesson_date', [$fromDate, $toDate]);
            } else {
                $year = $request->input('year');
                if (!$year) {
                    return response()->json(['success' => false, 'error' => 'Please provide a year']);
                }
                $query->whereYear('lesson_date', $year);
            }

            $lessonPlans = $query->orderBy('lesson_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'lesson_plans' => $lessonPlans
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting lesson plans by filter: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get lesson plan by ID
     */
    public function getLessonPlanById(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $lessonPlanID = $request->input('lesson_planID');

            $lessonPlan = LessonPlan::where('lesson_planID', $lessonPlanID)
                ->where('teacherID', $teacherID)
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
            Log::error('Error getting lesson plan by ID: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Send lesson plan to admin
     */
    public function sendLessonPlanToAdmin(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $lessonPlanID = $request->input('lesson_planID');

            if (!$teacherID) {
                return response()->json(['success' => false, 'error' => 'Session expired']);
            }

            $lessonPlan = LessonPlan::where('lesson_planID', $lessonPlanID)
                ->where('teacherID', $teacherID)
                ->first();

            if (!$lessonPlan) {
                return response()->json(['success' => false, 'error' => 'Lesson plan not found']);
            }

            if ($lessonPlan->sent_to_admin) {
                return response()->json(['success' => false, 'error' => 'Lesson plan already sent to admin']);
            }

            $lessonPlan->update([
                'sent_to_admin' => true,
                'sent_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson plan sent to admin successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending lesson plan to admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Download bulk lesson plans as PDF
     */
    public function downloadBulkLessonPlansPDF(Request $request)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');
            $lessonPlanIDs = $request->input('lesson_plan_ids', []);

            if (!$teacherID || !$schoolID) {
                return redirect()->back()->with('error', 'Session expired');
            }

            if (empty($lessonPlanIDs)) {
                return redirect()->back()->with('error', 'No lesson plans selected');
            }

            $lessonPlans = LessonPlan::whereIn('lesson_planID', $lessonPlanIDs)
                ->where('teacherID', $teacherID)
                ->orderBy('lesson_date', 'asc')
                ->get();

            if ($lessonPlans->isEmpty()) {
                return redirect()->back()->with('error', 'No lesson plans found');
            }

            // Get school info
            $school = School::find($schoolID);
            $schoolType = $school && $school->school_type ? strtolower($school->school_type) : 'primary';
            $isPrimary = strpos($schoolType, 'primary') !== false || strpos($schoolType, 'pre') !== false;
            $schoolTypeDisplay = $isPrimary ? 'PRE AND PRIMARY SCHOOL' : 'SECONDARY SCHOOL';

            // Prepare data for PDF
            $data = [
                'lessonPlans' => $lessonPlans,
                'school' => $school,
                'schoolType' => $schoolTypeDisplay,
            ];

            // Generate PDF
            $pdf = PDF::loadView('Teacher.pdf.bulk_lesson_plans', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'Lesson_Plans_Bulk_' . Carbon::now()->format('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error downloading bulk lesson plans PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function manageTeacherPermissions(Request $request)
    {
        $user = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Teacher' || !$teacherID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $activeTab = $request->get('tab', 'request');

        $permissions = PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'teacher')
            ->where('teacherID', $teacherID)
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingPermissions = $permissions->where('status', 'pending');
        $approvedPermissions = $permissions->where('status', 'approved');
        $rejectedPermissions = $permissions->where('status', 'rejected');

        if (in_array($activeTab, ['pending', 'approved', 'rejected'], true)) {
            PermissionRequest::where('schoolID', $schoolID)
                ->where('requester_type', 'teacher')
                ->where('teacherID', $teacherID)
                ->where('status', $activeTab)
                ->update(['is_read_by_requester' => true]);
        }

        $unreadPendingCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'teacher')
            ->where('teacherID', $teacherID)
            ->where('status', 'pending')
            ->where('is_read_by_requester', false)
            ->count();
        $unreadApprovedCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'teacher')
            ->where('teacherID', $teacherID)
            ->where('status', 'approved')
            ->where('is_read_by_requester', false)
            ->count();
        $unreadRejectedCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'teacher')
            ->where('teacherID', $teacherID)
            ->where('status', 'rejected')
            ->where('is_read_by_requester', false)
            ->count();

        return view('Teacher.manage_permissions', [
            'activeTab' => $activeTab,
            'pendingPermissions' => $pendingPermissions,
            'approvedPermissions' => $approvedPermissions,
            'rejectedPermissions' => $rejectedPermissions,
            'unreadPendingCount' => $unreadPendingCount,
            'unreadApprovedCount' => $unreadApprovedCount,
            'unreadRejectedCount' => $unreadRejectedCount,
        ]);
    }

    public function storeTeacherPermission(Request $request)
    {
        $user = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Teacher' || !$teacherID || !$schoolID) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'time_mode' => 'required|in:days,hours',
            'days_count' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'reason_type' => 'required|in:medical,official,professional,emergency,other',
            'reason_description' => 'required|string|min:5',
        ]);

        if ($validated['time_mode'] === 'days') {
            if (empty($validated['days_count']) || empty($validated['start_date']) || empty($validated['end_date'])) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please provide days count and date range.'], 422);
                }
                return redirect()->back()->with('error', 'Please provide days count and date range.')->withInput();
            }
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $daysCount = $startDate->diffInDays($endDate) + 1;
            if ($daysCount > 7) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Days exceed limit. Use 7 days or less.'], 422);
                }
                return redirect()->back()->with('error', 'Days exceed limit. Use 7 days or less.')->withInput();
            }
        }

        if ($validated['time_mode'] === 'hours') {
            if (empty($validated['start_time']) || empty($validated['end_time'])) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please provide start and end time.'], 422);
                }
                return redirect()->back()->with('error', 'Please provide start and end time.')->withInput();
            }
            $start = Carbon::createFromFormat('H:i', $validated['start_time']);
            $end = Carbon::createFromFormat('H:i', $validated['end_time']);
            if ($end->lessThanOrEqualTo($start)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'End time must be after start time.'], 422);
                }
                return redirect()->back()->with('error', 'End time must be after start time.')->withInput();
            }
        }

        $computedDays = null;
        if ($validated['time_mode'] === 'days') {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $computedDays = $startDate->diffInDays($endDate) + 1;
        }

        PermissionRequest::create([
            'schoolID' => $schoolID,
            'requester_type' => 'teacher',
            'teacherID' => $teacherID,
            'time_mode' => $validated['time_mode'],
            'days_count' => $validated['time_mode'] === 'days' ? $computedDays : null,
            'start_date' => $validated['time_mode'] === 'days' ? $validated['start_date'] : null,
            'end_date' => $validated['time_mode'] === 'days' ? $validated['end_date'] : null,
            'start_time' => $validated['time_mode'] === 'hours' ? $validated['start_time'] : null,
            'end_time' => $validated['time_mode'] === 'hours' ? $validated['end_time'] : null,
            'reason_type' => $validated['reason_type'],
            'reason_description' => $validated['reason_description'],
            'status' => 'pending',
            'is_read_by_admin' => false,
            'is_read_by_requester' => true,
        ]);

        $teacher = Teacher::where('id', $teacherID)->first();
        $school = School::where('schoolID', $schoolID)->first();
        $smsService = new SmsService();

        if ($teacher && $teacher->phone_number) {
            $smsService->sendSms($teacher->phone_number, 'Your permission request has been submitted to Admin. Please wait for approval.');
        }

        if ($school && $school->phone) {
            $teacherName = $teacher ? trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')) : 'Teacher';
            $reasonLabel = ucfirst($validated['reason_type']);
            $schoolName = $school->school_name ?? 'School';
            $smsService->sendSms($school->phone, "{$schoolName}: New permission request by {$teacherName}. Reason: {$reasonLabel}. Please review.");
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Permission request submitted successfully.']);
        }
        return redirect()->route('teacher.permissions')->with('success', 'Permission request submitted successfully.');
    }
}
