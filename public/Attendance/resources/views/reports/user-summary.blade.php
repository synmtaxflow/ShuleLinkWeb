@extends('layouts.vali')

@section('title', 'User Attendance Summary')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1>👤 {{ $user->name }} - Attendance Summary</h1>
            <p style="color: #666; margin-top: 0.5rem;">Enroll ID: {{ $user->enroll_id }} | Email: {{ $user->email }}</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('users.show', $user->id) }}" class="btn" style="background: #6c757d; color: white;">← Back to User</a>
            <a href="{{ route('dashboard') }}" class="btn" style="background: #667eea; color: white;">📊 Dashboard</a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold;">{{ $totalRecords }}</div>
            <div>Total Records</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold;">{{ $today }}</div>
            <div>Today</div>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold;">{{ $thisWeek }}</div>
            <div>This Week</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold;">{{ $thisMonth }}</div>
            <div>This Month</div>
        </div>
    </div>

    <h3 style="margin-bottom: 1rem;">📋 Recent Attendance Records (Last 20)</h3>
    
    @if($recentAttendances->count() > 0)
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
                @foreach($recentAttendances as $attendance)
                    <tr>
                        <td><strong>{{ $attendance->punch_time->format('Y-m-d H:i:s') }}</strong></td>
                        <td>
                            @if($attendance->status == 1)
                                <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.85em;">✓ Check In</span>
                            @else
                                <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.85em;">✗ Check Out</span>
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
        <div style="text-align: center; padding: 3rem; color: #666;">
            <p style="font-size: 1.2em; margin-bottom: 1rem;">No attendance records found for this user</p>
            <a href="{{ route('attendances.sync-page') }}" class="btn btn-primary">🔄 Sync Attendance</a>
        </div>
    @endif
        </div>
    </div>
</div>
@endsection





