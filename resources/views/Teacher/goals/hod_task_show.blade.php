@include('includes.teacher_nav')

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
        .content { padding-top: 0 !important; }
    }

    .task-card-compact {
        background: #fff; border-radius: 16px; border: 1px solid #eee;
        transition: transform 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        overflow: hidden;
        word-wrap: break-word;
    }
    .task-card-compact:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important; }
    .progress-sm { height: 6px !important; }
</style>

<div class="mobile-header">
    <div class="d-flex align-items-center">
        <a href="{{ route('hod.goals.assigned') }}" class="text-white mr-3"><i class="fa fa-arrow-left fa-lg"></i></a>
        <h6 class="mb-0 font-weight-bold" style="max-width: 200px; word-break: break-word;">{{ $task->task_name }}</h6>
    </div>
    <div class="badge badge-light text-maroon p-2" style="border-radius: 10px;">{{ number_format($task->overall_progress_percent, 1) }}%</div>
</div>

<div class="content mt-3 text-dark">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <!-- Parent Task Overview - Desktop Only -->
                <div class="card shadow-sm border-0 mb-3 desktop-header" style="border-radius: 12px; background: #fff; border-left: 5px solid #940000 !important;">
                    <div class="card-body py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="mr-4 text-center border-right pr-4" style="min-width: 100px;">
                                    <h4 class="mb-0 font-weight-bold text-maroon">{{ (float)$task->overall_progress_percent == (int)$task->overall_progress_percent ? number_format($task->overall_progress_percent, 0) : rtrim(rtrim(number_format($task->overall_progress_percent, 2, '.', ''), '0'), '.') }}%</h4>
                                    <small class="text-muted text-uppercase" style="font-size: 0.6rem; letter-spacing: 1px; font-weight: 700;">Progress</small>
                                </div>
                                <div>
                                    <h4 class="mb-1" style="font-weight: 800; color: #2f2f2f;">{{ $task->task_name }}</h4>
                                    <div class="d-flex align-items-center small text-muted">
                                        <span class="mr-3"><i class="fa fa-bullseye mr-1 text-maroon"></i> Allocation: <b>{{ $task->weight }}%</b></span>
                                        <span><i class="fa fa-folder-open-o mr-1 text-maroon"></i> Goal: {{ $task->goal->goal_name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <a href="{{ route('hod.goals.assigned') }}" class="btn btn-sm btn-outline-secondary px-3" style="border-radius: 20px;">
                                    <i class="fa fa-arrow-left mr-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0" style="color: #2f2f2f;">Delegations & Performance</h5>
                </div>

                <div class="row">
                    @forelse($task->memberTasks as $mTask)
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="card h-100 task-card-compact shadow-sm bg-white overflow-hidden">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="mb-0 pr-2" style="font-weight: 700; color: #333; line-height: 1.4;" title="{{ $mTask->task_name }}">
                                            {{ $mTask->task_name }}
                                        </h6>
                                        <span class="badge badge-light text-primary-shule p-1" style="font-size: 0.75rem;">{{ $mTask->weight }}%</span>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">Member:</small>
                                        <div class="text-dark font-weight-600 small text-truncate">
                                            <i class="fa fa-user-o text-muted mr-1"></i> {{ $mTask->member_name }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                                            <span class="text-muted">Status</span>
                                            <span class="font-weight-bold {{ $mTask->progress == 100 ? 'text-success' : 'text-primary-shule' }}">{{ (float)$mTask->progress == (int)$mTask->progress ? number_format($mTask->progress, 0) : rtrim(rtrim(number_format($mTask->progress, 6, '.', ''), '0'), '.') }}%</span>
                                        </div>
                                        <div class="progress progress-sm rounded-pill bg-light">
                                            <div class="progress-bar {{ $mTask->progress == 100 ? 'bg-success' : 'bg-primary-shule' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $mTask->progress }}%;" 
                                                 aria-valuenow="{{ $mTask->progress }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                        <div class="small text-muted">
                                            <i class="fa fa-list-ul mr-1"></i> {{ $mTask->subtasks->count() }} Subtasks
                                        </div>
                                        <button class="btn btn-xs btn-outline-danger btn-view-performance" data-id="{{ $mTask->id }}" data-name="{{ $mTask->member_name }}" data-task="{{ $mTask->task_name }}">
                                            View Performance
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="fa fa-users fa-3x text-muted mb-3" style="opacity: 0.2;"></i>
                            <p class="text-muted mt-2">Hujapanga majukumu kwa walimu bado kwa kazi hii.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Review Member Performance -->
<div class="modal fade" id="reviewPerformanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 80%;">
        <div class="modal-content border-0" style="border-radius: 15px;">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title">Performance Review: <span id="perf_member_name"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4 bg-light" id="perf_subtasks_container" style="max-height: 70vh; overflow-y: auto;">
                <!-- Subtasks and their steps will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Modal: Give Marks & Approve -->
<div class="modal fade" id="approveSubtaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold">Mark & Approve Subtask</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="approveForm">
                @csrf
                <input type="hidden" name="id" id="approve_subtask_id">
                <div class="modal-body">
                    <p class="mb-3">Subtask: <b id="approve_subtask_name"></b></p>
                    <div class="form-group">
                        <label class="font-weight-bold">Marks to Assign (Max: <span id="max_marks_display"></span>)</label>
                        <input type="number" name="marks" id="approve_marks_input" class="form-control" required min="0" step="0.1">
                        <small class="text-muted">Enter the weight achieved for this subtask.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success px-5 rounded-pill">Approve & Save Marks</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-maroon { background-color: #940000 !important; }
    .text-maroon { color: #940000 !important; }
</style>

<script>
$(document).ready(function() {
    $('.btn-view-performance').click(function() {
        const id = $(this).data('id');
        $('#perf_member_name').text($(this).data('name') + ' - ' + $(this).data('task'));
        loadMemberPerformance(id);
        $('#reviewPerformanceModal').modal('show');
    });

    function loadMemberPerformance(memberTaskId) {
        $('#perf_subtasks_container').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-maroon"></i></div>');
        
        $.get('{{ url("goals/fetch-subtasks") }}/' + memberTaskId + '?role=hod', function(subtasks) {
            let html = '<div class="row">';
            if(subtasks.length === 0) {
                html += '<div class="col-12 text-center py-4">Huyu member bado hajaanza ku-breakdown kazi yake.</div>';
            } else {
                subtasks.forEach(s => {
                    let stepsHtml = '';
                    if(s.steps.length === 0) {
                        stepsHtml = '<div class="text-muted italic small py-2">No implementation steps recorded.</div>';
                    } else {
                        s.steps.forEach(step => {
                            stepsHtml += `
                                <div class="p-2 mb-2 bg-white rounded border-left-info" style="border-left: 3px solid #17a2b8; font-size: 0.8rem;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <b class="text-info">${step.date}</b>
                                    </div>
                                    <div>${step.step_description}</div>
                                </div>
                            `;
                        });
                    }

                    let statusBadge = '';
                    if(s.status === 'Submitted') statusBadge = '<span class="badge badge-warning">Submitted</span>';
                    else if(s.status === 'Approved') statusBadge = '<span class="badge badge-success">Approved</span>';
                    else statusBadge = '<span class="badge badge-light">Pending</span>';

                    html += `
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="font-weight-bold mb-0">${s.subtask_name}</h6>
                                            <small class="text-muted">Weight: ${s.weight}%</small>
                                        </div>
                                        ${statusBadge}
                                    </div>
                                    <p class="small text-muted mb-3">${s.description || ''}</p>
                                    
                                    <div class="bg-light p-2 rounded mb-3" style="max-height: 150px; overflow-y: auto;">
                                        <b class="small d-block mb-1">Steps Used:</b>
                                        ${stepsHtml}
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                         ${s.is_approved ? `
                                            <div class="text-success font-weight-bold">Marks: ${s.marks}/${s.weight}</div>
                                            <div class="btn-group ml-3">
                                                <button class="btn btn-xs btn-outline-info btn-re-mark" data-id="${s.id}" data-name="${s.subtask_name}" data-marks="${s.marks}" data-max="${s.weight}"><i class="fa fa-edit"></i> Edit</button>
                                                <button class="btn btn-xs btn-outline-danger btn-reset-marks" data-id="${s.id}"><i class="fa fa-undo"></i> Reset</button>
                                            </div>
                                         ` : `
                                            <div class="text-muted small italic">Not yet marked</div>
                                            <button class="btn btn-sm btn-success btn-approve-now" data-id="${s.id}" data-name="${s.subtask_name}" data-max="${s.weight}">Approve & Mark</button>
                                         `}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            html += '</div>';
            $('#perf_subtasks_container').html(html);
        });
    }

    $(document).on('click', '.btn-approve-now, .btn-re-mark', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const max = $(this).data('max');
        const current = $(this).data('marks') || '';

        $('#approve_subtask_id').val(id);
        $('#approve_subtask_name').text(name);
        $('#max_marks_display').text(max);
        $('#approve_marks_input').val(current).attr('max', max);
        
        $('#approveSubtaskModal').modal('show');
    });

    $('#approveForm').submit(function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Saving...');

        $.post('{{ route("goals.review.approve") }}', $(this).serialize(), function(response) {
            if(response.success) {
                Swal.fire('Success', response.message, 'success');
                $('#approveSubtaskModal').modal('hide');
                // Reload performance content
                loadMemberPerformance($('.btn-view-performance[data-id]').first().data('id')); // Simple hack, better tracking needed
                // Better approach: store current memberTaskId in a global var
                location.reload(); // Refresh to update main cards too
            } else {
                $btn.prop('disabled', false).text('Approve & Save Marks');
                Swal.fire('Error', response.message, 'error');
            }
        });
    });

    $(document).on('click', '.btn-reset-marks', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Reset Marks?',
            text: "This will remove marks and set status back to Submitted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ url("goals/review/reset-marks") }}/' + id, {_token: '{{ csrf_token() }}'}, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            }
        });
    });
});
</script>

@include('includes.footer')
