@php
    $userType = $userType ?? Session::get('user_type');
@endphp

@if($userType == 'Admin')
    @include('includes.Admin_nav')
@elseif($userType == 'Teacher')
    @include('includes.teacher_nav')
@else
    @include('includes.staff_nav')
@endif

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="dashboard-hero bg-white p-4 mb-4 shadow-sm border-0" style="border-radius: 15px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 style="color: #004d94; font-weight: 700;">Departmental Performance Dashboard</h3>
                            <p class="text-muted mb-0">Overview for Department: <strong>{{ $department->department_name }}</strong></p>
                        </div>
                        <div class="text-end">
                            @php
                                $avgScore = $tasks->avg('total_score') ?? 0;
                            @endphp
                            <h2 class="mb-0 text-primary fw-bold">{{ number_format($avgScore, 1) }}%</h2>
                            <small class="text-muted">Avg. Departmental Score</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="stat-widget-five">
                            <div class="stat-icon dib flat-color-1">
                                <i class="fa fa-tasks"></i>
                            </div>
                            <div class="stat-content">
                                <div class="text-left dib">
                                    <div class="stat-text"><span class="count">{{ $tasks->count() }}</span></div>
                                    <div class="stat-heading">Total Tasks</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="stat-widget-five">
                            <div class="stat-icon dib flat-color-2">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="text-left dib">
                                    <div class="stat-text"><span class="count">{{ $tasks->where('status', 'Approved')->count() }}</span></div>
                                    <div class="stat-heading">Completed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="stat-widget-five">
                            <div class="stat-icon dib flat-color-3">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <div class="stat-content">
                                <div class="text-left dib">
                                    <div class="stat-text"><span class="count">{{ $tasks->where('status', 'In Progress')->count() }}</span></div>
                                    <div class="stat-heading">In Progress</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="stat-widget-five">
                            <div class="stat-icon dib flat-color-4">
                                <i class="fa fa-warning"></i>
                            </div>
                            <div class="stat-content">
                                <div class="text-left dib">
                                    <div class="stat-text"><span class="count">{{ $tasks->where('status', 'Rejected')->count() }}</span></div>
                                    <div class="stat-heading">Rejected</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">Staff Performance Rankings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Staff Name</th>
                                        <th>Tasks Assigned</th>
                                        <th>Avg. Score</th>
                                        <th>Performance Bar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $staffRankings = $tasks->groupBy('assigned_to');
                                    @endphp
                                    @foreach($staffRankings as $userId => $staffTasks)
                                        @php
                                            $staffScore = $staffTasks->avg('total_score') ?? 0;
                                            $staff = $staffTasks->first()->assignee;
                                            $color = $staffScore >= 80 ? 'success' : ($staffScore >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <tr>
                                            <td>{{ $staff->name ?? 'User ID: '.$userId }}</td>
                                            <td>{{ $staffTasks->count() }}</td>
                                            <td><span class="badge bg-{{ $color }}">{{ number_format($staffScore, 1) }}%</span></td>
                                            <td style="width: 200px;">
                                                <div class="progress" style="height: 10px; border-radius: 5px;">
                                                    <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $staffScore }}%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
