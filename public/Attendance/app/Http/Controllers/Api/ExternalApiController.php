<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExternalApiController extends Controller
{
    /**
     * Register a new user and automatically register to device
     * 
     * POST /api/v1/users/register
     * 
     * Request Body (Simplified - only id and name required):
     * {
     *   "id": "1001",           // Required: Enroll ID from external system
     *   "name": "John Doe",     // Required: User name
     *   "auto_register_device": true,  // Optional, default: true
     *   "device_ip": "192.168.100.108", // Optional, uses config default if not provided
     *   "device_port": 4370              // Optional, uses config default if not provided
     * }
     * 
     * Note: Email and password are auto-generated if not provided
     */
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|unique:users,enroll_id|regex:/^\d+$/|min:1|max:9',
            'name' => 'required|string|max:255',
            'email' => 'sometimes|nullable|string|email|max:255|unique:users',
            'password' => 'sometimes|nullable|string|min:8',
            'auto_register_device' => 'sometimes|boolean',
            'device_ip' => 'sometimes|ip',
            'device_port' => 'sometimes|integer|min:1|max:65535',
        ], [
            'id.regex' => 'ID must contain only numbers',
            'id.max' => 'ID must be maximum 9 digits',
            'id.unique' => 'This ID is already registered',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Auto-generate email if not provided
            $email = $request->input('email');
            if (empty($email)) {
                $email = 'user_' . $request->id . '@attendance.local';
                // Ensure uniqueness
                $counter = 1;
                while (User::where('email', $email)->exists()) {
                    $email = 'user_' . $request->id . '_' . $counter . '@attendance.local';
                    $counter++;
                }
            }

            // Auto-generate password if not provided
            $password = $request->input('password');
            if (empty($password)) {
                $password = bin2hex(random_bytes(16)); // Generate secure random password
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'password' => Hash::make($password),
                'enroll_id' => $request->id, // Use 'id' from request as enroll_id
            ]);

            $response = [
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'enroll_id' => $user->enroll_id,
                    'registered_on_device' => false,
                ]
            ];

            // Auto-register to device if requested (default: true)
            $autoRegister = $request->input('auto_register_device', true);
            
            if ($autoRegister) {
                $deviceIp = $request->input('device_ip', config('zkteco.ip'));
                $devicePort = $request->input('device_port', config('zkteco.port'));

                try {
                    $zkteco = new ZKTecoService($deviceIp, $devicePort);
                    
                    if ($zkteco->connect()) {
                        $registered = $zkteco->registerUser(
                            (int)$user->enroll_id,
                            $user->enroll_id,
                            $user->name,
                            '',
                            0,
                            0
                        );

                        if ($registered) {
                            // Verify registration
                            $deviceUsers = $zkteco->getUsers();
                            $found = false;
                            foreach ($deviceUsers as $deviceUser) {
                                if ($deviceUser['uid'] == (int)$user->enroll_id) {
                                    $found = true;
                                    break;
                                }
                            }

                            if ($found) {
                                $user->update([
                                    'registered_on_device' => true,
                                    'device_registered_at' => now(),
                                ]);

                                $response['data']['registered_on_device'] = true;
                                $response['data']['device_registered_at'] = $user->device_registered_at->format('Y-m-d H:i:s');
                                $response['message'] = 'User created and registered to device successfully';
                            } else {
                                $response['warning'] = 'User created but device registration verification failed';
                            }
                        } else {
                            $response['warning'] = 'User created but device registration failed';
                        }

                        $zkteco->disconnect();
                    } else {
                        $response['warning'] = 'User created but could not connect to device';
                    }
                } catch (\Exception $e) {
                    Log::error("Device registration failed for user {$user->id}: " . $e->getMessage());
                    $response['warning'] = 'User created but device registration failed: ' . $e->getMessage();
                }
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            Log::error("User registration failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user by ID
     * GET /api/v1/users/{id}
     */
    public function getUser($id)
    {
        $user = User::withCount('attendances')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'enroll_id' => $user->enroll_id,
                'registered_on_device' => $user->registered_on_device,
                'device_registered_at' => $user->device_registered_at?->format('Y-m-d H:i:s'),
                'attendances_count' => $user->attendances_count,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Get user by enroll_id
     * GET /api/v1/users/enroll/{enrollId}
     */
    public function getUserByEnrollId($enrollId)
    {
        $user = User::where('enroll_id', $enrollId)->withCount('attendances')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'enroll_id' => $user->enroll_id,
                'registered_on_device' => $user->registered_on_device,
                'device_registered_at' => $user->device_registered_at?->format('Y-m-d H:i:s'),
                'attendances_count' => $user->attendances_count,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * List all users
     * GET /api/v1/users
     */
    public function listUsers(Request $request)
    {
        $query = User::withCount('attendances');

        // Filter by registered status
        if ($request->has('registered')) {
            $query->where('registered_on_device', $request->registered === 'true');
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('enroll_id', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Update user
     * PUT /api/v1/users/{id}
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'enroll_id' => 'sometimes|string|unique:users,enroll_id,' . $id . '|regex:/^\d+$/|min:1|max:9',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'enroll_id']);
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'enroll_id' => $user->enroll_id,
            ]
        ]);
    }

    /**
     * Delete user
     * DELETE /api/v1/users/{id}
     */
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Register user to device
     * POST /api/v1/users/{id}/register-device
     */
    public function registerUserToDevice(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $deviceIp = $request->input('device_ip', config('zkteco.ip'));
        $devicePort = $request->input('device_port', config('zkteco.port'));

        try {
            $zkteco = new ZKTecoService($deviceIp, $devicePort);
            
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device'
                ], 500);
            }

            $registered = $zkteco->registerUser(
                (int)$user->enroll_id,
                $user->enroll_id,
                $user->name,
                '',
                0,
                0
            );

            if ($registered) {
                // Verify registration
                $deviceUsers = $zkteco->getUsers();
                $found = false;
                foreach ($deviceUsers as $deviceUser) {
                    if ($deviceUser['uid'] == (int)$user->enroll_id) {
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    $user->update([
                        'registered_on_device' => true,
                        'device_registered_at' => now(),
                    ]);

                    $zkteco->disconnect();

                    return response()->json([
                        'success' => true,
                        'message' => 'User registered to device successfully',
                        'data' => [
                            'id' => $user->id,
                            'enroll_id' => $user->enroll_id,
                            'registered_on_device' => true,
                        ]
                    ]);
                }
            }

            $zkteco->disconnect();

            return response()->json([
                'success' => false,
                'message' => 'Failed to register user to device'
            ], 500);

        } catch (\Exception $e) {
            Log::error("Device registration failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Device registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records
     * GET /api/v1/attendances
     */
    public function getAttendances(Request $request)
    {
        $query = Attendance::with('user');

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('attendance_date', [
                $request->date_from,
                $request->date_to
            ]);
        }

        // Filter by user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by enroll_id
        if ($request->has('enroll_id')) {
            $query->where('enroll_id', $request->enroll_id);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $attendances->map(function($attendance) {
                return [
                    'id' => $attendance->id,
                    'user' => [
                        'id' => $attendance->user->id,
                        'name' => $attendance->user->name,
                        'enroll_id' => $attendance->user->enroll_id,
                    ],
                    'attendance_date' => $attendance->attendance_date?->format('Y-m-d'),
                    'check_in_time' => $attendance->check_in_time?->format('Y-m-d H:i:s'),
                    'check_out_time' => $attendance->check_out_time?->format('Y-m-d H:i:s'),
                    'status' => $attendance->status,
                    'verify_mode' => $attendance->verify_mode,
                    'device_ip' => $attendance->device_ip,
                ];
            }),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'total' => $attendances->total(),
                'per_page' => $attendances->perPage(),
                'last_page' => $attendances->lastPage(),
            ]
        ]);
    }

    /**
     * Get attendance by ID
     * GET /api/v1/attendances/{id}
     */
    public function getAttendance($id)
    {
        $attendance = Attendance::with('user')->find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'user' => [
                    'id' => $attendance->user->id,
                    'name' => $attendance->user->name,
                    'enroll_id' => $attendance->user->enroll_id,
                ],
                'attendance_date' => $attendance->attendance_date?->format('Y-m-d'),
                'check_in_time' => $attendance->check_in_time?->format('Y-m-d H:i:s'),
                'check_out_time' => $attendance->check_out_time?->format('Y-m-d H:i:s'),
                'status' => $attendance->status,
                'verify_mode' => $attendance->verify_mode,
                'device_ip' => $attendance->device_ip,
            ]
        ]);
    }

    /**
     * Get daily attendance summary
     * GET /api/v1/attendances/daily/{date}
     */
    public function getDailyAttendance($date)
    {
        $attendances = Attendance::with('user')
            ->whereDate('attendance_date', $date)
            ->get()
            ->map(function($attendance) {
                $duration = null;
                if ($attendance->check_in_time && $attendance->check_out_time) {
                    $diff = $attendance->check_in_time->diff($attendance->check_out_time);
                    $duration = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
                }

                return [
                    'user' => [
                        'id' => $attendance->user->id,
                        'name' => $attendance->user->name,
                        'enroll_id' => $attendance->user->enroll_id,
                    ],
                    'date' => $attendance->attendance_date->format('Y-m-d'),
                    'check_in' => $attendance->check_in_time?->format('H:i:s'),
                    'check_out' => $attendance->check_out_time?->format('H:i:s'),
                    'duration' => $duration,
                ];
            });

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $attendances,
            'total' => $attendances->count(),
        ]);
    }
}

