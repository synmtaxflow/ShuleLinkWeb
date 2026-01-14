@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<!-- jsPDF Library for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<!-- html2canvas for converting HTML to image -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom {
        background-color: #f5f5f5 !important;
        color: #212529 !important;
    }
    .text-primary-custom {
        color: #212529 !important;
    }
    div, .card, .session-card, .alert, .btn {
        border-radius: 0 !important;
    }
    .session-card {
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: white;
    }
    .session-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    .time-badge {
        background: #f5f5f5;
        color: #212529;
        padding: 5px 12px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid #e9ecef;
    }
    .btn-session-action {
        background: #f5f5f5 !important;
        color: #212529 !important;
        border: 1px solid #e9ecef !important;
        padding: 8px 20px;
        font-weight: 600;
    }
    .lesson-plan-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        border: 2px solid #212529;
        font-size: 0.9rem;
    }
    .lesson-plan-table td, .lesson-plan-table th {
        border: 1px solid #212529 !important;
        padding: 3px 5px;
        text-align: left;
    }
    .lesson-plan-table th {
        background-color: #f5f5f5;
        color: #212529;
        font-weight: bold;
        border: 1px solid #212529 !important;
    }
    .lesson-plan-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .lesson-plan-table tbody tr:nth-child(odd) {
        background-color: white;
    }
    .lesson-plan-table tbody tr {
        border: 1px solid #212529;
    }
    .lesson-plan-table input, .lesson-plan-table textarea {
        width: 100%;
        border: 1px solid #212529;
        padding: 2px 4px;
        background-color: white;
        font-size: 0.9rem;
    }
    .lesson-plan-header {
        text-align: center;
        font-weight: bold;
        font-size: 18px;
        margin: 20px 0;
    }
    #lessonPlanTabs .nav-link {
        color: #212529;
        border-radius: 0 !important;
    }
    #lessonPlanTabs .nav-link.active {
        background-color: #f5f5f5 !important;
        color: #212529 !important;
        border-bottom: 2px solid #212529;
    }
    .dotted-line {
        border-bottom: 2px dotted #212529;
        min-height: 30px;
        padding: 5px 0;
        margin: 5px 0;
    }
    .signature-container {
        margin: 20px 0;
    }
    .signature-label {
        font-weight: bold;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    .signature-canvas {
        border: 2px solid #212529;
        border-radius: 4px;
        cursor: crosshair;
        background-color: white;
        width: 100%;
        max-width: 400px;
    }
    .signature-preview {
        border: 2px solid #212529;
        border-radius: 4px;
        max-width: 100%;
        height: auto;
    }
    .signature-actions {
        margin-top: 10px;
    }
    .signature-actions button {
        margin-right: 5px;
        padding: 5px 15px;
        font-size: 0.85rem;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-primary-custom" style="border-bottom: 1px solid #e9ecef;">
                    <h4 class="mb-2">
                        <i class="bi bi-book"></i> Welcome to Lesson Plan Management
                    </h4>
                    <p class="mb-0">Let's simplify your teaching style</p>
                </div>
            </div>

            @if(isset($message))
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> {{ $message }}
                </div>
            @else
                <!-- Subject Selector -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Select Subject</h5>
                        <div class="form-group">
                            <label>Subject</label>
                            <select class="form-control" id="subjectSelector" onchange="loadSessionsBySubject()" style="width: 100%;">
                                <option value="">-- Select Subject --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject['subjectID'] }}">{{ $subject['subject_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sessions Display -->
                <div id="sessionsContainer">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">Sessions</h5>
                            <div id="sessionsList">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Please select a subject to view sessions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Lesson Plan Modal -->
<div class="modal fade" id="lessonPlanModal" tabindex="-1" role="dialog" style="z-index: 1050;">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom" style="border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title">
                    <i class="bi bi-journal-text"></i> Lesson Plan Management
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color: #212529;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist" id="lessonPlanTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="create-tab" data-toggle="tab" href="#create-lesson-plan" role="tab">
                            <i class="bi bi-plus-circle"></i> Create Lesson Plan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="manage-tab" data-toggle="tab" href="#manage-lesson-plan" role="tab">
                            <i class="bi bi-pencil-square"></i> Manage Lesson Plan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="view-tab" data-toggle="tab" href="#view-lesson-plan" role="tab">
                            <i class="bi bi-eye"></i> View Lesson Plan
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="lessonPlanTabContent">
                    <!-- Create Tab -->
                    <div class="tab-pane fade show active" id="create-lesson-plan" role="tabpanel">
                        <div id="createTabContent">
                            <div class="text-center mb-3">
                                <button class="btn btn-lg mr-3" onclick="showCreateNewForm()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                                    <i class="bi bi-file-plus"></i> Create New Lesson Plan
                                </button>
                                <button class="btn btn-lg" onclick="showUseExistingForm()" style="background-color: white; color: #212529; border: 1px solid #e9ecef;">
                                    <i class="bi bi-folder"></i> Use Existing Lesson Plan
                                </button>
                            </div>
                            <div id="createNewForm" style="display: none;">
                                <!-- Lesson Plan Form will be loaded here -->
                            </div>
                            <div id="useExistingForm" style="display: none;">
                                <div class="form-group">
                                    <label>Select Date</label>
                                    <input type="date" class="form-control" id="existingLessonDate" onchange="loadExistingLessonPlan()">
                                </div>
                                <div id="existingLessonPlanContent"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Manage Tab -->
                    <div class="tab-pane fade" id="manage-lesson-plan" role="tabpanel">
                        <div id="manageTabContent">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Filter Lesson Plans to Manage</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Filter Type</label>
                                                <select class="form-control" id="manageFilterType" onchange="toggleManageFilterOptions()">
                                                    <option value="single_date">Single Date</option>
                                                    <option value="date_range">Date Range</option>
                                                    <option value="year">By Year</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="manageSingleDateFilter">
                                            <div class="form-group">
                                                <label>Select Date</label>
                                                <input type="date" class="form-control" id="manageLessonDate" onchange="loadLessonPlanForManage()">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="manageDateRangeFilter1" style="display: none;">
                                            <div class="form-group">
                                                <label>From Date</label>
                                                <input type="date" class="form-control" id="manageFromDate">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="manageDateRangeFilter2" style="display: none;">
                                            <div class="form-group">
                                                <label>To Date</label>
                                                <input type="date" class="form-control" id="manageToDate">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="manageYearFilter" style="display: none;">
                                            <div class="form-group">
                                                <label>Year</label>
                                                <input type="number" class="form-control" id="manageYear" min="2020" max="2100" value="{{ date('Y') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button class="btn btn-block" onclick="loadLessonPlansForManage()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                                                    <i class="bi bi-search"></i> Load Lesson Plans
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="manageLessonPlanContent">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Please select filter options and click "Load Lesson Plans"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- View Tab -->
                    <div class="tab-pane fade" id="view-lesson-plan" role="tabpanel">
                        <div id="viewTabContent">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Filter Lesson Plans</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Filter Type</label>
                                                <select class="form-control" id="filterType" onchange="toggleFilterOptions()">
                                                    <option value="date_range">Date Range</option>
                                                    <option value="year">By Year</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="dateRangeFilter">
                                            <div class="form-group">
                                                <label>From Date</label>
                                                <input type="date" class="form-control" id="viewFromDate">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="dateRangeFilter2">
                                            <div class="form-group">
                                                <label>To Date</label>
                                                <input type="date" class="form-control" id="viewToDate">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="yearFilter" style="display: none;">
                                            <div class="form-group">
                                                <label>Year</label>
                                                <input type="number" class="form-control" id="viewYear" min="2020" max="2100" value="{{ date('Y') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button class="btn btn-block" onclick="loadLessonPlansByFilter()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                                                    <i class="bi bi-search"></i> Load Lesson Plans
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="viewLessonPlanContent">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Please select filter options and click "Load Lesson Plans"</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentSessionData = {
    sessionTimetableID: null,
    day: null,
    startTime: null,
    endTime: null,
    subjectName: null,
    className: null
};

// Load sessions by subject
function loadSessionsBySubject() {
    const subjectID = $('#subjectSelector').val();
    
    if (!subjectID) {
        $('#sessionsList').html(`
            <div class="text-center text-muted py-5">
                <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                <p class="mt-3">Please select a subject to view sessions</p>
            </div>
        `);
        return;
    }
    
    // Show loading
    $('#sessionsList').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading sessions...</p>
        </div>
    `);
    
    $.ajax({
        url: '{{ route("teacher.get_sessions_by_subject") }}',
        method: 'GET',
        data: {
            subjectID: subjectID
        },
        success: function(response) {
            if (response.success && response.sessions.length > 0) {
                let html = '<div class="row">';
                
                response.sessions.forEach(function(session) {
                    const startTime = formatTime(session.start_time);
                    const endTime = formatTime(session.end_time);
                    
                    html += `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="session-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="time-badge">
                                        <i class="bi bi-clock"></i> ${startTime} - ${endTime}
                                    </span>
                                </div>
                                <h6 class="mb-2" style="font-weight: bold;">
                                    <i class="bi bi-book text-primary-custom"></i> ${session.subject_name}
                                </h6>
                                <p class="mb-2 text-muted">
                                    <i class="bi bi-people"></i> ${session.class_name} - ${session.subclass_name}
                                </p>
                                <p class="mb-2 text-muted">
                                    <i class="bi bi-calendar"></i> ${session.day}
                                </p>
                                <p class="mb-2 text-muted small" id="sessionDates_${session.session_timetableID}">
                                    <i class="bi bi-calendar3"></i> <span class="text-info">Loading dates...</span>
                                </p>
                                <button 
                                    class="btn btn-session-action btn-sm btn-block" 
                                    onclick="openLessonPlanModal(${session.session_timetableID}, '${session.day}', '${session.start_time}', '${session.end_time}', '${session.subject_name}', '${session.class_name}')"
                                >
                                    <i class="bi bi-journal-text"></i> My Lesson Plan
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                $('#sessionsList').html(html);
                
                // Load dates for each session
                response.sessions.forEach(function(session) {
                    loadSessionDates(session.session_timetableID);
                });
            } else {
                $('#sessionsList').html(`
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                        <p class="mt-3">${response.error || 'No session available for this subject'}</p>
                    </div>
                `);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to load sessions';
            $('#sessionsList').html(`
                <div class="text-center text-danger py-5">
                    <i class="bi bi-x-circle" style="font-size: 3rem;"></i>
                    <p class="mt-3">${error}</p>
                </div>
            `);
        }
    });
}

function formatTime(timeStr) {
    if (!timeStr) return 'N/A';
    const parts = timeStr.split(':');
    const hours = parseInt(parts[0]);
    const minutes = parts[1];
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 || 12;
    return displayHours + ':' + minutes + ' ' + ampm;
}

