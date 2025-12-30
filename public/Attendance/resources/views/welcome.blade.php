<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System - Welcome</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 2.5em;
        }

        .subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1em;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .info {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: left;
        }

        .info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .info p {
            color: #666;
            margin: 5px 0;
            line-height: 1.6;
        }

        .info ul {
            color: #666;
            margin: 10px 0 0 20px;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔐</div>
        <h1>Attendance System</h1>
        <p class="subtitle">ZKTeco Fingerprint Device Integration</p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('dashboard') }}" class="btn" style="background: #667eea; color: white; font-weight: bold;">📊 Dashboard</a>
            <a href="{{ route('users.index') }}" class="btn">Manage Users</a>
            <a href="{{ route('attendances.index') }}" class="btn">View Attendance</a>
            <a href="{{ route('attendances.sync-page') }}" class="btn">Sync Attendance</a>
            <a href="{{ route('zkteco.test') }}" class="btn">Test Device</a>
            <a href="{{ route('zkteco.push-test') }}" class="btn" style="background: #4caf50; color: white;">📡 Test Push SDK</a>
            <a href="{{ route('zkteco.push-setup') }}" class="btn" style="background: #ff9800; color: white;">🚀 Setup Wizard</a>
            <button onclick="verifyDatabase()" class="btn" style="background: #17a2b8; color: white;">🔍 Verify Database</button>
        </div>

        <div class="info">
            <h3>📋 Quick Start</h3>
            <p>This system allows you to manage your ZKTeco fingerprint attendance device.</p>
            <ol style="margin-left: 20px; margin-top: 10px;">
                <li><strong>Create Users:</strong> Add users with unique Enroll IDs</li>
                <li><strong>Register to Device:</strong> Register users to your ZKTeco device</li>
                <li><strong>Sync Attendance:</strong> Pull attendance records from the device</li>
                <li><strong>View Records:</strong> View all attendance logs in the system</li>
            </ol>
            <p style="margin-top: 15px;"><strong>Device IP:</strong> 192.168.100.108 | <strong>Port:</strong> 4370</p>
            <p style="margin-top: 10px; color: #17a2b8;"><strong>📡 Push SDK Enabled:</strong> Device can push data automatically via HTTP</p>
        </div>
    </div>

    <script>
    function verifyDatabase() {
        fetch('{{ route("database.verify") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = '✓ DATABASE VERIFICATION\n\n';
                    message += `Connection: ${data.connection}\n`;
                    message += `Database: ${data.database}\n`;
                    message += `Database File Exists: ${data.database_exists ? 'YES ✓' : 'NO ✗'}\n`;
                    message += `Total Tables: ${data.total_tables}\n\n`;
                    message += 'Tables:\n';
                    data.tables.forEach(table => {
                        message += `  - ${table.name}: ${table.count} records\n`;
                    });
                    alert(message);
                    console.log('Full Database Info:', data);
                } else {
                    alert('✗ Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('✗ Error: ' + error.message);
            });
    }
    </script>
</body>
</html>

