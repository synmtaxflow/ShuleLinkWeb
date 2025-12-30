<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushSetupController extends Controller
{
    /**
     * Get server information for device configuration
     */
    public function getServerInfo()
    {
        $protocol = request()->isSecure() ? 'https' : 'http';
        $host = request()->getHost();
        $port = request()->getPort();
        $ip = $this->getServerIP();
        
        // Build full URLs
        $baseUrl = $protocol . '://' . $host . ($port != 80 && $port != 443 ? ':' . $port : '');
        $pingUrl = $baseUrl . '/iclock/getrequest';
        $dataUrl = $baseUrl . '/iclock/cdata';
        
        return response()->json([
            'success' => true,
            'server' => [
                'ip' => $ip,
                'host' => $host,
                'port' => $port,
                'protocol' => $protocol,
                'base_url' => $baseUrl,
                'ping_endpoint' => $pingUrl,
                'data_endpoint' => $dataUrl,
            ],
            'device_config' => [
                'server_ip' => $ip,
                'server_port' => $port == 80 ? 80 : $port,
                'server_path' => '/iclock/getrequest',
            ],
            'instructions' => [
                '1' => 'On your ZKTeco device, press MENU',
                '2' => 'Go to: System → Communication → ADMS (or Push Server)',
                '3' => 'Enable ADMS: ON',
                '4' => "Set Server IP: {$ip}",
                '5' => "Set Server Port: " . ($port == 80 ? 80 : $port),
                '6' => 'Set Server Path: /iclock/getrequest (or leave default)',
                '7' => 'Save settings',
                '8' => 'Test connection from device (if available)',
            ]
        ]);
    }
    
    /**
     * Get server IP address
     */
    private function getServerIP()
    {
        // Try to get server IP from various sources
        $ip = null;
        
        // Method 1: From request
        $ip = request()->server('SERVER_ADDR');
        
        // Method 2: From HTTP_HOST
        if (!$ip) {
            $host = request()->getHost();
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                $ip = $host;
            }
        }
        
        // Method 3: Try to get local IP
        if (!$ip || $ip === '127.0.0.1' || $ip === '::1') {
            // Try to get actual network IP
            $ip = $this->getLocalIP();
        }
        
        return $ip ?: '127.0.0.1';
    }
    
    /**
     * Get local network IP address
     */
    private function getLocalIP()
    {
        // Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = 'ipconfig | findstr /i "IPv4"';
            exec($command, $output);
            if (!empty($output)) {
                foreach ($output as $line) {
                    if (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
                        $ip = $matches[1];
                        if ($ip !== '127.0.0.1') {
                            return $ip;
                        }
                    }
                }
            }
        } else {
            // Linux/Mac
            $command = "hostname -I | awk '{print $1}'";
            exec($command, $output);
            if (!empty($output[0])) {
                return trim($output[0]);
            }
        }
        
        return null;
    }
    
    /**
     * Test if device can reach server
     */
    public function testDeviceConnection(Request $request)
    {
        $deviceIp = $request->input('device_ip', '192.168.100.109');
        
        // Try to ping device (if ping is available)
        $canPing = false;
        $pingResult = null;
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "ping -n 1 {$deviceIp}";
        } else {
            $command = "ping -c 1 {$deviceIp}";
        }
        
        exec($command . ' 2>&1', $output, $returnCode);
        $canPing = $returnCode === 0;
        $pingResult = implode("\n", $output);
        
        return response()->json([
            'success' => true,
            'device_ip' => $deviceIp,
            'can_ping' => $canPing,
            'ping_result' => $pingResult,
            'message' => $canPing 
                ? "✓ Device is reachable from server" 
                : "⚠ Cannot ping device (may be normal if ping is disabled or device is on different network)"
        ]);
    }
}






