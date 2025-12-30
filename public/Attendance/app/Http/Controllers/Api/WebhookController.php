<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    /**
     * Configure webhook URL for external system
     * POST /api/v1/webhook/configure
     * 
     * Request Body:
     * {
     *   "webhook_url": "https://external-system.com/api/attendance/webhook",
     *   "api_key": "optional-api-key-for-authentication",
     *   "minimal_payload": true  // Optional: Send only user ID (default: false)
     * }
     */
    public function configure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'webhook_url' => 'required|url',
            'api_key' => 'sometimes|string|max:255',
            'minimal_payload' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Store webhook configuration in cache (or database if you prefer)
        Cache::forever('external_webhook_url', $request->webhook_url);
        
        if ($request->has('api_key')) {
            Cache::forever('external_webhook_api_key', $request->api_key);
        }

        // Store minimal payload preference (default: false = send full data)
        Cache::forever('webhook_minimal_payload', $request->input('minimal_payload', false));

        Log::info("Webhook configured: {$request->webhook_url}, minimal_payload: " . ($request->input('minimal_payload', false) ? 'true' : 'false'));

        return response()->json([
            'success' => true,
            'message' => 'Webhook configured successfully',
            'data' => [
                'webhook_url' => $request->webhook_url,
                'minimal_payload' => $request->input('minimal_payload', false),
                'configured_at' => now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Get webhook configuration
     * GET /api/v1/webhook/config
     */
    public function getConfig()
    {
        $webhookUrl = Cache::get('external_webhook_url');
        $apiKey = Cache::get('external_webhook_api_key');
        $minimalPayload = Cache::get('webhook_minimal_payload', false);

        return response()->json([
            'success' => true,
            'data' => [
                'webhook_url' => $webhookUrl,
                'has_api_key' => !empty($apiKey),
                'minimal_payload' => $minimalPayload,
                'configured' => !empty($webhookUrl),
            ]
        ]);
    }

    /**
     * Test webhook endpoint
     * POST /api/v1/webhook/test
     */
    public function test(Request $request)
    {
        $webhookUrl = Cache::get('external_webhook_url');

        if (!$webhookUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook URL not configured. Please configure it first using POST /api/v1/webhook/configure'
            ], 400);
        }

        try {
            $testData = [
                'event' => 'test',
                'message' => 'This is a test webhook from Attendance System',
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];

            $response = $this->sendWebhook($webhookUrl, $testData);

            return response()->json([
                'success' => $response['success'],
                'message' => $response['success'] 
                    ? 'Test webhook sent successfully' 
                    : 'Test webhook failed',
                'response' => $response['response'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error("Webhook test failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Webhook test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send webhook to external system
     * This is called internally when attendance is created/updated
     */
    public static function sendAttendanceWebhook($attendance)
    {
        $webhookUrl = Cache::get('external_webhook_url');

        if (!$webhookUrl) {
            Log::debug('Webhook URL not configured, skipping webhook');
            return false;
        }

        try {
            // Check if minimal payload is enabled (only send user ID)
            $minimalPayload = Cache::get('webhook_minimal_payload', false);

            if ($minimalPayload) {
                // Minimal payload: Only send user ID (enroll_id)
                $webhookData = [
                    'event' => 'attendance.created',
                    'data' => [
                        'id' => $attendance->enroll_id,  // Only the user ID from external system
                    ]
                ];
            } else {
                // Full payload: Send all attendance data
                $webhookData = [
                    'event' => 'attendance.created',
                    'data' => [
                        'id' => $attendance->id,
                        'user_id' => $attendance->user_id,
                        'enroll_id' => $attendance->enroll_id,
                        'user_name' => $attendance->user->name ?? null,
                        'user_email' => $attendance->user->email ?? null,
                        'attendance_date' => $attendance->attendance_date?->format('Y-m-d'),
                        'check_in_time' => $attendance->check_in_time?->format('Y-m-d H:i:s'),
                        'check_out_time' => $attendance->check_out_time?->format('Y-m-d H:i:s'),
                        'status' => $attendance->status,
                        'verify_mode' => $attendance->verify_mode,
                        'device_ip' => $attendance->device_ip,
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                    ]
                ];
            }

            $apiKey = Cache::get('external_webhook_api_key');
            
            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                ...($apiKey ? ['Authorization: Bearer ' . $apiKey] : [])
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error("Webhook curl error: {$error}");
                return false;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info("Webhook sent successfully to {$webhookUrl} for attendance {$attendance->id}");
                return true;
            } else {
                Log::warning("Webhook returned HTTP {$httpCode}: {$response}");
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Webhook send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper method to send webhook (used by test method)
     */
    private function sendWebhook($url, $data)
    {
        $apiKey = Cache::get('external_webhook_api_key');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            ...($apiKey ? ['Authorization: Bearer ' . $apiKey] : [])
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
}

