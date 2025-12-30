@extends('layouts.vali')

@section('title', 'User Details')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>User Details</h1>
        <a href="{{ route('users.index') }}" class="btn" style="background: #6c757d; color: white;">Back to Users</a>
    </div>

    <div style="margin-bottom: 2rem;">
        <h2>{{ $user->name }}</h2>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Enroll ID:</strong> {{ $user->enroll_id ?? 'N/A' }}</p>
        <p><strong>Device Status:</strong> 
            @if($user->registered_on_device)
                <span style="color: #28a745;">✓ Registered on {{ $user->device_registered_at?->format('Y-m-d H:i:s') }}</span>
            @else
                <span style="color: #dc3545;">Not Registered</span>
            @endif
        </p>
        
        @if($user->registered_on_device)
            <div style="margin-top: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <h4 style="margin-bottom: 1rem;">Fingerprint Management</h4>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <button onclick="checkFingerprints({{ $user->id }})" class="btn" style="background: #17a2b8; color: white;">Check Fingerprints</button>
                    <button onclick="showEnrollInstructions({{ $user->id }}, '{{ $user->enroll_id }}')" class="btn" style="background: #ffc107; color: #000;">Enroll Fingerprint</button>
                </div>
            </div>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold;">{{ $user->attendances->count() }}</div>
            <div>Total Records</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold;">{{ $user->attendances->where('punch_time', '>=', \Carbon\Carbon::today())->count() }}</div>
            <div>Today</div>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold;">{{ $user->attendances->where('punch_time', '>=', \Carbon\Carbon::now()->startOfWeek())->count() }}</div>
            <div>This Week</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold;">{{ $user->attendances->where('punch_time', '>=', \Carbon\Carbon::now()->startOfMonth())->count() }}</div>
            <div>This Month</div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>📋 Attendance Records ({{ $user->attendances->count() }})</h3>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('reports.user-summary', $user->id) }}" class="btn" style="background: #17a2b8; color: white;">📊 View Report</a>
            <button onclick="syncAttendanceForUser()" class="btn btn-primary" style="background: #28a745; color: white;">🔄 Sync Attendance</button>
        </div>
    </div>

    @if($user->attendances->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Verify Mode</th>
                    <th>Device IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user->attendances->sortByDesc('punch_time') as $attendance)
                    <tr>
                        <td><strong>{{ $attendance->punch_time->format('Y-m-d H:i:s') }}</strong></td>
                        <td>
                            @if($attendance->status == 1)
                                <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.85em;">✓ Check In</span>
                            @elseif($attendance->status == 0 || $attendance->status == 15)
                                <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.85em;">✗ Check Out</span>
                            @else
                                <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.85em;">Status: {{ $attendance->status ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->verify_mode == 15 || $attendance->verify_mode == 255)
                                <span style="background: #ffc107; color: #000; padding: 4px 8px; border-radius: 3px; font-size: 0.85em;">Fingerprint</span>
                            @else
                                <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.85em;">Other</span>
                            @endif
                        </td>
                        <td>{{ $attendance->device_ip ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 2rem; color: #666;">No attendance records found.</p>
    @endif
        </div>
    </div>
</div>

@if($user->registered_on_device)
<script>
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

function syncAttendanceForUser() {
    if (!confirm('Sync attendance records from device?\n\nThis will get all attendance records, including for this user.')) {
        return;
    }
    
    // Show loading
    const loadingMsg = document.createElement('div');
    loadingMsg.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 9999;';
    loadingMsg.innerHTML = '<p>⏳ Syncing attendance from device...</p><p style="font-size: 0.9em; color: #666;">Please wait...</p>';
    document.body.appendChild(loadingMsg);
    
    fetch('{{ route("attendances.sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ip: '192.168.100.108',
            port: 4370
        })
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(loadingMsg);
        
        if (data.success) {
            let message = '✓ ' + data.message;
            if (data.data && data.data.users_verified > 0) {
                message += '\n\nVerified users: ' + data.data.verified_user_names.join(', ');
            }
            alert(message);
            location.reload();
        } else {
            alert('✗ Error: ' + data.message + '\n\n💡 Tip: Make sure user has punched in/out on the device first.');
        }
    })
    .catch(error => {
        document.body.removeChild(loadingMsg);
        alert('✗ Error: ' + error.message);
    });
}
</script>
@endif
@endsection

