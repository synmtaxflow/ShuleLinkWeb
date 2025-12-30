@extends('layouts.vali')

@section('title', 'Attendance Records')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <div>
                        <h3 class="tile-title">Attendance Records</h3>
                        <div class="alert alert-info mb-0 mt-2">
                            <strong>💡 Check In/Out Info:</strong>
                            <span class="badge badge-success ml-1 mr-1">✓ Check In</span> = User scanned in |
                            <span class="badge badge-danger ml-1 mr-1">✗ Check Out</span> = User scanned out
                            <br>
                            <small>If you only see Check In, configure device: System → Attendance → Work Code Mode</small>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap">
                        @if($consolidated ?? false)
                            <a href="{{ route('attendances.index') }}" class="btn btn-secondary mb-2 mr-2">📋 All Records</a>
                        @else
                            <a href="{{ route('attendances.index', ['consolidated' => 1]) }}" class="btn btn-info mb-2 mr-2">📊 Daily Summary</a>
                        @endif
                        <button onclick="quickSync()" class="btn btn-success mb-2 mr-2">🔄 Quick Sync</button>
                        <button id="autoSyncBtn" onclick="toggleAutoSync()" class="btn btn-success mb-2 mr-2">▶️ Start Auto-Sync</button>
                        <a href="{{ route('attendances.sync-page') }}" class="btn btn-primary mb-2 mr-2">Sync from Device</a>
                        <button onclick="showFilters()" class="btn btn-info mb-2 mr-2">🔍 Filter</button>
                        <button onclick="showCheckInOutHelp()" class="btn btn-warning mb-2 mr-2 text-white">❓ Help</button>
                        <button onclick="clearAllAttendances()" class="btn btn-danger mb-2 mr-2">🗑️ Clear All (DB + Device)</button>
                        <button onclick="deleteAllAttendances()" class="btn btn-secondary mb-2">🗑️ Delete DB Only</button>
                    </div>
                </div>
        
                <div id="autoSyncStatus" class="alert alert-success" style="display: none;">
                    <strong>🔄 Auto-Sync Active:</strong> Checking for new records every 10 seconds...
                </div>
        
                <!-- Filter Section -->
                <div id="filterSection" class="card mb-3" style="display: none;">
                    <div class="card-body">
                        <h5 class="card-title mb-3">🔍 Filter Records</h5>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label><strong>User:</strong></label>
                                <select id="filterUser" class="form-control">
                                    <option value="">All Users</option>
                                    @foreach(\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->enroll_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label><strong>From Date:</strong></label>
                                <input type="date" id="filterFromDate" value="{{ request('from_date') }}" class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label><strong>To Date:</strong></label>
                                <input type="date" id="filterToDate" value="{{ request('to_date') }}" class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label><strong>Status:</strong></label>
                                <select id="filterStatus" class="form-control">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Check In</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Check Out</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button onclick="applyFilters()" class="btn btn-primary mr-2">Apply Filters</button>
                            <button onclick="clearFilters()" class="btn btn-secondary">Clear</button>
                        </div>
                    </div>
                </div>
    
                @if(session('success'))
                    <div class="alert alert-success">
                        ✓ {{ session('success') }}
                    </div>
                @endif

    @if($attendances->count() > 0)
        @if($consolidated ?? false)
            @php
                $total = $stats['total'] ?? $attendances->total();
                $checkIns = $stats['check_ins'] ?? 0;
                $checkOuts = $stats['check_outs'] ?? 0;
                $withBoth = $stats['with_both'] ?? 0;
            @endphp
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small primary coloured-icon">
                        <i class="icon fa fa-calendar fa-3x"></i>
                        <div class="info">
                            <h4>Total Days</h4>
                            <p><b>{{ $total }}</b><br><small>Showing: {{ $attendances->firstItem() }} - {{ $attendances->lastItem() }}</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small success coloured-icon">
                        <i class="icon fa fa-sign-in fa-3x"></i>
                        <div class="info">
                            <h4>With Check In</h4>
                            <p><b>{{ $checkIns }}</b><br><small>{{ $total > 0 ? round(($checkIns / $total) * 100, 1) : 0 }}% of days</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small danger coloured-icon">
                        <i class="icon fa fa-sign-out fa-3x"></i>
                        <div class="info">
                            <h4>With Check Out</h4>
                            <p><b>{{ $checkOuts }}</b><br><small>{{ $total > 0 ? round(($checkOuts / $total) * 100, 1) : 0 }}% of days</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small info coloured-icon">
                        <i class="icon fa fa-check fa-3x"></i>
                        <div class="info">
                            <h4>Complete Days</h4>
                            <p><b>{{ $withBoth }}</b><br><small>{{ $total > 0 ? round(($withBoth / $total) * 100, 1) : 0 }}% complete</small></p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @php
                $checkIns = $attendances->where('status', 1)->count();
                $checkOuts = $attendances->where('status', 0)->count();
                $total = $attendances->total();
            @endphp
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small primary coloured-icon">
                        <i class="icon fa fa-list fa-3x"></i>
                        <div class="info">
                            <h4>Total Records</h4>
                            <p><b>{{ $total }}</b><br><small>Showing: {{ $attendances->firstItem() }} - {{ $attendances->lastItem() }}</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small success coloured-icon">
                        <i class="icon fa fa-sign-in fa-3x"></i>
                        <div class="info">
                            <h4>Check Ins</h4>
                            <p><b>{{ $checkIns }}</b><br><small>{{ $total > 0 ? round(($checkIns / $total) * 100, 1) : 0 }}% of total</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small danger coloured-icon">
                        <i class="icon fa fa-sign-out fa-3x"></i>
                        <div class="info">
                            <h4>Check Outs</h4>
                            <p><b>{{ $checkOuts }}</b><br><small>{{ $total > 0 ? round(($checkOuts / $total) * 100, 1) : 0 }}% of total</small></p>
                        </div>
                    </div>
                </div>
                @if($checkOuts == 0 && $checkIns > 0)
                    <div class="col-md-3 col-sm-6">
                        <div class="widget-small warning coloured-icon">
                            <i class="icon fa fa-exclamation-triangle fa-3x"></i>
                            <div class="info">
                                <h4>Notice</h4>
                                <p><b>No Check Out records</b><br><small>Configure device for Check In/Out mode (see Help button)</small></p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif

    @if($consolidated ?? false)
        <!-- Consolidated/Daily Summary View -->
        <div class="alert alert-info">
            <strong>📊 Daily Summary Mode:</strong> Showing one check-in and one check-out per user per day. 
            <a href="{{ route('attendances.index') }}" class="alert-link">View all records</a>
        </div>
        
        <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Enroll ID</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $index => $record)
                    <tr class="{{ $index === 0 ? 'table-warning' : '' }}">
                        <td>{{ $attendances->firstItem() + $index }}</td>
                        <td>
                            @if($record['user'])
                                <a href="{{ route('reports.user-summary', $record['user']->id) }}" class="text-primary font-weight-bold">
                                    {{ $record['user']->name }}
                                </a>
                            @else
                                <strong>N/A</strong>
                            @endif
                            @if($index === 0)
                                <span class="badge badge-success ml-1">NEWEST</span>
                            @endif
                        </td>
                        <td>{{ $record['enroll_id'] }}</td>
                        <td>
                            <strong>{{ $record['date'] }}</strong>
                        </td>
                        <td>
                            @if($record['check_in_time'])
                                <span class="badge badge-success">
                                    ✓ {{ $record['check_in_time']->format('H:i:s') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($record['check_out_time'])
                                <span class="badge badge-danger">
                                    ✗ {{ $record['check_out_time']->format('H:i:s') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($record['duration'])
                                <strong class="text-primary">{{ $record['duration'] }}</strong>
                                <br><small class="text-muted">({{ number_format($record['duration_hours'], 2) }} hours)</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($record['check_in'] && $record['check_out'])
                                <span class="badge badge-success">Complete</span>
                            @elseif($record['check_in'])
                                <span class="badge badge-warning text-dark">No Check Out</span>
                            @else
                                <span class="badge badge-secondary">Incomplete</span>
                            @endif
                        </td>
                        <td>
                            @if($record['user'])
                                <a href="{{ route('users.show', $record['user']->id) }}" class="btn btn-sm btn-secondary">View User</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 2rem;">
                            <p style="font-size: 1.2em; margin-bottom: 1rem;">No attendance records found.</p>
                            <p class="text-muted" style="margin-bottom: 1rem;">Sync attendance from device to see records.</p>
                            <a href="{{ route('attendances.sync-page') }}" class="btn btn-primary">Sync from Device</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    @else
        <!-- All Records View - One row per user per day with Check In and Check Out columns -->
        <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Enroll ID</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Verify Mode</th>
                    <th>Device IP</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $index => $attendance)
                    @php
                        // Use attendance_date if available, otherwise use punch_time date
                        $attendanceDate = $attendance->attendance_date 
                            ? \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d')
                            : $attendance->punch_time->format('Y-m-d');
                    @endphp
                    <tr class="{{ $index === 0 ? 'table-warning' : '' }}">
                        <td>{{ $attendances->firstItem() + $index }}</td>
                        <td>
                            @if($attendance->user)
                                <a href="{{ route('reports.user-summary', $attendance->user->id) }}" class="text-primary font-weight-bold">
                                    {{ $attendance->user->name }}
                                </a>
                            @else
                                <strong>N/A</strong>
                            @endif
                            @if($index === 0)
                                <span class="badge badge-success ml-1">NEWEST</span>
                            @endif
                        </td>
                        <td>{{ $attendance->enroll_id }}</td>
                        <td>
                            <strong>{{ $attendanceDate }}</strong>
                        </td>
                        <td>
                            @if($attendance->check_in_time)
                                <span class="badge badge-success">
                                    ✓ {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->check_out_time)
                                <span class="badge badge-danger">
                                    ✗ {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i:s') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->verify_mode == 'Fingerprint' || $attendance->verify_mode == 255 || $attendance->verify_mode == 0)
                                <span class="badge badge-info">Fingerprint</span>
                            @elseif($attendance->verify_mode)
                                <span class="badge badge-secondary">Mode: {{ $attendance->verify_mode }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $attendance->device_ip ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('attendances.show', $attendance->id) }}" class="btn btn-sm btn-primary">View</a>
                            @if($attendance->user)
                                <a href="{{ route('users.show', $attendance->user->id) }}" class="btn btn-sm btn-secondary">User</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 2rem;">
                            <p style="font-size: 1.2em; margin-bottom: 1rem;">No attendance records found.</p>
                            <p class="text-muted" style="margin-bottom: 1rem;">Sync attendance from device to see records.</p>
                            <a href="{{ route('attendances.sync-page') }}" class="btn btn-primary">Sync from Device</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    @endif
    
    <div class="mt-3">
        {{ $attendances->links() }}
    </div>
            </div>
        </div>
    </div>

<script>
// Auto-refresh and auto-sync functionality
let autoSyncInterval = null;
let isAutoSyncing = false;

// Start auto-sync on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if auto-sync is enabled (stored in localStorage)
    const autoSyncEnabled = localStorage.getItem('autoSyncEnabled') === 'true';
    if (autoSyncEnabled) {
        startAutoSync();
        updateAutoSyncButton(true);
    }
});

function toggleAutoSync() {
    const enabled = localStorage.getItem('autoSyncEnabled') === 'true';
    if (enabled) {
        stopAutoSync();
        localStorage.setItem('autoSyncEnabled', 'false');
        updateAutoSyncButton(false);
        alert('Auto-sync stopped');
    } else {
        startAutoSync();
        localStorage.setItem('autoSyncEnabled', 'true');
        updateAutoSyncButton(true);
        alert('Auto-sync started! The page will automatically check for new records every 10 seconds.');
    }
}

function startAutoSync() {
    if (autoSyncInterval) {
        clearInterval(autoSyncInterval);
    }
    
    // Sync immediately
    performAutoSync();
    
    // Then sync every 10 seconds
    autoSyncInterval = setInterval(function() {
        performAutoSync();
    }, 10000); // 10 seconds
}

function stopAutoSync() {
    if (autoSyncInterval) {
        clearInterval(autoSyncInterval);
        autoSyncInterval = null;
    }
}

function performAutoSync() {
    if (isAutoSyncing) {
        return; // Don't sync if already syncing
    }
    
    isAutoSyncing = true;
    
    // Silent sync (no alerts, just update the page)
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
        if (data.success && data.data && data.data.synced > 0) {
            // New records found - reload page to show them
            console.log('Auto-sync: Found ' + data.data.synced + ' new record(s)');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Auto-sync error:', error);
    })
    .finally(() => {
        isAutoSyncing = false;
    });
}

