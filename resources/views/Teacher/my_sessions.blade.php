
@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom {
        background-color: #f9eeee !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    /* Remove border-radius from divs and cards */
    div, .card, .session-card, .day-header, .time-badge, .alert, .btn {
        border-radius: 0 !important;
    }
    body, .container-fluid, .card, .session-card, .btn {
        font-family: "Century Gothic", "CenturyGothic", "AppleGothic", sans-serif;
    }
    .session-card {
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: white;
    }
    .session-card.widget-card {
        border-radius: 12px !important;
        border: 1px solid #e7e7e7;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        padding: 18px;
    }
    .session-card:hover {
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    .session-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #e9ecef !important;
        filter: grayscale(70%);
    }
    .session-card.disabled .btn-session-action {
        opacity: 0.6;
        pointer-events: none;
    }
    .session-card.active {
        border-color: #940000;
        border-width: 2px;
    }
    .day-header {
        background: #f9eeee !important;
        color: #7a1f1f;
        padding: 15px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .time-badge {
        background: #f2dede;
        color: #7a1f1f;
        padding: 5px 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .sessions-hero {
        background: linear-gradient(135deg, #fff2f2 0%, #f7dede 100%);
        border: 1px solid #e8c8c8;
        color: #7a1f1f;
    }
    .btn-session-action {
        background: white !important;
        color: #940000 !important;
        border: 1px solid #940000;
        padding: 8px 14px;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
    }
    .btn-session-action:hover {
        background: #f8f8f8 !important;
        color: #940000 !important;
        border-color: #940000;
    }
    .session-card.widget-card .btn {
        border-radius: 8px !important;
        width: 100%;
    }
    .session-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 12px;
    }
    
    /* Tabs styling */
    #attendanceTabs .nav-link {
        color: #940000;
        border-radius: 0 !important;
    }
    #attendanceTabs .nav-link:hover {
        border-color: #940000;
    }
    #attendanceTabs .nav-link.active {
        background-color: #940000 !important;
        color: white !important;
        border-color: #940000 !important;
    }
    #attendanceTabContent .tab-pane {
        border-radius: 0 !important;
    }
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .btn-session-action:focus,
    .btn-session-action:active {
        background: #940000 !important;
        color: white !important;
    }
    .btn-session-action:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body sessions-hero">
                    <h4 class="mb-0">
                        <i class="bi bi-clock-history"></i> My Sessions
                    </h4>
                    <small>View and manage your weekly teaching sessions</small>
                </div>
            </div>

            @if(isset($message))
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> {{ $message }}
                </div>
            @else
                <!-- Week Navigation -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Week of:</strong> {{ $startOfWeek->format('F d, Y') }} - {{ $startOfWeek->copy()->endOfWeek()->format('F d, Y') }}
                            </div>
                            <div>
                                <button class="btn btn-outline-primary-custom btn-sm" onclick="changeWeek(-1)">
                                    <i class="bi bi-chevron-left"></i> Previous Week
                                </button>
                                <button class="btn btn-outline-primary-custom btn-sm" onclick="changeWeek(0)">
                                    <i class="bi bi-calendar"></i> Current Week
                                </button>
                                <button class="btn btn-outline-primary-custom btn-sm" onclick="changeWeek(1)">
                                    Next Week <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sessions by Day -->
                @foreach($days as $day)
                    @php
                        $dayDate = $startOfWeek->copy()->addDays(array_search($day, $days));
                        $isHoliday = in_array($dayDate->format('Y-m-d'), $holidayDates ?? []);
                        $daySessions = $sessionsByDay[$day] ?? collect();
                    @endphp
                    
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 0 !important;">
                        <div class="day-header">
                            <i class="bi bi-calendar-day"></i> {{ $day }} - {{ $dayDate->format('M d, Y') }}
                            @php
                                $dayOfWeek = $dayDate->dayOfWeek;
                                $isWeekend = ($dayOfWeek === \Carbon\Carbon::SATURDAY || $dayOfWeek === \Carbon\Carbon::SUNDAY);
                            @endphp
                            @if($isHoliday)
                                <span class="badge badge-warning ml-2">
                                    <i class="bi bi-info-circle"></i> Holiday
                                </span>
                            @endif
                            @if($isWeekend)
                                <span class="badge badge-secondary ml-2">
                                    <i class="bi bi-calendar-x"></i> Weekend
                                </span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($daySessions->isEmpty())
                                <div class="alert alert-info" style="border-radius: 0 !important;">
                                    <i class="bi bi-info-circle"></i> No sessions scheduled for {{ $day }}
                                </div>
                            @else
                                <div class="row">
                                    @foreach($daySessions as $session)
                                        @php
                                            // Parse time strings and combine with day date
                                            // Get time as string (handles both time string and datetime object)
                                            $startTimeStr = is_string($session->start_time) 
                                                ? $session->start_time 
                                                : ($session->start_time instanceof \DateTime 
                                                    ? $session->start_time->format('H:i:s') 
                                                    : '00:00:00');
                                            $endTimeStr = is_string($session->end_time) 
                                                ? $session->end_time 
                                                : ($session->end_time instanceof \DateTime 
                                                    ? $session->end_time->format('H:i:s') 
                                                    : '00:00:00');
                                            
                                            // Ensure time format is correct (HH:MM:SS)
                                            if (strlen($startTimeStr) == 5) {
                                                $startTimeStr .= ':00'; // Add seconds if missing
                                            }
                                            if (strlen($endTimeStr) == 5) {
                                                $endTimeStr .= ':00'; // Add seconds if missing
                                            }
                                            
                                            // Combine day date with session time
                                            $sessionDateTime = $dayDate->copy()->setTimeFromTimeString($startTimeStr);
                                            $sessionEndDateTime = $dayDate->copy()->setTimeFromTimeString($endTimeStr);
                                            // Use application timezone for accurate time comparison
                                            $now = \Carbon\Carbon::now(config('app.timezone'));
                                            
                                            // Check if it's a weekend (Saturday = 6, Sunday = 0)
                                            $dayOfWeek = $dayDate->dayOfWeek;
                                            $isWeekend = ($dayOfWeek === \Carbon\Carbon::SATURDAY || $dayOfWeek === \Carbon\Carbon::SUNDAY);
                                            
                                            // Check if it's today and session day matches today
                                            $isToday = $dayDate->isToday();
                                            $todayDayName = \Carbon\Carbon::now(config('app.timezone'))->format('l'); // Monday, Tuesday, etc.
                                            $isTodaySession = $isToday && ($session->day === $todayDayName);
                                            
                                            // Session is enabled when:
                                            // - Current time >= session start time (time has arrived)
                                            // - Current time <= session end time (session hasn't ended yet)
                                            $hasReachedStartTime = $now >= $sessionDateTime;
                                            $isBeforeEndTime = $now <= $sessionEndDateTime;
                                            $isWithinSessionTime = $hasReachedStartTime && $isBeforeEndTime;
                                            
                                            // Debug: Log time comparison (commented out for production - uncomment if needed for debugging)
                                            // Only log if it's today's session to reduce log noise
                                            // if ($isTodaySession) {
                                            //     \Log::info('Session Time Check (Today)', [
                                            //         'sessionID' => $session->session_timetableID,
                                            //         'day' => $session->day,
                                            //         'dayDate' => $dayDate->format('Y-m-d'),
                                            //         'startTimeStr' => $startTimeStr,
                                            //         'endTimeStr' => $endTimeStr,
                                            //         'sessionDateTime' => $sessionDateTime->format('Y-m-d H:i:s'),
                                            //         'sessionEndDateTime' => $sessionEndDateTime->format('Y-m-d H:i:s'),
                                            //         'now' => $now->format('Y-m-d H:i:s'),
                                            //         'isToday' => $dayDate->isToday(),
                                            //         'todayDayName' => \Carbon\Carbon::now(config('app.timezone'))->format('l'),
                                            //         'sessionDay' => $session->day,
                                            //         'isTodaySession' => $isTodaySession,
                                            //         'hasReachedStartTime' => $hasReachedStartTime,
                                            //         'isBeforeEndTime' => $isBeforeEndTime,
                                            //         'isWithinTime' => $isWithinSessionTime,
                                            //         'isHoliday' => $isHoliday,
                                            //         'isWeekend' => ($dayDate->dayOfWeek === \Carbon\Carbon::SATURDAY || $dayDate->dayOfWeek === \Carbon\Carbon::SUNDAY),
                                            //         'canInteract' => !$isHoliday && !$isWeekend && $isTodaySession && $isWithinSessionTime,
                                            //         'timeDiff' => $now->diffInMinutes($sessionDateTime, false) . ' minutes'
                                            //     ]);
                                            // }
                                            
                                            // Session status indicators
                                            $isSessionTime = $now >= $sessionDateTime && $now <= $sessionEndDateTime;
                                            $isPast = $now > $sessionEndDateTime;
                                            
                                            // Session can only be interacted with if:
                                            // 1. Not a holiday
                                            // 2. Not a weekend
                                            // 3. It's today (same date as session date) - check both dayDate and session day name
                                            // 4. Current time has reached or passed session start time AND is still within session end time
                                            // Once session time ends, it should be disabled
                                            $canInteract = !$isHoliday && !$isWeekend && $isTodaySession && $isWithinSessionTime;
                                            
                                            // Check if task exists and is approved
                                            $task = \App\Models\SessionTask::where('session_timetableID', $session->session_timetableID)
                                                ->where('task_date', $dayDate->format('Y-m-d'))
                                                ->first();
                                            $hasApprovedTask = $task && $task->status === 'approved';
                                        @endphp
                                        
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="session-card widget-card {{ $isSessionTime ? 'active' : '' }}">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <span class="time-badge">
                                                            <i class="bi bi-clock"></i> 
                                                            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - 
                                                            {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                                        </span>
                                                    </div>
                                                    @if($isSessionTime)
                                                        <span class="badge badge-success">
                                                            <i class="bi bi-circle-fill"></i> Active
                                                        </span>
                                                    @elseif($isPast)
                                                        <span class="badge badge-secondary">
                                                            <i class="bi bi-check-circle"></i> Completed
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <h6 class="mb-2" style="font-weight: bold;">
                                                    <i class="bi bi-book text-primary-custom"></i> 
                                                    @php
                                                        $subjectName = 'N/A';
                                                        if($session->classSubject && $session->classSubject->subject && $session->classSubject->subject->subject_name) {
                                                            $subjectName = $session->classSubject->subject->subject_name;
                                                        } elseif($session->subject && $session->subject->subject_name) {
                                                            $subjectName = $session->subject->subject_name;
                                                        }
                                                        if($session->is_prepo) {
                                                            $subjectName .= ' (Prepo)';
                                                        }
                                                    @endphp
                                                    {{ $subjectName }}
                                                </h6>
                                                
                                                <p class="mb-2 text-muted">
                                                    <i class="bi bi-people"></i> 
                                                    {{ $session->subclass->class->class_name ?? '' }} - {{ $session->subclass->subclass_name ?? '' }}
                                                </p>
                                                
                                                <div class="session-actions">
                                                    <button 
                                                        class="btn btn-session-action btn-sm" 
                                                        onclick="collectAttendance({{ $session->session_timetableID }}, '{{ $dayDate->format('Y-m-d') }}')"
                                                    >
                                                        <i class="bi bi-clipboard-check"></i> Collect Attendance
                                                    </button>
                                                    
                                                    @php
                                                        $startTimeStr = $session->start_time ? (\Carbon\Carbon::parse($session->start_time)->format('H:i:s')) : '00:00:00';
                                                        $endTimeStr = $session->end_time ? (\Carbon\Carbon::parse($session->end_time)->format('H:i:s')) : '00:00:00';
                                                        $subjectName = 'N/A';
                                                        if($session->classSubject && $session->classSubject->subject && $session->classSubject->subject->subject_name) {
                                                            $subjectName = $session->classSubject->subject->subject_name;
                                                        } elseif($session->subject && $session->subject->subject_name) {
                                                            $subjectName = $session->subject->subject_name;
                                                        }
                                                        if($session->is_prepo) {
                                                            $subjectName .= ' (Prepo)';
                                                        }
                                                        $className = ($session->subclass->class->class_name ?? '') . ' - ' . ($session->subclass->subclass_name ?? '');
                                                    @endphp
                                                    <button 
                                                        class="btn btn-session-action btn-sm" 
                                                        onclick="assignTask({{ $session->session_timetableID }}, '{{ $dayDate->format('Y-m-d') }}', '{{ $startTimeStr }}', '{{ $endTimeStr }}')"
                                                    >
                                                        <i class="bi bi-journal-plus"></i> Assign Task
                                                    </button>
                                                    
                                                    <button 
                                                        class="btn btn-session-action btn-sm" 
                                                        onclick="openLessonPlan({{ $session->session_timetableID }}, '{{ $session->day }}', '{{ $startTimeStr }}', '{{ $endTimeStr }}', '{{ addslashes($subjectName) }}', '{{ addslashes($className) }}', '{{ $dayDate->format('Y-m-d') }}')"
                                                    >
                                                        <i class="bi bi-journal-text"></i> Lesson Plan
                                                    </button>
                                                </div>
                                                
                                                @if($task)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            Task Status: 
                                                            <span class="badge badge-{{ $task->status === 'approved' ? 'success' : ($task->status === 'rejected' ? 'danger' : 'warning') }}">
                                                                {{ ucfirst($task->status) }}
                                                            </span>
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<!-- Collect Attendance Modal -->
<div class="modal fade" id="collectAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check"></i> Attendance Management
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="attendanceModalBody">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist" id="attendanceTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="collect-tab" data-toggle="tab" href="#collect-attendance" role="tab" aria-controls="collect-attendance" aria-selected="true">
                            <i class="bi bi-clipboard-plus"></i> Collect
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="update-tab" data-toggle="tab" href="#update-attendance" role="tab" aria-controls="update-attendance" aria-selected="false">
                            <i class="bi bi-pencil-square"></i> Update
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="attendanceTabContent">
                    <!-- Collect Tab -->
                    <div class="tab-pane fade show active" id="collect-attendance" role="tabpanel" aria-labelledby="collect-tab">
                        <div id="collectTabContent">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Tab -->
                    <div class="tab-pane fade" id="update-attendance" role="tabpanel" aria-labelledby="update-tab">
                        <div id="updateTabContent">
                            <div id="updateAttendanceTableContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Task Modal -->
<div class="modal fade" id="assignTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-journal-plus"></i> Assign Task
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignTaskForm">
                    <input type="hidden" id="task_session_timetableID" name="session_timetableID">
                    <input type="hidden" id="task_task_date" name="task_date">
                    
                    <!-- Date (Auto-filled) -->
                    <div class="form-group">
                        <label for="task_date_display">Tarehe <span class="text-muted">(Auto)</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="task_date_display" 
                            readonly
                            style="background-color: #f5f5f5; cursor: not-allowed;"
                        >
                    </div>
                    
                    <!-- Time (Auto-filled) -->
                    <div class="form-group">
                        <label for="task_time_display">Muda <span class="text-muted">(Auto)</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="task_time_display" 
                            readonly
                            style="background-color: #f5f5f5; cursor: not-allowed;"
                        >
                    </div>
                    
                    <!-- Topic -->
                    <div class="form-group">
                        <label for="task_topic">Topic <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="task_topic" 
                            name="topic" 
                            required
                            placeholder="Andika topic (mada kuu)..."
                            style="border-radius: 0 !important;"
                        >
                    </div>
                    
                    <!-- Subtopic -->
                    <div class="form-group">
                        <label for="task_subtopic">Subtopic</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="task_subtopic" 
                            name="subtopic" 
                            placeholder="Andika subtopic (mada ndogo) - optional..."
                            style="border-radius: 0 !important;"
                        >
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label for="task_description">Description <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control" 
                            id="task_description" 
                            name="task_description" 
                            rows="5" 
                            required
                            placeholder="Andika maelezo zaidi kuhusu topic aliyofundisha..."
                            style="border-radius: 0 !important;"
                        ></textarea>
                        <small class="form-text text-muted">Minimum 10 characters required</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary-custom btn-block">
                            <i class="bi bi-check-circle"></i> Submit Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
// Define global functions that will use jQuery when available
let currentWeekOffset = 0;

window.changeWeek = function(offset) {
    currentWeekOffset = offset === 0 ? 0 : currentWeekOffset + offset;
    window.location.href = '{{ route("teacher.mySessions") }}?week=' + currentWeekOffset;
};

// Store current session data for modal
let currentSessionData = {};

window.collectAttendance = function(sessionTimetableID, date) {
    function executeWhenJQueryReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(executeWhenJQueryReady, 50);
            return;
        }
        
        // Store session data
        currentSessionData = {
            sessionTimetableID: sessionTimetableID,
            date: date
        };
        
    $('#collectAttendanceModal').modal('show');
        $('#collectTabContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
    
        // Reset tabs
        $('#collect-tab').tab('show');
        
        // Load collect tab
        loadCollectTab(sessionTimetableID, date);
        
        // Load update tab
        loadUpdateTab(sessionTimetableID, date);
    }
    executeWhenJQueryReady();
};

