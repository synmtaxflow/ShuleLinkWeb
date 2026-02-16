<!-- Review Subtasks Modal (for HOD) -->
<div class="modal fade" id="reviewSubtasksModal{{ $task->taskID }}" tabindex="-1">
    <div class="modal-dialog" style="max-width: 95%; width: 1200px;">
        <div class="modal-content" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
            <div class="modal-header text-white" style="background-color: #940000;">
                <h5 class="modal-title">Review Sub-tasks: {{ $task->kpi }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Filters -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Filter by Team Member</strong></label>
                                <select class="form-control filter-member" data-task="{{ $task->taskID }}">
                                    <option value="">All Team Members</option>
                                    @php
                                        $assignedUser = $task->assignee;
                                        $profile = $assignedUser->teacher ?? $assignedUser->staff;
                                        $fullName = $profile ? ($profile->first_name . ' ' . ($profile->middle_name ? $profile->middle_name . ' ' : '') . $profile->last_name) : $assignedUser->name;
                                    @endphp
                                    <option value="{{ $assignedUser->id }}" selected>{{ $fullName }} ({{ $assignedUser->name }})</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Filter by Status</strong></label>
                                <select class="form-control filter-status" data-task="{{ $task->taskID }}">
                                    <option value="">All Status</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Submitted" selected>Submitted</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered subtasks-table" id="subtasksTable{{ $task->taskID }}">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th style="width: 25%;">Title</th>
                                <th style="width: 15%;">Assigned By</th>
                                <th style="width: 8%;">Weight</th>
                                <th style="width: 10%;">Due Date</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 17%;">Evidence</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($task->subtasks as $subtask)
                                @php
                                    $assignedUser = $task->assignee;
                                    $profile = $assignedUser->teacher ?? $assignedUser->staff;
                                    $fullName = $profile ? ($profile->first_name . ' ' . ($profile->middle_name ? $profile->middle_name . ' ' : '') . $profile->last_name) : $assignedUser->name;
                                @endphp
                                <tr class="subtask-row" data-status="{{ $subtask->status }}" data-member="{{ $assignedUser->id }}">
                                    <td>
                                        <strong>{{ $subtask->title }}</strong>
                                        @if($subtask->description)
                                            <br><small class="text-muted">{{ $subtask->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $fullName }}</strong>
                                        <br><small class="text-muted">{{ $assignedUser->name }}</small>
                                    </td>
                                    <td><span class="badge bg-primary">{{ $subtask->weight_percentage }}%</span></td>
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
                                            <button class="btn btn-xs btn-success mb-1" data-toggle="modal" data-target="#approveSubtaskModal{{ $subtask->subtaskID }}">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-xs btn-danger approve-subtask" data-id="{{ $subtask->subtaskID }}" data-action="reject">
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
                                        @else
                                            <em class="text-muted">Draft</em>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Approve Subtask Modal -->
                                <div class="modal fade" id="approveSubtaskModal{{ $subtask->subtaskID }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
                                            <form class="approve-subtask-form" data-id="{{ $subtask->subtaskID }}">
                                                @csrf
                                                <div class="modal-header text-white" style="background-color: #940000;">
                                                    <h6 class="modal-title">Approve: {{ $subtask->title }}</h6>
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
                                                        <small class="text-muted">Enter the actual score achieved (0 - {{ $subtask->weight_percentage }}%)</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Comments (Optional)</label>
                                                        <textarea name="hod_comments" class="form-control" rows="3" placeholder="Add feedback for the staff/teacher"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn text-white" style="background-color: #940000;">
                                                        <i class="fa fa-check"></i> Approve
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No subtasks to review yet.</td>
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
