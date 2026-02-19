<?php

namespace App\Http\Controllers;

use App\Models\OtherStaff;
use App\Models\StaffProfession;
use App\Models\StaffPermission;
use App\Models\School;
use App\Models\User;
use App\Services\ZKTecoService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ManageOtherStaffController extends Controller
{
    /**
     * Manage Other Staff - Main View
     */
    public function manageOtherStaff()
    {
        $user = Session::get('user_type');

        if (!$user || $user !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');
        $otherStaff = OtherStaff::where('schoolID', $schoolID)->get();
        $school = School::find($schoolID);
        $professions = StaffProfession::where('schoolID', $schoolID)->get();

        return view('Admin.manage_other_staff', compact('otherStaff', 'school', 'professions'));
    }

    /**
     * Save Staff Profession
     */
    public function save_staff_profession(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info('save_staff_profession called', [
                'request_data' => $request->all(),
                'schoolID' => Session::get('schoolID')
            ]);

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                Log::error('School ID is missing');
                return response()->json(['error' => 'School ID is required'], 422);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ], [
                'name.required' => 'Profession name is required.',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Check if profession with same name already exists for this school
            $existing = StaffProfession::where('schoolID', $schoolID)
                ->where('name', $request->name)
                ->lockForUpdate() // Lock to prevent race conditions
                ->first();

            if ($existing) {
                DB::rollBack();
                Log::warning('Profession already exists', ['name' => $request->name]);
                return response()->json(['errors' => ['name' => 'This profession already exists for your school']], 422);
            }

            Log::info('Creating profession', [
                'name' => $request->name,
                'description' => $request->description,
                'schoolID' => $schoolID
            ]);

            $profession = StaffProfession::create([
                'name' => $request->name,
                'description' => $request->description ?? null,
                'schoolID' => $schoolID,
            ]);

            DB::commit();
            Log::info('Profession created successfully', ['profession_id' => $profession->id]);

            return response()->json([
                'success' => 'Profession "' . $profession->name . '" created successfully!',
                'profession' => $profession
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception in save_staff_profession', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Staff Profession
     */
    public function get_staff_profession($id)
    {
        try {
            $schoolID = Session::get('schoolID');
            $profession = StaffProfession::where('id', $id)
                ->where('schoolID', $schoolID)
                ->with('permissions')
                ->first();

            if (!$profession) {
                return response()->json(['error' => 'Profession not found'], 404);
            }

            return response()->json(['profession' => $profession]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Staff Profession
     */
    public function update_staff_profession(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['error' => 'School ID is required'], 422);
            }

            $validator = Validator::make($request->all(), [
                'profession_id' => 'required|exists:staff_professions,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            $profession = StaffProfession::where('id', $request->profession_id)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$profession) {
                return response()->json(['error' => 'Profession not found'], 404);
            }

            // Check if another profession with same name exists
            $existing = StaffProfession::where('schoolID', $schoolID)
                ->where('name', $request->name)
                ->where('id', '!=', $request->profession_id)
                ->first();

            if ($existing) {
                return response()->json(['errors' => ['name' => 'This profession name already exists']], 422);
            }

            $profession->update([
                'name' => $request->name,
                'description' => $request->description ?? null,
            ]);

            return response()->json([
                'success' => 'Profession updated successfully!',
                'profession' => $profession->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Staff Profession
     */
    public function delete_staff_profession($id)
    {
        try {
            $schoolID = Session::get('schoolID');
            $profession = StaffProfession::where('id', $id)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$profession) {
                return response()->json(['error' => 'Profession not found'], 404);
            }

            // Check if profession is assigned to any staff
            $staffCount = OtherStaff::where('profession_id', $id)->count();
            if ($staffCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete profession. It is currently assigned to ' . $staffCount . ' staff member(s).'
                ], 422);
            }

            $professionName = $profession->name;
            $profession->delete();

            return response()->json([
                'success' => 'Profession "' . $professionName . '" deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Save Staff Permissions/Duties
     */
    public function save_staff_permissions(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['error' => 'School ID is required'], 422);
            }

            $validator = Validator::make($request->all(), [
                'profession_id' => 'required|exists:staff_professions,id',
                'permissions' => 'required|array|min:1',
                'permissions.*' => 'required|string|max:255',
            ], [
                'permissions.required' => 'Please select at least one permission/duty.',
                'permissions.min' => 'Please select at least one permission/duty.',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Verify profession belongs to school
            $profession = StaffProfession::where('id', $request->profession_id)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$profession) {
                return response()->json(['error' => 'Profession not found or does not belong to this school'], 404);
            }

            // Delete existing permissions for this profession
            StaffPermission::where('profession_id', $request->profession_id)->delete();

            // Create new permissions
            $permissionNames = [];
            foreach ($request->permissions as $permissionName) {
                $permissionName = trim($permissionName);
                if (!empty($permissionName)) {
                    $category = $this->getPermissionCategory($permissionName);
                    StaffPermission::create([
                        'profession_id' => $request->profession_id,
                        'name' => $permissionName,
                        'guard_name' => 'web',
                        'permission_category' => $category,
                    ]);
                    $permissionNames[] = $permissionName;
                }
            }

            return response()->json([
                'success' => 'Permissions assigned successfully!',
                'permissions' => $permissionNames
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Staff Profession with Permissions
     */
    public function get_staff_profession_with_permissions($id)
    {
        try {
            $schoolID = Session::get('schoolID');
            $profession = StaffProfession::where('id', $id)
                ->where('schoolID', $schoolID)
                ->with('permissions')
                ->first();

            if (!$profession) {
                return response()->json(['error' => 'Profession not found'], 404);
            }

            return response()->json([
                'success' => true,
                'profession' => $profession
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Determine permission category based on permission name
     */
    private function getPermissionCategory($permissionName)
    {
        $permissionName = strtolower($permissionName);
        
        if (strpos($permissionName, '_') !== false) {
            $parts = explode('_', $permissionName);
            $category = $parts[0];
            
            $validCategories = ['examination', 'classes', 'subject', 'result', 'attendance', 'student', 'parent', 'timetable', 'teacher', 'fees', 'accommodation', 'library', 'calendar', 'fingerprint', 'task', 'sms', 'revenue', 'expenses', 'resources'];
            if (in_array($category, $validCategories)) {
                return $category;
            }
        }

        // Legacy format matching
        if (strpos($permissionName, 'examination') !== false || strpos($permissionName, 'exam') !== false) return 'examination';
        if (strpos($permissionName, 'subject') !== false) return 'subject';
        if (strpos($permissionName, 'teacher') !== false || strpos($permissionName, 'role') !== false) return 'teacher';
        if (strpos($permissionName, 'class') !== false) return 'classes';
        if (strpos($permissionName, 'attendance') !== false) return 'attendance';
        if (strpos($permissionName, 'result') !== false) return 'result';
        if (strpos($permissionName, 'timetable') !== false) return 'timetable';
        if (strpos($permissionName, 'student') !== false) return 'student';
        if (strpos($permissionName, 'parent') !== false) return 'parent';
        if (strpos($permissionName, 'fee') !== false) return 'fees';
        if (strpos($permissionName, 'revenue') !== false) return 'revenue';
        if (strpos($permissionName, 'expense') !== false) return 'expenses';
        if (strpos($permissionName, 'resource') !== false) return 'resources';
        if (strpos($permissionName, 'accommodation') !== false) return 'accommodation';
        if (strpos($permissionName, 'library') !== false) return 'library';
        if (strpos($permissionName, 'calendar') !== false || strpos($permissionName, 'holiday') !== false || strpos($permissionName, 'event') !== false) return 'calendar';
        if (strpos($permissionName, 'fingerprint') !== false) return 'fingerprint';
        if (strpos($permissionName, 'task') !== false) return 'task';
        if (strpos($permissionName, 'sms') !== false || strpos($permissionName, 'notification') !== false) return 'sms';

        return null;
    }

    /**
     * Save Other Staff
     */
    public function save_other_staff(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'first_name'      => 'required|string|max:255',
                'last_name'       => 'required|string|max:255',
                'gender'          => 'required|in:Male,Female',
                'national_id'     => 'required|unique:other_staff,national_id',
                'employee_number' => 'required|unique:other_staff,employee_number',
                'email'           => 'required|email|unique:other_staff,email',
                'phone_number'    => [
                    'required',
                    'unique:other_staff,phone_number',
                    'regex:/^255\d{9}$/'
                ],
                'profession_id'   => 'nullable|exists:staff_professions,id',
                'bank_account_number' => 'nullable|string|max:255',
                'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ], [
                'phone_number.regex' => 'Phone number must have 12 digits: start with 255 followed by 9 digits (e.g., 255614863345)',
                'image.max'         => 'Image must not exceed 2MB.',
                'image.mimes'       => 'Only JPG and PNG formats are allowed.',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Handle Image Upload
            $imageName = null;
            if ($request->hasFile('image')) {
                // Determine upload path - Prioritize public_html for cPanel
                $basePath = base_path();
                $parentDir = dirname($basePath);
                $publicHtmlPath = $parentDir . '/public_html/userImages';
                $docRootPath = $_SERVER['DOCUMENT_ROOT'] . '/userImages';
                $localPublicPath = public_path('userImages');

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
                $imageName = time().'_'.$request->file('image')->getClientOriginalName();
                $request->file('image')->move($uploadPath, $imageName);
            }

            $schoolID = Session::get('schoolID') ?? $request->schoolID ?? null;

            // Generate unique 4-digit fingerprint ID
            $fingerprintId = null;
            do {
                $fingerprintId = (string)rand(1000, 9999);
            } while (
                User::where('fingerprint_id', $fingerprintId)->exists() ||
                OtherStaff::where('fingerprint_id', $fingerprintId)->exists() ||
                OtherStaff::where('id', (int)$fingerprintId)->exists()
            );

            // Create staff with id = fingerprintID
            $staff = OtherStaff::create([
                'id'             => (int)$fingerprintId,
                'schoolID'       => $schoolID,
                'profession_id'  => $request->profession_id ?? null,
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
                'fingerprint_id' => $fingerprintId,
            ]);

            // Register to biometric device (direct, not API)
            try {
                Log::info("ZKTeco Direct: Attempting to register staff - Fingerprint ID: {$fingerprintId}, Name: {$request->first_name}");
                $staffName = strtoupper($request->first_name);
                $apiResult = $this->registerStaffToBiometricDevice($fingerprintId, $staffName);
                
                if ($apiResult['success']) {
                    Log::info("ZKTeco Direct: Staff registered successfully - Fingerprint ID: {$fingerprintId}");
                } else {
                    Log::error("ZKTeco Direct: Staff registration failed - Fingerprint ID: {$fingerprintId}, Error: " . ($apiResult['message'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                Log::error('ZKTeco Direct Registration Error: ' . $e->getMessage());
            }

            // Create user account
            $user = User::create([
                'name'          => $request->employee_number,
                'email'         => $request->email,
                'password'      => bcrypt($request->last_name),
                'user_type'     => 'Staff',
                'fingerprint_id' => $fingerprintId
            ]);

            // Send SMS with credentials
            try {
                $school = School::find($schoolID);
                $smsService = new SmsService();
                $phoneNumber = $request->phone_number;
                $schoolName = $school->school_name ?? 'School';
                $username = $request->employee_number;
                $password = $request->last_name;
                
                $message = "{$schoolName}. Usajili umekamilika. Username: {$username}. Password: {$password}. Asante";
                $smsResult = $smsService->sendSms($phoneNumber, $message);
                
                if ($smsResult['success']) {
                    Log::info("SMS sent successfully to staff: {$phoneNumber}");
                } else {
                    Log::error("SMS sending failed: " . ($smsResult['message'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                Log::error('SMS sending error: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => 'Staff added successfully!',
                'fingerprint_id' => $fingerprintId
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Other Staff
     */
    public function get_other_staff($id)
    {
        try {
            $staff = OtherStaff::find($id);
            if (!$staff) {
                return response()->json(['error' => 'Staff not found'], 404);
            }
            return response()->json(['staff' => $staff]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Other Staff
     */
    public function update_other_staff(Request $request)
    {
        try {
            $staffId = $request->input('staff_id');
            $staff = OtherStaff::find($staffId);

            if (!$staff) {
                return response()->json(['error' => 'Staff not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'staff_id'        => 'required|exists:other_staff,id',
                'first_name'      => 'required|string|max:255',
                'last_name'       => 'required|string|max:255',
                'gender'          => 'required|in:Male,Female',
                'national_id'     => 'required|unique:other_staff,national_id,' . $staffId,
                'employee_number' => 'required|unique:other_staff,employee_number,' . $staffId,
                'email'           => 'required|email|unique:other_staff,email,' . $staffId,
                'phone_number'    => [
                    'required',
                    'unique:other_staff,phone_number,' . $staffId,
                    'regex:/^255\d{9}$/'
                ],
                'profession_id'   => 'nullable|exists:staff_professions,id',
                'bank_account_number' => 'nullable|string|max:255',
                'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            $oldEmail = $staff->email;

            // Handle Image Upload
            $imageName = $staff->image;
            if ($request->hasFile('image')) {
                // Determine upload path - Prioritize public_html for cPanel
                $basePath = base_path();
                $parentDir = dirname($basePath);
                $publicHtmlPath = $parentDir . '/public_html/userImages';
                $docRootPath = $_SERVER['DOCUMENT_ROOT'] . '/userImages';
                $localPublicPath = public_path('userImages');

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
                if ($staff->image && file_exists($uploadPath . '/' . $staff->image)) {
                    unlink($uploadPath . '/' . $staff->image);
                }
                $imageName = time().'_'.$request->file('image')->getClientOriginalName();
                $request->file('image')->move($uploadPath, $imageName);
            }

            // Update staff
            $staff->update([
                'profession_id'  => $request->profession_id ?? null,
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

            return response()->json(['success' => 'Staff updated successfully!']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Other Staff
     */
    public function delete_other_staff($id)
    {
        try {
            $schoolID = Session::get('schoolID');
            $staff = OtherStaff::where('id', $id)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$staff) {
                return response()->json(['error' => 'Staff not found'], 404);
            }

            // Delete user account
            $user = User::where('email', $staff->email)->first();
            if ($user) {
                $user->delete();
            }

            // Delete image if exists
            if ($staff->image) {
                $imagePath = public_path('userImages/' . $staff->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $staffName = $staff->first_name . ' ' . $staff->last_name;
            $staff->delete();

            return response()->json([
                'success' => 'Staff "' . $staffName . '" deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send Staff to Fingerprint Device
     */
    public function send_staff_to_fingerprint(Request $request)
    {
        try {
            $request->validate([
                'staff_id' => 'required|exists:other_staff,id'
            ]);

            $staff = OtherStaff::findOrFail($request->staff_id);

            if ($staff->fingerprint_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff already has a fingerprint ID: ' . $staff->fingerprint_id
                ], 400);
            }

            DB::beginTransaction();

            // Generate unique 4-digit fingerprint ID
            $fingerprintId = null;
            $oldStaffId = $staff->id;
            
            do {
                $fingerprintId = (string)rand(1000, 9999);
            } while (
                User::where('fingerprint_id', $fingerprintId)->exists() ||
                OtherStaff::where('fingerprint_id', $fingerprintId)->exists() ||
                OtherStaff::where('id', (int)$fingerprintId)->exists()
            );

            // Update staff with fingerprint_id first
            $staff->fingerprint_id = $fingerprintId;
            $staff->save();
            
            if ($oldStaffId != (int)$fingerprintId) {
                // WARNING: Changing Primary Key is risky. 
                // We must temporarily disable FK checks to avoid constraint violations during the update.
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                try {
                    DB::table('other_staff')
                        ->where('id', $oldStaffId)
                        ->update(['id' => (int)$fingerprintId]);
                        
                    // Also update any related tables manually if not CASCADE
                    // For example, if User links to OtherStaff via some other key, or if there are other relations
                } finally {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
                
                // Refresh staff instance with new ID
                $staff = OtherStaff::find((int)$fingerprintId);
            }

            // Send to biometric device
            $staffName = strtoupper($staff->first_name);
            $apiResult = $this->registerStaffToBiometricDevice($fingerprintId, $staffName);

            if ($apiResult['success']) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Staff successfully sent to fingerprint device',
                    'fingerprint_id' => $fingerprintId
                ]);
            } else {
                // We keep the ID change even if device registration fails? 
                // Usually better to commit the ID change so we don't handle it again, 
                // but the user might want a retry. 
                // Let's commit because the ID change is a database structural requirement for this system
                DB::commit();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint ID generated (' . $fingerprintId . ') but failed to register to device: ' . ($apiResult['message'] ?? 'Unknown error'),
                    'fingerprint_id' => $fingerprintId
                ], 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register staff to biometric device directly (not via API)
     */
    private function registerStaffToBiometricDevice($fingerprintId, $staffName)
    {
        try {
            $ip = config('zkteco.ip', '192.168.1.108');
            $port = (int) config('zkteco.port', 4370);

            Log::info("ZKTeco Direct: Attempting to register staff to device", [
                'fingerprint_id' => $fingerprintId,
                'staff_name'     => $staffName,
                'device_ip'      => $ip,
                'device_port'    => $port,
            ]);

            $zkteco = new ZKTecoService($ip, $port);
            $uid = (int) $fingerprintId;
            $name = strtoupper($staffName);

            $result = $zkteco->registerUser($uid, $name, 0, '', '', (string) $uid);

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
