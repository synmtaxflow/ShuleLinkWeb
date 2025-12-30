<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZKTeco Push SDK Test</title>
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
            max-width: 900px;
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
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .endpoint {
            background: #f5f5f5;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #4caf50;
        }
        .endpoint h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .endpoint code {
            background: #fff;
            padding: 5px 10px;
            border-radius: 3px;
            display: block;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
        .btn {
            background: #2196f3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
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
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            display: none;
        }
        .result.show {
            display: block;
        }
        .result.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .result.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin-top: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('welcome') }}" class="back-link">← Back to Home</a>
        <h1>📡 ZKTeco Push SDK Test</h1>
        <p class="subtitle">Test if your server endpoints are accessible from the device</p>

        <div class="info-box">
            <strong>📋 Instructions:</strong>
            <ol style="margin-left: 20px; margin-top: 10px;">
                <li>Click "Test Ping Endpoint" to verify server is accessible</li>
                <li>Check the response - should show "OK"</li>
                <li>Configure your device with these endpoints (see ZKTECO_PUSH_SDK_SETUP.md)</li>
                <li>Device will automatically push data when users register or punch in/out</li>
            </ol>
        </div>

        <div class="endpoint">
            <h3>1. Device Ping Endpoint (GET)</h3>
            <p>Device calls this to check if server is available</p>
            <code>GET {{ url('/iclock/getrequest') }}?SN=TEST123</code>
            <button class="btn btn-success" onclick="testPing()">Test Ping Endpoint</button>
        </div>

        <div class="endpoint">
            <h3>2. User Registration Endpoint (POST)</h3>
            <p>Device sends user registration data here</p>
            <code>POST {{ url('/iclock/cdata') }}?SN=TEST123&table=OPERLOG&Stamp=9999</code>
            <button class="btn btn-success" onclick="testUserRegistration()">Test User Registration</button>
        </div>

        <div class="endpoint">
            <h3>3. Attendance Log Endpoint (POST)</h3>
            <p>Device sends attendance records here</p>
            <code>POST {{ url('/iclock/cdata') }}?SN=TEST123&table=ATTLOG&Stamp=9999</code>
            <button class="btn btn-success" onclick="testAttendance()">Test Attendance Log</button>
        </div>

        <div id="result" class="result"></div>
    </div>

    <script>
        function showResult(message, isSuccess) {
            const result = document.getElementById('result');
            result.className = 'result show ' + (isSuccess ? 'success' : 'error');
            result.innerHTML = '<pre>' + message + '</pre>';
        }

        async function testPing() {
            try {
                const response = await fetch('{{ url("/iclock/getrequest") }}?SN=TEST123');
                const text = await response.text();
                showResult(`Status: ${response.status}\nResponse: ${text}\n\n✅ Endpoint is working!`, true);
            } catch (error) {
                showResult(`Error: ${error.message}\n\n❌ Endpoint failed. Check server logs.`, false);
            }
        }

        async function testUserRegistration() {
            try {
                // Simulate user registration data
                const data = 'PIN=999\tName=Test User\tPri=0\tPasswd=\tCard=\tGrp=1\tTZ=0000000100000000\tVerify=0\tViceCard=\tStartDatetime=0\tEndDatetime=0\n';
                
                const response = await fetch('{{ url("/iclock/cdata") }}?SN=TEST123&table=OPERLOG&Stamp=9999', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'text/plain',
                    },
                    body: data
                });
                const text = await response.text();
                showResult(`Status: ${response.status}\nResponse: ${text}\n\n✅ User registration endpoint is working!\n\nCheck Users page to see if test user was created.`, true);
            } catch (error) {
                showResult(`Error: ${error.message}\n\n❌ Endpoint failed. Check server logs.`, false);
            }
        }

        async function testAttendance() {
            try {
                // Simulate attendance data
                const now = new Date();
                const dateStr = now.getFullYear() + '-' + 
                    String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(now.getDate()).padStart(2, '0') + ' ' +
                    String(now.getHours()).padStart(2, '0') + ':' +
                    String(now.getMinutes()).padStart(2, '0') + ':' +
                    String(now.getSeconds()).padStart(2, '0');
                const data = `999\t${dateStr}\t0\t15\t\t0\t0\t\t\t43\n`;
                
                const response = await fetch('{{ url("/iclock/cdata") }}?SN=TEST123&table=ATTLOG&Stamp=9999', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'text/plain',
                    },
                    body: data
                });
                const text = await response.text();
                showResult(`Status: ${response.status}\nResponse: ${text}\n\n✅ Attendance endpoint is working!\n\nCheck Attendance page to see if test record was created.`, true);
            } catch (error) {
                showResult(`Error: ${error.message}\n\n❌ Endpoint failed. Check server logs.`, false);
            }
        }
    </script>
</body>
</html>






