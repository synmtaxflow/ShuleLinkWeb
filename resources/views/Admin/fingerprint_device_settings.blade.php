
@extends('layouts.vali')

@section('title', 'Fingerprint Device Settings')

@section('icon', 'fa-fingerprint')

@section('content')

<style>
    .tab-content {
        padding: 20px;
        background: white;
        border-radius: 5px;
        margin-top: 20px;
    }
    .nav-tabs .nav-link {
        color: #940000;
        border-color: #940000;
    }
    .nav-tabs .nav-link.active {
        background-color: #940000;
        color: white;
        border-color: #940000;
    }
    .nav-tabs .nav-link:hover {
        border-color: #940000;
        color: #940000;
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
    .endpoint {
        background: #f5f5f5;
        padding: 15px;
        margin: 15px 0;
        border-radius: 5px;
        border-left: 4px solid #4caf50;
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
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-fingerprint"></i> Fingerprint Device Settings</h3>
                </div>
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#deviceTester" role="tab">
                                <i class="fa fa-plug"></i> Device Tester
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pushSetup" role="tab">
                                <i class="fa fa-cog"></i> Push SDK Setup Wizard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pushTest" role="tab">
                                <i class="fa fa-check-circle"></i> Push SDK Test
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#userManagement" role="tab">
                                <i class="fa fa-users"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#liveAttendance" role="tab">
                                <i class="fa fa-clock-o"></i> Live Attendance (Today)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#attendanceHistory" role="tab">
                                <i class="fa fa-calendar"></i> Attendance History
                            </a>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <!-- Device Tester Tab -->
                        <div class="tab-pane fade show active" id="deviceTester" role="tabpanel">
                            <div class="tile">
                                <h3 class="tile-title">ZKTeco Device Tester</h3>
                                <div class="alert alert-info">
                                    <strong>Default Settings:</strong> IP: 192.168.100.108, Port: 4370<br>
                                    <strong>Note:</strong> Make sure your device is connected to the same network and the IP address is correct.<br>
                                    <strong>📡 Push SDK:</strong> Configure device ADMS to push data automatically.
                                </div>

                                <form id="deviceForm">
                                    <div class="form-group">
                                        <label for="ip">Device IP Address:</label>
                                        <input type="text" id="ip" name="ip" value="192.168.100.108" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="port">Port:</label>
                                        <input type="number" id="port" name="port" value="4370" class="form-control" required>
                                    </div>
                                    <div class="btn-group btn-group-justified" role="group" style="margin-top: 15px;">
                                        <button type="button" class="btn btn-primary" onclick="testConnection()">Test Connection</button>
                                        <button type="button" class="btn btn-success" onclick="getDeviceInfo()">Get Device Info</button>
                                        <button type="button" class="btn btn-info" onclick="getAttendance()">Get Attendance</button>
                                    </div>
                                </form>

                                <div id="loading" class="text-center" style="display:none; margin-top: 20px;">
                                    <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
                                    <p>Connecting to device...</p>
                                </div>

                                <div id="result" style="margin-top: 20px; display:none;"></div>
                            </div>
                        </div>

                        <!-- Push SDK Setup Wizard Tab -->
                        <div class="tab-pane fade" id="pushSetup" role="tabpanel">
                            <h1>🚀 ZKTeco Push SDK Setup Wizard</h1>
                            <p class="text-muted">Step-by-step guide to configure your device for automatic data push</p>

                            <div class="info-box">
                                <strong>📋 What this does:</strong> Configures your ZKTeco device to automatically send user registrations and attendance records to this server via HTTP. No more manual syncing!
                            </div>

                            <div class="step">
                                <h3>
                                    <span class="step-number">1</span>
                                    Get Server Information
                                </h3>
                                <p>First, let's get your server's IP address and configuration details.</p>
                                <button class="btn btn-primary" onclick="getServerInfo()">📡 Get Server Info</button>
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
                                        <li>Check the Users page - user should appear automatically</li>
                                        <li>Punch in/out on the device using fingerprint</li>
                                        <li>Check the Attendance page - record should appear automatically</li>
                                        <li>Check <code>storage/logs/laravel.log</code> for push activity</li>
                                    </ol>
                                </div>
                                <button class="btn btn-success" onclick="checkRecentActivity()">📊 Check Recent Activity</button>
                                <div id="activityCheck" class="result"></div>
                            </div>

                            <div id="loadingSetup" class="text-center" style="display:none; margin-top: 20px;">
                                <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
                                <p>⏳ Processing...</p>
                            </div>
                        </div>

                        <!-- Push SDK Test Tab -->
                        <div class="tab-pane fade" id="pushTest" role="tabpanel">
                            <h1>📡 ZKTeco Push SDK Test</h1>
                            <p class="text-muted">Test if your server endpoints are accessible from the device</p>

                            <div class="info-box">
                                <strong>📋 Instructions:</strong>
                                <ol style="margin-left: 20px; margin-top: 10px;">
                                    <li>Click "Test Ping Endpoint" to verify server is accessible</li>
                                    <li>Check the response - should show "OK"</li>
                                    <li>Configure your device with these endpoints</li>
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

                            <div id="resultTest" class="result"></div>
                        </div>

                        <!-- User Management Tab -->
                        <div class="tab-pane fade" id="userManagement" role="tabpanel">
                            <h1>👥 User Management</h1>
                            <p class="text-muted">Register, retrieve, and delete users on the fingerprint device</p>

                            <div class="info-box">
                                <strong>📋 Instructions:</strong>
                                <ol style="margin-left: 20px; margin-top: 10px;">
                                    <li>Enter device IP and Port</li>
                                    <li>Register users: Enter Enroll ID and Name, then click Register</li>
                                    <li>Retrieve users: Click "Get Users from Device" to see all users on device</li>
                                    <li>Delete users: Select a user from the list and click Delete</li>
                                </ol>
                            </div>

                            <div class="step">
                                <h3>
                                    <span class="step-number">1</span>
                                    Device Connection Settings
                                </h3>
                                <div class="form-group">
                                    <label for="userMgmtIp">Device IP Address:</label>
                                    <input type="text" id="userMgmtIp" name="userMgmtIp" value="192.168.100.108" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="userMgmtPort">Port:</label>
                                    <input type="number" id="userMgmtPort" name="userMgmtPort" value="4370" class="form-control" required>
                                </div>
                                <button class="btn btn-primary" onclick="checkDeviceStatus()">🔧 Check Device Status</button>
                                <div id="deviceStatusResult" class="result" style="margin-top: 15px;"></div>
                            </div>

                            <div class="step">
                                <h3>
                                    <span class="step-number">2</span>
                                    Register User to Device
                                </h3>
                                <div class="form-group">
                                    <label for="registerEnrollId">Enroll ID (1-65535):</label>
                                    <input type="number" id="registerEnrollId" name="registerEnrollId" class="form-control" min="1" max="65535" required>
                                    <small class="form-text text-muted">Must be a unique number between 1 and 65535</small>
                                </div>
                                <div class="form-group">
                                    <label for="registerName">Name:</label>
                                    <input type="text" id="registerName" name="registerName" class="form-control" maxlength="24" required>
                                    <small class="form-text text-muted">Maximum 24 characters</small>
                                </div>
                                <button id="registerUserButton" class="btn btn-success" onclick="registerUserToDevice()">📝 Register User to Device</button>
                                <div id="registerResult" class="result" style="margin-top: 15px;"></div>
                            </div>

                            <div class="step">
                                <h3>
                                    <span class="step-number">3</span>
                                    Retrieve Users from Device
                                </h3>
                                <p>Get a list of all users currently registered on the device.</p>
                                <button class="btn btn-info" onclick="getUsersFromDevice()">📥 Get Users from Device</button>
                                <div id="usersListResult" class="result" style="margin-top: 15px;"></div>
                                <div id="usersTableContainer" style="margin-top: 20px; display: none;">
                                    <table class="table table-striped table-bordered" id="usersTable">
                                        <thead>
                                            <tr>
                                                <th>Enroll ID</th>
                                                <th>Name</th>
                                                <th>Role</th>
                                                <th>Card No</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="usersTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="step">
                                <h3>
                                    <span class="step-number">4</span>
                                    Delete User from Device
                                </h3>
                                <p>Select a user from the list above and click Delete, or enter Enroll ID manually.</p>
                                <div class="form-group">
                                    <label for="deleteEnrollId">Enroll ID to Delete:</label>
                                    <input type="number" id="deleteEnrollId" name="deleteEnrollId" class="form-control" min="1" max="65535">
                                </div>
                                <button class="btn btn-danger" onclick="deleteUserFromDevice()">🗑️ Delete User from Device</button>
                                <div id="deleteResult" class="result" style="margin-top: 15px;"></div>
                            </div>

                            <div id="loadingUserMgmt" class="text-center" style="display:none; margin-top: 20px;">
                                <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
                                <p>⏳ Processing...</p>
                            </div>
                        </div>

                        <!-- Live Attendance Tab -->
                        <div class="tab-pane fade" id="liveAttendance" role="tabpanel">
                            <h1>📅 Live Attendance - Today</h1>
                            <p class="text-muted">
                                View today's attendance records in near real-time. When a user places a finger on the device,
                                the first scan will be treated as <strong>Check In</strong> and the second scan as <strong>Check Out</strong> for that day.
                            </p>

                            <div class="info-box">
                                <strong>📋 How it works:</strong>
                                <ul style="margin-left: 20px; margin-top: 10px;">
                                    <li>Only <strong>today's</strong> attendance records are shown.</li>
                                    <li>Each user has at most <strong>one Check In</strong> and <strong>one Check Out</strong> per day.</li>
                                    <li>The table auto-refreshes every <strong>5 seconds</strong> when live mode is ON.</li>
                                </ul>
                            </div>

                            <div class="d-flex align-items-center mb-3" style="gap: 10px;">
                                <button id="startLiveAttendanceBtn" class="btn btn-success btn-sm" onclick="startLiveAttendance()">
                                    ▶ Start Live View
                                </button>
                                <button id="stopLiveAttendanceBtn" class="btn btn-secondary btn-sm" onclick="stopLiveAttendance()" disabled>
                                    ⏸ Stop Live View
                                </button>
                                <button class="btn btn-primary btn-sm" onclick="loadTodayAttendance(true)">
                                    🔄 Refresh Now
                                </button>
                                <span id="liveAttendanceStatus" class="text-muted small ml-2">
                                    Live view is OFF
                                </span>
                            </div>

                            <div id="liveAttendanceLoading" class="text-center" style="display:none; margin-top: 10px;">
                                <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
                                <p>⏳ Loading today's attendance...</p>
                            </div>

                            <div id="liveAttendanceContainer" style="margin-top: 15px; display:none;">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-sm mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>User Name</th>
                                                <th>UID</th>
                                                <th>User ID</th>
                                                <th>State</th>
                                                <th>Record Time</th>
                                                <th>Type</th>
                                                <th>Device IP</th>
                                            </tr>
                                        </thead>
                                        <tbody id="liveAttendanceBody">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2 text-muted small" id="liveAttendanceFooter">
                                    Showing today's attendance. Last updated: <span id="liveAttendanceUpdatedAt">-</span>
                                </div>
                            </div>

                            <div id="liveAttendanceEmpty" class="result info" style="display:none; margin-top: 15px;">
                                No attendance records found for today yet. Place a finger on the device to start recording.
                            </div>
                        </div>

                        <!-- Attendance History Tab -->
                        <div class="tab-pane fade" id="attendanceHistory" role="tabpanel">
                            <h1>📚 Attendance History</h1>
                            <p class="text-muted">
                                View attendance records for previous days. Select a date to see Check In and Check Out times per student.
                            </p>

                            <div class="info-box">
                                <strong>📋 How it works:</strong>
                                <ul style="margin-left: 20px; margin-top: 10px;">
                                    <li>Data is loaded from <code>student_fingerprint_attendance</code> (synced from device via Push SDK or sync).</li>
                                    <li>Each row shows at most one <strong>Check In</strong> and one <strong>Check Out</strong> per student per day.</li>
                                    <li>Use the date picker to move between different days.</li>
                                </ul>
                            </div>

                            <div class="d-flex align-items-end mb-3" style="gap: 10px; flex-wrap: wrap;">
                                <div class="form-group mb-2">
                                    <label for="historyDate"><strong>Select Date:</strong></label>
                                    <input type="date" id="historyDate" class="form-control" value="{{ now()->toDateString() }}">
                                </div>
                                <div class="mb-2">
                                    <button class="btn btn-primary" onclick="loadHistoryAttendance(true)">
                                        🔍 Load Records
                                    </button>
                                </div>
                                <div class="mb-2">
                                    <span id="historyAttendanceStatus" class="text-muted small ml-2">
                                        Select a date and click "Load Records"
                                    </span>
                                </div>
                            </div>

                            <div id="historyAttendanceLoading" class="text-center" style="display:none; margin-top: 10px;">
                                <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
                                <p>⏳ Loading attendance...</p>
                            </div>

                            <div id="historyAttendanceContainer" style="margin-top: 15px; display:none;">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-sm mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>User Name</th>
                                                <th>UID</th>
                                                <th>User ID</th>
                                                <th>State</th>
                                                <th>Record Time</th>
                                                <th>Type</th>
                                                <th>Device IP</th>
                                            </tr>
                                        </thead>
                                        <tbody id="historyAttendanceBody">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2 text-muted small" id="historyAttendanceFooter">
                                    Showing attendance for <span id="historyAttendanceDate">-</span>. Total records: <span id="historyAttendanceCount">0</span>
                                </div>
                            </div>

                            <div id="historyAttendanceEmpty" class="result info" style="display:none; margin-top: 15px;">
                                No attendance records found for the selected date.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

@endsection

@push('scripts')
<script>
    // Device Tester Functions
    function showLoading(show) {
        var el = document.getElementById('loading');
        if (!el) return;
        el.style.display = show ? 'block' : 'none';
    }

    function showResult(success, message, data = null) {
        const resultDiv = document.getElementById('result');
        if (!resultDiv) return;
        resultDiv.style.display = 'block';
        
        let content = '<div class="alert ' + (success ? 'alert-success' : 'alert-danger') + '">';
        content += '<strong>' + (success ? '✓ Success' : '✗ Error') + '</strong><br>';
        content += message;
        content += '</div>';
        
        if (data) {
            content += '<pre class="small" style="background:#f8f9fa; padding:10px; border-radius:4px; margin-top:10px;">' +
                JSON.stringify(data, null, 2) + '</pre>';
        }
        
        resultDiv.innerHTML = content;
    }

    function getFormData() {
        return {
            ip: document.getElementById('ip').value,
            port: document.getElementById('port').value
        };
    }

    function getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    async function testConnection() {
        showLoading(true);
        document.getElementById('result').classList.remove('show');
        
        try {
            const formData = getFormData();
            const response = await fetch('/zkteco/test-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            showResult(result.success, result.message, result.device_info || result);
        } catch (error) {
            showResult(false, 'Error: ' + error.message);
        } finally {
            showLoading(false);
        }
    }

    async function getDeviceInfo() {
        showLoading(true);
        document.getElementById('result').classList.remove('show');
        
        try {
            const formData = getFormData();
            const response = await fetch('/zkteco/device-info', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            showResult(result.success, result.success ? 'Device information retrieved successfully!' : result.message, result);
        } catch (error) {
            showResult(false, 'Error: ' + error.message);
        } finally {
            showLoading(false);
        }
    }

    async function getAttendance() {
        showLoading(true);
        document.getElementById('result').classList.remove('show');
        
        try {
            const formData = getFormData();
            const response = await fetch('/zkteco/attendance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            const message = result.success 
                ? `Retrieved ${result.count || 0} attendance record(s)` 
                : result.message;
            showResult(result.success, message, result);
        } catch (error) {
            showResult(false, 'Error: ' + error.message);
        } finally {
            showLoading(false);
        }
    }

    // Push SDK Setup Wizard Functions
    let serverInfo = null;

    function showLoadingSetup(show) {
        document.getElementById('loadingSetup').style.display = show ? 'block' : 'none';
    }

    function showResultSetup(elementId, message, type = 'info') {
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
        showLoadingSetup(true);
        try {
            const response = await fetch('{{ route("zkteco.setup.server-info") }}');
            const data = await response.json();
            
            if (data.success) {
                serverInfo = data;
                
                let html = '<div class="info-box">';
                html += '<strong>✓ Server Information:</strong><br>';
                html += `Server IP: <code>${data.server.ip}</code><br>`;
                html += `Server Host: <code>${data.server.host}</code><br>`;
                html += `Server Port: <code>${data.server.port}</code><br>`;
                html += `Protocol: <code>${data.server.protocol}</code><br>`;
                html += `Ping Endpoint: <code>${data.server.ping_endpoint}</code><br>`;
                html += `Data Endpoint: <code>${data.server.data_endpoint}</code>`;
                html += '</div>';
                
                showResultSetup('serverInfo', html, 'success');
                
                document.getElementById('deviceConfig').style.display = 'block';
                document.getElementById('configServerIp').textContent = data.device_config.server_ip;
                document.getElementById('configServerPort').textContent = data.device_config.server_port;
            } else {
                showResultSetup('serverInfo', '✗ Failed to get server information', 'error');
            }
        } catch (error) {
            showResultSetup('serverInfo', '✗ Error: ' + error.message, 'error');
        } finally {
            showLoadingSetup(false);
        }
    }

    async function testDeviceConnection() {
        showLoadingSetup(true);
        try {
            const response = await fetch('{{ route("zkteco.setup.test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
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
                showResultSetup('connectionTest', html, 'success');
            } else {
                html = '<div class="info-box">';
                html += '<strong>⚠ Cannot ping device</strong><br>';
                html += 'This is usually normal if:<br>';
                html += '• Ping is disabled on device<br>';
                html += '• Device is on different network<br>';
                html += '• Firewall is blocking ICMP<br><br>';
                html += 'This does NOT mean push won\'t work. Try configuring the device anyway.';
                html += '</div>';
                showResultSetup('connectionTest', html, 'info');
            }
        } catch (error) {
            showResultSetup('connectionTest', '✗ Error: ' + error.message, 'error');
        } finally {
            showLoadingSetup(false);
        }
    }

    async function importUsersFromDevice() {
        if (!confirm('This will import all users from the device (192.168.100.108) into the database. Continue?')) {
            return;
        }
        
        showLoadingSetup(true);
        try {
            const response = await fetch('{{ route("zkteco.setup.import-users") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
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
                html += `Total Processed: <code>${data.data.verified || 0}</code> user(s)<br>`;
                if (data.data.created > 0) {
                    html += `✓ Created: <code>${data.data.created}</code> new user(s)<br>`;
                }
                html += '</div>';
                showResultSetup('importResult', html, 'success');
                setTimeout(() => checkRecentActivity(), 1000);
            } else {
                html = '<div class="info-box">';
                html += '<strong>✗ Import Failed</strong><br>';
                html += `Error: ${data.message}`;
                html += '</div>';
                showResultSetup('importResult', html, 'error');
            }
        } catch (error) {
            showResultSetup('importResult', '✗ Error: ' + error.message, 'error');
        } finally {
            showLoadingSetup(false);
        }
    }

    async function checkRecentActivity() {
        showLoadingSetup(true);
        try {
            const response = await fetch('{{ route("zkteco.setup.check-activity") }}');
            const data = await response.json();
            
            let html = '<div class="info-box">';
            html += `<strong>📊 Recent Activity:</strong><br>`;
            html += `Total Users: <code>${data.users_count || 0}</code><br>`;
            html += `Total Attendance Records: <code>${data.attendances_count || 0}</code><br>`;
            html += `Recent Users (last 5): <code>${data.recent_users || 0}</code><br>`;
            html += `Recent Attendances (last 5): <code>${data.recent_attendances || 0}</code>`;
            html += '</div>';
            
            showResultSetup('activityCheck', html, 'success');
        } catch (error) {
            showResultSetup('activityCheck', '✗ Error: ' + error.message, 'error');
        } finally {
            showLoadingSetup(false);
        }
    }

    // Push SDK Test Functions
    function showResultTest(message, isSuccess) {
        const result = document.getElementById('resultTest');
        result.className = 'result show ' + (isSuccess ? 'success' : 'error');
        result.innerHTML = '<pre>' + message + '</pre>';
    }

    async function testPing() {
        try {
            const response = await fetch('{{ url("/iclock/getrequest") }}?SN=TEST123');
            const text = await response.text();
            showResultTest(`Status: ${response.status}\nResponse: ${text}\n\n✅ Endpoint is working!`, true);
        } catch (error) {
            showResultTest(`Error: ${error.message}\n\n❌ Endpoint failed. Check server logs.`, false);
        }
    }

    async function testUserRegistration() {
        try {
            const data = 'PIN=999\tName=Test User\tPri=0\tPasswd=\tCard=\tGrp=1\tTZ=0000000100000000\tVerify=0\tViceCard=\tStartDatetime=0\tEndDatetime=0\n';
            
            const response = await fetch('{{ url("/iclock/cdata") }}?SN=TEST123&table=OPERLOG&Stamp=9999', {
                method: 'POST',
                headers: {
                    'Content-Type': 'text/plain',
                },
                body: data
            });
            const text = await response.text();
            showResultTest(`Status: ${response.status}\nResponse: ${text}\n\n✅ User registration endpoint is working!\n\nCheck Users page to see if test user was created.`, true);
        } catch (error) {
            showResultTest(`Error: ${error.message}\n\n❌ Endpoint failed. Check server logs.`, false);
        }
    }

    async function testAttendance() {
        try {
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
            showResultTest(`Status: ${response.status}\nResponse: ${text}\n\n✅ Attendance endpoint is working!\n\nCheck Attendance page to see if test record was created.`, true);
        } catch (error) {
            showResultTest(`Error: ${error.message}\n\n❌ Endpoint failed. Check server logs.`, false);
        }
    }

    // Auto-load server info on Push Setup tab load
    document.querySelector('a[href="#pushSetup"]').addEventListener('shown.bs.tab', function() {
        if (!serverInfo) {
            getServerInfo();
        }
    });

    // User Management Functions
    function showLoadingUserMgmt(show) {
        document.getElementById('loadingUserMgmt').style.display = show ? 'block' : 'none';
    }

    function showResultUserMgmt(elementId, message, type = 'info') {
        const element = document.getElementById(elementId);
        element.className = 'result show ' + type;
        element.innerHTML = message;
    }

    function getUserMgmtFormData() {
        return {
            ip: document.getElementById('userMgmtIp').value,
            port: parseInt(document.getElementById('userMgmtPort').value)
        };
    }

    async function checkDeviceStatus() {
        showLoadingUserMgmt(true);
        try {
            const formData = getUserMgmtFormData();
            const response = await fetch('{{ route("zkteco.user.check-device-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                body: JSON.stringify(formData)
            });
            const data = await response.json();
            
            let html = '';
            if (data.success) {
                const status = data.status;
                html = '<div class="info-box">';
                html += '<strong>Device Status:</strong><br>';
                html += `Connection: ${status.connection ? '✓ Connected' : '✗ Failed'}<br>`;
                html += `Authentication: ${status.authentication ? '✓ Authenticated' : '✗ Failed'}<br>`;
                html += `Can Read Users: ${status.can_read_users ? '✓ Yes' : '✗ No'}<br>`;
                html += `Users Count: <code>${status.users_count}</code><br>`;
                if (status.device_name) {
                    html += `Device Name: <code>${status.device_name}</code><br>`;
                }
                if (status.issues && status.issues.length > 0) {
                    html += '<br><strong>⚠️ Issues:</strong><ul>';
                    status.issues.forEach(issue => {
                        html += `<li>${issue}</li>`;
                    });
                    html += '</ul>';
                }
                if (status.recommendations && status.recommendations.length > 0) {
                    html += '<br><strong>💡 Recommendations:</strong><ul>';
                    status.recommendations.forEach(rec => {
                        html += `<li>${rec}</li>`;
                    });
                    html += '</ul>';
                }
                html += '</div>';
                showResultUserMgmt('deviceStatusResult', html, data.ready_for_registration ? 'success' : 'info');
            } else {
                showResultUserMgmt('deviceStatusResult', '✗ Error: ' + data.message, 'error');
            }
        } catch (error) {
            showResultUserMgmt('deviceStatusResult', '✗ Error: ' + error.message, 'error');
        } finally {
            showLoadingUserMgmt(false);
        }
    }

    async function registerUserToDevice() {
        const enrollId = document.getElementById('registerEnrollId').value;
        const name = document.getElementById('registerName').value;
        const button = document.getElementById('registerUserButton');
        const originalButtonHtml = button ? button.innerHTML : null;
        
        if (!enrollId || !name) {
            showResultUserMgmt('registerResult', '✗ Please enter both Enroll ID and Name', 'error');
            return;
        }

        if (enrollId < 1 || enrollId > 65535) {
            showResultUserMgmt('registerResult', '✗ Enroll ID must be between 1 and 65535', 'error');
            return;
        }

        // Show loading state on button and global loader
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Registering...';
        }
        showLoadingUserMgmt(true);
        try {
            const formData = getUserMgmtFormData();
            formData.enroll_id = enrollId;
            formData.name = name;
            
            const response = await fetch('{{ route("zkteco.user.register") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                body: JSON.stringify(formData)
            });
            const data = await response.json();
            
            if (data.success) {
                showResultUserMgmt('registerResult', '✓ ' + data.message, 'success');
                // Clear form
                document.getElementById('registerEnrollId').value = '';
                document.getElementById('registerName').value = '';
                // Refresh users list
                setTimeout(() => getUsersFromDevice(), 1000);
            } else {
                let html = '<div class="info-box">';
                html += '<strong>✗ Registration Failed</strong><br>';
                html += `Error: ${data.message}`;
                if (data.troubleshooting) {
                    html += '<br><br><strong>Troubleshooting:</strong><br>';
                    html += '<pre style="white-space: pre-wrap; background: #f8f9fa; padding: 10px; border-radius: 4px;">' + data.troubleshooting + '</pre>';
                }
                html += '</div>';
                showResultUserMgmt('registerResult', html, 'error');
            }
        } catch (error) {
            showResultUserMgmt('registerResult', '✗ Error: ' + error.message, 'error');
        } finally {
            // Restore button state and hide loader
            if (button && originalButtonHtml !== null) {
                button.disabled = false;
                button.innerHTML = originalButtonHtml;
            }
            showLoadingUserMgmt(false);
        }
    }

    async function getUsersFromDevice() {
        showLoadingUserMgmt(true);
        try {
            const formData = getUserMgmtFormData();
            const response = await fetch('{{ route("zkteco.user.list-device-users") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                body: JSON.stringify(formData)
            });
            const data = await response.json();
            
            if (data.success) {
                let html = '<div class="info-box">';
                html += `<strong>✓ ${data.message}</strong><br>`;
                html += `Total Users: <code>${data.count}</code>`;
                html += '</div>';
                showResultUserMgmt('usersListResult', html, 'success');
                
                // Display users table
                if (data.users && data.users.length > 0) {
                    const tbody = document.getElementById('usersTableBody');
                    tbody.innerHTML = '';
                    data.users.forEach(user => {
                        const row = tbody.insertRow();
                        row.insertCell(0).textContent = user.uid || user.user_id || 'N/A';
                        row.insertCell(1).textContent = user.name || 'N/A';
                        row.insertCell(2).textContent = user.role || 'N/A';
                        row.insertCell(3).textContent = user.card_no || 'N/A';
                        const actionsCell = row.insertCell(4);
                        const enrollId = user.uid || user.user_id;
                        actionsCell.innerHTML = `<button class="btn btn-sm btn-danger" onclick="deleteUserById(${enrollId})">Delete</button>`;
                    });
                    document.getElementById('usersTableContainer').style.display = 'block';
                } else {
                    document.getElementById('usersTableContainer').style.display = 'none';
                }
            } else {
                showResultUserMgmt('usersListResult', '✗ Error: ' + data.message, 'error');
                document.getElementById('usersTableContainer').style.display = 'none';
            }
        } catch (error) {
            showResultUserMgmt('usersListResult', '✗ Error: ' + error.message, 'error');
            document.getElementById('usersTableContainer').style.display = 'none';
        } finally {
            showLoadingUserMgmt(false);
        }
    }

    function deleteUserById(enrollId) {
        document.getElementById('deleteEnrollId').value = enrollId;
        deleteUserFromDevice();
    }

    async function deleteUserFromDevice() {
        const enrollId = document.getElementById('deleteEnrollId').value;
        
        if (!enrollId) {
            showResultUserMgmt('deleteResult', '✗ Please enter Enroll ID', 'error');
            return;
        }

        if (!confirm(`Are you sure you want to delete user with Enroll ID ${enrollId} from the device?`)) {
            return;
        }

        showLoadingUserMgmt(true);
        try {
            const formData = getUserMgmtFormData();
            formData.enroll_id = enrollId;
            
            const response = await fetch('{{ route("zkteco.user.delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                body: JSON.stringify(formData)
            });
            const data = await response.json();
            
            if (data.success) {
                showResultUserMgmt('deleteResult', '✓ ' + data.message, 'success');
                // Clear input
                document.getElementById('deleteEnrollId').value = '';
                // Refresh users list
                setTimeout(() => getUsersFromDevice(), 1000);
            } else {
                showResultUserMgmt('deleteResult', '✗ Error: ' + data.message, 'error');
            }
        } catch (error) {
            showResultUserMgmt('deleteResult', '✗ Error: ' + error.message, 'error');
        } finally {
            showLoadingUserMgmt(false);
        }
    }

    // Live Attendance (Today) Functions
    let liveAttendanceInterval = null;

    function setLiveAttendanceLoading(show) {
        const loader = document.getElementById('liveAttendanceLoading');
        if (loader) {
            loader.style.display = show ? 'block' : 'none';
        }
    }

    function updateLiveAttendanceStatus(text) {
        const el = document.getElementById('liveAttendanceStatus');
        if (el) {
            el.textContent = text;
        }
    }

    function renderLiveAttendance(records, date) {
        const container = document.getElementById('liveAttendanceContainer');
        const tbody = document.getElementById('liveAttendanceBody');
        const empty = document.getElementById('liveAttendanceEmpty');
        const updatedAt = document.getElementById('liveAttendanceUpdatedAt');

        if (!tbody || !container || !empty) return;

        tbody.innerHTML = '';

        if (!records || records.length === 0) {
            container.style.display = 'none';
            empty.style.display = 'block';
        } else {
            empty.style.display = 'none';
            container.style.display = 'block';

            records.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${row.user_name ?? row.user_id ?? '-'}</td>
                    <td>${row.uid ?? '-'}</td>
                    <td>${row.user_id ?? '-'}</td>
                    <td>${row.state ?? '-'}</td>
                    <td>${row.record_time ?? '-'}</td>
                    <td>${row.type ?? '-'}</td>
                    <td>${row.device_ip ?? '-'}</td>
                `.trim();
                tbody.appendChild(tr);
            });
        }

        if (updatedAt) {
            const now = new Date();
            updatedAt.textContent = `${date} ${now.toLocaleTimeString()}`;
        }
    }

    async function loadTodayAttendance(showLoader = false) {
        if (showLoader) {
            setLiveAttendanceLoading(true);
        }

        try {
            const formData = getFormData();
            const response = await fetch('{{ route("zkteco.attendance.today") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                renderLiveAttendance(data.attendance || [], data.date || '');
            } else {
                renderLiveAttendance([], '');
                updateLiveAttendanceStatus('Error: ' + (data.message || 'Failed to load attendance'));
            }
        } catch (error) {
            renderLiveAttendance([], '');
            updateLiveAttendanceStatus('Error: ' + error.message);
        } finally {
            if (showLoader) {
                setLiveAttendanceLoading(false);
            }
        }
    }

    function startLiveAttendance() {
        if (liveAttendanceInterval) {
            return;
        }

        const startBtn = document.getElementById('startLiveAttendanceBtn');
        const stopBtn = document.getElementById('stopLiveAttendanceBtn');

        if (startBtn) startBtn.disabled = true;
        if (stopBtn) stopBtn.disabled = false;

        updateLiveAttendanceStatus('Live view is ON (refreshing every 5 seconds)');

        // Load immediately, then every 5 seconds
        loadTodayAttendance(true);
        liveAttendanceInterval = setInterval(() => {
            loadTodayAttendance(false);
        }, 5000);
    }

    function stopLiveAttendance() {
        if (liveAttendanceInterval) {
            clearInterval(liveAttendanceInterval);
            liveAttendanceInterval = null;
        }

        const startBtn = document.getElementById('startLiveAttendanceBtn');
        const stopBtn = document.getElementById('stopLiveAttendanceBtn');

        if (startBtn) startBtn.disabled = false;
        if (stopBtn) stopBtn.disabled = true;

        updateLiveAttendanceStatus('Live view is OFF');
    }

    // Attendance History Functions
    function setHistoryAttendanceLoading(show) {
        const loader = document.getElementById('historyAttendanceLoading');
        if (loader) {
            loader.style.display = show ? 'block' : 'none';
        }
    }

    function updateHistoryAttendanceStatus(text) {
        const el = document.getElementById('historyAttendanceStatus');
        if (el) {
            el.textContent = text;
        }
    }

    function renderHistoryAttendance(records, date, count) {
        const container = document.getElementById('historyAttendanceContainer');
        const tbody = document.getElementById('historyAttendanceBody');
        const empty = document.getElementById('historyAttendanceEmpty');
        const dateSpan = document.getElementById('historyAttendanceDate');
        const countSpan = document.getElementById('historyAttendanceCount');

        if (!tbody || !container || !empty) return;

        tbody.innerHTML = '';

        if (!records || records.length === 0) {
            container.style.display = 'none';
            empty.style.display = 'block';
        } else {
            empty.style.display = 'none';
            container.style.display = 'block';

            records.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${row.user_name ?? row.user_id ?? '-'}</td>
                    <td>${row.uid ?? '-'}</td>
                    <td>${row.user_id ?? '-'}</td>
                    <td>${row.state ?? '-'}</td>
                    <td>${row.record_time ?? '-'}</td>
                    <td>${row.type ?? '-'}</td>
                    <td>${row.device_ip ?? '-'}</td>
                `.trim();
                tbody.appendChild(tr);
            });
        }

        if (dateSpan) {
            dateSpan.textContent = date || '-';
        }
        if (countSpan) {
            countSpan.textContent = count ?? records.length;
        }
    }

    async function loadHistoryAttendance(showLoader = false) {
        const dateInput = document.getElementById('historyDate');
        const selectedDate = dateInput ? dateInput.value : null;

        if (!selectedDate) {
            updateHistoryAttendanceStatus('Please select a date first');
            renderHistoryAttendance([], '', 0);
            return;
        }

        if (showLoader) {
            setHistoryAttendanceLoading(true);
        }

        try {
            const formData = getFormData();
            formData.date = selectedDate;
            
            const response = await fetch('{{ route("zkteco.attendance.by-date") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                renderHistoryAttendance(data.attendance || [], data.date || selectedDate, data.count || 0);
                updateHistoryAttendanceStatus('Loaded attendance for ' + (data.date || selectedDate));
            } else {
                renderHistoryAttendance([], selectedDate, 0);
                updateHistoryAttendanceStatus('Error: ' + (data.message || 'Failed to load attendance'));
            }
        } catch (error) {
            renderHistoryAttendance([], selectedDate, 0);
            updateHistoryAttendanceStatus('Error: ' + error.message);
        } finally {
            if (showLoader) {
                setHistoryAttendanceLoading(false);
            }
        }
    }
</script>
@endpush
