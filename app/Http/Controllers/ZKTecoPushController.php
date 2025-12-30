<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZKTecoPushController extends Controller
{
    /**
     * Device ping/command request endpoint
     * GET /iclock/getrequest?SN=XXXXXXXXXX
     * Device calls this to check server availability and get commands
     */
    public function getRequest(Request $request)
    {
        $serialNumber = $request->query('SN');
        $deviceIP = $request->ip();
        
        // Log connection attempt with detailed information
        Log::info('ZKTeco Push: Device connection attempt', [
            'serial_number' => $serialNumber,
            'device_ip' => $deviceIP,
            'server_ip' => $request->server('SERVER_ADDR'),
            'server_port' => $request->server('SERVER_PORT'),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'all_headers' => $request->headers->all(),
            'all_query_params' => $request->query->all()
        ]);
        
        // Return "OK" to acknowledge device
        // Device expects plain text response
        $response = response('OK', 200)->header('Content-Type', 'text/plain');
        
        Log::info('ZKTeco Push: Connection successful - Response sent to device', [
            'serial_number' => $serialNumber,
            'device_ip' => $deviceIP,
            'response' => 'OK'
        ]);
        
        return $response;
    }

    /**
     * Device data push endpoint
     * POST /iclock/cdata?SN=XXXXXXXXXX&table=ATTLOG&c=log
     * Device sends attendance records to this endpoint
     */
    public function cdata(Request $request)
    {
        try {
            $serialNumber = $request->query('SN');
            $table = $request->query('table'); // ATTLOG for attendance
            $command = $request->query('c'); // log, user, etc.
            $deviceIP = $request->ip();
            
            // Detailed logging for connection verification
            Log::info('ZKTeco Push: Data received from device', [
                'serial_number' => $serialNumber,
                'table' => $table,
                'command' => $command,
                'device_ip' => $deviceIP,
                'server_ip' => $request->server('SERVER_ADDR'),
                'server_port' => $request->server('SERVER_PORT'),
                'content_type' => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length'),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
                'all_query_params' => $request->query->all()
            ]);
            
            // Handle attendance data (ATTLOG) - DISABLED - Connection testing only
            if ($table === 'ATTLOG' && $command === 'log') {
                $rawData = $request->getContent();
                Log::info('ZKTeco Push: ✅ CONNECTION SUCCESSFUL - Attendance data received (NOT PROCESSED - Connection test only)', [
                    'serial_number' => $serialNumber,
                    'device_ip' => $deviceIP,
                    'data_length' => strlen($rawData),
                    'raw_data_preview' => substr($rawData, 0, 500),
                    'server_response' => 'OK',
                    'connection_status' => 'SUCCESS',
                    'note' => 'Attendance processing is disabled. Only testing connection.'
                ]);
                
                // Return OK to device
                return response('OK', 200)->header('Content-Type', 'text/plain');
            }
            
            // Handle user data (USER)
            if ($table === 'USER' && $command === 'log') {
                return $this->handleUserData($request, $serialNumber);
            }
            
            // Unknown table/command - but still log for connection verification
            Log::info('ZKTeco Push: ✅ CONNECTION SUCCESSFUL - Unknown table/command (but connection works)', [
                'serial_number' => $serialNumber,
                'device_ip' => $deviceIP,
                'table' => $table,
                'command' => $command,
                'connection_status' => 'SUCCESS'
            ]);
            
            return response('OK', 200)->header('Content-Type', 'text/plain');
            
        } catch (\Exception $e) {
            Log::error('ZKTeco Push: Error processing data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Still return OK to device to prevent retries
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Handle attendance data from device
     * DISABLED - Connection testing only
     * Will be enabled later for attendance tracking
     */
    // private function handleAttendanceData(Request $request, $serialNumber)
    // {
    //     // Attendance processing disabled - connection testing only
    //     // This method will be enabled later
    // }

    /**
     * Handle user data from device (optional - for syncing users from device)
     */
    private function handleUserData(Request $request, $serialNumber)
    {
        $rawData = $request->getContent();
        
        Log::info('ZKTeco Push: User data received', [
            'serial_number' => $serialNumber,
            'data_length' => strlen($rawData)
        ]);
        
        // TODO: Implement user sync from device if needed
        // For now, just acknowledge
        
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }
}
