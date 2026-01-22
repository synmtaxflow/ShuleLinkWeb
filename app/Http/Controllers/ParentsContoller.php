<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Result;
use App\Models\Attendance;
use App\Models\BookBorrow;
use App\Models\Examination;
use App\Models\ExamTimetable;
use App\Models\School;
use App\Models\Payment;
use App\Models\Fee;
use App\Models\ClassSubject;
use App\Models\SubjectElector;
use App\Models\PermissionRequest;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ParentsContoller extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new SmsService();
    }

    /**
     * Get current academic year ID for a school
     * Returns the academic year ID for current year, or null if not found
     */
    private function getCurrentAcademicYearID($schoolID)
    {
        // First, try to get active academic year (prioritize active year)
        $activeAcademicYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('year', 'desc')
            ->first();
        
        if ($activeAcademicYear) {
            return $activeAcademicYear->academic_yearID;
        }
        
        // If no active year, try to get academic year for current calendar year
        $currentYear = date('Y');
        $academicYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('year', $currentYear)
            ->first();
        
        // If found, return its ID
        if ($academicYear) {
            return $academicYear->academic_yearID;
        }
        
        // If not found, return null
        return null;
    }

    /**
     * Get previous year outstanding balance (debt) for a student
     * Returns array with 'school_fee_balance' and 'other_contribution_balance'
     */
    private function getPreviousYearBalance($studentID, $schoolID)
    {
        $currentYear = date('Y');
        $previousYear = $currentYear - 1;
        
        // Get the most recent closed academic year
        $closedAcademicYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Closed')
            ->where('year', '<', $currentYear)
            ->orderBy('year', 'desc')
            ->first();
        
        if (!$closedAcademicYear) {
            // No closed academic year found, return zero balance
            return ['school_fee_balance' => 0, 'other_contribution_balance' => 0];
        }
        
        // Get payments from history for closed academic year
        $previousYearPayments = DB::table('payments_history')
            ->where('studentID', $studentID)
            ->where('academic_yearID', $closedAcademicYear->academic_yearID)
            ->get();
        
        // Calculate outstanding balances
        $schoolFeeBalance = 0;
        $otherContributionBalance = 0;
        
        foreach ($previousYearPayments as $payment) {
            $balance = (float) $payment->balance;
            
            // Map fee types: 'Tuition Fees' -> 'School Fee', 'Other Fees' -> 'Other Contribution'
            if ($payment->fee_type === 'Tuition Fees') {
                $schoolFeeBalance += $balance;
            } elseif ($payment->fee_type === 'Other Fees') {
                $otherContributionBalance += $balance;
            }
        }
        
        return [
            'school_fee_balance' => max(0, $schoolFeeBalance), // Ensure non-negative
            'other_contribution_balance' => max(0, $otherContributionBalance) // Ensure non-negative
        ];
    }

    public function parentDashboard()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class', 'results.examination', 'subclass.classTeacher'])
            ->get();

        // Statistics
        $totalStudents = $students->count();
        $activeStudents = $students->where('status', 'Active')->count();
        $maleStudents = $students->where('gender', 'Male')->count();
        $femaleStudents = $students->where('gender', 'Female')->count();

        // Get recent results (last 5)
        $recentResults = Result::whereIn('studentID', $students->pluck('studentID'))
            ->with(['student', 'examination', 'classSubject.subject'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent attendance (last 7 days)
        $recentAttendance = Attendance::whereIn('studentID', $students->pluck('studentID'))
            ->where('attendance_date', '>=', Carbon::now()->subDays(7))
            ->with(['student', 'subclass.class'])
            ->orderBy('attendance_date', 'desc')
            ->limit(10)
            ->get();

        // Calculate attendance statistics
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

        // Get book borrows (active)
        $activeBookBorrows = BookBorrow::whereIn('studentID', $students->pluck('studentID'))
            ->where('status', 'borrowed')
            ->with(['student', 'book'])
            ->orderBy('borrow_date', 'desc')
            ->get();

        // Get upcoming exams (next 30 days)
        $upcomingExams = ExamTimetable::whereIn('subclassID', $students->pluck('subclassID'))
            ->where('exam_date', '>=', Carbon::now())
            ->where('exam_date', '<=', Carbon::now()->addDays(30))
            ->with(['examination', 'subclass.class', 'subject'])
            ->orderBy('exam_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(10)
            ->get();

        // Get recent examinations
        $recentExaminations = Examination::where('schoolID', $schoolID)
            ->where('status', 'results_available')
            ->orderBy('end_date', 'desc')
            ->limit(5)
            ->get();

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        // Notifications (combine results, attendance, exams)
        $notifications = collect();

        // New results notifications
        foreach ($recentResults as $result) {
            $notifications->push([
                'type' => 'result',
                'icon' => 'bi-trophy',
                'color' => 'success',
                'title' => 'New Result Available',
                'message' => $result->student->first_name . ' ' . $result->student->last_name . ' - ' . ($result->examination->exam_name ?? 'Exam'),
                'date' => $result->created_at,
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
                'date' => $attendance->attendance_date,
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
                'date' => $exam->exam_date,
                'link' => '#'
            ]);
        }

        // Sort notifications by date
        $notifications = $notifications->sortByDesc('date')->take(10)->values();

        return view('Parents.dashboard', compact(
            'parent',
            'students',
            'totalStudents',
            'activeStudents',
            'maleStudents',
            'femaleStudents',
            'recentResults',
            'recentAttendance',
            'attendanceStats',
            'activeBookBorrows',
            'upcomingExams',
            'recentExaminations',
            'school',
            'notifications'
        ));
    }

    public function manageParentPermissions(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        $normalizedUserType = strtolower($userType ?? '');
        if (!$userType || $normalizedUserType !== 'parent' || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $activeTab = $request->get('tab', 'request');
        $students = Student::where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();
        $studentNames = $students->mapWithKeys(function ($student) {
            $name = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            return [$student->studentID => $name ?: 'N/A'];
        });

        $permissions = PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'student')
            ->where('parentID', $parentID)
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingPermissions = $permissions->where('status', 'pending');
        $approvedPermissions = $permissions->where('status', 'approved');
        $rejectedPermissions = $permissions->where('status', 'rejected');

        if (in_array($activeTab, ['pending', 'approved', 'rejected'], true)) {
            PermissionRequest::where('schoolID', $schoolID)
                ->where('requester_type', 'student')
                ->where('parentID', $parentID)
                ->where('status', $activeTab)
                ->update(['is_read_by_requester' => true]);
        }

        $unreadPendingCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'student')
            ->where('parentID', $parentID)
            ->where('status', 'pending')
            ->where('is_read_by_requester', false)
            ->count();
        $unreadApprovedCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'student')
            ->where('parentID', $parentID)
            ->where('status', 'approved')
            ->where('is_read_by_requester', false)
            ->count();
        $unreadRejectedCount = PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'student')
            ->where('parentID', $parentID)
            ->where('status', 'rejected')
            ->where('is_read_by_requester', false)
            ->count();

        return view('Parent.manage_permissions', [
            'activeTab' => $activeTab,
            'students' => $students,
            'studentNames' => $studentNames,
            'pendingPermissions' => $pendingPermissions,
            'approvedPermissions' => $approvedPermissions,
            'rejectedPermissions' => $rejectedPermissions,
            'unreadPendingCount' => $unreadPendingCount,
            'unreadApprovedCount' => $unreadApprovedCount,
            'unreadRejectedCount' => $unreadRejectedCount,
        ]);
    }

    public function storeParentPermission(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        $normalizedUserType = strtolower($userType ?? '');
        if (!$userType || $normalizedUserType !== 'parent' || !$schoolID || !$parentID) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,studentID',
            'days_count' => 'nullable|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason_type' => 'required|in:medical,official,professional,emergency,other',
            'reason_description' => 'required|string|min:5',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $student = Student::where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->where('studentID', $validated['student_id'])
            ->where('status', 'Active')
            ->firstOrFail();

        if ($validated['reason_type'] === 'medical' && !$request->hasFile('attachment')) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Medical reason requires attachment.'], 422);
            }
            return redirect()->back()->with('error', 'Medical reason requires attachment.')->withInput();
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('permission_attachments', 'public');
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

        PermissionRequest::create([
            'schoolID' => $schoolID,
            'requester_type' => 'student',
            'studentID' => $student->studentID,
            'parentID' => $parentID,
            'time_mode' => 'days',
            'days_count' => $daysCount,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason_type' => $validated['reason_type'],
            'reason_description' => $validated['reason_description'],
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'is_read_by_admin' => false,
            'is_read_by_requester' => true,
        ]);

        $parent = ParentModel::where('parentID', $parentID)->first();
        $school = School::where('schoolID', $schoolID)->first();
        $smsService = $this->smsService;

        if ($parent && $parent->phone) {
            $smsService->sendSms($parent->phone, 'Your permission request has been submitted to Admin. Please wait for approval.');
        }

        if ($school && $school->phone) {
            $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            $reasonLabel = ucfirst($validated['reason_type']);
            $schoolName = $school->school_name ?? 'School';
            $smsService->sendSms($school->phone, "{$schoolName}: New student permission request by {$studentName}. Reason: {$reasonLabel}. Please review.");
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Permission request submitted successfully.']);
        }
        return redirect()->route('parent.permissions')->with('success', 'Permission request submitted successfully.');
    }

    public function parentResults(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }
        
        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class'])
            ->get();

        // Get filter parameters
        $studentFilter = $request->get('student', '');
        $yearFilter = $request->get('year', '');
        $termFilter = $request->get('term', '');
        $typeFilter = $request->get('type', 'exam'); // 'exam' or 'report'
        $examFilter = $request->get('exam', '');

        // Build query for results - try both allowed and approved statuses
        $query = Result::whereIn('studentID', $students->pluck('studentID'))
            ->whereIn('status', ['allowed', 'approved'])
            ->with(['student.subclass.class', 'examination', 'classSubject.subject']);

        // Apply filters
        if (!empty($studentFilter)) {
            $query->where('studentID', $studentFilter);
        }

        if (!empty($yearFilter)) {
            $query->whereHas('examination', function($q) use ($yearFilter) {
                $q->where('year', $yearFilter);
            });
        }

        if (!empty($termFilter)) {
            $query->whereHas('examination', function($q) use ($termFilter) {
                $q->where('term', $termFilter);
            });
        }

        // For exam type, filter by specific exam
        if ($typeFilter === 'exam' && !empty($examFilter)) {
            $query->where('examID', $examFilter);
        }

        // For report type, get all exams in the term
        if ($typeFilter === 'report' && !empty($termFilter) && !empty($yearFilter)) {
            $examIDs = Examination::where('schoolID', $schoolID)
                ->where('year', $yearFilter)
                ->where('term', $termFilter)
                ->where('approval_status', 'Approved')
                ->pluck('examID');
            $query->whereIn('examID', $examIDs);
        }

        // Get results ordered by exam date and student
        $results = $query->orderBy('created_at', 'desc')
            ->get();

        // Get all examinations for filter dropdown (only approved results exams)
        $examinationsQuery = Examination::where('schoolID', $schoolID)
            ->where('approval_status', 'Approved')
            ->whereHas('results', function($q) use ($students) {
                $q->whereIn('studentID', $students->pluck('studentID'))
                  ->whereIn('status', ['allowed', 'approved']); // Try both statuses
            });

        // Filter examinations by year if year filter is set
        if (!empty($yearFilter)) {
            $examinationsQuery->where('year', $yearFilter);
        }

        // Filter examinations by term if term filter is set
        if (!empty($termFilter)) {
            $examinationsQuery->where('term', $termFilter);
        }

        $examinations = $examinationsQuery->orderBy('year', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get unique years from examinations (all approved exams with results for parent's students)
        // Try multiple status values to ensure we get data
        $years = Examination::where('schoolID', $schoolID)
            ->where('approval_status', 'Approved')
            ->whereHas('results', function($q) use ($students) {
                $q->whereIn('studentID', $students->pluck('studentID'))
                  ->whereIn('status', ['allowed', 'approved']); // Try both statuses
            })
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->unique()
            ->values()
            ->toArray();

        // If still no years, try without status restriction
        if (empty($years)) {
            $years = Examination::where('schoolID', $schoolID)
                ->where('approval_status', 'Approved')
                ->whereHas('results', function($q) use ($students) {
                    $q->whereIn('studentID', $students->pluck('studentID'));
                })
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->unique()
                ->values()
                ->toArray();
        }

        // Get unique terms from examinations (all approved exams with results for parent's students)
        $terms = Examination::where('schoolID', $schoolID)
            ->where('approval_status', 'Approved')
            ->whereHas('results', function($q) use ($students) {
                $q->whereIn('studentID', $students->pluck('studentID'))
                  ->whereIn('status', ['allowed', 'approved']); // Try both statuses
            })
            ->distinct()
            ->orderBy('term', 'asc')
            ->pluck('term')
            ->unique()
            ->values()
            ->toArray();

        // If still no terms, try without status restriction
        if (empty($terms)) {
            $terms = Examination::where('schoolID', $schoolID)
                ->where('approval_status', 'Approved')
                ->whereHas('results', function($q) use ($students) {
                    $q->whereIn('studentID', $students->pluck('studentID'));
                })
                ->distinct()
                ->orderBy('term', 'asc')
                ->pluck('term')
                ->unique()
                ->values()
                ->toArray();
        }

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        // Process results based on type (exam or report)
        $processedResults = [];
        
        if ($typeFilter === 'report' && !empty($termFilter) && !empty($yearFilter)) {
            // For report: Group by student and calculate term averages
            $studentResults = $results->groupBy('studentID');
            
            foreach ($studentResults as $studentID => $studentResultList) {
                $student = $studentResultList->first()->student;
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : null;
                
                // Group results by exam
                $examResults = $studentResultList->groupBy('examID');
                $examsData = [];
                $subjectData = [];
                
                foreach ($examResults as $examID => $examResultList) {
                    $exam = $examResultList->first()->examination;
                    if (!$exam) continue;
                    
                    $examTotalMarks = 0;
                    $examSubjectCount = 0;
                    
                    foreach ($examResultList as $result) {
                        if ($result->marks !== null && $result->marks !== '') {
                            $examTotalMarks += (float)$result->marks;
                            $examSubjectCount++;
                        }
                        
                        $subjectName = $result->classSubject->subject->subject_name ?? 'N/A';
                        if (!isset($subjectData[$subjectName])) {
                            $subjectData[$subjectName] = [
                                'subject_name' => $subjectName,
                                'exams' => []
                            ];
                        }
                        
                        $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $school->school_type ?? 'Secondary', $className);
                        $subjectData[$subjectName]['exams'][$examID] = [
                            'marks' => $result->marks,
                            'grade' => $result->grade ?? $gradeOrDivision['grade'],
                            'exam_name' => $exam->exam_name
                        ];
                    }
                    
                    $examAverage = $examSubjectCount > 0 ? $examTotalMarks / $examSubjectCount : 0;
                    $examGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $examAverage);
                    
                    $examsData[] = [
                        'exam' => $exam,
                        'average' => $examAverage,
                        'grade' => $examGradeOrDiv['grade'] ?? null,
                        'division' => $examGradeOrDiv['division'] ?? null
                    ];
                }
                
                // Calculate overall average and grade
                $overallTotalMarks = 0;
                $overallSubjectCount = 0;
                foreach ($subjectData as $subjectName => $data) {
                    $subjectMarks = [];
                    foreach ($data['exams'] as $examID => $examData) {
                        if ($examData['marks'] !== null && $examData['marks'] !== '') {
                            $subjectMarks[] = (float)$examData['marks'];
                        }
                    }
                    if (!empty($subjectMarks)) {
                        $overallTotalMarks += array_sum($subjectMarks);
                        $overallSubjectCount += count($subjectMarks);
                    }
                }
                
                $overallAverage = $overallSubjectCount > 0 ? $overallTotalMarks / $overallSubjectCount : 0;
                $overallGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $overallAverage);
                
                // Calculate subjects with averages
                $subjects = [];
                foreach ($subjectData as $subjectName => $data) {
                    $subjectMarks = [];
                    foreach ($data['exams'] as $examID => $examData) {
                        if ($examData['marks'] !== null && $examData['marks'] !== '') {
                            $subjectMarks[] = (float)$examData['marks'];
                        }
                    }
                    $subjectAverage = !empty($subjectMarks) ? array_sum($subjectMarks) / count($subjectMarks) : 0;
                    $subjectGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $subjectAverage);
                    
                    $subjects[] = [
                        'subject_name' => $subjectName,
                        'exams' => array_values($data['exams']),
                        'average' => $subjectAverage,
                        'grade' => $subjectGradeOrDiv['grade'] ?? null
                    ];
                }
                
                $processedResults[] = [
                    'type' => 'report',
                    'student' => $student,
                    'term' => $termFilter,
                    'year' => $yearFilter,
                    'exams' => $examsData,
                    'subjects' => $subjects,
                    'overall_average' => $overallAverage,
                    'overall_grade' => $overallGradeOrDiv['grade'] ?? null,
                    'overall_division' => $overallGradeOrDiv['division'] ?? null
                ];
            }
        } else {
            // For exam: Group by exam and student (existing logic)
        foreach($results as $result) {
            $examKey = $result->examID . '_' . $result->studentID;
                if(!isset($processedResults[$examKey])) {
                    $processedResults[$examKey] = [
                        'type' => 'exam',
                    'exam' => $result->examination,
                    'student' => $result->student,
                    'results' => []
                ];
            }
                $processedResults[$examKey]['results'][] = $result;
            }
        }
        
        // Calculate divisions for exam results (existing logic)
        $resultsWithDivisions = [];
        foreach($processedResults as $key => $group) {
            if ($group['type'] === 'exam') {
                $examKey = $key;
                if(!isset($resultsWithDivisions[$examKey])) {
                    $resultsWithDivisions[$examKey] = [
                        'exam' => $group['exam'],
                        'student' => $group['student'],
                        'results' => $group['results']
                    ];
                }
            } else {
                // For report, use different key
                $reportKey = 'report_' . $group['student']->studentID . '_' . $group['term'] . '_' . $group['year'];
                $resultsWithDivisions[$reportKey] = $group;
            }
        }

        // Calculate total division for each group
        foreach($resultsWithDivisions as $key => $group) {
            // Skip calculation for report type (already calculated)
            if (isset($group['type']) && $group['type'] === 'report') {
                continue;
            }
            
            $student = $group['student'];
            $examID = $group['exam']->examID ?? null;
            $className = $student->subclass && $student->subclass->class
                ? $student->subclass->class->class_name
                : null;

            $subjectsData = [];
            $totalMarks = 0;
            $subjectCount = 0;

            foreach($group['results'] as $result) {
                if ($result->marks !== null && $result->marks !== '') {
                    $totalMarks += (float)$result->marks;
                    $subjectCount++;
                }
                $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $school->school_type ?? 'Secondary', $className);
                $subjectsData[] = [
                    'marks' => $result->marks,
                    'points' => $gradeOrDivision['points'] ?? null,
                    'grade' => $gradeOrDivision['grade'] ?? null,
                    'division' => $gradeOrDivision['division'] ?? null
                ];
            }

            // Calculate total points
            $subjectPoints = array_filter(array_column($subjectsData, 'points'), function($p) { return $p !== null; });
            $totalPoints = 0;

            if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                if (count($subjectPoints) > 0) {
                    sort($subjectPoints);
                    $bestSeven = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
                    $totalPoints = array_sum($bestSeven);
                }
            } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                if (count($subjectPoints) > 0) {
                    rsort($subjectPoints);
                    $bestThree = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                    $totalPoints = array_sum($bestThree);
                }
            }

            $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            $totalDivision = $this->calculateTotalDivision($totalPoints, $school->school_type ?? 'Secondary', $className, $averageMarks);

            // Calculate position - Get all students who took this exam in the same class/subclass
            $position = null;
            $totalStudentsInExam = 0;

            if ($examID && $student->subclassID) {
                // Get all students in the same subclass who took this exam
                $subclassStudents = Student::where('subclassID', $student->subclassID)
                    ->where('status', 'Active')
                    ->pluck('studentID');

                // Get all results for this exam in this subclass
                $allSubclassResults = Result::where('examID', $examID)
                    ->whereIn('studentID', $subclassStudents)
                    ->where('status', 'approved')
                    ->with('classSubject')
                    ->get();

                // Group by student and calculate totals
                $studentTotals = [];
                foreach ($allSubclassResults as $subResult) {
                    $subStudentID = $subResult->studentID;
                    if (!isset($studentTotals[$subStudentID])) {
                        $studentTotals[$subStudentID] = [
                            'total_marks' => 0,
                            'total_points' => 0,
                            'subject_count' => 0
                        ];
                    }

                    if ($subResult->marks !== null && $subResult->marks !== '') {
                        $studentTotals[$subStudentID]['total_marks'] += (float)$subResult->marks;
                        $studentTotals[$subStudentID]['subject_count']++;
                    }

                    $subGradeOrDiv = $this->calculateGradeOrDivision($subResult->marks, $school->school_type ?? 'Secondary', $className);
                    if ($subGradeOrDiv['points'] !== null) {
                        $studentTotals[$subStudentID]['total_points'] += $subGradeOrDiv['points'];
                    }
                }

                // Calculate average marks and final points for each student
                $finalScores = [];
                foreach ($studentTotals as $subStudentID => $totals) {
                    $avgMarks = $totals['subject_count'] > 0 ? $totals['total_marks'] / $totals['subject_count'] : 0;

                    // Calculate final points (same logic as above)
                    $subFinalPoints = 0;
                    if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        // For O-Level, we need to recalculate with best 7
                        // For simplicity, use total_points if available
                        $subFinalPoints = $totals['total_points'];
                    } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                        $subFinalPoints = $totals['total_points'];
                    }

                    $finalScores[$subStudentID] = [
                        'total_marks' => $totals['total_marks'],
                        'average_marks' => $avgMarks,
                        'total_points' => $subFinalPoints
                    ];
                }

                // Sort by appropriate criteria
                if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    // O-Level: Lower points is better
                    uasort($finalScores, function($a, $b) {
                        return ($a['total_points'] ?? 999) <=> ($b['total_points'] ?? 999);
                    });
                } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                    // A-Level: Higher points is better
                    uasort($finalScores, function($a, $b) {
                        return ($b['total_points'] ?? 0) <=> ($a['total_points'] ?? 0);
                    });
                } else {
                    // Primary or other: Higher marks is better
                    uasort($finalScores, function($a, $b) {
                        return ($b['average_marks'] ?? 0) <=> ($a['average_marks'] ?? 0);
                    });
                }

                // Find position
                $currentPos = 1;
                $prevValue = null;
                $totalStudentsInExam = count($finalScores);

                foreach ($finalScores as $subStudentID => $score) {
                    $currentValue = $school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])
                        ? $score['total_points']
                        : $score['average_marks'];

                    if ($prevValue !== null && abs($currentValue - $prevValue) > 0.01) {
                        $currentPos++;
                    }

                    if ($subStudentID == $student->studentID) {
                        $position = $currentPos;
                        break;
                    }

                    $prevValue = $currentValue;
                }
            }

            $resultsWithDivisions[$key]['total_division'] = $totalDivision['division'] ?? null;
            $resultsWithDivisions[$key]['total_grade'] = $totalDivision['grade'] ?? null;
            $resultsWithDivisions[$key]['total_points'] = $totalPoints;
            $resultsWithDivisions[$key]['average_marks'] = $averageMarks;
            $resultsWithDivisions[$key]['subjects_data'] = $subjectsData;
            $resultsWithDivisions[$key]['position'] = $position;
            $resultsWithDivisions[$key]['total_students'] = $totalStudentsInExam;
        }

        return view('Parents.results', compact(
            'parent',
            'students',
            'results',
            'resultsWithDivisions',
            'examinations',
            'years',
            'terms',
            'studentFilter',
            'yearFilter',
            'termFilter',
            'typeFilter',
            'examFilter',
            'school'
        ));
    }

    private function calculateGradeOrDivision($marks, $schoolType, $className = null)
    {
        if ($marks === null || $marks === '') {
            if ($schoolType === 'Primary') {
                return ['grade' => null, 'division' => 'Division Zero', 'points' => null];
            }
            return ['grade' => null, 'division' => null, 'points' => null];
        }

        $marksNum = (float)$marks;
        $classNameLower = strtolower($className ?? '');

        if ($schoolType === 'Primary') {
            // Primary: Division One, Two, Three, Four, Zero
            if ($marksNum >= 75) {
                return ['grade' => null, 'division' => 'Division One', 'points' => null];
            } elseif ($marksNum >= 50) {
                return ['grade' => null, 'division' => 'Division Two', 'points' => null];
            } elseif ($marksNum >= 30) {
                return ['grade' => null, 'division' => 'Division Three', 'points' => null];
            } elseif ($marksNum >= 0) {
                return ['grade' => null, 'division' => 'Division Four', 'points' => null];
            } else {
                return ['grade' => null, 'division' => 'Division Zero', 'points' => null];
            }
        } else {
            // Secondary School
            if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                // O-Level Grading System
                if ($marksNum >= 75) {
                    return ['grade' => 'A', 'division' => null, 'points' => 1];
                } elseif ($marksNum >= 65) {
                    return ['grade' => 'B', 'division' => null, 'points' => 2];
                } elseif ($marksNum >= 45) {
                    return ['grade' => 'C', 'division' => null, 'points' => 3];
                } elseif ($marksNum >= 30) {
                    return ['grade' => 'D', 'division' => null, 'points' => 4];
                } elseif ($marksNum >= 20) {
                    return ['grade' => 'E', 'division' => null, 'points' => 5];
                } else {
                    return ['grade' => 'F', 'division' => null, 'points' => 6];
                }
            } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
                // A-Level Grading System
                if ($marksNum >= 80) {
                    return ['grade' => 'A', 'division' => null, 'points' => 5];
                } elseif ($marksNum >= 70) {
                    return ['grade' => 'B', 'division' => null, 'points' => 4];
                } elseif ($marksNum >= 60) {
                    return ['grade' => 'C', 'division' => null, 'points' => 3];
                } elseif ($marksNum >= 50) {
                    return ['grade' => 'D', 'division' => null, 'points' => 2];
                } elseif ($marksNum >= 40) {
                    return ['grade' => 'E', 'division' => null, 'points' => 1];
                } else {
                    return ['grade' => 'S/F', 'division' => null, 'points' => 0];
                }
            } else {
                // Default Secondary (fallback to old system)
                if ($marksNum >= 75) {
                    return ['grade' => 'A', 'division' => null, 'points' => null];
                } elseif ($marksNum >= 65) {
                    return ['grade' => 'B', 'division' => null, 'points' => null];
                } elseif ($marksNum >= 45) {
                    return ['grade' => 'C', 'division' => null, 'points' => null];
                } elseif ($marksNum >= 30) {
                    return ['grade' => 'D', 'division' => null, 'points' => null];
                } else {
                    return ['grade' => 'F', 'division' => null, 'points' => null];
                }
            }
        }
    }

    private function calculateTotalDivision($totalPoints, $schoolType, $className = null, $averageMarks = 0)
    {
        $classNameLower = strtolower($className ?? '');

        if ($schoolType === 'Primary') {
            // Primary: Calculate grade based on average marks
            if ($averageMarks >= 75) {
                return ['grade' => 'A', 'division' => null];
            } elseif ($averageMarks >= 65) {
                return ['grade' => 'B', 'division' => null];
            } elseif ($averageMarks >= 45) {
                return ['grade' => 'C', 'division' => null];
            } elseif ($averageMarks >= 30) {
                return ['grade' => 'D', 'division' => null];
            } else {
                return ['grade' => 'F', 'division' => null];
            }
        } else {
            // Secondary School
            if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                // O-Level Division based on total points (7 best subjects)
                if ($totalPoints >= 7 && $totalPoints <= 17) {
                    return ['grade' => null, 'division' => 'I.' . $totalPoints];
                } elseif ($totalPoints >= 18 && $totalPoints <= 21) {
                    return ['grade' => null, 'division' => 'II.' . $totalPoints];
                } elseif ($totalPoints >= 22 && $totalPoints <= 25) {
                    return ['grade' => null, 'division' => 'III.' . $totalPoints];
                } elseif ($totalPoints >= 26 && $totalPoints <= 33) {
                    return ['grade' => null, 'division' => 'IV.' . $totalPoints];
                } else {
                    return ['grade' => null, 'division' => '0.' . $totalPoints];
                }
            } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
                // A-Level Division based on 3 best principal subjects points
                if ($totalPoints >= 12 && $totalPoints <= 15) {
                    return ['grade' => null, 'division' => 'I.' . $totalPoints];
                } elseif ($totalPoints >= 9 && $totalPoints <= 11) {
                    return ['grade' => null, 'division' => 'II.' . $totalPoints];
                } elseif ($totalPoints >= 6 && $totalPoints <= 8) {
                    return ['grade' => null, 'division' => 'III.' . $totalPoints];
                } elseif ($totalPoints >= 3 && $totalPoints <= 5) {
                    return ['grade' => null, 'division' => 'IV.' . $totalPoints];
                } else {
                    return ['grade' => null, 'division' => '0.' . $totalPoints];
                }
            } else {
                // Default Secondary (fallback to average marks)
                if ($averageMarks >= 75) {
                    return ['grade' => 'A', 'division' => null];
                } elseif ($averageMarks >= 65) {
                    return ['grade' => 'B', 'division' => null];
                } elseif ($averageMarks >= 45) {
                    return ['grade' => 'C', 'division' => null];
                } elseif ($averageMarks >= 30) {
                    return ['grade' => 'D', 'division' => null];
                } else {
                    return ['grade' => 'F', 'division' => null];
                }
            }
        }
    }

    public function parentAttendance(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }
        
        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class'])
            ->get();

        // Get filter parameters
        $studentFilter = $request->get('student', '');
        $yearFilter = $request->get('year', date('Y'));
        $monthFilter = $request->get('month', date('m'));
        $dateFilter = $request->get('date', '');
        $searchType = $request->get('search_type', 'month'); // month, year, date

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        // Get available years from attendance records
        $years = Attendance::whereIn('studentID', $students->pluck('studentID'))
            ->distinct()
            ->orderBy('attendance_date', 'desc')
            ->get()
            ->pluck('attendance_date')
            ->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('Y');
            })
            ->unique()
            ->values()
            ->sort()
            ->reverse()
            ->values();

        // Initialize attendance data
        $attendanceData = null;
        $overviewData = null;
        $dailyRecords = collect();

        // If filters are applied, get attendance data
        if ($studentFilter || $searchType) {
            $query = Attendance::whereIn('studentID', $students->pluck('studentID'))
                ->with(['student.subclass.class']);

            // Apply student filter
            if (!empty($studentFilter)) {
                $query->where('studentID', $studentFilter);
            }

            // Apply date filters based on search type
            if ($searchType === 'date' && !empty($dateFilter)) {
                $query->whereDate('attendance_date', $dateFilter);
                $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
            } elseif ($searchType === 'month' && !empty($yearFilter) && !empty($monthFilter)) {
                $query->whereYear('attendance_date', $yearFilter)
                      ->whereMonth('attendance_date', $monthFilter);
                $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
            } elseif ($searchType === 'year' && !empty($yearFilter)) {
                $query->whereYear('attendance_date', $yearFilter);
                $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
            }

            // Calculate overview statistics
            if ($dailyRecords->count() > 0) {
                $overviewData = [
                    'total_days' => $dailyRecords->count(),
                    'present' => $dailyRecords->where('status', 'Present')->count(),
                    'absent' => $dailyRecords->where('status', 'Absent')->count(),
                    'late' => $dailyRecords->where('status', 'Late')->count(),
                    'excused' => $dailyRecords->where('status', 'Excused')->count(),
                    'attendance_rate' => $dailyRecords->count() > 0
                        ? round(($dailyRecords->where('status', 'Present')->count() / $dailyRecords->count()) * 100, 2)
                        : 0
                ];
            }
        }

        return view('Parents.attendance', compact(
            'parent',
            'students',
            'years',
            'studentFilter',
            'yearFilter',
            'monthFilter',
            'dateFilter',
            'searchType',
            'overviewData',
            'dailyRecords',
            'school'
        ));
    }

    /**
     * API Endpoint: Get Parent Attendance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetParentAttendance(Request $request)
    {
        try {
            // Get parameters
            $parentID = $request->input('parentID');
            $schoolID = $request->input('schoolID');
            $studentFilter = $request->input('student', '');
            $yearFilter = $request->input('year', date('Y'));
            $monthFilter = $request->input('month', date('m'));
            $dateFilter = $request->input('date', '');
            $searchType = $request->input('search_type', 'month'); // month, year, date

            // Validate required parameters
            if (!$parentID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'parentID and schoolID are required'
                ], 400);
            }

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Get all students of this parent
            $students = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->with(['subclass.class'])
                ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No students found for this parent',
                    'data' => [
                        'parent' => $parent,
                        'students' => [],
                        'years' => [],
                        'overview' => null,
                        'daily_records' => []
                    ]
                ]);
            }

            // Get school details
            $school = School::where('schoolID', $schoolID)->first();

            // Get available years from attendance records
            $years = Attendance::whereIn('studentID', $students->pluck('studentID'))
                ->distinct()
                ->orderBy('attendance_date', 'desc')
                ->get()
                ->pluck('attendance_date')
                ->map(function($date) {
                    return \Carbon\Carbon::parse($date)->format('Y');
                })
                ->unique()
                ->values()
                ->sort()
                ->reverse()
                ->values();

            // Initialize attendance data
            $overviewData = null;
            $dailyRecords = collect();

            // If filters are applied, get attendance data
            if ($studentFilter || $searchType) {
                $query = Attendance::whereIn('studentID', $students->pluck('studentID'))
                    ->with(['student.subclass.class']);

                // Apply student filter
                if (!empty($studentFilter)) {
                    $query->where('studentID', $studentFilter);
                }

                // Apply date filters based on search type
                if ($searchType === 'date' && !empty($dateFilter)) {
                    $query->whereDate('attendance_date', $dateFilter);
                    $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
                } elseif ($searchType === 'month' && !empty($yearFilter) && !empty($monthFilter)) {
                    $query->whereYear('attendance_date', $yearFilter)
                          ->whereMonth('attendance_date', $monthFilter);
                    $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
                } elseif ($searchType === 'year' && !empty($yearFilter)) {
                    $query->whereYear('attendance_date', $yearFilter);
                    $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
                }

                // Calculate overview statistics
                if ($dailyRecords->count() > 0) {
                    $overviewData = [
                        'total_days' => $dailyRecords->count(),
                        'present' => $dailyRecords->where('status', 'Present')->count(),
                        'absent' => $dailyRecords->where('status', 'Absent')->count(),
                        'late' => $dailyRecords->where('status', 'Late')->count(),
                        'excused' => $dailyRecords->where('status', 'Excused')->count(),
                        'attendance_rate' => $dailyRecords->count() > 0
                            ? round(($dailyRecords->where('status', 'Present')->count() / $dailyRecords->count()) * 100, 2)
                            : 0
                    ];
                }
            }

            // Format daily records
            $formattedRecords = [];
            foreach ($dailyRecords as $record) {
                $formattedRecords[] = [
                    'attendanceID' => $record->attendanceID ?? null,
                    'student' => [
                        'studentID' => $record->student->studentID ?? null,
                        'first_name' => $record->student->first_name ?? '',
                        'middle_name' => $record->student->middle_name ?? '',
                        'last_name' => $record->student->last_name ?? '',
                        'admission_number' => $record->student->admission_number ?? '',
                        'photo' => $record->student->photo ? url('userImages/' . $record->student->photo) : null,
                        'class' => $record->student->subclass && $record->student->subclass->class
                            ? $record->student->subclass->class->class_name
                            : null,
                        'subclass' => $record->student->subclass
                            ? $record->student->subclass->subclass_name
                            : null,
                    ],
                    'attendance_date' => $record->attendance_date ? \Carbon\Carbon::parse($record->attendance_date)->format('Y-m-d') : null,
                    'status' => $record->status ?? null,
                    'remark' => $record->remark ?? null,
                    'created_at' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : null,
                ];
            }

            // Group daily records by date for easier consumption
            $groupedRecords = [];
            foreach ($dailyRecords->groupBy('attendance_date') as $date => $records) {
                $dateObj = \Carbon\Carbon::parse($date);
                $firstRecord = $records->first();
                $groupedRecords[] = [
                    'date' => $dateObj->format('Y-m-d'),
                    'date_formatted' => $dateObj->format('l, F d, Y'),
                    'status' => $firstRecord->status ?? null,
                    'remark' => $firstRecord->remark ?? null,
                    'students' => $records->map(function($rec) {
                        return [
                            'studentID' => $rec->student->studentID ?? null,
                            'first_name' => $rec->student->first_name ?? '',
                            'middle_name' => $rec->student->middle_name ?? '',
                            'last_name' => $rec->student->last_name ?? '',
                            'admission_number' => $rec->student->admission_number ?? '',
                            'photo' => $rec->student->photo ? url('userImages/' . $rec->student->photo) : null,
                            'status' => $rec->status ?? null,
                            'remark' => $rec->remark ?? null,
                        ];
                    })->toArray()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance records retrieved successfully',
                'data' => [
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                    ],
                    'school' => [
                        'schoolID' => $school->schoolID ?? null,
                        'school_name' => $school->school_name ?? '',
                        'school_type' => $school->school_type ?? 'Secondary',
                    ],
                    'students' => $students->map(function($student) {
                        return [
                            'studentID' => $student->studentID,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name ?? '',
                            'last_name' => $student->last_name,
                            'admission_number' => $student->admission_number ?? '',
                            'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                            'class' => $student->subclass && $student->subclass->class
                                ? $student->subclass->class->class_name
                                : null,
                            'subclass' => $student->subclass
                                ? $student->subclass->subclass_name
                                : null,
                        ];
                    }),
                    'years' => $years->toArray(),
                    'filters' => [
                        'student' => $studentFilter,
                        'year' => $yearFilter,
                        'month' => $monthFilter,
                        'date' => $dateFilter,
                        'search_type' => $searchType,
                    ],
                    'overview' => $overviewData,
                    'daily_records' => $formattedRecords,
                    'grouped_records' => $groupedRecords,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Endpoint: Get Parent Results
     *
     * This endpoint returns all results for a parent's children including:
     * - Parent details
     * - Students list
     * - Examinations list
     * - Available years and terms
     * - Results grouped by exam and student (for exam type)
     * - Term reports with all exams (for report type)
     * - Statistics (total marks, average, position, division/grade)
     * - Subject-wise results with grades/divisions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Request Parameters (Query or Body):
     * - parentID: integer (required) - Parent ID
     * - schoolID: integer (required) - School ID
     * - student: integer (required) - Filter by student ID
     * - year: string (required) - Filter by year
     * - term: string (required) - Filter by term (first_term, second_term, third_term)
     * - type: string (required) - Type of results: "exam" or "report"
     * - exam: integer (required if type="exam") - Filter by exam ID
     *
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Results retrieved successfully",
     *   "data": {
     *     "parent": {...},
     *     "school": {...},
     *     "students": [...],
     *     "examinations": [...],
     *     "years": [...],
     *     "terms": [...],
     *     "filters": {...},
     *     "results": [...]
     *   }
     * }
     *
     * Error Response (400/404/500):
     * {
     *   "success": false,
     *   "message": "Error message"
     * }
     */
    public function apiGetParentResults(Request $request)
    {
        try {
            // Get parameters
            $parentID = $request->input('parentID');
            $schoolID = $request->input('schoolID');
            $studentFilter = $request->input('student', '');
            $yearFilter = $request->input('year', '');
            $termFilter = $request->input('term', '');
            $typeFilter = $request->input('type', 'exam'); // 'exam' or 'report'
            $examFilter = $request->input('exam', '');

            // Validate required parameters
            if (!$parentID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'parentID and schoolID are required'
                ], 400);
            }

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Get all students of this parent
            $students = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->with(['subclass.class'])
                ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No students found for this parent',
                    'data' => [
                        'parent' => [
                            'parentID' => $parent->parentID,
                            'first_name' => $parent->first_name ?? '',
                            'middle_name' => $parent->middle_name ?? '',
                            'last_name' => $parent->last_name ?? '',
                        ],
                        'school' => null,
                        'students' => [],
                        'examinations' => [],
                        'years' => [],
                        'filters' => [
                            'student' => $studentFilter,
                            'year' => $yearFilter,
                            'exam' => $examFilter,
                        ],
                        'statistics' => [
                            'total_students' => 0,
                            'total_examinations' => 0,
                            'total_results' => 0,
                        ],
                        'results' => []
                    ]
                ]);
            }

            // Build query for results - only allowed/approved results
            $query = Result::whereIn('studentID', $students->pluck('studentID'))
                ->whereIn('status', ['allowed', 'approved'])
                ->with(['student.subclass.class', 'examination', 'classSubject.subject']);

            // Apply filters
            if (!empty($studentFilter)) {
                $query->where('studentID', $studentFilter);
            }

            if (!empty($yearFilter)) {
                $query->whereHas('examination', function($q) use ($yearFilter) {
                    $q->where('year', $yearFilter);
                });
            }

            if (!empty($termFilter)) {
                $query->whereHas('examination', function($q) use ($termFilter) {
                    $q->where('term', $termFilter);
                });
            }

            // For exam type, filter by specific exam
            if ($typeFilter === 'exam' && !empty($examFilter)) {
                $query->where('examID', $examFilter);
            }

            // For report type, get all exams in the term
            if ($typeFilter === 'report' && !empty($termFilter) && !empty($yearFilter)) {
                $examIDs = Examination::where('schoolID', $schoolID)
                    ->where('year', $yearFilter)
                    ->where('term', $termFilter)
                    ->where('approval_status', 'Approved')
                    ->pluck('examID');
                $query->whereIn('examID', $examIDs);
            }

            // Get results ordered by exam date and student
            $results = $query->orderBy('created_at', 'desc')->get();

            // Get all examinations for filter dropdown (only approved results exams)
            $examinationsQuery = Examination::where('schoolID', $schoolID)
                ->where('approval_status', 'Approved')
                ->whereHas('results', function($q) use ($students) {
                    $q->whereIn('studentID', $students->pluck('studentID'))
                      ->whereIn('status', ['allowed', 'approved']);
                });

            // Filter examinations by year if year filter is set
            if (!empty($yearFilter)) {
                $examinationsQuery->where('year', $yearFilter);
            }

            // Filter examinations by term if term filter is set
            if (!empty($termFilter)) {
                $examinationsQuery->where('term', $termFilter);
            }

            $examinations = $examinationsQuery->orderBy('year', 'desc')
                ->orderBy('start_date', 'desc')
                ->get();

            // Get unique years from examinations
            $years = Examination::where('schoolID', $schoolID)
                ->where('approval_status', 'Approved')
                ->whereHas('results', function($q) use ($students) {
                    $q->whereIn('studentID', $students->pluck('studentID'))
                      ->whereIn('status', ['allowed', 'approved']);
                })
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->unique()
                ->values();

            // Get unique terms from examinations
            $terms = Examination::where('schoolID', $schoolID)
                ->where('approval_status', 'Approved')
                ->whereHas('results', function($q) use ($students) {
                    $q->whereIn('studentID', $students->pluck('studentID'))
                      ->whereIn('status', ['allowed', 'approved']);
                })
                ->distinct()
                ->orderBy('term', 'asc')
                ->pluck('term')
                ->unique()
                ->values();

            // Get school details
            $school = School::where('schoolID', $schoolID)->first();

            // Process results based on type (exam or report) - same logic as parentResults
            $processedResults = [];
            
            if ($typeFilter === 'report' && !empty($termFilter) && !empty($yearFilter)) {
                // For report: Group by student and calculate term averages
                $studentResults = $results->groupBy('studentID');
                
                foreach ($studentResults as $studentID => $studentResultList) {
                    $student = $studentResultList->first()->student;
                    $className = $student->subclass && $student->subclass->class
                        ? $student->subclass->class->class_name
                        : null;
                    
                    // Group results by exam
                    $examResults = $studentResultList->groupBy('examID');
                    $examsData = [];
                    $subjectData = [];
                    
                    foreach ($examResults as $examID => $examResultList) {
                        $exam = $examResultList->first()->examination;
                        if (!$exam) continue;
                        
                        $examTotalMarks = 0;
                        $examSubjectCount = 0;
                        
                        foreach ($examResultList as $result) {
                            if ($result->marks !== null && $result->marks !== '') {
                                $examTotalMarks += (float)$result->marks;
                                $examSubjectCount++;
                            }
                            
                            $subjectName = $result->classSubject->subject->subject_name ?? 'N/A';
                            if (!isset($subjectData[$subjectName])) {
                                $subjectData[$subjectName] = [
                                    'subject_name' => $subjectName,
                                    'exams' => []
                                ];
                            }
                            
                            $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $school->school_type ?? 'Secondary', $className);
                            $subjectData[$subjectName]['exams'][$examID] = [
                                'marks' => $result->marks,
                                'grade' => $result->grade ?? $gradeOrDivision['grade'],
                                'exam_name' => $exam->exam_name
                            ];
                        }
                        
                        $examAverage = $examSubjectCount > 0 ? $examTotalMarks / $examSubjectCount : 0;
                        $examGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $examAverage);
                        
                        $examsData[] = [
                            'exam' => $exam,
                            'average' => $examAverage,
                            'grade' => $examGradeOrDiv['grade'] ?? null,
                            'division' => $examGradeOrDiv['division'] ?? null
                        ];
                    }
                    
                    // Calculate overall average and grade
                    $overallTotalMarks = 0;
                    $overallSubjectCount = 0;
                    foreach ($subjectData as $subjectName => $data) {
                        $subjectMarks = [];
                        foreach ($data['exams'] as $examID => $examData) {
                            if ($examData['marks'] !== null && $examData['marks'] !== '') {
                                $subjectMarks[] = (float)$examData['marks'];
                            }
                        }
                        if (!empty($subjectMarks)) {
                            $overallTotalMarks += array_sum($subjectMarks);
                            $overallSubjectCount += count($subjectMarks);
                        }
                    }
                    
                    $overallAverage = $overallSubjectCount > 0 ? $overallTotalMarks / $overallSubjectCount : 0;
                    $overallGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $overallAverage);
                    
                    // Calculate subjects with averages
                    $subjects = [];
                    foreach ($subjectData as $subjectName => $data) {
                        $subjectMarks = [];
                        foreach ($data['exams'] as $examID => $examData) {
                            if ($examData['marks'] !== null && $examData['marks'] !== '') {
                                $subjectMarks[] = (float)$examData['marks'];
                            }
                        }
                        $subjectAverage = !empty($subjectMarks) ? array_sum($subjectMarks) / count($subjectMarks) : 0;
                        $subjectGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $subjectAverage);
                        
                        $subjects[] = [
                            'subject_name' => $subjectName,
                            'exams' => array_values($data['exams']),
                            'average' => $subjectAverage,
                            'grade' => $subjectGradeOrDiv['grade'] ?? null
                        ];
                    }
                    
                    $processedResults[] = [
                        'type' => 'report',
                        'student' => $student,
                        'term' => $termFilter,
                        'year' => $yearFilter,
                        'exams' => $examsData,
                        'subjects' => $subjects,
                        'overall_average' => $overallAverage,
                        'overall_grade' => $overallGradeOrDiv['grade'] ?? null,
                        'overall_division' => $overallGradeOrDiv['division'] ?? null
                    ];
                }
            } else {
                // For exam: Group by exam and student (existing logic)
            foreach($results as $result) {
                $examKey = $result->examID . '_' . $result->studentID;
                    if(!isset($processedResults[$examKey])) {
                        $processedResults[$examKey] = [
                            'type' => 'exam',
                        'exam' => $result->examination,
                        'student' => $result->student,
                        'results' => []
                    ];
                }
                    $processedResults[$examKey]['results'][] = $result;
                }
            }

            // Calculate divisions for exam results (existing logic)
            $resultsWithDivisions = [];
            foreach($processedResults as $key => $group) {
                if ($group['type'] === 'exam') {
                    $examKey = $key;
                    if(!isset($resultsWithDivisions[$examKey])) {
                        $resultsWithDivisions[$examKey] = [
                            'exam' => $group['exam'],
                            'student' => $group['student'],
                            'results' => $group['results']
                        ];
                    }
                } else {
                    // For report, use different key
                    $reportKey = 'report_' . $group['student']->studentID . '_' . $group['term'] . '_' . $group['year'];
                    $resultsWithDivisions[$reportKey] = $group;
                }
            }

            // Calculate total division for each group (only for exam type)
            foreach($resultsWithDivisions as $key => $group) {
                // Skip calculation for report type (already calculated)
                if (isset($group['type']) && $group['type'] === 'report') {
                    continue;
                }
                
                $student = $group['student'];
                $examID = $group['exam']->examID ?? null;
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : null;

                $subjectsData = [];
                $totalMarks = 0;
                $subjectCount = 0;

                foreach($group['results'] as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $totalMarks += (float)$result->marks;
                        $subjectCount++;
                    }
                    $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $school->school_type ?? 'Secondary', $className);
                    $subjectsData[] = [
                        'marks' => $result->marks,
                        'points' => $gradeOrDivision['points'] ?? null,
                        'grade' => $gradeOrDivision['grade'] ?? null,
                        'division' => $gradeOrDivision['division'] ?? null
                    ];
                }

                // Calculate total points
                $subjectPoints = array_filter(array_column($subjectsData, 'points'), function($p) { return $p !== null; });
                $totalPoints = 0;

                if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    if (count($subjectPoints) > 0) {
                        sort($subjectPoints);
                        $bestSeven = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
                        $totalPoints = array_sum($bestSeven);
                    }
                } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                    if (count($subjectPoints) > 0) {
                        rsort($subjectPoints);
                        $bestThree = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                        $totalPoints = array_sum($bestThree);
                    }
                }

                $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
                $totalDivision = $this->calculateTotalDivision($totalPoints, $school->school_type ?? 'Secondary', $className, $averageMarks);

                // Calculate position - Get all students who took this exam in the same class/subclass
                $position = null;
                $totalStudentsInExam = 0;

                if ($examID && $student->subclassID) {
                    // Get all students in the same subclass who took this exam
                    $subclassStudents = Student::where('subclassID', $student->subclassID)
                        ->where('status', 'Active')
                        ->pluck('studentID');

                    // Get all results for this exam in this subclass
                    $allSubclassResults = Result::where('examID', $examID)
                        ->whereIn('studentID', $subclassStudents)
                        ->where('status', 'approved')
                        ->with('classSubject')
                        ->get();

                    // Group by student and calculate totals
                    $studentTotals = [];
                    foreach ($allSubclassResults as $subResult) {
                        $subStudentID = $subResult->studentID;
                        if (!isset($studentTotals[$subStudentID])) {
                            $studentTotals[$subStudentID] = [
                                'total_marks' => 0,
                                'total_points' => 0,
                                'subject_count' => 0
                            ];
                        }

                        if ($subResult->marks !== null && $subResult->marks !== '') {
                            $studentTotals[$subStudentID]['total_marks'] += (float)$subResult->marks;
                            $studentTotals[$subStudentID]['subject_count']++;
                        }

                        $subGradeOrDiv = $this->calculateGradeOrDivision($subResult->marks, $school->school_type ?? 'Secondary', $className);
                        if ($subGradeOrDiv['points'] !== null) {
                            $studentTotals[$subStudentID]['total_points'] += $subGradeOrDiv['points'];
                        }
                    }

                    // Calculate average marks and final points for each student
                    $finalScores = [];
                    foreach ($studentTotals as $subStudentID => $totals) {
                        $avgMarks = $totals['subject_count'] > 0 ? $totals['total_marks'] / $totals['subject_count'] : 0;

                        // Calculate final points (same logic as above)
                        $subFinalPoints = 0;
                        if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                            $subFinalPoints = $totals['total_points'];
                        } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                            $subFinalPoints = $totals['total_points'];
                        }

                        $finalScores[$subStudentID] = [
                            'total_marks' => $totals['total_marks'],
                            'average_marks' => $avgMarks,
                            'total_points' => $subFinalPoints
                        ];
                    }

                    // Sort by appropriate criteria
                    if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        // O-Level: Lower points is better
                        uasort($finalScores, function($a, $b) {
                            return ($a['total_points'] ?? 999) <=> ($b['total_points'] ?? 999);
                        });
                    } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                        // A-Level: Higher points is better
                        uasort($finalScores, function($a, $b) {
                            return ($b['total_points'] ?? 0) <=> ($a['total_points'] ?? 0);
                        });
                    } else {
                        // Primary or other: Higher marks is better
                        uasort($finalScores, function($a, $b) {
                            return ($b['average_marks'] ?? 0) <=> ($a['average_marks'] ?? 0);
                        });
                    }

                    // Find position
                    $currentPos = 1;
                    $prevValue = null;
                    $totalStudentsInExam = count($finalScores);

                    foreach ($finalScores as $subStudentID => $score) {
                        $currentValue = $school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])
                            ? $score['total_points']
                            : $score['average_marks'];

                        if ($prevValue !== null && abs($currentValue - $prevValue) > 0.01) {
                            $currentPos++;
                        }

                        if ($subStudentID == $student->studentID) {
                            $position = $currentPos;
                            break;
                        }

                        $prevValue = $currentValue;
                    }
                }

                $resultsWithDivisions[$key]['total_division'] = $totalDivision['division'] ?? null;
                $resultsWithDivisions[$key]['total_grade'] = $totalDivision['grade'] ?? null;
                $resultsWithDivisions[$key]['total_points'] = $totalPoints;
                $resultsWithDivisions[$key]['average_marks'] = $averageMarks;
                $resultsWithDivisions[$key]['subjects_data'] = $subjectsData;
                $resultsWithDivisions[$key]['position'] = $position;
                $resultsWithDivisions[$key]['total_students'] = $totalStudentsInExam;
            }

            // Format results for API response
            $formattedResults = [];
            foreach($resultsWithDivisions as $group) {
                $student = $group['student'];
                $exam = $group['exam'];
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : null;

                $isSecondaryWithDivision = ($school->school_type ?? 'Secondary') === 'Secondary' &&
                    in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six']);
                $displayLabel = ($school->school_type ?? 'Secondary') === 'Primary' ? 'Division' :
                    ($isSecondaryWithDivision ? 'Division' : 'Grade');

                $subjectResults = [];
                foreach($group['results'] as $index => $result) {
                    $subjectData = $group['subjects_data'][$index] ?? null;
                    $displayGradeOrDivision = '';

                    if($subjectData) {
                        if(($school->school_type ?? 'Secondary') === 'Primary') {
                            $displayGradeOrDivision = $subjectData['division'] ?? '-';
                        } else {
                            $displayGradeOrDivision = $subjectData['grade'] ?? '-';
                        }
                    } else {
                        $displayGradeOrDivision = $result->grade ?? '-';
                    }

                    $subjectResults[] = [
                        'resultID' => $result->resultID ?? null,
                        'subject' => [
                            'subjectID' => $result->classSubject && $result->classSubject->subject
                                ? $result->classSubject->subject->subjectID
                                : null,
                            'subject_name' => $result->classSubject && $result->classSubject->subject
                                ? $result->classSubject->subject->subject_name
                                : 'N/A',
                        ],
                        'marks' => $result->marks ?? null,
                        'grade' => $displayGradeOrDivision !== '-' ? $displayGradeOrDivision : null,
                        'division' => ($school->school_type ?? 'Secondary') === 'Primary' && $displayGradeOrDivision !== '-' ? $displayGradeOrDivision : null,
                        'points' => $subjectData['points'] ?? null,
                        'remark' => $result->remark ?? null,
                    ];
                }

                $formattedResults[] = [
                    'exam' => [
                        'examID' => $exam->examID ?? null,
                        'exam_name' => $exam->exam_name ?? 'N/A',
                        'year' => $exam->year ?? 'N/A',
                        'start_date' => $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('Y-m-d') : null,
                        'end_date' => $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('Y-m-d') : null,
                    ],
                    'student' => [
                        'studentID' => $student->studentID ?? null,
                        'first_name' => $student->first_name ?? '',
                        'middle_name' => $student->middle_name ?? '',
                        'last_name' => $student->last_name ?? '',
                        'admission_number' => $student->admission_number ?? '',
                        'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                        'class' => $student->subclass && $student->subclass->class
                            ? $student->subclass->class->class_name
                            : null,
                        'subclass' => $student->subclass
                            ? $student->subclass->subclass_name
                            : null,
                    ],
                    'summary' => [
                        'subject_count' => count($group['results']),
                        'total_marks' => array_sum(array_map(function($r) { return (float)($r->marks ?? 0); }, $group['results'])),
                        'average_marks' => round($group['average_marks'] ?? 0, 1),
                        'total_points' => $group['total_points'] ?? 0,
                        'total_division' => $group['total_division'] ?? null,
                        'total_grade' => $group['total_grade'] ?? null,
                        'display_label' => $displayLabel,
                        'position' => $group['position'] ?? null,
                        'total_students' => $group['total_students'] ?? 0,
                    ],
                    'subjects' => $subjectResults,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Results retrieved successfully',
                'data' => [
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                    ],
                    'school' => [
                        'schoolID' => $school->schoolID ?? null,
                        'school_name' => $school->school_name ?? '',
                        'school_type' => $school->school_type ?? 'Secondary',
                    ],
                    'students' => $students->map(function($student) {
                        return [
                            'studentID' => $student->studentID,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name ?? '',
                            'last_name' => $student->last_name,
                            'admission_number' => $student->admission_number ?? '',
                            'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                            'class' => $student->subclass && $student->subclass->class
                                ? $student->subclass->class->class_name
                                : null,
                            'subclass' => $student->subclass
                                ? $student->subclass->subclass_name
                                : null,
                        ];
                    }),
                    'examinations' => $examinations->map(function($exam) {
                        return [
                            'examID' => $exam->examID,
                            'exam_name' => $exam->exam_name ?? '',
                            'year' => $exam->year ?? '',
                            'term' => $exam->term ?? null,
                            'start_date' => $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('Y-m-d') : null,
                            'end_date' => $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('Y-m-d') : null,
                        ];
                    }),
                    'years' => $years->toArray(),
                    'terms' => $terms->toArray(),
                    'filters' => [
                        'student' => $studentFilter,
                        'year' => $yearFilter,
                        'term' => $termFilter,
                        'type' => $typeFilter,
                        'exam' => $examFilter,
                    ],
                    'statistics' => [
                        'total_students' => $students->count(),
                        'total_examinations' => $examinations->count(),
                        'total_results' => count($formattedResults),
                    ],
                    'results' => $formattedResults,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving results: ' . $e->getMessage()
            ], 500);
        }
    }

    public function parentPayments()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }
        
        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class'])
            ->get();

        // Get available years from payments
        $availableYears = Payment::whereIn('studentID', $students->pluck('studentID'))
            ->where('schoolID', $schoolID)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Get current year
        $currentYear = date('Y');
        
        // Add current year if not in list
        if (!in_array($currentYear, $availableYears)) {
            $availableYears[] = $currentYear;
        }
        
        // Add past years (last 10 years) to show historical data option
        for ($i = 1; $i <= 10; $i++) {
            $pastYear = $currentYear - $i;
            if (!in_array($pastYear, $availableYears)) {
                $availableYears[] = $pastYear;
            }
        }
        
        // Sort years in descending order (newest first)
        rsort($availableYears);

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        return view('Parents.payments', compact('parent', 'students', 'school', 'availableYears', 'currentYear'));
    }

    public function get_parent_payments_ajax(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Get filter parameters
            $search = $request->input('search', '');
            $year = $request->input('year', date('Y'));
            $studentFilter = $request->input('student', '');
            $feeType = $request->input('fee_type', '');

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json(['success' => false, 'message' => 'Parent not found'], 404);
            }

            // Get all students of this parent
            $query = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with(['subclass.class', 'payments' => function($q) use ($year) {
                    $q->whereYear('created_at', $year);
                }, 'payments.fee']);

            // Filter by student if provided
            if (!empty($studentFilter)) {
                $query->where('studentID', $studentFilter);
            }

            // Search by student name or admission number
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('admission_number', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('middle_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'like', '%' . $search . '%');
                });
            }

            $students = $query->orderBy('first_name')->get();

            // Filter payments based on criteria
            $filteredData = [];
            $index = 1;

            foreach ($students as $student) {
                foreach ($student->payments as $payment) {
                    // Filter by year (already filtered in query, but double-check)
                    if ($year && date('Y', strtotime($payment->created_at)) != $year) {
                        continue;
                    }

                    // Filter by fee type
                    if (!empty($feeType) && $payment->fee_type !== $feeType) {
                        continue;
                    }

                    // Format payment data for JSON
                    $paymentData = [
                        'paymentID' => $payment->paymentID,
                        'fee_type' => $payment->fee_type,
                        'control_number' => $payment->control_number,
                        'amount_required' => (float) $payment->amount_required,
                        'amount_paid' => (float) $payment->amount_paid,
                        'balance' => (float) $payment->balance,
                        'payment_status' => $payment->payment_status,
                        'payment_date' => $payment->payment_date ? $payment->payment_date->toDateTimeString() : null,
                        'payment_reference' => $payment->payment_reference,
                        'notes' => $payment->notes,
                    ];

                    // Format student data for JSON
                    $studentImgPath = $student->photo
                        ? asset('userImages/' . $student->photo)
                        : ($student->gender == 'Female'
                            ? asset('images/female.png')
                            : asset('images/male.png'));
                    
                    $studentData = [
                        'studentID' => $student->studentID,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'admission_number' => $student->admission_number,
                        'photo' => $studentImgPath,
                        'subclass' => $student->subclass ? [
                            'subclass_name' => $student->subclass->subclass_name,
                            'class' => $student->subclass->class ? [
                                'class_name' => $student->subclass->class->class_name,
                            ] : null,
                        ] : null,
                    ];

                    // Add to filtered data
                    $filteredData[] = [
                        'index' => $index++,
                        'student' => $studentData,
                        'payment' => $paymentData
                    ];
                }
            }

            // Calculate statistics
            $totalPayments = count($filteredData);
            $pendingPayments = 0;
            $incompletePayments = 0;
            $paidPayments = 0;
            $totalRequired = 0;
            $totalPaid = 0;
            $totalBalance = 0;
            $tuitionFeesTotal = 0;
            $otherFeesTotal = 0;

            foreach ($filteredData as $item) {
                if ($item['payment']) {
                    $payment = $item['payment'];
                    $totalRequired += $payment['amount_required'] ?? 0;
                    $totalPaid += $payment['amount_paid'] ?? 0;
                    $totalBalance += $payment['balance'] ?? 0;

                    // Separate by fee type
                    if ($payment['fee_type'] === 'Tuition Fees') {
                        $tuitionFeesTotal += $payment['amount_required'] ?? 0;
                    } else {
                        $otherFeesTotal += $payment['amount_required'] ?? 0;
                    }

                    if ($payment['payment_status'] === 'Pending') {
                        $pendingPayments++;
                    } elseif ($payment['payment_status'] === 'Incomplete Payment' || $payment['payment_status'] === 'Partial') {
                        $incompletePayments++;
                    } elseif ($payment['payment_status'] === 'Paid') {
                        $paidPayments++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $filteredData,
                'statistics' => [
                    'total_payments' => $totalPayments,
                    'pending_payments' => $pendingPayments,
                    'incomplete_payments' => $incompletePayments,
                    'paid_payments' => $paidPayments,
                    'total_required' => $totalRequired,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance,
                    'tuition_fees_total' => $tuitionFeesTotal,
                    'other_fees_total' => $otherFeesTotal,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request control number for a specific student
     */
    public function request_control_number(Request $request, $studentID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Verify student belongs to this parent (check without status first to give better error message)
            $student = Student::where('studentID', $studentID)
                ->where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found or does not belong to you'
                ], 404);
            }

            // Check if student is active
            if ($student->status !== 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not active (Status: ' . $student->status . '). Only active students can request control numbers.'
                ], 400);
            }

            DB::beginTransaction();

            $generated = 0;
            $messages = [];

            // Get classID from subclass (fees are assigned to class, not subclass)
            $classID = $student->subclass && $student->subclass->class
                ? $student->subclass->class->classID
                : null;

            // Get active fees for this student's class
            $classFees = Fee::where('classID', $classID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->get();

            // Group fees by type
            $tuitionFees = $classFees->where('fee_type', 'Tuition Fees');
            $otherFees = $classFees->where('fee_type', 'Other Fees');

            // Generate control number for Tuition Fees if exists
            if ($tuitionFees->count() > 0) {
                $tuitionAmount = $tuitionFees->sum('amount');
                
                // Get previous year balance (debt) for School Fee
                $previousYearBalance = $this->getPreviousYearBalance($studentID, $schoolID);
                $schoolFeeDebt = $previousYearBalance['school_fee_balance'];
                
                // Add previous year debt to current year amount
                $totalTuitionAmount = $tuitionAmount + $schoolFeeDebt;
                
                // Check if student already has tuition payment
                $existingTuitionPayment = Payment::where('studentID', $studentID)
                    ->where('fee_type', 'Tuition Fees')
                    ->where('payment_status', '!=', 'Paid')
                    ->first();

                if (!$existingTuitionPayment) {
                    $controlNumber = $this->generateControlNumber($schoolID, $studentID, 'TUITION');
                    
                    $payment = Payment::create([
                        'schoolID' => $schoolID,
                        'academic_yearID' => $this->getCurrentAcademicYearID($schoolID),
                        'studentID' => $studentID,
                        'feeID' => null,
                        'fee_type' => 'Tuition Fees',
                        'control_number' => $controlNumber,
                        'amount_required' => $totalTuitionAmount,
                        'amount_paid' => 0,
                        'balance' => $totalTuitionAmount,
                        'payment_status' => 'Pending',
                        'sms_sent' => 'No',
                        'notes' => $schoolFeeDebt > 0 ? "Imeongezewa na deni la mwaka uliopita: " . number_format($schoolFeeDebt, 0) . " TZS" : null,
                    ]);
                    $generated++;
                    $messages[] = "Tuition Fees control number generated: {$controlNumber}";
                    
                    // Send SMS to parent (non-blocking - don't fail if SMS fails)
                    try {
                        $this->sendControlNumberSMS($payment, $student, $schoolID);
                    } catch (\Exception $e) {
                        Log::error("Failed to send SMS for tuition control number: " . $e->getMessage());
                        // Don't fail the request if SMS fails
                    }
                } else {
                    $messages[] = "Tuition Fees control number already exists: {$existingTuitionPayment->control_number}";
                    
                    // Send SMS for existing control number
                    try {
                        $this->sendControlNumberSMS($existingTuitionPayment, $student, $schoolID);
                    } catch (\Exception $e) {
                        Log::error("Failed to send SMS for existing tuition control number: " . $e->getMessage());
                        // Don't fail the request if SMS fails
                    }
                }
            }

            // Generate control number for Other Fees if exists
            if ($otherFees->count() > 0) {
                $otherAmount = $otherFees->sum('amount');
                
                // Check if student already has other fees payment
                $existingOtherPayment = Payment::where('studentID', $studentID)
                    ->where('fee_type', 'Other Fees')
                    ->where('payment_status', '!=', 'Paid')
                    ->first();

                if (!$existingOtherPayment) {
                    // Get previous year balance (debt) for Other Contribution
                    $previousYearBalance = $this->getPreviousYearBalance($studentID, $schoolID);
                    $otherContributionDebt = $previousYearBalance['other_contribution_balance'];
                    
                    // Add previous year debt to current year amount
                    $totalOtherAmount = $otherAmount + $otherContributionDebt;
                    
                    $controlNumber = $this->generateControlNumber($schoolID, $studentID, 'OTHER');
                    
                    $payment = Payment::create([
                        'schoolID' => $schoolID,
                        'academic_yearID' => $this->getCurrentAcademicYearID($schoolID),
                        'studentID' => $studentID,
                        'feeID' => null,
                        'fee_type' => 'Other Fees',
                        'control_number' => $controlNumber,
                        'amount_required' => $totalOtherAmount,
                        'amount_paid' => 0,
                        'balance' => $totalOtherAmount,
                        'payment_status' => 'Pending',
                        'sms_sent' => 'No',
                        'notes' => $otherContributionDebt > 0 ? "Imeongezewa na deni la mwaka uliopita: " . number_format($otherContributionDebt, 0) . " TZS" : null,
                    ]);
                    $generated++;
                    $messages[] = "Other Fees control number generated: {$controlNumber}";
                    
                    // Send SMS to parent (non-blocking - don't fail if SMS fails)
                    try {
                        $this->sendControlNumberSMS($payment, $student, $schoolID);
                    } catch (\Exception $e) {
                        Log::error("Failed to send SMS for other fees control number: " . $e->getMessage());
                        // Don't fail the request if SMS fails
                    }
                } else {
                    $messages[] = "Other Fees control number already exists: {$existingOtherPayment->control_number}";
                    
                    // Send SMS for existing control number
                    try {
                        $this->sendControlNumberSMS($existingOtherPayment, $student, $schoolID);
                    } catch (\Exception $e) {
                        Log::error("Failed to send SMS for existing other fees control number: " . $e->getMessage());
                        // Don't fail the request if SMS fails
                    }
                }
            }

            DB::commit();

            if ($generated > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Control numbers generated successfully',
                    'generated' => $generated,
                    'details' => $messages
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Control numbers already exist or no fees found',
                    'generated' => 0,
                    'details' => $messages
                ], 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating control numbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique control number
     */
    private function generateControlNumber($schoolID, $studentID, $type = 'TUITION')
    {
        do {
            // Generate 5 random digits (0-9)
            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // Format: 3345 + 5 random digits = 9 digits total
            $controlNumber = '3345' . $randomDigits;
            
            // Check if control number already exists
            $exists = Payment::where('control_number', $controlNumber)->exists();
        } while ($exists);

        return $controlNumber;
    }

    /**
     * Send SMS to parent with control number
     */
    private function sendControlNumberSMS($payment, $student, $schoolID)
    {
        try {
            // Reload payment with relationships
            $payment->load(['student.parent', 'student.subclass.class', 'school']);
            
            if (!$payment->student || !$payment->student->parent || !$payment->student->parent->phone) {
                Log::warning("Cannot send SMS: Parent or phone number not found for student {$student->studentID}");
                return false;
            }

            $parent = $payment->student->parent;
            $student = $payment->student;
            $school = $payment->school ?? School::find($schoolID);
            
            if (!$school) {
                Log::warning("School not found for schoolID: {$schoolID}");
                return false;
            }

            $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);
            $parentName = trim($parent->first_name . ' ' . ($parent->last_name ?? ''));

            // Build SMS message based on fee type
            $message = "{$school->school_name}. Mzazi {$parentName}, mwanafunzi {$studentName} ana control number: {$payment->control_number} kwa ajili ya malipo ya ";
            
            if ($payment->fee_type == 'Tuition Fees') {
                // Tuition Fees message with installments
                $message .= "ada za masomo (Tuition Fees). Kiasi kinachohitajika: TZS " . number_format($payment->amount_required, 2);
                
                // Get tuition fees for student's class
                $classID = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->classID
                    : null;
                
                $tuitionFees = collect();
                if ($classID) {
                    $tuitionFees = Fee::where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->where('fee_type', 'Tuition Fees')
                        ->where('status', 'Active')
                        ->with(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }])
                        ->get();
                }
                
                // Add installment information if available
                $allInstallments = collect();
                $allowPartialPayment = false;
                foreach ($tuitionFees as $fee) {
                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $allInstallments = $allInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $allowPartialPayment = true;
                        }
                    }
                }
                
                if ($allInstallments->count() > 0) {
                    $message .= ". Ada hii inaweza kulipwa kwa awamu " . $allInstallments->count() . ": ";
                    $installmentList = [];
                    foreach ($allInstallments as $installment) {
                        $installmentList[] = $installment->installment_name . " (TZS " . number_format($installment->amount, 2) . ")";
                    }
                    $message .= implode(", ", $installmentList);
                    
                    if ($allowPartialPayment) {
                        $message .= ". Unaweza kulipa nusu nusu kwa kila awamu.";
                    } else {
                        $message .= ". Lazima ulipe kiasi kamili cha kila awamu.";
                    }
                }
            } else {
                // Other Fees message with details
                $message .= "ada zingine (Other Fees). Kiasi kinachohitajika: TZS " . number_format($payment->amount_required, 2);
                
                // Get other fees for student's class
                $classID = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->classID
                    : null;
                
                $otherFees = collect();
                if ($classID) {
                    $otherFees = Fee::where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->where('fee_type', 'Other Fees')
                        ->where('status', 'Active')
                        ->with([
                            'otherFeeDetails' => function($q) {
                                $q->where('status', 'Active');
                            },
                            'installments' => function($q) {
                                $q->where('status', 'Active');
                            }
                        ])
                        ->get();
                }
                
                // Add other fees details if available
                $allOtherFeeDetails = collect();
                foreach ($otherFees as $fee) {
                    if ($fee->otherFeeDetails && $fee->otherFeeDetails->count() > 0) {
                        $allOtherFeeDetails = $allOtherFeeDetails->merge($fee->otherFeeDetails);
                    }
                }
                
                if ($allOtherFeeDetails->count() > 0) {
                    $message .= ". Ada hii inajumuisha: ";
                    $detailList = [];
                    foreach ($allOtherFeeDetails as $detail) {
                        $detailText = $detail->fee_detail_name . " (TZS " . number_format($detail->amount, 2) . ")";
                        if ($detail->description) {
                            $detailText .= " - " . $detail->description;
                        }
                        $detailList[] = $detailText;
                    }
                    $message .= implode(", ", $detailList);
                }
                
                // Add installment information if available for Other Fees
                $allInstallments = collect();
                $allowPartialPayment = false;
                foreach ($otherFees as $fee) {
                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $allInstallments = $allInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $allowPartialPayment = true;
                        }
                    }
                }
                
                if ($allInstallments->count() > 0) {
                    $message .= ". Ada hii inaweza kulipwa kwa awamu " . $allInstallments->count() . ": ";
                    $installmentList = [];
                    foreach ($allInstallments as $installment) {
                        $installmentList[] = $installment->installment_name . " (TZS " . number_format($installment->amount, 2) . ")";
                    }
                    $message .= implode(", ", $installmentList);
                    
                    if ($allowPartialPayment) {
                        $message .= ". Unaweza kulipa nusu nusu kwa kila awamu.";
                    } else {
                        $message .= ". Lazima ulipe kiasi kamili cha kila awamu.";
                    }
                }
            }
            
            $message .= " Tafadhali lipa kwa kutumia control number hii kwenye benki yoyote.";

            // Send SMS
            $smsResult = $this->smsService->sendSms($parent->phone, $message);

            if ($smsResult['success']) {
                $payment->sms_sent = 'Yes';
                $payment->sms_sent_at = now();
                $payment->save();
                return true;
            } else {
                Log::error("Failed to send SMS to parent {$parent->parentID}: " . ($smsResult['message'] ?? 'Unknown error'));
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Error sending control number SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change language
     */
    public function changeLanguage(Request $request)
    {
        $locale = $request->input('locale', 'sw');
        
        // Validate locale
        if (!in_array($locale, ['en', 'sw'])) {
            $locale = 'sw';
        }
        
        // Set locale in session
        Session::put('locale', $locale);
        app()->setLocale($locale);
        
        return response()->json([
            'success' => true,
            'locale' => $locale,
            'message' => 'Language changed successfully'
        ]);
    }

    public function parentFeesSummary()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }
        
        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class'])
            ->get();

        // Get available years from payments
        $availableYears = Payment::whereIn('studentID', $students->pluck('studentID'))
            ->where('schoolID', $schoolID)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Get current year
        $currentYear = date('Y');
        
        // Add current year if not in list
        if (!in_array($currentYear, $availableYears)) {
            $availableYears[] = $currentYear;
        }
        
        // Add past years (last 10 years) to show historical data option
        for ($i = 1; $i <= 10; $i++) {
            $pastYear = $currentYear - $i;
            if (!in_array($pastYear, $availableYears)) {
                $availableYears[] = $pastYear;
            }
        }
        
        // Sort years in descending order (newest first)
        rsort($availableYears);

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        return view('Parents.feesSummary', compact('parent', 'students', 'school', 'availableYears', 'currentYear'));
    }

    /**
     * Get fees summary for all students by class
     */
    public function get_fees_summary_ajax(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $year = $request->input('year', date('Y'));

            // Get all students of this parent with subclass and class relationships
            $students = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with([
                    'subclass.class',
                    'payments' => function($q) use ($year) {
                        $q->whereYear('created_at', $year);
                    }
                ])
                ->get();

            $summary = [];

            foreach ($students as $student) {
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : 'N/A';

                // Get classID from subclass (fees are assigned to class, not subclass)
                $classID = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->classID
                    : null;

                // Get fees for this student's class with relationships
                $classFees = Fee::where('classID', $classID)
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->with(['installments' => function($q) {
                        $q->where('status', 'Active');
                    }, 'otherFeeDetails' => function($q) {
                        $q->where('status', 'Active');
                    }])
                    ->get();

                $tuitionFees = $classFees->where('fee_type', 'Tuition Fees');
                $otherFees = $classFees->where('fee_type', 'Other Fees');

                // Get payments for this student
                $tuitionPayment = $student->payments->where('fee_type', 'Tuition Fees')->first();
                $otherPayment = $student->payments->where('fee_type', 'Other Fees')->first();

                // Calculate totals
                $tuitionRequired = $tuitionFees->sum('amount');
                $otherRequired = $otherFees->sum('amount');
                $totalRequired = $tuitionRequired + $otherRequired;

                $tuitionPaid = $tuitionPayment ? (float) $tuitionPayment->amount_paid : 0;
                $otherPaid = $otherPayment ? (float) $otherPayment->amount_paid : 0;
                $totalPaid = $tuitionPaid + $otherPaid;

                $tuitionBalance = $tuitionRequired - $tuitionPaid;
                $otherBalance = $otherRequired - $otherPaid;
                $totalBalance = $totalRequired - $totalPaid;

                // Get installments for tuition fees
                $tuitionInstallments = collect();
                $tuitionAllowPartial = false;
                foreach ($tuitionFees as $fee) {
                    // Reload fee with installments if not already loaded
                    if (!$fee->relationLoaded('installments')) {
                        $fee->load(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }
                    
                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $tuitionInstallments = $tuitionInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $tuitionAllowPartial = true;
                        }
                    }
                }

                // Get installments and other fees details for other fees
                $otherInstallments = collect();
                $otherAllowPartial = false;
                $otherFeesDetails = collect();
                foreach ($otherFees as $fee) {
                    // Reload fee with installments and otherFeeDetails if not already loaded
                    if (!$fee->relationLoaded('installments')) {
                        $fee->load(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }
                    if (!$fee->relationLoaded('otherFeeDetails')) {
                        $fee->load(['otherFeeDetails' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }
                    
                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $otherInstallments = $otherInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $otherAllowPartial = true;
                        }
                    }
                    if ($fee->otherFeeDetails && $fee->otherFeeDetails->count() > 0) {
                        $otherFeesDetails = $otherFeesDetails->merge($fee->otherFeeDetails);
                    }
                }

                $summary[] = [
                    'studentID' => $student->studentID,
                    'student_name' => ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''),
                    'admission_number' => $student->admission_number ?? 'N/A',
                    'class' => $className,
                    'tuition_fees' => [
                        'required' => $tuitionRequired,
                        'paid' => $tuitionPaid,
                        'balance' => $tuitionBalance,
                        'control_number' => $tuitionPayment ? $tuitionPayment->control_number : null,
                        'status' => $tuitionPayment ? $tuitionPayment->payment_status : 'No Payment',
                        'installments' => $tuitionInstallments->map(function($installment) {
                            return [
                                'installmentID' => $installment->installmentID,
                                'installment_name' => $installment->installment_name,
                                'installment_type' => $installment->installment_type,
                                'amount' => (float) $installment->amount,
                            ];
                        })->values(),
                        'allow_partial_payment' => $tuitionAllowPartial
                    ],
                    'other_fees' => [
                        'required' => $otherRequired,
                        'paid' => $otherPaid,
                        'balance' => $otherBalance,
                        'control_number' => $otherPayment ? $otherPayment->control_number : null,
                        'status' => $otherPayment ? $otherPayment->payment_status : 'No Payment',
                        'installments' => $otherInstallments->map(function($installment) {
                            return [
                                'installmentID' => $installment->installmentID,
                                'installment_name' => $installment->installment_name,
                                'installment_type' => $installment->installment_type,
                                'amount' => (float) $installment->amount,
                            ];
                        })->values(),
                        'allow_partial_payment' => $otherAllowPartial,
                        'other_fees_details' => $otherFeesDetails->map(function($detail) {
                            return [
                                'detailID' => $detail->detailID,
                                'fee_detail_name' => $detail->fee_detail_name,
                                'amount' => (float) $detail->amount,
                                'description' => $detail->description,
                            ];
                        })->values()
                    ],
                    'total' => [
                        'required' => $totalRequired,
                        'paid' => $totalPaid,
                        'balance' => $totalBalance
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading fees summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Endpoint: Get Parent Payments
     *
     * This endpoint returns payment records for a parent's children including:
     * - Parent details
     * - Students list
     * - Payment records with details
     * - Payment statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Request Parameters (Query or Body):
     * - parentID: integer (required) - Parent ID obtained from login
     * - schoolID: integer (required) - School ID obtained from login
     * - student: integer (optional) - Filter by student ID
     * - year: string (optional) - Filter by year (default: current year)
     * - fee_type: string (optional) - Filter by fee type (Tuition Fees, Other Fees)
     * - search: string (optional) - Search by student name or admission number
     *
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Payments retrieved successfully",
     *   "data": {
     *     "parent": {...},
     *     "school": {...},
     *     "students": [...],
     *     "years": [...],
     *     "filters": {...},
     *     "statistics": {...},
     *     "payments": [...]
     *   }
     * }
     */
    public function apiGetParentPayments(Request $request)
    {
        try {
            // Get parameters
            $parentID = $request->input('parentID');
            $schoolID = $request->input('schoolID');
            $studentFilter = $request->input('student', '');
            $yearFilter = $request->input('year', date('Y'));
            $feeTypeFilter = $request->input('fee_type', '');
            $search = $request->input('search', '');

            // Validate required parameters
            if (!$parentID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'parentID and schoolID are required'
                ], 400);
            }

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Get all students of this parent
            $query = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with(['subclass.class', 'payments' => function($q) use ($yearFilter) {
                    $q->whereYear('created_at', $yearFilter);
                }, 'payments.fee']);

            // Filter by student if provided
            if (!empty($studentFilter)) {
                $query->where('studentID', $studentFilter);
            }

            // Search by student name or admission number
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('admission_number', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('middle_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'like', '%' . $search . '%');
                });
            }

            $students = $query->orderBy('first_name')->get();

            // Get available years from payments
            $availableYears = Payment::whereIn('studentID', $students->pluck('studentID'))
                ->where('schoolID', $schoolID)
                ->selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            $currentYear = date('Y');
            if (!in_array($currentYear, $availableYears)) {
                $availableYears[] = $currentYear;
            }
            rsort($availableYears);

            // Get school details
            $school = School::where('schoolID', $schoolID)->first();

            // Format payments data
            $formattedPayments = [];
            $index = 1;

            foreach ($students as $student) {
                foreach ($student->payments as $payment) {
                    // Filter by year (already filtered in query, but double-check)
                    if ($yearFilter && date('Y', strtotime($payment->created_at)) != $yearFilter) {
                        continue;
                    }

                    // Filter by fee type
                    if (!empty($feeTypeFilter) && $payment->fee_type !== $feeTypeFilter) {
                        continue;
                    }

                    // Format payment data
                    $paymentData = [
                        'paymentID' => $payment->paymentID,
                        'fee_type' => $payment->fee_type,
                        'control_number' => $payment->control_number,
                        'amount_required' => (float) $payment->amount_required,
                        'amount_paid' => (float) $payment->amount_paid,
                        'balance' => (float) $payment->balance,
                        'payment_status' => $payment->payment_status,
                        'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                        'payment_reference' => $payment->payment_reference,
                        'notes' => $payment->notes,
                        'created_at' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : null,
                    ];

                    // Format student data
                    $studentData = [
                        'studentID' => $student->studentID,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name ?? '',
                        'last_name' => $student->last_name,
                        'admission_number' => $student->admission_number,
                        'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                        'class' => $student->subclass && $student->subclass->class
                            ? $student->subclass->class->class_name
                            : null,
                        'subclass' => $student->subclass
                            ? $student->subclass->subclass_name
                            : null,
                    ];

                    $formattedPayments[] = [
                        'index' => $index++,
                        'student' => $studentData,
                        'payment' => $paymentData
                    ];
                }
            }

            // Calculate statistics
            $totalPayments = count($formattedPayments);
            $pendingPayments = 0;
            $incompletePayments = 0;
            $paidPayments = 0;
            $totalRequired = 0;
            $totalPaid = 0;
            $totalBalance = 0;
            $tuitionFeesTotal = 0;
            $otherFeesTotal = 0;

            foreach ($formattedPayments as $item) {
                if ($item['payment']) {
                    $payment = $item['payment'];
                    $totalRequired += $payment['amount_required'] ?? 0;
                    $totalPaid += $payment['amount_paid'] ?? 0;
                    $totalBalance += $payment['balance'] ?? 0;

                    // Separate by fee type
                    if ($payment['fee_type'] === 'Tuition Fees') {
                        $tuitionFeesTotal += $payment['amount_required'] ?? 0;
                    } else {
                        $otherFeesTotal += $payment['amount_required'] ?? 0;
                    }

                    if ($payment['payment_status'] === 'Pending') {
                        $pendingPayments++;
                    } elseif ($payment['payment_status'] === 'Incomplete Payment' || $payment['payment_status'] === 'Partial') {
                        $incompletePayments++;
                    } elseif ($payment['payment_status'] === 'Paid') {
                        $paidPayments++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payments retrieved successfully',
                'data' => [
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                    ],
                    'school' => [
                        'schoolID' => $school->schoolID ?? null,
                        'school_name' => $school->school_name ?? '',
                    ],
                    'students' => $students->map(function($student) {
                        return [
                            'studentID' => $student->studentID,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name ?? '',
                            'last_name' => $student->last_name,
                            'admission_number' => $student->admission_number ?? '',
                            'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                            'class' => $student->subclass && $student->subclass->class
                                ? $student->subclass->class->class_name
                                : null,
                            'subclass' => $student->subclass
                                ? $student->subclass->subclass_name
                                : null,
                        ];
                    }),
                    'years' => $availableYears,
                    'filters' => [
                        'student' => $studentFilter,
                        'year' => $yearFilter,
                        'fee_type' => $feeTypeFilter,
                        'search' => $search,
                    ],
                    'statistics' => [
                        'total_payments' => $totalPayments,
                        'pending_payments' => $pendingPayments,
                        'incomplete_payments' => $incompletePayments,
                        'paid_payments' => $paidPayments,
                        'total_required' => round($totalRequired, 2),
                        'total_paid' => round($totalPaid, 2),
                        'total_balance' => round($totalBalance, 2),
                        'tuition_fees_total' => round($tuitionFeesTotal, 2),
                        'other_fees_total' => round($otherFeesTotal, 2),
                    ],
                    'payments' => $formattedPayments,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Endpoint: Get Parent Fees Summary
     *
     * This endpoint returns fees summary for a parent's children including:
     * - Fees required for each student
     * - Payment status
     * - Installments information
     * - Other fees details
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Request Parameters (Query or Body):
     * - parentID: integer (required) - Parent ID obtained from login
     * - schoolID: integer (required) - School ID obtained from login
     * - year: string (optional) - Filter by year (default: current year)
     *
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Fees summary retrieved successfully",
     *   "data": {
     *     "parent": {...},
     *     "school": {...},
     *     "years": [...],
     *     "filters": {...},
     *     "summary": [...]
     *   }
     * }
     */
    public function apiGetParentFeesSummary(Request $request)
    {
        try {
            // Get parameters
            $parentID = $request->input('parentID');
            $schoolID = $request->input('schoolID');
            $yearFilter = $request->input('year', date('Y'));

            // Validate required parameters
            if (!$parentID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'parentID and schoolID are required'
                ], 400);
            }

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Get all students of this parent with subclass and class relationships
            $students = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with([
                    'subclass.class',
                    'payments' => function($q) use ($yearFilter) {
                        $q->whereYear('created_at', $yearFilter);
                    }
                ])
                ->get();

            // Get available years from payments
            $availableYears = Payment::whereIn('studentID', $students->pluck('studentID'))
                ->where('schoolID', $schoolID)
                ->selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            $currentYear = date('Y');
            if (!in_array($currentYear, $availableYears)) {
                $availableYears[] = $currentYear;
            }
            rsort($availableYears);

            // Get school details
            $school = School::where('schoolID', $schoolID)->first();

            $summary = [];

            foreach ($students as $student) {
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : 'N/A';

                // Get classID from subclass (fees are assigned to class, not subclass)
                $classID = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->classID
                    : null;

                // Get fees for this student's class with relationships
                $classFees = Fee::where('classID', $classID)
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->with(['installments' => function($q) {
                        $q->where('status', 'Active');
                    }, 'otherFeeDetails' => function($q) {
                        $q->where('status', 'Active');
                    }])
                    ->get();

                $tuitionFees = $classFees->where('fee_type', 'Tuition Fees');
                $otherFees = $classFees->where('fee_type', 'Other Fees');

                // Get payments for this student
                $tuitionPayment = $student->payments->where('fee_type', 'Tuition Fees')->first();
                $otherPayment = $student->payments->where('fee_type', 'Other Fees')->first();

                // Calculate totals
                $tuitionRequired = $tuitionFees->sum('amount');
                $otherRequired = $otherFees->sum('amount');
                $totalRequired = $tuitionRequired + $otherRequired;

                $tuitionPaid = $tuitionPayment ? (float) $tuitionPayment->amount_paid : 0;
                $otherPaid = $otherPayment ? (float) $otherPayment->amount_paid : 0;
                $totalPaid = $tuitionPaid + $otherPaid;

                $tuitionBalance = $tuitionRequired - $tuitionPaid;
                $otherBalance = $otherRequired - $otherPaid;
                $totalBalance = $totalRequired - $totalPaid;

                // Get installments for tuition fees
                $tuitionInstallments = collect();
                $tuitionAllowPartial = false;
                foreach ($tuitionFees as $fee) {
                    if (!$fee->relationLoaded('installments')) {
                        $fee->load(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }
                    
                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $tuitionInstallments = $tuitionInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $tuitionAllowPartial = true;
                        }
                    }
                }

                // Get installments and other fees details for other fees
                $otherInstallments = collect();
                $otherAllowPartial = false;
                $otherFeesDetails = collect();
                foreach ($otherFees as $fee) {
                    if (!$fee->relationLoaded('installments')) {
                        $fee->load(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }
                    if (!$fee->relationLoaded('otherFeeDetails')) {
                        $fee->load(['otherFeeDetails' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }
                    
                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $otherInstallments = $otherInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $otherAllowPartial = true;
                        }
                    }
                    if ($fee->otherFeeDetails && $fee->otherFeeDetails->count() > 0) {
                        $otherFeesDetails = $otherFeesDetails->merge($fee->otherFeeDetails);
                    }
                }

                $summary[] = [
                    'studentID' => $student->studentID,
                    'student_name' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'admission_number' => $student->admission_number ?? 'N/A',
                    'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                    'class' => $className,
                    'tuition_fees' => [
                        'required' => round($tuitionRequired, 2),
                        'paid' => round($tuitionPaid, 2),
                        'balance' => round($tuitionBalance, 2),
                        'control_number' => $tuitionPayment ? $tuitionPayment->control_number : null,
                        'status' => $tuitionPayment ? $tuitionPayment->payment_status : 'No Payment',
                        'installments' => $tuitionInstallments->map(function($installment) {
                            return [
                                'installmentID' => $installment->installmentID,
                                'installment_name' => $installment->installment_name,
                                'installment_type' => $installment->installment_type,
                                'amount' => (float) $installment->amount,
                            ];
                        })->values(),
                        'allow_partial_payment' => $tuitionAllowPartial
                    ],
                    'other_fees' => [
                        'required' => round($otherRequired, 2),
                        'paid' => round($otherPaid, 2),
                        'balance' => round($otherBalance, 2),
                        'control_number' => $otherPayment ? $otherPayment->control_number : null,
                        'status' => $otherPayment ? $otherPayment->payment_status : 'No Payment',
                        'installments' => $otherInstallments->map(function($installment) {
                            return [
                                'installmentID' => $installment->installmentID,
                                'installment_name' => $installment->installment_name,
                                'installment_type' => $installment->installment_type,
                                'amount' => (float) $installment->amount,
                            ];
                        })->values(),
                        'allow_partial_payment' => $otherAllowPartial,
                        'other_fees_details' => $otherFeesDetails->map(function($detail) {
                            return [
                                'detailID' => $detail->detailID,
                                'fee_detail_name' => $detail->fee_detail_name,
                                'amount' => (float) $detail->amount,
                                'description' => $detail->description,
                            ];
                        })->values()
                    ],
                    'total' => [
                        'required' => round($totalRequired, 2),
                        'paid' => round($totalPaid, 2),
                        'balance' => round($totalBalance, 2)
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Fees summary retrieved successfully',
                'data' => [
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                    ],
                    'school' => [
                        'schoolID' => $school->schoolID ?? null,
                        'school_name' => $school->school_name ?? '',
                    ],
                    'years' => $availableYears,
                    'filters' => [
                        'year' => $yearFilter,
                    ],
                    'summary' => $summary,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving fees summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function parentSubjects()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all active students of this parent with their subclass and class
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['subclass.class'])
            ->get();

        return view('Parents.subjects', compact('parent', 'students'));
    }

    public function getStudentSubjects($studentID)
    {
        try {
            $parentID = Session::get('parentID');
            $schoolID = Session::get('schoolID');

            if (!$parentID || !$schoolID) {
                return response()->json([
                    'error' => 'Parent ID or School ID not found in session.'
                ], 400);
            }

            // Verify student belongs to this parent
            $student = Student::where('studentID', $studentID)
                ->where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('subclass.class')
                ->first();

            if (!$student) {
                return response()->json([
                    'error' => 'Student not found or unauthorized access.'
                ], 404);
            }

            // Get all subjects for this student's subclass
            $subjects = ClassSubject::where('subclassID', $student->subclassID)
                ->with(['subject', 'teacher'])
                ->get()
                ->map(function($classSubject) use ($student) {
                    // Get election counts
                    $totalElectors = SubjectElector::where('classSubjectID', $classSubject->class_subjectID)->count();
                    $totalStudents = Student::where('subclassID', $classSubject->subclassID)
                        ->where('status', 'Active')
                        ->count();
                    $nonElectors = $totalStudents - $totalElectors;
                    
                    // Check if this student has elected this subject
                    $isElected = SubjectElector::where('studentID', $student->studentID)
                        ->where('classSubjectID', $classSubject->class_subjectID)
                        ->exists();

                    return [
                        'class_subjectID' => $classSubject->class_subjectID,
                        'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                        'subject_code' => $classSubject->subject ? $classSubject->subject->subject_code : null,
                        'teacher_name' => $classSubject->teacher
                            ? $classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name
                            : 'Not Assigned',
                        'status' => $classSubject->status ?? 'Inactive',
                        'student_status' => $classSubject->student_status ?? null,
                        'elected_count' => $totalElectors,
                        'non_elected_count' => $nonElectors,
                        'total_students' => $totalStudents,
                        'is_elected' => $isElected,
                    ];
                });

            return response()->json([
                'success' => true,
                'student' => [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'subclass_name' => $student->subclass ? ($student->subclass->class->class_name . ' ' . trim($student->subclass->subclass_name)) : 'N/A',
                ],
                'subjects' => $subjects
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function electSubject(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'studentID' => 'required|exists:students,studentID',
                'classSubjectID' => 'required|exists:class_subjects,class_subjectID',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $parentID = Session::get('parentID');
            $schoolID = Session::get('schoolID');

            if (!$parentID || !$schoolID) {
                return response()->json(['error' => 'Parent ID or School ID not found in session.'], 400);
            }

            // Verify student belongs to this parent
            $student = Student::where('studentID', $request->studentID)
                ->where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('parent')
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or unauthorized access.'], 404);
            }

            // Get class subject
            $classSubject = ClassSubject::with(['subject', 'subclass.class'])->find($request->classSubjectID);
            if (!$classSubject) {
                return response()->json(['error' => 'Class subject not found.'], 404);
            }

            // Verify it's an optional subject
            if ($classSubject->student_status !== 'Optional') {
                return response()->json(['error' => 'This subject is required, not optional.'], 400);
            }

            // Verify student belongs to this subclass
            if ($classSubject->subclassID != $student->subclassID) {
                return response()->json(['error' => 'Student does not belong to this subclass.'], 400);
            }

            // Check if already elected
            $existing = SubjectElector::where('studentID', $request->studentID)
                ->where('classSubjectID', $request->classSubjectID)
                ->first();

            if ($existing) {
                return response()->json(['error' => 'Student has already elected this subject.'], 400);
            }

            // Create election
            SubjectElector::create([
                'studentID' => $request->studentID,
                'classSubjectID' => $request->classSubjectID,
            ]);

            // Send SMS notification
            $this->sendElectionSMS($student, $classSubject, 'elected');

            return response()->json([
                'success' => 'Subject elected successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deselectSubject(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'studentID' => 'required|exists:students,studentID',
                'classSubjectID' => 'required|exists:class_subjects,class_subjectID',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $parentID = Session::get('parentID');
            $schoolID = Session::get('schoolID');

            if (!$parentID || !$schoolID) {
                return response()->json(['error' => 'Parent ID or School ID not found in session.'], 400);
            }

            // Verify student belongs to this parent
            $student = Student::where('studentID', $request->studentID)
                ->where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('parent')
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or unauthorized access.'], 404);
            }

            // Get class subject
            $classSubject = ClassSubject::with(['subject', 'subclass.class'])->find($request->classSubjectID);
            if (!$classSubject) {
                return response()->json(['error' => 'Class subject not found.'], 404);
            }

            // Remove election
            $elector = SubjectElector::where('studentID', $request->studentID)
                ->where('classSubjectID', $request->classSubjectID)
                ->first();

            if (!$elector) {
                return response()->json(['error' => 'Student has not elected this subject.'], 400);
            }

            $elector->delete();

            // Send SMS notification
            $this->sendElectionSMS($student, $classSubject, 'deselected');

            return response()->json([
                'success' => 'Subject deselected successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendElectionSMS(Student $student, ClassSubject $classSubject, $action)
    {
        try {
            if (!$student->parent || !$student->parent->phone) {
                \Log::warning("Parent or parent phone not found for student ID: {$student->studentID}. Cannot send SMS.");
                return;
            }

            $schoolName = Session::get('school_name') ?? 'ShuleLink';
            $studentName = $student->first_name . ' ' . $student->last_name;
            $subjectName = $classSubject->subject->subject_name ?? 'N/A';
            $className = $classSubject->subclass->class->class_name ?? 'N/A';
            $subclassName = $classSubject->subclass->subclass_name ?? 'N/A';
            $fullClassName = trim($subclassName) === '' ? $className : $className . ' ' . $subclassName;

            $message = '';
            if ($action === 'elected') {
                $message = "{$schoolName}. Mwanafunzi {$studentName} amechagua somo la {$subjectName} kwenye darasa {$fullClassName}. Asante";
            } elseif ($action === 'deselected') {
                $message = "{$schoolName}. Mwanafunzi {$studentName} ameondoa somo la {$subjectName} kwenye darasa {$fullClassName}. Asante";
            }

            if (!empty($message)) {
                $response = $this->smsService->sendSms($student->parent->phone, $message);
                if (!$response['success']) {
                    \Log::error("Failed to send election SMS to {$student->parent->phone}: {$response['message']}");
                } else {
                    \Log::info("Election SMS sent to {$student->parent->phone} for student {$student->studentID} ({$action}).");
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error sending election SMS: " . $e->getMessage());
        }
    }
}
