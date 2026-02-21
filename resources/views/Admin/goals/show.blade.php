@include('includes.Admin_nav')

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<style>
    .bg-maroon { background-color: #940000 !important; }
    .text-maroon { color: #940000 !important; }
    .btn-maroon { background-color: #940000 !important; color: white !important; }
    
    .mobile-header {
        position: sticky; top: 0; z-index: 1020;
        background: #940000; color: white; padding: 15px; margin: -15px -15px 20px -15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none;
        width: calc(100% + 30px);
    }

    @media (max-width: 767px) {
        .mobile-header { display: flex; justify-content: space-between; align-items: center; }
        .desktop-header { display: none; }
        .content { padding-top: 5px !important; overflow-x: hidden; }
    }

    .goal-card-compact {
        background: #fff; border-radius: 16px; border: 1px solid #eee;
        transition: transform 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        overflow: hidden;
        word-wrap: break-word;
    }
    .goal-card-compact:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
    }
    .progress-sm { height: 6px !important; }
    .text-maroon { color: #940000 !important; }
    .text-primary-shule { color: #940000 !important; }
    .bg-primary-shule { background-color: #940000 !important; }
</style>

<div class="mobile-header">
    <div class="d-flex align-items-center">
        <a href="{{ route('admin.goals.index') }}" class="text-white mr-3"><i class="fa fa-arrow-left fa-lg"></i></a>
        <h6 class="mb-0 font-weight-bold text-truncate" style="max-width: 200px;">{{ $goal->goal_name }}</h6>
    </div>
    <div class="badge badge-light text-maroon p-2" style="border-radius: 10px;">{{ number_format($goal->progress, 1) }}%</div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <!-- Goal Overview Header - Desktop Only -->
                <div class="card shadow-sm border-0 mb-3 desktop-header" style="border-radius: 12px; background: #fff; border-left: 5px solid #940000 !important;">
                    <div class="card-body py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1" style="font-weight: 800; color: #2f2f2f;">{{ $goal->goal_name }}</h4>
                                <div class="d-flex align-items-center small text-muted">
                                    <span class="mr-3"><i class="fa fa-calendar-o mr-1 text-maroon"></i> {{ \Carbon\Carbon::parse($goal->deadline)->format('d M, Y') }}</span>
                                    <span><i class="fa fa-bullseye mr-1 text-maroon"></i> Target: <b>{{ $goal->target_percentage }}%</b></span>
                                </div>
                            </div>
                            <div class="text-center bg-light px-3 py-2 rounded shadow-sm" style="min-width: 110px;">
                                <div class="h4 mb-0 font-weight-bold text-maroon">{{ (float)$goal->progress == (int)$goal->progress ? number_format($goal->progress, 0) : rtrim(rtrim(number_format($goal->progress, 2, '.', ''), '0'), '.') }}%</div>
                                <div class="small text-muted" style="font-size: 0.65rem; text-transform: uppercase; font-weight: 700;">Progress</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar (Desktop) -->
                <div class="d-flex justify-content-between align-items-center mb-3 desktop-header">
                    <h5 style="color: #2f2f2f; font-weight: 800; margin: 0;">Task Breakdown</h5>
                    <a href="{{ route('admin.goals.index') }}" class="btn btn-sm btn-outline-secondary px-3" style="border-radius: 20px;">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="row">
                    @forelse($goal->tasks as $task)
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="card h-100 goal-card-compact shadow-sm bg-white overflow-hidden">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="mb-0 pr-2" style="font-weight: 700; color: #333; line-height: 1.4;" title="{{ $task->task_name }}">
                                            {{ $task->task_name }}
                                        </h6>
                                        <span class="badge badge-light text-primary-shule p-1" style="font-size: 0.75rem;">{{ $task->weight }}%</span>
                                    </div>
                                    
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <div style="flex:1;">
                                            <small class="text-muted" style="font-size: 0.75rem;">Assigned to:</small>
                                            <div class="text-dark font-weight-600 small" style="line-height: 1.3;">
                                                <i class="fa fa-user-circle-o text-muted mr-1"></i> {{ $task->assignee_name }}
                                            </div>
                                        </div>
                                        @if($task->assignee_phone && $task->assignee_phone != 'N/A')
                                            <div class="dropdown">
                                                <button class="btn btn-xs btn-light text-success border-0 rounded-circle" data-toggle="dropdown" title="Contact">
                                                    <i class="fa fa-whatsapp"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                                    <a class="dropdown-item small" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $task->assignee_phone) }}" target="_blank">
                                                        <i class="fa fa-whatsapp text-success mr-2"></i> WhatsApp HOD
                                                    </a>
                                                    <a class="dropdown-item small" href="tel:{{ $task->assignee_phone }}">
                                                        <i class="fa fa-phone text-primary mr-2"></i> Call HOD
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                                            <span class="text-muted">Progress</span>
                                            <span class="font-weight-bold {{ $task->progress == 100 ? 'text-success' : 'text-primary-shule' }}">{{ (float)$task->progress == (int)$task->progress ? number_format($task->progress, 0) : rtrim(rtrim(number_format($task->progress, 6, '.', ''), '0'), '.') }}%</span>
                                        </div>
                                        <div class="progress progress-sm rounded-pill bg-light">
                                            <div class="progress-bar {{ $task->progress == 100 ? 'bg-success' : 'bg-primary-shule' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $task->progress }}%;" 
                                                 aria-valuenow="{{ $task->progress }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                        @php
                                            $totalSub = 0;
                                            $doneSub = 0;
                                            foreach($task->memberTasks as $m) $totalSub += $m->subtasks->count();
                                            foreach($task->memberTasks as $m) $doneSub += $m->subtasks->where('is_approved', true)->count();
                                            $totalSub += $task->subtasks->count();
                                            $doneSub += $task->subtasks->where('is_approved', true)->count();
                                        @endphp
                                        <div class="small text-muted">
                                            <i class="fa fa-list-ul mr-1"></i> {{ $doneSub }}/{{ $totalSub }} Tasks
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-xs btn-outline-danger btn-view-full-task mr-1" 
                                                data-id="{{ $task->id }}" 
                                                data-name="{{ $task->task_name }}" 
                                                style="font-size: 0.7rem; border-radius: 4px; padding: 2px 8px; border-color: #940000; color: #940000;">
                                                <i class="fa fa-sitemap"></i> Breakdown
                                            </button>
                                            <div class="dropdown">
                                                <button class="btn btn-xs btn-light text-muted p-0" data-toggle="dropdown" style="width:24px; height:24px;">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                                    <a class="dropdown-item small btn-edit-task" href="javascript:void(0)" data-id="{{ $task->id }}">
                                                        <i class="fa fa-edit text-info mr-2"></i> Edit Task
                                                    </a>
                                                    <a class="dropdown-item small text-danger btn-delete-task" href="javascript:void(0)" data-id="{{ $task->id }}">
                                                        <i class="fa fa-trash mr-2"></i> Delete Task
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="fa fa-folder-open-o fa-3x text-muted mb-3" style="opacity: 0.2;"></i>
                            <p class="text-muted">Hakuna majukumu yaliyopangwa bado.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Task -->
<div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title font-weight-bold"><i class="fa fa-edit mr-2"></i>Edit Task</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editTaskForm">
                @csrf
                <input type="hidden" id="edit_task_id" name="task_id">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600">Task Name</label>
                        <input type="text" id="edit_task_name" name="task_name" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-600">Assign To Type</label>
                            <select id="edit_assigned_to_type" name="assigned_to_type" class="form-control" required>
                                <option value="Department">Department</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-600">Target Entity</label>
                            <select id="edit_assigned_to_id" name="assigned_to_id" class="form-control" required>
                                <option value="">Select Target</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-600">Weight (%)</label>
                            <input type="number" id="edit_task_weight" name="weight" class="form-control" required min="1" max="100">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-600">Current Progress</label>
                            <div class="input-group">
                                <input type="text" id="edit_current_progress" class="form-control" readonly>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-600">Description</label>
                        <textarea id="edit_task_description" name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div id="edit_progress_container" class="mt-3" style="display:none;">
                        <small id="edit_status_text" class="text-muted d-block mb-1">Saving changes...</small>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div id="edit_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 20px;">Cancel</button>
                    <button type="submit" id="btn_update_task" class="btn btn-maroon px-4" style="border-radius: 20px;">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Full Breakdown Modal -->
<div class="modal fade" id="taskFullBreakdownModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="modal-title"><i class="fa fa-sitemap mr-2"></i>Task Breakdown: <span id="full_task_name_display"></span></h5>
                <button type="button" class="close text-white ml-0" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light" id="full_task_breakdown_content" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">Loading breakdown...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Review/Mark Subtask -->
<div class="modal fade" id="reviewSubtaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title font-weight-bold"><i class="fa fa-check-square-o mr-2"></i>Review Subtask</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="reviewSubtaskForm">
                @csrf
                <input type="hidden" name="id" id="review_sub_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Subtask Name:</small>
                        <h6 id="review_sub_name" class="font-weight-bold text-dark"></h6>
                    </div>

                    <input type="hidden" name="action" value="Approve">

                    <div class="form-group mb-3">
                        <label class="font-weight-600">Marks / Score (Max: <span id="review_sub_weight_label">0</span>)</label>
                        <div class="input-group">
                            <input type="number" name="marks" id="review_sub_marks" class="form-control" required min="0" step="0.1">
                            <div class="input-group-append">
                                <span class="input-group-text">/ <span id="review_sub_weight_val">0</span></span>
                            </div>
                        </div>
                        <small class="text-muted italic">Assessed marks will contribute directly to task and goal completion percentage.</small>
                    </div>

                    <div id="review_progress_container" style="display:none;">
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div id="review_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 20px;">Cancel</button>
                    <button type="submit" id="btn_approve_subtask" class="btn btn-success px-4" style="border-radius: 20px;">Approve & Mark</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Breakdown View Logic
    $('.btn-view-full-task').click(function() {
        const taskId = $(this).data('id');
        const taskName = $(this).data('name');
        
        $('#full_task_name_display').text(taskName);
        $('#full_task_breakdown_content').html(`
            <div class="text-center py-5">
                <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2 text-muted">Fetching all subtasks and steps...</p>
            </div>
        `);
        $('#taskFullBreakdownModal').modal('show');

        $.get('{{ url("goals/task/full-structure") }}/' + taskId, function(task) {
            let html = '';

            // 1. Direct Subtasks (HOD Level Breakdown)
            if (task.subtasks && task.subtasks.length > 0) {
                html += `
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-primary-shule mb-3">
                            <i class="fa fa-shield mr-2 text-primary"></i> Admin Progress Breakdown (Direct Assignments)
                        </h6>
                        <div class="list-group shadow-sm">
                `;
                task.subtasks.forEach(sub => {
                    html += renderSubtaskHtml(sub);
                });
                html += `</div></div>`;
            }

            // 2. Member Tasks (Delegated to Staff)
            if (task.member_tasks && task.member_tasks.length > 0) {
                html += `
                    <div class="mb-2">
                        <h6 class="font-weight-bold text-success mb-3">
                            <i class="fa fa-users mr-2"></i> Delegated to Team Members
                        </h6>
                `;
                task.member_tasks.forEach(mTask => {
                    const weightEarned = parseFloat(mTask.weight_earned).toFixed(2);
                    const progressVal = parseFloat(mTask.progress_percent).toFixed(2);
                    
                    html += `
                        <div class="card mb-4 border-0 shadow-sm" style="border-radius:12px; border-left: 5px solid #28a745 !important;">
                            <div class="card-header bg-white py-3" style="border-radius:12px 12px 0 0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold text-dark h6 mb-0">
                                        <i class="fa fa-user-circle mr-2 text-success"></i>${mTask.member_name}
                                    </span>
                                    <span class="badge badge-light border text-success">${mTask.weight}% allocation</span>
                                </div>
                                
                                <div class="row align-items-center no-gutters mt-2">
                                    <div class="col-8 pr-3">
                                        <div class="progress" style="height: 8px; border-radius: 4px; background: #e9ecef;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: ${progressVal}%;"></div>
                                        </div>
                                    </div>
                                    <div class="col-4 text-right">
                                        <small class="font-weight-bold text-success">${progressVal}% Done</small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted"><i class="fa fa-line-chart mr-1"></i>Earned: <b>${weightEarned}%</b> of Goal</small>
                                    <small class="text-muted"><i class="fa fa-clock-o mr-1"></i><b>${mTask.pending_count}</b> Tasks Pending</small>
                                </div>
                            </div>
                            <div class="card-body p-3 bg-white">
                                <p class="small text-muted mb-3 border-bottom pb-2"><b>Assigned Task:</b> ${mTask.task_name}</p>
                    `;
                    
                    if (mTask.subtasks && mTask.subtasks.length > 0) {
                        html += `<div class="list-group list-group-flush">`;
                        mTask.subtasks.forEach(sub => {
                            html += renderSubtaskHtml(sub);
                        });
                        html += `</div>`;
                    } else {
                        html += `<p class="small text-center text-muted italic">No subtasks defined yet.</p>`;
                    }
                    
                    html += `</div></div>`;
                });
                html += `</div>`;
            }

            if (html === '') {
                html = `
                    <div class="text-center py-5">
                        <i class="fa fa-info-circle fa-2x text-muted mb-2"></i>
                        <p>No breakdown found for this task.</p>
                    </div>
                `;
            }

            $('#full_task_breakdown_content').html(html);
        });
    });

    // Helper: Render Subtask HTML
    function renderSubtaskHtml(sub) {
        let stepsHtml = '';
        if (sub.steps && sub.steps.length > 0) {
            stepsHtml += `<div class="mt-2 pl-3 border-left ml-2" style="border-color: #eee !important;">`;
            sub.steps.forEach(step => {
                stepsHtml += `
                    <div class="small mb-1 d-flex">
                        <i class="fa fa-check-circle-o text-success mr-2 mt-1" style="font-size:0.7rem;"></i>
                        <span class="text-muted">${step.step_description} <small>(${step.date})</small></span>
                    </div>
                `;
            });
            stepsHtml += `</div>`;
        }

        const isApproved = sub.is_approved ? true : false;
        const statusColor = isApproved ? 'success' : (sub.status === 'Submitted' ? 'warning' : 'secondary');
        const statusText = isApproved ? 'APPROVED' : (sub.status === 'Submitted' ? 'PENDING REVIEW' : 'DRAFT');

        return `
            <div class="list-group-item bg-light border-0 mb-2 p-3 shadow-xs" style="border-radius:12px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <span class="font-weight-bold text-dark d-block mb-1" style="font-size: 0.9rem;">${sub.subtask_name}</span>
                        <div class="small text-muted mb-2" style="font-size: 0.8rem; line-height: 1.4;">${sub.description || 'No description'}</div>
                        
                        <div class="mb-2">
                            <span class="badge badge-pill badge-${statusColor} mr-2" style="font-size: 0.65rem; padding: 4px 8px;">${statusText}</span>
                        </div>

                        ${(sub.status || '').toLowerCase() === 'submitted' && (!sub.is_approved) ? `
                            <div class="d-flex" style="gap:8px; margin-top: 10px;">
                                <button class="btn btn-xs btn-success btn-approve-subtask-trigger py-2 px-3 shadow-sm" 
                                    data-id="${sub.id}" data-name="${sub.subtask_name}" data-weight="${sub.weight}"
                                    style="font-size: 0.75rem; border-radius: 20px; font-weight: 700;">
                                    <i class="fa fa-check-circle"></i> Approve & Mark
                                </button>
                                <button class="btn btn-xs btn-danger btn-reject-subtask-trigger py-2 px-3 shadow-sm" 
                                    data-id="${sub.id}" data-name="${sub.subtask_name}"
                                    style="font-size: 0.75rem; border-radius: 20px; font-weight: 700;">
                                    <i class="fa fa-times-circle"></i> Reject
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    <div class="text-right ml-3" style="min-width: 100px;">
                        <div class="small text-muted uppercase font-weight-bold mb-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">Performance</div>
                        <div class="h6 mb-0 font-weight-bold ${isApproved ? 'text-success' : 'text-muted'}" style="font-size: 0.95rem;">
                            ${sub.marks || 0} / <span class="text-muted">${sub.weight}</span>
                        </div>
                        <div class="small font-weight-bold text-maroon" style="font-size: 0.7rem;">${sub.weight}% Weight</div>
                    </div>
                </div>
                ${stepsHtml}
            </div>
        `;
    }

    // --- Task Edit Logic ---
    $(document).on('click', '.btn-edit-task', function() {
        const taskId = $(this).data('id');
        
        $.get('{{ url("goals/task/details") }}/' + taskId, function(task) {
            $('#edit_task_id').val(task.id);
            $('#edit_task_name').val(task.task_name);
            $('#edit_assigned_to_type').val(task.assigned_to_type).trigger('change');
            $('#edit_task_weight').val(task.weight);
            $('#edit_current_progress').val(task.progress);
            $('#edit_task_description').val(task.description);
            
            // Set target id after fetching options
            setTimeout(() => {
                $('#edit_assigned_to_id').val(task.assigned_to_id);
            }, 500);

            $('#editTaskModal').modal('show');
        });
    });

    $(document).on('change', '#edit_assigned_to_type', function() {
        const type = $(this).val();
        const $targetSelect = $('#edit_assigned_to_id');
        
        $targetSelect.empty().append('<option value="">Loading...</option>').prop('disabled', true);
        if (!type) return;

        $.get('{{ url("goals/fetch-targets") }}/' + type, function(data) {
            $targetSelect.empty().append('<option value="">Select Target</option>');
            data.forEach(item => {
                $targetSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
            });
            $targetSelect.prop('disabled', false);
        });
    });

    $('#editTaskForm').submit(function(e) {
        e.preventDefault();
        const taskId = $('#edit_task_id').val();
        const $btn = $('#btn_update_task');
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
        $('#edit_progress_container').show();
        $('#edit_progress_bar').css('width', '50%');

        $.ajax({
            url: '{{ url("goals/task/update") }}/' + taskId,
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#edit_progress_bar').css('width', '100%');
                    Swal.fire('Updated!', 'Task details updated successfully.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Update failed', 'error');
                    $btn.prop('disabled', false).html('Update Task');
                }
            },
            error: function() {
                Swal.fire('Error', 'Server communication failed.', 'error');
                $btn.prop('disabled', false).html('Update Task');
            }
        });
    });

    // Approve Trigger
    $(document).on('click', '.btn-approve-subtask-trigger', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const weight = $(this).data('weight');

        $('#review_sub_id').val(id);
        $('#review_sub_name').text(name);
        $('#review_sub_weight_label, #review_sub_weight_val').text(weight);
        $('#review_sub_marks').attr('max', weight).val(weight); // Default to full marks
        
        $('#reviewSubtaskModal').modal('show');
    });

    // Reject Trigger
    $(document).on('click', '.btn-reject-subtask-trigger', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Reject Subtask?',
            text: `Unataka kuirudisha subtask "${name}" kwa mwalimu ili afanye marekebisho? Hali ya kazi itarudi kuwa Draft.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ndiyo, Irudishe'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("goals.review.approve") }}', {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    action: 'Reject'
                }, function(res) {
                    if (res.success) {
                        Swal.fire('Imerejeshwa!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Hitilafu', res.message, 'error');
                    }
                });
            }
        });
    });

    $('#reviewSubtaskForm').submit(function(e) {
        e.preventDefault();
        const $btn = $('#btn_approve_subtask');
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $('#review_progress_container').show();
        $('#review_progress_bar').css('width', '100%');

        $.ajax({
            url: '{{ route("goals.review.approve") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Review failed', 'error');
                    $btn.prop('disabled', false).text('Save Review');
                }
            },
            error: function() {
                Swal.fire('Error', 'Server communication failed.', 'error');
                $btn.prop('disabled', false).text('Save Review');
            }
        });
    });
});
</script>

@include('includes.footer')
