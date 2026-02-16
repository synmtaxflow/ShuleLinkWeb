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

                <!-- Add Subtask Form -->
                <div class="card mb-3">
                    <div class="card-header" style="background-color: #f8f9fa; color: #940000; font-weight: 600;">
                        <strong>Create New Sub-task</strong>
                    </div>
                    <div class="card-body">
                        <form class="ajax-form" action="{{ route('sgpm.subtasks.store') }}" method="POST">
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
                            <button type="submit" class="btn btn-sm text-white" style="background-color: #940000;">
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
                                            <span class="badge {{ $subtask->status == 'Draft' ? 'bg-secondary' : ($subtask->status == 'Submitted' ? 'bg-info' : ($subtask->status == 'Approved' ? 'bg-success' : 'bg-danger')) }}">
                                                {{ $subtask->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($subtask->status == 'Draft')
                                                <button class="btn btn-xs btn-success send-subtask" data-id="{{ $subtask->subtaskID }}">
                                                    <i class="fa fa-paper-plane"></i> Send to HOD
                                                </button>
                                            @elseif($subtask->status == 'Rejected')
                                                <button class="btn btn-xs btn-warning send-subtask" data-id="{{ $subtask->subtaskID }}">
                                                    <i class="fa fa-refresh"></i> Resubmit
                                                </button>
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
