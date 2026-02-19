@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .task-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .task-card:hover {
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15);
    }
    .task-card.pending {
        border-left: 4px solid #ffc107;
    }
    .task-card.approved {
        border-left: 4px solid #28a745;
    }
    .task-card.rejected {
        border-left: 4px solid #dc3545;
    }
    /* Remove border-radius from divs and cards */
    div, .card, .alert, .btn, table {
        border-radius: 0 !important;
    }
</style>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-primary-custom text-white rounded">
                    <h4 class="mb-0">
                        <i class="bi bi-tasks"></i> Task Management
                    </h4>
                    <small>Review and approve tasks assigned by teachers</small>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="filter_teacher">Filter by Teacher:</label>
                            <select class="form-control" id="filter_teacher">
                                <option value="">All Teachers</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->first_name }} {{ $teacher->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filter_date">Filter by Date:</label>
                            <input type="date" class="form-control" id="filter_date">
                        </div>
                        <div class="col-md-4">
                            <label for="filter_status">Filter by Status:</label>
                            <select class="form-control" id="filter_status">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button class="btn btn-primary-custom" onclick="loadTasks()">
                                <i class="bi bi-search"></i> Apply Filters
                            </button>
                            <button class="btn btn-secondary" onclick="resetFilters()">
                                <i class="bi bi-x-circle"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="taskTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="teacher-tasks-tab" data-toggle="tab" href="#teacher-tasks" role="tab">
                        <i class="bi bi-person-check"></i> Teacher Tasks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="other-staff-tab" data-toggle="tab" href="#other-staff" role="tab">
                        <i class="bi bi-people"></i> Other Staff
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="taskTabsContent">
                <!-- Teacher Tasks Tab -->
                <div class="tab-pane fade show active" id="teacher-tasks" role="tabpanel">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <div id="teacherTasksContainer">
                                <div class="text-center">
                                    <div class="spinner-border text-primary-custom" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other Staff Tab -->
                <div class="tab-pane fade" id="other-staff" role="tabpanel">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Other Staff tasks will be displayed here in future updates.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve/Reject Task Modal -->
<div class="modal fade" id="taskActionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="taskActionModalTitle">
                    <i class="bi bi-check-circle"></i> Approve Task
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="taskActionForm">
                    <input type="hidden" id="task_action_id" name="taskID">
                    <input type="hidden" id="task_action_type" name="actionType">
                    
                    <div class="form-group">
                        <label for="admin_comment" id="comment_label">Comment:</label>
                        <textarea 
                            class="form-control" 
                            id="admin_comment" 
                            name="admin_comment" 
                            rows="4"
                            placeholder=""
                            style="border-radius: 0 !important;"
                        ></textarea>
                        <small class="form-text text-muted" id="comment_help">Optional comment for approval</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary-custom btn-block" id="taskActionSubmitBtn">
                            <i class="bi bi-check-circle"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
function loadTasks() {
    const teacherID = $('#filter_teacher').val();
    const date = $('#filter_date').val();
    const status = $('#filter_status').val();
    
    $('#teacherTasksContainer').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
    
    $.ajax({
        url: '/admin/get-teacher-tasks',
        method: 'GET',
        data: {
            teacherID: teacherID,
            date: date,
            status: status
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                displayTasks(response.tasks);
            } else {
                $('#teacherTasksContainer').html('<div class="alert alert-danger">' + response.error + '</div>');
            }
        },
        error: function() {
            $('#teacherTasksContainer').html('<div class="alert alert-danger">Failed to load tasks</div>');
        }
    });
}

function displayTasks(tasks) {
    if (tasks.length === 0) {
        $('#teacherTasksContainer').html('<div class="alert alert-info text-center"><i class="bi bi-info-circle"></i> No tasks found</div>');
        return;
    }
    
    let html = '<div class="table-responsive">';
    html += '<table class="table table-bordered table-hover" id="tasksTable">';
    html += '<thead class="bg-primary-custom text-white">';
    html += '<tr>';
    html += '<th>Teacher</th>';
    html += '<th>Class-Subclass</th>';
    html += '<th>Subject</th>';
    html += '<th>Topic</th>';
    html += '<th>Subtopic</th>';
    html += '<th>Time</th>';
    html += '<th>Date</th>';
    html += '<th>Day</th>';
    html += '<th>Actions</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    tasks.forEach(function(task) {
        const statusBadge = task.status === 'approved' ? 'success' : 
                          task.status === 'rejected' ? 'danger' : 'warning';
        
        html += '<tr>';
        html += '<td>' + task.teacher_name + '</td>';
        html += '<td>' + task.class_name + '</td>';
        html += '<td>' + task.subject_name + '</td>';
        html += '<td>' + (task.topic || 'N/A') + '</td>';
        html += '<td>' + (task.subtopic || 'N/A') + '</td>';
        html += '<td>' + task.time_display + '</td>';
        html += '<td>' + task.task_date_display + '</td>';
        html += '<td>' + task.day + '</td>';
        html += '<td>';
        
        if (task.status === 'pending') {
            html += '<button class="btn btn-success btn-sm mr-1" onclick="approveTask(' + task.session_taskID + ')" title="Approve">';
            html += '<i class="bi bi-check-circle"></i>';
            html += '</button>';
            html += '<button class="btn btn-danger btn-sm" onclick="rejectTask(' + task.session_taskID + ')" title="Reject">';
            html += '<i class="bi bi-x-circle"></i>';
            html += '</button>';
        } else {
            html += '<span class="badge badge-' + statusBadge + '">' + task.status.toUpperCase() + '</span>';
        }
        
        html += '</td>';
        html += '</tr>';
    });
    
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    
    $('#teacherTasksContainer').html(html);
    
    // Initialize DataTable for filtering
    if ($.fn.DataTable) {
        $('#tasksTable').DataTable({
            "pageLength": 25,
            "order": [[6, "desc"]], // Sort by date descending
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered from _MAX_ total entries)"
            }
        });
    }
}

