@extends('layouts.vali')

@section('title', 'Attendance Details')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Attendance Record Details</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('attendances.index') }}" class="btn" style="background: #6c757d; color: white;">← Back to Attendance</a>
            @if($attendance->user)
                <a href="{{ route('users.show', $attendance->user->id) }}" class="btn btn-primary">View User</a>
            @endif
        </div>
    </div>

    <div style="background: #f8f9fa; padding: 2rem; border-radius: 8px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Record Information</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div>
                <strong style="color: #666; display: block; margin-bottom: 0.5rem;">User Name</strong>
                <p style="font-size: 1.2em; margin: 0;">{{ $attendance->user->name ?? 'N/A' }}</p>
            </div>
            
            <div>
                <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Enroll ID</strong>
                <p style="font-size: 1.2em; margin: 0;">{{ $attendance->enroll_id }}</p>
            </div>
            
            <div>
                <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Punch Date</strong>
                <p style="font-size: 1.2em; margin: 0;">{{ $attendance->punch_time->format('Y-m-d') }}</p>
            </div>
            
            <div>
                <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Punch Time</strong>
                <p style="font-size: 1.2em; margin: 0;">{{ $attendance->punch_time->format('H:i:s') }}</p>
            </div>
            
            <div>
                <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Status</strong>
                <p style="margin: 0;">
                    @if($attendance->status == 1)
                        <span style="background: #28a745; color: white; padding: 6px 12px; border-radius: 5px; font-size: 1em;">✓ Check In</span>
                    @elseif($attendance->status == 0)
                        <span style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 5px; font-size: 1em;">✗ Check Out</span>
                    @else
                        {{ $attendance->status ?? 'N/A' }}
                    @endif
                </p>
            </div>
            
            <div>
                <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Verify Mode</strong>
                <p style="margin: 0;">
                    @if($attendance->verify_mode)
                        <span style="background: #17a2b8; color: white; padding: 6px 12px; border-radius: 5px; font-size: 1em;">Fingerprint ({{ $attendance->verify_mode }})</span>
                    @else
                        {{ $attendance->verify_mode ?? 'N/A' }}
                    @endif
                </p>
            </div>
            
            <div>
                <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Device IP</strong>
                <p style="font-size: 1.2em; margin: 0;">{{ $attendance->device_ip ?? 'N/A' }}</p>
            </div>
            
            <div>
                <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Record Created</strong>
                <p style="font-size: 1.2em; margin: 0;">{{ $attendance->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>

    @if($attendance->user)
    <div style="background: #e3f2fd; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">User Information</h3>
        <p><strong>Name:</strong> {{ $attendance->user->name }}</p>
        <p><strong>Email:</strong> {{ $attendance->user->email }}</p>
        <p><strong>Enroll ID:</strong> {{ $attendance->user->enroll_id }}</p>
        <p><strong>Device Status:</strong> 
            @if($attendance->user->registered_on_device)
                <span style="color: #28a745;">✓ Registered</span>
            @else
                <span style="color: #dc3545;">Not Registered</span>
            @endif
        </p>
        <div style="margin-top: 1rem;">
            <a href="{{ route('users.show', $attendance->user->id) }}" class="btn btn-primary">View All User Records</a>
        </div>
    </div>
    @endif
        </div>
    </div>
</div>
@endsection





