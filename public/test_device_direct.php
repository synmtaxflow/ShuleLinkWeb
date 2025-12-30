<?php
/**
 * Direct Device Connection Test (No AJAX)
 * Access: http://localhost/shuleLink/public/test_device_direct.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ZKTecoService;
use Illuminate\Support\Facades\Log;

header('Content-Type: text/html; charset=utf-8');

$ip = $_GET['ip'] ?? $_POST['ip'] ?? env('ZKTECO_IP', '192.168.100.108');
$port = $_GET['port'] ?? $_POST['port'] ?? env('ZKTECO_PORT', 4370);
$password = $_GET['password'] ?? $_POST['password'] ?? env('ZKTECO_PASSWORD', 0);

$result = null;
$error = null;
$deviceInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['test'])) {
    try {
        echo "<div style='padding: 20px; background: #f0f0f0; margin: 20px 0; border-radius: 5px;'>";
        echo "<h3>Testing Connection...</h3>";
        echo "<p><strong>IP:</strong> {$ip}</p>";
        echo "<p><strong>Port:</strong> {$port}</p>";
        echo "<p><strong>Comm Key:</strong> {$password}</p>";
        echo "</div>";
        
        flush();
        ob_flush();
        
        $zkteco = new ZKTecoService($ip, $port, $password);
        
        echo "<div style='padding: 20px; background: #e8f5e9; margin: 20px 0; border-radius: 5px;'>";
        echo "<p><strong>Step 1:</strong> Creating ZKTecoService instance... ✅</p>";
        flush();
        ob_flush();
        
        if ($zkteco->connect()) {
            echo "<p><strong>Step 2:</strong> Connecting to device... ✅</p>";
            flush();
            ob_flush();
            
            $deviceInfo = $zkteco->getDeviceInfo();
            echo "<p><strong>Step 3:</strong> Getting device info... ✅</p>";
            flush();
            ob_flush();
            
            $zkteco->disconnect();
            echo "<p><strong>Step 4:</strong> Disconnecting... ✅</p>";
            echo "</div>";
            
            $result = true;
        } else {
            echo "</div>";
            $result = false;
            $error = "Failed to connect to device";
        }
    } catch (\Exception $e) {
        $result = false;
        $error = $e->getMessage();
        echo "<div style='padding: 20px; background: #ffebee; margin: 20px 0; border-radius: 5px; color: #c62828;'>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($error) . "</p>";
        echo "</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Direct Device Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; max-width: 900px; margin: 0 auto; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #940000; padding-bottom: 10px; }
        form { margin: 20px 0; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        button { background: #940000; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #7a0000; }
        .result { padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; padding: 15px; margin: 10px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table td { padding: 10px; border: 1px solid #ddd; }
        table td:first-child { font-weight: bold; background: #f9f9f9; width: 200px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 Direct Device Connection Test</h1>
        <p>This page tests the connection directly without AJAX. Use this to debug connection issues.</p>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="ip">Device IP Address:</label>
                <input type="text" id="ip" name="ip" value="<?php echo htmlspecialchars($ip); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="port">Device Port:</label>
                <input type="number" id="port" name="port" value="<?php echo htmlspecialchars($port); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Comm Key (Password):</label>
                <input type="text" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>">
            </div>
            
            <button type="submit">Test Connection</button>
        </form>
        
        <?php if ($result !== null): ?>
            <div class="result <?php echo $result ? 'success' : 'error'; ?>">
                <?php if ($result): ?>
                    <h2>✅ Connection Successful!</h2>
                    <p>The device is connected and ready to use.</p>
                    
                    <?php if ($deviceInfo): ?>
                        <h3>Device Information:</h3>
                        <table>
                            <tr>
                                <td>IP Address</td>
                                <td><?php echo htmlspecialchars($ip); ?></td>
                            </tr>
                            <tr>
                                <td>Port</td>
                                <td><?php echo htmlspecialchars($port); ?></td>
                            </tr>
                            <?php if (isset($deviceInfo['serial_number']) && $deviceInfo['serial_number']): ?>
                            <tr>
                                <td>Serial Number</td>
                                <td><?php echo htmlspecialchars($deviceInfo['serial_number']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (isset($deviceInfo['firmware_version']) && $deviceInfo['firmware_version']): ?>
                            <tr>
                                <td>Firmware Version</td>
                                <td><?php echo htmlspecialchars($deviceInfo['firmware_version']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (isset($deviceInfo['device_name']) && $deviceInfo['device_name']): ?>
                            <tr>
                                <td>Device Name</td>
                                <td><?php echo htmlspecialchars($deviceInfo['device_name']); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    <?php endif; ?>
                <?php else: ?>
                    <h2>❌ Connection Failed!</h2>
                    <p><strong>Error:</strong> <?php echo htmlspecialchars($error ?? 'Unknown error'); ?></p>
                    
                    <div class="info">
                        <strong>Possible Solutions:</strong>
                        <ul>
                            <li>Check if device is powered on</li>
                            <li>Verify IP address is correct (<?php echo htmlspecialchars($ip); ?>)</li>
                            <li>Verify port is correct (<?php echo htmlspecialchars($port); ?>)</li>
                            <li>Check if device and computer are on the same network</li>
                            <li>Verify Comm Key matches device settings</li>
                            <li>Check firewall settings</li>
                            <li>Try pinging the device: <code>ping <?php echo htmlspecialchars($ip); ?></code></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>PHP Configuration:</strong>
            <ul>
                <li><strong>Sockets Extension:</strong> <?php echo extension_loaded('sockets') ? '✅ Enabled' : '❌ Disabled'; ?></li>
                <li><strong>socket_create():</strong> <?php echo function_exists('socket_create') ? '✅ Available' : '❌ Not Available'; ?></li>
                <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                <li><strong>Server API:</strong> <?php echo php_sapi_name(); ?></li>
                <li><strong>php.ini:</strong> <code><?php echo php_ini_loaded_file(); ?></code></li>
            </ul>
        </div>
    </div>
</body>
</html>

