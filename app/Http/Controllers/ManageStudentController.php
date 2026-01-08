<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subclass;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\ParentModel;
use App\Models\School;
use App\Services\SmsService;
use App\Services\ZKTecoService;
use App\Libraries\ZKLib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ManageStudentController extends Controller
{
    /**
     * Check if user has a specific permission
     */
    private function hasPermission($permissionName)
    {
        $userType = Session::get('user_type');

        // Admin has ALL permissions by default
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

    /**
     * Get all permissions for the current teacher
     */
    private function getTeacherPermissions()
    {
        $userType = Session::get('user_type');

        // Admin has all permissions
        if ($userType === 'Admin') {
            return collect(); // Return empty collection, Admin checks are done separately
        }

        // For teachers, get all their permissions from their roles
        if ($userType === 'Teacher') {
            $teacherID = Session::get('teacherID');
            if (!$teacherID) {
                return collect();
            }

            // Get teacher's roles
            $roles = DB::table('teachers')
                ->join('role_user', 'teachers.id', '=', 'role_user.teacher_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.teacher_id', $teacherID)
                ->select('roles.id as roleID')
                ->get();

            if ($roles->count() === 0) {
                return collect();
            }

            $roleIds = $roles->pluck('roleID')->toArray();

            // Get all permissions for these roles
            $permissions = DB::table('permissions')
                ->whereIn('role_id', $roleIds)
                ->pluck('name')
                ->unique()
                ->values();

            return $permissions;
        }

        return collect();
    }

    public function save_student(Request $request)
    {
        // Check create permission - New format: student_create
        if (!$this->hasPermission('student_create')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create students. You need student_create permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        // Auto-generate admission number if not provided
        $school = School::find($schoolID);
        $currentYear = date('Y');
        $admissionNumber = $request->admission_number;

        if (empty($admissionNumber)) {
            // Generate unique admission number: registration_number/unique_sequence/currentYear
            $schoolNumber = $school->registration_number ?? 'SCH' . $schoolID;

            // Get the last admission number for this school and year to generate next sequence
            $lastStudent = Student::where('schoolID', $schoolID)
                ->where('admission_number', 'like', $schoolNumber . '/%/' . $currentYear)
                ->orderBy('admission_number', 'desc')
                ->first();

            $sequence = 1;
            if ($lastStudent && preg_match('/\/(\d+)\/' . $currentYear . '$/', $lastStudent->admission_number, $matches)) {
                $sequence = (int)$matches[1] + 1;
            }

            // Format: school_number/sequence/year (e.g., SCH001/001/2025)
            $admissionNumber = $schoolNumber . '/' . str_pad($sequence, 3, '0', STR_PAD_LEFT) . '/' . $currentYear;

            // Ensure uniqueness
            while (Student::where('admission_number', $admissionNumber)->exists() ||
                   User::where('name', $admissionNumber)->exists()) {
                $sequence++;
                $admissionNumber = $schoolNumber . '/' . str_pad($sequence, 3, '0', STR_PAD_LEFT) . '/' . $currentYear;
            }
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female',
            'admission_number' => 'nullable|string|max:50|unique:students,admission_number|unique:users,name',
            'subclassID' => 'required|exists:subclasses,subclassID',
            'date_of_birth' => 'nullable|date',
            'admission_date' => 'nullable|date',
            'parentID' => 'nullable|exists:parents,parentID',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|in:Active,Transferred,Graduated,Inactive',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ], [
            'admission_number.unique' => 'Admission number already exists. Please use a different admission number.',
            'photo.image' => 'Photo must be an image file.',
            'photo.mimes' => 'Photo must be a jpg, jpeg, or png file.',
            'photo.max' => 'Photo must not exceed 2MB.'
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field] = $messages[0]; // Get first error message per field
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }

        // Verify subclass belongs to school
        $subclass = Subclass::find($request->subclassID);
        if (!$subclass || $subclass->class->schoolID != $schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid subclass or unauthorized access'
            ], 403);
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

            // Generate unique 4-digit fingerprint ID (must be unique in users table first)
            $fingerprintId = null;
            $sentToDevice = false;
            $deviceSentAt = null;
            $apiResult = null;

            // Generate 4-digit ID (1000-9999) - ensure it's unique in users table
            do {
                $fingerprintId = (string)rand(1000, 9999);
            } while (
                User::where('fingerprint_id', $fingerprintId)->exists() ||
                Student::where('fingerprint_id', $fingerprintId)->exists() ||
                Student::where('studentID', $fingerprintId)->exists()
            );

            // Create student with studentID = fingerprintID and fingerprint_id = fingerprintID
            $student = Student::create([
                'studentID' => (int)$fingerprintId, // Set studentID equal to fingerprintID (as integer)
                'schoolID' => $schoolID,
                'subclassID' => $request->subclassID,
                'parentID' => $request->parentID ?: null,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?: null,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth ?: null,
                'admission_number' => $admissionNumber,
                'fingerprint_id' => $fingerprintId, // Also store in fingerprint_id column
                'sent_to_device' => false,
                'device_sent_at' => null,
                'fingerprint_capture_count' => 0,
                'admission_date' => $request->admission_date ?: null,
                'address' => $request->address ?: null,
                'photo' => $imageName,
                'status' => $request->status ?: 'Active',
                'is_disabled' => $request->has('is_disabled') && $request->is_disabled == '1' ? true : false,
                'has_epilepsy' => $request->has('has_epilepsy') && $request->has_epilepsy == '1' ? true : false,
                'has_allergies' => $request->has('has_allergies') && $request->has_allergies == '1' ? true : false,
                'allergies_details' => $request->has_allergies == '1' ? ($request->allergies_details ?: null) : null
            ]);

            // Send student to biometric device directly (not via API)
            try {
                Log::info("ZKTeco Direct: Attempting to register student - Fingerprint ID: {$fingerprintId}, Name: {$request->first_name}");

                // Use first_name only for device (as per user requirement)
                $studentName = strtoupper($request->first_name); // Convert to uppercase as per example

                $apiResult = $this->registerStudentToBiometricDevice($fingerprintId, $studentName);

                if ($apiResult['success']) {
                    $enrollId = $apiResult['data']['enroll_id'] ?? $fingerprintId;
                    $deviceRegisteredAt = $apiResult['data']['device_registered_at'] ?? null;

                    Log::info("ZKTeco Direct: User registered successfully - Fingerprint ID: {$fingerprintId}, Enroll ID: {$enrollId}");

                    // Update student record
                    $student->update([
                        'sent_to_device' => true,
                        'device_sent_at' => $deviceRegisteredAt ? \Carbon\Carbon::parse($deviceRegisteredAt) : now()
                    ]);
                    $sentToDevice = true;
                    $deviceSentAt = $deviceRegisteredAt ? \Carbon\Carbon::parse($deviceRegisteredAt) : now();
                } else {
                    Log::error("ZKTeco Direct: User registration failed - Fingerprint ID: {$fingerprintId}, Error: " . ($apiResult['message'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                Log::error('ZKTeco Direct Registration Error: ' . $errorMessage);
                Log::error('ZKTeco Direct Registration Stack Trace: ' . $e->getTraceAsString());

                // Continue even if API call fails - student is still registered
            }

            // Create user account for student
            // Username = admission_number, Password = last_name
            // Use admission_number as email (must be unique)
            $userEmail = $admissionNumber . '@student.local';
            // Ensure email is unique
            $emailCounter = 1;
            while (User::where('email', $userEmail)->exists()) {
                $userEmail = $admissionNumber . '_' . $emailCounter . '@student.local';
                $emailCounter++;
            }

            // Create user with same fingerprint_id (already verified unique above)
            User::create([
                'name' => $admissionNumber,
                'email' => $userEmail,
                'password' => Hash::make($request->last_name),
                'user_type' => 'student',
                'fingerprint_id' => $fingerprintId  // Same fingerprint_id as student (studentID)
            ]);

            // Send SMS to parent if parent exists
            $smsSent = false;
            if ($request->parentID) {
                $parent = ParentModel::find($request->parentID);
                if ($parent && $parent->phone) {
                    $smsService = new SmsService();
                    $studentName = $request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name;
                    $smsResult = $smsService->sendStudentCredentials(
                        $parent->phone,
                        $school->school_name,
                        $studentName,
                        $admissionNumber,
                        $request->last_name
                    );
                    $smsSent = $smsResult['success'];
                }
            }

            DB::commit();

            $message = 'Student registered successfully';
            if ($sentToDevice && $apiResult && $apiResult['success']) {
                $message .= ' and registered to biometric device successfully';
            }
            if ($smsSent) {
                $message .= ' and SMS sent to parent';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'student' => $student,
                'fingerprint_id' => $fingerprintId,
                'sent_to_device' => $sentToDevice,
                'device_sent_at' => $deviceSentAt ? $deviceSentAt->format('Y-m-d H:i:s') : null,
                'api_response' => $apiResult
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded image if student creation failed
            if (isset($imageName) && file_exists(public_path('userImages/' . $imageName))) {
                unlink(public_path('userImages/' . $imageName));
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to register student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_student($studentID)
    {
        // Check read permission - Allow read_only, create, update, delete permissions for viewing
        $userType = Session::get('user_type');
        $canView = false;

        if ($userType === 'Admin') {
            $canView = true;
        } else {
            // Check if user has any student permission (read_only, create, update, delete)
            $canView = $this->hasPermission('student_read_only') ||
                      $this->hasPermission('student_create') ||
                      $this->hasPermission('student_update') ||
                      $this->hasPermission('student_delete') ||
                      $this->hasPermission('view_students'); // Legacy support
        }

        if (!$canView) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view student details.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        $student = Student::with(['parent', 'subclass.class'])
            ->where('studentID', $studentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
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

        // Get student photo path
        $studentImgPath = null;
        if ($student->photo) {
            $photoPath = public_path('userImages/' . $student->photo);
            if (file_exists($photoPath)) {
                $studentImgPath = asset('userImages/' . $student->photo);
            }
        }
        if (!$studentImgPath) {
            $studentImgPath = $student->gender == 'Female'
                ? asset('images/female.png')
                : asset('images/male.png');
        }

        return response()->json([
            'success' => true,
            'student' => [
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
                'parentID' => $student->parentID,
                'subclassID' => $student->subclassID,
                'old_subclassID' => $student->old_subclassID,
                'parent_name' => $student->parent ?
                    $student->parent->first_name . ' ' . $student->parent->last_name : 'Not Assigned',
                'parent_phone' => $student->parent ? $student->parent->phone : null,
                'photo' => $studentImgPath,
                // Additional fields
                'birth_certificate_number' => $student->birth_certificate_number,
                'religion' => $student->religion,
                'nationality' => $student->nationality,
                'general_health_condition' => $student->general_health_condition,
                'has_disability' => $student->has_disability ?? false,
                'disability_details' => $student->disability_details,
                'has_chronic_illness' => $student->has_chronic_illness ?? false,
                'chronic_illness_details' => $student->chronic_illness_details,
                'immunization_details' => $student->immunization_details,
                'emergency_contact_name' => $student->emergency_contact_name,
                'emergency_contact_relationship' => $student->emergency_contact_relationship,
                'emergency_contact_phone' => $student->emergency_contact_phone,
                'is_disabled' => $student->is_disabled ?? false,
                'has_epilepsy' => $student->has_epilepsy ?? false,
                'has_allergies' => $student->has_allergies ?? false,
                'allergies_details' => $student->allergies_details,
            ]
        ]);
    }

    public function update_student(Request $request)
    {
        // Check update permission - New format: student_update
        if (!$this->hasPermission('student_update')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update students. You need student_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'studentID' => 'required|exists:students,studentID',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female',
            'admission_number' => 'required|string|max:50|unique:students,admission_number,' . $request->studentID . ',studentID',
            'date_of_birth' => 'nullable|date',
            'admission_date' => 'nullable|date',
            'parentID' => 'nullable|exists:parents,parentID',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|in:Active,Transferred,Graduated,Inactive',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ], [
            'admission_number.unique' => 'Admission number already exists. Please use a different admission number.',
            'photo.image' => 'Photo must be an image file.',
            'photo.mimes' => 'Photo must be a jpg, jpeg, or png file.',
            'photo.max' => 'Photo must not exceed 2MB.'
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field] = $messages[0];
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }

        try {
            DB::beginTransaction();

            $student = Student::where('studentID', $request->studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            // Handle Image Upload
            $imageName = $student->photo; // Keep existing photo
            if ($request->hasFile('photo')) {
                $uploadPath = public_path('userImages');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Delete old image if exists
                if ($student->photo && file_exists($uploadPath . '/' . $student->photo)) {
                    unlink($uploadPath . '/' . $student->photo);
                }

                $imageName = time() . '_' . $request->file('photo')->getClientOriginalName();
                $request->file('photo')->move($uploadPath, $imageName);
            }

            // Update student
            $student->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?: null,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth ?: null,
                'admission_date' => $request->admission_date ?: null,
                'address' => $request->address ?: null,
                'parentID' => $request->parentID ?: null,
                'subclassID' => $request->subclassID ?: null,
                'photo' => $imageName,
                'status' => $request->status ?: 'Active',
                // Additional particulars
                'birth_certificate_number' => $request->birth_certificate_number ?: null,
                'religion' => $request->religion ?: null,
                'nationality' => $request->nationality ?: null,
                // Health information
                'general_health_condition' => $request->general_health_condition ?: null,
                'has_disability' => $request->has('has_disability') && $request->has_disability == '1' ? 1 : 0,
                'disability_details' => ($request->has('has_disability') && $request->has_disability == '1') ? ($request->disability_details ?: null) : null,
                'has_chronic_illness' => $request->has('has_chronic_illness') && $request->has_chronic_illness == '1' ? 1 : 0,
                'chronic_illness_details' => ($request->has('has_chronic_illness') && $request->has_chronic_illness == '1') ? ($request->chronic_illness_details ?: null) : null,
                'immunization_details' => $request->immunization_details ?: null,
                'is_disabled' => $request->has('is_disabled') && $request->is_disabled == '1' ? true : false,
                'has_epilepsy' => $request->has('has_epilepsy') && $request->has_epilepsy == '1' ? true : false,
                'has_allergies' => $request->has('has_allergies') && $request->has_allergies == '1' ? true : false,
                'allergies_details' => ($request->has('has_allergies') && $request->has_allergies == '1') ? ($request->allergies_details ?: null) : null,
                // Emergency contact
                'emergency_contact_name' => $request->emergency_contact_name ?: null,
                'emergency_contact_relationship' => $request->emergency_contact_relationship ?: null,
                'emergency_contact_phone' => $request->emergency_contact_phone ?: null,
            ]);

            // Update user account if admission number changed
            $user = User::where('name', $student->getOriginal('admission_number'))->first();
            if ($user && $request->admission_number != $student->getOriginal('admission_number')) {
                $user->name = $request->admission_number;
                $user->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'student' => $student
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transfer_student(Request $request)
    {
        // Check update permission - New format: student_update (transfer is an update action)
        if (!$this->hasPermission('student_update')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to transfer students. You need student_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'studentID' => 'required|exists:students,studentID',
            'new_subclassID' => 'required|exists:subclasses,subclassID'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $student = Student::where('studentID', $request->studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            // Verify new subclass belongs to same school
            $newSubclass = Subclass::find($request->new_subclassID);
            if (!$newSubclass || $newSubclass->class->schoolID != $schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subclass or unauthorized access'
                ], 403);
            }

            $oldSubclassID = $student->subclassID;

            // Get old and new subclass info for SMS
            $oldSubclass = Subclass::with(['class', 'classTeacher'])->find($oldSubclassID);
            $newSubclass = Subclass::with(['class', 'classTeacher'])->find($request->new_subclassID);

            // Get old subclass name only (use subclass_name, not stream_code)
            $oldSubclassName = $oldSubclass ? $oldSubclass->subclass_name : 'Darasa la zamani';

            // Get new subclass name only (use subclass_name, not stream_code)
            $newSubclassName = $newSubclass ? $newSubclass->subclass_name : 'Darasa jipya';

            // Update student subclass
            $student->subclassID = $request->new_subclassID;
            $student->old_subclassID = $oldSubclassID; // Save old subclass ID
            $student->status = 'Transferred';
            $student->save();

            DB::commit();

            // Send SMS to new class teacher if available
            if ($newSubclass && $newSubclass->classTeacher && $newSubclass->classTeacher->phone_number) {
                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $message = "Mwanafunzi {$studentName} Amehamishwa darasa {$newSubclassName} kutoka {$oldSubclassName}";

                // Send SMS asynchronously (don't wait for response)
                try {
                    $this->sendSMS($newSubclass->classTeacher->phone_number, $message);
                } catch (\Exception $smsError) {
                    // Log error but don't fail the transfer
                    \Log::error('SMS sending failed for student transfer: ' . $smsError->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Student transferred successfully from class ' . $oldSubclassID . ' to class ' . $request->new_subclassID,
                'student' => $student
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_student($studentID)
    {
        // Check delete permission - New format: student_delete
        if (!$this->hasPermission('student_delete')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete students. You need student_delete permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        try {
            $student = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            // Delete photo if exists
            if ($student->photo && file_exists(public_path('userImages/' . $student->photo))) {
                unlink(public_path('userImages/' . $student->photo));
            }

            // Delete user account
            $user = User::where('name', $student->admission_number)->first();
            if ($user) {
                $user->delete();
            }

            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate_student($studentID)
    {
        // Check update permission - New format: student_update (activate is an update action)
        if (!$this->hasPermission('student_update')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to activate students. You need student_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        try {
            $student = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            if ($student->status !== 'Transferred') {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not in Transferred status'
                ], 400);
            }

            // Activate student in current class
            $student->status = 'Active';
            $student->save();

            return response()->json([
                'success' => true,
                'message' => 'Student activated successfully',
                'student' => $student
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function revert_transfer($studentID)
    {
        // Check update permission - New format: student_update (revert transfer is an update action)
        if (!$this->hasPermission('student_update')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to revert transfers. You need student_update permission.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $student = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            if ($student->status !== 'Transferred') {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not in Transferred status'
                ], 400);
            }

            if (!$student->old_subclassID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Previous class information not found'
                ], 400);
            }

            // Verify old subclass still exists and belongs to same school
            $oldSubclass = Subclass::find($student->old_subclassID);
            if (!$oldSubclass || $oldSubclass->class->schoolID != $schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Previous class no longer exists or is invalid'
                ], 400);
            }

            // Revert student to previous class
            $currentSubclassID = $student->subclassID;
            $student->subclassID = $student->old_subclassID;
            $student->old_subclassID = null; // Clear old subclass ID
            $student->status = 'Active';
            $student->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student reverted to previous class successfully',
                'student' => $student
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to revert transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download_students_pdf($subclassID)
    {
        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return redirect()->back()->with('error', 'School ID not found');
        }

        try {
            $subclass = Subclass::with('class')->find($subclassID);
            if (!$subclass) {
                return redirect()->back()->with('error', 'Class not found');
            }

            // Verify subclass belongs to school
            if ($subclass->class->schoolID != $schoolID) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }

            $school = School::find($schoolID);
            if (!$school) {
                return redirect()->back()->with('error', 'School not found');
            }

            // Get students for this subclass
            $students = Student::where('subclassID', $subclassID)
                ->with('parent')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            // Get subclass name only (without class name)
            $subclassName = $subclass->subclass_name ?? $subclass->stream_code ?? 'N/A';
            $schoolName = $school->school_name;
            $schoolEmail = $school->email ?? 'N/A';
            $schoolPhone = $school->phone ?? 'N/A';
            $schoolLogo = $school->school_logo ? public_path($school->school_logo) : null;

            $dompdf = new \Dompdf\Dompdf();
            $html = view('pdf.students_report', compact(
                'students',
                'schoolName',
                'schoolEmail',
                'schoolPhone',
                'schoolLogo',
                'subclassName'
            ))->render();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Wanafunzi_Darasa_' . str_replace(' ', '_', $subclassName) . '_' . date('Y-m-d') . '.pdf';

            return response()->streamDownload(function() use ($dompdf) {
                echo $dompdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS using NextSMS API
     */
    private function sendSMS($phoneNumber, $message)
    {
        try {
            // Clean phone number (remove spaces, dashes, etc.)
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

            // Ensure phone number starts with 255
            if (substr($phoneNumber, 0, 3) !== '255') {
                // If starts with 0, replace with 255
                if (substr($phoneNumber, 0, 1) === '0') {
                    $phoneNumber = '255' . substr($phoneNumber, 1);
                } else {
                    // If doesn't start with 255, add it
                    $phoneNumber = '255' . $phoneNumber;
                }
            }

            // Validate phone number format (255 + 6/7 + 8 digits = 12 total)
            if (!preg_match('/^255[67]\d{8}$/', $phoneNumber)) {
                \Log::warning('Invalid phone number format for SMS: ' . $phoneNumber);
                return false;
            }

            // URL encode the message
            $text = urlencode($message);

            // Initialize cURL
            $curl = curl_init();

            // Build API URL
            $apiUrl = 'https://messaging-service.co.tz/link/sms/v1/text/single?username=emcatechn&password=Emca@%2312&from=ShuleLink&to=' . $phoneNumber . '&text=' . $text;

            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10, // 10 second timeout
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (curl_errno($curl)) {
                $error = curl_error($curl);
                curl_close($curl);
                \Log::error('SMS cURL Error: ' . $error);
                return false;
            }

            curl_close($curl);

            // Log response for debugging
            \Log::info('SMS sent to ' . $phoneNumber . '. Response: ' . $response . ' (HTTP: ' . $httpCode . ')');

            // Check if SMS was sent successfully (HTTP 200 or 201 typically means success)
            return ($httpCode >= 200 && $httpCode < 300);

        } catch (\Exception $e) {
            \Log::error('SMS sending exception: ' . $e->getMessage());
            return false;
        }
    }

    public function manage_student()
    {
        $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        // Check permission - allow if user has any student management permission
        // New format: student_create, student_update, student_delete, student_read_only
        $studentPermissions = [
            'student_create',
            'student_update',
            'student_delete',
            'student_read_only',
            // Legacy permissions (for backward compatibility)
            'register_students',
            'edit_student',
            'delete_student',
            'view_students',
        ];

        $hasAnyPermission = false;
        if ($user === 'Admin') {
            $hasAnyPermission = true;
        } else {
            foreach ($studentPermissions as $permission) {
                if ($this->hasPermission($permission)) {
                    $hasAnyPermission = true;
                    break;
                }
            }
        }

        if (!$hasAnyPermission) {
            return redirect()->back()->with('error', 'You do not have permission to access student management.');
        }

        $user_type = Session::get('user_type');
        $teacherPermissions = $this->getTeacherPermissions();
        $schoolID = Session::get('schoolID');

        // Get classes for filter dropdown
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        return view('Admin.manage_student', compact('user_type', 'teacherPermissions', 'classes'));
    }

    public function get_students(Request $request)
    {
        try {
            // Check read permission - Allow read_only, create, update, delete permissions for viewing
            $userType = Session::get('user_type');
            $canView = false;

            if ($userType === 'Admin') {
                $canView = true;
            } else {
                // Check if user has any student permission (read_only, create, update, delete)
                $canView = $this->hasPermission('student_read_only') ||
                          $this->hasPermission('student_create') ||
                          $this->hasPermission('student_update') ||
                          $this->hasPermission('student_delete') ||
                          $this->hasPermission('view_students'); // Legacy support
            }

            if (!$canView) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view students.',
                ], 403);
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found'
                ], 400);
            }

            // Get filter parameters
            $status = $request->input('status', ''); // Empty means all statuses
            $classID = $request->input('classID', '');
            $subclassID = $request->input('subclassID', '');
            $gender = $request->input('gender', ''); // 'Male' or 'Female'
            $health = $request->input('health', ''); // 'good' or 'bad'
            
            \Log::info('get_students called with filters', [
                'status' => $status,
                'classID' => $classID,
                'subclassID' => $subclassID,
                'gender' => $gender,
                'health' => $health
            ]);

        $query = Student::with(['subclass.class', 'parent'])
            ->where('schoolID', $schoolID);

        // Filter by status (default to Active if not specified)
        if (!empty($status) && in_array($status, ['Active', 'Applied', 'Inactive', 'Graduated', 'Transferred'])) {
            $query->where('status', $status);
        } else {
            // Default to Active if no status specified
            $query->where('status', 'Active');
        }

        // Filter by class
        if (!empty($classID)) {
            $query->whereHas('subclass', function($q) use ($classID) {
                $q->where('classID', $classID);
            });
        }

        // Filter by subclass
        if (!empty($subclassID)) {
            $query->where('subclassID', $subclassID);
        }

        // Filter by gender
        if (!empty($gender) && in_array($gender, ['Male', 'Female'])) {
            $query->where('gender', $gender);
        }

        // Filter by health condition
        if (!empty($health)) {
            if ($health === 'good') {
                // Good health: general_health_condition is null, empty, or contains positive words
                $query->where(function($q) {
                    $q->whereNull('general_health_condition')
                      ->orWhere('general_health_condition', '')
                      ->orWhere('general_health_condition', 'like', '%good%')
                      ->orWhere('general_health_condition', 'like', '%excellent%')
                      ->orWhere('general_health_condition', 'like', '%fine%')
                      ->orWhere('general_health_condition', 'like', '%well%');
                })
                ->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('is_disabled', false)
                           ->orWhereNull('is_disabled');
                    })
                    ->where(function($q2) {
                        $q2->where('has_epilepsy', false)
                           ->orWhereNull('has_epilepsy');
                    })
                    ->where(function($q2) {
                        $q2->where('has_allergies', false)
                           ->orWhereNull('has_allergies');
                    })
                    ->where(function($q2) {
                        $q2->where('has_disability', false)
                           ->orWhereNull('has_disability');
                    })
                    ->where(function($q2) {
                        $q2->where('has_chronic_illness', false)
                           ->orWhereNull('has_chronic_illness');
                    });
                });
            } elseif ($health === 'bad') {
                // Bad health: has disability, chronic illness, epilepsy, allergies, or negative health condition
                $query->where(function($q) {
                    $q->where('is_disabled', true)
                      ->orWhere('has_epilepsy', true)
                      ->orWhere('has_allergies', true)
                      ->orWhere('has_disability', true)
                      ->orWhere('has_chronic_illness', true)
                      ->orWhere('general_health_condition', 'like', '%poor%')
                      ->orWhere('general_health_condition', 'like', '%bad%')
                      ->orWhere('general_health_condition', 'like', '%sick%')
                      ->orWhere('general_health_condition', 'like', '%ill%');
                });
            }
        }

        // Search functionality removed as per user request

        $students = $query->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $students = $students->map(function($student) {
            // Construct image path - check if file exists
            $studentImgPath = $student->gender == 'Female'
                ? asset('images/female.png')
                : asset('images/male.png'); // Default to placeholder
            
            if ($student->photo && !empty(trim($student->photo))) {
                $photoPath = public_path('userImages/' . $student->photo);
                if (file_exists($photoPath)) {
                    $studentImgPath = asset('userImages/' . $student->photo);
                }
                // If file doesn't exist, keep using placeholder (already set above)
            }
            // If no photo, keep using placeholder (already set above)

            return [
                'studentID' => $student->studentID,
                'admission_number' => $student->admission_number,
                'full_name' => $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : null,
                'admission_date' => $student->admission_date ? $student->admission_date->format('Y-m-d') : null,
                'address' => $student->address,
                'photo' => $studentImgPath,
                'status' => $student->status,
                'class' => $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name . ' ' . $student->subclass->subclass_name
                    : 'N/A',
                'parent_name' => $student->parent
                    ? $student->parent->first_name . ' ' . ($student->parent->middle_name ? $student->parent->middle_name . ' ' : '') . $student->parent->last_name
                    : 'N/A',
                'parent_phone' => $student->parent ? $student->parent->phone : 'N/A',
                'fingerprint_id' => $student->fingerprint_id,
                'sent_to_device' => $student->sent_to_device ?? false,
                'fingerprint_capture_count' => $student->fingerprint_capture_count ?? 0,
                'is_disabled' => $student->is_disabled ?? false,
                'has_epilepsy' => $student->has_epilepsy ?? false,
                'has_allergies' => $student->has_allergies ?? false,
                'allergies_details' => $student->allergies_details ?? null,
                'general_health_condition' => $student->general_health_condition ?? null,
                'has_disability' => $student->has_disability ?? false,
                'has_chronic_illness' => $student->has_chronic_illness ?? false,
                'classID' => $student->subclass && $student->subclass->class ? $student->subclass->class->classID : null,
                'subclassID' => $student->subclassID,
                'admission_year' => $student->admission_date ? $student->admission_date->format('Y') : null,
            ];
        });

            return response()->json([
                'success' => true,
                'students' => $students
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in get_students: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error loading students: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_student_statistics(Request $request)
    {
        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        // Get filter parameters (same as get_students)
        $status = $request->input('status', '');
        $classID = $request->input('classID', '');
        $subclassID = $request->input('subclassID', '');
        $gender = $request->input('gender', '');
        $health = $request->input('health', '');

        $query = Student::with(['subclass.class'])
            ->where('schoolID', $schoolID);

        // Apply same filters as get_students (default to Active)
        if (!empty($status) && in_array($status, ['Active', 'Applied', 'Inactive', 'Graduated', 'Transferred'])) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'Active');
        }

        if (!empty($classID)) {
            $query->whereHas('subclass', function($q) use ($classID) {
                $q->where('classID', $classID);
            });
        }

        if (!empty($subclassID)) {
            $query->where('subclassID', $subclassID);
        }

        // Filter by gender
        if (!empty($gender) && in_array($gender, ['Male', 'Female'])) {
            $query->where('gender', $gender);
        }

        if (!empty($health)) {
            if ($health === 'good') {
                $query->where(function($q) {
                    $q->whereNull('general_health_condition')
                      ->orWhere('general_health_condition', '')
                      ->orWhere('general_health_condition', 'like', '%good%')
                      ->orWhere('general_health_condition', 'like', '%excellent%')
                      ->orWhere('general_health_condition', 'like', '%fine%')
                      ->orWhere('general_health_condition', 'like', '%well%');
                })
                ->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('is_disabled', false)
                           ->orWhereNull('is_disabled');
                    })
                    ->where(function($q2) {
                        $q2->where('has_epilepsy', false)
                           ->orWhereNull('has_epilepsy');
                    })
                    ->where(function($q2) {
                        $q2->where('has_allergies', false)
                           ->orWhereNull('has_allergies');
                    })
                    ->where(function($q2) {
                        $q2->where('has_disability', false)
                           ->orWhereNull('has_disability');
                    })
                    ->where(function($q2) {
                        $q2->where('has_chronic_illness', false)
                           ->orWhereNull('has_chronic_illness');
                    });
                });
            } elseif ($health === 'bad') {
                $query->where(function($q) {
                    $q->where('is_disabled', true)
                      ->orWhere('has_epilepsy', true)
                      ->orWhere('has_allergies', true)
                      ->orWhere('has_disability', true)
                      ->orWhere('has_chronic_illness', true)
                      ->orWhere('general_health_condition', 'like', '%poor%')
                      ->orWhere('general_health_condition', 'like', '%bad%')
                      ->orWhere('general_health_condition', 'like', '%sick%')
                      ->orWhere('general_health_condition', 'like', '%ill%');
                });
            }
        }

        $students = $query->get();

        // Calculate statistics
        $totalStudents = $students->count();
        $maleCount = $students->where('gender', 'Male')->count();
        $femaleCount = $students->where('gender', 'Female')->count();
        
        // Good health: no disability, no chronic illness, positive health condition
        $goodHealthStudents = $students->filter(function($student) {
            $hasGoodHealth = true;
            if ($student->has_disability || $student->has_chronic_illness) {
                $hasGoodHealth = false;
            }
            if ($student->general_health_condition) {
                $healthLower = strtolower($student->general_health_condition);
                if (strpos($healthLower, 'poor') !== false || 
                    strpos($healthLower, 'bad') !== false || 
                    strpos($healthLower, 'sick') !== false || 
                    strpos($healthLower, 'ill') !== false) {
                    $hasGoodHealth = false;
                }
            }
            return $hasGoodHealth;
        });
        
        $goodHealthCount = $goodHealthStudents->count();
        $badHealthCount = $totalStudents - $goodHealthCount;
        
        // Male with good health
        $maleGoodHealthCount = $goodHealthStudents->where('gender', 'Male')->count();
        
        // Female with good health
        $femaleGoodHealthCount = $goodHealthStudents->where('gender', 'Female')->count();
        
        // Male with bad health
        $maleBadHealthCount = $maleCount - $maleGoodHealthCount;
        
        // Female with bad health
        $femaleBadHealthCount = $femaleCount - $femaleGoodHealthCount;

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_students' => $totalStudents,
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
                'good_health_count' => $goodHealthCount,
                'bad_health_count' => $badHealthCount,
                'male_good_health_count' => $maleGoodHealthCount,
                'female_good_health_count' => $femaleGoodHealthCount,
                'male_bad_health_count' => $maleBadHealthCount,
                'female_bad_health_count' => $femaleBadHealthCount,
            ]
        ]);
    }

    public function export_students_pdf(Request $request)
    {
        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return redirect()->back()->with('error', 'School ID not found');
        }

        try {
            // Get filter parameters (same as get_students)
            $status = $request->input('status', '');
            $classID = $request->input('classID', '');
            $subclassID = $request->input('subclassID', '');
            $gender = $request->input('gender', '');
            $health = $request->input('health', '');

            $query = Student::with(['subclass.class', 'parent'])
                ->where('schoolID', $schoolID);

            // Apply same filters as get_students
            if (!empty($status) && in_array($status, ['Active', 'Applied', 'Inactive', 'Graduated', 'Transferred'])) {
                $query->where('status', $status);
            } else {
                $query->whereIn('status', ['Active', 'Applied', 'Inactive', 'Graduated', 'Transferred']);
            }

            if (!empty($classID)) {
                $query->whereHas('subclass', function($q) use ($classID) {
                    $q->where('classID', $classID);
                });
            }

            if (!empty($subclassID)) {
                $query->where('subclassID', $subclassID);
            }

            if (!empty($gender) && in_array($gender, ['Male', 'Female'])) {
                $query->where('gender', $gender);
            }

            if (!empty($health)) {
                if ($health === 'good') {
                    $query->where(function($q) {
                        $q->whereNull('general_health_condition')
                          ->orWhere('general_health_condition', '')
                          ->orWhere('general_health_condition', 'like', '%good%')
                          ->orWhere('general_health_condition', 'like', '%excellent%')
                          ->orWhere('general_health_condition', 'like', '%fine%')
                          ->orWhere('general_health_condition', 'like', '%well%');
                    })
                    ->where(function($q) {
                        $q->where('has_disability', false)
                          ->where('has_chronic_illness', false);
                    });
                } elseif ($health === 'bad') {
                    $query->where(function($q) {
                        $q->where('has_disability', true)
                          ->orWhere('has_chronic_illness', true)
                          ->orWhere('general_health_condition', 'like', '%poor%')
                          ->orWhere('general_health_condition', 'like', '%bad%')
                          ->orWhere('general_health_condition', 'like', '%sick%')
                          ->orWhere('general_health_condition', 'like', '%ill%');
                    });
                }
            }

            $students = $query->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $school = School::find($schoolID);
            if (!$school) {
                return redirect()->back()->with('error', 'School not found');
            }

            $schoolName = $school->school_name;
            $schoolEmail = $school->email ?? 'N/A';
            $schoolPhone = $school->phone ?? 'N/A';
            $schoolLogo = $school->school_logo ? public_path($school->school_logo) : null;

            // Build filter description
            $filterDesc = [];
            if ($status) $filterDesc[] = 'Status: ' . $status;
            if ($classID) {
                $class = ClassModel::find($classID);
                if ($class) $filterDesc[] = 'Class: ' . $class->class_name;
            }
            if ($subclassID) {
                $subclass = Subclass::find($subclassID);
                if ($subclass) $filterDesc[] = 'Subclass: ' . $subclass->subclass_name;
            }
            if ($health) $filterDesc[] = 'Health: ' . ucfirst($health);

            $dompdf = new \Dompdf\Dompdf();
            $html = view('pdf.students_report', compact(
                'students',
                'schoolName',
                'schoolEmail',
                'schoolPhone',
                'schoolLogo',
                'filterDesc'
            ))->render();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'Students_Report_' . date('Y-m-d') . '.pdf';
            if (!empty($filterDesc)) {
                $filename = 'Students_' . implode('_', array_map(function($f) {
                    return str_replace([' ', ':'], '_', $f);
                }, $filterDesc)) . '_' . date('Y-m-d') . '.pdf';
            }

            return response()->streamDownload(function() use ($dompdf) {
                echo $dompdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting students PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export PDF: ' . $e->getMessage());
        }
    }

    public function export_students_excel(Request $request)
    {
        $schoolID = Session::get('schoolID');

        if (!$schoolID) {
            return redirect()->back()->with('error', 'School ID not found');
        }

        try {
            // Get filter parameters (same as get_students)
            $status = $request->input('status', '');
            $classID = $request->input('classID', '');
            $subclassID = $request->input('subclassID', '');
            $gender = $request->input('gender', '');
            $health = $request->input('health', '');

            $query = Student::with(['subclass.class', 'parent'])
                ->where('schoolID', $schoolID);

            // Apply same filters as get_students
            if (!empty($status) && in_array($status, ['Active', 'Applied', 'Inactive', 'Graduated', 'Transferred'])) {
                $query->where('status', $status);
            } else {
                $query->whereIn('status', ['Active', 'Applied', 'Inactive', 'Graduated', 'Transferred']);
            }

            if (!empty($classID)) {
                $query->whereHas('subclass', function($q) use ($classID) {
                    $q->where('classID', $classID);
                });
            }

            if (!empty($subclassID)) {
                $query->where('subclassID', $subclassID);
            }

            if (!empty($gender) && in_array($gender, ['Male', 'Female'])) {
                $query->where('gender', $gender);
            }

            if (!empty($health)) {
                if ($health === 'good') {
                    $query->where(function($q) {
                        $q->whereNull('general_health_condition')
                          ->orWhere('general_health_condition', '')
                          ->orWhere('general_health_condition', 'like', '%good%')
                          ->orWhere('general_health_condition', 'like', '%excellent%')
                          ->orWhere('general_health_condition', 'like', '%fine%')
                          ->orWhere('general_health_condition', 'like', '%well%');
                    })
                    ->where(function($q) {
                        $q->where(function($q2) {
                            $q2->where('is_disabled', false)
                               ->orWhereNull('is_disabled');
                        })
                        ->where(function($q2) {
                            $q2->where('has_epilepsy', false)
                               ->orWhereNull('has_epilepsy');
                        })
                        ->where(function($q2) {
                            $q2->where('has_allergies', false)
                               ->orWhereNull('has_allergies');
                        })
                        ->where(function($q2) {
                            $q2->where('has_disability', false)
                               ->orWhereNull('has_disability');
                        })
                        ->where(function($q2) {
                            $q2->where('has_chronic_illness', false)
                               ->orWhereNull('has_chronic_illness');
                        });
                    });
                } elseif ($health === 'bad') {
                    $query->where(function($q) {
                        $q->where('is_disabled', true)
                          ->orWhere('has_epilepsy', true)
                          ->orWhere('has_allergies', true)
                          ->orWhere('has_disability', true)
                          ->orWhere('has_chronic_illness', true)
                          ->orWhere('general_health_condition', 'like', '%poor%')
                          ->orWhere('general_health_condition', 'like', '%bad%')
                          ->orWhere('general_health_condition', 'like', '%sick%')
                          ->orWhere('general_health_condition', 'like', '%ill%');
                    });
                }
            }

            $students = $query->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            // Prepare Excel data
            $data = [];
            $data[] = ['Admission Number', 'Full Name', 'Gender', 'Class', 'Subclass', 'Status', 'Health', 'Parent Name', 'Parent Phone', 'Admission Date'];

            foreach ($students as $student) {
                $className = $student->subclass && $student->subclass->class 
                    ? $student->subclass->class->class_name 
                    : 'N/A';
                $subclassName = $student->subclass ? $student->subclass->subclass_name : 'N/A';
                $fullName = $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name;
                $parentName = $student->parent 
                    ? $student->parent->first_name . ' ' . ($student->parent->middle_name ? $student->parent->middle_name . ' ' : '') . $student->parent->last_name
                    : 'N/A';
                $parentPhone = $student->parent ? $student->parent->phone : 'N/A';
                
                // Determine health status
                $healthStatus = 'Good';
                if ($student->has_disability || $student->has_chronic_illness) {
                    $healthStatus = 'Bad';
                } elseif ($student->general_health_condition) {
                    $healthLower = strtolower($student->general_health_condition);
                    if (strpos($healthLower, 'poor') !== false || 
                        strpos($healthLower, 'bad') !== false || 
                        strpos($healthLower, 'sick') !== false || 
                        strpos($healthLower, 'ill') !== false) {
                        $healthStatus = 'Bad';
                    }
                }

                $data[] = [
                    $student->admission_number,
                    $fullName,
                    $student->gender,
                    $className,
                    $subclassName,
                    $student->status,
                    $healthStatus,
                    $parentName,
                    $parentPhone,
                    $student->admission_date ? $student->admission_date->format('Y-m-d') : 'N/A'
                ];
            }

            // Generate CSV file
            $filename = 'Students_Report_' . date('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Error exporting students Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export Excel: ' . $e->getMessage());
        }
    }

    public function get_student_details($studentID)
    {
        // Check read permission - Allow read_only, create, update, delete permissions for viewing
        $userType = Session::get('user_type');
        $canView = false;

        if ($userType === 'Admin') {
            $canView = true;
        } else {
            // Check if user has any student permission (read_only, create, update, delete)
            $canView = $this->hasPermission('student_read_only') ||
                      $this->hasPermission('student_create') ||
                      $this->hasPermission('student_update') ||
                      $this->hasPermission('student_delete') ||
                      $this->hasPermission('view_students'); // Legacy support
        }

        if (!$canView) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view student details.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');

        $student = Student::with(['subclass.class', 'parent'])
            ->where('studentID', $studentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        $studentImgPath = $student->photo
            ? asset('userImages/' . $student->photo)
            : ($student->gender == 'Female'
                ? asset('images/female.png')
                : asset('images/male.png'));

        return response()->json([
            'success' => true,
            'student' => [
                'studentID' => $student->studentID,
                'admission_number' => $student->admission_number,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'full_name' => $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('d M Y') : 'N/A',
                'admission_date' => $student->admission_date ? $student->admission_date->format('d M Y') : 'N/A',
                'address' => $student->address ?? 'N/A',
                'photo' => $studentImgPath,
                'status' => $student->status,
                'class' => $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name . ' ' . $student->subclass->subclass_name
                    : 'N/A',
                'birth_certificate_number' => $student->birth_certificate_number ?? 'N/A',
                'religion' => $student->religion ?? 'N/A',
                'nationality' => $student->nationality ?? 'N/A',
                'general_health_condition' => $student->general_health_condition ?? 'N/A',
                'has_disability' => $student->has_disability ?? false,
                'disability_details' => $student->disability_details ?? 'N/A',
                'has_chronic_illness' => $student->has_chronic_illness ?? false,
                'chronic_illness_details' => $student->chronic_illness_details ?? 'N/A',
                'immunization_details' => $student->immunization_details ?? 'N/A',
                'emergency_contact_name' => $student->emergency_contact_name ?? 'N/A',
                'emergency_contact_relationship' => $student->emergency_contact_relationship ?? 'N/A',
                'emergency_contact_phone' => $student->emergency_contact_phone ?? 'N/A',
                'parent' => $student->parent ? [
                    'parentID' => $student->parent->parentID,
                    'full_name' => $student->parent->first_name . ' ' . ($student->parent->middle_name ? $student->parent->middle_name . ' ' : '') . $student->parent->last_name,
                    'phone' => $student->parent->phone ?? 'N/A',
                    'email' => $student->parent->email ?? 'N/A',
                    'occupation' => $student->parent->occupation ?? 'N/A',
                    'relationship' => $student->parent->relationship_to_student ?? 'N/A',
                ] : null,
                'is_disabled' => $student->is_disabled ?? false,
                'has_epilepsy' => $student->has_epilepsy ?? false,
                'has_allergies' => $student->has_allergies ?? false,
                'allergies_details' => $student->allergies_details ?? null,
                'registering_officer_name' => $student->registering_officer_name ?? 'N/A',
                'declaration_date' => $student->declaration_date ? $student->declaration_date->format('d M Y') : 'N/A',
            ]
        ]);
    }

    /**
     * Test device connection
     */
    public function test_device_connection(Request $request)
    {
        try {
            $ip = $request->input('ip', env('ZKTECO_IP', '192.168.1.108'));
            $port = $request->input('port', env('ZKTECO_PORT', 4370));
            $password = $request->input('password', env('ZKTECO_PASSWORD', 0));

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'ip' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535',
                'password' => 'nullable|string|max:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input: ' . $validator->errors()->first()
                ], 422);
            }

            Log::info("ZKTeco: Testing connection to device - IP: {$ip}, Port: {$port}, Comm Key: {$password}");

            // Try multiple connection methods
            $connected = false;
            $connectionMethod = '';
            $triedCommKeys = [$password];
            $zkteco = null;

            // Method 1: Try simple TCP connection test (without sending packets)
            Log::info("ZKTeco: Trying Method 1 - Simple TCP connection test");
            $zkteco = new ZKTecoService($ip, $port, $password);
            if ($zkteco->testConnectionOnly()) {
                $connected = true;
                $connectionMethod = 'Simple TCP Test';
                Log::info("ZKTeco: Simple TCP connection test successful");
            }

            // Method 2: Try full connection with provided Comm Key
            if (!$connected) {
                Log::info("ZKTeco: Trying Method 2 - Full connection with Comm Key: {$password}");
                $zkteco = new ZKTecoService($ip, $port, $password);
                if ($zkteco->connect()) {
                    $connected = true;
                    $connectionMethod = 'Full Connection';
                } else {
                    // If connection failed, try common Comm Keys
                    $commonCommKeys = ['0', '12345', '8888', '0000', ''];
                    $commonCommKeys = array_diff($commonCommKeys, [$password]); // Remove already tried

                    Log::info("ZKTeco: Initial connection failed, trying common Comm Keys...");

                    foreach ($commonCommKeys as $commKey) {
                        if ($connected) break;

                        Log::info("ZKTeco: Trying Comm Key: " . ($commKey === '' ? '(empty)' : $commKey));
                        $zkteco = new ZKTecoService($ip, $port, $commKey);
                        $triedCommKeys[] = $commKey;

                        if ($zkteco->connect()) {
                            $connected = true;
                            $password = $commKey; // Update to working Comm Key
                            $connectionMethod = 'Full Connection (Auto-detected Comm Key)';
                            Log::info("ZKTeco: Connection successful with Comm Key: " . ($commKey === '' ? '(empty)' : $commKey));
                            break;
                        }
                    }
                }
            }

            // Method 3: Try HTTP connection test (if device has web interface)
            if (!$connected) {
                Log::info("ZKTeco: Trying Method 3 - HTTP connection test");
                $zkteco = new ZKTecoService($ip, $port, $password);
                if ($zkteco->testHttpConnection()) {
                    $connected = true;
                    $connectionMethod = 'HTTP Connection';
                    Log::info("ZKTeco: HTTP connection test successful");
                }
            }

            if ($connected) {
                // Try to get device info (only if full connection was successful)
                $deviceInfo = [
                    'ip' => $ip,
                    'port' => $port,
                    'serial_number' => null,
                    'firmware_version' => null,
                    'device_name' => null
                ];

                if ($connectionMethod === 'Full Connection' || strpos($connectionMethod, 'Full Connection') !== false) {
                    try {
                        $deviceInfo = $zkteco->getDeviceInfo();
                        $zkteco->disconnect();
                    } catch (\Exception $e) {
                        Log::warning("ZKTeco: Could not get device info: " . $e->getMessage());
                        if ($zkteco) {
                            $zkteco->disconnect();
                        }
                    }
                } else {
                    // For simple TCP or HTTP tests, we can't get device info
                    Log::info("ZKTeco: Connection test successful but device info not available (connection method: {$connectionMethod})");
                }

                // Get server configuration for Push SDK
                // Priority: 1. Environment variable, 2. Request server info, 3. Default
                $serverIP = env('APP_SERVER_IP', null);
                if (!$serverIP) {
                    // Try to get from request
                    $serverIP = request()->server('SERVER_ADDR');
                    // If still null or localhost, use configured default
                    if (!$serverIP || $serverIP === '127.0.0.1' || $serverIP === '::1') {
                        $serverIP = '192.168.100.105'; // Default server IP
                    }
                }

                $serverPort = env('APP_SERVER_PORT', null);
                if (!$serverPort) {
                    $serverPort = request()->server('SERVER_PORT') ?: '8000';
                }

                $response = [
                    'success' => true,
                    'message' => 'Successfully connected to device' .
                        ($connectionMethod ? " (Method: {$connectionMethod})" : '') .
                        (count($triedCommKeys) > 1 ? ' (tried multiple Comm Keys)' : ''),
                    'connection_method' => $connectionMethod,
                    'device_info' => [
                        'ip' => $ip,
                        'port' => $port,
                        'serial_number' => $deviceInfo['serial_number'] ?? null,
                        'firmware_version' => $deviceInfo['firmware_version'] ?? null,
                        'device_name' => $deviceInfo['device_name'] ?? null,
                    ],
                    'comm_key_info' => [
                        'working_comm_key' => $password === '' ? '(empty)' : $password,
                        'tried_comm_keys' => array_map(function($key) { return $key === '' ? '(empty)' : $key; }, $triedCommKeys),
                        'note' => count($triedCommKeys) > 1 ? 'Multiple Comm Keys were tried. Update your .env file with the working Comm Key.' : 'Comm Key matched on first try.'
                    ],
                    'server_config' => [
                        'server_ip' => $serverIP,
                        'server_port' => $serverPort,
                        'push_sdk_url' => "http://{$serverIP}:{$serverPort}/iclock/getrequest",
                        'push_sdk_data_url' => "http://{$serverIP}:{$serverPort}/iclock/cdata",
                        'instructions' => [
                            '1. On device: Menu → System → Communication → ADMS',
                            '2. Enable ADMS: ON',
                            "3. Server IP: {$serverIP}",
                            "4. Server Port: {$serverPort}",
                            '5. Server Path: /iclock/getrequest',
                            '6. Save settings'
                        ]
                    ]
                ];

                Log::info("ZKTeco: Connection test successful - IP: {$ip}, Port: {$port}");

                return response()->json($response);
            } else {
                // Get detailed error from logs
                $lastError = socket_last_error();
                $errorString = socket_strerror($lastError);

                Log::error("ZKTeco: Connection test failed - IP: {$ip}, Port: {$port}, Error Code: {$lastError}, Error: {$errorString}");

                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed. ' . $errorString . ' (Code: ' . $lastError . ')'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('ZKTeco Connection Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve users from device
     */
    public function retrieve_users_from_device(Request $request)
    {
        try {
            $ip = $request->input('ip', env('ZKTECO_IP', '192.168.1.108'));
            $port = $request->input('port', env('ZKTECO_PORT', 4370));
            $password = $request->input('password', env('ZKTECO_PASSWORD', 0));

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'ip' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535',
                'password' => 'nullable|string|max:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input: ' . $validator->errors()->first()
                ], 422);
            }

            Log::info("ZKLib: Retrieving users from device - IP: {$ip}, Port: {$port}, Comm Key: {$password}");

            // Use ZKLib library to retrieve users from fingerprint device
            $zkLib = new ZKLib($ip, $port, $password);

            // Connect to device
            if (!$zkLib->connect()) {
                // Get detailed error information
                $lastError = socket_last_error();
                $errorString = socket_strerror($lastError);

                // Get recent log entries for more details
                $logFile = storage_path('logs/laravel.log');
                $recentLogs = [];
                if (file_exists($logFile)) {
                    $lines = file($logFile);
                    $recentLines = array_slice($lines, -20); // Last 20 lines
                    $recentLogs = array_filter($recentLines, function($line) {
                        return stripos($line, 'ZKLib') !== false ||
                               stripos($line, 'getUsers') !== false ||
                               stripos($line, 'GET_USER') !== false ||
                               stripos($line, 'USERTEMP_RRQ') !== false;
                    });
                }

                $errorMessage = 'Failed to connect to device. ';

                // Provide specific error message based on error code
                if ($lastError == 10054) { // Connection forcibly closed
                    $errorMessage .= 'Connection was closed by device. This usually means: ';
                    $errorMessage .= '1) Device requires authentication before GET_USER command, ';
                    $errorMessage .= '2) Comm Key may be incorrect, ';
                    $errorMessage .= '3) Device protocol mismatch. ';
                    $errorMessage .= 'Error Code: ' . $lastError . ' (' . $errorString . ')';
                } elseif ($lastError == 10061) { // Connection refused
                    $errorMessage .= 'Connection refused. Error Code: ' . $lastError . ' (' . $errorString . ')';
                } elseif ($lastError == 10060) { // Connection timeout
                    $errorMessage .= 'Connection timeout. Error Code: ' . $lastError . ' (' . $errorString . ')';
                } else {
                    $errorMessage .= 'Error Code: ' . $lastError . ' (' . $errorString . ')';
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error_code' => $lastError,
                    'error_string' => $errorString,
                    'recent_logs' => array_values($recentLogs)
                ], 500);
            }

            // Retrieve users using ZKLib
            $users = $zkLib->getUsers();

            if ($users === false) {
                // Get detailed error information
                $lastError = socket_last_error();
                $errorString = socket_strerror($lastError);

                // Get recent log entries for more details
                $logFile = storage_path('logs/laravel.log');
                $recentLogs = [];
                if (file_exists($logFile)) {
                    $lines = file($logFile);
                    $recentLines = array_slice($lines, -20); // Last 20 lines
                    $recentLogs = array_filter($recentLines, function($line) {
                        return stripos($line, 'ZKLib') !== false ||
                               stripos($line, 'getUsers') !== false ||
                               stripos($line, 'GET_USER') !== false ||
                               stripos($line, 'USERTEMP_RRQ') !== false;
                    });
                }

                $errorMessage = 'Failed to retrieve users from device. ';

                // Provide specific error message based on error code
                if ($lastError == 10054) { // Connection forcibly closed
                    $errorMessage .= 'Connection was closed by device. This usually means: ';
                    $errorMessage .= '1) Device requires authentication before GET_USER command, ';
                    $errorMessage .= '2) Comm Key may be incorrect, ';
                    $errorMessage .= '3) Device protocol mismatch. ';
                    $errorMessage .= 'Error Code: ' . $lastError . ' (' . $errorString . ')';
                } elseif ($lastError == 10061) { // Connection refused
                    $errorMessage .= 'Connection refused. Error Code: ' . $lastError . ' (' . $errorString . ')';
                } elseif ($lastError == 10060) { // Connection timeout
                    $errorMessage .= 'Connection timeout. Error Code: ' . $lastError . ' (' . $errorString . ')';
                } else {
                    $errorMessage .= 'Error Code: ' . $lastError . ' (' . $errorString . ')';
                }

                $zkLib->disconnect();

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error_code' => $lastError,
                    'error_string' => $errorString,
                    'recent_logs' => array_values($recentLogs)
                ], 500);
            }

            $zkLib->disconnect();

            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved ' . count($users) . ' user(s) from device',
                'users' => $users,
                'count' => count($users)
            ], 200);

        } catch (\Exception $e) {
            Log::error('ZKLib Retrieve Users Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register student to biometric device directly (not via API)
     *
     * @param string $fingerprintId The fingerprint ID
     * @param string $studentName The student's first name (uppercase)
     * @return array Response with success status and data
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
     * Check fingerprint capture progress for a given fingerprint/enroll ID
     * Used by classMangement fingerprint modal to update progress in real-time
     */
    public function check_fingerprint_progress(Request $request)
    {
        $request->validate([
            'fingerprint_id' => 'required|integer',
        ]);

        $fingerprintId = (int) $request->input('fingerprint_id');

        try {
            $today = \Carbon\Carbon::today()->toDateString();

            // Read attendance DIRECT from device instead of DB
            $ip   = config('zkteco.ip', '192.168.100.108');
            $port = (int) config('zkteco.port', 4370);

            $zkteco = new \App\Services\ZKTecoService($ip, $port);

            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device while checking fingerprint progress',
                    'count'   => 0,
                    'today'   => $today,
                ], 200);
            }

            $allAttendance = $zkteco->getAttendance();
            $zkteco->disconnect();

            // Count how many punches today for this user_id (fingerprint/enroll ID)
            $count = 0;
            foreach ($allAttendance as $record) {
                if (!isset($record['user_id'], $record['record_time'])) {
                    continue;
                }
                if ((int)$record['user_id'] !== $fingerprintId) {
                    continue;
                }
                $recordDate = \Carbon\Carbon::parse($record['record_time'])->toDateString();
                if ($recordDate === $today) {
                    $count++;
                }
            }

            return response()->json([
                'success'        => true,
                'fingerprint_id' => $fingerprintId,
                'count'          => $count,
                'today'          => $today,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error checking fingerprint progress: ' . $e->getMessage(), [
                'fingerprint_id' => $fingerprintId,
                'trace'          => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check fingerprint progress: ' . $e->getMessage(),
                'count'   => 0,
                'today'   => \Carbon\Carbon::today()->toDateString(),
            ], 500);
        }
    }

    /**
     * Get all subclasses with student statistics
     */
    public function getSubclassesWithStats()
    {
        try {
            // Check permission
            $userType = Session::get('user_type');
            $canView = false;

            if ($userType === 'Admin') {
                $canView = true;
            } else {
                $canView = $this->hasPermission('student_read_only') ||
                          $this->hasPermission('student_create') ||
                          $this->hasPermission('student_update') ||
                          $this->hasPermission('student_delete') ||
                          $this->hasPermission('view_students');
            }

            if (!$canView) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied'
                ], 403);
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session'
                ], 401);
            }

            // Join with classes to filter by schoolID
            $subclasses = Subclass::join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->select('subclasses.*')
                   ->with('class')
                ->get()
                ->map(function ($subclass) {
                    // Count students by gender
                    $maleCount = Student::where('subclassID', $subclass->subclassID)
                        ->where('gender', 'Male')
                        ->where('status', 'Active')
                        ->count();

                    $femaleCount = Student::where('subclassID', $subclass->subclassID)
                        ->where('gender', 'Female')
                        ->where('status', 'Active')
                        ->count();

                    $totalCount = $maleCount + $femaleCount;

                    return [
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                           'class_name' => optional($subclass->class)->class_name ?? 'N/A',
                        'total_students' => $totalCount,
                        'male_count' => $maleCount,
                        'female_count' => $femaleCount
                    ];
                })
                ->sortBy('class_name');

            return response()->json([
                'success' => true,
                'subclasses' => $subclasses->values()->toArray(),
                'debug' => [
                    'schoolID' => $schoolID,
                    'count' => $subclasses->count()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('getSubclassesWithStats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }
}
