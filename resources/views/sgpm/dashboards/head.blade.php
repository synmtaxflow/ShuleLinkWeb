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
        
        <!-- Strategic Goals Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3" style="color: #444; font-weight: 600;">Strategic Goals Progress (Board Level)</h4>
            </div>
            @forelse($goals as $goal)
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title fw-bold text-dark">{{ $goal->title }}</h5>
                            <span class="badge bg-primary">{{ number_format($goal->progress_percent, 0) }}%</span>
                        </div>
                        <p class="text-muted small mb-3">{{ Str::limit($goal->description, 100) }}</p>
                        
                        <div class="mb-3">
                            <label class="small text-muted fw-bold">Overall Progress</label>
                            <div class="progress" style="height: 10px; border-radius: 5px;">
                                <div class="progress-bar bg-{{ $goal->progress_percent >= 100 ? 'success' : ($goal->progress_percent >= 50 ? 'info' : 'warning') }}" 
                                     role="progressbar" 
                                     style="width: {{ $goal->progress_percent }}%"></div>
                            </div>
                        </div>

                        <div class="row text-center small text-muted mb-3">
                            <div class="col-4 border-end">
                                <strong class="d-block text-dark h5 mb-0">{{ $goal->stats['total_objectives'] }}</strong>
                                Dept. Objectives
                            </div>
                            <div class="col-4 border-end">
                                <strong class="d-block text-dark h5 mb-0">{{ $goal->stats['total_tasks'] }}</strong>
                                Total Tasks
                            </div>
                            <div class="col-4">
                                <strong class="d-block text-dark h5 mb-0">{{ $goal->stats['completed_tasks'] }}</strong>
                                Completed
                            </div>
                        </div>

                        <a href="{{ route('sgpm.performance.goal.review', $goal->strategic_goalID) }}" class="btn btn-sm btn-outline-dark w-100">
                            <i class="fa fa-search"></i> Review Execution & Tasks
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="alert alert-info">No Strategic Goals defined yet.</div>
            </div>
            @endforelse
        </div>
        
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="mb-3" style="color: #444; font-weight: 600;">Departmental Performance</h4>
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
