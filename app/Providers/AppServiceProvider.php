<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Examination;
use App\Models\ClassSessionTimetable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
        $school = Session::get('schoolID');
        $schoolID = null;
        $school_details = null;
        $role = null;
        $user = Session::get('user_type');
        $user_type = null;
        $teacherID = Session::get('teacherID');
        $class_teacher = null;
        $coordinator = null;
        $teacher = null;
        if ($school)
        {
           $schoolID = Session::get('schoolID');
           $school_details = School::where('schoolID',$schoolID)->first();
        }
        if($user)
        {
            $user_type = Session::get('user_type');
            if($user_type == 'Teacher')
    {
            // Get teacher data for profile image
            if ($teacherID) {
                $teacher = Teacher::find($teacherID);
            }
    $class_teacher = DB::table('subclasses')
    ->join('teachers', 'subclasses.teacherID', '=', 'teachers.id')
    ->select('subclasses.classID', 'subclasses.subclass_name')
    ->where('teachers.id', $teacherID)
    ->first();

    $coordinator = DB::table('teachers')
    ->join('subclasses', 'teachers.id', '=', 'subclasses.teacherID')
    ->join('classes', 'subclasses.classID', '=', 'classes.classID')
    ->select('classes.classID', 'classes.class_name')
    ->where('teachers.id', $teacherID)
    ->get();

    // Get roles with permissions for teacher
    $role = DB::table('teachers')
    ->join('role_user', 'teachers.id', '=', 'role_user.teacher_id')
    ->join('roles', 'role_user.role_id', '=', 'roles.id')
    ->where('role_user.teacher_id', $teacherID)
    ->select('teachers.id as teacherID', 'roles.role_name', 'roles.id as roleID')
    ->get();

    // Get all permissions for teacher's roles with categories
    $teacherPermissions = collect();
    $teacherPermissionsByCategory = collect();
    if ($role && $role->count() > 0) {
        $roleIds = $role->pluck('roleID')->toArray();
        $permissionsData = DB::table('permissions')
            ->whereIn('role_id', $roleIds)
            ->select('name', 'role_id', 'permission_category')
            ->get();

        $teacherPermissions = $permissionsData->pluck('name')->unique()->values();

        // Group permissions by category
        $teacherPermissionsByCategory = $permissionsData->groupBy('permission_category')
            ->map(function($perms) {
                return $perms->pluck('name')->unique()->values();
            });
    }
    
    // Get teacher notifications
    $teacherNotifications = collect();
    if ($teacherID && $schoolID) {
        // Exam rejection notifications from session
        $sessionKeys = Session::all();
        foreach ($sessionKeys as $key => $value) {
            if (strpos($key, "teacher_notification_{$teacherID}_exam_rejected_") === 0 && is_array($value)) {
                $teacherNotifications->push([
                    'type' => 'exam_rejected',
                    'icon' => 'fa-times-circle',
                    'color' => 'danger',
                    'title' => 'Exam Rejected',
                    'message' => $value['message'] ?? 'Your examination has been rejected',
                    'date' => $value['created_at'] ?? now()->toDateTimeString(),
                    'link' => '#'
                ]);
            }
        }
        
        // New examinations (recent)
        try {
            $recentExams = Examination::where('schoolID', $schoolID)
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->where('approval_status', 'Approved')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            
            foreach ($recentExams as $exam) {
                $teacherNotifications->push([
                    'type' => 'new_exam',
                    'icon' => 'fa-calendar-check-o',
                    'color' => 'info',
                    'title' => 'New Examination',
                    'message' => $exam->exam_name . ' - ' . ($exam->year ?? ''),
                    'date' => $exam->created_at->toDateTimeString(),
                    'link' => route('supervise_exams')
                ]);
            }
            
            // Session time notifications (sessions happening today or soon)
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();
            
            if ($definition) {
                $today = Carbon::today(config('app.timezone'));
                $todayDayName = $today->format('l');
                $now = Carbon::now(config('app.timezone'));
                
                $todaySessions = ClassSessionTimetable::with(['subject', 'classSubject.subject', 'subclass.class'])
                    ->where('teacherID', $teacherID)
                    ->where('definitionID', $definition->definitionID)
                    ->where('day', $todayDayName)
                    ->get();
                
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
                    
                    // Check if it's a holiday or weekend
                    $isHoliday = false;
                    $isWeekend = $today->isWeekend();
                    
                    // Check for holidays
                    try {
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
                    } catch (\Exception $e) {
                        // Silently fail if there's an error
                    }
                    
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
                        
                        $teacherNotifications->push([
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
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Error fetching session notifications: ' . $e->getMessage(), [
                'teacherID' => $teacherID ?? null,
                'schoolID' => $schoolID ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // Sort notifications by date
    $teacherNotifications = $teacherNotifications->sortByDesc(function($notification) {
        return $notification['date'];
    })->values()->take(10);
            }
        }

       // Set locale from session for all views
       $locale = Session::get('locale', 'sw');
       app()->setLocale($locale);

       $view->with([
    'role'   => $role,
    'teacherPermissions' => $teacherPermissions ?? collect(),
    'teacherPermissionsByCategory' => $teacherPermissionsByCategory ?? collect(),
    'schoolID'   => $schoolID,
    'school_details'   => $school_details,
    'class_teacher'   => $class_teacher,
    'coordinator'   => $coordinator,
    'user_type'    => $user_type,
    'teacher' => $teacher,
    'locale' => $locale,
    'teacherNotifications' => $teacherNotifications ?? collect(),
]);

    });
    }
}
