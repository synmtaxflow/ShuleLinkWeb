<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Response;

class ZKTecoPushController extends Controller
{
    /**
     * Handle device ping/check-in and command polling (ADMS Protocol)
     * GET /iclock/getrequest?SN=XXXXXXXXXX
     * 
     * Device polls this endpoint to:
     * 1. Check if server is available
     * 2. Get commands from server (USER ADD, USER DEL, etc.)
     */
    public function getRequest(Request $request)
    {
        $sn = $request->get('SN', 'UNKNOWN');
        
        Log::info("=== ZKTECO DEVICE PING/COMMAND REQUEST ===");
        Log::info("Serial Number: {$sn}");
        Log::info("Request: " . $request->fullUrl());
        Log::info("IP: " . $request->ip());
        
        // For now, just return OK
        // In the future, we can return commands like:
        // "USER ADD PIN=1001\tName=John Doe\tPrivilege=0\tCard=12345678"
        // "USER DEL PIN=1002"
        
        // Device expects simple "OK" response or commands in text/plain
        return Response::make('OK', 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Handle device data push (ADMS Protocol)
     * POST /iclock/cdata?SN=XXXXXXXXXX&table=ATTLOG&c=log
     * POST /iclock/cdata?SN=XXXXXXXXXX&table=USER&c=data
     * POST /iclock/cdata?SN=XXXXXXXXXX&table=OPERLOG&Stamp=9999 (legacy format)
     */
    public function cdata(Request $request)
    {
        $sn = $request->get('SN', 'UNKNOWN');
        $table = $request->get('table', 'UNKNOWN');
        $command = $request->get('c', ''); // ADMS command: log, data, registry, etc.
        $stamp = $request->get('Stamp', 0);
        
        Log::info("=== ZKTECO DEVICE DATA PUSH (ADMS) ===");
        Log::info("Serial Number: {$sn}");
        Log::info("Table: {$table}");
        Log::info("Command: {$command}");
        Log::info("Stamp: {$stamp}");
        Log::info("IP: " . $request->ip());
        Log::info("All Params: " . json_encode($request->all()));
        
        // Get raw body data
        $rawData = $request->getContent();
        Log::info("Raw Data Length: " . strlen($rawData));
        Log::info("Raw Data: " . substr($rawData, 0, 1000)); // First 1000 chars
        
        try {
            // Handle ADMS protocol format
            if ($table === 'ATTLOG' || ($table === 'ATTLOG' && $command === 'log')) {
                // Attendance log (ADMS format)
                $this->handleAttendanceLogADMS($sn, $rawData);
            } elseif ($table === 'USER' && $command === 'data') {
                // User data (ADMS format)
                $this->handleUserDataADMS($sn, $rawData);
            } elseif ($table === 'OPERLOG') {
                // User registration/operation log (legacy format)
                $this->handleUserRegistration($sn, $rawData);
            } elseif ($table === 'ATTLOG' && empty($command)) {
                // Legacy attendance format
                $this->handleAttendanceLog($sn, $rawData);
            } elseif ($table === 'options' && $command === 'registry') {
                // Device registration
                Log::info("Device registration: SN={$sn}");
                // Just acknowledge
            } else {
                Log::warning("Unknown table/command combination: table={$table}, command={$command}");
            }
            
            // Device expects "OK" response
            return Response::make('OK', 200, ['Content-Type' => 'text/plain']);
        } catch (\Exception $e) {
            Log::error("Error processing push data: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Still return OK to device to prevent retries
            return Response::make('OK', 200, ['Content-Type' => 'text/plain']);
        }
    }

    /**
     * Handle user data in ADMS format
     * Format: PIN=1001\tName=John Doe\tPrivilege=0\tCard=12345678\n
     */
    private function handleUserDataADMS($sn, $rawData)
    {
        Log::info("=== PROCESSING USER DATA (ADMS FORMAT) ===");
        
        // Split by newlines (each line is a user)
        $lines = explode("\n", trim($rawData));
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            Log::info("Processing user line (ADMS): " . $line);
            
            // Parse tab-separated key=value pairs
            $parts = explode("\t", $line);
            $userData = [];
            
            foreach ($parts as $part) {
                $part = trim($part);
                if (strpos($part, '=') !== false) {
                    list($key, $value) = explode('=', $part, 2);
                    $userData[trim($key)] = trim($value);
                }
            }
            
            Log::info("Parsed user data (ADMS): " . json_encode($userData));
            
            // Extract user information
            $pin = isset($userData['PIN']) ? (int)$userData['PIN'] : null;
            $name = isset($userData['Name']) ? trim($userData['Name']) : '';
            $card = isset($userData['Card']) ? trim($userData['Card']) : '';
            $privilege = isset($userData['Privilege']) ? (int)$userData['Privilege'] : 0;
            
            if ($pin === null || $pin === 0) {
                Log::warning("User data missing PIN, skipping");
                continue;
            }
            
            // Find or create user by enroll_id (PIN)
            $user = User::where('enroll_id', $pin)->first();
            
            if ($user) {
                // Update existing user
                $user->update([
                    'name' => $name ?: $user->name,
                    'device_registered' => true,
                    'device_ip' => request()->ip(),
                ]);
                Log::info("Updated user (ADMS): PIN={$pin}, Name={$name}");
            } else {
                // Create new user
                $email = "user{$pin}@device.local";
                $counter = 0;
                while (User::where('email', $email)->exists() && $counter < 100) {
                    $email = "user{$pin}_{$counter}@device.local";
                    $counter++;
                }
                
                $user = User::create([
                    'enroll_id' => $pin,
                    'name' => $name ?: "User {$pin}",
                    'email' => $email,
                    'password' => bcrypt('device_user_' . $pin),
                    'device_registered' => true,
                    'device_ip' => request()->ip(),
                ]);
                Log::info("Created new user (ADMS): PIN={$pin}, Name={$name}");
            }
        }
    }

    /**
     * Handle attendance log in ADMS format
     * Format: PIN=1001\tDateTime=2025-09-02 14:32:11\tVerified=1\tStatus=0\n
     */
    private function handleAttendanceLogADMS($sn, $rawData)
    {
        Log::info("=== PROCESSING ATTENDANCE LOG (ADMS FORMAT) ===");
        
        // Split by newlines (each line is an attendance record)
        $lines = explode("\n", trim($rawData));
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            Log::info("Processing attendance line (ADMS): " . $line);
            
            // Parse tab-separated key=value pairs
            $parts = explode("\t", $line);
            $attData = [];
            
            foreach ($parts as $part) {
                $part = trim($part);
                if (strpos($part, '=') !== false) {
                    list($key, $value) = explode('=', $part, 2);
                    $attData[trim($key)] = trim($value);
                }
            }
            
            Log::info("Parsed attendance data (ADMS): " . json_encode($attData));
            
            $pin = isset($attData['PIN']) ? (int)$attData['PIN'] : null;
            $dateTime = isset($attData['DateTime']) ? trim($attData['DateTime']) : null;
            $verified = isset($attData['Verified']) ? (int)$attData['Verified'] : 0;
            $status = isset($attData['Status']) ? (int)$attData['Status'] : 0; // 0=Check In, 1=Check Out
            
            if ($pin === null || $dateTime === null) {
                Log::warning("Attendance log missing PIN or DateTime, skipping");
                continue;
            }
            
            // Parse datetime - try multiple formats
            try {
                $punchTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateTime);
            } catch (\Exception $e) {
                try {
                    $punchTime = \Carbon\Carbon::parse($dateTime);
                } catch (\Exception $e2) {
                    Log::warning("Invalid datetime format: {$dateTime}");
                    continue;
                }
            }
            
            // Find user by enroll_id
            $user = User::where('enroll_id', $pin)->first();
            
            if (!$user) {
                // Create user if not exists
                $email = "user{$pin}@device.local";
                $counter = 0;
                while (User::where('email', $email)->exists() && $counter < 100) {
                    $email = "user{$pin}_{$counter}@device.local";
                    $counter++;
                }
                
                $user = User::create([
                    'enroll_id' => $pin,
                    'name' => "User {$pin}",
                    'email' => $email,
                    'password' => bcrypt('device_user_' . $pin),
                    'device_registered' => true,
                    'device_ip' => request()->ip(),
                ]);
                Log::info("Auto-created user from attendance (ADMS): PIN={$pin}");
            }
            
            // Get attendance date
            $attendanceDate = $punchTime->format('Y-m-d');
            
            // Use database transaction with lock to prevent race conditions
            // Process the entire check-in/check-out logic INSIDE the transaction
            $result = DB::transaction(function() use ($user, $pin, $attendanceDate, $punchTime, $verified, $dateTime) {
                // Find existing attendance record for this user on this date WITH LOCK
                // Try to find by attendance_date first, then by punch_time date (for old records)
                $attendance = Attendance::where('user_id', $user->id)
                    ->where(function($query) use ($attendanceDate) {
                        $query->where('attendance_date', $attendanceDate)
                              ->orWhere(function($q) use ($attendanceDate) {
                                  // For old records without attendance_date, check by punch_time date
                                  $q->whereNull('attendance_date')
                                    ->whereDate('punch_time', $attendanceDate);
                              });
                    })
                    ->lockForUpdate() // Lock the row to prevent concurrent updates
                    ->first();
                
                // If not found, create a new record
                if (!$attendance) {
                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'enroll_id' => $pin,
                        'punch_time' => $punchTime, // Keep for backward compatibility
                        'status' => 1, // Default to Check In
                        'verify_mode' => $verified,
                        'device_ip' => request()->ip(),
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'attendance_date' => $attendanceDate,
                    ]);
                } else {
                    // Update attendance_date if it's missing (for old records)
                    if (!$attendance->attendance_date) {
                        $attendance->attendance_date = $attendanceDate;
                        $attendance->save();
                    }
                }
                
                // Refresh to get latest data
                $attendance->refresh();
                
                // Log current state for debugging
                $checkInTimeStr = $attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d H:i:s') : 'NULL';
                $checkOutTimeStr = $attendance->check_out_time ? $attendance->check_out_time->format('Y-m-d H:i:s') : 'NULL';
                Log::info("Processing scan for User {$pin} at {$dateTime}. Current state: check_in_time={$checkInTimeStr}, check_out_time={$checkOutTimeStr}");
                
                // Use explicit null checks to avoid issues with Carbon instances
                $hasCheckIn = !is_null($attendance->check_in_time);
                $hasCheckOut = !is_null($attendance->check_out_time);
                
                // Check if user already has both check-in and check-out for today
                if ($hasCheckIn && $hasCheckOut) {
                    // User already checked in and out today - reject additional scans
                    Log::info("User {$pin} already checked in and out today - rejecting additional scan");
                    return ['action' => 'rejected', 'reason' => 'already_checked_in_and_out'];
                }
                
                // CRITICAL: Check if this scan time matches an already processed time
                // This prevents the device from sending the same scan multiple times
                if ($hasCheckIn && $attendance->check_in_time && abs($punchTime->diffInSeconds($attendance->check_in_time)) <= 2) {
                    // This scan time is the same as (or very close to) the check-in time
                    // This means the device is sending the first scan again
                    // Skip it - we don't want to overwrite check-in with the same time
                    Log::warning("User {$pin} scan at {$dateTime} matches existing check-in time ({$checkInTimeStr}). Skipping duplicate scan.");
                    return ['action' => 'rejected', 'reason' => 'duplicate_scan_time', 'existing_time' => $checkInTimeStr];
                }
                
                if ($hasCheckOut && $attendance->check_out_time && abs($punchTime->diffInSeconds($attendance->check_out_time)) <= 2) {
                    // This scan time is the same as (or very close to) the check-out time
                    // Skip it - we don't want to overwrite check-out with the same time
                    Log::warning("User {$pin} scan at {$dateTime} matches existing check-out time ({$checkOutTimeStr}). Skipping duplicate scan.");
                    return ['action' => 'rejected', 'reason' => 'duplicate_scan_time', 'existing_time' => $checkOutTimeStr];
                }
                
                // Determine if this is check-in or check-out
                if (!$hasCheckIn) {
                    // First scan of the day = Check In
                    $attendance->update([
                        'check_in_time' => $punchTime,
                        'punch_time' => $punchTime, // Update punch_time to latest
                        'status' => 1, // Check In
                        'verify_mode' => $verified,
                        'attendance_date' => $attendanceDate, // Ensure date is set
                    ]);
                    Log::info("✓ User {$pin} checked IN at {$dateTime}");
                    return ['action' => 'check_in', 'success' => true];
                } elseif (!$hasCheckOut) {
                    // Second scan of the day = Check Out
                    // IMPORTANT: Ensure check_out_time is after check_in_time
                    // If timestamps are the same or check_out is before check_in, add 1 second
                    if ($punchTime->lte($attendance->check_in_time)) {
                        $checkOutTime = $attendance->check_in_time->copy()->addSecond();
                        Log::warning("User {$pin} check-out time ({$dateTime}) is same or before check-in ({$attendance->check_in_time->format('Y-m-d H:i:s')}). Adjusting to: {$checkOutTime->format('Y-m-d H:i:s')}");
                    } else {
                        $checkOutTime = $punchTime;
                    }
                    
                    // IMPORTANT: Only update check_out_time, preserve check_in_time
                    $updateResult = $attendance->update([
                        'check_out_time' => $checkOutTime,
                        'punch_time' => $checkOutTime, // Update punch_time to latest
                        'status' => 0, // Check Out
                        'verify_mode' => $verified,
                        'attendance_date' => $attendanceDate, // Ensure date is set
                        // Note: check_in_time is NOT in the update array, so it will be preserved
                    ]);
                    
                    // Verify the update succeeded
                    $attendance->refresh();
                    if ($attendance->check_out_time) {
                        Log::info("✓ User {$pin} checked OUT at {$checkOutTime->format('Y-m-d H:i:s')} (Check In was at: " . $attendance->check_in_time->format('Y-m-d H:i:s') . ") - UPDATE SUCCESS");
                        return ['action' => 'check_out', 'success' => true, 'check_out_time' => $checkOutTime->format('Y-m-d H:i:s')];
                    } else {
                        Log::error("✗ User {$pin} check-out UPDATE FAILED! check_out_time is still NULL after update.");
                        return ['action' => 'check_out', 'success' => false, 'error' => 'update_failed'];
                    }
                } else {
                    // This shouldn't happen, but log it if it does
                    Log::warning("User {$pin} scan at {$dateTime} - unexpected state: check_in_time=" . ($hasCheckIn ? 'SET' : 'NULL') . ", check_out_time=" . ($hasCheckOut ? 'SET' : 'NULL'));
                    return ['action' => 'unexpected_state', 'has_check_in' => $hasCheckIn, 'has_check_out' => $hasCheckOut];
                }
            });
            
