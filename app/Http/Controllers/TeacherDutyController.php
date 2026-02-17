<?php

namespace App\Http\Controllers;

use App\Models\TeacherDuty;
use App\Models\Teacher; // Assuming Teacher model exists or is 'App\Models\Teachers'
use App\Models\Term;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TeacherDutyController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new \App\Services\SmsService();
    }

    public function index(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login');
        }

        // --- FILTERING LOGIC ---
        // Default to current month if no dates provided
        $fromDate = $request->get('from_date') ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->get('to_date') ?: Carbon::now()->endOfMonth()->format('Y-m-d');

        // Human readable month title
        $monthTitle = Carbon::parse($fromDate)->format('F Y');
        if ($fromDate && $toDate && Carbon::parse($fromDate)->format('M Y') != Carbon::parse($toDate)->format('M Y')) {
            $monthTitle = Carbon::parse($fromDate)->format('M Y') . ' - ' . Carbon::parse($toDate)->format('M Y');
        }

        // Fetch current active term and year (for assignments)
        $activeTerm = Term::where('schoolID', $schoolID)->where('status', 'Active')->first();
        $activeYear = AcademicYear::where('schoolID', $schoolID)->where('status', 'Active')->first();

        // Fetch duties for this school within date range
        $duties = TeacherDuty::where('schoolID', $schoolID)
            ->with(['teacher', 'term'])
            ->where(function($q) use ($fromDate, $toDate) {
                $q->whereBetween('start_date', [$fromDate, $toDate])
                  ->orWhereBetween('end_date', [$fromDate, $toDate])
                  ->orWhere(function($sub) use ($fromDate, $toDate) {
                      $sub->where('start_date', '<=', $fromDate)
                          ->where('end_date', '>=', $toDate);
                  });
            })
            ->orderBy('start_date', 'asc')
            ->get();

        // Fetch all teachers for the dropdown
        $teachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();

        // Get the end date of the last duty assigned to help with continuity
        $lastDuty = TeacherDuty::where('schoolID', $schoolID)->orderBy('end_date', 'desc')->first();
        $lastDutyEndDate = $lastDuty ? $lastDuty->end_date : null;

        // If AJAX, we only want the table part and the title
        if ($request->ajax()) {
            return response()->json([
                'html' => view('Admin.teacher_duties.table_body', compact('duties'))->render(),
                'title' => 'Teacher on Duties in ' . $monthTitle
            ]);
        }

        return view('Admin.teacher_duties.index', compact('duties', 'teachers', 'activeTerm', 'activeYear', 'lastDutyEndDate', 'fromDate', 'toDate', 'monthTitle'));
    }

    public function export_pdf(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) return redirect()->back();

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $teacherID = Session::get('teacherID');
    $isTeacherRoute = str_contains($request->route()->getName(), 'teacher.duty_book');

    $query = TeacherDuty::where('schoolID', $schoolID)
        ->with(['teacher', 'term'])
        ->whereBetween('start_date', [$fromDate, $toDate])
        ->orderBy('start_date', 'asc');

    // If teacher is exporting, only show their assignments
    if ($isTeacherRoute && $teacherID) {
        $query->where('teacherID', $teacherID);
    }

    $duties = $query->get();

        $school = \App\Models\School::find($schoolID);
        $monthTitle = Carbon::parse($fromDate)->format('F Y');

        $dompdf = new \Dompdf\Dompdf();
        $html = view('Admin.teacher_duties.pdf_roster', compact('duties', 'school', 'fromDate', 'toDate', 'monthTitle'))->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Teacher_Duty_Roster_' . $monthTitle . '.pdf';
        return response()->streamDownload(function() use ($dompdf) {
            echo $dompdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function teacherIndex(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return redirect()->route('login');
        }

        $fromDate = $request->get('from_date') ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->get('to_date') ?: Carbon::now()->endOfMonth()->format('Y-m-d');
        $monthTitle = Carbon::parse($fromDate)->format('F Y');

        // Only show duties assigned to THIS teacher
        $duties = TeacherDuty::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->with(['teacher', 'term'])
            ->where(function($q) use ($fromDate, $toDate) {
                $q->whereBetween('start_date', [$fromDate, $toDate])
                  ->orWhereBetween('end_date', [$fromDate, $toDate]);
            })
            ->orderBy('start_date', 'asc')
            ->get();

        // Check if current teacher is on duty
        $today = Carbon::today();
        $currentDuty = TeacherDuty::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        // Fetch classes for the attendance form
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->orderBy('class_name', 'asc')->get();

        // Fetch reports for status indicators
        $reports = \App\Models\DailyDutyReport::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->whereBetween('report_date', [$fromDate, $toDate])
            ->get()
            ->keyBy(function($item) {
                return $item->report_date->format('Y-m-d');
            });

        return view('Teacher.duty_book', compact('duties', 'monthTitle', 'fromDate', 'toDate', 'currentDuty', 'classes', 'reports'));
    }

    public function getDailyReport(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        $date = $request->get('date');

        if (!$date) return response()->json(['success' => false, 'message' => 'Date is required.'], 400);

        // Fetch saved report
        $report = \App\Models\DailyDutyReport::where('schoolID', $schoolID)
            ->whereDate('report_date', $date)
            ->first();

        // System Attendance Calculation (Pre-fill data)
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->orderBy('class_name', 'asc')->get();
        $systemAttendance = [];
        
        foreach ($classes as $class) {
            $subclassIDs = \App\Models\Subclass::where('classID', $class->classID)->pluck('subclassID')->toArray();

            // Registered
            $registeredBoys = \App\Models\Student::where('schoolID', $schoolID)
                ->whereIn('subclassID', $subclassIDs)
                ->where('status', '!=', 'Transferred')
                ->where('gender', 'Male')
                ->count();

            $registeredGirls = \App\Models\Student::where('schoolID', $schoolID)
                ->whereIn('subclassID', $subclassIDs)
                ->where('status', '!=', 'Transferred')
                ->where('gender', 'Female')
                ->count();

            // 1. System Attendance Stats (Only from 'attendances' table)
            // Join with students to get gender for boy/girl breakdown
            // Using LEFT JOIN to ensure we don't drop records if student record is somehow missing (though it shouldn't be)
            $stats = DB::table('attendances')
                ->leftJoin('students', 'attendances.studentID', '=', 'students.studentID')
                ->where('attendances.schoolID', $schoolID)
                ->whereIn('attendances.subclassID', $subclassIDs)
                ->whereDate('attendances.attendance_date', $date)
                ->select(
                    DB::raw('SUM(CASE WHEN LOWER(students.gender) = "male" AND attendances.status = "Present" THEN 1 ELSE 0 END) as present_boys'),
                    DB::raw('SUM(CASE WHEN LOWER(students.gender) = "female" AND attendances.status = "Present" THEN 1 ELSE 0 END) as present_girls'),
                    DB::raw('SUM(CASE WHEN LOWER(students.gender) = "male" AND attendances.status = "Absent" THEN 1 ELSE 0 END) as absent_boys'),
                    DB::raw('SUM(CASE WHEN LOWER(students.gender) = "female" AND attendances.status = "Absent" THEN 1 ELSE 0 END) as absent_girls'),
                    DB::raw('SUM(CASE WHEN LOWER(students.gender) = "male" AND attendances.status = "Sick" THEN 1 ELSE 0 END) as sick_boys'),
                    DB::raw('SUM(CASE WHEN LOWER(students.gender) = "female" AND attendances.status = "Sick" THEN 1 ELSE 0 END) as sick_girls'),
                    DB::raw('SUM(CASE WHEN LOWER(students.gender) = "male" AND (attendances.status = "Excused" OR attendances.status = "Permission") THEN 1 ELSE 0 END) as perm_boys'),
                    DB::raw('SUM(CASE WHEN LOWER(students.gender) = "female" AND (attendances.status = "Excused" OR attendances.status = "Permission") THEN 1 ELSE 0 END) as perm_girls')
                )
                ->first();

            // Debugging log to verify data
            \Illuminate\Support\Facades\Log::info("Duty Report System Stats for Class " . $class->classID, [
                'subclasses' => $subclassIDs,
                'date' => $date,
                'stats' => $stats
            ]);

            // New Comers (Students admitted on this date)
            $newComersBoys = \App\Models\Student::where('schoolID', $schoolID)
                ->whereIn('subclassID', $subclassIDs)
                ->whereDate('admission_date', $date)
                ->where('gender', 'Male')
                ->count();

            $newComersGirls = \App\Models\Student::where('schoolID', $schoolID)
                ->whereIn('subclassID', $subclassIDs)
                ->whereDate('admission_date', $date)
                ->where('gender', 'Female')
                ->count();

            $systemAttendance[$class->classID] = [
                'reg_b' => (int)$registeredBoys,
                'reg_g' => (int)$registeredGirls,
                'pres_b' => (int)($stats->present_boys ?? 0),
                'pres_g' => (int)($stats->present_girls ?? 0),
                'abs_b' => (int)($stats->absent_boys ?? 0),
                'abs_g' => (int)($stats->absent_girls ?? 0),
                'perm_b' => (int)($stats->perm_boys ?? 0),
                'perm_g' => (int)($stats->perm_girls ?? 0),
                'new_b' => (int)$newComersBoys,
                'new_g' => (int)$newComersGirls,
                'shifted_b' => 0,
                'shifted_g' => 0,
                'sick_b' => (int)($stats->sick_boys ?? 0),
                'sick_g' => (int)($stats->sick_girls ?? 0),
            ];
    }

        // Total active students in school for percentage calculation
        $totalActiveStudents = \App\Models\Student::where('schoolID', $schoolID)
            ->where('status', '!=', 'Transferred')
            ->count();

        return response()->json([
            'success' => true, 
            'report' => $report, 
            'system_attendance' => $systemAttendance,
            'total_active_students' => $totalActiveStudents
        ]);
    }

    public function saveDailyReport(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("Saving Daily Report", $request->all());
        
        try {
            $schoolID = Session::get('schoolID');
            $teacherID = Session::get('teacherID');
            
            if (!$schoolID || !$teacherID) {
                return response()->json(['success' => false, 'message' => 'Session expired. Please login again.'], 401);
            }

            $request->validate([
                'report_date' => 'required|date',
                'action' => 'required|in:send,draft',
                'attendance_percentage' => 'nullable|numeric'
            ]);

            $data = $request->all();
            $date = $data['report_date'];

            // Handle potential JSON string from JS
            $attendanceData = $request->get('attendance_data');
            if (is_string($attendanceData)) {
                $attendanceData = json_decode($attendanceData, true);
            }

            $report = \App\Models\DailyDutyReport::updateOrCreate(
                ['schoolID' => $schoolID, 'teacherID' => $teacherID, 'report_date' => $date],
                [
                    'attendance_data' => $attendanceData ?? [],
                    'attendance_percentage' => $data['attendance_percentage'] ?? 0,
                    'school_environment' => $data['school_environment'] ?? null,
                    'pupils_cleanliness' => $data['pupils_cleanliness'] ?? null,
                    'teachers_attendance' => $data['teachers_attendance'] ?? null,
                    'timetable_status' => $data['timetable_status'] ?? null,
                    'outside_activities' => $data['outside_activities'] ?? null,
                    'special_events' => $data['special_events'] ?? null,
                    'teacher_comments' => $data['teacher_comments'] ?? null,
                    'status' => $data['action'] === 'send' ? 'Sent' : 'Draft'
                ]
            );

            return response()->json(['success' => true, 'message' => 'Report saved as ' . $report->status]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['success' => false, 'message' => 'Validation error: ' . $ve->getMessage()], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("DailyDutyReport Save Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'start_date' => 'required|date',
            'weeks' => 'required|array|min:1',
            'weeks.*.teachers' => 'required|array|min:1',
            'weeks.*.teachers.*' => 'exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School ID not found in session. Please login again.'], 401);
        }

        $school = \App\Models\School::find($schoolID);
        $activeTerm = Term::where('schoolID', $schoolID)->where('status', 'Active')->first();
        $activeYear = AcademicYear::where('schoolID', $schoolID)->where('status', 'Active')->first();

        // Fetch holidays to skip them
        $holidays = \App\Models\Holiday::where('schoolID', $schoolID)->get();

        $initialDate = Carbon::parse($request->start_date);
        
        DB::beginTransaction();

        try {
            $currentStartDate = $initialDate->copy();

            foreach ($request->weeks as $weekIndex => $weekData) {
                if (!isset($weekData['teachers']) || empty($weekData['teachers'])) {
                    continue; 
                }

                // Skip holidays logic
                $attempts = 0;
                while ($this->isHolidayWeek($currentStartDate, $holidays) && $attempts < 52) {
                    $currentStartDate->addWeek();
                    $attempts++;
                }

                $weekEndDate = $currentStartDate->copy()->addDays(6);

                // --- UPDATE/EDIT LOGIC ---
                // If records already exist for this exact timeframe, delete them before re-creating
                // This allows the "Edit" functionality to replace teachers for that week.
                TeacherDuty::where('schoolID', $schoolID)
                    ->whereDate('start_date', $currentStartDate->format('Y-m-d'))
                    ->whereDate('end_date', $weekEndDate->format('Y-m-d'))
                    ->delete();

                foreach ($weekData['teachers'] as $teacherID) {
                    if (!$teacherID) continue;

                    TeacherDuty::create([
                        'schoolID' => $schoolID,
                        'teacherID' => $teacherID,
                        'termID' => $activeTerm ? $activeTerm->termID : null,
                        'academic_yearID' => $activeYear ? $activeYear->academic_yearID : null,
                        'start_date' => $currentStartDate->format('Y-m-d'),
                        'end_date' => $weekEndDate->format('Y-m-d'),
                    ]);

                    // Send SMS (tolerant of failure)
                    try {
                        $teacher = Teacher::find($teacherID);
                        if ($teacher && $teacher->phone_number) {
                            $schoolName = $school ? $school->school_name : 'ShuleLink';
                            $msg = "Habari {$teacher->first_name}, umechaguliwa kuwa mwalimu wa zamu kuanzia " . 
                                   $currentStartDate->format('d/m/Y') . " hadi " . $weekEndDate->format('d/m/Y') . ". {$schoolName}";
                            
                            $this->smsService->sendSms($teacher->phone_number, $msg);
                        }
                    } catch (\Exception $smsEx) {
                        \Illuminate\Support\Facades\Log::error("TeacherDuty SMS Error: " . $smsEx->getMessage());
                    }
                }

                $currentStartDate->addWeek();
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Duty roster generated/updated successfully. Teachers have been notified via SMS.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("TeacherDuty Store Error: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Critical error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            TeacherDuty::where('schoolID', $schoolID)
                ->whereDate('start_date', $request->start_date)
                ->whereDate('end_date', $request->end_date)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Duties for this week deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to check if a week is a holiday week
     */
    private function isHolidayWeek($date, $holidays)
    {
        $weekStart = $date->copy()->startOfDay();
        $weekEnd = $date->copy()->addDays(6)->endOfDay();

        foreach ($holidays as $holiday) {
            $hStart = Carbon::parse($holiday->start_date)->startOfDay();
            $hEnd = Carbon::parse($holiday->end_date)->endOfDay();

            // If the holiday covers the START of the week or significantly overlaps
            // More aggressive: if ANY holiday day overlaps with the week assignment
            if ($weekStart->between($hStart, $hEnd) || $weekEnd->between($hStart, $hEnd) || ($hStart->between($weekStart, $weekEnd))) {
                return true;
            }
        }
        return false;
    }

    public function report()
    {
        $schoolID = Session::get('schoolID');
        
        // Fetch all duties
        $duties = TeacherDuty::where('schoolID', $schoolID)
            ->with(['teacher', 'term'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('Admin.teacher_duties.report', compact('duties'));
    }

    public function exportDailyReportPdf(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $date = $request->get('date');
        $teacherID = Session::get('teacherID');

        if (!$date) return redirect()->back();

        $report = \App\Models\DailyDutyReport::where('schoolID', $schoolID)
            ->whereDate('report_date', $date)
            ->first();

        if (!$report) return redirect()->back()->with('error', 'Report not found.');

        $school = \App\Models\School::find($schoolID);
        $teacher = \App\Models\Teacher::find($report->teacherID);
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->orderBy('class_name', 'asc')->get();

        $dompdf = new \Dompdf\Dompdf();
        $html = view('Teacher.duty_report_pdf', compact('report', 'school', 'teacher', 'classes'))->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Daily_Duty_Report_' . $date . '.pdf';
        return response()->streamDownload(function() use ($dompdf) {
            echo $dompdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }
}