function updateAutoSyncButton(enabled) {
    const btn = document.getElementById('autoSyncBtn');
    const statusDiv = document.getElementById('autoSyncStatus');
    
    if (btn) {
        if (enabled) {
            btn.textContent = '⏸️ Stop Auto-Sync';
            btn.style.background = '#dc3545';
            if (statusDiv) {
                statusDiv.style.display = 'block';
            }
        } else {
            btn.textContent = '▶️ Start Auto-Sync';
            btn.style.background = '#28a745';
            if (statusDiv) {
                statusDiv.style.display = 'none';
            }
        }
    }
}

function quickSync() {
    if (!confirm('Sync attendance records from device (192.168.100.108)?')) {
        return;
    }
    
    // Show loading
    const loadingMsg = document.createElement('div');
    loadingMsg.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 9999;';
    loadingMsg.innerHTML = '<p>⏳ Syncing attendance from device...</p><p style="font-size: 0.9em; color: #666;">Please wait...</p>';
    document.body.appendChild(loadingMsg);
    
    // Create AbortController for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 90000); // 90 second timeout

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
        }),
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
        if (error.name === 'AbortError') {
            alert('✗ Error: Request timed out after 90 seconds.\n\nPossible causes:\n- Device is not responding\n- Network connection issue\n- Device IP address is incorrect\n\nPlease check device connection and try again.');
        } else {
            alert('✗ Error: ' + error.message);
        }
        console.error('Sync error:', error);
    });
}

