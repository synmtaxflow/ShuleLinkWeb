<?php
/**
 * Test Push SDK Connection
 * Access: http://192.168.100.105:8000/test_push_connection.php
 * 
 * This page helps verify that the Push SDK endpoints are accessible
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Log;

header('Content-Type: text/html; charset=utf-8');

$serverIP = $_SERVER['SERVER_ADDR'] ?? '192.168.100.105';
$serverPort = $_SERVER['SERVER_PORT'] ?? '8000';
$testSerialNumber = 'TEST123456';

?>
<!DOCTYPE html>
<html>
<head>
    <title>ZKTeco Push SDK Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; max-width: 1000px; margin: 0 auto; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #940000; padding-bottom: 10px; }
        .info-box { background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .success-box { background: #e8f5e9; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .error-box { background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .test-section { margin: 30px 0; padding: 20px; background: #fafafa; border-radius: 5px; }
        button { background: #940000; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; margin: 10px 5px; }
        button:hover { background: #7a0000; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .url-box { background: #fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px; margin: 10px 0; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table td { padding: 10px; border: 1px solid #ddd; }
        table td:first-child { font-weight: bold; background: #f9f9f9; width: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 ZKTeco Push SDK Connection Test</h1>
        
        <div class="info-box">
            <h3>Server Configuration</h3>
            <table>
                <tr>
                    <td>Server IP</td>
                    <td><code><?php echo htmlspecialchars($serverIP); ?></code></td>
                </tr>
                <tr>
                    <td>Server Port</td>
                    <td><code><?php echo htmlspecialchars($serverPort); ?></code></td>
                </tr>
                <tr>
                    <td>Ping Endpoint</td>
                    <td><code>http://<?php echo htmlspecialchars($serverIP); ?>:<?php echo htmlspecialchars($serverPort); ?>/iclock/getrequest</code></td>
                </tr>
                <tr>
                    <td>Data Endpoint</td>
                    <td><code>http://<?php echo htmlspecialchars($serverIP); ?>:<?php echo htmlspecialchars($serverPort); ?>/iclock/cdata</code></td>
                </tr>
            </table>
        </div>

        <div class="test-section">
            <h2>Test 1: Ping Endpoint</h2>
            <p>Test if the device ping endpoint is accessible:</p>
            <div class="url-box">
                GET <?php echo htmlspecialchars("http://{$serverIP}:{$serverPort}/iclock/getrequest?SN={$testSerialNumber}"); ?>
            </div>
            <button onclick="testPing()">Test Ping Endpoint</button>
            <div id="pingResult"></div>
        </div>

        <div class="test-section">
            <h2>Test 2: Data Endpoint</h2>
            <p>Test if the data endpoint is accessible:</p>
            <div class="url-box">
                POST <?php echo htmlspecialchars("http://{$serverIP}:{$serverPort}/iclock/cdata?SN={$testSerialNumber}&table=ATTLOG&c=log"); ?>
            </div>
            <button onclick="testData()">Test Data Endpoint</button>
            <div id="dataResult"></div>
        </div>

        <div class="test-section">
            <h2>Device Configuration Instructions</h2>
            <div class="info-box">
                <h3>Configure Device ADMS Settings:</h3>
                <ol>
                    <li>On device: Press <strong>MENU</strong> button</li>
                    <li>Navigate to: <strong>System → Communication → ADMS</strong> (or <strong>Push Server</strong>)</li>
                    <li>Enable ADMS: <strong>ON</strong></li>
                    <li>Configure:
                        <ul>
                            <li><strong>Server IP:</strong> <code><?php echo htmlspecialchars($serverIP); ?></code></li>
                            <li><strong>Server Port:</strong> <code><?php echo htmlspecialchars($serverPort); ?></code></li>
                            <li><strong>Server Path:</strong> <code>/iclock/getrequest</code></li>
                        </ul>
                    </li>
                    <li>Save settings</li>
                </ol>
            </div>
        </div>

        <div class="test-section">
            <h2>Check Logs</h2>
            <p>After configuring device, check logs for connection attempts:</p>
            <div class="info-box">
                <p><strong>Log File:</strong> <code>storage/logs/laravel.log</code></p>
                <p><strong>Search for:</strong> <code>ZKTeco Push</code></p>
                <p>Look for messages like:</p>
                <ul>
                    <li><code>ZKTeco Push: Device connection attempt</code></li>
                    <li><code>ZKTeco Push: ✅ CONNECTION SUCCESSFUL</code></li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function testPing() {
            const resultDiv = document.getElementById('pingResult');
            resultDiv.innerHTML = '<p>Testing...</p>';
            
            const url = '<?php echo "http://{$serverIP}:{$serverPort}/iclock/getrequest?SN={$testSerialNumber}"; ?>';
            
            fetch(url)
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'OK') {
                        resultDiv.innerHTML = '<div class="success-box"><h3>✅ Success!</h3><p>Ping endpoint is working. Response: <code>' + data + '</code></p></div>';
                    } else {
                        resultDiv.innerHTML = '<div class="error-box"><h3>❌ Unexpected Response</h3><p>Response: <code>' + data + '</code></p></div>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="error-box"><h3>❌ Error</h3><p>' + error.message + '</p></div>';
                });
        }

        function testData() {
            const resultDiv = document.getElementById('dataResult');
            resultDiv.innerHTML = '<p>Testing...</p>';
            
            const url = '<?php echo "http://{$serverIP}:{$serverPort}/iclock/cdata?SN={$testSerialNumber}&table=ATTLOG&c=log"; ?>';
            const testData = 'PIN=1001\tDateTime=2025-12-01 10:00:00\tVerified=0\tStatus=0';
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'text/plain',
                },
                body: testData
            })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'OK') {
                        resultDiv.innerHTML = '<div class="success-box"><h3>✅ Success!</h3><p>Data endpoint is working. Response: <code>' + data + '</code></p><p>Check logs for detailed information.</p></div>';
                    } else {
                        resultDiv.innerHTML = '<div class="error-box"><h3>❌ Unexpected Response</h3><p>Response: <code>' + data + '</code></p></div>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="error-box"><h3>❌ Error</h3><p>' + error.message + '</p></div>';
                });
        }
    </script>
</body>
</html>