function loadCollectTab(sessionTimetableID, date) {
    $.ajax({
        url: '/teacher/get-session-students',
        method: 'GET',
        data: {
            session_timetableID: sessionTimetableID,
            attendance_date: date
        },
        success: function(response) {
            if (response.success) {
                let html = '<form id="attendanceForm">';
                html += '<input type="hidden" name="session_timetableID" value="' + sessionTimetableID + '">';
                html += '<input type="hidden" name="attendance_date" value="' + date + '">';
                html += '<input type="hidden" name="is_update" value="false">';
                html += '<table id="collectAttendanceTable" class="table table-bordered table-hover" style="width:100%">';
                html += '<thead><tr><th>Student Name</th><th>Status</th><th>Remark</th></tr></thead>';
                html += '<tbody>';
                
                // Store students count for validation
                const totalStudents = response.students.length;
                
                response.students.forEach(function(student) {
                    html += '<tr>';
                    html += '<td>' + student.name + '</td>';
                    html += '<td>';
                    html += '<select class="form-control form-control-sm attendance-status" name="attendance[' + student.studentID + '][status]" data-student-id="' + student.studentID + '" required>';
                    html += '<option value="Present"' + (student.status === 'Present' ? ' selected' : '') + '>Present</option>';
                    html += '<option value="Absent"' + (student.status === 'Absent' ? ' selected' : '') + '>Absent</option>';
                    html += '<option value="Late"' + (student.status === 'Late' ? ' selected' : '') + '>Late</option>';
                    html += '<option value="Excused"' + (student.status === 'Excused' ? ' selected' : '') + '>Excused</option>';
                    html += '</select>';
                    html += '<input type="hidden" class="student-id-input" name="attendance[' + student.studentID + '][studentID]" value="' + student.studentID + '">';
                    html += '</td>';
                    html += '<td><input type="text" class="form-control form-control-sm" name="attendance[' + student.studentID + '][remark]" value="' + (student.remark || '') + '" placeholder="Optional"></td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                html += '<button type="submit" class="btn btn-primary-custom btn-block mt-3">Save Attendance</button>';
                html += '</form>';
                
                $('#collectTabContent').html(html);
                
                // Initialize DataTable with pagination but we'll collect ALL rows on submit
                if ($.fn.DataTable.isDataTable('#collectAttendanceTable')) {
                    $('#collectAttendanceTable').DataTable().destroy();
                }
                const table = $('#collectAttendanceTable').DataTable({
                    paging: true,
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    searching: true,
                    ordering: true,
                    info: true
                });
                
                // Store table reference for later use
                window.collectAttendanceTable = table;
                
                // Handle form submission - collect ALL data including from DataTable
                $('#attendanceForm').on('submit', function(e) {
                    e.preventDefault();
                    window.saveAttendance();
                });
            } else {
                $('#collectTabContent').html('<div class="alert alert-danger">' + response.error + '</div>');
            }
        },
        error: function() {
            $('#collectTabContent').html('<div class="alert alert-danger">Failed to load students</div>');
        }
    });
}

function loadUpdateTab(sessionTimetableID, date) {
    $('#updateAttendanceTableContainer').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
    
    $.ajax({
        url: '/teacher/get-session-attendance-for-update',
        method: 'GET',
        data: {
            session_timetableID: sessionTimetableID,
            attendance_date: date
        },
        success: function(response) {
            if (response.success) {
                if (!response.data || response.data.length === 0) {
                    $('#updateAttendanceTableContainer').html('<div class="alert alert-info text-center">No attendance collected for this session on this date.</div>');
                    return;
                }
                
                let html = '<table id="updateAttendanceTable" class="table table-bordered table-hover" style="width:100%">';
                html += '<thead><tr><th>Student Name</th><th>Status</th><th>Remark</th><th>Actions</th></tr></thead>';
                html += '<tbody>';
                
                response.data.forEach(function(item) {
                    const statusClass = item.status === 'Present' ? 'success' : 
                                      item.status === 'Absent' ? 'danger' : 
                                      item.status === 'Late' ? 'warning' : 'info';
                    html += '<tr>';
                    html += '<td>' + item.student_name + '</td>';
                    html += '<td><span class="badge badge-' + statusClass + '">' + item.status + '</span></td>';
                    html += '<td>' + (item.remark || '-') + '</td>';
                    html += '<td><button class="btn btn-sm btn-primary-custom" onclick="editAttendanceRecord(' + item.attendanceID + ', ' + item.studentID + ', \'' + item.student_name + '\', \'' + item.status + '\', \'' + (item.remark || '') + '\')"><i class="bi bi-pencil"></i> Edit</button></td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#updateAttendanceTableContainer').html(html);
                
                // Initialize DataTable
                if ($.fn.DataTable.isDataTable('#updateAttendanceTable')) {
                    $('#updateAttendanceTable').DataTable().destroy();
                }
                $('#updateAttendanceTable').DataTable({
                    paging: true,
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    searching: true,
                    ordering: true,
                    info: true
                });
            } else {
                $('#updateAttendanceTableContainer').html('<div class="alert alert-danger">' + response.error + '</div>');
            }
        },
        error: function() {
            $('#updateAttendanceTableContainer').html('<div class="alert alert-danger">Failed to load attendance data</div>');
        }
    });
}

window.saveAttendance = function() {
    function executeWhenJQueryReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(executeWhenJQueryReady, 50);
            return;
        }
        
        // Collect ALL attendance data from DataTable (all rows, not just visible page)
        // DataTable stores all data in memory, so we can access all rows
        let attendanceArray = [];
        
        if (window.collectAttendanceTable) {
            // Get ALL rows from DataTable (including those on other pages)
            window.collectAttendanceTable.rows().every(function(rowIdx, tableLoop, rowLoop) {
                const node = this.node();
                const $row = $(node);
                
                const studentID = $row.find('.student-id-input').val();
                const status = $row.find('.attendance-status').val();
                const remark = $row.find('input[name*="[remark]"]').val() || '';
                
                if (studentID && status) {
                    attendanceArray.push({
                        studentID: parseInt(studentID),
                        status: status,
                        remark: remark
                    });
                }
            });
        } else {
            // Fallback: collect from form if DataTable not available
            $('#attendanceForm input.student-id-input').each(function() {
                const studentID = $(this).val();
                const $row = $(this).closest('tr');
                const status = $row.find('.attendance-status').val();
                const remark = $row.find('input[name*="[remark]"]').val() || '';
                
                if (studentID && status) {
                    attendanceArray.push({
                        studentID: parseInt(studentID),
                        status: status,
                        remark: remark
                    });
        }
    });
}

        // Validate we have data
        if (attendanceArray.length === 0) {
            Swal.fire({
                title: 'Error!',
                text: 'No attendance data found. Please ensure all students have status selected.',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            return;
        }
        
        // Prepare form data in the format Laravel expects for nested arrays
        // Laravel expects: attendance[0][studentID], attendance[0][status], etc.
        const formData = new FormData();
        formData.append('session_timetableID', currentSessionData.sessionTimetableID);
        formData.append('attendance_date', currentSessionData.date);
        formData.append('is_update', false);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Add attendance array with proper indexing
        attendanceArray.forEach(function(item, index) {
            formData.append('attendance[' + index + '][studentID]', item.studentID);
            formData.append('attendance[' + index + '][status]', item.status);
            if (item.remark) {
                formData.append('attendance[' + index + '][remark]', item.remark);
            }
        });
    
    $.ajax({
        url: '{{ route("teacher.collect_session_attendance") }}',
        method: 'POST',
        data: formData,
            processData: false,
            contentType: false,
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
                    // Reload both tabs
                    if (currentSessionData.sessionTimetableID && currentSessionData.date) {
                        loadCollectTab(currentSessionData.sessionTimetableID, currentSessionData.date);
                        loadUpdateTab(currentSessionData.sessionTimetableID, currentSessionData.date);
                    } else {
                    $('#collectAttendanceModal').modal('hide');
                    location.reload();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to save attendance',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to save attendance';
            Swal.fire({
                title: 'Error!',
                text: error,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}
    executeWhenJQueryReady();
};

window.editAttendanceRecord = function(attendanceID, studentID, studentName, currentStatus, currentRemark) {
    // Escape HTML in strings
    const escapedName = $('<div>').text(studentName).html();
    const escapedRemark = $('<div>').text(currentRemark || '').html();
    
    // Create edit form HTML
    let html = '<div class="alert alert-info mb-3">Editing attendance for: <strong>' + escapedName + '</strong></div>';
    html += '<form id="editAttendanceForm">';
    html += '<div class="form-group">';
    html += '<label>Status:</label>';
    html += '<select class="form-control" id="editStatusSelect" required>';
    html += '<option value="Present"' + (currentStatus === 'Present' ? ' selected' : '') + '>Present</option>';
    html += '<option value="Absent"' + (currentStatus === 'Absent' ? ' selected' : '') + '>Absent</option>';
    html += '<option value="Late"' + (currentStatus === 'Late' ? ' selected' : '') + '>Late</option>';
    html += '<option value="Excused"' + (currentStatus === 'Excused' ? ' selected' : '') + '>Excused</option>';
    html += '</select>';
    html += '</div>';
    
    html += '<div class="form-group">';
    html += '<label>Remark:</label>';
    html += '<input type="text" class="form-control" id="editRemarkInput" value="' + escapedRemark + '" placeholder="Optional">';
    html += '</div>';
    html += '</form>';
    
    Swal.fire({
        title: 'Edit Attendance',
        html: html,
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',
        width: '500px',
        preConfirm: () => {
            // Prepare form data in the format Laravel expects
            const formData = new FormData();
            formData.append('session_timetableID', currentSessionData.sessionTimetableID);
            formData.append('attendance_date', currentSessionData.date);
            formData.append('is_update', true);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            // Add attendance array with proper indexing (Laravel expects attendance[0][studentID], etc.)
            const status = $('#editStatusSelect').val();
            const remark = $('#editRemarkInput').val() || '';
            
            formData.append('attendance[0][studentID]', studentID);
            formData.append('attendance[0][status]', status);
            if (remark) {
                formData.append('attendance[0][remark]', remark);
            }
            
            return $.ajax({
                url: '{{ route("teacher.collect_session_attendance") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(response => {
                if (!response.success) {
                    throw new Error(response.error || 'Failed to update attendance');
                }
                return response;
            }).catch(error => {
                const errorMsg = error.responseJSON?.error || error.responseJSON?.message || error.message || 'Failed to update attendance';
                Swal.showValidationMessage(errorMsg);
                return Promise.reject(error);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
                title: 'Success!',
                text: 'Attendance updated successfully',
                icon: 'success',
                confirmButtonColor: '#940000'
            }).then(() => {
                // Reload update tab
                if (currentSessionData.sessionTimetableID && currentSessionData.date) {
                    loadUpdateTab(currentSessionData.sessionTimetableID, currentSessionData.date);
                }
            });
        }
    });
};

window.assignTask = function(sessionTimetableID, date, startTime, endTime) {
    console.log('assignTask called with:', {sessionTimetableID, date, startTime, endTime});
    
    // Wait for jQuery to be available
    function executeWhenJQueryReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            console.log('Waiting for jQuery...');
            setTimeout(executeWhenJQueryReady, 50);
            return;
        }
        
        // Now jQuery is available, execute the function
        executeAssignTask(sessionTimetableID, date, startTime, endTime);
    }
    
    function executeAssignTask(sessionTimetableID, date, startTime, endTime) {
    
    try {
        // Set hidden fields
    $('#task_session_timetableID').val(sessionTimetableID);
    $('#task_task_date').val(date);
        $('#task_topic').val('');
        $('#task_subtopic').val('');
    $('#task_description').val('');
        
        // Format and display date
        const dateObj = new Date(date + 'T00:00:00'); // Add time to ensure correct parsing
        if (isNaN(dateObj.getTime())) {
            console.error('Invalid date:', date);
            $('#task_date_display').val(date); // Fallback to original date
        } else {
            const formattedDate = dateObj.toLocaleDateString('en-GB', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            $('#task_date_display').val(formattedDate);
        }
        
        // Format and display time
        const formatTime = (timeStr) => {
            if (!timeStr || timeStr === 'undefined' || timeStr === 'null') return 'N/A';
            try {
                // Handle both "HH:mm:ss" and "HH:mm" formats
                const parts = timeStr.toString().split(':');
                const hours = parseInt(parts[0]) || 0;
                const minutes = parts[1] || '00';
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const displayHours = hours % 12 || 12;
                return displayHours + ':' + minutes + ' ' + ampm;
            } catch (e) {
                console.error('Error formatting time:', timeStr, e);
                return timeStr;
            }
        };
        
        const formattedStartTime = formatTime(startTime);
        const formattedEndTime = formatTime(endTime);
        $('#task_time_display').val(formattedStartTime + ' - ' + formattedEndTime);
        
        // Check if modal element exists
        const $modal = $('#assignTaskModal');
        if ($modal.length === 0) {
            console.error('Modal element not found');
            alert('Modal not found. Please refresh the page.');
            return;
        }
        
        // Check if Bootstrap modal function is available
        if (typeof $.fn.modal === 'undefined') {
            console.error('Bootstrap modal function not available. Waiting...');
            setTimeout(function() {
                if (typeof $.fn.modal !== 'undefined') {
                    $modal.modal('show');
                } else {
                    alert('Bootstrap is not loaded. Please refresh the page.');
                }
            }, 100);
            return;
        }
        
        // Show modal - using Bootstrap 4 syntax
        console.log('Showing modal...');
        $modal.modal('show');
    } catch (error) {
        console.error('Error in assignTask:', error);
        alert('Error opening task form: ' + error.message);
    }
    }
    
    // Start checking for jQuery
    executeWhenJQueryReady();
};

window.openLessonPlan = function(sessionTimetableID, day, startTime, endTime, subjectName, className, date) {
    // Navigate to lesson plans page with session details as URL parameters
    const params = new URLSearchParams({
        session_timetableID: sessionTimetableID,
        day: day,
        start_time: startTime,
        end_time: endTime,
        subject_name: subjectName,
        class_name: className,
        date: date,
        auto_open: 'true'
    });
    
    window.location.href = '{{ route("teacher.lessonPlans") }}?' + params.toString();
};

// Initialize jQuery-dependent code when ready
(function() {
    function initWhenReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(initWhenReady, 50);
            return;
        }
        
        jQuery(document).ready(function($) {
            // Form submit handler
$('#assignTaskForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("teacher.assign_session_task") }}',
        method: 'POST',
        data: $(this).serialize(),
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
                    $('#assignTaskModal').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to assign task',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to assign task';
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
    }
    initWhenReady();
})();
</script>