function showFilters() {
    const filterSection = document.getElementById('filterSection');
    if (filterSection.style.display === 'none') {
        filterSection.style.display = 'block';
    } else {
        filterSection.style.display = 'none';
    }
}

function applyFilters() {
    const userId = document.getElementById('filterUser').value;
    const fromDate = document.getElementById('filterFromDate').value;
    const toDate = document.getElementById('filterToDate').value;
    const status = document.getElementById('filterStatus').value;
    
    // Build URL with filters
    let url = '{{ route("attendances.index") }}?';
    const params = new URLSearchParams();
    
    // Preserve consolidated view if active
    @if($consolidated ?? false)
        params.append('consolidated', '1');
    @endif
    
    if (userId) params.append('user_id', userId);
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);
    if (status !== '') params.append('status', status);
    
    window.location.href = url + params.toString();
}

function clearFilters() {
    document.getElementById('filterUser').value = '';
    document.getElementById('filterFromDate').value = '';
    document.getElementById('filterToDate').value = '';
    document.getElementById('filterStatus').value = '';
    window.location.href = '{{ route("attendances.index") }}';
}

function showCheckInOutHelp() {
    const helpText = `📋 CHECK IN / CHECK OUT GUIDE

HOW IT WORKS:
• ✓ Check In (Green) = User scanned fingerprint to enter/start work
• ✗ Check Out (Red) = User scanned fingerprint to leave/end work

WHY YOU MIGHT ONLY SEE CHECK IN:
1. Device is in "Single Punch Mode" - every scan is treated as a punch
2. Users haven't checked out yet (only scanned in)
3. Device not configured for Check In/Out mode

HOW TO ENABLE CHECK OUT:
1. On Device: Press MENU → System → Attendance → Work Code
2. Enable "Work Code Mode" or "Check In/Out Mode"
3. Set work schedule (e.g., 9 AM - 5 PM)
4. Users must scan TWICE: once to check in, once to check out

CURRENT STATUS:
• Check In records: ${document.querySelectorAll('span:contains("Check In")').length || 'N/A'}
• Check Out records: ${document.querySelectorAll('span:contains("Check Out")').length || 'N/A'}

After configuring the device, sync attendance to see Check Out records.`;
    
    alert(helpText);
}

