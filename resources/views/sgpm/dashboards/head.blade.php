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
                            <h3 style="color: #940000; font-weight: 700;">Performance Management Dashboard</h3>
                            <p class="text-muted mb-0">Overview of institutional performance across all departments</p>
                        </div>
                        <div class="text-end">
                            @php
                                $institutionalScore = $departments->avg('performance_score') ?? 0;
                            @endphp
                            <h2 class="mb-0 text-primary-custom fw-bold">{{ number_format($institutionalScore, 1) }}%</h2>
                            <small class="text-muted">Avg. Institutional Score</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($departments as $dept)
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small fw-bold">{{ $dept->type }}</span>
                            @php
                                $score =$dept->performance_score ?? 0;
                                $color = $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ number_format($score, 1) }}% Score</span>
                        </div>
                        <h5 class="fw-bold mt-2">{{ $dept->department_name }}</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Performance Progress</span>
                                <span>{{ number_format($score, 0) }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $score }}%;"></div>
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="fa fa-user-tie"></i> Head: 
                            @if($dept->type == 'Academic')
                                {{ $dept->headTeacher->first_name ?? 'N/A' }} {{ $dept->headTeacher->last_name ?? '' }}
                            @else
                                {{ $dept->headStaff->first_name ?? 'N/A' }} {{ $dept->headStaff->last_name ?? '' }}
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0">
                        <a href="{{ route('sgpm.objectives.index') }}?dept={{ $dept->departmentID }}" class="btn btn-sm btn-outline-primary w-100">View Objectives</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@include('includes.footer')