function loadSessionDates(sessionTimetableID) {
    const currentYear = {{ $currentYear }};
    
    $.ajax({
        url: '{{ route("teacher.get_all_sessions_for_year") }}',
        method: 'GET',
        data: {
            session_timetableID: sessionTimetableID,
            year: currentYear
        },
        success: function(response) {
            if (response.success && response.data.dates.length > 0) {
                const dates = response.data.dates;
                const totalSessions = dates.length;
                const sessionsWithPlans = dates.filter(d => d.has_lesson_plan).length;
                
                // Show first few dates and total count
                let datesText = '';
                if (dates.length <= 5) {
                    datesText = dates.map(d => d.formatted_date).join(', ');
                } else {
                    datesText = dates.slice(0, 3).map(d => d.formatted_date).join(', ') + ' ... (' + totalSessions + ' sessions)';
                }
                
                $('#sessionDates_' + sessionTimetableID).html(
                    '<i class="bi bi-calendar3"></i> <span class="text-info">' + totalSessions + ' sessions/year</span> | ' +
                    '<span class="text-success">' + sessionsWithPlans + ' with plans</span><br>' +
                    '<small class="text-muted">' + datesText + '</small>'
                );
            } else {
                $('#sessionDates_' + sessionTimetableID).html(
                    '<i class="bi bi-calendar3"></i> <span class="text-muted">No sessions available</span>'
                );
            }
        },
        error: function() {
            $('#sessionDates_' + sessionTimetableID).html(
                '<i class="bi bi-calendar3"></i> <span class="text-danger">Error loading dates</span>'
            );
        }
    });
}

function openLessonPlanModal(sessionTimetableID, day, startTime, endTime, subjectName, className) {
    currentSessionData = {
        sessionTimetableID: sessionTimetableID,
        day: day,
        startTime: startTime,
        endTime: endTime,
        subjectName: subjectName,
        className: className
    };
    
    $('#lessonPlanModal').modal('show');
    $('#create-tab').tab('show');
    $('#createNewForm').hide();
    $('#useExistingForm').hide();
}

function showCreateNewForm() {
    $('#useExistingForm').hide();
    $('#createNewForm').show();
    loadCreateNewForm();
}

function showUseExistingForm() {
    $('#createNewForm').hide();
    $('#useExistingForm').show();
    
    // Show date picker to check if lesson plan exists
    Swal.fire({
        title: 'Select Date to View Lesson Plan',
        html: '<input type="date" id="swal-existing-date" class="swal2-input" value="' + new Date().toISOString().split('T')[0] + '">',
        showCancelButton: true,
        confirmButtonColor: '#f5f5f5',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            return document.getElementById('swal-existing-date').value;
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const sessionDate = result.value;
            
            // Check if lesson plan exists
            $.ajax({
                url: '{{ route("teacher.check_lesson_plan_exists") }}',
                method: 'GET',
                data: {
                    session_timetableID: currentSessionData.sessionTimetableID,
                    date: sessionDate
                },
                success: function(checkResponse) {
                    if (checkResponse.success && checkResponse.exists) {
                        // Load existing lesson plan
                        loadExistingLessonPlanByDate(sessionDate);
                    } else {
                        Swal.fire({
                            title: 'Not Found!',
                            text: 'No lesson plan exists for this date. Please create a new one.',
                            icon: 'info',
                            confirmButtonColor: '#f5f5f5',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to check lesson plan',
                        icon: 'error',
                        confirmButtonColor: '#f5f5f5'
                    });
                }
            });
        }
    });
}

function loadExistingLessonPlanByDate(date) {
    $('#existingLessonDate').val(date);
    loadExistingLessonPlan();
}

