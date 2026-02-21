@include('includes.teacher_nav')

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<style>
    .bg-maroon { background-color: #940000 !important; }
    .text-maroon { color: #940000 !important; }
    .btn-maroon { background-color: #940000 !important; color: white !important; }
    .btn-outline-maroon { border: 1.5px solid #940000; color: #940000; background: transparent; font-weight: 600; border-radius: 10px; }
    .text-primary-shule { color: #940000 !important; }
    .bg-primary-shule { background-color: #940000 !important; }
    
    /* Sticky Mobile Header */
    .mobile-header {
        position: sticky; top: 0; z-index: 1020;
        background: linear-gradient(135deg, #940000 0%, #c20000 100%);
        color: white; padding: 18px 15px; margin: -20px -20px 20px -20px;
        box-shadow: 0 4px 12px rgba(148,0,0,0.25); display: none;
    }

    @media (max-width: 767px) {
        .mobile-header { display: flex; justify-content: space-between; align-items: center; }
        .desktop-header { display: none; }
        .desktop-table-view { display: none; }
        .mobile-card-view { display: block; }
        .content { padding: 0 10px !important; }
    }
    @media (min-width: 768px) {
        .mobile-card-view { display: none; }
        .desktop-table-view { display: block; }
    }

    .hod-task-card {
        background: #fff; border-radius: 16px; margin-bottom: 15px; overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.06); border-left: 5px solid #940000;
        transition: transform 0.1s;
    }
    .hod-task-card:active { transform: scale(0.98); }
</style>

<div class="mobile-header">
    <h5 class="mb-0 font-weight-bold">
        <i class="fa fa-university mr-2"></i>Dept Tasks
    </h5>
    <span class="badge badge-light text-maroon p-2" style="border-radius: 10px;">{{ $department->department_name }}</span>
</div>

<div class="content mt-3 text-dark">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12 text-dark">
                <div class="card shadow-sm border-0 desktop-header" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                         <h5 class="mb-0 font-weight-bold"><i class="fa fa-university text-maroon mr-2"></i>Dept Strategic Tasks</h5>
                         <p class="text-muted small mb-0">{{ $department->department_name }}</p>
                    </div>
                </div>

                <div class="desktop-table-view">
                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="vertical-align: middle;">
                                    <thead class="bg-light">
                                        <tr class="small text-uppercase font-weight-bold text-muted">
                                            <th class="border-0 px-4 py-3">Task / Goal</th>
                                            <th class="border-0 py-3">Weight</th>
                                            <th class="border-0 py-3">Deadline</th>
                                            <th class="border-0 text-center py-3 px-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tasks as $task)
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <div class="font-weight-bold text-dark h6 mb-1">{{ $task->task_name }}</div>
                                                    <div class="text-muted small"><i class="fa fa-bullseye mr-1"></i> {{ $task->goal->goal_name }}</div>
                                                </td>
                                                <td class="py-3"><span class="badge badge-maroon text-white px-2 py-1">{{ $task->weight }}%</span></td>
                                                <td class="py-3 text-muted small">{{ \Carbon\Carbon::parse($task->goal->deadline)->format('d M, Y') }}</td>
                                                <td class="text-center py-3 px-4">
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-maroon btn-assign-member mr-2 px-3" data-id="{{ $task->id }}" data-name="{{ $task->task_name }}">
                                                            <i class="fa fa-user-plus mr-1"></i> Delegate
                                                        </button>
                                                        <a href="{{ route('hod.goals.viewTask', $task->id) }}" class="btn btn-sm btn-outline-info px-3">
                                                            <i class="fa fa-line-chart"></i> Progress
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center py-5">No departmental tasks found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="mobile-card-view">
                    @forelse($tasks as $task)
                        <div class="hod-task-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="font-weight-bold text-dark mb-0 line-height-1-4">{{ $task->task_name }}</h6>
                                <span class="badge badge-maroon text-white">{{ $task->weight }}%</span>
                            </div>
                            <div class="text-muted small mb-3 border-bottom pb-2">
                                <i class="fa fa-bullseye text-primary mr-1"></i> Goal: {{ $task->goal->goal_name }}
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="fa fa-calendar-o mr-1"></i> {{ \Carbon\Carbon::parse($task->goal->deadline)->format('d M, Y') }}</span>
                                <div class="d-flex">
                                    <button class="btn btn-xs btn-outline-maroon btn-assign-member mr-2" data-id="{{ $task->id }}" data-name="{{ $task->task_name }}">
                                        <i class="fa fa-user-plus"></i> Delegate
                                    </button>
                                    <a href="{{ route('hod.goals.viewTask', $task->id) }}" class="btn btn-xs btn-outline-info">
                                        <i class="fa fa-line-chart"></i> Progress
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">Hakuna kazi yoyote iliyopangwa kwa sasa.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Multi-Member Assignment -->
<div class="modal fade" id="assignMemberModal" tabindex="-1" role="dialog" aria-labelledby="assignMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 70%;">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #940000; color: white;">
                <h5 class="modal-title" id="assignMemberModalLabel">Delegate Task to Members: <span id="modal_task_name"></span></h5>
                <div id="task_budget_display" class="px-3 py-1 bg-white text-dark rounded-pill shadow-sm" style="font-size: 0.85rem; font-weight: 700;">
                    Budget: <span id="budget_parent_weight">0</span>% | 
                    Left: <span id="budget_remaining">0</span>%
                </div>
                <button type="button" class="close text-white ml-0" data-dismiss="modal">&times;</button>
            </div>
            <form id="assignMemberForm">
                @csrf
                <input type="hidden" name="parent_task_id" id="modal_task_id">
                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    
                    <!-- Existing Delegations Summary -->
                    <div id="existing_assignments_section" class="mb-4" style="display: none;">
                        <h6 class="font-weight-bold mb-2 text-muted small uppercase">Current Delegations</h6>
                        <div id="existing_assignments_list" class="list-group list-group-flush border rounded bg-white">
                            <!-- Existing tasks loaded here -->
                        </div>
                    </div>

                    <h6 class="font-weight-bold mb-3 text-maroon small uppercase">Add New Delegation</h6>
                    <div id="member_tasks_container">
                        <!-- Dynamic rows here -->
                    </div>
                    <button type="button" id="add_member_row" class="btn btn-outline-success btn-sm mt-2"><i class="fa fa-plus-circle"></i> Add Member Row</button>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div id="assign_progress_container" class="flex-grow-1 mr-3" style="display: none;">
                        <div class="progress" style="height: 15px; border-radius: 10px;">
                            <div id="assign_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;">0%</div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal" style="border-radius: 20px;">Cancel</button>
                        <button type="submit" id="btn_save_member_tasks" class="btn btn-primary btn-sm px-4" style="background-color: #940000; border: none; border-radius: 20px;">
                             Save Assignments
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let memberRowCount = 0;
    let deptMembers = [];
    let global_parent_weight = 0;
    let global_assigned_weight = 0;

    $('.btn-assign-member').click(function() {
        const id = $(this).data('id');
        $('#modal_task_id').val(id);
        $('#modal_task_name').text($(this).data('name'));
        
        // Fetch current delegation budget and existing tasks
        $.get('{{ url("goals/hod/fetch-member-tasks") }}/' + id, function(response) {
            global_parent_weight = parseFloat(response.parent_task.weight);
            global_assigned_weight = parseFloat(response.total_assigned_weight);
            
            // Render existing tasks summary
            const $existingList = $('#existing_assignments_list').empty();
            if (response.member_tasks && response.member_tasks.length > 0) {
                $('#existing_assignments_section').show();
                response.member_tasks.forEach(mt => {
                    $existingList.append(`
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 small">
                            <span class="text-dark"><i class="fa fa-user-o text-muted mr-2"></i><strong>${mt.member_name}</strong>: ${mt.task_name}</span>
                            <span class="badge badge-light border text-maroon font-weight-bold">${mt.weight}%</span>
                        </div>
                    `);
                });
            } else {
                $('#existing_assignments_section').hide();
            }

            updateBudgetDisplay();
        });

        // Load members first if not already loaded
        if (deptMembers.length === 0) {
            $.get('{{ route("goals.fetchDeptMembers") }}', function(data) {
                deptMembers = data;
                resetRows();
            });
        } else {
            resetRows();
        }

        $('#assignMemberModal').modal('show');
    });

    function resetRows() {
        $('#member_tasks_container').empty();
        memberRowCount = 0;
        addMemberRow();
    }

    $('#add_member_row').click(function() {
        // Prevent adding row if no weight left
        let currentModalWeight = 0;
        $('.member-weight-input').each(function() {
            currentModalWeight += parseFloat($(this).val()) || 0;
        });

        if ((global_assigned_weight + currentModalWeight) >= global_parent_weight) {
            Swal.fire('No Budget Left', 'You have already allocated your full task weight of ' + global_parent_weight + '%. Remove some rows or reduce weight to add more.', 'warning');
            return;
        }
        addMemberRow();
    });

    function addMemberRow() {
        let options = '<option value="">Select Member</option>';
        deptMembers.forEach(m => {
            options += `<option value="${m.id}" data-type="${m.type}">${m.name} (${m.type})</option>`;
        });

        const row = `
            <div class="task-row border p-3 mb-3 rounded bg-light shadow-sm" style="position: relative;">
                <div class="row">
                    <div class="col-md-5 form-group mb-2">
                        <label class="small font-weight-bold">Task Name</label>
                        <input type="text" name="tasks[${memberRowCount}][task_name]" class="form-control form-control-sm" required placeholder="e.g. Conduct Practical Exams">
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="small font-weight-bold">Target Member</label>
                        <select name="tasks[${memberRowCount}][member_id]" class="form-control form-control-sm member-select" required>
                            ${options}
                        </select>
                        <input type="hidden" name="tasks[${memberRowCount}][member_type]" class="member-type-hidden">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="small font-weight-bold">Weight (%)</label>
                        <input type="number" name="tasks[${memberRowCount}][weight]" class="form-control form-control-sm member-weight-input" required min="0.1" step="0.1" value="0">
                    </div>
                    <div class="col-md-1 d-flex align-items-end mb-2">
                        <button type="button" class="btn btn-danger btn-sm remove-member-row"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="col-md-12 form-group mb-0">
                        <label class="small font-weight-bold">Task Instructions / Description</label>
                        <textarea name="tasks[${memberRowCount}][description]" class="form-control form-control-sm" rows="2" placeholder="Describe the expectations for this task..."></textarea>
                    </div>
                </div>
            </div>
        `;
        $('#member_tasks_container').append(row);
        memberRowCount++;
        updateRemoveButtons();
        updateBudgetDisplay();
    }

    $(document).on('click', '.remove-member-row', function() {
        $(this).closest('.task-row').remove();
        updateRemoveButtons();
        updateBudgetDisplay();
    });

    function updateRemoveButtons() {
        $('.remove-member-row').prop('disabled', $('.task-row').length <= 1);
    }

    $(document).on('change', '.member-select', function() {
        const type = $(this).find(':selected').data('type');
        $(this).closest('.task-row').find('.member-type-hidden').val(type);
    });

    $(document).on('input', '.member-weight-input', function() {
        updateBudgetDisplay();
    });

    function updateBudgetDisplay() {
        let currentModalWeight = 0;
        $('.member-weight-input').each(function() {
            currentModalWeight += parseFloat($(this).val()) || 0;
        });

        const totalUsed = global_assigned_weight + currentModalWeight;
        const remaining = global_parent_weight - totalUsed;

        $('#budget_parent_weight').text(global_parent_weight);
        $('#budget_remaining').text(remaining.toFixed(1));

        if (remaining < 0) {
            $('#budget_remaining').addClass('text-danger').removeClass('text-success').css('text-decoration', 'underline');
            $('#btn_save_member_tasks').prop('disabled', true).addClass('bg-secondary');
        } else {
            $('#budget_remaining').addClass('text-success').removeClass('text-danger').css('text-decoration', 'none');
            $('#btn_save_member_tasks').prop('disabled', false).removeClass('bg-secondary');
        }
    }

    $('#assignMemberForm').submit(function(e) {
        e.preventDefault();
        
        let currentModalWeight = 0;
        $('.member-weight-input').each(function() {
            currentModalWeight += parseFloat($(this).val()) || 0;
        });

        if ((global_assigned_weight + currentModalWeight) > global_parent_weight) {
            Swal.fire({
                title: 'Limit Exceeded',
                text: 'Hujaweza kuhifadhi kwa sababu uzito (weight) unazidi ' + global_parent_weight + '%. Tafadhali kagua viwango ulivyopanga.',
                icon: 'error'
            });
            return;
        }

        const $btn = $('#btn_save_member_tasks');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $('#assign_progress_container').show();
        $('#assign_progress_bar').css('width', '50%').text('50%');

        $.ajax({
            url: '{{ route("hod.goals.assignMemberStore") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#assign_progress_bar').css('width', '100%').text('100%');
                    setTimeout(() => {
                        Swal.fire('Hongera', response.message, 'success').then(() => location.reload());
                    }, 500);
                } else {
                    $btn.prop('disabled', false).text('Save Assignments');
                    $('#assign_progress_container').hide();
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Save Assignments');
                $('#assign_progress_container').hide();
                Swal.fire('Error', 'Tatizo limetokea wakati wa kuhifadhi.', 'error');
            }
        });
    });
});
</script>

@include('includes.footer')
