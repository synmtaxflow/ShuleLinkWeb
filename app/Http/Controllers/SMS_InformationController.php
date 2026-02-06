<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Combie;
use App\Models\Attendance;
use App\Models\School;
use App\Models\ParentModel;
use App\Services\SmsService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;

class SMS_InformationController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new SmsService();
    }

    public function sms_notification()
    {
      $user_type = Session::get('user_type');
      $schoolID = Session::get('schoolID');
        if (!$user_type || !$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get school info
        $school = School::find($schoolID);
        
        // Get classes for dropdown
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        return view('Admin.manage_sms_updates', compact('school', 'classes', 'user_type'));
    }

    /**
     * Get all parents in school
     */
    public function get_all_parents()
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            // Get parents who have at least one active student
            $parents = ParentModel::where('schoolID', $schoolID)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereHas('students', function($q) {
                    $q->where('status', 'Active');
                })
                ->with(['students' => function($q) {
                    $q->where('status', 'Active');
                }, 'students.subclass.class'])
                ->get();

            $count = $parents->count();

            return response()->json([
                'success' => true,
                'count' => $count,
                'parents' => $parents
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all parents: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get parents by class
     */
    public function get_parents_by_class(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $classID = $request->input('classID');
            if (!$classID) {
                return response()->json(['success' => false, 'message' => 'Class ID is required'], 400);
            }

            // Get all subclasses for this class
            $subclassIDs = Subclass::where('classID', $classID)->pluck('subclassID');

            // Get parents who have at least one active student in these subclasses
            $parents = ParentModel::where('schoolID', $schoolID)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereHas('students', function($q) use ($subclassIDs) {
                    $q->whereIn('subclassID', $subclassIDs)
                      ->where('status', 'Active');
                })
                ->with(['students' => function($q) use ($subclassIDs) {
                    $q->whereIn('subclassID', $subclassIDs)
                      ->where('status', 'Active');
                }])
                ->get()
                ->unique('parentID');

            $count = $parents->count();

            return response()->json([
                'success' => true,
                'count' => $count,
                'parents' => $parents->values()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting parents by class: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all teachers
     */
    public function get_all_teachers()
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $teachers = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->get();

            $count = $teachers->count();

            return response()->json([
                'success' => true,
                'count' => $count,
                'teachers' => $teachers
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all teachers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get parent by student
     */
    public function get_parent_by_student(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $studentID = $request->input('studentID');
            if (!$studentID) {
                return response()->json(['success' => false, 'message' => 'Student ID is required'], 400);
            }

            $student = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('parent')
                ->first();

            if (!$student || !$student->parent) {
                return response()->json(['success' => false, 'message' => 'Active student or parent not found'], 404);
            }

            $parent = $student->parent;
            if (!$parent->phone || $parent->phone == '') {
                return response()->json(['success' => false, 'message' => 'Parent phone number not available'], 400);
            }

            return response()->json([
                'success' => true,
                'parent' => $parent,
                'student' => $student
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting parent by student: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Search students for parent selection
     */
    public function search_students(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $search = $request->input('search', '');
            
            $query = Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with(['parent', 'subclass.class']);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('admission_number', 'like', "%{$search}%");
                });
            }

            $students = $query->limit(50)->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching students: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Search teachers for selection
     */
    public function search_teachers(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $search = $request->input('search', '');

            $query = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            $teachers = $query->limit(50)->get();

            return response()->json([
                'success' => true,
                'teachers' => $teachers
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching teachers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Send SMS to recipients
     */
    public function send_sms(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $validator = Validator::make($request->all(), [
                'message' => 'required|string|min:1',
                'recipient_type' => 'required|in:all_parents,class_parents,all_parents_teachers,all_teachers,specific_parent,specific_teacher'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
            }

            $school = School::find($schoolID);
            if (!$school) {
                return response()->json(['success' => false, 'message' => 'School not found'], 404);
            }

            $message = $request->input('message');
            $recipientType = $request->input('recipient_type');
            
            // Prepend school name to message
            $fullMessage = $school->school_name . '. ' . $message;

            $recipients = [];
            $results = [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'details' => []
            ];

            // Get recipients based on type
            switch ($recipientType) {
                case 'all_parents':
                    // Get parents who have at least one active student
                    $parents = ParentModel::where('schoolID', $schoolID)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->whereHas('students', function($q) {
                            $q->where('status', 'Active');
                        })
                        ->get();
                    foreach ($parents as $parent) {
                        $recipients[] = [
                            'type' => 'parent',
                            'phone' => $parent->phone,
                            'name' => $parent->first_name . ' ' . ($parent->last_name ?? ''),
                            'id' => $parent->parentID
                        ];
                    }
                    break;

                case 'class_parents':
                    $classID = $request->input('classID');
                    if (!$classID) {
                        return response()->json(['success' => false, 'message' => 'Class ID is required'], 400);
                    }
                    $subclassIDs = Subclass::where('classID', $classID)->pluck('subclassID');
                    // Get parents who have at least one active student in these subclasses
                    $parents = ParentModel::where('schoolID', $schoolID)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->whereHas('students', function($q) use ($subclassIDs) {
                            $q->whereIn('subclassID', $subclassIDs)
                              ->where('status', 'Active');
                        })
                        ->get()
                        ->unique('parentID');
                    foreach ($parents as $parent) {
                        $recipients[] = [
                            'type' => 'parent',
                            'phone' => $parent->phone,
                            'name' => $parent->first_name . ' ' . ($parent->last_name ?? ''),
                            'id' => $parent->parentID
                        ];
                    }
                    break;

                case 'all_parents_teachers':
                    // Get parents who have at least one active student
                    $parents = ParentModel::where('schoolID', $schoolID)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->whereHas('students', function($q) {
                            $q->where('status', 'Active');
                        })
                        ->get();
                    foreach ($parents as $parent) {
                        $recipients[] = [
                            'type' => 'parent',
                            'phone' => $parent->phone,
                            'name' => $parent->first_name . ' ' . ($parent->last_name ?? ''),
                            'id' => $parent->parentID
                        ];
                    }
                    // Get all active teachers
                    $teachers = Teacher::where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '')
                        ->get();
                    foreach ($teachers as $teacher) {
                        $recipients[] = [
                            'type' => 'teacher',
                            'phone' => $teacher->phone_number,
                            'name' => $teacher->first_name . ' ' . ($teacher->last_name ?? ''),
                            'id' => $teacher->id
                        ];
                    }
                    break;

                case 'all_teachers':
                    $teachers = Teacher::where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '')
                        ->get();
                    foreach ($teachers as $teacher) {
                        $recipients[] = [
                            'type' => 'teacher',
                            'phone' => $teacher->phone_number,
                            'name' => $teacher->first_name . ' ' . ($teacher->last_name ?? ''),
                            'id' => $teacher->id
                        ];
                    }
                    break;

                case 'specific_parent':
                    $studentID = $request->input('studentID');
                    if (!$studentID) {
                        return response()->json(['success' => false, 'message' => 'Student ID is required'], 400);
                    }
                    // Get active student
                    $student = Student::where('studentID', $studentID)
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->with('parent')
                        ->first();
                    if (!$student || !$student->parent || !$student->parent->phone) {
                        return response()->json(['success' => false, 'message' => 'Active student or parent not found or phone number not available'], 404);
                    }
                    $recipients[] = [
                        'type' => 'parent',
                        'phone' => $student->parent->phone,
                        'name' => $student->parent->first_name . ' ' . ($student->parent->last_name ?? ''),
                        'id' => $student->parent->parentID
                    ];
                    break;

                case 'specific_teacher':
                    $teacherID = $request->input('teacherID');
                    if (!$teacherID) {
                        return response()->json(['success' => false, 'message' => 'Teacher ID is required'], 400);
                    }
                    $teacher = Teacher::where('id', $teacherID)
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '')
                        ->first();
                    if (!$teacher) {
                        return response()->json(['success' => false, 'message' => 'Active teacher not found or phone number not available'], 404);
                    }
                    $recipients[] = [
                        'type' => 'teacher',
                        'phone' => $teacher->phone_number,
                        'name' => $teacher->first_name . ' ' . ($teacher->last_name ?? ''),
                        'id' => $teacher->id
                    ];
                    break;
            }

            $results['total'] = count($recipients);

            // Return response immediately, then continue sending in background
            $response = response()->json([
                'success' => true,
                'status' => 'queued',
                'message' => 'SMS sending started',
                'results' => $results
            ]);

            if (function_exists('fastcgi_finish_request')) {
                $response->send();
                fastcgi_finish_request();
            }

            $this->sendSmsBatch($recipients, $fullMessage, $results);

            return $response;

        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function sendSmsBatch(array $recipients, string $fullMessage, array &$results): void
    {
        // Send SMS in batches to avoid timeout
        $batchSize = 50; // Send 50 at a time
        $batches = array_chunk($recipients, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $recipient) {
                try {
                    $smsResult = $this->smsService->sendSms($recipient['phone'], $fullMessage);
                    
                    if ($smsResult['success']) {
                        $results['success']++;
                        $results['details'][] = [
                            'phone' => $recipient['phone'],
                            'name' => $recipient['name'],
                            'status' => 'success',
                            'message' => 'SMS sent successfully'
                        ];
                    } else {
                        $results['failed']++;
                        $results['details'][] = [
                            'phone' => $recipient['phone'],
                            'name' => $recipient['name'],
                            'status' => 'failed',
                            'message' => $smsResult['message'] ?? 'Failed to send SMS'
                        ];
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['details'][] = [
                        'phone' => $recipient['phone'],
                        'name' => $recipient['name'],
                        'status' => 'failed',
                        'message' => 'Exception: ' . $e->getMessage()
                    ];
                }
                
                // Small delay to avoid overwhelming the API
                usleep(100000); // 0.1 second delay
            }
            
            // Longer delay between batches
            if ($batchIndex < count($batches) - 1) {
                sleep(1); // 1 second delay between batches
            }
        }
    }

    /**
     * Get SMS account balance
     */
    public function get_sms_balance()
    {
        try {
            $balanceResult = $this->smsService->getBalance();
            
            return response()->json([
                'success' => $balanceResult['success'],
                'balance' => $balanceResult['balance'] ?? 0,
                'currency' => $balanceResult['currency'] ?? 'TZS',
                'message' => $balanceResult['message'] ?? 'Balance retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting SMS balance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'balance' => 0,
                'currency' => 'TZS',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
