<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Examination;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subclass;
use App\Models\SchoolSubject;
use App\Models\ClassSubject;
use App\Models\GradeDefinition;
use App\Models\ExamPaper;
use App\Models\ExamPaperQuestion;
use App\Models\ExamPaperQuestionMark;
use App\Models\ExamPaperOptionalRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ResultManagementController extends Controller
{
    /**
     * Display result management page
     */
    public function index(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');

        // Allow both Admin and Teacher access
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get school details
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : 'Secondary';

        // Get filter parameters
        $termFilter = $request->get('term', '');
        $yearFilter = $request->get('year', date('Y'));
        $typeFilter = $request->get('type', 'exam'); // 'exam' or 'report'
        $statusFilter = $request->get('status', 'active'); // 'active', 'all', 'history'
        $classFilter = $request->get('class', '');
        $subclassFilter = $request->get('subclass', '');
        $subclassIDParam = $request->get('subclassID', ''); // From classManagement link
        $classIDParam = $request->get('classID', ''); // From coordinator view
        $isCoordinatorView = $request->get('coordinator') === 'true';
        $examFilter = $request->get('examID', ''); // Specific exam filter

        // Check if coordinator view is requested
        $isCoordinatorResultsView = false;
        if ($isCoordinatorView && !empty($classIDParam) && !empty($teacherID)) {
            // Verify teacher is coordinator of this main class
            $mainClass = \App\Models\ClassModel::find($classIDParam);
            if ($mainClass && $mainClass->teacherID == $teacherID && $mainClass->schoolID == $schoolID) {
                // Set default class filter for coordinator view
                $classFilter = $classIDParam;
                $isCoordinatorResultsView = true;
                // Coordinator can only choose subclass, main class is pre-selected and locked
            } else {
                // Coordinator doesn't have access to this class
                if ($userType === 'Teacher') {
                    return redirect()->route('AdmitedClasses', ['coordinator' => 'true'])
                        ->with('error', 'Unauthorized access to this class')
                        ->with('error_type', 'unauthorized_access');
                }
            }
        }

        // If subclassID is provided (from classManagement), verify teacher access and set defaults
        $isTeacherView = false;
        if (!empty($subclassIDParam) && !empty($teacherID) && !$isCoordinatorResultsView) {
            // Verify teacher has access to this subclass
            $subclass = Subclass::with('class')->find($subclassIDParam);
            if ($subclass && $subclass->teacherID == $teacherID) {
                // Set default filters for teacher view
                $subclassFilter = $subclassIDParam;
                $classFilter = $subclass->classID;
                $isTeacherView = true;
            } else {
                // Teacher doesn't have access to this subclass
                if ($userType === 'Teacher') {
                    return redirect()->route('AdmitedClasses')
                        ->with('error', 'Unauthorized access to this class')
                        ->with('error_type', 'unauthorized_access');
                }
            }
        }

        // SECURITY: For teachers, always verify they have access to the selected subclass
        // This prevents teachers from changing subclass via form manipulation or refresh
        if ($userType === 'Teacher' && !empty($teacherID)) {
            // If subclassID param is provided, use it
            if (!empty($subclassIDParam)) {
                $subclass = Subclass::with('class')->find($subclassIDParam);
                if ($subclass && $subclass->teacherID == $teacherID) {
                    $subclassFilter = $subclassIDParam;
                    $classFilter = $subclass->classID;
                    $isTeacherView = true;
                }
            }

            // Priority 2: If subclassFilter is set (from form submission), verify access
            if (!empty($subclassFilter) && !$isTeacherView) {
                $subclass = Subclass::with('class')->find($subclassFilter);
                if (!$subclass) {
                    return redirect()->route('AdmitedClasses')
                        ->with('error', 'Invalid class ID')
                        ->with('error_type', 'invalid_class');
                }

                // Verify teacher is the class teacher of this subclass
                if ($subclass->teacherID != $teacherID) {
                    return redirect()->route('AdmitedClasses')
                        ->with('error', 'You do not have access to view results for this class')
                        ->with('error_type', 'unauthorized_access');
                }

                // Force teacher view mode and lock filters
                $isTeacherView = true;
                $classFilter = $subclass->classID; // Ensure class filter matches subclass
            }

            // If isTeacherView is true, subclassFilter MUST be set
            if ($isTeacherView && empty($subclassFilter)) {
                return redirect()->route('AdmitedClasses')
                    ->with('error', 'Please select a class to view results')
                    ->with('error_type', 'no_subclass_selected');
            }
        }

        // Get available years
        $availableYears = Examination::where('schoolID', $schoolID)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Get classes for filter
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        // Get subclasses for filter (only for selected main class) with display names
        $subclasses = collect();
        $allSubclasses = collect();

        // If coordinator view, get all subclasses for the main class
        if ($isCoordinatorResultsView && $classFilter) {
            $classSubclasses = Subclass::with('class')
                ->where('classID', $classFilter)
                ->where('status', 'Active')
                ->orderBy('subclass_name')
                ->get();

            // Group by classID for consistency with other views
            $allSubclasses = $classSubclasses->groupBy('classID');
        } elseif ($userType === 'Teacher' && !empty($teacherID) && !$isCoordinatorResultsView) {
            // SECURITY: For teachers (class teacher view), only show their assigned subclasses
            if ($classFilter) {
                // Get only teacher's subclasses in the selected class
                $allSubclasses = Subclass::with('class')
                    ->whereHas('class', function($query) use ($schoolID, $classFilter) {
                        $query->where('schoolID', $schoolID)
                              ->where('classID', $classFilter);
                    })
                    ->where('teacherID', $teacherID)
                    ->where('status', 'Active')
                    ->get()
                    ->groupBy('classID');
            } else {
                // Get all teacher's assigned subclasses
                $allSubclasses = Subclass::with('class')
                    ->whereHas('class', function($query) use ($schoolID) {
                        $query->where('schoolID', $schoolID);
                    })
                    ->where('teacherID', $teacherID)
                    ->where('status', 'Active')
                    ->get()
                    ->groupBy('classID');
            }
        } elseif ($classFilter) {
            // Admin: Get subclasses for selected class
            $allSubclasses = Subclass::with('class')
                ->whereHas('class', function($query) use ($schoolID, $classFilter) {
                    $query->where('schoolID', $schoolID)
                          ->where('classID', $classFilter);
                })
                ->where('status', 'Active')
                ->get()
                ->groupBy('classID');
        } else {
            // Admin: If no class selected, get all subclasses
            $allSubclasses = Subclass::with('class')
                ->whereHas('class', function($query) use ($schoolID) {
                    $query->where('schoolID', $schoolID);
                })
                ->where('status', 'Active')
                ->get()
                ->groupBy('classID');
        }

        // Format subclasses with display names
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
                $subclassName = trim($subclass->subclass_name);
                $displayName = $isCoordinatorResultsView
                    ? (empty($subclassName) ? $subclass->class->class_name : $subclass->class->class_name . ' - ' . $subclassName)
                    : $subclass->class->class_name . ' ' . $subclass->subclass_name;
                $subclasses->push((object)[
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'display_name' => $displayName,
                    'class_name' => $subclass->class->class_name,
                ]);
            }
        } else {
            // Multiple subclasses - show all with class_name + subclass_name
            foreach ($classSubclasses as $subclass) {
                $subclassName = trim($subclass->subclass_name);
                $displayName = empty($subclassName)
                    ? $subclass->class->class_name
                    : ($isCoordinatorResultsView
                        ? $subclass->class->class_name . ' - ' . $subclassName
                        : $subclass->class->class_name . ' ' . $subclassName);

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

        // Check if this is a request for subject details (AJAX)
        $getSubjectDetails = $request->get('getSubjectDetails', false);
        $studentIDFilter = $request->get('studentID', '');
        $typeFilterForDetails = $request->get('type', 'exam');

        // Support both exam and term report subject details
        if ($getSubjectDetails && $studentIDFilter && (($typeFilterForDetails === 'exam' && $examFilter) || ($typeFilterForDetails === 'report' && $termFilter && $yearFilter))) {
            // Return JSON response with subject details
            $student = Student::where('studentID', $studentIDFilter)
                ->where('schoolID', $schoolID)
                ->with(['subclass.class', 'oldSubclass.class'])
                ->first();

            if (!$student) {
                return response()->json([
                    'error' => 'Student not found'
                ], 404);
            }

            // Check if exam has ended (for exam type only)
            if ($typeFilterForDetails === 'exam' && $examFilter) {
                $exam = Examination::where('examID', $examFilter)
                    ->where('schoolID', $schoolID)
                    ->first();

                if ($exam) {
                    // Check if exam has ended (for Weekly/Monthly tests, check differently)
                    $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
                    $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');

                    $examHasEnded = false;
                    if (!$isWeeklyTest && !$isMonthlyTest) {
                        try {
                            $today = now()->startOfDay();
                            $endDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
                            $examHasEnded = $endDate < $today;
                        } catch (\Exception $e) {
                            // If date parsing fails, assume exam has ended
                            $examHasEnded = true;
                        }
                    } else {
                        // For Weekly/Monthly tests, they are always ongoing, so allow viewing
                        $examHasEnded = true;
                    }

                    // If exam hasn't ended, don't allow viewing results
                    if (!$examHasEnded) {
                        return response()->json([
                            'error' => "Can't view result for this exam because it's still taken.",
                            'error_type' => 'exam_not_ended'
                        ], 422);
                    }
                }
            }

            // Get results for this student
            if ($typeFilterForDetails === 'report') {
                // For term report: get all exams in the term
                $examinationsQuery = Examination::where('schoolID', $schoolID)
                    ->where('year', $yearFilter)
                    ->where('term', $termFilter)
                    ->where('approval_status', 'Approved')
                    ->orderBy('start_date');
                $examinations = $examinationsQuery->get();
                $examIDs = $examinations->pluck('examID')->toArray();

                $results = Result::where('studentID', $studentIDFilter)
                    ->whereIn('examID', $examIDs)
                    ->whereNotNull('marks')
                    ->where('status', 'allowed')
                    ->with(['classSubject.subject', 'examination'])
                    ->get();
            } else {
                // For exam: get results for specific exam
                $results = Result::where('studentID', $studentIDFilter)
                    ->where('examID', $examFilter)
                    ->whereNotNull('marks')
                    ->where('status', 'allowed')
                    ->with(['classSubject.subject'])
                    ->get();
            }

            // Get class name and classID
            $className = '';
            $classID = null;
            if ($student->subclass && $student->subclass->class) {
                $className = $student->subclass->class->class_name ?? '';
                $classID = $student->subclass->class->classID ?? null;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $className = $student->oldSubclass->class->class_name ?? '';
                $classID = $student->oldSubclass->class->classID ?? null;
            }

            // Process results differently for exam vs term report
            $examsData = []; // Initialize outside if block for use in response
            if ($typeFilterForDetails === 'report') {
                // For term report: group by subject and collect exam results
                $subjectData = [];

                // Get all exams
                $examinationsQuery = Examination::where('schoolID', $schoolID)
                    ->where('year', $yearFilter)
                    ->where('term', $termFilter)
                    ->where('approval_status', 'Approved')
                    ->orderBy('start_date');
                $examinations = $examinationsQuery->get();

                foreach ($examinations as $exam) {
                    $examsData[$exam->examID] = [
                        'examID' => $exam->examID,
                        'exam_name' => $exam->exam_name,
                        'start_date' => $exam->start_date
                    ];
                }

                // Group results by subject
                foreach ($results as $result) {
                    $subjectName = $result->classSubject->subject->subject_name ?? 'N/A';
                    $examID = $result->examID;

                    if (!isset($subjectData[$subjectName])) {
                        $subjectData[$subjectName] = [
                            'subject_name' => $subjectName,
                            'exams' => [],
                            'marks_sum' => 0,
                            'marks_count' => 0
                        ];
                    }

                    if ($result->marks !== null && $result->marks !== '') {
                        $marks = (float)$result->marks;
                        $gradePoints = $this->calculateGradePoints($marks, $schoolType, $className, $classID);
                        $grade = $result->grade ?? $gradePoints['grade'];

                        // Get exam name from $examsData or from result relationship
                        $examName = 'N/A';
                        if (isset($examsData[$examID]) && isset($examsData[$examID]['exam_name'])) {
                            $examName = $examsData[$examID]['exam_name'];
                        } elseif ($result->examination && $result->examination->exam_name) {
                            $examName = $result->examination->exam_name;
                        }

                        $subjectData[$subjectName]['exams'][$examID] = [
                            'marks' => $marks,
                            'grade' => $grade,
                            'exam_name' => $examName
                        ];

                        $subjectData[$subjectName]['marks_sum'] += $marks;
                        $subjectData[$subjectName]['marks_count']++;
                    }
                }

                // Calculate average and overall grade for each subject
                $subjects = [];
                $totalMarks = 0;
                $subjectCount = 0;
                $subjectPoints = [];

                foreach ($subjectData as $subjectName => $data) {
                    $average = $data['marks_count'] > 0 ? $data['marks_sum'] / $data['marks_count'] : 0;

                    // Calculate grade based on average using grade_definitions
                    $overallGrade = null;
                    if ($classID && $average > 0) {
                        $gradeResult = $this->getGradeFromDefinition($average, $classID);
                        $overallGrade = $gradeResult['grade'];
                    } else {
                        // Fallback to old logic
                        if ($average >= 75) $overallGrade = 'A';
                        elseif ($average >= 65) $overallGrade = 'B';
                        elseif ($average >= 45) $overallGrade = 'C';
                        elseif ($average >= 30) $overallGrade = 'D';
                        else $overallGrade = 'F';
                    }

                    $subjects[] = [
                        'subject_name' => $subjectName,
                        'exams' => array_values($data['exams']),
                        'average' => $average,
                        'grade' => $overallGrade
                    ];

                    $totalMarks += $data['marks_sum'];
                    $subjectCount += $data['marks_count'];

                    // Store points for division calculation
                    if ($schoolType === 'Secondary' && $average > 0) {
                        $avgGradePoints = $this->calculateGradePoints($average, $schoolType, $className, $classID);
                        $subjectPoints[] = [
                            'points' => $avgGradePoints['points'] ?? 5,
                            'marks' => $average
                        ];
                    }
                }

                $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            } else {
                // For exam: original logic
                $totalMarks = 0;
                $subjectCount = 0;
                $subjects = [];
                $subjectPoints = [];

                foreach ($results as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $totalMarks += (float)$result->marks;
                        $subjectCount++;
                    }
                    $gradePoints = $this->calculateGradePoints($result->marks, $schoolType, $className, $classID);
                    $subjects[] = [
                        'subject_name' => $result->classSubject->subject->subject_name ?? 'N/A',
                        'marks' => $result->marks,
                        'grade' => $result->grade ?? $gradePoints['grade'],
                    ];

                    if ($schoolType === 'Secondary' && $result->marks !== null && $result->marks !== '') {
                        $subjectPoints[] = [
                            'points' => $gradePoints['points'] ?? 5,
                            'marks' => (float)$result->marks
                        ];
                    }
                }

                $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            }

            // Calculate total points for secondary school (best 7 subjects for O-Level, best 3 for A-Level)
            $totalPoints = 0;
            if ($schoolType === 'Secondary' && !empty($subjectPoints)) {
                $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));

                if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    // O-Level: Best 7 subjects (ascending order of points = best)
                    usort($subjectPoints, function($a, $b) {
                        if ($a['points'] != $b['points']) {
                            return $a['points'] <=> $b['points']; // Ascending (lower points = better)
                        }
                        return $b['marks'] <=> $a['marks']; // If points equal, higher marks = better
                    });
                    $bestSubjects = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
                } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
                    // A-Level: Best 3 subjects (descending order of points = best)
                    usort($subjectPoints, function($a, $b) {
                        if ($a['points'] != $b['points']) {
                            return $b['points'] <=> $a['points']; // Descending (lower points = better, but we sort descending to get best first)
                        }
                        return $b['marks'] <=> $a['marks'];
                    });
                    $bestSubjects = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                } else {
                    $bestSubjects = $subjectPoints;
                }

                // Sum points of best subjects
                foreach ($bestSubjects as $subject) {
                    $totalPoints += $subject['points'];
                }
            }

            // Calculate grade/division
            $gradeDivision = $this->calculateGradeDivision($totalMarks, $averageMarks, $subjectCount, $schoolType, $className, $totalPoints, $classID);

            // Get total students count and position for this student in their class
            // Position should be calculated per class (not overall)
            $totalStudentsCount = 0;
            $studentPosition = 0;

            // Get student's class ID
            $studentClassID = null;
            if ($student->subclass && $student->subclass->class) {
                $studentClassID = $student->subclass->class->classID;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $studentClassID = $student->oldSubclass->class->classID;
            }

            if ($studentClassID) {
                // Get all students in the same class
                $classStudents = Student::where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->where(function($query) use ($studentClassID) {
                        $query->whereHas('subclass', function($q) use ($studentClassID) {
                            $q->where('classID', $studentClassID);
                        })->orWhereHas('oldSubclass', function($q) use ($studentClassID) {
                            $q->where('classID', $studentClassID);
                        });
                    })
                    ->with(['subclass.class', 'oldSubclass.class'])
                    ->get();

                // Get results for all students in this class
                $classStudentsWithResults = [];
                foreach ($classStudents as $classStudent) {
                    if ($typeFilterForDetails === 'report') {
                        // For term report: get all exams in term
                        $examinationsQuery = Examination::where('schoolID', $schoolID)
                            ->where('year', $yearFilter)
                            ->where('term', $termFilter)
                            ->where('approval_status', 'Approved');
                        $examinations = $examinationsQuery->get();
                        $examIDs = $examinations->pluck('examID')->toArray();

                        $classStudentResults = Result::where('studentID', $classStudent->studentID)
                            ->whereIn('examID', $examIDs)
                            ->whereNotNull('marks')
                            ->where('status', 'allowed')
                            ->with(['classSubject.subject'])
                            ->get();
                    } else {
                        // For exam: get results for specific exam
                        $classStudentResults = Result::where('studentID', $classStudent->studentID)
                            ->where('examID', $examFilter)
                            ->whereNotNull('marks')
                            ->where('status', 'allowed')
                            ->with(['classSubject.subject'])
                            ->get();
                    }

                    if ($classStudentResults->isNotEmpty()) {
                        if ($typeFilterForDetails === 'report') {
                            // For term report: calculate average per exam, then overall average
                            $examAverages = [];
                            $examResultsByExam = $classStudentResults->groupBy('examID');

                            foreach ($examResultsByExam as $examID => $examResults) {
                                $examTotalMarks = 0;
                                $examSubjectCount = 0;

                                foreach ($examResults as $examResult) {
                                    if ($examResult->marks !== null && $examResult->marks !== '') {
                                        $examTotalMarks += (float)$examResult->marks;
                                        $examSubjectCount++;
                                    }
                                }

                                if ($examSubjectCount > 0) {
                                    $examAverages[] = $examTotalMarks / $examSubjectCount;
                                }
                            }

                            // Overall average is average of exam averages
                            $overallAverage = count($examAverages) > 0 ? array_sum($examAverages) / count($examAverages) : 0;

                            $classStudentsWithResults[] = [
                                'student' => $classStudent,
                                'average_marks' => $overallAverage,
                                'total_marks' => 0, // Not used for term report sorting
                                'total_points' => 0 // Not used for term report sorting
                            ];
                        } else {
                            // For exam: calculate total marks and points
                            $classStudentTotalMarks = 0;
                            $classStudentSubjectCount = 0;
                            $classStudentSubjectPoints = [];

                            foreach ($classStudentResults as $classResult) {
                                if ($classResult->marks !== null && $classResult->marks !== '') {
                                    $classStudentTotalMarks += (float)$classResult->marks;
                                    $classStudentSubjectCount++;
                                }

                                if ($schoolType === 'Secondary' && $classResult->marks !== null && $classResult->marks !== '') {
                                    // Get classID for this student
                                    $studentClassID = null;
                                    if ($classStudent->subclass && $classStudent->subclass->class) {
                                        $studentClassID = $classStudent->subclass->class->classID ?? null;
                                    } elseif ($classStudent->oldSubclass && $classStudent->oldSubclass->class) {
                                        $studentClassID = $classStudent->oldSubclass->class->classID ?? null;
                                    }
                                    $classGradePoints = $this->calculateGradePoints($classResult->marks, $schoolType, $className, $studentClassID);
                                    $classStudentSubjectPoints[] = $classGradePoints['points'] ?? 5;
                                }
                            }

                            // Calculate total points for secondary
                            $classStudentTotalPoints = 0;
                            if ($schoolType === 'Secondary' && !empty($classStudentSubjectPoints)) {
                                $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));
                                if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                                    // O-Level: Best 7
                                    sort($classStudentSubjectPoints);
                                    $bestSubjects = array_slice($classStudentSubjectPoints, 0, min(7, count($classStudentSubjectPoints)));
                                    $classStudentTotalPoints = array_sum($bestSubjects);
                                } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
                                    // A-Level: Best 3
                                    rsort($classStudentSubjectPoints);
                                    $bestSubjects = array_slice($classStudentSubjectPoints, 0, min(3, count($classStudentSubjectPoints)));
                                    $classStudentTotalPoints = array_sum($bestSubjects);
                                }
                            }

                            $classStudentsWithResults[] = [
                                'student' => $classStudent,
                                'total_marks' => $classStudentTotalMarks,
                                'total_points' => $classStudentTotalPoints,
                                'best_seven_total_marks' => 0
                            ];
                        }
                    }
                }

                $totalStudentsCount = count($classStudentsWithResults);

                // Sort students by performance (same logic as in frontend)
                if ($typeFilterForDetails === 'report') {
                    // For term report: sort by average (descending for primary, by grade then average for secondary)
                    if ($schoolType === 'Secondary') {
                        usort($classStudentsWithResults, function($a, $b) {
                            // Sort by grade order (A is best), then by average
                            $gradeOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6];
                            // Calculate grade for average using grade_definitions
                            $avgA = $a['average_marks'] ?? 0;
                            $avgB = $b['average_marks'] ?? 0;

                            // Get classID for students
                            $classIDA = null;
                            $classIDB = null;
                            if (isset($a['student'])) {
                                if ($a['student']->subclass && $a['student']->subclass->class) {
                                    $classIDA = $a['student']->subclass->class->classID ?? null;
                                } elseif ($a['student']->oldSubclass && $a['student']->oldSubclass->class) {
                                    $classIDA = $a['student']->oldSubclass->class->classID ?? null;
                                }
                            }
                            if (isset($b['student'])) {
                                if ($b['student']->subclass && $b['student']->subclass->class) {
                                    $classIDB = $b['student']->subclass->class->classID ?? null;
                                } elseif ($b['student']->oldSubclass && $b['student']->oldSubclass->class) {
                                    $classIDB = $b['student']->oldSubclass->class->classID ?? null;
                                }
                            }

                            $gradeResultA = $classIDA ? $this->getGradeFromDefinition($avgA, $classIDA) : null;
                            $gradeResultB = $classIDB ? $this->getGradeFromDefinition($avgB, $classIDB) : null;
                            $gradeA = $gradeResultA['grade'] ?? (($avgA >= 75) ? 'A' : (($avgA >= 65) ? 'B' : (($avgA >= 45) ? 'C' : (($avgA >= 30) ? 'D' : 'F'))));
                            $gradeB = $gradeResultB['grade'] ?? (($avgB >= 75) ? 'A' : (($avgB >= 65) ? 'B' : (($avgB >= 45) ? 'C' : (($avgB >= 30) ? 'D' : 'F'))));
                            $orderA = $gradeOrder[$gradeA] ?? 999;
                            $orderB = $gradeOrder[$gradeB] ?? 999;

                            if ($orderA != $orderB) {
                                return $orderA <=> $orderB; // Lower number = better grade
                            }
                            return $b['average_marks'] <=> $a['average_marks']; // Higher average = better
                        });
                    } else {
                        // Primary: Sort by average descending
                        usort($classStudentsWithResults, function($a, $b) {
                            return $b['average_marks'] <=> $a['average_marks'];
                        });
                    }
                } else {
                    // For exam: original sorting logic
                    if ($schoolType === 'Secondary') {
                        usort($classStudentsWithResults, function($a, $b) {
                            // First sort by total points (ascending - lower is better)
                            if ($a['total_points'] != $b['total_points']) {
                                return $a['total_points'] <=> $b['total_points'];
                            }
                            // If points are equal, sort by total marks descending (higher marks = better)
                            return $b['total_marks'] <=> $a['total_marks'];
                        });
                    } else {
                        // Primary: Sort by total marks descending
                        usort($classStudentsWithResults, function($a, $b) {
                            return $b['total_marks'] <=> $a['total_marks'];
                        });
                    }
                }

                // Find position of current student
                foreach ($classStudentsWithResults as $index => $classStudentData) {
                    if ($classStudentData['student']->studentID == $studentIDFilter) {
                        $studentPosition = $index + 1;
                        break;
                    }
                }
            }

            // Get student photo path or placeholder
            $studentPhoto = null;
            if ($student->photo) {
                $studentPhoto = asset('userImages/' . $student->photo);
            } else {
                // Use placeholder based on gender
                $placeholderPath = $student->gender === 'Female'
                    ? asset('placeholder/female.png')
                    : asset('placeholder/male.png');
                $studentPhoto = $placeholderPath;
            }

            $responseData = [
                'subjects' => $subjects,
                'student' => [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'gender' => $student->gender,
                    'photo' => $studentPhoto
                ],
                'totalMarks' => $totalMarks,
                'averageMarks' => $averageMarks,
                'subjectCount' => $subjectCount,
                'grade' => $gradeDivision['grade'] ?? null,
                'division' => $gradeDivision['division'] ?? null,
                'totalStudentsCount' => $totalStudentsCount,
                'position' => $studentPosition
            ];

            // For term report, include exams data
            if ($typeFilterForDetails === 'report') {
                // Use the exams we already fetched earlier
                $responseData['exams'] = array_values($examsData);
                $responseData['term'] = $termFilter;
                $responseData['year'] = $yearFilter;
            }

            return response()->json($responseData);
        }

        // Get available exams for the selected term and year
        $availableExams = collect();
        if ($termFilter && $yearFilter) {
            $availableExams = Examination::where('schoolID', $schoolID)
                ->where('year', $yearFilter)
                ->where('term', $termFilter)
                ->where('approval_status', 'Approved')
                ->orderBy('start_date')
                ->get();
        }

        // Check result approvals if exam filter is selected
        if ($examFilter && $typeFilter === 'exam') {
            $exam = Examination::where('examID', $examFilter)
                ->where('schoolID', $schoolID)
                ->first();

            if ($exam) {
                // Check if exam has ended (for Weekly/Monthly tests, check differently)
                $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
                $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');

                $examHasEnded = false;
                if (!$isWeeklyTest && !$isMonthlyTest) {
                    try {
                        $today = now()->startOfDay();
                        $endDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
                        $examHasEnded = $endDate < $today;
                    } catch (\Exception $e) {
                        // If date parsing fails, assume exam has ended
                        $examHasEnded = true;
                    }
                } else {
                    // For Weekly/Monthly tests, they are always ongoing, so allow viewing
                    $examHasEnded = true;
                }

                // If exam hasn't ended, don't allow viewing results
                if (!$examHasEnded) {
                    return view('Admin.result_management', [
                        'error' => "Can't view result for this exam because it's still taken.",
                        'error_type' => 'exam_not_ended',
                        'availableYears' => $availableYears,
                        'classes' => $classes,
                        'subclasses' => $subclasses,
                        'availableExams' => $availableExams,
                        'schoolSubjects' => SchoolSubject::where('schoolID', $schoolID)
                            ->where('status', 'Active')
                            ->orderBy('subject_name')
                            ->get(),
                            'students' => collect(),
                        'filters' => [
                            'term' => $termFilter,
                            'year' => $yearFilter,
                            'type' => $typeFilter,
                            'status' => $statusFilter,
                            'class' => $classFilter,
                            'subclass' => $subclassFilter,
                            'examID' => $examFilter,
                        ],
                        'isTeacherView' => $isTeacherView,
                        'schoolType' => $schoolType,
                        'resultsData' => [],
                    ]);
                }

                // Get all result approvals for this exam
                $resultApprovals = ResultApproval::where('examID', $examFilter)
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
                            // Use accessor if available
                            if ($role && method_exists($role, 'getNameAttribute')) {
                                $roleName = $role->name ?? 'Unknown Role';
                            }

                            return view('Admin.result_management', [
                                'error' => "Result unavailable. Wait approval of {$roleName}.",
                                'error_type' => 'approval_pending',
                                'availableYears' => $availableYears,
                                'classes' => $classes,
                                'subclasses' => $subclasses,
                                'availableExams' => $availableExams,
                                'schoolSubjects' => SchoolSubject::where('schoolID', $schoolID)
                                    ->where('status', 'Active')
                                    ->orderBy('subject_name')
                                    ->get(),
                                'students' => collect(),
                                'filters' => [
                                    'term' => $termFilter,
                                    'year' => $yearFilter,
                                    'type' => $typeFilter,
                                    'status' => $statusFilter,
                                    'class' => $classFilter,
                                    'subclass' => $subclassFilter,
                                    'examID' => $examFilter,
                                ],
                                'isTeacherView' => $isTeacherView,
                                'schoolType' => $schoolType,
                                'resultsData' => [],
                            ]);
                        } else {
                            // Check if any approval was rejected
                            $rejectedApproval = $resultApprovals->firstWhere('status', 'rejected');
                            if ($rejectedApproval) {
                                $role = Role::find($rejectedApproval->role_id);
                                $roleName = $role ? ($role->name ?? $role->role_name ?? 'Unknown Role') : 'Unknown Role';
                                // Use accessor if available
                                if ($role && method_exists($role, 'getNameAttribute')) {
                                    $roleName = $role->name ?? 'Unknown Role';
                                }

                                return view('Admin.result_management', [
                                    'error' => "Result unavailable. Approval was rejected by {$roleName}.",
                                    'error_type' => 'approval_rejected',
                                    'availableYears' => $availableYears,
                                    'classes' => $classes,
                                    'subclasses' => $subclasses,
                                    'availableExams' => $availableExams,
                                    'schoolSubjects' => SchoolSubject::where('schoolID', $schoolID)
                                        ->where('status', 'Active')
                                        ->orderBy('subject_name')
                                        ->get(),
                                        'students' => collect(),
                                    'filters' => [
                                        'term' => $termFilter,
                                        'year' => $yearFilter,
                                        'type' => $typeFilter,
                                        'status' => $statusFilter,
                                        'class' => $classFilter,
                                        'subclass' => $subclassFilter,
                                        'examID' => $examFilter,
                                    ],
                                    'isTeacherView' => $isTeacherView,
                                    'schoolType' => $schoolType,
                                    'resultsData' => [],
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Build query for students based on status filter
        $studentQuery = Student::where('schoolID', $schoolID);

        if ($statusFilter === 'active') {
            $studentQuery->where('status', 'Active');
        } elseif ($statusFilter === 'history') {
            // Students who have old_subclassID (were shifted) - show their history
            $studentQuery->whereNotNull('old_subclassID');
        }
        // 'all' includes all students

        // SECURITY: For teachers, ensure they can only access their assigned subclass
        if ($userType === 'Teacher' && !empty($teacherID)) {
            // If teacher view is active (from classManagement), MUST have subclassFilter set
            if ($isTeacherView) {
                if (empty($subclassFilter)) {
                    // If isTeacherView is true but no subclassFilter, redirect back
                    return redirect()->route('AdmitedClasses')
                        ->with('error', 'Please select a class to view results')
                        ->with('error_type', 'no_subclass_selected');
                }

                // Verify teacher has access to this subclass
                $subclass = Subclass::find($subclassFilter);
                if (!$subclass || $subclass->teacherID != $teacherID) {
                    return redirect()->route('AdmitedClasses')
                        ->with('error', 'You do not have access to view results for this class')
                        ->with('error_type', 'unauthorized_access');
                }

                // ALWAYS filter by the specific subclass - no exceptions
                $studentQuery->where('subclassID', $subclassFilter);
            } elseif ($subclassFilter) {
                // If subclassFilter is set but not teacher view, verify access
                $subclass = Subclass::find($subclassFilter);
                if (!$subclass || $subclass->teacherID != $teacherID) {
                    return redirect()->route('AdmitedClasses')
                        ->with('error', 'You do not have access to view results for this class')
                        ->with('error_type', 'unauthorized_access');
                }
                $studentQuery->where('subclassID', $subclassFilter);
            } else {
                // If no subclass selected and not teacher view, show nothing
                $studentQuery->whereRaw('1 = 0'); // Return no students
            }
        } elseif ($subclassFilter) {
            // Admin or non-teacher: filter by subclass if selected
            $studentQuery->where('subclassID', $subclassFilter);
        } elseif ($classFilter) {
            // Filter by main class if subclass not selected
            // Check both current subclass and old subclass (for history)
            $studentQuery->where(function($query) use ($classFilter) {
                $query->whereHas('subclass', function($q) use ($classFilter) {
                    $q->where('classID', $classFilter);
                })->orWhereHas('oldSubclass', function($q) use ($classFilter) {
                    $q->where('classID', $classFilter);
                });
            });
        }


        $students = $studentQuery->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
            ->orderBy('first_name')
            ->get();

        // Get results data
        $resultsData = [];
        $debugInfo = [];

        // Only process if we have term and year (required for results)
        if ($termFilter && $yearFilter) {
            if ($typeFilter === 'exam') {
                // Get exam results
                $resultsData = $this->getExamResults($students, $termFilter, $yearFilter, $schoolType, $examFilter);
                
                // Debug: Check why results might be empty
                if (empty($resultsData) && $students->count() > 0) {
                    // Check if exam exists and is approved
                    if ($examFilter) {
                        $exam = Examination::where('examID', $examFilter)
                            ->where('schoolID', $schoolID)
                            ->first();
                        if (!$exam) {
                            $debugInfo[] = "Exam with ID {$examFilter} not found.";
                        } elseif ($exam->approval_status !== 'Approved') {
                            $debugInfo[] = "Exam '{$exam->exam_name}' is not approved yet.";
                        } else {
                            // Check if exam has ended
                            $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
                            $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');
                            if (!$isWeeklyTest && !$isMonthlyTest) {
                                try {
                                    $today = now()->startOfDay();
                                    $endDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
                                    if ($endDate >= $today) {
                                        $debugInfo[] = "Exam '{$exam->exam_name}' has not ended yet (ends on {$exam->end_date}).";
                                    }
                                } catch (\Exception $e) {
                                    // Date parsing failed
                                }
                            }
                        }
                    }
                    
                    // Check if students have results but with wrong status or no marks
                    $studentIDs = $students->pluck('studentID')->toArray();
                    $totalResults = \App\Models\Result::whereIn('studentID', $studentIDs);
                    if ($examFilter) {
                        $totalResults->where('examID', $examFilter);
                    } else {
                        $examsQuery = Examination::where('schoolID', $schoolID)
                            ->where('year', $yearFilter)
                            ->where('term', $termFilter);
                        $examIDs = $examsQuery->pluck('examID')->toArray();
                        $totalResults->whereIn('examID', $examIDs);
                    }
                    
                    $totalCount = $totalResults->count();
                    $allowedCount = (clone $totalResults)->where('status', 'allowed')->count();
                    $withMarksCount = (clone $totalResults)->whereNotNull('marks')->count();
                    $allowedWithMarksCount = (clone $totalResults)->where('status', 'allowed')->whereNotNull('marks')->count();
                    
                    if ($totalCount > 0) {
                        $notAllowedCount = $totalCount - $allowedCount;
                        if ($allowedCount < $totalCount) {
                            if ($notAllowedCount > 0) {
                                $debugInfo[] = "Found {$totalCount} results total. {$notAllowedCount} results have status 'not_allowed' and need to be allowed first. Only results with status 'allowed' can be viewed.";
                            } else {
                                $debugInfo[] = "Found {$totalCount} results, but only {$allowedCount} have status 'allowed'. Results need to be allowed first.";
                            }
                        }
                        if ($withMarksCount < $totalCount) {
                            $debugInfo[] = "Found {$totalCount} results, but only {$withMarksCount} have marks entered.";
                        }
                        if ($allowedWithMarksCount < $totalCount) {
                            $debugInfo[] = "Found {$totalCount} results, but only {$allowedWithMarksCount} have both status 'allowed' and marks entered. To view results, you need to change the status from 'not_allowed' to 'allowed'.";
                        }
                    } else {
                        $debugInfo[] = "No results found in database for selected students and exam.";
                    }
                }
            } else {
                // Get report (term average)
                $resultsData = $this->getTermReport($students, $termFilter, $yearFilter, $schoolType);
                
                // Debug: Check why term report might be empty
                if (empty($resultsData) && $students->count() > 0) {
                    $examsQuery = Examination::where('schoolID', $schoolID)
                        ->where('year', $yearFilter)
                        ->where('term', $termFilter)
                        ->where('approval_status', 'Approved');
                    $exams = $examsQuery->get();
                    
                    if ($exams->isEmpty()) {
                        $debugInfo[] = "No approved exams found for {$termFilter} term, {$yearFilter}.";
                    } else {
                        $endedExams = $exams->filter(function($exam) {
                            $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
                            $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');
                            if ($isWeeklyTest || $isMonthlyTest) {
                                return true;
                            }
                            try {
                                $today = now()->startOfDay();
                                $endDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
                                return $endDate < $today;
                            } catch (\Exception $e) {
                                return true;
                            }
                        });
                        
                        if ($endedExams->isEmpty()) {
                            $debugInfo[] = "All exams for {$termFilter} term, {$yearFilter} have not ended yet.";
                        } else {
                            $examIDs = $endedExams->pluck('examID')->toArray();
                            $studentIDs = $students->pluck('studentID')->toArray();
                            $totalResults = \App\Models\Result::whereIn('studentID', $studentIDs)
                                ->whereIn('examID', $examIDs)
                                ->count();
                            
                            if ($totalResults == 0) {
                                $debugInfo[] = "No results found in database for selected students in {$termFilter} term, {$yearFilter}.";
                            } else {
                                $allowedCount = \App\Models\Result::whereIn('studentID', $studentIDs)
                                    ->whereIn('examID', $examIDs)
                                    ->where('status', 'allowed')
                                    ->count();
                                $notAllowedCount = $totalResults - $allowedCount;
                                
                                $allowedWithMarksCount = \App\Models\Result::whereIn('studentID', $studentIDs)
                                    ->whereIn('examID', $examIDs)
                                    ->where('status', 'allowed')
                                    ->whereNotNull('marks')
                                    ->count();
                                
                                if ($allowedWithMarksCount < $totalResults) {
                                    if ($notAllowedCount > 0) {
                                        $debugInfo[] = "Found {$totalResults} results total. {$notAllowedCount} results have status 'not_allowed' and need to be changed to 'allowed' first. Go to Manage Examinations to update results status.";
                                    } else {
                                        $debugInfo[] = "Found {$totalResults} results, but only {$allowedWithMarksCount} have status 'allowed' and marks entered.";
                                    }
                                }
                            }
                        }
                    }
                }
            }


            // Sort students by grade (best first) - only for term report
            if ($typeFilter === 'report' && !empty($resultsData)) {
                // Create a collection of students with their results for sorting
                $studentsWithResults = $students->filter(function($student) use ($resultsData) {
                    return isset($resultsData[$student->studentID]);
                })->map(function($student) use ($resultsData) {
                    return [
                        'student' => $student,
                        'result' => $resultsData[$student->studentID]
                    ];
                });

                // Sort by grade (A is best, then B, C, D, F)
                $gradeOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6];
                $studentsWithResults = $studentsWithResults->sortBy(function($item) use ($gradeOrder, $schoolType) {
                    $grade = $item['result']['grade'] ?? 'F';
                    $order = $gradeOrder[strtoupper($grade)] ?? 999;

                    // For secondary, also consider division if grade is not available
                    if ($schoolType === 'Secondary' && ($order === 999 || !isset($item['result']['grade']))) {
                        $division = $item['result']['division'] ?? '';
                        // Extract division number (e.g., I.7 -> 7, II.18 -> 18)
                        if (preg_match('/^([IVX]+)\.(\d+)$/', $division, $matches)) {
                            $order = (int)$matches[2]; // Lower points = better
                        }
                    }

                    return $order;
                });

                // Rebuild students collection in sorted order
                $sortedStudents = collect();
                foreach ($studentsWithResults as $item) {
                    $sortedStudents->push($item['student']);
                }
                $students = $sortedStudents;
            }
        }

        return view('Admin.result_management', [
            'school' => $school,
            'schoolType' => $schoolType,
            'students' => $students,
            'resultsData' => $resultsData,
            'classes' => $classes,
            'subclasses' => $subclasses,
            'availableYears' => $availableYears,
            'filters' => [
                'term' => $termFilter,
                'year' => $yearFilter,
                'type' => $typeFilter,
                'status' => $statusFilter,
                'class' => $classFilter,
                'subclass' => $subclassFilter,
                'examID' => $examFilter,
            ],
            'availableExams' => $availableExams,
            'schoolSubjects' => SchoolSubject::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('subject_name')
                ->get(),
            'isTeacherView' => $isTeacherView, // Pass teacher view flag
            'isCoordinatorResultsView' => $isCoordinatorResultsView ?? false,
            'debugInfo' => $debugInfo ?? [],
            'isCoordinatorResultsView' => $isCoordinatorResultsView, // Pass coordinator results view flag
            'user_type' => $userType, // Pass user type for navigation
            'error' => null, // No error if we reach here
        ]);
    }

    public function subjectAnalysis(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (! $schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $year = $request->get('year', '');
        $term = $request->get('term', '');
        $examID = $request->get('examID', '');
        $classID = $request->get('classID', '');
        $subclassID = $request->get('subclassID', '');
        $subjectID = $request->get('subjectID', '');
        $allSubclasses = $request->get('all_subclasses', '') === '1';

        $availableYears = Examination::where('schoolID', $schoolID)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        $subjects = SchoolSubject::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('subject_name')
            ->get();

        $examsQuery = Examination::where('schoolID', $schoolID);
        if ($year) {
            $examsQuery->where('year', $year);
        }
        if ($term) {
            $examsQuery->where('term', $term);
        }
        $exams = $examsQuery->orderBy('start_date', 'desc')->get();

        $analysisData = [];
        if ($examID) {
            $classSubjectsQuery = ClassSubject::with(['subject', 'class', 'subclass'])
                ->where('status', 'Active');

            if ($classID) {
                $classSubjectsQuery->where('classID', $classID);
            }

            if ($subclassID && ! $allSubclasses) {
                $classSubjectsQuery->where('subclassID', $subclassID);
            }

            if ($subjectID) {
                $classSubjectsQuery->where('subjectID', $subjectID);
            }

            $classSubjects = $classSubjectsQuery->get();

            foreach ($classSubjects as $classSubject) {
                $classSubjectID = $classSubject->class_subjectID;
                $subjectName = $classSubject->subject->subject_name ?? 'N/A';
                $className = $classSubject->class->class_name ?? 'N/A';
                $subclassName = $classSubject->subclass->subclass_name ?? '';
                $classDisplay = trim($className.' '.$subclassName);

                $results = Result::with(['student'])
                    ->where('examID', $examID)
                    ->where('class_subjectID', $classSubjectID)
                    ->get();

                $studentsQuery = Student::where('status', 'Active');
                if ($classSubject->subclassID) {
                    $studentsQuery->where('subclassID', $classSubject->subclassID);
                } elseif ($classSubject->classID) {
                    $subclassIds = Subclass::where('classID', $classSubject->classID)
                        ->pluck('subclassID')
                        ->toArray();
                    $studentsQuery->whereIn('subclassID', $subclassIds);
                }
                $students = $studentsQuery->get();

                $resultsByStudent = $results->keyBy('studentID');
                $resultRows = $students->map(function ($student) use ($resultsByStudent) {
                    $result = $resultsByStudent->get($student->studentID);
                    return [
                        'student' => $student,
                        'marks' => $result ? $result->marks : null,
                        'grade' => $result ? $result->grade : null,
                        'remark' => $result ? $result->remark : null,
                        'result_id' => $result ? $result->resultID : null,
                    ];
                });

                $examPaper = ExamPaper::where('examID', $examID)
                    ->where('class_subjectID', $classSubjectID)
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $questions = collect();
                $optionalRanges = collect();
                $questionStats = [];
                $studentQuestionMarks = [];
                $bestQuestion = null;
                $worstQuestion = null;

                if ($examPaper) {
                    $questions = ExamPaperQuestion::where('exam_paperID', $examPaper->exam_paperID)
                        ->orderBy('question_number')
                        ->get();
                    $optionalRanges = ExamPaperOptionalRange::where('exam_paperID', $examPaper->exam_paperID)
                        ->orderBy('range_number')
                        ->get();

                    $marks = ExamPaperQuestionMark::where('examID', $examID)
                        ->where('class_subjectID', $classSubjectID)
                        ->get();

                    foreach ($marks as $mark) {
                        $studentQuestionMarks[$mark->studentID][$mark->exam_paper_questionID] = $mark->marks;
                    }

                    foreach ($questions as $question) {
                        $questionMarks = $marks->where('exam_paper_questionID', $question->exam_paper_questionID)
                            ->pluck('marks')
                            ->filter(function ($value) {
                                return $value !== null;
                            });
                        $avg = $questionMarks->count() > 0 ? $questionMarks->avg() : 0;
                        $percent = $question->marks > 0 ? round(($avg / $question->marks) * 100, 1) : 0;

                        $questionStats[] = [
                            'question' => $question,
                            'average' => round($avg, 2),
                            'percent' => $percent,
                        ];
                    }

                    if (!empty($questionStats)) {
                        $bestQuestion = collect($questionStats)->sortByDesc('percent')->first();
                        $worstQuestion = collect($questionStats)->sortBy('percent')->first();
                    }
                }

                $analysisData[] = [
                    'class_subjectID' => $classSubjectID,
                    'subject_name' => $subjectName,
                    'class_display' => $classDisplay,
                    'results' => $results,
                    'result_rows' => $resultRows,
                    'questions' => $questions,
                    'optional_ranges' => $optionalRanges,
                    'question_stats' => $questionStats,
                    'best_question' => $bestQuestion,
                    'worst_question' => $worstQuestion,
                    'student_question_marks' => $studentQuestionMarks,
                ];
            }
        }

        $groupedAnalysis = collect($analysisData)->groupBy('class_display');

        return view('Admin.subject_analysis', compact(
            'availableYears',
            'classes',
            'subjects',
            'exams',
            'analysisData',
            'groupedAnalysis',
            'year',
            'term',
            'examID',
            'classID',
            'subclassID',
            'subjectID',
            'allSubclasses'
        ));
    }

    public function getClassSubjectsForAnalysis(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $classID = $request->input('classID');
            $subclassID = $request->input('subclassID');

            if (!$schoolID || !$classID) {
                return response()->json(['success' => false, 'error' => 'Class ID and school ID required']);
            }

            $query = DB::table('class_subjects')
                ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->where('class_subjects.status', 'Active')
                ->where('school_subjects.status', 'Active')
                ->where('class_subjects.classID', $classID);

            if ($subclassID) {
                $query->where(function ($subQuery) use ($subclassID) {
                    $subQuery->where('class_subjects.subclassID', $subclassID)
                        ->orWhereNull('class_subjects.subclassID');
                });
            }

            $subjects = $query->select(
                'school_subjects.subjectID',
                'school_subjects.subject_name'
            )->distinct()->orderBy('school_subjects.subject_name')->get();

            return response()->json(['success' => true, 'subjects' => $subjects]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get exam results for students
     */
    private function getExamResults($students, $term, $year, $schoolType, $examID = null)
    {
        $resultsData = [];

        foreach ($students as $student) {
            $studentResults = [];

            // Get examinations for this term and year
            $examinationsQuery = Examination::where('schoolID', Session::get('schoolID'))
                ->where('year', $year)
                ->where('approval_status', 'Approved');

            if ($term) {
                $examinationsQuery->where('term', $term);
            }

            // Filter by specific exam if selected
            if ($examID) {
                $examinationsQuery->where('examID', $examID);
            }

            $examinations = $examinationsQuery->orderBy('start_date')
                ->get();

            foreach ($examinations as $exam) {
                // Check if exam has ended (for Weekly/Monthly tests, check differently)
                $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
                $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');

                $examHasEnded = false;
                if (!$isWeeklyTest && !$isMonthlyTest) {
                    try {
                        $today = now()->startOfDay();
                        $endDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
                        $examHasEnded = $endDate < $today;
                    } catch (\Exception $e) {
                        // If date parsing fails, assume exam has ended
                        $examHasEnded = true;
                    }
                } else {
                    // For Weekly/Monthly tests, they are always ongoing, so allow viewing
                    $examHasEnded = true;
                }

                // Skip this exam if it hasn't ended yet
                if (!$examHasEnded) {
                    continue;
                }

                // Get results for this student and exam - only get results with marks and allowed status
                $results = Result::where('studentID', $student->studentID)
                    ->where('examID', $exam->examID)
                    ->whereNotNull('marks')
                    ->where('status', 'allowed')
                    ->with(['classSubject.subject', 'examination'])
                    ->get();

                if ($results->isEmpty()) {
                    continue;
                }

                // Calculate totals
                $totalMarks = 0;
                $subjectCount = 0;
                $subjectsData = [];
                $subjectPoints = [];

                // Get class name and classID - handle both current subclass and old subclass (for history students)
                $className = '';
                $classID = null;
                if ($student->subclass && $student->subclass->class) {
                    $className = $student->subclass->class->class_name ?? '';
                    $classID = $student->subclass->class->classID ?? null;
                } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                    $className = $student->oldSubclass->class->class_name ?? '';
                    $classID = $student->oldSubclass->class->classID ?? null;
                }

                foreach ($results as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $totalMarks += (float) $result->marks;
                        $subjectCount++;

                        // Calculate points for this subject
                        $gradePoints = $this->calculateGradePoints($result->marks, $schoolType, $className, $classID);
                        if ($gradePoints['points'] !== null) {
                            $subjectPoints[] = $gradePoints['points'];
                        }
                    }
                    $gradePointsForSubject = $this->calculateGradePoints($result->marks, $schoolType, $className, $classID);
                    $subjectsData[] = [
                        'subject_name' => $result->classSubject->subject->subject_name ?? 'N/A',
                        'marks' => $result->marks,
                        'grade' => $result->grade ?? $gradePointsForSubject['grade'],
                        'points' => $gradePointsForSubject['points'],
                    ];
                }

                // Only add if student has at least one subject with marks
                if ($subjectCount == 0) {
                    continue;
                }

                $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;

                // Calculate total points for division (for O-Level and A-Level)
                $totalPoints = 0;
                $bestSevenTotalMarks = 0;

                if ($schoolType === 'Secondary' && in_array(strtolower(preg_replace('/[\s\-]+/', '_', $className)), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    // O-Level: Use 7 best subjects (lowest points = best performance)
                    // Sort ascending (lowest points first = best performance)
                    if (count($subjectPoints) > 0) {
                        // Create array with marks and points for sorting
                        $marksPointsArray = [];
                        foreach ($subjectsData as $subject) {
                            if ($subject['marks'] !== null && $subject['marks'] !== '' && $subject['points'] !== null) {
                                $marksPointsArray[] = [
                                    'marks' => (float)$subject['marks'],
                                    'points' => $subject['points']
                                ];
                            }
                        }

                        // Sort by points ascending (lowest points first = best)
                        usort($marksPointsArray, function($a, $b) {
                            if ($a['points'] != $b['points']) {
                                return $a['points'] <=> $b['points'];
                            }
                            // If points are equal, sort by marks descending (higher marks = better)
                            return $b['marks'] <=> $a['marks'];
                        });

                        // Get best 7 (lowest points)
                        $bestSeven = array_slice($marksPointsArray, 0, min(7, count($marksPointsArray)));

                        // Calculate total points
                        $totalPoints = array_sum(array_column($bestSeven, 'points'));

                        // Calculate total marks for best 7 subjects
                        $bestSevenTotalMarks = array_sum(array_column($bestSeven, 'marks'));
                    }
                } elseif ($schoolType === 'Secondary' && in_array(strtolower(preg_replace('/[\s\-]+/', '_', $className)), ['form_five', 'form_six'])) {
                    // A-Level: Use 3 best principal subjects (highest points)
                    if (count($subjectPoints) > 0) {
                        rsort($subjectPoints); // Sort descending (highest first)
                        $bestThree = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                        $totalPoints = array_sum($bestThree);
                    }
                }

                // Calculate grade/division with points
                $gradeDivision = $this->calculateGradeDivision($totalMarks, $averageMarks, $subjectCount, $schoolType, $className, $totalPoints, $classID);

                $studentResults[] = [
                    'exam' => $exam,
                    'total_marks' => $totalMarks,
                    'average_marks' => $averageMarks,
                    'subject_count' => $subjectCount,
                    'subjects' => $subjectsData,
                    'grade' => $gradeDivision['grade'] ?? null,
                    'division' => $gradeDivision['division'] ?? null,
                    'total_points' => $totalPoints,
                    'best_seven_total_marks' => $bestSevenTotalMarks,
                ];
            }

            if (!empty($studentResults)) {
                $resultsData[$student->studentID] = $studentResults;
            }
        }

        return $resultsData;
    }

    /**
     * Get term report (average of all exams in term)
     */
    private function getTermReport($students, $term, $year, $schoolType)
    {
        $resultsData = [];
        $schoolID = Session::get('schoolID');

        // OPTIMIZATION: Query examinations once for all students instead of inside the loop
        $examinationsQuery = Examination::where('schoolID', $schoolID)
            ->where('year', $year)
            ->where('approval_status', 'Approved');

        if ($term) {
            $examinationsQuery->where('term', $term);
        }

        $examinations = $examinationsQuery->orderBy('start_date')->get();

        // Filter out exams that haven't ended yet
        $endedExaminations = $examinations->filter(function($exam) {
            // Check if exam has ended (for Weekly/Monthly tests, check differently)
            $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
            $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');

            if ($isWeeklyTest || $isMonthlyTest) {
                // For Weekly/Monthly tests, they are always ongoing, so allow viewing
                return true;
            }

            try {
                $today = now()->startOfDay();
                $endDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
                return $endDate < $today;
            } catch (\Exception $e) {
                // If date parsing fails, assume exam has ended
                return true;
            }
        });

        $examIDs = $endedExaminations->pluck('examID')->toArray();

        if ($endedExaminations->isEmpty()) {
            return $resultsData;
        }

        // OPTIMIZATION: Get all student IDs
        $studentIDs = $students->pluck('studentID')->toArray();

        // OPTIMIZATION: Fetch all results in batch instead of per student/exam
        $allResults = Result::whereIn('studentID', $studentIDs)
            ->whereIn('examID', $examIDs)
            ->whereNotNull('marks')
            ->where('status', 'allowed')
            ->with(['classSubject.subject'])
            ->get()
            ->groupBy('studentID');

        // Store results by studentID and examID for quick lookup
        $resultsByStudentAndExam = [];
        foreach ($allResults as $studentID => $studentResults) {
            $resultsByStudentAndExam[$studentID] = $studentResults->groupBy('examID');
        }

        foreach ($students as $student) {
            $studentResultsByExam = $resultsByStudentAndExam[$student->studentID] ?? collect();

            if ($studentResultsByExam->isEmpty()) {
                continue;
            }

            // Get class name and classID once
            $className = '';
            $classID = null;
            if ($student->subclass && $student->subclass->class) {
                $className = $student->subclass->class->class_name ?? '';
                $classID = $student->subclass->class->classID ?? null;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $className = $student->oldSubclass->class->class_name ?? '';
                $classID = $student->oldSubclass->class->classID ?? null;
            }

            $allExamResults = [];
            $totalMarksAllExams = 0;
            $totalSubjectCount = 0;
            $allSubjectPoints = []; // Collect points from all exams

            foreach ($endedExaminations as $exam) {
                $results = $studentResultsByExam->get($exam->examID, collect());

                if ($results->isEmpty()) {
                    continue;
                }

                $examTotalMarks = 0;
                $examSubjectCount = 0;

                foreach ($results as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $marks = (float) $result->marks;
                        $examTotalMarks += $marks;
                        $examSubjectCount++;

                        // OPTIMIZATION: Calculate points here instead of querying again
                        $gradePoints = $this->calculateGradePoints($marks, $schoolType, $className, $classID);
                        if ($gradePoints['points'] !== null) {
                            $allSubjectPoints[] = $gradePoints['points'];
                        }
                    }
                }

                if ($examSubjectCount > 0) {
                    $examAverage = $examTotalMarks / $examSubjectCount;

                    // Calculate grade for this exam based on average using grade_definitions
                    $examGrade = null;
                    if ($classID && $examAverage > 0) {
                        $gradeResult = $this->getGradeFromDefinition($examAverage, $classID);
                        $examGrade = $gradeResult['grade'];
                    } else {
                        // Fallback to old logic
                        if ($examAverage >= 75) {
                            $examGrade = 'A';
                        } elseif ($examAverage >= 65) {
                            $examGrade = 'B';
                        } elseif ($examAverage >= 45) {
                            $examGrade = 'C';
                        } elseif ($examAverage >= 30) {
                            $examGrade = 'D';
                        } else {
                            $examGrade = 'F';
                        }
                    }

                    $allExamResults[] = [
                        'exam' => $exam,
                        'total_marks' => $examTotalMarks,
                        'subject_count' => $examSubjectCount,
                        'average' => $examAverage,
                        'grade' => $examGrade,
                    ];
                    $totalMarksAllExams += $examTotalMarks;
                    $totalSubjectCount += $examSubjectCount;
                }
            }

            if (!empty($allExamResults)) {
                // Calculate overall average as average of exam averages (for report view)
                $examAveragesSum = 0;
                foreach ($allExamResults as $examResult) {
                    $examAveragesSum += $examResult['average'];
                }
                $overallAverage = count($allExamResults) > 0 ? $examAveragesSum / count($allExamResults) : 0;

                // Calculate total points for division
                $totalPoints = 0;
                if ($schoolType === 'Secondary' && in_array(strtolower(preg_replace('/[\s\-]+/', '_', $className)), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    // O-Level: Use 7 best subjects (lowest points = best performance)
                    if (count($allSubjectPoints) > 0) {
                        sort($allSubjectPoints); // Sort ascending (lowest first = best)
                        $bestSeven = array_slice($allSubjectPoints, 0, min(7, count($allSubjectPoints)));
                        $totalPoints = array_sum($bestSeven);
                    }
                } elseif ($schoolType === 'Secondary' && in_array(strtolower(preg_replace('/[\s\-]+/', '_', $className)), ['form_five', 'form_six'])) {
                    // A-Level: Use 3 best principal subjects (highest points)
                    if (count($allSubjectPoints) > 0) {
                        rsort($allSubjectPoints); // Sort descending (highest first)
                        $bestThree = array_slice($allSubjectPoints, 0, min(3, count($allSubjectPoints)));
                        $totalPoints = array_sum($bestThree);
                    }
                }

                // Calculate overall grade based on overall average using grade_definitions
                $overallGrade = null;
                if ($classID && $overallAverage > 0) {
                    $gradeResult = $this->getGradeFromDefinition($overallAverage, $classID);
                    $overallGrade = $gradeResult['grade'];
                } else {
                    // Fallback to old logic
                    if ($overallAverage >= 75) {
                        $overallGrade = 'A';
                    } elseif ($overallAverage >= 65) {
                        $overallGrade = 'B';
                    } elseif ($overallAverage >= 45) {
                        $overallGrade = 'C';
                    } elseif ($overallAverage >= 30) {
                        $overallGrade = 'D';
                    } else {
                        $overallGrade = 'F';
                    }
                }

                // Calculate division for Secondary using points
                $gradeDivision = $this->calculateGradeDivision($totalMarksAllExams, $overallAverage, $totalSubjectCount, $schoolType, $className, $totalPoints, $classID);

                // OPTIMIZATION: Skip position calculation here - it's very expensive and will be calculated in the view per class
                // Position will be calculated in the Blade template where it's actually needed

                $resultsData[$student->studentID] = [
                    'exams' => $allExamResults,
                    'total_marks' => $totalMarksAllExams,
                    'average_marks' => $overallAverage,
                    'subject_count' => $totalSubjectCount,
                    'exam_count' => count($allExamResults),
                    'grade' => $overallGrade,
                    'division' => $gradeDivision['division'] ?? null,
                    'position' => null, // Will be calculated in view per class
                    'total_points' => $totalPoints,
                ];
            }
        }

        return $resultsData;
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
     * Calculate grade points for a single subject
     * Now uses grade_definitions table based on student's class
     */
    private function calculateGradePoints($marks, $schoolType, $className, $classID = null)
    {
        if ($marks === null || $marks === '') {
            return ['grade' => null, 'points' => null];
        }

        // If classID is provided, use grade_definitions table
        if ($classID) {
            return $this->getGradeFromDefinition($marks, $classID);
        }

        // Fallback to old logic if classID not provided (for backward compatibility)
        $marksNum = (float) $marks;
        $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));

        if ($schoolType === 'Secondary') {
            if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
                // O-Level Grading System (Secondary only)
                if ($marksNum >= 75 && $marksNum <= 100) {
                    return ['grade' => 'A', 'points' => 1];
                } elseif ($marksNum >= 65 && $marksNum <= 74) {
                    return ['grade' => 'B', 'points' => 2];
                } elseif ($marksNum >= 45 && $marksNum <= 64) {
                    return ['grade' => 'C', 'points' => 3];
                } elseif ($marksNum >= 30 && $marksNum <= 44) {
                    return ['grade' => 'D', 'points' => 4];
                } elseif ($marksNum <= 29) {
                    return ['grade' => 'F', 'points' => 5];
                } else {
                    return ['grade' => 'F', 'points' => 5];
                }
            } elseif (in_array($classNameLower, ['form_five', 'form_six'])) {
                // A-Level Grading System
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
        }

        // Primary or default - no points calculation
        return ['grade' => null, 'points' => null];
    }

    /**
     * Calculate grade or division with points
     * Now uses grade_definitions table for average marks grade
     */
    private function calculateGradeDivision($totalMarks, $averageMarks, $subjectCount, $schoolType, $className, $totalPoints = 0, $classID = null)
    {
        $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));

        if ($schoolType === 'Secondary' && in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four'])) {
            // O-Level: Calculate division based on total points (7 best subjects)
            // 7-17: I
            // 18-21: II
            // 22-25: III
            // 26-33: IV
            // >=34: 0
            if ($totalPoints >= 7 && $totalPoints <= 17) {
                return ['grade' => null, 'division' => 'I.' . $totalPoints];
            } elseif ($totalPoints >= 18 && $totalPoints <= 21) {
                return ['grade' => null, 'division' => 'II.' . $totalPoints];
            } elseif ($totalPoints >= 22 && $totalPoints <= 25) {
                return ['grade' => null, 'division' => 'III.' . $totalPoints];
            } elseif ($totalPoints >= 26 && $totalPoints <= 33) {
                return ['grade' => null, 'division' => 'IV.' . $totalPoints];
            } elseif ($totalPoints >= 34) {
                return ['grade' => null, 'division' => '0.' . $totalPoints];
            } else {
                // If total points is less than 7, still assign Division 0
                return ['grade' => null, 'division' => '0.' . $totalPoints];
            }
        } elseif ($schoolType === 'Secondary' && in_array($classNameLower, ['form_five', 'form_six'])) {
            // A-Level: Calculate division based on total points
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
            // Primary or Secondary without division: Calculate grade from grade_definitions
            if ($classID && $averageMarks !== null && $averageMarks !== '') {
                $gradeResult = $this->getGradeFromDefinition($averageMarks, $classID);
                return ['grade' => $gradeResult['grade'], 'division' => null];
            }

            // Fallback to old logic if classID not provided
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

    /**
     * Calculate student position in main class
     */
    private function calculatePosition($student, $term, $year, $studentAverage, $schoolType)
    {
        // Get main class (class, not subclass) - handle both current and old subclass
        $mainClassID = null;
        if ($student->subclass) {
            $mainClassID = $student->subclass->classID ?? null;
        } elseif ($student->oldSubclass) {
            $mainClassID = $student->oldSubclass->classID ?? null;
        }

        if (!$mainClassID) {
            return null;
        }

        // Get all students in the same main class
        $classStudents = Student::whereHas('subclass', function($query) use ($mainClassID) {
            $query->where('classID', $mainClassID);
        })
        ->where('schoolID', Session::get('schoolID'))
        ->where('status', 'Active')
        ->get();

        // Calculate averages for all students in the class
        $studentAverages = [];

        foreach ($classStudents as $classStudent) {
            $examinationsQuery = Examination::where('schoolID', Session::get('schoolID'))
                ->where('year', $year)
                ->where('approval_status', 'Approved');

            if ($term) {
                $examinationsQuery->where('term', $term);
            }

            $examinations = $examinationsQuery->get();

            $totalMarks = 0;
            $totalSubjects = 0;

            foreach ($examinations as $exam) {
                // Get results with marks and allowed status
                $results = Result::where('studentID', $classStudent->studentID)
                    ->where('examID', $exam->examID)
                    ->whereNotNull('marks')
                    ->where('status', 'allowed')
                    ->get();

                foreach ($results as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $totalMarks += (float) $result->marks;
                        $totalSubjects++;
                    }
                }
            }

            if ($totalSubjects > 0) {
                $avg = $totalMarks / $totalSubjects;
                $studentAverages[] = [
                    'studentID' => $classStudent->studentID,
                    'average' => $avg,
                ];
            }
        }

        // Sort by average (descending)
        usort($studentAverages, function($a, $b) {
            return $b['average'] <=> $a['average'];
        });

        // Find position
        $position = null;
        foreach ($studentAverages as $index => $data) {
            if ($data['studentID'] == $student->studentID) {
                $position = $index + 1;
                break;
            }
        }

        return $position;
    }

    /**
     * Download results as PDF
     */
    public function downloadPdf(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if ($userType !== 'Admin' || !$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $option = $request->get('option', 'all'); // single, all, class, subclass
        $studentID = $request->get('studentID', null);
        $classID = $request->get('classID', null);
        $subclassID = $request->get('subclassID', null);

        // Get filter parameters from request
        $termFilter = $request->get('term', '');
        $yearFilter = $request->get('year', date('Y'));
        $typeFilter = $request->get('type', 'exam');
        $statusFilter = $request->get('status', 'active');
        $examFilter = $request->get('examID', '');

        // Get school details
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : 'Secondary';

        // Get students based on option
        $students = collect();
        if ($option === 'single' && $studentID) {
            $students = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
                ->get();
        } elseif ($option === 'class' && $classID) {
            $students = Student::where('schoolID', $schoolID)
                ->where(function($query) use ($classID) {
                    $query->whereHas('subclass', function($q) use ($classID) {
                        $q->where('classID', $classID);
                    })->orWhereHas('oldSubclass', function($q) use ($classID) {
                        $q->where('classID', $classID);
                    });
                })
                ->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
                ->orderBy('first_name')
                ->get();
        } elseif ($option === 'subclass' && $subclassID) {
            $students = Student::where('schoolID', $schoolID)
                ->where('subclassID', $subclassID)
                ->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
                ->orderBy('first_name')
                ->get();
        } else {
            // All students based on filters
            $studentQuery = Student::where('schoolID', $schoolID);
            if ($statusFilter === 'active') {
                $studentQuery->where('status', 'Active');
            } elseif ($statusFilter === 'history') {
                $studentQuery->whereNotNull('old_subclassID');
            }
            $students = $studentQuery->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
                ->orderBy('first_name')
                ->get();
        }

        // Get results data
        $resultsData = [];
        if ($termFilter && $yearFilter) {
            if ($typeFilter === 'exam') {
                $resultsData = $this->getExamResults($students, $termFilter, $yearFilter, $schoolType, $examFilter);
            } else {
                $resultsData = $this->getTermReport($students, $termFilter, $yearFilter, $schoolType);
            }
        }

        // Filter students to only those with results
        $students = $students->filter(function($student) use ($resultsData) {
            return isset($resultsData[$student->studentID]);
        });

        // Build title based on filters
        $title = '';
        if ($typeFilter === 'exam') {
            $title = 'Exam Results';
            if ($examFilter) {
                $exam = Examination::find($examFilter);
                if ($exam) {
                    $title = $exam->exam_name;
                }
            }
        } else {
            $title = 'Term Report';
        }

        if ($termFilter) {
            $title .= ' - ' . ucfirst(str_replace('_', ' ', $termFilter));
        }
        $title .= ' - ' . $yearFilter;

        // Prepare data for PDF
        $data = [
            'school' => $school,
            'schoolType' => $schoolType,
            'students' => $students,
            'resultsData' => $resultsData,
            'filters' => [
                'term' => $termFilter,
                'year' => $yearFilter,
                'type' => $typeFilter,
                'status' => $statusFilter,
                'examID' => $examFilter,
                'class' => $request->get('class', ''),
                'subclass' => $request->get('subclass', ''),
                'grade' => $request->get('grade', ''),
                'gender' => $request->get('gender', ''),
            ],
            'option' => $option,
            'title' => $title,
        ];

        // Generate PDF
        $pdf = PDF::loadView('Admin.pdf.results', $data);
        $pdf->setPaper('A4', 'portrait');

        // Generate filename
        $filename = 'Results_';
        if ($option === 'single' && $students->count() > 0) {
            $student = $students->first();
            $filename .= $student->first_name . '_' . $student->last_name . '_';
        } elseif ($option === 'class' && $classID) {
            $class = ClassModel::find($classID);
            $filename .= ($class ? str_replace(' ', '_', $class->class_name) : 'Class') . '_';
        } elseif ($option === 'subclass' && $subclassID) {
            $subclass = Subclass::find($subclassID);
            $filename .= ($subclass ? str_replace(' ', '_', $subclass->subclass_name) : 'Subclass') . '_';
        } else {
            $filename .= 'All_Students_';
        }
        $filename .= ($typeFilter === 'exam' ? 'Exam' : 'Term_Report') . '_';
        $filename .= ($termFilter ? str_replace('_', '', $termFilter) . '_' : '') . $yearFilter . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download results as Excel
     */
    public function downloadExcel(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if ($userType !== 'Admin' || !$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $option = $request->get('option', 'all');
        $studentID = $request->get('studentID', null);
        $classID = $request->get('classID', null);
        $subclassID = $request->get('subclassID', null);

        // Get filter parameters
        $termFilter = $request->get('term', '');
        $yearFilter = $request->get('year', date('Y'));
        $typeFilter = $request->get('type', 'exam');
        $statusFilter = $request->get('status', 'active');
        $examFilter = $request->get('examID', '');

        // Get school details
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : 'Secondary';

        // Get students based on option (same logic as PDF)
        $students = collect();
        if ($option === 'single' && $studentID) {
            $students = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
                ->get();
        } elseif ($option === 'class' && $classID) {
            $students = Student::where('schoolID', $schoolID)
                ->where(function($query) use ($classID) {
                    $query->whereHas('subclass', function($q) use ($classID) {
                        $q->where('classID', $classID);
                    })->orWhereHas('oldSubclass', function($q) use ($classID) {
                        $q->where('classID', $classID);
                    });
                })
                ->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
                ->orderBy('first_name')
                ->get();
        } elseif ($option === 'subclass' && $subclassID) {
            $students = Student::where('schoolID', $schoolID)
                ->where('subclassID', $subclassID)
                ->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
                ->orderBy('first_name')
                ->get();
        } else {
            $studentQuery = Student::where('schoolID', $schoolID);
            if ($statusFilter === 'active') {
                $studentQuery->where('status', 'Active');
            } elseif ($statusFilter === 'history') {
                $studentQuery->whereNotNull('old_subclassID');
            }
            $students = $studentQuery->with(['subclass.class', 'subclass.combie', 'parent', 'oldSubclass.class'])
                ->orderBy('first_name')
                ->get();
        }

        // Get results data
        $resultsData = [];
        if ($termFilter && $yearFilter) {
            if ($typeFilter === 'exam') {
                $resultsData = $this->getExamResults($students, $termFilter, $yearFilter, $schoolType, $examFilter);
            } else {
                $resultsData = $this->getTermReport($students, $termFilter, $yearFilter, $schoolType);
            }
        }

        // Filter students to only those with results
        $students = $students->filter(function($student) use ($resultsData) {
            return isset($resultsData[$student->studentID]);
        });

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Build title based on filters
        $title = '';
        if ($typeFilter === 'exam') {
            $title = 'Exam Results';
            if ($examFilter) {
                $exam = Examination::find($examFilter);
                if ($exam) {
                    $title = $exam->exam_name;
                }
            }
        } else {
            $title = 'Term Report';
        }

        if ($termFilter) {
            $title .= ' - ' . ucfirst(str_replace('_', ' ', $termFilter));
        }
        $title .= ' - ' . $yearFilter;

        // Add school name and title
        $sheet->setCellValue('A1', $school->school_name ?? 'School');
        $sheet->setCellValue('A2', $title);

        // Merge cells for better appearance
        $lastCol = 'K'; // Adjust based on number of columns
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');

        // Style header
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF940000');
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Set headers
        $headers = ['#', 'Student Name', 'Admission No.', 'Class', 'Subclass', 'Total Marks', 'Average', 'Grade', 'Division'];
        if ($typeFilter === 'report') {
            $headers[] = 'Position';
            $headers[] = 'Exams Count';
        } else {
            $headers[] = 'Exam Name';
        }

        $row = 4;
        $col = 'A';
        $lastHeaderCol = '';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $lastHeaderCol = $col;
            $col++;
        }

        // Style header row
        $sheet->getStyle('A4:' . $lastHeaderCol . '4')->getFont()->setBold(true);
        $sheet->getStyle('A4:' . $lastHeaderCol . '4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF940000');
        $sheet->getStyle('A4:' . $lastHeaderCol . '4')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Add data
        $row = 5;
        $index = 1;
        foreach ($students as $student) {
            if (isset($resultsData[$student->studentID])) {
                $result = $resultsData[$student->studentID];

                $className = '';
                $subclassName = '';
                if ($student->subclass && $student->subclass->class) {
                    $className = $student->subclass->class->class_name ?? '';
                    $subclassName = $student->subclass->subclass_name ?? '';
                } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                    $className = $student->oldSubclass->class->class_name ?? '';
                    $subclassName = $student->oldSubclass->subclass_name ?? '';
                }

                if ($typeFilter === 'report') {
                    $sheet->setCellValue('A' . $row, $index);
                    $sheet->setCellValue('B' . $row, $student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name);
                    $sheet->setCellValue('C' . $row, $student->admission_number ?? 'N/A');
                    $sheet->setCellValue('D' . $row, $className);
                    $sheet->setCellValue('E' . $row, $subclassName);
                    $sheet->setCellValue('F' . $row, number_format($result['total_marks'], 2));
                    $sheet->setCellValue('G' . $row, number_format($result['average_marks'], 2));
                    $sheet->setCellValue('H' . $row, $result['grade'] ?? 'N/A');
                    $sheet->setCellValue('I' . $row, $result['division'] ?? 'N/A');
                    $sheet->setCellValue('J' . $row, $result['position'] ?? 'N/A');
                    $sheet->setCellValue('K' . $row, $result['exam_count'] ?? 0);
                } else {
                    // For exam results, show first exam or aggregate
                    $firstExam = is_array($result) && !empty($result) ? $result[0] : $result;
                    $sheet->setCellValue('A' . $row, $index);
                    $sheet->setCellValue('B' . $row, $student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name);
                    $sheet->setCellValue('C' . $row, $student->admission_number ?? 'N/A');
                    $sheet->setCellValue('D' . $row, $className);
                    $sheet->setCellValue('E' . $row, $subclassName);
                    $sheet->setCellValue('F' . $row, number_format($firstExam['total_marks'] ?? 0, 2));
                    $sheet->setCellValue('G' . $row, number_format($firstExam['average_marks'] ?? 0, 2));
                    $sheet->setCellValue('H' . $row, $firstExam['grade'] ?? 'N/A');
                    $sheet->setCellValue('I' . $row, $firstExam['division'] ?? 'N/A');
                    $sheet->setCellValue('J' . $row, $firstExam['exam']->exam_name ?? 'N/A');
                }

                $row++;
                $index++;
            }
        }

        // Auto-size columns
        foreach (range('A', $lastHeaderCol) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Generate filename
        $filename = 'Results_';
        if ($option === 'single' && $students->count() > 0) {
            $student = $students->first();
            $filename .= $student->first_name . '_' . $student->last_name . '_';
        } elseif ($option === 'class' && $classID) {
            $class = ClassModel::find($classID);
            $filename .= ($class ? str_replace(' ', '_', $class->class_name) : 'Class') . '_';
        } elseif ($option === 'subclass' && $subclassID) {
            $subclass = Subclass::find($subclassID);
            $filename .= ($subclass ? str_replace(' ', '_', $subclass->subclass_name) : 'Subclass') . '_';
        } else {
            $filename .= 'All_Students_';
        }
        $filename .= ($typeFilter === 'exam' ? 'Exam' : 'Term_Report') . '_';
        $filename .= ($termFilter ? str_replace('_', '', $termFilter) . '_' : '') . $yearFilter . '.xlsx';

        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}




