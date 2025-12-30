<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegistrationTestController extends Controller
{
    /**
     * Test user registration with detailed logging
     */
    public function testRegistration(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'enroll_id' => 'required|integer|min:1|max:65535',
            'name' => 'required|string|max:255',
        ]);

        $ip = $request->input('ip');
        $port = $request->input('port');
        $enrollId = $request->input('enroll_id');
        $name = $request->input('name');

        $results = [
            'timestamp' => now()->toDateTimeString(),
            'device' => [
                'ip' => $ip,
                'port' => $port,
            ],
            'test_user' => [
                'enroll_id' => $enrollId,
                'name' => $name,
            ],
            'steps' => [],
            'success' => false,
            'error' => null,
        ];

        try {
            // Step 1: Connect to device
            $results['steps'][] = [
                'step' => 1,
                'name' => 'Connect to Device',
                'status' => 'running',
            ];

            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                throw new \Exception('Failed to connect to device');
            }

            $results['steps'][0]['status'] = 'success';
            $results['steps'][0]['message'] = 'Connected successfully';

            // Step 2: Get device info
            $results['steps'][] = [
                'step' => 2,
                'name' => 'Get Device Info',
                'status' => 'running',
            ];

            try {
                $deviceInfo = $zkteco->getDeviceInfo();
                $results['steps'][1]['status'] = 'success';
                $results['steps'][1]['message'] = 'Device info retrieved';
                $results['steps'][1]['data'] = $deviceInfo;
            } catch (\Exception $e) {
                $results['steps'][1]['status'] = 'warning';
                $results['steps'][1]['message'] = 'Could not get device info: ' . $e->getMessage();
            }

            // Step 3: Get users before registration
            $results['steps'][] = [
                'step' => 3,
                'name' => 'Get Users Before Registration',
                'status' => 'running',
            ];

            $usersBefore = $zkteco->getUsers();
            $userCountBefore = count($usersBefore);
            
            // Check if user already exists
            $userExists = false;
            foreach ($usersBefore as $key => $deviceUser) {
                if ((string)$key === (string)$enrollId || 
                    (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === (int)$enrollId) ||
                    (isset($deviceUser['user_id']) && (string)$deviceUser['user_id'] === (string)$enrollId)) {
                    $userExists = true;
                    break;
                }
            }

            if ($userExists) {
                $results['steps'][2]['status'] = 'warning';
                $results['steps'][2]['message'] = "User with Enroll ID {$enrollId} already exists on device";
            } else {
                $results['steps'][2]['status'] = 'success';
                $results['steps'][2]['message'] = "Found {$userCountBefore} users on device";
            }
            $results['steps'][2]['data'] = ['user_count' => $userCountBefore];

            // Step 4: Register user
            $results['steps'][] = [
                'step' => 4,
                'name' => 'Register User to Device',
                'status' => 'running',
            ];

            if ($userExists) {
                $results['steps'][3]['status'] = 'skipped';
                $results['steps'][3]['message'] = 'User already exists, skipping registration';
            } else {
                $result = $zkteco->registerUser(
                    (int)$enrollId,
                    (string)$enrollId,
                    $name,
                    '', // password
                    0,  // role
                    0   // cardno
                );

                if ($result) {
                    $results['steps'][3]['status'] = 'success';
                    $results['steps'][3]['message'] = 'User registration command sent successfully';
                } else {
                    throw new \Exception('Registration returned false');
                }
            }

            // Step 5: Verify user was added
            $results['steps'][] = [
                'step' => 5,
                'name' => 'Verify User on Device',
                'status' => 'running',
            ];

            // Wait a moment for device to process
            sleep(2);

            $usersAfter = $zkteco->getUsers();
            $userCountAfter = count($usersAfter);
            
            $userFound = false;
            $foundBy = null;
            
            foreach ($usersAfter as $key => $deviceUser) {
                if ((string)$key === (string)$enrollId) {
                    $userFound = true;
                    $foundBy = "array key (userid: '{$key}')";
                    break;
                }
                if (isset($deviceUser['uid']) && (int)$deviceUser['uid'] === (int)$enrollId) {
                    $userFound = true;
                    $foundBy = "UID ({$enrollId})";
                    break;
                }
                if (isset($deviceUser['user_id']) && (string)$deviceUser['user_id'] === (string)$enrollId) {
                    $userFound = true;
                    $foundBy = "user_id ({$deviceUser['user_id']})";
                    break;
                }
            }

            if ($userFound) {
                $results['steps'][4]['status'] = 'success';
                $results['steps'][4]['message'] = "User found on device (found by: {$foundBy})";
                $results['steps'][4]['data'] = [
                    'user_count_before' => $userCountBefore,
                    'user_count_after' => $userCountAfter,
                    'user_count_increased' => $userCountAfter > $userCountBefore,
                    'found_by' => $foundBy,
                ];
                $results['success'] = true;
            } else {
                $results['steps'][4]['status'] = 'failed';
                $results['steps'][4]['message'] = 'User NOT found on device after registration';
                $results['steps'][4]['data'] = [
                    'user_count_before' => $userCountBefore,
                    'user_count_after' => $userCountAfter,
                    'user_count_increased' => $userCountAfter > $userCountBefore,
                ];
                throw new \Exception('User registration verification failed - user not found on device');
            }

            // Step 6: Get final device users list
            $results['steps'][] = [
                'step' => 6,
                'name' => 'Final Device Users List',
                'status' => 'success',
                'message' => "Total users on device: {$userCountAfter}",
                'data' => [
                    'users' => array_map(function($user, $key) {
                        return [
                            'key' => $key,
                            'uid' => $user['uid'] ?? null,
                            'user_id' => $user['user_id'] ?? null,
                            'name' => $user['name'] ?? null,
                        ];
                    }, $usersAfter, array_keys($usersAfter)),
                ],
            ];

        } catch (\Exception $e) {
            $results['success'] = false;
            $results['error'] = $e->getMessage();
            
            // Mark current step as failed
            if (!empty($results['steps'])) {
                $lastStep = &$results['steps'][count($results['steps']) - 1];
                if ($lastStep['status'] === 'running') {
                    $lastStep['status'] = 'failed';
                    $lastStep['message'] = $e->getMessage();
                }
            }
        }

        return response()->json($results, $results['success'] ? 200 : 500);
    }

    /**
     * Simple test page
     */
    public function testPage()
    {
        return view('test.registration-test');
    }
}


