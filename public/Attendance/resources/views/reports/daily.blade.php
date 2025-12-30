@extends('layouts.vali')

@section('title', 'Daily Attendance Report')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    <h3 class="tile-title">Daily Attendance Report</h3>
                    <p class="text-muted mb-0">{{ $selectedDate->format('F d, Y') }}</p>
                </div>
                <div class="d-flex flex-wrap align-items-center">
                    <form method="GET" action="{{ route('reports.daily') }}" class="form-inline mr-2 mb-2">
                        <div class="form-group mr-2 mb-2">
                            <input type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">View Report</button>
                    </form>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary mb-2">← Back to Dashboard</a>
                </div>
            </div>

            <!-- Summary Widgets -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small primary coloured-icon">
                        <i class="icon fa fa-list fa-3x"></i>
                        <div class="info">
                            <h4>Total Records</h4>
                            <p><b>{{ $summary['total'] }}</b></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small info coloured-icon">
                        <i class="icon fa fa-sign-in fa-3x"></i>
                        <div class="info">
                            <h4>Check Ins</h4>
                            <p><b>{{ $summary['check_ins'] }}</b></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small warning coloured-icon">
                        <i class="icon fa fa-sign-out fa-3x"></i>
                        <div class="info">
                            <h4>Check Outs</h4>
                            <p><b>{{ $summary['check_outs'] }}</b></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="widget-small success coloured-icon">
                        <i class="icon fa fa-user fa-3x"></i>
                        <div class="info">
                            <h4>Unique Users</h4>
                            <p><b>{{ $summary['unique_users'] }}</b></p>
                        </div>
                    </div>
                </div>
            </div>

            @if($attendances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Enroll ID</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Verify Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                                <tr>
                                    <td><strong>{{ $attendance->user->name ?? 'N/A' }}</strong></td>
                                    <td>{{ $attendance->user->enroll_id ?? 'N/A' }}</td>
                                    <td>{{ $attendance->punch_time->format('H:i:s') }}</td>
                                    <td>
                                        @if($attendance->status == 1)
                                            <span class="badge badge-success">Check In</span>
                                        @else
                                            <span class="badge badge-danger">Check Out</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->verify_mode == 15 || $attendance->verify_mode == 255)
                                            <span class="badge badge-warning text-dark">Fingerprint</span>
                                        @else
                                            <span class="badge badge-secondary">Other</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted" style="padding: 3rem;">
                    <p style="font-size: 1.2em; margin-bottom: 1rem;">
                        No attendance records found for {{ $selectedDate->format('F d, Y') }}
                    </p>
                    <a href="{{ route('attendances.sync-page') }}" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> Sync Attendance
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection





