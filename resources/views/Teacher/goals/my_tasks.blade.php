@include('includes.teacher_nav')

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<style>
    /* === Mobile-First Base === */
    *, *::before, *::after { box-sizing: border-box; }
    body { -webkit-tap-highlight-color: transparent; }

    .text-maroon { color: #940000 !important; }
    .bg-maroon   { background-color: #940000 !important; }
    .btn-maroon  { background-color: #940000 !important; color: #fff !important; border: none; border-radius: 10px; }
    .btn-maroon:hover, .btn-maroon:active { background-color: #7a0000 !important; color: #fff !important; }
    .btn-send-hod {
        border: 2px solid #940000 !important;
        color: #940000 !important;
        font-weight: 700 !important;
        background: transparent !important;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-send-hod:hover, .btn-send-hod:active { background: #940000 !important; color: white !important; }

    /* === Page Wrapper === */
    .mt-page { background: #f2f3f7; min-height: 100vh; padding: 0; }

    /* === Sticky Header === */
    .task-header {
        background: linear-gradient(135deg, #940000 0%, #c20000 100%);
        color: white;
        padding: 18px 16px 14px;
        position: sticky;
        top: 0;
        z-index: 200;
        box-shadow: 0 2px 10px rgba(148,0,0,0.3);
    }
    .task-header h4 { font-size: 1.1rem; font-weight: 800; margin: 0; }
    .task-header .portal-pill {
        background: rgba(255,255,255,0.18);
        border-radius: 20px;
        padding: 3px 12px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* === Task Cards Grid === */
    .tasks-wrap { padding: 14px 12px; }
    .tasks-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }

    /* === Task Card === */
    .task-card {
        background: #fff;
        border-radius: 16px;
        border-left: 5px solid #940000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        overflow: hidden;
        transition: transform 0.15s;
    }
    .task-card:active { transform: scale(0.985); }
    
    .tc-body { padding: 14px 14px 10px; }
    .tc-title { font-size: 0.95rem; font-weight: 800; color: #222; margin: 0 0 4px; line-height: 1.3; }
    .tc-goal  { font-size: 0.75rem; color: #999; }
    .tc-weight-badge {
        background: #f7f7f7;
        border: 1px solid #eee;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 0.76rem;
        font-weight: 700;
        color: #555;
        white-space: nowrap;
    }
    .tc-progress-num { font-size: 0.82rem; font-weight: 800; color: #940000; margin-top: 2px; }
    .tc-desc {
        margin: 8px 14px;
        background: #fafafa;
        border-left: 3px solid #e0e0e0;
        padding: 7px 10px;
        border-radius: 6px;
        font-size: 0.78rem;
        color: #777;
        font-style: italic;
    }

    /* Progress bar strip */
    .tc-progress-strip { height: 5px; background: #f0f0f0; }
    .tc-progress-fill  { height: 100%; background: linear-gradient(to right, #940000, #e63333); transition: width 0.5s ease; }

    /* Card Footer */
    .tc-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #fafafa;
        border-top: 1px solid #f0f0f0;
    }
    .tc-count { font-size: 0.76rem; color: #aaa; }
    .tc-actions { display: flex; gap: 6px; }
    .tc-actions .btn {
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 7px 14px;
        min-height: 38px;
        line-height: 1.2;
    }

    /* Empty State */
    .empty-state { text-align: center; padding: 70px 20px; color: #ccc; grid-column: 1 / -1; }
    .empty-state i { font-size: 3.5rem; display: block; margin-bottom: 14px; }
    .empty-state p { font-size: 0.88rem; margin-top: 6px; color: #bbb; }

    /* === Modal Mobile Tuning === */
    .modal-content { border-radius: 18px !important; }
    @media (max-width: 576px) {
        .modal-dialog { margin: 8px 8px !important; }
        .modal-dialog[style] { max-width: calc(100vw - 16px) !important; }
    }
    .modal-header { border-radius: 18px 18px 0 0 !important; padding: 14px 16px !important; }
    .modal-header h5 { font-size: 0.92rem !important; }
    .modal-body { padding: 14px 14px !important; font-size: 0.88rem; }
    .modal-footer { padding: 10px 14px !important; }

    /* === Subtask Card in View Modal === */
    .sub-card {
        background: #fff;
        border-radius: 12px;
        border-left: 4px solid #940000;
        padding: 12px 12px 10px;
        margin-bottom: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.07);
    }
    .sub-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; }
    .sub-card-name { font-size: 0.88rem; font-weight: 700; color: #222; margin: 0; flex:1; }
    .sub-card-meta { font-size: 0.75rem; color: #aaa; margin: 3px 0 6px; }
    .sub-card-desc { font-size: 0.78rem; color: #888; margin-bottom: 8px; font-style: italic; }
    .sub-actions { display: flex; flex-wrap: wrap; gap: 6px; }
    .sub-actions .btn {
        flex: 1; min-width: 72px; font-size: 0.76rem; font-weight: 700;
        padding: 6px 10px; border-radius: 8px; min-height: 36px; text-align: center;
    }

    /* === Step Entry === */
    .step-entry {
        background: #f4fbfd;
        border-left: 3px solid #17a2b8;
        border-radius: 8px;
        padding: 9px 12px;
        margin-bottom: 8px;
    }
    .step-entry-top { display: flex; justify-content: space-between; align-items: center; }
    .step-date { font-size: 0.72rem; color: #17a2b8; font-weight: 700; }
    .step-desc { font-size: 0.82rem; color: #333; margin-top: 4px; }
    .step-btns { display: flex; gap: 2px; }

    /* === Form Controls === */
    .form-control { border-radius: 9px !important; font-size: 0.88rem !important; min-height: 42px; }
    textarea.form-control { min-height: 72px; }
    .form-control:focus { border-color: #940000 !important; box-shadow: 0 0 0 2px rgba(148,0,0,0.15) !important; }
    label { font-size: 0.8rem !important; font-weight: 700 !important; }

    /* === Step Input Row === */
    .step-row { background: #f8f8f8; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }

    /* === Desktop Breakpoints === */
    @media (min-width: 576px) {
        .tasks-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (min-width: 992px) {
        .tasks-grid { grid-template-columns: 1fr 1fr 1fr; }
        .task-header { position: static; }
        .modal-dialog { max-width: 65% !important; }
    }
</style>

<div class="mt-page">

    <!-- Sticky Header -->
    <div class="task-header d-flex justify-content-between align-items-center">
        <h4><i class="fa fa-thumb-tack mr-2"></i>My Assigned Tasks</h4>
        <span class="portal-pill">Member Portal</span>
    </div>

    <!-- Tasks Grid -->
    <div class="tasks-wrap">
        <div class="tasks-grid">
            @forelse($myTasks as $task)
                <div class="task-card" style="border-left-color: {{ $task->is_direct ? '#2980b9' : '#940000' }};">
                    <div class="tc-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex:1; margin-right:8px;">
                                <div class="mb-1">
                                    @if($task->is_direct)
                                        <span class="badge badge-primary px-2 py-1" style="font-size: 0.65rem; border-radius: 6px;"><i class="fa fa-shield"></i> Direct Assignment</span>
                                    @else
                                        <span class="badge badge-light border px-2 py-1" style="font-size: 0.65rem; border-radius: 6px; color: #940000;"><i class="fa fa-users"></i> Via Department</span>
                                    @endif
                                </div>
                                <p class="tc-title">{{ $task->display_name }}</p>
                                <div class="tc-goal"><i class="fa fa-bullseye mr-1"></i>{{ $task->parent_goal_name }}</div>
                            </div>
                            <div class="text-right">
                                <span class="tc-weight-badge">{{ $task->assigned_weight }}%</span>
                                <div class="tc-progress-num">{{ (float)$task->progress_val == (int)$task->progress_val ? number_format($task->progress_val, 0) : rtrim(rtrim(number_format($task->progress_val, 6, '.', ''), '0'), '.') }}% Done</div>
                            </div>
                        </div>
                    </div>

                    @if($task->description)
                        <div class="tc-desc">"{{ $task->description }}"</div>
                    @endif

                    <div class="tc-progress-strip">
                        <div class="tc-progress-fill" style="width: {{ $task->progress_val }}%;"></div>
                    </div>

                    <div class="tc-footer">
                        <div class="tc-count"><i class="fa fa-list mr-1"></i>{{ $task->subtasks->count() }} subtasks</div>
                        <div class="tc-actions">
                            <button class="btn btn-maroon btn-add-subtasks"
                                data-id="{{ $task->id }}"
                                data-is-direct="{{ $task->is_direct ? '1' : '0' }}"
                                data-name="{{ $task->display_name }}"
                                data-weight="{{ $task->assigned_weight }}"
                                data-used="{{ $task->weight_sum }}">
                                <i class="fa fa-plus-circle"></i> Subtasks
                            </button>
                            <button class="btn btn-maroon btn-view-subtasks"
                                data-id="{{ $task->id }}"
                                data-name="{{ $task->display_name }}">
                                <i class="fa fa-eye"></i> View
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa fa-tasks"></i>
                    <h5 style="color:#bbb;">Hujapangiwa kazi yoyote hivi sasa.</h5>
                    <p>HOD atakapokupa kazi, itaonekana hapa.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- ===== MODALS ===== -->

<!-- Modal: Add Subtasks -->
<div class="modal fade" id="addSubtasksModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-maroon text-white">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">Add Subtasks</h5>
                    <div class="small" style="opacity:0.85;"><span id="modal_parent_task_name"></span></div>
                </div>
                <div class="ml-auto d-flex align-items-center mr-2">
                    <span class="badge badge-light text-dark font-weight-bold px-2 py-1">
                        Left: <span id="remaining_weight_display">100</span>%
                    </span>
                </div>
                <button type="button" class="close text-white ml-1" data-dismiss="modal">&times;</button>
            </div>
            <form id="subtaskForm">
                @csrf
                <input type="hidden" name="member_task_id" id="modal_member_task_id">
                <input type="hidden" name="direct_task_id" id="modal_direct_task_id">
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    <div id="subtasks_container"></div>
                    <button type="button" id="btn_add_subtask_row" class="btn btn-maroon btn-sm w-100 mt-1">
                        <i class="fa fa-plus"></i> Add Another Subtask
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm flex-fill" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="btn_save_subtasks" class="btn btn-maroon btn-sm flex-fill">Save Subtasks</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: View & Conduct Subtasks -->
<div class="modal fade" id="viewSubtasksModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title font-weight-bold">My Subtasks: <span id="view_modal_task_name"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto; background: #f5f5f8;">
                <div id="subtasks_list_container"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Subtask -->
<div class="modal fade" id="editSubtaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title font-weight-bold">Edit Subtask</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="editSubtaskForm">
                @csrf
                <input type="hidden" id="edit_subtask_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Subtask Name</label>
                        <input type="text" id="edit_sub_name" name="subtask_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Weight (%)</label>
                        <input type="number" id="edit_sub_weight" name="weight" class="form-control" required min="0.1" step="0.1">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="edit_sub_desc" name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm flex-fill" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-maroon btn-sm flex-fill">Update Subtask</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Conduct Steps -->
<div class="modal fade" id="conductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: #17a2b8; color:white; border-radius: 18px 18px 0 0 !important;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">Conduct</h5>
                    <div class="small" style="opacity:0.85;" id="conduct_subtask_name"></div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 82vh; overflow-y: auto;">
                <!-- Log Steps -->
                <div style="background:#f0fafc; border-radius:12px; padding:12px; margin-bottom:14px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold" style="font-size:0.85rem; color:#17a2b8;"><i class="fa fa-plus-circle"></i> Log Steps</span>
                        <button type="button" id="btn_add_step_row" class="btn btn-sm" style="background:#17a2b8; color:white; border-radius:8px; font-size:0.76rem; padding:4px 10px;">+ More</button>
                    </div>
                    <form id="stepForm">
                        @csrf
                        <input type="hidden" name="subtask_id" id="conduct_subtask_id">
                        <div id="steps_rows_container"></div>
                        <button type="submit" id="btn_save_steps" class="btn w-100 mt-2" style="background:#17a2b8; color:white; border-radius:10px; font-weight:700; min-height:42px;">
                            Save Action Steps
                        </button>
                    </form>
                </div>

                <!-- History -->
                <div class="font-weight-bold mb-2" style="font-size:0.85rem; color:#555;">
                    <i class="fa fa-history mr-1"></i> Implementation Log
                </div>
                <div id="steps_history_list"></div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let subtaskRowCount = 0;
    let stepRowCount = 0;
    let global_used_weight_percent = 0;
    let currentViewTaskId = null;

    // ======== SUBTASK CREATION ========
    $('.btn-add-subtasks').click(function() {
        global_used_weight_percent = parseFloat($(this).data('used')) || 0;

        if (global_used_weight_percent >= 100) {
            Swal.fire({
                title: '100% Used',
                text: 'Asilimia zote zimeshatumika na subtasks zilizopo. Futa au hariri subtask iliyopo ili uweze kuongeza nyingine.',
                icon: 'warning',
                confirmButtonColor: '#940000',
                confirmButtonText: 'Sawa'
            });
            return;
        }

        const id   = $(this).data('id');
        const name = $(this).data('name');
        const isDirect = $(this).data('is-direct') == '1';

        $('#modal_member_task_id').val(isDirect ? '' : id);
        $('#modal_direct_task_id').val(isDirect ? id : '');
        
        $('#modal_parent_task_name').text(name);
        $('#subtasks_container').empty();
        subtaskRowCount = 0;
        addSubtaskRow();
        updateBudget();
        $('#addSubtasksModal').modal('show');
    });

    $('#btn_add_subtask_row').click(addSubtaskRow);

    function addSubtaskRow() {
        const row = `
            <div class="subtask-row" style="background:#fff; border-radius:10px; padding:12px; margin-bottom:10px; box-shadow:0 1px 5px rgba(0,0,0,0.07);">
                <div class="form-group mb-2">
                    <label>Subtask Name</label>
                    <input type="text" name="subtasks[${subtaskRowCount}][subtask_name]" class="form-control" required placeholder="e.g. Prepare lesson notes">
                </div>
                <div class="d-flex" style="gap:8px;">
                    <div class="form-group mb-2" style="flex:1;">
                        <label>Weight (%)</label>
                        <input type="number" name="subtasks[${subtaskRowCount}][weight]" class="form-control sub-weight-input" required min="0.1" max="100" step="0.1" placeholder="e.g. 25">
                    </div>
                    <div class="d-flex align-items-end mb-2">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" style="min-height:42px; border-radius:9px;"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Description (Optional)</label>
                    <textarea name="subtasks[${subtaskRowCount}][description]" class="form-control" rows="2" placeholder="Briefly describe this subtask..."></textarea>
                </div>
            </div>
        `;
        $('#subtasks_container').append(row);
        subtaskRowCount++;
        updateBudget();
    }

    $(document).on('click', '.remove-row', function() {
        $(this).closest('.subtask-row').remove();
        updateBudget();
    });

    $(document).on('input', '.sub-weight-input', updateBudget);

    function updateBudget() {
        let total = 0;
        $('.sub-weight-input').each(function() { total += parseFloat($(this).val()) || 0; });
        const remaining = 100 - (global_used_weight_percent + total);
        $('#remaining_weight_display').text(remaining.toFixed(1));
        if (remaining < 0) {
            $('#remaining_weight_display').css('color', '#c0392b');
        } else {
            $('#remaining_weight_display').css('color', '');
        }
    }

    $('#subtaskForm').submit(function(e) {
        e.preventDefault();
        let total = 0;
        $('.sub-weight-input').each(function() { total += parseFloat($(this).val()) || 0; });

        if ((global_used_weight_percent + total) > 100) {
            Swal.fire('Limit Exceeded', 'Total subtask weight cannot exceed 100%', 'error');
            return;
        }

        const $btn = $('#btn_save_subtasks');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '{{ route("member.goals.subtaskStore") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    Swal.fire('Saved!', res.message, 'success').then(() => location.reload());
                } else {
                    $btn.prop('disabled', false).text('Save Subtasks');
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Save Subtasks');
                Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
            }
        });
    });

    // ======== VIEW SUBTASKS ========
    $('.btn-view-subtasks').click(function() {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        currentViewTaskId = id;
        $('#view_modal_task_name').text(name);
        loadSubtasks(id);
        $('#viewSubtasksModal').modal('show');
    });

    function loadSubtasks(memberTaskId) {
        const container = $('#subtasks_list_container');
        container.html('<div class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $.get('{{ url("goals/fetch-subtasks") }}/' + memberTaskId, function(subtasks) {
            renderSubtasks(subtasks);
        }).fail(function() {
            container.html('<div class="text-center py-4 text-danger">Failed to load subtasks. Please try again.</div>');
        });
    }

    function renderSubtasks(subtasks) {
        const container = $('#subtasks_list_container');
        container.empty();

        if (!subtasks.length) {
            container.html(`
                <div style="text-align:center; padding:40px 20px; color:#ccc;">
                    <i class="fa fa-list fa-3x mb-3" style="display:block;"></i>
                    <p style="font-size:0.88rem;">Bado hujagawanya kazi hii. Bonyeza Subtasks kuanza.</p>
                </div>
            `);
            return;
        }

        subtasks.forEach(s => {
            // Status badge
            let statusColor = '#888', statusIcon = 'fa-clock-o', statusLabel = 'Draft';
            if (s.status === 'Submitted') { statusColor = '#e67e22'; statusIcon = 'fa-send'; statusLabel = 'Pending Review'; }
            if (s.status === 'Approved')  { statusColor = '#27ae60'; statusIcon = 'fa-check-circle'; statusLabel = 'Approved'; }

            // Score block
            const scorePercent = s.is_approved ? Math.round((s.marks / s.weight) * 100) : null;
            const scoreHtml = s.is_approved ? `
                <div style="display:flex; align-items:center; gap:10px; margin:8px 0; background:#f0fff4; border-radius:8px; padding:8px 10px;">
                    <div style="flex:1;">
                        <div style="height:6px; background:#e0e0e0; border-radius:4px;">
                            <div style="width:${scorePercent}%; height:100%; background:#27ae60; border-radius:4px; transition:width 0.5s;"></div>
                        </div>
                    </div>
                    <span style="font-weight:800; color:#27ae60; font-size:0.85rem; white-space:nowrap;">
                        ${s.marks}/${s.weight} pts &bull; ${scorePercent}%
                    </span>
                </div>
            ` : (s.status === 'Submitted' ? `
                <div style="background:#fff8f0; border-radius:8px; padding:7px 10px; margin:6px 0; font-size:0.78rem; color:#e67e22;">
                    <i class="fa fa-hourglass-half mr-1"></i> Awaiting HOD review...
                </div>
            ` : '');

            const actionsHtml = !s.is_sent_to_hod ? `
                <button class="btn btn-sm btn-outline-secondary btn-conduct" data-id="${s.id}" data-name="${s.subtask_name}">
                    <i class="fa fa-edit"></i> Conduct
                </button>
                <button class="btn btn-sm btn-outline-primary btn-edit-subtask" data-id="${s.id}" data-name="${s.subtask_name}" data-weight="${s.weight}" data-desc="${(s.description||'').replace(/"/g,'&quot;')}">
                    <i class="fa fa-pencil"></i>
                </button>
                <button class="btn btn-sm btn-send-hod btn-submit-hod" data-id="${s.id}" title="Send to HOD">
                    <i class="fa fa-send"></i> HOD
                </button>
                <button class="btn btn-sm btn-outline-danger btn-delete-subtask" data-id="${s.id}">
                    <i class="fa fa-trash"></i>
                </button>
            ` : `
                <button class="btn btn-sm btn-outline-secondary btn-conduct" data-id="${s.id}" data-name="${s.subtask_name}">
                    <i class="fa fa-eye"></i> Steps
                </button>
                <button class="btn btn-sm" disabled style="background:#f5f5f5; color:#aaa; border-radius:8px; font-size:0.76rem; cursor:default;">
                    <i class="fa fa-lock"></i> Locked
                </button>
            `;

            container.append(`
                <div class="sub-card">
                    <div class="sub-card-top">
                        <p class="sub-card-name">${s.subtask_name}</p>
                        <span style="background:${statusColor}; color:white; border-radius:12px; padding:3px 10px; font-size:0.72rem; font-weight:700; white-space:nowrap;">
                            <i class="fa ${statusIcon}"></i> ${statusLabel}
                        </span>
                    </div>
                    <div class="sub-card-meta">
                        <i class="fa fa-balance-scale mr-1"></i>${s.weight}% Weight &bull;
                        <i class="fa fa-list mr-1"></i>${s.steps.length} steps
                    </div>
                    ${s.description ? `<div class="sub-card-desc">"${s.description}"</div>` : ''}
                    ${scoreHtml}
                    <div class="sub-actions mt-2">${actionsHtml}</div>
                </div>
            `);
        });
    }

    // ======== EDIT SUBTASK ========
    $(document).on('click', '.btn-edit-subtask', function() {
        $('#edit_subtask_id').val($(this).data('id'));
        $('#edit_sub_name').val($(this).data('name'));
        $('#edit_sub_weight').val($(this).data('weight'));
        $('#edit_sub_desc').val($(this).data('desc'));
        $('#viewSubtasksModal').modal('hide');
        setTimeout(() => $('#editSubtaskModal').modal('show'), 350);
    });

    $('#editSubtaskForm').submit(function(e) {
        e.preventDefault();
        const id = $('#edit_subtask_id').val();
        $.post('{{ url("goals/member/update-subtask") }}/' + id, $(this).serialize(), function(res) {
            if (res.success) {
                $('#editSubtaskModal').modal('hide');
                Swal.fire({icon:'success', title:'Updated!', text: res.message, toast:true, position:'top-end', showConfirmButton:false, timer:2500});
                location.reload();
            } else {
                Swal.fire('Hitilafu', res.message, 'error');
            }
        });
    });

    // ======== CONDUCT / STEPS ========
    $(document).on('click', '.btn-conduct', function() {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        $('#conduct_subtask_id').val(id);
        $('#conduct_subtask_name').text(name);

        $('#steps_rows_container').empty();
        stepRowCount = 0;
        addStepRow();

        loadSteps(id);
        $('#viewSubtasksModal').modal('hide');
        setTimeout(() => $('#conductModal').modal('show'), 350);
    });

    $('#btn_add_step_row').click(addStepRow);

    function addStepRow() {
        const row = `
            <div class="step-row">
                <div class="d-flex" style="gap:8px; align-items:flex-end;">
                    <div class="form-group mb-0" style="width:130px; flex-shrink:0;">
                        <label>Date</label>
                        <input type="date" name="steps[${stepRowCount}][date]" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group mb-0" style="flex:1;">
                        <label>What did you do?</label>
                        <input type="text" name="steps[${stepRowCount}][step_description]" class="form-control form-control-sm" required placeholder="Describe your action...">
                    </div>
                    ${stepRowCount > 0 ? `<button type="button" class="btn btn-sm btn-outline-danger remove-step-row mb-0" style="min-height:36px; border-radius:8px;"><i class="fa fa-times"></i></button>` : ''}
                </div>
            </div>
        `;
        $('#steps_rows_container').append(row);
        stepRowCount++;
    }

    $(document).on('click', '.remove-step-row', function() {
        $(this).closest('.step-row').remove();
    });

    function loadSteps(subtaskId) {
        const list = $('#steps_history_list');
        list.html('<div class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin"></i></div>');
        $.get('{{ url("goals/fetch-subtask-details") }}/' + subtaskId, function(data) {
            list.empty();
            if (!data.steps.length) {
                list.html('<div style="text-align:center; padding:20px; color:#ccc; font-size:0.82rem; border:1px dashed #ddd; border-radius:8px;">No steps logged yet.</div>');
            } else {
                data.steps.forEach(step => {
                    list.append(`
                        <div class="step-entry">
                            <div class="step-entry-top">
                                <span class="step-date"><i class="fa fa-calendar"></i> ${step.date}</span>
                                <div class="step-btns">
                                    <button class="btn btn-xs btn-link text-info btn-edit-step p-0 mr-1" data-id="${step.id}" data-date="${step.date}" data-desc="${step.step_description}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-xs btn-link text-danger btn-delete-step p-0" data-id="${step.id}"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="step-desc">${step.step_description}</div>
                        </div>
                    `);
                });
            }
        });
    }

    $('#stepForm').submit(function(e) {
        e.preventDefault();
        const subtaskId = $('#conduct_subtask_id').val();
        const $btn = $('#btn_save_steps');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.post('{{ url("goals/member/save-step") }}', $(this).serialize(), function(res) {
            $btn.prop('disabled', false).text('Save Action Steps');
            if (res.success) {
                Swal.fire({icon:'success', title:'Saved!', toast:true, position:'top-end', showConfirmButton:false, timer:2000});
                $('#steps_rows_container').empty();
                stepRowCount = 0;
                addStepRow();
                loadSteps(subtaskId);
                if (currentViewTaskId) loadSubtasks(currentViewTaskId);
            } else {
                Swal.fire('Hitilafu', res.message || 'Imeshindwa kuhifadhi. Jaribu tena.', 'error');
            }
        }).fail(function(xhr) {
            $btn.prop('disabled', false).text('Save Action Steps');
            const msg = xhr.responseJSON?.message || ('Server error ' + xhr.status);
            Swal.fire('Error ' + xhr.status, msg, 'error');
        });
    });

    // Edit individual step
    $(document).on('click', '.btn-edit-step', function() {
        const id   = $(this).data('id');
        const date = $(this).data('date');
        const desc = $(this).data('desc');
        Swal.fire({
            title: 'Edit Step',
            html: `
                <label style="font-size:0.82rem; font-weight:700; display:block; text-align:left; margin-bottom:4px;">Date</label>
                <input type="date" id="swal_step_date" class="swal2-input" value="${date}" style="margin:0 0 10px; width:100%;">
                <label style="font-size:0.82rem; font-weight:700; display:block; text-align:left; margin-bottom:4px;">Description</label>
                <textarea id="swal_step_desc" class="swal2-textarea" style="margin:0; width:100%;">${desc}</textarea>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            confirmButtonColor: '#940000',
            preConfirm: () => ({
                date: document.getElementById('swal_step_date').value,
                step_description: document.getElementById('swal_step_desc').value
            })
        }).then(result => {
            if (result.isConfirmed) {
                $.post('{{ url("goals/member/update-step") }}/' + id, { ...result.value, _token: '{{ csrf_token() }}' }, () => {
                    loadSteps($('#conduct_subtask_id').val());
                });
            }
        });
    });

    // Delete individual step
    $(document).on('click', '.btn-delete-step', function() {
        const id = $(this).data('id');
        Swal.fire({ title:'Delete Step?', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33' })
        .then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("goals/member/delete-step") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: () => { loadSteps($('#conduct_subtask_id').val()); }
                });
            }
        });
    });

    // ======== SEND TO HOD ========
    $(document).on('click', '.btn-submit-hod', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Send to HOD?',
            text: 'This will send your subtask to your HOD for review and marking.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: 'Yes, Send'
        }).then(result => {
            if (result.isConfirmed) {
                $.post('{{ url("goals/member/submit-subtask") }}/' + id, { _token: '{{ csrf_token() }}' }, res => {
                    if (res.success) {
                        Swal.fire({icon:'success', title:'Sent!', text: res.message, toast:true, position:'top-end', showConfirmButton:false, timer:2500});
                        if (currentViewTaskId) loadSubtasks(currentViewTaskId);
                    }
                });
            }
        });
    });

    // ======== DELETE SUBTASK ========
    $(document).on('click', '.btn-delete-subtask', function() {
        const id = $(this).data('id');
        Swal.fire({ title:'Delete Subtask?', text:'This action cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33' })
        .then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("goals/member/delete-subtask") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: res => {
                        if (res.success) {
                            Swal.fire({icon:'success', toast:true, title:'Deleted!', position:'top-end', showConfirmButton:false, timer:2000});
                            location.reload();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>

@include('includes.footer')
