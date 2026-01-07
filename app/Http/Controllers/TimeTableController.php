<?php

namespace App\Http\Controllers;

use App\Models\ExamTimetable;
use App\Models\ExamTimetableSchoolWide;
use App\Models\ExamSuperviseTeacher;
use App\Models\Examination;
use App\Models\Subclass;
use App\Models\ClassSubject as ClassSubjectModel;
use App\Models\SchoolSubject;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\ExamHall;
use App\Models\ExamHallSupervisor;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TimeTableController extends Controller
{
    /**
     * Store teacher assignments for batch SMS notifications
     */
    private $teacherAssignments = [];

    /**
     * Check if user has a specific permission
     */
    private function hasPermission($permissionName)
    {
        $userType = Session::get('user_type');

        // Admin has ALL permissions by default
        if ($userType === 'Admin') {
            return true;
        }

        // For teachers, check their role permissions
        if ($userType === 'Teacher') {
            $teacherID = Session::get('teacherID');
            if (!$teacherID) {
                return false;
            }

            // Get teacher's roles
            $roles = DB::table('teachers')
                ->join('role_user', 'teachers.id', '=', 'role_user.teacher_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.teacher_id', $teacherID)
                ->select('roles.id as roleID')
                ->get();

            if ($roles->count() === 0) {
                return false;
            }

            $roleIds = $roles->pluck('roleID')->toArray();

            // Check if any role has this permission
            $hasPermission = DB::table('permissions')
                ->whereIn('role_id', $roleIds)
                ->where('name', $permissionName)
                ->exists();

            return $hasPermission;
        }

        return false;
    }

    public function timeTable()
    {
        $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        // Check permission - allow if user has any timetable management permission
        // New format: timetable_create, timetable_update, timetable_delete, timetable_read_only
        $timetablePermissions = [
            'timetable_create',
            'timetable_update',
            'timetable_delete',
            'timetable_read_only',
            // Legacy permissions (for backward compatibility)
            'create_timetable',
            'edit_timetable',
            'delete_timetable',
            'show_timetable',
            'view_all_timetable',
        ];
        
        $hasAnyPermission = false;
        if ($user === 'Admin') {
            $hasAnyPermission = true;
        } else {
            foreach ($timetablePermissions as $permission) {
                if ($this->hasPermission($permission)) {
                    $hasAnyPermission = true;
                    break;
                }
            }
        }
        
        if (!$hasAnyPermission) {
            return redirect()->back()->with('error', 'You do not have permission to access timetables.');
        }

        $schoolID = Session::get('schoolID');

        // Get scheduled examinations
        $examinations = Examination::where('schoolID', $schoolID)
            ->where('status', 'scheduled')
            ->where('approval_status', 'Approved')
            ->orderBy('year', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get subclasses
        $subclasses = Subclass::whereHas('class', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->with('class')
            ->orderBy('subclass_name')
            ->get();

        // Get teachers
        $teachers = Teacher::where('schoolID', $schoolID)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Get classes for view dropdown
        $classes = ClassModel::where('schoolID', $schoolID)
            ->orderBy('class_name')
            ->get();

        // Get school subjects (for school-wide timetable)
        $schoolSubjects = SchoolSubject::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('subject_name')
            ->get();

        // Get school information for PDF
        $school = \App\Models\School::find($schoolID);

        // Get teacher permissions for the view
        $teacherPermissions = $this->getTeacherPermissions();

        return view('Admin.manage_Timetable', compact('examinations', 'subclasses', 'teachers', 'classes', 'schoolSubjects', 'school', 'teacherPermissions'));
    }

    /**
     * Get all permissions for the current teacher
     */
    private function getTeacherPermissions()
    {
        $userType = Session::get('user_type');

        // Admin has all permissions
        if ($userType === 'Admin') {
            return collect(); // Return empty collection, Admin checks are done separately
        }

        // For teachers, get all their permissions from their roles
        if ($userType === 'Teacher') {
            $teacherID = Session::get('teacherID');
            if (!$teacherID) {
                return collect();
            }

            // Get teacher's roles
            $roles = DB::table('teachers')
                ->join('role_user', 'teachers.id', '=', 'role_user.teacher_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.teacher_id', $teacherID)
                ->select('roles.id as roleID')
                ->get();

            if ($roles->count() === 0) {
                return collect();
            }

            $roleIds = $roles->pluck('roleID')->toArray();

            // Get all permissions for these roles
            $permissions = DB::table('permissions')
                ->whereIn('role_id', $roleIds)
                ->pluck('name')
                ->unique()
                ->values();

            return $permissions;
        }

        return collect();
    }

    public function teacher_time_table()
    {
         $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Teacher.teacher_timetable');
    }

    public function supervise_exam_time_table()
    {
         $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Teacher.supervise_exam');
    }

    /**
     * Get subjects for selected subclass
     */
    public function getSubclassSubjects(Request $request)
    {
        $subclassID = $request->input('subclassID');
        $schoolID = Session::get('schoolID');

        if (!$subclassID) {
            return response()->json(['error' => 'Subclass ID is required'], 400);
        }

        $subclass = Subclass::find($subclassID);
        if (!$subclass) {
            return response()->json(['error' => 'Subclass not found'], 404);
        }

        // Get active subjects for this subclass
        $subjects = ClassSubjectModel::where('subclassID', $subclassID)
            ->where('status', 'Active')
            ->with(['subject', 'teacher'])
            ->get()
            ->map(function($cs) {
                return [
                    'class_subjectID' => $cs->class_subjectID,
                    'subjectID' => $cs->subjectID,
                    'subject_name' => $cs->subject->subject_name ?? 'N/A',
                    'subject_code' => $cs->subject->subject_code ?? '',
                    'teacherID' => $cs->teacherID,
                    'teacher_name' => $cs->teacher ? ($cs->teacher->first_name . ' ' . $cs->teacher->last_name) : 'Not Assigned',
                ];
            });

        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    /**
     * Get school subjects (for school-wide timetable)
     */
    public function getSchoolSubjects(Request $request)
    {
        $schoolID = Session::get('schoolID');

        $subjects = SchoolSubject::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('subject_name')
            ->get();

        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    /**
     * Store exam timetable
     */
    public function storeExamTimetable(Request $request)
    {
        // Check create permission - New format: timetable_create
        if (!$this->hasPermission('timetable_create')) {
            return response()->json([
                'error' => 'You do not have permission to create timetables. You need timetable_create permission.',
            ], 403);
        }

        $timetableType = $request->timetable_type;

        // Different validation rules based on timetable type
        if ($timetableType === 'class_specific') {
            $validator = Validator::make($request->all(), [
                'examID' => 'required|exists:examinations,examID',
                'subclassID' => 'required|exists:subclasses,subclassID',
                'exam_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'teacherID' => 'required|exists:teachers,id',
                'timetable_type' => 'required|in:class_specific,school_wide',
                'class_subjectID' => 'required|exists:class_subjects,class_subjectID',
                'notes' => 'nullable|string|max:500',
            ]);
        } else {
            // school_wide - check creation method
            $creationMethod = $request->input('creation_method', 'custom');
            
            if ($creationMethod === 'automatic') {
                // Automatic generation - validate settings
                $validator = Validator::make($request->all(), [
                    'examID' => 'required|exists:examinations,examID',
                    'timetable_type' => 'required|in:class_specific,school_wide',
                    'creation_method' => 'required|in:automatic,custom',
                    'exam_start_date' => 'required|date',
                    'exam_end_date' => 'required|date|after_or_equal:exam_start_date',
                    'exam_duration' => 'required|integer|min:30|max:300',
                    'daily_start_time' => 'required|date_format:H:i',
                    'daily_end_time' => 'required|date_format:H:i|after:daily_start_time',
                    'max_exams_per_day' => 'required|integer|min:1|max:6',
                    'break_start_time' => 'nullable|date_format:H:i',
                    'break_duration' => 'nullable|integer|min:5|max:60',
                ]);
            } else {
                // Custom/manual entry - validate days array
                $validator = Validator::make($request->all(), [
                    'examID' => 'required|exists:examinations,examID',
                    'timetable_type' => 'required|in:class_specific,school_wide',
                    'creation_method' => 'required|in:automatic,custom',
                    'days' => 'required|array|min:1',
                    'days.*.date' => 'required|date',
                    'days.*.day' => 'required|string',
                    'days.*.subjects' => 'required|array|min:1',
                    'days.*.subjects.*.subjectID' => 'required|exists:school_subjects,subjectID',
                    'days.*.subjects.*.start_time' => 'required|date_format:H:i',
                    'days.*.subjects.*.end_time' => 'required|date_format:H:i|after:days.*.subjects.*.start_time',
                ]);
            }
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schoolID = Session::get('schoolID');
        $examID = $request->examID;

        // Validate exam is scheduled
        $exam = Examination::where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->where('status', 'scheduled')
            ->where('approval_status', 'Approved')
            ->first();

        if (!$exam) {
            return response()->json(['error' => 'Selected examination is not scheduled or not approved'], 400);
        }

        // For class_specific, validate exam date
        if ($timetableType === 'class_specific') {
            $examDate = $request->exam_date;
            $startTime = $request->start_time;
            $endTime = $request->end_time;

            // Validate exam date is within exam period
            if ($examDate < $exam->start_date || $examDate > $exam->end_date) {
                return response()->json(['error' => 'Exam date must be within the examination period'], 400);
            }

            // Validate end time is not after 12:00 PM (noon)
            $endTimeObj = \Carbon\Carbon::createFromFormat('H:i', $endTime);
            if ($endTimeObj->format('H:i') > '12:00') {
                return response()->json(['error' => 'Exam must end by 12:00 PM (noon)'], 400);
            }
        } else {
            // For school_wide, validate overall times (no 12:00 PM restriction for school-wide)
            // User can set any end time they want
        }

        try {
            DB::beginTransaction();

            $createdEntries = [];
            $errors = [];

            if ($timetableType === 'class_specific') {
                // Single entry for class_specific
                $subclassID = $request->subclassID;
                $teacherID = $request->teacherID;
                $classSubjectID = $request->class_subjectID;

                // Validate teacher is from same school
                $teacher = Teacher::where('id', $teacherID)
                    ->where('schoolID', $schoolID)
                    ->first();

                if (!$teacher) {
                    return response()->json(['error' => 'Teacher not found or not from your school'], 400);
                }

                // Check for teacher conflict
                $teacherConflict = ExamTimetable::where('teacherID', $teacherID)
                    ->where('exam_date', $examDate)
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->where(function($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                              ->where('end_time', '>', $startTime);
                        })->orWhere(function($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<', $endTime)
                              ->where('end_time', '>=', $endTime);
                        })->orWhere(function($q) use ($startTime, $endTime) {
                            $q->where('start_time', '>=', $startTime)
                              ->where('end_time', '<=', $endTime);
                        });
                    })
                    ->exists();

                if ($teacherConflict) {
                    return response()->json(['error' => 'This teacher is already assigned to supervise another exam at this time'], 400);
                }

                // Check for duplicate subject
                $duplicateSubject = ExamTimetable::where('examID', $examID)
                    ->where('subclassID', $subclassID)
                    ->where('class_subjectID', $classSubjectID)
                    ->exists();

                if ($duplicateSubject) {
                    return response()->json(['error' => 'This subject is already scheduled for this class in this examination'], 400);
                }

                $examTimetable = new ExamTimetable();
                $examTimetable->schoolID = $schoolID;
                $examTimetable->examID = $examID;
                $examTimetable->subclassID = $subclassID;
                $examTimetable->teacherID = $teacherID;
                $examTimetable->exam_date = $examDate;
                $examTimetable->start_time = $startTime;
                $examTimetable->end_time = $endTime;
                $examTimetable->timetable_type = $timetableType;
                $examTimetable->class_subjectID = $classSubjectID;
                
                // Get subjectID from class_subject
                $classSubject = ClassSubjectModel::with('subject')->find($classSubjectID);
                $examTimetable->subjectID = $classSubject ? $classSubject->subjectID : null;
                
                $examTimetable->notes = $request->notes;
                $examTimetable->save();

                $createdEntries[] = $examTimetable->load(['examination', 'subclass', 'teacher', 'classSubject.subject']);

                // Assign supervise teachers to halls for class_specific timetable
                $this->assignSupervisorsForClassSpecificTimetable(
                    $examID,
                    $examTimetable->exam_timetableID,
                    $examTimetable->subjectID,
                    $schoolID,
                    $examTimetable
                );

            } else {
                // School-wide timetable
                $creationMethod = $request->input('creation_method', 'custom');
                
                if ($creationMethod === 'automatic') {
                    // Automatic generation
                    $createdEntries = $this->generateAutomaticTimetable(
                        $schoolID,
                        $exam,
                        $request
                    );
                } else {
                    // Custom/manual entry
                    $createdEntries = $this->createNewSchoolWideTimetable(
                        $schoolID,
                        $exam,
                        $request->days,
                        $request
                    );
                }
            }

            // For class_specific, check if entries were created
            if ($timetableType === 'class_specific' && empty($createdEntries) && !empty($errors)) {
                DB::rollBack();
                return response()->json(['error' => implode('; ', $errors)], 400);
            }

            // For school_wide, check if entries were created
            if ($timetableType === 'school_wide' && empty($createdEntries)) {
                DB::rollBack();
                return response()->json(['error' => 'Failed to create timetable entries. Please check your settings.'], 400);
            }

            DB::commit();

            // Send SMS to supervise teachers for class_specific timetable
            if ($timetableType === 'class_specific' && !empty($createdEntries)) {
                $this->sendClassSpecificSupervisorSMS($createdEntries);
            }

            $message = count($createdEntries) . ' timetable entry/entries created successfully';
            if (!empty($errors)) {
                $message .= '. Some errors occurred: ' . implode('; ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created_entries' => $createdEntries,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error creating exam timetable: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to create exam timetable: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get exam timetables
     */
    public function getExamTimetables(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $examID = $request->input('examID');
        $viewType = $request->input('view_type', 'class_specific'); // class_specific or school_wide
        $classID = $request->input('classID'); // For class_specific view

        if ($viewType === 'school_wide') {
            // Get school-wide timetables from exam_timetable table
            $query = ExamTimetableSchoolWide::where('schoolID', $schoolID)
                ->with(['examination', 'subject']);

            if ($examID) {
                $query->where('examID', $examID);
            }

            $timetables = $query->orderBy('exam_date')
                ->orderBy('start_time')
                ->get()
                ->map(function($tt) {
                    // Format times properly
                    $startTime = $tt->start_time;
                    $endTime = $tt->end_time;

                    // If time is in datetime format, extract just the time part
                    if (is_string($startTime) && strpos($startTime, ' ') !== false) {
                        $startTime = substr($startTime, strpos($startTime, ' ') + 1, 5);
                    }
                    if (is_string($endTime) && strpos($endTime, ' ') !== false) {
                        $endTime = substr($endTime, strpos($endTime, ' ') + 1, 5);
                    }

                    // Ensure time is in HH:mm format
                    if (is_string($startTime) && strlen($startTime) > 5) {
                        $startTime = substr($startTime, 0, 5);
                    }
                    if (is_string($endTime) && strlen($endTime) > 5) {
                        $endTime = substr($endTime, 0, 5);
                    }

                    $tt->start_time = $startTime;
                    $tt->end_time = $endTime;

                    return $tt;
                });

            return response()->json(['success' => true, 'timetables' => $timetables]);
        } else {
            // Class-specific timetables
            $query = ExamTimetable::where('schoolID', $schoolID)
                ->with(['examination', 'subclass.class', 'teacher', 'classSubject.subject', 'subject']);

            if ($examID) {
                $query->where('examID', $examID);
            }

            if ($viewType === 'class_specific' && $classID) {
                // Get timetables for specific class (all subclasses)
                $query->whereHas('subclass', function($q) use ($classID) {
                    $q->where('classID', $classID);
                });
            }

            $timetables = $query->orderBy('exam_date')
                ->orderBy('start_time')
                ->get();

            return response()->json(['success' => true, 'timetables' => $timetables]);
        }
    }

    /**
     * Create automatic school-wide timetable
     */
    private function createAutomaticSchoolWideTimetable($schoolID, $exam, $overallStartTime, $overallEndTime, $breakStartTime, $breakEndTime, $examDuration, $maxExamsPerDay, $subjectsPerDay, $autoAssignTeachers, $autoArrangeSubjects, $skipWeekends, $notes)
    {
        $createdEntries = [];
        $errors = [];

        // Get all subclasses for this school
        $subclasses = Subclass::whereHas('class', function($q) use ($schoolID) {
            $q->where('schoolID', $schoolID);
        })->with('class')->get();

        // Get all active school subjects (NOT class subjects)
        // For school-wide timetable, we use school subjects which are common to all classes
        // This avoids the issue of having different subjects per class
        $subjects = SchoolSubject::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get();

        if ($subjects->isEmpty()) {
            throw new \Exception('No active school subjects found for this school. Please ensure school subjects are created and active.');
        }

        // Get all teachers for auto-assignment
        $teachers = Teacher::where('schoolID', $schoolID)->get();
        if ($teachers->isEmpty() && $autoAssignTeachers) {
            throw new \Exception('No teachers found for auto-assignment');
        }

        // Calculate available time slots per day (excluding break)
        $overallStart = \Carbon\Carbon::createFromFormat('H:i', $overallStartTime);
        $overallEnd = \Carbon\Carbon::createFromFormat('H:i', $overallEndTime);
        $breakStart = \Carbon\Carbon::createFromFormat('H:i', $breakStartTime);
        $breakEnd = \Carbon\Carbon::createFromFormat('H:i', $breakEndTime);

        // Validate time order
        if ($overallStart->gte($overallEnd)) {
            throw new \Exception('Overall start time must be before end time');
        }
        if ($breakStart->gte($breakEnd)) {
            throw new \Exception('Break start time must be before end time');
        }
        // Break must be within overall time range: overallStart < breakStart < breakEnd < overallEnd
        if ($breakStart->lte($overallStart)) {
            throw new \Exception('Break start time must be after overall start time');
        }
        if ($breakEnd->gte($overallEnd)) {
            throw new \Exception('Break end time must be before overall end time');
        }
        if ($breakStart->gte($overallEnd) || $breakEnd->lte($overallStart)) {
            throw new \Exception('Break time must be within overall time range (between start and end time)');
        }

        // Calculate time before break and after break
        $timeBeforeBreak = $overallStart->diffInMinutes($breakStart, false);
        $timeAfterBreak = $breakEnd->diffInMinutes($overallEnd, false);

        // Ensure positive values
        if ($timeBeforeBreak < 0) {
            $timeBeforeBreak = 0;
        }
        if ($timeAfterBreak < 0) {
            $timeAfterBreak = 0;
        }

        // Calculate how many exams can fit per day
        $examDurationMinutes = $examDuration * 60;
        if ($examDurationMinutes <= 0) {
            throw new \Exception('Exam duration must be greater than 0');
        }

        $examsBeforeBreak = floor($timeBeforeBreak / $examDurationMinutes);
        $examsAfterBreak = floor($timeAfterBreak / $examDurationMinutes);
        $examsPerDay = $examsBeforeBreak + $examsAfterBreak;

        if ($examsPerDay <= 0) {
            throw new \Exception('No time slots available. Please check your time settings (start time, end time, break time, and exam duration).');
        }

        // Generate dates from exam start to end (excluding weekends if needed)
        $currentDate = \Carbon\Carbon::parse($exam->start_date);
        $endDate = \Carbon\Carbon::parse($exam->end_date);
        $dates = [];

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 6 = Saturday

            if ($skipWeekends && ($dayOfWeek == 0 || $dayOfWeek == 6)) {
                $currentDate->addDay();
                continue;
            }

            $dates[] = $currentDate->copy();
            $currentDate->addDay();
        }

        // Create time slots for each day first
        $timeSlots = [];

        // Before break slots
        $currentTime = $overallStart->copy();
        for ($i = 0; $i < $examsBeforeBreak; $i++) {
            $examStart = $currentTime->copy();
            $examEnd = $currentTime->copy()->addMinutes($examDurationMinutes);

            // Ensure exam ends before break starts
            if ($examEnd->gt($breakStart)) {
                break; // Stop if would overlap with break
            }

            $timeSlots[] = [
                'start' => $examStart->copy(),
                'end' => $examEnd->copy()
            ];
            $currentTime->addMinutes($examDurationMinutes);
        }

        // After break slots
        $currentTime = $breakEnd->copy();
        for ($i = 0; $i < $examsAfterBreak; $i++) {
            $examStart = $currentTime->copy();
            $examEnd = $currentTime->copy()->addMinutes($examDurationMinutes);

            // Ensure exam ends before overall end time
            if ($examEnd->gt($overallEnd)) {
                break; // Stop if would exceed overall end time
            }

            $timeSlots[] = [
                'start' => $examStart->copy(),
                'end' => $examEnd->copy()
            ];
            $currentTime->addMinutes($examDurationMinutes);
        }

        // Recalculate actual exams per day based on created slots
        $examsPerDay = count($timeSlots);

        if ($examsPerDay <= 0) {
            throw new \Exception('No valid time slots could be created. Please check your time settings (start time, end time, break time, and exam duration).');
        }

        // Apply maximum exams per day limit if specified
        if ($maxExamsPerDay !== null && $maxExamsPerDay > 0 && $maxExamsPerDay < $examsPerDay) {
            $timeSlots = array_slice($timeSlots, 0, $maxExamsPerDay);
            $examsPerDay = count($timeSlots);
        }

        // Distribute subjects across dates - each class needs all school subjects
        // Note: We use school subjects (common to all classes), NOT class subjects
        $totalSubjects = $subjects->count();

        // Calculate minimum days needed based on subjects per day
        $minDaysNeeded = ceil($totalSubjects / $subjectsPerDay);

        // Calculate total slots needed: each class needs all subjects, but distributed across days
        $totalSlotsNeeded = $subclasses->count() * $totalSubjects;

        // Calculate available slots: exams per day × number of days
        $totalAvailableSlots = count($dates) * $examsPerDay;

        // Check if we have enough days for all subjects
        if ($minDaysNeeded > count($dates)) {
            $suggestions = [];
            $suggestions[] = "Extend exam period to at least {$minDaysNeeded} days (currently " . count($dates) . " days) to accommodate {$totalSubjects} subjects at {$subjectsPerDay} subjects/day";

            if ($subjectsPerDay < $totalSubjects) {
                $suggestedSubjectsPerDay = ceil($totalSubjects / count($dates));
                $suggestions[] = "Or increase 'Subjects Per Day' to at least {$suggestedSubjectsPerDay} (currently {$subjectsPerDay})";
            }

            $suggestionText = "\n\nSuggestions:\n- " . implode("\n- ", $suggestions);
            throw new \Exception("Not enough days. Need at least {$minDaysNeeded} days to schedule {$totalSubjects} subjects at {$subjectsPerDay} subjects/day per class, but only " . count($dates) . " days available.{$suggestionText}");
        }

        // Check if we have enough time slots
        if ($totalSlotsNeeded > $totalAvailableSlots) {
            // Calculate suggestions
            $suggestedDays = ceil($totalSlotsNeeded / $examsPerDay);
            $suggestedDuration = ($overallStart->diffInMinutes($overallEnd, false) - $breakStart->diffInMinutes($breakEnd, false)) / ceil($totalSlotsNeeded / count($dates));
            $suggestedDurationHours = round($suggestedDuration / 60, 1);

            $suggestions = [];
            if ($suggestedDays > count($dates)) {
                $suggestions[] = "Extend exam period to at least {$suggestedDays} days (currently " . count($dates) . " days)";
            }
            if ($suggestedDurationHours < $examDuration) {
                $suggestions[] = "Reduce exam duration to approximately {$suggestedDurationHours} hours (currently {$examDuration} hours)";
            }
            if ($maxExamsPerDay !== null && $maxExamsPerDay < $examsPerDay) {
                $suggestions[] = "Remove or increase 'Maximum Exams Per Day' limit (currently {$maxExamsPerDay}, available: {$examsPerDay})";
            }
            if ($subjectsPerDay < $totalSubjects && $subjectsPerDay < 5) {
                $suggestedSubjectsPerDay = min(ceil($totalSubjects / count($dates)), 10);
                $suggestions[] = "Increase 'Subjects Per Day' to allow more subjects per day (currently {$subjectsPerDay}, suggested: {$suggestedSubjectsPerDay})";
            }

            $suggestionText = !empty($suggestions) ? "\n\nSuggestions:\n- " . implode("\n- ", $suggestions) : "";

            throw new \Exception("Not enough time slots. Need {$totalSlotsNeeded} slots (for {$subclasses->count()} classes × {$totalSubjects} subjects) but only {$totalAvailableSlots} available ({$examsPerDay} exams/day × " . count($dates) . " days).{$suggestionText}");
        }

        // Assign subjects to time slots for each class
        // Distribute subjects across days based on subjects_per_day
        foreach ($subclasses as $subclass) {
            $subjectIndex = 0;
            $dateIndex = 0;

            // Process each date, scheduling up to subjects_per_day subjects per class per day
            foreach ($dates as $date) {
                $subjectsScheduledToday = 0;

                // Schedule up to subjects_per_day subjects for this class on this date
                foreach ($timeSlots as $slot) {
                    if ($subjectIndex >= $totalSubjects) {
                        break 2; // All subjects scheduled for this class
                    }

                    if ($subjectsScheduledToday >= $subjectsPerDay) {
                        break; // Move to next day
                    }

                    // Get school subject (NOT class subject)
                    // $subjects array contains SchoolSubject objects, not ClassSubject objects
                    $subject = $subjects[$subjectIndex];

                    // Check if this school subject already scheduled for this class in this exam
                    // Note: We check by subjectID (school subject), NOT class_subjectID
                    $existing = ExamTimetable::where('examID', $exam->examID)
                        ->where('subclassID', $subclass->subclassID)
                        ->where('subjectID', $subject->subjectID) // Using subjectID (school subject), NOT class_subjectID
                        ->exists();

                    if ($existing) {
                        $subjectIndex++;
                        continue;
                    }

                    // Auto-assign teacher
                    $teacher = null;
                    if ($autoAssignTeachers) {
                        // Find available teacher (not assigned at this time)
                        $assignedTeachers = ExamTimetable::where('exam_date', $date->format('Y-m-d'))
                            ->where(function($q) use ($slot) {
                                $q->where(function($subQ) use ($slot) {
                                    $subQ->where('start_time', '<=', $slot['start']->format('H:i'))
                                         ->where('end_time', '>', $slot['start']->format('H:i'));
                                })->orWhere(function($subQ) use ($slot) {
                                    $subQ->where('start_time', '<', $slot['end']->format('H:i'))
                                         ->where('end_time', '>=', $slot['end']->format('H:i'));
                                });
                            })
                            ->pluck('teacherID')
                            ->toArray();

                        $teacher = $teachers->whereNotIn('id', $assignedTeachers)->first();

                        if (!$teacher) {
                            $teacher = $teachers->first();
                        }
                    } else {
                        $teacher = $teachers->first();
                    }

                    if (!$teacher) {
                        $errors[] = "No teacher available for {$subclass->subclass_name} - {$subject->subject_name} on {$date->format('Y-m-d')}";
                        $subjectIndex++;
                        continue;
                    }

                    $examTimetable = new ExamTimetable();
                    $examTimetable->schoolID = $schoolID;
                    $examTimetable->examID = $exam->examID;
                    $examTimetable->subclassID = $subclass->subclassID;
                    $examTimetable->teacherID = $teacher->id;
                    $examTimetable->exam_date = $date->format('Y-m-d');
                    $examTimetable->start_time = $slot['start']->format('H:i');
                    $examTimetable->end_time = $slot['end']->format('H:i');
                    $examTimetable->timetable_type = 'school_wide';
                    $examTimetable->class_subjectID = null; // School-wide uses school subjects, not class subjects
                    $examTimetable->subjectID = $subject->subjectID; // School subject ID (from SchoolSubject table)
                    $examTimetable->notes = $notes;
                    $examTimetable->save();

                    $createdEntries[] = $examTimetable->load(['examination', 'subclass', 'teacher', 'subject']);
                    $subjectIndex++;
                    $subjectsScheduledToday++;
                }

                // Move to next date (subjects_per_day limit is already enforced by the break statement above)
            }
        }

        return $createdEntries;
    }

    /**
     * Create manual school-wide timetable using daily pattern
     */
    private function createManualSchoolWideTimetable($schoolID, $exam, $dailyPattern, $autoAssignTeachers, $notes)
    {
        $createdEntries = [];
        $errors = [];

        // Get all subclasses for this school
        $subclasses = Subclass::whereHas('class', function($q) use ($schoolID) {
            $q->where('schoolID', $schoolID);
        })->with('class')->get();

        // Get all active school subjects
        $subjects = SchoolSubject::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get();

        if ($subjects->isEmpty()) {
            throw new \Exception('No active school subjects found for this school. Please ensure school subjects are created and active.');
        }

        // Get all teachers for auto-assignment
        $teachers = Teacher::where('schoolID', $schoolID)->get();
        if ($teachers->isEmpty() && $autoAssignTeachers) {
            throw new \Exception('No teachers found for auto-assignment');
        }

        // Generate dates from exam start to end (excluding weekends)
        $currentDate = \Carbon\Carbon::parse($exam->start_date);
        $endDate = \Carbon\Carbon::parse($exam->end_date);
        $dates = [];

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 6 = Saturday

            // Skip weekends
            if ($dayOfWeek != 0 && $dayOfWeek != 6) {
                $dates[] = $currentDate->copy();
            }
            $currentDate->addDay();
        }

        if (empty($dates)) {
            throw new \Exception('No valid weekdays found in exam period. Please ensure exam period includes at least one weekday.');
        }

        // Process each date and apply daily pattern
        foreach ($dates as $date) {
            // Track teacher assignments for this date to avoid duplicates in same transaction
            $dateTeacherAssignments = [];

            // Process each item in daily pattern
            foreach ($dailyPattern as $patternItem) {
                if ($patternItem['type'] === 'break') {
                    // Skip breaks - they are just time markers
                    continue;
                }

                // This is an exam slot
                $startTime = $patternItem['start_time'];
                $endTime = $patternItem['end_time'];
                $subjectID = $patternItem['subjectID'] ?? null;

                // Determine which subjects to schedule
                $subjectsToSchedule = $subjectID
                    ? $subjects->where('subjectID', $subjectID)
                    : $subjects;

                if ($subjectsToSchedule->isEmpty()) {
                    continue;
                }

                // Schedule this time slot for all classes and all subjects (or specific subject)
                foreach ($subclasses as $subclass) {
                    foreach ($subjectsToSchedule as $subject) {
                        // Check if already scheduled
                        $existing = ExamTimetable::where('examID', $exam->examID)
                            ->where('subclassID', $subclass->subclassID)
                            ->where('subjectID', $subject->subjectID)
                            ->where('exam_date', $date->format('Y-m-d'))
                            ->where('start_time', $startTime)
                            ->where('end_time', $endTime)
                            ->exists();

                        if ($existing) {
                            continue;
                        }

                        // Create a key for this time slot to track assignments
                        $timeSlotKey = $date->format('Y-m-d') . '_' . $startTime . '_' . $endTime;

                        // Auto-assign teacher
                        $teacher = null;
                        if ($autoAssignTeachers) {
                            // Get teachers already assigned at this exact time slot (from database)
                            $dbAssignedTeachers = ExamTimetable::where('exam_date', $date->format('Y-m-d'))
                                ->where('start_time', $startTime)
                                ->where('end_time', $endTime)
                                ->pluck('teacherID')
                                ->toArray();

                            // Get teachers assigned in current transaction for this time slot
                            $transactionAssignedTeachers = $dateTeacherAssignments[$timeSlotKey] ?? [];

                            // Also check for overlapping time slots in database
                            $overlappingTeachers = ExamTimetable::where('exam_date', $date->format('Y-m-d'))
                                ->where(function($q) use ($startTime, $endTime) {
                                    $q->where(function($subQ) use ($startTime, $endTime) {
                                        $subQ->where('start_time', '<=', $startTime)
                                             ->where('end_time', '>', $startTime);
                                    })->orWhere(function($subQ) use ($startTime, $endTime) {
                                        $subQ->where('start_time', '<', $endTime)
                                             ->where('end_time', '>=', $endTime);
                                    })->orWhere(function($subQ) use ($startTime, $endTime) {
                                        $subQ->where('start_time', '>=', $startTime)
                                             ->where('end_time', '<=', $endTime);
                                    });
                                })
                                ->pluck('teacherID')
                                ->toArray();

                            // Combine all assigned teachers (database + current transaction)
                            $allAssignedTeachers = array_unique(array_merge($dbAssignedTeachers, $transactionAssignedTeachers, $overlappingTeachers));

                            // Find a teacher not in the assigned list
                            $teacher = $teachers->whereNotIn('id', $allAssignedTeachers)->first();

                            // If no teacher available, try to find one with least assignments
                            if (!$teacher && $teachers->isNotEmpty()) {
                                // Count assignments per teacher for this date (from database)
                                $teacherAssignments = ExamTimetable::where('exam_date', $date->format('Y-m-d'))
                                    ->whereIn('teacherID', $teachers->pluck('id')->toArray())
                                    ->selectRaw('teacherID, COUNT(*) as assignment_count')
                                    ->groupBy('teacherID')
                                    ->pluck('assignment_count', 'teacherID')
                                    ->toArray();

                                // Find teacher with least assignments (excluding already assigned ones)
                                $leastAssignedTeacherId = null;
                                $minAssignments = PHP_INT_MAX;
                                foreach ($teachers as $t) {
                                    if (in_array($t->id, $allAssignedTeachers)) {
                                        continue; // Skip already assigned teachers
                                    }
                                    $assignments = $teacherAssignments[$t->id] ?? 0;
                                    if ($assignments < $minAssignments) {
                                        $minAssignments = $assignments;
                                        $leastAssignedTeacherId = $t->id;
                                    }
                                }

                                if ($leastAssignedTeacherId) {
                                    $teacher = $teachers->where('id', $leastAssignedTeacherId)->first();
                                }
                            }
                        } else {
                            $teacher = $teachers->first();
                        }

                        if (!$teacher) {
                            $errors[] = "No teacher available for {$subclass->subclass_name} - {$subject->subject_name} on {$date->format('Y-m-d')} at {$startTime}-{$endTime}";
                            continue;
                        }

                        // Track this teacher assignment for this time slot
                        if (!isset($dateTeacherAssignments[$timeSlotKey])) {
                            $dateTeacherAssignments[$timeSlotKey] = [];
                        }
                        $dateTeacherAssignments[$timeSlotKey][] = $teacher->id;

                        $examTimetable = new ExamTimetable();
                        $examTimetable->schoolID = $schoolID;
                        $examTimetable->examID = $exam->examID;
                        $examTimetable->subclassID = $subclass->subclassID;
                        $examTimetable->teacherID = $teacher->id;
                        $examTimetable->exam_date = $date->format('Y-m-d');
                        $examTimetable->start_time = $startTime;
                        $examTimetable->end_time = $endTime;
                        $examTimetable->timetable_type = 'school_wide';
                        $examTimetable->class_subjectID = null; // School-wide uses school subjects, not class subjects
                        $examTimetable->subjectID = $subject->subjectID; // School subject ID (from SchoolSubject table)
                        $examTimetable->notes = $notes;
                        $examTimetable->save();

                        $createdEntries[] = $examTimetable->load(['examination', 'subclass.class', 'teacher', 'subject']);
                    }
                }
            }
        }

        if (!empty($errors)) {
            \Log::warning('Errors creating manual school-wide timetable: ' . implode('; ', $errors));
        }

        return $createdEntries;
    }

    /**
     * Create new school-wide timetable format
     */
    /**
     * Generate automatic timetable based on settings (INTELLIGENT)
     */
    private function generateAutomaticTimetable($schoolID, $exam, $request)
    {
        // Get settings
        $startDate = \Carbon\Carbon::parse($request->exam_start_date);
        $endDate = \Carbon\Carbon::parse($request->exam_end_date);
        $examDuration = (int) $request->exam_duration; // minutes
        $dailyStartTime = $request->daily_start_time;
        $dailyEndTime = $request->daily_end_time;
        $maxExamsPerDay = (int) $request->max_exams_per_day;
        $breakDuration = (int) ($request->break_duration ?? 15); // minutes between exams

        // Get all subjects
        $subjects = $this->getExamSubjects($exam, $schoolID);
        $subjectCount = $subjects->count();

        \Log::info("Automatic Timetable: {$subjectCount} subjects to schedule");

        if ($subjects->isEmpty()) {
            throw new \Exception('No subjects found. Please add subjects to your school first.');
        }

        // Calculate available weekdays in date range
        $availableDays = 0;
        $tempDate = $startDate->copy();
        while ($tempDate->lte($endDate)) {
            if (!$tempDate->isWeekend()) {
                $availableDays++;
            }
            $tempDate->addDay();
        }

        // Calculate daily available time for exams
        // Parse times with today's date to ensure proper calculation
        $today = \Carbon\Carbon::today();
        $dailyStartCarbon = \Carbon\Carbon::parse($today->toDateString() . ' ' . $dailyStartTime);
        $dailyEndCarbon = \Carbon\Carbon::parse($today->toDateString() . ' ' . $dailyEndTime);
        
        // Use diffInMinutes with signed parameter to get correct positive value
        $dailyMinutes = $dailyStartCarbon->diffInMinutes($dailyEndCarbon, false);
        
        \Log::info("Daily time window: {$dailyStartTime} to {$dailyEndTime} = {$dailyMinutes} minutes (Start: {$dailyStartCarbon}, End: {$dailyEndCarbon})");

        // Calculate how many exams can fit per day (with breaks)
        $timePerExamWithBreak = $examDuration + $breakDuration;
        $maxPossibleExamsPerDay = floor($dailyMinutes / $timePerExamWithBreak);
        
        // Use the smaller of user's max or calculated max
        $effectiveMaxPerDay = min($maxExamsPerDay, $maxPossibleExamsPerDay);

        // Calculate total capacity
        $totalCapacity = $availableDays * $effectiveMaxPerDay;

        \Log::info("Timetable Calculation: Daily minutes={$dailyMinutes}, Exam duration={$examDuration}, Break={$breakDuration}, Time per exam+break={$timePerExamWithBreak}, Max possible/day={$maxPossibleExamsPerDay}, User max/day={$maxExamsPerDay}, Effective max/day={$effectiveMaxPerDay}, Available days={$availableDays}, Total capacity={$totalCapacity}, Subjects needed={$subjectCount}");

        // Validate if all subjects can fit
        if ($totalCapacity < $subjectCount) {
            throw new \Exception(
                "Cannot fit all {$subjectCount} subjects in the date range. " .
                "Available slots: {$totalCapacity} ({$availableDays} days × {$effectiveMaxPerDay} exams/day). " .
                "Please extend the date range or increase daily time window."
            );
        }

        // Generate timetable - distribute subjects evenly
        $days = [];
        $currentDate = $startDate->copy();
        $subjectIndex = 0;

        while ($currentDate->lte($endDate) && $subjectIndex < $subjectCount) {
            // Skip weekends
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }

            $daySubjects = [];
            // Parse current time with proper date context for this specific date
            $currentTime = \Carbon\Carbon::parse($currentDate->toDateString() . ' ' . $dailyStartTime);
            $dailyEndForThisDate = \Carbon\Carbon::parse($currentDate->toDateString() . ' ' . $dailyEndTime);
            $examsScheduledToday = 0;

            // Schedule up to max exams for this day
            while ($examsScheduledToday < $maxExamsPerDay && $subjectIndex < $subjectCount) {
                $endTime = $currentTime->copy()->addMinutes($examDuration);

                // Check if exam + potential break fits before daily end time
                $timeNeeded = $examDuration;
                if ($examsScheduledToday < $maxExamsPerDay - 1 && $subjectIndex < $subjectCount - 1) {
                    // Not the last exam of day or last subject overall - add break
                    $timeNeeded += $breakDuration;
                }

                $projectedEnd = $currentTime->copy()->addMinutes($timeNeeded);
                if ($projectedEnd->gt($dailyEndForThisDate)) {
                    // Can't fit more exams today
                    break;
                }

                // Schedule this subject
                $daySubjects[] = [
                    'subjectID' => $subjects[$subjectIndex]->subjectID,
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                ];

                $subjectIndex++;
                $examsScheduledToday++;

                // Move time forward (exam duration + break)
                $currentTime = $endTime->copy();
                
                // Add break if not the last exam of the day and not the last subject
                if ($examsScheduledToday < $maxExamsPerDay && $subjectIndex < $subjectCount) {
                    $currentTime->addMinutes($breakDuration);
                }
            }

            // Save this day if it has subjects
            if (!empty($daySubjects)) {
                $days[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day' => $currentDate->format('l'),
                    'subjects' => $daySubjects,
                ];
            }

            $currentDate->addDay();
        }

        \Log::info("Successfully scheduled {$subjectIndex} subjects across " . count($days) . " days");

        if ($subjectIndex < $subjectCount) {
            $remaining = $subjectCount - $subjectIndex;
            throw new \Exception(
                "Only {$subjectIndex} out of {$subjectCount} subjects could be scheduled. " .
                "{$remaining} subjects remaining. Please adjust your settings."
            );
        }

        if (empty($days)) {
            throw new \Exception('No timetable could be generated. Please check your date range and settings.');
        }

        // Create timetable entries with supervisor assignment
        return $this->createNewSchoolWideTimetable($schoolID, $exam, $days, $request);
    }

    /**
     * Get subjects for exam based on exam category
     */
    private function getExamSubjects($exam, $schoolID)
    {
        // Get all active school subjects
        $subjects = SchoolSubject::where('schoolID', $schoolID)
            ->orderBy('subject_name')
            ->get();

        \Log::info("Found " . $subjects->count() . " subjects for automatic timetable generation");

        if ($subjects->isEmpty()) {
            \Log::error("No subjects found for school {$schoolID}");
        }

        return $subjects;
    }

    /**
     * Get class IDs participating in exam
     */
    private function getExamClassIDs($exam)
    {
        // Get all classes
        $allClasses = ClassModel::where('schoolID', $exam->schoolID)
            ->pluck('classID')
            ->toArray();

        // Exclude classes if specified
        $excludedClasses = $exam->except_class_ids ?? [];

        if (is_string($excludedClasses)) {
            $excludedClasses = json_decode($excludedClasses, true) ?? [];
        }

        return array_diff($allClasses, $excludedClasses);
    }

    private function createNewSchoolWideTimetable($schoolID, $exam, $days, $request = null)
    {
        $createdEntries = [];
        $errors = [];

        // Get all subclasses for auto-assigning teachers
        $subclasses = Subclass::whereHas('class', function($q) use ($schoolID) {
            $q->where('schoolID', $schoolID);
        })->with('class')->get();

        // Get all teachers for auto-assignment
        $teachers = Teacher::where('schoolID', $schoolID)->get();
        if ($teachers->isEmpty()) {
            throw new \Exception('No teachers found for auto-assignment');
        }

        // Process each day
        foreach ($days as $dayData) {
            $examDate = $dayData['date'];
            $dayName = $dayData['day'];
            $subjects = $dayData['subjects'] ?? [];

            // Validate exam date is within exam period
            $examDateObj = \Carbon\Carbon::parse($examDate);
            $examStartDate = \Carbon\Carbon::parse($exam->start_date);
            $examEndDate = \Carbon\Carbon::parse($exam->end_date);

            if ($examDateObj->lt($examStartDate) || $examDateObj->gt($examEndDate)) {
                $errors[] = "Date {$examDate} is outside exam period ({$exam->start_date} to {$exam->end_date})";
                continue;
            }

            // Process each subject for this day
            // Track processed subjects to avoid duplicates within the same request
            $processedSubjects = [];
            
            foreach ($subjects as $subjectData) {
                $subjectID = $subjectData['subjectID'];
                $startTime = $subjectData['start_time'];
                $endTime = $subjectData['end_time'];

                // Create unique key for this subject/time combination
                $subjectKey = "{$exam->examID}_{$examDate}_{$subjectID}_{$startTime}_{$endTime}";
                
                // Skip if we've already processed this subject/time in this request
                if (isset($processedSubjects[$subjectKey])) {
                    continue;
                }
                $processedSubjects[$subjectKey] = true;

                // Check if this timetable entry already exists in database
                // Use firstOrCreate to avoid race conditions
                $examTimetable = ExamTimetableSchoolWide::firstOrCreate(
                    [
                        'schoolID' => $schoolID,
                        'examID' => $exam->examID,
                        'exam_date' => $examDate,
                        'subjectID' => $subjectID,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ],
                    [
                        'day' => $dayName,
                    ]
                );

                // Auto-assign supervise teachers to all classes randomly
                // Shuffle teachers array for random assignment
                $shuffledTeachers = $teachers->shuffle();
                $teacherIndex = 0;
                
                // Track which teachers have been assigned for this subject/time slot
                $assignedTeachersForSlot = [];
                
                foreach ($subclasses as $subclass) {
                    // Get timetable IDs for this date and time (including the one we just created)
                    $timetableIDs = ExamTimetableSchoolWide::where('examID', $exam->examID)
                        ->where('exam_date', $examDate)
                        ->where('start_time', $startTime)
                        ->where('end_time', $endTime)
                        ->pluck('exam_timetableID')
                        ->toArray();

                    // Include the current timetable ID
                    if (!in_array($examTimetable->exam_timetableID, $timetableIDs)) {
                        $timetableIDs[] = $examTimetable->exam_timetableID;
                    }

                    $assignedTeachers = ExamSuperviseTeacher::where('examID', $exam->examID)
                        ->whereIn('exam_timetableID', $timetableIDs)
                        ->where('subclassID', $subclass->subclassID)
                        ->pluck('teacherID')
                        ->toArray();

                    // Also check for overlapping time slots
                    $overlappingTimetableIDs = ExamTimetableSchoolWide::where('examID', $exam->examID)
                        ->where('exam_date', $examDate)
                        ->where(function($timeQ) use ($startTime, $endTime) {
                            $timeQ->where(function($subQ) use ($startTime, $endTime) {
                                $subQ->where('start_time', '<=', $startTime)
                                     ->where('end_time', '>', $startTime);
                            })->orWhere(function($subQ) use ($startTime, $endTime) {
                                $subQ->where('start_time', '<', $endTime)
                                     ->where('end_time', '>=', $endTime);
                            })->orWhere(function($subQ) use ($startTime, $endTime) {
                                $subQ->where('start_time', '>=', $startTime)
                                     ->where('end_time', '<=', $endTime);
                            });
                        })
                        ->pluck('exam_timetableID')
                        ->toArray();

                    $overlappingTeachers = ExamSuperviseTeacher::where('examID', $exam->examID)
                        ->whereIn('exam_timetableID', $overlappingTimetableIDs)
                        ->where('subclassID', $subclass->subclassID)
                        ->pluck('teacherID')
                        ->toArray();

                    $allAssignedTeachers = array_unique(array_merge($assignedTeachers, $overlappingTeachers, $assignedTeachersForSlot));

                    // Find a teacher not in the assigned list - try to use all teachers
                    $teacher = null;
                    
                    // First, try to find a teacher not assigned at this time slot
                    foreach ($shuffledTeachers as $t) {
                        if (!in_array($t->id, $allAssignedTeachers)) {
                            $teacher = $t;
                            break;
                        }
                    }
                    
                    // If all teachers are assigned at this time, find one with least assignments
                    if (!$teacher && $shuffledTeachers->isNotEmpty()) {
                        // Count assignments per teacher for this exam (from database)
                        // Get timetable IDs for this date to count assignments
                        $dateTimetableIDs = ExamTimetableSchoolWide::where('examID', $exam->examID)
                            ->where('exam_date', $examDate)
                            ->pluck('exam_timetableID')
                            ->toArray();
                        
                        $teacherAssignments = ExamSuperviseTeacher::where('examID', $exam->examID)
                            ->whereIn('exam_timetableID', $dateTimetableIDs)
                            ->whereIn('teacherID', $shuffledTeachers->pluck('id')->toArray())
                            ->selectRaw('teacherID, COUNT(*) as assignment_count')
                            ->groupBy('teacherID')
                            ->pluck('assignment_count', 'teacherID')
                            ->toArray();

                        // Find teacher with least assignments
                        $leastAssignedTeacherId = null;
                        $minAssignments = PHP_INT_MAX;
                        foreach ($shuffledTeachers as $t) {
                            $assignments = $teacherAssignments[$t->id] ?? 0;
                            if ($assignments < $minAssignments) {
                                $minAssignments = $assignments;
                                $leastAssignedTeacherId = $t->id;
                            }
                        }

                        if ($leastAssignedTeacherId) {
                            $teacher = $shuffledTeachers->where('id', $leastAssignedTeacherId)->first();
                        } else {
                            // Fallback to first teacher
                            $teacher = $shuffledTeachers->first();
                        }
                    }

                    if ($teacher) {
                        // Create supervise teacher entry
                        $superviseTeacher = new ExamSuperviseTeacher();
                        $superviseTeacher->schoolID = $schoolID;
                        $superviseTeacher->examID = $exam->examID;
                        $superviseTeacher->exam_timetableID = $examTimetable->exam_timetableID;
                        $superviseTeacher->subclassID = $subclass->subclassID;
                        $superviseTeacher->teacherID = $teacher->id;
                        $superviseTeacher->save();
                        
                        // Track this assignment
                        $assignedTeachersForSlot[] = $teacher->id;
                    } else {
                        $errors[] = "No teacher available for {$subclass->subclass_name} on {$examDate} at {$startTime}-{$endTime}";
                    }
                }
                
                // Ensure all teachers are assigned at least once across all subjects/times
                // Get all teachers that haven't been assigned yet for this exam
                $allAssignedTeacherIDs = ExamSuperviseTeacher::where('examID', $exam->examID)
                    ->distinct()
                    ->pluck('teacherID')
                    ->toArray();
                
                $unassignedTeachers = $teachers->whereNotIn('id', $allAssignedTeacherIDs);
                
                // If there are unassigned teachers, assign them to random classes for this subject
                if ($unassignedTeachers->isNotEmpty() && $subclasses->isNotEmpty()) {
                    $randomSubclasses = $subclasses->shuffle();
                    foreach ($unassignedTeachers as $unassignedTeacher) {
                        // Find a class that doesn't have this teacher assigned for this timetable entry
                        $assignedSubclasses = ExamSuperviseTeacher::where('examID', $exam->examID)
                            ->where('exam_timetableID', $examTimetable->exam_timetableID)
                            ->where('teacherID', $unassignedTeacher->id)
                            ->pluck('subclassID')
                            ->toArray();
                        
                        $availableSubclass = $randomSubclasses->whereNotIn('subclassID', $assignedSubclasses)->first();
                        
                        if (!$availableSubclass) {
                            // If all subclasses have this teacher, assign to first available
                            $availableSubclass = $randomSubclasses->first();
                        }
                        
                        if ($availableSubclass) {
                            // Check if this subclass already has a teacher for this timetable
                            $existingSupervisor = ExamSuperviseTeacher::where('examID', $exam->examID)
                                ->where('exam_timetableID', $examTimetable->exam_timetableID)
                                ->where('subclassID', $availableSubclass->subclassID)
                                ->first();
                            
                            if (!$existingSupervisor) {
                                // Create supervise teacher entry
                                $superviseTeacher = new ExamSuperviseTeacher();
                                $superviseTeacher->schoolID = $schoolID;
                                $superviseTeacher->examID = $exam->examID;
                                $superviseTeacher->exam_timetableID = $examTimetable->exam_timetableID;
                                $superviseTeacher->subclassID = $availableSubclass->subclassID;
                                $superviseTeacher->teacherID = $unassignedTeacher->id;
                                $superviseTeacher->save();
                            }
                        }
                    }
                }

                // Auto-assign hall supervisors if method is automatic
                $supervisorMethod = $request ? $request->input('supervisor_assignment_method', 'automatic') : 'automatic';
                if ($supervisorMethod === 'automatic') {
                    $this->autoAssignHallSupervisors(
                        $exam->examID,
                        $subjectID,
                        $examTimetable->exam_timetableID,
                        $schoolID
                    );
                }

                $createdEntries[] = $examTimetable->load(['examination', 'subject']);
            }
        }

        // Send batch SMS to all assigned teachers (only if automatic assignment)
        $supervisorMethod = $request ? $request->input('supervisor_assignment_method', 'automatic') : 'automatic';
        if ($supervisorMethod === 'automatic') {
            $this->sendBatchSupervisorSMS();
        }

        if (!empty($errors)) {
            \Log::warning('Errors creating school-wide timetable: ' . implode('; ', $errors));
        }

        return $createdEntries;
    }

    /**
     * Get exam details by ID
     */
    public function getExamDetails(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $examID = $request->input('examID');

        if (!$examID) {
            return response()->json(['error' => 'Exam ID is required'], 400);
        }

        $exam = Examination::where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$exam) {
            return response()->json(['error' => 'Examination not found'], 404);
        }

        return response()->json([
            'success' => true,
            'exam' => [
                'examID' => $exam->examID,
                'exam_name' => $exam->exam_name,
                'start_date' => $exam->start_date ? $exam->start_date->format('Y-m-d') : null,
                'end_date' => $exam->end_date ? $exam->end_date->format('Y-m-d') : null,
            ]
        ]);
    }

    /**
     * Get exam supervisors for a timetable entry or for all subjects on a specific date
     */
    public function getExamSupervisors(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $examTimetableID = $request->input('exam_timetableID');
        $date = $request->input('date');
        $examID = $request->input('examID');

        // If date and examID are provided, get supervisors for all subjects on that date
        if ($date && $examID) {
            // Get all timetable entries for this date and exam
            $timetableEntries = ExamTimetableSchoolWide::where('schoolID', $schoolID)
                ->where('examID', $examID)
                ->where('exam_date', $date)
                ->with('subject')
                ->orderBy('start_time')
                ->get();

            if ($timetableEntries->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'supervisors' => [],
                    'all_teachers' => [],
                    'all_subclasses' => [],
                    'date' => $date
                ]);
            }

            // Build supervisors data with hall information
            $supervisors = [];
            
            foreach ($timetableEntries as $timetable) {
                // Get hall supervisors for this timetable/subject
                $hallSupervisors = ExamHallSupervisor::where('exam_timetableID', $timetable->exam_timetableID)
                    ->where('schoolID', $schoolID)
                    ->with(['teacher', 'examHall.class'])
                    ->get();

                if ($hallSupervisors->isNotEmpty()) {
                    $supervisors[] = [
                        'subject_name' => $timetable->subject->subject_name ?? 'N/A',
                        'start_time' => $timetable->start_time,
                        'end_time' => $timetable->end_time,
                        'halls' => $hallSupervisors->groupBy('exam_hallID')->map(function ($supervisors) {
                            $hall = $supervisors->first()->examHall;
                            return [
                                'hall_name' => $hall->hall_name,
                                'class_name' => $hall->class->class_name ?? 'N/A',
                                'capacity' => $hall->capacity,
                                'gender_allowed' => $hall->gender_allowed,
                                'supervisors' => $supervisors->map(function ($s) {
                                    return [
                                        'teacher_name' => $s->teacher->name ?? 'N/A',
                                        'teacher_phone' => $s->teacher->phone ?? 'N/A',
                                    ];
                                })->values(),
                            ];
                        })->values(),
                    ];
                }
            }
            
            $supervisors = collect($supervisors);

        } else {
            // Original behavior: get supervisors for a specific timetable entry
        if (!$examTimetableID) {
                return response()->json(['error' => 'Timetable ID or Date and Exam ID are required'], 400);
            }

            // Get timetable entry to get subject and time information (for general info)
            $timetable = ExamTimetableSchoolWide::where('exam_timetableID', $examTimetableID)
                ->where('schoolID', $schoolID)
                ->with('subject')
                ->first();
            
            $subjectName = $timetable && $timetable->subject ? $timetable->subject->subject_name : 'N/A';
            $startTime = $timetable ? $timetable->start_time : 'N/A';
            $endTime = $timetable ? $timetable->end_time : 'N/A';
            $examDate = $timetable ? $timetable->exam_date : null;

            // Get supervisors for this timetable entry with class and timetable information
            // Load examTimetable relationship to get subject, time, and date for each supervisor
        $supervisors = ExamSuperviseTeacher::where('schoolID', $schoolID)
            ->where('exam_timetableID', $examTimetableID)
                ->with(['teacher', 'subclass', 'examTimetable.subject'])
            ->get()
            ->map(function($st) {
                    // Get subject, time, and date from the examTimetable relationship
                    $timetable = $st->examTimetable;
                    $subjectName = $timetable && $timetable->subject ? $timetable->subject->subject_name : 'N/A';
                    $startTime = $timetable ? $timetable->start_time : 'N/A';
                    $endTime = $timetable ? $timetable->end_time : 'N/A';
                    $examDate = $timetable ? $timetable->exam_date : null;
                    
                return [
                    'exam_supervise_teacherID' => $st->exam_supervise_teacherID,
                    'teacher_name' => $st->teacher ? ($st->teacher->first_name . ' ' . $st->teacher->last_name) : 'N/A',
                    'teacherID' => $st->teacherID,
                    'subclass_name' => $st->subclass ? $st->subclass->subclass_name : 'N/A',
                        'subclassID' => $st->subclassID,
                        'subject_name' => $subjectName,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'exam_date' => $examDate ? $examDate->format('Y-m-d') : null,
                        'exam_timetableID' => $st->exam_timetableID
                ];
            });
        }

        // Get all available teachers for editing
        $allTeachers = Teacher::where('schoolID', $schoolID)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->first_name . ' ' . $teacher->last_name,
                    'employee_number' => $teacher->employee_number
                ];
            });

        // Get all subclasses for this school
        $allSubclasses = Subclass::whereHas('class', function($q) use ($schoolID) {
            $q->where('schoolID', $schoolID);
        })
        ->with('class')
        ->get()
        ->map(function($subclass) {
            return [
                'subclassID' => $subclass->subclassID,
                'subclass_name' => $subclass->subclass_name,
                'class_name' => $subclass->class ? $subclass->class->class_name : 'N/A'
            ];
        });

        // Prepare response data
        $responseData = [
            'success' => true,
            'supervisors' => $supervisors,
            'all_teachers' => $allTeachers,
            'all_subclasses' => $allSubclasses
        ];

        // Add date info if we're getting supervisors for a specific date
        if ($date && $examID) {
            $responseData['date'] = $date;
        } else {
            // Original behavior: add single subject info
            $responseData['subject_name'] = $subjectName ?? 'N/A';
            $responseData['start_time'] = $startTime ?? 'N/A';
            $responseData['end_time'] = $endTime ?? 'N/A';
            $responseData['exam_date'] = $examDate ? $examDate->format('Y-m-d') : null;
        }

        return response()->json($responseData);
    }

    /**
     * Update supervise teacher
     */
    public function updateSuperviseTeacher(Request $request)
    {
        // Check update permission - New format: timetable_update
        if (!$this->hasPermission('timetable_update')) {
            return response()->json([
                'error' => 'You do not have permission to update timetables. You need timetable_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');
        $superviseTeacherID = $request->input('exam_supervise_teacherID');
        $teacherID = $request->input('teacherID');
        $subclassID = $request->input('subclassID');

        if (!$superviseTeacherID || !$teacherID) {
            return response()->json(['error' => 'Supervise teacher ID and teacher ID are required'], 400);
        }

        // Verify the supervise teacher belongs to this school
        $superviseTeacher = ExamSuperviseTeacher::where('exam_supervise_teacherID', $superviseTeacherID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$superviseTeacher) {
            return response()->json(['error' => 'Supervise teacher not found'], 404);
        }

        // Verify the teacher belongs to this school
        $teacher = Teacher::where('id', $teacherID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found or not from your school'], 400);
        }

        // If subclassID is provided, verify it belongs to this school
        if ($subclassID) {
            $subclass = Subclass::where('subclassID', $subclassID)
                ->whereHas('class', function($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                })
                ->first();

            if (!$subclass) {
                return response()->json(['error' => 'Class not found or not from your school'], 400);
            }
        }

        // Check for conflicts (same teacher, same time, same or different class)
        $timetable = ExamTimetableSchoolWide::find($superviseTeacher->exam_timetableID);
        if ($timetable) {
            $conflictQuery = ExamSuperviseTeacher::where('examID', $superviseTeacher->examID)
                ->where('exam_timetableID', $superviseTeacher->exam_timetableID)
                ->where('teacherID', $teacherID)
                ->where('exam_supervise_teacherID', '!=', $superviseTeacherID);

            // If updating subclass, check if teacher is already assigned to that subclass
            if ($subclassID) {
                $conflictQuery->where('subclassID', $subclassID);
            }

            if ($conflictQuery->exists()) {
                return response()->json(['error' => 'This teacher is already assigned to this class for this exam'], 400);
            }
        }

        try {
            $superviseTeacher->teacherID = $teacherID;
            if ($subclassID) {
                $superviseTeacher->subclassID = $subclassID;
            }
            $superviseTeacher->save();

            return response()->json([
                'success' => true,
                'message' => 'Supervise teacher updated successfully',
                'supervise_teacher' => $superviseTeacher->load(['teacher', 'subclass.class'])
            ]);
        } catch (\Exception $e) {
            \Log::error("Error updating supervise teacher: " . $e->getMessage());
            return response()->json(['error' => 'Failed to update supervise teacher: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete exam timetable
     */
    public function deleteExamTimetable($examTimetableID)
    {
        // Check delete permission - New format: timetable_delete
        if (!$this->hasPermission('timetable_delete')) {
            return response()->json([
                'error' => 'You do not have permission to delete timetables. You need timetable_delete permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        // Try to delete from school-wide timetable first
        $examTimetable = ExamTimetableSchoolWide::where('exam_timetableID', $examTimetableID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$examTimetable) {
            // Try class-specific timetable
            $examTimetable = ExamTimetable::where('exam_timetableID', $examTimetableID)
                ->where('schoolID', $schoolID)
                ->first();
        }

        if (!$examTimetable) {
            return response()->json(['error' => 'Exam timetable not found'], 404);
        }

        try {
            // If it's a school-wide timetable, also delete supervise teachers and hall supervisors
            if ($examTimetable instanceof ExamTimetableSchoolWide) {
                ExamSuperviseTeacher::where('exam_timetableID', $examTimetableID)->delete();
                ExamHallSupervisor::where('exam_timetableID', $examTimetableID)->delete();
            }

            $examTimetable->delete();
            return response()->json(['success' => true, 'message' => 'Exam timetable deleted successfully']);
        } catch (\Exception $e) {
            \Log::error("Error deleting exam timetable: " . $e->getMessage());
            return response()->json(['error' => 'Failed to delete exam timetable'], 500);
        }
    }

    /**
     * Delete all timetable entries for an exam
     */
    public function deleteAllExamTimetable($examID)
    {
        // Check delete permission - New format: timetable_delete
        if (!$this->hasPermission('timetable_delete')) {
            return response()->json([
                'error' => 'You do not have permission to delete timetables. You need timetable_delete permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        try {
            \DB::beginTransaction();

            // Delete all school-wide timetable entries
            $schoolWideTimetables = ExamTimetableSchoolWide::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->get();

            foreach ($schoolWideTimetables as $timetable) {
                // Delete related supervise teachers and hall supervisors
                ExamSuperviseTeacher::where('exam_timetableID', $timetable->exam_timetableID)->delete();
                ExamHallSupervisor::where('exam_timetableID', $timetable->exam_timetableID)->delete();
            }

            // Delete school-wide timetables
            ExamTimetableSchoolWide::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->delete();

            // Delete class-specific timetables
            ExamTimetable::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->delete();

            \DB::commit();
            
            \Log::info("All timetable entries deleted for exam {$examID}");
            
            return response()->json([
                'success' => true, 
                'message' => 'All timetable entries deleted successfully'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Error deleting all exam timetables: " . $e->getMessage());
            return response()->json(['error' => 'Failed to delete all timetable entries'], 500);
        }
    }

    /**
     * Update exam timetable time
     */
    public function updateExamTimetableTime(Request $request, $examTimetableID)
    {
        // Check update permission - New format: timetable_update
        if (!$this->hasPermission('timetable_update')) {
            return response()->json([
                'error' => 'You do not have permission to update timetables. You need timetable_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Try to find in school-wide timetable first
        $examTimetable = ExamTimetableSchoolWide::where('exam_timetableID', $examTimetableID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$examTimetable) {
            // Try class-specific timetable
            $examTimetable = ExamTimetable::where('exam_timetableID', $examTimetableID)
                ->where('schoolID', $schoolID)
                ->first();
        }

        if (!$examTimetable) {
            return response()->json(['error' => 'Timetable entry not found'], 404);
        }

        try {
            $examTimetable->start_time = $request->start_time;
            $examTimetable->end_time = $request->end_time;
            $examTimetable->save();

            \Log::info("Updated timetable {$examTimetableID} time to {$request->start_time} - {$request->end_time}");

            return response()->json([
                'success' => true,
                'message' => 'Timetable time updated successfully',
                'timetable' => $examTimetable
            ]);
        } catch (\Exception $e) {
            \Log::error("Error updating timetable time: " . $e->getMessage());
            return response()->json(['error' => 'Failed to update timetable time'], 500);
        }
    }

    /**
     * Get all active teachers for supervisor assignment
     */
    public function getSuperviseTeachers()
    {
        $schoolID = Session::get('schoolID');

        // Get ALL active teachers (not just those with supervise_exams permission)
        $teachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'success' => true,
            'teachers' => $teachers
        ]);
    }

    /**
     * Update hall supervisor teacher
     */
    public function updateHallSupervisor(Request $request, $supervisorID)
    {
        // Check update permission - New format: timetable_update
        if (!$this->hasPermission('timetable_update')) {
            return response()->json([
                'error' => 'You do not have permission to update timetables. You need timetable_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Find supervisor
        $supervisor = ExamHallSupervisor::where('exam_hall_supervisorID', $supervisorID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$supervisor) {
            return response()->json(['error' => 'Supervisor assignment not found'], 404);
        }

        // Check if new teacher already assigned to this hall for this subject/timetable
        $existingAssignment = ExamHallSupervisor::where('exam_hallID', $supervisor->exam_hallID)
            ->where('subjectID', $supervisor->subjectID)
            ->where('exam_timetableID', $supervisor->exam_timetableID)
            ->where('teacherID', $request->teacher_id)
            ->where('exam_hall_supervisorID', '!=', $supervisorID)
            ->exists();

        if ($existingAssignment) {
            return response()->json(['error' => 'This teacher is already assigned to this hall for this subject'], 422);
        }

        // Check if teacher is already assigned to ANOTHER hall at the same time
        $conflictingAssignment = ExamHallSupervisor::where('examID', $supervisor->examID)
            ->where('exam_timetableID', $supervisor->exam_timetableID)
            ->where('teacherID', $request->teacher_id)
            ->where('exam_hallID', '!=', $supervisor->exam_hallID)
            ->where('exam_hall_supervisorID', '!=', $supervisorID)
            ->with('examHall:exam_hallID,hall_name')
            ->first();

        if ($conflictingAssignment) {
            $conflictHallName = $conflictingAssignment->examHall ? $conflictingAssignment->examHall->hall_name : 'another hall';
            return response()->json([
                'error' => "This teacher is already assigned to {$conflictHallName} at the same time. A teacher cannot supervise multiple halls simultaneously."
            ], 422);
        }

        // Verify new teacher is active and belongs to same school
        $teacher = Teacher::where('id', $request->teacher_id)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$teacher) {
            return response()->json(['error' => 'Selected teacher not found or inactive'], 422);
        }

        try {
            $supervisor->teacherID = $request->teacher_id;
            $supervisor->save();

            \Log::info("Updated hall supervisor {$supervisorID} to teacher {$request->teacher_id}");

            return response()->json([
                'success' => true,
                'message' => 'Supervisor updated successfully',
                'exam_timetableID' => $supervisor->exam_timetableID
            ]);
        } catch (\Exception $e) {
            \Log::error("Error updating hall supervisor: " . $e->getMessage());
            return response()->json(['error' => 'Failed to update supervisor'], 500);
        }
    }

    /**
     * Get hall supervisors for a specific subject/time slot
     */
    public function getSubjectHallSupervisors(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $examTimetableID = $request->exam_timetableID;

        if (!$examTimetableID) {
            return response()->json(['error' => 'Timetable ID is required'], 400);
        }

        // Get hall supervisors for this specific timetable entry
        $supervisors = ExamHallSupervisor::where('exam_timetableID', $examTimetableID)
            ->where('schoolID', $schoolID)
            ->with([
                'teacher:id,first_name,last_name',
                'examHall:exam_hallID,hall_name,classID,capacity,gender_allowed',
                'examHall.class:classID,class_name'
            ])
            ->get();

        \Log::info("Retrieved " . $supervisors->count() . " supervisors for timetable {$examTimetableID}");

        return response()->json([
            'success' => true,
            'supervisors' => $supervisors
        ]);
    }

    /**
     * Auto-assign hall supervisors for a specific subject/time slot in exam timetable
     * This method assigns different teachers to different halls for the same subject/time
     */
    private function autoAssignHallSupervisors($examID, $subjectID, $examTimetableID, $schoolID)
    {
        // Get all exam halls for this exam
        $examHalls = ExamHall::where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->get();

        if ($examHalls->isEmpty()) {
            // No halls defined, skip hall supervisor assignment
            return;
        }

        // Get eligible teachers - first try with supervise_exams permission, if none found, use all active teachers
        $eligibleTeachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('role_user')
                    ->join('permissions', 'role_user.role_id', '=', 'permissions.role_id')
                    ->whereColumn('role_user.teacher_id', 'teachers.id')
                    ->where('permissions.name', 'supervise_exams');
            })
            ->get();

        // If no teachers with permission, use all active teachers
        if ($eligibleTeachers->isEmpty()) {
            \Log::info("No teachers with 'supervise_exams' permission found. Using all active teachers for exam {$examID}.");
            $eligibleTeachers = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->get();
        }

        if ($eligibleTeachers->isEmpty()) {
            \Log::warning("No active teachers found for hall supervision in exam {$examID}.");
            return;
        }

        \Log::info("Found " . $eligibleTeachers->count() . " eligible teachers for hall supervision in exam {$examID}");

        // Shuffle teachers for random assignment
        $shuffledTeachers = $eligibleTeachers->shuffle()->values();
        $teacherIndex = 0;
        
        // Track teachers assigned during this method call (for this specific time slot)
        $localAssignedTeachers = [];

        foreach ($examHalls as $hall) {
            // Check if supervisor already assigned to this hall for this subject/time
            $existingSupervisor = ExamHallSupervisor::where('exam_hallID', $hall->exam_hallID)
                ->where('subjectID', $subjectID)
                ->where('exam_timetableID', $examTimetableID)
                ->exists();

            if ($existingSupervisor) {
                continue; // Skip if already assigned
            }

            // Get teachers that are not already assigned at this time (from DB + current loop)
            $dbAssignedTeachers = ExamHallSupervisor::where('examID', $examID)
                ->where('exam_timetableID', $examTimetableID)
                ->pluck('teacherID')
                ->toArray();
                
            $assignedAtThisTime = array_merge($dbAssignedTeachers, $localAssignedTeachers);

            // Filter out teachers already assigned at this time
            $availableTeachers = $shuffledTeachers->filter(function($teacher) use ($assignedAtThisTime) {
                return !in_array($teacher->id, $assignedAtThisTime);
            })->values();

            // If no available teachers (all assigned), use original list (will have conflicts but system will work)
            if ($availableTeachers->isEmpty()) {
                $availableTeachers = $shuffledTeachers;
            }

            // Get next available teacher (round-robin)
            $teacher = $availableTeachers[$teacherIndex % $availableTeachers->count()];
            $teacherIndex++;

            // Assign additional teacher if hall capacity > 100
            $teachersToAssign = [$teacher->id];
            if ($hall->capacity > 100 && $availableTeachers->count() > 1) {
                // Find another available teacher not yet assigned at this time
                $secondTeacher = null;
                for ($i = 0; $i < $availableTeachers->count(); $i++) {
                    $candidateTeacher = $availableTeachers[$teacherIndex % $availableTeachers->count()];
                    $teacherIndex++;
                    if (!in_array($candidateTeacher->id, $assignedAtThisTime) && $candidateTeacher->id != $teacher->id) {
                        $secondTeacher = $candidateTeacher;
                        break;
                    }
                }
                if ($secondTeacher) {
                    $teachersToAssign[] = $secondTeacher->id;
                }
            }

            // Create supervisor assignments
            foreach ($teachersToAssign as $teacherID) {
                ExamHallSupervisor::create([
                    'examID' => $examID,
                    'exam_hallID' => $hall->exam_hallID,
                    'teacherID' => $teacherID,
                    'subjectID' => $subjectID,
                    'exam_timetableID' => $examTimetableID,
                    'schoolID' => $schoolID,
                ]);
                
                // Add to local assigned list for next iteration (prevent double-booking)
                $localAssignedTeachers[] = $teacherID;
            }

            // Store teacher assignments for later batch SMS
            foreach ($teachersToAssign as $teacherID) {
                if (!isset($this->teacherAssignments[$teacherID])) {
                    $this->teacherAssignments[$teacherID] = [];
                }
                
                $this->teacherAssignments[$teacherID][] = [
                    'examID' => $examID,
                    'hall' => $hall,
                    'subjectID' => $subjectID,
                    'exam_timetableID' => $examTimetableID,
                ];
            }
        }
    }
    
    /**
     * Send batch SMS to all assigned teachers with their complete schedule
     */
    private function sendBatchSupervisorSMS()
    {
        if (empty($this->teacherAssignments)) {
            return;
        }

        $smsService = new SmsService();

        foreach ($this->teacherAssignments as $teacherID => $assignments) {
            $teacher = Teacher::find($teacherID);
            $phone = $teacher->phone_number ?? $teacher->phone ?? null;
            if (!$teacher || !$phone) {
                continue;
            }

            // Get exam name from first assignment
            $firstAssignment = $assignments[0];
            $exam = Examination::find($firstAssignment['examID']);
            
            if (!$exam) {
                continue;
            }

            // Build comprehensive message
            $message = "Habari {$teacher->name},\n\n";
            $message .= "Umepewa kusimamia mtihani: {$exam->exam_name}\n";
            $message .= "Jumla: " . count($assignments) . " sehemu za kusimamia\n\n";

            // Group by date
            $byDate = [];
            foreach ($assignments as $assignment) {
                $timetable = ExamTimetableSchoolWide::find($assignment['exam_timetableID']);
                $subject = SchoolSubject::find($assignment['subjectID']);
                $class = ClassModel::find($assignment['hall']->classID);
                
                if (!$timetable || !$subject || !$class) {
                    continue;
                }
                
                $dateKey = $timetable->exam_date->format('Y-m-d');
                if (!isset($byDate[$dateKey])) {
                    $byDate[$dateKey] = [
                        'date' => $timetable->exam_date,
                        'assignments' => []
                    ];
                }
                
                $byDate[$dateKey]['assignments'][] = [
                    'subject' => $subject->subject_name,
                    'hall' => $assignment['hall']->hall_name,
                    'class' => $class->class_name,
                    'start_time' => $timetable->start_time,
                    'end_time' => $timetable->end_time,
                    'gender' => $assignment['hall']->gender_allowed,
                ];
            }

            // Sort by date
            ksort($byDate);

            // Build message with details
            $count = 1;
            foreach ($byDate as $dateData) {
                $dayName = $dateData['date']->format('l'); // Monday, Tuesday, etc.
                $formattedDate = $dateData['date']->format('d/m/Y');
                
                $message .= "{$dayName}, {$formattedDate}:\n";
                
                foreach ($dateData['assignments'] as $assign) {
                    $startTime = \Carbon\Carbon::parse($assign['start_time'])->format('h:i A');
                    $endTime = \Carbon\Carbon::parse($assign['end_time'])->format('h:i A');
                    
                    $message .= "{$count}. {$assign['subject']} ({$startTime}-{$endTime})\n";
                    $message .= "   Hall: {$assign['hall']} | Class: {$assign['class']}\n";
                    $message .= "   Gender: " . ucfirst($assign['gender']) . "\n";
                    $count++;
                }
                $message .= "\n";
            }

            $message .= "Ahsante!";

            try {
                $smsService->sendSms($phone, $message);
            } catch (\Exception $e) {
                \Log::error("Error sending batch supervisor SMS to teacher {$teacherID}: " . $e->getMessage());
            }
        }

        // Clear assignments after sending
        $this->teacherAssignments = [];
    }

    /**
     * Send SMS notification to hall supervisors
     */
    private function sendHallSupervisorSMS($examID, $hall, $teacherIDs, $subjectID, $examTimetableID)
    {
        try {
            $exam = Examination::find($examID);
            $subject = SchoolSubject::find($subjectID);
            $timetable = ExamTimetableSchoolWide::find($examTimetableID);
            $class = ClassModel::find($hall->classID);

            if (!$exam || !$subject || !$timetable || !$class) {
                return;
            }

            // Get day name
            $dayName = \Carbon\Carbon::parse($timetable->exam_date)->format('l'); // Monday, Tuesday, etc.
            $formattedDate = \Carbon\Carbon::parse($timetable->exam_date)->format('d/m/Y');
            $startTime = \Carbon\Carbon::parse($timetable->start_time)->format('h:i A');
            $endTime = \Carbon\Carbon::parse($timetable->end_time)->format('h:i A');

            $smsService = new SmsService();

            foreach ($teacherIDs as $teacherID) {
                $teacher = Teacher::find($teacherID);
                $phone = $teacher->phone_number ?? $teacher->phone ?? null;
                if (!$teacher || !$phone) {
                    continue;
                }

                $message = "Kusimamia Mtihani: {$exam->exam_name}\n";
                $message .= "Somo: {$subject->subject_name}\n";
                $message .= "Tarehe: {$dayName}, {$formattedDate}\n";
                $message .= "Muda: {$startTime} - {$endTime}\n";
                $message .= "Hall: {$hall->hall_name}\n";
                $message .= "Darasa: {$class->class_name}\n";
                $message .= "Wanafunzi: {$hall->gender_allowed}";

                $smsService->sendSms($phone, $message);
            }
        } catch (\Exception $e) {
            \Log::error("Error sending hall supervisor SMS: " . $e->getMessage());
        }
    }

    /**
     * Assign supervise teachers to halls for class_specific timetable
     */
    private function assignSupervisorsForClassSpecificTimetable($examID, $examTimetableID, $subjectID, $schoolID, $examTimetable)
    {
        if (!$subjectID) {
            \Log::warning("Cannot assign supervisors: subjectID is null for exam_timetableID {$examTimetableID}");
            return;
        }

        // Get all exam halls for this exam
        $examHalls = ExamHall::where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->get();

        if ($examHalls->isEmpty()) {
            \Log::info("No exam halls found for exam {$examID}, skipping supervisor assignment");
            return;
        }

        // Get eligible teachers - first try with supervise_exams permission, if none found, use all active teachers
        $eligibleTeachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('role_user')
                    ->join('permissions', 'role_user.role_id', '=', 'permissions.role_id')
                    ->whereColumn('role_user.teacher_id', 'teachers.id')
                    ->where('permissions.name', 'supervise_exams');
            })
            ->get();

        // If no teachers with permission, use all active teachers
        if ($eligibleTeachers->isEmpty()) {
            \Log::info("No teachers with 'supervise_exams' permission found. Using all active teachers for exam {$examID}.");
            $eligibleTeachers = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->get();
        }

        if ($eligibleTeachers->isEmpty()) {
            \Log::warning("No active teachers found for hall supervision in exam {$examID}.");
            return;
        }

        // Shuffle teachers for random assignment
        $shuffledTeachers = $eligibleTeachers->shuffle()->values();
        $teacherIndex = 0;

        foreach ($examHalls as $hall) {
            // Check if supervisor already assigned to this hall for this subject/time
            $existingSupervisor = ExamHallSupervisor::where('exam_hallID', $hall->exam_hallID)
                ->where('subjectID', $subjectID)
                ->where('exam_timetableID', $examTimetableID)
                ->exists();

            if ($existingSupervisor) {
                continue; // Skip if already assigned
            }

            // Get teachers that are not already assigned at this time
            $dbAssignedTeachers = ExamHallSupervisor::where('examID', $examID)
                ->where('exam_timetableID', $examTimetableID)
                ->pluck('teacherID')
                ->toArray();

            // Filter out teachers already assigned at this time
            $availableTeachers = $shuffledTeachers->filter(function($teacher) use ($dbAssignedTeachers) {
                return !in_array($teacher->id, $dbAssignedTeachers);
            })->values();

            if ($availableTeachers->isEmpty()) {
                $availableTeachers = $shuffledTeachers;
            }

            // Get next available teacher
            $teacher = $availableTeachers[$teacherIndex % $availableTeachers->count()];
            $teacherIndex++;

            // Assign additional teacher if hall capacity > 100
            $teachersToAssign = [$teacher->id];
            if ($hall->capacity > 100 && $availableTeachers->count() > 1) {
                $secondTeacher = $availableTeachers[$teacherIndex % $availableTeachers->count()];
                $teacherIndex++;
                if ($secondTeacher->id != $teacher->id) {
                    $teachersToAssign[] = $secondTeacher->id;
                }
            }

            // Create supervisor assignments
            foreach ($teachersToAssign as $teacherID) {
                ExamHallSupervisor::create([
                    'examID' => $examID,
                    'exam_hallID' => $hall->exam_hallID,
                    'teacherID' => $teacherID,
                    'subjectID' => $subjectID,
                    'exam_timetableID' => $examTimetableID,
                    'schoolID' => $schoolID,
                ]);
            }

            // Send SMS to assigned teachers
            $this->sendClassSpecificHallSupervisorSMS($examID, $hall, $teachersToAssign, $subjectID, $examTimetable);
        }
    }

    /**
     * Send SMS to supervise teachers for class_specific timetable
     */
    private function sendClassSpecificSupervisorSMS($createdEntries)
    {
        try {
            $smsService = new SmsService();
            
            foreach ($createdEntries as $entry) {
                // Get supervisors for this timetable entry
                $supervisors = ExamHallSupervisor::where('exam_timetableID', $entry->exam_timetableID)
                    ->with(['teacher', 'examHall.class'])
                    ->get();

                if ($supervisors->isEmpty()) {
                    continue;
                }

                $exam = $entry->examination;
                $subject = $entry->classSubject->subject ?? null;
                $subclass = $entry->subclass;
                $class = $subclass->class ?? null;

                if (!$exam || !$subject || !$class) {
                    continue;
                }

                $dayName = \Carbon\Carbon::parse($entry->exam_date)->format('l');
                $formattedDate = \Carbon\Carbon::parse($entry->exam_date)->format('d/m/Y');
                $startTime = \Carbon\Carbon::parse($entry->start_time)->format('h:i A');
                $endTime = \Carbon\Carbon::parse($entry->end_time)->format('h:i A');

                foreach ($supervisors as $supervisor) {
                    $teacher = $supervisor->teacher;
                    $phone = $teacher->phone_number ?? $teacher->phone ?? null;
                    if (!$teacher || !$phone) {
                        continue;
                    }

                    $hall = $supervisor->examHall;
                    $hallClass = $hall->class ?? null;

                    $message = "Kusimamia Mtihani: {$exam->exam_name}\n";
                    $message .= "Somo: {$subject->subject_name}\n";
                    $message .= "Tarehe: {$dayName}, {$formattedDate}\n";
                    $message .= "Muda: {$startTime} - {$endTime}\n";
                    $message .= "Hall: {$hall->hall_name}\n";
                    if ($hallClass) {
                        $message .= "Darasa: {$hallClass->class_name}\n";
                    }
                    $message .= "Wanafunzi: " . ucfirst($hall->gender_allowed);

                    try {
                        $smsService->sendSms($phone, $message);
                    } catch (\Exception $e) {
                        \Log::error("Error sending SMS to teacher {$teacher->id}: " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error sending class specific supervisor SMS: " . $e->getMessage());
        }
    }

    /**
     * Send SMS to hall supervisors for class_specific timetable (individual hall)
     */
    private function sendClassSpecificHallSupervisorSMS($examID, $hall, $teacherIDs, $subjectID, $examTimetable)
    {
        try {
            $exam = Examination::find($examID);
            $subject = SchoolSubject::find($subjectID);
            $class = ClassModel::find($hall->classID);

            if (!$exam || !$subject || !$class || !$examTimetable) {
                return;
            }

            $dayName = \Carbon\Carbon::parse($examTimetable->exam_date)->format('l');
            $formattedDate = \Carbon\Carbon::parse($examTimetable->exam_date)->format('d/m/Y');
            $startTime = \Carbon\Carbon::parse($examTimetable->start_time)->format('h:i A');
            $endTime = \Carbon\Carbon::parse($examTimetable->end_time)->format('h:i A');

            $smsService = new SmsService();

            foreach ($teacherIDs as $teacherID) {
                $teacher = Teacher::find($teacherID);
                $phone = $teacher->phone_number ?? $teacher->phone ?? null;
                if (!$teacher || !$phone) {
                    continue;
                }

                $message = "Kusimamia Mtihani: {$exam->exam_name}\n";
                $message .= "Somo: {$subject->subject_name}\n";
                $message .= "Tarehe: {$dayName}, {$formattedDate}\n";
                $message .= "Muda: {$startTime} - {$endTime}\n";
                $message .= "Hall: {$hall->hall_name}\n";
                $message .= "Darasa: {$class->class_name}\n";
                $message .= "Wanafunzi: " . ucfirst($hall->gender_allowed);

                try {
                    $smsService->sendSms($phone, $message);
                } catch (\Exception $e) {
                    \Log::error("Error sending SMS to teacher {$teacherID}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error sending class specific hall supervisor SMS: " . $e->getMessage());
        }
    }

    /**
     * Shuffle exam timetable - randomly redistribute subjects across dates and times
     */
    public function shuffleExamTimetable($examID)
    {
        // Check update permission - New format: timetable_update (shuffle is an update action)
        if (!$this->hasPermission('timetable_update')) {
            return response()->json([
                'error' => 'You do not have permission to update timetables. You need timetable_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Get the exam
            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$exam) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Get all timetable entries for this exam (both class-specific and school-wide)
            $timetableEntries = DB::table('exam_timetable')
                ->where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->get();

            if ($timetableEntries->isEmpty()) {
                return response()->json(['error' => 'No timetable entries found to shuffle'], 404);
            }

            \Log::info("Starting shuffle for exam {$examID} with {$timetableEntries->count()} entries");

            // Extract unique time slots (date + time combinations)
            $timeSlots = [];
            foreach ($timetableEntries as $entry) {
                $timeSlots[] = [
                    'exam_date' => $entry->exam_date,
                    'start_time' => $entry->start_time,
                    'end_time' => $entry->end_time
                ];
            }

            // Remove duplicates
            $timeSlots = collect($timeSlots)->unique(function ($slot) {
                return $slot['exam_date'] . '|' . $slot['start_time'] . '|' . $slot['end_time'];
            })->values()->shuffle()->all();

            // Shuffle the time slots
            shuffle($timeSlots);

            \Log::info("Shuffled time slots: " . json_encode($timeSlots));

            // Reassign each timetable entry to a shuffled time slot
            DB::beginTransaction();

            $index = 0;
            foreach ($timetableEntries as $entry) {
                $newTimeSlot = $timeSlots[$index % count($timeSlots)];
                $index++;

                DB::table('exam_timetable')
                    ->where('exam_timetableID', $entry->exam_timetableID)
                    ->update([
                        'exam_date' => $newTimeSlot['exam_date'],
                        'start_time' => $newTimeSlot['start_time'],
                        'end_time' => $newTimeSlot['end_time'],
                        'updated_at' => now()
                    ]);

                \Log::info("Updated timetable {$entry->exam_timetableID}: {$newTimeSlot['exam_date']} {$newTimeSlot['start_time']}-{$newTimeSlot['end_time']}");
            }

            DB::commit();

            \Log::info("Shuffle completed successfully for exam {$examID}");

            return response()->json([
                'success' => true,
                'message' => 'Timetable shuffled successfully. All subjects have been randomly redistributed.',
                'shuffled_count' => $timetableEntries->count()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error shuffling timetable: " . $e->getMessage());
            return response()->json(['error' => 'Failed to shuffle timetable: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Swap two subjects in the timetable - exchange their dates and times
     */
    public function swapExamSubjects(Request $request)
    {
        // Check update permission - New format: timetable_update (swap is an update action)
        if (!$this->hasPermission('timetable_update')) {
            return response()->json([
                'error' => 'You do not have permission to update timetables. You need timetable_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'timetable1_id' => 'required|integer',
            'timetable2_id' => 'required|integer',
        ]);

        try {
            // Get both timetable entries
            $timetable1 = DB::table('exam_timetable')
                ->where('exam_timetableID', $request->timetable1_id)
                ->where('schoolID', $schoolID)
                ->first();

            $timetable2 = DB::table('exam_timetable')
                ->where('exam_timetableID', $request->timetable2_id)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$timetable1 || !$timetable2) {
                return response()->json(['error' => 'One or both timetable entries not found'], 404);
            }

            // Ensure both belong to the same exam
            if ($timetable1->examID !== $timetable2->examID) {
                return response()->json(['error' => 'Cannot swap subjects from different exams'], 422);
            }

            \Log::info("Swapping timetable entries: {$timetable1->exam_timetableID} <-> {$timetable2->exam_timetableID}");

            DB::beginTransaction();

            // Store temporary values from timetable1
            $temp = [
                'exam_date' => $timetable1->exam_date,
                'start_time' => $timetable1->start_time,
                'end_time' => $timetable1->end_time
            ];

            // Update timetable1 with timetable2's values
            DB::table('exam_timetable')
                ->where('exam_timetableID', $timetable1->exam_timetableID)
                ->update([
                    'exam_date' => $timetable2->exam_date,
                    'start_time' => $timetable2->start_time,
                    'end_time' => $timetable2->end_time,
                    'updated_at' => now()
                ]);

            // Update timetable2 with timetable1's (temp) values
            DB::table('exam_timetable')
                ->where('exam_timetableID', $timetable2->exam_timetableID)
                ->update([
                    'exam_date' => $temp['exam_date'],
                    'start_time' => $temp['start_time'],
                    'end_time' => $temp['end_time'],
                    'updated_at' => now()
                ]);

            DB::commit();

            \Log::info("Swap completed successfully");

            // Get subject names for response
            $subject1Name = $this->getSubjectNameFromTimetable($timetable1);
            $subject2Name = $this->getSubjectNameFromTimetable($timetable2);

            return response()->json([
                'success' => true,
                'message' => "Successfully swapped {$subject1Name} with {$subject2Name}"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error swapping subjects: " . $e->getMessage());
            return response()->json(['error' => 'Failed to swap subjects: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to get subject name from timetable entry
     */
    private function getSubjectNameFromTimetable($timetableEntry)
    {
        if ($timetableEntry->subjectID) {
            // School-wide subject
            $subject = DB::table('school_subjects')
                ->where('subjectID', $timetableEntry->subjectID)
                ->first();
            return $subject ? $subject->subject_name : 'Unknown Subject';
        } elseif ($timetableEntry->subclassSubjectID) {
            // Class-specific subject
            $classSubject = DB::table('class_subjects')
                ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                ->where('class_subjects.subclassSubjectID', $timetableEntry->subclassSubjectID)
                ->first();
            return $classSubject ? $classSubject->subject_name : 'Unknown Subject';
        }
        return 'Unknown Subject';
    }

    // Get session timetable definition
    public function getSessionTimetableDefinition(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            
            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'School ID not found']);
            }

            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json([
                    'success' => true,
                    'definition' => null
                ]);
            }

            // Get break times
            $breakTimes = DB::table('break_times')
                ->where('definitionID', $definition->definitionID)
                ->orderBy('order')
                ->orderBy('start_time')
                ->get()
                ->map(function($bt) {
                    return [
                        'start_time' => $bt->start_time,
                        'end_time' => $bt->end_time,
                        'order' => $bt->order
                    ];
                });

            // Get session types
            $sessionTypes = DB::table('session_types')
                ->where('definitionID', $definition->definitionID)
                ->orderBy('name')
                ->get()
                ->map(function($st) {
                    return [
                        'session_typeID' => $st->session_typeID,
                        'type' => $st->type,
                        'name' => $st->name,
                        'minutes' => $st->minutes
                    ];
                });

            return response()->json([
                'success' => true,
                'definition' => [
                    'id' => $definition->definitionID,
                    'session_start_time' => $definition->session_start_time,
                    'session_end_time' => $definition->session_end_time,
                    'has_prepo' => (bool)$definition->has_prepo,
                    'prepo_start_time' => $definition->prepo_start_time,
                    'prepo_end_time' => $definition->prepo_end_time,
                    'break_times' => $breakTimes,
                    'session_types' => $sessionTypes
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting session timetable definition: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Save session timetable definition
    public function saveSessionTimetableDefinition(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            
            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'School ID not found']);
            }

            $validator = Validator::make($request->all(), [
                'session_start_time' => 'required',
                'session_end_time' => 'required',
                'has_prepo' => 'nullable|in:true,false,1,0,on,off',
                'break_times' => 'array',
                'session_types' => 'required|array|min:1',
                'prepo_start_time' => 'nullable|required_if:has_prepo,true|required_if:has_prepo,on|required_if:has_prepo,1',
                'prepo_end_time' => 'nullable|required_if:has_prepo,true|required_if:has_prepo,on|required_if:has_prepo,1'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Normalize has_prepo to boolean
            // Always check the value, even if it's false/0
            $hasPrepo = false;
            $hasPrepoValue = $request->input('has_prepo', '0');
            
            // Check if it's explicitly set to true/1/on (case-insensitive)
            $hasPrepoValue = is_string($hasPrepoValue) ? strtolower($hasPrepoValue) : $hasPrepoValue;
            
                if (in_array($hasPrepoValue, ['true', '1', 'on', true, 1])) {
                    $hasPrepo = true;
            } else {
                // Explicitly set to false if it's '0', 'false', 'off', or false
                $hasPrepo = false;
            }

            DB::beginTransaction();

            // Check if definition already exists for this school
            $existingDefinition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if ($existingDefinition) {
                // Update existing definition
                $definitionID = $existingDefinition->definitionID;
                
                DB::table('session_timetable_definitions')
                    ->where('definitionID', $definitionID)
                    ->update([
                        'session_start_time' => $request->session_start_time,
                        'session_end_time' => $request->session_end_time,
                        'has_prepo' => $hasPrepo ? 1 : 0,
                        'prepo_start_time' => $hasPrepo ? $request->prepo_start_time : null,
                        'prepo_end_time' => $hasPrepo ? $request->prepo_end_time : null,
                        'updated_at' => now()
                    ]);

                // Delete old break times and session types
                DB::table('break_times')->where('definitionID', $definitionID)->delete();
                DB::table('session_types')->where('definitionID', $definitionID)->delete();
            } else {
                // Create new definition
                $definitionID = DB::table('session_timetable_definitions')->insertGetId([
                    'schoolID' => $schoolID,
                    'session_start_time' => $request->session_start_time,
                    'session_end_time' => $request->session_end_time,
                    'has_prepo' => $hasPrepo ? 1 : 0,
                    'prepo_start_time' => $hasPrepo ? $request->prepo_start_time : null,
                    'prepo_end_time' => $hasPrepo ? $request->prepo_end_time : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Save break times
            if ($request->break_times && is_array($request->break_times)) {
                $order = 1;
                foreach ($request->break_times as $breakTime) {
                    if (isset($breakTime['start_time']) && isset($breakTime['end_time'])) {
                        DB::table('break_times')->insert([
                            'definitionID' => $definitionID,
                            'start_time' => $breakTime['start_time'],
                            'end_time' => $breakTime['end_time'],
                            'order' => $order++,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            // Save session types
            if ($request->session_types && is_array($request->session_types)) {
                foreach ($request->session_types as $sessionType) {
                    if (isset($sessionType['type']) && isset($sessionType['name']) && isset($sessionType['minutes'])) {
                        DB::table('session_types')->insert([
                            'definitionID' => $definitionID,
                            'type' => $sessionType['type'],
                            'name' => $sessionType['name'],
                            'minutes' => (int)$sessionType['minutes'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Timetable definition saved successfully',
                'definitionID' => $definitionID
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving session timetable definition: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get session types for definition
    public function getSessionTypes(Request $request)
    {
        try {
            $definitionID = $request->input('definitionID');
            
            if (!$definitionID) {
                return response()->json(['success' => false, 'error' => 'Definition ID required']);
            }

            $types = DB::table('session_types')
                ->where('definitionID', $definitionID)
                ->orderBy('name')
                ->get()
                ->map(function($st) {
                    return [
                        'session_typeID' => $st->session_typeID,
                        'type' => $st->type,
                        'name' => $st->name,
                        'minutes' => $st->minutes
                    ];
                });

            return response()->json(['success' => true, 'types' => $types]);
        } catch (\Exception $e) {
            \Log::error('Error getting session types: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get selected subjects for subclass (to disable them in dropdown)
    public function getSelectedSubjectsForSubclass(Request $request)
    {
        try {
            $subclassID = $request->input('subclassID');
            $schoolID = Session::get('schoolID');
            
            if (!$subclassID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Subclass ID and school ID required']);
            }

            // Get definition ID
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => true, 'selectedSubjects' => []]);
            }

            // Get already scheduled subjects for this subclass
            $selectedSubjects = DB::table('class_session_timetables')
                ->where('definitionID', $definition->definitionID)
                ->where('subclassID', $subclassID)
                ->whereNotNull('class_subjectID')
                ->select('class_subjectID')
                ->distinct()
                ->get()
                ->map(function($st) {
                    return [
                        'class_subjectID' => $st->class_subjectID
                    ];
                });

            return response()->json(['success' => true, 'selectedSubjects' => $selectedSubjects]);
        } catch (\Exception $e) {
            \Log::error('Error getting selected subjects: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Check if subclass has timetable
    public function checkSubclassHasTimetable(Request $request)
    {
        try {
            $subclassID = $request->input('subclassID');
            $schoolID = Session::get('schoolID');
            
            if (!$subclassID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Subclass ID and school ID required']);
            }

            // Get definition ID
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => true, 'hasTimetable' => false]);
            }

            // Check if subclass has any timetable entries
            $hasTimetable = DB::table('class_session_timetables')
                ->where('definitionID', $definition->definitionID)
                ->where('subclassID', $subclassID)
                ->exists();

            return response()->json(['success' => true, 'hasTimetable' => $hasTimetable]);
        } catch (\Exception $e) {
            \Log::error('Error checking subclass timetable: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get all subclasses that have timetables
    public function getAllSubclassesWithTimetables(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            
            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'School ID required']);
            }

            // Get definition ID
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => true, 'subclassIDs' => []]);
            }

            // Get all subclass IDs that have timetable entries
            $subclassIDs = DB::table('class_session_timetables')
                ->where('definitionID', $definition->definitionID)
                ->select('subclassID')
                ->distinct()
                ->pluck('subclassID')
                ->toArray();

            return response()->json(['success' => true, 'subclassIDs' => $subclassIDs]);
        } catch (\Exception $e) {
            \Log::error('Error getting subclasses with timetables: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Save class session timetables
    public function saveClassSessionTimetables(Request $request)
    {
        // Check create permission - New format: timetable_create
        if (!$this->hasPermission('timetable_create')) {
            return response()->json([
                'error' => 'You do not have permission to create timetables. You need timetable_create permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');
            
            if (!$schoolID) {
                return response()->json(['success' => false, 'error' => 'School ID not found']);
            }

            $timetables = $request->input('timetables', []);
            
            if (empty($timetables)) {
                return response()->json(['success' => false, 'error' => 'No timetable data provided']);
            }

            // Get definition ID
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => false, 'error' => 'Timetable definition not found. Please create definition first.']);
            }

            DB::beginTransaction();

            // Get unique subclass IDs from timetables
            $subclassIDs = array_unique(array_column($timetables, 'subclassID'));
            
            // Delete existing timetables for these subclasses before inserting new ones
            foreach ($subclassIDs as $subclassID) {
                DB::table('class_session_timetables')
                    ->where('definitionID', $definition->definitionID)
                    ->where('subclassID', $subclassID)
                    ->delete();
            }

            // Group timetables by teacher for SMS
            $teacherTimetables = [];
            
            foreach ($timetables as $timetable) {
                // For free sessions, teacherID can be null
                $teacherID = $timetable['teacherID'] ?? null;
                
                DB::table('class_session_timetables')->insert([
                    'schoolID' => $schoolID,
                    'definitionID' => $definition->definitionID,
                    'subclassID' => $timetable['subclassID'],
                    'class_subjectID' => $timetable['class_subjectID'] ?? null,
                    'subjectID' => $timetable['subjectID'] ?? null,
                    'teacherID' => $teacherID,
                    'session_typeID' => $timetable['session_typeID'],
                    'day' => $timetable['day'],
                    'start_time' => $timetable['start_time'],
                    'end_time' => $timetable['end_time'],
                    'is_prepo' => $timetable['is_prepo'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Group by teacher for SMS (only if teacherID exists - skip free sessions)
                if ($teacherID) {
                    if (!isset($teacherTimetables[$teacherID])) {
                        $teacherTimetables[$teacherID] = [];
                    }
                    $teacherTimetables[$teacherID][] = $timetable;
                }
            }

            DB::commit();

            // Send SMS to teachers after successful save
            \Log::info('Starting SMS sending for ' . count($teacherTimetables) . ' teachers');
            
            $smsService = new SmsService();
            $school = DB::table('schools')->where('schoolID', $schoolID)->first();
            $schoolName = $school ? $school->school_name : 'ShuleLink';
            
            $smsResults = [
                'sent' => 0,
                'failed' => 0,
                'details' => []
            ];
            
            foreach ($teacherTimetables as $teacherID => $sessions) {
                \Log::info('Processing SMS for teacher ID: ' . $teacherID . ' with ' . count($sessions) . ' sessions');
                try {
                    // Get teacher details
                    $teacher = Teacher::find($teacherID);
                    $phoneNumber = $teacher->phone_number ?? $teacher->phone ?? null;
                    if (!$teacher || !$phoneNumber) {
                        $smsResults['failed']++;
                        $smsResults['details'][] = [
                            'teacherID' => $teacherID,
                            'status' => 'failed',
                            'reason' => 'Teacher not found or no phone number'
                        ];
                        continue;
                    }
                    
                    // Group sessions by day
                    $sessionsByDay = [];
                    foreach ($sessions as $session) {
                        $day = $session['day'];
                        if (!isset($sessionsByDay[$day])) {
                            $sessionsByDay[$day] = [];
                        }
                        
                        // Get subject name
                        $subjectName = 'N/A';
                        if (isset($session['class_subjectID']) && $session['class_subjectID']) {
                            $classSubject = DB::table('class_subjects')
                                ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                                ->where('class_subjects.class_subjectID', $session['class_subjectID'])
                                ->select('school_subjects.subject_name')
                                ->first();
                            if ($classSubject) {
                                $subjectName = $classSubject->subject_name;
                            }
                        }
                        
                        // Get subclass name
                        $subclassName = 'N/A';
                        if (isset($session['subclassID'])) {
                            $subclass = DB::table('subclasses')
                                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                                ->where('subclasses.subclassID', $session['subclassID'])
                                ->select('classes.class_name', 'subclasses.subclass_name')
                                ->first();
                            if ($subclass) {
                                $subclassName = trim($subclass->class_name . ' ' . $subclass->subclass_name);
                            }
                        }
                        
                        $sessionType = $session['is_prepo'] ? 'Prepo' : 'Regular';
                        $sessionsByDay[$day][] = [
                            'subject' => $subjectName,
                            'class' => $subclassName,
                            'time' => $session['start_time'] . '-' . $session['end_time'],
                            'type' => $sessionType
                        ];
                    }
                    
                    // Build SMS message
                    $daysOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                    $message = $schoolName . ". Ratiba yako ya masomo:\n\n";
                    
                    foreach ($daysOrder as $day) {
                        if (isset($sessionsByDay[$day]) && !empty($sessionsByDay[$day])) {
                            $message .= $day . ":\n";
                            foreach ($sessionsByDay[$day] as $session) {
                                $message .= $session['time'] . " - " . $session['subject'] . " (" . $session['class'] . ")\n";
                            }
                            $message .= "\n";
                        }
                    }
                    
                    $message .= "Asante!";
                    
                    // Send SMS
                    \Log::info('Sending SMS to teacher ' . $teacher->first_name . ' ' . $teacher->last_name . ' at ' . $phoneNumber);
                    \Log::info('SMS Message: ' . substr($message, 0, 200) . '...');
                    
                    $smsResult = $smsService->sendSms($phoneNumber, $message);
                    
                    \Log::info('SMS Result for teacher ' . $teacherID . ':', $smsResult);
                    
                    if ($smsResult['success']) {
                        $smsResults['sent']++;
                        $smsResults['details'][] = [
                            'teacherID' => $teacherID,
                            'teacherName' => $teacher->first_name . ' ' . $teacher->last_name,
                            'phone' => $phoneNumber,
                            'status' => 'sent'
                        ];
                        \Log::info('SMS sent successfully to teacher ' . $teacherID);
                    } else {
                        $smsResults['failed']++;
                        $smsResults['details'][] = [
                            'teacherID' => $teacherID,
                            'teacherName' => $teacher->first_name . ' ' . $teacher->last_name,
                            'phone' => $phoneNumber,
                            'status' => 'failed',
                            'reason' => $smsResult['message'] ?? 'Unknown error'
                        ];
                        \Log::warning('SMS failed for teacher ' . $teacherID . ': ' . ($smsResult['message'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    \Log::error('Error sending SMS to teacher ' . $teacherID . ': ' . $e->getMessage());
                    $smsResults['failed']++;
                    $smsResults['details'][] = [
                        'teacherID' => $teacherID,
                        'status' => 'failed',
                        'reason' => $e->getMessage()
                    ];
                }
            }

            // Log SMS results
            \Log::info('SMS sending results for timetable save:', [
                'total_teachers' => count($teacherTimetables),
                'sent' => $smsResults['sent'],
                'failed' => $smsResults['failed'],
                'details' => $smsResults['details']
            ]);

            return response()->json([
                'success' => true,
                'message' => count($timetables) . ' session(s) saved successfully. SMS sent to ' . $smsResults['sent'] . ' teacher(s).',
                'sms' => [
                    'sent' => $smsResults['sent'],
                    'failed' => $smsResults['failed'],
                    'total' => count($teacherTimetables),
                    'details' => $smsResults['details']
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving class session timetables: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get session timetable for a subclass
     */
    public function getSessionTimetable(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $subclassID = $request->input('subclassID');
            
            if (!$schoolID || !$subclassID) {
                return response()->json(['success' => false, 'error' => 'School ID and Subclass ID required']);
            }

            // Get definition
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => false, 'error' => 'Timetable definition not found']);
            }

            // Get break times
            $breakTimes = DB::table('break_times')
                ->where('definitionID', $definition->definitionID)
                ->orderBy('start_time')
                ->get();

            // Get session types
            $sessionTypes = DB::table('session_types')
                ->where('definitionID', $definition->definitionID)
                ->get()
                ->keyBy('session_typeID');

            // Check if subclass has timetable
            $hasTimetable = DB::table('class_session_timetables')
                ->where('definitionID', $definition->definitionID)
                ->where('subclassID', $subclassID)
                ->exists();

            if (!$hasTimetable) {
                return response()->json([
                    'success' => false,
                    'error' => 'No timetable defined for this class',
                    'has_timetable' => false
                ]);
            }

            // Get timetable sessions - use distinct to avoid duplicates
            $sessions = DB::table('class_session_timetables')
                ->leftJoin('class_subjects', 'class_session_timetables.class_subjectID', '=', 'class_subjects.class_subjectID')
                ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                ->leftJoin('subclasses', 'class_session_timetables.subclassID', '=', 'subclasses.subclassID')
                ->leftJoin('classes', 'subclasses.classID', '=', 'classes.classID')
                ->leftJoin('teachers', 'class_session_timetables.teacherID', '=', 'teachers.id')
                ->leftJoin('session_types', 'class_session_timetables.session_typeID', '=', 'session_types.session_typeID')
                ->where('class_session_timetables.definitionID', $definition->definitionID)
                ->where('class_session_timetables.subclassID', $subclassID)
                ->select(
                    'class_session_timetables.session_timetableID',
                    'class_session_timetables.class_subjectID',
                    'class_session_timetables.subjectID',
                    'class_session_timetables.teacherID',
                    'class_session_timetables.session_typeID',
                    'class_session_timetables.day',
                    'class_session_timetables.start_time',
                    'class_session_timetables.end_time',
                    'class_session_timetables.is_prepo',
                    'school_subjects.subject_name',
                    'classes.class_name',
                    'subclasses.subclass_name',
                    'session_types.type as session_type',
                    'session_types.name as session_type_name',
                    DB::raw("CONCAT(teachers.first_name, ' ', teachers.last_name) as teacher_name")
                )
                ->distinct()
                ->orderBy('class_session_timetables.day')
                ->orderBy('class_session_timetables.start_time')
                ->get()
                ->map(function($session) {
                    // Check if this is a free session
                    $isFree = false;
                    if (($session->session_type && strtolower($session->session_type) === 'free') || 
                        ($session->session_type_name && stripos(strtolower($session->session_type_name), 'free') !== false) ||
                        (!$session->class_subjectID && !$session->subjectID && !$session->teacherID)) {
                        $isFree = true;
                    }
                    
                    return [
                        'session_timetableID' => $session->session_timetableID,
                        'class_subjectID' => $session->class_subjectID,
                        'subjectID' => $session->subjectID,
                        'teacherID' => $session->teacherID,
                        'session_typeID' => $session->session_typeID,
                        'day' => $session->day,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'is_prepo' => $session->is_prepo,
                        'subject_name' => $isFree ? 'FREE' : ($session->subject_name ?? 'N/A'),
                        'teacher_name' => $isFree ? 'FREE' : ($session->teacher_name ?? 'N/A'),
                        'class_name' => $session->class_name,
                        'subclass_name' => $session->subclass_name,
                        'is_free' => $isFree,
                        'session_type' => $session->session_type
                    ];
                });

            // Get subclass info
            $subclass = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('subclasses.subclassID', $subclassID)
                ->select('classes.class_name', 'subclasses.subclass_name')
                ->first();

            return response()->json([
                'success' => true,
                'definition' => [
                    'session_start_time' => $definition->session_start_time,
                    'session_end_time' => $definition->session_end_time,
                    'has_prepo' => (bool)$definition->has_prepo,
                    'prepo_start_time' => $definition->prepo_start_time,
                    'prepo_end_time' => $definition->prepo_end_time,
                ],
                'break_times' => $breakTimes,
                'session_types' => $sessionTypes,
                'sessions' => $sessions,
                'subclass' => $subclass
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting session timetable: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Delete class session timetable
     */
    public function deleteClassSessionTimetable(Request $request)
    {
        // Check delete permission - New format: timetable_delete
        if (!$this->hasPermission('timetable_delete')) {
            return response()->json([
                'error' => 'You do not have permission to delete timetables. You need timetable_delete permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');
            $subclassID = $request->input('subclassID');
            
            if (!$schoolID || !$subclassID) {
                return response()->json(['success' => false, 'error' => 'School ID and Subclass ID required']);
            }

            // Get definition ID
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => false, 'error' => 'Timetable definition not found']);
            }

            // Delete all sessions for this subclass
            $deleted = DB::table('class_session_timetables')
                ->where('definitionID', $definition->definitionID)
                ->where('subclassID', $subclassID)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Timetable deleted successfully',
                'deleted_count' => $deleted
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting class session timetable: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Shuffle session timetable - randomly redistribute sessions
     */
    public function shuffleSessionTimetable(Request $request)
    {
        // Check update permission - New format: timetable_update (shuffle is an update action)
        if (!$this->hasPermission('timetable_update')) {
            return response()->json([
                'error' => 'You do not have permission to update timetables. You need timetable_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');
            $subclassID = $request->input('subclassID');
            
            if (!$schoolID || !$subclassID) {
                return response()->json(['success' => false, 'error' => 'School ID and Subclass ID required']);
            }

            // Get definition
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => false, 'error' => 'Timetable definition not found']);
            }

            // Get all sessions for this subclass
            $sessions = DB::table('class_session_timetables')
                ->where('definitionID', $definition->definitionID)
                ->where('subclassID', $subclassID)
                ->get();

            if ($sessions->count() < 2) {
                return response()->json(['success' => false, 'error' => 'Need at least 2 sessions to shuffle']);
            }

            // Separate prepo and regular sessions
            $regularSessions = $sessions->where('is_prepo', 0)->values();
            $prepoSessions = $sessions->where('is_prepo', 1)->values();

            // Get all possible days
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            
            // Get break times to avoid
            $breakTimes = DB::table('break_times')
                ->where('definitionID', $definition->definitionID)
                ->get();

            // Get all available time slots for regular sessions (excluding break times)
            $allTimeSlots = [];
            foreach ($regularSessions as $session) {
                $timeKey = $session->start_time . '-' . $session->end_time;
                if (!in_array($timeKey, $allTimeSlots)) {
                    $allTimeSlots[] = $timeKey;
                }
            }

            // Shuffle regular sessions and time slots
            $shuffledRegular = $regularSessions->shuffle();
            $shuffledTimeSlots = collect($allTimeSlots)->shuffle()->toArray();
            $shuffledDays = collect($days)->shuffle()->toArray();
            
            DB::beginTransaction();

            // Update regular sessions with shuffled days and times
            $timeSlotIndex = 0;
            $dayIndex = 0;
            
            foreach ($shuffledRegular as $index => $session) {
                // Get shuffled day and time slot
                $newDay = $shuffledDays[$dayIndex % count($shuffledDays)];
                $newTimeSlot = $shuffledTimeSlots[$timeSlotIndex % count($shuffledTimeSlots)];
                list($newStartTime, $newEndTime) = explode('-', $newTimeSlot);
                
                $dayIndex++;
                $timeSlotIndex++;
                
                DB::table('class_session_timetables')
                    ->where('session_timetableID', $session->session_timetableID)
                    ->update([
                        'day' => $newDay,
                        'start_time' => $newStartTime,
                        'end_time' => $newEndTime,
                        'updated_at' => now()
                    ]);
            }

            // Shuffle prepo sessions if any (keep prepo times but shuffle days)
            if ($prepoSessions->count() > 0) {
                $shuffledPrepo = $prepoSessions->shuffle();
                $prepoDayIndex = 0;
                
                foreach ($shuffledPrepo as $index => $session) {
                    $newDay = $shuffledDays[$prepoDayIndex % count($shuffledDays)];
                    $prepoDayIndex++;
                    
                    DB::table('class_session_timetables')
                        ->where('session_timetableID', $session->session_timetableID)
                        ->update([
                            'day' => $newDay,
                            'updated_at' => now()
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sessions shuffled successfully. ' . $sessions->count() . ' session(s) redistributed.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error shuffling session timetable: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Swap two sessions in timetable
     */
    public function swapSessionTimetable(Request $request)
    {
        // Check update permission - New format: timetable_update (swap is an update action)
        if (!$this->hasPermission('timetable_update')) {
            return response()->json([
                'error' => 'You do not have permission to update timetables. You need timetable_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');
            $subclassID = $request->input('subclassID');
            $session1ID = $request->input('session1ID');
            $session2ID = $request->input('session2ID');
            
            if (!$schoolID || !$subclassID || !$session1ID || !$session2ID) {
                return response()->json(['success' => false, 'error' => 'All parameters required']);
            }

            // Get definition
            $definition = DB::table('session_timetable_definitions')
                ->where('schoolID', $schoolID)
                ->first();

            if (!$definition) {
                return response()->json(['success' => false, 'error' => 'Timetable definition not found']);
            }

            // Get both sessions
            $session1 = DB::table('class_session_timetables')
                ->where('session_timetableID', $session1ID)
                ->where('definitionID', $definition->definitionID)
                ->where('subclassID', $subclassID)
                ->first();

            $session2 = DB::table('class_session_timetables')
                ->where('session_timetableID', $session2ID)
                ->where('definitionID', $definition->definitionID)
                ->where('subclassID', $subclassID)
                ->first();

            if (!$session1 || !$session2) {
                return response()->json(['success' => false, 'error' => 'One or both sessions not found']);
            }

            // Check if both are prepo or both are regular
            $session1IsPrepo = $session1->is_prepo == 1 || $session1->is_prepo === true;
            $session2IsPrepo = $session2->is_prepo == 1 || $session2->is_prepo === true;

            // If one is prepo and one is regular, don't allow swap
            if ($session1IsPrepo !== $session2IsPrepo) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Cannot swap prepo session with regular session. Please swap sessions of the same type.'
                ]);
            }

            DB::beginTransaction();

            if ($session1IsPrepo && $session2IsPrepo) {
                // Both are prepo sessions - only swap days, keep times
                $tempDay = $session1->day;

                DB::table('class_session_timetables')
                    ->where('session_timetableID', $session1ID)
                    ->update([
                        'day' => $session2->day,
                        'updated_at' => now()
                    ]);

                DB::table('class_session_timetables')
                    ->where('session_timetableID', $session2ID)
                    ->update([
                        'day' => $tempDay,
                        'updated_at' => now()
                    ]);
            } else {
                // Both are regular sessions - swap both days and times
                $tempDay = $session1->day;
                $tempStartTime = $session1->start_time;
                $tempEndTime = $session1->end_time;

                DB::table('class_session_timetables')
                    ->where('session_timetableID', $session1ID)
                    ->update([
                        'day' => $session2->day,
                        'start_time' => $session2->start_time,
                        'end_time' => $session2->end_time,
                        'updated_at' => now()
                    ]);

                DB::table('class_session_timetables')
                    ->where('session_timetableID', $session2ID)
                    ->update([
                        'day' => $tempDay,
                        'start_time' => $tempStartTime,
                        'end_time' => $tempEndTime,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sessions swapped successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error swapping session timetable: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
