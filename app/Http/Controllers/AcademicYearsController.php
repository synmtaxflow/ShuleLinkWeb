<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\School;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\GradeDefinition;
use App\Services\SmsService;
use Carbon\Carbon;

class AcademicYearsController extends Controller
{
    /**
     * Display Academic Years Management Page
     */
    public function index()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Admin') {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get only CLOSED academic years from database (past years)
        $pastYears = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Closed')
            ->orderBy('year', 'desc')
            ->get();

        // Current year is NOT from database - it's the current working year
        // This will be used for closing and opening new year
        $currentYear = (object) [
            'academic_yearID' => null, // Not in database yet
            'year' => date('Y'),
            'year_name' => date('Y'), // Only year, not year/year+1 format
            'start_date' => date('Y') . '-01-01',
            'end_date' => date('Y') . '-12-31',
            'status' => 'Active',
            'is_current' => true // Flag to indicate this is current working year
        ];

        // Get school details
        $school = School::find($schoolID);

        return view('Admin.academic_years', compact('pastYears', 'currentYear', 'school'));
    }

    /**
     * View specific academic year details
     */
    public function viewYear($academicYearID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Admin') {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get academic year details
        $academicYear = DB::table('academic_years')
            ->where('academic_yearID', $academicYearID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$academicYear) {
            return redirect()->route('admin.academicYears')->with('error', 'Academic year not found');
        }

        // Get historical data for this academic year
        $yearData = $this->getYearHistoricalData($academicYearID);

        // Get school details
        $school = School::find($schoolID);

        return view('Admin.view_academic_year', compact('academicYear', 'yearData', 'school'));
    }

    /**
     * Get historical data for a specific academic year
     */
    private function getYearHistoricalData($academicYearID)
    {
        $data = [];

        // Get students count from history
        $data['students_count'] = DB::table('student_class_history')
            ->where('academic_yearID', $academicYearID)
            ->distinct('studentID')
            ->count('studentID');

        // Get classes count from history
        $data['classes_count'] = DB::table('classes_history')
            ->where('academic_yearID', $academicYearID)
            ->count();

        // Get subclasses count from history
        $data['subclasses_count'] = DB::table('subclasses_history')
            ->where('academic_yearID', $academicYearID)
            ->count();

        // Get subjects count from history
        $data['subjects_count'] = DB::table('class_subjects_history')
            ->where('academic_yearID', $academicYearID)
            ->distinct('subjectID')
            ->count('subjectID');

        // Get examinations count
        $data['examinations_count'] = DB::table('examinations_history')
            ->where('academic_yearID', $academicYearID)
            ->count();

        // Get results count
        $data['results_count'] = DB::table('results_history')
            ->where('academic_yearID', $academicYearID)
            ->count();

        // Get attendances count
        $data['attendances_count'] = DB::table('attendances_history')
            ->where('academic_yearID', $academicYearID)
            ->count();

        // Get scheme of works count
        $data['scheme_of_works_count'] = DB::table('scheme_of_works_history')
            ->where('academic_yearID', $academicYearID)
            ->count();

        // Get lesson plans count
        $data['lesson_plans_count'] = DB::table('lesson_plans_history')
            ->where('academic_yearID', $academicYearID)
            ->count();

        // Get payments count
        $data['payments_count'] = DB::table('payments_history')
            ->where('academic_yearID', $academicYearID)
            ->count();

        return $data;
    }

    /**
     * Close Academic Year
     */
    public function closeYear(Request $request)
    {
        // Set execution time limit to 60 minutes (3600 seconds)
        set_time_limit(3600);
        ini_set('max_execution_time', 3600);

        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $modalities = $request->input('modalities', []);
            $schoolClosingDate = $request->input('school_closing_date');
            $schoolReopeningDate = $request->input('school_reopening_date');
            $closeNotes = $request->input('close_notes');

            $currentYear = date('Y');

            // Create academic year record for current year
            $academicYearID = DB::table('academic_years')->insertGetId([
                'schoolID' => $schoolID,
                'year' => $currentYear,
                'year_name' => $currentYear, // Only year, not year/year+1 format
                'start_date' => $currentYear . '-01-01',
                'end_date' => $currentYear . '-12-31',
                'status' => 'Closed',
                'closed_at' => now(),
                'closed_by' => Session::get('user_id'),
                'notes' => $closeNotes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 1. Send SMS to Parents (if selected)
            if (in_array('send_sms_to_parents', $modalities)) {
                $this->sendSmsToParents($schoolID, $schoolClosingDate, $schoolReopeningDate);
            }

            // 2. Promote/Shift Students (if selected)
            if (in_array('promote_students', $modalities)) {
                $shiftByGrade = in_array('shift_by_grade', $modalities);
                $this->promoteStudents($schoolID, $academicYearID, $shiftByGrade);
            }

            // 3. Save Scheme of Work to History (if selected)
            if (in_array('save_scheme_of_work', $modalities)) {
                $this->saveSchemeOfWorkToHistory($schoolID, $academicYearID);
            }

            // 4. Lock Results Editing (if selected)
            if (in_array('lock_results_editing', $modalities)) {
                // This will be handled by checking academic_yearID in results table
                // Results will be locked for closed academic years
            }

            // 5. Lock Exams Editing (if selected)
            if (in_array('lock_exams_editing', $modalities)) {
                // This will be handled by checking academic_yearID in examinations table
                // Exams will be locked for closed academic years
            }

            // 6. Save All Data to History (Required)
            $this->saveAllDataToHistory($schoolID, $academicYearID);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Academic year closed successfully!',
                'academic_yearID' => $academicYearID
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing academic year: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error closing academic year: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Open New Academic Year
     */
    public function openNewYear(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $currentYear = date('Y');
            $nextYear = $currentYear + 1;

            // Check if next year already exists
            $existingYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('year', $nextYear)
                ->first();

            if ($existingYear) {
                // If exists but is Closed, update to Active
                if ($existingYear->status === 'Closed') {
                    DB::table('academic_years')
                        ->where('academic_yearID', $existingYear->academic_yearID)
                        ->update([
                            'status' => 'Active',
                            'updated_at' => now(),
                        ]);

                    $academicYearID = $existingYear->academic_yearID;
                } else {
                    // Already Active
                    return response()->json([
                        'success' => false,
                        'message' => "Academic year {$nextYear} already exists and is Active."
                    ], 400);
                }
            } else {
                // Create new academic year
                $academicYearID = DB::table('academic_years')->insertGetId([
                    'schoolID' => $schoolID,
                    'year' => $nextYear,
                    'year_name' => $nextYear,
                    'start_date' => $nextYear . '-01-01',
                    'end_date' => $nextYear . '-12-31',
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Auto-create terms for the new year
                $terms = [
                    ['term_name' => 'First Term', 'term_number' => 1, 'start_date' => $nextYear . '-01-01', 'end_date' => $nextYear . '-04-30'],
                    ['term_name' => 'Second Term', 'term_number' => 2, 'start_date' => $nextYear . '-05-01', 'end_date' => $nextYear . '-08-31'],
                ];

                foreach ($terms as $term) {
                    DB::table('terms')->insert([
                        'academic_yearID' => $academicYearID,
                        'schoolID' => $schoolID,
                        'term_name' => $term['term_name'],
                        'term_number' => $term['term_number'],
                        'year' => $nextYear,
                        'start_date' => $term['start_date'],
                        'end_date' => $term['end_date'],
                        'status' => 'Active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Academic year {$nextYear} opened successfully!",
                'academic_yearID' => $academicYearID,
                'year' => $nextYear
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error opening new academic year: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error opening new academic year: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS to Parents about school closure
     */
    private function sendSmsToParents($schoolID, $closingDate, $reopeningDate)
    {
        try {
            $smsService = new SmsService();
            $school = School::find($schoolID);

            if (!$school) {
                Log::error("School not found for schoolID: {$schoolID}");
                return;
            }

            // Get all parents
            $parents = ParentModel::where('schoolID', $schoolID)
                ->whereNotNull('phone')
                ->get();

            $closingDateFormatted = Carbon::parse($closingDate)->format('d/m/Y');
            $reopeningDateFormatted = Carbon::parse($reopeningDate)->format('d/m/Y');

            foreach ($parents as $parent) {
                $message = "{$school->school_name}. Mzazi {$parent->first_name} {$parent->last_name}, " .
                          "shule itafungwa tarehe {$closingDateFormatted} na itafunguliwa tena tarehe {$reopeningDateFormatted}. " .
                          "Asante kwa ushirikiano wako.";

                $result = $smsService->sendSms($parent->phone, $message);
                
                if (!$result['success']) {
                    Log::error("Failed to send SMS to parent {$parent->parentID}: " . ($result['message'] ?? 'Unknown error'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending SMS to parents: ' . $e->getMessage());
        }
    }

    /**
     * Promote/Shift Students based on grade definitions
     */
    private function promoteStudents($schoolID, $academicYearID, $shiftByGrade = false)
    {
        try {
            $currentYear = date('Y');
            
            // Get all active students
            $students = DB::table('students')
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->get();

            $school = School::find($schoolID);
            $schoolType = $school->school_type ?? 'Secondary';

            foreach ($students as $student) {
                // Get student's current subclass and class
                $currentSubclass = DB::table('subclasses')
                    ->where('subclassID', $student->subclassID)
                    ->first();

                if (!$currentSubclass) {
                    continue;
                }

                $currentClass = DB::table('classes')
                    ->where('classID', $currentSubclass->classID)
                    ->first();

                if (!$currentClass) {
                    continue;
                }

                // Save to student_class_history for current year
                DB::table('student_class_history')->insert([
                    'studentID' => $student->studentID,
                    'academic_yearID' => $academicYearID,
                    'classID' => $currentClass->classID,
                    'subclassID' => $currentSubclass->subclassID,
                    'student_status' => 'Active',
                    'joined_date' => $student->admission_date ?? now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $studentAverage = 0;
                $gradeDefinition = ['grade' => 'N/A'];
                $studentGrade = null;
                
                if ($shiftByGrade) {
                    // If shift_by_grade is checked, check final exam grade
                    $finalExamGrade = $this->getStudentFinalExamGrade($student->studentID, $currentClass->classID, $currentYear, $schoolID, $schoolType);
                    $studentGrade = $finalExamGrade['grade'];
                    
                    if ($finalExamGrade['average'] > 0) {
                        $studentAverage = $finalExamGrade['average'];
                        $gradeDefinition = $this->getGradeForMarks($studentAverage, $currentClass->classID);
                    }
                } else {
                    // If shift_by_grade is unchecked, ignore grade and shift all students
                    $studentAverage = 1; // Set to 1 to indicate shift without checking
                    $gradeDefinition = ['grade' => 'N/A'];
                }
                
                // Check if student meets promotion criteria
                $shouldPromote = $this->shouldPromoteStudent($studentAverage, $currentSubclass, $currentClass, $shiftByGrade, $studentGrade);
                
                // Get next class for promotion
                $nextClass = $this->getNextClass($currentClass->class_name, $schoolType);
                
                // Determine promotion type
                $promotionType = 'Repeated';
                $toClassID = $currentClass->classID;
                $toSubclassID = $currentSubclass->subclassID;
                
                if ($nextClass) {
                    // Check if it's final class (should graduate)
                    $isFinalClass = $this->isFinalClass($currentClass->class_name, $schoolType, $currentYear);
                    
                    if ($isFinalClass && $shouldPromote) {
                        // Graduate student
                        $promotionType = 'Graduated';
                        DB::table('students')
                            ->where('studentID', $student->studentID)
                            ->update(['status' => 'Graduated']);
                    } elseif ($shouldPromote) {
                        // Promote to next class
                        $promotionType = 'Promoted';
                        
                        // Find next class by matching class_name exactly (case-insensitive)
                        $nextClassObj = DB::table('classes')
                            ->where('schoolID', $schoolID)
                            ->whereRaw('UPPER(REPLACE(REPLACE(class_name, " ", "_"), "-", "_")) = ?', [strtoupper(str_replace([' ', '-'], '_', $nextClass))])
                            ->where('status', 'Active')
                            ->first();
                        
                        if ($nextClassObj) {
                            $toClassID = $nextClassObj->classID;
                            
                            // Find suitable subclass in next class based on grade definitions
                            $nextSubclass = $this->findSuitableSubclass($nextClassObj->classID, $studentAverage, $schoolID, $shiftByGrade, $studentGrade, $schoolType, $nextClassObj->class_name);
                            
                            if ($nextSubclass) {
                                $toSubclassID = $nextSubclass->subclassID;
                                
                                // Update student to new subclass
                                DB::table('students')
                                    ->where('studentID', $student->studentID)
                                    ->update([
                                        'subclassID' => $toSubclassID,
                                        'old_subclassID' => $currentSubclass->subclassID
                                    ]);
                            } else {
                                // No suitable subclass found - repeat same class (kariri darasa)
                                $promotionType = 'Repeated';
                            }
                        }
                    } else {
                        // Repeat same class (kariri darasa)
                        $promotionType = 'Repeated';
                    }
                } else {
                    // No next class - student graduates if final class
                    $isFinalClass = $this->isFinalClass($currentClass->class_name, $schoolType, $currentYear);
                    if ($isFinalClass && $shouldPromote) {
                        $promotionType = 'Graduated';
                        DB::table('students')
                            ->where('studentID', $student->studentID)
                            ->update(['status' => 'Graduated']);
                    }
                }
                
                // Send SMS to parent about promotion/repetition
                $this->sendPromotionSMS($student->studentID, $promotionType, $currentClass->class_name, $nextClass, $schoolID);

                // Save promotion record
                DB::table('student_promotions')->insert([
                    'studentID' => $student->studentID,
                    'from_academic_yearID' => $academicYearID,
                    'to_academic_yearID' => null, // Will be set when new year is opened
                    'from_classID' => $currentClass->classID,
                    'from_subclassID' => $currentSubclass->subclassID,
                    'to_classID' => $toClassID,
                    'to_subclassID' => $toSubclassID,
                    'promotion_type' => $promotionType,
                    'promotion_date' => now(),
                    'promoted_by' => Session::get('user_id'),
                    'notes' => "Average grade: {$gradeDefinition['grade']} ({$studentAverage} marks)",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error promoting students: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get student's grade from final exam of the year
     */
    private function getStudentFinalExamGrade($studentID, $classID, $year, $schoolID, $schoolType)
    {
        // Get final exam (last exam by end_date) for this year
        $finalExam = DB::table('examinations')
            ->where('schoolID', $schoolID)
            ->where('year', $year)
            ->where('approval_status', 'Approved')
            ->whereNotNull('end_date')
            ->where('end_date', '!=', 'every_week')
            ->where('end_date', '!=', 'every_month')
            ->orderBy('end_date', 'desc')
            ->first();

        if (!$finalExam) {
            return ['average' => 0, 'grade' => null];
        }

        // Get results for final exam
        $results = DB::table('results')
            ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
            ->where('results.studentID', $studentID)
            ->where('classes.classID', $classID)
            ->where('results.examID', $finalExam->examID)
            ->whereNotNull('results.marks')
            ->where('results.status', 'allowed')
            ->select('results.marks')
            ->get();

        if ($results->count() == 0) {
            return ['average' => 0, 'grade' => null];
        }

        $totalMarks = 0;
        $count = 0;

        foreach ($results as $result) {
            if ($result->marks !== null && $result->marks !== '') {
                $totalMarks += (float) $result->marks;
                $count++;
            }
        }

        if ($count == 0) {
            return ['average' => 0, 'grade' => null];
        }

        $average = $totalMarks / $count;
        
        // Get grade from grade definitions
        $gradeInfo = $this->getGradeForMarks($average, $classID);
        
        return ['average' => $average, 'grade' => $gradeInfo['grade']];
    }
    
    /**
     * Get student's average grade from second term only
     */
    private function getStudentSecondTermAverage($studentID, $classID, $year, $schoolID)
    {
        // Get second term examinations for this student in this year
        $examinations = DB::table('examinations')
            ->where('schoolID', $schoolID)
            ->where('year', $year)
            ->where('term', 'second_term')
            ->where('approval_status', 'Approved')
            ->pluck('examID')
            ->toArray();

        if (empty($examinations)) {
            return 0;
        }

        // Get results for second term exams only
        $results = DB::table('results')
            ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
            ->where('results.studentID', $studentID)
            ->where('classes.classID', $classID)
            ->whereIn('results.examID', $examinations)
            ->whereNotNull('results.marks')
            ->where('results.status', 'allowed')
            ->select('results.marks')
            ->get();

        if ($results->count() == 0) {
            return 0;
        }

        $totalMarks = 0;
        $count = 0;

        foreach ($results as $result) {
            if ($result->marks !== null && $result->marks !== '') {
                $totalMarks += (float) $result->marks;
                $count++;
            }
        }

        return $count > 0 ? ($totalMarks / $count) : 0;
    }
    
    /**
     * Get student's average grade across all subjects (for backward compatibility)
     */
    private function getStudentAverageGrade($studentID, $classID, $year)
    {
        // Get all exam results for this student in this year
        $results = DB::table('results')
            ->join('examinations', 'results.examID', '=', 'examinations.examID')
            ->join('class_subjects', 'results.class_subjectID', '=', 'class_subjects.class_subjectID')
            ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
            ->where('results.studentID', $studentID)
            ->where('classes.classID', $classID)
            ->where('examinations.year', $year)
            ->whereNotNull('results.marks')
            ->select('results.marks')
            ->get();

        if ($results->count() == 0) {
            return 0;
        }

        $totalMarks = 0;
        $count = 0;

        foreach ($results as $result) {
            if ($result->marks !== null && $result->marks !== '') {
                $totalMarks += (float) $result->marks;
                $count++;
            }
        }

        return $count > 0 ? ($totalMarks / $count) : 0;
    }

    /**
     * Get grade for marks based on grade definitions
     */
    private function getGradeForMarks($marks, $classID)
    {
        if (!$marks || !$classID) {
            return ['grade' => 'F', 'points' => null];
        }

        $gradeDefinition = GradeDefinition::where('classID', $classID)
            ->where('first', '<=', $marks)
            ->where('last', '>=', $marks)
            ->first();

        if (!$gradeDefinition) {
            return ['grade' => 'F', 'points' => null];
        }

        return ['grade' => $gradeDefinition->grade, 'points' => null];
    }

    /**
     * Check if student should be promoted based on grade requirements
     * Note: This is a simplified check. The actual grade matching is done in findSuitableSubclass
     */
    private function shouldPromoteStudent($averageMarks, $subclass, $class, $shiftByGrade = false, $studentGrade = null)
    {
        // If shift_by_grade is unchecked, ignore grade and shift all students
        if (!$shiftByGrade) {
            return true; // Shift student without checking results
        }
        
        // If shift_by_grade is checked, check if student has results
        // The actual grade matching will be done in findSuitableSubclass
        if ($averageMarks == 0) {
            return false;
        }

        // Return true if student has results (grade matching will be done in findSuitableSubclass)
        return true;
    }
    
    /**
     * Check if student grade matches subclass grade range (simplified)
     */
    private function checkGradeRangeSimple($studentGrade, $firstGrade, $finalGrade)
    {
        // Handle letter grades (Primary: A, B, C, D, E)
        $gradeOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6];
        
        if (isset($gradeOrder[strtoupper($studentGrade)]) && 
            isset($gradeOrder[strtoupper($firstGrade)]) && 
            isset($gradeOrder[strtoupper($finalGrade)])) {
            $studentOrder = $gradeOrder[strtoupper($studentGrade)];
            $firstOrder = $gradeOrder[strtoupper($firstGrade)];
            $finalOrder = $gradeOrder[strtoupper($finalGrade)];
            // Check if student's grade is within range (lower order = better grade)
            return $studentOrder >= $firstOrder && $studentOrder <= $finalOrder;
        }
        
        // Handle division grades (Secondary: I.15, II.11, etc.)
        if (preg_match('/^([IVX0]+)\.(\d+)$/', $studentGrade, $studentMatches) &&
            preg_match('/^([IVX0]+)\.(\d+)$/', $firstGrade, $firstMatches) &&
            preg_match('/^([IVX0]+)\.(\d+)$/', $finalGrade, $finalMatches)) {
            $studentNum = (int)$studentMatches[2];
            $studentLevel = $studentMatches[1];
            $firstNum = (int)$firstMatches[2];
            $firstLevel = $firstMatches[1];
            $finalNum = (int)$finalMatches[2];
            $finalLevel = $finalMatches[1];
            
            $divisionOrder = ['0' => 0, 'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4];
            $studentOrder = $divisionOrder[$studentLevel] ?? 999;
            $firstOrder = $divisionOrder[$firstLevel] ?? 999;
            $finalOrder = $divisionOrder[$finalLevel] ?? 999;
            
            // Same division level - check number range
            if ($studentLevel === $firstLevel && $studentLevel === $finalLevel) {
                return $studentNum >= $firstNum && $studentNum <= $finalNum;
            }
            
            // Different division levels
            if ($studentOrder > $firstOrder && $studentOrder < $finalOrder) {
                return true;
            } elseif ($studentOrder === $firstOrder && $studentNum >= $firstNum) {
                return true;
            } elseif ($studentOrder === $finalOrder && $studentNum <= $finalNum) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get next class name based on current class
     */
    private function getNextClass($currentClassName, $schoolType)
    {
        $normalized = strtoupper(str_replace([' ', '-'], '_', $currentClassName));
        
        if ($schoolType === 'Secondary') {
            $classSequence = [
                'FORM_ONE' => 'FORM TWO',
                'FORM_1' => 'FORM TWO',
                'FORM_TWO' => 'FORM THREE',
                'FORM_2' => 'FORM THREE',
                'FORM_THREE' => 'FORM FOUR',
                'FORM_3' => 'FORM FOUR',
                'FORM_FOUR' => null, // Graduates
                'FORM_4' => null,
                'FORM_FIVE' => 'FORM SIX',
                'FORM_5' => 'FORM SIX',
                'FORM_SIX' => null, // Graduates
                'FORM_6' => null,
            ];
        } else {
            // Primary school sequence
            $classSequence = [
                'BABY_CLASS' => 'NURSERY1',
                'NURSERY1' => 'NURSERY2',
                'NURSERY2' => 'STANDARD1',
                'STANDARD1' => 'STANDARD2',
                'STANDARD2' => 'STANDARD3',
                'STANDARD3' => 'STANDARD4',
                'STANDARD4' => 'STANDARD5',
                'STANDARD5' => 'STANDARD6',
                'STANDARD6' => 'STANDARD7',
                'STANDARD7' => null, // Graduates (with special logic for 2028+)
            ];
        }

        return $classSequence[$normalized] ?? null;
    }

    /**
     * Check if class is final class (should graduate)
     */
    private function isFinalClass($className, $schoolType, $currentYear = null)
    {
        $normalized = strtoupper(str_replace([' ', '-'], '_', $className));
        $year = $currentYear ?? (int)date('Y');
        
        if ($schoolType === 'Secondary') {
            return in_array($normalized, ['FORM_FOUR', 'FORM_4', 'FORM_SIX', 'FORM_6']);
        } else {
            // Primary school graduation logic
            // Starting 2028: STANDARD7 and STANDARD6 both graduate
            // Starting 2029: Only STANDARD6 graduates (no STANDARD7)
            if ($year >= 2029) {
                return in_array($normalized, ['STANDARD6', 'STANDARD_6']);
            } elseif ($year >= 2028) {
                return in_array($normalized, ['STANDARD6', 'STANDARD_6', 'STANDARD7', 'STANDARD_7']);
            } else {
                // Before 2028: Only STANDARD7 graduates
                return in_array($normalized, ['STANDARD7', 'STANDARD_7']);
            }
        }
    }

    /**
     * Find suitable subclass in next class based on grade definitions
     */
    private function findSuitableSubclass($nextClassID, $studentAverage, $schoolID, $shiftByGrade = false, $studentGrade = null, $schoolType = 'Secondary', $className = '')
    {
        // Get next class object
        $nextClass = DB::table('classes')
            ->where('classID', $nextClassID)
            ->first();
        
        if (!$nextClass) {
            return null;
        }
        
        // Get all active subclasses in the next class
        $subclasses = DB::table('subclasses')
            ->where('classID', $nextClassID)
            ->where('status', 'Active')
            ->get();
        
        if ($subclasses->isEmpty()) {
            return null;
        }
        
        // If shift_by_grade is unchecked, return first available subclass (shift without checking)
        if (!$shiftByGrade) {
            return $subclasses->first();
        }
        
        // If shift_by_grade is checked, find subclass that matches student's grade
        foreach ($subclasses as $subclass) {
            // Check if subclass has grade requirements
            if ($subclass->first_grade && $subclass->final_grade && $studentGrade) {
                // Check if student's grade matches subclass grade range
                if ($this->checkGradeRangeSimple($studentGrade, $subclass->first_grade, $subclass->final_grade)) {
                    return $subclass;
                }
            } elseif (empty($subclass->first_grade) && empty($subclass->final_grade)) {
                // If subclass has no grade requirements, it's eligible
                // But prioritize subclasses with grade requirements first
                continue;
            }
        }
        
        // If no subclass with matching grade definitions, return null (student will repeat)
        return null;
    }
    
    /**
     * Send SMS to parent about student promotion/repetition
     */
    private function sendPromotionSMS($studentID, $promotionType, $currentClassName, $nextClassName, $schoolID)
    {
        try {
            $smsService = new SmsService();
            $school = School::find($schoolID);
            
            if (!$school) {
                return;
            }
            
            // Get student with parent info
            $student = DB::table('students')
                ->join('parents', 'students.parentID', '=', 'parents.parentID')
                ->where('students.studentID', $studentID)
                ->select('students.*', 'parents.first_name as parent_first_name', 'parents.last_name as parent_last_name', 'parents.phone as parent_phone')
                ->first();
            
            if (!$student || !$student->parent_phone) {
                return;
            }
            
            $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            $parentName = trim(($student->parent_first_name ?? '') . ' ' . ($student->parent_last_name ?? ''));
            
            $message = '';
            
            if ($promotionType === 'Repeated') {
                $message = "{$school->school_name}. Mzazi {$parentName}, mwanafunzi {$studentName} amerudia darasa {$currentClassName}. " .
                          "Tafadhali msaidie mwanafunzi kujitahidi zaidi mwaka ujao. Asante kwa ushirikiano wako.";
            } elseif ($promotionType === 'Promoted') {
                $message = "{$school->school_name}. Mzazi {$parentName}, mwanafunzi {$studentName} amehama kutoka darasa {$currentClassName} kwenda darasa {$nextClassName}. " .
                          "Hongera! Tafadhali endelea kumtia moyo mwanafunzi. Asante kwa ushirikiano wako.";
            } elseif ($promotionType === 'Graduated') {
                $message = "{$school->school_name}. Mzazi {$parentName}, mwanafunzi {$studentName} amehitimu darasa {$currentClassName}. " .
                          "Hongera sana! Mwanafunzi amekamilisha masomo yake. Asante kwa ushirikiano wako.";
            }
            
            if ($message) {
                $result = $smsService->sendSms($student->parent_phone, $message);
                
                if (!$result['success']) {
                    Log::error("Failed to send promotion SMS to parent {$student->parentID}: " . ($result['message'] ?? 'Unknown error'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending promotion SMS: ' . $e->getMessage());
        }
    }

    /**
     * Save Scheme of Work to History and Delete from Main Table
     */
    private function saveSchemeOfWorkToHistory($schoolID, $academicYearID)
    {
        try {
            // Get all scheme of works for current year
            $currentYear = date('Y');
            $schemeOfWorks = DB::table('scheme_of_works')
                ->where('year', $currentYear)
                ->get();

            foreach ($schemeOfWorks as $scheme) {
                // Get class_subject to find classID
                $classSubject = DB::table('class_subjects')
                    ->where('class_subjectID', $scheme->class_subjectID)
                    ->first();

                if (!$classSubject) {
                    continue;
                }

                // Count items before saving to history
                $totalItems = DB::table('scheme_of_work_items')
                    ->where('scheme_of_workID', $scheme->scheme_of_workID)
                    ->count();

                // Save to history (reference only, scheme remains in main table for restoration)
                DB::table('scheme_of_works_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_scheme_of_workID' => $scheme->scheme_of_workID,
                    'original_class_subjectID' => $scheme->class_subjectID,
                    'year' => $scheme->year,
                    'status' => $scheme->status,
                    'created_by' => $scheme->created_by,
                    'total_items' => $totalItems,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Mark scheme as Archived instead of deleting (so it can be restored/used from history)
                // Scheme remains in main table with all items and learning objectives
                // Teachers can still use these schemes when creating new ones
                DB::table('scheme_of_works')
                    ->where('scheme_of_workID', $scheme->scheme_of_workID)
                    ->update(['status' => 'Archived']);
            }
        } catch (\Exception $e) {
            Log::error('Error saving scheme of work to history: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save All Data to History (Required)
     */
    private function saveAllDataToHistory($schoolID, $academicYearID)
    {
        try {
            $currentYear = date('Y');

            // 1. Save Classes to History
            $classes = DB::table('classes')
                ->where('schoolID', $schoolID)
                ->get();

            foreach ($classes as $class) {
                $totalSubclasses = DB::table('subclasses')
                    ->where('classID', $class->classID)
                    ->count();

                $totalStudents = DB::table('students')
                    ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                    ->where('subclasses.classID', $class->classID)
                    ->where('students.status', 'Active')
                    ->count();

                DB::table('classes_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_classID' => $class->classID,
                    'schoolID' => $schoolID,
                    'teacherID' => $class->teacherID,
                    'class_name' => $class->class_name,
                    'description' => $class->description,
                    'status' => $class->status ?? 'Active',
                    'has_subclasses' => $totalSubclasses > 0,
                    'total_subclasses' => $totalSubclasses,
                    'total_students' => $totalStudents,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. Save Subclasses to History
            $subclasses = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->select('subclasses.*', 'classes.classID as original_classID')
                ->get();

            foreach ($subclasses as $subclass) {
                $totalStudents = DB::table('students')
                    ->where('subclassID', $subclass->subclassID)
                    ->where('status', 'Active')
                    ->count();

                DB::table('subclasses_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_subclassID' => $subclass->subclassID,
                    'original_classID' => $subclass->original_classID,
                    'teacherID' => $subclass->teacherID,
                    'combieID' => $subclass->combieID ?? null,
                    'subclass_name' => $subclass->subclass_name,
                    'stream_code' => $subclass->stream_code,
                    'status' => $subclass->status ?? 'Active',
                    'first_grade' => $subclass->first_grade ?? null,
                    'final_grade' => $subclass->final_grade ?? null,
                    'total_students' => $totalStudents,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Save Class Subjects to History
            $classSubjects = DB::table('class_subjects')
                ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->select('class_subjects.*', 'classes.classID as original_classID')
                ->get();

            foreach ($classSubjects as $classSubject) {
                DB::table('class_subjects_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_class_subjectID' => $classSubject->class_subjectID,
                    'original_classID' => $classSubject->original_classID,
                    'subjectID' => $classSubject->subjectID,
                    'teacherID' => $classSubject->teacherID,
                    'status' => $classSubject->status ?? 'Active',
                    'student_status' => $classSubject->student_status ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Save Fees to History
            $fees = DB::table('fees')
                ->where('schoolID', $schoolID)
                ->get();

            foreach ($fees as $fee) {
                $totalInstallments = DB::table('fee_installments')
                    ->where('feeID', $fee->feeID)
                    ->count();

                DB::table('fees_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_feeID' => $fee->feeID,
                    'schoolID' => $schoolID,
                    'original_classID' => $fee->classID,
                    'fee_type' => $fee->fee_type,
                    'fee_name' => $fee->fee_name,
                    'amount' => $fee->amount,
                    'duration' => $fee->duration,
                    'description' => $fee->description,
                    'status' => $fee->status ?? 'Active',
                    'has_installments' => $totalInstallments > 0,
                    'total_installments' => $totalInstallments,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 5. Save Examinations to History
            $examinations = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->where('year', $currentYear)
                ->get();

            foreach ($examinations as $exam) {
                $totalStudents = DB::table('exam_attendance')
                    ->where('examID', $exam->examID)
                    ->count();

                $totalResults = DB::table('results')
                    ->where('examID', $exam->examID)
                    ->count();

                DB::table('examinations_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_examID' => $exam->examID,
                    'schoolID' => $schoolID,
                    'exam_name' => $exam->exam_name,
                    'start_date' => $exam->start_date,
                    'end_date' => $exam->end_date,
                    'status' => $exam->status ?? 'ongoing',
                    'exam_type' => $exam->exam_type,
                    'exam_category' => $exam->exam_category ?? null,
                    'term' => $exam->term ?? null,
                    'approval_status' => $exam->approval_status ?? 'Pending',
                    'rejection_reason' => $exam->rejection_reason ?? null,
                    'year' => $exam->year,
                    'details' => $exam->details ?? null,
                    'created_by' => $exam->created_by ?? null,
                    'enter_result' => $exam->enter_result ?? false,
                    'publish_result' => $exam->publish_result ?? false,
                    'upload_paper' => $exam->upload_paper ?? false,
                    'student_shifting_status' => $exam->student_shifting_status ?? null,
                    'total_students' => $totalStudents,
                    'total_results' => $totalResults,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 6. Save Results to History
            $results = DB::table('results')
                ->join('examinations', 'results.examID', '=', 'examinations.examID')
                ->where('examinations.schoolID', $schoolID)
                ->where('examinations.year', $currentYear)
                ->select('results.*', 'examinations.examID as original_examID')
                ->get();

            foreach ($results as $result) {
                DB::table('results_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_resultID' => $result->resultID,
                    'studentID' => $result->studentID,
                    'original_examID' => $result->original_examID,
                    'subclassID' => $result->subclassID,
                    'original_class_subjectID' => $result->class_subjectID,
                    'marks' => $result->marks,
                    'grade' => $result->grade,
                    'remark' => $result->remark ?? null,
                    'status' => $result->status ?? 'Draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 7. Save Payments to History
            // Get all payments for current year (by year or by academic_yearID if set)
            $payments = DB::table('payments')
                ->where('schoolID', $schoolID)
                ->where(function($query) use ($currentYear, $academicYearID) {
                    // Get payments for current year by created_at OR by academic_yearID
                    $query->whereYear('created_at', $currentYear)
                          ->orWhere('academic_yearID', $academicYearID);
                })
                ->get();

            foreach ($payments as $payment) {
                // Update payment's academic_yearID if it's null (for payments created before academic year was closed)
                if (!$payment->academic_yearID) {
                    DB::table('payments')
                        ->where('paymentID', $payment->paymentID)
                        ->update(['academic_yearID' => $academicYearID]);
                }

                DB::table('payments_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_paymentID' => $payment->paymentID,
                    'schoolID' => $schoolID,
                    'studentID' => $payment->studentID,
                    'original_feeID' => $payment->feeID,
                    'control_number' => $payment->control_number,
                    'amount_required' => $payment->amount_required,
                    'amount_paid' => $payment->amount_paid,
                    'balance' => $payment->balance,
                    'debt' => $payment->debt ?? 0,
                    'required_fees_amount' => $payment->required_fees_amount ?? 0,
                    'required_fees_paid' => $payment->required_fees_paid ?? 0,
                    'payment_status' => $payment->payment_status,
                    'fee_type' => $payment->fee_type ?? null,
                    'sms_sent' => $payment->sms_sent ?? 'No',
                    'sms_sent_at' => $payment->sms_sent_at ?? null,
                    'payment_date' => $payment->payment_date ?? null,
                    'payment_reference' => $payment->payment_reference ?? null,
                    'notes' => $payment->notes ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 8. Save Attendances to History
            $attendances = DB::table('attendances')
                ->where('schoolID', $schoolID)
                ->whereYear('attendance_date', $currentYear)
                ->get();

            foreach ($attendances as $attendance) {
                DB::table('attendances_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_attendanceID' => $attendance->attendanceID,
                    'schoolID' => $schoolID,
                    'subclassID' => $attendance->subclassID,
                    'studentID' => $attendance->studentID,
                    'teacherID' => $attendance->teacherID,
                    'attendance_date' => $attendance->attendance_date,
                    'status' => $attendance->status,
                    'check_in_time' => $attendance->check_in_time ?? null,
                    'check_out_time' => $attendance->check_out_time ?? null,
                    'remark' => $attendance->remark ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 9. Save Lesson Plans to History
            $lessonPlans = DB::table('lesson_plans')
                ->where('schoolID', $schoolID)
                ->where('year', $currentYear)
                ->get();

            foreach ($lessonPlans as $lessonPlan) {
                DB::table('lesson_plans_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_lesson_planID' => $lessonPlan->lesson_planID,
                    'schoolID' => $schoolID,
                    'session_timetableID' => $lessonPlan->session_timetableID,
                    'teacherID' => $lessonPlan->teacherID,
                    'lesson_date' => $lessonPlan->lesson_date,
                    'lesson_time_start' => $lessonPlan->lesson_time_start,
                    'lesson_time_end' => $lessonPlan->lesson_time_end,
                    'subject' => $lessonPlan->subject,
                    'class_name' => $lessonPlan->class_name,
                    'year' => $lessonPlan->year,
                    'registered_girls' => $lessonPlan->registered_girls ?? 0,
                    'registered_boys' => $lessonPlan->registered_boys ?? 0,
                    'registered_total' => $lessonPlan->registered_total ?? 0,
                    'present_girls' => $lessonPlan->present_girls ?? 0,
                    'present_boys' => $lessonPlan->present_boys ?? 0,
                    'present_total' => $lessonPlan->present_total ?? 0,
                    'main_competence' => $lessonPlan->main_competence ?? null,
                    'specific_competence' => $lessonPlan->specific_competence ?? null,
                    'main_activity' => $lessonPlan->main_activity ?? null,
                    'specific_activity' => $lessonPlan->specific_activity ?? null,
                    'teaching_learning_resources' => $lessonPlan->teaching_learning_resources ?? null,
                    'references' => $lessonPlan->references ?? null,
                    'lesson_stages' => $lessonPlan->lesson_stages ?? null,
                    'remarks' => $lessonPlan->remarks ?? null,
                    'reflection' => $lessonPlan->reflection ?? null,
                    'evaluation' => $lessonPlan->evaluation ?? null,
                    'reflection_signature' => $lessonPlan->reflection_signature ?? null,
                    'evaluation_signature' => $lessonPlan->evaluation_signature ?? null,
                    'status' => $lessonPlan->status ?? 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 10. Save Student Class Assignments to History
            // Save all active students' class assignments for this academic year
            $activeStudents = DB::table('students')
                ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->where('students.status', 'Active')
                ->select('students.studentID', 'students.subclassID', 'subclasses.classID', 'students.status')
                ->get();

            foreach ($activeStudents as $student) {
                // Check if record already exists (to avoid duplicates)
                $existingRecord = DB::table('student_class_history')
                    ->where('studentID', $student->studentID)
                    ->where('academic_yearID', $academicYearID)
                    ->first();

                if (!$existingRecord) {
                    DB::table('student_class_history')->insert([
                        'studentID' => $student->studentID,
                        'academic_yearID' => $academicYearID,
                        'classID' => $student->classID,
                        'subclassID' => $student->subclassID,
                        'student_status' => $student->status ?? 'Active',
                        'joined_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 11. Save Session Timetables to History
            $sessionTimetables = DB::table('class_session_timetables')
                ->join('subclasses', 'class_session_timetables.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->select('class_session_timetables.*')
                ->get();

            foreach ($sessionTimetables as $timetable) {
                DB::table('class_session_timetables_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_session_timetableID' => $timetable->session_timetableID,
                    'schoolID' => $schoolID,
                    'definitionID' => $timetable->definitionID,
                    'subclassID' => $timetable->subclassID,
                    'original_class_subjectID' => $timetable->class_subjectID ?? null,
                    'subjectID' => $timetable->subjectID ?? null,
                    'teacherID' => $timetable->teacherID,
                    'session_typeID' => $timetable->session_typeID,
                    'day' => $timetable->day,
                    'start_time' => $timetable->start_time,
                    'end_time' => $timetable->end_time,
                    'is_prepo' => $timetable->is_prepo ?? false,
                    'notes' => $timetable->notes ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 11. Save Exam Timetables to History
            $examTimetables = DB::table('exam_timetables')
                ->join('examinations', 'exam_timetables.examID', '=', 'examinations.examID')
                ->where('examinations.schoolID', $schoolID)
                ->where('examinations.year', $currentYear)
                ->select('exam_timetables.*', 'examinations.examID as original_examID')
                ->get();

            foreach ($examTimetables as $timetable) {
                DB::table('exam_timetables_history')->insert([
                    'academic_yearID' => $academicYearID,
                    'original_exam_timetableID' => $timetable->exam_timetableID,
                    'schoolID' => $schoolID,
                    'original_examID' => $timetable->original_examID,
                    'subclassID' => $timetable->subclassID,
                    'original_class_subjectID' => $timetable->class_subjectID ?? null,
                    'subjectID' => $timetable->subjectID ?? null,
                    'teacherID' => $timetable->teacherID,
                    'exam_date' => $timetable->exam_date,
                    'start_time' => $timetable->start_time,
                    'end_time' => $timetable->end_time,
                    'timetable_type' => $timetable->timetable_type,
                    'notes' => $timetable->notes ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error saving data to history: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * View Terms for Current Year
     */
    public function viewTerms()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Admin') {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $currentYear = date('Y');

        // Get terms for current year
        $terms = DB::table('terms')
            ->where('schoolID', $schoolID)
            ->where('year', $currentYear)
            ->orderBy('term_number')
            ->get();

        // If no terms exist, create default First Term and Second Term
        if ($terms->isEmpty()) {
            // Create First Term
            DB::table('terms')->insert([
                'schoolID' => $schoolID,
                'year' => $currentYear,
                'term_name' => 'First Term',
                'term_number' => 1,
                'start_date' => $currentYear . '-01-01',
                'end_date' => $currentYear . '-04-30',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Second Term
            DB::table('terms')->insert([
                'schoolID' => $schoolID,
                'year' => $currentYear,
                'term_name' => 'Second Term',
                'term_number' => 2,
                'start_date' => $currentYear . '-05-01',
                'end_date' => $currentYear . '-08-31',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Refresh terms
            $terms = DB::table('terms')
                ->where('schoolID', $schoolID)
                ->where('year', $currentYear)
                ->orderBy('term_number')
                ->get();
        }

        // Get school details
        $school = School::find($schoolID);

        return view('admin.view_terms', compact('terms', 'school', 'currentYear'));
    }

    /**
     * Close Term
     */
    public function closeTerm(Request $request)
    {
        // Set execution time limit to 60 minutes (3600 seconds)
        set_time_limit(3600);
        ini_set('max_execution_time', 3600);

        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $termID = $request->input('termID');
            $modalities = $request->input('modalities', []);
            $closeNotes = $request->input('close_term_notes');

            $term = DB::table('terms')->where('termID', $termID)->where('schoolID', $schoolID)->first();

            if (!$term) {
                return response()->json([
                    'success' => false,
                    'message' => 'Term not found'
                ], 404);
            }

            if ($term->status === 'Closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Term is already closed'
                ], 400);
            }

            // Update term status
            DB::table('terms')
                ->where('termID', $termID)
                ->update([
                    'status' => 'Closed',
                    'closed_at' => now(),
                    'closed_by' => Session::get('user_id'),
                    'notes' => $closeNotes,
                    'updated_at' => now(),
                ]);

            // Perform selected modalities
            $performedActions = [];

            // 1. Send SMS to Parents (if selected)
            if (in_array('send_sms_to_parents', $modalities)) {
                $this->sendTermResultsSMS($schoolID, $term->term_number, $term->year);
                $performedActions[] = 'SMS notifications sent to parents';
            }

            // 2. Lock Results Editing (if selected)
            if (in_array('lock_results_editing', $modalities)) {
                // This is handled by checking term status in TeachersController
                // Results will be locked for closed terms
                $performedActions[] = 'Results editing locked for this term';
            }

            DB::commit();

            $message = 'Term closed successfully!';
            if (!empty($performedActions)) {
                $message .= ' ' . implode('. ', $performedActions) . '.';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing term: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error closing term: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send Term Results SMS to Parents
     */
    private function sendTermResultsSMS($schoolID, $termNumber, $year)
    {
        try {
            $smsService = new SmsService();
            $school = School::find($schoolID);

            if (!$school) {
                Log::error("School not found for schoolID: {$schoolID}");
                return;
            }

            // Get all active students
            $students = Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with(['parent', 'subclass.class'])
                ->get();

            $termName = $termNumber == 1 ? 'First Term' : 'Second Term';

            foreach ($students as $student) {
                if (!$student->parent || !$student->parent->phone) {
                    continue;
                }

                // Get student's term results
                $termResults = $this->getStudentTermResults($student->studentID, $termNumber, $year, $schoolID);

                if (!$termResults || $termResults['total_marks'] == 0) {
                    // Skip if no results
                    continue;
                }

                $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);
                $parentName = trim($student->parent->first_name . ' ' . ($student->parent->last_name ?? ''));

                // Build SMS message
                $message = "{$school->school_name}. Mzazi {$parentName}, mwanafunzi {$studentName} amepata jumla ya alama {$termResults['total_marks']} daraja {$termResults['grade']} kwenye {$termName} {$year}. ";

                if ($termResults['average_marks'] > 0) {
                    $message .= "Wastani: " . number_format($termResults['average_marks'], 2) . ". ";
                }

                $message .= "Asante kwa ushirikiano wako.";

                $result = $smsService->sendSms($student->parent->phone, $message);
                
                if (!$result['success']) {
                    Log::error("Failed to send SMS to parent {$student->parent->parentID}: " . ($result['message'] ?? 'Unknown error'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending term results SMS: ' . $e->getMessage());
        }
    }

    /**
     * Get Student Term Results
     */
    private function getStudentTermResults($studentID, $termNumber, $year, $schoolID)
    {
        try {
            // Get examinations for this term
            $examinations = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->where('year', $year)
                ->where('term', $termNumber)
                ->where('approval_status', 'Approved')
                ->pluck('examID')
                ->toArray();

            if (empty($examinations)) {
                return null;
            }

            // Get student's results for these examinations
            $results = DB::table('results')
                ->where('studentID', $studentID)
                ->whereIn('examID', $examinations)
                ->whereNotNull('marks')
                ->where('status', 'allowed')
                ->get();

            if ($results->isEmpty()) {
                return null;
            }

            // Calculate total marks and average
            $totalMarks = 0;
            $subjectCount = 0;

            foreach ($results as $result) {
                if ($result->marks !== null && $result->marks !== '') {
                    $totalMarks += (float) $result->marks;
                    $subjectCount++;
                }
            }

            if ($subjectCount == 0) {
                return null;
            }

            $averageMarks = $totalMarks / $subjectCount;

            // Get student's class for grade calculation
            $student = Student::find($studentID);
            $classID = null;
            $className = '';

            if ($student && $student->subclass && $student->subclass->class) {
                $classID = $student->subclass->class->classID;
                $className = $student->subclass->class->class_name ?? '';
            }

            // Calculate grade using grade definitions
            $grade = 'F';
            if ($classID && $averageMarks > 0) {
                $gradeDefinition = GradeDefinition::where('classID', $classID)
                    ->where('first', '<=', $averageMarks)
                    ->where('last', '>=', $averageMarks)
                    ->first();

                if ($gradeDefinition) {
                    $grade = $gradeDefinition->grade;
                } else {
                    // Fallback to old logic
                    if ($averageMarks >= 75) {
                        $grade = 'A';
                    } elseif ($averageMarks >= 65) {
                        $grade = 'B';
                    } elseif ($averageMarks >= 45) {
                        $grade = 'C';
                    } elseif ($averageMarks >= 30) {
                        $grade = 'D';
                    }
                }
            }

            return [
                'total_marks' => (int) $totalMarks,
                'average_marks' => $averageMarks,
                'subject_count' => $subjectCount,
                'grade' => $grade
            ];

        } catch (\Exception $e) {
            Log::error('Error getting student term results: ' . $e->getMessage());
            return null;
        }
    }
}

