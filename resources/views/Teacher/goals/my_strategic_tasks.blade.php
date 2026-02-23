@include('includes.teacher_nav')

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white p-4">
                        <h4 class="mb-0" style="color: #940000; font-weight: 700;">My Assigned Strategic Tasks</h4>
                    </div>
                    <div class="card-body p-4">
                        @forelse($tasks as $task)
                            <div class="task-block mb-5 p-4 border" style="border-radius: 15px; background-color: #fdfdfd;">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="text-primary" style="font-weight: 700;">{{ $task->task_name }}</h5>
                                        <p class="mb-1 text-muted"><strong>Parent Goal:</strong> {{ $task->parentTask->goal->goal_name }}</p>
                                        <p class="mb-0 text-muted"><strong>Weight in Department:</strong> {{ $task->weight }}%</p>
                                    </div>
                                    <span class="badge badge-{{ $task->status == 'Pending' ? 'warning' : 'success' }} px-3 py-2" style="border-radius: 20px;">
                                        {{ $task->status }}
                                    </span>
                                </div>
                                
                                <div class="subtasks-section mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 style="font-weight: 600;">My Implementation Breakdown (Subtasks)</h6>
                                        <button class="btn btn-sm btn-outline-success btn-add-subtask" data-id="{{ $task->id }}" style="border-radius: 10px;">
                                            <i class="fa fa-plus"></i> Add Subtask
                                        </button>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Subtask Name</th>
                                                    <th>Weight (%)</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Subtasks will be loaded here or rendered from relation if pre-loaded -->
                                                @foreach($task->subtasks as $sub)
                                                    <tr>
                                                        <td>{{ $sub->subtask_name }}</td>
                                                        <td>{{ $sub->weight }}%</td>
                                                        <td>
                                                            @if($sub->status == 'done')
                                                                <span class="text-success"><i class="fa fa-check-circle"></i> Done</span>
                                                            @else
                                                                <span class="text-warning"><i class="fa fa-circle-o"></i> Undone</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($sub->status == 'undone')
                                                                <button class="btn btn-xs btn-success mark-done" data-id="{{ $sub->id }}">Mark Done</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @if($task->subtasks->isEmpty())
                                                    <tr><td colspan="4" class="text-center text-muted">No subtasks added.</td></tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fa fa-tasks fa-3x mb-3" style="opacity: 0.1;"></i>
                                <p>You have no assigned strategic tasks.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Subtask Modal -->
<div class="modal fade" id="subtaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Add Implementation Subtask</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="subtaskForm">
                @csrf
                <input type="hidden" name="member_task_id" id="modal_member_task_id">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Subtask Name</label>
                        <input type="text" name="subtask_name" class="form-control" required placeholder="e.g. Gather teaching materials">
                    </div>
                    <div class="form-group mb-3">
                        <label>Weight (%)</label>
                        <input type="number" name="weight" class="form-control" required min="1" max="100">
                    </div>
                    <div class="form-group mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success px-5" style="border-radius: 20px;">Save Subtask</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.btn-add-subtask').click(function() {
        $('#modal_member_task_id').val($(this).data('id'));
        $('#subtaskModal').modal('show');
    });

    $('#subtaskForm').submit(function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.post('{{ url("goals/member/subtask-store") }}', $(this).serialize(), function(response) {
            if (response.success) {
                location.reload();
            } else {
                $btn.prop('disabled', false).text('Save Subtask');
                Swal.fire('Error', response.message, 'error');
            }
        }).fail(function(xhr) {
            $btn.prop('disabled', false).text('Save Subtask');
            let msg = 'Something went wrong on the server.';
            if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            Swal.fire('Error', msg, 'error');
        });
    });

    $('.mark-done').click(function() {
        const id = $(this).data('id');
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.post('{{ url("goals/member/subtask-done") }}/' + id, { _token: '{{ csrf_token() }}' }, function(response) {
            location.reload();
        }).fail(function(xhr) {
            $btn.prop('disabled', false).text('Mark Done');
            Swal.fire('Error', 'Failed to mark as done.', 'error');
        });
    });
});
</script>

@include('includes.footer')
