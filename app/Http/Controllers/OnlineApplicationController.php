<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\SchoolSubject;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OnlineApplicationController extends Controller
{
    // Default capacity per subclass (can be made configurable later)
    const DEFAULT_SUBCLASS_CAPACITY = 50;

    /**
     * Display the online application page
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search', '');
            
            // Get all active schools with their statistics
            $schoolsQuery = School::where('status', 'Active');
            
            if ($search) {
                $schoolsQuery->where(function($q) use ($search) {
                    $q->where('school_name', 'like', '%' . $search . '%')
                      ->orWhere('region', 'like', '%' . $search . '%')
                      ->orWhere('district', 'like', '%' . $search . '%');
                });
            }
            
            $schools = $schoolsQuery->get()->map(function ($school) {
                // Get active classes for this school
                $classes = ClassModel::where('schoolID', $school->schoolID)
                    ->where('status', 'Active')
                    ->get();
                
                $classIDs = $classes->pluck('classID')->toArray();
                
                if (empty($classIDs)) {
                    return [
                        'schoolID' => $school->schoolID,
                        'schoolName' => $school->school_name,
                        'schoolType' => $school->school_type,
                        'region' => $school->region,
                        'district' => $school->district,
                        'schoolLogo' => $school->school_logo,
                        'classesCount' => 0,
                        'subclassesCount' => 0,
                        'activeStudentsCount' => 0,
                        'classesWithSpaces' => collect(),
                        'hasAnySpaces' => false,
                    ];
                }
                
                // Get all subclasses for this school's classes in one query
                $allSubclasses = Subclass::whereIn('classID', $classIDs)
                    ->where('status', 'Active')
                    ->get()
                    ->groupBy('classID');
                
                // Get all student counts grouped by subclassID in one query
                $subclassIDs = $allSubclasses->flatten()->pluck('subclassID')->toArray();
                $studentCounts = [];
                if (!empty($subclassIDs)) {
                    $studentCounts = Student::whereIn('subclassID', $subclassIDs)
                        ->where('status', 'Active')
                        ->select(DB::raw('subclassID, COUNT(*) as count'))
                        ->groupBy('subclassID')
                        ->pluck('count', 'subclassID')
                        ->toArray();
                }
                
                // Get active students count for the school
                $activeStudentsCount = Student::where('schoolID', $school->schoolID)
                    ->where('status', 'Active')
                    ->count();
                
                // Calculate available spaces per class
                $classesWithSpaces = $classes->map(function ($class) use ($allSubclasses, $studentCounts) {
                    $classSubclasses = $allSubclasses->get($class->classID, collect());
                    
                    $totalCapacity = $classSubclasses->count() * self::DEFAULT_SUBCLASS_CAPACITY;
                    
                    $currentStudents = $classSubclasses->sum(function($subclass) use ($studentCounts) {
                        return $studentCounts[$subclass->subclassID] ?? 0;
                    });
                    
                    $availableSpaces = max(0, $totalCapacity - $currentStudents);
                    
                    return [
                        'classID' => $class->classID,
                        'className' => $class->class_name,
                        'subclassesCount' => $classSubclasses->count(),
                        'currentStudents' => $currentStudents,
                        'totalCapacity' => $totalCapacity,
                        'availableSpaces' => $availableSpaces,
                        'hasSpaces' => $availableSpaces > 0,
                    ];
                });
                
                return [
                    'schoolID' => $school->schoolID,
                    'schoolName' => $school->school_name,
                    'schoolType' => $school->school_type,
                    'region' => $school->region,
                    'district' => $school->district,
                    'schoolLogo' => $school->school_logo,
                    'classesCount' => $classes->count(),
                    'subclassesCount' => $allSubclasses->flatten()->count(),
                    'activeStudentsCount' => $activeStudentsCount,
                    'classesWithSpaces' => $classesWithSpaces,
                    'hasAnySpaces' => $classesWithSpaces->where('hasSpaces', true)->count() > 0,
                ];
            });
            
            return view('online_application', compact('schools', 'search'));
        } catch (\Exception $e) {
            \Log::error('OnlineApplicationController index error: ' . $e->getMessage());
            return view('online_application', ['schools' => collect(), 'search' => $request->get('search', '')])
                ->withErrors(['error' => 'An error occurred while loading schools. Please try again.']);
        }
    }

    /**
     * Get detailed information about a school
     */
    public function getSchoolDetails($schoolID)
    {
        $school = School::find($schoolID);
        
        if (!$school || $school->status !== 'Active') {
            return response()->json(['error' => 'School not found'], 404);
        }

        // Get active classes with their subclasses and student counts
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get()
            ->map(function ($class) {
                $subclasses = Subclass::where('classID', $class->classID)
                    ->where('status', 'Active')
                    ->get();
                
                $subclassesWithStudents = $subclasses->map(function ($subclass) {
                    $studentCount = Student::where('subclassID', $subclass->subclassID)
                        ->where('status', 'Active')
                        ->count();
                    
                    return [
                        'subclassID' => $subclass->subclassID,
                        'subclassName' => $subclass->subclass_name ?: 'Main',
                        'studentCount' => $studentCount,
                    ];
                });
                
                return [
                    'classID' => $class->classID,
                    'className' => $class->class_name,
                    'subclassesCount' => $subclasses->count(),
                    'subclasses' => $subclassesWithStudents,
                ];
            });

        // Get school subjects
        $schoolSubjects = SchoolSubject::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('subject_name')
            ->get(['subjectID', 'subject_name', 'subject_code']);

        // Get class subjects (grouped by class)
        $classSubjects = ClassSubject::whereHas('class', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID)->where('status', 'Active');
            })
            ->where('status', 'Active')
            ->with(['subject', 'class', 'teacher'])
            ->get()
            ->groupBy('classID')
            ->map(function ($subjects, $classID) {
                return $subjects->map(function ($classSubject) {
                    return [
                        'subjectName' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                        'subjectCode' => $classSubject->subject ? $classSubject->subject->subject_code : '',
                        'teacherName' => $classSubject->teacher 
                            ? ($classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name)
                            : 'Not Assigned',
                    ];
                });
            });

        // Get all teachers with their roles
        $teachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get()
            ->map(function ($teacher) use ($schoolID) {
                // Get teacher roles
                $roles = DB::table('role_user')
                    ->join('roles', 'role_user.role_id', '=', 'roles.id')
                    ->where('role_user.teacher_id', $teacher->id)
                    ->where('roles.schoolID', $schoolID)
                    ->pluck('roles.role_name')
                    ->toArray();
                
                // Also check if teacher is a class teacher (coordinator)
                $isCoordinator = ClassModel::where('teacherID', $teacher->id)
                    ->where('schoolID', $schoolID)
                    ->exists();
                
                if ($isCoordinator && !in_array('Coordinator', $roles)) {
                    $roles[] = 'Coordinator';
                }
                
                // Check if teacher is a class teacher (subclass teacher)
                $isClassTeacher = Subclass::where('teacherID', $teacher->id)
                    ->whereHas('class', function($query) use ($schoolID) {
                        $query->where('schoolID', $schoolID);
                    })
                    ->exists();
                
                if ($isClassTeacher && !in_array('Class Teacher', $roles)) {
                    $roles[] = 'Class Teacher';
                }
                
                return [
                    'teacherID' => $teacher->id,
                    'firstName' => $teacher->first_name,
                    'middleName' => $teacher->middle_name,
                    'lastName' => $teacher->last_name,
                    'fullName' => trim($teacher->first_name . ' ' . ($teacher->middle_name ?? '') . ' ' . $teacher->last_name),
                    'position' => $teacher->position,
                    'roles' => $roles,
                ];
            });

        return response()->json([
            'success' => true,
            'school' => [
                'schoolID' => $school->schoolID,
                'schoolName' => $school->school_name,
                'schoolType' => $school->school_type,
                'ownership' => $school->ownership,
                'region' => $school->region,
                'district' => $school->district,
                'ward' => $school->ward,
                'village' => $school->village,
                'address' => $school->address,
                'email' => $school->email,
                'phone' => $school->phone,
                'establishedYear' => $school->established_year,
                'schoolLogo' => $school->school_logo,
            ],
            'classes' => $classes,
            'schoolSubjects' => $schoolSubjects,
            'classSubjects' => $classSubjects,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Show the application form
     */
    public function showApplicationForm(Request $request)
    {
        $schoolID = $request->query('school');
        $classID = $request->query('class');

        // Validate school and class
        if (!$schoolID || !$classID) {
            return redirect()->route('online_application')
                ->with('error', 'Please select a school and class to apply.');
        }

        $school = School::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$school) {
            return redirect()->route('online_application')
                ->with('error', 'Selected school not found or is inactive.');
        }

        $class = ClassModel::where('classID', $classID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$class) {
            return redirect()->route('online_application')
                ->with('error', 'Selected class not found or is inactive.');
        }

        // Check if class has available spaces
        $subclasses = Subclass::where('classID', $classID)
            ->where('status', 'Active')
            ->get();

        $totalCapacity = $subclasses->count() * self::DEFAULT_SUBCLASS_CAPACITY;
        $currentStudents = Student::whereIn('subclassID', $subclasses->pluck('subclassID'))
            ->where('status', 'Active')
            ->count();

        $availableSpaces = max(0, $totalCapacity - $currentStudents);

        if ($availableSpaces <= 0) {
            return redirect()->route('online_application')
                ->with('error', 'No available spaces in the selected class.');
        }

        return view('apply', compact('school', 'class', 'availableSpaces'));
    }

    /**
     * Store the application
     */
    public function storeApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|exists:schools,schoolID',
            'classID' => 'required|exists:classes,classID',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'is_disabled' => 'nullable|boolean',
            'has_epilepsy' => 'nullable|boolean',
            'has_allergies' => 'nullable|boolean',
            'allergies_details' => 'nullable|string|max:500',
        ], [
            'photo.image' => 'Photo must be an image file.',
            'photo.mimes' => 'Photo must be a jpg, jpeg, or png file.',
            'photo.max' => 'Photo must not exceed 2MB.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $schoolID = $request->schoolID;
        $classID = $request->classID;

        // Verify school and class are active
        $school = School::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$school) {
            return back()
                ->with('error', 'Selected school not found or is inactive.')
                ->withInput();
        }

        $class = ClassModel::where('classID', $classID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$class) {
            return back()
                ->with('error', 'Selected class not found or is inactive.')
                ->withInput();
        }

        // Check if class has available spaces
        $subclasses = Subclass::where('classID', $classID)
            ->where('status', 'Active')
            ->get();

        $totalCapacity = $subclasses->count() * self::DEFAULT_SUBCLASS_CAPACITY;
        $currentStudents = Student::whereIn('subclassID', $subclasses->pluck('subclassID'))
            ->where('status', 'Active')
            ->count();

        $availableSpaces = max(0, $totalCapacity - $currentStudents);

        if ($availableSpaces <= 0) {
            return back()
                ->with('error', 'No available spaces in the selected class.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle Image Upload
            $imageName = null;
            if ($request->hasFile('photo')) {
                $uploadPath = public_path('userImages');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $imageName = time() . '_' . $request->file('photo')->getClientOriginalName();
                $request->file('photo')->move($uploadPath, $imageName);
            }

            // Generate unique application number (username)
            $applicationNumber = 'APP/' . $schoolID . '/' . time();
            $counter = 1;
            while (User::where('name', $applicationNumber)->exists()) {
                $applicationNumber = 'APP/' . $schoolID . '/' . time() . '_' . $counter;
                $counter++;
            }

            // Generate random password (8 characters)
            $password = Str::random(8);
            $hashedPassword = Hash::make($password);

            // Create user account for applicant
            $userEmail = $applicationNumber . '@application.local';
            $emailCounter = 1;
            while (User::where('email', $userEmail)->exists()) {
                $userEmail = $applicationNumber . '_' . $emailCounter . '@application.local';
                $emailCounter++;
            }

            $user = User::create([
                'name' => $applicationNumber,
                'email' => $userEmail,
                'password' => $hashedPassword,
                'user_type' => 'student',
            ]);

            // Get first available subclass for this class (will be reassigned by admin on acceptance)
            $firstSubclass = $subclasses->first();
            
            // Generate temporary studentID (will be changed when accepted)
            // For now, use a unique number based on timestamp
            $tempStudentID = (int) (time() % 999999);
            while (Student::where('studentID', $tempStudentID)->exists() || 
                   User::where('fingerprint_id', $tempStudentID)->exists()) {
                $tempStudentID = (int) ((time() + rand(1, 1000)) % 999999);
            }

            // Create student with status 'Applied'
            $student = Student::create([
                'studentID' => $tempStudentID,
                'schoolID' => $schoolID,
                'subclassID' => $firstSubclass->subclassID ?? null, // Temporary, will be reassigned by admin
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?: null,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'admission_number' => $applicationNumber,
                'address' => $request->address ?: null,
                'photo' => $imageName,
                'status' => 'Applied',
                'is_disabled' => $request->has('is_disabled') && $request->is_disabled == '1' ? true : false,
                'has_epilepsy' => $request->has('has_epilepsy') && $request->has_epilepsy == '1' ? true : false,
                'has_allergies' => $request->has('has_allergies') && $request->has_allergies == '1' ? true : false,
                'allergies_details' => $request->has_allergies == '1' ? ($request->allergies_details ?: null) : null,
            ]);

            DB::commit();

            // Return success view with credentials
            return view('application_success', [
                'applicationNumber' => $applicationNumber,
                'password' => $password,
                'studentName' => $request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name,
                'schoolName' => $school->school_name,
                'className' => $class->class_name,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'An error occurred while submitting your application. Please try again.')
                ->withInput();
        }
    }
}

