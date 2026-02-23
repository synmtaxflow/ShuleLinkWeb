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
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0" style="color: #940000; font-weight: 700;">Performance Tasks</h4>
                            <p class="text-muted small mb-0">Individual staff tasks derived from departmental action plans</p>
                        </div>
                         @if($userType == 'Admin' || $isHod)
                        <button class="btn btn-primary-custom" data-toggle="modal" data-target="#addTaskModal">
                            <i class="fa fa-plus"></i> Assign Task
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Task / Action Plan</th>
                                        <th>Assigned To</th>
                                        <th>KPI (Weight)</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tasks as $task)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $task->kpi }}</div>
                                                <div class="small text-muted">Plan: {{ $task->actionPlan->title }}</div>
                                            </td>
                                            <td>{{ $task->assignee->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-info text-dark">{{ $task->weight }}% Weight</span>
                                            </td>
                                            <td>
                                                <div class="small {{ \Carbon\Carbon::parse($task->due_date)->isPast() ? 'text-danger fw-bold' : '' }}">
                                                    {{ $task->due_date }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $task->status == 'Pending' ? 'bg-secondary' : ($task->status == 'Completed' ? 'bg-info' : ($task->status == 'Approved' ? 'bg-success' : 'bg-danger')) }}">
                                                    {{ $task->status }}
                                                </span>
                                                <div class="progress mt-2" style="height: 6px;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($task->progress / max($task->weight, 1)) * 100 }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ round(($task->progress / max($task->weight, 1)) * 100) }}% Complete</small>
                                            </td>
                                            <td>
                                                @if($task->assigned_to == Session::get('userID') && ($task->status == 'Pending' || $task->status == 'In Progress'))
                                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#subtasksModal{{ $task->taskID }}">
                                                        <i class="fa fa-list"></i> Manage Subtasks
                                                    </button>
                                                @endif
                                                
                                                @if(($userType == 'Admin' || $isHod) && $task->status == 'Completed')
                                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#evaluateModal{{ $task->taskID }}">
                                                        Evaluate
                                                    </button>
                                                @endif

                                                @if(($userType == 'Admin' || $isHod) && $task->subtasks->where('status', 'Submitted')->count() > 0)
                                                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#reviewSubtasksModal{{ $task->taskID }}">
                                                        <i class="fa fa-check-circle"></i> Review Subtasks ({{ $task->subtasks->where('status', 'Submitted')->count() }})
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Submit Progress Modal -->
                                        <div class="modal fade" id="submitProgressModal{{ $task->taskID }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('sgpm.tasks.submit', $task->taskID) }}" method="POST" enctype="multipart/form-data" class="submit-progress-form">
                                                        @csrf
                                                        <div class="modal-header text-white" style="background-color: #940000;">
                                                            <h5 class="modal-title">Submit Evidence for Review</h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Sending Progress Bar -->
                                                            <div class="sending-overlay" style="display:none; margin-bottom:12px;">
                                                                <div class="card border-0" style="background: linear-gradient(135deg,#fff5f5,#ffe8e8); border-left: 4px solid #940000 !important; border-left: solid;">
                                                                    <div class="card-body py-2">
                                                                        <div class="d-flex align-items-center mb-1">
                                                                            <i class="fa fa-cloud-upload fa-spin text-danger mr-2"></i>
                                                                            <strong style="color:#940000;">Sending to Admin for Review...</strong>
                                                                        </div>
                                                                        <div class="progress" style="height:8px;border-radius:4px;background:#f0e0e0;">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:100%;background-color:#940000 !important;"></div>
                                                                        </div>
                                                                        <small class="text-muted">Please wait...</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Work Remarks</label>
                                                                <textarea name="remarks" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Upload Evidence (PDF/Image/Doc)</label>
                                                                <input type="file" name="evidence_file" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn text-white" style="background-color:#940000;">
                                                                <i class="fa fa-send"></i> Submit for Admin Review
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Evaluate Modal -->
                                        <div class="modal fade" id="evaluateModal{{ $task->taskID }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('sgpm.tasks.evaluate', $task->taskID) }}" method="POST" class="evaluate-task-form">
                                                        @csrf
                                                        <div class="modal-header text-white bg-warning">
                                                            <h5 class="modal-title">Task Evaluation & Scoring</h5>
                                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Score for Completion (0-100)</label>
                                                                <input type="number" name="score_completion" class="form-control" min="0" max="100" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Score for Quality/KPI (0-100)</label>
                                                                <input type="number" name="score_kpi" class="form-control" min="0" max="100" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Status</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="Approved">Approve & Score</option>
                                                                    <option value="Rejected">Reject (Requires Resubmission)</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Comments</label>
                                                                <textarea name="hod_comments" class="form-control" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-warning eval-submit-btn">Save Score</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No tasks assigned yet.</td>
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

<!-- Subtask Modals (Outside table to prevent z-index issues) -->
@foreach($tasks as $task)
    @if($task->assigned_to == Session::get('userID'))
        @include('sgpm.tasks._subtask_modal', ['task' => $task])
    @endif
    
    @if($userType == 'Admin' || $isHod)
        @include('sgpm.tasks._hod_review_subtasks', ['task' => $task])
    @endif
@endforeach

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0" style="border-radius: 15px;">
            <form action="{{ route('sgpm.tasks.store') }}" method="POST" class="ajax-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Task to Staff</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Action Plan</label>
                        <select name="action_planID" class="form-select" required>
                            <option value="">-- Select Action Plan --</option>
                            @foreach($actionPlans as $plan)
                                <option value="{{ $plan->action_planID }}">{{ $plan->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">-- Select Staff/Teacher --</option>
                            @foreach($users as $u)
                                @php
                                    $profile = $u->teacher ?? $u->staff;
                                    $fullName = $profile ? ($profile->first_name . ' ' . ($profile->middle_name ? $profile->middle_name . ' ' : '') . $profile->last_name) : $u->name;
                                @endphp
                                <option value="{{ $u->id }}">{{ $fullName }} ({{ $u->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Description</label>
                        <textarea name="kpi" class="form-control" rows="2" placeholder="What needs to be done?" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Weight (0-100%)</label>
                        <input type="number" name="weight" class="form-control" min="1" max="100" value="20" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')

<script>
// Clean up modal backdrops to prevent them from getting stuck
$(document).on('hidden.bs.modal', '.modal', function () {
    if ($('.modal:visible').length) return; // Don't remove if another modal open
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
});

// ====================================================
// Handle: Create New Sub-task (form inside modal)
// ====================================================
$(document).on('submit', '.subtask-create-form', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('.subtask-save-btn');
    const originalHtml = $btn.html();
    const taskId = $form.data('task');

    $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

    $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: response.message,
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message || 'Failed to add sub-task', 'error');
                $btn.html(originalHtml).prop('disabled', false);
            }
        },
        error: function(xhr) {
            let errMsg = 'Failed to add sub-task';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                    errMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
            }
            Swal.fire('Error', errMsg, 'error');
            $btn.html(originalHtml).prop('disabled', false);
        }
    });
});

