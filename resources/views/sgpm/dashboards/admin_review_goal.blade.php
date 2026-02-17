@include('includes.Admin_nav')

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <!-- Goal Header -->
                <div class="dashboard-hero bg-white p-4 mb-4 shadow-sm border-0" style="border-radius: 12px; font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="{{ route('performance.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
                        <span class="badge bg-primary fs-5">{{ number_format($goal->progress_percent, 1) }}% Completed</span>
                    </div>
                    <h3 style="color: #940000; font-weight: 700;">{{ $goal->title }}</h3>
                    <p class="text-muted lead mb-2">{{ $goal->description }}</p>
                    
                    <div class="row mt-4 text-center">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="fw-bold">{{ $goal->stats['total_objectives'] }}</h4>
                                <small class="text-muted">Total Objectives</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="fw-bold">{{ $goal->stats['total_tasks'] }}</h4>
                                <small class="text-muted">Total Tasks</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="fw-bold text-success">{{ $goal->stats['completed_tasks'] }}</h4>
                                <small class="text-muted">Completed Tasks</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="fw-bold text-info">{{ $goal->stats['approved_subtasks'] }}</h4>
                                <small class="text-muted">Approved Subtasks</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departmental Objectives Breakdown -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0">Departmental Execution Breakdown</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion" id="objectivesAccordion">
                            @forelse($goal->objectives as $index => $objective)
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header" id="heading{{ $objective->objectiveID }}">
                                        <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} bg-light text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $objective->objectiveID }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $objective->objectiveID }}">
                                            <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                                <span>
                                                    <span class="badge bg-secondary me-2">{{ $objective->department->department_name }}</span>
                                                    <strong>{{ $objective->kpi }}</strong>
                                                </span>
                                                <span class="badge {{ $objective->status == 'Completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ $objective->status }}
                                                </span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $objective->objectiveID }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $objective->objectiveID }}" data-bs-parent="#objectivesAccordion">
                                        <div class="accordion-body bg-white">
                                            
                                            <!-- Action Plans -->
                                            @foreach($objective->actionPlans as $plan)
                                                <div class="mb-4 ps-3 border-start border-3 border-primary">
                                                    <h6 class="fw-bold text-primary mb-2">Action Plan: {{ $plan->title }}</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-hover align-middle">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th width="30%">Task</th>
                                                                    <th width="15%">Assigned To</th>
                                                                    <th width="10%">Weight</th>
                                                                    <th width="20%">Progress</th>
                                                                    <th width="10%">Status</th>
                                                                    <th width="15%">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($plan->tasks as $task)
                                                                    <tr>
                                                                        <td>{{ $task->kpi }}</td>
                                                                        <td>
                                                                            @if($task->assignee)
                                                                                <small class="fw-bold">{{ $task->assignee->name }}</small>
                                                                            @else
                                                                                <span class="text-muted">Unassigned</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $task->weight }}%</td>
                                                                        <td>
                                                                            <div class="progress" style="height: 6px;">
                                                                                <div class="progress-bar bg-success" style="width: {{ ($task->progress / max($task->weight, 1)) * 100 }}%"></div>
                                                                            </div>
                                                                            <small class="text-muted">{{ round(($task->progress / max($task->weight, 1)) * 100) }}%</small>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge {{ $task->status == 'Completed' ? 'bg-success' : ($task->status == 'In Progress' ? 'bg-info' : 'bg-secondary') }}">
                                                                                {{ $task->status }}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            @if($task->subtasks->count() > 0)
                                                                                <button class="btn btn-xs btn-outline-dark" data-toggle="modal" data-target="#adminReviewSubtasksModal{{ $task->taskID }}">
                                                                                    Review Subtasks
                                                                                </button>
                                                                            @else
                                                                                <small class="text-muted">No subtasks</small>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr><td colspan="6" class="text-center text-muted">No tasks defined for this plan.</td></tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if($objective->actionPlans->isEmpty())
                                                <div class="alert alert-light text-center text-muted">No action plans created for this objective yet.</div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted">No departmental objectives linked to this strategic goal yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Include Subtask Modals for Admin -->
@foreach($goal->objectives as $objective)
    @foreach($objective->actionPlans as $plan)
        @foreach($plan->tasks as $task)
            @include('sgpm.dashboards._admin_review_subtasks', ['task' => $task])
        @endforeach
    @endforeach
@endforeach

<script>
    // Handle Admin Subtask Approval
    $(document).on('submit', '.admin-approve-subtask-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const subtaskId = $form.data('id');
        const formData = $form.serialize();
        const $btn = $form.find('button[type="submit"]');
        const originalText = $btn.html();

        $btn.html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

        $.ajax({
            url: "{{ url('sgpm/performance/subtasks') }}/" + subtaskId + "/approve",
            method: 'POST',
            data: formData,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Approved!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to approve', 'error');
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Handle Admin Subtask Rejection
    $(document).on('click', '.admin-reject-subtask', function(e) {
        e.preventDefault();
        const subtaskId = $(this).data('id');
        
        Swal.fire({
            title: 'Reject Subtask?',
            text: "This will return it to the assignee for revision.",
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Reason for rejection...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Reject it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('sgpm/performance/subtasks') }}/" + subtaskId + "/reject",
                    method: 'POST',
                    data: { hod_comments: result.value },
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(response) {
                        Swal.fire('Rejected!', 'Subtask has been rejected.', 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to reject subtask', 'error');
                    }
                });
            }
        });
    });
</script>

@include('includes.footer')
