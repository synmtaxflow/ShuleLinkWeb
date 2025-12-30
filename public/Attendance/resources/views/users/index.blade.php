@extends('layouts.vali')

@section('title', 'Users')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h1>👥 Users Management</h1>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ route('dashboard') }}" class="btn" style="background: #667eea; color: white;">📊 Dashboard</a>
                <a href="{{ route('users.create') }}" class="btn btn-primary">➕ Add New User</a>
                <button onclick="syncUsersToDevice()" class="btn" style="background: #28a745; color: white;">📤 Register to Device</button>
                <button onclick="syncUsersFromDevice()" class="btn" style="background: #17a2b8; color: white;">📥 Sync from Device</button>
                <button onclick="listDeviceUsers()" class="btn" style="background: #6c757d; color: white;">📋 List Device Users</button>
                <button onclick="diagnoseDevice()" class="btn" style="background: #17a2b8; color: white;">🔧 Diagnose</button>
                <button onclick="testRegistration()" class="btn" style="background: #ff9800; color: white;">🧪 Test Registration</button>
                <button onclick="deleteAllUsers()" class="btn" style="background: #dc3545; color: white;">🗑️ Delete All (DB)</button>
                <button onclick="deleteAllUsersFromDevice()" class="btn" style="background: #c82333; color: white;">🗑️ Delete All from Device</button>
            </div>
        </div>
        
        @if($users->count() > 0)
            <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin-bottom: 1rem; border-radius: 4px;">
                <strong>📊 Total Users:</strong> {{ $users->total() }} | 
                <strong>Registered on Device:</strong> {{ $users->where('registered_on_device', true)->count() }} |
                <strong>Not Registered:</strong> {{ $users->where('registered_on_device', false)->count() }}
            </div>
        @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Enroll ID</th>
                <th>Device Status</th>
                <th>Attendance Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->enroll_id ?? 'N/A' }}</td>
                    <td>
                        @if($user->registered_on_device)
                            <span style="color: #28a745;">✓ Registered</span>
                        @else
                            <span style="color: #dc3545;">Not Registered</span>
                        @endif
                    </td>
                    <td>{{ $user->attendances_count ?? 0 }}</td>
                    <td>
                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-primary">View</a>
                        @if(!$user->registered_on_device)
                            <button onclick="registerUser({{ $user->id }})" class="btn btn-sm btn-success">Register to Device</button>
                            <button onclick="markAsRegistered({{ $user->id }})" class="btn btn-sm" style="background: #6c757d; color: white;" title="Mark as registered if you registered manually on device">Mark as Registered</button>
                        @else
                            <button onclick="checkFingerprints({{ $user->id }})" class="btn btn-sm" style="background: #17a2b8; color: white;">Check Fingerprints</button>
                            <button onclick="showEnrollInstructions({{ $user->id }}, '{{ $user->enroll_id }}')" class="btn btn-sm" style="background: #ffc107; color: #000;">Enroll Fingerprint</button>
                            <button onclick="removeUser({{ $user->id }})" class="btn btn-sm btn-danger">Remove from Device</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">No users found. <a href="{{ route('users.create') }}">Create one</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 2rem;">
        {{ $users->links() }}
    </div>
            </div>
        </div>
    </div>