function loadCreateNewForm() {
    // Show date picker first
    Swal.fire({
        title: 'Select Date for Lesson Plan',
        html: '<input type="date" id="swal-date" class="swal2-input" value="' + new Date().toISOString().split('T')[0] + '">',
        showCancelButton: true,
        confirmButtonColor: '#f5f5f5',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            return document.getElementById('swal-date').value;
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const sessionDate = result.value;
            
            // First check if lesson plan already exists
            $.ajax({
                url: '{{ route("teacher.check_lesson_plan_exists") }}',
                method: 'GET',
                data: {
                    session_timetableID: currentSessionData.sessionTimetableID,
                    date: sessionDate
                },
                success: function(checkResponse) {
                    if (checkResponse.success && checkResponse.exists) {
                        Swal.fire({
                            title: 'Already Exists!',
                            text: 'Lesson plan already exists for this date. Please use "Manage Lesson Plan" tab to edit or "Use Existing Lesson Plan" to view.',
                            icon: 'warning',
                            confirmButtonColor: '#f5f5f5',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    
                    // If doesn't exist, proceed to get attendance stats
                    $.ajax({
                        url: '{{ route("teacher.get_session_attendance_stats") }}',
                        method: 'GET',
                        data: {
                            session_timetableID: currentSessionData.sessionTimetableID,
                            date: sessionDate
                        },
                        success: function(response) {
                            if (response.success) {
                                renderLessonPlanForm(response.data, sessionDate);
                            } else {
                                let errorMessage = response.error || 'Failed to load attendance statistics';
                                let icon = 'error';
                                
                                // Check date status
                                if (response.date_status === 'weekend') {
                                    icon = 'info';
                                } else if (response.date_status === 'holiday') {
                                    icon = 'warning';
                                } else if (response.date_status === 'no_session') {
                                    icon = 'info';
                                }
                                
                                Swal.fire({
                                    title: icon === 'error' ? 'Error!' : 'Notice',
                                    text: errorMessage,
                                    icon: icon,
                                    confirmButtonColor: '#f5f5f5',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load attendance statistics',
                                icon: 'error',
                                confirmButtonColor: '#f5f5f5'
                            });
                        }
                    });
                },
                error: function() {
                    // If check fails, proceed anyway
                    $.ajax({
                        url: '{{ route("teacher.get_session_attendance_stats") }}',
                        method: 'GET',
                        data: {
                            session_timetableID: currentSessionData.sessionTimetableID,
                            date: sessionDate
                        },
                        success: function(response) {
                            if (response.success) {
                                renderLessonPlanForm(response.data, sessionDate);
                            } else {
                                let errorMessage = response.error || 'Failed to load attendance statistics';
                                let icon = 'error';
                                
                                if (response.date_status === 'weekend') {
                                    icon = 'info';
                                } else if (response.date_status === 'holiday') {
                                    icon = 'warning';
                                } else if (response.date_status === 'no_session') {
                                    icon = 'info';
                                }
                                
                                Swal.fire({
                                    title: icon === 'error' ? 'Error!' : 'Notice',
                                    text: errorMessage,
                                    icon: icon,
                                    confirmButtonColor: '#f5f5f5',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to load attendance statistics',
                                icon: 'error',
                                confirmButtonColor: '#f5f5f5'
                            });
                        }
                    });
                }
            });
        }
    });
}

function renderLessonPlanForm(attendanceData, date) {
    const schoolType = '{{ $schoolType }}';
    const currentYear = {{ $currentYear }};
    
    // Format time
    const formatTime = (timeStr) => {
        if (!timeStr) return 'N/A';
        const parts = timeStr.split(':');
        const hours = parseInt(parts[0]);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return displayHours + ':' + minutes + ' ' + ampm;
    };
    
    const startTime = formatTime(attendanceData.start_time);
    const endTime = formatTime(attendanceData.end_time);
    const dateObj = new Date(date);
    const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    
    let html = `
        <div class="lesson-plan-header">LESSON PLAN</div>
        <div class="lesson-plan-header">${schoolType}</div>
        
        <table class="lesson-plan-table">
            <tr>
                <th>SUBJECT:</th>
                <td><input type="text" id="lesson_subject" value="${attendanceData.subject || ''}" readonly style="background-color: #f5f5f5;"></td>
                <th>CLASS:</th>
                <td><input type="text" id="lesson_class" value="${attendanceData.class_name || ''}" readonly style="background-color: #f5f5f5;"></td>
                <th>YEAR:</th>
                <td><input type="text" id="lesson_year" value="${currentYear}" readonly style="background-color: #f5f5f5;"></td>
            </tr>
            <tr>
                <th>TEACHER'S NAME</th>
                <td colspan="2"><input type="text" id="lesson_teacher_name" value="${attendanceData.teacher_name || ''}" readonly style="background-color: #f5f5f5; border: 1px solid #212529; padding: 2px 4px; width: 100%;"></td>
                <td colspan="3">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #212529;">
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">REGISTERED</th>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.registered_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.registered_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.registered_total || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.present_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.present_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.present_total || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>TIME</th>
                <td><input type="text" value="${startTime} - ${endTime}" readonly style="background-color: #f5f5f5;"></td>
                <th>DATE</th>
                <td colspan="3"><input type="text" id="lesson_date" value="${formattedDate}" readonly style="background-color: #f5f5f5;"></td>
            </tr>
        </table>
        
        <table class="lesson-plan-table">
            <tr>
                <th>MAIN COMPETENCE</th>
                <td><textarea id="main_competence" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>SPECIFIC COMPETENCE</th>
                <td><textarea id="specific_competence" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>MAIN ACTIVITY</th>
                <td><textarea id="main_activity" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>SPECIFIC ACTIVITY</th>
                <td><textarea id="specific_activity" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>TEACHING & LEARNING RESOURCES</th>
                <td><textarea id="teaching_learning_resources" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>REFERENCES</th>
                <td><textarea id="references" rows="2"></textarea></td>
            </tr>
        </table>
        
        <h5 class="mt-4 mb-3">LESSON DEVELOPMENT</h5>
        <table class="lesson-plan-table">
            <thead>
                <tr>
                    <th>STAGE</th>
                    <th>TIME</th>
                    <th>TEACHING ACTIVITIES</th>
                    <th>LEARNING ACTIVITIES</th>
                    <th>ASSESSMENT CRITERIA</th>
                </tr>
            </thead>
            <tbody id="lessonStagesTable">
                <tr>
                    <td>Introduction</td>
                    <td><input type="text" class="stage-time" placeholder="e.g., 5 minutes"></td>
                    <td><textarea class="stage-teaching" rows="2"></textarea></td>
                    <td><textarea class="stage-learning" rows="2"></textarea></td>
                    <td><textarea class="stage-assessment" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td>Competence development</td>
                    <td><input type="text" class="stage-time" placeholder="e.g., 15 minutes"></td>
                    <td><textarea class="stage-teaching" rows="2"></textarea></td>
                    <td><textarea class="stage-learning" rows="2"></textarea></td>
                    <td><textarea class="stage-assessment" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td>Design</td>
                    <td><input type="text" class="stage-time" placeholder="e.g., 15 minutes"></td>
                    <td><textarea class="stage-teaching" rows="2"></textarea></td>
                    <td><textarea class="stage-learning" rows="2"></textarea></td>
                    <td><textarea class="stage-assessment" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td>Realization</td>
                    <td><input type="text" class="stage-time" placeholder="e.g., 5 minutes"></td>
                    <td><textarea class="stage-teaching" rows="2"></textarea></td>
                    <td><textarea class="stage-learning" rows="2"></textarea></td>
                    <td><textarea class="stage-assessment" rows="2"></textarea></td>
                </tr>
            </tbody>
        </table>
        
        <div class="form-group mt-3">
            <label><strong>Remarks:</strong></label>
            <div class="dotted-line">
                <textarea id="remarks" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Reflection:</strong></label>
            <div class="dotted-line">
                <textarea id="reflection" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
            <div class="dotted-line">
                <textarea class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Evaluation:</strong></label>
            <div class="dotted-line">
                <textarea id="evaluation" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
            <div class="dotted-line">
                <textarea class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="signature-container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-label">Subject Teacher's Signature:</div>
                    <canvas id="teacherSignatureCanvas" class="signature-canvas" width="400" height="150"></canvas>
                    <div class="signature-actions">
                        <button type="button" class="btn btn-sm" onclick="clearTeacherSignature()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                    <input type="hidden" id="teacher_signature" name="teacher_signature">
                </div>
                <div class="col-md-6">
                    <div class="signature-label">Academic/Supervisor's Signature:</div>
                    <div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                        <p class="text-muted mb-0">To be signed by supervisor</p>
                    </div>
                    <input type="hidden" id="supervisor_signature" name="supervisor_signature">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="lesson_date_hidden" value="${date}">
        
        <button class="btn btn-block mt-3" onclick="saveLessonPlan()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
            <i class="bi bi-save"></i> Save Changes
        </button>
    `;
    
    $('#createNewForm').html(html);
    
    // Initialize signature pad after a short delay to ensure canvas is rendered
    setTimeout(function() {
        initializeSignaturePad();
    }, 100);
}

function saveLessonPlan() {
    // Collect lesson stages data
    const stages = [];
    $('#lessonStagesTable tr').each(function() {
        const stageName = $(this).find('td:first').text().trim();
        const time = $(this).find('.stage-time').val();
        const teaching = $(this).find('.stage-teaching').val();
        const learning = $(this).find('.stage-learning').val();
        const assessment = $(this).find('.stage-assessment').val();
        
        if (stageName) {
            stages.push({
                stage: stageName,
                time: time,
                teaching_activities: teaching,
                learning_activities: learning,
                assessment_criteria: assessment
            });
        }
    });
    
    const formData = {
        session_timetableID: currentSessionData.sessionTimetableID,
        lesson_date: $('#lesson_date_hidden').val(),
        main_competence: $('#main_competence').val(),
        specific_competence: $('#specific_competence').val(),
        main_activity: $('#main_activity').val(),
        specific_activity: $('#specific_activity').val(),
        teaching_learning_resources: $('#teaching_learning_resources').val(),
        references: $('#references').val(),
        lesson_stages: stages,
        remarks: $('#remarks').val(),
        reflection: $('#reflection').val(),
        evaluation: $('#evaluation').val(),
        teacher_signature: teacherSignaturePad && !teacherSignaturePad.isEmpty() ? teacherSignaturePad.toDataURL() : '',
        supervisor_signature: $('#supervisor_signature').val() || '',
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    $.ajax({
        url: '{{ route("teacher.store_lesson_plan") }}',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    $('#lessonPlanModal').modal('hide');
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to save lesson plan',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to save lesson plan';
            Swal.fire({
                title: 'Error!',
                text: error,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

function loadExistingLessonPlan() {
    const date = $('#existingLessonDate').val();
    if (!date) return;
    
    $.ajax({
        url: '{{ route("teacher.get_lesson_plan") }}',
        method: 'GET',
        data: {
            session_timetableID: currentSessionData.sessionTimetableID,
            date: date
        },
        success: function(response) {
            if (response.success) {
                $('#existingLessonPlanContent').html('<div class="alert alert-success">Lesson plan found! You can use this as a template.</div>');
            } else {
                $('#existingLessonPlanContent').html('<div class="alert alert-info">' + response.error + '</div>');
            }
        }
    });
}


function toggleManageFilterOptions() {
    const filterType = $('#manageFilterType').val();
    if (filterType === 'single_date') {
        $('#manageSingleDateFilter').show();
        $('#manageDateRangeFilter1').hide();
        $('#manageDateRangeFilter2').hide();
        $('#manageYearFilter').hide();
    } else if (filterType === 'date_range') {
        $('#manageSingleDateFilter').hide();
        $('#manageDateRangeFilter1').show();
        $('#manageDateRangeFilter2').show();
        $('#manageYearFilter').hide();
    } else {
        $('#manageSingleDateFilter').hide();
        $('#manageDateRangeFilter1').hide();
        $('#manageDateRangeFilter2').hide();
        $('#manageYearFilter').show();
    }
}

function loadLessonPlansForManage() {
    const filterType = $('#manageFilterType').val();
    let url = '{{ route("teacher.get_lesson_plans_by_filter") }}';
    let data = {
        session_timetableID: currentSessionData.sessionTimetableID,
        filter_type: filterType
    };
    
    if (filterType === 'single_date') {
        const date = $('#manageLessonDate').val();
        if (!date) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a date',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }
        data.from_date = date;
        data.to_date = date;
        data.filter_type = 'date_range';
    } else if (filterType === 'date_range') {
        const fromDate = $('#manageFromDate').val();
        const toDate = $('#manageToDate').val();
        
        if (!fromDate || !toDate) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select both from and to dates',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }
        
        if (new Date(fromDate) > new Date(toDate)) {
            Swal.fire({
                title: 'Error!',
                text: 'From date cannot be greater than To date',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }
        
        data.from_date = fromDate;
        data.to_date = toDate;
    } else {
        const year = $('#manageYear').val();
        if (!year) {
            Swal.fire({
                title: 'Error!',
                text: 'Please enter a year',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }
        data.year = year;
    }
    
    $('#manageLessonPlanContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading lesson plans...</p>
        </div>
    `);
    
    $.ajax({
        url: url,
        method: 'GET',
        data: data,
        success: function(response) {
            if (response.success && response.lesson_plans && response.lesson_plans.length > 0) {
                renderManageLessonPlansList(response.lesson_plans);
            } else {
                $('#manageLessonPlanContent').html(`
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No lesson plans found for the selected filter.
                    </div>
                `);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to load lesson plans';
            $('#manageLessonPlanContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> ${error}
                </div>
            `);
        }
    });
}

function renderManageLessonPlansList(lessonPlans) {
    let html = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Found ${lessonPlans.length} Lesson Plan(s) to Manage</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    lessonPlans.forEach(function(plan) {
        const dateObj = new Date(plan.lesson_date);
        const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const formatTime = (timeStr) => {
            if (!timeStr) return 'N/A';
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return displayHours + ':' + minutes + ' ' + ampm;
        };
        
        html += `
            <tr>
                <td>${formattedDate}</td>
                <td>${plan.subject || 'N/A'}</td>
                <td>${plan.class_name || 'N/A'}</td>
                <td>${formatTime(plan.lesson_time_start)} - ${formatTime(plan.lesson_time_end)}</td>
                <td>
                    ${plan.sent_to_admin ? 
                        '<span class="badge badge-success">Sent to Admin</span>' : 
                        '<span class="badge badge-secondary">Not Sent</span>'
                    }
                </td>
                <td>
                    <button class="btn btn-sm" onclick="loadLessonPlanForManageByID(${plan.lesson_planID})" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    $('#manageLessonPlanContent').html(html);
}

function loadLessonPlanForManageByID(lessonPlanID) {
    $.ajax({
        url: '{{ route("teacher.get_lesson_plan_by_id") }}',
        method: 'GET',
        data: { lesson_planID: lessonPlanID },
        success: function(response) {
            if (response.success) {
                // Render form for editing
                renderLessonPlanFormForEdit(response.data);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to load lesson plan',
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to load lesson plan',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
        }
    });
}

function loadLessonPlanForManage() {
    // For backward compatibility - single date selection
    const date = $('#manageLessonDate').val();
    if (!date) return;
    
    $.ajax({
        url: '{{ route("teacher.get_lesson_plan") }}',
        method: 'GET',
        data: {
            session_timetableID: currentSessionData.sessionTimetableID,
            date: date
        },
        success: function(response) {
            if (response.success) {
                // Render form for editing
                renderLessonPlanFormForEdit(response.data);
            } else {
                $('#manageLessonPlanContent').html('<div class="alert alert-info">' + response.error + '</div>');
            }
        }
    });
}

function renderLessonPlanFormForEdit(data) {
    const schoolType = '{{ $schoolType }}';
    const currentYear = {{ $currentYear }};
    
    const formatTime = (timeStr) => {
        if (!timeStr) return 'N/A';
        const parts = timeStr.split(':');
        const hours = parseInt(parts[0]);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return displayHours + ':' + minutes + ' ' + ampm;
    };
    
    const startTime = formatTime(data.lesson_time_start);
    const endTime = formatTime(data.lesson_time_end);
    const dateObj = new Date(data.lesson_date);
    const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    
    let html = `
        <div class="lesson-plan-header">LESSON PLAN</div>
        <div class="lesson-plan-header">${schoolType}</div>
        
        <table class="lesson-plan-table">
            <tr>
                <th>SUBJECT:</th>
                <td><input type="text" value="${data.subject || ''}" readonly style="background-color: #f5f5f5;"></td>
                <th>CLASS:</th>
                <td><input type="text" value="${data.class_name || ''}" readonly style="background-color: #f5f5f5;"></td>
                <th>YEAR:</th>
                <td><input type="text" value="${data.year}" readonly style="background-color: #f5f5f5;"></td>
            </tr>
            <tr>
                <th>TEACHER'S NAME</th>
                <td colspan="2"><input type="text" value="${data.teacher_name || ''}" readonly style="background-color: #f5f5f5; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                <td colspan="3">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #212529;">
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">REGISTERED</th>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.registered_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.registered_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.registered_total || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.present_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.present_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.present_total || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>TIME</th>
                <td><input type="text" value="${startTime} - ${endTime}" readonly style="background-color: #f5f5f5;"></td>
                <th>DATE</th>
                <td colspan="3"><input type="text" value="${formattedDate}" readonly style="background-color: #f5f5f5;"></td>
            </tr>
        </table>
        
        <table class="lesson-plan-table">
            <tr>
                <th>MAIN COMPETENCE</th>
                <td><textarea id="edit_main_competence" rows="2">${data.main_competence || ''}</textarea></td>
            </tr>
            <tr>
                <th>SPECIFIC COMPETENCE</th>
                <td><textarea id="edit_specific_competence" rows="2">${data.specific_competence || ''}</textarea></td>
            </tr>
            <tr>
                <th>MAIN ACTIVITY</th>
                <td><textarea id="edit_main_activity" rows="2">${data.main_activity || ''}</textarea></td>
            </tr>
            <tr>
                <th>SPECIFIC ACTIVITY</th>
                <td><textarea id="edit_specific_activity" rows="2">${data.specific_activity || ''}</textarea></td>
            </tr>
            <tr>
                <th>TEACHING & LEARNING RESOURCES</th>
                <td><textarea id="edit_teaching_learning_resources" rows="2">${data.teaching_learning_resources || ''}</textarea></td>
            </tr>
            <tr>
                <th>REFERENCES</th>
                <td><textarea id="edit_references" rows="2">${data.references || ''}</textarea></td>
            </tr>
        </table>
        
        <h5 class="mt-4 mb-3">LESSON DEVELOPMENT</h5>
        <table class="lesson-plan-table">
            <thead>
                <tr>
                    <th>STAGE</th>
                    <th>TIME</th>
                    <th>TEACHING ACTIVITIES</th>
                    <th>LEARNING ACTIVITIES</th>
                    <th>ASSESSMENT CRITERIA</th>
                </tr>
            </thead>
            <tbody id="editLessonStagesTable">
    `;
    
    const stages = data.lesson_stages || [];
    const stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];
    
    stageNames.forEach((stageName, index) => {
        const stage = stages.find(s => s.stage === stageName) || {};
        html += `
            <tr>
                <td>${stageName}</td>
                <td><input type="text" class="edit-stage-time" value="${stage.time || ''}" placeholder="e.g., 5 minutes"></td>
                <td><textarea class="edit-stage-teaching" rows="2">${stage.teaching_activities || ''}</textarea></td>
                <td><textarea class="edit-stage-learning" rows="2">${stage.learning_activities || ''}</textarea></td>
                <td><textarea class="edit-stage-assessment" rows="2">${stage.assessment_criteria || ''}</textarea></td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
        
        <div class="form-group mt-3">
            <label><strong>Remarks:</strong></label>
            <div class="dotted-line">
                <textarea id="edit_remarks" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;">${data.remarks || ''}</textarea>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Reflection:</strong></label>
            <div class="dotted-line">
                <textarea id="edit_reflection" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;">${data.reflection || ''}</textarea>
            </div>
            <div class="dotted-line">
                <textarea class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Evaluation:</strong></label>
            <div class="dotted-line">
                <textarea id="edit_evaluation" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;">${data.evaluation || ''}</textarea>
            </div>
            <div class="dotted-line">
                <textarea class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="signature-container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-label">Subject Teacher's Signature:</div>
                    <canvas id="editTeacherSignatureCanvas" class="signature-canvas" width="400" height="150"></canvas>
                    <div class="signature-actions">
                        <button type="button" class="btn btn-sm" onclick="clearEditTeacherSignature()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                    <input type="hidden" id="edit_teacher_signature" name="edit_teacher_signature" value="${data.teacher_signature || ''}">
                    ${data.teacher_signature ? `<img src="${data.teacher_signature}" class="signature-preview mt-2" style="max-width: 400px;">` : ''}
                </div>
                <div class="col-md-6">
                    <div class="signature-label">Academic/Supervisor's Signature:</div>
                    ${data.supervisor_signature ? 
                        `<img src="${data.supervisor_signature}" class="signature-preview mt-2" style="max-width: 400px;">` :
                        `<div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">To be signed by supervisor</p>
                        </div>`
                    }
                    <input type="hidden" id="edit_supervisor_signature" name="edit_supervisor_signature" value="${data.supervisor_signature || ''}">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="edit_lesson_planID" value="${data.lesson_planID}">
        <input type="hidden" id="edit_sent_to_admin" value="${data.sent_to_admin || false}">
        
        <div class="row mt-3">
            <div class="col-md-6">
                <button class="btn btn-block" onclick="updateLessonPlan()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                    <i class="bi bi-save"></i> Update Changes
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-block" onclick="sendLessonPlanToAdmin(${data.lesson_planID})" style="background-color: #940000; color: #ffffff; border: 1px solid #940000;" ${data.sent_to_admin ? 'disabled' : ''}>
                    <i class="bi bi-send"></i> ${data.sent_to_admin ? 'Sent to Admin' : 'Send to Admin'}
                </button>
            </div>
        </div>
    `;
    
    $('#manageLessonPlanContent').html(html);
    
    // Initialize signature pad for edit form
    setTimeout(function() {
        initializeEditSignaturePad(data.teacher_signature);
    }, 100);
}

let teacherSignaturePad = null;

function initializeSignaturePad() {
    const canvas = document.getElementById('teacherSignatureCanvas');
    if (canvas) {
        teacherSignaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 1,
            maxWidth: 3,
        });
        
        // Handle resize
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const width = canvas.offsetWidth;
            const height = canvas.offsetHeight;
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            if (teacherSignaturePad) {
                teacherSignaturePad.clear();
            }
        }
        
        // Initial resize
        resizeCanvas();
        
        // Handle window resize
        window.addEventListener('resize', resizeCanvas);
    }
}

function clearTeacherSignature() {
    if (teacherSignaturePad) {
        teacherSignaturePad.clear();
        $('#teacher_signature').val('');
    }
}

let editTeacherSignaturePad = null;

function initializeEditSignaturePad(existingSignature) {
    const canvas = document.getElementById('editTeacherSignatureCanvas');
    if (canvas) {
        editTeacherSignaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 1,
            maxWidth: 3,
        });
        
        // Load existing signature if available
        if (existingSignature) {
            const img = new Image();
            img.onload = function() {
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                editTeacherSignaturePad.fromDataURL(existingSignature);
            };
            img.src = existingSignature;
        }
        
        // Handle resize
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const width = canvas.offsetWidth;
            const height = canvas.offsetHeight;
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            if (editTeacherSignaturePad && existingSignature) {
                const img = new Image();
                img.onload = function() {
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                };
                img.src = existingSignature;
            }
        }
        
        // Initial resize
        resizeCanvas();
        
        // Handle window resize
        window.addEventListener('resize', resizeCanvas);
    }
}

function clearEditTeacherSignature() {
    if (editTeacherSignaturePad) {
        editTeacherSignaturePad.clear();
        $('#edit_teacher_signature').val('');
    }
}

function updateLessonPlan() {
    const lessonPlanID = $('#edit_lesson_planID').val();
    
    const stages = [];
    $('#editLessonStagesTable tr').each(function() {
        const stageName = $(this).find('td:first').text().trim();
        const time = $(this).find('.edit-stage-time').val();
        const teaching = $(this).find('.edit-stage-teaching').val();
        const learning = $(this).find('.edit-stage-learning').val();
        const assessment = $(this).find('.edit-stage-assessment').val();
        
        if (stageName) {
            stages.push({
                stage: stageName,
                time: time,
                teaching_activities: teaching,
                learning_activities: learning,
                assessment_criteria: assessment
            });
        }
    });
    
    const formData = {
        main_competence: $('#edit_main_competence').val(),
        specific_competence: $('#edit_specific_competence').val(),
        main_activity: $('#edit_main_activity').val(),
        specific_activity: $('#edit_specific_activity').val(),
        teaching_learning_resources: $('#edit_teaching_learning_resources').val(),
        references: $('#edit_references').val(),
        lesson_stages: stages,
        remarks: $('#edit_remarks').val(),
        reflection: $('#edit_reflection').val(),
        evaluation: $('#edit_evaluation').val(),
        teacher_signature: editTeacherSignaturePad && !editTeacherSignaturePad.isEmpty() ? editTeacherSignaturePad.toDataURL() : $('#edit_teacher_signature').val(),
        supervisor_signature: $('#edit_supervisor_signature').val() || '',
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    $.ajax({
        url: '{{ route("teacher.update_lesson_plan", ":id") }}'.replace(':id', lessonPlanID),
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    loadLessonPlanForManage();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to update lesson plan',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to update lesson plan';
            Swal.fire({
                title: 'Error!',
                text: error,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

function loadLessonPlanForView() {
    const date = $('#viewLessonDate').val();
    if (!date) return;
    
    $.ajax({
        url: '{{ route("teacher.get_lesson_plan") }}',
        method: 'GET',
        data: {
            session_timetableID: currentSessionData.sessionTimetableID,
            date: date
        },
        success: function(response) {
            if (response.success) {
                // Render read-only view
                renderLessonPlanView(response.data);
            } else {
                $('#viewLessonPlanContent').html('<div class="alert alert-info">' + response.error + '</div>');
            }
        }
    });
}

function renderLessonPlanView(data) {
    const schoolType = '{{ $schoolType }}';
    const currentYear = {{ $currentYear }};
    
    const formatTime = (timeStr) => {
        if (!timeStr) return 'N/A';
        const parts = timeStr.split(':');
        const hours = parseInt(parts[0]);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return displayHours + ':' + minutes + ' ' + ampm;
    };
    
    const startTime = formatTime(data.lesson_time_start);
    const endTime = formatTime(data.lesson_time_end);
    const dateObj = new Date(data.lesson_date);
    const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    
    let html = `
        <div class="lesson-plan-header">LESSON PLAN</div>
        <div class="lesson-plan-header">${schoolType}</div>
        
        <table class="lesson-plan-table">
            <tr>
                <th>SUBJECT:</th>
                <td>${data.subject || 'N/A'}</td>
                <th>CLASS:</th>
                <td>${data.class_name || 'N/A'}</td>
                <th>YEAR:</th>
                <td>${data.year}</td>
            </tr>
            <tr>
                <th>TEACHER'S NAME</th>
                <td colspan="2">${data.teacher_name || 'N/A'}</td>
                <td colspan="3">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #212529;">
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">REGISTERED</th>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.registered_girls || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.registered_boys || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.registered_total || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.present_girls || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.present_boys || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.present_total || 0}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>TIME</th>
                <td>${startTime} - ${endTime}</td>
                <th>DATE</th>
                <td colspan="3">${formattedDate}</td>
            </tr>
        </table>
        
        <table class="lesson-plan-table">
            <tr>
                <th>MAIN COMPETENCE</th>
                <td>${data.main_competence || ''}</td>
            </tr>
            <tr>
                <th>SPECIFIC COMPETENCE</th>
                <td>${data.specific_competence || ''}</td>
            </tr>
            <tr>
                <th>MAIN ACTIVITY</th>
                <td>${data.main_activity || ''}</td>
            </tr>
            <tr>
                <th>SPECIFIC ACTIVITY</th>
                <td>${data.specific_activity || ''}</td>
            </tr>
            <tr>
                <th>TEACHING & LEARNING RESOURCES</th>
                <td>${data.teaching_learning_resources || ''}</td>
            </tr>
            <tr>
                <th>REFERENCES</th>
                <td>${data.references || ''}</td>
            </tr>
        </table>
        
        <h5 class="mt-4 mb-3">LESSON DEVELOPMENT</h5>
        <table class="lesson-plan-table">
            <thead>
                <tr>
                    <th>STAGE</th>
                    <th>TIME</th>
                    <th>TEACHING ACTIVITIES</th>
                    <th>LEARNING ACTIVITIES</th>
                    <th>ASSESSMENT CRITERIA</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    const stages = data.lesson_stages || [];
    const stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];
    
    stageNames.forEach((stageName) => {
        const stage = stages.find(s => s.stage === stageName) || {};
        html += `
            <tr>
                <td>${stageName}</td>
                <td>${stage.time || ''}</td>
                <td>${stage.teaching_activities || ''}</td>
                <td>${stage.learning_activities || ''}</td>
                <td>${stage.assessment_criteria || ''}</td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
        
        <div class="form-group mt-3">
            <label><strong>Remarks:</strong></label>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;">${data.remarks || ''}</p>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Reflection:</strong></label>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;">${data.reflection || ''}</p>
            </div>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;"></p>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Evaluation:</strong></label>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;">${data.evaluation || ''}</p>
            </div>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;"></p>
            </div>
        </div>
        
        <div class="signature-container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-label">Subject Teacher's Signature:</div>
                    ${data.teacher_signature ? 
                        `<img src="${data.teacher_signature}" class="signature-preview" style="max-width: 100%; border: 2px solid #212529; border-radius: 4px;">` :
                        `<div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">No signature</p>
                        </div>`
                    }
                </div>
                <div class="col-md-6">
                    <div class="signature-label">Academic/Supervisor's Signature:</div>
                    ${data.supervisor_signature ? 
                        `<img src="${data.supervisor_signature}" class="signature-preview" style="max-width: 100%; border: 2px solid #212529; border-radius: 4px;">` :
                        `<div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">To be signed by supervisor</p>
                        </div>`
                    }
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 mb-3">
            <button class="btn btn-lg" onclick="downloadLessonPlanPDF('${data.lesson_planID || ''}', '${data.lesson_date || ''}')" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                <i class="bi bi-download"></i> Download PDF
            </button>
        </div>
    `;
    
    $('#viewLessonPlanContent').html(html);
}

function downloadLessonPlanPDF(lessonPlanID, date) {
    if (!lessonPlanID) {
        Swal.fire({
            title: 'Error!',
            text: 'Lesson plan ID is missing',
            icon: 'error',
            confirmButtonColor: '#f5f5f5'
        });
        return;
    }
    
    // Check if jsPDF is available
    if (typeof window.jspdf === 'undefined') {
        Swal.fire({
            title: 'Error!',
            text: 'PDF library not loaded. Please refresh the page.',
            icon: 'error',
            confirmButtonColor: '#f5f5f5'
        });
        return;
    }
    
    const { jsPDF } = window.jspdf;
    
    // Show loading
    Swal.fire({
        title: 'Generating PDF...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Get lesson plan data
    $.ajax({
        url: '{{ route("teacher.get_lesson_plan_by_id") }}',
        method: 'GET',
        data: { lesson_planID: lessonPlanID },
        success: function(response) {
            if (response.success) {
                generatePDFFromData(response.data);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to load lesson plan data',
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to load lesson plan data',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
        }
    });
}

function generatePDFFromData(data) {
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();
        const margin = 15;
        let yPos = margin;
        const lineHeight = 7;
        const maxWidth = pageWidth - (margin * 2);
        
        const schoolType = '{{ $schoolType }}';
        
        // Helper function to format time
        const formatTime = (timeStr) => {
            if (!timeStr) return 'N/A';
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return displayHours + ':' + minutes + ' ' + ampm;
        };
        
        const startTime = formatTime(data.lesson_time_start);
        const endTime = formatTime(data.lesson_time_end);
        const dateObj = new Date(data.lesson_date);
        const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
        
        // Helper function to add text with word wrap
        const addWrappedText = (text, x, y, maxWidth, fontSize = 10) => {
            doc.setFontSize(fontSize);
            const lines = doc.splitTextToSize(text || '', maxWidth);
            doc.text(lines, x, y);
            return lines.length * (fontSize * 0.4);
        };
        
        // Helper function to check if new page needed
        const checkNewPage = (requiredSpace) => {
            if (yPos + requiredSpace > pageHeight - margin) {
                doc.addPage();
                yPos = margin;
                return true;
            }
            return false;
        };
        
        // Header
        doc.setFontSize(18);
        doc.setFont(undefined, 'bold');
        doc.text('LESSON PLAN', pageWidth / 2, yPos, { align: 'center' });
        yPos += 8;
        
        doc.setFontSize(14);
        doc.text(schoolType, pageWidth / 2, yPos, { align: 'center' });
        yPos += 5;
        
        // Subject and Class header
        const subjectClassText = (data.subject || '') + ' CLASS ' + (data.class_name || '') + ' ' + data.year;
        doc.setFontSize(12);
        doc.text(subjectClassText, pageWidth / 2, yPos, { align: 'center' });
        yPos += 8;
        
        // First table - Teacher's Name, Period, Time, Date
        doc.setFontSize(9);
        doc.setFont(undefined, 'normal');
        const table1Y = yPos;
        doc.setDrawColor(0, 0, 0);
        doc.setLineWidth(0.5);
        
        const cellHeight = 8;
        const totalTableWidth = pageWidth - (margin * 2);
        
        // Calculate period duration from time
        let periodMinutes = 40; // Default
        if (data.lesson_time_start && data.lesson_time_end) {
            const startParts = data.lesson_time_start.split(':');
            const endParts = data.lesson_time_end.split(':');
            const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
            const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
            periodMinutes = endMinutes - startMinutes;
            if (periodMinutes < 0) periodMinutes += 24 * 60; // Handle next day
        }
        const periodText = periodMinutes + ' Minutes';
        
        // Column widths for first row: TEACHER'S NAME, PERIOD, TIME, DATE
        const col1Width = 40; // TEACHER'S NAME label
        const col2Width = 45; // Teacher name value
        const col3Width = 25; // PERIOD label
        const col4Width = 30; // Period value
        const col5Width = 20; // TIME label
        const col6Width = 30; // Time value
        const col7Width = 20; // DATE label
        const col8Width = totalTableWidth - (col1Width + col2Width + col3Width + col4Width + col5Width + col6Width + col7Width); // Date value
        
        let xPos = margin;
        yPos = table1Y;
        
        // Row 1 - Teacher's Name, Period, Time, Date
        doc.rect(xPos, yPos, col1Width, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        doc.text('TEACHER\'S NAME', xPos + 2, yPos + 5);
        xPos += col1Width;
        doc.rect(xPos, yPos, col2Width, cellHeight);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(8);
        const teacherNameLines = doc.splitTextToSize(data.teacher_name || '', col2Width - 4);
        doc.text(teacherNameLines, xPos + 2, yPos + 5);
        xPos += col2Width;
        doc.rect(xPos, yPos, col3Width, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.text('PERIOD', xPos + 2, yPos + 5);
        xPos += col3Width;
        doc.rect(xPos, yPos, col4Width, cellHeight);
        doc.setFont(undefined, 'normal');
        doc.text(periodText, xPos + 2, yPos + 5);
        xPos += col4Width;
        doc.rect(xPos, yPos, col5Width, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.text('TIME', xPos + 2, yPos + 5);
        xPos += col5Width;
        doc.rect(xPos, yPos, col6Width, cellHeight);
        doc.setFont(undefined, 'normal');
        const timeText = doc.splitTextToSize(startTime + ' - ' + endTime, col6Width - 4);
        doc.text(timeText, xPos + 2, yPos + 5);
        xPos += col6Width;
        doc.rect(xPos, yPos, col7Width, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.text('DATE', xPos + 2, yPos + 5);
        xPos += col7Width;
        doc.rect(xPos, yPos, col8Width, cellHeight);
        doc.setFont(undefined, 'normal');
        doc.text(formattedDate, xPos + 2, yPos + 5);
        yPos += cellHeight;
        
        // Row 2 - Number of Pupils table
        xPos = margin;
        const pupilsLabelWidth = col1Width;
        const pupilsTableWidth = totalTableWidth - pupilsLabelWidth;
        const pupilsTableHeight = cellHeight * 2;
        
        doc.rect(xPos, yPos, pupilsLabelWidth, pupilsTableHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        const pupilsLabelLines = doc.splitTextToSize('NUMBER OF PUPILS', pupilsLabelWidth - 4);
        doc.text(pupilsLabelLines, xPos + 2, yPos + 5);
        xPos += pupilsLabelWidth;
        
        // Attendance nested table
        doc.rect(xPos, yPos, pupilsTableWidth, pupilsTableHeight);
        
        // Attendance table header
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        doc.text('NUMBER OF PUPILS', xPos + pupilsTableWidth / 2, yPos + 4, { align: 'center' });
        yPos += cellHeight;
        
        // Attendance sub-headers
        const attColWidth = pupilsTableWidth / 6;
        let attX = xPos;
        doc.setFontSize(7);
        doc.text('REGISTERED', attX + attColWidth * 1.5, yPos + 4, { align: 'center' });
        doc.text('PRESENT', attX + attColWidth * 4.5, yPos + 4, { align: 'center' });
        yPos += cellHeight;
        
        // Attendance labels
        attX = xPos;
        doc.setFontSize(7);
        doc.text('GIRLS', attX + attColWidth * 0.5, yPos + 4, { align: 'center' });
        doc.text('BOYS', attX + attColWidth * 1.5, yPos + 4, { align: 'center' });
        doc.text('TOTAL', attX + attColWidth * 2.5, yPos + 4, { align: 'center' });
        doc.text('GIRLS', attX + attColWidth * 3.5, yPos + 4, { align: 'center' });
        doc.text('BOYS', attX + attColWidth * 4.5, yPos + 4, { align: 'center' });
        doc.text('TOTAL', attX + attColWidth * 5.5, yPos + 4, { align: 'center' });
        yPos += cellHeight;
        
        // Attendance values
        doc.setFont(undefined, 'normal');
        doc.setFontSize(8);
        doc.text((data.registered_girls || 0).toString(), attX + attColWidth * 0.5, yPos + 4, { align: 'center' });
        doc.text((data.registered_boys || 0).toString(), attX + attColWidth * 1.5, yPos + 4, { align: 'center' });
        doc.text((data.registered_total || 0).toString(), attX + attColWidth * 2.5, yPos + 4, { align: 'center' });
        doc.text((data.present_girls || 0).toString(), attX + attColWidth * 3.5, yPos + 4, { align: 'center' });
        doc.text((data.present_boys || 0).toString(), attX + attColWidth * 4.5, yPos + 4, { align: 'center' });
        doc.text((data.present_total || 0).toString(), attX + attColWidth * 5.5, yPos + 4, { align: 'center' });
        
        yPos = table1Y + cellHeight + pupilsTableHeight + 5;
        
        checkNewPage(30);
        
        // Competence and Activities table
        const compTableWidth = pageWidth - (margin * 2);
        const labelColWidth = 55; // Increased label column width
        const valueColWidth = compTableWidth - labelColWidth; // Remaining width for value
        
        const competenceFields = [
            { label: 'MAIN COMPETENCE', value: data.main_competence || '' },
            { label: 'SPECIFIC COMPETENCE', value: data.specific_competence || '' },
            { label: 'MAIN ACTIVITY', value: data.main_activity || '' },
            { label: 'SPECIFIC ACTIVITY', value: data.specific_activity || '' },
            { label: 'TEACHING MATERIAL', value: data.teaching_learning_resources || '' },
            { label: 'REFERENCES', value: data.references || '' }
        ];
        
        competenceFields.forEach(field => {
            // Calculate required height for label
            doc.setFont(undefined, 'bold');
            doc.setFontSize(8);
            const labelLines = doc.splitTextToSize(field.label, labelColWidth - 4);
            const labelHeight = Math.max(8, labelLines.length * 4);
            
            // Calculate required height for value
            doc.setFont(undefined, 'normal');
            doc.setFontSize(8);
            const valueLines = doc.splitTextToSize(field.value, valueColWidth - 4);
            const valueHeight = Math.max(8, valueLines.length * 4);
            
            // Use the maximum height
            const cellHeight = Math.max(labelHeight, valueHeight) + 2;
            
            checkNewPage(cellHeight + 3);
            
            xPos = margin;
            // Label cell
            doc.rect(xPos, yPos, labelColWidth, cellHeight);
            doc.setFont(undefined, 'bold');
            doc.setFontSize(8);
            doc.text(labelLines, xPos + 2, yPos + 4);
            
            // Value cell
            xPos += labelColWidth;
            doc.rect(xPos, yPos, valueColWidth, cellHeight);
            doc.setFont(undefined, 'normal');
            doc.setFontSize(8);
            doc.text(valueLines, xPos + 2, yPos + 4);
            
            yPos += cellHeight;
        });
        
        yPos += 5;
        checkNewPage(30);
        
        // Lesson Development section
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text('LESSON DEVELOPMENT', margin, yPos);
        yPos += 8;
        
        // Lesson Development table
        const devTableWidth = pageWidth - (margin * 2);
        // Better column width distribution - ensure all columns fit
        const devColWidths = [35, 22, 38, 38, 37]; // Total: 170mm (fits in A4 with margins)
        const devHeaders = ['STAGE', 'TIME', 'TEACHING ACTIVITIES', 'LEARNING ACTIVITIES', 'ASSESSMENT'];
        
        // Calculate header height
        doc.setFont(undefined, 'bold');
        doc.setFontSize(7);
        let maxHeaderHeight = 8;
        devHeaders.forEach((header, idx) => {
            const headerLines = doc.splitTextToSize(header, devColWidths[idx] - 4);
            maxHeaderHeight = Math.max(maxHeaderHeight, headerLines.length * 3.5);
        });
        const headerHeight = maxHeaderHeight + 2;
        
        // Table header
        xPos = margin;
        devHeaders.forEach((header, idx) => {
            doc.rect(xPos, yPos, devColWidths[idx], headerHeight);
            doc.setFont(undefined, 'bold');
            doc.setFontSize(7);
            const headerLines = doc.splitTextToSize(header, devColWidths[idx] - 4);
            doc.text(headerLines, xPos + 2, yPos + 4);
            xPos += devColWidths[idx];
        });
        yPos += headerHeight;
        
        // Table rows
        const stages = data.lesson_stages || [];
        const stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];
        
        stageNames.forEach(stageName => {
            const stage = stages.find(s => s.stage === stageName) || {};
            
            // Calculate required height for each cell
            doc.setFont(undefined, 'normal');
            doc.setFontSize(7);
            
            // Stage name height
            const stageLines = doc.splitTextToSize(stageName, devColWidths[0] - 4);
            let maxHeight = Math.max(8, stageLines.length * 3.5);
            
            // Time height
            const timeLines = doc.splitTextToSize(stage.time || '', devColWidths[1] - 4);
            maxHeight = Math.max(maxHeight, timeLines.length * 3.5);
            
            // Teaching activities height
            const teachingLines = doc.splitTextToSize(stage.teaching_activities || '', devColWidths[2] - 4);
            maxHeight = Math.max(maxHeight, teachingLines.length * 3.5);
            
            // Learning activities height
            const learningLines = doc.splitTextToSize(stage.learning_activities || '', devColWidths[3] - 4);
            maxHeight = Math.max(maxHeight, learningLines.length * 3.5);
            
            // Assessment height
            const assessmentLines = doc.splitTextToSize(stage.assessment_criteria || '', devColWidths[4] - 4);
            maxHeight = Math.max(maxHeight, assessmentLines.length * 3.5);
            
            const rowHeight = maxHeight + 2;
            
            checkNewPage(rowHeight + 3);
            
            xPos = margin;
            
            // Stage name
            doc.rect(xPos, yPos, devColWidths[0], rowHeight);
            doc.setFont(undefined, 'normal');
            doc.setFontSize(7);
            doc.text(stageLines, xPos + 2, yPos + 4);
            xPos += devColWidths[0];
            
            // Time
            doc.rect(xPos, yPos, devColWidths[1], rowHeight);
            doc.text(timeLines, xPos + 2, yPos + 4);
            xPos += devColWidths[1];
            
            // Teaching activities
            doc.rect(xPos, yPos, devColWidths[2], rowHeight);
            doc.text(teachingLines, xPos + 2, yPos + 4);
            xPos += devColWidths[2];
            
            // Learning activities
            doc.rect(xPos, yPos, devColWidths[3], rowHeight);
            doc.text(learningLines, xPos + 2, yPos + 4);
            xPos += devColWidths[3];
            
            // Assessment
            doc.rect(xPos, yPos, devColWidths[4], rowHeight);
            doc.text(assessmentLines, xPos + 2, yPos + 4);
            
            yPos += rowHeight;
        });
        
        yPos += 5;
        checkNewPage(40);
        
        // Reflection, Evaluation, Remarks (in order as per image)
        const sections = [
            { label: 'Reflection', value: data.reflection || '' },
            { label: 'Evaluation', value: data.evaluation || '' },
            { label: 'Remarks', value: data.remarks || '' }
        ];
        
        sections.forEach(section => {
            checkNewPage(20);
            doc.setFontSize(10);
            doc.setFont(undefined, 'bold');
            doc.text(section.label + ':', margin, yPos);
            yPos += 6;
            
            // Draw dotted line
            const lineLength = maxWidth;
            const dotSpacing = 2;
            let lineX = margin;
            doc.setDrawColor(0, 0, 0);
            doc.setLineWidth(0.3);
            while (lineX < margin + lineLength) {
                doc.line(lineX, yPos, lineX + 1, yPos);
                lineX += dotSpacing;
            }
            yPos += 3;
            
            // Add value if exists
            if (section.value) {
                doc.setFont(undefined, 'normal');
                doc.setFontSize(9);
                const sectionLines = doc.splitTextToSize(section.value, maxWidth);
                doc.text(sectionLines, margin, yPos);
                yPos += sectionLines.length * 4;
            }
            yPos += 3;
        });
        
        checkNewPage(50);
        
        // Signatures - Horizontal (side by side)
        yPos += 5;
        checkNewPage(25);
        
        const sigWidth = (maxWidth - 10) / 2; // Half width minus spacing
        const sigStartY = yPos;
        
        // Left side - Teacher signature
        doc.setFontSize(9);
        doc.setFont(undefined, 'normal');
        doc.text('Subject teacher\'s signature', margin, yPos);
        
        // Draw dotted line for teacher signature
        let sigLineX = margin + 45;
        const sigLineLength = sigWidth - 45;
        doc.setDrawColor(0, 0, 0);
        doc.setLineWidth(0.3);
        while (sigLineX < margin + 45 + sigLineLength) {
            doc.line(sigLineX, yPos, sigLineX + 1, yPos);
            sigLineX += 2;
        }
        
        // Add signature image if exists
        if (data.teacher_signature) {
            try {
                doc.addImage(data.teacher_signature, 'PNG', margin + 45, yPos - 8, 30, 10);
            } catch (e) {
                console.error('Error adding teacher signature:', e);
            }
        }
        
        // Right side - Supervisor signature
        const rightSigX = margin + sigWidth + 10;
        doc.setFont(undefined, 'normal');
        doc.text('Academic/Supervisor\'s signature', rightSigX, yPos);
        
        // Draw dotted line for supervisor signature
        sigLineX = rightSigX + 45;
        doc.setDrawColor(0, 0, 0);
        doc.setLineWidth(0.3);
        while (sigLineX < rightSigX + 45 + sigLineLength) {
            doc.line(sigLineX, yPos, sigLineX + 1, yPos);
            sigLineX += 2;
        }
        
        // Add signature image if exists
        if (data.supervisor_signature) {
            try {
                doc.addImage(data.supervisor_signature, 'PNG', rightSigX + 45, yPos - 8, 30, 10);
            } catch (e) {
                console.error('Error adding supervisor signature:', e);
            }
        }
        
        yPos += 8;
        
        // Generate filename
        const dateObj = new Date(data.lesson_date);
        const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const subjectName = (data.subject || 'Lesson_Plan').replace(/[^A-Za-z0-9_\-]/g, '_');
        const filename = 'Lesson_Plan_' + subjectName + '_' + formattedDate.replace(/\//g, '-') + '.pdf';
        
        // Save PDF
        doc.save(filename);
        
        Swal.close();
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to generate PDF: ' + error.message,
            icon: 'error',
            confirmButtonColor: '#f5f5f5'
        });
    }
}

function toggleFilterOptions() {
    const filterType = $('#filterType').val();
    if (filterType === 'year') {
        $('#dateRangeFilter').hide();
        $('#dateRangeFilter2').hide();
        $('#yearFilter').show();
    } else {
        $('#dateRangeFilter').show();
        $('#dateRangeFilter2').show();
        $('#yearFilter').hide();
    }
}

function loadLessonPlansByFilter() {
    const filterType = $('#filterType').val();
    let url = '{{ route("teacher.get_lesson_plans_by_filter") }}';
    let data = {
        session_timetableID: currentSessionData.sessionTimetableID,
        filter_type: filterType
    };
    
    if (filterType === 'date_range') {
        const fromDate = $('#viewFromDate').val();
        const toDate = $('#viewToDate').val();
        
        if (!fromDate || !toDate) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select both from and to dates',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }
        
        if (new Date(fromDate) > new Date(toDate)) {
            Swal.fire({
                title: 'Error!',
                text: 'From date cannot be greater than To date',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }
        
        data.from_date = fromDate;
        data.to_date = toDate;
    } else {
        const year = $('#viewYear').val();
        if (!year) {
            Swal.fire({
                title: 'Error!',
                text: 'Please enter a year',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }
        data.year = year;
    }
    
    $('#viewLessonPlanContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading lesson plans...</p>
        </div>
    `);
    
    $.ajax({
        url: url,
        method: 'GET',
        data: data,
        success: function(response) {
            if (response.success && response.lesson_plans && response.lesson_plans.length > 0) {
                renderLessonPlansList(response.lesson_plans);
            } else {
                $('#viewLessonPlanContent').html(`
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No lesson plans found for the selected filter.
                    </div>
                `);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to load lesson plans';
            $('#viewLessonPlanContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> ${error}
                </div>
            `);
        }
    });
}

function renderLessonPlansList(lessonPlans) {
    let html = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Found ${lessonPlans.length} Lesson Plan(s)</h6>
                <button class="btn btn-sm" onclick="downloadAllLessonPlansPDF()" style="background-color: #940000; color: #ffffff; border: 1px solid #940000;">
                    <i class="bi bi-download"></i> Download All as PDF
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    lessonPlans.forEach(function(plan) {
        const dateObj = new Date(plan.lesson_date);
        const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const formatTime = (timeStr) => {
            if (!timeStr) return 'N/A';
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return displayHours + ':' + minutes + ' ' + ampm;
        };
        
        html += `
            <tr>
                <td>${formattedDate}</td>
                <td>${plan.subject || 'N/A'}</td>
                <td>${plan.class_name || 'N/A'}</td>
                <td>${formatTime(plan.lesson_time_start)} - ${formatTime(plan.lesson_time_end)}</td>
                <td>
                    ${plan.sent_to_admin ? 
                        '<span class="badge badge-success">Sent to Admin</span>' : 
                        '<span class="badge badge-secondary">Not Sent</span>'
                    }
                </td>
                <td>
                    <button class="btn btn-sm" onclick="viewSingleLessonPlan(${plan.lesson_planID})" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                        <i class="bi bi-eye"></i> View
                    </button>
                    <button class="btn btn-sm" onclick="downloadLessonPlanPDF(${plan.lesson_planID}, '${plan.lesson_date}')" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                        <i class="bi bi-download"></i> PDF
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    // Store lesson plans for bulk download
    window.filteredLessonPlans = lessonPlans;
    
    $('#viewLessonPlanContent').html(html);
}

function viewSingleLessonPlan(lessonPlanID) {
    $.ajax({
        url: '{{ route("teacher.get_lesson_plan_by_id") }}',
        method: 'GET',
        data: { lesson_planID: lessonPlanID },
        success: function(response) {
            if (response.success) {
                renderLessonPlanView(response.data);
                $('#view-tab').tab('show');
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to load lesson plan',
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to load lesson plan',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
        }
    });
}

function downloadAllLessonPlansPDF() {
    if (!window.filteredLessonPlans || window.filteredLessonPlans.length === 0) {
        Swal.fire({
            title: 'Error!',
            text: 'No lesson plans to download',
            icon: 'error',
            confirmButtonColor: '#f5f5f5'
        });
        return;
    }
    
    // Check if jsPDF is available
    if (typeof window.jspdf === 'undefined') {
        Swal.fire({
            title: 'Error!',
            text: 'PDF library not loaded. Please refresh the page.',
            icon: 'error',
            confirmButtonColor: '#f5f5f5'
        });
        return;
    }
    
    const lessonPlanIDs = window.filteredLessonPlans.map(p => p.lesson_planID);
    
    // Show loading
    Swal.fire({
        title: 'Generating PDF...',
        text: `Processing ${lessonPlanIDs.length} lesson plan(s)...`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Load all lesson plans
    let loadedPlans = [];
    let loadedCount = 0;
    
    lessonPlanIDs.forEach(function(lessonPlanID, index) {
        $.ajax({
            url: '{{ route("teacher.get_lesson_plan_by_id") }}',
            method: 'GET',
            data: { lesson_planID: lessonPlanID },
            success: function(response) {
                if (response.success) {
                    loadedPlans.push(response.data);
                }
                loadedCount++;
                
                // When all loaded, generate PDF
                if (loadedCount === lessonPlanIDs.length) {
                    generateBulkPDFFromData(loadedPlans);
                }
            },
            error: function() {
                loadedCount++;
                if (loadedCount === lessonPlanIDs.length) {
                    if (loadedPlans.length > 0) {
                        generateBulkPDFFromData(loadedPlans);
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to load lesson plan data',
                            icon: 'error',
                            confirmButtonColor: '#f5f5f5'
                        });
                    }
                }
            }
        });
    });
}

function generateBulkPDFFromData(lessonPlansArray) {
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        lessonPlansArray.forEach((data, index) => {
            if (index > 0) {
                doc.addPage();
            }
            
            // Use the same generatePDFFromData function but pass doc and return yPos
            generateSingleLessonPlanPage(doc, data);
        });
        
        // Generate filename
        const filename = 'Lesson_Plans_Bulk_' + new Date().toISOString().split('T')[0] + '.pdf';
        
        // Save PDF
        doc.save(filename);
        
        Swal.close();
        
    } catch (error) {
        console.error('Error generating bulk PDF:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to generate PDF: ' + error.message,
            icon: 'error',
            confirmButtonColor: '#f5f5f5'
        });
    }
}

function generateSingleLessonPlanPage(doc, data) {
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 15;
    let yPos = margin;
    const maxWidth = pageWidth - (margin * 2);
    
    const schoolType = '{{ $schoolType }}';
    
    // Helper function to format time
    const formatTime = (timeStr) => {
        if (!timeStr) return 'N/A';
        const parts = timeStr.split(':');
        const hours = parseInt(parts[0]);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return displayHours + ':' + minutes + ' ' + ampm;
    };
    
    const startTime = formatTime(data.lesson_time_start);
    const endTime = formatTime(data.lesson_time_end);
    const dateObj = new Date(data.lesson_date);
    const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    
    // Helper function to check if new page needed
    const checkNewPage = (requiredSpace) => {
        if (yPos + requiredSpace > pageHeight - margin) {
            doc.addPage();
            yPos = margin;
            return true;
        }
        return false;
    };
    
    // Header
    doc.setFontSize(18);
    doc.setFont(undefined, 'bold');
    doc.text('LESSON PLAN', pageWidth / 2, yPos, { align: 'center' });
    yPos += 8;
    
    doc.setFontSize(14);
    doc.text(schoolType, pageWidth / 2, yPos, { align: 'center' });
    yPos += 5;
    
    // Subject and Class header
    const subjectClassText = (data.subject || '') + ' CLASS ' + (data.class_name || '') + ' ' + data.year;
    doc.setFontSize(12);
    doc.text(subjectClassText, pageWidth / 2, yPos, { align: 'center' });
    yPos += 8;
    
    // First table - Teacher's Name, Period, Time, Date
    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    const table1Y = yPos;
    doc.setDrawColor(0, 0, 0);
    doc.setLineWidth(0.5);
    
    const cellHeight = 8;
    const totalTableWidth = pageWidth - (margin * 2);
    
    // Calculate period duration from time
    let periodMinutes = 40; // Default
    if (data.lesson_time_start && data.lesson_time_end) {
        const startParts = data.lesson_time_start.split(':');
        const endParts = data.lesson_time_end.split(':');
        const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
        const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
        periodMinutes = endMinutes - startMinutes;
        if (periodMinutes < 0) periodMinutes += 24 * 60; // Handle next day
    }
    const periodText = periodMinutes + ' Minutes';
    
    // Column widths for first row: TEACHER'S NAME, PERIOD, TIME, DATE
    const col1Width = 40; // TEACHER'S NAME label
    const col2Width = 45; // Teacher name value
    const col3Width = 25; // PERIOD label
    const col4Width = 30; // Period value
    const col5Width = 20; // TIME label
    const col6Width = 30; // Time value
    const col7Width = 20; // DATE label
    const col8Width = totalTableWidth - (col1Width + col2Width + col3Width + col4Width + col5Width + col6Width + col7Width); // Date value
    
    let xPos = margin;
    yPos = table1Y;
    
    // Row 1 - Teacher's Name, Period, Time, Date
    doc.rect(xPos, yPos, col1Width, cellHeight);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8);
    doc.text('TEACHER\'S NAME', xPos + 2, yPos + 5);
    xPos += col1Width;
    doc.rect(xPos, yPos, col2Width, cellHeight);
    doc.setFont(undefined, 'normal');
    doc.setFontSize(8);
    const teacherNameLines = doc.splitTextToSize(data.teacher_name || '', col2Width - 4);
    doc.text(teacherNameLines, xPos + 2, yPos + 5);
    xPos += col2Width;
    doc.rect(xPos, yPos, col3Width, cellHeight);
    doc.setFont(undefined, 'bold');
    doc.text('PERIOD', xPos + 2, yPos + 5);
    xPos += col3Width;
    doc.rect(xPos, yPos, col4Width, cellHeight);
    doc.setFont(undefined, 'normal');
    doc.text(periodText, xPos + 2, yPos + 5);
    xPos += col4Width;
    doc.rect(xPos, yPos, col5Width, cellHeight);
    doc.setFont(undefined, 'bold');
    doc.text('TIME', xPos + 2, yPos + 5);
    xPos += col5Width;
    doc.rect(xPos, yPos, col6Width, cellHeight);
    doc.setFont(undefined, 'normal');
    const timeText = doc.splitTextToSize(startTime + ' - ' + endTime, col6Width - 4);
    doc.text(timeText, xPos + 2, yPos + 5);
    xPos += col6Width;
    doc.rect(xPos, yPos, col7Width, cellHeight);
    doc.setFont(undefined, 'bold');
    doc.text('DATE', xPos + 2, yPos + 5);
    xPos += col7Width;
    doc.rect(xPos, yPos, col8Width, cellHeight);
    doc.setFont(undefined, 'normal');
    doc.text(formattedDate, xPos + 2, yPos + 5);
    yPos += cellHeight;
    
    // Row 2 - Number of Pupils table
    xPos = margin;
    const pupilsLabelWidth = col1Width;
    const pupilsTableWidth = totalTableWidth - pupilsLabelWidth;
    const pupilsTableHeight = cellHeight * 2;
    
    doc.rect(xPos, yPos, pupilsLabelWidth, pupilsTableHeight);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8);
    const pupilsLabelLines = doc.splitTextToSize('NUMBER OF PUPILS', pupilsLabelWidth - 4);
    doc.text(pupilsLabelLines, xPos + 2, yPos + 5);
    xPos += pupilsLabelWidth;
    
    // Attendance nested table
    doc.rect(xPos, yPos, pupilsTableWidth, pupilsTableHeight);
    
    // Attendance table header
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8);
    doc.text('NUMBER OF PUPILS', xPos + pupilsTableWidth / 2, yPos + 4, { align: 'center' });
    yPos += cellHeight;
    
    // Attendance sub-headers
    const attColWidth = pupilsTableWidth / 6;
    let attX = xPos;
    doc.setFontSize(7);
    doc.text('REGISTERED', attX + attColWidth * 1.5, yPos + 4, { align: 'center' });
    doc.text('PRESENT', attX + attColWidth * 4.5, yPos + 4, { align: 'center' });
    yPos += cellHeight;
    
    // Attendance labels
    attX = xPos;
    doc.setFontSize(7);
    doc.text('GIRLS', attX + attColWidth * 0.5, yPos + 4, { align: 'center' });
    doc.text('BOYS', attX + attColWidth * 1.5, yPos + 4, { align: 'center' });
    doc.text('TOTAL', attX + attColWidth * 2.5, yPos + 4, { align: 'center' });
    doc.text('GIRLS', attX + attColWidth * 3.5, yPos + 4, { align: 'center' });
    doc.text('BOYS', attX + attColWidth * 4.5, yPos + 4, { align: 'center' });
    doc.text('TOTAL', attX + attColWidth * 5.5, yPos + 4, { align: 'center' });
    yPos += cellHeight;
    
    // Attendance values
    doc.setFont(undefined, 'normal');
    doc.setFontSize(8);
    doc.text((data.registered_girls || 0).toString(), attX + attColWidth * 0.5, yPos + 4, { align: 'center' });
    doc.text((data.registered_boys || 0).toString(), attX + attColWidth * 1.5, yPos + 4, { align: 'center' });
    doc.text((data.registered_total || 0).toString(), attX + attColWidth * 2.5, yPos + 4, { align: 'center' });
    doc.text((data.present_girls || 0).toString(), attX + attColWidth * 3.5, yPos + 4, { align: 'center' });
    doc.text((data.present_boys || 0).toString(), attX + attColWidth * 4.5, yPos + 4, { align: 'center' });
    doc.text((data.present_total || 0).toString(), attX + attColWidth * 5.5, yPos + 4, { align: 'center' });
    
    yPos = table1Y + cellHeight + pupilsTableHeight + 5;
    
    checkNewPage(30);
    
    // Competence and Activities table
    const compTableWidth = pageWidth - (margin * 2);
    const labelColWidth = 55;
    const valueColWidth = compTableWidth - labelColWidth;
    
    const competenceFields = [
        { label: 'MAIN COMPETENCE', value: data.main_competence || '' },
        { label: 'SPECIFIC COMPETENCE', value: data.specific_competence || '' },
        { label: 'MAIN ACTIVITY', value: data.main_activity || '' },
        { label: 'SPECIFIC ACTIVITY', value: data.specific_activity || '' },
        { label: 'TEACHING MATERIAL', value: data.teaching_learning_resources || '' },
        { label: 'REFERENCES', value: data.references || '' }
    ];
    
    competenceFields.forEach(field => {
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        const labelLines = doc.splitTextToSize(field.label, labelColWidth - 4);
        const labelHeight = Math.max(8, labelLines.length * 4);
        
        doc.setFont(undefined, 'normal');
        doc.setFontSize(8);
        const valueLines = doc.splitTextToSize(field.value, valueColWidth - 4);
        const valueHeight = Math.max(8, valueLines.length * 4);
        
        const cellHeight = Math.max(labelHeight, valueHeight) + 2;
        
        checkNewPage(cellHeight + 3);
        
        xPos = margin;
        doc.rect(xPos, yPos, labelColWidth, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        doc.text(labelLines, xPos + 2, yPos + 4);
        
        xPos += labelColWidth;
        doc.rect(xPos, yPos, valueColWidth, cellHeight);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(8);
        doc.text(valueLines, xPos + 2, yPos + 4);
        
        yPos += cellHeight;
    });
    
    yPos += 5;
    checkNewPage(30);
    
    // Lesson Development section
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('LESSON DEVELOPMENT', margin, yPos);
    yPos += 8;
    
    // Lesson Development table
    const devTableWidth = pageWidth - (margin * 2);
    const devColWidths = [35, 22, 38, 38, 37];
    const devHeaders = ['STAGE', 'TIME', 'TEACHING ACTIVITIES', 'LEARNING ACTIVITIES', 'ASSESSMENT'];
    
    doc.setFont(undefined, 'bold');
    doc.setFontSize(7);
    let maxHeaderHeight = 8;
    devHeaders.forEach((header, idx) => {
        const headerLines = doc.splitTextToSize(header, devColWidths[idx] - 4);
        maxHeaderHeight = Math.max(maxHeaderHeight, headerLines.length * 3.5);
    });
    const headerHeight = maxHeaderHeight + 2;
    
    xPos = margin;
    devHeaders.forEach((header, idx) => {
        doc.rect(xPos, yPos, devColWidths[idx], headerHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(7);
        const headerLines = doc.splitTextToSize(header, devColWidths[idx] - 4);
        doc.text(headerLines, xPos + 2, yPos + 4);
        xPos += devColWidths[idx];
    });
    yPos += headerHeight;
    
    const stages = data.lesson_stages || [];
    const stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];
    
    stageNames.forEach(stageName => {
        const stage = stages.find(s => s.stage === stageName) || {};
        
        doc.setFont(undefined, 'normal');
        doc.setFontSize(7);
        
        const stageLines = doc.splitTextToSize(stageName, devColWidths[0] - 4);
        let maxHeight = Math.max(8, stageLines.length * 3.5);
        
        const timeLines = doc.splitTextToSize(stage.time || '', devColWidths[1] - 4);
        maxHeight = Math.max(maxHeight, timeLines.length * 3.5);
        
        const teachingLines = doc.splitTextToSize(stage.teaching_activities || '', devColWidths[2] - 4);
        maxHeight = Math.max(maxHeight, teachingLines.length * 3.5);
        
        const learningLines = doc.splitTextToSize(stage.learning_activities || '', devColWidths[3] - 4);
        maxHeight = Math.max(maxHeight, learningLines.length * 3.5);
        
        const assessmentLines = doc.splitTextToSize(stage.assessment_criteria || '', devColWidths[4] - 4);
        maxHeight = Math.max(maxHeight, assessmentLines.length * 3.5);
        
        const rowHeight = maxHeight + 2;
        
        checkNewPage(rowHeight + 3);
        
        xPos = margin;
        
        doc.rect(xPos, yPos, devColWidths[0], rowHeight);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(7);
        doc.text(stageLines, xPos + 2, yPos + 4);
        xPos += devColWidths[0];
        
        doc.rect(xPos, yPos, devColWidths[1], rowHeight);
        doc.text(timeLines, xPos + 2, yPos + 4);
        xPos += devColWidths[1];
        
        doc.rect(xPos, yPos, devColWidths[2], rowHeight);
        doc.text(teachingLines, xPos + 2, yPos + 4);
        xPos += devColWidths[2];
        
        doc.rect(xPos, yPos, devColWidths[3], rowHeight);
        doc.text(learningLines, xPos + 2, yPos + 4);
        xPos += devColWidths[3];
        
        doc.rect(xPos, yPos, devColWidths[4], rowHeight);
        doc.text(assessmentLines, xPos + 2, yPos + 4);
        
        yPos += rowHeight;
    });
    
    yPos += 5;
    checkNewPage(40);
    
    // Reflection, Evaluation, Remarks
    const sections = [
        { label: 'Reflection', value: data.reflection || '' },
        { label: 'Evaluation', value: data.evaluation || '' },
        { label: 'Remarks', value: data.remarks || '' }
    ];
    
    sections.forEach(section => {
        checkNewPage(20);
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.text(section.label + ':', margin, yPos);
        yPos += 6;
        
        const lineLength = maxWidth;
        const dotSpacing = 2;
        let lineX = margin;
        doc.setDrawColor(0, 0, 0);
        doc.setLineWidth(0.3);
        while (lineX < margin + lineLength) {
            doc.line(lineX, yPos, lineX + 1, yPos);
            lineX += dotSpacing;
        }
        yPos += 3;
        
        if (section.value) {
            doc.setFont(undefined, 'normal');
            doc.setFontSize(9);
            const sectionLines = doc.splitTextToSize(section.value, maxWidth);
            doc.text(sectionLines, margin, yPos);
            yPos += sectionLines.length * 4;
        }
        yPos += 3;
    });
    
    checkNewPage(25);
    
    // Signatures - Horizontal (side by side)
    yPos += 5;
    
    const sigWidth = (maxWidth - 10) / 2;
    
    // Left side - Teacher signature
    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    doc.text('Subject teacher\'s signature', margin, yPos);
    
    let sigLineX = margin + 45;
    const sigLineLength = sigWidth - 45;
    doc.setDrawColor(0, 0, 0);
    doc.setLineWidth(0.3);
    while (sigLineX < margin + 45 + sigLineLength) {
        doc.line(sigLineX, yPos, sigLineX + 1, yPos);
        sigLineX += 2;
    }
    
    if (data.teacher_signature) {
        try {
            doc.addImage(data.teacher_signature, 'PNG', margin + 45, yPos - 8, 30, 10);
        } catch (e) {
            console.error('Error adding teacher signature:', e);
        }
    }
    
    // Right side - Supervisor signature
    const rightSigX = margin + sigWidth + 10;
    doc.setFont(undefined, 'normal');
    doc.text('Academic/Supervisor\'s signature', rightSigX, yPos);
    
    sigLineX = rightSigX + 45;
    doc.setDrawColor(0, 0, 0);
    doc.setLineWidth(0.3);
    while (sigLineX < rightSigX + 45 + sigLineLength) {
        doc.line(sigLineX, yPos, sigLineX + 1, yPos);
        sigLineX += 2;
    }
    
    if (data.supervisor_signature) {
        try {
            doc.addImage(data.supervisor_signature, 'PNG', rightSigX + 45, yPos - 8, 30, 10);
        } catch (e) {
            console.error('Error adding supervisor signature:', e);
        }
    }
}

function sendLessonPlanToAdmin(lessonPlanID) {
    Swal.fire({
        title: 'Send to Admin?',
        text: 'Are you sure you want to send this lesson plan to admin for review?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Send',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("teacher.send_lesson_plan_to_admin") }}',
                method: 'POST',
                data: {
                    lesson_planID: lessonPlanID,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Lesson plan sent to admin successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            loadLessonPlanForManage();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.error || 'Failed to send lesson plan',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to send lesson plan';
                    Swal.fire({
                        title: 'Error!',
                        text: error,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        }
    });
}
</script>