function clearAllAttendances() {
    if (!confirm('⚠️ WARNING: This will delete ALL attendance records from:\n\n• Database (permanent)\n• Device (192.168.100.108)\n\nAre you sure you want to continue?')) {
        return;
    }
    
    if (!confirm('⚠️ FINAL WARNING: This action cannot be undone!\n\nAll attendance records will be permanently deleted from both database AND device.\n\nClick OK to proceed or Cancel to abort.')) {
        return;
    }

    // Show loading
    const loadingMsg = document.createElement('div');
    loadingMsg.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 9999; text-align: center;';
    loadingMsg.innerHTML = '<p>⏳ Clearing attendance from database and device...</p><p style="font-size: 0.9em; color: #666;">This may take 10-30 seconds. Please wait...</p>';
    document.body.appendChild(loadingMsg);

    fetch('{{ route("attendances.clear-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ip: '192.168.100.108',
            port: 4370,
            clear_device: true
        })
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(loadingMsg);
        if (data.success) {
            let message = '✓ ' + data.message;
            if (data.data && data.data.device) {
                if (data.data.device.success) {
                    message += '\n\n✓ Device cleared successfully!';
                } else {
                    message += '\n\n⚠️ Device clear warning: ' + data.data.device.message;
                }
            }
            alert(message);
            location.reload();
        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(error => {
        document.body.removeChild(loadingMsg);
        alert('✗ Error: ' + error.message);
        console.error('Clear all error:', error);
    });
}

function deleteAllAttendances() {
    if (!confirm('⚠️ WARNING: This will delete ALL attendance records from the DATABASE ONLY!\n\nDevice records will NOT be deleted.\n\nAre you sure you want to continue?')) {
        return;
    }
    
    if (!confirm('⚠️ FINAL WARNING: This action cannot be undone!\n\nAll attendance records will be permanently deleted.\n\nClick OK to proceed or Cancel to abort.')) {
        return;
    }

    fetch('/attendances/delete-all', {
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
            alert('✓ ' + data.message + '\n\nNote: Device records were NOT deleted. Use "Clear All" button to delete from device too.');
            location.reload();
        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('✗ Error: ' + error.message);
    });
}
</script>
@endsection

