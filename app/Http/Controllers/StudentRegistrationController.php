<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subclass;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\ParentModel;
use App\Services\SmsService;

class StudentRegistrationController extends Controller
{
    /**
     * Show step 1 - Student Particulars (SECTION A)
     */
    public function showStep1()
    {
        $schoolID = Session::get('schoolID');

        return view('student_registration.step1', [
            'schoolID' => $schoolID
        ]);
    }

    /**
     * Store step 1 - Student Particulars
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date|before:today',
            'birth_certificate_number' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:100',
            'student_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $schoolID = Session::get('schoolID');

        // Store in session for later
        session([
            'student_registration' => [
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'birth_certificate_number' => $request->birth_certificate_number,
                'religion' => $request->religion,
                'nationality' => $request->nationality,
                'schoolID' => $schoolID
            ]
        ]);

        // Handle photo upload
        if ($request->hasFile('student_photo')) {
            $photoPath = $request->file('student_photo')->store('students', 'public');
            $registration = session('student_registration');
            $registration['photo'] = $photoPath;
            session(['student_registration' => $registration]);
        }

        return redirect()->route('student.registration.step2')->with('success', 'Step 1 completed. Proceed to parent/guardian information.');
    }

    /**
     * Show step 2 - Parent/Guardian Information (SECTION B)
     */
    public function showStep2()
    {
        $schoolID = Session::get('schoolID');

        return view('student_registration.step2', [
            'schoolID' => $schoolID
        ]);
    }