<script>
function diagnoseDevice() {
    const ip = prompt('Enter device IP:', '192.168.100.127');
    if (!ip) return;
    
    const port = prompt('Enter device port:', '4370');
    if (!port) return;

    fetch('/users/diagnose-device', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const diag = data.diagnostics;
            let message = '🔍 DEVICE DIAGNOSTICS\n\n';
            message += `Connection: ${diag.connection ? '✓ OK' : '✗ FAILED'}\n`;
            message += `Device Enabled: ${diag.device_enabled ? '✓ OK' : '✗ FAILED'}\n`;
            message += `Can Get Users: ${diag.can_get_users ? '✓ OK' : '✗ FAILED'}\n`;
            message += `Users Count: ${diag.users_count}\n`;
            message += `Can Get Time: ${diag.can_get_time ? '✓ OK' : '✗ FAILED'}\n`;
            if (diag.device_time) {
                message += `Device Time: ${diag.device_time}\n`;
            }
            if (diag.errors && diag.errors.length > 0) {
                message += `\nErrors:\n${diag.errors.join('\n')}`;
            }
            console.log('Full Diagnostics:', diag);
            alert(message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function diagnoseSetUser() {
    const ip = prompt('Enter device IP:', '192.168.100.127');
    if (!ip) return;
    
    const port = prompt('Enter device port:', '4370');
    if (!port) return;

    alert('Running comprehensive setUser diagnostic... This may take 10-15 seconds.');

    fetch('/users/diagnose-setuser', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const diag = data.diagnostics;
            let message = '🔍 SETUSER DIAGNOSTIC RESULTS\n\n';
            message += data.summary + '\n\n';
            
            if (diag.device_info && Object.keys(diag.device_info).length > 0) {
                message += 'Device Info:\n';
                if (diag.device_info.name) message += `  Name: ${diag.device_info.name}\n`;
                if (diag.device_info.serial) message += `  Serial: ${diag.device_info.serial}\n`;
                if (diag.device_info.version) message += `  Version: ${diag.device_info.version}\n`;
            }
            
            if (diag.raw_setuser_test) {
                message += '\nsetUser Test Results:\n';
                message += `  Command Result: ${diag.raw_setuser_test.success ? 'Got Response' : 'Failed'}\n`;
                if (diag.raw_setuser_test.response_code !== undefined && diag.raw_setuser_test.response_code !== null) {
                    message += `  Device Response Code: ${diag.raw_setuser_test.response_code_name || diag.raw_setuser_test.response_code}\n`;
                    if (diag.raw_setuser_test.response_hex) {
                        message += `  Response Hex: ${diag.raw_setuser_test.response_hex}\n`;
                    }
                    if (diag.raw_setuser_test.response_analysis && diag.raw_setuser_test.response_analysis.length > 0) {
                        message += `  Response Analysis:\n`;
                        diag.raw_setuser_test.response_analysis.forEach(line => {
                            message += `    - ${line}\n`;
                        });
                    }
                    if (diag.raw_setuser_test.response_length) {
                        message += `  Response Length: ${diag.raw_setuser_test.response_length} bytes\n`;
                    }
                }
                if (diag.raw_setuser_test.user_added !== undefined) {
                    message += `  User Actually Added: ${diag.raw_setuser_test.user_added ? 'YES ✓' : 'NO ✗'}\n`;
                }
                if (diag.raw_setuser_test.users_before !== undefined && diag.raw_setuser_test.users_after !== undefined) {
                    message += `  Users: ${diag.raw_setuser_test.users_before} → ${diag.raw_setuser_test.users_after}\n`;
                }
                if (diag.raw_setuser_test.error) {
                    message += `  Error: ${diag.raw_setuser_test.error}\n`;
                }
            }
            
            if (diag.errors && diag.errors.length > 0) {
                message += `\nErrors:\n${diag.errors.join('\n')}`;
            }
            
            message += '\n\nCheck browser console for full JSON data.';
            console.log('Full Diagnostic Data:', data);
            alert(message);
        } else {
            alert('✗ Error: ' + data.message);
            console.error('Diagnostic Error:', data);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        console.error('Diagnostic Error:', error);
    });
}

function deleteAllUsers() {
    if (!confirm('⚠️ WARNING: This will delete ALL users from the database!\n\nAre you sure you want to continue?')) {
        return;
    }
    
    if (!confirm('⚠️ FINAL WARNING: This action cannot be undone!\n\nAll users and their data will be permanently deleted.\n\nClick OK to proceed or Cancel to abort.')) {
        return;
    }

    fetch('/users/delete-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ ' + data.message);
            location.reload();
        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('✗ Error: ' + error.message);
    });
}

function deleteAllUsersFromDevice() {
    if (!confirm('⚠️ WARNING: This will delete ALL users from the ZKTeco device!\n\nThis will remove all users from the device but keep them in the database.\n\nAre you sure you want to continue?')) {
        return;
    }
    
    const useDefault = confirm('Delete all users from device (192.168.100.100:4370)?\n\nClick OK to use defaults, or Cancel to enter custom IP/port.');
    
    let ip = '192.168.100.100';
    let port = '4370';
    
    if (!useDefault) {
        ip = prompt('Enter device IP:', '192.168.100.100');
        if (!ip) return;
        
        port = prompt('Enter device port:', '4370');
        if (!port) return;
    }

    if (!confirm('⚠️ FINAL WARNING: This will remove ALL users from the device!\n\nDevice IP: ' + ip + ':' + port + '\n\nClick OK to proceed or Cancel to abort.')) {
        return;
    }

    fetch('/users/delete-all-device', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = '✓ ' + data.message;
            if (data.data && data.data.errors && data.data.errors.length > 0) {
                message += '\n\nErrors:\n' + data.data.errors.slice(0, 5).join('\n');
                if (data.data.errors.length > 5) {
                    message += '\n... and ' + (data.data.errors.length - 5) + ' more errors';
                }
            }
            alert(message);
            location.reload();
        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('✗ Error: ' + error.message);
    });
}

