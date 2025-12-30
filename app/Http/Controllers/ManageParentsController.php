<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use App\Models\User;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ManageParentsController extends Controller
{
    public function manage_parents()
    {
        $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $user_type = Session::get('user_type');
        return view('Admin.manage_parents', compact('user_type'));
    }

    public function get_parent_details($parentID)
    {
        $schoolID = Session::get('schoolID');
        
        $parent = ParentModel::with(['students' => function($query) {
            $query->where('status', 'Active')
                  ->with(['subclass.class']);
        }])
            ->where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Parent not found'
            ], 404);
        }

        // Get active students with their classes
        $activeStudents = $parent->students->map(function($student) {
            return [
                'studentID' => $student->studentID,
                'admission_number' => $student->admission_number,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'full_name' => $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name,
                'class_name' => $student->subclass && $student->subclass->class 
                    ? $student->subclass->class->class_name 
                    : 'N/A',
                'subclass_name' => $student->subclass 
                    ? $student->subclass->subclass_name 
                    : 'N/A',
                'full_class' => $student->subclass && $student->subclass->class 
                    ? $student->subclass->class->class_name . ' ' . ($student->subclass->subclass_name ?? '') 
                    : 'N/A',
                'gender' => $student->gender,
                'status' => $student->status,
                'photo' => $student->photo ? asset('userImages/' . $student->photo) : null,
            ];
        });

        // Get unique classes
        $classes = $activeStudents->pluck('full_class')->unique()->values();

        return response()->json([
            'success' => true,
            'parent' => [
                'parentID' => $parent->parentID,
                'first_name' => $parent->first_name,
                'middle_name' => $parent->middle_name,
                'last_name' => $parent->last_name,
                'full_name' => $parent->first_name . ' ' . ($parent->middle_name ? $parent->middle_name . ' ' : '') . $parent->last_name,
                'phone' => $parent->phone,
                'email' => $parent->email,
                'occupation' => $parent->occupation,
                'national_id' => $parent->national_id,
                'address' => $parent->address,
                'gender' => $parent->gender,
                'photo' => $parent->photo ? asset('userImages/' . $parent->photo) : null,
                'student_count' => $activeStudents->count(),
                'students' => $activeStudents,
                'classes' => $classes
            ]
        ]);
    }
    public function save_parent(Request $request)
    {
        $schoolID = Session::get('schoolID');
        
        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        // Validate phone number format: 255 + 6/7 + 8 digits = 12 total
        // Examples: 255614863345 or 255714863345
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (!preg_match('/^255[67]\d{8}$/', $phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => ['phone' => 'Phone number must be 12 digits: 255 + 6 or 7 + 8 more digits (e.g., 255614863345 or 255714863345).']
            ], 422);
        }
        $request->merge(['phone' => $phone]);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|size:12|unique:parents,phone|unique:users,name|regex:/^255[67]\d{8}$/',
            'email' => 'nullable|email|max:100|unique:parents,email',
            'gender' => 'nullable|string|max:10',
            'occupation' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ], [
            'phone.unique' => 'Phone number already exists. Please use a different phone number.',
            'phone.size' => 'Phone number must be exactly 12 digits.',
            'phone.regex' => 'Phone number must be 12 digits: 255 + 6 or 7 + 8 more digits (e.g., 255614863345 or 255714863345).',
            'email.unique' => 'Email already exists. Please use a different email.',
            'email.email' => 'Please enter a valid email address.',
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

            // Create parent
            $parent = ParentModel::create([
                'schoolID' => $schoolID,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?: null,
                'last_name' => $request->last_name,
                'gender' => $request->gender ?: null,
                'phone' => $request->phone,
                'email' => $request->email ?: null,
                'occupation' => $request->occupation ?: null,
                'national_id' => $request->national_id ?: null,
                'address' => $request->address ?: null,
                'photo' => $imageName
            ]);

            // Create user account for parent
            // Username = phone number, Password = last_name
            User::create([
                'name' => $request->phone,
                'email' => $request->email ?: null,
                'password' => Hash::make($request->last_name),
                'user_type' => 'parent'
            ]);

            // Send SMS with credentials
            $school = \App\Models\School::find($schoolID);
            $smsService = new SmsService();
            $smsResult = $smsService->sendParentCredentials(
                $request->phone,
                $school->school_name,
                $request->phone,
                $request->last_name
            );
            $smsSent = $smsResult['success'];

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Parent registered successfully' . ($smsSent ? ' and SMS sent' : ''),
                'parent' => $parent
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded image if parent creation failed
            if (isset($imageName) && file_exists(public_path('userImages/' . $imageName))) {
                unlink(public_path('userImages/' . $imageName));
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to register parent: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_parents(Request $request)
    {
        $schoolID = Session::get('schoolID');
        
        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        $subclassID = $request->input('subclassID');
        $classID = $request->input('classID'); // For coordinator view
        $isCoordinatorView = $request->input('coordinator') === 'true';
        $filterBySubclass = $request->input('filterBySubclass', false); // New parameter to control filtering

        // Always get all parents in school, but optionally filter by subclass if requested
        $query = ParentModel::where('schoolID', $schoolID);

        // If coordinator view, get parents for all subclasses in the main class
        if ($isCoordinatorView && $classID) {
            // Get all subclasses for this main class
            $subclassIDs = \App\Models\Subclass::where('classID', $classID)
                ->where('status', 'Active')
                ->pluck('subclassID')
                ->toArray();
            
            if (!empty($subclassIDs)) {
                $query->whereHas('students', function($q) use ($subclassIDs) {
                    $q->whereIn('subclassID', $subclassIDs);
                });
            }
        } elseif ($filterBySubclass && $subclassID) {
            // Only filter by subclass if explicitly requested (e.g., for PDF report)
            $query->whereHas('students', function($q) use ($subclassID) {
                $q->where('subclassID', $subclassID);
            });
        }

        $parents = $query->with('students')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $parents = $parents->map(function($parent) use ($subclassID) {
            // Count all students for this parent
            $studentCount = $parent->students ? $parent->students->count() : 0;
            
            // Count students in this subclass if subclassID is provided
            $studentCountInClass = 0;
            if ($subclassID && $parent->students) {
                $studentCountInClass = $parent->students->where('subclassID', $subclassID)->count();
            }

            return [
                'parentID' => $parent->parentID,
                'first_name' => $parent->first_name,
                'middle_name' => $parent->middle_name,
                'last_name' => $parent->last_name,
                'phone' => $parent->phone,
                'email' => $parent->email,
                'occupation' => $parent->occupation,
                'national_id' => $parent->national_id,
                'address' => $parent->address,
                'gender' => $parent->gender,
                'photo' => $parent->photo ? asset('userImages/' . $parent->photo) : null,
                'student_count' => $studentCount,
                'student_count_in_class' => $studentCountInClass
            ];
        });

        return response()->json([
            'success' => true,
            'parents' => $parents,
            'total' => $parents->count()
        ]);
    }

    public function get_parent($parentID)
    {
        $schoolID = Session::get('schoolID');
        
        $parent = ParentModel::with('students.subclass.class')
            ->where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Parent not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'parent' => [
                'parentID' => $parent->parentID,
                'first_name' => $parent->first_name,
                'middle_name' => $parent->middle_name,
                'last_name' => $parent->last_name,
                'phone' => $parent->phone,
                'email' => $parent->email,
                'occupation' => $parent->occupation,
                'national_id' => $parent->national_id,
                'address' => $parent->address,
                'gender' => $parent->gender,
                'photo' => $parent->photo ? asset('userImages/' . $parent->photo) : null,
                'students' => $parent->students->map(function($student) {
                    return [
                        'studentID' => $student->studentID,
                        'admission_number' => $student->admission_number,
                        'full_name' => $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name,
                        'subclass_name' => $student->subclass ? ($student->subclass->class->class_name . ' ' . $student->subclass->subclass_name) : 'N/A'
                    ];
                })
            ]
        ]);
    }

    public function update_parent(Request $request)
    {
        $schoolID = Session::get('schoolID');
        
        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'parentID' => 'required|exists:parents,parentID',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:parents,phone,' . $request->parentID . ',parentID|unique:users,name,' . $request->phone . ',name',
            'email' => 'nullable|email|max:100|unique:parents,email,' . $request->parentID . ',parentID',
            'gender' => 'nullable|string|max:10',
            'occupation' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ], [
            'phone.unique' => 'Phone number already exists. Please use a different phone number.',
            'email.unique' => 'Email already exists. Please use a different email.',
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

            $parent = ParentModel::where('parentID', $request->parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Handle Image Upload
            $imageName = $parent->photo; // Keep existing photo
            if ($request->hasFile('photo')) {
                $uploadPath = public_path('userImages');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Delete old image if exists
                if ($parent->photo && file_exists($uploadPath . '/' . $parent->photo)) {
                    unlink($uploadPath . '/' . $parent->photo);
                }

                $imageName = time() . '_' . $request->file('photo')->getClientOriginalName();
                $request->file('photo')->move($uploadPath, $imageName);
            }

            // Update parent
            $parent->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?: null,
                'last_name' => $request->last_name,
                'gender' => $request->gender ?: null,
                'phone' => $request->phone,
                'email' => $request->email ?: null,
                'occupation' => $request->occupation ?: null,
                'national_id' => $request->national_id ?: null,
                'address' => $request->address ?: null,
                'photo' => $imageName
            ]);

            // Update user account if phone changed
            $user = User::where('name', $parent->getOriginal('phone'))->first();
            if ($user && $request->phone != $parent->getOriginal('phone')) {
                $user->name = $request->phone;
                $user->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Parent updated successfully',
                'parent' => $parent
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update parent: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_parent($parentID)
    {
        $schoolID = Session::get('schoolID');
        
        try {
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Check if parent has students
            if ($parent->students()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete parent. They have students assigned.'
                ], 400);
            }

            // Delete photo if exists
            if ($parent->photo && file_exists(public_path('userImages/' . $parent->photo))) {
                unlink(public_path('userImages/' . $parent->photo));
            }

            // Delete user account
            $user = User::where('name', $parent->phone)->first();
            if ($user) {
                $user->delete();
            }

            $parent->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parent deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete parent: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_parents_for_pdf($subclassID)
    {
        $schoolID = Session::get('schoolID');
        
        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School ID not found'
            ], 400);
        }

        try {
            $subclass = \App\Models\Subclass::with('class')->find($subclassID);
            if (!$subclass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class not found'
                ], 404);
            }

            $school = \App\Models\School::find($schoolID);
            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'School not found'
                ], 404);
            }

            // Get parents who have students in this subclass
            $parents = ParentModel::where('schoolID', $schoolID)
                ->whereHas('students', function($q) use ($subclassID) {
                    $q->where('subclassID', $subclassID);
                })
                ->with(['students' => function($q) use ($subclassID) {
                    $q->where('subclassID', $subclassID);
                }])
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $subclassName = $subclass->class->class_name . ' ' . ($subclass->subclass_name ?? $subclass->stream_code);
            
            $parentsData = $parents->map(function($parent) {
                return [
                    'parentID' => $parent->parentID,
                    'first_name' => $parent->first_name,
                    'middle_name' => $parent->middle_name,
                    'last_name' => $parent->last_name,
                    'phone' => $parent->phone,
                    'email' => $parent->email,
                    'occupation' => $parent->occupation,
                    'national_id' => $parent->national_id,
                    'address' => $parent->address,
                    'students' => $parent->students->map(function($student) {
                        return [
                            'admission_number' => $student->admission_number,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name,
                            'last_name' => $student->last_name,
                            'full_name' => $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'school' => [
                    'school_name' => $school->school_name,
                    'school_logo' => $school->school_logo ? asset($school->school_logo) : null
                ],
                'subclassName' => $subclassName,
                'parents' => $parentsData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data: ' . $e->getMessage()
            ], 500);
        }
    }
}
