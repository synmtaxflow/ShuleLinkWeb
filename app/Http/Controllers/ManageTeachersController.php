<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Teacher;
use App\Models\School;
use App\Models\User;
use App\Services\ZKTecoService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
// use Spatie\Permission\Models\Permission; // Will be enabled after package installation

class ManageTeachersController extends Controller
{
    /**
     * Determine permission category based on permission name
     * New format: category_action (e.g. examination_create, examination_update, examination_delete, examination_read_only)
     */
    private function getPermissionCategory($permissionName)
    {
        $permissionName = strtolower($permissionName);
        
        // Check if it's already in new format (category_action)
        if (strpos($permissionName, '_') !== false) {
            $parts = explode('_', $permissionName);
            $category = $parts[0];
            
            // Validate category
            $validCategories = ['examination', 'classes', 'subject', 'result', 'attendance', 'student', 'parent', 'timetable', 'teacher', 'fees', 'accommodation', 'library', 'calendar', 'fingerprint', 'task', 'sms'];
            if (in_array($category, $validCategories)) {
                return $category;
            }
        }

        // Legacy format - try to determine category from permission name
        // Examination permissions
        if (strpos($permissionName, 'examination') !== false || strpos($permissionName, 'exam') !== false) {
            return 'examination';
        }

        // Subject permissions
        if (strpos($permissionName, 'subject') !== false) {
            return 'subject';
        }

        // Teacher permissions
        if (strpos($permissionName, 'teacher') !== false || strpos($permissionName, 'role') !== false) {
            return 'teacher';
        }

        // Class permissions
        if (strpos($permissionName, 'class') !== false) {
            return 'classes';
        }

        // Attendance permissions
        if (strpos($permissionName, 'attendance') !== false) {
            return 'attendance';
        }

        // Results permissions
        if (strpos($permissionName, 'result') !== false) {
            return 'result';
        }

        // Timetable permissions
        if (strpos($permissionName, 'timetable') !== false) {
            return 'timetable';
        }

        // Student permissions
        if (strpos($permissionName, 'student') !== false) {
            return 'student';
        }

        // Parent permissions
        if (strpos($permissionName, 'parent') !== false) {
            return 'parent';
        }

        // Fees permissions
        if (strpos($permissionName, 'fee') !== false) {
            return 'fees';
        }

        // Accommodation permissions
        if (strpos($permissionName, 'accommodation') !== false) {
            return 'accommodation';
        }

        // Library permissions
        if (strpos($permissionName, 'library') !== false) {
            return 'library';
        }

        // Calendar permissions
        if (strpos($permissionName, 'calendar') !== false || strpos($permissionName, 'holiday') !== false || strpos($permissionName, 'event') !== false) {
            return 'calendar';
        }

        // Fingerprint permissions
        if (strpos($permissionName, 'fingerprint') !== false) {
            return 'fingerprint';
        }

        // Task permissions
        if (strpos($permissionName, 'task') !== false) {
            return 'task';
        }

        // SMS permissions
        if (strpos($permissionName, 'sms') !== false || strpos($permissionName, 'notification') !== false) {
            return 'sms';
        }

        // Default to null if no match
        return null;
    }
    public function manageTeachers()
    {
        $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        // If session exists, continue to page
        $schoolID = Session::get('schoolID');
        $teachers = Teacher::where('schoolID', $schoolID)->get();
        $school   = School::find($schoolID);

        //get all roles for this school (including NULL for backward compatibility during transition)
        $roles = Role::where(function($query) use ($schoolID) {
            $query->where('schoolID', $schoolID)
                  ->orWhereNull('schoolID');
        })->get();

        // Get all available permissions organized by category
        // These are the permissions that can be assigned to roles
        $availablePermissions = [
            // Timetable Management
            'create_timetable_category',
            'edit_timetable_category',
            'delete_timetable_category',
            'show_timetable_category',
            'approve_timetable_category',
            'create_timetable',
            'edit_timetable',
            'show_timetable',
            'view_all_timetable',
            'review_timetable',
            'approval_timetable',

            // Class Management
            'create_class_category',
            'edit_class_category',
            'delete_class_category',
            'show_class_category',
            'view_all_class_category',
            'approval_class_category',
            'create_class',
            'edit_class',
            'delete_class',
            'show_class',
            'view_all_class',
            'review_class',
            'approval_class',
            'activate_class',
            'create_subclass',
            'edit_subclass',
            'delete_subclass',
            'view_subclass',
            'view_students',
            'view_subjects',
            'create_combie',
            'edit_combie',
            'delete_combie',
            'view_combies',

            // Examination Management (existing)
            'create_examination',
            'create_school_wide_all_subjects_exam',
            'create_specific_classes_all_subjects_exam',
            'create_school_wide_specific_subjects_exam',
            'create_specific_classes_specific_subjects_exam',
            'edit_exam',
            'delete_exam',
            'view_exam',
            'approve_uploaded_or_created_exams_questions',
            'approve_created_exam',
            'approve_results',
            'change_exam_status',
            'approve_exam',
            'reject_exam',
            'view_exam_papers',
            'approve_exam_paper',
            'reject_exam_paper',
            'manage_upload_paper',

            // Subject Management (existing)
            'create_subject',
            'update_subject',
            'delete_subject',
            'approve_created_subject',
            'activate_subject',
            'view_class_subjects',
            'create_class_subject',
            'update_class_subject',
            'delete_class_subject',
            'activate_class_subject',

            // Other
            'register_parents',
            'register_teachers',
            'register_students',
        ];

        // Get permissions that are already assigned to roles (for display)
        $permissions = \App\Models\Permission::orderBy('name')->get();

        // Get teachers with their roles
        // Handle both cases: records with teacher_id (new) and records with user_id (old data)

        // Case 1: Records with teacher_id (new way)
        $rolesWithTeacherId = DB::table('role_user')
            ->join('teachers', 'role_user.teacher_id', '=', 'teachers.id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('teachers.schoolID', $schoolID)
            ->whereNotNull('role_user.teacher_id')
            ->select(
                'teachers.id as teacher_id',
                'teachers.first_name',
                'teachers.middle_name',
                'teachers.last_name',
                'teachers.email',
                'teachers.employee_number',
                'teachers.image',
                'teachers.gender',
                'roles.id as role_id',
                'roles.role_name',
                'role_user.id as role_user_id'
            )
            ->distinct()
            ->get();

        // Case 2: Records with user_id but no teacher_id (old data) - match by email
        $rolesWithUserId = DB::table('role_user')
            ->join('users', 'role_user.user_id', '=', 'users.id')
            ->join('teachers', 'users.email', '=', 'teachers.email')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('teachers.schoolID', $schoolID)
            ->whereNull('role_user.teacher_id')
            ->whereNotNull('role_user.user_id')
            ->select(
                'teachers.id as teacher_id',
                'teachers.first_name',
                'teachers.middle_name',
                'teachers.last_name',
                'teachers.email',
                'teachers.employee_number',
                'teachers.image',
                'teachers.gender',
                'roles.id as role_id',
                'roles.role_name',
                'role_user.id as role_user_id'
            )
            ->distinct()
            ->get();

        // Merge both results and remove any duplicates based on role_user_id
        $teachersWithRoles = $rolesWithTeacherId->merge($rolesWithUserId)
            ->unique('role_user_id')
            ->values();

        return view('Admin.manage_teachers', compact('teachers', 'roles', 'teachersWithRoles', 'permissions', 'school'));
    }

    public function save_teacher_role(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'role_id' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Get schoolID from session
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID is required. Please ensure you are logged in.'
                ], 422);
            }

            // Find the teacher
            $teacher = Teacher::find($request->teacher_id);
            if (!$teacher) {
                return response()->json(['error' => 'Teacher not found'], 404);
            }

            // Verify teacher belongs to the school
            if ($teacher->schoolID != $schoolID) {
                return response()->json(['error' => 'Teacher does not belong to this school'], 403);
            }

            // Check if role exists and belongs to this school
            $role = Role::where('id', $request->role_id)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$role) {
                return response()->json(['error' => 'Role not found or does not belong to this school'], 404);
            }

            // Check if this teacher already has this role assigned (prevent duplicate assignment)
            $existingTeacherRole = DB::table('role_user')
                ->where('teacher_id', $teacher->id)
                ->where('role_id', $role->id)
                ->first();

            if ($existingTeacherRole) {
                return response()->json(['error' => 'This role is already assigned to this teacher.'], 400);
            }

            // Assign role using Spatie (if available)
            if (method_exists($teacher, 'assignRole')) {
                $teacher->assignRole($role);
            }

            // Also maintain backward compatibility with role_user table
            DB::table('role_user')->insert([
                'user_id' => null,
                'teacher_id' => $teacher->id,
                'role_id' => $role->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => 'Role assigned successfully to ' . $teacher->first_name . ' ' . $teacher->last_name
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function change_teacher_role(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'role_user_id' => 'required|exists:role_user,id',
                'new_teacher_id' => 'required|exists:teachers,id',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Get current role assignment - handle both cases (with teacher_id or user_id)
            $currentRoleAssignment = DB::table('role_user')
                ->where('id', $request->role_user_id)
                ->first();

            if (!$currentRoleAssignment) {
                return response()->json(['error' => 'Role assignment not found'], 404);
            }

            // If it's an old record with user_id but no teacher_id, we need to handle it
            if (!$currentRoleAssignment->teacher_id && $currentRoleAssignment->user_id) {
                // Find teacher by email from user
                $user = User::find($currentRoleAssignment->user_id);
                if ($user) {
                    $teacher = Teacher::where('email', $user->email)->first();
                    if ($teacher) {
                        // Update the role_user record to have teacher_id
                        DB::table('role_user')
                            ->where('id', $request->role_user_id)
                            ->update([
                                'teacher_id' => $teacher->id,
                                'updated_at' => now(),
                            ]);
                        // Refresh the assignment
                        $currentRoleAssignment = DB::table('role_user')
                            ->where('id', $request->role_user_id)
                            ->first();
                    } else {
                        return response()->json(['error' => 'Teacher not found for this role assignment'], 404);
                    }
                } else {
                    return response()->json(['error' => 'User not found for this role assignment'], 404);
                }
            }

            // Now check if teacher_id exists
            if (!$currentRoleAssignment->teacher_id) {
                return response()->json(['error' => 'Role assignment not found or invalid'], 404);
            }

            // Get schoolID from session
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID is required. Please ensure you are logged in.'
                ], 422);
            }

            // Get the role and verify it belongs to this school
            $role = Role::where('id', $currentRoleAssignment->role_id)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$role) {
                return response()->json(['error' => 'Role not found or does not belong to this school'], 404);
            }

            // Get new teacher
            $newTeacher = Teacher::find($request->new_teacher_id);
            if (!$newTeacher) {
                return response()->json(['error' => 'New teacher not found'], 404);
            }

            // Check if new teacher already has this role
            $existingAssignment = DB::table('role_user')
                ->where('teacher_id', $newTeacher->id)
                ->where('role_id', $role->id)
                ->first();

            if ($existingAssignment) {
                return response()->json(['error' => 'This teacher already has this role assigned.'], 400);
            }

            // Get old teacher for removal
            $oldTeacher = Teacher::find($currentRoleAssignment->teacher_id);
            $oldTeacherName = $oldTeacher
                ? $oldTeacher->first_name . ' ' . $oldTeacher->last_name
                : 'Previous teacher';

            // Remove role from old teacher using Spatie (if available)
            if ($oldTeacher && method_exists($oldTeacher, 'removeRole')) {
                $oldTeacher->removeRole($role);
            }

            // Assign role to new teacher using Spatie (if available)
            if (method_exists($newTeacher, 'assignRole')) {
                $newTeacher->assignRole($role);
            }

            // Update the role assignment to new teacher (backward compatibility)
            DB::table('role_user')
                ->where('id', $request->role_user_id)
                ->update([
                    'teacher_id' => $newTeacher->id,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => 'Role "' . $role->role_name . '" has been changed from ' . $oldTeacherName . ' to ' . $newTeacher->first_name . ' ' . $newTeacher->last_name
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

  public function save_teachers(Request $request)
{
    try {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'gender'          => 'required|in:Male,Female',
            'national_id'     => 'required|unique:teachers,national_id',
            'employee_number' => 'required|unique:teachers,employee_number',
            'email'           => 'required|email|unique:teachers,email',
            'phone_number'    => [
                'required',
                'unique:teachers,phone_number',
                'regex:/^255\d{9}$/'
            ],
            'bank_account_number' => 'nullable|string|max:255',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'phone_number.regex' => 'Phone number must have 12 digits: start with 255 followed by 9 digits (e.g., 255614863345)',
            'image.max'         => 'Image must not exceed 2MB.',
            'image.mimes'       => 'Only JPG and PNG formats are allowed.',
        ]);

        // If validation fails, return errors as JSON
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field] = $messages[0]; // first error per field
            }
            return response()->json(['errors' => $errors], 422);
        }

        // Handle Image Upload
        $imageName = null;
        if ($request->hasFile('image')) {
            $uploadPath = public_path('userImages');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $imageName = time().'_'.$request->file('image')->getClientOriginalName();
            $request->file('image')->move($uploadPath, $imageName);
        }

        // Get schoolID from session
        $schoolID = Session::get('schoolID') ?? $request->schoolID ?? null;

        // Generate unique 4-digit fingerprint ID (must be unique in users table first)
        $fingerprintId = null;
        // Generate 4-digit ID (1000-9999) - ensure it's unique in users table
        do {
            $fingerprintId = (string)rand(1000, 9999);
        } while (
            User::where('fingerprint_id', $fingerprintId)->exists() ||
            Teacher::where('fingerprint_id', $fingerprintId)->exists() ||
            Teacher::where('id', (int)$fingerprintId)->exists() // Check id as well
        );

        // Create teacher with id = fingerprintID and fingerprint_id = fingerprintID
        $teacher = Teacher::create([
            'id'             => (int)$fingerprintId, // Set id equal to fingerprintID (as integer)
            'schoolID'       => $schoolID,
            'first_name'     => $request->first_name,
            'middle_name'   => $request->middle_name ?? null,
            'last_name'      => $request->last_name,
            'image'          => $imageName,
            'gender'         => $request->gender,
            'national_id'    => $request->national_id,
            'employee_number' => $request->employee_number,
            'qualification' => $request->qualification ?? null,
            'specialization' => $request->specialization ?? null,
            'experience'     => $request->experience ?? null,
            'date_of_birth' => $request->date_of_birth ?? null,
            'date_hired'    => $request->date_hired ?? null,
            'address'       => $request->address ?? null,
            'email'         => $request->email,
            'phone_number'  => $request->phone_number,
            'bank_account_number' => $request->bank_account_number ?? null,
            'position'      => $request->position ?? null,
            'status'        => $request->status ?? 'Active',
            'fingerprint_id' => $fingerprintId, // Also store in fingerprint_id column (as string)
        ]);

        // Send teacher to biometric device directly (not via API)
        try {
            Log::info("ZKTeco Direct: Attempting to register teacher - Fingerprint ID: {$fingerprintId}, Name: {$request->first_name}");
            
            // Use first_name only for device (as per user requirement)
            $teacherName = strtoupper($request->first_name); // Convert to uppercase
            
            $apiResult = $this->registerTeacherToBiometricDeviceDirect($fingerprintId, $teacherName);
            
            if ($apiResult['success']) {
                $enrollId = $apiResult['data']['enroll_id'] ?? $fingerprintId;
                $deviceRegisteredAt = $apiResult['data']['device_registered_at'] ?? null;
                
                Log::info("ZKTeco Direct: Teacher registered successfully - Fingerprint ID: {$fingerprintId}, Enroll ID: {$enrollId}");
            } else {
                Log::error("ZKTeco Direct: Teacher registration failed - Fingerprint ID: {$fingerprintId}, Error: " . ($apiResult['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error('ZKTeco Direct Registration Error: ' . $errorMessage);
            Log::error('ZKTeco Direct Registration Stack Trace: ' . $e->getTraceAsString());
            
            // Continue even if device registration fails - teacher is still registered
        }

        // Create user for login with same fingerprint_id
        $user = User::create([
            'name'          => $request->employee_number,
            'email'         => $request->email,
            'password'      => bcrypt($request->last_name), // hash password
            'user_type'     => 'Teacher',
            'fingerprint_id' => $fingerprintId  // Same fingerprint_id as teacher (id)
        ]);

        return response()->json([
            'success' => 'Teacher added successfully!',
            'fingerprint_id' => $fingerprintId
        ]);

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

    public function get_teacher($id)
    {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                return response()->json(['error' => 'Teacher not found'], 404);
            }

            return response()->json(['teacher' => $teacher]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_teacher(Request $request)
    {
        try {
            $teacherId = $request->input('teacher_id');
            $teacher = Teacher::find($teacherId);

            if (!$teacher) {
                return response()->json(['error' => 'Teacher not found'], 404);
            }

            // Validation rules with unique constraints ignoring current teacher
            $validator = Validator::make($request->all(), [
                'teacher_id'      => 'required|exists:teachers,id',
                'first_name'      => 'required|string|max:255',
                'last_name'       => 'required|string|max:255',
                'gender'          => 'required|in:Male,Female',
                'national_id'     => 'required|unique:teachers,national_id,' . $teacherId,
                'employee_number' => 'required|unique:teachers,employee_number,' . $teacherId,
                'email'           => 'required|email|unique:teachers,email,' . $teacherId,
                'phone_number'    => [
                    'required',
                    'unique:teachers,phone_number,' . $teacherId,
                    'regex:/^255\d{9}$/'
                ],
                'bank_account_number' => 'nullable|string|max:255',
                'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ], [
                'phone_number.regex' => 'Phone number must have 12 digits: start with 255 followed by 9 digits (e.g., 255614863345)',
                'phone_number.unique' => 'This phone number is already taken by another teacher.',
                'email.unique' => 'This email is already taken by another teacher.',
                'national_id.unique' => 'This national ID is already taken by another teacher.',
                'employee_number.unique' => 'This employee number is already taken by another teacher.',
                'image.max'         => 'Image must not exceed 2MB.',
                'image.mimes'       => 'Only JPG and PNG formats are allowed.',
            ]);

            // If validation fails, return errors as JSON
            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Store original email before update
            $oldEmail = $teacher->email;

            // Handle Image Upload - only if new image is provided
            $imageName = $teacher->image; // Keep existing image by default
            if ($request->hasFile('image')) {
                $uploadPath = public_path('userImages');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Delete old image if exists
                if ($teacher->image && file_exists($uploadPath . '/' . $teacher->image)) {
                    unlink($uploadPath . '/' . $teacher->image);
                }

                $imageName = time().'_'.$request->file('image')->getClientOriginalName();
                $request->file('image')->move($uploadPath, $imageName);
            }

            // Update teacher
            $teacher->update([
                'first_name'      => $request->first_name,
                'middle_name'     => $request->middle_name ?? null,
                'last_name'       => $request->last_name,
                'image'           => $imageName,
                'gender'          => $request->gender,
                'national_id'     => $request->national_id,
                'employee_number' => $request->employee_number,
                'qualification'   => $request->qualification ?? null,
                'specialization'  => $request->specialization ?? null,
                'experience'      => $request->experience ?? null,
                'date_of_birth'   => $request->date_of_birth ?? null,
                'date_hired'      => $request->date_hired ?? null,
                'address'         => $request->address ?? null,
                'email'           => $request->email,
                'phone_number'    => $request->phone_number,
                'bank_account_number' => $request->bank_account_number ?? null,
                'position'        => $request->position ?? null,
                'status'          => $request->status ?? 'Active',
            ]);

            // Update user account if email changed
            $user = User::where('email', $oldEmail)->first();
            if ($user) {
                $user->update([
                    'email' => $request->email,
                    'name' => $request->employee_number,
                ]);
            }

            return response()->json(['success' => 'Teacher updated successfully!']);

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

    /**
     * Create a new role with permissions
     */
    public function create_role(Request $request)
    {
        try {
            // Get schoolID from session
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID is required. Please ensure you are logged in.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'role_name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'permissions' => 'required|array|min:1',
                'permissions.*' => 'required|string|max:255',
            ], [
                'permissions.required' => 'Please select at least one permission for this role.',
                'permissions.min' => 'Please select at least one permission for this role.',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Create role
            $role = Role::create([
                'name' => $request->role_name,
                'role_name' => $request->role_name, // Backward compatibility
                'guard_name' => 'web',
                'schoolID' => $schoolID,
            ]);

            // Create permissions for this role
            $permissionNames = [];
            if ($request->has('permissions') && is_array($request->permissions) && count($request->permissions) > 0) {
                foreach ($request->permissions as $permissionName) {
                    $permissionName = trim($permissionName);
                    if (!empty($permissionName)) {
                        // Check if permission already exists for this role
                        $existing = \App\Models\Permission::where('role_id', $role->id)
                            ->where('name', $permissionName)
                            ->first();

                        if (!$existing) {
                            $category = $this->getPermissionCategory($permissionName);
                            \App\Models\Permission::create([
                                'name' => $permissionName,
                                'guard_name' => 'web',
                                'role_id' => $role->id,
                                'permission_category' => $category,
                            ]);
                        }
                        $permissionNames[] = $permissionName;
                    }
                }
            }

            return response()->json([
                'success' => 'Role "' . $role->name . '" created successfully with ' . count($permissionNames) . ' permission(s)!',
                'role' => $role->load('permissions'),
                'permissions' => $permissionNames
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update role name
     */
    public function update_role(Request $request)
    {
        try {
            // Get schoolID from session
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID is required. Please ensure you are logged in.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'role_id' => 'required|exists:roles,id',
                'role_name' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ], [
                'role_name.required' => 'Role name is required.',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Check if role exists and belongs to this school
            $role = Role::where('id', $request->role_id)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$role) {
                return response()->json([
                    'error' => 'Role not found or does not belong to this school.'
                ], 404);
            }

            // Update role name
            $oldName = $role->role_name ?? $role->name;
            $role->update([
                'name' => $request->role_name,
                'role_name' => $request->role_name, // Backward compatibility
            ]);

            return response()->json([
                'success' => 'Role name updated from "' . $oldName . '" to "' . $request->role_name . '" successfully!',
                'role' => $role->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new permission (single or bulk)
     */
    public function create_permission(Request $request)
    {
        try {
            // Check if permissions table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                return response()->json(['error' => 'Permissions table does not exist. Please run migrations first.'], 500);
            }

            // Handle bulk creation
            if ($request->has('permissions') && is_array($request->permissions)) {
                $validator = Validator::make($request->all(), [
                    'permissions' => 'required|array|min:1',
                    'permissions.*' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    $errors = [];
                    foreach ($validator->errors()->toArray() as $field => $messages) {
                        $errors[$field] = $messages[0];
                    }
                    return response()->json(['errors' => $errors], 422);
                }

                $created = [];
                $skipped = [];

                foreach ($request->permissions as $permissionName) {
                    $permissionName = trim($permissionName);
                    if (empty($permissionName)) {
                        continue;
                    }

                    // Check if permission already exists (try both Spatie and direct DB)
                    $exists = false;
                    if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                        $exists = \Spatie\Permission\Models\Permission::where('name', $permissionName)
                            ->where('guard_name', 'web')
                            ->first();
                    } else {
                        $exists = DB::table('permissions')
                            ->where('name', $permissionName)
                            ->where('guard_name', 'web')
                            ->first();
                    }

                    if ($exists) {
                        $skipped[] = $permissionName;
                        continue;
                    }

                    // Create permission (try Spatie first, fallback to direct DB)
                    $category = $this->getPermissionCategory($permissionName);
                    if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                        $permission = \Spatie\Permission\Models\Permission::create([
                            'name' => $permissionName,
                            'guard_name' => 'web',
                        ]);
                    } else {
                        $permissionId = DB::table('permissions')->insertGetId([
                            'name' => $permissionName,
                            'guard_name' => 'web',
                            'permission_category' => $category,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $permission = (object) [
                            'id' => $permissionId,
                            'name' => $permissionName,
                            'guard_name' => 'web',
                        ];
                    }
                    $created[] = $permission;
                }

                $message = count($created) . ' permission(s) created successfully!';
                if (count($skipped) > 0) {
                    $message .= ' ' . count($skipped) . ' permission(s) already exist and were skipped.';
                }

                return response()->json([
                    'success' => $message,
                    'permissions' => $created,
                    'skipped' => $skipped
                ]);
            }

            // Handle single permission creation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ], [
                'name.required' => 'Permission name is required.',
            ]);

            // Check uniqueness manually (works with both Spatie and direct DB)
            $exists = false;
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $exists = \Spatie\Permission\Models\Permission::where('name', $request->name)
                    ->where('guard_name', 'web')
                    ->exists();
            } else {
                $exists = DB::table('permissions')
                    ->where('name', $request->name)
                    ->where('guard_name', 'web')
                    ->exists();
            }

            if ($exists) {
                return response()->json([
                    'errors' => ['name' => 'This permission already exists.']
                ], 422);
            }

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Create permission (try Spatie first, fallback to direct DB)
            $category = $this->getPermissionCategory($request->name);
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::create([
                    'name' => $request->name,
                    'guard_name' => 'web',
                ]);
            } else {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $request->name,
                    'guard_name' => 'web',
                    'permission_category' => $category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permission = (object) [
                    'id' => $permissionId,
                    'name' => $request->name,
                    'guard_name' => 'web',
                ];
            }

            return response()->json([
                'success' => 'Permission "' . $permission->name . '" created successfully!',
                'permission' => $permission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update role permissions
     */
    public function update_role_permissions(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|exists:roles,id',
                'permissions' => 'nullable|array',
                'permissions.*' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Get schoolID from session
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID is required. Please ensure you are logged in.'
                ], 422);
            }

            // Check if role exists and belongs to this school
            $role = Role::where('id', $request->role_id)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$role) {
                return response()->json(['error' => 'Role not found or does not belong to this school'], 404);
            }

            // Delete existing permissions for this role
            \App\Models\Permission::where('role_id', $role->id)->delete();

            // Create new permissions
            $permissionNames = [];
            if ($request->has('permissions') && is_array($request->permissions) && count($request->permissions) > 0) {
                foreach ($request->permissions as $permissionName) {
                    $permissionName = trim($permissionName);
                    if (!empty($permissionName)) {
                        $category = $this->getPermissionCategory($permissionName);
                        \App\Models\Permission::create([
                            'name' => $permissionName,
                            'guard_name' => 'web',
                            'role_id' => $role->id,
                            'permission_category' => $category,
                        ]);
                        $permissionNames[] = $permissionName;
                    }
                }
            }

            return response()->json([
                'success' => 'Role permissions updated successfully!',
                'role' => $role->load('permissions'),
                'permissions' => $permissionNames
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available permissions (system-wide, not tied to roles)
     */
    public function get_permissions()
    {
        try {
            // New permission structure: Each category has 4 actions: create, update, delete, read_only
            $categories = ['examination', 'classes', 'subject', 'result', 'attendance', 'student', 'parent', 'timetable', 'fees', 'accommodation', 'library', 'calendar', 'fingerprint', 'task', 'sms'];
            $actions = ['create', 'update', 'delete', 'read_only'];
            
            $availablePermissions = [];
            foreach($categories as $category) {
                foreach($actions as $action) {
                    $availablePermissions[] = $category . '_' . $action;
                }
            }

            // Convert to objects for consistency
            $permissions = collect($availablePermissions)->map(function($name) {
                return (object) [
                    'name' => $name,
                    'guard_name' => 'web',
                ];
            });

            return response()->json([
                'success' => true,
                'permissions' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role with permissions
     */
    public function get_role_with_permissions($id)
    {
        try {
            // Get schoolID from session
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID is required. Please ensure you are logged in.'
                ], 422);
            }

            $role = Role::with('permissions')
                ->where('id', $id)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$role) {
                return response()->json(['error' => 'Role not found or does not belong to this school'], 404);
            }

            return response()->json([
                'success' => true,
                'role' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove role from teacher
     */
    public function remove_teacher_role($id)
    {
        try {
            // Get schoolID from session
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID is required. Please ensure you are logged in.'
                ], 422);
            }

            // Get role assignment
            $roleAssignment = DB::table('role_user')
                ->where('id', $id)
                ->first();

            if (!$roleAssignment) {
                return response()->json([
                    'error' => 'Role assignment not found.'
                ], 404);
            }

            // Verify role belongs to this school
            $role = Role::where('id', $roleAssignment->role_id)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$role) {
                return response()->json([
                    'error' => 'Role not found or does not belong to this school.'
                ], 404);
            }

            // Get teacher info for response
            $teacher = Teacher::find($roleAssignment->teacher_id);
            $teacherName = $teacher
                ? $teacher->first_name . ' ' . $teacher->last_name
                : 'Teacher';
            $roleName = $role->role_name ?? $role->name;

            // Delete the role assignment
            DB::table('role_user')
                ->where('id', $id)
                ->delete();

            return response()->json([
                'success' => 'Role "' . $roleName . '" has been removed from ' . $teacherName . ' successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a role
     */
    public function delete_role($id)
    {
        try {
            // Get schoolID from session
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID is required. Please ensure you are logged in.'
                ], 422);
            }

            // Check if role exists and belongs to this school
            $role = Role::where('id', $id)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$role) {
                return response()->json([
                    'error' => 'Role not found or does not belong to this school.'
                ], 404);
            }

            // Check if role is assigned to any teacher
            $assignedCount = DB::table('role_user')
                ->where('role_id', $role->id)
                ->count();

            if ($assignedCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete role. This role is currently assigned to ' . $assignedCount . ' teacher(s). Please remove the role assignment(s) first.'
                ], 422);
            }

            // Delete all permissions associated with this role
            \App\Models\Permission::where('role_id', $role->id)->delete();

            // Delete the role
            $roleName = $role->role_name ?? $role->name;
            $role->delete();

            return response()->json([
                'success' => 'Role "' . $roleName . '" deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register teacher to biometric device via API
     */
    private function registerTeacherToBiometricDevice($fingerprintId, $teacherName)
    {
        try {
            $apiUrl = 'http://192.168.100.100:8000/api/v1/users/register';
            
            // Prepare request data
            $data = [
                'id' => (string)$fingerprintId,
                'name' => strtoupper($teacherName)
            ];
            
            Log::info("Biometric API: Sending request to {$apiUrl}", $data);
            
            // Initialize cURL
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                Log::error("Biometric API: cURL Error - {$curlError}");
                return [
                    'success' => false,
                    'message' => 'Connection error: ' . $curlError
                ];
            }
            
            // Accept both 200 (OK) and 201 (Created) as success codes
            if ($httpCode !== 200 && $httpCode !== 201) {
                Log::error("Biometric API: HTTP Error - Status Code: {$httpCode}, Response: {$response}");
                return [
                    'success' => false,
                    'message' => "API returned status code: {$httpCode}",
                    'http_code' => $httpCode
                ];
            }
            
            $responseData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Biometric API: JSON Decode Error - " . json_last_error_msg());
                return [
                    'success' => false,
                    'message' => 'Invalid JSON response from API'
                ];
            }
            
            // If status code is 200 or 201, consider it success (even if response doesn't have success field)
            // This handles APIs that return 201 Created as success
            if ($httpCode === 200 || $httpCode === 201) {
                Log::info("Biometric API: Success response (Status: {$httpCode})", $responseData);
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? $responseData ?? [],
                    'message' => $responseData['message'] ?? 'Teacher registered successfully to fingerprint device',
                    'http_code' => $httpCode
                ];
            }
            
            // Fallback: check if response has success field
            if (isset($responseData['success']) && $responseData['success']) {
                Log::info("Biometric API: Success response", $responseData);
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? [],
                    'message' => $responseData['message'] ?? 'Teacher registered successfully'
                ];
            } else {
                Log::error("Biometric API: API returned error", $responseData);
                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Unknown error from API',
                    'response' => $responseData
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Biometric API Registration Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send existing teacher to fingerprint device
     * Generates unique fingerprint ID and registers teacher to biometric device
     */
    public function sendTeacherToFingerprint(Request $request)
    {
        try {
            $request->validate([
                'teacher_id' => 'required|exists:teachers,id'
            ]);

            $teacher = Teacher::findOrFail($request->teacher_id);

            // Check if teacher already has fingerprint_id
            if ($teacher->fingerprint_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher already has a fingerprint ID: ' . $teacher->fingerprint_id
                ], 400);
            }

            DB::beginTransaction();

            // Generate unique 4-digit fingerprint ID (must be unique in users table first)
            $fingerprintId = null;
            $oldTeacherId = $teacher->id;
            
            // Generate 4-digit ID (1000-9999) - ensure it's unique in users table
            do {
                $fingerprintId = (string)rand(1000, 9999);
            } while (
                User::where('fingerprint_id', $fingerprintId)->exists() ||
                Teacher::where('fingerprint_id', $fingerprintId)->exists() ||
                Teacher::where('id', (int)$fingerprintId)->exists()
            );

            // Update teacher with fingerprint_id and id = fingerprintID
            // First update fingerprint_id, then update id using DB::statement to handle primary key update
            $teacher->update([
                'fingerprint_id' => $fingerprintId
            ]);
            
            // Update id field to match fingerprintID (same as registration)
            // Temporarily disable foreign key checks to update primary key
            if ($oldTeacherId != (int)$fingerprintId) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::statement("UPDATE teachers SET id = ? WHERE id = ?", [(int)$fingerprintId, $oldTeacherId]);
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                
                // Refresh teacher model to get new id
                $teacher = Teacher::find((int)$fingerprintId);
                
                if (!$teacher) {
                    throw new \Exception('Failed to update teacher ID. Please try again.');
                }
            }

            // Send teacher to biometric device directly (not via API)
            $teacherName = strtoupper($teacher->first_name); // Use first_name only as per requirement
            $apiResult = $this->registerTeacherToBiometricDeviceDirect($fingerprintId, $teacherName);

            if ($apiResult['success']) {
                $enrollId = $apiResult['data']['enroll_id'] ?? $fingerprintId;
                $deviceRegisteredAt = $apiResult['data']['device_registered_at'] ?? null;
                
                Log::info("Biometric API: Teacher sent successfully - Fingerprint ID: {$fingerprintId}, Enroll ID: {$enrollId}");
                
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Teacher successfully sent to fingerprint device',
                    'fingerprint_id' => $fingerprintId,
                    'enroll_id' => $enrollId,
                    'device_registered_at' => $deviceRegisteredAt
                ]);
            } else {
                // Even if API fails, fingerprint_id is saved
                Log::error("ZKTeco Direct: Teacher registration failed - Fingerprint ID: {$fingerprintId}, Error: " . ($apiResult['message'] ?? 'Unknown error'));
                
                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint ID generated but failed to register to device: ' . ($apiResult['message'] ?? 'Unknown error'),
                    'fingerprint_id' => $fingerprintId
                ], 200); // Return 200 because fingerprint_id was saved
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Send Teacher to Fingerprint Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register teacher to biometric device directly (not via API)
     */
    private function registerTeacherToBiometricDeviceDirect($fingerprintId, $teacherName)
    {
        try {
            // Direct registration to ZKTeco device using internal service
            $ip = config('zkteco.ip', '192.168.1.108');
            $port = (int) config('zkteco.port', 4370);

            Log::info("ZKTeco Direct: Attempting to register teacher to device", [
                'fingerprint_id' => $fingerprintId,
                'teacher_name'   => $teacherName,
                'device_ip'      => $ip,
                'device_port'    => $port,
            ]);

            $zkteco = new ZKTecoService($ip, $port);

            // UID and UserID will both use fingerprintId (must be 1–65535)
            $uid = (int) $fingerprintId;
            $name = strtoupper($teacherName);

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
                'fingerprint_id' => $fingerprintId,
                'trace'          => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
}
