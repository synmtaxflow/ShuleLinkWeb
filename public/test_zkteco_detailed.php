<?php
/**
 * Detailed ZKTeco Connection Test
 * Access: http://192.168.100.105:8000/test_zkteco_detailed.php?ip=192.168.100.108&port=4370&password=0
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ZKTecoService;
use Illuminate\Support\Facades\Log;

header('Content-Type: text/html; charset=utf-8');

$ip = $_GET['ip'] ?? '192.168.100.108';
$port = (int)($_GET['port'] ?? 4370);
$password = $_GET['password'] ?? '0';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Detailed ZKTeco Connection Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 5px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        .log { background: #000; color: #0f0; padding: 15px; border-radius: 5px; margin: 10px 0; max-height: 600px; overflow-y: auto; }
        .error { color: #f00; }
        .success { color: #0f0; }
        .info { color: #0ff; }
        form { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px; }
        input { padding: 8px; margin: 5px; width: 200px; }
        button { padding: 10px 20px; background: #940000; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detailed ZKTeco Connection Test</h1>
        
        <form method="GET">
            <label>Device IP:</label>
            <input type="text" name="ip" value="<?php echo htmlspecialchars($ip); ?>" required><br>
            <label>Port:</label>
            <input type="number" name="port" value="<?php echo htmlspecialchars($port); ?>" required><br>
            <label>Comm Key:</label>
            <input type="text" name="password" value="<?php echo htmlspecialchars($password); ?>"><br>
            <button type="submit">Test Connection</button>
        </form>

        <?php if (isset($_GET['ip'])): ?>
        <div class="log">
            <h3>Connection Test Log:</h3>
            <?php
            echo "<div class='info'>Testing connection to {$ip}:{$port} with Comm Key: {$password}</div>\n";
            flush();
            ob_flush();
            
            try {
                $zkteco = new ZKTecoService($ip, $port, $password);
                
                echo "<div class='info'>Step 1: Created ZKTecoService instance</div>\n";
                flush();
                ob_flush();
                
                echo "<div class='info'>Step 2: Attempting to connect...</div>\n";
                flush();
                ob_flush();
                
                $connected = $zkteco->connect();
                
                if ($connected) {
                    echo "<div class='success'>✅ Connection successful!</div>\n";
                    flush();
                    ob_flush();
                    
                    echo "<div class='info'>Step 3: Getting device info...</div>\n";
                    flush();
                    ob_flush();
                    
                    $deviceInfo = $zkteco->getDeviceInfo();
                    
                    if ($deviceInfo) {
                        echo "<div class='success'>Device Info:</div>\n";
                        echo "<div class='info'>- Serial Number: " . ($deviceInfo['serial_number'] ?? 'N/A') . "</div>\n";
                        echo "<div class='info'>- Firmware Version: " . ($deviceInfo['firmware_version'] ?? 'N/A') . "</div>\n";
                        echo "<div class='info'>- Device Name: " . ($deviceInfo['device_name'] ?? 'N/A') . "</div>\n";
                    }
                    
                    echo "<div class='info'>Step 4: Disconnecting...</div>\n";
                    flush();
                    ob_flush();
                    
                    $zkteco->disconnect();
                    
                    echo "<div class='success'>✅ Test completed successfully!</div>\n";
                } else {
                    echo "<div class='error'>❌ Connection failed!</div>\n";
                    echo "<div class='error'>Check logs for detailed error information.</div>\n";
                }
                
            } catch (\Exception $e) {
                echo "<div class='error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</div>\n";
                echo "<div class='error'>Stack Trace:</div>\n";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
            }
            
            echo "<div class='info'>\n";
            echo "<h4>Recent Log Entries (from storage/logs/laravel.log):</h4>\n";
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $lines = file($logFile);
                $recentLines = array_slice($lines, -50); // Last 50 lines
                foreach ($recentLines as $line) {
                    if (stripos($line, 'ZKTeco') !== false) {
                        echo htmlspecialchars($line) . "<br>\n";
                    }
                }
            }
            echo "</div>\n";
            ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