// ====================================================
// Handle: Send Sub-task to HOD / Resubmit
// ====================================================
$(document).on('click', '.send-subtask-btn', function(e) {
    e.preventDefault();
    const $btn = $(this);
    const subtaskId = $btn.data('id');
    const taskId = $btn.data('task');
    const label = $btn.data('label') || 'Sending to HOD...';
    const originalHtml = $btn.html();

    // Show progress bar inside modal
    const $progressBox = $('#sendingProgress' + taskId);
    const $progressLabel = $('#sendingLabel' + taskId);
    $progressLabel.text(label);
    $progressBox.slideDown(200);

    // Disable the button
    $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

    $.ajax({
        url: '/sgpm/subtasks/' + subtaskId + '/submit',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $progressLabel.text('✓ Sent successfully! Reloading...');
                setTimeout(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sent!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }, 400);
            } else {
                Swal.fire('Error', response.message || 'Failed to send', 'error');
                $progressBox.slideUp(200);
                $btn.html(originalHtml).prop('disabled', false);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to send sub-task';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            Swal.fire('Error', errorMsg, 'error');
            $progressBox.slideUp(200);
            $btn.html(originalHtml).prop('disabled', false);
        }
    });
});

// ====================================================
// Handle: HOD Reject Sub-task (with reason prompt)
// ====================================================
$(document).on('click', '.approve-subtask', function(e) {
    e.preventDefault();
    const $btn = $(this);
    const subtaskId = $btn.data('id');
    const action = $btn.data('action'); // 'reject'
    const originalHtml = $btn.html();

    Swal.fire({
        title: 'Reject Sub-task?',
        text: 'This will return the sub-task to the staff member for revision.',
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Enter reason for rejection (optional)...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Reject it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
            
            $.ajax({
                url: `/sgpm/subtasks/${subtaskId}/${action}`,
                method: 'POST',
                data: { hod_comments: result.value || '' },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Rejected!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errMsg = xhr.responseJSON?.message || 'An error occurred';
                    Swal.fire('Error', errMsg, 'error');
                    $btn.html(originalHtml).prop('disabled', false);
                }
            });
        }
    });
});

