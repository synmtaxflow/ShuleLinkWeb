<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Combie;
use App\Models\Attendance;
use App\Models\User;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\PDF;

class ManageClassessController extends Controller
{
    /**
     * Check if user has a specific permission
     */
    private function hasPermission($permissionName)
    {
        $userType = Session::get('user_type');

        // Admin has all permissions
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

    public function manageClasses()
    {
         $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');

        // Get all subclasses with their class details and counts
        $subclasses = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->leftJoin('students', 'subclasses.subclassID', '=', 'students.subclassID')
            ->leftJoin('class_subjects', 'subclasses.classID', '=', 'class_subjects.classID')
            ->leftJoin('teachers', 'subclasses.teacherID', '=', 'teachers.id')
            ->leftJoin('combies', 'subclasses.combieID', '=', 'combies.combieID')
            ->where('classes.schoolID', $schoolID)
            ->select(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'subclasses.stream_code',
                'subclasses.teacherID',
                'subclasses.status as subclass_status',
                'subclasses.combieID',
                'classes.classID',
                'classes.class_name',
                'classes.description as class_description',
                'classes.status as class_status',
                'teachers.first_name as teacher_first_name',
                'teachers.last_name as teacher_last_name',
                'combies.combie_name',
                'combies.combie_code',
                DB::raw('COUNT(DISTINCT students.studentID) as student_count'),
                DB::raw('COUNT(DISTINCT class_subjects.class_subjectID) as subject_count')
            )
            ->groupBy(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'subclasses.stream_code',
                'subclasses.teacherID',
                'subclasses.status',
                'subclasses.combieID',
                'classes.classID',
                'classes.class_name',
                'classes.description',
                'classes.status',
                'teachers.first_name',
                'teachers.last_name',
                'combies.combie_name',
                'combies.combie_code'
            )
            ->get();

        // Get all classes with their subclasses and counts
        $classes = ClassModel::with(['subclasses' => function($query) {
                $query->with(['teacher', 'combie']);
            }])
            ->where('schoolID', $schoolID)
            ->get()
            ->map(function($class) {
                // Get subclass IDs for this class
                $subclassIDs = $class->subclasses->pluck('subclassID')->toArray();
                
                // Calculate total students across all subclasses
                $totalStudents = 0;
                if (!empty($subclassIDs)) {
                    $totalStudents = DB::table('students')
                        ->whereIn('subclassID', $subclassIDs)
                        ->count();
                }
                
                // Calculate total subjects
                $totalSubjects = DB::table('class_subjects')
                    ->where('classID', $class->classID)
                    ->count();
                
                return [
                    'classID' => $class->classID,
                    'class_name' => $class->class_name,
                    'description' => $class->description,
                    'status' => $class->status,
                    'teacherID' => $class->teacherID,
                    'coordinator' => $class->coordinator ? $class->coordinator->first_name . ' ' . $class->coordinator->last_name : null,
                    'subclass_count' => $class->subclasses->count(),
                    'student_count' => $totalStudents,
                    'subject_count' => $totalSubjects,
                    'subclasses' => $class->subclasses->map(function($subclass) {
                        $subclassStudentCount = DB::table('students')
                            ->where('subclassID', $subclass->subclassID)
                            ->count();
                        
                        return [
                            'subclassID' => $subclass->subclassID,
                            'subclass_name' => $subclass->subclass_name,
                            'stream_code' => $subclass->stream_code,
                            'status' => $subclass->status,
                            'teacherID' => $subclass->teacherID,
                            'teacher_name' => $subclass->teacher ? $subclass->teacher->first_name . ' ' . $subclass->teacher->last_name : null,
                            'combieID' => $subclass->combieID,
                            'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                            'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                            'student_count' => $subclassStudentCount,
                        ];
                    })
                ];
            });

        // Get all classes for dropdown (simple list)
        $classesForDropdown = ClassModel::where('schoolID', $schoolID)->get();

        // Get all teachers for dropdown
        $teachers = Teacher::where('schoolID', $schoolID)->get();

        // Get all combies for the school
        $combies = Combie::where('schoolID', $schoolID)->orderBy('combie_name')->get();

        // Get school details for conditional display
        $school_details = \App\Models\School::find($schoolID);
        
        // Get existing class names for disabling in dropdowns
        $existingClassNames = ClassModel::where('schoolID', $schoolID)
            ->pluck('class_name')
            ->toArray();

        return view('Admin.manageClasses', compact('subclasses', 'classes', 'classesForDropdown', 'teachers', 'combies', 'school_details', 'existingClassNames'));
    }

    //class teachers class management
    public function ClassMangement(Request $request, $subclassID = null)
    {
        $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID) {
            return redirect()->route('login')->with('error', 'Teacher ID not found');
        }

        // Check if coordinator view is requested
        $isCoordinatorView = $request->get('coordinator') === 'true';
        $decryptedClassID = null;
        $decryptedSubclassID = null;
        
        // If coordinator view, get classID instead of subclassID
        if ($isCoordinatorView) {
            $classID = $request->get('classID');
            if ($classID) {
                try {
                    $decryptedClassID = Crypt::decrypt($classID);
                } catch (\Exception $e) {
                    return redirect()->route('AdmitedClasses', ['coordinator' => 'true'])->with('error', 'Invalid class ID');
                }
            }
            
            // Verify teacher is coordinator of this main class
            if ($decryptedClassID) {
                $mainClass = ClassModel::find($decryptedClassID);
                if (!$mainClass || $mainClass->teacherID != $teacherID || $mainClass->schoolID != $schoolID) {
                    return redirect()->route('AdmitedClasses', ['coordinator' => 'true'])->with('error', 'Unauthorized access to this class');
                }
                
                // Verify main class has more than one subclass
                $subclassCount = Subclass::where('classID', $decryptedClassID)
                    ->where('status', 'Active')
                    ->count();
                
                if ($subclassCount <= 1) {
                    return redirect()->route('AdmitedClasses', ['coordinator' => 'true'])->with('error', 'This class does not have multiple subclasses');
                }
                
                $subclassDisplayName = $mainClass->class_name . ' (Coordinator)';
            } else {
                return redirect()->route('AdmitedClasses', ['coordinator' => 'true'])->with('error', 'Class ID is required');
            }
        } else {
            // Regular class teacher view - use subclassID
            if ($subclassID) {
                try {
                    $decryptedSubclassID = Crypt::decrypt($subclassID);
                } catch (\Exception $e) {
                    return redirect()->route('AdmitedClasses')->with('error', 'Invalid class ID');
                }
            }

            // Verify subclass belongs to this teacher and get subclass details
            $subclass = null;
            $subclassDisplayName = 'Class Management';
            if ($decryptedSubclassID) {
                $subclass = Subclass::with('class')->find($decryptedSubclassID);
                if (!$subclass || $subclass->teacherID != $teacherID) {
                    return redirect()->route('AdmitedClasses')->with('error', 'Unauthorized access to this class');
                }
                
                // Get display name: class_name + subclass_name (or just class_name if empty)
                $subclassName = trim($subclass->subclass_name);
                $subclassDisplayName = empty($subclassName) 
                    ? $subclass->class->class_name 
                    : $subclass->class->class_name . ' ' . $subclassName;
            }
        }

        return view('Teacher.classMangement', compact('decryptedSubclassID', 'decryptedClassID', 'subclassDisplayName', 'isCoordinatorView'));
    }

