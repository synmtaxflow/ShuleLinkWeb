<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary-custom text-white">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Session Attendance - {{ $classSubject->subject->subject_name ?? 'N/A' }}
            </h5>
            <small>{{ $classSubject->subclass->class->class_name ?? '' }} - {{ $classSubject->subclass->subclass_name ?? '' }}</small>
        </div>
        <div class="card-body">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="date-tab" data-toggle="tab" href="#date-filter" role="tab" aria-controls="date-filter" aria-selected="true">
                        <i class="bi bi-calendar-day"></i> Filter by Date
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="month-tab" data-toggle="tab" href="#month-filter" role="tab" aria-controls="month-filter" aria-selected="false">
                        <i class="bi bi-calendar-month"></i> Filter by Month
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Date Filter Tab -->
                <div class="tab-pane fade show active" id="date-filter" role="tabpanel" aria-labelledby="date-tab">
            <div class="row mb-4">
                        <div class="col-md-3">
                    <label for="attendance_date">Select Date:</label>
                            <input type="date" class="form-control" id="attendance_date" value="{{ date('Y-m-d') }}" style="border-radius: 0 !important;">
                        </div>
                        <div class="col-md-3">
                            <label for="statusFilter">Filter by Status:</label>
                            <select class="form-control" id="statusFilter" style="border-radius: 0 !important;">
                                <option value="">All Statuses</option>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Late">Late</option>
                                <option value="Excused">Excused</option>
                            </select>
                </div>
                        <div class="col-md-3">
                    <label>&nbsp;</label>
                            <button class="btn btn-primary-custom btn-block" onclick="loadAttendanceData('date')" style="border-radius: 0 !important;">
                        <i class="bi bi-search"></i> View Attendance
                    </button>
                </div>
            </div>

                    <!-- Attendance Data for Date -->
            <div id="attendanceDataContainer">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> Please select a date and click "View Attendance" to see attendance records.
                        </div>
                    </div>
                </div>

                <!-- Month Filter Tab -->
                <div class="tab-pane fade" id="month-filter" role="tabpanel" aria-labelledby="month-tab">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="attendance_month">Select Month:</label>
                            <input type="month" class="form-control" id="attendance_month" value="{{ date('Y-m') }}">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary-custom btn-block" onclick="loadAttendanceData('month')">
                                <i class="bi bi-search"></i> View Statistics
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Data for Month -->
                    <div id="monthStatisticsContainer">
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> Please select a month and click "View Statistics" to see attendance statistics.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }
    .btn-primary-custom:hover {
        background-color: #7a0000;
        border-color: #7a0000;
        color: white;
    }
    table, .card, .modal-content, .btn, .nav-tabs, .form-control, select {
        border-radius: 0 !important;
    }
    .nav-tabs .nav-link.active {
        background-color: #940000;
        color: white;
        border-color: #940000;
    }
    .nav-tabs .nav-link {
        color: #940000;
    }
    .nav-tabs .nav-link:hover {
        border-color: #940000;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadAttendanceData(filterType) {
    const classSubjectID = {{ $classSubject->class_subjectID }};
    let url = '{{ route("teacher.get_session_attendance_data") }}';
    let data = {
        classSubjectID: classSubjectID,
        filter_type: filterType
    };
    
    if (filterType === 'date') {
        const date = $('#attendance_date').val();
    if (!date) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a date',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }
        data.attendance_date = date;
    $('#attendanceDataContainer').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
    } else {
        const month = $('#attendance_month').val();
        if (!month) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a month',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            return;
        }
        data.month = month;
        $('#monthStatisticsContainer').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
    }
    
    $.ajax({
        url: url,
        method: 'GET',
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                if (filterType === 'date') {
                    displayDateAttendance(response);
                } else {
                    displayMonthStatistics(response);
                }
            } else {
                const container = filterType === 'date' ? '#attendanceDataContainer' : '#monthStatisticsContainer';
                $(container).html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${response.error || 'Failed to load attendance data'}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to load attendance data';
            const container = filterType === 'date' ? '#attendanceDataContainer' : '#monthStatisticsContainer';
            $(container).html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${error}
                </div>
            `);
        }
    });
}

function displayDateAttendance(response) {
    if (!response.data || response.data.length === 0) {
                    $('#attendanceDataContainer').html(`
                        <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i> No attendance collected on this date.
                        </div>
                    `);
                    return;
                }
    
    // Get status filter value
    const statusFilter = $('#statusFilter').val();
                
                let html = '<div class="table-responsive">';
                response.data.forEach(function(session) {
                    html += '<div class="mb-4">';
                    html += '<h6 class="text-primary-custom">';
                    html += '<i class="bi bi-clock"></i> ' + session.day + ' - ';
                    html += session.start_time + ' to ' + session.end_time;
                    html += '</h6>';
                    html += '<table class="table table-bordered table-hover">';
                    html += '<thead class="thead-light">';
                    html += '<tr><th>Student Name</th><th>Status</th><th>Remark</th></tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    session.attendance.forEach(function(att) {
            // Apply status filter
            if (statusFilter && att.status !== statusFilter) {
                return; // Skip if doesn't match filter
            }
            
                        if (att.status) {
                            const statusClass = att.status === 'Present' ? 'success' : 
                                              att.status === 'Absent' ? 'danger' : 
                                              att.status === 'Late' ? 'warning' : 'info';
                
                // Highlight absent students in red background
                const rowStyle = att.status === 'Absent' ? 'style="background-color: #ffcdd2 !important;"' : '';
                const nameStyle = att.status === 'Absent' ? 'style="color: #c62828; font-weight: bold;"' : '';
                
                html += '<tr ' + rowStyle + '>';
                html += '<td><span ' + nameStyle + '>' + att.name + '</span></td>';
                            html += '<td><span class="badge badge-' + statusClass + '">' + att.status + '</span></td>';
                            html += '<td>' + (att.remark || '-') + '</td>';
                            html += '</tr>';
                        }
                    });
                    
                    html += '</tbody></table></div>';
                });
                html += '</div>';
                
                $('#attendanceDataContainer').html(html);
}

function displayMonthStatistics(response) {
    if (!response.data || response.data.length === 0) {
        $('#monthStatisticsContainer').html(`
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i> No attendance data found for this month.
                </div>
            `);
        return;
    }
    
    let html = '<div class="alert alert-info mb-3">';
    html += '<strong>Total Sessions in Month:</strong> ' + response.total_sessions;
    html += '</div>';
    html += '<div class="table-responsive">';
    html += '<table class="table table-bordered table-hover">';
    html += '<thead class="thead-light">';
    html += '<tr>';
    html += '<th>Student Name</th>';
    html += '<th>Total Sessions</th>';
    html += '<th>Attended</th>';
    html += '<th>Present</th>';
    html += '<th>Absent</th>';
    html += '<th>Late</th>';
    html += '<th>Excused</th>';
    html += '<th>Percentage</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    response.data.forEach(function(student) {
        const percentage = student.total_sessions > 0 
            ? ((student.attended_sessions / student.total_sessions) * 100).toFixed(1) 
            : '0.0';
        
        html += '<tr>';
        html += '<td>' + student.name + '</td>';
        html += '<td>' + student.total_sessions + '</td>';
        html += '<td>' + student.attended_sessions + '</td>';
        html += '<td><span class="badge badge-success">' + student.present + '</span></td>';
        html += '<td><span class="badge badge-danger">' + student.absent + '</span></td>';
        html += '<td><span class="badge badge-warning">' + student.late + '</span></td>';
        html += '<td><span class="badge badge-info">' + student.excused + '</span></td>';
        html += '<td><strong>' + percentage + '%</strong></td>';
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    
    $('#monthStatisticsContainer').html(html);
}

// Load attendance on page load if date is set
$(document).ready(function() {
    if ($('#attendance_date').val()) {
        loadAttendanceData('date');
    }
});
</script>
