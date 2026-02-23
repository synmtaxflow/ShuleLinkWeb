<!-- Subtasks Modal -->
<div class="modal fade" id="subtasksModal{{ $task->taskID }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
            <div class="modal-header text-white" style="background-color: #940000;">
                <h5 class="modal-title" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">Manage Sub-tasks for: {{ $task->kpi }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Progress Summary -->
                <div class="alert alert-info">
                    <strong>Task Weight:</strong> {{ $task->weight }}% |
                    <strong>Allocated:</strong> {{ $task->subtasks->sum('weight_percentage') }}% |
                    <strong>Remaining:</strong> {{ $task->weight - $task->subtasks->sum('weight_percentage') }}%
                </div>

                <!-- Sending Progress Bar (hidden by default) -->
                <div id="sendingProgress{{ $task->taskID }}" style="display:none;" class="mb-3">
                    <div class="card border-0" style="background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%); border-left: 4px solid #940000 !important; border-left: solid;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa fa-paper-plane fa-spin text-danger mr-2"></i>
                                <strong style="color: #940000;" id="sendingLabel{{ $task->taskID }}">Sending sub-task to HOD...</strong>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 5px; background: #f0e0e0;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                     role="progressbar"
                                     style="width: 100%; background-color: #940000 !important;"
                                     id="sendingProgressBar{{ $task->taskID }}">
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block">Please wait, do not close this window...</small>
                        </div>
                    </div>
                </div>

                <!-- Add Subtask Form (using specific class, NOT ajax-form to avoid footer global handler) -->
                <div class="card mb-3">
                    <div class="card-header" style="background-color: #f8f9fa; color: #940000; font-weight: 600;">
                        <strong>Create New Sub-task</strong>
                    </div>
                    <div class="card-body">
                        <form class="subtask-create-form" data-task="{{ $task->taskID }}" action="{{ route('sgpm.subtasks.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="taskID" value="{{ $task->taskID }}">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Sub-task Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="e.g., Conduct weekly quizzes" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Weight %</label>
                                    <input type="number" name="weight_percentage" class="form-control" min="1" max="{{ $task->weight - $task->subtasks->sum('weight_percentage') }}" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" name="due_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Details about this sub-task"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm text-white subtask-save-btn" style="background-color: #940000;">
                                <i class="fa fa-plus"></i> Add Sub-task
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Subtasks List -->
                <div class="card">
                    <div class="card-header" style="background-color: #f8f9fa; color: #940000; font-weight: 600;">
                        <strong>My Sub-tasks</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Weight</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($task->subtasks as $subtask)
                                    <tr>
                                        <td>
                                            <strong>{{ $subtask->title }}</strong>
                                            @if($subtask->description)
                                                <br><small class="text-muted">{{ $subtask->description }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $subtask->weight_percentage }}%</td>
                                        <td>{{ $subtask->due_date }}</td>
                                        <td>
                                            @php
                                                $statusColor = match($subtask->status) {
                                                    'Draft'     => 'bg-secondary',
                                                    'Submitted' => 'bg-info',
                                                    'Approved'  => 'bg-success',
                                                    'Rejected'  => 'bg-danger',
                                                    default     => 'bg-secondary',
                                                };
                                                $statusLabel = match($subtask->status) {
                                                    'Submitted' => '⏳ Awaiting Review',
                                                    'Approved'  => '✓ Approved',
                                                    'Rejected'  => '✗ Returned',
                                                    default     => 'Draft',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusColor }}">{{ $statusLabel }}</span>
                                            @if($subtask->status == 'Approved')
                                                <br><small class="text-success"><strong>Score: {{ $subtask->achieved_score }}/{{ $subtask->weight_percentage }}%</strong></small>
                                            @elseif($subtask->status == 'Rejected' && $subtask->hod_comments)
                                                <br><small class="text-danger"><i class="fa fa-comment"></i> {{ $subtask->hod_comments }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($subtask->status == 'Draft')
                                                <button class="btn btn-xs btn-success send-subtask-btn"
                                                        data-id="{{ $subtask->subtaskID }}"
                                                        data-task="{{ $task->taskID }}"
                                                        data-label="Sending to HOD...">
                                                    <i class="fa fa-paper-plane"></i> Send to HOD
                                                </button>
                                            @elseif($subtask->status == 'Rejected')
                                                <button class="btn btn-xs btn-warning send-subtask-btn"
                                                        data-id="{{ $subtask->subtaskID }}"
                                                        data-task="{{ $task->taskID }}"
                                                        data-label="Resubmitting to HOD...">
                                                    <i class="fa fa-refresh"></i> Resubmit
                                                </button>
                                            @elseif($subtask->status == 'Submitted')
                                                <small class="text-info"><i class="fa fa-clock-o"></i> Awaiting HOD Review</small>
                                            @elseif($subtask->status == 'Approved')
                                                <small class="text-success"><i class="fa fa-check-circle"></i> Done</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No sub-tasks created yet. Start by adding one above.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
