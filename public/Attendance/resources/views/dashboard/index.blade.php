@extends('layouts.vali')

@section('title', 'Dashboard')
@section('icon', 'fa-dashboard')

@section('content')
<div class="row">
    <!-- Widgets row -->
    <div class="col-md-6 col-lg-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info">
                <h4>Total Users</h4>
                <p><b>{{ $totalUsers }}</b></p>
                <p>{{ $registeredUsers }} registered on device</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-list fa-3x"></i>
            <div class="info">
                <h4>Total Records</h4>
                <p><b>{{ $totalAttendances }}</b></p>
                <p>All time</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-calendar fa-3x"></i>
            <div class="info">
                <h4>Today's Records</h4>
                <p><b>{{ $todayAttendances }}</b></p>
                <p>✓ {{ $todayCheckIns }} In · ✗ {{ $todayCheckOuts }} Out</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-line-chart fa-3x"></i>
            <div class="info">
                <h4>This Week</h4>
                <p><b>{{ $weekAttendances }}</b></p>
                <p>Last 7 days</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Activity -->
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Recent Activity</h3>
            @if($recentAttendances->count() > 0)
                <div style="max-height: 400px; overflow-y: auto;">
                    @foreach($recentAttendances as $attendance)
                        <div class="clearfix" style="padding: 8px 0; border-bottom: 1px solid #eee;">
                            <div class="pull-left">
                                <strong>{{ $attendance->user->name ?? 'N/A' }}</strong>
                                <div style="font-size: 0.9em; color: #666;">
                                    {{ $attendance->punch_time->format('Y-m-d H:i:s') }}
                                </div>
                            </div>
                            <div class="pull-right">
                                @if($attendance->status == 1)
                                    <span class="badge badge-success">✓ In</span>
                                @else
                                    <span class="badge badge-danger">✗ Out</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-muted" style="padding: 2rem;">No recent activity</p>
            @endif
        </div>
    </div>

    <!-- Top Users -->
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Top Users (By Attendance)</h3>
            @if($topUsers->count() > 0)
                @foreach($topUsers as $index => $user)
                    <div class="clearfix" style="padding: 8px 0; border-bottom: 1px solid #eee;">
                        <div class="pull-left">
                            <strong>#{{ $index + 1 }} {{ $user->name }}</strong>
                            <div style="font-size: 0.9em; color: #666;">
                                Enroll ID: {{ $user->enroll_id }}
                            </div>
                        </div>
                        <div class="pull-right">
                            <span class="badge badge-info">{{ $user->attendances_count }} records</span>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-center text-muted" style="padding: 2rem;">No users yet</p>
            @endif
        </div>
    </div>
</div>

<!-- Attendance Trend (Last 7 Days) -->
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title">Attendance Trend (Last 7 Days)</h3>
            @if($attendanceByDay->count() > 0)
                <div class="embed-responsive embed-responsive-16by9">
                    <canvas id="attendanceTrendChart" class="embed-responsive-item"></canvas>
                </div>
            @else
                <p class="text-center text-muted" style="padding: 2rem;">No data for the last 7 days</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
@if($attendanceByDay->count() > 0)
<script>
    (function() {
        var el = document.getElementById('attendanceTrendChart');
        if (!el || typeof Chart === 'undefined') {
            return;
        }

        var labels = @json($attendanceByDay->pluck('date'));
        var values = @json($attendanceByDay->pluck('count'));

        var data = {
            labels: labels,
            datasets: [
                {
                    label: "Attendance",
                    fillColor: "rgba(0,150,136,0.2)",
                    strokeColor: "#009688",
                    pointColor: "#009688",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "#009688",
                    data: values
                }
            ]
        };

        var ctx = el.getContext("2d");
        new Chart(ctx).Line(data);
    })();
</script>
@endif
@endpush

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title">Quick Actions</h3>
            <div class="btn-group" role="group" aria-label="Quick actions">
                <a href="{{ route('users.index') }}" class="btn btn-primary">
                    <i class="fa fa-users"></i> Manage Users
                </a>
                <a href="{{ route('attendances.index') }}" class="btn btn-info">
                    <i class="fa fa-list"></i> View All Records
                </a>
                <a href="{{ route('reports.daily') }}" class="btn btn-warning text-white">
                    <i class="fa fa-calendar"></i> Daily Report
                </a>
                <a href="{{ route('attendances.sync-page') }}" class="btn btn-success">
                    <i class="fa fa-refresh"></i> Sync Attendance
                </a>
                <a href="{{ route('zkteco.test') }}" class="btn btn-secondary">
                    <i class="fa fa-plug"></i> Test Device
                </a>
                <a href="{{ route('reset.index') }}" class="btn btn-danger">
                    <i class="fa fa-trash"></i> Fresh Start
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


