<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records
     */
    public function index(Request $request)
    {
        // Check if consolidated view is requested
        $consolidated = $request->has('consolidated') && $request->consolidated == '1';
        
        if ($consolidated) {
            return $this->consolidatedIndex($request);
        }
        
        // Use the new structure: one record per user per day with check_in_time and check_out_time
        $query = Attendance::with('user')
            ->whereNotNull('attendance_date') // Only show records with attendance_date (new format)
            ->orWhere(function($q) {
                // Also include old records without attendance_date for backward compatibility
                $q->whereNull('attendance_date');
            });
        
        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('from_date') && $request->from_date) {
            $query->where(function($q) use ($request) {
                $q->whereDate('attendance_date', '>=', $request->from_date)
                  ->orWhereDate('punch_time', '>=', $request->from_date);
            });
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->where(function($q) use ($request) {
                $q->whereDate('attendance_date', '<=', $request->to_date)
                  ->orWhereDate('punch_time', '<=', $request->to_date);
            });
        }
        
        // Search by user name
        if ($request->has('search') && $request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        
        $attendances = $query->orderByRaw('COALESCE(attendance_date, DATE(punch_time)) DESC, user_id')->paginate(50);
        
        // Get all users for filter dropdown
        $users = \App\Models\User::orderBy('name')->get();
        
        // Get statistics for display (optimized - use single query with conditional aggregation)
        $statsQuery = \App\Models\Attendance::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN check_in_time IS NOT NULL THEN 1 ELSE 0 END) as check_ins,
            SUM(CASE WHEN check_out_time IS NOT NULL THEN 1 ELSE 0 END) as check_outs
        ')->first();
        
        $stats = [
            'total' => $statsQuery->total ?? 0,
            'check_ins' => $statsQuery->check_ins ?? 0,
            'check_outs' => $statsQuery->check_outs ?? 0,
        ];
        
        return view('attendances.index', compact('attendances', 'users', 'stats', 'consolidated'));
    }
    
    /**
     * Display consolidated attendance (one check-in and one check-out per user per day)
     */
    private function consolidatedIndex(Request $request)
    {
        $query = Attendance::with('user');
        
        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('punch_time', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('punch_time', '<=', $request->to_date);
        }
        
        // Get all records first (limit to prevent memory issues)
        $allRecords = $query->orderBy('punch_time', 'asc')
            ->limit(10000) // Limit to prevent memory issues
            ->get();
        
        // Group by user and date
        $consolidated = [];
        
        foreach ($allRecords as $record) {
            if (!$record->user) continue;
            
            $date = $record->punch_time->format('Y-m-d');
            $key = $record->user_id . '_' . $date;
            
            if (!isset($consolidated[$key])) {
                $consolidated[$key] = [
                    'user' => $record->user,
                    'user_id' => $record->user_id,
                    'enroll_id' => $record->enroll_id,
                    'date' => $date,
                    'check_in' => null,
                    'check_out' => null,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'duration' => null,
                ];
            }
            
            // Status: 1 = Check In, 0 or 15 = Check Out
            $status = $record->status;
            
            if ($status == 1) {
                // Check In - take the earliest one
                if (!$consolidated[$key]['check_in'] || 
                    $record->punch_time < $consolidated[$key]['check_in']->punch_time) {
                    $consolidated[$key]['check_in'] = $record;
                    $consolidated[$key]['check_in_time'] = $record->punch_time;
                }
            } else {
                // Check Out - take the latest one
                if (!$consolidated[$key]['check_out'] || 
                    $record->punch_time > $consolidated[$key]['check_out']->punch_time) {
                    $consolidated[$key]['check_out'] = $record;
                    $consolidated[$key]['check_out_time'] = $record->punch_time;
                }
            }
        }
        
        // Calculate duration for each consolidated record
        foreach ($consolidated as $key => &$record) {
            if ($record['check_in_time'] && $record['check_out_time']) {
                $duration = $record['check_in_time']->diff($record['check_out_time']);
                $record['duration'] = $duration->format('%h:%I:%S');
                $record['duration_hours'] = $duration->h + ($duration->days * 24) + ($duration->i / 60);
            }
        }
        
        // Sort by date descending, then by user name
        usort($consolidated, function($a, $b) {
            $dateCompare = strcmp($b['date'], $a['date']);
            if ($dateCompare !== 0) return $dateCompare;
            return strcmp($a['user']->name, $b['user']->name);
        });
        
        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $total = count($consolidated);
        $items = array_slice($consolidated, $offset, $perPage);
        
        // Create paginator manually
        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Get all users for filter dropdown
        $users = \App\Models\User::orderBy('name')->get();
        
        // Get statistics
        $stats = [
            'total' => $total,
            'check_ins' => count(array_filter($consolidated, fn($r) => $r['check_in'] !== null)),
            'check_outs' => count(array_filter($consolidated, fn($r) => $r['check_out'] !== null)),
            'with_both' => count(array_filter($consolidated, fn($r) => $r['check_in'] !== null && $r['check_out'] !== null)),
        ];
        
        return view('attendances.index', compact('attendances', 'users', 'stats', 'consolidated'));
    }

    /**
     * Sync attendance from device
     */
    public function sync(Request $request)
    {
        // Increase execution time for device operations
        set_time_limit(120); // 2 minutes
        
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            Log::info("Syncing attendances from device: {$ip}:{$port}");

            $zkteco = new ZKTecoService($ip, $port);
            
            // First, get raw attendances to verify we're getting real data
            Log::info("=== ATTEMPTING TO GET ATTENDANCES FROM DEVICE ===");
            Log::info("Device IP: {$ip}, Port: {$port}");
            
            $rawAttendances = $zkteco->getAttendances();
            Log::info("Got " . count($rawAttendances) . " raw attendance records from device");
            
            if (count($rawAttendances) === 0) {
                Log::warning("⚠️ NO ATTENDANCE RECORDS FOUND ON DEVICE!");
                Log::warning("Possible reasons:");
                Log::warning("1. No users have punched in/out on the device yet");
                Log::warning("2. Device attendance log is empty");
                Log::warning("3. Device connection issue (but connection was successful)");
                Log::warning("4. Device firmware issue (UF200-S firmware 6.60)");
                
                // Try to get device info to verify connection
                try {
                    $deviceInfo = $zkteco->getDeviceInfo();
                    Log::info("Device info retrieved: " . json_encode($deviceInfo));
                } catch (\Exception $e) {
                    Log::error("Could not get device info: " . $e->getMessage());
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'No attendance records found on device. Make sure users have punched in/out on the device. Check device menu: Data Management → Attendance Records to verify if records exist.',
                    'data' => [
                        'raw_count' => 0,
                        'device_connected' => true,
                        'note' => 'Device connection successful, but no attendance records found. This could mean: 1) No users have punched in/out, 2) Device log is empty, 3) Device needs to be checked manually.'
                    ]
                ], 400);
            }
            
            // Log ALL records for debugging
            Log::info("=== ALL RAW ATTENDANCE RECORDS FROM DEVICE ===");
            Log::info("Total records: " . count($rawAttendances));
            foreach ($rawAttendances as $idx => $record) {
                Log::info("Record #{$idx}: " . json_encode($record, JSON_PRETTY_PRINT));
            }
            
            // Log first record for debugging
            if (count($rawAttendances) > 0) {
                Log::info("First raw attendance record: " . json_encode($rawAttendances[0], JSON_PRETTY_PRINT));
                Log::info("First record keys: " . implode(', ', array_keys($rawAttendances[0])));
            }
            
            $result = $zkteco->syncAttendancesToDatabase();
            
            // Log sync result
            Log::info("=== SYNC RESULT ===");
            Log::info("Synced: " . ($result['synced'] ?? 0));
            Log::info("Skipped: " . ($result['skipped'] ?? 0));
            Log::info("Users verified: " . ($result['users_verified'] ?? 0));
            
            // Include diagnostic info in response
            $responseData = array_merge($result, [
                'raw_count_from_device' => count($rawAttendances),
                'sample_raw_record' => count($rawAttendances) > 0 ? $rawAttendances[0] : null
            ]);
            
            // Add diagnostic message if records were skipped
            if (($result['skipped'] ?? 0) > 0 && isset($result['skipped_details'])) {
                $skipReasons = [];
                foreach ($result['skipped_details'] as $skipped) {
                    if (isset($skipped['skip_reason'])) {
                        $skipReasons[] = $skipped['skip_reason'];
                    }
                }
                $responseData['skip_reasons'] = array_count_values($skipReasons);
            }

            return response()->json([
                'success' => true,
                'message' => "Synced {$result['synced']} attendance record(s) from device. {$result['skipped']} skipped. " . 
                            (count($rawAttendances) > 0 ? "Device has " . count($rawAttendances) . " total records." : ""),
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error('Sync attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display sync page
     */
    public function syncPage()
    {
        return view('attendances.sync');
    }

    /**
     * Test connection and show raw data from device
     */
    public function testDeviceData(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            $zkteco = new ZKTecoService($ip, $port);
            
            // Test connection
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device'
                ], 400);
            }

            // Get raw attendances
            $rawAttendances = $zkteco->getAttendances();
            
            // Get device info
            $deviceInfo = $zkteco->getDeviceInfo();
            
            // Get all users from database for comparison
            $systemUsers = \App\Models\User::select('id', 'name', 'enroll_id')->get();
            
            return response()->json([
                'success' => true,
                'device_info' => $deviceInfo,
                'raw_attendances_count' => count($rawAttendances),
                'raw_attendances' => $rawAttendances,
                'system_users' => $systemUsers,
                'message' => 'Successfully retrieved data from device'
            ]);
        } catch (\Exception $e) {
            Log::error('Test device data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified attendance record
     */
    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('attendances.show', compact('attendance'));
    }

    /**
     * Delete all attendance records
     */
    public function deleteAll(Request $request)
    {
        try {
            $count = Attendance::count();
            
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No attendance records to delete'
                ], 400);
            }

            // Permanently delete all attendance records (not soft delete)
            // Using delete() which permanently removes records from database
            Attendance::query()->delete();

            Log::info("Deleted all {$count} attendance records from database");

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} attendance record(s) from database",
                'deleted_count' => $count
            ]);
        } catch (\Throwable $e) {
            Log::error('Delete all attendances error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear attendance records from device
     */
    public function clearDeviceAttendance(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            Log::info("Clearing attendance from device: {$ip}:{$port}");

            $zkteco = new ZKTecoService($ip, $port);
            $result = $zkteco->clearDeviceAttendance();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result
                ]);
            } else {
                // Provide more detailed error message
                $errorMsg = $result['message'];
                if (isset($result['response_code'])) {
                    if ($result['response_code'] == 2001) {
                        $errorMsg .= ' Device rejected the command (Error 2001).';
                    } elseif ($result['response_code'] == 2005) {
                        $errorMsg .= ' Authentication required (Error 2005). Check Comm Key.';
                    }
                }
                if (isset($result['records_before']) && isset($result['records_after'])) {
                    if ($result['records_before'] == $result['records_after']) {
                        $errorMsg .= ' Records count unchanged - clear may have failed.';
                    }
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'data' => $result
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Clear device attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear attendance from both database and device
     */
    public function clearAll(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'clear_device' => 'boolean'
        ]);

        try {
            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));
            $clearDevice = $request->input('clear_device', true);

            $results = [
                'database' => ['success' => false, 'count' => 0],
                'device' => ['success' => false, 'message' => '']
            ];

            // Clear database
            $dbCount = Attendance::count();
            if ($dbCount > 0) {
                Attendance::query()->delete();
                $results['database'] = ['success' => true, 'count' => $dbCount];
                Log::info("Deleted {$dbCount} attendance records from database");
            }

            // Clear device if requested
            if ($clearDevice) {
                try {
                    $zkteco = new ZKTecoService($ip, $port);
                    $deviceResult = $zkteco->clearDeviceAttendance();
                    $results['device'] = [
                        'success' => $deviceResult['success'],
                        'message' => $deviceResult['message'],
                        'records_cleared' => $deviceResult['cleared'] ?? 0
                    ];
                } catch (\Exception $e) {
                    $results['device'] = [
                        'success' => false,
                        'message' => 'Device error: ' . $e->getMessage()
                    ];
                    Log::error('Device clear error: ' . $e->getMessage());
                }
            }

            $message = "Cleared {$results['database']['count']} record(s) from database.";
            if ($clearDevice) {
                if ($results['device']['success']) {
                    $cleared = $results['device']['cleared'] ?? $results['device']['records_cleared'] ?? 0;
                    $message .= " Cleared {$cleared} record(s) from device.";
                } else {
                    $deviceMsg = $results['device']['message'] ?? 'Unknown error';
                    // Check if records were actually cleared despite error message
                    if (isset($results['device']['records_before']) && isset($results['device']['records_after'])) {
                        $before = $results['device']['records_before'];
                        $after = $results['device']['records_after'];
                        if ($after < $before) {
                            $message .= " Device clear partially succeeded: {$before} → {$after} records (cleared " . ($before - $after) . ").";
                        } else {
                            $message .= " Device clear failed: {$deviceMsg}";
                        }
                    } else {
                        $message .= " Device clear failed: {$deviceMsg}";
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Clear all attendances error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
