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
                            <h3 style="color: #2e7d32; font-weight: 700;">My Performance Scorecard</h3>
                            <p class="text-muted mb-0">Track your KPIs and assigned tasks</p>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 text-success fw-bold">{{ number_format($avgScore, 1) }}%</h2>
                            <small class="text-muted">Personal Growth Score</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; height: calc(100% - 1.5rem);">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-5">
                        <div class="mb-3">
                            <div style="width: 120px; height: 120px; border-radius: 50%; border: 8px solid #e8f5e9; display: inline-flex; align-items: center; justify-content: center;">
                                <h2 class="mb-0 fw-bold text-success">{{ number_format($avgScore, 0) }}%</h2>
                            </div>
                        </div>
                        <h5 class="fw-bold">Excellent Standing</h5>
                        <p class="text-muted small px-3">Your average score is calculated from all reviewed tasks and milestone completions.</p>
                        <div class="mt-3">
                            <a href="{{ route('sgpm.tasks.index') }}" class="btn btn-success btn-sm px-4" style="border-radius: 20px;">View My Tasks</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">Recent Task Reviews</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4">KPI / Task</th>
                                        <th class="border-0">Due Date</th>
                                        <th class="border-0">Score</th>
                                        <th class="border-0 px-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tasks->take(10) as $t)
                                    @php
                                        $statusColor = $t->status == 'Approved' ? 'success' : ($t->status == 'Rejected' ? 'danger' : 'info');
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="fw-bold d-block text-dark">{{ $t->kpi }}</span>
                                            <small class="text-muted">{{ $t->actionPlan->title ?? 'General Task' }}</small>
                                        </td>
                                        <td class="py-3">{{ \Carbon\Carbon::parse($t->due_date)->format('M d, Y') }}</td>
                                        <td class="py-3">
                                            @if($t->total_score)
                                                <span class="fw-bold text-{{ $t->total_score >= 80 ? 'success' : 'warning' }}">{{ number_format($t->total_score, 1) }}%</span>
                                            @else
                                                <span class="text-muted">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-{{ $statusColor }} text-white px-3" style="border-radius: 10px;">{{ $t->status }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No tasks assigned yet.</td>
                                    </tr>
                                    @endforelse
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