    public function getExaminationsForSubclass($subclassID)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json([
                    'error' => 'Teacher ID or School ID not found in session.'
                ], 400);
            }

            // Verify subclass belongs to this teacher
            $subclass = Subclass::find($subclassID);
            if (!$subclass || $subclass->teacherID != $teacherID) {
                return response()->json([
                    'error' => 'Subclass not found or unauthorized access.'
                ], 404);
            }

            // Get all examinations for the school
            // Display all exams - user will search and select
            $examinations = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->select('examinations.*')
                ->orderBy('examinations.created_at', 'desc')
                ->get()
                ->map(function($exam) {
                    return [
                        'examID' => $exam->examID,
                        'exam_name' => $exam->exam_name,
                        'year' => $exam->year,
                        'status' => $exam->status,
                        'start_date' => $exam->start_date,
                        'end_date' => $exam->end_date,
                        'exam_type' => $exam->exam_type,
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

    public function getSubclassResults($subclassID, $examID = null)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json([
                    'error' => 'Teacher ID or School ID not found in session.'
                ], 400);
            }

            // Verify subclass belongs to this teacher
            $subclass = Subclass::with('class')->find($subclassID);
            if (!$subclass || $subclass->teacherID != $teacherID) {
                return response()->json([
                    'error' => 'Subclass not found or unauthorized access.'
                ], 404);
            }

            // Get school type
            $school = \App\Models\School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Get class name from subclass
            $className = $subclass->class ? $subclass->class->class_name : null;

            // Get students in this subclass
            $students = Student::where('subclassID', $subclassID)
                ->where('status', 'Active')
                ->get();

            // Get results for this subclass
            $query = DB::table('results')
                ->join('students', 'results.studentID', '=', 'students.studentID')
                ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                ->leftJoin('examinations', 'results.examID', '=', 'examinations.examID')
                ->where('results.subclassID', $subclassID);

            if ($examID) {
                $query->where('results.examID', $examID);
            }

            $results = $query->select(
                'results.*',
                'students.first_name',
                'students.middle_name',
                'students.last_name',
                'students.admission_number',
                'students.photo',
                'students.gender',
                'school_subjects.subject_name',
                'school_subjects.subject_code',
                'examinations.exam_name',
                'examinations.year'
            )->get();

            // Group results by student and calculate totals
            $studentResults = [];
            foreach ($students as $student) {
                // Filter results for this student
                $studentResult = $results->filter(function($result) use ($student) {
                    return isset($result->studentID) && (int)$result->studentID === (int)$student->studentID;
                });

                $totalMarks = 0;
                $subjectCount = 0;
                $subjectsData = [];

                foreach ($studentResult as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $totalMarks += (float)$result->marks;
                        $subjectCount++;
                    }

                    // Calculate grade or division based on marks
                    $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $schoolType, $className);

                    $subjectsData[] = [
                        'subject_name' => $result->subject_name ?? 'N/A',
                        'subject_code' => $result->subject_code ?? null,
                        'marks' => $result->marks,
                        'grade' => $gradeOrDivision['grade'] ?? $result->grade,
                        'division' => $gradeOrDivision['division'] ?? null,
                        'points' => $gradeOrDivision['points'] ?? null,
                        'remark' => $result->remark,
                    ];
                }

                // Calculate total points for division (for O-Level and A-Level)
                $totalPoints = 0;
                $subjectPoints = array_filter(array_column($subjectsData, 'points'), function($p) { return $p !== null; });

                if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    // O-Level: Use 7 best subjects (lowest points - ascending order)
                    if (count($subjectPoints) > 0) {
                        sort($subjectPoints); // Sort ascending (lowest first)
                        $bestSeven = array_slice($subjectPoints, 0, min(7, count($subjectPoints))); // Take 7 best (or all if less than 7)
                        $totalPoints = array_sum($bestSeven);
                    }
                } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                    // A-Level: Use 3 best principal subjects (highest points)
                    if (count($subjectPoints) > 0) {
                        rsort($subjectPoints); // Sort descending (highest first)
                        $bestThree = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                        $totalPoints = array_sum($bestThree);
                    }
                } else {
                    // Fallback: sum all points
                    $totalPoints = array_sum($subjectPoints);
                }

                $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
                $totalGradeOrDivision = $this->calculateTotalDivision($totalPoints, $schoolType, $className, $averageMarks);

                $studentResults[] = [
                    'studentID' => $student->studentID,
                    'admission_number' => $student->admission_number,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'photo' => $student->photo,
                    'gender' => $student->gender,
                    'total_marks' => round($totalMarks, 2),
                    'average_marks' => round($averageMarks, 2),
                    'subject_count' => $subjectCount,
                    'total_grade' => $totalGradeOrDivision['grade'] ?? null,
                    'total_division' => $totalGradeOrDivision['division'] ?? null,
                    'total_points' => $totalPoints,
                    'subjects' => $subjectsData,
                ];
            }

            // Calculate positions within subclass (sort by total_marks descending, or total_points for secondary)
            usort($studentResults, function($a, $b) use ($schoolType, $className) {
                if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])) {
                    // For secondary O-Level/A-Level, use total_points (lower is better for O-Level, higher is better for A-Level)
                    $aValue = $a['total_points'] ?? 0;
                    $bValue = $b['total_points'] ?? 0;
                    if (in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        // O-Level: lower points is better
                        return $aValue <=> $bValue;
                    } else {
                        // A-Level: higher points is better
                        return $bValue <=> $aValue;
                    }
                } else {
                    // For primary or other, use total_marks (higher is better)
                    return ($b['total_marks'] ?? 0) <=> ($a['total_marks'] ?? 0);
                }
            });

            // Assign subclass positions
            $currentPosition = 1;
            $prevValue = null;
            foreach ($studentResults as &$result) {
                $currentValue = $schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])
                    ? ($result['total_points'] ?? 0)
                    : ($result['total_marks'] ?? 0);

                if ($prevValue !== null && $currentValue != $prevValue) {
                    $currentPosition = $currentPosition + 1;
                }
                $result['subclass_position'] = $currentPosition;
                $prevValue = $currentValue;
            }
            unset($result); // Unset reference

            // Calculate positions within entire class (all subclasses)
            $classID = $subclass->classID;
            $allClassStudents = Student::whereHas('subclass', function($query) use ($classID) {
                $query->where('classID', $classID);
            })
            ->where('status', 'Active')
            ->with('subclass')
            ->get();

            $allClassResults = [];
            foreach ($allClassStudents as $classStudent) {
                // Fetch results for this student from the class (not just subclass)
                $classStudentResultsQuery = DB::table('results')
                    ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                    ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->where('results.studentID', $classStudent->studentID);

                if ($examID) {
                    $classStudentResultsQuery->where('results.examID', $examID);
                }

                $classStudentResults = $classStudentResultsQuery->select(
                    'results.marks',
                    'school_subjects.subject_name'
                )->get();

                $classTotalMarks = 0;
                $classSubjectCount = 0;
                $classSubjectsData = [];

                foreach ($classStudentResults as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $classTotalMarks += (float)$result->marks;
                        $classSubjectCount++;
                    }

                    $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $schoolType, $className);
                    $classSubjectsData[] = [
                        'points' => $gradeOrDivision['points'] ?? null,
                    ];
                }

                $classTotalPoints = 0;
                $classSubjectPoints = array_filter(array_column($classSubjectsData, 'points'), function($p) { return $p !== null; });

                if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    if (count($classSubjectPoints) > 0) {
                        sort($classSubjectPoints);
                        $bestSeven = array_slice($classSubjectPoints, 0, min(7, count($classSubjectPoints)));
                        $classTotalPoints = array_sum($bestSeven);
                    }
                } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                    if (count($classSubjectPoints) > 0) {
                        rsort($classSubjectPoints);
                        $bestThree = array_slice($classSubjectPoints, 0, min(3, count($classSubjectPoints)));
                        $classTotalPoints = array_sum($bestThree);
                    }
                } else {
                    $classTotalPoints = array_sum($classSubjectPoints);
                }

                $classAverageMarks = $classSubjectCount > 0 ? round($classTotalMarks / $classSubjectCount, 2) : 0;

                $allClassResults[] = [
                    'studentID' => $classStudent->studentID,
                    'total_marks' => round($classTotalMarks, 2),
                    'total_points' => $classTotalPoints,
                    'average_marks' => $classAverageMarks,
                    'subject_count' => $classSubjectCount,
                ];
            }

            // Sort all class results by average marks (descending - higher is better)
            usort($allClassResults, function($a, $b) {
                return ($b['average_marks'] ?? 0) <=> ($a['average_marks'] ?? 0);
            });

            // Assign class positions based on average marks
            $classPositionMap = [];
            $currentClassPosition = 1;
            $prevClassAverage = null;
            foreach ($allClassResults as $classResult) {
                $currentAverage = $classResult['average_marks'] ?? 0;

                if ($prevClassAverage !== null && abs($currentAverage - $prevClassAverage) > 0.01) {
                    $currentClassPosition = $currentClassPosition + 1;
                }
                $classPositionMap[$classResult['studentID']] = $currentClassPosition;
                $prevClassAverage = $currentAverage;
            }

            // Get total number of students in class
            $totalClassStudents = count($allClassResults);

            // Add class positions and total students to student results
            foreach ($studentResults as &$result) {
                $result['class_position'] = $classPositionMap[$result['studentID']] ?? null;
                $result['total_class_students'] = $totalClassStudents;
            }
            unset($result); // Unset reference

            return response()->json([
                'success' => true,
                'results' => $studentResults,
                'school_type' => $schoolType,
                'class_name' => $className,
                'subclass' => $subclass
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudentDetailedResults($studentID, $examID = null)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                return response()->json([
                    'error' => 'Teacher ID or School ID not found in session.'
                ], 400);
            }

            // Get student
            $student = Student::with(['subclass.class', 'parent'])->find($studentID);
            if (!$student) {
                return response()->json([
                    'error' => 'Student not found.'
                ], 404);
            }

            // Verify student belongs to teacher's subclass
            if ($student->subclass->teacherID != $teacherID) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            // Get school type
            $school = \App\Models\School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Get class name from student's subclass
            $className = $student->subclass && $student->subclass->class ? $student->subclass->class->class_name : null;

            // Get all results for this student
            $query = DB::table('results')
                ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                ->leftJoin('examinations', 'results.examID', '=', 'examinations.examID')
                ->where('results.studentID', $studentID);

            if ($examID) {
                $query->where('results.examID', $examID);
            }

            $results = $query->select(
                'results.*',
                'school_subjects.subject_name',
                'school_subjects.subject_code',
                'examinations.exam_name',
                'examinations.year',
                'examinations.examID'
            )->orderBy('examinations.created_at', 'desc')
            ->orderBy('school_subjects.subject_name')
            ->get();

            // Group by examination
            $examResults = [];
            foreach ($results->groupBy('examID') as $examId => $examResultsData) {
                $exam = $examResultsData->first();
                $totalMarks = 0;
                $subjectCount = 0;
                $subjectsData = [];

                foreach ($examResultsData as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $totalMarks += (float)$result->marks;
                        $subjectCount++;
                    }

                    $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $schoolType, $className);

                    $subjectsData[] = [
                        'subject_name' => $result->subject_name ?? 'N/A',
                        'subject_code' => $result->subject_code ?? null,
                        'marks' => $result->marks,
                        'grade' => $gradeOrDivision['grade'] ?? $result->grade,
                        'division' => $gradeOrDivision['division'] ?? null,
                        'points' => $gradeOrDivision['points'] ?? null,
                        'remark' => $result->remark,
                    ];
                }

                // Calculate total points for division (for O-Level and A-Level)
                $totalPoints = 0;
                $subjectPoints = array_filter(array_column($subjectsData, 'points'), function($p) { return $p !== null; });

                if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    // O-Level: Use 7 best subjects (lowest points - ascending order)
                    if (count($subjectPoints) > 0) {
                        sort($subjectPoints); // Sort ascending (lowest first)
                        $bestSeven = array_slice($subjectPoints, 0, min(7, count($subjectPoints))); // Take 7 best (or all if less than 7)
                        $totalPoints = array_sum($bestSeven);
                    }
                } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                    // A-Level: Use 3 best principal subjects (highest points)
                    if (count($subjectPoints) > 0) {
                        rsort($subjectPoints); // Sort descending (highest first)
                        $bestThree = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                        $totalPoints = array_sum($bestThree);
                    }
                } else {
                    // Fallback: sum all points
                    $totalPoints = array_sum($subjectPoints);
                }

                $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
                $totalGradeOrDivision = $this->calculateTotalDivision($totalPoints, $schoolType, $className, $averageMarks);

                // Calculate positions for this exam
                $subclassID = $student->subclassID;
                $classID = $student->subclass->classID;

                // Get all students in subclass for this exam
                $subclassStudents = Student::where('subclassID', $subclassID)
                    ->where('status', 'Active')
                    ->get();

                $subclassResultsForExam = [];
                foreach ($subclassStudents as $subclassStudent) {
                    $subclassStudentResults = DB::table('results')
                        ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                        ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                        ->where('results.studentID', $subclassStudent->studentID)
                        ->where('results.examID', $examId)
                        ->select('results.marks', 'school_subjects.subject_name')
                        ->get();

                    $subclassTotalMarks = 0;
                    $subclassSubjectCount = 0;
                    $subclassSubjectsData = [];

                    foreach ($subclassStudentResults as $subclassResult) {
                        if ($subclassResult->marks !== null && $subclassResult->marks !== '') {
                            $subclassTotalMarks += (float)$subclassResult->marks;
                            $subclassSubjectCount++;
                        }
                        $gradeOrDiv = $this->calculateGradeOrDivision($subclassResult->marks, $schoolType, $className);
                        $subclassSubjectsData[] = ['points' => $gradeOrDiv['points'] ?? null];
                    }

                    $subclassTotalPoints = 0;
                    $subclassSubjectPoints = array_filter(array_column($subclassSubjectsData, 'points'), function($p) { return $p !== null; });

                    if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        if (count($subclassSubjectPoints) > 0) {
                            sort($subclassSubjectPoints);
                            $bestSeven = array_slice($subclassSubjectPoints, 0, min(7, count($subclassSubjectPoints)));
                            $subclassTotalPoints = array_sum($bestSeven);
                        }
                    } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                        if (count($subclassSubjectPoints) > 0) {
                            rsort($subclassSubjectPoints);
                            $bestThree = array_slice($subclassSubjectPoints, 0, min(3, count($subclassSubjectPoints)));
                            $subclassTotalPoints = array_sum($bestThree);
                        }
                    } else {
                        $subclassTotalPoints = array_sum($subclassSubjectPoints);
                    }

                    $subclassResultsForExam[] = [
                        'studentID' => $subclassStudent->studentID,
                        'total_marks' => round($subclassTotalMarks, 2),
                        'total_points' => $subclassTotalPoints,
                    ];
                }

                // Sort subclass results
                usort($subclassResultsForExam, function($a, $b) use ($schoolType, $className) {
                    if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])) {
                        $aValue = $a['total_points'] ?? 0;
                        $bValue = $b['total_points'] ?? 0;
                        if (in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                            return $aValue <=> $bValue;
                        } else {
                            return $bValue <=> $aValue;
                        }
                    } else {
                        return ($b['total_marks'] ?? 0) <=> ($a['total_marks'] ?? 0);
                    }
                });

                // Get subclass position
                $subclassPosition = null;
                $currentSubclassPos = 1;
                $prevSubclassValue = null;
                foreach ($subclassResultsForExam as $subclassResult) {
                    $currentSubclassValue = $schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])
                        ? ($subclassResult['total_points'] ?? 0)
                        : ($subclassResult['total_marks'] ?? 0);

                    if ($prevSubclassValue !== null && $currentSubclassValue != $prevSubclassValue) {
                        $currentSubclassPos++;
                    }
                    if ($subclassResult['studentID'] == $studentID) {
                        $subclassPosition = $currentSubclassPos;
                        break;
                    }
                    $prevSubclassValue = $currentSubclassValue;
                }

                // Get all students in class for this exam
                $allClassStudentsForExam = Student::whereHas('subclass', function($query) use ($classID) {
                    $query->where('classID', $classID);
                })
                ->where('status', 'Active')
                ->get();

                $allClassResultsForExam = [];
                foreach ($allClassStudentsForExam as $classStudentForExam) {
                    $classStudentResultsForExam = DB::table('results')
                        ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                        ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                        ->where('results.studentID', $classStudentForExam->studentID)
                        ->where('results.examID', $examId)
                        ->select('results.marks', 'school_subjects.subject_name')
                        ->get();

                    $classTotalMarksForExam = 0;
                    $classSubjectCountForExam = 0;
                    $classSubjectsDataForExam = [];

                    foreach ($classStudentResultsForExam as $classResultForExam) {
                        if ($classResultForExam->marks !== null && $classResultForExam->marks !== '') {
                            $classTotalMarksForExam += (float)$classResultForExam->marks;
                            $classSubjectCountForExam++;
                        }
                        $gradeOrDivForExam = $this->calculateGradeOrDivision($classResultForExam->marks, $schoolType, $className);
                        $classSubjectsDataForExam[] = ['points' => $gradeOrDivForExam['points'] ?? null];
                    }

                    $classTotalPointsForExam = 0;
                    $classSubjectPointsForExam = array_filter(array_column($classSubjectsDataForExam, 'points'), function($p) { return $p !== null; });

                    if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        if (count($classSubjectPointsForExam) > 0) {
                            sort($classSubjectPointsForExam);
                            $bestSeven = array_slice($classSubjectPointsForExam, 0, min(7, count($classSubjectPointsForExam)));
                            $classTotalPointsForExam = array_sum($bestSeven);
                        }
                    } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                        if (count($classSubjectPointsForExam) > 0) {
                            rsort($classSubjectPointsForExam);
                            $bestThree = array_slice($classSubjectPointsForExam, 0, min(3, count($classSubjectPointsForExam)));
                            $classTotalPointsForExam = array_sum($bestThree);
                        }
                    } else {
                        $classTotalPointsForExam = array_sum($classSubjectPointsForExam);
                    }

                    $classAverageMarksForExam = $classSubjectCountForExam > 0 ? round($classTotalMarksForExam / $classSubjectCountForExam, 2) : 0;

                    $allClassResultsForExam[] = [
                        'studentID' => $classStudentForExam->studentID,
                        'total_marks' => round($classTotalMarksForExam, 2),
                        'total_points' => $classTotalPointsForExam,
                        'average_marks' => $classAverageMarksForExam,
                        'subject_count' => $classSubjectCountForExam,
                    ];
                }

                // Sort all class results by average marks (descending - higher is better)
                usort($allClassResultsForExam, function($a, $b) {
                    return ($b['average_marks'] ?? 0) <=> ($a['average_marks'] ?? 0);
                });

                // Get class position based on average marks
                $classPosition = null;
                $currentClassPos = 1;
                $prevClassAverageForExam = null;
                $totalClassStudentsForExam = count($allClassResultsForExam);
                foreach ($allClassResultsForExam as $classResultForExam) {
                    $currentAverage = $classResultForExam['average_marks'] ?? 0;

                    if ($prevClassAverageForExam !== null && abs($currentAverage - $prevClassAverageForExam) > 0.01) {
                        $currentClassPos++;
                    }
                    if ($classResultForExam['studentID'] == $studentID) {
                        $classPosition = $currentClassPos;
                        break;
                    }
                    $prevClassAverageForExam = $currentAverage;
                }

                $examResults[] = [
                    'examID' => $examId,
                    'exam_name' => $exam->exam_name,
                    'year' => $exam->year,
                    'total_marks' => round($totalMarks, 2),
                    'average_marks' => round($averageMarks, 2),
                    'subject_count' => $subjectCount,
                    'total_grade' => $totalGradeOrDivision['grade'] ?? null,
                    'total_division' => $totalGradeOrDivision['division'] ?? null,
                    'total_points' => $totalPoints,
                    'subjects' => $subjectsData,
                    'class_position' => $classPosition,
                    'total_class_students' => $totalClassStudentsForExam,
                ];
            }

            return response()->json([
                'success' => true,
                'student' => $student,
                'results' => $examResults,
                'school_type' => $schoolType,
                'class_name' => $className
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadStudentResultsPDF($studentID, $examID)
    {
        try {
            $teacherID = Session::get('teacherID');
            $schoolID = Session::get('schoolID');

            if (!$teacherID || !$schoolID) {
                abort(400, 'Teacher ID or School ID not found in session.');
            }

            // Get student
            $student = Student::with(['subclass.class', 'parent'])->find($studentID);
            if (!$student) {
                abort(404, 'Student not found.');
            }

            // Verify student belongs to teacher's subclass
            if ($student->subclass->teacherID != $teacherID) {
                abort(403, 'Unauthorized access.');
            }

            // Get school
            $school = \App\Models\School::find($schoolID);
            if (!$school) {
                abort(404, 'School not found.');
            }
            $schoolType = $school->school_type ?? 'Secondary';
            $schoolName = $school->school_name ?? 'School';

            // Get class name
            $className = $student->subclass && $student->subclass->class ? $student->subclass->class->class_name : null;

            // Get results for this student and exam
            $results = DB::table('results')
                ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                ->leftJoin('examinations', 'results.examID', '=', 'examinations.examID')
                ->where('results.studentID', $studentID)
                ->where('results.examID', $examID)
                ->select(
                    'results.*',
                    'school_subjects.subject_name',
                    'school_subjects.subject_code',
                    'examinations.exam_name',
                    'examinations.year'
                )
                ->orderBy('school_subjects.subject_name')
                ->get();

            if ($results->isEmpty()) {
                abort(404, 'No results found for this student and exam.');
            }

            $firstResult = $results->first();

            // Get exam details separately
            $examDetails = DB::table('examinations')
                ->where('examID', $examID)
                ->first();

            if (!$examDetails) {
                abort(404, 'Examination not found.');
            }
            $totalMarks = 0;
            $subjectCount = 0;
            $subjectsData = [];

            foreach ($results as $result) {
                if ($result->marks !== null && $result->marks !== '') {
                    $totalMarks += (float)$result->marks;
                    $subjectCount++;
                }

                $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $schoolType, $className);

                $subjectsData[] = [
                    'subject_name' => $result->subject_name ?? 'N/A',
                    'subject_code' => $result->subject_code ?? null,
                    'marks' => $result->marks,
                    'grade' => $gradeOrDivision['grade'] ?? $result->grade,
                    'division' => $gradeOrDivision['division'] ?? null,
                    'points' => $gradeOrDivision['points'] ?? null,
                    'remark' => $result->remark,
                ];
            }

            // Calculate total points
            $totalPoints = 0;
            $subjectPoints = array_filter(array_column($subjectsData, 'points'), function($p) { return $p !== null; });

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
            } else {
                $totalPoints = array_sum($subjectPoints);
            }

            $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            $totalGradeOrDivision = $this->calculateTotalDivision($totalPoints, $schoolType, $className, $averageMarks);

            // Calculate positions (similar to getStudentDetailedResults)
            $subclassID = $student->subclassID;
            $classID = $student->subclass->classID;

            // Get subclass position
            $subclassStudents = Student::where('subclassID', $subclassID)
                ->where('status', 'Active')
                ->get();

            $subclassResultsForExam = [];
            foreach ($subclassStudents as $subclassStudent) {
                $subclassStudentResults = DB::table('results')
                    ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                    ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->where('results.studentID', $subclassStudent->studentID)
                    ->where('results.examID', $examID)
                    ->select('results.marks', 'school_subjects.subject_name')
                    ->get();

                $subclassTotalMarks = 0;
                $subclassSubjectsData = [];

                foreach ($subclassStudentResults as $subclassResult) {
                    if ($subclassResult->marks !== null && $subclassResult->marks !== '') {
                        $subclassTotalMarks += (float)$subclassResult->marks;
                    }
                    $gradeOrDiv = $this->calculateGradeOrDivision($subclassResult->marks, $schoolType, $className);
                    $subclassSubjectsData[] = ['points' => $gradeOrDiv['points'] ?? null];
                }

                $subclassTotalPoints = 0;
                $subclassSubjectPoints = array_filter(array_column($subclassSubjectsData, 'points'), function($p) { return $p !== null; });

                if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    if (count($subclassSubjectPoints) > 0) {
                        sort($subclassSubjectPoints);
                        $bestSeven = array_slice($subclassSubjectPoints, 0, min(7, count($subclassSubjectPoints)));
                        $subclassTotalPoints = array_sum($bestSeven);
                    }
                } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                    if (count($subclassSubjectPoints) > 0) {
                        rsort($subclassSubjectPoints);
                        $bestThree = array_slice($subclassSubjectPoints, 0, min(3, count($subclassSubjectPoints)));
                        $subclassTotalPoints = array_sum($bestThree);
                    }
                } else {
                    $subclassTotalPoints = array_sum($subclassSubjectPoints);
                }

                $subclassResultsForExam[] = [
                    'studentID' => $subclassStudent->studentID,
                    'total_marks' => round($subclassTotalMarks, 2),
                    'total_points' => $subclassTotalPoints,
                ];
            }

            usort($subclassResultsForExam, function($a, $b) use ($schoolType, $className) {
                if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])) {
                    $aValue = $a['total_points'] ?? 0;
                    $bValue = $b['total_points'] ?? 0;
                    if (in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        return $aValue <=> $bValue;
                    } else {
                        return $bValue <=> $aValue;
                    }
                } else {
                    return ($b['total_marks'] ?? 0) <=> ($a['total_marks'] ?? 0);
                }
            });

            $subclassPosition = null;
            $currentSubclassPos = 1;
            $prevSubclassValue = null;
            foreach ($subclassResultsForExam as $subclassResult) {
                $currentSubclassValue = $schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])
                    ? ($subclassResult['total_points'] ?? 0)
                    : ($subclassResult['total_marks'] ?? 0);

                if ($prevSubclassValue !== null && $currentSubclassValue != $prevSubclassValue) {
                    $currentSubclassPos++;
                }
                if ($subclassResult['studentID'] == $studentID) {
                    $subclassPosition = $currentSubclassPos;
                    break;
                }
                $prevSubclassValue = $currentSubclassValue;
            }

            // Get class position
            $allClassStudentsForExam = Student::whereHas('subclass', function($query) use ($classID) {
                $query->where('classID', $classID);
            })
            ->where('status', 'Active')
            ->get();

            $allClassResultsForExam = [];
            foreach ($allClassStudentsForExam as $classStudentForExam) {
                $classStudentResultsForExam = DB::table('results')
                    ->leftJoin('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
                    ->leftJoin('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->where('results.studentID', $classStudentForExam->studentID)
                    ->where('results.examID', $examID)
                    ->select('results.marks', 'school_subjects.subject_name')
                    ->get();

                $classTotalMarksForExam = 0;
                $classSubjectsDataForExam = [];

                foreach ($classStudentResultsForExam as $classResultForExam) {
                    if ($classResultForExam->marks !== null && $classResultForExam->marks !== '') {
                        $classTotalMarksForExam += (float)$classResultForExam->marks;
                    }
                    $gradeOrDivForExam = $this->calculateGradeOrDivision($classResultForExam->marks, $schoolType, $className);
                    $classSubjectsDataForExam[] = ['points' => $gradeOrDivForExam['points'] ?? null];
                }

                $classTotalPointsForExam = 0;
                $classSubjectPointsForExam = array_filter(array_column($classSubjectsDataForExam, 'points'), function($p) { return $p !== null; });

                if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    if (count($classSubjectPointsForExam) > 0) {
                        sort($classSubjectPointsForExam);
                        $bestSeven = array_slice($classSubjectPointsForExam, 0, min(7, count($classSubjectPointsForExam)));
                        $classTotalPointsForExam = array_sum($bestSeven);
                    }
                } elseif ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                    if (count($classSubjectPointsForExam) > 0) {
                        rsort($classSubjectPointsForExam);
                        $bestThree = array_slice($classSubjectPointsForExam, 0, min(3, count($classSubjectPointsForExam)));
                        $classTotalPointsForExam = array_sum($bestThree);
                    }
                } else {
                    $classTotalPointsForExam = array_sum($classSubjectPointsForExam);
                }

                $allClassResultsForExam[] = [
                    'studentID' => $classStudentForExam->studentID,
                    'total_marks' => round($classTotalMarksForExam, 2),
                    'total_points' => $classTotalPointsForExam,
                ];
            }

            usort($allClassResultsForExam, function($a, $b) use ($schoolType, $className) {
                if ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])) {
                    $aValue = $a['total_points'] ?? 0;
                    $bValue = $b['total_points'] ?? 0;
                    if (in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        return $aValue <=> $bValue;
                    } else {
                        return $bValue <=> $aValue;
                    }
                } else {
                    return ($b['total_marks'] ?? 0) <=> ($a['total_marks'] ?? 0);
                }
            });

            $classPosition = null;
            $currentClassPos = 1;
            $prevClassValueForExam = null;
            foreach ($allClassResultsForExam as $classResultForExam) {
                $currentClassValueForExam = $schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])
                    ? ($classResultForExam['total_points'] ?? 0)
                    : ($classResultForExam['total_marks'] ?? 0);

                if ($prevClassValueForExam !== null && $currentClassValueForExam != $prevClassValueForExam) {
                    $currentClassPos++;
                }
                if ($classResultForExam['studentID'] == $studentID) {
                    $classPosition = $currentClassPos;
                    break;
                }
                $prevClassValueForExam = $currentClassValueForExam;
            }

            // Prepare data for PDF
            $data = [
                'school' => $school,
                'schoolName' => $schoolName,
                'student' => $student,
                'exam' => $examDetails,
                'subjects' => $subjectsData,
                'total_marks' => round($totalMarks, 2),
                'average_marks' => round($averageMarks, 2),
                'subject_count' => $subjectCount,
                'total_grade' => $totalGradeOrDivision['grade'] ?? null,
                'total_division' => $totalGradeOrDivision['division'] ?? null,
                'subclass_position' => $subclassPosition,
                'class_position' => $classPosition,
                'schoolType' => $schoolType,
                'className' => $className,
            ];

            // Generate PDF
            try {
                // Use service container - should be registered now
                $pdf = app('dompdf.wrapper');

                $pdf->loadView('Teacher.pdf.student_results', $data);

                // Clean filename - remove special characters
                $filename = 'student_results_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $student->admission_number) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $examDetails->exam_name) . '.pdf';

                return $pdf->download($filename);
            } catch (\Exception $pdfError) {
                \Log::error('PDF Generation Error: ' . $pdfError->getMessage());
                \Log::error('PDF Generation Trace: ' . $pdfError->getTraceAsString());
                abort(500, 'Error generating PDF: ' . $pdfError->getMessage());
            }

        } catch (\Exception $e) {
            \Log::error('Student Results PDF Error: ' . $e->getMessage());
            \Log::error('Student Results PDF Trace: ' . $e->getTraceAsString());
            abort(500, 'Error: ' . $e->getMessage());
        }
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
            // Check if O-Level (Form One, Two, Three, Four) or A-Level (Form Five, Form Six)
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


    //class teachers Admitted class
    public function AdmitedClasses(Request $request)
    {
        // Check if coordinator view is requested
        $isCoordinatorView = $request->get('coordinator') === 'true';
        $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID) {
            return redirect()->route('login')->with('error', 'Teacher ID not found');
        }

        // If coordinator view, show main classes instead of subclasses
        if ($isCoordinatorView) {
            // Get main classes where teacher is coordinator and have more than one subclass
            $mainClasses = DB::table('classes')
                ->where('teacherID', $teacherID)
                ->where('schoolID', $schoolID)
                ->get()
                ->filter(function($mainClass) {
                    // Only include classes with more than one subclass
                    $subclassCount = DB::table('subclasses')
                        ->where('classID', $mainClass->classID)
                        ->where('status', 'Active')
                        ->count();
                    return $subclassCount > 1;
                })
                ->map(function($mainClass) use ($schoolID) {
                    // Get total students in all subclasses of this main class
                    $totalStudents = DB::table('students')
                        ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                        ->where('subclasses.classID', $mainClass->classID)
                        ->where('students.status', 'Active')
                        ->where('subclasses.status', 'Active')
                        ->distinct('students.studentID')
                        ->count('students.studentID');
                    
                    // Get total subclasses count
                    $subclassesCount = DB::table('subclasses')
                        ->where('classID', $mainClass->classID)
                        ->where('status', 'Active')
                        ->count();
                    
                    // Get subclasses list
                    $subclasses = DB::table('subclasses')
                        ->where('classID', $mainClass->classID)
                        ->where('status', 'Active')
                        ->select('subclassID', 'subclass_name')
                        ->get()
                        ->map(function($subclass) {
                            return [
                                'subclassID' => $subclass->subclassID,
                                'subclass_name' => $subclass->subclass_name
                            ];
                        });

                    return [
                        'classID' => $mainClass->classID,
                        'class_name' => $mainClass->class_name,
                        'description' => $mainClass->description ?? '',
                        'student_count' => (int)$totalStudents,
                        'subclasses_count' => (int)$subclassesCount,
                        'subclasses' => $subclasses
                    ];
                })
                ->values();

            return view('Teacher.AdmitedClasses', compact('mainClasses', 'isCoordinatorView'));
        }

        // Regular class teacher view - get subclasses
        $subclasses = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->leftJoin('students', 'subclasses.subclassID', '=', 'students.subclassID')
            ->leftJoin('class_subjects', function($join) {
                $join->on('subclasses.classID', '=', 'class_subjects.classID')
                     ->where('class_subjects.status', '=', 'Active');
            })
            ->where('subclasses.teacherID', $teacherID)
            ->select(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'subclasses.stream_code',
                'subclasses.teacherID',
                'classes.classID',
                'classes.class_name',
                'classes.description',
                DB::raw('COUNT(DISTINCT students.studentID) as student_count'),
                DB::raw('COUNT(DISTINCT class_subjects.class_subjectID) as subject_count')
            )
            ->groupBy(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'subclasses.stream_code',
                'subclasses.teacherID',
                'classes.classID',
                'classes.class_name',
                'classes.description'
            )
            ->orderBy('classes.class_name')
            ->orderBy('subclasses.subclass_name')
            ->get()
            ->map(function($subclass) {
                // Get subjects for this subclass
                $subjects = ClassSubject::with(['subject', 'teacher'])
                    ->where('classID', $subclass->classID)
                    ->where('subclassID', $subclass->subclassID)
                    ->where('status', 'Active')
                    ->get()
                    ->map(function($classSubject) {
                        return [
                            'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                            'subject_code' => $classSubject->subject ? $classSubject->subject->subject_code : null,
                            'teacher_name' => $classSubject->teacher
                                ? $classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name
                                : 'Not Assigned'
                        ];
                    });

                return [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'stream_code' => $subclass->stream_code,
                    'classID' => $subclass->classID,
                    'class_name' => $subclass->class_name,
                    'description' => $subclass->description,
                    'student_count' => (int)$subclass->student_count,
                    'subject_count' => (int)$subclass->subject_count,
                    'subjects' => $subjects
                ];
            });

        return view('Teacher.AdmitedClasses', compact('subclasses', 'isCoordinatorView'));
    }

    public function get_subclass_subjects($subclassID)
    {
        // Check permission
        if (!$this->hasPermission('view_subjects')) {
            return response()->json([
                'error' => 'You do not have permission to view subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subclass = Subclass::with(['class.classSubjects.subject', 'class.classSubjects.teacher'])
                ->where('subclassID', $subclassID)
                ->first();

            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            // Check if subclass belongs to this school
            if ($subclass->class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access to this subclass.'
                ], 403);
            }

            // Get subjects for this class
            $subjects = ClassSubject::with(['subject', 'teacher'])
                ->where('classID', $subclass->classID)
                ->where('status', 'Active')
                ->get()
                ->map(function($classSubject) {
                    return [
                        'class_subjectID' => $classSubject->class_subjectID,
                        'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                        'subject_code' => $classSubject->subject ? $classSubject->subject->subject_code : null,
                        'teacher_name' => $classSubject->teacher
                            ? $classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name
                            : 'Not Assigned',
                        'status' => $classSubject->status
                    ];
                });
            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'subclass_name' => $subclass->stream_code || ($subclass->class->class_name . ' ' . $subclass->subclass_name),
                'subject_count' => $subjects->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_classes()
    {
        // Check permission
        if (!$this->hasPermission('view_all_class')) {
            return response()->json([
                'error' => 'You do not have permission to view classes.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Get all classes with their subclasses, coordinator, and student counts
            $classes = ClassModel::with(['coordinator', 'subclasses'])
                ->where('schoolID', $schoolID)
                ->get()
                ->map(function($class) {
                    // Get subclass IDs for this class
                    $subclassIDs = $class->subclasses->pluck('subclassID')->toArray();

                    // Calculate total students across all subclasses using DB query
                    $totalStudents = 0;
                    if (!empty($subclassIDs)) {
                        $totalStudents = DB::table('students')
                            ->whereIn('subclassID', $subclassIDs)
                            ->count();
                    }

                    // Get student count per subclass
                    $subclassStudentCounts = [];
                    if (!empty($subclassIDs)) {
                        $counts = DB::table('students')
                            ->whereIn('subclassID', $subclassIDs)
                            ->select('subclassID', DB::raw('COUNT(*) as student_count'))
                            ->groupBy('subclassID')
                            ->pluck('student_count', 'subclassID')
                            ->toArray();
                        $subclassStudentCounts = $counts;
                    }

                    return [
                        'classID' => $class->classID,
                        'class_name' => $class->class_name,
                        'description' => $class->description,
                        'status' => $class->status ?? 'Inactive',
                        'coordinator_name' => $class->coordinator
                            ? $class->coordinator->first_name . ' ' . $class->coordinator->last_name
                            : 'Not Assigned',
                        'coordinator_id' => $class->coordinator ? $class->coordinator->id : null,
                        'subclass_count' => $class->subclasses->count(),
                        'total_students' => $totalStudents,
                        'subclasses' => $class->subclasses->map(function($subclass) use ($subclassStudentCounts) {
                            return [
                                'subclassID' => $subclass->subclassID,
                                'subclass_name' => $subclass->subclass_name,
                                'stream_code' => $subclass->stream_code,
                                'student_count' => $subclassStudentCounts[$subclass->subclassID] ?? 0
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'classes' => $classes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_subclass($subclassID)
    {
        // Check permission
        if (!$this->hasPermission('view_subclass')) {
            return response()->json([
                'error' => 'You do not have permission to view subclasses.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subclass = Subclass::with(['class', 'classTeacher', 'combie'])
                ->where('subclassID', $subclassID)
                ->first();

            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            // Check if subclass belongs to this school
            if ($subclass->class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access to this subclass.'
                ], 403);
            }

            // Include class name in response
            $subclassData = $subclass->toArray();
            $subclassData['class_name'] = $subclass->class->class_name;

            return response()->json([
                'success' => true,
                'subclass' => $subclassData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_subclass(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('edit_subclass')) {
            return response()->json([
                'error' => 'You do not have permission to edit subclasses.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'subclassID' => 'required|exists:subclasses,subclassID',
                'classID' => 'required|exists:classes,classID',
                'subclass_name' => 'required|string|max:50',
                'teacherID' => '',
                'combieID' => 'nullable|exists:combies,combieID',
                'first_grade' => 'nullable|string|max:50',
                'final_grade' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    $errors = [];
                    foreach ($validator->errors()->toArray() as $field => $messages) {
                        $errors[$field] = $messages[0];
                    }
                    return response()->json(['errors' => $errors], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'School ID not found in session. Please login again.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'School ID not found in session. Please login again.');
            }

            $subclass = Subclass::find($request->subclassID);

            if (!$subclass) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Subclass not found.'
                    ], 404);
                }
                return redirect()->back()->with('error', 'Subclass not found.');
            }

            // Check if subclass belongs to this school
            $class = ClassModel::find($subclass->classID);
            if ($class->schoolID != $schoolID) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Unauthorized access to this subclass.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized access to this subclass.');
            }

            // Get new class to generate stream_code
            $newClass = ClassModel::find($request->classID);
            if (!$newClass) {
                return response()->json([
                    'error' => 'Selected class not found.'
                ], 404);
            }

            $stream_code = $newClass->class_name . ' ' . $request->subclass_name;

            // Check if subclass name already exists for this class (excluding current)
            $existingSubclass = Subclass::where('classID', $request->classID)
                ->where('subclass_name', $request->subclass_name)
                ->where('subclassID', '!=', $request->subclassID)
                ->first();

            if ($existingSubclass) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Subclass "' . $request->subclass_name . '" already exists for class "' . $newClass->class_name . '".'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Subclass "' . $request->subclass_name . '" already exists for class "' . $newClass->class_name . '".')->withInput();
            }

            // Handle teacherID
            $teacherID = $request->input('teacherID');
            if ($teacherID === null || $teacherID === '' || $teacherID === '0' || $teacherID === false) {
                $teacherID = null;
            } else {
                $teacherID = (int) $teacherID;
                $teacher = Teacher::find($teacherID);
                if (!$teacher) {
                    return response()->json([
                        'error' => 'Selected teacher not found.'
                    ], 404);
                }
            }

            // Handle combieID
            $combieID = $request->input('combieID');
            if ($combieID === null || $combieID === '' || $combieID === '0' || $combieID === false) {
                $combieID = null;
            } else {
                $combieID = (int) $combieID;
                $combie = Combie::where('combieID', $combieID)
                    ->where('schoolID', $schoolID)
                    ->first();
                if (!$combie) {
                    return response()->json([
                        'error' => 'Selected combination not found or does not belong to this school.'
                    ], 404);
                }
            }

            // Check if user has approval permission - if yes, set status to Active, otherwise Inactive
            $status = $this->hasPermission('approval_class') ? 'Active' : 'Inactive';

            // Handle first_grade and final_grade - convert empty string to null
            $firstGrade = $request->input('first_grade');
            if (empty($firstGrade) || $firstGrade === '') {
                $firstGrade = null;
            }

            $finalGrade = $request->input('final_grade');
            if (empty($finalGrade) || $finalGrade === '') {
                $finalGrade = null;
            }

            // Update subclass
            $subclass->classID = $request->classID;
            $subclass->subclass_name = $request->subclass_name;
            $subclass->stream_code = $stream_code;
            $subclass->teacherID = $teacherID;
            $subclass->combieID = $combieID;
            $subclass->first_grade = $firstGrade;
            $subclass->final_grade = $finalGrade;
            $subclass->status = $status;
            $subclass->save();

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => 'Subclass "' . $stream_code . '" updated successfully!',
                    'subclass' => $subclass
                ], 200);
            }

            // Traditional form submission
            return redirect()->route('manageClasses')->with('success', 'Subclass "' . $stream_code . '" updated successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Database error: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Database error occurred. Please try again.')->withInput();
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred. Please try again.')->withInput();
        }
    }

    public function get_class($classID)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $class = ClassModel::with('coordinator')
                ->where('classID', $classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$class) {
                return response()->json([
                    'error' => 'Class not found or unauthorized access.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'class' => $class
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_class(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('edit_class')) {
            return response()->json([
                'error' => 'You do not have permission to edit classes.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'classID' => 'required|exists:classes,classID',
                'class_name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'teacherID' => 'nullable|exists:teachers,id',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    $errors = [];
                    foreach ($validator->errors()->toArray() as $field => $messages) {
                        $errors[$field] = $messages[0];
                    }
                    return response()->json(['errors' => $errors], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'School ID not found in session. Please login again.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'School ID not found in session. Please login again.');
            }

            $class = ClassModel::where('classID', $request->classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$class) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Class not found or unauthorized access.'
                    ], 404);
                }
                return redirect()->back()->with('error', 'Class not found or unauthorized access.');
            }

            // Check if class name already exists (excluding current class)
            $existingClass = ClassModel::where('schoolID', $schoolID)
                ->where('class_name', $request->class_name)
                ->where('classID', '!=', $request->classID)
                ->first();

            if ($existingClass) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Class name "' . $request->class_name . '" already exists.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Class name "' . $request->class_name . '" already exists.')->withInput();
            }

            // Handle teacherID
            $teacherID = $request->input('teacherID');
            if ($teacherID === null || $teacherID === '' || $teacherID === '0' || $teacherID === false) {
                $teacherID = null;
            } else {
                $teacherID = (int) $teacherID;
                $teacher = Teacher::find($teacherID);
                if (!$teacher) {
                    return response()->json([
                        'error' => 'Selected coordinator not found.'
                    ], 404);
                }
            }

            // Check if user has approval permission - if yes, set status to Active, otherwise Inactive
            $status = $this->hasPermission('approval_class') ? 'Active' : 'Inactive';

            // Update class
            $class->class_name = $request->class_name;
            $class->description = $request->description ?? null;
            $class->teacherID = $teacherID;
            $class->status = $status;
            $class->save();

            // If class has only one subclass (default subclass), update its teacherID
            $subclasses = Subclass::where('classID', $class->classID)->get();
            if ($subclasses->count() === 1) {
                $defaultSubclass = $subclasses->first();
                // Check if it's default subclass (empty/whitespace name)
                if (trim($defaultSubclass->subclass_name) === '') {
                    $defaultSubclass->teacherID = $teacherID;
                    $defaultSubclass->save();
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => 'Class "' . $class->class_name . '" updated successfully!',
                    'class' => $class
                ], 200);
            }

            return redirect()->route('manageClasses')->with('success', 'Class "' . $class->class_name . '" updated successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Database error: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Database error occurred. Please try again.')->withInput();
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred. Please try again.')->withInput();
        }
    }

    public function delete_class($classID)
    {
        // Check permission
        if (!$this->hasPermission('delete_class')) {
            return response()->json([
                'error' => 'You do not have permission to delete classes.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $class = ClassModel::where('classID', $classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$class) {
                return response()->json([
                    'error' => 'Class not found or unauthorized access.'
                ], 404);
            }

            // Check if class has subclasses
            $subclassCount = Subclass::where('classID', $classID)->count();
            if ($subclassCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete class "' . $class->class_name . '" because it has ' . $subclassCount . ' subclass(es). Please delete or reassign subclasses first.'
                ], 400);
            }

            $className = $class->class_name;
            $class->delete();

            return response()->json([
                'success' => 'Class "' . $className . '" deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save_class(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('create_class')) {
            return response()->json([
                'error' => 'You do not have permission to create classes.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'class_name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'teacherID' => 'nullable|exists:teachers,id',
                'has_subclasses' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session. Please login again.'
                ], 400);
            }

            // Check if class name already exists in this school
            $existingClass = ClassModel::where('schoolID', $schoolID)
                ->where('class_name', $request->class_name)
                ->first();

            if ($existingClass) {
                return response()->json([
                    'error' => 'Class name "' . $request->class_name . '" already exists in this school. Please choose a different name.'
                ], 400);
            }

            // Handle teacherID - convert empty string to null
            $teacherID = $request->teacherID;
            if ($teacherID === '' || $teacherID === null || $teacherID === '0') {
                $teacherID = null;
            }

            // Check if user has approval permission - if yes, set status to Active, otherwise Inactive
            $status = $this->hasPermission('approval_class') ? 'Active' : 'Inactive';

            // Determine if class has subclasses (default to true if not specified)
            $hasSubclasses = $request->has('has_subclasses') ? (bool)$request->has_subclasses : true;

            $class = ClassModel::create([
                'schoolID' => $schoolID,
                'class_name' => $request->class_name,
                'description' => $request->description ?? null,
                'teacherID' => $teacherID,
                'status' => $status,
                'has_subclasses' => $hasSubclasses,
            ]);

            // If class doesn't have subclasses, create a default subclass with empty name
            if (!$hasSubclasses) {
                Subclass::create([
                    'classID' => $class->classID,
                    'subclass_name' => ' ', // Whitespace as default
                    'stream_code' => $class->class_name,
                    'teacherID' => $teacherID, // Use same teacherID as class coordinator
                    'combieID' => null,
                    'first_grade' => null,
                    'final_grade' => null,
                    'status' => $status,
                ]);
            } else {
                // Handle subclasses if provided
                if ($request->has('subclasses') && is_array($request->subclasses) && count($request->subclasses) > 0) {
                    foreach ($request->subclasses as $subclassData) {
                        if (!empty($subclassData['subclass_name'])) {
                            $stream_code = $class->class_name . ' ' . $subclassData['subclass_name'];
                            
                            // Check if subclass already exists
                            $existingSubclass = Subclass::where('classID', $class->classID)
                                ->where('subclass_name', $subclassData['subclass_name'])
                                ->first();

                            if (!$existingSubclass) {
                                // Handle teacherID
                                $subclassTeacherID = $subclassData['teacherID'] ?? null;
                                if ($subclassTeacherID === '' || $subclassTeacherID === null || $subclassTeacherID === '0') {
                                    $subclassTeacherID = null;
                                }

                                // Handle combieID
                                $combieID = $subclassData['combieID'] ?? null;
                                if ($combieID === '' || $combieID === null || $combieID === '0') {
                                    $combieID = null;
                                }

                                Subclass::create([
                                    'classID' => $class->classID,
                                    'subclass_name' => $subclassData['subclass_name'],
                                    'stream_code' => $stream_code,
                                    'teacherID' => $subclassTeacherID,
                                    'combieID' => $combieID,
                                    'first_grade' => $subclassData['first_grade'] ?? null,
                                    'final_grade' => $subclassData['final_grade'] ?? null,
                                    'status' => $status,
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json([
                'success' => 'Class "' . $class->class_name . '" and subclasses created successfully!',
                'class' => $class
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save_sub_lass(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'classID' => 'required|exists:classes,classID',
                'subclass_name' => 'required|string|max:50',
                'teacherID' => '',
                'combieID' => 'nullable|exists:combies,combieID',
                'first_grade' => 'nullable|string|max:50',
                'final_grade' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Get class to generate stream_code
            $class = ClassModel::find($request->classID);

            if (!$class) {
                return response()->json([
                    'error' => 'Selected class not found.'
                ], 404);
            }

            $stream_code = $class->class_name . ' ' . $request->subclass_name;

            // Check if subclass already exists for this class
            $existingSubclass = Subclass::where('classID', $request->classID)
                ->where('subclass_name', $request->subclass_name)
                ->first();

            if ($existingSubclass) {
                return response()->json([
                    'error' => 'Subclass "' . $request->subclass_name . '" already exists for class "' . $class->class_name . '".'
                ], 400);
            }

            // Handle teacherID - convert empty string to null
            $teacherID = $request->input('teacherID');

            // Log received data BEFORE processing
            \Log::info('Subclass creation request - RAW DATA:', [
                'teacherID_request' => $request->teacherID,
                'teacherID_input' => $teacherID,
                'teacherID_type' => gettype($teacherID),
                'all_input' => $request->all(),
                'all_keys' => array_keys($request->all())
            ]);

            // Handle teacherID - be more explicit
            if ($teacherID === null || $teacherID === '' || $teacherID === '0' || $teacherID === false) {
                $teacherID = null;
                \Log::info('TeacherID set to NULL (empty/zero/false)');
            } else {
                // Convert to integer if it's a string
                $teacherID = (int) $teacherID;
                \Log::info('TeacherID converted to integer:', ['teacherID' => $teacherID]);

                // Verify teacher exists
                $teacher = Teacher::find($teacherID);
                if (!$teacher) {
                    \Log::warning('Teacher not found:', ['teacherID' => $teacherID]);
                    return response()->json([
                        'error' => 'Selected teacher not found.'
                    ], 404);
                }
                \Log::info('Teacher verified:', ['teacherID' => $teacherID, 'teacher_name' => $teacher->first_name . ' ' . $teacher->last_name]);
            }

            // Handle combieID - convert empty string to null
            $combieID = $request->input('combieID');

            if (empty($combieID) || $combieID === '' || $combieID === '0') {
                $combieID = null;
            } else {
                // Convert to integer if it's a string
                $combieID = (int) $combieID;

                // Verify combie exists and belongs to this school
                $schoolID = Session::get('schoolID');
                $combie = Combie::where('combieID', $combieID)
                    ->where('schoolID', $schoolID)
                    ->first();

                if (!$combie) {
                    return response()->json([
                        'error' => 'Selected combination not found or does not belong to this school.'
                    ], 404);
                }
            }

            // Check if user has approval permission - if yes, set status to Active, otherwise Inactive
            $status = $this->hasPermission('approval_class') ? 'Active' : 'Inactive';

            // Handle first_grade and final_grade - convert empty string to null
            $firstGrade = $request->input('first_grade');
            if (empty($firstGrade) || $firstGrade === '') {
                $firstGrade = null;
            }

            $finalGrade = $request->input('final_grade');
            if (empty($finalGrade) || $finalGrade === '') {
                $finalGrade = null;
            }

            $subclassData = [
                'classID' => $request->classID,
                'subclass_name' => $request->subclass_name,
                'first_grade' => $firstGrade,
                'final_grade' => $finalGrade,
                'stream_code' => $stream_code,
                'teacherID' => $teacherID,
                'combieID' => $combieID,
                'status' => $status,
            ];

            \Log::info('Creating subclass with data:', [
                'classID' => $subclassData['classID'],
                'subclass_name' => $subclassData['subclass_name'],
                'stream_code' => $subclassData['stream_code'],
                'teacherID' => $subclassData['teacherID'],
                'teacherID_type' => gettype($subclassData['teacherID']),
                'combieID' => $subclassData['combieID'],
                'combieID_type' => gettype($subclassData['combieID'])
            ]);

            // Create subclass using fillable attributes explicitly
            $subclass = new Subclass();
            $subclass->classID = $subclassData['classID'];
            $subclass->subclass_name = $subclassData['subclass_name'];
            $subclass->stream_code = $subclassData['stream_code'];
            $subclass->teacherID = $subclassData['teacherID'];
            $subclass->combieID = $subclassData['combieID'];
            $subclass->first_grade = $subclassData['first_grade'];
            $subclass->final_grade = $subclassData['final_grade'];
            $subclass->status = $status;
            $subclass->save();

            // Refresh to get latest data from database
            $subclass->refresh();

            \Log::info('Subclass created:', [
                'subclassID' => $subclass->subclassID,
                'teacherID' => $subclass->teacherID,
                'teacherID_raw' => $subclass->getRawOriginal('teacherID'),
                'teacherID_is_null' => is_null($subclass->teacherID),
                'combieID' => $subclass->combieID,
                'combieID_raw' => $subclass->getRawOriginal('combieID'),
                'combieID_is_null' => is_null($subclass->combieID)
            ]);

            return response()->json([
                'success' => 'Subclass "' . $stream_code . '" created successfully!',
                'subclass' => $subclass,
                'debug' => [
                    'teacherID_sent' => $request->teacherID,
                    'teacherID_saved' => $subclass->teacherID,
                    'combieID_sent' => $request->combieID,
                    'combieID_saved' => $subclass->combieID
                ]
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_subclass_students(Request $request, $subclassID)
    {
        try {
            $userType = Session::get('user_type');
            $schoolID = Session::get('schoolID');
            $isCoordinatorView = $request->get('coordinator') === 'true';
            $classID = $request->get('classID');

            // If coordinator view, get students for all subclasses in the main class
            if ($isCoordinatorView && $classID) {
                $teacherID = Session::get('teacherID');
                if (!$teacherID) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Teacher ID not found in session.'
                    ], 400);
                }

                // Verify teacher is coordinator of this main class
                $mainClass = ClassModel::find($classID);
                if (!$mainClass || $mainClass->teacherID != $teacherID || $mainClass->schoolID != $schoolID) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Unauthorized access to this class.'
                    ], 403);
                }

                // Get all subclasses for this main class
                $subclassIDs = Subclass::where('classID', $classID)
                    ->where('status', 'Active')
                    ->pluck('subclassID')
                    ->toArray();

                if (empty($subclassIDs)) {
                    return response()->json([
                        'success' => true,
                        'students' => []
                    ]);
                }

                // Get filter parameters
                $subclassFilter = $request->get('subclassFilter');
                $genderFilter = $request->get('genderFilter');
                
                // Build query
                $studentQuery = Student::whereIn('subclassID', $subclassIDs)
                    ->with(['parent', 'subclass.class']);
                
                // Apply subclass filter if provided
                if ($subclassFilter) {
                    $studentQuery->where('subclassID', $subclassFilter);
                }
                
                // Apply gender filter if provided
                if ($genderFilter) {
                    $studentQuery->where('gender', $genderFilter);
                }
                
                $students = $studentQuery->get();
                
                // Get school type for grade calculation
                $school = \App\Models\School::find($schoolID);
                $schoolType = $school ? $school->school_type : 'Secondary';
                
                // Calculate statistics
                $totalStudents = $students->count();
                $maleCount = $students->where('gender', 'Male')->count();
                $femaleCount = $students->where('gender', 'Female')->count();
                $healthIssuesCount = $students->filter(function($s) {
                    return ($s->is_disabled ?? false) || ($s->has_epilepsy ?? false) || ($s->has_allergies ?? false);
                })->count();
            } else {
                // Regular subclass view
                // Get subclass with class info
                $subclass = Subclass::with('class')->find($subclassID);

                if (!$subclass) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Subclass not found.'
                    ], 404);
                }

                // Check if subclass belongs to this school
                if ($subclass->class->schoolID != $schoolID) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Unauthorized access to this subclass.'
                    ], 403);
                }

                // For teachers, verify they own this subclass or have view_students permission
                if ($userType === 'Teacher') {
                    $teacherID = Session::get('teacherID');
                    if (!$teacherID) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Teacher ID not found in session.'
                        ], 400);
                    }

                    // Check if teacher is the class teacher of this subclass
                    $isClassTeacher = $subclass->teacherID == $teacherID;

                    // Check if teacher has view_students permission
                    $hasPermission = $this->hasPermission('view_students');

                    // Allow access if teacher is class teacher OR has view_students permission
                    if (!$isClassTeacher && !$hasPermission) {
                        return response()->json([
                            'success' => false,
                            'error' => 'You do not have permission to view students in this subclass.'
                        ], 403);
                    }
                } elseif ($userType === 'Admin') {
                    // Admin has access
                } else {
                    // Check permission for other user types
            if (!$this->hasPermission('view_students')) {
                return response()->json([
                            'success' => false,
                    'error' => 'You do not have permission to view students.'
                ], 403);
                    }
            }

                // Get school type for grade calculation
                $school = \App\Models\School::find($schoolID);
                $schoolType = $school ? $school->school_type : 'Secondary';

                $students = Student::where('subclassID', $subclassID)
                    ->with(['parent', 'subclass.class'])
                    ->get();
            }

            $students = $students
                ->map(function($student) use ($schoolID, $schoolType) {
                    $oldSubclassInfo = null;
                    $studentGrade = null;

                    // Get old subclass info if student is transferred
                    if ($student->old_subclassID && $student->status === 'Transferred') {
                        $oldSubclass = Subclass::with('class')->find($student->old_subclassID);
                        if ($oldSubclass) {
                            $oldSubclassInfo = [
                                'class_name' => $oldSubclass->class->class_name ?? 'N/A',
                                'subclass_name' => $oldSubclass->subclass_name ?? 'N/A',
                                'stream_code' => $oldSubclass->stream_code ?? null,
                                'display_name' => $oldSubclass->subclass_name ?? 'N/A' // Use subclass_name only
                            ];

                            // Get student's latest grade/division from old subclass
                            $currentClassName = $oldSubclass->class ? strtolower(preg_replace('/[\s\-]+/', '_', $oldSubclass->class->class_name)) : null;
                            $studentResults = $this->getStudentLatestResults($student->studentID, $schoolID, $schoolType, $currentClassName);
                            if ($studentResults && $studentResults['total_division']) {
                                $studentGrade = $studentResults['total_division'];
                            }
                        }
                    }

                    // Helper function to safely format dates
                    $formatDate = function($date) {
                        if (!$date) return null;
                        if (is_string($date)) {
                            try {
                                return \Carbon\Carbon::parse($date)->format('Y-m-d');
                            } catch (\Exception $e) {
                                return $date; // Return as-is if parsing fails
                            }
                        }
                        if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
                            return $date->format('Y-m-d');
                        }
                        return $date;
                    };

                    $formatDateTime = function($date) {
                        if (!$date) return null;
                        if (is_string($date)) {
                            try {
                                return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
                            } catch (\Exception $e) {
                                return $date; // Return as-is if parsing fails
                            }
                        }
                        if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
                            return $date->format('Y-m-d H:i:s');
                        }
                        return $date;
                    };

                    // Get subclass name and class name for display (for coordinator view)
                    $subclassName = $student->subclass ? ($student->subclass->subclass_name ?? '') : '';
                    $className = $student->subclass && $student->subclass->class ? ($student->subclass->class->class_name ?? '') : '';
                    $subclassDisplay = $className ? ($subclassName ? $className . ' - ' . $subclassName : $className) : ($subclassName ?: 'N/A');
                    $subclassID = $student->subclassID ?? null;
                    
                    // Check if student has health issues
                    $hasHealthIssues = ($student->is_disabled ?? false) || ($student->has_epilepsy ?? false) || ($student->has_allergies ?? false);

                    return [
                        'studentID' => $student->studentID,
                        'admission_number' => $student->admission_number,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'gender' => $student->gender,
                        'date_of_birth' => $formatDate($student->date_of_birth),
                        'admission_date' => $formatDate($student->admission_date),
                        'address' => $student->address,
                        'status' => $student->status,
                        'old_subclassID' => $student->old_subclassID,
                        'old_subclass_info' => $oldSubclassInfo,
                        'student_grade' => $studentGrade,
                        'parent_name' => $student->parent ?
                            $student->parent->first_name . ' ' . $student->parent->last_name : 'Not Assigned',
                        'parent_phone' => $student->parent ? $student->parent->phone : null,
                        'photo' => $student->photo, // Return filename only, frontend will construct full URL
                        'fingerprint_id' => $student->fingerprint_id,
                        'sent_to_device' => $student->sent_to_device ?? false,
                        'device_sent_at' => $formatDateTime($student->device_sent_at),
                        'fingerprint_capture_count' => $student->fingerprint_capture_count ?? 0,
                        'is_disabled' => $student->is_disabled ?? false,
                        'has_epilepsy' => $student->has_epilepsy ?? false,
                        'has_allergies' => $student->has_allergies ?? false,
                        'allergies_details' => $student->allergies_details ?? null,
                        'subclass_name' => $subclassName, // Subclass name only
                        'class_name' => $className, // Class name only
                        'subclass_display' => $subclassDisplay, // Format: "MainClass - Subclass"
                        'subclassID' => $subclassID, // Subclass ID for filtering
                        'has_health_issues' => $hasHealthIssues // For statistics
                    ];
                });

            // Prepare response
            $response = [
                'success' => true,
                'students' => $students
            ];
            
            // Add statistics if coordinator view
            if ($isCoordinatorView && $classID && isset($totalStudents)) {
                $response['statistics'] = [
                    'total_students' => $totalStudents,
                    'male_count' => $maleCount,
                    'female_count' => $femaleCount,
                    'health_issues_count' => $healthIssuesCount
                ];
            }
            
            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_class_subclasses($classID)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Get class and verify it belongs to this school
            $class = ClassModel::where('classID', $classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$class) {
                return response()->json([
                    'error' => 'Class not found or unauthorized access.'
                ], 404);
            }

            // Get all subclasses for this class
            $allSubclasses = Subclass::where('classID', $classID)
                ->with(['teacher', 'combie', 'class'])
                ->get();
            
            // Filter out default subclass if class has only one subclass and it's default
            $subclasses = $allSubclasses->filter(function($subclass) use ($allSubclasses) {
                // If class has only one subclass and it's default (empty name), hide it
                if ($allSubclasses->count() === 1 && trim($subclass->subclass_name) === '') {
                    return false;
                }
                return true;
            })->map(function($subclass) use ($class) {
                $studentCount = DB::table('students')
                    ->where('subclassID', $subclass->subclassID)
                    ->count();
                
                // Get display name: class_name + subclass_name (or just class_name if empty)
                $subclassName = trim($subclass->subclass_name);
                $displayName = empty($subclassName) 
                    ? $class->class_name 
                    : $class->class_name . ' ' . $subclassName;
                
                return [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'display_name' => $displayName,
                    'stream_code' => $subclass->stream_code,
                    'status' => $subclass->status,
                    'teacherID' => $subclass->teacherID,
                    'teacher_name' => $subclass->teacher ? $subclass->teacher->first_name . ' ' . $subclass->teacher->last_name : null,
                    'combieID' => $subclass->combieID,
                    'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                    'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                    'first_grade' => $subclass->first_grade,
                    'final_grade' => $subclass->final_grade,
                    'student_count' => $studentCount,
                    'class_name' => $class->class_name,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'class' => [
                    'classID' => $class->classID,
                    'class_name' => $class->class_name,
                ],
                'subclasses' => $subclasses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_class_grading()
    {
        // Check permission
        if (!$this->hasPermission('view_students')) {
            return response()->json([
                'error' => 'You do not have permission to view class grading.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Get school details for school type
            $school = \App\Models\School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Primary';

            // Get all subclasses with their class details and grade ranges
            $subclasses = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->leftJoin('combies', 'subclasses.combieID', '=', 'combies.combieID')
                ->where('classes.schoolID', $schoolID)
                ->select(
                    'subclasses.subclassID',
                    'subclasses.subclass_name',
                    'subclasses.stream_code',
                    'subclasses.first_grade',
                    'subclasses.final_grade',
                    'classes.class_name',
                    'combies.combie_name',
                    'combies.combie_code'
                )
                ->orderBy('classes.class_name')
                ->orderBy('subclasses.subclass_name')
                ->get()
                ->map(function($subclass) {
                    return [
                        'subclassID' => $subclass->subclassID,
                        'class_name' => $subclass->class_name ?? 'N/A',
                        'subclass_name' => $subclass->subclass_name,
                        'stream_code' => $subclass->stream_code,
                        'first_grade' => $subclass->first_grade,
                        'final_grade' => $subclass->final_grade,
                        'combie_name' => $subclass->combie_name,
                        'combie_code' => $subclass->combie_code,
                    ];
                });

            return response()->json([
                'success' => true,
                'subclasses' => $subclasses,
                'school_type' => $schoolType
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_grade_range(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('edit_subclass')) {
            return response()->json([
                'error' => 'You do not have permission to edit grade ranges.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'subclassID' => 'required|exists:subclasses,subclassID',
                'first_grade' => 'nullable|string|max:50',
                'final_grade' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    $errors = [];
                    foreach ($validator->errors()->toArray() as $field => $messages) {
                        $errors[$field] = $messages[0];
                    }
                    return response()->json(['errors' => $errors], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'School ID not found in session. Please login again.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'School ID not found in session. Please login again.');
            }

            $subclass = Subclass::with('class')->find($request->subclassID);

            if (!$subclass) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Subclass not found.'
                    ], 404);
                }
                return redirect()->back()->with('error', 'Subclass not found.');
            }

            // Check if subclass belongs to this school
            if ($subclass->class->schoolID != $schoolID) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Unauthorized access to this subclass.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized access to this subclass.');
            }

            // Handle first_grade and final_grade - convert empty string to null
            $firstGrade = $request->input('first_grade');
            if (empty($firstGrade) || $firstGrade === '') {
                $firstGrade = null;
            }

            $finalGrade = $request->input('final_grade');
            if (empty($finalGrade) || $finalGrade === '') {
                $finalGrade = null;
            }

            // Update only grade range fields
            $subclass->first_grade = $firstGrade;
            $subclass->final_grade = $finalGrade;
            $subclass->save();

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => 'Grade range updated successfully!',
                    'subclass' => $subclass
                ], 200);
            }

            // Traditional form submission
            return redirect()->route('manageClasses')->with('success', 'Grade range updated successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Database error: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Database error occurred. Please try again.')->withInput();
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred. Please try again.')->withInput();
        }
    }

    public function get_subclasses_for_school()
    {
        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        try {
            // Get school type to include combie info for secondary schools
            $school = \App\Models\School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Get all subclasses with relationships
            $allSubclasses = Subclass::with(['class', 'combie'])
                ->whereHas('class', function($query) use ($schoolID) {
                    $query->where('schoolID', $schoolID);
                })
                ->get()
                ->groupBy(function($subclass) {
                    return $subclass->classID;
                });
            
            // Format subclasses with display names
            $subclasses = collect();
            foreach ($allSubclasses as $classID => $classSubclasses) {
                // If class has only one subclass and it's default (empty name), include it but show only class name
                if ($classSubclasses->count() === 1) {
                    $subclass = $classSubclasses->first();
                    if (trim($subclass->subclass_name) === '') {
                        // Default subclass - show only class name
                        $displayName = $subclass->class->class_name;
                        
                        // Add combie info for secondary schools if exists
                        if ($schoolType === 'Secondary' && $subclass->combie && $subclass->combie->combie_name) {
                            $displayName .= ' (' . $subclass->combie->combie_name . ')';
                        }
                        
                        $subclasses->push((object)[
                            'subclassID' => $subclass->subclassID,
                            'subclass_name' => $subclass->subclass_name,
                            'display_name' => $displayName,
                            'stream_code' => $subclass->stream_code,
                            'class_name' => $subclass->class->class_name,
                            'classID' => $subclass->classID,
                            'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                            'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                        ]);
                    } else {
                        // Single subclass with name
                        $displayName = $subclass->class->class_name . ' ' . $subclass->subclass_name;
                        
                        // Add combie info for secondary schools if exists
                        if ($schoolType === 'Secondary' && $subclass->combie && $subclass->combie->combie_name) {
                            $displayName .= ' (' . $subclass->combie->combie_name . ')';
                        }
                        
                        $subclasses->push((object)[
                            'subclassID' => $subclass->subclassID,
                            'subclass_name' => $subclass->subclass_name,
                            'display_name' => $displayName,
                            'stream_code' => $subclass->stream_code,
                            'class_name' => $subclass->class->class_name,
                            'classID' => $subclass->classID,
                            'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                            'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                        ]);
                    }
                } else {
                    // Multiple subclasses - show all with class_name + subclass_name
                    foreach ($classSubclasses as $subclass) {
                        $subclassName = trim($subclass->subclass_name);
                        $displayName = empty($subclassName) 
                            ? $subclass->class->class_name 
                            : $subclass->class->class_name . ' ' . $subclassName;
                        
                        // Add combie info for secondary schools if exists
                        if ($schoolType === 'Secondary' && $subclass->combie && $subclass->combie->combie_name) {
                            $displayName .= ' (' . $subclass->combie->combie_name . ')';
                        }
                        
                        $subclasses->push((object)[
                            'subclassID' => $subclass->subclassID,
                            'subclass_name' => $subclass->subclass_name,
                            'display_name' => $displayName,
                            'stream_code' => $subclass->stream_code,
                            'class_name' => $subclass->class->class_name,
                            'classID' => $subclass->classID,
                            'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                            'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                        ]);
                    }
                }
            }
            
            // Sort by class_name then subclass_name and convert to array
            $subclasses = $subclasses->sortBy(function($item) {
                return $item->class_name . ' ' . $item->subclass_name;
            })->values()->map(function($subclass) {
                return [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'display_name' => $subclass->display_name,
                    'stream_code' => $subclass->stream_code,
                    'class_name' => $subclass->class_name,
                    'classID' => $subclass->classID ?? null,
                    'combie_name' => $subclass->combie_name,
                    'combie_code' => $subclass->combie_code,
                ];
            });

            return response()->json([
                'success' => true,
                'subclasses' => $subclasses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subclasses: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_eligible_subclasses_for_transfer($studentID)
    {
        try {
            $schoolID = Session::get('schoolID');
            $teacherID = Session::get('teacherID');
            $userType = Session::get('user_type');

            // Check if user is Admin or Teacher
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found'
                ], 400);
            }
            
            // For Admin, teacherID is not required
            if ($userType !== 'Admin' && !$teacherID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher ID not found'
                ], 400);
            }

            // Get student with subclass and class info
            $student = Student::with(['subclass.class', 'subclass.combie'])->find($studentID);
            if (!$student || $student->schoolID != $schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found or unauthorized access'
                ], 404);
            }

            // Get school type
            $school = \App\Models\School::find($schoolID);
            $schoolType = $school ? $school->school_type : 'Secondary';

            // Get latest exam with student_shifting_status (only for school_wide_all_subjects)
            $latestExam = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->where('exam_type', 'school_wide_all_subjects')
                ->orderBy('created_at', 'desc')
                ->first();

            $shiftingStatus = $latestExam ? ($latestExam->student_shifting_status ?? 'none') : 'none';

            // If shifting is not allowed
            if ($shiftingStatus === 'none') {
                return response()->json([
                    'success' => false,
                    'message' => 'Student shifting is not allowed. No exam with shifting enabled found.',
                    'shifting_status' => 'none',
                    'subclasses' => []
                ], 200);
            }

            $currentSubclass = $student->subclass;
            $currentClass = $currentSubclass ? $currentSubclass->class : null;
            // Normalize class name (handle Form_One, FORM ONE, Form One, etc.)
            $currentClassName = $currentClass ? strtolower(preg_replace('/[\s\-]+/', '_', $currentClass->class_name)) : null;
            $currentCombieID = $currentSubclass ? $currentSubclass->combieID : null;

            $eligibleSubclasses = collect();

            if ($shiftingStatus === 'internal') {
                // Internal: Only subclasses within same class level
                // Get all subclasses in the same class
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
                        'combies.combie_name',
                        'combies.combie_code'
                    )
                    ->get();

                // Get student's latest results for grade checking
                $studentResults = $this->getStudentLatestResults($studentID, $schoolID, $schoolType, $currentClassName);

                foreach ($targetSubclasses as $subclass) {
                    $isEligible = false;

                    if ($schoolType === 'Secondary') {
                        // Secondary: Check grade + combie
                        // Check if subclass has grade requirements
                        if ($subclass->first_grade && $subclass->final_grade) {
                            // Check if student's results match grade range
                            if ($studentResults && $this->checkGradeRange($studentResults, $subclass->first_grade, $subclass->final_grade, $schoolType, $currentClassName)) {
                                // Grade matches - check combie match
                                if ($currentCombieID && $subclass->combieID) {
                                    // Both have combie - must match
                                    if ($currentCombieID == $subclass->combieID) {
                                        $isEligible = true;
                                    }
                                } else {
                                    // No combie requirement - allow
                                    $isEligible = true;
                                }
                            }
                        } else {
                            // No grade requirements - check combie match only
                            if ($currentCombieID && $subclass->combieID) {
                                // Both have combie - must match
                                if ($currentCombieID == $subclass->combieID) {
                                    $isEligible = true;
                                }
                            } else {
                                // No combie requirement - allow random shift within class
                                $isEligible = true;
                            }
                        }
                    } else {
                        // Primary: Check grade only (no combie)
                        // Check if subclass has grade requirements
                        if ($subclass->first_grade && $subclass->final_grade) {
                            // Check if student's results match grade range
                            if ($studentResults && $this->checkGradeRange($studentResults, $subclass->first_grade, $subclass->final_grade, $schoolType, $currentClassName)) {
                                $isEligible = true;
                            }
                        } else {
                            // No grade requirements - allow random shift within class
                            $isEligible = true;
                        }
                    }

                    if ($isEligible) {
                        $eligibleSubclasses->push($subclass);
                    }
                }
            } elseif ($shiftingStatus === 'external') {
                // External: Can transfer to different class level
                if ($schoolType === 'Secondary') {
                    // For secondary: Check next class level (e.g., Form Three → Form Four)
                    $classProgression = [
                        'form_one' => 'form_two',
                        'form_two' => 'form_three',
                        'form_three' => 'form_four',
                        'form_four' => 'form_five',
                        'form_five' => 'form_six'
                    ];

                    $nextClassName = isset($classProgression[$currentClassName]) ? $classProgression[$currentClassName] : null;

                    if ($nextClassName) {
                        // Get all subclasses for next class
                        $targetSubclasses = DB::table('subclasses')
                            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                            ->leftJoin('combies', 'subclasses.combieID', '=', 'combies.combieID')
                            ->where('classes.schoolID', $schoolID)
                            ->whereRaw('LOWER(REPLACE(REPLACE(classes.class_name, " ", "_"), "-", "_")) = ?', [$nextClassName])
                            ->select(
                                'subclasses.subclassID',
                                'subclasses.subclass_name',
                                'subclasses.stream_code',
                                'subclasses.first_grade',
                                'subclasses.final_grade',
                                'subclasses.combieID',
                                'classes.class_name',
                                'combies.combie_name',
                                'combies.combie_code'
                            )
                            ->get();

                        // Get student's latest results for grade checking
                        $studentResults = $this->getStudentLatestResults($studentID, $schoolID, $schoolType, $currentClassName);

                        foreach ($targetSubclasses as $subclass) {
                            $isEligible = false;

                            // Check if subclass has grade requirements
                            if ($subclass->first_grade && $subclass->final_grade) {
                                // Check if student's results match grade range
                                if ($studentResults && $this->checkGradeRange($studentResults, $subclass->first_grade, $subclass->final_grade, $schoolType, $currentClassName)) {
                                    // Check combie match if student has combie
                                    if ($currentCombieID && $subclass->combieID) {
                                        if ($currentCombieID == $subclass->combieID) {
                                            $isEligible = true;
                                        }
                                    } else {
                                        $isEligible = true; // If no combie requirement, allow
                                    }
                                }
                            } else {
                                // No grade requirements - check combie match only
                                if ($currentCombieID && $subclass->combieID) {
                                    if ($currentCombieID == $subclass->combieID) {
                                        $isEligible = true;
                                    }
                                } else {
                                    $isEligible = true; // Allow if no combie requirement
                                }
                            }

                            if ($isEligible) {
                                $eligibleSubclasses->push($subclass);
                            }
                        }
                    }
                } else {
                    // Primary: Check next standard (Standard 1 → Standard 2, etc.)
                    // Note: Primary schools don't use combies
                    $standardProgression = [
                        'nursery' => 'baby_class',
                        'baby_class' => 'standard_1',
                        'standard_1' => 'standard_2',
                        'standard_2' => 'standard_3',
                        'standard_3' => 'standard_4',
                        'standard_4' => 'standard_5',
                        'standard_5' => 'standard_6',
                        'standard_6' => 'standard_7'
                    ];

                    $nextClassName = isset($standardProgression[$currentClassName]) ? $standardProgression[$currentClassName] : null;

                    if ($nextClassName) {
                        // Get student's latest results
                        $studentResults = $this->getStudentLatestResults($studentID, $schoolID, $schoolType, $currentClassName);

                        $targetSubclasses = DB::table('subclasses')
                            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                            ->where('classes.schoolID', $schoolID)
                            ->whereRaw('LOWER(REPLACE(REPLACE(classes.class_name, " ", "_"), "-", "_")) = ?', [$nextClassName])
                            ->select(
                                'subclasses.subclassID',
                                'subclasses.subclass_name',
                                'subclasses.stream_code',
                                'subclasses.first_grade',
                                'subclasses.final_grade',
                                'classes.class_name'
                            )
                            ->get();

                        foreach ($targetSubclasses as $subclass) {
                            $isEligible = false;

                            // Primary: Check grade only (no combie checking)
                            // Check if subclass has grade requirements
                            if ($subclass->first_grade && $subclass->final_grade) {
                                // Check if student's results match grade range
                                if ($studentResults && $this->checkGradeRange($studentResults, $subclass->first_grade, $subclass->final_grade, $schoolType, $currentClassName)) {
                                    $isEligible = true;
                                }
                            } else {
                                // No grade requirements - allow transfer to any subclass in next class
                                $isEligible = true;
                            }

                            if ($isEligible) {
                                $eligibleSubclasses->push($subclass);
                            }
                        }
                    }
                }
            }

            // Format response
            $formattedSubclasses = $eligibleSubclasses->map(function($subclass) {
                $displayName = $subclass->class_name . ' ' . ($subclass->stream_code ?? $subclass->subclass_name);
                if (isset($subclass->combie_name)) {
                    $displayName .= ' (' . $subclass->combie_name . ')';
                }
                return [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'stream_code' => $subclass->stream_code,
                    'class_name' => $subclass->class_name,
                    'combie_name' => $subclass->combie_name ?? null,
                    'display_name' => $displayName
                ];
            });

            // If no eligible subclasses but shifting is allowed, still return success
            if (count($formattedSubclasses) === 0 && $shiftingStatus !== 'none') {
                return response()->json([
                    'success' => true,
                    'shifting_status' => $shiftingStatus,
                    'subclasses' => [],
                    'message' => 'No eligible subclasses found for transfer. Student may need to meet grade requirements.'
                ], 200);
            }
            
            return response()->json([
                'success' => true,
                'shifting_status' => $shiftingStatus,
                'subclasses' => $formattedSubclasses,
                'message' => count($formattedSubclasses) > 0 ? 'Eligible subclasses found' : 'No eligible subclasses found for transfer'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch eligible subclasses: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getStudentLatestResults($studentID, $schoolID, $schoolType, $className)
    {
        // Get latest exam results for this student
        $latestExam = DB::table('examinations')
            ->where('schoolID', $schoolID)
            ->where('exam_type', 'school_wide_all_subjects')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestExam) {
            return null;
        }

        // Get student's results for latest exam
        $results = DB::table('results')
            ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
            ->where('results.studentID', $studentID)
            ->where('results.examID', $latestExam->examID)
            ->select('results.marks', 'school_subjects.subject_name')
            ->get();

        if ($results->isEmpty()) {
            return null;
        }

        // Calculate total division/grade
        $subjectsData = [];
        $totalMarks = 0;
        $subjectCount = 0;

        foreach ($results as $result) {
            if ($result->marks !== null && $result->marks !== '') {
                $totalMarks += (float)$result->marks;
                $subjectCount++;
            }
            $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $schoolType, $className);
            $subjectsData[] = [
                'marks' => $result->marks,
                'points' => $gradeOrDivision['points'] ?? null
            ];
        }

        // Calculate total points
        $subjectPoints = array_filter(array_column($subjectsData, 'points'), function($p) { return $p !== null; });
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
        $totalDivision = $this->calculateTotalDivision($totalPoints, $schoolType, $className, $averageMarks);

        return [
            'total_division' => $totalDivision['division'] ?? null,
            'total_points' => $totalPoints,
            'average_marks' => $averageMarks
        ];
    }

    private function checkGradeRange($studentResults, $firstGrade, $finalGrade, $schoolType, $className)
    {
        if (!$studentResults || !$studentResults['total_division']) {
            return false;
        }

        $studentDivision = $studentResults['total_division'];

        if ($schoolType === 'Primary') {
            // Primary: Handle A, B, C, D, E grades
            // Student division is "Division One", "Division Two", etc.
            // Grade range is A, B, C, D, E

            // Map Division to Grade
            $divisionToGrade = [
                'Division One' => 'A',
                'Division Two' => 'B',
                'Division Three' => 'C',
                'Division Four' => 'D',
                'Division Zero' => 'E'
            ];

            $studentGrade = $divisionToGrade[$studentDivision] ?? null;
            if (!$studentGrade) {
                return false;
            }

            // Grade order: A (best) to E (worst)
            $gradeOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5];

            $firstOrder = $gradeOrder[$firstGrade] ?? 999;
            $finalOrder = $gradeOrder[$finalGrade] ?? 999;
            $studentOrder = $gradeOrder[$studentGrade] ?? 999;

            // Check if student's grade is within range (lower order = better grade)
            return $studentOrder >= $firstOrder && $studentOrder <= $finalOrder;
        } else {
            // Secondary: Handle I.7, II.20, etc. divisions
            // Parse division format (I.7, II.20, etc.)
            if (preg_match('/^([IVX0]+)\.(\d+)$/', $studentDivision, $matches)) {
                $studentDivisionNum = (int)$matches[2];
                $studentDivisionLevel = $matches[1];
            } else {
                return false;
            }

            // Parse first and final grade ranges
            if (preg_match('/^([IVX0]+)\.(\d+)$/', $firstGrade, $firstMatches) &&
                preg_match('/^([IVX0]+)\.(\d+)$/', $finalGrade, $finalMatches)) {
                $firstNum = (int)$firstMatches[2];
                $finalNum = (int)$finalMatches[2];
                $firstLevel = $firstMatches[1];
                $finalLevel = $finalMatches[1];

                // Check if student's division number is within range
                if ($studentDivisionLevel === $firstLevel && $studentDivisionLevel === $finalLevel) {
                    // Same division level - check number range
                    return $studentDivisionNum >= $firstNum && $studentDivisionNum <= $finalNum;
                } else {
                    // Different division levels - check if student is within range
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

    public function delete_subclass($subclassID)
    {
        // Check permission
        if (!$this->hasPermission('delete_subclass')) {
            return response()->json([
                'error' => 'You do not have permission to delete subclasses.'
            ], 403);
        }

        try {
            $subclass = Subclass::find($subclassID);

            if (!$subclass) {
                return response()->json(['error' => 'Subclass not found'], 404);
            }

            // Check if subclass has students
            $studentCount = Student::where('subclassID', $subclassID)->count();
            if ($studentCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete subclass. It has ' . $studentCount . ' student(s) assigned.'
                ], 400);
            }

            $subclass->delete();

            return response()->json([
                'success' => 'Subclass deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save_combie(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('create_combie')) {
            return response()->json([
                'error' => 'You do not have permission to create combinations.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'combie_name' => 'required|string|max:100',
                'combie_code' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'status' => 'nullable|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    $errors = [];
                    foreach ($validator->errors()->toArray() as $field => $messages) {
                        $errors[$field] = $messages[0];
                    }
                    return response()->json(['errors' => $errors], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'School ID not found in session. Please login again.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'School ID not found in session. Please login again.');
            }

            // Check if combie with same name already exists for this school
            $existingCombie = Combie::where('schoolID', $schoolID)
                ->where('combie_name', $request->combie_name)
                ->first();

            if ($existingCombie) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Combination "' . $request->combie_name . '" already exists for this school.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Combination "' . $request->combie_name . '" already exists for this school.')->withInput();
            }

            $combie = Combie::create([
                'schoolID' => $schoolID,
                'combie_name' => $request->combie_name,
                'combie_code' => $request->combie_code ?? null,
                'description' => $request->description ?? null,
                'status' => $request->status ?? 'Active',
            ]);

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => 'Combination "' . $combie->combie_name . '" created successfully!',
                    'combie' => $combie
                ], 200);
            }

            // Traditional form submission
            return redirect()->route('manageClasses')->with('success', 'Combination "' . $combie->combie_name . '" created successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Database error: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Database error occurred. Please try again.')->withInput();
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred. Please try again.')->withInput();
        }
    }

    public function update_combie(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('edit_combie')) {
            return response()->json([
                'error' => 'You do not have permission to edit combinations.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'combieID' => 'required|exists:combies,combieID',
                'combie_name' => 'required|string|max:100',
                'combie_code' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'status' => 'nullable|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    $errors = [];
                    foreach ($validator->errors()->toArray() as $field => $messages) {
                        $errors[$field] = $messages[0];
                    }
                    return response()->json(['errors' => $errors], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'School ID not found in session. Please login again.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'School ID not found in session. Please login again.');
            }

            $combie = Combie::find($request->combieID);

            if (!$combie) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Combination not found.'
                    ], 404);
                }
                return redirect()->back()->with('error', 'Combination not found.');
            }

            // Check if combie belongs to this school
            if ($combie->schoolID != $schoolID) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Unauthorized access to this combination.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized access to this combination.');
            }

            // Check if combie with same name already exists for this school (excluding current)
            $existingCombie = Combie::where('schoolID', $schoolID)
                ->where('combie_name', $request->combie_name)
                ->where('combieID', '!=', $request->combieID)
                ->first();

            if ($existingCombie) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Combination "' . $request->combie_name . '" already exists for this school.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Combination "' . $request->combie_name . '" already exists for this school.')->withInput();
            }

            $combie->update([
                'combie_name' => $request->combie_name,
                'combie_code' => $request->combie_code ?? null,
                'description' => $request->description ?? null,
                'status' => $request->status ?? 'Active',
            ]);

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => 'Combination "' . $combie->combie_name . '" updated successfully!',
                    'combie' => $combie
                ], 200);
            }

            // Traditional form submission
            return redirect()->route('manageClasses')->with('success', 'Combination "' . $combie->combie_name . '" updated successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Database error: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Database error occurred. Please try again.')->withInput();
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred. Please try again.')->withInput();
        }
    }

    public function delete_combie($combieID)
    {
        // Check permission
        if (!$this->hasPermission('delete_combie')) {
            return response()->json([
                'error' => 'You do not have permission to delete combinations.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session. Please login again.'
                ], 400);
            }

            $combie = Combie::find($combieID);

            if (!$combie) {
                return response()->json([
                    'error' => 'Combination not found.'
                ], 404);
            }

            // Check if combie belongs to this school
            if ($combie->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access to this combination.'
                ], 403);
            }

            // Check if combie is used in subclasses
            $subclassCount = Subclass::where('combieID', $combieID)->count();
            if ($subclassCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete combination. It is assigned to ' . $subclassCount . ' subclass(es).'
                ], 400);
            }

            $combieName = $combie->combie_name;
            $combie->delete();

            return response()->json([
                'success' => 'Combination "' . $combieName . '" deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate_class(Request $request, $classID)
    {
        // Check permission
        if (!$this->hasPermission('activate_class')) {
            return response()->json([
                'error' => 'You do not have permission to activate classes.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $class = ClassModel::where('classID', $classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$class) {
                return response()->json([
                    'error' => 'Class not found or unauthorized access.'
                ], 404);
            }

            // Toggle status
            $newStatus = $class->status === 'Active' ? 'Inactive' : 'Active';
            $class->status = $newStatus;
            $class->save();

            return response()->json([
                'success' => 'Class "' . $class->class_name . '" has been ' . strtolower($newStatus) . 'd successfully!',
                'status' => $newStatus
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate_subclass(Request $request, $subclassID)
    {
        // Check permission
        if (!$this->hasPermission('activate_class')) {
            return response()->json([
                'error' => 'You do not have permission to activate subclasses.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subclass = Subclass::find($subclassID);

            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            // Check if subclass belongs to this school
            if ($subclass->class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access to this subclass.'
                ], 403);
            }

            // Toggle status
            $newStatus = $subclass->status === 'Active' ? 'Inactive' : 'Active';
            $subclass->status = $newStatus;
            $subclass->save();

            return response()->json([
                'success' => 'Subclass "' . ($subclass->stream_code ?? $subclass->subclass_name) . '" has been ' . strtolower($newStatus) . 'd successfully!',
                'status' => $newStatus
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== ATTENDANCE MANAGEMENT METHODS ====================

    /**
     * Save attendance for students
     */
    public function saveAttendance(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $teacherID = Session::get('teacherID');
            $subclassID = $request->input('subclassID');
            $attendanceDate = $request->input('attendance_date');
            $attendanceData = $request->input('attendance', []);

            if (!$schoolID || !$subclassID || !$attendanceDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameters'
                ], 400);
            }

            // Verify subclass belongs to school
            $subclass = Subclass::where('subclassID', $subclassID)
                ->whereHas('class', function($query) use ($schoolID) {
                    $query->where('schoolID', $schoolID);
                })
                ->first();

            if (!$subclass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subclass not found or unauthorized'
                ], 404);
            }

            $savedCount = 0;
            $errors = [];

            foreach ($attendanceData as $studentID => $data) {
                try {
                    // Check if attendance already exists for this date
                    $existing = Attendance::where('subclassID', $subclassID)
                        ->where('studentID', $studentID)
                        ->where('attendance_date', $attendanceDate)
                        ->first();

                    if ($existing) {
                        // Update existing
                        $existing->status = $data['status'] ?? 'Present';
                        $existing->remark = $data['remark'] ?? null;
                        $existing->teacherID = $teacherID;
                        $existing->save();
                    } else {
                        // Create new
                        Attendance::create([
                            'schoolID' => $schoolID,
                            'subclassID' => $subclassID,
                            'studentID' => $studentID,
                            'teacherID' => $teacherID,
                            'attendance_date' => $attendanceDate,
                            'status' => $data['status'] ?? 'Present',
                            'remark' => $data['remark'] ?? null,
                        ]);
                    }
                    $savedCount++;
                } catch (\Exception $e) {
                    $errors[] = 'Error saving attendance for student ID ' . $studentID . ': ' . $e->getMessage();
                }
            }

            if (count($errors) > 0 && $savedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save attendance: ' . implode(', ', $errors)
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance saved successfully for ' . $savedCount . ' student(s)',
                'saved_count' => $savedCount
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records
     */
    public function getAttendance(Request $request, $attendanceID = null)
    {
        try {
            $schoolID = Session::get('schoolID');

            if ($attendanceID) {
                // Get single attendance record
                $attendance = Attendance::with(['student', 'subclass'])
                    ->where('attendanceID', $attendanceID)
                    ->where('schoolID', $schoolID)
                    ->first();

                if (!$attendance) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Attendance record not found'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'attendance' => [
                        'attendanceID' => $attendance->attendanceID,
                        'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
                        'status' => $attendance->status,
                        'remark' => $attendance->remark,
                        'studentID' => $attendance->studentID,
                        'subclassID' => $attendance->subclassID,
                    ]
                ], 200);
            }

            // Get multiple attendance records
            $subclassID = $request->input('subclassID');
            $date = $request->input('date');
            $status = $request->input('status');

            if (!$subclassID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subclass ID is required'
                ], 400);
            }

            $query = Attendance::join('students', 'attendances.studentID', '=', 'students.studentID')
                ->where('attendances.schoolID', $schoolID)
                ->where('attendances.subclassID', $subclassID)
                ->select(
                    'attendances.*',
                    'students.first_name',
                    'students.middle_name',
                    'students.last_name',
                    'students.admission_number',
                    'students.photo',
                    'students.gender'
                );

            if ($date) {
                $query->where('attendances.attendance_date', $date);
            }

            if ($status) {
                $query->where('attendances.status', $status);
            }

            $attendances = $query->orderBy('attendances.attendance_date', 'desc')
                ->orderBy('students.first_name')
                ->get();

            return response()->json([
                'success' => true,
                'attendances' => $attendances
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance by ID (alias for getAttendance)
     */
    public function getAttendanceById($attendanceID)
    {
        return $this->getAttendance(request(), $attendanceID);
    }

    /**
     * Update attendance
     */
    public function updateAttendance(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $teacherID = Session::get('teacherID');
            $attendanceID = $request->input('attendanceID');

            if (!$attendanceID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance ID is required'
                ], 400);
            }

            $attendance = Attendance::where('attendanceID', $attendanceID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found'
                ], 404);
            }

            $attendance->attendance_date = $request->input('attendance_date', $attendance->attendance_date);
            $attendance->status = $request->input('status', $attendance->status);
            $attendance->remark = $request->input('remark', $attendance->remark);
            $attendance->teacherID = $teacherID;
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete attendance
     */
    public function deleteAttendance($attendanceID)
    {
        try {
            $schoolID = Session::get('schoolID');

            $attendance = Attendance::where('attendanceID', $attendanceID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found'
                ], 404);
            }

            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance overview with statistics
     */
    public function getAttendanceOverview(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $subclassID = $request->input('subclassID');
            $searchType = $request->input('searchType', 'day'); // day, week, month
            $searchDate = $request->input('searchDate');

            if (!$subclassID || !$searchDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subclass ID and date are required'
                ], 400);
            }

            // Get total students in subclass
            $totalStudents = Student::where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->count();

            // Calculate date range based on search type
            $startDate = null;
            $endDate = null;

            switch ($searchType) {
                case 'day':
                    $startDate = $searchDate;
                    $endDate = $searchDate;
                    break;
                case 'week':
                    $date = new \DateTime($searchDate);
                    $date->modify('monday this week');
                    $startDate = $date->format('Y-m-d');
                    $date->modify('sunday this week');
                    $endDate = $date->format('Y-m-d');
                    break;
                case 'month':
                    $date = new \DateTime($searchDate);
                    $date->modify('first day of this month');
                    $startDate = $date->format('Y-m-d');
                    $date->modify('last day of this month');
                    $endDate = $date->format('Y-m-d');
                    break;
            }

            // Get attendance statistics
            $attendances = Attendance::where('subclassID', $subclassID)
                ->where('schoolID', $schoolID)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->get();

            $present = $attendances->where('status', 'Present')->count();
            $absent = $attendances->where('status', 'Absent')->count();
            $late = $attendances->where('status', 'Late')->count();
            $excused = $attendances->where('status', 'Excused')->count();

            // Calculate attendance rate
            $totalRecords = $attendances->count();
            $attendanceRate = $totalStudents > 0 ? round(($present / ($totalStudents * max(1, $totalRecords / max(1, $totalStudents)))) * 100, 2) : 0;

            // Prepare chart data
            $chartLabels = [];
            $chartPresent = [];
            $chartAbsent = [];

            if ($searchType === 'day') {
                $chartLabels = [$searchDate];
                $chartPresent = [$present];
                $chartAbsent = [$absent];
            } else {
                // For week/month, group by date
                $grouped = $attendances->groupBy(function($item) {
                    return $item->attendance_date->format('Y-m-d');
                });

                foreach ($grouped as $date => $items) {
                    $chartLabels[] = $date;
                    $chartPresent[] = $items->where('status', 'Present')->count();
                    $chartAbsent[] = $items->where('status', 'Absent')->count();
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_students' => $totalStudents,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'excused' => $excused,
                    'attendance_rate' => $attendanceRate,
                    'chart_labels' => $chartLabels,
                    'chart_present' => $chartPresent,
                    'chart_absent' => $chartAbsent,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register student to biometric device via API
     * Similar to registerTeacherToBiometricDevice but for students
     */
    private function registerStudentToBiometricDevice($fingerprintId, $studentName)
    {
        try {
            // Direct registration to ZKTeco device using internal service
            $ip = config('zkteco.ip', '192.168.1.108');
            $port = (int) config('zkteco.port', 4370);

            Log::info("ZKTeco Direct: Attempting to register student to device", [
                'fingerprint_id' => $fingerprintId,
                'student_name'   => $studentName,
                'device_ip'      => $ip,
                'device_port'    => $port,
            ]);

            $zkteco = new \App\Services\ZKTecoService($ip, $port);

            // UID and UserID will both use fingerprintId (must be 1–65535)
            $uid = (int) $fingerprintId;
            $name = strtoupper($studentName);

            $result = $zkteco->registerUser($uid, $name, 0, '', '', (string) $uid);

            // If no exception thrown, treat as success
            Log::info("ZKTeco Direct: Registration result", [
                'fingerprint_id' => $fingerprintId,
                'result'         => $result,
            ]);

            return [
                'success' => true,
                'message' => 'User registered to device directly',
                'data'    => [
                    'enroll_id'           => $uid,
                    'device_registered_at'=> now()->format('Y-m-d H:i:s'),
                    'device_ip'           => $ip,
                    'device_port'         => $port,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error("ZKTeco Direct: Exception during registration - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'data'    => [],
            ];
        }
    }

    /**
     * Send existing student to fingerprint device
     * Generates unique fingerprint ID and registers student to biometric device
     */
    public function sendStudentToFingerprint(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:students,studentID'
            ]);

            $student = Student::findOrFail($request->student_id);

            DB::beginTransaction();

            // Generate unique 4-digit fingerprint ID
            // Step 1: Validate fingerprintID doesn't exist in users table
            // Step 2: Validate it doesn't exist in students table
            // Step 3: Generate unique ID
            $fingerprintId = null;
            $oldStudentId = $student->studentID;
            $maxAttempts = 100; // Prevent infinite loop
            $attempts = 0;
            
            do {
                $fingerprintId = (string)rand(1000, 9999);
                $attempts++;
                
                // Check if fingerprintID exists in users table
                $existsInUsers = User::where('fingerprint_id', $fingerprintId)->exists();
                
                // Check if fingerprintID exists in students table
                $existsInStudents = Student::where('fingerprint_id', $fingerprintId)->exists() ||
                                    Student::where('studentID', $fingerprintId)->exists();
                
                // Also check teachers table to avoid conflicts
                $existsInTeachers = Teacher::where('fingerprint_id', $fingerprintId)->exists() ||
                                   Teacher::where('id', (int)$fingerprintId)->exists();
                
                // If ID is unique in all tables, break the loop
                if (!$existsInUsers && !$existsInStudents && !$existsInTeachers) {
                    break;
                }
                
                if ($attempts >= $maxAttempts) {
                    throw new \Exception('Unable to generate unique fingerprint ID after ' . $maxAttempts . ' attempts. Please try again.');
                }
            } while (true);

            // Update student with fingerprint_id
            $student->update([
                'fingerprint_id' => $fingerprintId
            ]);
            
            // Update studentID field to match fingerprintID (same as registration)
            // Temporarily disable foreign key checks to update primary key
            if ($oldStudentId != $fingerprintId) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::statement("UPDATE students SET studentID = ? WHERE studentID = ?", [$fingerprintId, $oldStudentId]);
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                
                // Refresh student model to get new studentID
                $student = Student::find($fingerprintId);
                
                if (!$student) {
                    throw new \Exception('Failed to update student ID. Please try again.');
                }
            }

            // Step 3: Send student's first name and fingerprintID to API
            $studentName = strtoupper($student->first_name); // Use first_name only as per requirement
            Log::info("Sending student to biometric device", [
                'student_id' => $student->studentID,
                'fingerprint_id' => $fingerprintId,
                'student_name' => $studentName
            ]);
            
            $apiResult = $this->registerStudentToBiometricDevice($fingerprintId, $studentName);

            if ($apiResult['success']) {
                $enrollId = $apiResult['data']['enroll_id'] ?? $fingerprintId;
                $deviceRegisteredAt = $apiResult['data']['device_registered_at'] ?? null;
                
                // Update sent_to_device to true and set device_sent_at timestamp
                $student->update([
                    'sent_to_device' => true,
                    'device_sent_at' => now()
                ]);
                
                Log::info("ZKTeco Direct: Student sent successfully - Fingerprint ID: {$fingerprintId}, Enroll ID: {$enrollId}");
                
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Student successfully sent to fingerprint device',
                    'fingerprint_id' => $fingerprintId,
                    'enroll_id' => $enrollId,
                    'device_registered_at' => $deviceRegisteredAt
                ]);
            } else {
                // Even if API fails, fingerprint_id is saved
                Log::error("ZKTeco Direct: Student registration failed - Fingerprint ID: {$fingerprintId}, Error: " . ($apiResult['message'] ?? 'Unknown error'));
                
                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint ID generated but failed to register to device: ' . ($apiResult['message'] ?? 'Unknown error'),
                    'fingerprint_id' => $fingerprintId
                ], 200); // Return 200 because fingerprint_id was saved
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Send Student to Fingerprint Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