function syncUsersToDevice() {
    // Use default IP/port from config, but allow user to override
    const useDefault = confirm('Register all unregistered users to device (192.168.100.108:4370)?\n\nClick OK to use defaults, or Cancel to enter custom IP/port.');
    
    let ip = '192.168.100.108';
    let port = '4370';
    
    if (!useDefault) {
        ip = prompt('Enter device IP:', '192.168.100.108');
        if (!ip) return;
        
        port = prompt('Enter device port:', '4370');
        if (!port) return;
    }

    if (!confirm('This will register all users that are NOT registered on the device.\n\nContinue?')) {
        return;
    }

    // Show loading indicator
    const loadingMsg = document.createElement('div');
    loadingMsg.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 9999; text-align: center;';
    loadingMsg.innerHTML = '<p>⏳ Registering users to device...</p><p style="font-size: 0.9em; color: #666;">This may take 1-2 minutes. Please wait...</p>';
    document.body.appendChild(loadingMsg);

    // Create AbortController for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 180000); // 3 minute timeout

    fetch('/users/sync-to-device', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port }),
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (document.body.contains(loadingMsg)) {
            document.body.removeChild(loadingMsg);
        }
        
        if (data.success) {
            let message = '✓ ' + data.message;
            if (data.data && data.data.errors && data.data.errors.length > 0) {
                message += '\n\n⚠️ Errors:\n';
                data.data.errors.slice(0, 10).forEach(error => {
                    message += '  - ' + error + '\n';
                });
                if (data.data.errors.length > 10) {
                    message += `  ... and ${data.data.errors.length - 10} more errors.\n`;
                }
            }
            alert(message);
            location.reload();
        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(error => {
        if (document.body.contains(loadingMsg)) {
            document.body.removeChild(loadingMsg);
        }
        
        let errorMessage = '✗ Error registering users to device\n\n';
        
        if (error.name === 'AbortError') {
            errorMessage += 'Request timed out after 3 minutes.\n\n';
            errorMessage += 'Possible causes:\n';
            errorMessage += '• Device is not responding\n';
            errorMessage += '• Network connection issue\n';
            errorMessage += '• Device IP address is incorrect\n';
            errorMessage += '\nPlease check:\n';
            errorMessage += '1. Device is powered on and connected to network\n';
            errorMessage += '2. IP address is correct (192.168.100.108)\n';
            errorMessage += '3. Device is not busy with another operation\n';
            errorMessage += '4. Check `storage/logs/laravel.log` for server-side errors.';
        } else if (error.message.includes('Server error: 500')) {
            errorMessage += 'A server-side error occurred. This usually indicates a problem with the application\'s backend logic or its ability to communicate with the device.\n\n';
            errorMessage += 'Please check `storage/logs/laravel.log` for detailed error messages and stack traces.';
        } else if (error.message.includes('Server error')) {
            errorMessage += `A server error occurred: ${error.message}.\n\n`;
            errorMessage += 'Please check `storage/logs/laravel.log` for more details.';
        } else {
            errorMessage += `An unexpected error occurred: ${error.message}.\n\n`;
            errorMessage += 'Please check device connection, IP address, and `storage/logs/laravel.log`.';
        }
        
        alert(errorMessage);
        console.error('Register to device error:', error);
    });
}

