@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    /* Remove border-radius from divs and cards */
    div, .card, .task-card, .alert, .btn {
        border-radius: 0 !important;
    }
    .task-card {
        border: 1px solid #e0e0e0;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: white;
    }
    .task-card:hover {
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    .status-badge {
        padding: 6px 12px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 0 !important;
    }
    .status-pending {
        background-color: #ffc100;
        color: #333;
    }
    .status-approved {
        background-color: #28a745;
        color: white;
    }
    .status-rejected {
        background-color: #dc3545;
        color: white;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-custom text-white">
                    <h4 class="mb-0"><i class="fa fa-tasks"></i> My Assigned Tasks</h4>
                </div>
                <div class="card-body">
                    <form id="filterTasksForm" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="statusFilter" class="form-label">Filter by Status:</label>
                                <select class="form-select" id="statusFilter" name="status" style="border-radius: 0 !important;">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="dateFilter" class="form-label">Filter by Date:</label>
                                <input type="date" class="form-control" id="dateFilter" name="date" value="{{ request('date') }}" style="border-radius: 0 !important;">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary-custom w-100" style="border-radius: 0 !important;">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tasks List -->
                    @if($tasks->count() > 0)
                        <div class="tasks-list">
                            @foreach($tasks as $task)
                                <div class="task-card">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="mb-3">
                                                <i class="fa fa-book text-primary-custom"></i> 
                                                {{ $task->sessionTimetable->subject->subject_name ?? ($task->sessionTimetable->classSubject->subject->subject_name ?? 'N/A') }}
                                            </h5>
                                            <p class="mb-2">
                                                <strong><i class="fa fa-users"></i> Class:</strong> 
                                                {{ $task->sessionTimetable->subclass->class->class_name ?? 'N/A' }} 
                                                - {{ $task->sessionTimetable->subclass->subclass_name ?? 'N/A' }}
                                            </p>
                                            <p class="mb-2">
                                                <strong><i class="fa fa-calendar"></i> Date:</strong> 
                                                {{ \Carbon\Carbon::parse($task->task_date)->format('l, F d, Y') }}
                                            </p>
                                            <p class="mb-2">
                                                <strong><i class="fa fa-clock-o"></i> Time:</strong> 
                                                {{ \Carbon\Carbon::parse($task->sessionTimetable->start_time)->format('h:i A') }} - 
                                                {{ \Carbon\Carbon::parse($task->sessionTimetable->end_time)->format('h:i A') }}
                                            </p>
                                            <div class="mt-3">
                                                <p class="mb-2">
                                                    <strong><i class="fa fa-book"></i> Topic:</strong> 
                                                    {{ $task->topic ?? 'N/A' }}
                                                </p>
                                                @if($task->subtopic)
                                                <p class="mb-2">
                                                    <strong><i class="fa fa-bookmark"></i> Subtopic:</strong> 
                                                    {{ $task->subtopic }}
                                                </p>
                                                @endif
                                                <div class="mt-3">
                                                    <strong><i class="fa fa-file-text"></i> Description:</strong>
                                                    <p class="mt-2" style="background: #f8f9fa; padding: 15px; border-left: 4px solid #940000;">
                                                        {{ $task->task_description }}
                                                    </p>
                                                </div>
                                            </div>
                                            @if($task->admin_comment)
                                                <div class="mt-3">
                                                    <strong><i class="fa fa-comment"></i> Admin Comment:</strong>
                                                    <p class="mt-2" style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;">
                                                        {{ $task->admin_comment }}
                                                    </p>
                                                </div>
                                            @endif
                                            @if($task->approved_by && $task->approved_at)
                                                <p class="mb-0 mt-2 text-muted">
                                                    <small>
                                                        <i class="fa fa-check-circle"></i> 
                                                        Approved by {{ $task->approver->name ?? 'Admin' }} 
                                                        on {{ \Carbon\Carbon::parse($task->approved_at)->format('M d, Y h:i A') }}
                                                    </small>
                                                </p>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <span class="status-badge status-{{ $task->status }}">
                                                @if($task->status == 'pending')
                                                    <i class="fa fa-clock-o"></i> Pending
                                                @elseif($task->status == 'approved')
                                                    <i class="fa fa-check-circle"></i> Approved
                                                @else
                                                    <i class="fa fa-times-circle"></i> Rejected
                                                @endif
                                            </span>
                                            <p class="mt-3 mb-0 text-muted">
                                                <small>
                                                    <i class="fa fa-calendar-o"></i> 
                                                    Submitted: {{ \Carbon\Carbon::parse($task->created_at)->format('M d, Y') }}
                                                </small>
                                            </p>
                                            @if($task->status == 'rejected')
                                                <div class="mt-3">
                                                    <button 
                                                        class="btn btn-primary-custom btn-sm" 
                                                        onclick="editTask({{ $task->session_taskID }}, '{{ $task->sessionTimetable->session_timetableID }}', '{{ $task->task_date->format('Y-m-d') }}', '{{ \Carbon\Carbon::parse($task->sessionTimetable->start_time)->format('H:i') }}', '{{ \Carbon\Carbon::parse($task->sessionTimetable->end_time)->format('H:i') }}', '{{ addslashes($task->topic ?? '') }}', '{{ addslashes($task->subtopic ?? '') }}', '{{ addslashes($task->task_description) }}')"
                                                        style="border-radius: 0 !important;"
                                                    >
                                                        <i class="fa fa-edit"></i> Edit & Resubmit
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-2x mb-3"></i>
                            <h5>No tasks found</h5>
                            <p>You haven't assigned any tasks yet, or no tasks match your filter criteria.</p>
                            <a href="{{ route('teacher.mySessions') }}" class="btn btn-primary-custom">
                                <i class="fa fa-plus"></i> Assign Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="fa fa-edit"></i> Edit & Resubmit Task
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editTaskForm">
                    <input type="hidden" id="edit_task_id" name="task_id">
                    <input type="hidden" id="edit_session_timetableID" name="session_timetableID">
                    <input type="hidden" id="edit_task_date" name="task_date">
                    
                    <!-- Date (Auto-filled) -->
                    <div class="form-group">
                        <label for="edit_task_date_display">Tarehe <span class="text-muted">(Auto)</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="edit_task_date_display" 
                            readonly
                            style="background-color: #f5f5f5; cursor: not-allowed; border-radius: 0 !important;"
                        >
                    </div>
                    
                    <!-- Time (Auto-filled) -->
                    <div class="form-group">
                        <label for="edit_task_time_display">Muda <span class="text-muted">(Auto)</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="edit_task_time_display" 
                            readonly
                            style="background-color: #f5f5f5; cursor: not-allowed; border-radius: 0 !important;"
                        >
                    </div>
                    
                    <!-- Topic -->
                    <div class="form-group">
                        <label for="edit_task_topic">Topic <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="edit_task_topic" 
                            name="topic" 
                            required
                            placeholder="Andika topic (mada kuu)..."
                            style="border-radius: 0 !important;"
                        >
                    </div>
                    
                    <!-- Subtopic -->
                    <div class="form-group">
                        <label for="edit_task_subtopic">Subtopic</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="edit_task_subtopic" 
                            name="subtopic" 
                            placeholder="Andika subtopic (mada ndogo) - optional..."
                            style="border-radius: 0 !important;"
                        >
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label for="edit_task_description">Description <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control" 
                            id="edit_task_description" 
                            name="task_description" 
                            rows="5" 
                            required
                            placeholder="Andika maelezo zaidi kuhusu topic aliyofundisha..."
                            style="border-radius: 0 !important;"
                        ></textarea>
                        <small class="form-text text-muted">Minimum 10 characters required</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary-custom btn-block" style="border-radius: 0 !important;">
                            <i class="fa fa-check-circle"></i> Update & Resubmit Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Script for handling filters
    $(document).ready(function() {
        $('#filterTasksForm').on('submit', function(e) {
            e.preventDefault();
            const status = $('#statusFilter').val();
            const date = $('#dateFilter').val();
            
            let url = '{{ route("teacher.myTasks") }}';
            const params = [];
            if (status) params.push(`status=${status}`);
            if (date) params.push(`date=${date}`);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            window.location.href = url;
        });

        // Edit Task Form Submit Handler
        $('#editTaskForm').on('submit', function(e) {
            e.preventDefault();
            
            const taskID = $('#edit_task_id').val();
            const formData = $(this).serialize();
            
            $.ajax({
                url: '/teacher/update-session-task/' + taskID,
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            $('#editTaskModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.error || 'Failed to update task',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to update task';
                    Swal.fire({
                        title: 'Error!',
                        text: error,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });
    });

    // Edit Task Function
    function editTask(taskID, sessionTimetableID, date, startTime, endTime, topic, subtopic, description) {
        function executeWhenReady() {
            if (typeof jQuery === 'undefined' || typeof $ === 'undefined' || typeof $.fn.modal === 'undefined') {
                setTimeout(executeWhenReady, 50);
                return;
            }
            
            // Set hidden fields
            $('#edit_task_id').val(taskID);
            $('#edit_session_timetableID').val(sessionTimetableID);
            $('#edit_task_date').val(date);
            
            // Set form fields with existing data
            $('#edit_task_topic').val(topic || '');
            $('#edit_task_subtopic').val(subtopic || '');
            $('#edit_task_description').val(description || '');
            
            // Format and display date
            const dateObj = new Date(date + 'T00:00:00');
            if (!isNaN(dateObj.getTime())) {
                const formattedDate = dateObj.toLocaleDateString('en-GB', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                $('#edit_task_date_display').val(formattedDate);
            } else {
                $('#edit_task_date_display').val(date);
            }
            
            // Format and display time
            const formatTime = (timeStr) => {
                if (!timeStr) return 'N/A';
                const parts = timeStr.split(':');
                if (parts.length >= 2) {
                    const hours = parseInt(parts[0]);
                    const minutes = parts[1];
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
                    return `${displayHours}:${minutes} ${ampm}`;
                }
                return timeStr;
            };
            
            const startTimeFormatted = formatTime(startTime);
            const endTimeFormatted = formatTime(endTime);
            $('#edit_task_time_display').val(`${startTimeFormatted} - ${endTimeFormatted}`);
            
            // Show modal
            $('#editTaskModal').modal('show');
        }
        executeWhenReady();
    }
</script>
