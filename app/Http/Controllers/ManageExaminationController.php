<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\Examination;
use App\Models\ExamPaper;
use App\Models\ExamPaperNotification;
use App\Models\ExamPaperQuestion;
use App\Models\ExamPaperOptionalRange;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\Subclass;
use App\Models\SubjectElector;
use App\Models\Teacher;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ManageExaminationController extends Controller
{
    /**
     * Check if user has a specific permission
     */
    public function supervise_exams()
    {
        $userType = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if ($userType !== 'Teacher' || ! $teacherID || ! $schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $assignments = $this->fetchSupervisorAssignments($teacherID, $schoolID);

        return view('Teacher.supervise_exams', [
            'assignments' => $assignments,
        ]);
    }

    public function exam_paper()
    {
        $userType = Session::get('user_type');
        $teacherID = Session::get('teacherID');

        if ($userType !== 'Teacher' || ! $teacherID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get teacher's subjects
        $teacherSubjects = ClassSubject::where('teacherID', $teacherID)
            ->where('status', 'Active')
            ->with(['subject', 'class', 'subclass'])
            ->get();

        // Get schoolID from session
        $schoolID = Session::get('schoolID');
        $school = School::find($schoolID);
        $schoolType = $school && $school->school_type ? $school->school_type : 'Secondary';

        // Get examinations with upload_paper = true for this school
        // Include scheduled, ongoing, and awaiting_results statuses
        $examinations = Examination::where('schoolID', $schoolID)
            ->where('approval_status', 'Approved')
            ->whereIn('status', ['scheduled', 'ongoing', 'awaiting_results'])
            ->where('upload_paper', true)
            ->orderBy('year', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get teacher's uploaded exam papers
        $myExamPapers = ExamPaper::where('teacherID', $teacherID)
            ->with(['examination', 'classSubject.subject', 'classSubject.class', 'classSubject.subclass'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Check for exam rejection notifications
        $rejectionNotifications = [];
        $sessionKeys = Session::all();
        foreach ($sessionKeys as $key => $value) {
            if (strpos($key, "teacher_notification_{$teacherID}_exam_rejected_") === 0) {
                $rejectionNotifications[] = $value;
            }
        }

        return view('Teacher.exam_papers', [
            'teacherSubjects' => $teacherSubjects,
            'examinations' => $examinations,
            'myExamPapers' => $myExamPapers,
            'rejectionNotifications' => $rejectionNotifications,
            'schoolType' => $schoolType,
        ]);
    }

    public function getExamAllowedClasses($examID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (! $userType || ! $schoolID) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        try {
            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json(['error' => 'Examination not found'], 404);
            }

            $allowedClassIds = [];

            if (($exam->exam_category ?? '') === 'special_exams') {
                $allowedClassIds = DB::table('exam_halls')
                    ->where('examID', $examID)
                    ->distinct()
                    ->pluck('classID')
                    ->toArray();
            } else {
                $allowedClassIds = ClassModel::where('schoolID', $schoolID)
                    ->pluck('classID')
                    ->toArray();

                $exceptClassIds = $exam->except_class_ids ?? [];
                if (! empty($exceptClassIds)) {
                    $allowedClassIds = array_values(array_diff($allowedClassIds, $exceptClassIds));
                }
            }

            return response()->json([
                'success' => true,
                'allowed_class_ids' => $allowedClassIds,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load exam classes'], 500);
        }
    }

    private function hasPermission($permissionName)
    {
        $userType = Session::get('user_type');

        // Admin has ALL permissions by default - no need to check specific permissions
        if ($userType === 'Admin') {
            return true;
        }

        // For teachers, check their role permissions
        if ($userType === 'Teacher') {
            $teacherID = Session::get('teacherID');
            if (! $teacherID) {
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

        // For staff, check profession permissions
        if ($userType === 'Staff') {
            $staffID = Session::get('staffID');
            if (!$staffID) {
                return false;
            }

            $professionId = DB::table('other_staff')
                ->where('id', $staffID)
                ->value('profession_id');

            if (!$professionId) {
                return false;
            }

            return DB::table('staff_permissions')
                ->where('profession_id', $professionId)
                ->where('name', $permissionName)
                ->exists();
        }

        return false;
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
            if (! $teacherID) {
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

    /**
     * Auto-update exam status based on dates
     */
    private function updateExamStatusBasedOnDates()
    {
        $today = now()->startOfDay();

        // Update exams that should be ongoing (only if approved)
        Examination::where('status', 'scheduled')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where(function ($query) {
                $query->where('approval_status', 'Approved')
                    ->orWhereNull('approval_status'); // Handle existing exams created before approval system
            })
            ->update(['status' => 'ongoing']);

        // Update exams that have ended - automatically set to awaiting_results
        Examination::where('status', 'ongoing')
            ->where('end_date', '<', $today)
            ->update(['status' => 'awaiting_results']);
    }

    public function manageExamination()
    {
        $user = Session::get('user_type');

        if (! $user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        // Check permission - allow if user has any examination management permission
        // New format: examination_create, examination_update, examination_delete, examination_read_only
        $examinationPermissions = [
            'examination_create',
            'examination_update',
            'examination_delete',
            'examination_read_only',
            // Legacy permissions (for backward compatibility)
            'create_examination',
            'edit_exam',
            'delete_exam',
            'view_exam_details',
            'approve_exam',
            'reject_exam',
            'view_exam_papers',
            'approve_exam_paper',
            'reject_exam_paper',
            'toggle_enter_result',
            'toggle_publish_result',
            'toggle_upload_paper',
            'view_exam_results',
            'update_results_status',
        ];
        
        $hasAnyPermission = false;
        if ($user === 'Admin') {
            $hasAnyPermission = true;
        } else {
            foreach ($examinationPermissions as $permission) {
                if ($this->hasPermission($permission)) {
                    $hasAnyPermission = true;
                    break;
                }
            }
        }
        
        if (! $hasAnyPermission) {
            return redirect()->back()->with('error', 'You do not have permission to access examinations.');
        }
        // Auto-update exam status based on dates
        $this->updateExamStatusBasedOnDates();

        $schoolID = Session::get('schoolID');
        $currentYear = date('Y');

        // Get search parameters
        $search = request()->get('search', '');
        $yearFilter = request()->get('year', '');
        $statusFilter = request()->get('status', '');
        $termFilter = request()->get('term', '');
        $examCategoryFilter = request()->get('exam_category', '');
        $perPage = request()->get('per_page', 12); // Default 12 exams per page

        // Build query
        $query = Examination::where('schoolID', $schoolID);

        // Apply filters
        if (! empty($search)) {
            $query->where('exam_name', 'like', '%'.$search.'%');
        }

        if (! empty($yearFilter)) {
            $query->where('year', $yearFilter);
        }

        if (! empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        if (! empty($termFilter)) {
            $query->where('term', $termFilter);
        }

        if (! empty($examCategoryFilter)) {
            $query->where('exam_category', $examCategoryFilter);
        }

        // Hide rejected exams for users with approve permission (Admin and users with approve_exam permission)
        $hasApprovePermission = ($user === 'Admin') || $this->hasPermission('approve_exam');
        if ($hasApprovePermission) {
            $query->where(function ($q) {
                $q->where('approval_status', '!=', 'Rejected')
                    ->orWhereNull('approval_status'); // Handle existing exams created before approval system
            });
        }

        // Get total count before pagination
        $totalExams = $query->count();

        // Get paginated examinations with statistics
        $examinations = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($exam) {
                // Get total expected students (unique studentIDs in results)
                $expectedStudents = Result::where('examID', $exam->examID)
                    ->distinct('studentID')
                    ->count('studentID');

                // Get students who have taken the exam (have at least one mark filled)
                $studentsWithMarks = Result::where('examID', $exam->examID)
                    ->whereNotNull('marks')
                    ->distinct('studentID')
                    ->count('studentID');

                // Students who haven't taken (expected - with marks)
                $studentsWithoutMarks = $expectedStudents - $studentsWithMarks;

                // Calculate days remaining until exam starts
                $today = now()->startOfDay();
                $startDate = \Carbon\Carbon::parse($exam->start_date)->startOfDay();
                $daysRemaining = $today->diffInDays($startDate, false);

                // Get results status (most common status for this exam)
                $resultsStatus = Result::where('examID', $exam->examID)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->orderByDesc('count')
                    ->first();

                $exam->expected_students = $expectedStudents;
                $exam->students_with_marks = $studentsWithMarks;
                $exam->students_without_marks = $studentsWithoutMarks;
                $exam->days_remaining = $daysRemaining;
                $exam->results_status = $resultsStatus ? $resultsStatus->status : 'not_allowed';

                return $exam;
            });

        // Group by exam_category for display
        $examinationsGrouped = $examinations->groupBy('exam_category');

        // Get subclasses for dropdown
        $subclasses = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('classes.schoolID', $schoolID)
            ->select('subclasses.subclassID', 'subclasses.subclass_name', 'subclasses.stream_code', 'classes.class_name')
            ->orderBy('classes.class_name')
            ->orderBy('subclasses.subclass_name')
            ->get();

        // Get all class subjects for dropdown
        $classSubjects = ClassSubject::with(['subject', 'class'])
            ->whereHas('class', function ($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->where('status', 'Active')
            ->get();

        // Get all classes for Except/Include options
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        // Get available years for filter
        $availableYears = Examination::where('schoolID', $schoolID)
            ->distinct('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Get teacher permissions for the view
        $teacherPermissions = $this->getTeacherPermissions();

        // Get all roles for the school for result approval chain
        $roles = Role::where('schoolID', $schoolID)
            ->orderBy('name')
            ->get();

        // Get closed terms for current year
        $closedTerms = DB::table('terms')
            ->where('schoolID', $schoolID)
            ->where('year', $currentYear)
            ->where('status', 'Closed')
            ->pluck('term_number')
            ->toArray();

        return view('Admin.manage_exam', compact('examinations', 'examinationsGrouped', 'subclasses', 'classSubjects', 'classes', 'currentYear', 'availableYears', 'totalExams', 'search', 'yearFilter', 'statusFilter', 'termFilter', 'examCategoryFilter', 'teacherPermissions', 'roles', 'closedTerms'));
    }

    public function searchExaminations(Request $request)
    {
        $user = Session::get('user_type');

        if (! $user) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        // Check permission - allow if user has any examination management permission
        $examinationPermissions = [
            'create_examination',
            'edit_exam',
            'delete_exam',
            'view_exam_details',
            'approve_exam',
            'reject_exam',
            'view_exam_papers',
            'approve_exam_paper',
            'reject_exam_paper',
            'toggle_enter_result',
            'toggle_publish_result',
            'toggle_upload_paper',
            'view_exam_results',
            'update_results_status',
        ];
        
        $hasAnyPermission = false;
        if ($user === 'Admin') {
            $hasAnyPermission = true;
        } else {
            foreach ($examinationPermissions as $permission) {
                if ($this->hasPermission($permission)) {
                    $hasAnyPermission = true;
                    break;
                }
            }
        }
        
        if (! $hasAnyPermission) {
            return response()->json(['error' => 'You do not have permission to access examinations.'], 403);
        }

        $schoolID = Session::get('schoolID');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 12);
        $search = $request->get('search', '');
        $yearFilter = $request->get('year', '');
        $statusFilter = $request->get('status', '');
        $approvalFilter = $request->get('approval_status', '');
        $examTypeFilter = $request->get('exam_type', '');

        // Build query
        $query = Examination::where('schoolID', $schoolID);

        // Apply filters
        if (! empty($search)) {
            $query->where('exam_name', 'like', '%'.$search.'%');
        }

        if (! empty($yearFilter)) {
            $query->where('year', $yearFilter);
        }

        if (! empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        if (! empty($approvalFilter)) {
            $query->where('approval_status', $approvalFilter);
        }

        if (! empty($examTypeFilter)) {
            $query->where('exam_type', $examTypeFilter);
        }

        // Hide rejected exams for users with approve permission
        $hasApprovePermission = ($user === 'Admin') || $this->hasPermission('approve_exam');
        if ($hasApprovePermission) {
            $query->where(function ($q) {
                $q->where('approval_status', '!=', 'Rejected')
                    ->orWhereNull('approval_status');
            });
        }

        // Get paginated examinations
        $examinations = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($exam) {
                // Get statistics
                $expectedStudents = Result::where('examID', $exam->examID)
                    ->distinct('studentID')
                    ->count('studentID');

                $studentsWithMarks = Result::where('examID', $exam->examID)
                    ->whereNotNull('marks')
                    ->distinct('studentID')
                    ->count('studentID');

                $studentsWithoutMarks = $expectedStudents - $studentsWithMarks;

                $today = now()->startOfDay();
                $startDate = \Carbon\Carbon::parse($exam->start_date)->startOfDay();
                $daysRemaining = $today->diffInDays($startDate, false);

                // Get results status (most common status for this exam)
                $resultsStatus = Result::where('examID', $exam->examID)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->orderByDesc('count')
                    ->first();

                $exam->expected_students = $expectedStudents;
                $exam->students_with_marks = $studentsWithMarks;
                $exam->students_without_marks = $studentsWithoutMarks;
                $exam->days_remaining = $daysRemaining;
                $exam->results_status = $resultsStatus ? $resultsStatus->status : 'not_allowed';

                return $exam;
            });

        // Group by exam_type
        $examinationsGrouped = $examinations->groupBy('exam_type');

        // Return HTML for each exam type tab
        $html = [];
        $examTypes = ['school_wide_all_subjects', 'specific_classes_all_subjects', 'school_wide_specific_subjects', 'specific_classes_specific_subjects'];

        foreach ($examTypes as $examType) {
            $exams = $examinationsGrouped->get($examType, collect());
            $html[$examType] = '';
            foreach ($exams as $exam) {
                $html[$examType] .= '<div class="col-md-6 col-lg-4 mb-4">'.
                    view('Admin.partials.exam_widget', [
                        'exam' => $exam,
                        'user_type' => $user,
                        'teacherPermissions' => Session::get('teacherPermissions', collect()),
                    ])->render().
                    '</div>';
            }
        }

        return response()->json([
            'html' => $html,
            'has_more' => $examinations->hasMorePages(),
            'current_page' => $examinations->currentPage(),
            'total' => $examinations->total(),
            'per_page' => $examinations->perPage(),
        ]);
    }

    public function store(Request $request)
    {
        // Check general permission first
        // Check create permission - New format: examination_create
        if (! $this->hasPermission('examination_create')) {
            return response()->json([
                'error' => 'You do not have permission to create examinations. You need examination_create permission.',
            ], 403);
        }

        try {
            // Get roles count for validation
            $schoolID = Session::get('schoolID');
            $rolesCount = Role::where('schoolID', $schoolID)->count();
            // Add 2 for hard-coded special roles: class_teacher and coordinator
            $maxApprovals = $rolesCount + 2;

            $validator = Validator::make($request->all(), [
                'exam_category' => 'required|in:school_exams,test,special_exams',
                'exam_name' => 'required|string|max:200',
                'exam_name_type' => 'required_if:exam_category,school_exams|in:Midterm,Terminal,Annual Exam',
                'term' => 'required_if:exam_category,school_exams|required_if:exam_category,test|required_if:exam_category,special_exams|in:first_term,second_term',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'year' => 'required|integer|min:2000|max:2100',
                'details' => 'nullable|string',
                'student_shifting_status' => 'required_if:exam_category,school_exams|required_if:exam_category,test|in:none,internal,external',
                'except_class_ids' => 'nullable|array',
                'except_class_ids.*' => 'exists:classes,classID',
                'include_class_ids' => 'required_if:exam_category,special_exams|array|min:1',
                'include_class_ids.*' => 'required|exists:classes,classID',
                'use_result_approval' => 'nullable|boolean',
                'number_of_approvals' => 'required_if:use_result_approval,1|integer|min:1|max:'.$maxApprovals,
                'approval_role_ids' => 'required_if:use_result_approval,1|array',
                'approval_role_ids.*' => [
                    'required',
                    function ($attribute, $value, $fail) use ($schoolID) {
                        // Allow special roles: class_teacher and coordinator
                        if (in_array($value, ['class_teacher', 'coordinator'])) {
                            return;
                        }
                        // For numeric values, check if role exists in database
                        if (is_numeric($value)) {
                            $roleExists = Role::where('id', $value)
                                ->where('schoolID', $schoolID)
                                ->exists();
                            if (!$roleExists) {
                                $fail('The selected role does not exist.');
                            }
                        } else {
                            $fail('Invalid role selected.');
                        }
                    },
                ],
                // Halls
                'hall_name' => 'required|array|min:1',
                'hall_name.*' => 'required|string|max:150',
                'hall_class_id' => 'required|array|min:1',
                'hall_class_id.*' => 'required|integer|exists:classes,classID',
                'hall_capacity' => 'required|array|min:1',
                'hall_capacity.*' => 'required|integer|min:1',
                'hall_gender' => 'required|array|min:1',
                'hall_gender.*' => 'required|in:male,female,both',
            ], [
                'exam_category.required' => 'Please select exam category.',
                'exam_category.in' => 'Invalid exam category selected.',
                'exam_name_type.required_if' => 'Please select examination type for school exams or test.',
                'exam_name_type.in' => 'Invalid examination type selected.',
                'term.required_if' => 'Please select term for school exams or test.',
                'term.in' => 'Invalid term selected.',
                'student_shifting_status.required_if' => 'Please select student shifting status for school exams or test.',
                'include_class_ids.required_if' => 'Please select at least one class for special exams.',
                'include_class_ids.min' => 'Please select at least one class for special exams.',
                'include_class_ids.*.required' => 'Invalid class selected.',
                'include_class_ids.*.exists' => 'One or more selected classes do not exist.',
                'except_class_ids.*.exists' => 'One or more excluded classes do not exist.',
                'number_of_approvals.required_if' => 'Please specify number of approvals.',
                'number_of_approvals.max' => 'Number of approvals cannot exceed the number of available roles ('.$rolesCount.' regular roles + 2 special roles = '.$maxApprovals.' total).',
                'approval_role_ids.required_if' => 'Please select roles for result approval.',
                'approval_role_ids.*.required' => 'Please select a role for each approval step.',
                'approval_role_ids.*.distinct' => 'Each role can only be selected once.',
                'hall_name.required' => 'Please add at least one hall.',
                'hall_name.*.required' => 'Hall name is required.',
                'hall_class_id.*.required' => 'Please select class for each hall.',
                'hall_capacity.*.required' => 'Please provide capacity for each hall.',
                'hall_capacity.*.min' => 'Hall capacity must be at least 1.',
                'hall_gender.*.required' => 'Please select gender for each hall.',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    // Convert field names to readable format
                    $readableField = str_replace('_', ' ', $field);
                    $readableField = ucwords($readableField);

                    // Handle array of messages
                    if (is_array($messages)) {
                        $errors[$readableField] = $messages;
                    } else {
                        $errors[$readableField] = [$messages];
                    }
                }

                return response()->json([
                    'errors' => $errors,
                    'error' => 'Please fix the validation errors below.',
                ], 422);
            }

            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            DB::beginTransaction();

            $userType = Session::get('user_type');
            $teacherID = Session::get('teacherID');

            // Determine initial status and approval_status
            $today = now()->startOfDay();
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();

            // Check if user can approve exams
            // If user is Admin, has approve_exam permission, OR has examination_create permission, exam is approved automatically
            $canApprove = ($userType === 'Admin') || 
                         $this->hasPermission('approve_exam') || 
                         $this->hasPermission('examination_create');

            // If user has create permission, exam is approved automatically (no need for approval)
            $approvalStatus = $canApprove ? 'Approved' : 'Pending';

            // Status logic:
            // - If approved and start date reached: ongoing
            // - If approved but start date not reached: scheduled
            // - If not approved: wait_approval
            if ($approvalStatus === 'Approved') {
                $status = ($startDate <= $today) ? 'ongoing' : 'scheduled';
            } else {
                $status = 'wait_approval';
            }

            // Prepare except_class_ids for school exams and test
            $exceptClassIds = null;
            if (($request->exam_category === 'school_exams' || $request->exam_category === 'test') && $request->has('except_class_ids') && !empty($request->except_class_ids)) {
                $exceptClassIds = $request->except_class_ids;
            }

            // Create the examination
            $examination = Examination::create([
                'exam_name' => $request->exam_name,
                'exam_category' => $request->exam_category,
                'term' => ($request->exam_category === 'school_exams' || $request->exam_category === 'test' || $request->exam_category === 'special_exams') ? $request->term : null,
                'except_class_ids' => $exceptClassIds,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'exam_type' => 'school_wide_all_subjects', // Set default for compatibility
                'schoolID' => $schoolID,
                'year' => $request->year,
                'details' => $request->details,
                'student_shifting_status' => ($request->exam_category === 'school_exams' || $request->exam_category === 'test') ? ($request->student_shifting_status ?? 'none') : 'none',
                'status' => $status,
                'approval_status' => $approvalStatus,
                'created_by' => $userType === 'Teacher' ? $teacherID : null,
            ]);

            // Build and store exam halls
            $hallPayload = $this->buildHallPayload(
                $request->hall_name,
                $request->hall_class_id,
                $request->hall_capacity,
                $request->hall_gender,
                $schoolID,
                $request->exam_category,
                $exceptClassIds,
                $request->include_class_ids ?? []
            );

            foreach ($hallPayload as $hall) {
                DB::table('exam_halls')->insert([
                    'schoolID' => $schoolID,
                    'examID' => $examination->examID,
                    'classID' => $hall['classID'],
                    'hall_name' => $hall['hall_name'],
                    'capacity' => $hall['capacity'],
                    'gender_allowed' => $hall['gender_allowed'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Fetch halls with IDs for allocation
            $examHalls = DB::table('exam_halls')
                ->where('examID', $examination->examID)
                ->get()
                ->map(function ($h) {
                    return [
                        'exam_hallID' => $h->exam_hallID,
                        'classID' => $h->classID,
                        'hall_name' => $h->hall_name,
                        'capacity' => $h->capacity,
                        'gender_allowed' => $h->gender_allowed,
                    ];
                })
                ->toArray();

            // Allocate students into halls
            $this->allocateStudentsToHalls(
                $examination->examID,
                $examHalls,
                $schoolID,
                $request->exam_category,
                $exceptClassIds,
                $request->include_class_ids ?? []
            );

            // Assign supervisors to halls (auto)
            $this->assignSupervisorsToHalls(
                $examination->examID,
                $examHalls,
                $schoolID,
                $examination->exam_name,
                $request->start_date,
                $request->end_date
            );

            // Create results based on exam category
            $results = [];

            if ($request->exam_category === 'school_exams' || $request->exam_category === 'test') {
                // School Exams: All students in the school, except excluded classes
                $query = Student::with('subclass.class')
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active');

                // Exclude students from excepted classes if any
                if ($exceptClassIds && !empty($exceptClassIds)) {
                    $query->whereHas('subclass', function ($q) use ($exceptClassIds) {
                        $q->whereNotIn('classID', $exceptClassIds);
                    });
                }

                $students = $query->get();

                foreach ($students as $student) {
                    // Skip if student doesn't have a subclass
                    if (! $student->subclass || ! $student->subclass->class) {
                        continue;
                    }

                    // Get all class subjects for this student's subclass
                    // Include subjects assigned to the subclass OR to the whole class
                    $classSubjects = ClassSubject::where(function ($query) use ($student) {
                        $query->where('subclassID', $student->subclassID)
                            ->orWhere(function ($q) use ($student) {
                                $q->whereNull('subclassID')
                                    ->where('classID', $student->subclass->classID);
                            });
                    })
                        ->where('status', 'Active')
                        ->get();

                    // Create a result entry for each subject the student takes
                    foreach ($classSubjects as $classSubject) {
                        // Check if subject is optional and if student has elected it
                        // If subject is Required or null, assign normally (as usual)
                        if ($classSubject->student_status === 'Optional') {
                            // Check if student has elected this optional subject
                            $hasElected = SubjectElector::where('studentID', $student->studentID)
                                ->where('classSubjectID', $classSubject->class_subjectID)
                                ->exists();
                            
                            // Skip if student hasn't elected this optional subject
                            if (!$hasElected) {
                                continue;
                            }
                        }
                        // If subject is Required or null, continue normally and assign to results
                        
                        $results[] = [
                            'studentID' => $student->studentID,
                            'examID' => $examination->examID,
                            'subclassID' => $student->subclassID,
                            'class_subjectID' => $classSubject->class_subjectID,
                            'marks' => null,
                            'grade' => null,
                            'remark' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            } elseif ($request->exam_category === 'special_exams') {
                // Special Exams: Only students from included classes
                $includeClassIds = $request->include_class_ids ?? [];
                
                if (empty($includeClassIds)) {
                    throw new \Exception('No classes selected for special examination.');
                }

                // Get all subclasses for the included classes
                $subclassIds = Subclass::whereIn('classID', $includeClassIds)
                    ->pluck('subclassID')
                    ->toArray();

                if (empty($subclassIds)) {
                    throw new \Exception('No subclasses found for the selected classes.');
                }

                $students = Student::with('subclass.class')
                    ->whereIn('subclassID', $subclassIds)
                    ->where('status', 'Active')
                    ->get();

                foreach ($students as $student) {
                    // Skip if student doesn't have a subclass
                    if (! $student->subclass || ! $student->subclass->class) {
                        continue;
                    }

                    // Get all class subjects for this student's subclass
                    // Include subjects assigned to the subclass OR to the whole class
                    $classSubjects = ClassSubject::where(function ($query) use ($student) {
                        $query->where('subclassID', $student->subclassID)
                            ->orWhere(function ($q) use ($student) {
                                $q->whereNull('subclassID')
                                    ->where('classID', $student->subclass->classID);
                            });
                    })
                        ->where('status', 'Active')
                        ->get();

                    // Create a result entry for each subject the student takes
                    foreach ($classSubjects as $classSubject) {
                        // Check if subject is optional and if student has elected it
                        // If subject is Required or null, assign normally (as usual)
                        if ($classSubject->student_status === 'Optional') {
                            // Check if student has elected this optional subject
                            $hasElected = SubjectElector::where('studentID', $student->studentID)
                                ->where('classSubjectID', $classSubject->class_subjectID)
                                ->exists();
                            
                            // Skip if student hasn't elected this optional subject
                            if (!$hasElected) {
                                continue;
                            }
                        }
                        // If subject is Required or null, continue normally and assign to results
                        
                        $results[] = [
                            'studentID' => $student->studentID,
                            'examID' => $examination->examID,
                            'subclassID' => $student->subclassID,
                            'class_subjectID' => $classSubject->class_subjectID,
                            'marks' => null,
                            'grade' => null,
                            'remark' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }


            // Bulk insert results
            if (! empty($results)) {
                Result::insert($results);
            }

            // Create result approval chain if enabled
            if ($request->has('use_result_approval') && $request->use_result_approval == '1' && $request->has('approval_role_ids')) {
                $approvalRoleIds = $request->approval_role_ids;
                $approvalOrder = 1;

                // Get participating subclasses and mainclasses for this exam
                $participatingSubclassIDs = [];
                $participatingMainclassIDs = [];
                
                if ($request->exam_category === 'school_exams' || $request->exam_category === 'test') {
                    // School Exams/Test: Get all subclasses except excluded classes
                    $query = Subclass::whereHas('class', function($q) use ($schoolID, $exceptClassIds) {
                        $q->where('schoolID', $schoolID);
                        if ($exceptClassIds && !empty($exceptClassIds)) {
                            $q->whereNotIn('classID', $exceptClassIds);
                        }
                    });
                    $participatingSubclassIDs = $query->pluck('subclassID')->toArray();
                    $participatingMainclassIDs = $query->distinct()->pluck('classID')->toArray();
                } elseif ($request->exam_category === 'special_exams') {
                    // Special Exams: Get subclasses from included classes
                    $includeClassIds = $request->include_class_ids ?? [];
                    if (!empty($includeClassIds)) {
                        $participatingSubclassIDs = Subclass::whereIn('classID', $includeClassIds)
                            ->pluck('subclassID')
                            ->toArray();
                        $participatingMainclassIDs = $includeClassIds;
                    }
                }

                foreach ($approvalRoleIds as $roleId) {
                    if (!empty($roleId)) {
                        // Check if this is a special role
                        if ($roleId === 'class_teacher_role' || $roleId === 'class_teacher') {
                            // Create ResultApproval with special_role_type
                            $resultApproval = ResultApproval::create([
                                'examID' => $examination->examID,
                                'role_id' => null, // NULL for special roles
                                'special_role_type' => 'class_teacher',
                                'approval_order' => $approvalOrder,
                                'status' => 'pending',
                            ]);
                            
                            // Create class_teacher_approvals for each participating subclass
                            if (!empty($participatingSubclassIDs)) {
                                foreach ($participatingSubclassIDs as $subclassID) {
                                    DB::table('class_teacher_approvals')->insert([
                                        'result_approvalID' => $resultApproval->result_approvalID,
                                        'subclassID' => $subclassID,
                                        'status' => 'pending',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            }
                            
                        } elseif ($roleId === 'coordinator_role' || $roleId === 'coordinator') {
                            // Create ResultApproval with special_role_type
                            $resultApproval = ResultApproval::create([
                                'examID' => $examination->examID,
                                'role_id' => null, // NULL for special roles
                                'special_role_type' => 'coordinator',
                                'approval_order' => $approvalOrder,
                                'status' => 'pending',
                            ]);
                            
                            // Create coordinator_approvals for each participating mainclass
                            if (!empty($participatingMainclassIDs)) {
                                foreach ($participatingMainclassIDs as $mainclassID) {
                                    DB::table('coordinator_approvals')->insert([
                                        'result_approvalID' => $resultApproval->result_approvalID,
                                        'mainclassID' => $mainclassID,
                                        'status' => 'pending',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            }
                            
                        } else {
                            // Regular role - store normally
                            ResultApproval::create([
                                'examID' => $examination->examID,
                                'role_id' => $roleId,
                                'special_role_type' => null,
                                'approval_order' => $approvalOrder,
                                'status' => 'pending',
                            ]);
                        }
                        
                        $approvalOrder++;
                    }
                }

                // Send SMS notifications to teachers with the first approval role
                if (!empty($approvalRoleIds[0])) {
                    $firstRoleId = $approvalRoleIds[0];
                    
                    if ($firstRoleId === 'class_teacher_role' || $firstRoleId === 'class_teacher') {
                        // Send SMS to all class teachers of participating subclasses
                        $classTeachers = DB::table('subclasses')
                            ->join('teachers', 'subclasses.teacherID', '=', 'teachers.id')
                            ->whereIn('subclasses.subclassID', $participatingSubclassIDs)
                            ->where('teachers.schoolID', $schoolID)
                            ->where('teachers.status', 'Active')
                            ->select('teachers.id', 'teachers.phone_number', 'teachers.first_name', 'teachers.last_name')
                            ->distinct()
                            ->get();
                        
                        $smsService = new SmsService();
                        foreach ($classTeachers as $teacher) {
                            $message = "Habari {$teacher->first_name}, umeteuliwa kuapprove matokeo ya mtihani: {$examination->exam_name} kama Class Teacher. Tafadhali fanya approval wakwanza ili wengine waweze kuendelea.";
                            try {
                                $smsService->sendSms($teacher->phone_number, $message);
                            } catch (\Exception $e) {
                                Log::error('Failed to send SMS to class teacher: '.$e->getMessage());
                            }
                        }
                    } elseif ($firstRoleId === 'coordinator_role' || $firstRoleId === 'coordinator') {
                        // Send SMS to all coordinators of participating mainclasses
                        $coordinators = DB::table('classes')
                            ->join('teachers', 'classes.teacherID', '=', 'teachers.id')
                            ->whereIn('classes.classID', $participatingMainclassIDs)
                            ->where('classes.schoolID', $schoolID)
                            ->where('teachers.status', 'Active')
                            ->select('teachers.id', 'teachers.phone_number', 'teachers.first_name', 'teachers.last_name')
                            ->distinct()
                            ->get();
                        
                        $smsService = new SmsService();
                        foreach ($coordinators as $teacher) {
                            $message = "Habari {$teacher->first_name}, umeteuliwa kuapprove matokeo ya mtihani: {$examination->exam_name} kama Coordinator. Tafadhali fanya approval wakwanza ili wengine waweze kuendelea.";
                            try {
                                $smsService->sendSms($teacher->phone_number, $message);
                            } catch (\Exception $e) {
                                Log::error('Failed to send SMS to coordinator: '.$e->getMessage());
                            }
                        }
                    } else {
                        // Regular role - send SMS to teachers with that role
                        $teachersWithRole = DB::table('role_user')
                            ->join('teachers', 'role_user.teacher_id', '=', 'teachers.id')
                            ->where('role_user.role_id', $firstRoleId)
                            ->where('teachers.schoolID', $schoolID)
                            ->where('teachers.status', 'Active')
                            ->select('teachers.id', 'teachers.phone_number', 'teachers.first_name', 'teachers.last_name')
                            ->get();

                        $smsService = new SmsService();
                        foreach ($teachersWithRole as $teacher) {
                            $message = "Habari {$teacher->first_name}, umeteuliwa kuapprove matokeo ya mtihani: {$examination->exam_name}. Tafadhali fanya approval wakwanza ili wengine waweze kuendelea.";
                            try {
                                $smsService->sendSms($teacher->phone_number, $message);
                            } catch (\Exception $e) {
                                Log::error('Failed to send SMS to teacher: '.$e->getMessage());
                            }
                        }
                    }
                }
            }

            // Create exam attendance if enabled
            if ($request->has('enable_exam_attendance') && $request->enable_exam_attendance == '1') {
                $examAttendanceRecords = [];
                
                // Get all students participating in this exam (same logic as results)
                $participatingStudents = [];
                
                if ($request->exam_category === 'school_exams' || $request->exam_category === 'test') {
                    // School Exams: All students in the school, except excluded classes
                    $query = Student::with('subclass.class')
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Active');

                    // Exclude students from excepted classes if any
                    if ($exceptClassIds && !empty($exceptClassIds)) {
                        $query->whereHas('subclass', function ($q) use ($exceptClassIds) {
                            $q->whereNotIn('classID', $exceptClassIds);
                        });
                    }

                    $participatingStudents = $query->get();
                } elseif ($request->exam_category === 'special_exams') {
                    // Special Exams: Only students from included classes
                    $includeClassIds = $request->include_class_ids ?? [];
                    
                    if (!empty($includeClassIds)) {
                        // Get all subclasses for the included classes
                        $subclassIds = Subclass::whereIn('classID', $includeClassIds)
                            ->pluck('subclassID')
                            ->toArray();

                        if (!empty($subclassIds)) {
                            $participatingStudents = Student::with('subclass.class')
                                ->whereIn('subclassID', $subclassIds)
                                ->where('status', 'Active')
                                ->get();
                        }
                    }
                }
                
                // Create exam attendance records for all participating students and their subjects
                foreach ($participatingStudents as $student) {
                    // Skip if student doesn't have a subclass
                    if (! $student->subclass || ! $student->subclass->class) {
                        continue;
                    }

                    // Get all class subjects for this student's subclass
                    // Include subjects assigned to the subclass OR to the whole class
                    $classSubjects = ClassSubject::where(function ($query) use ($student) {
                        $query->where('subclassID', $student->subclassID)
                            ->orWhere(function ($q) use ($student) {
                                $q->whereNull('subclassID')
                                    ->where('classID', $student->subclass->classID);
                            });
                    })
                        ->where('status', 'Active')
                        ->with('subject')
                        ->get();

                    // Create an attendance record for each subject the student takes
                    foreach ($classSubjects as $classSubject) {
                        // Skip if classSubject doesn't have a subject
                        if (! $classSubject->subject) {
                            continue;
                        }
                        
                        // Check if subject is optional and if student has elected it
                        // If subject is Required or null, assign normally (as usual)
                        if ($classSubject->student_status === 'Optional') {
                            // Check if student has elected this optional subject
                            $hasElected = SubjectElector::where('studentID', $student->studentID)
                                ->where('classSubjectID', $classSubject->class_subjectID)
                                ->exists();
                            
                            // Skip if student hasn't elected this optional subject
                            if (!$hasElected) {
                                continue;
                            }
                        }
                        // If subject is Required or null, continue normally and assign to exam attendance
                        
                        $examAttendanceRecords[] = [
                            'examID' => $examination->examID,
                            'studentID' => $student->studentID,
                            'subjectID' => $classSubject->subject->subjectID,
                            'status' => 'Absent', // Default status is Absent, can be updated later
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                
                // Bulk insert exam attendance records
                if (!empty($examAttendanceRecords)) {
                    DB::table('exam_attendance')->insert($examAttendanceRecords);
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Examination "'.$examination->exam_name.'" created successfully!',
                'exam' => $examination,
                'results_count' => count($results),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exam creation error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return detailed error for debugging
            $errorMessage = 'An error occurred while creating the examination.';
            if (config('app.debug')) {
                $errorMessage .= ' Error: '.$e->getMessage().' (Line: '.$e->getLine().')';
            }

            return response()->json([
                'error' => $errorMessage,
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ] : null,
            ], 500);
        }
    }

    public function getSubclasses(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $classID = $request->get('classID');

            $subclassesQuery = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->where('subclasses.status', 'Active');

            // Filter by classID if provided
            if ($classID) {
                $subclassesQuery->where('subclasses.classID', $classID);
            }

            $subclasses = $subclassesQuery
                ->select('subclasses.subclassID', 'subclasses.subclass_name', 'subclasses.stream_code', 'classes.class_name')
                ->orderBy('classes.class_name')
                ->orderBy('subclasses.subclass_name')
                ->get();

            return response()->json([
                'success' => true,
                'subclasses' => $subclasses,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getClassSubjects(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $subclassIds = $request->input('subclass_ids', []);

            $query = ClassSubject::with(['subject', 'class', 'subclass'])
                ->whereHas('class', function ($query) use ($schoolID) {
                    $query->where('schoolID', $schoolID);
                })
                ->where('status', 'Active');

            // If subclass IDs are provided, filter by them
            if (! empty($subclassIds) && is_array($subclassIds)) {
                // Get class IDs for the selected subclasses
                $classIds = DB::table('subclasses')
                    ->whereIn('subclassID', $subclassIds)
                    ->pluck('classID')
                    ->toArray();

                $query->where(function ($q) use ($subclassIds, $classIds) {
                    // Subjects assigned to specific subclasses
                    $q->whereIn('subclassID', $subclassIds)
                      // OR subjects assigned to the whole class (subclassID is null) for those classes
                        ->orWhere(function ($subQ) use ($classIds) {
                            $subQ->whereNull('subclassID')
                                ->whereIn('classID', $classIds);
                        });
                });
            }

            $classSubjects = $query->get()
                ->map(function ($classSubject) {
                    return [
                        'class_subjectID' => $classSubject->class_subjectID,
                        'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                        'subject_code' => $classSubject->subject ? $classSubject->subject->subject_code : null,
                        'class_name' => $classSubject->class ? $classSubject->class->class_name : 'N/A',
                        'subclass_name' => $classSubject->subclass ? $classSubject->subclass->subclass_name : 'N/A',
                        'subclassID' => $classSubject->subclassID,
                        'classID' => $classSubject->classID,
                    ];
                });

            return response()->json([
                'success' => true,
                'class_subjects' => $classSubjects,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getExam($examID)
    {
        // Allow Admin, and Teachers with examination permissions (including read_only)
        $userType = Session::get('user_type');
        
        if ($userType === 'Admin') {
            // Admin has access
        } elseif ($userType === 'Teacher') {
            // Check if teacher has any examination permission (read_only, create, update, delete)
            $canAccess = $this->hasPermission('examination_read_only') ||
                        $this->hasPermission('examination_create') ||
                        $this->hasPermission('examination_update') ||
                        $this->hasPermission('examination_delete') ||
                        $this->hasPermission('view_exam_details'); // Legacy support
            
            if (! $canAccess) {
                return response()->json([
                    'error' => 'You do not have permission to view examinations.',
                ], 403);
            }
        } else {
            return response()->json([
                'error' => 'You do not have permission to view examinations.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            // Format dates for JavaScript
            $examData = $exam->toArray();
            $examData['start_date'] = $exam->start_date ? $exam->start_date->format('Y-m-d') : null;
            $examData['end_date'] = $exam->end_date ? $exam->end_date->format('Y-m-d') : null;
            $examData['enter_result'] = $exam->enter_result ?? false;
            $examData['except_class_ids'] = $exam->except_class_ids ?? [];

            // Derive include_class_ids for special exams using existing results
            $examData['include_class_ids'] = [];
            if (($exam->exam_category ?? '') === 'special_exams') {
                $examData['include_class_ids'] = Result::where('examID', $examID)
                    ->join('subclasses', 'results.subclassID', '=', 'subclasses.subclassID')
                    ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                    ->where('classes.schoolID', $schoolID)
                    ->distinct()
                    ->pluck('classes.classID')
                    ->toArray();
            }

            // Approval chain
            $approvalRoles = ResultApproval::where('examID', $examID)
                ->orderBy('approval_order')
                ->get()
                ->map(function ($approval) {
                    return $approval->special_role_type ?: $approval->role_id;
                })
                ->filter()
                ->values()
                ->toArray();

            $examData['approval_role_ids'] = $approvalRoles;
            $examData['use_result_approval'] = count($approvalRoles) > 0;
            $examData['number_of_approvals'] = count($approvalRoles);
            $examData['has_exam_attendance'] = DB::table('exam_attendance')->where('examID', $examID)->exists();

            // Exam halls
            $examData['exam_halls'] = DB::table('exam_halls')
                ->where('examID', $examID)
                ->select(
                    'exam_hallID',
                    'hall_name',
                    'classID',
                    'capacity',
                    'gender_allowed'
                )
                ->get();

            // Determine test_type for tests
            $examData['test_type'] = null;
            if (($exam->exam_category ?? '') === 'test') {
                if ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week') {
                    $examData['test_type'] = 'weekly_test';
                } elseif ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month') {
                    $examData['test_type'] = 'monthly_test';
                } else {
                    $examData['test_type'] = 'other_test';
                }
            }

            if (($exam->exam_category ?? '') === 'school_exams') {
                $examData['exam_name_type'] = $exam->exam_name;
            }

            return response()->json([
                'success' => true,
                'exam' => $examData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student counts per class (total/male/female) for hall allocation checks.
     */
    public function getClassStudentCounts($classID)
    {
        $user = Session::get('user_type');
        if (! $user) {
            return response()->json([
                'error' => 'Unauthorized access',
            ], 401);
        }

        $schoolID = Session::get('schoolID');
        if (! $schoolID) {
            return response()->json([
                'error' => 'School ID not found in session.',
            ], 400);
        }

        try {
            $class = ClassModel::where('classID', $classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $class) {
                return response()->json([
                    'error' => 'Class not found',
                ], 404);
            }

            // Get subclasses of this class
            $subclassIds = Subclass::where('classID', $classID)->pluck('subclassID')->toArray();

            $total = Student::whereIn('subclassID', $subclassIds)
                ->where('status', 'Active')
                ->count();

            $male = Student::whereIn('subclassID', $subclassIds)
                ->where('status', 'Active')
                ->where('gender', 'Male')
                ->count();

            $female = Student::whereIn('subclassID', $subclassIds)
                ->where('status', 'Active')
                ->where('gender', 'Female')
                ->count();

            return response()->json([
                'success' => true,
                'classID' => $classID,
                'counts' => [
                    'total' => $total,
                    'male' => $male,
                    'female' => $female,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $examID)
    {
        // Check permission
        // Check update permission - New format: examination_update
        if (! $this->hasPermission('examination_update')) {
            return response()->json([
                'error' => 'You do not have permission to update examinations. You need examination_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $rolesCount = Role::where('schoolID', $schoolID)->count();
            $maxApprovals = $rolesCount + 2; // include class_teacher & coordinator

            $validator = Validator::make($request->all(), [
                'exam_category' => 'required|in:school_exams,test,special_exams',
                'exam_name' => 'required|string|max:200',
                'exam_name_type' => 'required_if:exam_category,school_exams|in:Midterm,Terminal,Annual Exam',
                'term' => 'required_if:exam_category,school_exams|required_if:exam_category,test|required_if:exam_category,special_exams|in:first_term,second_term',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'year' => 'required|integer|min:2000|max:2100',
                'details' => 'nullable|string',
                'student_shifting_status' => 'required_if:exam_category,school_exams|required_if:exam_category,test|in:none,internal,external',
                'except_class_ids' => 'nullable|array',
                'except_class_ids.*' => 'exists:classes,classID',
                'include_class_ids' => 'required_if:exam_category,special_exams|array|min:1',
                'include_class_ids.*' => 'required|exists:classes,classID',
                'use_result_approval' => 'nullable|boolean',
                'number_of_approvals' => 'required_if:use_result_approval,1|integer|min:1|max:'.$maxApprovals,
                'approval_role_ids' => 'required_if:use_result_approval,1|array',
                'approval_role_ids.*' => [
                    'required',
                    function ($attribute, $value, $fail) use ($schoolID) {
                        if (in_array($value, ['class_teacher', 'coordinator', 'class_teacher_role', 'coordinator_role'])) {
                            return;
                        }
                        if (is_numeric($value)) {
                            $roleExists = Role::where('id', $value)
                                ->where('schoolID', $schoolID)
                                ->exists();
                            if (! $roleExists) {
                                $fail('The selected role does not exist.');
                            }
                        } else {
                            $fail('Invalid role selected.');
                        }
                    },
                ],
                // Halls (edit prefix)
                'edit_hall_name' => 'required|array|min:1',
                'edit_hall_name.*' => 'required|string|max:150',
                'edit_hall_class_id' => 'required|array|min:1',
                'edit_hall_class_id.*' => 'required|integer|exists:classes,classID',
                'edit_hall_capacity' => 'required|array|min:1',
                'edit_hall_capacity.*' => 'required|integer|min:1',
                'edit_hall_gender' => 'required|array|min:1',
                'edit_hall_gender.*' => 'required|in:male,female,both',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = is_array($messages) ? $messages : [$messages];
                }

                return response()->json(['errors' => $errors], 422);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            DB::beginTransaction();

            // Derive exam name based on category
            $examCategory = $request->exam_category;
            $examName = $request->exam_name;
            if ($examCategory === 'school_exams') {
                $examName = $request->exam_name_type;
            } elseif ($examCategory === 'test') {
                $testType = $request->input('test_type');
                if ($testType === 'weekly_test') {
                    $examName = 'Weekly Test';
                } elseif ($testType === 'monthly_test') {
                    $examName = 'Monthly Test';
                }
            }

            $exceptClassIds = null;
            if (($examCategory === 'school_exams' || $examCategory === 'test') && $request->has('except_class_ids') && ! empty($request->except_class_ids)) {
                $exceptClassIds = $request->except_class_ids;
            }
            $includeClassIds = ($examCategory === 'special_exams') ? ($request->include_class_ids ?? []) : [];

            // Keep status consistent with creation rules.
            // If date is pushed to future, force scheduled when approved.
            $approvalStatus = $exam->approval_status ?? 'Pending';
            $status = $exam->status ?? 'wait_approval';
            $today = now()->startOfDay();
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();

            if ($approvalStatus !== 'Approved') {
                $status = 'wait_approval';
            } else {
                if ($startDate > $today) {
                    // Current date is before start date -> scheduled
                    $status = 'scheduled';
                } else {
                    // Start date reached/past -> keep or set to ongoing for active states
                    if (in_array($status, ['scheduled', 'wait_approval', 'ongoing'])) {
                        $status = 'ongoing';
                    }
                }
            }

            $exam->update([
                'exam_name' => $examName,
                'exam_category' => $examCategory,
                'term' => ($examCategory === 'school_exams' || $examCategory === 'test' || $examCategory === 'special_exams') ? $request->term : null,
                'except_class_ids' => $exceptClassIds,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'exam_type' => 'school_wide_all_subjects',
                'year' => $request->year,
                'details' => $request->details,
                'student_shifting_status' => ($examCategory === 'school_exams' || $examCategory === 'test') ? ($request->student_shifting_status ?? 'none') : 'none',
                'status' => $status,
            ]);

            // Rebuild halls: clear old, insert new, then allocate students
            DB::table('student_exam_halls')->where('examID', $examID)->delete();
            DB::table('exam_hall_supervisors')->where('examID', $examID)->delete();
            DB::table('exam_halls')->where('examID', $examID)->delete();

            $hallPayload = $this->buildHallPayload(
                $request->edit_hall_name,
                $request->edit_hall_class_id,
                $request->edit_hall_capacity,
                $request->edit_hall_gender,
                $schoolID,
                $examCategory,
                $exceptClassIds,
                $includeClassIds
            );

            foreach ($hallPayload as $hall) {
                DB::table('exam_halls')->insert([
                    'schoolID' => $schoolID,
                    'examID' => $examID,
                    'classID' => $hall['classID'],
                    'hall_name' => $hall['hall_name'],
                    'capacity' => $hall['capacity'],
                    'gender_allowed' => $hall['gender_allowed'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $examHalls = DB::table('exam_halls')
                ->where('examID', $examID)
                ->get()
                ->map(function ($h) {
                    return [
                        'exam_hallID' => $h->exam_hallID,
                        'classID' => $h->classID,
                        'hall_name' => $h->hall_name,
                        'capacity' => $h->capacity,
                        'gender_allowed' => $h->gender_allowed,
                    ];
                })
                ->toArray();

            $this->allocateStudentsToHalls(
                $examID,
                $examHalls,
                $schoolID,
                $examCategory,
                $exceptClassIds,
                $includeClassIds
            );

            $this->assignSupervisorsToHalls(
                $examID,
                $examHalls,
                $schoolID,
                $examName,
                $request->start_date,
                $request->end_date
            );

            // Sync results to reflect updated inclusion/exclusion
            if (in_array($examCategory, ['school_exams', 'test', 'special_exams'])) {
                $existingResults = Result::where('examID', $examID)->get();
                $existingKeys = [];
                foreach ($existingResults as $res) {
                    $existingKeys[$res->studentID.'-'.$res->class_subjectID] = $res->resultID;
                }

                $desiredKeys = [];
                $rowsToInsert = [];

                // Helper to process participating students
                $processStudents = function ($students) use (&$desiredKeys, &$rowsToInsert, $examID, &$existingKeys) {
                    foreach ($students as $student) {
                        if (! $student->subclass || ! $student->subclass->class) {
                            continue;
                        }

                        $classSubjects = ClassSubject::where(function ($query) use ($student) {
                            $query->where('subclassID', $student->subclassID)
                                ->orWhere(function ($q) use ($student) {
                                    $q->whereNull('subclassID')
                                        ->where('classID', $student->subclass->classID);
                                });
                        })
                            ->where('status', 'Active')
                            ->get();

                        foreach ($classSubjects as $classSubject) {
                            // Check if subject is optional and if student has elected it
                            // If subject is Required or null, assign normally (as usual)
                            if ($classSubject->student_status === 'Optional') {
                                // Check if student has elected this optional subject
                                $hasElected = SubjectElector::where('studentID', $student->studentID)
                                    ->where('classSubjectID', $classSubject->class_subjectID)
                                    ->exists();
                                
                                // Skip if student hasn't elected this optional subject
                                if (!$hasElected) {
                                    continue;
                                }
                            }
                            // If subject is Required or null, continue normally and assign to results
                            
                            $key = $student->studentID.'-'.$classSubject->class_subjectID;
                            $desiredKeys[$key] = true;
                            if (! isset($existingKeys[$key])) {
                                $rowsToInsert[] = [
                                    'studentID' => $student->studentID,
                                    'examID' => $examID,
                                    'subclassID' => $student->subclassID,
                                    'class_subjectID' => $classSubject->class_subjectID,
                                    'marks' => null,
                                    'grade' => null,
                                    'remark' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                };

                if ($examCategory === 'school_exams' || $examCategory === 'test') {
                    $students = Student::with('subclass.class')
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereHas('subclass.class', function ($q) use ($exceptClassIds) {
                            if ($exceptClassIds && ! empty($exceptClassIds)) {
                                $q->whereNotIn('classID', $exceptClassIds);
                            }
                        })
                        ->get();

                    $processStudents($students);
                } elseif ($examCategory === 'special_exams') {
                    if (! empty($includeClassIds)) {
                        $subclassIds = Subclass::whereIn('classID', $includeClassIds)
                            ->pluck('subclassID')
                            ->toArray();

                        if (! empty($subclassIds)) {
                            $students = Student::with('subclass.class')
                                ->whereIn('subclassID', $subclassIds)
                                ->where('status', 'Active')
                                ->get();
                            $processStudents($students);
                        }
                    }
                }

                // Delete results for students/subjects no longer participating
                $resultIdsToDelete = [];
                foreach ($existingResults as $res) {
                    $key = $res->studentID.'-'.$res->class_subjectID;
                    if (! isset($desiredKeys[$key])) {
                        $resultIdsToDelete[] = $res->resultID;
                    }
                }
                if (! empty($resultIdsToDelete)) {
                    Result::whereIn('resultID', $resultIdsToDelete)->delete();
                }

                if (! empty($rowsToInsert)) {
                    Result::insert($rowsToInsert);
                }
            }

            // Rebuild approval chain
            $resultApprovalIds = ResultApproval::where('examID', $examID)->pluck('result_approvalID')->toArray();
            if (! empty($resultApprovalIds)) {
                DB::table('class_teacher_approvals')->whereIn('result_approvalID', $resultApprovalIds)->delete();
                DB::table('coordinator_approvals')->whereIn('result_approvalID', $resultApprovalIds)->delete();
                ResultApproval::whereIn('result_approvalID', $resultApprovalIds)->delete();
            }

            if ($request->has('use_result_approval') && $request->use_result_approval == '1' && $request->has('approval_role_ids')) {
                $approvalRoleIds = $request->approval_role_ids;
                $approvalOrder = 1;

                $participatingSubclassIDs = [];
                $participatingMainclassIDs = [];

                if ($examCategory === 'school_exams' || $examCategory === 'test') {
                    $query = Subclass::whereHas('class', function ($q) use ($schoolID, $exceptClassIds) {
                        $q->where('schoolID', $schoolID);
                        if ($exceptClassIds && ! empty($exceptClassIds)) {
                            $q->whereNotIn('classID', $exceptClassIds);
                        }
                    });
                    $participatingSubclassIDs = $query->pluck('subclassID')->toArray();
                    $participatingMainclassIDs = $query->distinct()->pluck('classID')->toArray();
                } elseif ($examCategory === 'special_exams') {
                    $includeClassIds = $request->include_class_ids ?? [];
                    if (! empty($includeClassIds)) {
                        $participatingSubclassIDs = Subclass::whereIn('classID', $includeClassIds)
                            ->pluck('subclassID')
                            ->toArray();
                        $participatingMainclassIDs = $includeClassIds;
                    }
                }

                foreach ($approvalRoleIds as $roleId) {
                    if (! empty($roleId)) {
                        if (in_array($roleId, ['class_teacher_role', 'class_teacher'])) {
                            $resultApproval = ResultApproval::create([
                                'examID' => $exam->examID,
                                'role_id' => null,
                                'special_role_type' => 'class_teacher',
                                'approval_order' => $approvalOrder,
                                'status' => 'pending',
                            ]);

                            if (! empty($participatingSubclassIDs)) {
                                foreach ($participatingSubclassIDs as $subclassID) {
                                    DB::table('class_teacher_approvals')->insert([
                                        'result_approvalID' => $resultApproval->result_approvalID,
                                        'subclassID' => $subclassID,
                                        'status' => 'pending',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            }
                        } elseif (in_array($roleId, ['coordinator_role', 'coordinator'])) {
                            $resultApproval = ResultApproval::create([
                                'examID' => $exam->examID,
                                'role_id' => null,
                                'special_role_type' => 'coordinator',
                                'approval_order' => $approvalOrder,
                                'status' => 'pending',
                            ]);

                            if (! empty($participatingMainclassIDs)) {
                                foreach ($participatingMainclassIDs as $mainclassID) {
                                    DB::table('coordinator_approvals')->insert([
                                        'result_approvalID' => $resultApproval->result_approvalID,
                                        'mainclassID' => $mainclassID,
                                        'status' => 'pending',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            }
                        } else {
                            ResultApproval::create([
                                'examID' => $exam->examID,
                                'role_id' => $roleId,
                                'special_role_type' => null,
                                'approval_order' => $approvalOrder,
                                'status' => 'pending',
                            ]);
                        }

                        $approvalOrder++;
                    }
                }
            }

            // Rebuild exam attendance to mirror creation logic
            DB::table('exam_attendance')->where('examID', $examID)->delete();

            if ($request->has('enable_exam_attendance') && $request->enable_exam_attendance == '1') {
                $examAttendanceRecords = [];
                $participatingStudents = [];

                if ($examCategory === 'school_exams' || $examCategory === 'test') {
                    $query = Student::with('subclass.class')
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Active');

                    if ($exceptClassIds && ! empty($exceptClassIds)) {
                        $query->whereHas('subclass', function ($q) use ($exceptClassIds) {
                            $q->whereNotIn('classID', $exceptClassIds);
                        });
                    }

                    $participatingStudents = $query->get();
                } elseif ($examCategory === 'special_exams') {
                    $includeClassIds = $request->include_class_ids ?? [];

                    if (! empty($includeClassIds)) {
                        $subclassIds = Subclass::whereIn('classID', $includeClassIds)
                            ->pluck('subclassID')
                            ->toArray();

                        if (! empty($subclassIds)) {
                            $participatingStudents = Student::with('subclass.class')
                                ->whereIn('subclassID', $subclassIds)
                                ->where('status', 'Active')
                                ->get();
                        }
                    }
                }

                foreach ($participatingStudents as $student) {
                    if (! $student->subclass || ! $student->subclass->class) {
                        continue;
                    }

                    $classSubjects = ClassSubject::where(function ($query) use ($student) {
                        $query->where('subclassID', $student->subclassID)
                            ->orWhere(function ($q) use ($student) {
                                $q->whereNull('subclassID')
                                    ->where('classID', $student->subclass->classID);
                            });
                    })
                        ->where('status', 'Active')
                        ->with('subject')
                        ->get();

                    foreach ($classSubjects as $classSubject) {
                        if (! $classSubject->subject) {
                            continue;
                        }

                        // Check if subject is optional and if student has elected it
                        // If subject is Required or null, assign normally (as usual)
                        if ($classSubject->student_status === 'Optional') {
                            // Check if student has elected this optional subject
                            $hasElected = SubjectElector::where('studentID', $student->studentID)
                                ->where('classSubjectID', $classSubject->class_subjectID)
                                ->exists();
                            
                            // Skip if student hasn't elected this optional subject
                            if (!$hasElected) {
                                continue;
                            }
                        }
                        // If subject is Required or null, continue normally and assign to exam attendance

                        $examAttendanceRecords[] = [
                            'examID' => $exam->examID,
                            'studentID' => $student->studentID,
                            'subjectID' => $classSubject->subject->subjectID,
                            'status' => 'Absent',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (! empty($examAttendanceRecords)) {
                    DB::table('exam_attendance')->insert($examAttendanceRecords);
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Examination updated successfully!',
                'exam' => $exam,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function changeStatus(Request $request, $examID)
    {
        // Check permission
        if (! $this->hasPermission('update_results_status')) {
            return response()->json([
                'error' => 'You do not have permission to change examination status.',
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:scheduled,ongoing,awaiting_results,results_available',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            // Only allow changing to results_available manually
            // Other statuses (scheduled, ongoing, awaiting_results) are automatic
            if ($request->status !== 'results_available') {
                return response()->json([
                    'error' => 'Only results_available status can be set manually. Other statuses are updated automatically.',
                ], 422);
            }

            // Only allow if exam is in awaiting_results status
            if ($exam->status !== 'awaiting_results') {
                return response()->json([
                    'error' => 'Results can only be made available for exams that have ended (awaiting_results status).',
                ], 422);
            }

            $exam->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => 'Examination status updated successfully!',
                'exam' => $exam,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy($examID)
    {
        $schoolID = Session::get('schoolID');

        if (! $schoolID) {
            return response()->json([
                'error' => 'School ID not found in session.',
            ], 400);
        }

        // Get exam to check if it's rejected
        $exam = Examination::where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->first();

        if (! $exam) {
            return response()->json([
                'error' => 'Examination not found.',
            ], 404);
        }

        // Allow delete if exam is rejected, otherwise check permission
        $isRejected = ($exam->approval_status ?? 'Pending') == 'Rejected';

        // Check delete permission - New format: examination_delete
        // Note: Rejected exams can be deleted without permission (cleanup)
        if (! $isRejected && ! $this->hasPermission('examination_delete')) {
            return response()->json([
                'error' => 'You do not have permission to delete examinations. You need examination_delete permission. Only rejected examinations can be deleted without permission.',
            ], 403);
        }

        try {

            DB::beginTransaction();

            // Delete in correct order to handle dependencies
            
            // 1. Delete student exam halls (depends on exam_halls)
            DB::table('student_exam_halls')->where('examID', $examID)->delete();
            
            // 2. Delete exam hall supervisors (depends on exam_halls)
            DB::table('exam_hall_supervisors')->where('examID', $examID)->delete();
            
            // 3. Delete exam halls
            DB::table('exam_halls')->where('examID', $examID)->delete();
            
            // 4. Delete exam attendance
            DB::table('exam_attendance')->where('examID', $examID)->delete();
            
            // 5. Delete exam papers (and their files)
            $examPapers = DB::table('exam_papers')->where('examID', $examID)->get();
            foreach ($examPapers as $examPaper) {
                if ($examPaper->file_path && Storage::disk('public')->exists($examPaper->file_path)) {
                    Storage::disk('public')->delete($examPaper->file_path);
                }
            }
            DB::table('exam_papers')->where('examID', $examID)->delete();
            
            // 6. Delete result approvals and related data
            // First get result approval IDs
            $resultApprovalIDs = DB::table('result_approvals')->where('examID', $examID)->pluck('result_approvalID')->toArray();
            
            // Delete related coordinator approvals
            if (!empty($resultApprovalIDs)) {
                DB::table('coordinator_approvals')->whereIn('result_approvalID', $resultApprovalIDs)->delete();
            }
            
            // Delete related class teacher approvals
            if (!empty($resultApprovalIDs)) {
                DB::table('class_teacher_approvals')->whereIn('result_approvalID', $resultApprovalIDs)->delete();
            }
            
            // Delete result approvals
            DB::table('result_approvals')->where('examID', $examID)->delete();
            
            // 7. Delete exam supervise teacher
            DB::table('exam_supervise_teacher')->where('examID', $examID)->delete();
            
            // 8. Delete exam timetables (class-specific)
            DB::table('exam_timetables')->where('examID', $examID)->delete();
            
            // 9. Delete exam timetable (school-wide)
            DB::table('exam_timetable')->where('examID', $examID)->delete();
            
            // 10. Delete all results for this exam
            Result::where('examID', $examID)->delete();

            // 11. Delete the examination
            $exam->delete();

            DB::commit();

            return response()->json([
                'success' => 'Examination and all related data deleted successfully!',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function approveExam(Request $request, $examID)
    {
        $approvalStatus = $request->input('approval_status');
        
        // Check permission - Update action: examination_update (approve/reject updates exam status)
        if (! $this->hasPermission('examination_update')) {
            return response()->json([
                'error' => 'You do not have permission to approve/reject examinations. You need examination_update permission.',
            ], 403);
        } else {
            return response()->json([
                'error' => 'Invalid approval status.',
            ], 422);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'approval_status' => 'required|in:Approved,Rejected',
                'rejection_reason' => 'required_if:approval_status,Rejected|nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $rejectionReason = $request->input('rejection_reason');

            // If approved, update status based on dates
            $today = now()->startOfDay();
            $startDate = \Carbon\Carbon::parse($exam->start_date)->startOfDay();

            $newStatus = $exam->status;
            if ($approvalStatus === 'Approved') {
                // If exam should start today or has started, set to ongoing
                if ($startDate <= $today) {
                    $newStatus = 'ongoing';
                } else {
                    // If exam is approved but not yet started, set to scheduled
                    $newStatus = 'scheduled';
                }
            } elseif ($approvalStatus === 'Rejected') {
                // If rejected, keep as wait_approval
                $newStatus = 'wait_approval';
            }
            // If status is wait_approval and still pending, keep as wait_approval

            // If rejected, delete the examination and notify creator
            if ($approvalStatus === 'Rejected') {
                DB::beginTransaction();
                try {
                    // Get creator information before deletion
                    $creatorID = $exam->created_by;
                    $examName = $exam->exam_name;
                    $creatorName = null;
                    
                    // Get creator details if exists
                    if ($creatorID) {
                        try {
                            $creator = Teacher::find($creatorID);
                            if ($creator) {
                                $creatorName = ($creator->first_name ?? '') . ' ' . ($creator->last_name ?? '');
                                $creatorName = trim($creatorName);
                                
                                // Store notification message in session for creator
                                // This will be shown when creator logs in next time
                                $notificationKey = "exam_rejected_{$examID}_" . time();
                                Session::put("teacher_notification_{$creatorID}_{$notificationKey}", [
                                    'type' => 'exam_rejected',
                                    'message' => "Your examination '{$examName}' has been rejected and deleted.",
                                    'reason' => $rejectionReason,
                                    'exam_name' => $examName,
                                    'created_at' => now()->toDateTimeString()
                                ]);
                                
                                // Send SMS to creator
                                if ($creator->phone_number) {
                                    try {
                                        $smsService = new SmsService();
                                        $school = \App\Models\School::find($schoolID);
                                        $schoolName = $school ? $school->school_name : 'ShuleLink';
                                        
                                        $smsMessage = "{$schoolName}. Mwalimu {$creatorName}, mtihani wako '{$examName}' umekataliwa na kufutwa. Sababu: {$rejectionReason}. Asante";
                                        
                                        $smsResult = $smsService->sendSms($creator->phone_number, $smsMessage);
                                        
                                        if (!$smsResult['success']) {
                                            Log::warning("Failed to send SMS to creator {$creatorID}: " . ($smsResult['message'] ?? 'Unknown error'));
                                        }
                                    } catch (\Exception $smsException) {
                                        // Log SMS error but don't fail the rejection
                                        Log::error('Error sending SMS to exam creator: '.$smsException->getMessage());
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            // Log error but don't fail the rejection
                            Log::error('Error getting creator info: '.$e->getMessage());
                        }
                    }
                    
                    // Delete the examination
                    $exam->delete();
                    
                    DB::commit();
                    
                    $successMessage = "Examination '{$examName}' has been rejected and deleted.";
                    if ($creatorID && $creatorName) {
                        $successMessage .= " {$creatorName} (the creator) will be notified when they log in.";
                    } elseif ($creatorID) {
                        $successMessage .= " The creator will be notified when they log in.";
                    }
                    
                    return response()->json([
                        'success' => $successMessage,
                        'deleted' => true,
                        'creator_notified' => $creatorID ? true : false,
                    ], 200);
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'An error occurred while rejecting the examination: '.$e->getMessage(),
                    ], 500);
                }
            }

            $updateData = [
                'approval_status' => $approvalStatus,
                'status' => $newStatus,
            ];

            // Clear rejection reason if approved
            $updateData['rejection_reason'] = null;

            $exam->update($updateData);

            return response()->json([
                'success' => 'Examination '.strtolower($approvalStatus).' successfully!',
                'exam' => $exam,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getExamDetails($examID)
    {
        try {
            // Check permission - allow read_only, create, update, delete permissions for viewing
            $userType = Session::get('user_type');
            $canView = false;
            
            if ($userType === 'Admin') {
                $canView = true;
            } else {
                // Check if user has any examination permission (read_only, create, update, delete)
                $canView = $this->hasPermission('examination_read_only') ||
                          $this->hasPermission('examination_create') ||
                          $this->hasPermission('examination_update') ||
                          $this->hasPermission('examination_delete') ||
                          $this->hasPermission('view_exam_details'); // Legacy support
            }
            
            if (! $canView) {
                return response()->json([
                    'error' => 'You do not have permission to view examination details.',
                ], 403);
            }

            $schoolID = Session::get('schoolID');
            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            // Get exam
            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            // Get all results for this exam
            $results = DB::table('results')
                ->where('examID', $examID)
                ->select('subclassID', 'class_subjectID', 'studentID')
                ->distinct()
                ->get();

            // Group by subclass
            $subclassGroups = [];
            foreach ($results as $result) {
                if (! $result->subclassID) {
                    continue;
                }

                if (! isset($subclassGroups[$result->subclassID])) {
                    $subclassGroups[$result->subclassID] = [
                        'subclassID' => $result->subclassID,
                        'students' => [],
                        'subjects' => [],
                    ];
                }

                // Add student if not already added
                if (! in_array($result->studentID, $subclassGroups[$result->subclassID]['students'])) {
                    $subclassGroups[$result->subclassID]['students'][] = $result->studentID;
                }

                // Add subject if not already added
                if ($result->class_subjectID && ! in_array($result->class_subjectID, $subclassGroups[$result->subclassID]['subjects'])) {
                    $subclassGroups[$result->subclassID]['subjects'][] = $result->class_subjectID;
                }
            }

            // Get detailed information for each subclass
            $examDetails = [];
            foreach ($subclassGroups as $subclassID => $group) {
                $subclass = Subclass::with(['class', 'classTeacher'])
                    ->find($subclassID);

                if (! $subclass) {
                    continue;
                }

                // Get subjects details with teachers
                $subjectIds = $group['subjects'];
                $subjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->leftJoin('teachers', 'class_subjects.teacherID', '=', 'teachers.id')
                    ->whereIn('class_subjects.class_subjectID', $subjectIds)
                    ->select(
                        'class_subjects.class_subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code',
                        'teachers.id as teacher_id',
                        'teachers.first_name as teacher_first_name',
                        'teachers.last_name as teacher_last_name',
                        'teachers.middle_name as teacher_middle_name'
                    )
                    ->orderBy('school_subjects.subject_name')
                    ->get();

                // Get students who have completed (have at least one mark) and not completed
                $studentsWithMarks = DB::table('results')
                    ->where('examID', $examID)
                    ->where('subclassID', $subclassID)
                    ->whereNotNull('marks')
                    ->where('marks', '!=', '')
                    ->distinct('studentID')
                    ->count('studentID');

                $studentsWithoutMarks = count($group['students']) - $studentsWithMarks;

                $examDetails[] = [
                    'subclassID' => $subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'class_name' => $subclass->class ? $subclass->class->class_name : 'N/A',
                    'stream_code' => $subclass->stream_code ?? '',
                    'teacher_name' => $subclass->classTeacher ? $subclass->classTeacher->first_name.' '.$subclass->classTeacher->last_name : 'N/A',
                    'subjects' => $subjects,
                    'subjects_count' => count($subjects),
                    'students_count' => count($group['students']),
                    'students_with_marks' => $studentsWithMarks,
                    'students_without_marks' => $studentsWithoutMarks,
                ];
            }

            // Sort by class name and subclass name
            usort($examDetails, function ($a, $b) {
                $classCompare = strcmp($a['class_name'], $b['class_name']);
                if ($classCompare !== 0) {
                    return $classCompare;
                }

                return strcmp($a['subclass_name'], $b['subclass_name']);
            });

            // Calculate total unique subjects across all classes
            $allSubjectIds = [];
            foreach ($examDetails as $classItem) {
                foreach ($classItem['subjects'] as $subject) {
                    if (! in_array($subject->class_subjectID, $allSubjectIds)) {
                        $allSubjectIds[] = $subject->class_subjectID;
                    }
                }
            }

            // Check if exam has ended to show attendance information
            $today = now()->startOfDay();
            $examEndDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
            $examHasEnded = $examEndDate < $today;
            
            // Get attendance statistics if exam has ended
            $attendanceStats = null;
            if ($examHasEnded) {
                // Get all unique students who should take the exam (count distinct students)
                $expectedStudents = DB::table('exam_attendance')
                    ->where('examID', $examID)
                    ->select(DB::raw('COUNT(DISTINCT studentID) as count'))
                    ->first()
                    ->count ?? 0;
                
                // Get unique students who were present (attended at least one subject)
                // A student is considered "Present" if they have at least one "Present" status record
                $presentStudentIDs = DB::table('exam_attendance')
                    ->where('examID', $examID)
                    ->where('status', 'Present')
                    ->distinct()
                    ->pluck('studentID')
                    ->toArray();
                
                $presentStudents = count($presentStudentIDs);
                
                // Get students who were absent (didn't attend any subject)
                // A student is "Absent" if they don't have any "Present" status record
                $absentStudents = $expectedStudents - $presentStudents;
                
                // Get attendance by subclass
                $attendanceBySubclass = [];
                foreach ($examDetails as $classItem) {
                    $subclassID = $classItem['subclassID'];
                    
                    // Get unique students in this subclass who should take the exam
                    $subclassExpected = DB::table('exam_attendance')
                        ->join('students', 'exam_attendance.studentID', '=', 'students.studentID')
                        ->where('exam_attendance.examID', $examID)
                        ->where('students.subclassID', $subclassID)
                        ->select(DB::raw('COUNT(DISTINCT exam_attendance.studentID) as count'))
                        ->first()
                        ->count ?? 0;
                    
                    // Get unique students in this subclass who were present (attended at least one subject)
                    $subclassPresentStudentIDs = DB::table('exam_attendance')
                        ->join('students', 'exam_attendance.studentID', '=', 'students.studentID')
                        ->where('exam_attendance.examID', $examID)
                        ->where('students.subclassID', $subclassID)
                        ->where('exam_attendance.status', 'Present')
                        ->distinct()
                        ->pluck('exam_attendance.studentID')
                        ->toArray();
                    
                    $subclassPresent = count($subclassPresentStudentIDs);
                    
                    // Get absent students in this subclass (didn't attend any subject)
                    $subclassAbsent = $subclassExpected - $subclassPresent;
                    
                    $attendanceBySubclass[] = [
                        'subclassID' => $subclassID,
                        'subclass_name' => $classItem['subclass_name'],
                        'class_name' => $classItem['class_name'],
                        'expected' => $subclassExpected,
                        'present' => $subclassPresent,
                        'absent' => $subclassAbsent,
                    ];
                }
                
                $attendanceStats = [
                    'expected' => $expectedStudents,
                    'present' => $presentStudents,
                    'absent' => $absentStudents,
                    'by_subclass' => $attendanceBySubclass,
                ];
            }

            return response()->json([
                'success' => true,
                'exam' => [
                    'examID' => $exam->examID,
                    'exam_name' => $exam->exam_name,
                    'exam_type' => $exam->exam_type,
                    'start_date' => $exam->start_date,
                    'end_date' => $exam->end_date,
                    'has_ended' => $examHasEnded,
                ],
                'classes' => $examDetails,
                'total_classes' => count($examDetails),
                'total_students' => array_sum(array_column($examDetails, 'students_count')),
                'total_subjects' => count($allSubjectIds),
                'attendance' => $attendanceStats,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update results status for an examination
     */
    public function updateResultsStatus(Request $request, $examID)
    {
        $userType = Session::get('user_type');

        // Check if user is Admin or has the required permission
        $permission = $request->input('permission');
        $status = $request->input('status');

        // Check permission - Update action: examination_update (updating results status)
        if ($userType !== 'Admin' && ! $this->hasPermission('examination_update')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to perform this action. You need examination_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.',
                ], 400);
            }

            // Verify exam belongs to school
            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examination not found.',
                ], 404);
            }

            // Validate status
            $validStatuses = ['not_allowed', 'allowed', 'under_review', 'approved'];
            if (! in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided.',
                ], 400);
            }

            // Update all results for this exam
            $updated = DB::table('results')
                ->where('examID', $examID)
                ->update(['status' => $status]);

            // If status is "approved", automatically transfer students based on exam's shifting status
            $transferredCount = 0;
            if ($status === 'approved' && $exam->exam_type === 'school_wide_all_subjects' && $exam->student_shifting_status && $exam->student_shifting_status !== 'none') {
                $transferredCount = $this->autoTransferStudentsForExam($examID, $schoolID, $exam->student_shifting_status);
            }

            // If status is "approved", send SMS to parents
            $smsSentCount = 0;
            if ($status === 'approved') {
                $smsSentCount = $this->sendResultsSMSToParents($examID, $schoolID);
            }

            $message = 'Results status updated successfully.';
            if ($transferredCount > 0) {
                $message .= " {$transferredCount} student(s) transferred automatically.";
            }
            if ($smsSentCount > 0) {
                $message .= " {$smsSentCount} SMS sent to parents.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updated,
                'transferred_count' => $transferredCount,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Automatically transfer students based on exam results when status is approved
     */
    private function autoTransferStudentsForExam($examID, $schoolID, $shiftingStatus)
    {
        try {
            $school = \App\Models\School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Get all unique students who have results in this exam
            $studentsWithResults = DB::table('results')
                ->where('examID', $examID)
                ->distinct()
                ->pluck('studentID')
                ->toArray();

            // For external shifting, also get ALL students from specific classes (Form Four, Form Two, Standard 7)
            $allStudentsToProcess = collect($studentsWithResults);
            
            if ($shiftingStatus === 'external') {
                // Get all active students from Form Four, Form Two, and Standard 7 classes
                $classesToProcess = [];
                
                if ($schoolType === 'Secondary') {
                    // Get Form Four and Form Two classes by class name (not ID, since multiple schools use the system)
                    $formFourClasses = DB::table('classes')
                        ->where('schoolID', $schoolID)
                        ->where(function($query) {
                            $query->whereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%form_four%'])
                                  ->orWhereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%form_4%'])
                                  ->orWhereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%formfour%']);
                        })
                        ->pluck('classID')
                        ->toArray();
                    
                    $formTwoClasses = DB::table('classes')
                        ->where('schoolID', $schoolID)
                        ->where(function($query) {
                            $query->whereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%form_two%'])
                                  ->orWhereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%form_2%'])
                                  ->orWhereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%formtwo%']);
                        })
                        ->pluck('classID')
                        ->toArray();
                    
                    $classesToProcess = array_merge($formFourClasses, $formTwoClasses);
                } else {
                    // Primary: Get Standard 7 classes
                    $standard7Classes = DB::table('classes')
                        ->where('schoolID', $schoolID)
                        ->where(function($query) {
                            $query->whereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%standard_7%'])
                                  ->orWhereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%standard_seven%'])
                                  ->orWhereRaw("LOWER(REPLACE(REPLACE(class_name, ' ', '_'), '-', '_')) LIKE ?", ['%standard7%']);
                        })
                        ->pluck('classID')
                        ->toArray();
                    
                    $classesToProcess = $standard7Classes;
                }
                
                if (!empty($classesToProcess)) {
                    // Get all students from these classes
                    $allStudentsFromSpecialClasses = DB::table('students')
                        ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                        ->where('students.schoolID', $schoolID)
                        ->where('students.status', 'Active')
                        ->whereIn('subclasses.classID', $classesToProcess)
                        ->pluck('students.studentID')
                        ->toArray();
                    
                    // Merge with students who have results
                    $allStudentsToProcess = collect(array_unique(array_merge($studentsWithResults, $allStudentsFromSpecialClasses)));
                }
            }

            if ($allStudentsToProcess->isEmpty()) {
                return ['transferred' => 0, 'graduated' => 0];
            }

            $transferredCount = 0;
            $graduatedCount = 0;

            foreach ($allStudentsToProcess as $studentID) {
                $student = Student::with(['subclass.class', 'subclass.combie', 'parent'])->find($studentID);

                if (! $student || $student->schoolID != $schoolID || $student->status !== 'Active') {
                    continue; // Skip inactive or transferred students
                }

                $currentSubclass = $student->subclass;
                if (! $currentSubclass) {
                    continue;
                }

                $currentClass = $currentSubclass->class;
                if (! $currentClass) {
                    continue;
                }

                $currentClassName = strtolower(preg_replace('/[\s\-]+/', '_', $currentClass->class_name));
                $currentCombieID = $currentSubclass->combieID;

                // Normalize class name for comparison
                $normalizedClassName = str_replace('_seven', '_7', $currentClassName);
                $normalizedClassName = str_replace('seven', '_7', $normalizedClassName);
                $normalizedClassName = str_replace('_four', '_4', $normalizedClassName);
                $normalizedClassName = str_replace('four', '_4', $normalizedClassName);
                $normalizedClassName = str_replace('_two', '_2', $normalizedClassName);
                $normalizedClassName = str_replace('two', '_2', $normalizedClassName);

                // Check if student should be graduated (Form Four or Standard Seven) - ALL students, not just those with results
                if ($shiftingStatus === 'external') {
                    $shouldGraduate = false;
                    
                    if ($schoolType === 'Secondary' && (preg_match('/form_?4|form_four/i', $normalizedClassName))) {
                        $shouldGraduate = true;
                    } elseif ($schoolType === 'Primary' && (preg_match('/standard_?7|standard_seven/i', $normalizedClassName))) {
                        $shouldGraduate = true;
                    }

                    if ($shouldGraduate) {
                        // Graduate student - change status from Active to Graduated
                        // Keep history: Save old subclass ID before changing
                        $oldSubclassID = $student->subclassID;
                        $student->old_subclassID = $oldSubclassID; // Keep history for debt tracking and class history
                        $student->status = 'Graduated';
                        $student->save();
                        $graduatedCount++;

                        // Send SMS to parent about graduation
                        $this->sendStudentShiftSMS($student, $currentSubclass, null, 'graduated', $school);
                        continue;
                    }
                    
                    // For Form Two with external shifting: ALL students move to Form Three (even without results)
                    if ($schoolType === 'Secondary' && preg_match('/form_?2|form_two/i', $normalizedClassName)) {
                        // Get next class (Form Three)
                        $nextClassName = 'form_three';
                        $targetSubclasses = DB::table('subclasses')
                            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                            ->leftJoin('combies', 'subclasses.combieID', '=', 'combies.combieID')
                            ->where('classes.schoolID', $schoolID)
                            ->whereRaw('LOWER(REPLACE(REPLACE(classes.class_name, " ", "_"), "-", "_")) LIKE ?', ['%form_three%'])
                            ->select(
                                'subclasses.subclassID',
                                'subclasses.subclass_name',
                                'subclasses.stream_code',
                                'subclasses.first_grade',
                                'subclasses.final_grade',
                                'subclasses.combieID',
                                'classes.class_name',
                                'combies.combie_name'
                            )
                            ->get();

                        // Find eligible subclass (check combie match if student has combie)
                        $eligibleSubclass = null;
                        foreach ($targetSubclasses as $subclass) {
                            // If subclass has no grade requirements or student has results, check eligibility
                            if (!$subclass->first_grade || !$subclass->final_grade) {
                                // No grade requirements - check combie match
                                if ($currentCombieID && $subclass->combieID) {
                                    if ($currentCombieID == $subclass->combieID) {
                                        $eligibleSubclass = $subclass;
                                        break;
                                    }
                                } elseif (!$currentCombieID && !$subclass->combieID) {
                                    // Both have no combie - allow
                                    $eligibleSubclass = $subclass;
                                    break;
                                } elseif (!$currentCombieID && $subclass->combieID) {
                                    // Student has no combie but subclass has - skip
                                    continue;
                                }
                            } else {
                                // Has grade requirements - only allow if student has results
                                if (in_array($studentID, $studentsWithResults)) {
                                    $studentResults = $this->getStudentLatestResultsForExam($studentID, $examID, $schoolID, $schoolType, $currentClassName);
                                    if ($this->isSubclassEligible($subclass, $currentCombieID, $studentResults, $schoolType, $nextClassName)) {
                                        $eligibleSubclass = $subclass;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($eligibleSubclass) {
                            // Transfer student - keep history
                            $oldSubclassID = $student->subclassID;
                            $student->subclassID = $eligibleSubclass->subclassID;
                            $student->old_subclassID = $oldSubclassID; // Keep history for debt tracking and class history
                            $student->status = 'Active';
                            $student->save();
                            $transferredCount++;

                            // Send SMS to parent about class shift
                            $newSubclass = Subclass::with('class')->find($eligibleSubclass->subclassID);
                            $this->sendStudentShiftSMS($student, $currentSubclass, $newSubclass, 'transferred', $school);
                        }
                        continue; // Skip to next student
                    }
                }

                // For other cases: Only shift students who have exam results
                if (!in_array($studentID, $studentsWithResults)) {
                    continue; // Skip students without results
                }

                // Get student's results for this exam
                $studentResults = $this->getStudentLatestResultsForExam($studentID, $examID, $schoolID, $schoolType, $currentClassName);

                // Get eligible subclasses based on shifting status
                $eligibleSubclass = $this->getEligibleSubclassForAutoTransfer(
                    $student,
                    $currentSubclass,
                    $currentClass,
                    $currentClassName,
                    $currentCombieID,
                    $schoolID,
                    $schoolType,
                    $shiftingStatus,
                    $studentResults
                );

                if ($eligibleSubclass) {
                    // Transfer student - keep history
                    $oldSubclassID = $student->subclassID;
                    $student->subclassID = $eligibleSubclass->subclassID;
                    $student->old_subclassID = $oldSubclassID; // Keep history for debt tracking and class history
                    $student->status = 'Active'; // Keep as Active for automatic transfers
                    $student->save();
                    $transferredCount++;

                    // Send SMS to parent about class shift
                    $newSubclass = Subclass::with('class')->find($eligibleSubclass->subclassID);
                    $this->sendStudentShiftSMS($student, $currentSubclass, $newSubclass, 'transferred', $school);
                }
            }

            return ['transferred' => $transferredCount, 'graduated' => $graduatedCount];
        } catch (\Exception $e) {
            Log::error('Auto transfer students error: '.$e->getMessage());

            return ['transferred' => 0, 'graduated' => 0]; // Return 0 on error to not break the main flow
        }
    }

    /**
     * Get student's latest results for a specific exam
     */
    private function getStudentLatestResultsForExam($studentID, $examID, $schoolID, $schoolType, $className)
    {
        // Get student's results for this exam
        $results = DB::table('results')
            ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
            ->where('results.studentID', $studentID)
            ->where('results.examID', $examID)
            ->select('results.marks', 'school_subjects.subject_name')
            ->get();

        if ($results->isEmpty()) {
            return null;
        }

        // Calculate total division/grade (similar to ManageClassessController)
        $subjectsData = [];
        $totalMarks = 0;
        $subjectCount = 0;

        foreach ($results as $result) {
            if ($result->marks !== null && $result->marks !== '') {
                $totalMarks += (float) $result->marks;
                $subjectCount++;
            }
            $gradeOrDivision = $this->calculateGradeOrDivisionForExam($result->marks, $schoolType, $className);
            $subjectsData[] = [
                'marks' => $result->marks,
                'points' => $gradeOrDivision['points'] ?? null,
            ];
        }

        // Calculate total points
        $subjectPoints = array_filter(array_column($subjectsData, 'points'), function ($p) {
            return $p !== null;
        });
        $totalPoints = 0;

        if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
            if (count($subjectPoints) > 0) {
                sort($subjectPoints);
                $bestSeven = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
                $totalPoints = array_sum($bestSeven);
            }
        } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
            if (count($subjectPoints) > 0) {
                rsort($subjectPoints);
                $bestThree = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                $totalPoints = array_sum($bestThree);
            }
        }

        $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
        $totalDivision = $this->calculateTotalDivisionForExam($totalPoints, $schoolType, $className, $averageMarks);

        return [
            'total_division' => $totalDivision['division'] ?? null,
            'total_points' => $totalPoints,
            'average_marks' => $averageMarks,
        ];
    }

    /**
     * Calculate grade or division for a single subject
     */
    private function calculateGradeOrDivisionForExam($marks, $schoolType, $className)
    {
        if ($marks === null || $marks === '') {
            return ['points' => null, 'grade' => null];
        }

        $marks = (float) $marks;

        if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
            // O-Level grading
            if ($marks >= 75) {
                return ['points' => 1, 'grade' => 'A'];
            }
            if ($marks >= 65) {
                return ['points' => 2, 'grade' => 'B'];
            }
            if ($marks >= 45) {
                return ['points' => 3, 'grade' => 'C'];
            }
            if ($marks >= 30) {
                return ['points' => 4, 'grade' => 'D'];
            }
            if ($marks >= 20) {
                return ['points' => 5, 'grade' => 'E'];
            }

            return ['points' => 6, 'grade' => 'F'];
        } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
            // A-Level grading
            if ($marks >= 80) {
                return ['points' => 5, 'grade' => 'A'];
            }
            if ($marks >= 70) {
                return ['points' => 4, 'grade' => 'B'];
            }
            if ($marks >= 60) {
                return ['points' => 3, 'grade' => 'C'];
            }
            if ($marks >= 50) {
                return ['points' => 2, 'grade' => 'D'];
            }
            if ($marks >= 40) {
                return ['points' => 1, 'grade' => 'E'];
            }

            return ['points' => 0, 'grade' => 'S/F'];
        } else {
            // Primary grading (simplified)
            if ($marks >= 75) {
                return ['points' => 1, 'grade' => 'A'];
            }
            if ($marks >= 65) {
                return ['points' => 2, 'grade' => 'B'];
            }
            if ($marks >= 45) {
                return ['points' => 3, 'grade' => 'C'];
            }
            if ($marks >= 30) {
                return ['points' => 4, 'grade' => 'D'];
            }

            return ['points' => 5, 'grade' => 'E'];
        }
    }

    /**
     * Calculate total division
     */
    private function calculateTotalDivisionForExam($totalPoints, $schoolType, $className, $averageMarks)
    {
        if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
            // O-Level divisions
            if ($totalPoints >= 7 && $totalPoints <= 17) {
                return ['division' => 'I.'.$totalPoints, 'points' => $totalPoints];
            } elseif ($totalPoints >= 18 && $totalPoints <= 21) {
                return ['division' => 'II.'.$totalPoints, 'points' => $totalPoints];
            } elseif ($totalPoints >= 22 && $totalPoints <= 25) {
                return ['division' => 'III.'.$totalPoints, 'points' => $totalPoints];
            } elseif ($totalPoints >= 26 && $totalPoints <= 33) {
                return ['division' => 'IV.'.$totalPoints, 'points' => $totalPoints];
            } else {
                return ['division' => '0.'.$totalPoints, 'points' => $totalPoints];
            }
        } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
            // A-Level divisions
            if ($totalPoints >= 12 && $totalPoints <= 15) {
                return ['division' => 'I.'.$totalPoints, 'points' => $totalPoints];
            } elseif ($totalPoints >= 9 && $totalPoints <= 11) {
                return ['division' => 'II.'.$totalPoints, 'points' => $totalPoints];
            } elseif ($totalPoints >= 6 && $totalPoints <= 8) {
                return ['division' => 'III.'.$totalPoints, 'points' => $totalPoints];
            } elseif ($totalPoints >= 3 && $totalPoints <= 5) {
                return ['division' => 'IV.'.$totalPoints, 'points' => $totalPoints];
            } else {
                return ['division' => '0.'.$totalPoints, 'points' => $totalPoints];
            }
        } else {
            // Primary: Calculate grade based on average marks
            if ($averageMarks >= 75) {
                return ['grade' => 'A', 'division' => null, 'points' => null];
            } elseif ($averageMarks >= 65) {
                return ['grade' => 'B', 'division' => null, 'points' => null];
            } elseif ($averageMarks >= 45) {
                return ['grade' => 'C', 'division' => null, 'points' => null];
            } elseif ($averageMarks >= 30) {
                return ['grade' => 'D', 'division' => null, 'points' => null];
            } else {
                return ['grade' => 'F', 'division' => null, 'points' => null];
            }
        }
    }

    /**
     * Get eligible subclass for automatic transfer
     */
    private function getEligibleSubclassForAutoTransfer($student, $currentSubclass, $currentClass, $currentClassName, $currentCombieID, $schoolID, $schoolType, $shiftingStatus, $studentResults)
    {
        if ($shiftingStatus === 'internal') {
            // Internal: Only subclasses within same class level
            $targetSubclasses = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->leftJoin('combies', 'subclasses.combieID', '=', 'combies.combieID')
                ->where('classes.schoolID', $schoolID)
                ->where('classes.class_name', $currentClass->class_name)
                ->where('subclasses.subclassID', '!=', $currentSubclass->subclassID)
                ->select(
                    'subclasses.subclassID',
                    'subclasses.subclass_name',
                    'subclasses.stream_code',
                    'subclasses.first_grade',
                    'subclasses.final_grade',
                    'subclasses.combieID',
                    'classes.class_name',
                    'combies.combie_name'
                )
                ->get();

            foreach ($targetSubclasses as $subclass) {
                if ($this->isSubclassEligible($subclass, $currentCombieID, $studentResults, $schoolType, $currentClassName)) {
                    return $subclass;
                }
            }
        } elseif ($shiftingStatus === 'external') {
            // External: Next class level
            $nextClassName = $this->getNextClassName($currentClassName, $schoolType);
            if ($nextClassName) {
                $targetSubclasses = DB::table('subclasses')
                    ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                    ->leftJoin('combies', 'subclasses.combieID', '=', 'combies.combieID')
                    ->where('classes.schoolID', $schoolID)
                    ->whereRaw('LOWER(REPLACE(REPLACE(classes.class_name, " ", "_"), "-", "_")) = ?', [strtolower($nextClassName)])
                    ->select(
                        'subclasses.subclassID',
                        'subclasses.subclass_name',
                        'subclasses.stream_code',
                        'subclasses.first_grade',
                        'subclasses.final_grade',
                        'subclasses.combieID',
                        'classes.class_name',
                        'combies.combie_name'
                    )
                    ->get();

                foreach ($targetSubclasses as $subclass) {
                    if ($this->isSubclassEligible($subclass, $currentCombieID, $studentResults, $schoolType, $nextClassName)) {
                        return $subclass;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Check if subclass is eligible for transfer
     */
    private function isSubclassEligible($subclass, $currentCombieID, $studentResults, $schoolType, $className)
    {
        if ($schoolType === 'Secondary') {
            // Check grade requirements
            if ($subclass->first_grade && $subclass->final_grade) {
                // If subclass has grade requirements, student must match
                if (! $studentResults || ! $this->checkGradeRangeForExam($studentResults, $subclass->first_grade, $subclass->final_grade, $schoolType, $className)) {
                    return false;
                }
            }
            // If no grade requirements (NULL), can place anywhere but still check combie
            
            // Check combie match - if student has combie, must match subclass combie
            if ($currentCombieID && $subclass->combieID) {
                if ($currentCombieID != $subclass->combieID) {
                    return false;
                }
            }
            // If student has combie but subclass doesn't, don't allow (student must stay in their combie)
            if ($currentCombieID && ! $subclass->combieID) {
                return false;
            }
        } else {
            // Primary: Check grade only (no combie)
            if ($subclass->first_grade && $subclass->final_grade) {
                // If subclass has grade requirements, student must match
                if (! $studentResults || ! $this->checkGradeRangeForExam($studentResults, $subclass->first_grade, $subclass->final_grade, $schoolType, $className)) {
                    return false;
                }
            }
            // If no grade requirements (NULL), can place anywhere
        }

        return true;
    }

    /**
     * Check if student's grade/division is within subclass range
     */
    private function checkGradeRangeForExam($studentResults, $firstGrade, $finalGrade, $schoolType, $className)
    {
        if (! $studentResults || ! $studentResults['total_division']) {
            return false;
        }

        $studentDivision = $studentResults['total_division'];

        if ($schoolType === 'Primary') {
            $divisionToGrade = [
                'Division One' => 'A',
                'Division Two' => 'B',
                'Division Three' => 'C',
                'Division Four' => 'D',
                'Division Zero' => 'E',
            ];

            $studentGrade = $divisionToGrade[$studentDivision] ?? null;
            if (! $studentGrade) {
                return false;
            }

            $gradeOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5];
            $firstOrder = $gradeOrder[$firstGrade] ?? 999;
            $finalOrder = $gradeOrder[$finalGrade] ?? 999;
            $studentOrder = $gradeOrder[$studentGrade] ?? 999;

            return $studentOrder >= $firstOrder && $studentOrder <= $finalOrder;
        } else {
            // Secondary divisions
            if (preg_match('/^([IVX0]+)\.(\d+)$/', $studentDivision, $matches)) {
                $studentDivisionNum = (int) $matches[2];
                $studentDivisionLevel = $matches[1];
            } else {
                return false;
            }

            if (preg_match('/^([IVX0]+)\.(\d+)$/', $firstGrade, $firstMatches) &&
                preg_match('/^([IVX0]+)\.(\d+)$/', $finalGrade, $finalMatches)) {
                $firstNum = (int) $firstMatches[2];
                $finalNum = (int) $finalMatches[2];
                $firstLevel = $firstMatches[1];
                $finalLevel = $finalMatches[1];

                if ($studentDivisionLevel === $firstLevel && $studentDivisionLevel === $finalLevel) {
                    return $studentDivisionNum >= $firstNum && $studentDivisionNum <= $finalNum;
                } else {
                    $divisionOrder = ['0' => 0, 'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4];
                    $firstOrder = $divisionOrder[$firstLevel] ?? 999;
                    $finalOrder = $divisionOrder[$finalLevel] ?? 999;
                    $studentOrder = $divisionOrder[$studentDivisionLevel] ?? 999;

                    if ($studentOrder > $firstOrder && $studentOrder < $finalOrder) {
                        return true;
                    } elseif ($studentOrder === $firstOrder && $studentDivisionNum >= $firstNum) {
                        return true;
                    } elseif ($studentOrder === $finalOrder && $studentDivisionNum <= $finalNum) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get next class name based on current class
     */
    private function getNextClassName($currentClassName, $schoolType)
    {
        // Normalize class name to handle variations
        $normalized = strtolower($currentClassName);
        // Handle "Standard Seven" vs "Standard 7" variations
        $normalized = str_replace('_seven', '_7', $normalized);
        $normalized = str_replace('seven', '_7', $normalized);
        // Handle "Form Four" vs "Form 4" variations
        $normalized = str_replace('_four', '_4', $normalized);
        $normalized = str_replace('four', '_4', $normalized);
        // Handle "Form One" vs "Form 1" variations
        $normalized = str_replace('_one', '_1', $normalized);
        $normalized = str_replace('one', '_1', $normalized);
        $normalized = str_replace('_two', '_2', $normalized);
        $normalized = str_replace('two', '_2', $normalized);
        $normalized = str_replace('_three', '_3', $normalized);
        $normalized = str_replace('three', '_3', $normalized);
        $normalized = str_replace('_five', '_5', $normalized);
        $normalized = str_replace('five', '_5', $normalized);
        $normalized = str_replace('_six', '_6', $normalized);
        $normalized = str_replace('six', '_6', $normalized);

        if ($schoolType === 'Secondary') {
            $classSequence = [
                'form_1' => 'form_2',
                'form_one' => 'form_2',
                'form_2' => 'form_3',
                'form_two' => 'form_3',
                'form_3' => 'form_4',
                'form_three' => 'form_4',
                // Form Four graduates, no next class
                'form_4' => null,
                'form_four' => null,
                'form_5' => 'form_6',
                'form_five' => 'form_6',
            ];
        } else {
            $classSequence = [
                'nursery' => 'baby_class',
                'baby_class' => 'standard_1',
                'standard_1' => 'standard_2',
                'standard_2' => 'standard_3',
                'standard_3' => 'standard_4',
                'standard_4' => 'standard_5',
                'standard_5' => 'standard_6',
                'standard_6' => 'standard_7',
                // Standard Seven graduates, no next class
                'standard_7' => null,
                'standard_seven' => null,
            ];
        }

        return $classSequence[$normalized] ?? null;
    }

    /**
     * Send SMS to parent about student class shift or graduation
     */
    private function sendStudentShiftSMS($student, $oldSubclass, $newSubclass, $action, $school)
    {
        try {
            if (! $student || ! $student->parent || ! $student->parent->phone) {
                return false; // Skip if no parent or phone number
            }

            $parentName = trim($student->parent->first_name.' '.($student->parent->last_name ?? ''));
            $studentName = trim($student->first_name.' '.($student->middle_name ? $student->middle_name.' ' : '').$student->last_name);
            $schoolName = $school ? $school->school_name : 'Shule';

            $message = '';
            if ($action === 'graduated') {
                $oldClassName = $oldSubclass ? $oldSubclass->subclass_name : 'Darasa la zamani';
                $message = "{$schoolName}. Mzazi {$parentName}, mwanafunzi {$studentName} amehitimu kutoka {$oldClassName}. Hongera!";
            } else {
                $oldClassName = $oldSubclass ? $oldSubclass->subclass_name : 'Darasa la zamani';
                $newClassName = $newSubclass ? $newSubclass->subclass_name : 'Darasa jipya';
                $message = "{$schoolName}. Mzazi {$parentName}, mwanafunzi {$studentName} amehamishwa kutoka {$oldClassName} kwenda {$newClassName}.";
            }

            // Send SMS
            $phoneNo = $student->parent->phone;
            // Remove any spaces and ensure it starts with 255
            $phoneNo = preg_replace('/\s+/', '', $phoneNo);
            if (! preg_match('/^255/', $phoneNo)) {
                // If it starts with 0, replace with 255
                if (preg_match('/^0/', $phoneNo)) {
                    $phoneNo = '255'.substr($phoneNo, 1);
                } else {
                    $phoneNo = '255'.$phoneNo;
                }
            }

            $smsResult = $this->sendSMS($message, $phoneNo);

            return $smsResult;
        } catch (\Exception $e) {
            Log::error("Error sending shift SMS for student {$student->studentID}: ".$e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS to parents when results are approved for public review
     */
    private function sendResultsSMSToParents($examID, $schoolID)
    {
        try {
            $school = \App\Models\School::find($schoolID);
            if (! $school) {
                Log::error("School not found for SMS sending: {$schoolID}");

                return 0;
            }

            $schoolName = $school->school_name;
            $schoolType = $school->school_type;

            // Get exam details
            $exam = Examination::find($examID);
            if (! $exam) {
                Log::error("Exam not found for SMS sending: {$examID}");
                return 0;
            }
            $examName = $exam->exam_name;

            // Get all unique students who have results in this exam
            $studentIDs = DB::table('results')
                ->where('examID', $examID)
                ->distinct()
                ->pluck('studentID');

            if ($studentIDs->isEmpty()) {
                return 0;
            }

            $smsSentCount = 0;

            foreach ($studentIDs as $studentID) {
                try {
                    $student = Student::with(['parent', 'subclass.class'])->find($studentID);

                    if (! $student || ! $student->parent || ! $student->parent->phone) {
                        continue; // Skip if no parent or phone number
                    }

                    // Get student results with grade, position, and class info
                    $studentResult = $this->getStudentResultForSMS($studentID, $examID, $schoolID, $schoolType, $student);

                    if (! $studentResult) {
                        continue; // Skip if no results found
                    }

                    // Prepare SMS message
                    $parentName = trim($student->parent->first_name.' '.($student->parent->last_name ?? ''));
                    $studentName = trim($student->first_name.' '.($student->middle_name ? $student->middle_name.' ' : '').$student->last_name);
                    $totalMarks = $studentResult['total_marks'] ?? 0;
                    $averageMarks = $studentResult['average_marks'] ?? 0;
                    $position = $studentResult['position'] ?? 'N/A';
                    $totalStudents = $studentResult['total_students'] ?? 'N/A';
                    
                    // Format grade/division based on school type
                    $gradeOrDivision = '';
                    if ($schoolType === 'Secondary') {
                        $gradeOrDivision = $studentResult['division'] ?? 'N/A';
                    } else {
                        $gradeOrDivision = $studentResult['grade'] ?? 'N/A';
                    }

                    // Build SMS message according to user requirements
                    $message = "{$schoolName}. Mzazi {$parentName}, mwanafunzi {$studentName} amepata jumla ya alama {$totalMarks}";
                    if ($schoolType === 'Secondary') {
                        $message .= ", wastani division {$gradeOrDivision}";
                    } else {
                        $message .= ", wastani grade {$gradeOrDivision}";
                    }
                    $message .= " na kushika nafasi ya {$position} kati ya wanafunzi {$totalStudents} katika mtihani wa {$examName}. Kuona zaidi tembelea ingia kwenye application ya ShuleLink";

                    // Send SMS
                    $phoneNo = $student->parent->phone;
                    // Remove any spaces and ensure it starts with 255
                    $phoneNo = preg_replace('/\s+/', '', $phoneNo);
                    if (! preg_match('/^255/', $phoneNo)) {
                        // If it starts with 0, replace with 255
                        if (preg_match('/^0/', $phoneNo)) {
                            $phoneNo = '255'.substr($phoneNo, 1);
                        } else {
                            $phoneNo = '255'.$phoneNo;
                        }
                    }

                    $smsResult = $this->sendSMS($message, $phoneNo);

                    if ($smsResult) {
                        $smsSentCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Error sending SMS for student {$studentID}: ".$e->getMessage());

                    continue; // Continue with next student
                }
            }

            return $smsSentCount;
        } catch (\Exception $e) {
            Log::error('Error in sendResultsSMSToParents: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Get student result information for SMS
     */
    private function getStudentResultForSMS($studentID, $examID, $schoolID, $schoolType, $student)
    {
        try {
            if (! $student || ! $student->subclass || ! $student->subclass->class) {
                return null;
            }

            $className = $student->subclass->class->class_name;
            $classID = $student->subclass->classID;
            $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));

            // Get student's results for this exam
            $results = DB::table('results')
                ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                ->where('results.studentID', $studentID)
                ->where('results.examID', $examID)
                ->select('results.marks', 'school_subjects.subject_name')
                ->get();

            if ($results->isEmpty()) {
                return null;
            }

            // Calculate total marks and points
            $subjectsData = [];
            $totalMarks = 0;
            $subjectCount = 0;

            foreach ($results as $result) {
                if ($result->marks !== null && $result->marks !== '') {
                    $totalMarks += (float) $result->marks;
                    $subjectCount++;
                }
                $gradeOrDivision = $this->calculateGradeOrDivisionForExam($result->marks, $schoolType, $classNameLower);
                $subjectsData[] = [
                    'marks' => $result->marks,
                    'points' => $gradeOrDivision['points'] ?? null,
                ];
            }

            // Calculate total points
            $subjectPoints = array_filter(array_column($subjectsData, 'points'), function ($p) {
                return $p !== null;
            });
            $totalPoints = 0;

            if ($schoolType === 'Secondary' && in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                if (count($subjectPoints) > 0) {
                    sort($subjectPoints);
                    $bestSeven = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
                    $totalPoints = array_sum($bestSeven);
                }
            } elseif ($schoolType === 'Secondary' && in_array($classNameLower, ['form_five', 'form_six'])) {
                if (count($subjectPoints) > 0) {
                    rsort($subjectPoints);
                    $bestThree = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                    $totalPoints = array_sum($bestThree);
                }
            } else {
                $totalPoints = array_sum($subjectPoints);
            }

            $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            $totalGradeOrDivision = $this->calculateTotalDivisionForExam($totalPoints, $schoolType, $classNameLower, $averageMarks);

            // Get all students in the main class (not just subclass)
            $allClassStudents = Student::whereHas('subclass', function ($query) use ($classID) {
                $query->where('classID', $classID);
            })
                ->where('status', 'Active')
                ->get();

            // Calculate results for all students in class to determine position
            $allClassResults = [];
            foreach ($allClassStudents as $classStudent) {
                $classStudentResults = DB::table('results')
                    ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->where('results.studentID', $classStudent->studentID)
                    ->where('results.examID', $examID)
                    ->select('results.marks', 'school_subjects.subject_name')
                    ->get();

                if ($classStudentResults->isEmpty()) {
                    continue;
                }

                $classTotalMarks = 0;
                $classSubjectCount = 0;
                $classSubjectsData = [];

                foreach ($classStudentResults as $classResult) {
                    if ($classResult->marks !== null && $classResult->marks !== '') {
                        $classTotalMarks += (float) $classResult->marks;
                        $classSubjectCount++;
                    }
                    $gradeOrDiv = $this->calculateGradeOrDivisionForExam($classResult->marks, $schoolType, $classNameLower);
                    $classSubjectsData[] = ['points' => $gradeOrDiv['points'] ?? null];
                }

                $classTotalPoints = 0;
                $classSubjectPoints = array_filter(array_column($classSubjectsData, 'points'), function ($p) {
                    return $p !== null;
                });

                if ($schoolType === 'Secondary' && in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    if (count($classSubjectPoints) > 0) {
                        sort($classSubjectPoints);
                        $bestSeven = array_slice($classSubjectPoints, 0, min(7, count($classSubjectPoints)));
                        $classTotalPoints = array_sum($bestSeven);
                    }
                } elseif ($schoolType === 'Secondary' && in_array($classNameLower, ['form_five', 'form_six'])) {
                    if (count($classSubjectPoints) > 0) {
                        rsort($classSubjectPoints);
                        $bestThree = array_slice($classSubjectPoints, 0, min(3, count($classSubjectPoints)));
                        $classTotalPoints = array_sum($bestThree);
                    }
                } else {
                    $classTotalPoints = array_sum($classSubjectPoints);
                }

                $classAverageMarks = $classSubjectCount > 0 ? $classTotalMarks / $classSubjectCount : 0;

                $allClassResults[] = [
                    'studentID' => $classStudent->studentID,
                    'total_marks' => round($classTotalMarks, 2),
                    'total_points' => $classTotalPoints,
                    'average_marks' => $classAverageMarks,
                ];
            }

            // Sort by average marks (descending)
            usort($allClassResults, function ($a, $b) {
                return ($b['average_marks'] ?? 0) <=> ($a['average_marks'] ?? 0);
            });

            // Find student's position
            $position = null;
            $currentPos = 1;
            $prevAverage = null;
            foreach ($allClassResults as $classResult) {
                $currentAverage = $classResult['average_marks'] ?? 0;

                if ($prevAverage !== null && abs($currentAverage - $prevAverage) > 0.01) {
                    $currentPos++;
                }

                if ($classResult['studentID'] == $studentID) {
                    $position = $currentPos;
                    break;
                }

                $prevAverage = $currentAverage;
            }

            $totalStudents = count($allClassResults);

            // Determine progress based on grade/division
            $progress = $this->getProgressFromGrade($totalGradeOrDivision['division'] ?? null, $schoolType, $averageMarks);

            // Format grade for display
            $grade = $totalGradeOrDivision['division'] ?? 'N/A';
            if ($schoolType === 'Primary') {
                // For primary, show division
                $grade = $totalGradeOrDivision['division'] ?? 'N/A';
            } else {
                // For secondary, show division (e.g., I.7, II.20)
                $grade = $totalGradeOrDivision['division'] ?? 'N/A';
            }

            return [
                'grade' => $grade,
                'division' => $totalGradeOrDivision['division'] ?? 'N/A',
                'position' => $position ?? 'N/A',
                'total_students' => $totalStudents,
                'total_marks' => round($totalMarks, 2),
                'average_marks' => round($averageMarks, 2),
                'progress' => $progress,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting student result for SMS: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get progress description based on grade/division
     */
    private function getProgressFromGrade($division, $schoolType, $averageMarks = 0)
    {
        if (! $division) {
            return 'Hitaji kujifunza zaidi';
        }

        if ($schoolType === 'Primary') {
            if ($division === 'Division One') {
                return 'Vizuri sana';
            } elseif ($division === 'Division Two') {
                return 'Vizuri';
            } elseif ($division === 'Division Three') {
                return 'Sawa';
            } elseif ($division === 'Division Four') {
                return 'Inahitaji kuboresha';
            } else {
                return 'Hitaji kujifunza zaidi';
            }
        } else {
            // Secondary school
            if (preg_match('/^I\./', $division)) {
                return 'Vizuri sana';
            } elseif (preg_match('/^II\./', $division)) {
                return 'Vizuri';
            } elseif (preg_match('/^III\./', $division)) {
                return 'Sawa';
            } elseif (preg_match('/^IV\./', $division)) {
                return 'Inahitaji kuboresha';
            } else {
                return 'Hitaji kujifunza zaidi';
            }
        }
    }

    /**
     * Send SMS using the messaging service API
     */
    private function sendSMS($message, $phoneNo)
    {
        try {
            $text = urlencode($message);

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://messaging-service.co.tz/link/sms/v1/text/single?username=emcatechn&password=Emca@%2312&from=ShuleLink&to='.$phoneNo.'&text='.$text,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                Log::error('cURL Error sending SMS: '.curl_error($curl));
                curl_close($curl);

                return false;
            }

            curl_close($curl);

            // Check if response indicates success (you may need to adjust based on actual API response)
            if ($response) {
                Log::info("SMS sent successfully to {$phoneNo}. Response: {$response}");

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error sending SMS: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Store exam paper (upload or create)
     */
    public function storeExamPaper(Request $request)
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');

        if ($userType !== 'Teacher' || ! $teacherID) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'examID' => 'required|exists:examinations,examID',
            'class_subjectID' => 'required|exists:class_subjects,class_subjectID',
            'upload_type' => 'required|in:upload',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'optional_ranges' => 'nullable|array',
            'optional_ranges.*' => 'integer|min:1|max:100',
            'optional_required_counts' => 'nullable|array',
            'optional_required_counts.*' => 'integer|min:1|max:100',
        ]);

        $school = School::find(Session::get('schoolID'));
        $schoolType = $school && $school->school_type ? strtolower($school->school_type) : 'secondary';
        $requiresQuestionFormat = $schoolType === 'secondary';

        if ($requiresQuestionFormat) {
            $validator->after(function ($validator) use ($request) {
                $descriptions = $request->input('question_descriptions', []);
                $marks = $request->input('question_marks', []);
                $optionals = $request->input('question_optional', []);
                $optionalRanges = $request->input('optional_ranges', []);
                $optionalRequiredCounts = $request->input('optional_required_counts', []);
                $optionalEnabled = is_array($optionalRanges) && count($optionalRanges) > 0;
                $optionalTotals = [];
                if ($optionalEnabled) {
                    foreach ($optionalRanges as $rangeNumber => $totalMarks) {
                        $rangeNumber = (int) $rangeNumber;
                        $totalMarks = (int) $totalMarks;
                        if ($rangeNumber <= 0 || $totalMarks <= 0 || $totalMarks >= 100) {
                            $validator->errors()->add('optional_ranges', 'Optional range total marks must be between 1 and 99.');
                            return;
                        }
                        $optionalTotals[$rangeNumber] = $totalMarks;
                    }
                }

                if (! is_array($descriptions) || count($descriptions) === 0) {
                    $validator->errors()->add('question_descriptions', 'Please add at least one question format.');
                    return;
                }

                if (! is_array($marks) || count($marks) === 0) {
                    $validator->errors()->add('question_marks', 'Please provide marks for each question.');
                    return;
                }

                if (count($descriptions) !== count($marks)) {
                    $validator->errors()->add('question_marks', 'Question descriptions and marks do not match.');
                    return;
                }

                $requiredTotal = 0;
                $optionalSum = 0;
                foreach ($descriptions as $index => $description) {
                    $description = trim((string) $description);
                    $markValue = $marks[$index] ?? null;
                    $optionalRange = isset($optionals[$index]) ? (int) $optionals[$index] : 0;
                    $isOptional = $optionalRange > 0;

                    if ($description === '') {
                        $validator->errors()->add("question_descriptions.{$index}", 'Question description is required.');
                        return;
                    }

                    if (! is_numeric($markValue) || (int) $markValue <= 0) {
                        $validator->errors()->add("question_marks.{$index}", 'Each question must have a valid marks value.');
                        return;
                    }

                    if ($isOptional) {
                        $optionalSum += (int) $markValue;
                    } else {
                        $requiredTotal += (int) $markValue;
                    }
                }

                if ($optionalEnabled) {
                    $optionalSumByRange = [];
                    $optionalCountByRange = [];
                    foreach ($descriptions as $index => $description) {
                        $rangeNumber = isset($optionals[$index]) ? (int) $optionals[$index] : 0;
                        if ($rangeNumber > 0) {
                            $optionalSumByRange[$rangeNumber] = ($optionalSumByRange[$rangeNumber] ?? 0) + (int) ($marks[$index] ?? 0);
                            $optionalCountByRange[$rangeNumber] = ($optionalCountByRange[$rangeNumber] ?? 0) + 1;
                        }
                    }

                    $optionalTotalSum = array_sum($optionalTotals);
                    if ($optionalTotalSum > 100) {
                        $validator->errors()->add('optional_ranges', 'Optional range totals cannot exceed 100.');
                        return;
                    }

                    foreach ($optionalRequiredCounts as $rangeNumber => $requiredCount) {
                        $rangeNumber = (int) $rangeNumber;
                        $requiredCount = (int) $requiredCount;
                        if ($requiredCount <= 0) {
                            $validator->errors()->add('optional_required_counts', 'Required optional questions must be at least 1.');
                            return;
                        }
                        $available = $optionalCountByRange[$rangeNumber] ?? 0;
                        if ($requiredCount > $available) {
                            $validator->errors()->add('optional_required_counts', "Required optional questions exceed available questions for range {$rangeNumber}.");
                            return;
                        }
                    }

                    foreach ($optionalTotals as $rangeNumber => $rangeTotal) {
                        $sum = $optionalSumByRange[$rangeNumber] ?? 0;
                        if ($sum < $rangeTotal) {
                            $validator->errors()->add('question_marks', "Optional range {$rangeNumber} total must be at least {$rangeTotal}.");
                            return;
                        }
                    }

                    if ($requiredTotal > (100 - $optionalTotalSum)) {
                        $validator->errors()->add('question_marks', 'Required questions exceed allowed total.');
                        return;
                    }
                    if (($requiredTotal + $optionalTotalSum) !== 100) {
                        $validator->errors()->add('question_marks', 'Required total plus optional totals must be exactly 100.');
                        return;
                    }
                } else if (($requiredTotal + $optionalSum) !== 100) {
                    $validator->errors()->add('question_marks', 'Total marks must be exactly 100.');
                    return;
                }
            });
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify teacher owns this class_subject
        $classSubject = ClassSubject::where('class_subjectID', $request->class_subjectID)
            ->where('teacherID', $teacherID)
            ->where('status', 'Active')
            ->first();

        if (! $classSubject) {
            return response()->json(['error' => 'You do not teach this subject'], 403);
        }

        // Verify examination is approved and has upload_paper enabled
        $examination = Examination::where('examID', $request->examID)
            ->where('schoolID', Session::get('schoolID'))
            ->where('approval_status', 'Approved')
            ->where('upload_paper', true)
            ->whereIn('status', ['scheduled', 'ongoing', 'awaiting_results'])
            ->first();

        if (! $examination) {
            return response()->json(['error' => 'You can only upload exam papers for examinations with upload paper enabled'], 422);
        }

        $existingPaper = ExamPaper::where('examID', $request->examID)
            ->where('class_subjectID', $request->class_subjectID)
            ->where('teacherID', $teacherID)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existingPaper && $existingPaper->status !== 'rejected') {
            return response()->json([
                'error' => 'You already submitted this exam paper. Please use the edit option to update.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $examPaper = new ExamPaper;
            $examPaper->examID = $request->examID;
            $examPaper->class_subjectID = $request->class_subjectID;
            $examPaper->teacherID = $teacherID;
            $examPaper->upload_type = 'upload';
            $examPaper->status = 'wait_approval';

            $file = $request->file('file');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('exam_papers', $fileName, 'public');
            $examPaper->file_path = $filePath;
            $examPaper->question_content = null;
            $examPaper->optional_question_total = null;

            $examPaper->save();

            if ($requiresQuestionFormat) {
                $descriptions = $request->input('question_descriptions', []);
                $marks = $request->input('question_marks', []);
                $optionals = $request->input('question_optional', []);
                $optionalRanges = $request->input('optional_ranges', []);
                $optionalRequiredCounts = $request->input('optional_required_counts', []);
                $optionalTotals = [];
                if (is_array($optionalRanges)) {
                    foreach ($optionalRanges as $rangeNumber => $totalMarks) {
                        $rangeNumber = (int) $rangeNumber;
                        $totalMarks = (int) $totalMarks;
                        if ($rangeNumber > 0 && $totalMarks > 0) {
                            $optionalTotals[$rangeNumber] = $totalMarks;
                        }
                    }
                }
                $optionalRequiredMap = [];
                if (is_array($optionalRequiredCounts)) {
                    foreach ($optionalRequiredCounts as $rangeNumber => $requiredCount) {
                        $rangeNumber = (int) $rangeNumber;
                        $requiredCount = (int) $requiredCount;
                        if ($rangeNumber > 0 && $requiredCount > 0) {
                            $optionalRequiredMap[$rangeNumber] = $requiredCount;
                        }
                    }
                }

                foreach ($descriptions as $index => $description) {
                    $markValue = $marks[$index] ?? null;
                    ExamPaperQuestion::create([
                        'exam_paperID' => $examPaper->exam_paperID,
                        'question_number' => $index + 1,
                        'is_optional' => isset($optionals[$index]) ? (int) $optionals[$index] > 0 : false,
                        'optional_range_number' => isset($optionals[$index]) && (int) $optionals[$index] > 0 ? (int) $optionals[$index] : null,
                        'question_description' => trim((string) $description),
                        'marks' => (int) $markValue,
                    ]);
                }

                foreach ($optionalTotals as $rangeNumber => $totalMarks) {
                    ExamPaperOptionalRange::create([
                        'exam_paperID' => $examPaper->exam_paperID,
                        'range_number' => $rangeNumber,
                        'total_marks' => $totalMarks,
                        'required_questions' => $optionalRequiredMap[$rangeNumber] ?? 1,
                    ]);
                }
            }

            ExamPaperNotification::create([
                'schoolID' => Session::get('schoolID'),
                'exam_paperID' => $examPaper->exam_paperID,
                'teacherID' => $teacherID,
                'is_read' => false,
            ]);

            DB::commit();

            // Send SMS to school phone for approval notification
            try {
                $school = \App\Models\School::find(Session::get('schoolID'));
                $schoolPhone = $school ? $school->phone : null;
                if ($schoolPhone) {
                    $teacherName = trim(($classSubject->teacher->first_name ?? '') . ' ' . ($classSubject->teacher->last_name ?? ''));
                    $subjectName = $classSubject->subject->subject_name ?? 'somo';
                    $mainClass = $classSubject->class->class_name ?? ($classSubject->subclass->class->class_name ?? 'N/A');
                    $subclassName = $classSubject->subclass->subclass_name ?? '';
                    $classDisplay = trim($mainClass . ' ' . $subclassName);
                    $examName = $examination->exam_name ?? 'Mtihani';
                    $dateText = now()->format('Y-m-d H:i');

                    $smsMessage = "New exam paper {$examName} uploaded by teacher: {$teacherName}, subject: {$subjectName}, class: {$classDisplay}, date: {$dateText}. Login to approve now.";

                    $smsService = new SmsService();
                    $smsResult = $smsService->sendSms($schoolPhone, $smsMessage);

                    if (!($smsResult['success'] ?? false)) {
                        Log::warning("Failed to send school SMS for exam paper {$examPaper->exam_paperID}: " . ($smsResult['message'] ?? 'Unknown error'));
                    }
                }
            } catch (\Exception $smsException) {
                Log::error('Error sending school SMS for exam paper: '.$smsException->getMessage());
            }

            return response()->json([
                'success' => 'Exam paper submitted successfully. Waiting for approval.',
                'exam_paper' => $examPaper,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing exam paper: '.$e->getMessage());

            return response()->json(['error' => 'Failed to submit exam paper. Please try again.'], 500);
        }
    }

    /**
     * Get exam papers for a specific examination
     */
    public function getExamPapers(Request $request, $examID)
    {
        // Check permission - Admin can always access, others need view_exam_papers permission
        if (! $this->hasPermission('view_exam_papers')) {
            return response()->json(['error' => 'You are not allowed to perform this action. You need the view_exam_papers permission to view exam papers.'], 403);
        }

        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $class_subjectID = $request->input('class_subjectID', '');

        $query = ExamPaper::where('examID', $examID)
            ->with([
                'examination',
                'classSubject.subject',
                'classSubject.class',
                'classSubject.subclass',
                'teacher',
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('classSubject.subject', function ($subQ) use ($search) {
                    $subQ->where('subject_name', 'like', "%{$search}%");
                })->orWhereHas('classSubject.class', function ($subQ) use ($search) {
                    $subQ->where('class_name', 'like', "%{$search}%");
                })->orWhereHas('classSubject.subclass', function ($subQ) use ($search) {
                    $subQ->where('subclass_name', 'like', "%{$search}%");
                })->orWhereHas('teacher', function ($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($class_subjectID) {
            $query->where('class_subjectID', $class_subjectID);
        }

        $examPapers = $query->orderBy('created_at', 'desc')
            ->with(['questions', 'optionalRanges'])
            ->get();

        return response()->json([
            'success' => true,
            'exam_papers' => $examPapers,
        ]);
    }

    /**
     * Approve or reject exam paper
     */
    public function approveRejectExamPaper(Request $request, $examPaperID)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
            'approval_comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check permission based on action
        $action = $request->action;
        if ($action === 'approve') {
            if (! $this->hasPermission('approve_exam_paper')) {
                return response()->json(['error' => 'You are not allowed to perform this action. You need the approve_exam_paper permission to approve exam papers.'], 403);
            }
        } else {
            if (! $this->hasPermission('reject_exam_paper')) {
                return response()->json(['error' => 'You are not allowed to perform this action. You need the reject_exam_paper permission to reject exam papers.'], 403);
            }
        }

        try {
            $examPaper = ExamPaper::with(['teacher', 'classSubject.subject', 'examination'])->findOrFail($examPaperID);
            $teacher = $examPaper->teacher;
            $subject = $examPaper->classSubject->subject ?? null;
            $examination = $examPaper->examination ?? null;
            $schoolID = Session::get('schoolID');
            $school = \App\Models\School::find($schoolID);
            $schoolName = $school ? $school->school_name : 'ShuleLink';

            if ($request->action === 'approve') {
                $examPaper->status = 'approved';
                $examPaper->approval_comment = $request->approval_comment;
                $examPaper->rejection_reason = null;
                $message = 'Exam paper approved successfully';
                
                // Send SMS to teacher
                if ($teacher && $teacher->phone_number) {
                    try {
                        $smsService = new SmsService();
                        $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''));
                        $subjectName = $subject ? $subject->subject_name : 'somo';
                        $examName = $examination ? $examination->exam_name : 'mtihani';
                        $comment = $request->approval_comment ? " Maoni: {$request->approval_comment}" : '';
                        
                        $smsMessage = "{$schoolName}. Mwalimu {$teacherName}, karatasi ya mtihani ya {$subjectName} kwa {$examName} imekubaliwa.{$comment} Asante";
                        
                        $smsResult = $smsService->sendSms($teacher->phone_number, $smsMessage);
                        
                        if (!$smsResult['success']) {
                            Log::warning("Failed to send approval SMS to teacher {$teacher->id}: " . ($smsResult['message'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $smsException) {
                        Log::error('Error sending approval SMS to teacher: '.$smsException->getMessage());
                    }
                }
            } else {
                $examPaper->status = 'rejected';
                $examPaper->rejection_reason = $request->rejection_reason;
                $examPaper->approval_comment = null;
                $message = 'Exam paper rejected successfully';
                
                // Send SMS to teacher
                if ($teacher && $teacher->phone_number) {
                    try {
                        $smsService = new SmsService();
                        $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''));
                        $subjectName = $subject ? $subject->subject_name : 'somo';
                        $examName = $examination ? $examination->exam_name : 'mtihani';
                        $reason = $request->rejection_reason ?? 'Hakuna sababu iliyotolewa';
                        
                        $smsMessage = "{$schoolName}. Mwalimu {$teacherName}, karatasi ya mtihani ya {$subjectName} kwa {$examName} imekataliwa. Sababu: {$reason}";
                        
                        $smsResult = $smsService->sendSms($teacher->phone_number, $smsMessage);
                        
                        if (!$smsResult['success']) {
                            Log::warning("Failed to send rejection SMS to teacher {$teacher->id}: " . ($smsResult['message'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $smsException) {
                        Log::error('Error sending rejection SMS to teacher: '.$smsException->getMessage());
                    }
                }
            }

            $examPaper->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'exam_paper' => $examPaper,
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving/rejecting exam paper: '.$e->getMessage());

            return response()->json(['error' => 'Failed to process request'], 500);
        }
    }

    /**
     * Update exam paper (Teacher - only if pending)
     */
    public function updateExamPaper(Request $request, $examPaperID)
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');

        if ($userType !== 'Teacher' || ! $teacherID) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'upload_type' => 'required|in:upload',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $examPaper = ExamPaper::where('exam_paperID', $examPaperID)
                ->where('teacherID', $teacherID)
                ->firstOrFail();

            if ($examPaper->status !== 'wait_approval') {
                return response()->json(['error' => 'You can only edit exam papers that are pending approval'], 422);
            }

            DB::beginTransaction();

            $examPaper->upload_type = 'upload';

            // Delete old file if exists
            if ($examPaper->file_path && Storage::disk('public')->exists($examPaper->file_path)) {
                Storage::disk('public')->delete($examPaper->file_path);
            }

            $file = $request->file('file');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('exam_papers', $fileName, 'public');
            $examPaper->file_path = $filePath;
            $examPaper->question_content = null;

            $examPaper->save();

            DB::commit();

            return response()->json([
                'success' => 'Exam paper updated successfully',
                'exam_paper' => $examPaper,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating exam paper: '.$e->getMessage());

            return response()->json(['error' => 'Failed to update exam paper'], 500);
        }
    }

    public function getExamPaperQuestions($examPaperID)
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');

        if ($userType !== 'Teacher' || ! $teacherID) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $examPaper = ExamPaper::where('exam_paperID', $examPaperID)
                ->where('teacherID', $teacherID)
                ->firstOrFail();

            $questions = ExamPaperQuestion::where('exam_paperID', $examPaper->exam_paperID)
                ->orderBy('question_number')
                ->get([
                    'exam_paper_questionID',
                    'question_number',
                    'question_description',
                    'marks',
                    'is_optional',
                    'optional_range_number',
                ]);

            $optionalRanges = ExamPaperOptionalRange::where('exam_paperID', $examPaper->exam_paperID)
                ->orderBy('range_number')
                ->get(['range_number', 'total_marks']);

            return response()->json([
                'success' => true,
                'questions' => $questions,
                'optional_ranges' => $optionalRanges,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load questions'], 500);
        }
    }

    public function updateExamPaperQuestions(Request $request, $examPaperID)
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');

        if ($userType !== 'Teacher' || ! $teacherID) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'question_descriptions' => 'required|array|min:1',
            'question_descriptions.*' => 'required|string|max:500',
            'question_marks' => 'required|array|min:1',
            'question_marks.*' => 'required|integer|min:1|max:100',
            'question_optional' => 'nullable|array',
            'optional_ranges' => 'nullable|array',
            'optional_ranges.*' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $examPaper = ExamPaper::where('exam_paperID', $examPaperID)
                ->where('teacherID', $teacherID)
                ->firstOrFail();

            if ($examPaper->status !== 'wait_approval') {
                return response()->json(['error' => 'You can only edit questions for pending papers'], 422);
            }

            $descriptions = $request->input('question_descriptions', []);
            $marks = $request->input('question_marks', []);
            $optionals = $request->input('question_optional', []);
            $optionalRanges = $request->input('optional_ranges', []);
            $optionalTotals = [];

            if (is_array($optionalRanges)) {
                foreach ($optionalRanges as $rangeNumber => $totalMarks) {
                    $rangeNumber = (int) $rangeNumber;
                    $totalMarks = (int) $totalMarks;
                    if ($rangeNumber > 0 && $totalMarks > 0 && $totalMarks < 100) {
                        $optionalTotals[$rangeNumber] = $totalMarks;
                    }
                }
            }

            $optionalTotalSum = array_sum($optionalTotals);
            if ($optionalTotalSum > 100) {
                return response()->json(['error' => 'Optional range totals cannot exceed 100.'], 422);
            }

            $requiredTotal = 0;
            $optionalSumByRange = [];
            $optionalCountByRange = [];
            foreach ($descriptions as $index => $description) {
                $markValue = $marks[$index] ?? null;
                $rangeNumber = isset($optionals[$index]) ? (int) $optionals[$index] : 0;
                $markValue = (int) $markValue;
                if ($rangeNumber > 0) {
                    $optionalSumByRange[$rangeNumber] = ($optionalSumByRange[$rangeNumber] ?? 0) + $markValue;
                    $optionalCountByRange[$rangeNumber] = ($optionalCountByRange[$rangeNumber] ?? 0) + 1;
                } else {
                    $requiredTotal += $markValue;
                }
            }

            $optionalRequiredCounts = $request->input('optional_required_counts', []);
            if (is_array($optionalRequiredCounts)) {
                foreach ($optionalRequiredCounts as $rangeNumber => $requiredCount) {
                    $rangeNumber = (int) $rangeNumber;
                    $requiredCount = (int) $requiredCount;
                    $available = $optionalCountByRange[$rangeNumber] ?? 0;
                    if ($requiredCount > $available) {
                        return response()->json(['error' => "Required optional questions exceed available questions for range {$rangeNumber}."], 422);
                    }
                }
            }

            foreach ($optionalTotals as $rangeNumber => $rangeTotal) {
                $sum = $optionalSumByRange[$rangeNumber] ?? 0;
                if ($sum < $rangeTotal) {
                    return response()->json(['error' => "Optional range {$rangeNumber} total must be at least {$rangeTotal}."], 422);
                }
            }

            if ($requiredTotal > (100 - $optionalTotalSum)) {
                return response()->json(['error' => 'Required questions exceed allowed total.'], 422);
            }

            if (($requiredTotal + $optionalTotalSum) !== 100) {
                return response()->json(['error' => 'Required total plus optional totals must be exactly 100.'], 422);
            }

            DB::beginTransaction();

            ExamPaperQuestion::where('exam_paperID', $examPaper->exam_paperID)->delete();
            ExamPaperOptionalRange::where('exam_paperID', $examPaper->exam_paperID)->delete();

            foreach ($descriptions as $index => $description) {
                $rangeNumber = isset($optionals[$index]) ? (int) $optionals[$index] : 0;
                ExamPaperQuestion::create([
                    'exam_paperID' => $examPaper->exam_paperID,
                    'question_number' => $index + 1,
                    'is_optional' => $rangeNumber > 0,
                    'optional_range_number' => $rangeNumber > 0 ? $rangeNumber : null,
                    'question_description' => trim((string) $description),
                    'marks' => (int) ($marks[$index] ?? 0),
                ]);
            }

            foreach ($optionalTotals as $rangeNumber => $totalMarks) {
                ExamPaperOptionalRange::create([
                    'exam_paperID' => $examPaper->exam_paperID,
                    'range_number' => $rangeNumber,
                    'total_marks' => $totalMarks,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => 'Exam paper questions updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update exam paper questions'], 500);
        }
    }

    public function markExamPaperNotificationsRead()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if ($userType !== 'Admin' || ! $schoolID) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        ExamPaperNotification::where('schoolID', $schoolID)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Get my exam papers (Teacher)
     */
    public function getMyExamPapers()
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');

        if ($userType !== 'Teacher' || ! $teacherID) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $examPapers = ExamPaper::where('teacherID', $teacherID)
            ->with([
                'examination',
                'classSubject.subject',
                'classSubject.class',
                'classSubject.subclass',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'exam_papers' => $examPapers,
        ]);
    }

    /**
     * Download exam paper file
     */
    public function downloadExamPaper($examPaperID)
    {
        try {
            $examPaper = ExamPaper::findOrFail($examPaperID);
            $userType = Session::get('user_type');
            $teacherID = Session::get('teacherID');

            // Check access: Admin needs view_exam_papers permission, Teacher can only download their own
            if ($userType === 'Admin') {
                if (! $this->hasPermission('view_exam_papers')) {
                    abort(403, 'You are not allowed to perform this action. You need the view_exam_papers permission to view exam papers.');
                }
            } elseif ($userType === 'Teacher') {
                // Teacher can only download their own exam papers
                if ($examPaper->teacherID != $teacherID) {
                    abort(403, 'Unauthorized access');
                }
            } else {
                abort(403, 'Unauthorized access');
            }

            if (! $examPaper->file_path || ! Storage::disk('public')->exists($examPaper->file_path)) {
                abort(404, 'File not found');
            }

            $filePath = Storage::disk('public')->path($examPaper->file_path);
            $fileName = basename($examPaper->file_path);
            $mimeType = Storage::disk('public')->mimeType($examPaper->file_path);

            // Check if inline display is requested (for PDF preview)
            $inline = request()->get('inline', false);

            if ($inline || $mimeType === 'application/pdf') {
                // Return file with inline disposition for PDF preview
                return response()->file($filePath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="'.$fileName.'"',
                ]);
            } else {
                // Force download for other file types
                return Storage::disk('public')->download($examPaper->file_path);
            }
        } catch (\Exception $e) {
            Log::error('Error downloading exam paper: '.$e->getMessage());
            abort(404, 'File not found');
        }
    }

    /**
     * Delete exam paper (Only for rejected papers by the uploader)
     */
    public function deleteExamPaper($examPaperID)
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');

        if ($userType !== 'Teacher' || ! $teacherID) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $examPaper = ExamPaper::where('exam_paperID', $examPaperID)
                ->where('teacherID', $teacherID)
                ->firstOrFail();

            // Only allow deletion if status is rejected
            if ($examPaper->status !== 'rejected') {
                return response()->json([
                    'error' => 'You can only delete exam papers that have been rejected',
                ], 422);
            }

            DB::beginTransaction();

            // Delete file if exists
            if ($examPaper->file_path && Storage::disk('public')->exists($examPaper->file_path)) {
                Storage::disk('public')->delete($examPaper->file_path);
            }

            // Delete the exam paper record
            $examPaper->delete();

            DB::commit();

            return response()->json([
                'success' => 'Exam paper deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting exam paper: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to delete exam paper: '.$e->getMessage(),
            ], 500);
        }
    }

    public function toggleEnterResult(Request $request, $examID)
    {
        // Check permission - Update action: examination_update
        if (! $this->hasPermission('examination_update')) {
            return response()->json([
                'error' => 'You do not have permission to manage result entry. You need examination_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'enter_result' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Convert to boolean
            $enterResult = filter_var($request->enter_result, FILTER_VALIDATE_BOOLEAN);

            $exam->update([
                'enter_result' => $enterResult,
            ]);

            // Get all teachers involved in this examination and send SMS
            try {
                $smsService = new SmsService();
                $school = \App\Models\School::find($schoolID);
                $schoolName = $school ? $school->school_name : 'ShuleLink';
                
                // Get participating class IDs based on exam category
                $participatingClassIds = [];
                
                if ($exam->exam_category === 'school_exams' || $exam->exam_category === 'test') {
                    // For school_exams and test: all classes except excluded ones
                    $allClasses = ClassModel::where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->pluck('classID')
                        ->toArray();
                    
                    $exceptClassIds = $exam->except_class_ids ?? [];
                    if (!empty($exceptClassIds) && is_array($exceptClassIds)) {
                        $participatingClassIds = array_diff($allClasses, $exceptClassIds);
                    } else {
                        $participatingClassIds = $allClasses;
                    }
                } elseif ($exam->exam_category === 'special_exams') {
                    // For special_exams: get classes from exam_timetables or results
                    // Try to get from results first (most reliable)
                    $participatingSubclassIds = DB::table('results')
                        ->where('examID', $examID)
                        ->distinct()
                        ->pluck('subclassID')
                        ->toArray();
                    
                    if (!empty($participatingSubclassIds)) {
                        $participatingClassIds = Subclass::whereIn('subclassID', $participatingSubclassIds)
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
                            $participatingClassIds = Subclass::whereIn('subclassID', $participatingSubclassIds)
                                ->distinct()
                                ->pluck('classID')
                                ->toArray();
                        }
                    }
                }
                
                // Get all teachers teaching subjects in participating classes
                $teachers = collect();
                
                if (!empty($participatingClassIds)) {
                    // Get teachers via class subjects
                    $teachers = Teacher::where('schoolID', $schoolID)
                        ->whereHas('classSubjects', function($query) use ($participatingClassIds) {
                            $query->where('status', 'Active')
                                ->whereHas('subclass', function($q) use ($participatingClassIds) {
                                    $q->whereIn('classID', $participatingClassIds);
                                });
                        })
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '')
                        ->distinct()
                        ->get();
                }
                
                // If no teachers found via class subjects, get all active teachers in the school
                if ($teachers->isEmpty()) {
                    $teachers = Teacher::where('schoolID', $schoolID)
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '')
                        ->get();
                }
                
                $smsSentCount = 0;
                $examName = $exam->exam_name;
                
                foreach ($teachers as $teacher) {
                    try {
                        $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''));
                        
                        if ($enterResult) {
                            $message = "{$schoolName}. Habari {$teacherName}, mtihani '{$examName}' umeruhusiwa kujaza matokeo. Tafadhali jaza matokeo kwa wanafunzi wako. Asante";
                        } else {
                            $message = "{$schoolName}. Habari {$teacherName}, kujaza matokeo kwa mtihani '{$examName}' kumekatizwa. Tafadhali usijaze matokeo mpaka kuruhusiwa tena. Asante";
                        }
                        
                        $smsResult = $smsService->sendSms($teacher->phone_number, $message);
                        
                        if ($smsResult['success']) {
                            $smsSentCount++;
                        } else {
                            Log::warning("Failed to send SMS to teacher {$teacher->id} for enter_result toggle: " . ($smsResult['message'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $smsException) {
                        Log::error("Error sending SMS to teacher {$teacher->id}: " . $smsException->getMessage());
                    }
                }
                
                $successMessage = $enterResult 
                    ? 'Teachers can now enter results for this examination.'
                    : 'Result entry has been disabled for this examination.';
                
                if ($smsSentCount > 0) {
                    $successMessage .= " SMS zimetumwa kwa walimu {$smsSentCount}.";
                }
                
                return response()->json([
                    'success' => $successMessage,
                    'exam' => $exam,
                    'sms_sent_count' => $smsSentCount,
                ], 200);
                
            } catch (\Exception $smsError) {
                // Log SMS error but don't fail the request
                Log::error('Error sending SMS to teachers for enter_result toggle: ' . $smsError->getMessage());
                
                return response()->json([
                    'success' => $enterResult 
                        ? 'Teachers can now enter results for this examination. (Note: SMS notification failed)'
                        : 'Result entry has been disabled for this examination. (Note: SMS notification failed)',
                    'exam' => $exam,
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function togglePublishResult(Request $request, $examID)
    {
        // Check permission - Update action: examination_update
        if (! $this->hasPermission('examination_update')) {
            return response()->json([
                'error' => 'You do not have permission to publish results. You need examination_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'publish_result' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Convert to boolean
            $publishResult = filter_var($request->publish_result, FILTER_VALIDATE_BOOLEAN);

            // Check result approvals if trying to publish
            if ($publishResult) {
                // Get all result approvals for this exam
                $resultApprovals = ResultApproval::where('examID', $examID)
                    ->orderBy('approval_order')
                    ->get();

                // If result approval chain exists, check if all approvals are done
                if ($resultApprovals->count() > 0) {
                    // Check if all approvals are approved
                    $allApproved = $resultApprovals->every(function ($approval) {
                        return $approval->status === 'approved';
                    });

                    if (! $allApproved) {
                        // Find the first pending approval
                        $firstPendingApproval = $resultApprovals->firstWhere('status', 'pending');
                        
                        if ($firstPendingApproval) {
                            // Get role name
                            $role = Role::find($firstPendingApproval->role_id);
                            $roleName = $role ? ($role->name ?? $role->role_name ?? 'Unknown Role') : 'Unknown Role';
                            
                            return response()->json([
                                'error' => "Can't publish result. Wait approval of {$roleName}.",
                                'pending_approval' => [
                                    'role_id' => $firstPendingApproval->role_id,
                                    'role_name' => $roleName,
                                    'approval_order' => $firstPendingApproval->approval_order,
                                ],
                            ], 422);
                        } else {
                            // Check if any approval was rejected
                            $rejectedApproval = $resultApprovals->firstWhere('status', 'rejected');
                            if ($rejectedApproval) {
                                $role = Role::find($rejectedApproval->role_id);
                                $roleName = $role ? ($role->name ?? $role->role_name ?? 'Unknown Role') : 'Unknown Role';
                                
                                return response()->json([
                                    'error' => "Can't publish result. Approval was rejected by {$roleName}.",
                                ], 422);
                            }
                        }
                    }
                }
            }

            $exam->update([
                'publish_result' => $publishResult,
            ]);

            // Send SMS to parents when publishing results
            $smsSentCount = 0;
            if ($publishResult) {
                try {
                    $smsSentCount = $this->sendResultsSMSToParents($examID, $schoolID);
                } catch (\Exception $e) {
                    Log::error('Error sending SMS after publishing results: '.$e->getMessage());
                    // Continue even if SMS fails
                }
            }

            $successMessage = $publishResult 
                ? 'Results have been published and are now visible to all.'
                : 'Results have been unpublished.';
            
            if ($publishResult && $smsSentCount > 0) {
                $successMessage .= ' SMS zimetumwa kwa wazazi ' . $smsSentCount . ' wanaofunzi.';
            }

            return response()->json([
                'success' => $successMessage,
                'exam' => $exam,
                'sms_sent_count' => $smsSentCount,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function toggleUploadPaper(Request $request, $examID)
    {
        // Check permission - Update action: examination_update
        if (! $this->hasPermission('examination_update')) {
            return response()->json([
                'error' => 'You do not have permission to manage paper upload. You need examination_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'upload_paper' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Convert to boolean
            $uploadPaper = filter_var($request->upload_paper, FILTER_VALIDATE_BOOLEAN);

            $exam->update([
                'upload_paper' => $uploadPaper,
            ]);

            return response()->json([
                'success' => $uploadPaper 
                    ? 'Teachers can now upload exam papers for this examination.'
                    : 'Exam paper upload has been disabled for this examination.',
                'exam' => $exam,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Auto shift students based on exam results
     */
    public function autoShiftStudents(Request $request, $examID)
    {
        // Check permission - Update action: examination_update
        if (! $this->hasPermission('examination_update')) {
            return response()->json([
                'error' => 'You do not have permission to shift students. You need examination_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            // Check if exam has student_shifting_status set
            if (! $exam->student_shifting_status || $exam->student_shifting_status === 'none') {
                return response()->json([
                    'error' => 'This examination does not have student shifting enabled.',
                ], 400);
            }

            // Perform auto transfer
            $transferResult = $this->autoTransferStudentsForExam($examID, $schoolID, $exam->student_shifting_status);
            $transferredCount = $transferResult['transferred'] ?? 0;
            $graduatedCount = $transferResult['graduated'] ?? 0;

            $successMessage = 'Student shifting completed successfully.';
            if ($transferredCount > 0) {
                $successMessage .= " {$transferredCount} mwanafunzi wamehamishwa darasa.";
            }
            if ($graduatedCount > 0) {
                $successMessage .= " {$graduatedCount} mwanafunzi wamehitimu.";
            }
            if ($transferredCount === 0 && $graduatedCount === 0) {
                $successMessage = 'Hakuna wanafunzi waliohamishwa. Hakikisha kuwa matokeo yameingizwa na wanafunzi wana darasa sahihi.';
            }

            return response()->json([
                'success' => $successMessage,
                'transferred_count' => $transferredCount,
                'graduated_count' => $graduatedCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Auto shift students error: '.$e->getMessage());
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unshift students (revert to previous classes)
     */
    /**
     * Update exam attendance status for a student
     */
    public function updateExamAttendance(Request $request, $examID)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'studentID' => 'required|exists:students,studentID',
                'subjectID' => 'required|exists:school_subjects,subjectID',
                'status' => 'required|in:Present,Absent',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Verify exam exists and belongs to school
            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            // Verify student belongs to school
            $student = Student::where('studentID', $request->studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $student) {
                return response()->json([
                    'error' => 'Student not found.',
                ], 404);
            }

            // Update or create exam attendance record
            DB::table('exam_attendance')
                ->updateOrInsert(
                    [
                        'examID' => $examID,
                        'studentID' => $request->studentID,
                        'subjectID' => $request->subjectID,
                    ],
                    [
                        'status' => $request->status,
                        'updated_at' => now(),
                    ]
                );

            return response()->json([
                'success' => 'Exam attendance updated successfully!',
                'status' => $request->status,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function unshiftStudents(Request $request, $examID)
    {
        // Check permission - Update action: examination_update
        if (! $this->hasPermission('examination_update')) {
            return response()->json([
                'error' => 'You do not have permission to unshift students. You need examination_update permission.',
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (! $schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.',
                ], 400);
            }

            $exam = Examination::where('examID', $examID)
                ->where('schoolID', $schoolID)
                ->first();

            if (! $exam) {
                return response()->json([
                    'error' => 'Examination not found.',
                ], 404);
            }

            // Get all students who have results in this exam and have old_subclassID
            $studentIDs = DB::table('results')
                ->where('examID', $examID)
                ->distinct()
                ->pluck('studentID');

            if ($studentIDs->isEmpty()) {
                return response()->json([
                    'error' => 'No students found for this examination.',
                ], 404);
            }

            $unshiftedCount = 0;
            $revertedCount = 0;

            foreach ($studentIDs as $studentID) {
                $student = Student::find($studentID);

                if (! $student || $student->schoolID != $schoolID) {
                    continue;
                }

                // Check if student has old_subclassID (was shifted)
                if ($student->old_subclassID) {
                    $oldSubclass = Subclass::find($student->old_subclassID);
                    
                    if ($oldSubclass && $oldSubclass->class && $oldSubclass->class->schoolID == $schoolID) {
                        // Revert student to previous class
                        $currentSubclassID = $student->subclassID;
                        $student->subclassID = $student->old_subclassID;
                        $student->old_subclassID = null;
                        
                        // If student was graduated, change back to Active
                        if ($student->status === 'Graduated') {
                            $student->status = 'Active';
                            $revertedCount++;
                        } else {
                            $unshiftedCount++;
                        }
                        
                        $student->save();
                    }
                }
            }

            $successMessage = 'Student unshifting completed successfully.';
            if ($unshiftedCount > 0) {
                $successMessage .= " {$unshiftedCount} mwanafunzi wamerudi darasa la zamani.";
            }
            if ($revertedCount > 0) {
                $successMessage .= " {$revertedCount} mwanafunzi wamebadilishwa kutoka Graduated kwenda Active.";
            }
            if ($unshiftedCount === 0 && $revertedCount === 0) {
                $successMessage = 'Hakuna wanafunzi waliorudi darasa la zamani. Hakikisha kuwa wanafunzi walihamishwa awali.';
            }

            return response()->json([
                'success' => $successMessage,
                'unshifted_count' => $unshiftedCount,
                'reverted_count' => $revertedCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Unshift students error: '.$e->getMessage());
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build hall payload and validate capacities against class student counts.
     */
    private function buildHallPayload($names, $classIds, $capacities, $genders, $schoolID, $examCategory, $exceptClassIds = [], $includeClassIds = [])
    {
        $payload = [];
        $errors = [];

        $countNames = is_array($names) ? count($names) : 0;
        for ($i = 0; $i < $countNames; $i++) {
            $hallName = $names[$i] ?? null;
            $classID = $classIds[$i] ?? null;
            $capacity = isset($capacities[$i]) ? (int) $capacities[$i] : 0;
            $gender = $genders[$i] ?? null;

            if (! $hallName || ! $classID || ! $capacity || ! $gender) {
                continue;
            }

            $class = ClassModel::where('classID', $classID)->where('schoolID', $schoolID)->first();
            if (! $class) {
                $errors[] = "Selected class is invalid for hall {$hallName}.";
                continue;
            }

            if (in_array($examCategory, ['school_exams', 'test']) && $exceptClassIds && in_array($classID, $exceptClassIds)) {
                $errors[] = "Hall {$hallName}: class is excluded from this exam.";
                continue;
            }
            if ($examCategory === 'special_exams' && (! $includeClassIds || ! in_array($classID, $includeClassIds))) {
                $errors[] = "Hall {$hallName}: class not included for this special exam.";
                continue;
            }

            $payload[] = [
                'hall_name' => $hallName,
                'classID' => $classID,
                'capacity' => $capacity,
                'gender_allowed' => $gender,
            ];
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages(['halls' => $errors]);
        }

        // Validate capacities
        $byClass = [];
        foreach ($payload as $hall) {
            $cid = $hall['classID'];
            if (! isset($byClass[$cid])) {
                $byClass[$cid] = ['total' => 0, 'male' => 0, 'female' => 0];
            }
            $byClass[$cid]['total'] += $hall['capacity'];
            if ($hall['gender_allowed'] === 'male') {
                $byClass[$cid]['male'] += $hall['capacity'];
            } elseif ($hall['gender_allowed'] === 'female') {
                $byClass[$cid]['female'] += $hall['capacity'];
            } else {
                $byClass[$cid]['male'] += $hall['capacity'];
                $byClass[$cid]['female'] += $hall['capacity'];
            }
        }

        $capacityErrors = [];
        foreach ($byClass as $classID => $caps) {
            $counts = $this->getClassCounts($classID, $schoolID);
            if (! $counts) {
                $capacityErrors[] = "Failed to fetch student counts for class ID {$classID}.";
                continue;
            }
            if ($caps['total'] < $counts['total']) {
                $capacityErrors[] = "Class {$classID}: total hall capacity ({$caps['total']}) is less than total students ({$counts['total']}).";
            }
            if ($caps['male'] < $counts['male']) {
                $capacityErrors[] = "Class {$classID}: male hall capacity ({$caps['male']}) is less than male students ({$counts['male']}).";
            }
            if ($caps['female'] < $counts['female']) {
                $capacityErrors[] = "Class {$classID}: female hall capacity ({$caps['female']}) is less than female students ({$counts['female']}).";
            }
        }

        if (! empty($capacityErrors)) {
            throw ValidationException::withMessages(['halls' => $capacityErrors]);
        }

        return $payload;
    }

    /**
     * Get counts of students per class.
     */
    private function getClassCounts($classID, $schoolID)
    {
        $class = ClassModel::where('classID', $classID)->where('schoolID', $schoolID)->first();
        if (! $class) {
            return null;
        }
        $subclassIds = Subclass::where('classID', $classID)->pluck('subclassID')->toArray();
        $total = Student::whereIn('subclassID', $subclassIds)->where('status', 'Active')->count();
        $male = Student::whereIn('subclassID', $subclassIds)->where('status', 'Active')->where('gender', 'Male')->count();
        $female = Student::whereIn('subclassID', $subclassIds)->where('status', 'Active')->where('gender', 'Female')->count();
        return [
            'total' => $total,
            'male' => $male,
            'female' => $female,
        ];
    }

    /**
     * Allocate students to halls respecting gender/capacity.
     */
    private function allocateStudentsToHalls($examID, array $examHalls, $schoolID, $examCategory, $exceptClassIds = [], $includeClassIds = [])
    {
        $assignments = [];

        // Group halls by class
        $hallsByClass = [];
        foreach ($examHalls as $hall) {
            $cid = $hall['classID'];
            if (! isset($hallsByClass[$cid])) {
                $hallsByClass[$cid] = [];
            }
            $hall['remaining'] = (int) $hall['capacity'];
            $hallsByClass[$cid][] = $hall;
        }

        foreach ($hallsByClass as $classID => $halls) {
            if (in_array($examCategory, ['school_exams', 'test']) && $exceptClassIds && in_array($classID, $exceptClassIds)) {
                continue;
            }
            if ($examCategory === 'special_exams' && (! $includeClassIds || ! in_array($classID, $includeClassIds))) {
                continue;
            }

            $students = Student::with('subclass')
                ->whereHas('subclass', function ($q) use ($classID) {
                    $q->where('classID', $classID);
                })
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $maleQueue = $students->where('gender', 'Male')->values()->all();
            $femaleQueue = $students->where('gender', 'Female')->values()->all();

            $maleHalls = array_values(array_filter($halls, fn($h) => $h['gender_allowed'] === 'male'));
            $femaleHalls = array_values(array_filter($halls, fn($h) => $h['gender_allowed'] === 'female'));
            $bothHalls = array_values(array_filter($halls, fn($h) => $h['gender_allowed'] === 'both'));

            $assignQueue = function (&$queue, &$hallList) use (&$assignments, $examID) {
                foreach ($hallList as &$hall) {
                    while ($hall['remaining'] > 0 && ! empty($queue)) {
                        /** @var Student $student */
                        $student = array_shift($queue);
                        $assignments[] = [
                            'examID' => $examID,
                            'exam_hallID' => $hall['exam_hallID'],
                            'studentID' => $student->studentID,
                            'subclassID' => $student->subclassID,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $hall['remaining']--;
                    }
                }
            };

            // Gender-specific halls first
            $assignQueue($maleQueue, $maleHalls);
            $assignQueue($femaleQueue, $femaleHalls);

            // Both halls
            $idx = 0;
            while ((! empty($maleQueue) || ! empty($femaleQueue)) && ! empty($bothHalls)) {
                $hallIndex = $idx % count($bothHalls);
                $hall =& $bothHalls[$hallIndex];
                if ($hall['remaining'] <= 0) {
                    $idx++;
                    if ($idx > count($bothHalls) * 2) {
                        break;
                    }
                    continue;
                }

                $pickMale = count($maleQueue) >= count($femaleQueue);
                if ($pickMale && ! empty($maleQueue)) {
                    $student = array_shift($maleQueue);
                } elseif (! empty($femaleQueue)) {
                    $student = array_shift($femaleQueue);
                } elseif (! empty($maleQueue)) {
                    $student = array_shift($maleQueue);
                } else {
                    break;
                }

                $assignments[] = [
                    'examID' => $examID,
                    'exam_hallID' => $hall['exam_hallID'],
                    'studentID' => $student->studentID,
                    'subclassID' => $student->subclassID,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $hall['remaining']--;
                $idx++;
            }

            if (! empty($maleQueue) || ! empty($femaleQueue)) {
                throw ValidationException::withMessages([
                    'halls' => ["Not enough hall capacity to place all students for class {$classID} after allocation."],
                ]);
            }
        }

        if (! empty($assignments)) {
            DB::table('student_exam_halls')->insert($assignments);
        }
    }

    /**
     * Auto assign supervisors to halls (round-robin, 2 teachers if capacity > 100).
     */
    private function assignSupervisorsToHalls($examID, array $examHalls, $schoolID, $examName, $startDate, $endDate)
    {
        if (empty($examHalls)) {
            return;
        }

        DB::table('exam_hall_supervisors')->where('examID', $examID)->delete();

        $teachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get();

        if ($teachers->isEmpty()) {
            return;
        }

        $classMap = ClassModel::where('schoolID', $schoolID)
            ->whereIn('classID', collect($examHalls)->pluck('classID')->unique())
            ->pluck('class_name', 'classID');

        $inserts = [];
        $teacherIndex = 0;
        $teacherCount = $teachers->count();

        foreach ($examHalls as $hall) {
            $needed = ($hall['capacity'] > 100) ? 2 : 1;
            for ($i = 0; $i < $needed; $i++) {
                $teacher = $teachers[$teacherIndex % $teacherCount];
                $teacherIndex++;

                $inserts[] = [
                    'schoolID' => $schoolID,
                    'examID' => $examID,
                    'exam_hallID' => $hall['exam_hallID'],
                    'teacherID' => $teacher->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (! empty($teacher->phone_number)) {
                    $hallName = $hall['hall_name'];
                    $className = $classMap[$hall['classID']] ?? 'Class';
                    $genderText = $hall['gender_allowed'] === 'both' ? 'All' : ucfirst($hall['gender_allowed']);
                    $message = "Umepewa kusimamia mtihani: {$examName}. Hall: {$hallName}, Darasa: {$className}, Gender: {$genderText}. Muda: {$startDate} - {$endDate}.";
                    try {
                        (new SmsService())->sendSms($teacher->phone_number, $message);
                    } catch (\Exception $e) {
                        Log::error('Failed to send supervisor SMS: '.$e->getMessage());
                    }
                }
            }
        }

        if (! empty($inserts)) {
            DB::table('exam_hall_supervisors')->insert($inserts);
        }
    }

    /**
     * Get counts of students per class for halls listing (reuse).
     */
    private function fetchSupervisorAssignments($teacherID, $schoolID)
    {
        $today = now()->startOfDay();

        return DB::table('exam_hall_supervisors as ehs')
            ->join('exam_halls as eh', 'eh.exam_hallID', '=', 'ehs.exam_hallID')
            ->join('examinations as ex', 'ex.examID', '=', 'ehs.examID')
            ->join('classes as c', 'c.classID', '=', 'eh.classID')
            ->leftJoin('school_subjects as ss', 'ss.subjectID', '=', 'ehs.subjectID')
            ->leftJoin('exam_timetable as et', 'et.exam_timetableID', '=', 'ehs.exam_timetableID')
            ->where('ehs.teacherID', $teacherID)
            ->where('ex.schoolID', $schoolID)
            ->select(
                'ehs.exam_hall_supervisorID',
                'ehs.examID',
                'ehs.exam_hallID',
                'ehs.subjectID',
                'ehs.exam_timetableID',
                'eh.hall_name',
                'eh.gender_allowed',
                'eh.capacity',
                'eh.classID',
                'c.class_name',
                'ex.exam_name',
                'ex.start_date',
                'ex.end_date',
                'ex.term',
                'ex.exam_category',
                'ss.subject_name',
                'et.exam_date',
                'et.start_time',
                'et.end_time'
            )
            ->selectRaw('(select count(*) from student_exam_halls seh where seh.exam_hallID = eh.exam_hallID) as students_count')
            ->selectRaw('CASE WHEN ex.end_date >= ? THEN 1 ELSE 0 END as is_active', [$today])
            ->orderBy('ex.start_date', 'desc')
            ->orderBy('et.exam_date', 'asc')
            ->orderBy('et.start_time', 'asc')
            ->get();
    }

    public function getMySuperviseExams()
    {
        $userType = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if ($userType !== 'Teacher' || ! $teacherID || ! $schoolID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $assignments = $this->fetchSupervisorAssignments($teacherID, $schoolID);

        return response()->json([
            'success' => true,
            'assignments' => $assignments,
        ]);
    }

    public function getHallStudents($examHallID)
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if ($userType !== 'Teacher' || ! $teacherID || ! $schoolID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get optional subjectID and exam_timetableID from request
        $subjectID = request()->input('subjectID');
        $examTimetableID = request()->input('exam_timetableID');

        $assignmentQuery = DB::table('exam_hall_supervisors as ehs')
            ->join('exam_halls as eh', 'eh.exam_hallID', '=', 'ehs.exam_hallID')
            ->join('examinations as ex', 'ex.examID', '=', 'ehs.examID')
            ->where('ehs.exam_hallID', $examHallID)
            ->where('ehs.teacherID', $teacherID)
            ->where('ex.schoolID', $schoolID);

        // If subjectID is provided, filter by it
        if ($subjectID) {
            $assignmentQuery->where('ehs.subjectID', $subjectID);
        }

        // If exam_timetableID is provided, filter by it
        if ($examTimetableID) {
            $assignmentQuery->where('ehs.exam_timetableID', $examTimetableID);
        }

        $assignment = $assignmentQuery->select('ehs.examID', 'eh.classID', 'ehs.subjectID', 'ehs.exam_timetableID')
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $examID = $assignment->examID;
        $classID = $assignment->classID;
        $assignedSubjectID = $assignment->subjectID;

        // Get attendance status for students
        $attendanceStatuses = DB::table('exam_attendance')
            ->where('examID', $examID)
            ->where('subjectID', $assignedSubjectID)
            ->pluck('status', 'studentID')
            ->toArray();
        
        $students = DB::table('student_exam_halls as seh')
            ->join('students as s', 's.studentID', '=', 'seh.studentID')
            ->leftJoin('subclasses as sub', 'sub.subclassID', '=', 'seh.subclassID')
            ->leftJoin('classes as c', 'c.classID', '=', 'sub.classID')
            ->where('seh.exam_hallID', $examHallID)
            ->select(
                'seh.student_exam_hallID',
                's.studentID',
                's.first_name',
                's.last_name',
                's.gender',
                'sub.subclass_name',
                'c.class_name'
            )
            ->orderBy('s.first_name')
            ->orderBy('s.last_name')
            ->get();

        // Get attendance for this specific subject (if provided) or overall
        $attendanceQuery = DB::table('exam_attendance')
            ->where('examID', $examID)
            ->whereIn('studentID', $students->pluck('studentID'));

        if ($assignedSubjectID) {
            $attendanceQuery->where('subjectID', $assignedSubjectID);
        } else {
            $attendanceQuery->whereNull('subjectID');
        }

        $attendance = $attendanceQuery
            ->select('studentID', DB::raw("max(case when status='Present' then 1 else 0 end) as is_present"))
            ->groupBy('studentID')
            ->pluck('is_present', 'studentID');

        $hallOptions = DB::table('exam_halls')
            ->where('examID', $examID)
            ->where('classID', $classID)
            ->select('exam_hallID', 'hall_name', 'capacity', 'gender_allowed')
            ->get();

        // Get attendance status for each student
        $attendanceStatuses = DB::table('exam_attendance')
            ->where('examID', $examID)
            ->whereIn('studentID', $students->pluck('studentID'));
        
        if ($assignedSubjectID) {
            $attendanceStatuses->where('subjectID', $assignedSubjectID);
        } else {
            $attendanceStatuses->whereNull('subjectID');
        }
        
        $attendanceStatuses = $attendanceStatuses->pluck('status', 'studentID')->toArray();

        return response()->json([
            'success' => true,
            'examID' => $examID,
            'students' => $students->map(function ($s) use ($attendance, $attendanceStatuses) {
                $status = $attendanceStatuses[$s->studentID] ?? 'Absent';
                return [
                    'student_exam_hallID' => $s->student_exam_hallID,
                    'studentID' => $s->studentID,
                    'name' => trim($s->first_name.' '.$s->last_name),
                    'gender' => $s->gender,
                    'subclass' => $s->subclass_name,
                    'class_name' => $s->class_name,
                    'is_present' => $status === 'Present' ? 1 : 0,
                    'status' => $status,
                ];
            }),
            'halls' => $hallOptions,
            'subjectID' => $assignedSubjectID,
            'exam_timetableID' => $assignment->exam_timetableID,
        ]);
    }

    /**
     * Get exam halls for a specific exam
     */
    public function getExamHalls($examID)
    {
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');

        if (!$schoolID) {
            return response()->json(['success' => false, 'error' => 'School ID not found'], 401);
        }

        // Check if exam exists and belongs to school
        $exam = DB::table('examinations')
            ->where('examID', $examID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$exam) {
            return response()->json(['success' => false, 'error' => 'Examination not found'], 404);
        }

        // Get exam halls with class and supervisors
        $halls = DB::table('exam_halls as eh')
            ->leftJoin('classes as c', 'c.classID', '=', 'eh.classID')
            ->where('eh.examID', $examID)
            ->where('eh.schoolID', $schoolID)
            ->select(
                'eh.exam_hallID',
                'eh.hall_name',
                'eh.capacity',
                'eh.gender_allowed',
                'c.class_name'
            )
            ->get()
            ->map(function ($hall) {
                // Get supervisors for this hall
                $supervisors = DB::table('exam_hall_supervisors as ehs')
                    ->join('teachers as t', 't.id', '=', 'ehs.teacherID')
                    ->where('ehs.exam_hallID', $hall->exam_hallID)
                    ->select(
                        DB::raw("CONCAT(t.first_name, ' ', t.last_name) as teacher_name"),
                        't.id as teacherID'
                    )
                    ->get();

                return [
                    'exam_hallID' => $hall->exam_hallID,
                    'hall_name' => $hall->hall_name,
                    'class_name' => $hall->class_name ?? 'N/A',
                    'capacity' => $hall->capacity,
                    'gender_allowed' => $hall->gender_allowed,
                    'supervisors' => $supervisors
                ];
            });

        return response()->json([
            'success' => true,
            'halls' => $halls
        ]);
    }

    public function updateHallAttendance(Request $request, $examHallID)
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if ($userType !== 'Teacher' || ! $teacherID || ! $schoolID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get optional subjectID and exam_timetableID from request
        $subjectID = $request->input('subjectID');
        $examTimetableID = $request->input('exam_timetableID');

        $assignmentQuery = DB::table('exam_hall_supervisors as ehs')
            ->join('exam_halls as eh', 'eh.exam_hallID', '=', 'ehs.exam_hallID')
            ->join('examinations as ex', 'ex.examID', '=', 'ehs.examID')
            ->where('ehs.exam_hallID', $examHallID)
            ->where('ehs.teacherID', $teacherID)
            ->where('ex.schoolID', $schoolID);

        // If subjectID is provided, filter by it
        if ($subjectID) {
            $assignmentQuery->where('ehs.subjectID', $subjectID);
        }

        // If exam_timetableID is provided, filter by it
        if ($examTimetableID) {
            $assignmentQuery->where('ehs.exam_timetableID', $examTimetableID);
        }

        $assignment = $assignmentQuery->select('ehs.examID', 'ehs.subjectID')->first();

        if (! $assignment) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $examID = $assignment->examID;
        $assignedSubjectID = $assignment->subjectID;
        
        // Get attendance data (new format with status)
        $attendanceData = $request->input('attendance_data', []);
        
        // Fallback to old format for backward compatibility
        if (empty($attendanceData)) {
            $presentIds = $request->input('present_student_ids', []);
            $hallStudents = DB::table('student_exam_halls')
                ->where('exam_hallID', $examHallID)
                ->pluck('studentID');
            
            foreach ($hallStudents as $sid) {
                $attendanceData[] = [
                    'studentID' => $sid,
                    'status' => in_array($sid, $presentIds) ? 'Present' : 'Absent'
                ];
            }
        }

        if (empty($attendanceData)) {
            return response()->json(['error' => 'No attendance data provided'], 422);
        }

        $now = now();
        $rows = [];

        foreach ($attendanceData as $data) {
            $studentID = $data['studentID'] ?? $data['student_id'] ?? null;
            $status = $data['status'] ?? 'Absent';
            
            // Validate status
            if (!in_array($status, ['Present', 'Absent', 'Excused'])) {
                $status = 'Absent';
            }
            
            if ($studentID) {
                $rows[] = [
                    'examID' => $examID,
                    'studentID' => $studentID,
                    'subjectID' => $assignedSubjectID,
                    'status' => $status,
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }
        }

        if (empty($rows)) {
            return response()->json(['error' => 'No valid attendance data'], 422);
        }

        // Upsert attendance per student per subject
        DB::table('exam_attendance')->upsert(
            $rows,
            ['examID', 'studentID', 'subjectID'],
            ['status', 'updated_at']
        );

        return response()->json(['success' => true, 'message' => 'Attendance updated successfully']);
    }

    public function moveStudentHall(Request $request, $examHallID)
    {
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if ($userType !== 'Teacher' || ! $teacherID || ! $schoolID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $targetHallID = $request->input('target_hall_id');
        $studentExamHallID = $request->input('student_exam_hall_id');

        if (! $targetHallID || ! $studentExamHallID) {
            return response()->json(['error' => 'Missing target hall or student id'], 422);
        }

        $current = DB::table('student_exam_halls as seh')
            ->join('exam_halls as eh', 'eh.exam_hallID', '=', 'seh.exam_hallID')
            ->where('seh.student_exam_hallID', $studentExamHallID)
            ->select('seh.studentID', 'seh.subclassID', 'seh.examID', 'eh.classID')
            ->first();

        if (! $current) {
            return response()->json(['error' => 'Student assignment not found'], 404);
        }

        $allowed = DB::table('exam_hall_supervisors as ehs')
            ->where('ehs.exam_hallID', $examHallID)
            ->where('ehs.teacherID', $teacherID)
            ->where('ehs.examID', $current->examID)
            ->exists();

        if (! $allowed) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $targetHall = DB::table('exam_halls')->where('exam_hallID', $targetHallID)->first();
        if (! $targetHall || $targetHall->examID != $current->examID) {
            return response()->json(['error' => 'Invalid target hall'], 422);
        }

        $studentClassID = Subclass::find($current->subclassID)?->classID;
        if ($targetHall->classID != $studentClassID) {
            return response()->json(['error' => 'Target hall must be same class as student'], 422);
        }

        $student = Student::find($current->studentID);
        if ($targetHall->gender_allowed !== 'both' && strtolower($student->gender) !== strtolower($targetHall->gender_allowed)) {
            return response()->json(['error' => 'Gender not allowed in target hall'], 422);
        }

        $assignedCount = DB::table('student_exam_halls')->where('exam_hallID', $targetHallID)->count();
        if ($assignedCount >= $targetHall->capacity) {
            return response()->json(['error' => 'Target hall is full'], 422);
        }

        DB::table('student_exam_halls')
            ->where('student_exam_hallID', $studentExamHallID)
            ->update([
                'exam_hallID' => $targetHallID,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => 'Student moved to target hall']);
    }

    /**
     * Printing Unit - View approved exam papers with filters
     */
    public function printingUnit(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $userType = Session::get('user_type');

            if (!$schoolID || $userType !== 'Admin') {
                return redirect()->route('login')->with('error', 'Access denied');
            }

            // Get filter parameters
            $academicYear = $request->input('academic_year');
            $term = $request->input('term');
            $examID = $request->input('examID');
            $classID = $request->input('classID');
            $subclassID = $request->input('subclassID');

            // Get all academic years from examinations
            $academicYears = Examination::where('schoolID', $schoolID)
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            // Get all terms from examinations
            $terms = Examination::where('schoolID', $schoolID)
                ->whereNotNull('term')
                ->distinct()
                ->orderBy('term', 'asc')
                ->pluck('term')
                ->toArray();

            // Base query for examinations with approved exam papers
            $examinationsQuery = Examination::where('schoolID', $schoolID)
                ->whereHas('examPapers', function($query) {
                    $query->where('status', 'approved');
                })
                ->with([
                    'examPapers' => function($query) {
                        $query->where('status', 'approved')
                            ->with([
                                'classSubject.subject',
                                'classSubject.class',
                                'classSubject.subclass',
                                'teacher'
                            ]);
                    }
                ]);

            // Apply filters - order matters: apply all filters before getting results
            if ($academicYear) {
                $examinationsQuery->where('year', $academicYear);
            }

            if ($term) {
                // Handle term filtering - ensure it matches exactly (case-sensitive for enum)
                $examinationsQuery->where('term', $term);
            }

            if ($examID) {
                $examinationsQuery->where('examID', $examID);
            }

            $examinations = $examinationsQuery->orderBy('year', 'desc')
                ->orderBy('start_date', 'desc')
                ->get();
            
            // Debug: Log query results if needed
            if (config('app.debug')) {
                \Log::info('Printing Unit Filter Results', [
                    'academic_year' => $academicYear,
                    'term' => $term,
                    'examID' => $examID,
                    'examinations_count' => $examinations->count(),
                    'exam_ids' => $examinations->pluck('examID')->toArray(),
                ]);
            }

            // Get examinations for dropdown (filtered by academic year and term if provided)
            $examsForDropdown = Examination::where('schoolID', $schoolID)
                ->whereHas('examPapers', function($query) {
                    $query->where('status', 'approved');
                });

            if ($academicYear) {
                $examsForDropdown->where('year', $academicYear);
            }

            if ($term) {
                $examsForDropdown->where('term', $term);
            }

            $examsForDropdown = $examsForDropdown->orderBy('year', 'desc')
                ->orderBy('start_date', 'desc')
                ->get(['examID', 'exam_name', 'year', 'term', 'start_date', 'end_date']);

            // Get all classes for filter
            $classes = ClassModel::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('class_name')
                ->get(['classID', 'class_name']);

            // Get subclasses for filter (filtered by classID if provided)
            $subclassesQuery = Subclass::with('class')
                ->whereHas('class', function($query) use ($schoolID) {
                    $query->where('schoolID', $schoolID);
                })
                ->where('status', 'Active');
            
            if ($classID) {
                $subclassesQuery->where('classID', $classID);
            }
            
            $subclasses = $subclassesQuery
                ->orderBy('classID')
                ->orderBy('subclass_name')
                ->get()
                ->map(function($subclass) {
                    return (object) [
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'classID' => $subclass->classID,
                        'class_name' => $subclass->class ? $subclass->class->class_name : 'N/A'
                    ];
                });

            // Group examinations by class/subclass instead of by examination
            $groupedByClass = [];
            
            foreach ($examinations as $exam) {
                foreach ($exam->examPapers as $paper) {
                    $classSubject = $paper->classSubject ?? null;
                    if (!$classSubject) continue;
                    
                    $class = $classSubject->class ?? null;
                    $subclass = $classSubject->subclass ?? null;
                    
                    if (!$class || !$subclass) continue;
                    
                    $classIDKey = $class->classID ?? 'no_class';
                    $subclassIDKey = $subclass->subclassID ?? 'no_subclass';
                    $key = $classIDKey . '_' . $subclassIDKey;
                    
                    // Apply class/subclass filters
                    if ($classID && $class->classID != $classID) continue;
                    if ($subclassID && $subclass->subclassID != $subclassID) continue;
                    
                    if (!isset($groupedByClass[$key])) {
                        $groupedByClass[$key] = [
                            'classID' => $class->classID,
                            'className' => $class->class_name,
                            'subclassID' => $subclass->subclassID,
                            'subclassName' => $subclass->subclass_name,
                            'papers' => []
                        ];
                    }
                    
                    // Add paper with exam info
                    $groupedByClass[$key]['papers'][] = [
                        'paper' => $paper,
                        'exam' => $exam
                    ];
                }
            }

            return view('Admin.printing_unit', [
                'academicYears' => $academicYears,
                'terms' => $terms,
                'examsForDropdown' => $examsForDropdown,
                'classes' => $classes,
                'subclasses' => $subclasses,
                'groupedByClass' => $groupedByClass,
                'selectedAcademicYear' => $academicYear,
                'selectedTerm' => $term,
                'selectedExamID' => $examID,
                'selectedClassID' => $classID,
                'selectedSubclassID' => $subclassID,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading printing unit: ' . $e->getMessage());
            return redirect()->route('manageExamination')->with('error', 'Error loading printing unit: ' . $e->getMessage());
        }
    }

    /**
     * AJAX Filter endpoint for Printing Unit
     */
    public function filterPrintingUnit(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $userType = Session::get('user_type');

            if (!$schoolID || $userType !== 'Admin') {
                return response()->json(['success' => false, 'error' => 'Access denied'], 403);
            }

            // Check if only loading subclasses
            if ($request->input('load_subclasses_only')) {
                $subclassesQuery = Subclass::with('class')
                    ->whereHas('class', function($query) use ($schoolID) {
                        $query->where('schoolID', $schoolID);
                    })
                    ->where('status', 'Active');
                
                $subclasses = $subclassesQuery
                    ->orderBy('classID')
                    ->orderBy('subclass_name')
                    ->get()
                    ->map(function($subclass) {
                        return [
                            'subclassID' => $subclass->subclassID,
                            'subclass_name' => $subclass->subclass_name,
                            'classID' => $subclass->classID,
                            'class_name' => $subclass->class ? $subclass->class->class_name : 'N/A'
                        ];
                    });
                
                return response()->json([
                    'success' => true,
                    'subclasses' => $subclasses
                ]);
            }

            // Get filter parameters (convert to appropriate types)
            $academicYear = $request->input('academic_year');
            $term = $request->input('term');
            $examID = $request->input('examID') ? (int)$request->input('examID') : null;
            $classID = $request->input('classID') ? (int)$request->input('classID') : null;
            $subclassID = $request->input('subclassID') ? (int)$request->input('subclassID') : null;

            // Base query for examinations with approved exam papers
            $examinationsQuery = Examination::where('schoolID', $schoolID)
                ->whereHas('examPapers', function($query) {
                    $query->where('status', 'approved');
                })
                ->with([
                    'examPapers' => function($query) {
                        $query->where('status', 'approved')
                            ->with([
                                'classSubject.subject',
                                'classSubject.class',
                                'classSubject.subclass',
                                'teacher'
                            ]);
                    }
                ]);

            // Apply filters
            if ($academicYear) {
                $examinationsQuery->where('year', $academicYear);
            }

            if ($term) {
                $examinationsQuery->where('term', $term);
            }

            if ($examID) {
                $examinationsQuery->where('examID', $examID);
            }

            $examinations = $examinationsQuery->orderBy('year', 'desc')
                ->orderBy('start_date', 'desc')
                ->get();

            // Get examinations for dropdown (filtered by academic year and term if provided)
            $examsForDropdown = Examination::where('schoolID', $schoolID)
                ->whereHas('examPapers', function($query) {
                    $query->where('status', 'approved');
                });

            if ($academicYear) {
                $examsForDropdown->where('year', $academicYear);
            }

            if ($term) {
                $examsForDropdown->where('term', $term);
            }

            $examsForDropdown = $examsForDropdown->orderBy('year', 'desc')
                ->orderBy('start_date', 'desc')
                ->get(['examID', 'exam_name', 'year', 'term', 'start_date', 'end_date']);

            // Get subclasses for filter (filtered by classID if provided)
            $subclassesQuery = Subclass::with('class')
                ->whereHas('class', function($query) use ($schoolID) {
                    $query->where('schoolID', $schoolID);
                })
                ->where('status', 'Active');
            
            if ($classID) {
                $subclassesQuery->where('classID', $classID);
            }
            
            $subclasses = $subclassesQuery
                ->orderBy('classID')
                ->orderBy('subclass_name')
                ->get()
                ->map(function($subclass) {
                    return [
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'classID' => $subclass->classID,
                        'class_name' => $subclass->class ? $subclass->class->class_name : 'N/A'
                    ];
                });

            // Group examinations by class/subclass
            $groupedByClass = [];
            
            foreach ($examinations as $exam) {
                foreach ($exam->examPapers as $paper) {
                    $classSubject = $paper->classSubject ?? null;
                    if (!$classSubject) continue;
                    
                    $class = $classSubject->class ?? null;
                    $subclass = $classSubject->subclass ?? null;
                    
                    if (!$class || !$subclass) continue;
                    
                    $classIDKey = $class->classID ?? 'no_class';
                    $subclassIDKey = $subclass->subclassID ?? 'no_subclass';
                    $key = $classIDKey . '_' . $subclassIDKey;
                    
                    // Apply class/subclass filters (use strict comparison with type casting)
                    if ($classID && (int)$class->classID !== (int)$classID) continue;
                    if ($subclassID && (int)$subclass->subclassID !== (int)$subclassID) continue;
                    
                    if (!isset($groupedByClass[$key])) {
                        $groupedByClass[$key] = [
                            'classID' => $class->classID,
                            'className' => $class->class_name,
                            'subclassID' => $subclass->subclassID,
                            'subclassName' => $subclass->subclass_name,
                            'exam_name' => $exam->exam_name ?? 'N/A',
                            'start_date' => $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('Y-m-d') : null,
                            'end_date' => $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('Y-m-d') : null,
                            'papers' => []
                        ];
                    }
                    
                    // Add paper with exam info (convert to array for JSON)
                    $groupedByClass[$key]['papers'][] = [
                        'paper' => [
                            'exam_paperID' => $paper->exam_paperID,
                            'class_subject' => [
                                'subject' => [
                                    'subject_name' => $paper->classSubject->subject->subject_name ?? 'N/A'
                                ]
                            ],
                            'teacher' => $paper->teacher ? [
                                'first_name' => $paper->teacher->first_name ?? '',
                                'last_name' => $paper->teacher->last_name ?? '',
                                'phone_number' => $paper->teacher->phone_number ?? null,
                                'phone' => $paper->teacher->phone ?? null
                            ] : null
                        ],
                        'exam' => [
                            'examID' => $exam->examID,
                            'exam_name' => $exam->exam_name,
                            'start_date' => $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('Y-m-d') : null,
                            'end_date' => $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('Y-m-d') : null
                        ]
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'groupedByClass' => $groupedByClass,
                'examsForDropdown' => $examsForDropdown->map(function($exam) {
                    return [
                        'examID' => $exam->examID,
                        'exam_name' => $exam->exam_name
                    ];
                }),
                'subclasses' => $subclasses
            ]);

        } catch (\Exception $e) {
            Log::error('Error filtering printing unit: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error filtering data: ' . $e->getMessage()], 500);
        }
    }
}
