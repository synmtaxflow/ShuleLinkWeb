<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentModel;
use App\Models\SchoolSubject;
use App\Models\ClassModel;
use App\Models\Subclass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    public function school()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || !in_array($userType, ['Admin', 'Teacher'])) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get school details
        $school = School::find($schoolID);
        
        if (!$school) {
            return redirect()->route('login')->with('error', 'School not found');
        }

        // Get total counts
        $totalStudents = Student::where('schoolID', $schoolID)->count();
        $totalParents = ParentModel::where('schoolID', $schoolID)->count();
        $totalTeachers = Teacher::where('schoolID', $schoolID)->count();
        $totalSubjects = SchoolSubject::where('schoolID', $schoolID)->count();
        $totalClasses = ClassModel::where('schoolID', $schoolID)->count();
        $totalSubclasses = Subclass::join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('classes.schoolID', $schoolID)
            ->count();

        // Get student gender statistics
        $maleStudents = Student::where('schoolID', $schoolID)
            ->where('gender', 'Male')
            ->count();
        $femaleStudents = Student::where('schoolID', $schoolID)
            ->where('gender', 'Female')
            ->count();

        // Get teacher gender statistics
        $maleTeachers = Teacher::where('schoolID', $schoolID)
            ->where('gender', 'Male')
            ->count();
        $femaleTeachers = Teacher::where('schoolID', $schoolID)
            ->where('gender', 'Female')
            ->count();

        // Get active vs inactive counts
        $activeStudents = Student::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->count();
        $inactiveStudents = Student::where('schoolID', $schoolID)
            ->where('status', 'Inactive')
            ->count();

        $activeTeachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->count();
        $inactiveTeachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Inactive')
            ->count();

        // Get students by class distribution
        $studentsByClass = DB::table('students')
            ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('students.schoolID', $schoolID)
            ->select('classes.class_name', DB::raw('COUNT(students.studentID) as student_count'))
            ->groupBy('classes.class_name')
            ->orderBy('classes.class_name')
            ->get();

        // Get recent admissions (last 30 days)
        $recentAdmissions = Student::where('schoolID', $schoolID)
            ->where('admission_date', '>=', now()->subDays(30))
            ->count();

        return view('Admin.manage_school', compact(
            'school',
            'totalStudents',
            'totalParents',
            'totalTeachers',
            'totalSubjects',
            'totalClasses',
            'totalSubclasses',
            'maleStudents',
            'femaleStudents',
            'maleTeachers',
            'femaleTeachers',
            'activeStudents',
            'inactiveStudents',
            'activeTeachers',
            'inactiveTeachers',
            'studentsByClass',
            'recentAdmissions'
        ));
    }

    public function updateSchool(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');

        if (!$schoolID || !in_array($userType, ['Admin', 'Teacher'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $school = School::find($schoolID);
        
        if (!$school) {
            return response()->json(['error' => 'School not found'], 404);
        }

        // Validation with unique rules that ignore current school
        $validated = $request->validate([
            'school_name' => 'required|string|max:150',
            'registration_number' => 'nullable|string|max:50|unique:schools,registration_number,' . $schoolID . ',schoolID',
            'school_type' => 'required|in:Primary,Secondary',
            'ownership' => 'required|in:Public,Private',
            'region' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'ward' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:100|unique:schools,email,' . $schoolID . ',schoolID',
            'phone' => 'nullable|string|max:20|unique:schools,phone,' . $schoolID . ',schoolID',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'school_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);

        // Handle logo upload
        if ($request->hasFile('school_logo')) {
            // Determine upload path - Prioritize public_html for cPanel
            $basePath = base_path();
            $parentDir = dirname($basePath);
            $publicHtmlPath = $parentDir . '/public_html/logos';
            $docRootPath = $_SERVER['DOCUMENT_ROOT'] . '/logos';
            $localPublicPath = public_path('logos');

            if (file_exists($parentDir . '/public_html')) {
                $uploadPath = $publicHtmlPath;
            } elseif (strpos($_SERVER['DOCUMENT_ROOT'], 'public_html') !== false) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = $localPublicPath;
            }

            if (!file_exists($uploadPath)) {
                @mkdir($uploadPath, 0755, true);
            }

            // Delete old logo if exists
            if ($school->school_logo) {
                $possibleOldPaths = [
                    $parentDir . '/public_html/' . $school->school_logo,
                    $_SERVER['DOCUMENT_ROOT'] . '/' . $school->school_logo,
                    public_path($school->school_logo)
                ];
                foreach ($possibleOldPaths as $oldPath) {
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
            
            $logo = $request->file('school_logo');
            $filename = time() . '_' . $logo->getClientOriginalName();
            $logo->move($uploadPath, $filename);
            $validated['school_logo'] = 'logos/' . $filename;
        }

        $school->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Taarifa za shule zimesasishwa kwa mafanikio',
            'school' => $school
        ]);
    }
    
    public function get_school_details()
    {
        $schoolID = Session::get('schoolID');
        
        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }
        
        $school = School::find($schoolID);
        
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'School not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'school' => [
                'school_name' => $school->school_name,
                'registration_number' => $school->registration_number,
                'school_logo' => $school->school_logo ? asset($school->school_logo) : null,
                'school_type' => $school->school_type,
                'region' => $school->region,
                'district' => $school->district
            ]
        ]);
    }
}
