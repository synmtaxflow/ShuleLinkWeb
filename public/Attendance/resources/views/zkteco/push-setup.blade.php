<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZKTeco Push SDK Setup Wizard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .step {
            background: #f9f9f9;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .step h3 {
            color: #2196f3;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step-number {
            background: #2196f3;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .config-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .config-box h4 {
            color: #856404;
            margin-bottom: 15px;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: white;
            margin: 5px 0;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        .config-label {
            font-weight: bold;
            color: #333;
        }
        .config-value {
            font-family: 'Courier New', monospace;
            color: #2196f3;
            font-weight: bold;
        }
        .btn {
            background: #2196f3;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
            font-weight: bold;
        }
        .btn:hover {
            background: #1976d2;
        }
        .btn-success {
            background: #4caf50;
        }
        .btn-success:hover {
            background: #45a049;
        }
        .btn-warning {
            background: #ff9800;
        }
        .btn-warning:hover {
            background: #f57c00;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .loading.show {
            display: block;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .result.show {
            display: block;
        }
        .result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .result.info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        ol {
            margin-left: 20px;
            margin-top: 10px;
        }
        ol li {
            margin: 8px 0;
            line-height: 1.6;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #2196f3;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .copy-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
        }
        .copy-btn:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('welcome') }}" class="back-link">← Back to Home</a>
        <h1>🚀 ZKTeco Push SDK Setup Wizard</h1>
        <p class="subtitle">Step-by-step guide to configure your device for automatic data push</p>

        <div class="info-box">
            <strong>📋 What this does:</strong> Configures your ZKTeco device to automatically send user registrations and attendance records to this server via HTTP. No more manual syncing!
        </div>

        <div class="step">
            <h3>
                <span class="step-number">1</span>
                Get Server Information
            </h3>
            <p>First, let's get your server's IP address and configuration details.</p>
            <button class="btn" onclick="getServerInfo()">📡 Get Server Info</button>
            <div id="serverInfo" class="result"></div>
        </div>

        <div class="step">
            <h3>
                <span class="step-number">2</span>
                Configure Device Settings
            </h3>
            <p>Use the information above to configure your ZKTeco device (IP: <code>192.168.100.108</code>).</p>
            <div id="deviceConfig" style="display: none;">
                <div class="config-box">
                    <h4>⚙️ Device Configuration Values</h4>
                    <div class="config-item">
                        <span class="config-label">ADMS Status:</span>
                        <span class="config-value">ENABLED (ON)</span>
                    </div>
                    <div class="config-item">
                        <span class="config-label">Server IP:</span>
                        <span class="config-value" id="configServerIp">Loading...</span>
                        <button class="copy-btn" onclick="copyToClipboard('configServerIp')">Copy</button>
                    </div>
                    <div class="config-item">
                        <span class="config-label">Server Port:</span>
                        <span class="config-value" id="configServerPort">Loading...</span>
                        <button class="copy-btn" onclick="copyToClipboard('configServerPort')">Copy</button>
                    </div>
                    <div class="config-item">
                        <span class="config-label">Server Path:</span>
                        <span class="config-value">/iclock/getrequest</span>
                        <button class="copy-btn" onclick="copyToClipboard('path')">Copy</button>
                    </div>
                </div>
                
                <div class="info-box">
                    <strong>📝 Device Menu Path:</strong>
                    <ol>
                        <li>Press <strong>MENU</strong> button on device</li>
                        <li>Navigate to: <strong>System</strong> → <strong>Communication</strong> → <strong>ADMS</strong> (or <strong>Push Server</strong>)</li>
                        <li>Enable <strong>ADMS: ON</strong></li>
                        <li>Enter the Server IP shown above</li>
                        <li>Enter the Server Port shown above (usually 80)</li>
                        <li>Set Server Path: <code>/iclock/getrequest</code> (or leave default)</li>
                        <li><strong>Save</strong> settings</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="step">
            <h3>
                <span class="step-number">3</span>
                Test Device Connection
            </h3>
            <p>Verify that your device can reach the server.</p>
            <button class="btn btn-warning" onclick="testDeviceConnection()">🔍 Test Device Connection</button>
            <div id="connectionTest" class="result"></div>
        </div>

        <div class="step">
            <h3>
                <span class="step-number">4</span>
                Import Existing Users from Device
            </h3>
            <p><strong>Important:</strong> If you have users already registered on the device, import them first before configuring push.</p>
            <div class="info-box">
                <strong>📥 This will:</strong>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>Connect to device and get all users</li>
                    <li>Create users in database if they don't exist</li>
                    <li>Mark existing users as registered on device</li>
                </ul>
            </div>
            <button class="btn btn-success" onclick="importUsersFromDevice()">📥 Import Users from Device</button>
            <div id="importResult" class="result"></div>
        </div>

        <div class="step">
            <h3>
                <span class="step-number">5</span>
                Verify Push is Working
            </h3>
            <p>After configuring the device, test if data is being pushed automatically.</p>
            <div class="info-box">
                <strong>✅ How to verify:</strong>
                <ol>
                    <li>Register a new user on the device (User Management → Add User)</li>
                    <li>Check the <a href="{{ route('users.index') }}" target="_blank">Users page</a> - user should appear automatically</li>
                    <li>Punch in/out on the device using fingerprint</li>
                    <li>Check the <a href="{{ route('attendances.index') }}" target="_blank">Attendance page</a> - record should appear automatically</li>
                    <li>Check <code>storage/logs/laravel.log</code> for push activity</li>
                </ol>
            </div>
            <button class="btn btn-success" onclick="checkRecentActivity()">📊 Check Recent Activity</button>
            <div id="activityCheck" class="result"></div>
        </div>

        <div class="loading" id="loading">
            <p>⏳ Processing...</p>
        </div>
    </div>

    <script>
        let serverInfo = null;

        function showLoading(show) {
            document.getElementById('loading').classList.toggle('show', show);
        }

        function showResult(elementId, message, type = 'info') {
            const element = document.getElementById(elementId);
            element.className = 'result show ' + type;
            element.innerHTML = message;
        }

        function copyToClipboard(elementId) {
            let text = '';
            if (elementId === 'path') {
                text = '/iclock/getrequest';
            } else {
                const element = document.getElementById(elementId);
                text = element.textContent;
            }
            
            navigator.clipboard.writeText(text).then(() => {
                alert('✓ Copied to clipboard: ' + text);
            }).catch(err => {
                alert('✗ Failed to copy: ' + err);
            });
        }

        async function getServerInfo() {
            showLoading(true);
            try {
                const response = await fetch('{{ route("push.setup.server-info") }}');
                const data = await response.json();
                
                if (data.success) {
                    serverInfo = data;
                    
                    // Show server info
                    let html = '<div class="info-box">';
                    html += '<strong>✓ Server Information:</strong><br>';
                    html += `Server IP: <code>${data.server.ip}</code><br>`;
                    html += `Server Host: <code>${data.server.host}</code><br>`;
                    html += `Server Port: <code>${data.server.port}</code><br>`;
                    html += `Protocol: <code>${data.server.protocol}</code><br>`;
                    html += `Ping Endpoint: <code>${data.server.ping_endpoint}</code><br>`;
                    html += `Data Endpoint: <code>${data.server.data_endpoint}</code>`;
                    html += '</div>';
                    
                    showResult('serverInfo', html, 'success');
                    
                    // Show device config
                    document.getElementById('deviceConfig').style.display = 'block';
                    document.getElementById('configServerIp').textContent = data.device_config.server_ip;
                    document.getElementById('configServerPort').textContent = data.device_config.server_port;
                } else {
                    showResult('serverInfo', '✗ Failed to get server information', 'error');
                }
            } catch (error) {
                showResult('serverInfo', '✗ Error: ' + error.message, 'error');
            } finally {
                showLoading(false);
            }
        }

        async function testDeviceConnection() {
            showLoading(true);
            try {
                const response = await fetch('{{ route("push.setup.test-connection") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        device_ip: '192.168.100.108'
                    })
                });
                const data = await response.json();
                
                let html = '';
                if (data.can_ping) {
                    html = '<div class="info-box">';
                    html += '<strong>✓ Device is reachable!</strong><br>';
                    html += `Device IP: <code>${data.device_ip}</code><br>`;
                    html += '<pre style="margin-top: 10px; background: white; padding: 10px; border-radius: 3px;">' + data.ping_result + '</pre>';
                    html += '</div>';
                    showResult('connectionTest', html, 'success');
                } else {
                    html = '<div class="info-box">';
                    html += '<strong>⚠ Cannot ping device</strong><br>';
                    html += 'This is usually normal if:<br>';
                    html += '• Ping is disabled on device<br>';
                    html += '• Device is on different network<br>';
                    html += '• Firewall is blocking ICMP<br><br>';
                    html += 'This does NOT mean push won\'t work. Try configuring the device anyway.';
                    html += '</div>';
                    showResult('connectionTest', html, 'info');
                }
            } catch (error) {
                showResult('connectionTest', '✗ Error: ' + error.message, 'error');
            } finally {
                showLoading(false);
            }
        }

        async function importUsersFromDevice() {
            if (!confirm('This will import all users from the device (192.168.100.108) into the database. Continue?')) {
                return;
            }
            
            showLoading(true);
            try {
                const response = await fetch('{{ route("users.sync-from-device") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ip: '192.168.100.108',
                        port: 4370
                    })
                });
                const data = await response.json();
                
                let html = '';
                if (data.success) {
                    html = '<div class="info-box">';
                    html += '<strong>✓ Import Complete!</strong><br><br>';
                    html += `Total Processed: <code>${data.data.verified}</code> user(s)<br>`;
                    if (data.data.created > 0) {
                        html += `✓ Created: <code>${data.data.created}</code> new user(s)<br>`;
                    }
                    if (data.data.marked_registered > 0) {
                        html += `✓ Marked as Registered: <code>${data.data.marked_registered}</code> user(s)<br>`;
                    }
                    if (data.data.already_registered > 0) {
                        html += `✓ Already Registered: <code>${data.data.already_registered}</code> user(s)<br>`;
                    }
                    if (data.data.not_found > 0) {
                        html += `<br><strong>⚠ Could not process: <code>${data.data.not_found}</code> user(s)</strong><br>`;
                        if (data.data.device_users_not_in_db && data.data.device_users_not_in_db.length > 0) {
                            html += '<details style="margin-top: 10px;"><summary style="cursor: pointer; color: #856404;">Show Error Details</summary><ul style="margin-left: 20px; margin-top: 10px;">';
                            data.data.device_users_not_in_db.forEach(user => {
                                html += `<li><strong>Enroll ID:</strong> ${user.enroll_id || 'N/A'}, <strong>Name:</strong> ${user.name || 'N/A'}<br>`;
                                html += `<strong>Error:</strong> ${user.error || 'Unknown error'}</li>`;
                            });
                            html += '</ul></details>';
                        }
                    }
                    html += `<br>Total Users on Device: <code>${data.data.total_device_users || 'N/A'}</code><br>`;
                    html += '<br><a href="{{ route("users.index") }}" target="_blank" class="btn" style="margin-top: 10px;">View Users →</a>';
                    html += '<br><br><small>💡 Check <code>storage/logs/laravel.log</code> for detailed error information.</small>';
                    html += '</div>';
                    
                    showResult('importResult', html, data.data.not_found > 0 ? 'info' : 'success');
                    
                    // Refresh activity check
                    setTimeout(() => checkRecentActivity(), 1000);
                } else {
                    html = '<div class="info-box">';
                    html += '<strong>✗ Import Failed</strong><br>';
                    html += `Error: ${data.message}`;
                    html += '<br><br><small>💡 Check <code>storage/logs/laravel.log</code> for detailed error information.</small>';
                    html += '</div>';
                    showResult('importResult', html, 'error');
                }
            } catch (error) {
                showResult('importResult', '✗ Error: ' + error.message, 'error');
            } finally {
                showLoading(false);
            }
        }

        async function checkRecentActivity() {
            showLoading(true);
            try {
                const response = await fetch('{{ route("push.setup.check-activity") }}');
                const data = await response.json();
                
                let html = '<div class="info-box">';
                html += `<strong>📊 Recent Activity:</strong><br>`;
                html += `Total Users: <code>${data.users_count}</code><br>`;
                html += `Total Attendance Records: <code>${data.attendances_count}</code><br>`;
                html += `Recent Users (last 5): <code>${data.recent_users}</code><br>`;
                html += `Recent Attendances (last 5): <code>${data.recent_attendances}</code>`;
                html += '</div>';
                
                showResult('activityCheck', html, 'success');
            } catch (error) {
                showResult('activityCheck', '✗ Error: ' + error.message, 'error');
            } finally {
                showLoading(false);
            }
        }

        // Auto-load server info on page load
        window.addEventListener('load', function() {
            getServerInfo();
        });
    </script>
</body>
</html>