function syncUsersFromDevice() {
    // Use default IP/port from config, but allow user to override
    const useDefault = confirm('Use default device settings (192.168.100.108:4370)?\n\nClick OK to use defaults, or Cancel to enter custom IP/port.');
    
    let ip = '192.168.100.108';
    let port = '4370';
    
    if (!useDefault) {
        ip = prompt('Enter device IP:', '192.168.100.108');
        if (!ip) return;
        
        port = prompt('Enter device port:', '4370');
        if (!port) return;
    }

    // Show loading indicator
    const loadingMsg = document.createElement('div');
    loadingMsg.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 9999; text-align: center;';
    loadingMsg.innerHTML = '<p>⏳ Syncing users from device...</p><p style="font-size: 0.9em; color: #666;">This may take 30-60 seconds. Please wait...</p>';
    document.body.appendChild(loadingMsg);

    // Create AbortController for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 90000); // 90 second timeout

    fetch('/users/sync-from-device', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port }),
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (document.body.contains(loadingMsg)) {
            document.body.removeChild(loadingMsg);
        }
        
        if (data.success) {
            let message = '✓ ' + data.message;
            if (data.data && data.data.device_users_not_in_db && data.data.device_users_not_in_db.length > 0) {
                message += '\n\n⚠️ Device users not found in database:\n';
                data.data.device_users_not_in_db.forEach(user => {
                    message += `  - Enroll ID: ${user.enroll_id}, Name: ${user.name}\n`;
                });
                message += '\nThese users exist on the device but not in the database.';
            }
            alert(message);
            location.reload();
        } else {
            let errorMsg = '✗ Error syncing users from device\n\n';
            errorMsg += data.message || 'Unknown error occurred';
            
            if (data.error_details && data.error_details.file) {
                errorMsg += '\n\nTechnical details:\n';
                errorMsg += 'File: ' + data.error_details.file;
                if (data.error_details.line) {
                    errorMsg += '\nLine: ' + data.error_details.line;
                }
            }
            
            alert(errorMsg);
        }
    })
    .catch(error => {
        if (document.body.contains(loadingMsg)) {
            document.body.removeChild(loadingMsg);
        }
        
        let errorMessage = '✗ Error syncing users from device\n\n';
        
        if (error.name === 'AbortError') {
            errorMessage += 'Request timed out after 90 seconds.\n\n';
            errorMessage += 'Possible causes:\n';
            errorMessage += '• Device is not responding\n';
            errorMessage += '• Network connection issue\n';
            errorMessage += '• Device IP address is incorrect\n';
            errorMessage += '• Device is busy with another operation\n\n';
            errorMessage += 'Please check:\n';
            errorMessage += '1. Device is powered on and connected to network\n';
            errorMessage += '2. IP address is correct (192.168.100.108)\n';
            errorMessage += '3. Device is not being used by another application\n';
            errorMessage += '4. Try restarting the device if problem persists';
        } else if (error.message && error.message.includes('NetworkError')) {
            errorMessage += 'Network connection failed.\n\n';
            errorMessage += 'This usually means:\n';
            errorMessage += '• Server is not responding (check Laravel logs)\n';
            errorMessage += '• Device connection timed out\n';
            errorMessage += '• Network connectivity issue\n\n';
            errorMessage += 'Try:\n';
            errorMessage += '1. Check server logs (storage/logs/laravel.log)\n';
            errorMessage += '2. Verify device IP address\n';
            errorMessage += '3. Test device connection with "List Device Users" button\n';
            errorMessage += '4. Wait a few seconds and try again';
        } else {
            errorMessage += error.message || 'Unknown error occurred';
            errorMessage += '\n\nCheck device connection and IP address.';
        }
        
        alert(errorMessage);
        console.error('Sync error:', error);
    });
}

