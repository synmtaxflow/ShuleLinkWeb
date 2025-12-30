<?php

namespace App\Http\Controllers;

use App\Models\SchoolSubject;
use App\Models\ClassSubject;
use App\Models\Subclass;
use App\Models\Teacher;
use App\Models\SubjectElector;
use App\Models\Student;
use App\Models\School;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageSubjectController extends Controller
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

    public function manageSubjects()
    {
         $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');

        // Get all school subjects
        $schoolSubjects = SchoolSubject::where('schoolID', $schoolID)
            ->orderBy('subject_name')
            ->get();

        // Get all subclasses for dropdown with combinations
        // Use Eloquent to get subclasses with relationships
        $allSubclasses = \App\Models\Subclass::with(['class', 'combie'])
            ->whereHas('class', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->get()
            ->groupBy('classID');
        
        // Filter out default subclasses and format display names
        $subclasses = collect();
        foreach ($allSubclasses as $classID => $classSubclasses) {
            // If class has only one subclass and it's default (empty name), use it but show only class name
            if ($classSubclasses->count() === 1) {
                $subclass = $classSubclasses->first();
                if (trim($subclass->subclass_name) === '') {
                    // This is a default subclass - include it but with display name as class name only
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $subclass->class->class_name,
                        'stream_code' => $subclass->stream_code,
                        'class_name' => $subclass->class->class_name,
                        'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                        'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                    ]);
                } else {
                    // Single subclass with name - show class_name + subclass_name
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $subclass->class->class_name . ' ' . $subclass->subclass_name,
                        'stream_code' => $subclass->stream_code,
                        'class_name' => $subclass->class->class_name,
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
                    
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $displayName,
                        'stream_code' => $subclass->stream_code,
                        'class_name' => $subclass->class->class_name,
                        'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                        'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                    ]);
                }
            }
        }
        
        // Sort by class_name then subclass_name
        $subclasses = $subclasses->sortBy(function($item) {
            return $item->class_name . ' ' . $item->subclass_name;
        })->values();

        // Get all teachers for dropdown
        $teachers = Teacher::where('schoolID', $schoolID)->get();

        // Get school details for conditional display
        $school_details = \App\Models\School::find($schoolID);

        return view('Admin.manageSubject', compact('schoolSubjects', 'subclasses', 'teachers', 'school_details'));
    }

    public function save_school_subject(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('create_subject')) {
            return response()->json([
                'error' => 'You do not have permission to create subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Check if request has multiple subjects or single subject (backward compatibility)
            $subjects = $request->has('subjects') && is_array($request->subjects) 
                ? $request->subjects 
                : [[
                    'subject_name' => $request->subject_name,
                    'subject_code' => $request->subject_code,
                    'status' => $request->status,
                    'student_status' => $request->student_status,
                ]];

            $createdSubjects = [];
            $errors = [];
            $hasApprovalPermission = $this->hasPermission('approve_created_subject');

            foreach ($subjects as $index => $subjectData) {
                // Validate each subject
                $validator = Validator::make($subjectData, [
                    'subject_name' => 'required|string|max:100',
                    'subject_code' => 'nullable|string|max:20',
                    'status' => 'required|in:Active,Inactive',
                    'student_status' => 'nullable|in:Required,Optional',
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->toArray() as $field => $messages) {
                        $errors[] = "Subject " . ($index + 1) . ": " . $messages[0];
                    }
                    continue;
                }

                // Check if subject already exists for this school
                $existingSubject = SchoolSubject::where('schoolID', $schoolID)
                    ->where('subject_name', $subjectData['subject_name'])
                    ->first();

                if ($existingSubject) {
                    $errors[] = "Subject " . ($index + 1) . ": Subject '" . $subjectData['subject_name'] . "' already exists for this school.";
                    continue;
                }

                // Check if user has approval permission - if yes, use requested status, otherwise set to Inactive
                $status = $hasApprovalPermission ? ($subjectData['status'] ?? 'Active') : 'Inactive';

                try {
                    $subject = SchoolSubject::create([
                        'schoolID' => $schoolID,
                        'subject_name' => $subjectData['subject_name'],
                        'subject_code' => $subjectData['subject_code'] ?? null,
                        'status' => $status,
                    ]);

                    $createdSubjects[] = $subject;
                } catch (\Exception $e) {
                    $errors[] = "Subject " . ($index + 1) . ": Failed to create - " . $e->getMessage();
                }
            }

            // Return response
            if (count($errors) > 0 && count($createdSubjects) === 0) {
                // All failed
                return response()->json([
                    'error' => implode('<br>', $errors)
                ], 400);
            } elseif (count($errors) > 0) {
                // Some succeeded, some failed
                $successCount = count($createdSubjects);
                $errorCount = count($errors);
                return response()->json([
                    'success' => $successCount . ' subject(s) created successfully. ' . $errorCount . ' subject(s) failed.',
                    'errors' => $errors,
                    'subjects' => $createdSubjects
                ], 200);
            } else {
                // All succeeded
                $subjectNames = array_map(function($s) { return $s->subject_name; }, $createdSubjects);
                return response()->json([
                    'success' => count($createdSubjects) . ' subject(s) created successfully: ' . implode(', ', $subjectNames),
                    'subjects' => $createdSubjects
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_school_subject(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('edit_subject')) {
            return response()->json([
                'error' => 'You do not have permission to edit subjects.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'subjectID' => 'required|exists:school_subjects,subjectID',
                'subject_name' => 'required|string|max:100',
                'subject_code' => 'nullable|string|max:20',
                'status' => 'required|in:Active,Inactive',
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
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subject = SchoolSubject::where('subjectID', $request->subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    'error' => 'Subject not found or does not belong to this school.'
                ], 404);
            }

            // Check if subject name already exists (excluding current subject)
            $existingSubject = SchoolSubject::where('schoolID', $schoolID)
                ->where('subject_name', $request->subject_name)
                ->where('subjectID', '!=', $request->subjectID)
                ->first();

            if ($existingSubject) {
                return response()->json([
                    'error' => 'Subject "' . $request->subject_name . '" already exists for this school.'
                ], 400);
            }

            $subject->update([
                'subject_name' => $request->subject_name,
                'subject_code' => $request->subject_code ?? null,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => 'Subject "' . $subject->subject_name . '" updated successfully!',
                'subject' => $subject
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_school_subject($subjectID)
    {
        try {
            // Check permission - Admin has all permissions
            $userType = Session::get('user_type');
            if ($userType !== 'Admin' && !$this->hasPermission('delete_subject')) {
                return response()->json([
                    'error' => 'You do not have permission to delete subjects.'
                ], 403);
            }

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subject = SchoolSubject::where('subjectID', $subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    'error' => 'Subject not found or does not belong to this school.'
                ], 404);
            }

            // Check if subject is used in any class
            $classSubjectsCount = ClassSubject::where('subjectID', $subjectID)->count();
            if ($classSubjectsCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete subject. It is currently assigned to ' . $classSubjectsCount . ' class(es). Please remove it from all classes first.'
                ], 400);
            }

            $subjectName = $subject->subject_name;
            
            // Delete the subject
            try {
                $subject->delete();
            } catch (\Exception $deleteException) {
                \Log::error('Delete subject database error: ' . $deleteException->getMessage());
                return response()->json([
                    'error' => 'Failed to delete subject from database: ' . $deleteException->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => 'Subject "' . $subjectName . '" deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Delete school subject error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'An error occurred while deleting subject: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_class_subjects_by_subclass($subclassID = null)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $request = request();
            $classID = $request->input('classID');
            $isCoordinatorView = $request->input('coordinator') === 'true';

            // If coordinator view, get subjects for all subclasses in the main class
            if ($isCoordinatorView && $classID) {
                // Get all subclasses for this main class
                $subclassIDs = \App\Models\Subclass::where('classID', $classID)
                    ->where('status', 'Active')
                    ->pluck('subclassID')
                    ->toArray();
                
                if (empty($subclassIDs)) {
                    return response()->json([
                        'success' => true,
                        'subjects' => []
                    ], 200);
                }

                $subjects = ClassSubject::whereIn('subclassID', $subclassIDs)
                    ->with(['subject', 'subclass'])
                    ->get()
                    ->map(function($classSubject) {
                        // Get election counts
                        $totalElectors = SubjectElector::where('classSubjectID', $classSubject->class_subjectID)->count();
                        $totalStudents = Student::where('subclassID', $classSubject->subclassID)
                            ->where('status', 'Active')
                            ->count();
                        $nonElectors = $totalStudents - $totalElectors;
                        
                        // Get subclass display name
                        $subclassDisplay = 'N/A';
                        if ($classSubject->subclass) {
                            $subclassName = trim($classSubject->subclass->subclass_name ?? '');
                            $className = $classSubject->subclass->class ? $classSubject->subclass->class->class_name : '';
                            $subclassDisplay = empty($subclassName) 
                                ? $className 
                                : $className . ' - ' . $subclassName;
                        }
                        
                        return [
                            'subjectID' => $classSubject->subjectID,
                            'class_subjectID' => $classSubject->class_subjectID,
                            'subclassID' => $classSubject->subclassID,
                            'subclass_display' => $subclassDisplay,
                            'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : null,
                            'student_status' => $classSubject->student_status ?? null,
                            'elected_count' => $totalElectors,
                            'non_elected_count' => $nonElectors,
                            'total_students' => $totalStudents,
                        ];
                    });
            } else {
                // Regular subclass view
                if (!$subclassID) {
                    return response()->json([
                        'error' => 'Subclass ID is required.'
                    ], 400);
                }

                $subjects = ClassSubject::where('subclassID', $subclassID)
                    ->with('subject')
                    ->get()
                    ->map(function($classSubject) {
                        // Get election counts
                        $totalElectors = SubjectElector::where('classSubjectID', $classSubject->class_subjectID)->count();
                        $totalStudents = Student::where('subclassID', $classSubject->subclassID)
                            ->where('status', 'Active')
                            ->count();
                        $nonElectors = $totalStudents - $totalElectors;
                        
                        return [
                            'subjectID' => $classSubject->subjectID,
                            'class_subjectID' => $classSubject->class_subjectID,
                            'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : null,
                            'student_status' => $classSubject->student_status ?? null,
                            'elected_count' => $totalElectors,
                            'non_elected_count' => $nonElectors,
                            'total_students' => $totalStudents,
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'subjects' => $subjects
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save_class_subject(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('create_class_subject')) {
            return response()->json([
                'error' => 'You do not have permission to create class subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Validate subclassID
            $validator = Validator::make($request->all(), [
                'subclassID' => 'required|exists:subclasses,subclassID',
                'subjects' => 'required|array|min:1',
                'subjects.*.subjectID' => 'nullable|exists:school_subjects,subjectID',
                'subjects.*.new_subject_name' => 'nullable|string|max:100|required_with:subjects.*.new_subject_code',
                'subjects.*.new_subject_code' => 'nullable|string|max:20',
                'subjects.*.teacherID' => 'nullable|exists:teachers,id',
                'subjects.*.status' => 'required|in:Active,Inactive',
                'subjects.*.student_status' => 'nullable|in:Required,Optional',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Get subclass to get classID
            $subclass = Subclass::find($request->subclassID);
            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            // Verify subclass belongs to this school
            $class = $subclass->class;
            if ($class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access to this subclass.'
                ], 403);
            }

            DB::beginTransaction();

            $createdSubjects = [];
            $errors = [];
            $checkIfUserHasApprovalPermission = $this->hasPermission('approve_created_subject');

            foreach ($request->subjects as $index => $subjectData) {
                try {
                    $subjectID = $subjectData['subjectID'] ?? null;
                    $newSubjectName = $subjectData['new_subject_name'] ?? null;
                    $newSubjectCode = $subjectData['new_subject_code'] ?? null;
                    $teacherID = $subjectData['teacherID'] ?? null;
                    $status = $subjectData['status'] ?? 'Active';
                    $studentStatus = $subjectData['student_status'] ?? null;

                    // Determine if this is a new subject
                    $isNewSubject = ($subjectID === '__new_subject__' || $subjectID === null) && !empty($newSubjectName);

                    // Create new subject if needed
                    if ($isNewSubject) {
                        // Check if subject with same name already exists in school
                        $existingSubject = SchoolSubject::where('schoolID', $schoolID)
                            ->where('subject_name', $newSubjectName)
                            ->first();

                        if ($existingSubject) {
                            $subjectID = $existingSubject->subjectID;
                        } else {
                            // Create new school subject
                            $newSchoolSubject = SchoolSubject::create([
                                'schoolID' => $schoolID,
                                'subject_name' => $newSubjectName,
                                'subject_code' => $newSubjectCode,
                                'status' => 'Active',
                            ]);
                            $subjectID = $newSchoolSubject->subjectID;
                        }
                    }

                    if (!$subjectID) {
                        $errors[] = "Subject " . ($index + 1) . ": Please select a subject or provide a subject name.";
                        continue;
                    }

                    // Verify subject belongs to this school
                    $subject = SchoolSubject::find($subjectID);
                    if (!$subject || $subject->schoolID != $schoolID) {
                        $errors[] = "Subject " . ($index + 1) . ": Subject not found or does not belong to this school.";
                        continue;
                    }

                    // Handle teacherID
                    if ($teacherID === null || $teacherID === '' || $teacherID === '0' || $teacherID === false) {
                        $teacherID = null;
                    } else {
                        $teacherID = (int) $teacherID;
                        $teacher = Teacher::find($teacherID);
                        if (!$teacher || $teacher->schoolID != $schoolID) {
                            $errors[] = "Subject " . ($index + 1) . ": Selected teacher not found or does not belong to this school.";
                            continue;
                        }
                    }

                    // Integrity Constraint: Check if subject already assigned to this subclass only
                    // Note: Removed class-level validation - subject can exist in multiple subclasses within same class
                    // (e.g., in secondary schools, each subclass can have Mathematics, English, etc.)
                    $existingClassSubjectBySubclass = ClassSubject::where('subclassID', $request->subclassID)
                        ->where('subjectID', $subjectID)
                        ->first();

                    if ($existingClassSubjectBySubclass) {
                        $errors[] = "Subject " . ($index + 1) . ": Subject '" . $subject->subject_name . "' is already assigned to this subclass.";
                        continue;
                    }

                    // Integrity Constraint: Check if teacher is already assigned to this subject in this subclass only
                    if ($teacherID) {
                        $existingTeacherSubject = ClassSubject::where('subclassID', $request->subclassID)
                            ->where('subjectID', $subjectID)
                            ->where('teacherID', $teacherID)
                            ->first();

                        if ($existingTeacherSubject) {
                            $errors[] = "Subject " . ($index + 1) . ": This teacher is already assigned to teach '" . $subject->subject_name . "' in this subclass.";
                            continue;
                        }
                    }

                    // Check if user has approval permission - if yes, use requested status, otherwise set to Inactive
                    $finalStatus = $checkIfUserHasApprovalPermission ? $status : 'Inactive';

                    $classSubject = ClassSubject::create([
                        'classID' => $subclass->classID,
                        'subclassID' => $request->subclassID,
                        'subjectID' => $subjectID,
                        'teacherID' => $teacherID,
                        'status' => $finalStatus,
                        'student_status' => $studentStatus,
                    ]);

                    $createdSubjects[] = $classSubject;

                } catch (\Exception $e) {
                    $errors[] = "Subject " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            if (!empty($errors) && empty($createdSubjects)) {
                DB::rollBack();
                return response()->json([
                    'error' => implode("\n", $errors)
                ], 400);
            }

            if (!empty($errors)) {
                // Some subjects were created, some failed
                DB::commit();
                $successCount = count($createdSubjects);
                $errorCount = count($errors);
                return response()->json([
                    'success' => "Successfully created {$successCount} subject(s). " . ($errorCount > 0 ? "{$errorCount} subject(s) failed: " . implode("; ", $errors) : ""),
                    'createdSubjects' => $createdSubjects,
                    'warnings' => $errors
                ], 200);
            }

            DB::commit();

            $subjectCount = count($createdSubjects);
            $message = $subjectCount > 1 
                ? "Successfully assigned {$subjectCount} subjects to subclass!" 
                : "Subject assigned to subclass successfully!";

            return response()->json([
                'success' => $message,
                'createdSubjects' => $createdSubjects
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_class_subjects()
    {
        // Check permission
        if (!$this->hasPermission('view_class_subjects')) {
            return response()->json([
                'error' => 'You do not have permission to view class subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Get all subclasses with their subjects
            $subclasses = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->select(
                    'subclasses.subclassID',
                    'subclasses.subclass_name',
                    'subclasses.stream_code',
                    'classes.class_name'
                )
                ->orderBy('classes.class_name')
                ->orderBy('subclasses.subclass_name')
                ->get()
                ->map(function($subclass) {
                    // Get subjects for this subclass (show all, not just Active)
                    $subjects = ClassSubject::with(['subject', 'teacher'])
                        ->where('subclassID', $subclass->subclassID)
                        ->get()
                        ->map(function($classSubject) {
                            // Get election counts
                            $totalElectors = SubjectElector::where('classSubjectID', $classSubject->class_subjectID)->count();
                            $totalStudents = Student::where('subclassID', $classSubject->subclassID)
                                ->where('status', 'Active')
                                ->count();
                            $nonElectors = $totalStudents - $totalElectors;
                            
                            return [
                                'class_subjectID' => $classSubject->class_subjectID,
                                'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                                'subject_code' => $classSubject->subject ? $classSubject->subject->subject_code : null,
                                'teacher_name' => $classSubject->teacher
                                    ? $classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name
                                    : 'Not Assigned',
                                'teacher_id' => $classSubject->teacherID,
                                'status' => $classSubject->status ?? 'Inactive',
                                'student_status' => $classSubject->student_status ?? null,
                                'elected_count' => $totalElectors,
                                'non_elected_count' => $nonElectors,
                                'total_students' => $totalStudents,
                            ];
                        });

                    return [
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'stream_code' => $subclass->stream_code,
                        'class_name' => $subclass->class_name,
                        'subjects' => $subjects,
                        'subject_count' => $subjects->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'subclasses' => $subclasses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_class_subject(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('update_class_subject')) {
            return response()->json([
                'error' => 'You do not have permission to update class subjects.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'class_subjectID' => 'required|exists:class_subjects,class_subjectID',
                'subclassID' => 'required|exists:subclasses,subclassID',
                'teacherID' => 'nullable|exists:teachers,id',
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
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $classSubject = ClassSubject::find($request->class_subjectID);
            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found.'
                ], 404);
            }

            // Verify subclass belongs to this school
            $subclass = Subclass::find($request->subclassID);
            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            $class = $subclass->class;
            if ($class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            // Verify teacher belongs to this school if provided
            $teacherID = $request->input('teacherID');
            if ($teacherID === null || $teacherID === '' || $teacherID === '0' || $teacherID === false) {
                $teacherID = null;
            } else {
                $teacherID = (int) $teacherID;
                $teacher = Teacher::find($teacherID);
                if (!$teacher || $teacher->schoolID != $schoolID) {
                    return response()->json([
                        'error' => 'Selected teacher not found or does not belong to this school.'
                    ], 404);
                }
            }

            // Get old teacher ID before update
            $oldTeacherID = $classSubject->teacherID;
            
            // Update teacher
            $classSubject->teacherID = $teacherID;
            $classSubject->save();

            // Update all session timetables for this class_subjectID if teacher changed
            if ($oldTeacherID != $teacherID) {
                try {
                    // Get definition ID
                    $definition = DB::table('session_timetable_definitions')
                        ->where('schoolID', $schoolID)
                        ->first();
                    
                    if ($definition) {
                        // Update all sessions with this class_subjectID
                        $updatedCount = DB::table('class_session_timetables')
                            ->where('class_subjectID', $classSubject->class_subjectID)
                            ->where('definitionID', $definition->definitionID)
                            ->update([
                                'teacherID' => $teacherID,
                                'updated_at' => now()
                            ]);
                        
                        \Log::info('Updated ' . $updatedCount . ' session timetable(s) for class_subjectID: ' . $classSubject->class_subjectID . ' from teacher ' . $oldTeacherID . ' to ' . $teacherID);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating session timetables after teacher change: ' . $e->getMessage());
                    // Don't fail the request if session update fails
                }
            }

            return response()->json([
                'success' => 'Subject teacher updated successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_class_subject($classSubjectID)
    {
        // Check permission
        if (!$this->hasPermission('delete_class_subject')) {
            return response()->json([
                'error' => 'You do not have permission to delete class subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $classSubject = ClassSubject::find($classSubjectID);
            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found.'
                ], 404);
            }

            // Verify subclass belongs to this school
            $subclass = Subclass::find($classSubject->subclassID);
            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            $class = $subclass->class;
            if ($class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            $classSubject->delete();

            return response()->json([
                'success' => 'Subject removed from subclass successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate_subject(Request $request, $subjectID)
    {
        // Check permission
        if (!$this->hasPermission('activate_subject')) {
            return response()->json([
                'error' => 'You do not have permission to activate subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subject = SchoolSubject::where('subjectID', $subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    'error' => 'Subject not found or unauthorized access.'
                ], 404);
            }

            // Toggle status
            $newStatus = $subject->status === 'Active' ? 'Inactive' : 'Active';
            $subject->status = $newStatus;
            $subject->save();

            return response()->json([
                'success' => 'Subject "' . $subject->subject_name . '" has been ' . strtolower($newStatus) . 'd successfully!',
                'status' => $newStatus
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate_class_subject(Request $request, $classSubjectID)
    {
        // Check permission
        if (!$this->hasPermission('activate_class_subject')) {
            return response()->json([
                'error' => 'You do not have permission to activate class subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $classSubject = ClassSubject::find($classSubjectID);

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found.'
                ], 404);
            }

            // Verify subclass belongs to this school
            $subclass = Subclass::find($classSubject->subclassID);
            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            $class = $subclass->class;
            if ($class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            // Toggle status
            $newStatus = $classSubject->status === 'Active' ? 'Inactive' : 'Active';
            $classSubject->status = $newStatus;
            $classSubject->save();

            $subjectName = $classSubject->subject ? $classSubject->subject->subject_name : 'Subject';

            return response()->json([
                'success' => 'Class subject "' . $subjectName . '" has been ' . strtolower($newStatus) . 'd successfully!',
                'status' => $newStatus
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_subject_electors($classSubjectID)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['error' => 'School ID not found in session.'], 400);
            }

            $classSubject = ClassSubject::with('subclass.class')->find($classSubjectID);
            if (!$classSubject) {
                return response()->json(['error' => 'Class subject not found.'], 404);
            }
            
            if (!$classSubject->subclass || !$classSubject->subclass->class) {
                return response()->json(['error' => 'Subclass or class not found for this subject.'], 404);
            }
            
            if ($classSubject->subclass->class->schoolID != $schoolID) {
                return response()->json(['error' => 'Class subject does not belong to this school.'], 403);
            }

            $electors = SubjectElector::where('classSubjectID', $classSubjectID)
                ->get()
                ->map(function($elector) {
                    $student = Student::find($elector->studentID);
                    return [
                        'electorID' => $elector->electorID,
                        'studentID' => $elector->studentID,
                        'student_name' => $student ? $student->first_name . ' ' . $student->last_name : 'N/A',
                    ];
                });

            // Return empty array if no electors (not an error)
            return response()->json([
                'success' => true,
                'electors' => $electors->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save_subject_election(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'classSubjectID' => 'required|exists:class_subjects,class_subjectID',
                'subclassID' => 'required|exists:subclasses,subclassID',
                'selectedStudents' => 'nullable|array',
                'selectedStudents.*' => 'exists:students,studentID',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['error' => 'School ID not found in session.'], 400);
            }

            $classSubject = ClassSubject::with(['subclass.class', 'subject'])->find($request->classSubjectID);
            if (!$classSubject || $classSubject->subclass->class->schoolID != $schoolID) {
                return response()->json(['error' => 'Class subject not found or does not belong to this school.'], 404);
            }

            // Verify subclass matches
            if ($classSubject->subclassID != $request->subclassID) {
                return response()->json(['error' => 'Subclass mismatch.'], 400);
            }

            DB::beginTransaction();

            // Get current electors
            $currentElectors = SubjectElector::where('classSubjectID', $request->classSubjectID)
                ->pluck('studentID')
                ->toArray();

            $selectedStudents = $request->selectedStudents ?? [];
            $toAdd = array_diff($selectedStudents, $currentElectors);
            $toRemove = array_diff($currentElectors, $selectedStudents);

            // Add new electors
            foreach ($toAdd as $studentID) {
                // Verify student belongs to this subclass
                $student = Student::where('studentID', $studentID)
                    ->where('subclassID', $request->subclassID)
                    ->first();

                if ($student) {
                    SubjectElector::create([
                        'studentID' => $studentID,
                        'classSubjectID' => $request->classSubjectID,
                    ]);

                    // TODO: Send SMS to parent
                    // $this->sendElectionSMS($student, $classSubject, 'elected');
                }
            }

            // Remove deselected electors
            foreach ($toRemove as $studentID) {
                $elector = SubjectElector::where('studentID', $studentID)
                    ->where('classSubjectID', $request->classSubjectID)
                    ->first();

                if ($elector) {
                    $student = Student::find($studentID);
                    $elector->delete();

                    // Send SMS to parent
                    $this->sendElectionSMS($student, $classSubject, 'deselected');
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Subject election updated successfully!',
                'added' => count($toAdd),
                'removed' => count($toRemove)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deselect_student(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'classSubjectID' => 'required|exists:class_subjects,class_subjectID',
                'studentID' => 'required|exists:students,studentID',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['error' => 'School ID not found in session.'], 400);
            }

            $classSubject = ClassSubject::with(['subclass.class', 'subject'])->find($request->classSubjectID);
            if (!$classSubject || $classSubject->subclass->class->schoolID != $schoolID) {
                return response()->json(['error' => 'Class subject not found or does not belong to this school.'], 404);
            }

            // Verify student belongs to this subclass
            $student = Student::where('studentID', $request->studentID)
                ->where('subclassID', $classSubject->subclassID)
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or does not belong to this subclass.'], 404);
            }

            // Remove from election
            $elector = SubjectElector::where('studentID', $request->studentID)
                ->where('classSubjectID', $request->classSubjectID)
                ->first();

            if ($elector) {
                $elector->delete();

                // Send SMS to parent
                $this->sendElectionSMS($student, $classSubject, 'deselected');

                return response()->json([
                    'success' => 'Student deselected from subject election successfully!'
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Student is not currently elected for this subject.'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS notification to parent when student is elected/deselected from optional subject
     *
     * @param Student $student
     * @param ClassSubject $classSubject
     * @param string $action 'elected' or 'deselected'
     * @return void
     */
    private function sendElectionSMS($student, $classSubject, $action = 'elected')
    {
        try {
            // Load relationships if not already loaded
            if (!$student->relationLoaded('parent')) {
                $student->load('parent');
            }
            if (!$student->relationLoaded('subclass')) {
                $student->load('subclass');
            }
            if (!$classSubject->relationLoaded('subject')) {
                $classSubject->load('subject');
            }
            if (!$classSubject->relationLoaded('subclass')) {
                $classSubject->load('subclass');
            }
            if (!$classSubject->subclass->relationLoaded('class')) {
                $classSubject->subclass->load('class');
            }

            // Check if student has a parent
            if (!$student->parent) {
                Log::info("Student {$student->studentID} has no parent, skipping SMS notification.");
                return;
            }

            // Check if parent has a phone number
            if (!$student->parent->phone || trim($student->parent->phone) === '') {
                Log::info("Parent {$student->parent->parentID} has no phone number, skipping SMS notification.");
                return;
            }

            // Get school name
            $schoolID = Session::get('schoolID');
            $school = School::where('schoolID', $schoolID)->first();
            $schoolName = $school ? $school->school_name : 'ShuleLink';

            // Get student name
            $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);

            // Get subject name
            $subjectName = $classSubject->subject ? $classSubject->subject->subject_name : 'Subject';

            // Get class and subclass display name
            $classDisplayName = '';
            if ($classSubject->subclass && $classSubject->subclass->class) {
                $className = $classSubject->subclass->class->class_name;
                $subclassName = trim($classSubject->subclass->subclass_name);
                if ($subclassName === '') {
                    $classDisplayName = $className;
                } else {
                    $classDisplayName = $className . ' ' . $subclassName;
                }
            }

            // Build message based on action
            if ($action === 'elected') {
                $message = "{$schoolName}. Mwanafunzi {$studentName} amechagua somo la {$subjectName} kwenye darasa {$classDisplayName}. Asante";
            } else {
                $message = "{$schoolName}. Mwanafunzi {$studentName} ameondoa somo la {$subjectName} kwenye darasa {$classDisplayName}. Asante";
            }

            // Send SMS
            $smsService = new SmsService();
            $result = $smsService->sendSms($student->parent->phone, $message);

            if ($result['success']) {
                Log::info("SMS sent successfully to parent {$student->parent->parentID} for student {$student->studentID} - {$action} from {$subjectName}");
            } else {
                Log::warning("Failed to send SMS to parent {$student->parent->parentID} for student {$student->studentID}: " . ($result['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error("Error sending election SMS: " . $e->getMessage());
            // Don't throw exception - SMS failure shouldn't break the election process
        }
    }
}
