<!-- Review Subtasks Modal (for Admin) -->
<div class="modal fade" id="adminReviewSubtasksModal{{ $task->taskID }}" tabindex="-1">
    <div class="modal-dialog" style="max-width: 95%; width: 1200px;">
        <div class="modal-content" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
            <div class="modal-header text-white" style="background-color: #333;">
                <h5 class="modal-title">Admin Review: Sub-tasks for {{ $task->kpi }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Filters -->
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong class="text-muted">Assigned To:</strong> {{ $task->assignee->name }}
                            </div>
                            <div class="col-md-6 text-end">
                                <strong class="text-muted">Task Weight:</strong> {{ $task->weight }}%
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered admin-subtasks-table" id="adminSubtasksTable{{ $task->taskID }}">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th style="width: 25%;">Title</th>
                                <th style="width: 8%;">Weight</th>
                                <th style="width: 10%;">Due Date</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 17%;">Evidence</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($task->subtasks as $subtask)
                                <tr class="subtask-row">
                                    <td>
                                        <strong>{{ $subtask->title }}</strong>
                                        @if($subtask->description)
                                            <br><small class="text-muted">{{ $subtask->description }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $subtask->weight_percentage }}%</span></td>
                                    <td>{{ $subtask->due_date }}</td>
                                    <td>
                                        <span class="badge {{ $subtask->status == 'Draft' ? 'bg-secondary' : ($subtask->status == 'Submitted' ? 'bg-info' : ($subtask->status == 'Approved' ? 'bg-success' : 'bg-danger')) }}">
                                            {{ $subtask->status }}
                                        </span>
                                        @if($subtask->status == 'Approved')
                                            <br><small class="text-success"><strong>Score: {{ $subtask->achieved_score }}/{{ $subtask->weight_percentage }}%</strong></small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subtask->evidence_remarks)
                                            <small>{{ $subtask->evidence_remarks }}</small>
                                        @else
                                            <em class="text-muted">None</em>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subtask->status == 'Submitted')
                                            <button class="btn btn-xs btn-success mb-1" data-toggle="modal" data-target="#adminApproveSubtaskModal{{ $subtask->subtaskID }}">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-xs btn-danger admin-reject-subtask" data-id="{{ $subtask->subtaskID }}">
                                                <i class="fa fa-times"></i> Reject
                                            </button>
                                        @elseif($subtask->status == 'Approved')
                                            <small class="text-success"><strong>✓ Approved</strong></small>
                                            @if($subtask->hod_comments)
                                                <br><em class="text-muted">"{{ $subtask->hod_comments }}"</em>
                                            @endif
                                        @elseif($subtask->status == 'Rejected')
                                            <small class="text-danger"><strong>✗ Rejected</strong></small>
                                            @if($subtask->hod_comments)
                                                <br><em class="text-muted">"{{ $subtask->hod_comments }}"</em>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No subtasks found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Approve Nested Modals -->
@foreach($task->subtasks as $subtask)
@if($subtask->status == 'Submitted')
<div class="modal fade" id="adminApproveSubtaskModal{{ $subtask->subtaskID }}" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
            <form class="admin-approve-subtask-form" data-id="{{ $subtask->subtaskID }}">
                @csrf
                <div class="modal-header text-white" style="background-color: #28a745;">
                    <h6 class="modal-title">Admin Approve: {{ $subtask->title }}</h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Subtask Weight:</strong> {{ $subtask->weight_percentage }}%
                    </div>
                    <div class="form-group">
                        <label>Achieved Score (out of {{ $subtask->weight_percentage }}%)</label>
                        <input type="number" name="achieved_score" class="form-control" 
                               min="0" max="{{ $subtask->weight_percentage }}" 
                               step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Admin Comments (Optional)</label>
                        <textarea name="hod_comments" class="form-control" rows="3" placeholder="Add feedback"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success text-white">
                        <i class="fa fa-check"></i> Finalize Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
