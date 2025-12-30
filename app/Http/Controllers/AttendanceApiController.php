<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\User;
use App\Models\StudentFingerprintAttendance;
use App\Models\TeacherFingerprintAttendance;
use App\Models\Teacher;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class AttendanceApiController extends Controller
{
    /**
     * List raw attendance records from external biometric system (no local filtering).
     *
     * External API: http://192.168.100.100:8000/api/v1/attendances
     * Returns: all records as provided by external system (data + pagination).
     */
    public function listExternal(Request $request)
    {
        try {
            $baseUrl = 'http://192.168.100.100:8000/api/v1/attendances';
            $page = $request->input('page', 1);

            $url = $baseUrl . '?page=' . intval($page);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('External Attendance List: cURL error - ' . $curlError);
                return response()->json([
                    'success' => false,
                    'message' => 'Connection error to external attendance API: ' . $curlError,
                ], 500);
            }

            if ($httpCode !== 200) {
                Log::error('External Attendance List: HTTP error', [
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'External attendance API returned status code: ' . $httpCode,
                ], 500);
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('External Attendance List: JSON decode error - ' . json_last_error_msg());
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from external attendance API',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $data['data'] ?? [],
                'pagination' => $data['pagination'] ?? null,
            ], 200);

        } catch (\Exception $e) {
            Log::error('External Attendance List Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while listing external attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Receive fingerprint ID and record attendance (checkin/checkout)
     * 
     * Logic:
     * - First request of the day = CHECK IN
     * - Second request of the day = CHECK OUT
     * - More than 2 requests = Ignore
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordAttendance(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'fingerprint_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fingerprintId = $request->input('fingerprint_id');
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now();

            // Step 1: Check users table first to get user_type
            $user = User::where('fingerprint_id', $fingerprintId)->first();

            if (!$user) {
                Log::warning("Attendance API: User not found for fingerprint ID: {$fingerprintId}");
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with this fingerprint ID',
                    'fingerprint_id' => $fingerprintId
                ], 404);
            }

            // Step 2: Verify user_type is 'student'
            if ($user->user_type !== 'student') {
                Log::warning("Attendance API: User type is not student", [
                    'fingerprint_id' => $fingerprintId,
                    'user_type' => $user->user_type,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'This fingerprint ID belongs to a ' . $user->user_type . ', not a student',
                    'fingerprint_id' => $fingerprintId,
                    'user_type' => $user->user_type
                ], 403);
            }

            // Step 3: Find student by fingerprint ID
            $student = Student::where('fingerprint_id', $fingerprintId)
                ->where('status', 'Active')
                ->with(['subclass.class'])
                ->first();

            if (!$student) {
                Log::warning("Attendance API: Student not found for fingerprint ID: {$fingerprintId}", [
                    'user_id' => $user->id,
                    'user_type' => $user->user_type
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found with this fingerprint ID',
                    'fingerprint_id' => $fingerprintId
                ], 404);
            }

            // Check if attendance record exists for today
            $attendance = Attendance::where('studentID', $student->studentID)
                ->where('attendance_date', $currentDate)
                ->first();

            // Determine if this is checkin or checkout
            $isCheckin = false;
            $isCheckout = false;
            $action = '';

            if (!$attendance) {
                // First time today = CHECK IN
                $isCheckin = true;
                $action = 'checkin';
                
                // Create new attendance record
                $attendance = Attendance::create([
                    'schoolID' => $student->schoolID,
                    'subclassID' => $student->subclassID,
                    'studentID' => $student->studentID,
                    'teacherID' => null,
                    'attendance_date' => $currentDate,
                    'status' => 'Present',
                    'checkin_time' => $currentTime,
                    'checkout_time' => null,
                    'remark' => 'Auto-recorded via fingerprint device'
                ]);

                Log::info("Attendance API: CHECK IN recorded", [
                    'studentID' => $student->studentID,
                    'fingerprint_id' => $fingerprintId,
                    'checkin_time' => $currentTime->format('Y-m-d H:i:s')
                ]);

            } elseif ($attendance->checkin_time && !$attendance->checkout_time) {
                // Has checkin but no checkout = CHECK OUT
                $isCheckout = true;
                $action = 'checkout';
                
                // Update attendance with checkout time
                $attendance->update([
                    'checkout_time' => $currentTime
                ]);

                Log::info("Attendance API: CHECK OUT recorded", [
                    'studentID' => $student->studentID,
                    'fingerprint_id' => $fingerprintId,
                    'checkout_time' => $currentTime->format('Y-m-d H:i:s')
                ]);

            } else {
                // Already has both checkin and checkout = Ignore
                Log::warning("Attendance API: Multiple requests ignored", [
                    'studentID' => $student->studentID,
                    'fingerprint_id' => $fingerprintId,
                    'checkin_time' => $attendance->checkin_time ? $attendance->checkin_time->format('Y-m-d H:i:s') : null,
                    'checkout_time' => $attendance->checkout_time ? $attendance->checkout_time->format('Y-m-d H:i:s') : null
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Attendance already recorded for today (both checkin and checkout)',
                    'action' => 'ignored',
                    'fingerprint_id' => $fingerprintId,
                    'student_id' => $student->studentID,
                    'student_name' => $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name,
                    'checkin_time' => $attendance->checkin_time ? $attendance->checkin_time->format('Y-m-d H:i:s') : null,
                    'checkout_time' => $attendance->checkout_time ? $attendance->checkout_time->format('Y-m-d H:i:s') : null
                ], 200);
            }

            // Return success response
            return response()->json([
                'success' => true,
                'message' => ucfirst($action) . ' recorded successfully',
                'action' => $action,
                'fingerprint_id' => $fingerprintId,
                'student' => [
                    'student_id' => $student->studentID,
                    'name' => $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->subclass->class->class_name ?? 'N/A',
                    'subclass' => $student->subclass->subclass_name ?? 'N/A'
                ],
                'attendance' => [
                    'attendance_id' => $attendance->attendanceID,
                    'date' => $attendance->attendance_date->format('Y-m-d'),
                    'checkin_time' => $attendance->checkin_time ? $attendance->checkin_time->format('Y-m-d H:i:s') : null,
                    'checkout_time' => $attendance->checkout_time ? $attendance->checkout_time->format('Y-m-d H:i:s') : null,
                    'status' => $attendance->status
                ],
                'timestamp' => $currentTime->format('Y-m-d H:i:s')
            ], 200);

        } catch (\Exception $e) {
            Log::error('Attendance API Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'fingerprint_id' => $request->input('fingerprint_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while recording attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync attendance records from external biometric system.
     *
     * External API: http://192.168.100.100:8000/api/v1/attendances
     * - Uses user.enroll_id as fingerprintID
     * - For each record:
     *   - Find user by fingerprint_id and ensure user_type = 'student'
     *   - Find matching student by fingerprint_id
     *   - By (studentID, attendance_date):
     *       * If no record: insert with check_in_time as checkin_time, check_out_time as checkout_time
     *       * If record exists: update checkin_time / checkout_time (do NOT insert again)
     */
    public function syncFromExternal(Request $request)
    {
        try {
            $baseUrl = 'http://192.168.100.100:8000/api/v1/attendances';

            $page = 1;
            $totalProcessed = 0;
            $totalInserted = 0;
            $totalUpdated = 0;
            $totalSkipped = 0;

            do {
                $url = $baseUrl . '?page=' . $page;

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($curlError) {
                    Log::error('External Attendance Sync: cURL error - ' . $curlError);
                    return response()->json([
                        'success' => false,
                        'message' => 'Connection error to external attendance API: ' . $curlError,
                    ], 500);
                }

                if ($httpCode !== 200) {
                    Log::error('External Attendance Sync: HTTP error', [
                        'http_code' => $httpCode,
                        'response' => $response,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'External attendance API returned status code: ' . $httpCode,
                    ], 500);
                }

                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('External Attendance Sync: JSON decode error - ' . json_last_error_msg());
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid JSON response from external attendance API',
                    ], 500);
                }

                $records = $data['data'] ?? [];
                $pagination = $data['pagination'] ?? null;

                foreach ($records as $record) {
                    $totalProcessed++;

                    // Extract fingerprintID from external record (user.enroll_id)
                    $userData = $record['user'] ?? null;
                    if (!$userData || !isset($userData['enroll_id'])) {
                        $totalSkipped++;
                        continue;
                    }

                    $fingerprintId = (string)$userData['enroll_id'];
                    $attendanceDate = $record['attendance_date'] ?? null;
                    $checkInTime = $record['check_in_time'] ?? null;
                    $checkOutTime = $record['check_out_time'] ?? null;

                    if (!$attendanceDate) {
                        $totalSkipped++;
                        continue;
                    }

                    // Find user by fingerprint_id
                    $user = User::where('fingerprint_id', $fingerprintId)->first();
                    if (!$user || $user->user_type !== 'student') {
                        $totalSkipped++;
                        continue;
                    }

                    // Find student by fingerprint_id
                    $student = Student::where('fingerprint_id', $fingerprintId)
                        ->where('status', 'Active')
                        ->first();

                    if (!$student) {
                        $totalSkipped++;
                        continue;
                    }

                    // Find or create attendance for this student and date
                    $attendance = Attendance::where('studentID', $student->studentID)
                        ->where('attendance_date', $attendanceDate)
                        ->first();

                    // Map status from external system (assume '1' = Present)
                    $statusExternal = $record['status'] ?? null;
                    $status = $statusExternal === '1' ? 'Present' : 'Present';

                    if (!$attendance) {
                        // Insert new attendance (first time we see this date+student)
                        Attendance::create([
                            'schoolID' => $student->schoolID,
                            'subclassID' => $student->subclassID,
                            'studentID' => $student->studentID,
                            'teacherID' => null,
                            'attendance_date' => $attendanceDate,
                            'status' => $status,
                            'checkin_time' => $checkInTime ? Carbon::parse($checkInTime) : null,
                            'checkout_time' => $checkOutTime ? Carbon::parse($checkOutTime) : null,
                            'remark' => 'Imported from external biometric system',
                        ]);

                        $totalInserted++;
                        continue;
                    }

                    // Update existing attendance (do NOT create a new one)
                    $updateData = [];
                    if ($checkInTime) {
                        $updateData['checkin_time'] = Carbon::parse($checkInTime);
                    }
                    if ($checkOutTime) {
                        $updateData['checkout_time'] = Carbon::parse($checkOutTime);
                    }

                    if (!empty($updateData)) {
                        $updateData['status'] = $status;
                        $attendance->update($updateData);
                        $totalUpdated++;
                    } else {
                        $totalSkipped++;
                    }
                }

                // Pagination handling
                if ($pagination && isset($pagination['current_page'], $pagination['last_page'])) {
                    $currentPage = (int)$pagination['current_page'];
                    $lastPage = (int)$pagination['last_page'];
                    $page = $currentPage + 1;

                    if ($currentPage >= $lastPage) {
                        break;
                    }
                } else {
                    // No pagination info, break to avoid infinite loop
                    break;
                }

            } while (true);

            return response()->json([
                'success' => true,
                'message' => 'External attendance sync completed',
                'stats' => [
                    'processed' => $totalProcessed,
                    'inserted' => $totalInserted,
                    'updated' => $totalUpdated,
                    'skipped' => $totalSkipped,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('External Attendance Sync Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while syncing attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List attendance records from external biometric system,
     * filtered to only users that exist in our system as students
     * (users.user_type = 'student' AND users.fingerprint_id = user.enroll_id).
     *
     * External API: http://192.168.100.100:8000/api/v1/attendances
     */
    public function listExternalStudents(Request $request)
    {
        try {
            $baseUrl = 'http://192.168.100.100:8000/api/v1/attendances';

            $page = (int)($request->input('page', 1));
            if ($page < 1) {
                $page = 1;
            }

            $url = $baseUrl . '?page=' . $page;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('External Attendance listExternalStudents: cURL error - ' . $curlError);
                return response()->json([
                    'success' => false,
                    'message' => 'Connection error to external attendance API: ' . $curlError,
                ], 500);
            }

            if ($httpCode !== 200) {
                Log::error('External Attendance listExternalStudents: HTTP error', [
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'External attendance API returned status code: ' . $httpCode,
                ], 500);
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('External Attendance listExternalStudents: JSON decode error - ' . json_last_error_msg());
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from external attendance API',
                ], 500);
            }

            $records = $data['data'] ?? [];
            $pagination = $data['pagination'] ?? null;

            // Collect all enroll_ids from external records
            $enrollIds = collect($records)
                ->map(function ($rec) {
                    return isset($rec['user']['enroll_id']) ? (string)$rec['user']['enroll_id'] : null;
                })
                ->filter()
                ->unique()
                ->values();

            if ($enrollIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance records with enroll_id found',
                    'data' => [],
                    'pagination' => $pagination,
                    'stats' => [
                        'processed' => count($records),
                        'returned' => 0,
                    ],
                ], 200);
            }

            // Get students that match these fingerprint IDs (using studentID = fingerprintID)
            $students = Student::whereIn('studentID', $enrollIds)
                ->where('status', 'Active')
                ->with(['subclass.class', 'parent'])
                ->get()
                ->keyBy('studentID');

            $filtered = [];
            foreach ($records as $rec) {
                $userData = $rec['user'] ?? null;
                if (!$userData || !isset($userData['enroll_id'])) {
                    continue;
                }
                $enrollId = (string)$userData['enroll_id'];

                // Use enroll_id (fingerprintID) to find student (studentID = fingerprintID)
                if (!$enrollId || !isset($students[$enrollId])) {
                    // Skip students that don't exist in our system
                    continue;
                }

                $student = $students[$enrollId];
                
                // Sync to local table (student_fingerprint_attendance)
                // One record per student per day - update if exists, insert if not
                $localRecord = StudentFingerprintAttendance::where('studentID', $student->studentID)
                    ->where('attendance_date', $rec['attendance_date'])
                    ->first();

                $syncData = [
                    'studentID' => $student->studentID,
                    'user_id' => $userData['id'] ?? null,
                    'user_name' => $userData['name'] ?? null,
                    'enroll_id' => $enrollId,
                    'attendance_date' => $rec['attendance_date'],
                    'check_in_time' => $rec['check_in_time'] ? Carbon::parse($rec['check_in_time']) : null,
                    'check_out_time' => $rec['check_out_time'] ? Carbon::parse($rec['check_out_time']) : null,
                    'status' => $rec['status'] ?? null,
                    'verify_mode' => $rec['verify_mode'] ?? null,
                    'device_ip' => $rec['device_ip'] ?? null,
                    'external_id' => $rec['id'] ?? null,
                ];

                if ($localRecord) {
                    // Update existing record (especially if check_out_time was null and now we have it)
                    $updateData = [];
                    if ($syncData['check_in_time']) {
                        $updateData['check_in_time'] = $syncData['check_in_time'];
                    }
                    if ($syncData['check_out_time']) {
                        $updateData['check_out_time'] = $syncData['check_out_time'];
                    }
                    if ($syncData['status']) {
                        $updateData['status'] = $syncData['status'];
                    }
                    if ($syncData['verify_mode']) {
                        $updateData['verify_mode'] = $syncData['verify_mode'];
                    }
                    if ($syncData['device_ip']) {
                        $updateData['device_ip'] = $syncData['device_ip'];
                    }
                    if ($syncData['external_id']) {
                        $updateData['external_id'] = $syncData['external_id'];
                    }
                    if ($syncData['user_id']) {
                        $updateData['user_id'] = $syncData['user_id'];
                    }
                    if ($syncData['user_name']) {
                        $updateData['user_name'] = $syncData['user_name'];
                    }
                    if ($syncData['enroll_id']) {
                        $updateData['enroll_id'] = $syncData['enroll_id'];
                    }
                    
                    if (!empty($updateData)) {
                        $localRecord->update($updateData);
                    }
                } else {
                    // Insert new record (one per student per day)
                    StudentFingerprintAttendance::create($syncData);
                }
                
                // Add student information to the record for response
                $rec['student_info'] = [
                    'studentID' => $student->studentID,
                    'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'class_name' => $student->subclass && $student->subclass->class ? $student->subclass->class->class_name : 'N/A',
                    'subclass_name' => $student->subclass ? $student->subclass->subclass_name : 'N/A',
                    'admission_number' => $student->admission_number,
                ];

                $filtered[] = $rec;
            }

            return response()->json([
                'success' => true,
                'message' => 'Filtered external attendance records for students only',
                'data' => array_values($filtered),
                'pagination' => $pagination,
                'stats' => [
                    'processed' => count($records),
                    'returned' => count($filtered),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('External Attendance listExternalStudents Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while syncing external attendance for students: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync teacher attendance records from external biometric system
     * ID kutoka API ni ID ya teacher kwenye teachers table
     */
    public function listExternalTeachers(Request $request)
    {
        try {
            $baseUrl = 'http://192.168.100.100:8000/api/v1/attendances';

            $page = (int)($request->input('page', 1));
            $perPage = 50;

            // Build URL with pagination
            $url = $baseUrl . '?page=' . $page . '&per_page=' . $perPage;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('External Attendance listExternalTeachers: cURL error - ' . $curlError);
                return response()->json([
                    'success' => false,
                    'message' => 'Connection error to external attendance API: ' . $curlError,
                ], 500);
            }

            if ($httpCode !== 200) {
                Log::error('External Attendance listExternalTeachers: HTTP error', [
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'External attendance API returned status code: ' . $httpCode,
                ], 500);
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('External Attendance listExternalTeachers: JSON decode error - ' . json_last_error_msg());
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from external attendance API',
                ], 500);
            }

            $records = $data['data'] ?? [];
            $pagination = $data['pagination'] ?? null;

            // Collect all IDs from external records (ID kutoka API ni ID ya teacher)
            $teacherIds = collect($records)
                ->map(function ($rec) {
                    // ID kutoka API ni ID ya teacher
                    return isset($rec['user']['id']) ? (string)$rec['user']['id'] : null;
                })
                ->filter()
                ->unique()
                ->values();

            if ($teacherIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance records with teacher ID found',
                    'data' => [],
                    'pagination' => $pagination,
                    'stats' => [
                        'processed' => count($records),
                        'returned' => 0,
                    ],
                ], 200);
            }

            // Get teachers that match these IDs (check both id and fingerprint_id)
            $teachers = \App\Models\Teacher::where(function($query) use ($teacherIds) {
                    $query->whereIn('id', $teacherIds)
                          ->orWhereIn('fingerprint_id', $teacherIds);
                })
                ->where('status', 'Active')
                ->get()
                ->keyBy(function($teacher) {
                    // Use both id and fingerprint_id as keys
                    return (string)$teacher->id;
                });

            // Also create a map by fingerprint_id
            $teachersByFingerprint = $teachers->keyBy(function($teacher) {
                return $teacher->fingerprint_id ? (string)$teacher->fingerprint_id : null;
            })->filter();

            $filtered = [];
            foreach ($records as $rec) {
                $userData = $rec['user'] ?? null;
                if (!$userData || !isset($userData['id'])) {
                    continue;
                }
                
                $apiId = (string)$userData['id']; // ID kutoka API ni ID ya teacher
                $enrollId = isset($userData['enroll_id']) ? (string)$userData['enroll_id'] : $apiId;

                // Find teacher by ID (kutoka API) - check both id and fingerprint_id
                $teacher = null;
                if (isset($teachers[$apiId])) {
                    $teacher = $teachers[$apiId];
                } elseif (isset($teachersByFingerprint[$apiId])) {
                    $teacher = $teachersByFingerprint[$apiId];
                } elseif (isset($teachersByFingerprint[$enrollId])) {
                    $teacher = $teachersByFingerprint[$enrollId];
                }

                if (!$teacher) {
                    // Skip teachers that don't exist in our system
                    continue;
                }

                // Use teacher's fingerprint_id or id as studentID for the attendance table
                $attendanceId = $teacher->fingerprint_id ? (string)$teacher->fingerprint_id : (string)$teacher->id;
                
                // Sync to local table (student_fingerprint_attendance)
                // One record per teacher per day - update if exists, insert if not
                $localRecord = StudentFingerprintAttendance::where('enroll_id', $attendanceId)
                    ->where('attendance_date', $rec['attendance_date'])
                    ->first();

                $syncData = [
                    'studentID' => (int)$attendanceId, // Use teacher fingerprint_id or id
                    'user_id' => $userData['id'] ?? null,
                    'user_name' => $userData['name'] ?? null,
                    'enroll_id' => $attendanceId,
                    'attendance_date' => $rec['attendance_date'],
                    'check_in_time' => $rec['check_in_time'] ? Carbon::parse($rec['check_in_time']) : null,
                    'check_out_time' => $rec['check_out_time'] ? Carbon::parse($rec['check_out_time']) : null,
                    'status' => $rec['status'] ?? null,
                    'verify_mode' => $rec['verify_mode'] ?? null,
                    'device_ip' => $rec['device_ip'] ?? null,
                    'external_id' => $rec['id'] ?? null,
                ];

                if ($localRecord) {
                    // Update existing record
                    $updateData = [];
                    if ($syncData['check_in_time']) {
                        $updateData['check_in_time'] = $syncData['check_in_time'];
                    }
                    if ($syncData['check_out_time']) {
                        $updateData['check_out_time'] = $syncData['check_out_time'];
                    }
                    if ($syncData['status']) {
                        $updateData['status'] = $syncData['status'];
                    }
                    if ($syncData['verify_mode']) {
                        $updateData['verify_mode'] = $syncData['verify_mode'];
                    }
                    if ($syncData['device_ip']) {
                        $updateData['device_ip'] = $syncData['device_ip'];
                    }
                    if ($syncData['external_id']) {
                        $updateData['external_id'] = $syncData['external_id'];
                    }
                    if ($syncData['user_id']) {
                        $updateData['user_id'] = $syncData['user_id'];
                    }
                    if ($syncData['user_name']) {
                        $updateData['user_name'] = $syncData['user_name'];
                    }
                    if ($syncData['enroll_id']) {
                        $updateData['enroll_id'] = $syncData['enroll_id'];
                    }
                    
                    if (!empty($updateData)) {
                        $localRecord->update($updateData);
                    }
                } else {
                    // Insert new record (one per teacher per day)
                    StudentFingerprintAttendance::create($syncData);
                }
                
                // Add teacher information to the record for response
                $rec['teacher_info'] = [
                    'teacherID' => $teacher->id,
                    'full_name' => trim($teacher->first_name . ' ' . ($teacher->middle_name ? $teacher->middle_name . ' ' : '') . $teacher->last_name),
                    'first_name' => $teacher->first_name,
                    'middle_name' => $teacher->middle_name,
                    'last_name' => $teacher->last_name,
                    'employee_number' => $teacher->employee_number,
                    'position' => $teacher->position,
                ];

                $filtered[] = $rec;
            }

            return response()->json([
                'success' => true,
                'message' => 'Filtered external attendance records for teachers only',
                'data' => array_values($filtered),
                'pagination' => $pagination,
                'stats' => [
                    'processed' => count($records),
                    'returned' => count($filtered),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('External Attendance listExternalTeachers Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while syncing external attendance for teachers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get fingerprint attendance records from local database (student_fingerprint_attendance table)
     * This method reads from local table instead of external API
     */
    public function getLocalFingerprintAttendance(Request $request)
    {
        try {
            $subclassID = $request->input('subclassID');
            $dateFilter = $request->input('date');
            $page = (int)($request->input('page', 1));
            $perPage = 50;

            // Build query
            $query = StudentFingerprintAttendance::with(['student.subclass.class'])
                ->whereHas('student', function($q) use ($subclassID) {
                    if ($subclassID) {
                        $q->where('subclassID', $subclassID);
                    }
                    $q->where('status', 'Active');
                });

            // Filter by date if provided
            if ($dateFilter) {
                $query->where('attendance_date', $dateFilter);
            }

            // Get total count
            $total = $query->count();

            // Paginate
            $records = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            // Format records for response
            $formattedRecords = $records->map(function($record) {
                $student = $record->student;
                return [
                    'id' => $record->external_id,
                    'user' => [
                        'id' => $record->user_id,
                        'name' => $record->user_name,
                        'enroll_id' => $record->enroll_id,
                    ],
                    'attendance_date' => $record->attendance_date->format('Y-m-d'),
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => $record->status,
                    'verify_mode' => $record->verify_mode,
                    'device_ip' => $record->device_ip,
                    'student_info' => $student ? [
                        'studentID' => $student->studentID,
                        'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'class_name' => $student->subclass && $student->subclass->class ? $student->subclass->class->class_name : 'N/A',
                        'subclass_name' => $student->subclass ? $student->subclass->subclass_name : 'N/A',
                        'admission_number' => $student->admission_number,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Fingerprint attendance records from local database',
                'data' => $formattedRecords->toArray(),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get Local Fingerprint Attendance Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving fingerprint attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get teacher fingerprint attendance records from local database (teacher_fingerprint_attendance table)
     * Filters by teacher IDs
     */
    public function getTeacherFingerprintAttendance(Request $request)
    {
        try {
            $dateFilter = $request->input('date');
            $page = (int)($request->input('page', 1));
            $perPage = 50;

            // Build query - get from teacher_fingerprint_attendance table
            $query = TeacherFingerprintAttendance::with('teacher');

            // Filter by date if provided
            if ($dateFilter) {
                $query->where('attendance_date', $dateFilter);
            }

            // Get total count
            $total = $query->count();

            // Paginate
            $records = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            // Format records for response with teacher info
            $formattedRecords = $records->map(function($record) {
                $teacher = $record->teacher;
                
                return [
                    'id' => $record->external_id,
                    'user' => [
                        'id' => $record->user_id,
                        'name' => $record->user_name,
                        'enroll_id' => $record->enroll_id,
                    ],
                    'attendance_date' => $record->attendance_date->format('Y-m-d'),
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => $record->status,
                    'verify_mode' => $record->verify_mode,
                    'device_ip' => $record->device_ip,
                    'teacher_info' => $teacher ? [
                        'teacherID' => $teacher->id,
                        'full_name' => trim($teacher->first_name . ' ' . ($teacher->middle_name ? $teacher->middle_name . ' ' : '') . $teacher->last_name),
                        'first_name' => $teacher->first_name,
                        'middle_name' => $teacher->middle_name,
                        'last_name' => $teacher->last_name,
                        'employee_number' => $teacher->employee_number,
                        'position' => $teacher->position,
                        'fingerprint_id' => $teacher->fingerprint_id,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Teacher fingerprint attendance records from local database',
                'data' => $formattedRecords->toArray(),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get Teacher Fingerprint Attendance Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving teacher fingerprint attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get teacher fingerprint attendance from local database (teacher_fingerprint_attendance table)
     */
    public function getTeacherFingerprintAttendanceLocal(Request $request)
    {
        try {
            $dateFilter = $request->input('date');
            $page = (int)($request->input('page', 1));
            $perPage = 50;

            // Build query
            $query = TeacherFingerprintAttendance::with('teacher');

            // Filter by date if provided
            if ($dateFilter) {
                $query->where('attendance_date', $dateFilter);
            }

            // Get total count
            $total = $query->count();

            // Paginate
            $records = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            // Format records for response with teacher info
            $formattedRecords = $records->map(function($record) {
                $teacher = $record->teacher;
                
                return [
                    'id' => $record->external_id,
                    'user' => [
                        'id' => $record->user_id,
                        'name' => $record->user_name,
                        'enroll_id' => $record->enroll_id,
                    ],
                    'attendance_date' => $record->attendance_date->format('Y-m-d'),
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => $record->status,
                    'verify_mode' => $record->verify_mode,
                    'device_ip' => $record->device_ip,
                    'teacher_info' => $teacher ? [
                        'teacherID' => $teacher->id,
                        'full_name' => trim($teacher->first_name . ' ' . ($teacher->middle_name ? $teacher->middle_name . ' ' : '') . $teacher->last_name),
                        'first_name' => $teacher->first_name,
                        'middle_name' => $teacher->middle_name,
                        'last_name' => $teacher->last_name,
                        'employee_number' => $teacher->employee_number,
                        'position' => $teacher->position,
                        'fingerprint_id' => $teacher->fingerprint_id,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Teacher fingerprint attendance records from local database',
                'data' => $formattedRecords->toArray(),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get Teacher Fingerprint Attendance Local Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving teacher fingerprint attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all attendance records from external API filtered to teachers only
     * Filters by enroll_id (fingerprintID) that exists in teachers table (where id = enroll_id)
     * Saves/updates records to local database (teacher_fingerprint_attendance)
     */
    public function getAllAttendanceTeachers(Request $request)
    {
        try {
            $baseUrl = 'http://192.168.100.100:8000/api/v1/attendances';
            $page = (int)($request->input('page', 1));
            $perPage = 50;
            $fetchAll = $request->input('fetch_all', false); // Option to fetch all pages

            // Get all teacher IDs first for filtering
            $teachers = \App\Models\Teacher::where('status', 'Active')->get();
            
            // Create maps for quick lookup - use both string and integer keys
            $teacherMap = [];
            
            foreach ($teachers as $teacher) {
                // Map by id (as both string and integer)
                $idStr = (string)$teacher->id;
                $idInt = (int)$teacher->id;
                $teacherMap[$idStr] = $teacher;
                $teacherMap[$idInt] = $teacher;
                
                // Map by fingerprint_id if exists (as both string and integer)
                if ($teacher->fingerprint_id) {
                    $fpIdStr = (string)$teacher->fingerprint_id;
                    $fpIdInt = (int)$teacher->fingerprint_id;
                    $teacherMap[$fpIdStr] = $teacher;
                    $teacherMap[$fpIdInt] = $teacher;
                }
            }

            // Build URL with pagination
            $url = $baseUrl . '?page=' . $page . '&per_page=' . $perPage;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('External Attendance getAllAttendanceTeachers: cURL error - ' . $curlError);
                return response()->json([
                    'success' => false,
                    'message' => 'Connection error to external attendance API: ' . $curlError,
                ], 500);
            }

            if ($httpCode !== 200) {
                Log::error('External Attendance getAllAttendanceTeachers: HTTP error', [
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'External attendance API returned status code: ' . $httpCode,
                ], 500);
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('External Attendance getAllAttendanceTeachers: JSON decode error - ' . json_last_error_msg());
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from external attendance API',
                ], 500);
            }

            $records = $data['data'] ?? [];
            $pagination = $data['pagination'] ?? null;

            // Log for debugging - show teacher IDs in map
            $teacherIdsInMap = array_filter(array_keys($teacherMap), function($key) {
                return is_string($key) && is_numeric($key);
            });
            Log::info('Teacher IDs in map (string keys): ' . json_encode(array_values($teacherIdsInMap)));

            // Filter records where enroll_id exists in teachers table (as id or fingerprint_id)
            $filtered = [];
            foreach ($records as $rec) {
                $userData = $rec['user'] ?? null;
                if (!$userData || !isset($userData['enroll_id'])) {
                    continue;
                }
                
                $enrollId = $userData['enroll_id'];
                $enrollIdStr = (string)$enrollId;
                $enrollIdInt = (int)$enrollId;
                
                // Log for debugging
                Log::info('Checking enroll_id: ' . $enrollId . ' (str: ' . $enrollIdStr . ', int: ' . $enrollIdInt . ')');
                
                // Check if enroll_id exists in teachers table (as id or fingerprint_id)
                // Try both string and integer comparison
                $teacher = null;
                if (isset($teacherMap[$enrollIdStr])) {
                    $teacher = $teacherMap[$enrollIdStr];
                    Log::info('Found teacher by string key: ' . $enrollIdStr);
                } elseif (isset($teacherMap[$enrollIdInt])) {
                    $teacher = $teacherMap[$enrollIdInt];
                    Log::info('Found teacher by integer key: ' . $enrollIdInt);
                } else {
                    // Skip if not a teacher
                    continue;
                }

                // Save or update record in local database
                $attendanceDate = $rec['attendance_date'] ?? null;
                if (!$attendanceDate) {
                    continue;
                }

                // Find existing record for this teacher and date
                $localRecord = TeacherFingerprintAttendance::where('teacherID', $teacher->id)
                    ->where('attendance_date', $attendanceDate)
                    ->first();

                $checkInTime = isset($rec['check_in_time']) && $rec['check_in_time'] ? Carbon::parse($rec['check_in_time']) : null;
                $checkOutTime = isset($rec['check_out_time']) && $rec['check_out_time'] ? Carbon::parse($rec['check_out_time']) : null;

                $attendanceData = [
                    'teacherID' => $teacher->id,
                    'user_id' => $userData['id'] ?? null,
                    'user_name' => $userData['name'] ?? null,
                    'enroll_id' => $enrollId,
                    'attendance_date' => $attendanceDate,
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                    'status' => $rec['status'] ?? null,
                    'verify_mode' => $rec['verify_mode'] ?? null,
                    'device_ip' => $rec['device_ip'] ?? null,
                    'external_id' => $rec['id'] ?? null,
                ];

                if ($localRecord) {
                    // Update existing record
                    $updateData = [];
                    
                    // If check_in_time exists in API but not in local, update it
                    if ($checkInTime && !$localRecord->check_in_time) {
                        $updateData['check_in_time'] = $checkInTime;
                    }
                    // If check_out_time exists in API, always update it (even if local has it)
                    // This handles the case where teacher checked in earlier and now checked out
                    if ($checkOutTime) {
                        $updateData['check_out_time'] = $checkOutTime;
                    }
                    // Update other fields if they exist
                    if (isset($attendanceData['status'])) {
                        $updateData['status'] = $attendanceData['status'];
                    }
                    if (isset($attendanceData['verify_mode'])) {
                        $updateData['verify_mode'] = $attendanceData['verify_mode'];
                    }
                    if (isset($attendanceData['device_ip'])) {
                        $updateData['device_ip'] = $attendanceData['device_ip'];
                    }
                    if (isset($attendanceData['external_id'])) {
                        $updateData['external_id'] = $attendanceData['external_id'];
                    }
                    if (isset($attendanceData['user_id'])) {
                        $updateData['user_id'] = $attendanceData['user_id'];
                    }
                    if (isset($attendanceData['user_name'])) {
                        $updateData['user_name'] = $attendanceData['user_name'];
                    }
                    if (isset($attendanceData['enroll_id'])) {
                        $updateData['enroll_id'] = $attendanceData['enroll_id'];
                    }
                    
                    if (!empty($updateData)) {
                        $localRecord->update($updateData);
                    }
                } else {
                    // Create new record (avoid duplicate for same teacher and date)
                    // Use firstOrCreate to avoid duplicate errors
                    TeacherFingerprintAttendance::firstOrCreate(
                        [
                            'teacherID' => $teacher->id,
                            'attendance_date' => $attendanceDate
                        ],
                        $attendanceData
                    );
                }

                // Add teacher info to record
                $rec['teacher_info'] = [
                    'teacherID' => $teacher->id,
                    'full_name' => trim($teacher->first_name . ' ' . ($teacher->middle_name ? $teacher->middle_name . ' ' : '') . $teacher->last_name),
                    'first_name' => $teacher->first_name,
                    'middle_name' => $teacher->middle_name,
                    'last_name' => $teacher->last_name,
                    'employee_number' => $teacher->employee_number,
                    'position' => $teacher->position,
                    'fingerprint_id' => $teacher->fingerprint_id,
                ];

                $filtered[] = $rec;
            }

            return response()->json([
                'success' => true,
                'message' => 'All attendance records filtered to teachers only',
                'data' => array_values($filtered),
                'pagination' => $pagination,
                'stats' => [
                    'processed' => count($records),
                    'returned' => count($filtered),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('External Attendance getAllAttendanceTeachers Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving all teacher attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export teacher attendance to Excel
     */
    public function exportTeacherAttendanceExcel(Request $request)
    {
        try {
            $searchType = $request->input('search_type', 'month');
            $searchDate = $request->input('search_date', date('Y-m-d'));
            $searchMonth = $request->input('search_month');
            $searchYear = $request->input('search_year');
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

            // Parse date and determine date range
            $startDate = null;
            $endDate = null;
            $reportTitle = '';

            if ($searchType === 'month') {
                if ($searchMonth) {
                    $dateObj = Carbon::createFromFormat('Y-m', $searchMonth);
                } else {
                    $dateObj = Carbon::parse($searchDate);
                }
                $startDate = $dateObj->copy()->startOfMonth();
                $endDate = $dateObj->copy()->endOfMonth();
                // If month hasn't ended, use current date
                if ($endDate->isFuture()) {
                    $endDate = Carbon::now();
                }
                $reportTitle = $school->school_name . ' - Teacher Attendance in ' . $dateObj->format('F Y');
            } elseif ($searchType === 'year') {
                if ($searchYear) {
                    $year = (int)$searchYear;
                } else {
                    $dateObj = Carbon::parse($searchDate);
                    $year = $dateObj->year;
                }
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
                // If year hasn't ended, use current date
                if ($endDate->isFuture()) {
                    $endDate = Carbon::now();
                }
                $reportTitle = $school->school_name . ' - Teacher Attendance in ' . $year;
            } else {
                $dateObj = Carbon::parse($searchDate);
                $startDate = $dateObj->copy();
                $endDate = $dateObj->copy();
                $reportTitle = $school->school_name . ' - Teacher Attendance in ' . $dateObj->format('F d, Y');
            }

            // Get all teachers
            $teachers = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            // Get attendance records for the date range
            $attendanceRecords = TeacherFingerprintAttendance::whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->with('teacher')
                ->get()
                ->groupBy('teacherID');

            // Calculate working days (excluding weekends)
            $workingDays = $this->calculateWorkingDays($startDate, $endDate);

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Teacher Attendance');

            // Get all working dates in the range
            $allWorkingDates = [];
            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                if ($current->dayOfWeek !== Carbon::SATURDAY && $current->dayOfWeek !== Carbon::SUNDAY) {
                    $allWorkingDates[] = $current->copy();
                }
                $current->addDay();
            }

            // Calculate last column (A=1, B=2, then dates, then present, absent, working, rate)
            $numDateCols = count($allWorkingDates);
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $numDateCols + 3);

            // Header row 1: School name
            $sheet->setCellValue('A1', $school->school_name ?? 'School Name');
            $sheet->mergeCells('A1:' . $lastCol . '1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Header row 2: Report title
            $sheet->setCellValue('A2', $reportTitle);
            $sheet->mergeCells('A2:' . $lastCol . '2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Table headers
            $col = 'A';
            $sheet->setCellValue($col++ . '4', 'Teacher Name');
            $sheet->setCellValue($col++ . '4', 'Position');
            
            // Add date columns
            $dateCols = [];
            foreach ($allWorkingDates as $date) {
                $dateCol = $col++;
                $dateCols[$date->format('Y-m-d')] = $dateCol;
                $sheet->setCellValue($dateCol . '4', $date->format('d/m'));
                $sheet->getColumnDimension($dateCol)->setWidth(8);
            }
            
            $sheet->setCellValue($col++ . '4', 'Days Present');
            $sheet->setCellValue($col++ . '4', 'Days Absent');
            $sheet->setCellValue($col++ . '4', 'Total Working Days');
            $sheet->setCellValue($col . '4', 'Attendance Rate (%)');

            $lastCol = $col;
            $headerRange = 'A4:' . $lastCol . '4';

            // Style headers
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '940000']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ];
            $sheet->getStyle($headerRange)->applyFromArray($headerStyle);
            $sheet->getRowDimension('4')->setRowHeight(30);

            $row = 5;
            foreach ($teachers as $teacher) {
                $teacherRecords = $attendanceRecords->get($teacher->id, collect());
                
                // Get present dates
                $presentDates = $teacherRecords->filter(function($record) {
                    return $record->check_in_time !== null;
                })->pluck('attendance_date')->map(function($date) {
                    return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date;
                })->toArray();

                $daysPresent = count($presentDates);
                $daysAbsent = max(0, $workingDays - $daysPresent);
                $attendanceRate = $workingDays > 0 ? round(($daysPresent / $workingDays) * 100, 2) : 0;

                $fullName = trim($teacher->first_name . ' ' . ($teacher->middle_name ? $teacher->middle_name . ' ' : '') . $teacher->last_name);

                $col = 'A';
                $sheet->setCellValue($col++ . $row, $fullName);
                $sheet->setCellValue($col++ . $row, $teacher->position ?? 'N/A');
                
                // Mark dates
                foreach ($allWorkingDates as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $dateCol = $dateCols[$dateStr];
                    
                    if (in_array($dateStr, $presentDates)) {
                        $sheet->setCellValue($dateCol . $row, 'P');
                        $sheet->getStyle($dateCol . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('28a745');
                        $sheet->getStyle($dateCol . $row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                    } else {
                        $sheet->setCellValue($dateCol . $row, 'A');
                        $sheet->getStyle($dateCol . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('dc3545');
                        $sheet->getStyle($dateCol . $row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                    }
                    $sheet->getStyle($dateCol . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                
                $sheet->setCellValue($col++ . $row, $daysPresent);
                $sheet->setCellValue($col++ . $row, $daysAbsent);
                $sheet->setCellValue($col++ . $row, $workingDays);
                $sheet->setCellValue($col . $row, $attendanceRate);

                // Add borders
                $rowRange = 'A' . $row . ':' . $lastCol . $row;
                $sheet->getStyle($rowRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $row++;
            }

            // Auto-size name and position columns
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension($lastCol)->setAutoSize(true);

            // Create writer and download
            $writer = new Xlsx($spreadsheet);
            $filename = 'Teacher_Attendance_' . str_replace(' ', '_', $reportTitle) . '_' . date('Y-m-d') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Export Teacher Attendance Excel Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while exporting: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export teacher attendance to PDF
     */
    public function exportTeacherAttendancePdf(Request $request)
    {
        try {
            $searchType = $request->input('search_type', 'month');
            $searchDate = $request->input('search_date', date('Y-m-d'));
            $searchMonth = $request->input('search_month');
            $searchYear = $request->input('search_year');
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

            // Parse date and determine date range
            $startDate = null;
            $endDate = null;
            $reportTitle = '';

            if ($searchType === 'month') {
                if ($searchMonth) {
                    $dateObj = Carbon::createFromFormat('Y-m', $searchMonth);
                } else {
                    $dateObj = Carbon::parse($searchDate);
                }
                $startDate = $dateObj->copy()->startOfMonth();
                $endDate = $dateObj->copy()->endOfMonth();
                // If month hasn't ended, use current date
                if ($endDate->isFuture()) {
                    $endDate = Carbon::now();
                }
                $reportTitle = $school->school_name . ' - Teacher Attendance in ' . $dateObj->format('F Y');
            } elseif ($searchType === 'year') {
                if ($searchYear) {
                    $year = (int)$searchYear;
                } else {
                    $dateObj = Carbon::parse($searchDate);
                    $year = $dateObj->year;
                }
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
                // If year hasn't ended, use current date
                if ($endDate->isFuture()) {
                    $endDate = Carbon::now();
                }
                $reportTitle = $school->school_name . ' - Teacher Attendance in ' . $year;
            } else {
                $dateObj = Carbon::parse($searchDate);
                $startDate = $dateObj->copy();
                $endDate = $dateObj->copy();
                $reportTitle = $school->school_name . ' - Teacher Attendance in ' . $dateObj->format('F d, Y');
            }

            // Get all teachers
            $teachers = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            // Get attendance records for the date range
            $attendanceRecords = TeacherFingerprintAttendance::whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->with('teacher')
                ->get()
                ->groupBy('teacherID');

            // Calculate working days (excluding weekends)
            $workingDays = $this->calculateWorkingDays($startDate, $endDate);

            // Prepare data for PDF
            $teacherData = [];
            foreach ($teachers as $teacher) {
                $teacherRecords = $attendanceRecords->get($teacher->id, collect());
                
                // Get unique dates with attendance (check_in_time exists)
                $presentDates = $teacherRecords->filter(function($record) {
                    return $record->check_in_time !== null;
                })->pluck('attendance_date')->unique();

                $daysPresent = $presentDates->count();
                $daysAbsent = max(0, $workingDays - $daysPresent);
                $attendanceRate = $workingDays > 0 ? round(($daysPresent / $workingDays) * 100, 2) : 0;

                $fullName = trim($teacher->first_name . ' ' . ($teacher->middle_name ? $teacher->middle_name . ' ' : '') . $teacher->last_name);

                $teacherData[] = [
                    'name' => $fullName,
                    'position' => $teacher->position ?? 'N/A',
                    'days_present' => $daysPresent,
                    'days_absent' => $daysAbsent,
                    'working_days' => $workingDays,
                    'attendance_rate' => $attendanceRate,
                    'present_dates' => $presentDates->map(function($date) {
                        return $date->format('Y-m-d');
                    })->toArray(),
                ];
            }

            $data = [
                'school' => $school,
                'schoolLogo' => $school->school_logo ? public_path($school->school_logo) : null,
                'reportTitle' => $reportTitle,
                'attendanceDate' => $searchDate,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'teachers' => $teacherData,
                'workingDays' => $workingDays,
            ];

            $pdf = PDF::loadView('Admin.pdf.teacher_attendance', $data);
            $pdf->setPaper('A4', 'landscape');
            
            $filename = 'Teacher_Attendance_' . str_replace(' ', '_', $reportTitle) . '_' . date('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Export Teacher Attendance PDF Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while exporting: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate working days (excluding weekends)
     */
    private function calculateWorkingDays($startDate, $endDate)
    {
        $workingDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($current->dayOfWeek !== Carbon::SATURDAY && $current->dayOfWeek !== Carbon::SUNDAY) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }
}

