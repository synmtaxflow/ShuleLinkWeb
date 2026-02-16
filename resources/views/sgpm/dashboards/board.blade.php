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
                            <h3 style="color: #4a148c; font-weight: 700;">Board Strategic Monitoring Dashboard</h3>
                            <p class="text-muted mb-0">High-level institutional progress tracking</p>
                        </div>
                        <div class="text-end">
                            @php
                                $institutionalProgress = $goals->avg('progress_percent') ?? 0;
                            @endphp
                            <h2 class="mb-0 text-secondary fw-bold">{{ number_format($institutionalProgress, 1) }}%</h2>
                            <small class="text-muted">Global Goal Completion</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($goals as $goal)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 5px solid #4a148c !important;">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-light text-dark shadow-sm">Deadline: {{ \Carbon\Carbon::parse($goal->timeline_date)->format('M d, Y') }}</span>
                            @php
                                $progress = $goal->progress_percent ?? 0;
                                $color = $progress >= 80 ? 'success' : ($progress >= 40 ? 'warning' : 'danger');
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ number_format($progress, 1) }}% Met</span>
                        </div>
                        <h5 class="fw-bold mt-3" style="color: #333;">{{ $goal->title }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-4">{{ $goal->description }}</p>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold">Objective Achievement Progress</span>
                                <span>{{ number_format($progress, 0) }}%</span>
                            </div>
                            <div class="progress" style="height: 12px; border-radius: 10px; background-color: #f1f1f1;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $color }}" role="progressbar" style="width: {{ $progress }}%;"></div>
                            </div>
                        </div>

                        <div class="row text-center">
                            <div class="col-6 border-right">
                                <h4 class="mb-0 fw-bold">{{ $goal->objectives->count() }}</h4>
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Total Objectives</small>
                            </div>
                            <div class="col-6">
                                <h4 class="mb-0 fw-bold">{{ $goal->objectives->where('status', 'Completed')->count() }}</h4>
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@include('includes.footer')
