@include('includes.Admin_nav')

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<style>
    .bg-maroon { background-color: #940000 !important; }
    .text-maroon { color: #940000 !important; }
    .btn-maroon { background-color: #940000 !important; color: white !important; border-radius: 8px; }
    .btn-outline-maroon { border: 1.5px solid #940000; color: #940000; background: transparent; border-radius: 8px; font-weight: 600; }
    .badge-outline-maroon { border: 1.2px solid #940000; color: #940000; background: rgba(148, 0, 0, 0.05); font-weight: 700; }
    
    /* Desktop Table Styling */
    @media (min-width: 768px) {
        .mobile-card-view { display: none; }
        .desktop-table-view { display: block; }
    }

    /* Mobile Card Styling */
    @media (max-width: 767px) {
        .desktop-table-view { display: none; }
        .mobile-card-view { display: block; }
        .content { padding: 0 10px !important; }
    }

    .goal-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 15px;
        border-left: 5px solid #940000;
        overflow: hidden;
        transition: transform 0.2s;
    }
    .goal-card:active { transform: scale(0.98); }
    
    .card-progress-strip { height: 4px; background: #f0f0f0; width: 100%; }
    .card-progress-fill { height: 100%; background: #940000; }
</style>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white" style="border-bottom: 1px solid #eee; padding: 15px 20px;">
                        <h5 class="mb-0" style="color: #2f2f2f; font-weight: 800;">
                            <i class="fa fa-bullseye text-maroon mr-2"></i>School Goals
                        </h5>
                        <a href="{{ route('admin.goals.create') }}" class="btn btn-maroon btn-sm px-3 shadow-sm">
                            <i class="fa fa-plus-circle mr-1"></i> New Goal
                        </a>
                    </div>
                    
                    <div class="card-body p-0">
                        <!-- Desktop Table -->
                        <div class="desktop-table-view">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="vertical-align: middle;">
                                    <thead class="bg-light">
                                        <tr class="small text-uppercase font-weight-bold text-muted">
                                            <th class="border-0 px-4 py-3">Goal Name</th>
                                            <th class="border-0 py-3">Target</th>
                                            <th class="border-0 py-3">Deadline</th>
                                            <th class="border-0 py-3" style="width: 180px;">Progress</th>
                                            <th class="border-0 text-right py-3 px-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($goals as $goal)
                                            <tr>
                                                <td class="px-4 py-3 font-weight-bold text-dark">{{ $goal->goal_name }}</td>
                                                <td class="py-3">
                                                    <span class="badge badge-outline-maroon px-2 py-1" style="font-size: 0.85rem;">{{ $goal->target_percentage }}%</span>
                                                </td>
                                                <td class="py-3 text-muted small">{{ \Carbon\Carbon::parse($goal->deadline)->format('d M, Y') }}</td>
                                                <td class="py-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-maroon font-weight-bold">{{ (float)$goal->progress == (int)$goal->progress ? number_format($goal->progress, 0) : rtrim(rtrim(number_format($goal->progress, 2, '.', ''), '0'), '.') }}%</small>
                                                    </div>
                                                    <div class="progress" style="height: 6px; border-radius: 3px; background: #f0f0f0;">
                                                        <div class="progress-bar bg-maroon" style="width: {{ $goal->progress }}%;"></div>
                                                    </div>
                                                </td>
                                                <td class="text-right py-3 px-4">
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-maroon btn-assign-task mr-1" data-id="{{ $goal->id }}" data-name="{{ $goal->goal_name }}">
                                                            <i class="fa fa-tasks"></i> Assign
                                                        </button>
                                                        <a href="{{ route('admin.goals.show', $goal->id) }}" class="btn btn-sm btn-outline-info mr-1">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-light btn-edit-goal mr-1" data-id="{{ $goal->id }}">
                                                            <i class="fa fa-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-light text-danger btn-delete-goal" data-id="{{ $goal->id }}">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center py-5 text-muted">No goals found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="mobile-card-view p-2">
                            @forelse($goals as $goal)
                                <div class="goal-card">
                                    <div class="p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="font-weight-bold text-dark mb-0 line-height-1-4" style="word-break: break-word;">{{ $goal->goal_name }}</h6>
                                            <span class="badge badge-outline-maroon ml-2">{{ $goal->target_percentage }}%</span>
                                        </div>
                                        <div class="d-flex align-items-center text-muted small mb-3">
                                            <i class="fa fa-calendar-o mr-1"></i> Ends: {{ \Carbon\Carbon::parse($goal->deadline)->format('d M, Y') }}
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mb-1 small">
                                            <span class="text-muted">Current Progress</span>
                                            <span class="text-maroon font-weight-bold">{{ number_format($goal->progress, 2) }}%</span>
                                        </div>
                                        <div class="card-progress-strip mb-3">
                                            <div class="card-progress-fill" style="width: {{ $goal->progress }}%;"></div>
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <button class="btn btn-xs btn-maroon btn-assign-task flex-grow-1 mr-2" data-id="{{ $goal->id }}" data-name="{{ $goal->goal_name }}">
                                                <i class="fa fa-plus-circle"></i> Assign
                                            </button>
                                            <a href="{{ route('admin.goals.show', $goal->id) }}" class="btn btn-xs btn-outline-maroon flex-grow-1 mr-2">
                                                <i class="fa fa-line-chart"></i> Detail
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-xs btn-light" data-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item small btn-edit-goal" href="javascript:void(0)" data-id="{{ $goal->id }}"><i class="fa fa-pencil mr-2"></i> Edit</a>
                                                    <a class="dropdown-item small text-danger btn-delete-goal" href="javascript:void(0)" data-id="{{ $goal->id }}"><i class="fa fa-trash mr-2"></i> Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">No goals planned.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Multiple Task Assignment -->
<div class="modal fade" id="assignTaskModal" tabindex="-1" role="dialog" aria-labelledby="assignTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #940000; color: white;">
                <h5 class="modal-title" id="assignTaskModalLabel">Assign Tasks to Goal: <span id="modal_goal_name"></span></h5>
                <div id="goal_budget_display" class="px-3 py-1 bg-white text-dark rounded-pill shadow-sm" style="font-size: 0.85rem; font-weight: 700;">
                    Budget: <span id="budget_goal_weight">0</span>% | 
                    Left: <span id="budget_remaining">0</span>%
                </div>
                <button type="button" class="close text-white ml-0" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignTaskForm">
                @csrf
                <input type="hidden" name="goal_id" id="modal_goal_id">
                <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                    
                    <!-- Existing Tasks Summary -->
                    <div id="existing_tasks_section" class="mb-4" style="display: none;">
                        <h6 class="font-weight-bold mb-2 text-muted small uppercase">Current Delegations</h6>
                        <div id="existing_tasks_list" class="list-group list-group-flush border rounded bg-white shadow-sm">
                            <!-- Existing tasks loaded here -->
                        </div>
                    </div>

                    <h6 class="font-weight-bold mb-3 text-maroon small uppercase">Add New Tasks</h6>
                    <div id="tasks_container">
                        <div class="task-row border p-3 mb-3" style="border-radius: 10px; position: relative;">
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label>Task Name</label>
                                    <input type="text" name="tasks[0][task_name]" class="form-control" required placeholder="Task name">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Assign To Type</label>
                                    <select name="tasks[0][assigned_to_type]" class="form-control assigned_to_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Department">Department</option>
                                        <option value="Teacher">Teacher</option>
                                        <option value="Staff">Staff</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Target Entity</label>
                                    <select name="tasks[0][assigned_to_id]" class="form-control assigned_to_id" required disabled>
                                        <option value="">Select Target</option>
                                    </select>
                                </div>
                                <div class="col-md-2 form-group">
                                    <label>Weight (%)</label>
                                    <input type="number" name="tasks[0][weight]" class="form-control" required min="1" max="100">
                                </div>
                                <div class="col-md-1 d-flex align-items-end mb-3">
                                    <button type="button" class="btn btn-danger btn-sm remove-task-row" disabled><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label>Description</label>
                                <textarea name="tasks[0][description]" class="form-control" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add_task_row" class="btn btn-outline-success btn-sm mt-2"><i class="fa fa-plus-circle"></i> Add Task Row</button>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div id="assign_progress_container" class="flex-grow-1 mr-3" style="display: none;">
                        <div class="progress" style="height: 20px; border-radius: 10px;">
                            <div id="assign_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <small class="text-muted" id="assign_status_text">Creating tasks...</small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 20px;">Cancel</button>
                        <button type="submit" id="btn_save_tasks" class="btn btn-primary" style="background-color: #940000; border: none; border-radius: 20px; padding: 8px 30px;">
                            <i class="fa fa-save"></i> Save All Tasks
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Viewing/Editing Tasks -->

<!-- Modal for Editing Goal -->
<div class="modal fade" id="editGoalModal" tabindex="-1" role="dialog" aria-labelledby="editGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header" style="background-color: #f39c12; color: white;">
                <h5 class="modal-title" id="editGoalModalLabel">Edit School Goal</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editGoalForm">
                @csrf
                <input type="hidden" name="id" id="edit_goal_id">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label>Goal Name</label>
                        <input type="text" name="goal_name" id="edit_goal_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Target Weight (%)</label>
                        <input type="number" name="target_percentage" id="edit_target_percentage" class="form-control" required min="1" max="100">
                    </div>
                    <div class="form-group mb-3">
                        <label>Deadline</label>
                        <input type="date" name="deadline" id="edit_deadline" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 20px;">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white" style="border-radius: 20px;">Update Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let taskRowCount = 0;

    // --- GOAL ACTIONS ---
    $('.btn-edit-goal').click(function() {
        const id = $(this).data('id');
        $.get('{{ url("goals/edit") }}/' + id, function(goal) {
            $('#edit_goal_id').val(goal.id);
            $('#edit_goal_name').val(goal.goal_name);
            $('#edit_target_percentage').val(goal.target_percentage);
            $('#edit_deadline').val(goal.deadline);
            $('#editGoalModal').modal('show');
        });
    });

    $('#editGoalForm').submit(function(e) {
        e.preventDefault();
        const id = $('#edit_goal_id').val();
        $.post('{{ url("goals/update") }}/' + id, $(this).serialize(), function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        });
    });

    $('.btn-delete-goal').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the goal and ALL associated tasks!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("goals/delete") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    });

    // --- MULTI-TASK ASSIGNMENT ---
    let global_goal_weight = 0;
    let global_assigned_weight = 0;

    $('.btn-assign-task').click(function() {
        const id = $(this).data('id');
        $('#modal_goal_id').val(id);
        $('#modal_goal_name').text($(this).data('name'));
        
        // Fetch current budget status and existing tasks
        $.get('{{ url("goals/tasks") }}/' + id, function(response) {
            global_goal_weight = parseFloat(response.goal.target_percentage);
            global_assigned_weight = parseFloat(response.total_assigned_weight);
            
            // Render existing tasks summary
            const $existingList = $('#existing_tasks_list').empty();
            if (response.tasks && response.tasks.length > 0) {
                $('#existing_tasks_section').show();
                response.tasks.forEach(task => {
                    $existingList.append(`
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 small">
                            <span class="text-dark"><i class="fa fa-tasks text-muted mr-2"></i><strong>${task.task_name}</strong> &rarr; ${task.assigned_name} (${task.assigned_to_type})</span>
                            <span class="badge badge-light border text-maroon font-weight-bold">${task.weight}%</span>
                        </div>
                    `);
                });
            } else {
                $('#existing_tasks_section').hide();
            }

            updateBudgetDisplay();

            // Handle case where budget is already full
            $('#tasks_container').empty();
            taskRowCount = 0;
            if (global_assigned_weight >= global_goal_weight) {
                $('#tasks_container').append('<div class="alert alert-warning border-0 shadow-sm text-center py-4" style="border-radius:12px;"><i class="fa fa-info-circle fa-2x mb-2"></i><br><b>Budget Depleted!</b><br>This goal is already fully assigned (100% of target). You cannot add more tasks unless you delete existing ones.</div>');
                $('#add_task_row').hide();
                $('#btn_save_tasks').hide();
            } else {
                $('#add_task_row').show();
                $('#btn_save_tasks').show();
                addTaskRow();
            }
        });

        $('#assignTaskModal').modal('show');
    });

    function updateBudgetDisplay() {
        let currentModalWeight = 0;
        $('.task-weight-input').each(function() {
            let val = parseFloat($(this).val()) || 0;
            currentModalWeight += val;
        });

        const totalUsed = global_assigned_weight + currentModalWeight;
        const remaining = global_goal_weight - totalUsed;

        $('#budget_goal_weight').text(global_goal_weight);
        $('#budget_remaining').text(remaining.toFixed(1));

        if (remaining < 0) {
            $('#budget_remaining').addClass('text-danger').removeClass('text-success').css('text-decoration', 'underline');
            $('#btn_save_tasks').prop('disabled', true).addClass('bg-secondary');
        } else {
            $('#budget_remaining').addClass('text-success').removeClass('text-danger').css('text-decoration', 'none');
            $('#btn_save_tasks').prop('disabled', false).removeClass('bg-secondary');
        }
    }

    $('#add_task_row').click(function() {
        let currentModalWeight = 0;
        $('.task-weight-input').each(function() {
            currentModalWeight += parseFloat($(this).val()) || 0;
        });

        if ((global_assigned_weight + currentModalWeight) >= global_goal_weight) {
            Swal.fire({
                title: 'Budget Full',
                text: 'Huwezi kuongeza jukumu jipya kwa sababu umeshafikia ukomo wa asilimia za dhumuni hili.',
                icon: 'warning'
            });
            return;
        }
        addTaskRow();
    });

    function addTaskRow() {
        const row = `
            <div class="task-row border p-3 mb-3" style="border-radius: 10px; position: relative; background: #f9f9f9;">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Task Name</label>
                        <input type="text" name="tasks[${taskRowCount}][task_name]" class="form-control" required placeholder="Task name">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Assign To Type</label>
                        <select name="tasks[${taskRowCount}][assigned_to_type]" class="form-control assigned_to_type" required>
                            <option value="">Select Type</option>
                            <option value="Department">Department</option>
                            <option value="Teacher">Teacher</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Target Entity</label>
                        <select name="tasks[${taskRowCount}][assigned_to_id]" class="form-control assigned_to_id" required disabled>
                            <option value="">Select Target</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Weight (%)</label>
                        <input type="number" name="tasks[${taskRowCount}][weight]" class="form-control task-weight-input" required min="1" max="100">
                    </div>
                    <div class="col-md-1 d-flex align-items-end mb-3">
                        <button type="button" class="btn btn-danger btn-sm remove-task-row"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Description</label>
                    <textarea name="tasks[${taskRowCount}][description]" class="form-control" rows="1" placeholder="Optional description"></textarea>
                </div>
            </div>
        `;
        $('#tasks_container').append(row);
        taskRowCount++;
        updateRemoveButtons();
        updateBudgetDisplay();
    }

    $(document).on('click', '.remove-task-row', function() {
        $(this).closest('.task-row').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        $('.remove-task-row').prop('disabled', $('.task-row').length <= 1);
    }

    $(document).on('change', '.assigned_to_type', function() {
        const $row = $(this).closest('.task-row');
        const type = $(this).val();
        const $targetSelect = $row.find('.assigned_to_id');
        
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

    $('#assignTaskForm').submit(function(e) {
        e.preventDefault();
        
        // Final check on weight
        let currentModalWeight = 0;
        $('.task-weight-input').each(function() {
            currentModalWeight += parseFloat($(this).val()) || 0;
        });

        if ((global_assigned_weight + currentModalWeight) > global_goal_weight) {
            Swal.fire('Budget Exceeded', 'Total tasks weight cannot exceed goal budget of ' + global_goal_weight + '%', 'error');
            return;
        }

        // --- PROGRESS BAR LOGIC ---
        const $btn = $('#btn_save_tasks');
        const $progressContainer = $('#assign_progress_container');
        const $progressBar = $('#assign_progress_bar');
        const $statusText = $('#assign_status_text');

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $progressContainer.show();
        
        let progress = 0;
        const interval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 10;
                $progressBar.css('width', progress + '%').text(Math.round(progress) + '%');
            }
        }, 300);

        $.ajax({
            url: '{{ route("admin.goals.assignTask") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                clearInterval(interval);
                if (response.success) {
                    $progressBar.css('width', '100%').text('100%');
                    $statusText.text('Tasks created successfully!');
                    
                    setTimeout(() => {
                        Swal.fire('Success', response.message, 'success').then(() => location.reload());
                    }, 500);
                } else {
                    $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save All Tasks');
                    $progressContainer.hide();
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                clearInterval(interval);
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save All Tasks');
                $progressContainer.hide();
                Swal.fire('Error', 'Something went wrong', 'error');
            }
        });
    });

    // --- GOAL DELETE ---
    // (Already implemented above)
});
</script>

@include('includes.footer')
