<?php

namespace App\Http\Controllers;

use App\Services\ZKTecoService;
use Illuminate\Http\Request;

class ZKTecoController extends Controller
{
    public function index()
    {
        return view('zkteco.test');
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', '192.168.100.127');
        $port = $request->input('port', 4370);

        $zkteco = new ZKTecoService($ip, $port);
        $result = $zkteco->testConnection();

        return response()->json($result);
    }

    public function getDeviceInfo(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', '192.168.100.127');
        $port = $request->input('port', 4370);

        try {
            $zkteco = new ZKTecoService($ip, $port);
            $zkteco->connect();
            $deviceInfo = $zkteco->getDeviceInfo();
            $time = $zkteco->getTime();
            $zkteco->disconnect();

            return response()->json([
                'success' => true,
                'device_info' => $deviceInfo,
                'device_time' => $time
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAttendance(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', '192.168.100.127');
        $port = $request->input('port', 4370);

        try {
            $zkteco = new ZKTecoService($ip, $port);
            $attendance = $zkteco->getAttendances();

            return response()->json([
                'success' => true,
                'attendance' => $attendance,
                'count' => count($attendance)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