function approveTask(taskID) {
    // Prevent multiple clicks
    if ($('#taskActionModal').hasClass('show')) {
        return;
    }
    
    // Wait for jQuery and Bootstrap to be available
    function executeWhenReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined' || typeof $.fn.modal === 'undefined') {
            setTimeout(executeWhenReady, 50);
            return;
        }
        
        $('#task_action_id').val(taskID);
        $('#task_action_type').val('approve');
        $('#taskActionModalTitle').html('<i class="bi bi-check-circle"></i> Approve Task');
        $('#taskActionSubmitBtn').html('<i class="bi bi-check-circle"></i> Approve Task');
        $('#comment_label').html('Comment: <span class="text-muted">(Optional)</span>');
        $('#comment_help').html('Optional comment for approval');
        $('#admin_comment').attr('required', false);
        $('#admin_comment').attr('placeholder', 'Add an optional comment...');
        $('#admin_comment').val('');
        
        // Ensure button is enabled
        $('#taskActionSubmitBtn').prop('disabled', false).removeAttr('disabled');
        
        // Show modal with proper settings
        $('#taskActionModal').modal({
            backdrop: true,
            keyboard: true,
            show: true
        });
    }
    executeWhenReady();
}

function rejectTask(taskID) {
    // Prevent multiple clicks
    if ($('#taskActionModal').hasClass('show')) {
        return;
    }
    
    // Wait for jQuery and Bootstrap to be available
    function executeWhenReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined' || typeof $.fn.modal === 'undefined') {
            setTimeout(executeWhenReady, 50);
            return;
        }
        
        $('#task_action_id').val(taskID);
        $('#task_action_type').val('reject');
        $('#taskActionModalTitle').html('<i class="bi bi-x-circle"></i> Reject Task');
        $('#taskActionSubmitBtn').html('<i class="bi bi-x-circle"></i> Reject Task');
        $('#comment_label').html('Reason: <span class="text-danger">*</span>');
        $('#comment_help').html('Please provide a reason for rejection (required)');
        $('#admin_comment').attr('required', true);
        $('#admin_comment').attr('placeholder', 'Enter reason for rejection...');
        $('#admin_comment').val('');
        
        // Ensure button is enabled
        $('#taskActionSubmitBtn').prop('disabled', false).removeAttr('disabled');
        
        // Show modal with proper settings
        $('#taskActionModal').modal({
            backdrop: true,
            keyboard: true,
            show: true
        });
    }
    executeWhenReady();
}

function resetFilters() {
    $('#filter_teacher').val('');
    $('#filter_date').val('');
    $('#filter_status').val('');
    loadTasks();
}

$('#taskActionForm').on('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Disable button to prevent double submission
    const submitBtn = $('#taskActionSubmitBtn');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Processing...');
    
    const taskID = $('#task_action_id').val();
    const actionType = $('#task_action_type').val();
    const comment = $('#admin_comment').val().trim();
    
    if (actionType === 'reject' && !comment) {
        submitBtn.prop('disabled', false).html(originalText);
        Swal.fire({
            title: 'Error!',
            text: 'Reason is required for rejection',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }
    
    const url = actionType === 'approve' 
        ? '/approve-task/' + taskID 
        : '/reject-task/' + taskID;
    
    $.ajax({
        url: url,
        method: 'POST',
        data: {
            admin_comment: comment || null
        },
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
                    $('#taskActionModal').modal('hide');
                    // Re-enable button after modal closes
                    submitBtn.prop('disabled', false).html(originalText);
                    loadTasks();
                });
            } else {
                submitBtn.prop('disabled', false).html(originalText);
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to process task',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html(originalText);
            const error = xhr.responseJSON?.error || 'Failed to process task';
            Swal.fire({
                title: 'Error!',
                text: error,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
});

// Ensure buttons are enabled when modal is shown
$('#taskActionModal').on('shown.bs.modal', function() {
    $('#taskActionSubmitBtn').prop('disabled', false).removeAttr('disabled');
});

// Re-enable buttons when modal is hidden
$('#taskActionModal').on('hidden.bs.modal', function() {
    $('#taskActionSubmitBtn').prop('disabled', false).removeAttr('disabled');
    $('#admin_comment').val('');
});

// Load tasks on page load
$(document).ready(function() {
    loadTasks();
});
</script>

@include('includes.footer')

