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
                                <div id="existingLessonPlansList">
                                    <div class="text-center py-3">
                                        <div class="spinner-border text-primary-custom" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <p class="mt-2">Loading existing lesson plans...</p>
                                    </div>
                                </div>
                                <div id="existingLessonPlanContent" style="display: none;"></div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Make sure functions are globally accessible
let currentSubjectSessions = [];
function loadSessionsBySubject() {
    const subjectID = $('#subjectSelector').val();
    console.log('Loading sessions for subjectID:', subjectID);
    
    if (!subjectID) {
        currentSubjectSessions = [];
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
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('Sessions response:', response);
            
            if (!response) {
                $('#sessionsList').html(`
                    <div class="text-center text-danger py-5">
                        <i class="bi bi-x-circle" style="font-size: 3rem;"></i>
                        <p class="mt-3">Invalid response from server</p>
                    </div>
                `);
                return;
            }
            
            if (response.success && response.sessions && Array.isArray(response.sessions) && response.sessions.length > 0) {
                currentSubjectSessions = response.sessions.filter(s => s && s.session_timetableID);

                const firstSession = currentSubjectSessions[0];
                if (!firstSession) {
                    $('#sessionsList').html(`
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                            <p class="mt-3">No valid session found for this subject</p>
                        </div>
                    `);
                    return;
                }

                const startTime = formatTime(firstSession.start_time);
                const endTime = formatTime(firstSession.end_time);
                const subjectName = firstSession.subject_name || 'N/A';
                const className = firstSession.class_name || 'N/A';
                const subclassName = firstSession.subclass_name || '';
                const day = firstSession.day || 'N/A';

                $('#sessionsList').html(`
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle" style="font-size: 2.5rem;"></i>
                        <p class="mt-3 mb-1">Session selected automatically for this subject.</p>
                        <small>${subjectName} | ${className}${subclassName ? ' - ' + subclassName : ''} | ${day} | ${startTime} - ${endTime}</small>
                    </div>
                `);

                openLessonPlanModal(
                    firstSession.session_timetableID,
                    day,
                    firstSession.start_time,
                    firstSession.end_time,
                    subjectName.replace(/'/g, "\\'"),
                    className.replace(/'/g, "\\'")
                );
            } else {
                const errorMsg = response.error || response.message || 'No session available for this subject';
                $('#sessionsList').html(`
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                        <p class="mt-3">${errorMsg}</p>
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Error loading sessions:', xhr);
            console.error('Response:', xhr.responseJSON);
            console.error('Status:', xhr.status);
            const error = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to load sessions';
            $('#sessionsList').html(`
                <div class="text-center text-danger py-5">
                    <i class="bi bi-x-circle" style="font-size: 3rem;"></i>
                    <p class="mt-3">${error}</p>
                    <p class="mt-2 small text-muted">Status: ${xhr.status}</p>
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

function openLessonPlanModal(sessionTimetableID, day, startTime, endTime, subjectName, className, date, autoOpenCreate) {
    currentSessionData = {
        sessionTimetableID: sessionTimetableID,
        day: day,
        startTime: startTime,
        endTime: endTime,
        subjectName: subjectName,
        className: className,
        date: date || null
    };
    
    $('#lessonPlanModal').modal('show');
    $('#create-tab').tab('show');
    
    if (autoOpenCreate && date) {
        // Auto-open create form with session details
        $('#createNewForm').show();
        $('#useExistingForm').hide();
        // Load form directly with date
        loadCreateNewFormDirectly(date);
    } else {
        $('#createNewForm').hide();
        $('#useExistingForm').hide();
    }
}

function showCreateNewForm() {
    $('#useExistingForm').hide();
    $('#createNewForm').show();
    loadCreateNewForm();
}

function showUseExistingForm() {
    $('#createNewForm').hide();
    $('#useExistingForm').show();
    $('#existingLessonPlanContent').hide();
    $('#existingLessonPlansList').show();
    
    // Load all existing lesson plans for this session
    loadAllExistingLessonPlans();
}

function loadAllExistingLessonPlans() {
    if (!currentSessionData || !currentSessionData.sessionTimetableID) {
        $('#existingLessonPlansList').html('<div class="alert alert-warning">No session selected</div>');
        return;
    }
    
    // Get lesson plans for the last 2 years to show recent ones
    const currentYear = new Date().getFullYear();
    const lastYear = currentYear - 1;
    
    $.ajax({
        url: '{{ route("teacher.get_lesson_plans_by_filter") }}',
        method: 'GET',
        data: {
            session_timetableID: currentSessionData.sessionTimetableID,
            filter_type: 'year',
            year: currentYear
        },
        success: function(response) {
            if (response.success && response.lesson_plans && response.lesson_plans.length > 0) {
                displayExistingLessonPlansList(response.lesson_plans);
            } else {
                // Try last year if current year has no plans
                $.ajax({
                    url: '{{ route("teacher.get_lesson_plans_by_filter") }}',
                    method: 'GET',
                    data: {
                        session_timetableID: currentSessionData.sessionTimetableID,
                        filter_type: 'year',
                        year: lastYear
                    },
                    success: function(response2) {
                        if (response2.success && response2.lesson_plans && response2.lesson_plans.length > 0) {
                            displayExistingLessonPlansList(response2.lesson_plans);
                        } else {
                            $('#existingLessonPlansList').html(`
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
                                    <p class="mt-2">No existing lesson plans found for this subject.</p>
                                    <p>Please create a new lesson plan.</p>
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#existingLessonPlansList').html('<div class="alert alert-danger">Failed to load lesson plans</div>');
                    }
                });
            }
        },
        error: function() {
            $('#existingLessonPlansList').html('<div class="alert alert-danger">Failed to load lesson plans</div>');
        }
    });
}

function displayExistingLessonPlansList(lessonPlans) {
    let html = '<div class="mb-3"><h6>Select a lesson plan to use:</h6></div>';
    html += '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
    html += '<table class="table table-hover table-bordered">';
    html += '<thead class="thead-light" style="position: sticky; top: 0; background: white; z-index: 10;">';
    html += '<tr>';
    html += '<th>Date</th>';
    html += '<th>Subject</th>';
    html += '<th>Class</th>';
    html += '<th>Time</th>';
    html += '<th>Action</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    lessonPlans.forEach(function(plan) {
        const planDate = new Date(plan.lesson_date);
        const formattedDate = planDate.toLocaleDateString('en-GB', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
        });
        
        const formatTime = (timeStr) => {
            if (!timeStr) return 'N/A';
            try {
                const parts = timeStr.split(':');
                const hours = parseInt(parts[0]);
                const minutes = parts[1] || '00';
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const displayHours = hours % 12 || 12;
                return displayHours + ':' + minutes + ' ' + ampm;
            } catch (e) {
                return timeStr;
            }
        };
        
        const startTime = formatTime(plan.lesson_time_start);
        const endTime = formatTime(plan.lesson_time_end);
        
        html += '<tr>';
        html += '<td>' + formattedDate + '</td>';
        html += '<td>' + (plan.subject || 'N/A') + '</td>';
        html += '<td>' + (plan.class_name || 'N/A') + '</td>';
        html += '<td>' + startTime + ' - ' + endTime + '</td>';
        html += '<td>';
        html += '<button class="btn btn-sm btn-primary-custom" onclick="useExistingLessonPlan(' + plan.lesson_planID + ')">';
        html += '<i class="bi bi-check-circle"></i> Use This';
        html += '</button>';
        html += '</td>';
        html += '</tr>';
    });
    
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    
    $('#existingLessonPlansList').html(html);
}

function useExistingLessonPlan(lessonPlanID) {
    // Load the lesson plan data
    $.ajax({
        url: '{{ route("teacher.get_lesson_plan_by_id") }}',
        method: 'GET',
        data: {
            lesson_planID: lessonPlanID
        },
        success: function(response) {
            if (response.success && response.data) {
                // Show form to select new date and time
                showDateTimePickerForExistingPlan(response.data);
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

function showDateTimePickerForExistingPlan(lessonPlan) {
    const formatTime = (timeStr) => {
        if (!timeStr) return '';
        try {
            const parts = timeStr.split(':');
            const hours = parts[0].padStart(2, '0');
            const minutes = parts[1] || '00';
            return hours + ':' + minutes;
        } catch (e) {
            return '';
        }
    };
    
    const currentDate = new Date().toISOString().split('T')[0];
    const currentStartTime = currentSessionData.startTime || formatTime(lessonPlan.lesson_time_start);
    const currentEndTime = currentSessionData.endTime || formatTime(lessonPlan.lesson_time_end);
    
    Swal.fire({
        title: 'Select New Date and Time',
        html: `
            <div class="form-group text-left">
                <label>Date:</label>
                <input type="date" id="swal-new-date" class="form-control" value="${currentDate}" required>
            </div>
            <div class="form-group text-left">
                <label>Start Time:</label>
                <input type="time" id="swal-new-start-time" class="form-control" value="${currentStartTime}" required>
            </div>
            <div class="form-group text-left">
                <label>End Time:</label>
                <input type="time" id="swal-new-end-time" class="form-control" value="${currentEndTime}" required>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        width: '500px',
        preConfirm: () => {
            const date = document.getElementById('swal-new-date').value;
            const startTime = document.getElementById('swal-new-start-time').value;
            const endTime = document.getElementById('swal-new-end-time').value;
            
            if (!date || !startTime || !endTime) {
                Swal.showValidationMessage('Please fill all fields');
                return false;
            }
            
            return {
                date: date,
                startTime: startTime,
                endTime: endTime
            };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            // Load attendance stats for the new date
            loadExistingLessonPlanWithNewDateTime(lessonPlan, result.value.date, result.value.startTime, result.value.endTime);
        }
    });
}

function loadExistingLessonPlanWithNewDateTime(lessonPlan, newDate, newStartTime, newEndTime) {
    // First get attendance stats for the new date
    $.ajax({
        url: '{{ route("teacher.get_session_attendance_stats") }}',
        method: 'GET',
        data: {
            session_timetableID: currentSessionData.sessionTimetableID,
            date: newDate
        },
        success: function(response) {
            if (response.success) {
                // Convert time format from HH:MM to HH:MM:SS if needed
                const formatTimeForDisplay = (timeStr) => {
                    if (!timeStr) return '';
                    const parts = timeStr.split(':');
                    if (parts.length === 2) {
                        return timeStr + ':00';
                    }
                    return timeStr;
                };
                
                // Merge lesson plan data with attendance stats and new date/time
                const mergedData = {
                    ...response.data,
                    // Override time with new time
                    start_time: formatTimeForDisplay(newStartTime),
                    end_time: formatTimeForDisplay(newEndTime),
                    // Use lesson plan content if available
                    main_competence: lessonPlan.main_competence || response.data.main_competence || '',
                    specific_competence: lessonPlan.specific_competence || response.data.specific_competence || '',
                    main_activity: lessonPlan.main_activity || response.data.main_activity || '',
                    specific_activity: lessonPlan.specific_activity || response.data.specific_activity || '',
                    teaching_learning_resources: lessonPlan.teaching_learning_resources || response.data.teaching_learning_resources || '',
                    references: lessonPlan.references || response.data.references || '',
                    remarks: lessonPlan.remarks || response.data.remarks || '',
                    reflection: lessonPlan.reflection || response.data.reflection || '',
                    evaluation: lessonPlan.evaluation || response.data.evaluation || '',
                    // Handle lesson stages (JSON array)
                    stages: lessonPlan.lesson_stages || response.data.lesson_stages || []
                };
                
                // Render form with merged data
                renderLessonPlanForm(mergedData, newDate);
                
                // Hide existing plans list and show form
                $('#existingLessonPlansList').hide();
                $('#existingLessonPlanContent').hide();
                $('#createNewForm').show();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to load attendance statistics',
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
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

function loadCreateNewFormDirectly(date) {
    // Load create form directly with the provided date, bypassing date picker
    if (!date) {
        console.error('Date is required for loadCreateNewFormDirectly');
        return;
    }
    
    // First check if lesson plan already exists
    $.ajax({
        url: '{{ route("teacher.check_lesson_plan_exists") }}',
        method: 'GET',
        data: {
            session_timetableID: currentSessionData.sessionTimetableID,
            date: date
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
                    date: date
                },
                success: function(response) {
                    if (response.success) {
                        renderLessonPlanForm(response.data, date);
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
                    date: date
                },
                success: function(response) {
                    if (response.success) {
                        renderLessonPlanForm(response.data, date);
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
    
    // Populate fields from attendanceData if available (for using existing lesson plan)
    if (attendanceData.main_competence) {
        $('#main_competence').val(attendanceData.main_competence);
    }
    if (attendanceData.specific_competence) {
        $('#specific_competence').val(attendanceData.specific_competence);
    }
    if (attendanceData.main_activity) {
        $('#main_activity').val(attendanceData.main_activity);
    }
    if (attendanceData.specific_activity) {
        $('#specific_activity').val(attendanceData.specific_activity);
    }
    if (attendanceData.teaching_learning_resources || attendanceData.teaching_aids) {
        $('#teaching_learning_resources').val(attendanceData.teaching_learning_resources || attendanceData.teaching_aids);
    }
    if (attendanceData.references) {
        $('#references').val(attendanceData.references);
    }
    if (attendanceData.remarks) {
        $('#remarks').val(attendanceData.remarks);
    }
    if (attendanceData.reflection) {
        $('#reflection').val(attendanceData.reflection);
    }
    if (attendanceData.evaluation) {
        $('#evaluation').val(attendanceData.evaluation);
    }
    
    // Populate lesson stages if available
    if (attendanceData.stages && Array.isArray(attendanceData.stages)) {
        attendanceData.stages.forEach(function(stage, index) {
            const rows = $('#lessonStagesTable tr');
            if (rows.length > index) {
                const row = $(rows[index]);
                // Handle different stage field names
                if (stage.time) row.find('.stage-time').val(stage.time);
                if (stage.teaching_activities || stage.teaching) {
                    row.find('.stage-teaching').val(stage.teaching_activities || stage.teaching);
                }
                if (stage.learning_activities || stage.learning) {
                    row.find('.stage-learning').val(stage.learning_activities || stage.learning);
                }
                if (stage.assessment_criteria || stage.assessment) {
                    row.find('.stage-assessment').val(stage.assessment_criteria || stage.assessment);
                }
            }
        });
    }
    
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
        session_timetableID: currentSessionData?.sessionTimetableID,
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
    
    const sessionsForManage = Array.isArray(currentSubjectSessions) && currentSubjectSessions.length > 0
        ? currentSubjectSessions
        : (currentSessionData?.sessionTimetableID ? [currentSessionData] : []);

    if (sessionsForManage.length === 0) {
        $('#manageLessonPlanContent').html(`
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Please select a subject first.
            </div>
        `);
        return;
    }

    const requests = sessionsForManage.map(session => {
        return $.ajax({
            url: url,
            method: 'GET',
            data: {
                ...data,
                session_timetableID: session.session_timetableID || session.sessionTimetableID
            }
        });
    });

    $.when.apply($, requests)
        .done(function() {
            const responses = requests.length === 1 ? [arguments[0]] : Array.from(arguments).map(arg => arg[0]);
            const allPlans = [];
            responses.forEach(response => {
                if (response && response.success && Array.isArray(response.lesson_plans)) {
                    allPlans.push(...response.lesson_plans);
                }
            });
            const uniquePlans = [];
            const seen = new Set();
            allPlans.forEach(plan => {
                if (!plan || seen.has(plan.lesson_planID)) return;
                seen.add(plan.lesson_planID);
                uniquePlans.push(plan);
            });

            if (uniquePlans.length > 0) {
                renderManageLessonPlansList(uniquePlans);
            } else {
                $('#manageLessonPlanContent').html(`
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No lesson plans found for the selected filter.
                    </div>
                `);
            }
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to load lesson plans';
            $('#manageLessonPlanContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> ${error}
                </div>
            `);
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
        
        // Calculate table width first
        const totalTableWidth = pageWidth - (margin * 2);
        const cellHeight = 8;
        
        // First table - Restructured like the image
        doc.setFontSize(9);
        doc.setFont(undefined, 'normal');
        const table1Y = yPos;
        // Set border color and width for all table borders
        doc.setDrawColor(0, 0, 0); // Black borders
        doc.setLineWidth(0.5); // Ensure borders are visible
        
        // Column widths: Left column (TEACHER'S NAME, TIME, DATE) and Right column (NUMBER OF PUPILS)
        const leftColWidth = Math.floor(totalTableWidth * 0.25); // 25% for labels
        const rightColWidth = totalTableWidth - leftColWidth; // 75% for NUMBER OF PUPILS section
        
        let xPos = margin;
        yPos = table1Y;
        
        // Row 0: SUBJECT, CLASS, YEAR (inside table as first row)
        const subjectText = 'SUBJECT: ' + (data.subject || '');
        const classText = 'CLASS: ' + (data.class_name || '');
        const yearText = 'YEAR: ' + (data.year || '');
        
        // Calculate column widths for SUBJECT, CLASS, YEAR row
        const subjectColWidth = Math.floor(totalTableWidth * 0.35);
        const classColWidth = Math.floor(totalTableWidth * 0.30);
        const yearColWidth = totalTableWidth - subjectColWidth - classColWidth;
        
        // Draw SUBJECT cell
        doc.rect(xPos, yPos, subjectColWidth, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        const subjectLines = doc.splitTextToSize(subjectText, subjectColWidth - 4);
        doc.text(subjectLines, xPos + 2, yPos + 5);
        xPos += subjectColWidth;
        
        // Draw CLASS cell
        doc.rect(xPos, yPos, classColWidth, cellHeight);
        const classLines = doc.splitTextToSize(classText, classColWidth - 4);
        doc.text(classLines, xPos + 2, yPos + 5);
        xPos += classColWidth;
        
        // Draw YEAR cell (spans remaining width including NUMBER OF PUPILS area)
        doc.rect(xPos, yPos, yearColWidth, cellHeight);
        const yearLines = doc.splitTextToSize(yearText, yearColWidth - 4);
        doc.text(yearLines, xPos + 2, yPos + 5);
        
        yPos += cellHeight;
        xPos = margin;
        
        // Row 1: TEACHER'S NAME (left, vertical span) + NUMBER OF PUPILS section (right)
        const teacherRowHeight = cellHeight * 3; // Spans 3 rows
        
        // Left: TEACHER'S NAME (vertical span)
        doc.rect(xPos, yPos, leftColWidth, teacherRowHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        const teacherLabelLines = doc.splitTextToSize('TEACHER\'S NAME', leftColWidth - 4);
        doc.text(teacherLabelLines, xPos + 2, yPos + teacherRowHeight / 2, { align: 'left', baseline: 'middle' });
        
        // Right: NUMBER OF PUPILS section with proper borders
        xPos += leftColWidth;
        const attColWidth = rightColWidth / 6;
        let attX = xPos;
        let currentY = yPos;
        
        // Row 1: NUMBER OF PUPILS header (spans all 6 columns)
        doc.rect(attX, currentY, rightColWidth, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        doc.text('NUMBER OF PUPILS', attX + rightColWidth / 2, currentY + 5, { align: 'center' });
        currentY += cellHeight;
        
        // Row 2: REGISTERED and PRESENT sub-headers
        // REGISTERED spans 3 columns
        doc.rect(attX, currentY, attColWidth * 3, cellHeight);
        doc.setFontSize(7);
        doc.text('REGISTERED', attX + (attColWidth * 1.5), currentY + 4, { align: 'center' });
        // PRESENT spans 3 columns
        doc.rect(attX + (attColWidth * 3), currentY, attColWidth * 3, cellHeight);
        doc.text('PRESENT', attX + (attColWidth * 4.5), currentY + 4, { align: 'center' });
        currentY += cellHeight;
        
        // Row 3: GIRLS, BOYS, TOTAL labels (6 separate cells)
        for (let i = 0; i < 6; i++) {
            doc.rect(attX + (attColWidth * i), currentY, attColWidth, cellHeight);
        }
        doc.setFontSize(7);
        doc.text('GIRLS', attX + attColWidth * 0.5, currentY + 4, { align: 'center' });
        doc.text('BOYS', attX + attColWidth * 1.5, currentY + 4, { align: 'center' });
        doc.text('TOTAL', attX + attColWidth * 2.5, currentY + 4, { align: 'center' });
        doc.text('GIRLS', attX + attColWidth * 3.5, currentY + 4, { align: 'center' });
        doc.text('BOYS', attX + attColWidth * 4.5, currentY + 4, { align: 'center' });
        doc.text('TOTAL', attX + attColWidth * 5.5, currentY + 4, { align: 'center' });
        currentY += cellHeight;
        
        // Row 4: Attendance values (6 separate cells)
        for (let i = 0; i < 6; i++) {
            doc.rect(attX + (attColWidth * i), currentY, attColWidth, cellHeight);
        }
        doc.setFont(undefined, 'normal');
        doc.setFontSize(8);
        doc.text((data.registered_girls || 0).toString(), attX + attColWidth * 0.5, currentY + 4, { align: 'center' });
        doc.text((data.registered_boys || 0).toString(), attX + attColWidth * 1.5, currentY + 4, { align: 'center' });
        doc.text((data.registered_total || 0).toString(), attX + attColWidth * 2.5, currentY + 4, { align: 'center' });
        doc.text((data.present_girls || 0).toString(), attX + attColWidth * 3.5, currentY + 4, { align: 'center' });
        doc.text((data.present_boys || 0).toString(), attX + attColWidth * 4.5, currentY + 4, { align: 'center' });
        doc.text((data.present_total || 0).toString(), attX + attColWidth * 5.5, currentY + 4, { align: 'center' });
        
        // Row 2: TIME (left) + value in first cell only, then attendance cells
        yPos = table1Y + cellHeight + teacherRowHeight;
        xPos = margin;
        // Left: TIME label with border
        doc.rect(xPos, yPos, leftColWidth, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        doc.text('TIME', xPos + 2, yPos + 5);
        
        // Right side - TIME value in first cell only, then empty cells for attendance
        xPos += leftColWidth;
        // Reuse attColWidth from above (already calculated in NUMBER OF PUPILS section)
        
        // First cell: TIME value
        doc.rect(xPos, yPos, attColWidth, cellHeight);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(7); // Smaller font to prevent overlap
        const timeText = doc.splitTextToSize(startTime + ' - ' + endTime, attColWidth - 4);
        doc.text(timeText, xPos + 2, yPos + 5);
        xPos += attColWidth;
        
        // Remaining 5 cells: Empty (for attendance columns)
        for (let i = 0; i < 5; i++) {
            doc.rect(xPos + (attColWidth * i), yPos, attColWidth, cellHeight);
        }
        
        // Row 3: DATE (left) + value in first cell only, then empty cells
        yPos += cellHeight;
        xPos = margin;
        // Left: DATE label with border
        doc.rect(xPos, yPos, leftColWidth, cellHeight);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(8);
        doc.text('DATE', xPos + 2, yPos + 5);
        
        // Right side - DATE value in first cell only, then empty cells
        xPos += leftColWidth;
        
        // First cell: DATE value
        doc.rect(xPos, yPos, attColWidth, cellHeight);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(8);
        doc.text(formattedDate, xPos + 2, yPos + 5);
        xPos += attColWidth;
        
        // Remaining 5 cells: Empty
        for (let i = 0; i < 5; i++) {
            doc.rect(xPos + (attColWidth * i), yPos, attColWidth, cellHeight);
        }
        
        yPos += cellHeight + 5;
        
        checkNewPage(30);
        
        // Competence and Activities table - Full width with balanced columns
        const compTableWidth = pageWidth - (margin * 2);
        // Label column should be 25-30% of total width (narrower for labels)
        const labelColWidth = Math.floor(compTableWidth * 0.28);
        // Value column takes remaining 72% (wider for content)
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
        
        // Lesson Development table - Full width with balanced columns
        const devTableWidth = pageWidth - (margin * 2);
        // Calculate proportional widths: STAGE (18%), TIME (12%), TEACHING (23%), LEARNING (23%), ASSESSMENT (24%)
        const devColWidths = [
            Math.floor(devTableWidth * 0.18), // STAGE
            Math.floor(devTableWidth * 0.12), // TIME
            Math.floor(devTableWidth * 0.23), // TEACHING ACTIVITIES
            Math.floor(devTableWidth * 0.23), // LEARNING ACTIVITIES
            devTableWidth - Math.floor(devTableWidth * 0.18) - Math.floor(devTableWidth * 0.12) - Math.floor(devTableWidth * 0.23) - Math.floor(devTableWidth * 0.23) // ASSESSMENT (remaining)
        ];
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
        
        // Generate filename (reuse formattedDate from above)
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
        session_timetableID: currentSessionData?.sessionTimetableID,
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
    
    const sessionsForView = Array.isArray(currentSubjectSessions) && currentSubjectSessions.length > 0
        ? currentSubjectSessions
        : (currentSessionData?.sessionTimetableID ? [currentSessionData] : []);

    if (sessionsForView.length === 0) {
        $('#viewLessonPlanContent').html(`
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Please select a subject first.
            </div>
        `);
        return;
    }

    const requests = sessionsForView.map(session => {
        return $.ajax({
            url: url,
            method: 'GET',
            data: {
                ...data,
                session_timetableID: session.session_timetableID || session.sessionTimetableID
            }
        });
    });

    $.when.apply($, requests)
        .done(function() {
            const responses = requests.length === 1 ? [arguments[0]] : Array.from(arguments).map(arg => arg[0]);
            const allPlans = [];
            responses.forEach(response => {
                if (response && response.success && Array.isArray(response.lesson_plans)) {
                    allPlans.push(...response.lesson_plans);
                }
            });
            const uniquePlans = [];
            const seen = new Set();
            allPlans.forEach(plan => {
                if (!plan || seen.has(plan.lesson_planID)) return;
                seen.add(plan.lesson_planID);
                uniquePlans.push(plan);
            });

            if (uniquePlans.length > 0) {
                renderLessonPlansList(uniquePlans);
            } else {
                $('#viewLessonPlanContent').html(`
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No lesson plans found for the selected filter.
                    </div>
                `);
            }
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to load lesson plans';
            $('#viewLessonPlanContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> ${error}
                </div>
            `);
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
    
    // Calculate table width first
    const totalTableWidth = pageWidth - (margin * 2);
    const cellHeight = 8;
    
    // First table - Restructured like the image
    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    const table1Y = yPos;
    // Set border color and width for all table borders
    doc.setDrawColor(0, 0, 0); // Black borders
    doc.setLineWidth(0.5); // Ensure borders are visible
    
    // Column widths: Left column (TEACHER'S NAME, TIME, DATE) and Right column (NUMBER OF PUPILS)
    const leftColWidth = Math.floor(totalTableWidth * 0.25); // 25% for labels
    const rightColWidth = totalTableWidth - leftColWidth; // 75% for NUMBER OF PUPILS section
    
    let xPos = margin;
    yPos = table1Y;
    
    // Row 0: SUBJECT, CLASS, YEAR (inside table as first row)
    const subjectText = 'SUBJECT: ' + (data.subject || '');
    const classText = 'CLASS: ' + (data.class_name || '');
    const yearText = 'YEAR: ' + (data.year || '');
    
    // Calculate column widths for SUBJECT, CLASS, YEAR row
    const subjectColWidth = Math.floor(totalTableWidth * 0.35);
    const classColWidth = Math.floor(totalTableWidth * 0.30);
    const yearColWidth = totalTableWidth - subjectColWidth - classColWidth;
    
    // Draw SUBJECT cell
    doc.rect(xPos, yPos, subjectColWidth, cellHeight);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8);
    const subjectLines = doc.splitTextToSize(subjectText, subjectColWidth - 4);
    doc.text(subjectLines, xPos + 2, yPos + 5);
    xPos += subjectColWidth;
    
    // Draw CLASS cell
    doc.rect(xPos, yPos, classColWidth, cellHeight);
    const classLines = doc.splitTextToSize(classText, classColWidth - 4);
    doc.text(classLines, xPos + 2, yPos + 5);
    xPos += classColWidth;
    
    // Draw YEAR cell (spans remaining width including NUMBER OF PUPILS area)
    doc.rect(xPos, yPos, yearColWidth, cellHeight);
    const yearLines = doc.splitTextToSize(yearText, yearColWidth - 4);
    doc.text(yearLines, xPos + 2, yPos + 5);
    
    yPos += cellHeight;
    xPos = margin;
    
    // Row 1: TEACHER'S NAME (left, vertical span) + NUMBER OF PUPILS section (right)
    const teacherRowHeight = cellHeight * 3; // Spans 3 rows
    
    // Left: TEACHER'S NAME (vertical span)
    doc.rect(xPos, yPos, leftColWidth, teacherRowHeight);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8);
    const teacherLabelLines = doc.splitTextToSize('TEACHER\'S NAME', leftColWidth - 4);
    doc.text(teacherLabelLines, xPos + 2, yPos + teacherRowHeight / 2, { align: 'left', baseline: 'middle' });
    
    // Right: NUMBER OF PUPILS section with proper borders
    xPos += leftColWidth;
    // Calculate attendance column width (used throughout the table)
    const attColWidth = rightColWidth / 6;
    let attX = xPos;
    let currentY = yPos;
    
    // Row 1: NUMBER OF PUPILS header (spans all 6 columns)
    doc.rect(attX, currentY, rightColWidth, cellHeight);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8);
    doc.text('NUMBER OF PUPILS', attX + rightColWidth / 2, currentY + 5, { align: 'center' });
    currentY += cellHeight;
    
    // Row 2: REGISTERED and PRESENT sub-headers
    // REGISTERED spans 3 columns
    doc.rect(attX, currentY, attColWidth * 3, cellHeight);
    doc.setFontSize(7);
    doc.text('REGISTERED', attX + (attColWidth * 1.5), currentY + 4, { align: 'center' });
    // PRESENT spans 3 columns
    doc.rect(attX + (attColWidth * 3), currentY, attColWidth * 3, cellHeight);
    doc.text('PRESENT', attX + (attColWidth * 4.5), currentY + 4, { align: 'center' });
    currentY += cellHeight;
    
    // Row 3: GIRLS, BOYS, TOTAL labels (6 separate cells with borders)
    for (let i = 0; i < 6; i++) {
        doc.rect(attX + (attColWidth * i), currentY, attColWidth, cellHeight);
    }
    doc.setFontSize(7);
    doc.text('GIRLS', attX + attColWidth * 0.5, currentY + 4, { align: 'center' });
    doc.text('BOYS', attX + attColWidth * 1.5, currentY + 4, { align: 'center' });
    doc.text('TOTAL', attX + attColWidth * 2.5, currentY + 4, { align: 'center' });
    doc.text('GIRLS', attX + attColWidth * 3.5, currentY + 4, { align: 'center' });
    doc.text('BOYS', attX + attColWidth * 4.5, currentY + 4, { align: 'center' });
    doc.text('TOTAL', attX + attColWidth * 5.5, currentY + 4, { align: 'center' });
    currentY += cellHeight;
    
    // Row 4: Attendance values (6 separate cells with borders)
    for (let i = 0; i < 6; i++) {
        doc.rect(attX + (attColWidth * i), currentY, attColWidth, cellHeight);
    }
    doc.setFont(undefined, 'normal');
    doc.setFontSize(8);
    doc.text((data.registered_girls || 0).toString(), attX + attColWidth * 0.5, currentY + 4, { align: 'center' });
    doc.text((data.registered_boys || 0).toString(), attX + attColWidth * 1.5, currentY + 4, { align: 'center' });
    doc.text((data.registered_total || 0).toString(), attX + attColWidth * 2.5, currentY + 4, { align: 'center' });
    doc.text((data.present_girls || 0).toString(), attX + attColWidth * 3.5, currentY + 4, { align: 'center' });
    doc.text((data.present_boys || 0).toString(), attX + attColWidth * 4.5, currentY + 4, { align: 'center' });
    doc.text((data.present_total || 0).toString(), attX + attColWidth * 5.5, currentY + 4, { align: 'center' });
    
    // Row 2: TIME (left) + value in first cell only, then attendance cells
    yPos = table1Y + cellHeight + teacherRowHeight;
    xPos = margin;
    // Left: TIME label with border
    doc.rect(xPos, yPos, leftColWidth, cellHeight);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8);
    doc.text('TIME', xPos + 2, yPos + 5);
    
        // Right side - TIME value in first cell only, then empty cells for attendance
        xPos += leftColWidth;
        // Reuse attColWidth from above (already calculated in NUMBER OF PUPILS section at line 2074)
        
        // First cell: TIME value
        doc.rect(xPos, yPos, attColWidth, cellHeight);
    doc.setFont(undefined, 'normal');
    doc.setFontSize(7); // Smaller font to prevent overlap
    const timeText = doc.splitTextToSize(startTime + ' - ' + endTime, attColWidth - 4);
    doc.text(timeText, xPos + 2, yPos + 5);
    xPos += attColWidth;
    
    // Remaining 5 cells: Empty (for attendance columns)
    for (let i = 0; i < 5; i++) {
        doc.rect(xPos + (attColWidth * i), yPos, attColWidth, cellHeight);
    }
    
    // Row 3: DATE (left) + value in first cell only, then empty cells
    yPos += cellHeight;
    xPos = margin;
    // Left: DATE label with border
    doc.rect(xPos, yPos, leftColWidth, cellHeight);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8);
    doc.text('DATE', xPos + 2, yPos + 5);
    
    // Right side - DATE value in first cell only, then empty cells
    xPos += leftColWidth;
    
    // First cell: DATE value
    doc.rect(xPos, yPos, attColWidth, cellHeight);
    doc.setFont(undefined, 'normal');
    doc.setFontSize(8);
    doc.text(formattedDate, xPos + 2, yPos + 5);
    xPos += attColWidth;
    
    // Remaining 5 cells: Empty
    for (let i = 0; i < 5; i++) {
        doc.rect(xPos + (attColWidth * i), yPos, attColWidth, cellHeight);
    }
    
    yPos += cellHeight + 5;
    
    checkNewPage(30);
    
    // Competence and Activities table - Full width with balanced columns
    const compTableWidth = pageWidth - (margin * 2);
    // Label column should be 25-30% of total width (narrower for labels)
    const labelColWidth = Math.floor(compTableWidth * 0.28);
    // Value column takes remaining 72% (wider for content)
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
    
    // Lesson Development table - Full width with balanced columns
    const devTableWidth = pageWidth - (margin * 2);
    // Calculate proportional widths: STAGE (18%), TIME (12%), TEACHING (23%), LEARNING (23%), ASSESSMENT (24%)
    const devColWidths = [
        Math.floor(devTableWidth * 0.18), // STAGE
        Math.floor(devTableWidth * 0.12), // TIME
        Math.floor(devTableWidth * 0.23), // TEACHING ACTIVITIES
        Math.floor(devTableWidth * 0.23), // LEARNING ACTIVITIES
        devTableWidth - Math.floor(devTableWidth * 0.18) - Math.floor(devTableWidth * 0.12) - Math.floor(devTableWidth * 0.23) - Math.floor(devTableWidth * 0.23) // ASSESSMENT (remaining)
    ];
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

// Auto-open lesson plan modal with session details from URL parameters
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const autoOpen = urlParams.get('auto_open');
    
    if (autoOpen === 'true') {
        const sessionTimetableID = urlParams.get('session_timetableID');
        const day = urlParams.get('day');
        const startTime = urlParams.get('start_time');
        const endTime = urlParams.get('end_time');
        const subjectName = urlParams.get('subject_name');
        const className = urlParams.get('class_name');
        const date = urlParams.get('date');
        
        if (sessionTimetableID && day && startTime && endTime && subjectName && className && date) {
            // Wait a bit for modal to be ready
            setTimeout(function() {
                openLessonPlanModal(
                    sessionTimetableID,
                    day,
                    startTime,
                    endTime,
                    subjectName,
                    className,
                    date,
                    true // autoOpenCreate
                );
                
                // Clean up URL parameters
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }, 500);
        }
    }
});
</script>