// ====================================================
// Handle: HOD Approve Sub-task (with score modal)
// ====================================================
$(document).on('submit', '.approve-subtask-form', function(e) {
    e.preventDefault();
    const $form = $(this);
    const subtaskId = $form.data('id');
    const formData = $form.serialize();
    const $submitBtn = $form.find('button[type="submit"]');
    const originalHtml = $submitBtn.html();
    
    $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Approving...').prop('disabled', true);
    
    $.ajax({
        url: `/sgpm/subtasks/${subtaskId}/approve`,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Approved!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message || 'Could not approve sub-task', 'error');
                $submitBtn.html(originalHtml).prop('disabled', false);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to approve subtask';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            Swal.fire('Error', errorMsg, 'error');
            $submitBtn.html(originalHtml).prop('disabled', false);
        }
    });
});

// Filter subtasks in HOD review modal
$(document).on('change', '.filter-member, .filter-status', function() {
    const $table = $(this).closest('.modal-body').find('.subtasks-table');
    const taskID = $(this).data('task');
    const selectedMember = $(this).closest('.modal-body').find('.filter-member').val();
    const selectedStatus = $(this).closest('.modal-body').find('.filter-status').val();
    
    $table.find('.subtask-row').each(function() {
        const $row = $(this);
        const rowMember = $row.data('member').toString();
        const rowStatus = $row.data('status');
        
        let showRow = true;
        
        // Filter by member
        if (selectedMember && rowMember !== selectedMember) {
            showRow = false;
        }
        
        // Filter by status
        if (selectedStatus && rowStatus !== selectedStatus) {
            showRow = false;
        }
        
        if (showRow) {
            $row.show();
        } else {
            $row.hide();
        }
    });
    
    // Show "no results" message if all rows are hidden
    const visibleRows = $table.find('.subtask-row:visible').length;
    $table.find('.no-results-row').remove();
    if (visibleRows === 0) {
        $table.find('tbody').append(
            '<tr class="no-results-row"><td colspan="7" class="text-center text-muted py-3">No subtasks match the selected filters.</td></tr>'
        );
    }
    });
});

// ====================================================
// Handle: Submit Evidence for Admin Review (task-level)
// ====================================================
$(document).on('submit', '.submit-progress-form', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $submitBtn = $form.find('button[type="submit"]');
    const originalHtml = $submitBtn.html();
    const $overlay = $form.find('.sending-overlay');

    $overlay.slideDown(200);
    $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Submitting...').prop('disabled', true);

    $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Submitted!',
                    text: response.message || 'Evidence submitted for Admin review.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', response.message || 'Could not submit', 'error');
                $overlay.slideUp(200);
                $submitBtn.html(originalHtml).prop('disabled', false);
            }
        },
        error: function(xhr) {
            let errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to submit evidence';
            Swal.fire('Error', errMsg, 'error');
            $overlay.slideUp(200);
            $submitBtn.html(originalHtml).prop('disabled', false);
        }
    });
});

// ====================================================
// Handle: Evaluate Task (HOD/Admin scoring)
// ====================================================
$(document).on('submit', '.evaluate-task-form', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $submitBtn = $form.find('.eval-submit-btn');
    const originalHtml = $submitBtn.html();

    $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

    $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: response.message || 'Evaluation saved.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', response.message || 'Could not save evaluation', 'error');
                $submitBtn.html(originalHtml).prop('disabled', false);
            }
        },
        error: function(xhr) {
            let errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to save evaluation';
            Swal.fire('Error', errMsg, 'error');
            $submitBtn.html(originalHtml).prop('disabled', false);
        }
    });
});
</script>
