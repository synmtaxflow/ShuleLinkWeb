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
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    .time-badge {
        background: #940000;
        color: white;
        padding: 5px 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .btn-session-action {
        background: #940000 !important;
        color: white !important;
        border: none;
        padding: 8px 20px;
        font-weight: 600;
    }
    .lesson-plan-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .lesson-plan-table td, .lesson-plan-table th {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .lesson-plan-table th {
        background-color: #940000;
        color: white;
        font-weight: bold;
    }
    .lesson-plan-table input, .lesson-plan-table textarea {
        width: 100%;
        border: none;
        padding: 5px;
    }
    .lesson-plan-header {
        text-align: center;
        font-weight: bold;
        font-size: 18px;
        margin: 20px 0;
    }
    #lessonPlanTabs .nav-link {
        color: #940000;
        border-radius: 0 !important;
    }
    #lessonPlanTabs .nav-link.active {
        background-color: #940000 !important;
        color: white !important;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-primary-custom text-white">
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
                <!-- Search Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Search Sessions</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Search by Month</label>
                                    <input type="month" class="form-control" id="searchMonth" onchange="filterByMonth()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Search by Date</label>
                                    <input type="date" class="form-control" id="searchDate" onchange="filterByDate()">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Sessions Display -->
                <div id="sessionsContainer">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">All Sessions</h5>
                            <div class="row" id="sessionsList">
                                @foreach($sessions as $session)
                                    @php
                                        $subjectName = 'N/A';
                                        if($session->classSubject && $session->classSubject->subject) {
                                            $subjectName = $session->classSubject->subject->subject_name;
                                        } elseif($session->subject) {
                                            $subjectName = $session->subject->subject_name;
                                        }
                                        if($session->is_prepo) {
                                            $subjectName .= ' (Prepo)';
                                        }
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="session-card">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="time-badge">
                                                    <i class="bi bi-clock"></i> 
                                                    {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                                </span>
                                            </div>
                                            <h6 class="mb-2" style="font-weight: bold;">
                                                <i class="bi bi-book text-primary-custom"></i> {{ $subjectName }}
                                            </h6>
                                            <p class="mb-2 text-muted">
                                                <i class="bi bi-people"></i> 
                                                {{ $session->subclass->class->class_name ?? '' }} - {{ $session->subclass->subclass_name ?? '' }}
                                            </p>
                                            <p class="mb-2 text-muted">
                                                <i class="bi bi-calendar"></i> {{ $session->day }}
                                            </p>
                                            <button 
                                                class="btn btn-session-action btn-sm btn-block" 
                                                onclick="openLessonPlanModal({{ $session->session_timetableID }}, '{{ $session->day }}', '{{ $session->start_time }}', '{{ $session->end_time }}', '{{ $subjectName }}', '{{ $session->subclass->class->class_name ?? '' }}')"
                                            >
                                                <i class="bi bi-journal-text"></i> My Lesson Plan
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
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
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-journal-text"></i> Lesson Plan Management
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
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
                                <button class="btn btn-primary-custom btn-lg mr-3" onclick="showCreateNewForm()">
                                    <i class="bi bi-file-plus"></i> Create New Lesson Plan
                                </button>
                                <button class="btn btn-outline-primary-custom btn-lg" onclick="showUseExistingForm()">
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
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="date" class="form-control" id="manageLessonDate" onchange="loadLessonPlanForManage()">
                            </div>
                            <div id="manageLessonPlanContent"></div>
                        </div>
                    </div>

                    <!-- View Tab -->
                    <div class="tab-pane fade" id="view-lesson-plan" role="tabpanel">
                        <div id="viewTabContent">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="date" class="form-control" id="viewLessonDate" onchange="loadLessonPlanForView()">
                            </div>
                            <div id="viewLessonPlanContent"></div>
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
}

function loadCreateNewForm() {
    // Show date picker first
    Swal.fire({
        title: 'Select Date for Lesson Plan',
        html: '<input type="date" id="swal-date" class="swal2-input" value="' + new Date().toISOString().split('T')[0] + '">',
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            return document.getElementById('swal-date').value;
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const sessionDate = result.value;
            
            // Get attendance stats
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
                        Swal.fire({
                            title: 'Error!',
                            text: response.error || 'Failed to load attendance statistics',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load attendance statistics',
                        icon: 'error',
                        confirmButtonColor: '#940000'
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
                <td colspan="5">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <th style="border: none; text-align: center;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: none; text-align: center;" colspan="3">REGISTERED</th>
                            <th style="border: none; text-align: center;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #ddd;">GIRLS</th>
                            <th style="border: 1px solid #ddd;">BOYS</th>
                            <th style="border: 1px solid #ddd;">TOTAL</th>
                            <th style="border: 1px solid #ddd;">GIRLS</th>
                            <th style="border: 1px solid #ddd;">BOYS</th>
                            <th style="border: 1px solid #ddd;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${attendanceData.registered_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${attendanceData.registered_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${attendanceData.registered_total || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${attendanceData.present_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${attendanceData.present_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${attendanceData.present_total || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
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
            <label>Remarks:</label>
            <textarea id="remarks" class="form-control" rows="2"></textarea>
        </div>
        
        <input type="hidden" id="lesson_date_hidden" value="${date}">
        
        <button class="btn btn-primary-custom btn-block mt-3" onclick="saveLessonPlan()">
            <i class="bi bi-save"></i> Save Changes
        </button>
    `;
    
    $('#createNewForm').html(html);
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

function filterByMonth() {
    const month = $('#searchMonth').val();
    if (!month) return;
    
    // Filter sessions by month - this would need backend support
    // For now, just show all sessions
    console.log('Filter by month:', month);
}

function filterByDate() {
    const date = $('#searchDate').val();
    if (!date) return;
    
    // Filter sessions by date - this would need backend support
    console.log('Filter by date:', date);
}

function clearFilters() {
    $('#searchMonth').val('');
    $('#searchDate').val('');
}

function loadLessonPlanForManage() {
    // Similar to loadExistingLessonPlan but for manage tab
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
                <td colspan="5">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <th style="border: none; text-align: center;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: none; text-align: center;" colspan="3">REGISTERED</th>
                            <th style="border: none; text-align: center;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #ddd;">GIRLS</th>
                            <th style="border: 1px solid #ddd;">BOYS</th>
                            <th style="border: 1px solid #ddd;">TOTAL</th>
                            <th style="border: 1px solid #ddd;">GIRLS</th>
                            <th style="border: 1px solid #ddd;">BOYS</th>
                            <th style="border: 1px solid #ddd;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${data.registered_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${data.registered_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${data.registered_total || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${data.present_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${data.present_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
                            <td style="border: 1px solid #ddd;"><input type="text" value="${data.present_total || 0}" readonly style="background-color: #f5f5f5; text-align: center;"></td>
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
            <label>Remarks:</label>
            <textarea id="edit_remarks" class="form-control" rows="2">${data.remarks || ''}</textarea>
        </div>
        
        <input type="hidden" id="edit_lesson_planID" value="${data.lesson_planID}">
        
        <button class="btn btn-primary-custom btn-block mt-3" onclick="updateLessonPlan()">
            <i class="bi bi-save"></i> Update Changes
        </button>
    `;
    
    $('#manageLessonPlanContent').html(html);
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
                <td colspan="5">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <th style="border: none; text-align: center;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: none; text-align: center;" colspan="3">REGISTERED</th>
                            <th style="border: none; text-align: center;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #ddd;">GIRLS</th>
                            <th style="border: 1px solid #ddd;">BOYS</th>
                            <th style="border: 1px solid #ddd;">TOTAL</th>
                            <th style="border: 1px solid #ddd;">GIRLS</th>
                            <th style="border: 1px solid #ddd;">BOYS</th>
                            <th style="border: 1px solid #ddd;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; text-align: center;">${data.registered_girls || 0}</td>
                            <td style="border: 1px solid #ddd; text-align: center;">${data.registered_boys || 0}</td>
                            <td style="border: 1px solid #ddd; text-align: center;">${data.registered_total || 0}</td>
                            <td style="border: 1px solid #ddd; text-align: center;">${data.present_girls || 0}</td>
                            <td style="border: 1px solid #ddd; text-align: center;">${data.present_boys || 0}</td>
                            <td style="border: 1px solid #ddd; text-align: center;">${data.present_total || 0}</td>
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
            <p>${data.remarks || ''}</p>
        </div>
    `;
    
    $('#viewLessonPlanContent').html(html);
}
</script>