            // If the scan was rejected, skip to next record
            if (isset($result['action']) && $result['action'] === 'rejected') {
                continue;
            }
        }
    }

    /**
     * Parse and handle user registration data (legacy format)
     * Format: PIN=2\tName=Johny Deep\tPri=0\tPasswd=\tCard=\tGrp=1\tTZ=0000000100000000\tVerify=0\tViceCard=\tStartDatetime=0\tEndDatetime=0\n
     */
    private function handleUserRegistration($sn, $rawData)
    {
        Log::info("=== PROCESSING USER REGISTRATION ===");
        
        // Split by newlines (each line is a user)
        $lines = explode("\n", trim($rawData));
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            Log::info("Processing user line: " . $line);
            
            // Parse tab-separated key=value pairs
            $parts = explode("\t", $line);
            $userData = [];
            
            foreach ($parts as $part) {
                if (strpos($part, '=') !== false) {
                    list($key, $value) = explode('=', $part, 2);
                    $userData[$key] = $value;
                }
            }
            
            Log::info("Parsed user data: " . json_encode($userData));
            
            // Extract user information
            $pin = isset($userData['PIN']) ? (int)$userData['PIN'] : null;
            $name = isset($userData['Name']) ? trim($userData['Name']) : '';
            $card = isset($userData['Card']) ? trim($userData['Card']) : '';
            $group = isset($userData['Grp']) ? (int)$userData['Grp'] : 1;
            $verify = isset($userData['Verify']) ? (int)$userData['Verify'] : 0;
            
            if ($pin === null) {
                Log::warning("User registration missing PIN, skipping");
                continue;
            }
            
            // Find or create user by enroll_id (PIN)
            $user = User::where('enroll_id', $pin)->first();
            
            if ($user) {
                // Update existing user
                $user->update([
                    'name' => $name ?: $user->name,
                    'device_registered' => true,
                    'device_ip' => request()->ip(),
                ]);
                Log::info("Updated user: PIN={$pin}, Name={$name}");
            } else {
                // Create new user
                $user = User::create([
                    'enroll_id' => $pin,
                    'name' => $name ?: "User {$pin}",
                    'email' => "user{$pin}@device.local",
                    'device_registered' => true,
                    'device_ip' => request()->ip(),
                ]);
                Log::info("Created new user: PIN={$pin}, Name={$name}");
            }
        }
    }

    /**
     * Parse and handle attendance log data
     * Format: 2\t2022-07-12 16:00:20\t1\t15\t\t0\t0\t\t\t43\n
     * Fields: PIN, DateTime, Status, VerifyMode, WorkCode, Reserved, Reserved, Reserved, Reserved, Reserved
     */
    private function handleAttendanceLog($sn, $rawData)
    {
        Log::info("=== PROCESSING ATTENDANCE LOG ===");
        
        // Split by newlines (each line is an attendance record)
        $lines = explode("\n", trim($rawData));
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            Log::info("Processing attendance line: " . $line);
            
            // Parse tab-separated values
            $parts = explode("\t", $line);
            
            if (count($parts) < 2) {
                Log::warning("Invalid attendance line format: " . $line);
                continue;
            }
            
            $pin = isset($parts[0]) ? (int)trim($parts[0]) : null;
            $dateTime = isset($parts[1]) ? trim($parts[1]) : null;
            $status = isset($parts[2]) ? (int)trim($parts[2]) : 0; // 0=Check In, 1=Check Out
            $verifyMode = isset($parts[3]) ? (int)trim($parts[3]) : 0;
            
            if ($pin === null || $dateTime === null) {
                Log::warning("Attendance log missing PIN or DateTime, skipping");
                continue;
            }
            
            // Parse datetime
            try {
                $punchTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateTime);
            } catch (\Exception $e) {
                Log::warning("Invalid datetime format: {$dateTime}");
                continue;
            }
            
            // Find user by enroll_id
            $user = User::where('enroll_id', $pin)->first();
            
            if (!$user) {
                // Create user if not exists
                $user = User::create([
                    'enroll_id' => $pin,
                    'name' => "User {$pin}",
                    'email' => "user{$pin}@device.local",
                    'device_registered' => true,
                    'device_ip' => request()->ip(),
                ]);
                Log::info("Auto-created user from attendance: PIN={$pin}");
            }
            
            // Get attendance date
            $attendanceDate = $punchTime->format('Y-m-d');
            
            // Use database transaction with lock to prevent race conditions
            // Process the entire check-in/check-out logic INSIDE the transaction
            $result = DB::transaction(function() use ($user, $pin, $attendanceDate, $punchTime, $verifyMode, $dateTime) {
                // Find existing attendance record for this user on this date WITH LOCK
                // Try to find by attendance_date first, then by punch_time date (for old records)
                $attendance = Attendance::where('user_id', $user->id)
                    ->where(function($query) use ($attendanceDate) {
                        $query->where('attendance_date', $attendanceDate)
                              ->orWhere(function($q) use ($attendanceDate) {
                                  // For old records without attendance_date, check by punch_time date
                                  $q->whereNull('attendance_date')
                                    ->whereDate('punch_time', $attendanceDate);
                              });
                    })
                    ->lockForUpdate() // Lock the row to prevent concurrent updates
                    ->first();
                
                // If not found, create a new record
                if (!$attendance) {
                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'enroll_id' => $pin,
                        'punch_time' => $punchTime, // Keep for backward compatibility
                        'status' => 1, // Default to Check In
                        'verify_mode' => $verifyMode,
                        'device_ip' => request()->ip(),
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'attendance_date' => $attendanceDate,
                    ]);
                } else {
                    // Update attendance_date if it's missing (for old records)
                    if (!$attendance->attendance_date) {
                        $attendance->attendance_date = $attendanceDate;
                        $attendance->save();
                    }
                }
                
                // Refresh to get latest data
                $attendance->refresh();
                
                // Log current state for debugging
                $checkInTimeStr = $attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d H:i:s') : 'NULL';
                $checkOutTimeStr = $attendance->check_out_time ? $attendance->check_out_time->format('Y-m-d H:i:s') : 'NULL';
                Log::info("Processing scan for User {$pin} at {$dateTime}. Current state: check_in_time={$checkInTimeStr}, check_out_time={$checkOutTimeStr}");
                
                // Use explicit null checks to avoid issues with Carbon instances
                $hasCheckIn = !is_null($attendance->check_in_time);
                $hasCheckOut = !is_null($attendance->check_out_time);
                
                // Check if user already has both check-in and check-out for today
                if ($hasCheckIn && $hasCheckOut) {
                    // User already checked in and out today - reject additional scans
                    Log::info("User {$pin} already checked in and out today - rejecting additional scan");
                    return ['action' => 'rejected', 'reason' => 'already_checked_in_and_out'];
                }
                
                // CRITICAL: Check if this scan time matches an already processed time
                // This prevents the device from sending the same scan multiple times
                if ($hasCheckIn && $attendance->check_in_time && abs($punchTime->diffInSeconds($attendance->check_in_time)) <= 2) {
                    // This scan time is the same as (or very close to) the check-in time
                    // This means the device is sending the first scan again
                    // Skip it - we don't want to overwrite check-in with the same time
                    Log::warning("User {$pin} scan at {$dateTime} matches existing check-in time ({$checkInTimeStr}). Skipping duplicate scan.");
                    return ['action' => 'rejected', 'reason' => 'duplicate_scan_time', 'existing_time' => $checkInTimeStr];
                }
                
                if ($hasCheckOut && $attendance->check_out_time && abs($punchTime->diffInSeconds($attendance->check_out_time)) <= 2) {
                    // This scan time is the same as (or very close to) the check-out time
                    // Skip it - we don't want to overwrite check-out with the same time
                    Log::warning("User {$pin} scan at {$dateTime} matches existing check-out time ({$checkOutTimeStr}). Skipping duplicate scan.");
                    return ['action' => 'rejected', 'reason' => 'duplicate_scan_time', 'existing_time' => $checkOutTimeStr];
                }
                
                // Determine if this is check-in or check-out
                if (!$hasCheckIn) {
                    // First scan of the day = Check In
                    $attendance->update([
                        'check_in_time' => $punchTime,
                        'punch_time' => $punchTime, // Update punch_time to latest
                        'status' => 1, // Check In
                        'verify_mode' => $verifyMode,
                        'attendance_date' => $attendanceDate, // Ensure date is set
                    ]);
                    Log::info("✓ User {$pin} checked IN at {$dateTime}");
                    return ['action' => 'check_in', 'success' => true];
                } elseif (!$hasCheckOut) {
                    // Second scan of the day = Check Out
                    // IMPORTANT: Ensure check_out_time is after check_in_time
                    // If timestamps are the same or check_out is before check_in, add 1 second
                    if ($punchTime->lte($attendance->check_in_time)) {
                        $checkOutTime = $attendance->check_in_time->copy()->addSecond();
                        Log::warning("User {$pin} check-out time ({$dateTime}) is same or before check-in ({$attendance->check_in_time->format('Y-m-d H:i:s')}). Adjusting to: {$checkOutTime->format('Y-m-d H:i:s')}");
                    } else {
                        $checkOutTime = $punchTime;
                    }
                    
                    // IMPORTANT: Only update check_out_time, preserve check_in_time
                    $updateResult = $attendance->update([
                        'check_out_time' => $checkOutTime,
                        'punch_time' => $checkOutTime, // Update punch_time to latest
                        'status' => 0, // Check Out
                        'verify_mode' => $verifyMode,
                        'attendance_date' => $attendanceDate, // Ensure date is set
                        // Note: check_in_time is NOT in the update array, so it will be preserved
                    ]);
                    
                    // Verify the update succeeded
                    $attendance->refresh();
                    if ($attendance->check_out_time) {
                        Log::info("✓ User {$pin} checked OUT at {$checkOutTime->format('Y-m-d H:i:s')} (Check In was at: " . $attendance->check_in_time->format('Y-m-d H:i:s') . ") - UPDATE SUCCESS");
                        return ['action' => 'check_out', 'success' => true, 'check_out_time' => $checkOutTime->format('Y-m-d H:i:s')];
                    } else {
                        Log::error("✗ User {$pin} check-out UPDATE FAILED! check_out_time is still NULL after update.");
                        return ['action' => 'check_out', 'success' => false, 'error' => 'update_failed'];
                    }
                } else {
                    // This shouldn't happen, but log it if it does
                    Log::warning("User {$pin} scan at {$dateTime} - unexpected state: check_in_time=" . ($hasCheckIn ? 'SET' : 'NULL') . ", check_out_time=" . ($hasCheckOut ? 'SET' : 'NULL'));
                    return ['action' => 'unexpected_state', 'has_check_in' => $hasCheckIn, 'has_check_out' => $hasCheckOut];
                }
            });
            
            // If the scan was rejected, skip to next record
            if (isset($result['action']) && $result['action'] === 'rejected') {
                continue;
            }
        }
    }
}