    /**
     * Search for existing parent by phone
     */
    public function searchParentByPhone(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string|max:20'
            ]);

            $schoolID = Session::get('schoolID');
            
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found. Please log in again.'
                ], 400);
            }
            
            // First check if parent exists in current school
            $parent = ParentModel::where('schoolID', $schoolID)
                ->where('phone', $request->phone)
                ->first();

            if ($parent) {
                // Get image path - parent photos are stored in public/userImages/
                $imagePath = null;
                if ($parent->photo) {
                    $photoPath = public_path('userImages/' . $parent->photo);
                    if (file_exists($photoPath)) {
                        $imagePath = asset('userImages/' . $parent->photo);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                        'image' => $imagePath,
                        'gender' => $parent->gender ?? null,
                        'relationship_to_student' => $parent->relationship_to_student ?? 'Parent/Guardian'
                    ],
                    'message' => 'Parent found in this school. You can use this parent or enter a different number.',
                    'in_current_school' => true
                ]);
            }

            // Check if phone number exists in another school
            $parentInOtherSchool = ParentModel::where('phone', $request->phone)
                ->where('schoolID', '!=', $schoolID)
                ->first();

            if ($parentInOtherSchool) {
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number already exists in another school. Please try another number.',
                    'in_other_school' => true
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No parent found with this phone number. Please enter new parent details.',
                'in_other_school' => false
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error searching parent by phone: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for parent. Please try again.'
            ], 500);
        }
    }

    /**
     * Store step 2 - Parent/Guardian Information
     */
    public function storeStep2(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:parents,parentID',
            'parent_first_name' => 'nullable|string|max:100',
            'parent_middle_name' => 'nullable|string|max:100',
            'parent_last_name' => 'nullable|string|max:100',
            'parent_phone' => 'required|string|max:20',
            'parent_relationship' => 'required|string|max:100',
            'parent_occupation' => 'nullable|string|max:100',
            'parent_address' => 'nullable|string|max:255',
            'parent_email' => 'nullable|email'
        ]);

        $schoolID = Session::get('schoolID');
        $parentID = null;

        // Format phone number to start with 255 (not +255)
        $phone = $request->parent_phone;
        $phone = preg_replace('/[^0-9]/', '', $phone); // Remove any non-numeric characters
        // Remove leading 255 if user entered it (we'll add it back)
        if (str_starts_with($phone, '255')) {
            $phone = substr($phone, 3);
        }
        // Add 255 prefix (without +)
        $phone = '255' . $phone;

        // Check if parent exists
        if ($request->parent_id) {
            $parentID = $request->parent_id;
        } else {
            // Create new parent
            $parent = ParentModel::create([
                'schoolID' => $schoolID,
                'first_name' => $request->parent_first_name,
                'middle_name' => $request->parent_middle_name,
                'last_name' => $request->parent_last_name,
                'phone' => $phone,
                'relationship_to_student' => $request->parent_relationship,
                'occupation' => $request->parent_occupation,
                'address' => $request->parent_address,
                'email' => $request->parent_email
            ]);
            $parentID = $parent->parentID;
        }

        // Store in session
        $registration = session('student_registration') ?? [];
        $registration['parentID'] = $parentID;
        session(['student_registration' => $registration]);

        return redirect()->route('student.registration.step3')->with('success', 'Step 2 completed. Proceed to health information.');
    }

    /**
     * Show step 3 - Health Information (SECTION C)
     */
    public function showStep3()
    {
        return view('student_registration.step3');
    }

    /**
     * Store step 3 - Health Information
     */
    public function storeStep3(Request $request)
    {
        $request->validate([
            'general_health_condition' => 'nullable|string',
            'has_disability' => 'nullable|boolean',
            'disability_details' => 'nullable|string',
            'has_chronic_illness' => 'nullable|boolean',
            'chronic_illness_details' => 'nullable|string',
            'immunization_details' => 'nullable|string'
        ]);

        $registration = session('student_registration') ?? [];
        $registration['general_health_condition'] = $request->general_health_condition;
        $registration['has_disability'] = $request->has_disability ? true : false;
        $registration['disability_details'] = $request->disability_details;
        $registration['has_chronic_illness'] = $request->has_chronic_illness ? true : false;
        $registration['chronic_illness_details'] = $request->chronic_illness_details;
        $registration['immunization_details'] = $request->immunization_details;

        session(['student_registration' => $registration]);

        return redirect()->route('student.registration.step4')->with('success', 'Step 3 completed. Proceed to emergency contact information.');
    }

    /**
     * Show step 4 - Emergency Contact (SECTION D)
     */
    public function showStep4()
    {
        return view('student_registration.step4');
    }

    /**
     * Store step 4 - Emergency Contact
     */
    public function storeStep4(Request $request)
    {
        $request->validate([
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_relationship' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:20'
        ]);

        $registration = session('student_registration') ?? [];
        $registration['emergency_contact_name'] = $request->emergency_contact_name;
        $registration['emergency_contact_relationship'] = $request->emergency_contact_relationship;
        $registration['emergency_contact_phone'] = $request->emergency_contact_phone;

        session(['student_registration' => $registration]);

        return redirect()->route('student.registration.step5')->with('success', 'Step 4 completed. Proceed to declaration.');
    }

    /**
     * Show step 5 - Declaration & Official Use (SECTIONS E & F)
     */
    public function showStep5()
    {
        $registration = session('student_registration') ?? [];

        return view('student_registration.step5', [
            'registration' => $registration
        ]);
    }

    /**
     * Store step 5 - Complete Registration
     */
    public function storeStep5(Request $request)
    {
        $request->validate([
            'parent_declaration_checkbox' => 'required|accepted',
            'parent_signature' => 'nullable|string',
            'declaration_date' => 'required|date',
            'registering_officer_name' => 'nullable|string|max:100',
            'registering_officer_title' => 'nullable|string|max:100',
            'registering_officer_signature' => 'nullable|string',
            'school_stamp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $registration = session('student_registration') ?? [];
        $schoolID = Session::get('schoolID');

        try {
            DB::beginTransaction();

            // Generate admission number
            $admissionNumber = $this->generateAdmissionNumber($schoolID);

            // Get default subclass for new students (assuming there's a default or we need to assign)
            // You may need to modify this based on your logic
            $subclass = Subclass::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->first();

            if (!$subclass) {
                throw new \Exception('No active class found for admission. Please create a class first.');
            }

            // Handle school stamp upload
            $stampPath = null;
            if ($request->hasFile('school_stamp')) {
                $stampPath = $request->file('school_stamp')->store('stamps', 'public');
            }

            // Create student
            $student = Student::create([
                'schoolID' => $schoolID,
                'subclassID' => $subclass->subclassID,
                'parentID' => $registration['parentID'] ?? null,
                'first_name' => $registration['first_name'],
                'middle_name' => $registration['middle_name'] ?? null,
                'last_name' => $registration['last_name'],
                'gender' => $registration['gender'],
                'date_of_birth' => $registration['date_of_birth'],
                'birth_certificate_number' => $registration['birth_certificate_number'] ?? null,
                'religion' => $registration['religion'] ?? null,
                'nationality' => $registration['nationality'] ?? null,
                'admission_number' => $admissionNumber,
                'admission_date' => now()->toDateString(),
                'address' => $registration['parent_address'] ?? null,
                'photo' => $registration['photo'] ?? null,
                'general_health_condition' => $registration['general_health_condition'] ?? null,
                'has_disability' => $registration['has_disability'] ?? false,
                'disability_details' => $registration['disability_details'] ?? null,
                'has_chronic_illness' => $registration['has_chronic_illness'] ?? false,
                'chronic_illness_details' => $registration['chronic_illness_details'] ?? null,
                'immunization_details' => $registration['immunization_details'] ?? null,
                'emergency_contact_name' => $registration['emergency_contact_name'] ?? null,
                'emergency_contact_relationship' => $registration['emergency_contact_relationship'] ?? null,
                'emergency_contact_phone' => $registration['emergency_contact_phone'] ?? null,
                'parent_declaration' => 'Declared on ' . $request->declaration_date,
                'parent_signature' => $request->parent_signature ?? null,
                'declaration_date' => $request->declaration_date,
                'registering_officer_name' => $request->registering_officer_name ?? null,
                'registering_officer_title' => $request->registering_officer_title ?? null,
                'registering_officer_signature' => $request->registering_officer_signature ?? null,
                'school_stamp' => $stampPath,
                'registration_status' => 'Completed'
            ]);

            DB::commit();

            // Clear session
            session()->forget('student_registration');

            return redirect()->route('student.registration.success', ['studentID' => $student->studentID])
                ->with('success', 'Student registered successfully! Admission Number: ' . $admissionNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Show registration success page
     */
    public function showSuccess($studentID)
    {
        $student = Student::findOrFail($studentID);

        return view('student_registration.success', [
            'student' => $student
        ]);
    }

    /**
     * Generate unique admission number
     */
    private function generateAdmissionNumber($schoolID)
    {
        $year = now()->year;
        $count = Student::where('schoolID', $schoolID)->whereYear('created_at', $year)->count() + 1;

        return $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Reset/Cancel registration
     */
    public function cancelRegistration()
    {
        session()->forget('student_registration');
        return redirect()->route('student.registration.step1')->with('info', 'Registration cancelled.');
    }

    /**
     * Handle complete form submission from modal
     */
    public function storeComplete(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            // Validate all steps
            $validationRules = [
                // Step 1
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => 'required|in:Male,Female',
                'date_of_birth' => 'required|date|before:today',
                'birth_certificate_number' => 'nullable|string|max:100',
                'religion' => 'nullable|string|max:100',
                'nationality' => 'nullable|string|max:100',
                'student_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'parent_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'subclassID' => 'required|exists:subclasses,subclassID',
                // Step 2
                'parent_phone' => 'nullable|string|max:20',
                'parent_id' => 'nullable|exists:parents,parentID',
                'parent_first_name' => 'nullable|string|max:100',
                'parent_last_name' => 'nullable|string|max:100',
                'parent_relationship' => 'nullable|string|max:100',
                'parent_occupation' => 'nullable|string|max:100',
                'parent_address' => 'nullable|string|max:255',
                'parent_email' => 'nullable|email',
                // Step 3
                'general_health_condition' => 'nullable|string',
                'has_disability' => 'nullable',
                'disability_details' => 'nullable|string',
                'has_chronic_illness' => 'nullable',
                'chronic_illness_details' => 'nullable|string',
                'immunization_details' => 'nullable|string',
                // Step 4
                'emergency_contact_name' => 'required|string|max:100',
                'emergency_contact_relationship' => 'required|string|max:100',
                'emergency_contact_phone' => 'required|string|max:20',
                // Step 5
                'parent_declaration' => 'required',
                'declaration_date' => 'required|date',
            ];

            // If using existing parent, new parent fields are not required
            // If creating new parent, parent fields are required
            if (!$request->parent_id) {
                $validationRules['parent_phone'] = 'required|string|max:20';
                $validationRules['parent_relationship'] = 'required|string|max:100';
                $validationRules['parent_first_name'] = 'required|string|max:100';
                $validationRules['parent_last_name'] = 'required|string|max:100';
            } else {
                // When using existing parent, ensure parent_id is valid
                $validationRules['parent_id'] = 'required|exists:parents,parentID';
            }

            $validated = $request->validate($validationRules);
            
            // Convert parent_declaration checkbox to boolean
            $parentDeclaration = $request->has('parent_declaration') && 
                                ($request->parent_declaration === 'on' || 
                                 $request->parent_declaration === '1' || 
                                 $request->parent_declaration === true);

            // Store student photo if provided - save to public/userImages/
            $photoPath = null;
            if ($request->hasFile('student_photo')) {
                $uploadPath = public_path('userImages');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $imageName = time() . '_' . $request->file('student_photo')->getClientOriginalName();
                $request->file('student_photo')->move($uploadPath, $imageName);
                $photoPath = $imageName; // Store just the filename, not full path
            }

            // Get or create parent
            $parentID = $request->parent_id;
            $parentRelationship = null;
            
            if (!$parentID) {
                // Create new parent - format phone number to start with 255 (not +255)
                $phone = $request->parent_phone;
                if ($phone) {
                    $phone = preg_replace('/[^0-9]/', '', $phone); // Remove any non-numeric characters
                    // Remove leading 255 if user entered it (we'll add it back)
                    if (str_starts_with($phone, '255')) {
                        $phone = substr($phone, 3);
                    }
                    // Add 255 prefix (without +)
                    $phone = '255' . $phone;
                }
                
                // Check if phone number exists in another school
                $parentInOtherSchool = ParentModel::where('phone', $phone)
                    ->where('schoolID', '!=', $schoolID)
                    ->first();
                
                if ($parentInOtherSchool) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This phone number already exists in another school. Please try another number.',
                        'errors' => [
                            'parent_phone' => ['This phone number already exists in another school. Please try another number.']
                        ]
                    ], 422);
                }
                
                // Create new parent - relationship is required
                if (!$request->parent_relationship) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parent relationship is required when creating a new parent.'
                    ], 422);
                }
                
                $parent = ParentModel::create([
                    'schoolID' => $schoolID,
                    'first_name' => $request->parent_first_name,
                    'middle_name' => $request->parent_middle_name ?? null,
                    'last_name' => $request->parent_last_name,
                    'phone' => $phone,
                    'relationship_to_student' => $request->parent_relationship,
                    'occupation' => $request->parent_occupation ?? null,
                    'address' => $request->parent_address ?? null,
                    'email' => $request->parent_email ?? null
                ]);
                $parentID = $parent->parentID;
                $parentRelationship = $request->parent_relationship;
                
                // Create user account for new parent if doesn't exist
                // Username = phone number, Password = last_name
                $existingUser = User::where('name', $phone)->first();
                if (!$existingUser) {
                    User::create([
                        'name' => $phone, // Username = phone number
                        'email' => $request->parent_email ?? null,
                        'password' => Hash::make($request->parent_last_name), // Password = last_name
                        'user_type' => 'parent'
                    ]);
                }
            } else {
                // Using existing parent - get relationship from parent record
                $existingParent = ParentModel::find($parentID);
                if (!$existingParent) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected parent not found.'
                    ], 404);
                }
                $parentRelationship = $existingParent->relationship_to_student ?? 'Parent/Guardian';
                
                // Ensure parent has user account (if not, create one)
                $parentPhone = $existingParent->phone;
                $existingUser = User::where('name', $parentPhone)->first();
                if (!$existingUser) {
                    User::create([
                        'name' => $parentPhone, // Username = phone number
                        'email' => $existingParent->email ?? null,
                        'password' => Hash::make($existingParent->last_name), // Password = last_name
                        'user_type' => 'parent'
                    ]);
                }
            }

            // Create student within transaction
            DB::beginTransaction();

            // Generate unique 4-digit fingerprint ID (must be unique in users table first)
            $fingerprintId = null;
            $maxAttempts = 100;
            $attempts = 0;
            
            do {
                $fingerprintId = (string)rand(1000, 9999);
                $attempts++;
                
                // Check if fingerprintID exists in users table
                $existsInUsers = User::where('fingerprint_id', $fingerprintId)->exists();
                
                // Check if fingerprintID exists in students table
                $existsInStudents = Student::where('fingerprint_id', $fingerprintId)->exists() ||
                                    Student::where('studentID', $fingerprintId)->exists();
                
                // If ID is unique in all tables, break the loop
                if (!$existsInUsers && !$existsInStudents) {
                    break;
                }
                
                if ($attempts >= $maxAttempts) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to generate unique student ID. Please try again.'
                    ], 500);
                }
            } while (true);

            $admissionNumber = $this->generateAdmissionNumber($schoolID);

            $student = Student::create([
                'studentID' => (int)$fingerprintId, // Set studentID equal to fingerprintID (as integer)
                'schoolID' => $schoolID,
                'subclassID' => $request->subclassID,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?? null,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'parentID' => $parentID,
                'admission_number' => $admissionNumber,
                'fingerprint_id' => $fingerprintId, // Also store in fingerprint_id column
                'sent_to_device' => false,
                'device_sent_at' => null,
                'fingerprint_capture_count' => 0,
                'photo' => $photoPath,
                'birth_certificate_number' => $request->birth_certificate_number,
                'religion' => $request->religion,
                'nationality' => $request->nationality,
                'general_health_condition' => $request->general_health_condition,
                'has_disability' => $request->has('has_disability') ? 1 : 0,
                'disability_details' => $request->disability_details,
                'has_chronic_illness' => $request->has('has_chronic_illness') ? 1 : 0,
                'chronic_illness_details' => $request->chronic_illness_details,
                'immunization_details' => $request->immunization_details,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'parent_declaration' => $parentDeclaration ? 1 : 0,
                'declaration_date' => $request->declaration_date,
                'registering_officer_name' => $request->registering_officer_name,
                'registering_officer_title' => $request->registering_officer_title,
                'registration_status' => 'Completed'
            ]);

            // Create user account for the student
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

            // Send SMS to parent with student credentials (ALWAYS send this)
            $smsSent = false;
            $parentSmsSent = false;
            if ($parentID) {
                $parent = ParentModel::find($parentID);
                if ($parent && $parent->phone) {
                    try {
                        $school = School::find($schoolID);
                        $smsService = new SmsService();
                        
                        // Always send student credentials to parent
                        $studentName = trim($request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name);
                        $smsResult = $smsService->sendStudentCredentials(
                            $parent->phone,
                            $school->school_name ?? 'School',
                            $studentName,
                            $admissionNumber, // Username = admission number
                            $request->last_name // Password = last name
                        );
                        $smsSent = $smsResult['success'];
                        
                        // If parent is new, also send parent credentials
                        if (!$request->parent_id) {
                            $parentLastName = $request->parent_last_name ?? $parent->last_name ?? '';
                            if ($parentLastName) {
                                $parentSmsResult = $smsService->sendParentCredentials(
                                    $parent->phone,
                                    $school->school_name ?? 'School',
                                    $parent->phone, // Username = phone number
                                    $parentLastName // Password = last name
                                );
                                $parentSmsSent = $parentSmsResult['success'];
                            }
                        }
                    } catch (\Exception $smsError) {
                        // Log SMS error but don't fail registration
                        \Log::error('SMS sending failed during student registration: ' . $smsError->getMessage());
                        $smsSent = false;
                    }
                }
            }

            DB::commit();

            $message = 'Student registered successfully!';
            if ($smsSent) {
                $message .= ' SMS sent to parent with student credentials.';
            }
            if ($parentSmsSent) {
                $message .= ' Parent account credentials sent.';
            }

            return response()->json([
                'success' => true,
                'admission_number' => $student->admission_number,
                'student_id' => $student->studentID,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}