function listDeviceUsers() {
    const ip = prompt('Enter device IP:', '192.168.100.108');
    if (!ip) return;
    
    const port = prompt('Enter device port:', '4370');
    if (!port) return;

    fetch('/users/list-device-users', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.count > 0) {
                let message = `✓ Found ${data.count} user(s) on device:\n\n`;
                data.users.forEach((user, index) => {
                    message += `${index + 1}. UID: ${user.uid || 'N/A'}, UserID: ${user.user_id || 'N/A'}, Name: ${user.name || 'N/A'}, Role: ${user.role || 'N/A'}\n`;
                });
                message += `\n\nClick OK to see detailed JSON data in console.`;
                console.log('Device Users (Full Data):', data);
                alert(message);
            } else {
                alert('⚠ No users found on device.\n\nPossible reasons:\n- Users not registered yet\n- Device connection issue\n- Device needs to be refreshed\n\nCheck device manually or try registering a user first.');
            }
        } else {
            alert('✗ Error: ' + data.message + '\n\nCheck browser console and server logs for details.');
            console.error('List Device Users Error:', data);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function registerUser(userId) {
    // Show warning about firmware compatibility issue
    const warning = `⚠️ DIRECT REGISTRATION WARNING

Your device (UF200-S firmware 6.60) may have firmware compatibility issues with direct registration.

✅ RECOMMENDED METHOD:
1. Register user directly on device (User Management → Add User)
2. Then click "Sync Users from Device" button
3. User will appear automatically!

Would you like to try direct registration anyway, or use the manual method?`;
    
    if (!confirm(warning + '\n\nClick OK to try direct registration, or Cancel to use manual method.')) {
        // User chose manual method - show instructions
        alert(`📋 MANUAL REGISTRATION INSTRUCTIONS

1. On Device (192.168.100.108):
   - Press MENU → User Management → Add User
   - Enter Enroll ID (check user details for the number)
   - Enter Name
   - Save

2. On This Page:
   - Click "Sync Users from Device" button
   - User will appear automatically!

3. Enroll Fingerprint (optional):
   - On device: User Management → Enroll Fingerprint
   - Enter Enroll ID
   - Place finger 3 times

See MANUAL_REGISTRATION_GUIDE.md for more details.`);
        return;
    }
    
    const ip = prompt('Enter device IP:', '192.168.100.108');
    if (!ip) return;
    
    const port = prompt('Enter device port:', '4370');
    if (!port) return;

    fetch(`/users/${userId}/register-device`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ User registered to device successfully!');
            location.reload();
        } else {
            let errorMsg = '✗ ' + (data.message || 'Registration Failed');
            
            // Show troubleshooting guide if provided
            if (data.troubleshooting) {
                errorMsg += '\n\n' + data.troubleshooting;
            }
            
            // Show quick solution
            if (data.quick_solution) {
                errorMsg += '\n\n💡 QUICK SOLUTION:\n' + data.quick_solution;
            }
            
            // Show user info for reference
            if (data.user_info) {
                errorMsg += '\n\n📋 User Details:\n' +
                           '• Name: ' + data.user_info.name + '\n' +
                           '• Enroll ID: ' + data.user_info.enroll_id;
            }
            
            // Show device info
            if (data.device_info) {
                errorMsg += '\n\n🔌 Device:\n' +
                           '• IP: ' + data.device_info.ip + '\n' +
                           '• Port: ' + data.device_info.port;
            }
            
            // Show error type hints
            if (data.error_type) {
                if (data.error_type.comm_key_issue) {
                    errorMsg += '\n\n⚠️ This appears to be a Comm Key (password) issue.';
                } else if (data.error_type.device_rejected) {
                    errorMsg += '\n\n⚠️ The device rejected the registration. Check if Enroll ID already exists.';
                } else if (data.error_type.firmware_issue) {
                    errorMsg += '\n\n⚠️ This appears to be a firmware compatibility issue.';
                }
            }
            
            alert(errorMsg);
            
            // If user might be registered, offer to sync
            if (data.might_be_registered) {
                if (confirm('The device responded. Would you like to sync users from device to check if user was added?')) {
                    syncUsersFromDevice();
                }
            } else {
                // Offer to check device users or diagnose
                const action = confirm('Would you like to:\n\nOK = Check device users list\nCancel = Run diagnostics');
                if (action) {
                    listDeviceUsers();
                } else {
                    diagnoseDevice();
                }
            }
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function checkFingerprints(userId) {
    const ip = prompt('Enter device IP:', '192.168.100.127');
    if (!ip) return;
    
    const port = prompt('Enter device port:', '4370');
    if (!port) return;

    fetch(`/users/${userId}/check-fingerprints`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.has_fingerprints) {
                alert(`✓ Fingerprints found!\n\nEnrolled fingers: ${data.enrolled_fingers.join(', ')}\nTotal: ${data.count} fingerprint(s)`);
            } else {
                alert('No fingerprints enrolled yet.\n\nPlease enroll fingerprints directly on the device.');
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function showEnrollInstructions(userId, enrollId) {
    const instructions = `📋 FINGERPRINT ENROLLMENT INSTRUCTIONS

To enroll fingerprints for this user on the ZKTeco device:

1. On the device screen, press MENU button
2. Navigate to: User Management → Enroll Fingerprint
3. Enter the user's Enroll ID: ${enrollId}
4. Place the finger on the scanner when prompted
5. Lift and place the same finger again (3 times total)
6. The device will confirm successful enrollment

After enrollment, click "Check Fingerprints" to verify.

Note: Fingerprint enrollment must be done directly on the device.`;
    
    alert(instructions);
}

function removeUser(userId) {
    if (!confirm('Remove this user from the ZKTeco device?')) return;
    
    const ip = prompt('Enter device IP:', '192.168.100.127');
    if (!ip) return;
    
    const port = prompt('Enter device port:', '4370');
    if (!port) return;

    fetch(`/users/${userId}/remove-device`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User removed successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function markAsRegistered(userId) {
    const confirmMsg = `Mark this user as registered on device?\n\n⚠️ IMPORTANT:\nOnly use this if you have:\n1. Registered the user manually on the device (User Management → Add User)\n2. Verified the user appears in device user list\n\nOR\n\n3. Registered via "Sync Users from Device" button\n\nClick OK to mark as registered, or Cancel to abort.`;
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    fetch(`/users/${userId}/mark-registered`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ User marked as registered on device!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function testRegistration() {
    const ip = prompt('Enter device IP:', '192.168.100.100');
    if (!ip) return;
    
    const port = prompt('Enter device port:', '4370');
    if (!port) return;
    
    const enrollId = prompt('Enter test Enroll ID (must be numeric, e.g., 999):', '999');
    if (!enrollId || !/^\d+$/.test(enrollId)) {
        alert('Enroll ID must be numeric!');
        return;
    }
    
    const name = prompt('Enter test user name:', 'Test User ' + new Date().getTime());
    if (!name) return;

    // Show loading
    const loadingMsg = 'Testing registration...\n\nThis will:\n1. Connect to device\n2. Get device info\n3. Check users before registration\n4. Register test user\n5. Verify user was added\n\nPlease wait...';
    alert(loadingMsg);

    fetch('/users/test-registration', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ip, port, enroll_id: parseInt(enrollId), name })
    })
    .then(response => response.json())
    .then(data => {
        let message = '🧪 REGISTRATION TEST RESULTS\n';
        message += '═══════════════════════════════════\n\n';
        message += `Device: ${data.device.ip}:${data.device.port}\n`;
        message += `Test User: ${data.test_user.name} (ID: ${data.test_user.enroll_id})\n`;
        message += `Timestamp: ${data.timestamp}\n\n`;
        
        message += 'STEPS:\n';
        message += '─────────────────────────────────\n';
        
        data.steps.forEach(step => {
            const statusIcon = step.status === 'success' ? '✓' : 
                              step.status === 'failed' ? '✗' : 
                              step.status === 'warning' ? '⚠' : 
                              step.status === 'skipped' ? '⊘' : '⟳';
            message += `${statusIcon} [${step.step}] ${step.name}: ${step.message}\n`;
            
            if (step.data) {
                if (step.data.user_count !== undefined) {
                    message += `   Users on device: ${step.data.user_count}\n`;
                }
                if (step.data.user_count_before !== undefined) {
                    message += `   Before: ${step.data.user_count_before} users\n`;
                    message += `   After: ${step.data.user_count_after} users\n`;
                    message += `   Increased: ${step.data.user_count_increased ? 'YES ✓' : 'NO ✗'}\n`;
                    if (step.data.found_by) {
                        message += `   Found by: ${step.data.found_by}\n`;
                    }
                }
                if (step.data.device_name) {
                    message += `   Device: ${step.data.device_name}\n`;
                }
            }
            message += '\n';
        });
        
        message += '═══════════════════════════════════\n';
        if (data.success) {
            message += '✅ TEST PASSED - Registration Successful!\n';
            message += '\nThe user was successfully registered and verified on the device.';
        } else {
            message += '❌ TEST FAILED\n';
            message += `\nError: ${data.error || 'Unknown error'}\n`;
            message += '\nPossible reasons:\n';
            message += '1. Device firmware compatibility issue (2007 response code)\n';
            message += '2. Comm Key mismatch\n';
            message += '3. Device rejected the registration\n';
            message += '4. Network/connection issues\n';
            message += '\nCheck logs: storage/logs/laravel.log';
        }
        
        alert(message);
        
        // Show detailed results in console
        console.log('Registration Test Results:', data);
    })
    .catch(error => {
        alert('Error running test: ' + error.message + '\n\nCheck browser console for details.');
        console.error('Registration test error:', error);
    });
}
</script>
@endsection

