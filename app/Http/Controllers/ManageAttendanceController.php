<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Combie;
use App\Models\Attendance;
use App\Models\School;
use App\Models\StudentFingerprintAttendance;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;

class ManageAttendanceController extends Controller
{
    public function manageAttendance(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');

        if (!$userType || !$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Check if coordinator view
        $isCoordinatorView = $request->input('coordinator') === 'true';
        $classIDParam = $request->input('classID');
        $isCoordinatorView = $isCoordinatorView && $classIDParam;
        
        $selectedClass = null;
        $coordinatorSubclasses = collect();
        
        if ($isCoordinatorView && $classIDParam) {
            // Decrypt classID if encrypted
            try {
                $decryptedClassID = Crypt::decrypt($classIDParam);
            } catch (\Exception $e) {
                $decryptedClassID = $classIDParam;
            }
            
            // Verify teacher is coordinator for this class
            $selectedClass = ClassModel::where('classID', $decryptedClassID)
                ->where('teacherID', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();
            
            if (!$selectedClass) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }
            
            // Get subclasses for this main class
            $coordinatorSubclasses = Subclass::where('classID', $decryptedClassID)
                ->where('status', 'Active')
                ->with('class')
                ->get()
                ->map(function($subclass) {
                    $subclassName = trim($subclass->subclass_name);
                    $displayName = empty($subclassName) 
                        ? $subclass->class->class_name 
                        : $subclass->class->class_name . ' - ' . $subclassName;
                    
                    return (object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $displayName,
                        'class_name' => $subclass->class->class_name,
                    ];
                });
        }

        // Get all subclasses for dropdown with display names (for non-coordinator view)
        $allSubclasses = Subclass::with('class')
            ->whereHas('class', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->get()
            ->groupBy('classID');
        
        // Filter and format subclasses
        $subclasses = collect();
        foreach ($allSubclasses as $classID => $classSubclasses) {
            // If class has only one subclass and it's default (empty name), include it but show only class name
            if ($classSubclasses->count() === 1) {
                $subclass = $classSubclasses->first();
                if (trim($subclass->subclass_name) === '') {
                    // Default subclass - show only class name
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $subclass->class->class_name,
                        'class_name' => $subclass->class->class_name,
                    ]);
                } else {
                    // Single subclass with name
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $subclass->class->class_name . ' ' . $subclass->subclass_name,
                        'class_name' => $subclass->class->class_name,
                    ]);
                }
            } else {
                // Multiple subclasses - show all with class_name + subclass_name
                foreach ($classSubclasses as $subclass) {
                    $subclassName = trim($subclass->subclass_name);
                    $displayName = empty($subclassName) 
                        ? $subclass->class->class_name 
                        : $subclass->class->class_name . ' ' . $subclassName;
                    
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $displayName,
                        'class_name' => $subclass->class->class_name,
                    ]);
                }
            }
        }
        
        // Sort by class_name then subclass_name
        $subclasses = $subclasses->sortBy(function($item) {
            return $item->class_name . ' ' . $item->subclass_name;
        })->values();

        // Get school details for exports
        $school = School::where('schoolID', $schoolID)->first();
        $school_details = (object)[
            'school_name' => $school->school_name ?? 'School',
            'school_logo' => $school->school_logo ?? null
        ];

        return view('Admin.manage_attendance', compact('subclasses', 'school_details', 'isCoordinatorView', 'selectedClass', 'coordinatorSubclasses', 'classIDParam'));
    }

    public function searchAttendance(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $searchType = $request->input('search_type'); // date, year, month, week, day, subclass, all_school
            $subclassID = $request->input('subclassID');
            $classID = $request->input('classID');
            $isCoordinatorView = $request->input('coordinator') === 'true';
            $date = $request->input('date');
            $year = $request->input('year');
            $month = $request->input('month');
            $week = $request->input('week');
            $day = $request->input('day');
            $studentID = $request->input('studentID');

            $query = Attendance::join('students', 'attendances.studentID', '=', 'students.studentID')
                ->join('subclasses', 'attendances.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('attendances.schoolID', $schoolID)
                ->select(
                    'attendances.*',
                    'students.first_name',
                    'students.middle_name',
                    'students.last_name',
                    'students.admission_number',
                    'students.photo',
                    'students.gender',
                    'subclasses.subclass_name',
                    'classes.class_name'
                );
            
            // Coordinator view: filter by main class
            if ($isCoordinatorView && $classID) {
                try {
                    $decryptedClassID = Crypt::decrypt($classID);
                } catch (\Exception $e) {
                    $decryptedClassID = $classID;
                }
                $query->where('subclasses.classID', $decryptedClassID);
                
                // If subclass is selected, filter by that subclass
                if ($subclassID) {
                    $query->where('attendances.subclassID', $subclassID);
                }
            } elseif ($subclassID) {
                // Regular subclass filter
                $query->where('attendances.subclassID', $subclassID);
            }

            // Apply search filters
            switch ($searchType) {
                case 'date':
                    if ($date) {
                        $query->whereDate('attendances.attendance_date', $date);
                    }
                    break;
                case 'year':
                    if ($year) {
                        $query->whereYear('attendances.attendance_date', $year);
                    }
                    break;
                case 'month':
                    if ($year && $month) {
                        $query->whereYear('attendances.attendance_date', $year)
                              ->whereMonth('attendances.attendance_date', $month);
                    }
                    break;
                case 'week':
                    if ($week && $year) {
                        try {
                            $startDate = Carbon::create($year, 1, 1)->setISODate($year, $week, 1)->startOfWeek();
                            $endDate = $startDate->copy()->endOfWeek();
                            $query->whereBetween('attendances.attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                        } catch (\Exception $e) {
                            // Invalid week number
                        }
                    }
                    break;
                case 'day':
                    if ($day) {
                        $query->whereDay('attendances.attendance_date', $day);
                    }
                    break;
                case 'subclass':
                    if ($subclassID) {
                        $query->where('attendances.subclassID', $subclassID);
                    }
                    break;
                case 'all_school':
                    // No additional filter, already filtered by schoolID
                    break;
            }

            // Filter by student if provided
            if ($studentID) {
                $query->where('attendances.studentID', $studentID);
            }

            $attendances = $query->orderBy('attendances.attendance_date', 'desc')
                ->orderBy('classes.class_name')
                ->orderBy('subclasses.subclass_name')
                ->orderBy('students.first_name')
                ->get();
            // Calculate statistics
            $stats = $this->calculateAttendanceStats($attendances, $searchType, $date, $year, $month, $week, $subclassID, $schoolID);

            // Get attendance by class with student details
            $attendanceByClass = $this->getAttendanceByClass($searchType, $date, $year, $month, $week, $subclassID, $schoolID, $classID, $isCoordinatorView);

            return response()->json([
                'success' => true,
                'attendances' => $attendances,
                'stats' => $stats,
                'attendance_by_class' => $attendanceByClass
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudentAttendanceDetails($studentID, Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $period = $request->input('period', 'month'); // month, year
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));
            $week = $request->input('week');
            $date = $request->input('date');
            $searchType = $request->input('search_type', 'month'); // month, year, week, date

            $query = Attendance::where('studentID', $studentID)
                ->where('schoolID', $schoolID);

            // Apply filters based on search type
            if ($searchType == 'year' && $year) {
                $query->whereYear('attendance_date', $year);
            } elseif ($searchType == 'month' && $year && $month) {
                $query->whereYear('attendance_date', $year)
                      ->whereMonth('attendance_date', $month);
            } elseif ($searchType == 'date' && $date) {
                $query->whereDate('attendance_date', $date);
            } else {
                // Default to month if period is specified
                if ($period == 'year') {
                    $query->whereYear('attendance_date', $year);
                } else {
                    $query->whereYear('attendance_date', $year)
                          ->whereMonth('attendance_date', $month);
                }
            }

            $attendances = $query->orderBy('attendance_date', 'asc')->get();

            $student = Student::find($studentID);

            // Calculate statistics
            $totalDays = $attendances->count();
            $present = $attendances->where('status', 'Present')->count();
            $absent = $attendances->where('status', 'Absent')->count();
            $sick = $attendances->where('status', 'Sick')->count();
            $excused = $attendances->where('status', 'Excused')->count();

            // Prepare data for line chart
            $chartData = $this->prepareChartData($attendances, $searchType == 'year' ? 'year' : 'month');

            return response()->json([
                'success' => true,
                'student' => $student,
                'attendances' => $attendances,
                'stats' => [
                    'total_days' => $totalDays,
                    'present' => $present,
                    'absent' => $absent,
                    'sick' => $sick,
                    'excused' => $excused
                ],
                'chart_data' => $chartData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateAttendanceStats($attendances, $searchType, $date = null, $year = null, $month = null, $week = null, $subclassID = null, $schoolID = null)
    {
        // If searching by date, month, week, or year for all school, get stats by subclass
        if (in_array($searchType, ['date', 'month', 'week', 'year']) && !$subclassID && $schoolID) {
            $query = DB::table('attendances')
                ->join('subclasses', 'attendances.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('attendances.schoolID', $schoolID);

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $query->whereDate('attendances.attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $query->whereYear('attendances.attendance_date', $year)
                      ->whereMonth('attendances.attendance_date', $month);
            } elseif ($searchType == 'week' && $week && $year) {
                try {
                    $startDate = Carbon::create($year, 1, 1)->setISODate($year, $week, 1)->startOfWeek();
                    $endDate = $startDate->copy()->endOfWeek();
                    $query->whereBetween('attendances.attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                } catch (\Exception $e) {
                    // Invalid week
                }
            } elseif ($searchType == 'year' && $year) {
                $query->whereYear('attendances.attendance_date', $year);
            }

            $statsBySubclass = $query->select(
                    'subclasses.subclassID',
                    'subclasses.subclass_name',
                    'classes.class_name',
                    DB::raw('COUNT(DISTINCT attendances.studentID) as total_students'),
                    DB::raw('SUM(CASE WHEN attendances.status = "Present" THEN 1 ELSE 0 END) as present'),
                    DB::raw('SUM(CASE WHEN attendances.status = "Absent" THEN 1 ELSE 0 END) as absent'),
                    DB::raw('SUM(CASE WHEN attendances.status = "Sick" THEN 1 ELSE 0 END) as sick'),
                    DB::raw('SUM(CASE WHEN attendances.status = "Excused" THEN 1 ELSE 0 END) as excused')
                )
                ->groupBy('subclasses.subclassID', 'subclasses.subclass_name', 'classes.class_name')
                ->orderBy('classes.class_name')
                ->orderBy('subclasses.subclass_name')
                ->get()
                ->map(function($item) {
                    $subclassName = trim($item->subclass_name);
                    $item->display_name = empty($subclassName) 
                        ? $item->class_name 
                        : $item->class_name . ' ' . $subclassName;
                    return $item;
                });

            // Gender statistics
            $genderQuery = DB::table('attendances')
                ->join('students', 'attendances.studentID', '=', 'students.studentID')
                ->where('attendances.schoolID', $schoolID)
                ->where('attendances.status', 'Present');

            if ($searchType == 'date' && $date) {
                $genderQuery->whereDate('attendances.attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $genderQuery->whereYear('attendances.attendance_date', $year)
                           ->whereMonth('attendances.attendance_date', $month);
            } elseif ($searchType == 'week' && $week && $year) {
                try {
                    $startDate = Carbon::create($year, 1, 1)->setISODate($year, $week, 1)->startOfWeek();
                    $endDate = $startDate->copy()->endOfWeek();
                    $genderQuery->whereBetween('attendances.attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                } catch (\Exception $e) {
                    // Invalid week
                }
            } elseif ($searchType == 'year' && $year) {
                $genderQuery->whereYear('attendances.attendance_date', $year);
            }

            $genderStats = $genderQuery->select(
                    DB::raw('COUNT(DISTINCT CASE WHEN students.gender = "Male" THEN attendances.studentID END) as male_present'),
                    DB::raw('COUNT(DISTINCT CASE WHEN students.gender = "Female" THEN attendances.studentID END) as female_present')
                )
                ->first();

            return [
                'by_subclass' => $statsBySubclass,
                'gender_stats' => $genderStats,
                'total_present' => $attendances->where('status', 'Present')->count(),
                'total_absent' => $attendances->where('status', 'Absent')->count(),
                'total_sick' => $attendances->where('status', 'Sick')->count(),
                'total_excused' => $attendances->where('status', 'Excused')->count(),
                'total_students' => $attendances->unique('studentID')->count()
            ];
        }

        // General statistics
        return [
            'total_present' => $attendances->where('status', 'Present')->count(),
            'total_absent' => $attendances->where('status', 'Absent')->count(),
            'total_sick' => $attendances->where('status', 'Sick')->count(),
            'total_excused' => $attendances->where('status', 'Excused')->count(),
            'total_students' => $attendances->unique('studentID')->count()
        ];
    }

    private function getAttendanceByClass($searchType, $date = null, $year = null, $month = null, $week = null, $subclassID = null, $schoolID = null, $classID = null, $isCoordinatorView = false)
    {
        if (!in_array($searchType, ['date', 'month', 'year']) || !$schoolID) {
            return [];
        }

        // Get subclasses query
        $subclassesQuery = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('classes.schoolID', $schoolID);

        // Coordinator view: filter by main class
        if ($isCoordinatorView && $classID) {
            try {
                $decryptedClassID = Crypt::decrypt($classID);
            } catch (\Exception $e) {
                $decryptedClassID = $classID;
            }
            $subclassesQuery->where('subclasses.classID', $decryptedClassID);
            
            // If subclass is selected, filter by that subclass
            if ($subclassID) {
                $subclassesQuery->where('subclasses.subclassID', $subclassID);
            }
        } elseif ($subclassID) {
            // Regular subclass filter
            $subclassesQuery->where('subclasses.subclassID', $subclassID);
        }

        $subclasses = $subclassesQuery->select(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'classes.class_name',
                'classes.classID'
            )
            ->orderBy('classes.class_name')
            ->orderBy('subclasses.subclass_name')
            ->get();

        $result = [];
        foreach ($subclasses as $subclass) {
            // Get total students in this subclass
            $totalStudents = DB::table('students')
                ->where('subclassID', $subclass->subclassID)
                ->where('schoolID', $schoolID)
                ->where('status', '!=', 'Transferred')
                ->count();

            // Check if attendance was collected for this subclass in the period
            $attendanceCheckQuery = DB::table('attendances')
                ->where('schoolID', $schoolID)
                ->where('subclassID', $subclass->subclassID);

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $attendanceCheckQuery->whereDate('attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $attendanceCheckQuery->whereYear('attendance_date', $year)
                                  ->whereMonth('attendance_date', $month);
            } elseif ($searchType == 'year' && $year) {
                $attendanceCheckQuery->whereYear('attendance_date', $year);
            }

            $hasAttendance = $attendanceCheckQuery->exists();

            if (!$hasAttendance) {
                // No attendance collected
                // Calculate display name
                $subclassName = trim($subclass->subclass_name);
                $displayName = empty($subclassName) 
                    ? $subclass->class_name 
                    : $subclass->class_name . ' ' . $subclassName;
                
                $result[] = [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'class_name' => $subclass->class_name,
                    'display_name' => $displayName,
                    'total_students' => $totalStudents,
                    'has_attendance' => false,
                    'present' => 0,
                    'absent' => 0,
                    'sick' => 0,
                    'excused' => 0,
                    'present_percentage' => 0,
                    'absent_percentage' => 0,
                    'sick_percentage' => 0,
                    'excused_percentage' => 0,
                    'students' => []
                ];
                continue;
            }

            // Get attendance stats for this subclass
            $attendanceStatsQuery = DB::table('attendances')
                ->where('schoolID', $schoolID)
                ->where('subclassID', $subclass->subclassID);

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $attendanceStatsQuery->whereDate('attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $attendanceStatsQuery->whereYear('attendance_date', $year)
                                  ->whereMonth('attendance_date', $month);
            } elseif ($searchType == 'year' && $year) {
                $attendanceStatsQuery->whereYear('attendance_date', $year);
            }

            $attendanceStats = $attendanceStatsQuery
                ->select(
                    DB::raw('COUNT(DISTINCT studentID) as total_students_with_attendance'),
                    DB::raw('SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present'),
                    DB::raw('SUM(CASE WHEN status = "Absent" THEN 1 ELSE 0 END) as absent'),
                    DB::raw('SUM(CASE WHEN status = "Sick" THEN 1 ELSE 0 END) as sick'),
                    DB::raw('SUM(CASE WHEN status = "Excused" THEN 1 ELSE 0 END) as excused')
                )
                ->first();

            $present = $attendanceStats->present ?? 0;
            $absent = $attendanceStats->absent ?? 0;
            $sick = $attendanceStats->sick ?? 0;
            $excused = $attendanceStats->excused ?? 0;
            $totalRecords = $present + $absent + $sick + $excused;

            // Calculate percentages for month and year
            $presentPercentage = 0;
            $absentPercentage = 0;
            $sickPercentage = 0;
            $excusedPercentage = 0;

            if (in_array($searchType, ['month', 'year']) && $totalStudents > 0) {
                // For month/year, calculate percentage of students
                $presentPercentage = $totalRecords > 0 ? round(($present / $totalRecords) * 100, 2) : 0;
                $absentPercentage = $totalRecords > 0 ? round(($absent / $totalRecords) * 100, 2) : 0;
                $sickPercentage = $totalRecords > 0 ? round(($sick / $totalRecords) * 100, 2) : 0;
                $excusedPercentage = $totalRecords > 0 ? round(($excused / $totalRecords) * 100, 2) : 0;
            }

            // Get students with their attendance stats
            $studentsQuery = DB::table('students')
                ->leftJoin('attendances', function($join) use ($schoolID, $subclass, $searchType, $date, $year, $month, $week) {
                    $join->on('students.studentID', '=', 'attendances.studentID')
                         ->where('attendances.schoolID', $schoolID)
                         ->where('attendances.subclassID', $subclass->subclassID);

                    if ($searchType == 'date' && $date) {
                        $join->whereDate('attendances.attendance_date', $date);
                    } elseif ($searchType == 'month' && $year && $month) {
                        $join->whereYear('attendances.attendance_date', $year)
                            ->whereMonth('attendances.attendance_date', $month);
                    } elseif ($searchType == 'year' && $year) {
                        $join->whereYear('attendances.attendance_date', $year);
                    }
                })
                ->where('students.subclassID', $subclass->subclassID)
                ->where('students.schoolID', $schoolID)
                ->where('students.status', '!=', 'Transferred')
                ->select(
                    'students.studentID',
                    'students.first_name',
                    'students.middle_name',
                    'students.last_name',
                    'students.admission_number',
                    'students.gender',
                    DB::raw('COALESCE(SUM(CASE WHEN attendances.status = "Present" THEN 1 ELSE 0 END), 0) as days_present'),
                    DB::raw('COALESCE(SUM(CASE WHEN attendances.status = "Absent" THEN 1 ELSE 0 END), 0) as days_absent'),
                    DB::raw('COALESCE(SUM(CASE WHEN attendances.status = "Sick" THEN 1 ELSE 0 END), 0) as days_sick'),
                    DB::raw('COALESCE(SUM(CASE WHEN attendances.status = "Excused" THEN 1 ELSE 0 END), 0) as days_excused')
                )
                ->groupBy('students.studentID', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_number', 'students.gender')
                ->orderBy('students.first_name')
                ->get();

            // Calculate display name
            $subclassName = trim($subclass->subclass_name);
            $displayName = empty($subclassName) 
                ? $subclass->class_name 
                : $subclass->class_name . ' ' . $subclassName;
            
            $result[] = [
                'subclassID' => $subclass->subclassID,
                'subclass_name' => $subclass->subclass_name,
                'class_name' => $subclass->class_name,
                'display_name' => $displayName,
                'total_students' => $totalStudents,
                'has_attendance' => true,
                'present' => $present,
                'absent' => $absent,
                'sick' => $sick,
                'excused' => $excused,
                'present_percentage' => $presentPercentage,
                'absent_percentage' => $absentPercentage,
                'sick_percentage' => $sickPercentage,
                'excused_percentage' => $excusedPercentage,
                'students' => $studentsQuery
            ];
        }

        return $result;
    }

    private function prepareChartData($attendances, $period)
    {
        if ($period == 'year') {
            // Group by month
            return $attendances->groupBy(function($attendance) {
                return Carbon::parse($attendance->attendance_date)->format('M');
            })->map(function($monthAttendances) {
                return [
                    'present' => $monthAttendances->where('status', 'Present')->count(),
                    'absent' => $monthAttendances->where('status', 'Absent')->count(),
                    'sick' => $monthAttendances->where('status', 'Sick')->count(),
                    'excused' => $monthAttendances->where('status', 'Excused')->count()
                ];
            });
        } else {
            // Group by day
            return $attendances->groupBy(function($attendance) {
                return Carbon::parse($attendance->attendance_date)->format('Y-m-d');
            })->map(function($dayAttendances) {
                return [
                    'present' => $dayAttendances->where('status', 'Present')->count(),
                    'absent' => $dayAttendances->where('status', 'Absent')->count(),
                    'sick' => $dayAttendances->where('status', 'Sick')->count(),
                    'excused' => $dayAttendances->where('status', 'Excused')->count()
                ];
            });
        }
    }

    public function searchFingerprintAttendance(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $searchType = $request->input('search_type'); // date, year, month
            $date = $request->input('date');
            $year = $request->input('year');
            $month = $request->input('month');
            $classID = $request->input('classID');
            $subclassID = $request->input('subclassID');
            $isCoordinatorView = $request->input('coordinator') === 'true';

            // Get attendance by class with student details
            $attendanceByClass = $this->getFingerprintAttendanceByClass($searchType, $date, $year, $month, $schoolID, $classID, $subclassID, $isCoordinatorView);

            return response()->json([
                'success' => true,
                'attendance_by_class' => $attendanceByClass
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getFingerprintAttendanceByClass($searchType, $date = null, $year = null, $month = null, $schoolID = null, $classID = null, $subclassID = null, $isCoordinatorView = false)
    {
        if (!in_array($searchType, ['date', 'month', 'year']) || !$schoolID) {
            return [];
        }

        // Get subclasses query
        $subclassesQuery = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('classes.schoolID', $schoolID);
        
        // Coordinator view: filter by main class
        if ($isCoordinatorView && $classID) {
            try {
                $decryptedClassID = Crypt::decrypt($classID);
            } catch (\Exception $e) {
                $decryptedClassID = $classID;
            }
            $subclassesQuery->where('subclasses.classID', $decryptedClassID);
            
            // If subclass is selected, filter by that subclass
            if ($subclassID) {
                $subclassesQuery->where('subclasses.subclassID', $subclassID);
            }
        }
        
        $subclasses = $subclassesQuery->select(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'classes.class_name',
                'classes.classID'
            )
            ->orderBy('classes.class_name')
            ->orderBy('subclasses.subclass_name')
            ->get();

        $result = [];
        foreach ($subclasses as $subclass) {
            // Get total students in this subclass
            $totalStudents = DB::table('students')
                ->where('subclassID', $subclass->subclassID)
                ->where('schoolID', $schoolID)
                ->where('status', '!=', 'Transferred')
                ->count();

            // Check if fingerprint attendance was collected for this subclass in the period
            $attendanceCheckQuery = DB::table('student_fingerprint_attendance')
                ->join('students', 'student_fingerprint_attendance.studentID', '=', 'students.studentID')
                ->where('students.schoolID', $schoolID)
                ->where('students.subclassID', $subclass->subclassID)
                ->where('students.status', '!=', 'Transferred');

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $attendanceCheckQuery->whereDate('student_fingerprint_attendance.attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $attendanceCheckQuery->whereYear('student_fingerprint_attendance.attendance_date', $year)
                                  ->whereMonth('student_fingerprint_attendance.attendance_date', $month);
            } elseif ($searchType == 'year' && $year) {
                $attendanceCheckQuery->whereYear('student_fingerprint_attendance.attendance_date', $year);
            }

            $hasAttendance = $attendanceCheckQuery->exists();

            if (!$hasAttendance) {
                // No attendance collected
                // Calculate display name
                $subclassName = trim($subclass->subclass_name);
                $displayName = empty($subclassName) 
                    ? $subclass->class_name 
                    : $subclass->class_name . ' ' . $subclassName;
                
                $result[] = [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'class_name' => $subclass->class_name,
                    'display_name' => $displayName,
                    'total_students' => $totalStudents,
                    'has_attendance' => false,
                    'present' => 0,
                    'absent' => 0,
                    'present_percentage' => 0,
                    'absent_percentage' => 0,
                    'students' => []
                ];
                continue;
            }

            // Get students with their fingerprint attendance stats
            if ($searchType == 'date' && $date) {
                // For date view, get actual check in/out times
                $studentsQuery = DB::table('students')
                    ->leftJoin('student_fingerprint_attendance', function($join) use ($date) {
                        $join->on('students.studentID', '=', 'student_fingerprint_attendance.studentID')
                             ->whereDate('student_fingerprint_attendance.attendance_date', $date);
                    })
                    ->leftJoin('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                    ->where('students.subclassID', $subclass->subclassID)
                    ->where('students.schoolID', $schoolID)
                    ->where('students.status', '!=', 'Transferred')
                    ->select(
                        'students.studentID',
                        'students.first_name',
                        'students.middle_name',
                        'students.last_name',
                        'students.admission_number',
                        'students.gender',
                        'subclasses.subclass_name',
                        DB::raw('MAX(student_fingerprint_attendance.check_in_time) as check_in_time'),
                        DB::raw('MAX(student_fingerprint_attendance.check_out_time) as check_out_time'),
                        DB::raw('COUNT(DISTINCT student_fingerprint_attendance.attendance_date) as days_present'),
                        DB::raw('0 as days_absent'),
                        DB::raw('0 as days_late'),
                        DB::raw('0 as days_excused')
                    )
                    ->groupBy('students.studentID', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_number', 'students.gender', 'subclasses.subclass_name')
                    ->orderBy('students.first_name')
                    ->get();
            } else {
                // For month/year view, get aggregated stats
                $studentsQuery = DB::table('students')
                    ->leftJoin('student_fingerprint_attendance', function($join) use ($searchType, $date, $year, $month) {
                        $join->on('students.studentID', '=', 'student_fingerprint_attendance.studentID');

                        if ($searchType == 'month' && $year && $month) {
                            $join->whereYear('student_fingerprint_attendance.attendance_date', $year)
                                ->whereMonth('student_fingerprint_attendance.attendance_date', $month);
                        } elseif ($searchType == 'year' && $year) {
                            $join->whereYear('student_fingerprint_attendance.attendance_date', $year);
                        }
                    })
                    ->leftJoin('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                    ->where('students.subclassID', $subclass->subclassID)
                    ->where('students.schoolID', $schoolID)
                    ->where('students.status', '!=', 'Transferred')
                    ->select(
                        'students.studentID',
                        'students.first_name',
                        'students.middle_name',
                        'students.last_name',
                        'students.admission_number',
                        'students.gender',
                        'subclasses.subclass_name',
                        DB::raw('COUNT(DISTINCT student_fingerprint_attendance.attendance_date) as days_present'),
                        DB::raw('0 as days_absent'),
                        DB::raw('0 as days_late'),
                        DB::raw('0 as days_excused'),
                        DB::raw('NULL as check_in_time'),
                        DB::raw('NULL as check_out_time')
                    )
                    ->groupBy('students.studentID', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_number', 'students.gender', 'subclasses.subclass_name')
                    ->orderBy('students.first_name')
                    ->get();
            }

            // Calculate stats
            $totalPresentDays = $studentsQuery->sum('days_present');
            $totalStudentsWithAttendance = $studentsQuery->where('days_present', '>', 0)->count();
            
            // For month/year, calculate working days
            $workingDays = 0;
            if ($searchType == 'month' && $year && $month) {
                $startDate = Carbon::create($year, $month, 1);
                $endDate = $startDate->copy()->endOfMonth();
                if ($startDate->isCurrentMonth()) {
                    $endDate = Carbon::today();
                }
                $workingDays = $this->calculateWorkingDays($startDate, $endDate);
            } elseif ($searchType == 'year' && $year) {
                $startDate = Carbon::create($year, 1, 1);
                $endDate = Carbon::create($year, 12, 31);
                if ($startDate->year == Carbon::now()->year) {
                    $endDate = Carbon::today();
                }
                $workingDays = $this->calculateWorkingDays($startDate, $endDate);
            } else {
                $workingDays = 1; // For date search
            }

            // Calculate absent days for each student
            $studentsWithStats = $studentsQuery->map(function($student) use ($workingDays, $searchType, $subclass) {
                $daysAbsent = max(0, $workingDays - $student->days_present);
                $result = [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'gender' => $student->gender,
                    'subclass_name' => $student->subclass_name ?? $subclass->subclass_name,
                    'days_present' => $student->days_present,
                    'days_absent' => $daysAbsent,
                    'days_late' => 0,
                    'days_excused' => 0
                ];
                
                // Add check in/out times for date view
                if ($searchType == 'date') {
                    $result['check_in_time'] = $student->check_in_time ? Carbon::parse($student->check_in_time)->format('H:i:s') : null;
                    $result['check_out_time'] = $student->check_out_time ? Carbon::parse($student->check_out_time)->format('H:i:s') : null;
                }
                
                return $result;
            });

            $totalAbsentDays = $studentsWithStats->sum('days_absent');
            $totalRecords = $totalPresentDays + $totalAbsentDays;

            // Calculate percentages for month and year
            $presentPercentage = 0;
            $absentPercentage = 0;

            if (in_array($searchType, ['month', 'year']) && $totalRecords > 0) {
                $presentPercentage = round(($totalPresentDays / $totalRecords) * 100, 2);
                $absentPercentage = round(($totalAbsentDays / $totalRecords) * 100, 2);
            }

            // Calculate display name
            $subclassName = trim($subclass->subclass_name);
            $displayName = empty($subclassName) 
                ? $subclass->class_name 
                : $subclass->class_name . ' ' . $subclassName;
            
            $result[] = [
                'subclassID' => $subclass->subclassID,
                'subclass_name' => $subclass->subclass_name,
                'class_name' => $subclass->class_name,
                'display_name' => $displayName,
                'total_students' => $totalStudents,
                'has_attendance' => true,
                'present' => $totalPresentDays,
                'absent' => $totalAbsentDays,
                'late' => 0,
                'excused' => 0,
                'present_percentage' => $presentPercentage,
                'absent_percentage' => $absentPercentage,
                'late_percentage' => 0,
                'excused_percentage' => 0,
                'students' => $studentsWithStats->values()
            ];
        }

        return $result;
    }

    private function calculateWorkingDays($startDate, $endDate)
    {
        $workingDays = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($currentDate->dayOfWeek != Carbon::SATURDAY && $currentDate->dayOfWeek != Carbon::SUNDAY) {
                $workingDays++;
            }
            $currentDate->addDay();
        }
        
        return $workingDays;
    }

    public function getStudentFingerprintAttendanceDetails($studentID, Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $searchType = $request->input('search_type', 'month'); // month, year, date
            $date = $request->input('date');
            $year = $request->input('year');
            $month = $request->input('month');

            // Get student info
            $student = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            // Build query for fingerprint attendance
            $query = StudentFingerprintAttendance::where('studentID', $studentID);

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $query->whereDate('attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $query->whereYear('attendance_date', $year)
                      ->whereMonth('attendance_date', $month);
            } elseif ($searchType == 'year' && $year) {
                $query->whereYear('attendance_date', $year);
            }

            // Get all attendance records
            $attendanceRecords = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->get();

            // Calculate statistics
            $totalRecords = $attendanceRecords->count();
            $daysPresent = $attendanceRecords->groupBy(function($record) {
                return $record->attendance_date->format('Y-m-d');
            })->count();

            // Calculate working days
            $workingDays = 0;
            if ($searchType == 'month' && $year && $month) {
                $startDate = Carbon::create($year, $month, 1);
                $endDate = $startDate->copy()->endOfMonth();
                if ($startDate->isCurrentMonth()) {
                    $endDate = Carbon::today();
                }
                $workingDays = $this->calculateWorkingDays($startDate, $endDate);
            } elseif ($searchType == 'year' && $year) {
                $startDate = Carbon::create($year, 1, 1);
                $endDate = Carbon::create($year, 12, 31);
                if ($startDate->year == Carbon::now()->year) {
                    $endDate = Carbon::today();
                }
                $workingDays = $this->calculateWorkingDays($startDate, $endDate);
            } else {
                $workingDays = 1; // For date search
            }

            $daysAbsent = max(0, $workingDays - $daysPresent);

            // Format attendance records
            $formattedRecords = $attendanceRecords->map(function($record) {
                return [
                    'id' => $record->id,
                    'attendance_date' => $record->attendance_date->format('Y-m-d'),
                    'attendance_date_formatted' => $record->attendance_date->format('l, F j, Y'),
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('H:i:s') : null,
                    'status' => $record->status,
                    'verify_mode' => $record->verify_mode,
                    'device_ip' => $record->device_ip,
                ];
            });

            return response()->json([
                'success' => true,
                'student' => [
                    'studentID' => $student->studentID,
                    'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                    'admission_number' => $student->admission_number,
                ],
                'statistics' => [
                    'days_present' => $daysPresent,
                    'days_absent' => $daysAbsent,
                    'total_days' => $workingDays,
                    'total_records' => $totalRecords,
                ],
                'attendance_records' => $formattedRecords,
                'search_type' => $searchType,
                'search_period' => $searchType == 'month' && $year && $month 
                    ? Carbon::create($year, $month, 1)->format('F Y')
                    : ($searchType == 'year' && $year ? $year : ($searchType == 'date' && $date ? Carbon::parse($date)->format('F j, Y') : '')),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get terms for year (Admin)
    public function getTermsForYear(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $year = $request->input('year');

            if (!$year || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Year and school ID required']);
            }

            // Get distinct terms from examinations table for this year
            $terms = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->where('year', $year)
                ->whereNotNull('term')
                ->distinct()
                ->pluck('term')
                ->toArray();

            return response()->json(['success' => true, 'terms' => $terms]);
        } catch (\Exception $e) {
            \Log::error('Error getting terms for year: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get exams for year and term (Admin)
    public function getExamsForYearTerm(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $year = $request->input('year');
            $term = $request->input('term');

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

            $exams = DB::table('examinations')
                ->whereIn('examID', $examIDs)
                ->orderBy('start_date', 'desc')
                ->select('examID', 'exam_name', 'start_date', 'end_date')
                ->get();

            return response()->json(['success' => true, 'exams' => $exams]);
        } catch (\Exception $e) {
            \Log::error('Error getting exams for year and term: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get subclasses for class (Admin)
    public function getSubclassesForClass(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $classID = $request->input('classID');

            if (!$classID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Class ID and school ID required']);
            }

            $subclasses = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('subclasses.classID', $classID)
                ->where('classes.schoolID', $schoolID)
                ->select('subclasses.subclassID', 'subclasses.subclass_name')
                ->orderBy('subclasses.subclass_name')
                ->get();

            return response()->json(['success' => true, 'subclasses' => $subclasses]);
        } catch (\Exception $e) {
            \Log::error('Error getting subclasses for class: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get exam attendance data (Admin)
    public function getExamAttendanceData(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $examID = $request->input('examID');
            $mainClassID = $request->input('mainClassID');
            $subclassID = $request->input('subclassID');
            $subjectID = $request->input('subjectID');

            if (!$examID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Exam ID and school ID required']);
            }

            // Get subjects based on class selection (from class_subjects table)
            $classSubjects = collect();
            
            if ($subclassID) {
                // Get subjects for specific subclass
                $classSubjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                    ->where('class_subjects.subclassID', $subclassID)
                    ->where('class_subjects.status', 'Active')
                    ->where('school_subjects.status', 'Active')
                    ->where('classes.schoolID', $schoolID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code'
                    )
                    ->distinct()
                    ->orderBy('school_subjects.subject_name')
                    ->get();
            } elseif ($mainClassID) {
                // Get subjects for all subclasses in main class
                $classSubjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                    ->where('class_subjects.classID', $mainClassID)
                    ->where('class_subjects.status', 'Active')
                    ->where('school_subjects.status', 'Active')
                    ->where('classes.schoolID', $schoolID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code'
                    )
                    ->distinct()
                    ->orderBy('school_subjects.subject_name')
                    ->get();
            }

            // Get exam subject IDs from exam timetable
            $examSubjectIDs1 = DB::table('exam_timetable')
                ->where('examID', $examID)
                ->pluck('subjectID')
                ->toArray();

            $examSubjectIDs2 = DB::table('exam_timetables')
                ->where('examID', $examID)
                ->pluck('subjectID')
                ->toArray();

            $examSubjectIDs = array_unique(array_merge($examSubjectIDs1, $examSubjectIDs2));

            // Determine all exam subjects based on class selection
            if ($subclassID || $mainClassID) {
                // Filter class subjects to only include those in exam
                if (empty($examSubjectIDs) || $classSubjects->isEmpty()) {
                    $allExamSubjects = collect();
                } else {
                    $allExamSubjects = $classSubjects->filter(function($subject) use ($examSubjectIDs) {
                        return in_array($subject->subjectID, $examSubjectIDs);
                    })->values();
                }
            } else {
                // If no class selected, get all subjects from exam timetable
                $examSubjects1 = DB::table('exam_timetable')
                    ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetable.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->get();

                $examSubjects2 = DB::table('exam_timetables')
                    ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetables.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->get();

                $allExamSubjects = $examSubjects1->merge($examSubjects2)->unique('subjectID')->values();
            }

            $totalSubjects = $allExamSubjects->count();

            // Get all subclasses to filter
            $subclassQuery = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID);

            if ($subclassID) {
                $subclassQuery->where('subclasses.subclassID', $subclassID);
            } elseif ($mainClassID) {
                $subclassQuery->where('subclasses.classID', $mainClassID);
            }

            $subclassIDs = $subclassQuery->pluck('subclasses.subclassID')->toArray();

            if (empty($subclassIDs)) {
                return response()->json(['success' => true, 'data' => ['subclasses' => [], 'students' => []]]);
            }

            // Get all students in these subclasses
            $students = DB::table('students')
                ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->whereIn('students.subclassID', $subclassIDs)
                ->where('students.status', 'Active')
                ->select(
                    'students.studentID',
                    'students.first_name',
                    'students.last_name',
                    'students.middle_name',
                    'subclasses.subclass_name',
                    'classes.class_name',
                    'subclasses.subclassID'
                )
                ->get();

            // Get exam attendance records grouped by student and subject
            $attendanceRecords = DB::table('exam_attendance')
                ->where('examID', $examID)
                ->whereIn('studentID', $students->pluck('studentID')->toArray())
                ->select('studentID', 'subjectID', 'status')
                ->get()
                ->groupBy('studentID')
                ->map(function($records) {
                    return $records->keyBy('subjectID');
                })
                ->toArray();

            // Filter subjects if subjectID is provided
            $filteredSubjects = $allExamSubjects;
            if ($subjectID) {
                $filteredSubjects = $allExamSubjects->where('subjectID', $subjectID);
            }

            // Process students with attendance status and subjects information
            $allStudents = [];
            foreach ($students as $student) {
                $fullName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                
                // Format class display: Main Class - Subclass (or just Main Class if subclass is empty)
                $subclassName = trim($student->subclass_name ?? '');
                $classDisplay = $subclassName 
                    ? $student->class_name . ' - ' . $subclassName 
                    : $student->class_name;
                
                // Count subjects taken and get missed subjects
                $studentAttendance = $attendanceRecords[$student->studentID] ?? [];
                $subjectsTaken = 0;
                $missedSubjects = [];
                
                foreach ($filteredSubjects as $subject) {
                    $attendance = $studentAttendance[$subject->subjectID] ?? null;
                    if ($attendance && ($attendance->status ?? '') === 'Present') {
                        $subjectsTaken++;
                    } else {
                        $missedSubjects[] = [
                            'subjectID' => $subject->subjectID,
                            'subject_name' => $subject->subject_name,
                            'subject_code' => $subject->subject_code
                        ];
                    }
                }
                
                // Overall status: Present if taken at least one subject, Absent if none
                $overallStatus = $subjectsTaken > 0 ? 'Present' : 'Absent';
                
                $allStudents[] = [
                    'studentID' => $student->studentID,
                    'name' => $fullName,
                    'subclassID' => $student->subclassID,
                    'subclass_name' => $subclassName,
                    'class_name' => $student->class_name,
                    'class_display' => $classDisplay,
                    'status' => $overallStatus,
                    'subjects_taken' => $subjectsTaken,
                    'total_subjects' => $filteredSubjects->count(),
                    'missed_subjects' => $missedSubjects
                ];
            }

            // Group by subclass for statistics
            $attendanceData = [];
            $subclassGroups = collect($allStudents)->groupBy('subclassID');
            
            foreach ($subclassGroups as $subID => $subStudents) {
                $firstStudent = $subStudents->first();
                $present = $subStudents->where('status', 'Present')->count();
                $absent = $subStudents->where('status', '!=', 'Present')->count();
                $total = $subStudents->count();

                // Format class display: Main Class - Subclass (or just Main Class if subclass is empty)
                $subclassName = trim($firstStudent['subclass_name'] ?? '');
                $classDisplay = $subclassName 
                    ? $firstStudent['class_name'] . ' - ' . $subclassName 
                    : $firstStudent['class_name'];

                $attendanceData[] = [
                    'subclassID' => $subID,
                    'subclass_name' => $subclassName,
                    'class_name' => $firstStudent['class_name'],
                    'class_display' => $classDisplay,
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
            \Log::error('Error getting exam attendance data: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get subjects for class (Admin) - based on class_subjects table and exam timetable
    public function getSubjectsForClass(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $examID = $request->input('examID');
            $mainClassID = $request->input('mainClassID');
            $subclassID = $request->input('subclassID');

            if (!$examID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Exam ID and school ID required']);
            }

            // Get subjects based on class selection
            $subjects = collect();

            if ($subclassID) {
                // Get subjects for specific subclass from class_subjects
                $classSubjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                    ->where('class_subjects.subclassID', $subclassID)
                    ->where('class_subjects.status', 'Active')
                    ->where('school_subjects.status', 'Active')
                    ->where('classes.schoolID', $schoolID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code'
                    )
                    ->distinct()
                    ->pluck('subjectID')
                    ->toArray();

                // Get subjects from exam_timetables for this subclass
                $examTimetableSubjects = DB::table('exam_timetables')
                    ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetables.examID', $examID)
                    ->where('exam_timetables.subclassID', $subclassID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                // Get subjects from exam_timetable (school-wide)
                $schoolWideSubjects = DB::table('exam_timetable')
                    ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetable.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                // Merge and filter: only subjects that are in class_subjects AND in exam timetable
                $allExamSubjects = $examTimetableSubjects->merge($schoolWideSubjects)->unique('subjectID');
                $subjects = $allExamSubjects->filter(function($subject) use ($classSubjects) {
                    return in_array($subject->subjectID, $classSubjects);
                })->values();

            } elseif ($mainClassID) {
                // Get subjects for all subclasses in main class from class_subjects
                $classSubjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                    ->where('class_subjects.classID', $mainClassID)
                    ->where('class_subjects.status', 'Active')
                    ->where('school_subjects.status', 'Active')
                    ->where('classes.schoolID', $schoolID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code'
                    )
                    ->distinct()
                    ->pluck('subjectID')
                    ->toArray();

                // Get all subclasses for this main class
                $subclassIDs = DB::table('subclasses')
                    ->where('classID', $mainClassID)
                    ->pluck('subclassID')
                    ->toArray();

                // Get subjects from exam_timetables for these subclasses
                $examTimetableSubjects = DB::table('exam_timetables')
                    ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetables.examID', $examID)
                    ->whereIn('exam_timetables.subclassID', $subclassIDs)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                // Get subjects from exam_timetable (school-wide)
                $schoolWideSubjects = DB::table('exam_timetable')
                    ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetable.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                // Merge and filter: only subjects that are in class_subjects AND in exam timetable
                $allExamSubjects = $examTimetableSubjects->merge($schoolWideSubjects)->unique('subjectID');
                $subjects = $allExamSubjects->filter(function($subject) use ($classSubjects) {
                    return in_array($subject->subjectID, $classSubjects);
                })->values();

            } else {
                // If no class selected, get all subjects from exam timetable
                $subjects1 = DB::table('exam_timetable')
                    ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetable.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                $subjects2 = DB::table('exam_timetables')
                    ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetables.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                $subjects = $subjects1->merge($subjects2)->unique('subjectID')->values();
            }

            // Sort by subject name
            $subjects = $subjects->sortBy('subject_name')->values();

            return response()->json(['success' => true, 'subjects' => $subjects]);
        } catch (\Exception $e) {
            \Log::error('Error getting subjects for class: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get student missed subjects (Admin)
    public function getStudentMissedSubjects(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $studentID = $request->input('studentID');
            $examID = $request->input('examID');

            if (!$studentID || !$examID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Student ID, Exam ID and school ID required']);
            }

            // Get all subjects for this exam
            $examSubjects = DB::table('exam_timetable')
                ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                ->where('exam_timetable.examID', $examID)
                ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                ->get();

            $examSubjects2 = DB::table('exam_timetables')
                ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                ->where('exam_timetables.examID', $examID)
                ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                ->get();

            $allExamSubjects = $examSubjects->merge($examSubjects2)->unique('subjectID')->values();

            // Get attendance records for this student
            $attendanceRecords = DB::table('exam_attendance')
                ->where('examID', $examID)
                ->where('studentID', $studentID)
                ->where('status', 'Present')
                ->pluck('subjectID')
                ->toArray();

            // Get missed subjects (subjects not in attendance records)
            $missedSubjects = $allExamSubjects->filter(function($subject) use ($attendanceRecords) {
                return !in_array($subject->subjectID, $attendanceRecords);
            })->values();

            return response()->json([
                'success' => true,
                'missed_subjects' => $missedSubjects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting student missed subjects: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
