@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Color scheme for #940000 */
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    /* Increase modal width */
    #createTimetableModal .modal-dialog {
        max-width: 90%;
        width: 90%;
    }
    @media (min-width: 992px) {
        #createTimetableModal .modal-dialog {
            max-width: 95%;
            width: 95%;
        }
    }
    #subjectSupervisorModal .modal-dialog {
        max-width: 90%;
        width: 90%;
    }
    @media (min-width: 992px) {
        #subjectSupervisorModal .modal-dialog {
            max-width: 95%;
            width: 95%;
        }
    }
    .widget-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .widget-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(148, 0, 0, 0.2) !important;
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
        color: #940000;
    }
    .timetable-table {
        font-size: 0.9rem;
    }
    .timetable-table th {
        background-color: #940000;
        color: white;
        text-align: center;
        vertical-align: middle;
    }
    .timetable-table td {
        vertical-align: middle;
        text-align: center;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- jsPDF for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<!-- SheetJS for Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-primary-custom text-white rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-calendar-week"></i> Timetable Management
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Create Timetable Widget -->
            @php
                $canCreate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('timetable_create');
                $canUpdate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('timetable_update');
                $canDelete = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('timetable_delete');
            @endphp
            @if($canCreate)
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card shadow-sm widget-card h-100" id="createTimetableBtn">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bi bi-plus-circle-fill text-primary-custom" style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="card-title mb-0 fw-bold">Create Timetable</h6>
                            <small class="text-muted">Create new timetable</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- View Timetables Tabs -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="timetableTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="class-timetable-tab" data-toggle="tab" href="#class-timetable" role="tab">
                                <i class="bi bi-calendar-event"></i> Class Timetable
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="exam-timetable-tab" data-toggle="tab" href="#exam-timetable" role="tab">
                                <i class="bi bi-clipboard-check"></i> Exam Timetable
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="timetableTabsContent">
                        <!-- Class Timetable Tab -->
                        <div class="tab-pane fade show active" id="class-timetable" role="tabpanel">
                            <!-- Filters -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="view_session_subclass_select">Select Class (Subclass)</label>
                                    <select class="form-control" id="view_session_subclass_select">
                                        <option value="">Select Class...</option>
                                        @foreach($subclasses ?? [] as $subclass)
                                            @php
                                                $className = $subclass->class->class_name ?? $subclass->class_name ?? '';
                                                $subclassName = trim($subclass->subclass_name ?? '');
                                                $displayName = $subclassName ? $className . ' ' . $subclassName : $className;
                                            @endphp
                                            <option value="{{ $subclass->subclassID }}">{{ $displayName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary-custom w-100" id="loadSessionTimetableBtn">
                                        <i class="bi bi-search"></i> Load Timetable
                                    </button>
                                </div>
                            </div>

                            <!-- Timetable Display -->
                            <div id="sessionTimetableDisplay" style="display: none;">
                                <!-- Timetable Definition Info -->
                                <div class="card mb-3" id="timetableDefinitionInfo">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Timetable Definition</h6>
                                    </div>
                                    <div class="card-body" id="definitionInfoContent">
                                        <!-- Definition info will be loaded here -->
                                    </div>
                                </div>

                                <!-- Timetable Table -->
                                <div class="card">
                                    <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="bi bi-calendar-week"></i> Session Timetable</h6>
                                        <div id="timetableActions" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-success" id="exportTimetableExcelBtn" title="Export to Excel">
                                                <i class="bi bi-file-earmark-excel"></i> Excel
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" id="exportTimetablePdfBtn" title="Export to PDF">
                                                <i class="bi bi-file-earmark-pdf"></i> PDF
                                            </button>
                                            @if($canUpdate)
                                            <button type="button" class="btn btn-sm btn-warning" id="editTimetableBtn" title="Edit Timetable">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" id="shuffleSessionTimetableBtn" title="Shuffle Sessions">
                                                <i class="bi bi-shuffle"></i> Shuffle
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" id="swapSessionsBtn" title="Swap Sessions">
                                                <i class="bi bi-arrow-left-right"></i> Swap
                                            </button>
                                            @endif
                                            @if($canDelete)
                                            <button type="button" class="btn btn-sm btn-danger" id="deleteTimetableBtn" title="Delete Timetable">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered timetable-table" id="sessionTimetableTable">
                                                <thead>
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Monday</th>
                                                        <th>Tuesday</th>
                                                        <th>Wednesday</th>
                                                        <th>Thursday</th>
                                                        <th>Friday</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sessionTimetableBody">
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">Select a class to view timetable</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div id="sessionTimetableEmpty" class="text-center py-5">
                                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">Select a class to view session timetable</p>
                            </div>
                        </div>

                        <!-- Exam Timetable Tab -->
                        <div class="tab-pane fade" id="exam-timetable" role="tabpanel">
                            <!-- View Options -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="view_exam_select">Select Examination</label>
                                    <select class="form-control" id="view_exam_select">
                                        <option value="">Select Examination</option>
                                        @foreach($examinations ?? [] as $exam)
                                            <option value="{{ $exam->examID }}">{{ $exam->exam_name }} ({{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($exam->end_date)->format('M d, Y') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="view_type_select">View Type</label>
                                    <select class="form-control" id="view_type_select">
                                        <option value="class_specific">Class Specific</option>
                                        <option value="school_wide">School Wide</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="class_select_group" style="display: none;">
                                    <label for="view_class_select">Select Class</label>
                                    <select class="form-control" id="view_class_select">
                                        <option value="">Select Class</option>
                                        @foreach($classes ?? [] as $class)
                                            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary-custom w-100" id="loadTimetableBtn">
                                        <i class="bi bi-search"></i> Load Timetable
                                    </button>
                                </div>
                            </div>

                            <!-- Timetable Display -->
                            <div id="timetableDisplay">
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle"></i> Please select an examination and view type to load timetable.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Timetable Modal -->
<div class="modal fade" id="createTimetableModal" tabindex="-1" role="dialog" aria-labelledby="createTimetableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="createTimetableModalLabel">
                    <i class="bi bi-plus-circle"></i> Create Timetable
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createTimetableForm">
                <div class="modal-body">
                    <div id="timetableFormErrors" class="alert alert-danger" style="display: none;"></div>

                    <!-- Timetable Type -->
                    <div class="form-group">
                        <label>Timetable Type <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="timetable_category" id="exam_timetable_radio" value="exam" checked>
                            <label class="form-check-label" for="exam_timetable_radio">
                                Exam Timetable
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="timetable_category" id="class_timetable_radio" value="class">
                            <label class="form-check-label" for="class_timetable_radio">
                                Class Session Timetable
                            </label>
                        </div>
                    </div>

                    <div id="examTimetableForm">
                        <!-- Exam Selection -->
                        <div class="form-group">
                            <label for="examID">Examination <span class="text-danger">*</span></label>
                            <select class="form-control" id="examID" name="examID" required>
                                <option value="">Select Examination</option>
                                @foreach($examinations ?? [] as $exam)
                                    <option value="{{ $exam->examID }}">{{ $exam->exam_name }} ({{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($exam->end_date)->format('M d, Y') }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only scheduled and approved examinations are shown</small>
                        </div>

                        <!-- Timetable Type (Class Specific or School Wide) -->
                        <div class="form-group">
                            <label for="timetable_type">Timetable Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="timetable_type" name="timetable_type" required>
                                <option value="class_specific">Class Specific</option>
                                <option value="school_wide">School Wide</option>
                            </select>
                            <small class="text-muted">Class Specific: For specific class subjects | School Wide: For school-wide subjects</small>
                        </div>

                        <!-- Timetable Creation Method (for School Wide only) -->
                        <div class="form-group" id="creation_method_group" style="display: none;">
                            <label for="creation_method">Timetable Creation Method <span class="text-danger">*</span></label>
                            <select class="form-control" id="creation_method" name="creation_method">
                                <option value="custom">Custom - I will enter schedule manually</option>
                                <option value="automatic">Automatic - System generates schedule automatically</option>
                            </select>
                            <small class="text-muted">Choose how you want to create the timetable</small>
                        </div>

                        <div class="alert alert-info" id="method_info_custom" style="display:none;">
                            <i class="bi bi-pencil"></i> <strong>Custom Mode:</strong> You will manually enter subjects, dates, and times for each exam day.
                        </div>
                        <div class="alert alert-success" id="method_info_automatic" style="display:none;">
                            <i class="bi bi-magic"></i> <strong>Automatic Mode:</strong> System will automatically generate timetable based on:
                            <ul class="mb-0 mt-2">
                                <li>Available subjects</li>
                                <li>Exam duration settings</li>
                                <li>Break times</li>
                                <li>Date range (weekends excluded)</li>
                            </ul>
                        </div>

                        <!-- Class Specific Fields -->
                        <div id="class_specific_fields">
                            <!-- Subclass Selection -->
                            <div class="form-group">
                                <label for="subclassID">Class <span class="text-danger">*</span></label>
                                <select class="form-control" id="subclassID" name="subclassID">
                                    <option value="">Select Class</option>
                                    @foreach($subclasses ?? [] as $subclass)
                                        @php
                                            $className = $subclass->class->class_name ?? $subclass->class_name ?? '';
                                            $subclassName = trim($subclass->subclass_name ?? '');
                                            $displayName = $subclassName ? $className . ' ' . $subclassName : $className;
                                        @endphp
                                        <option value="{{ $subclass->subclassID }}">
                                            {{ $displayName }}
                                            @if($subclass->stream_code)
                                                ({{ $subclass->stream_code }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Subject Selection (Class Specific) -->
                            <div class="form-group">
                                <label for="class_subjectID">Subject <span class="text-danger">*</span></label>
                                <select class="form-control" id="class_subjectID" name="class_subjectID">
                                    <option value="">Select Class First</option>
                                </select>
                                <small class="text-muted">Active subjects for the selected class</small>
                            </div>

                            <!-- Exam Date -->
                            <div class="form-group">
                                <label for="exam_date">Exam Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="exam_date" name="exam_date">
                            </div>

                            <!-- Time Selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="start_time" name="start_time">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_time">End Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="end_time" name="end_time" max="12:00">
                                        <small class="text-muted">Must end by 12:00 PM (noon)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Teacher Selection -->
                            <div class="form-group">
                                <label for="teacherID">Supervising Teacher <span class="text-danger">*</span></label>
                                <select class="form-control" id="teacherID" name="teacherID">
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers ?? [] as $teacher)
                                        <option value="{{ $teacher->id }}">
                                            {{ $teacher->first_name }} {{ $teacher->last_name }}
                                            @if($teacher->employee_number)
                                                ({{ $teacher->employee_number }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- School Wide Fields -->
                        <div id="school_wide_fields" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> <strong>School Wide Timetable:</strong> Create a timetable that applies to all classes. Weekends are automatically excluded.
                            </div>

                            <!-- Exam Date Range -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_start_date">Exam Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="exam_start_date" name="exam_start_date">
                                        <small class="text-muted">First day of exams</small>
                                </div>
                                    </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_end_date">Exam End Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="exam_end_date" name="exam_end_date">
                                        <small class="text-muted">Last day of exams</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Timetable - Manual Entry -->
                            <div id="custom_timetable_section" style="display: none;">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h6 class="mb-0"><i class="bi bi-calendar-week"></i> Exam Schedule by Day (Manual Entry)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="days_timetable">
                                            <!-- Days will be added dynamically based on date range -->
                                        </div>
                                        <div class="alert alert-warning mt-3" id="days_info" style="display: none;">
                                            <i class="bi bi-info-circle"></i> Please select exam dates above to see available days.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Automatic Timetable - Configuration -->
                            <div id="automatic_timetable_section" style="display: none;">
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bi bi-gear"></i> Automatic Generation Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Exam Duration -->
                                        <div class="form-group">
                                            <label for="exam_duration">Exam Duration (minutes) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="exam_duration" name="exam_duration" 
                                                   value="120" min="30" max="300" step="15">
                                            <small class="text-muted">Duration for each exam (default: 120 minutes = 2 hours)</small>
                                        </div>

                                        <!-- Daily Start/End Time -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="daily_start_time">Daily Start Time <span class="text-danger">*</span></label>
                                                    <input type="time" class="form-control" id="daily_start_time" name="daily_start_time" value="08:00">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="daily_end_time">Daily End Time <span class="text-danger">*</span></label>
                                                    <input type="time" class="form-control" id="daily_end_time" name="daily_end_time" value="12:00">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Break Time -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="break_start_time">Break Start Time (Optional)</label>
                                                    <input type="time" class="form-control" id="break_start_time" name="break_start_time">
                                                    <small class="text-muted">Leave empty for no break</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="break_duration">Break Duration (minutes)</label>
                                                    <input type="number" class="form-control" id="break_duration" name="break_duration" 
                                                           value="15" min="5" max="60" step="5">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Max Exams Per Day -->
                                        <div class="form-group">
                                            <label for="max_exams_per_day">Maximum Exams Per Day <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="max_exams_per_day" name="max_exams_per_day" 
                                                   value="2" min="1" max="6">
                                            <small class="text-muted">How many exams can be scheduled per day</small>
                                        </div>

                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> System will automatically:
                                            <ul class="mb-0">
                                                <li>Select subjects from exam configuration</li>
                                                <li>Distribute subjects across available days</li>
                                                <li>Exclude weekends automatically</li>
                                                <li>Apply break times between exams</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hall Supervisor Assignment Method -->
                            <div class="form-group">
                                <label for="supervisor_assignment_method">Hall Supervisor Assignment <span class="text-danger">*</span></label>
                                <select class="form-control" id="supervisor_assignment_method" name="supervisor_assignment_method" required>
                                    <option value="automatic">Automatic - System assigns supervisors randomly</option>
                                    <option value="custom">Custom - I will assign supervisors manually later</option>
                                </select>
                                <small class="text-muted">Choose how to assign hall supervisors for this exam</small>
                            </div>

                            <div class="alert alert-info" id="supervisor_info_automatic">
                                <i class="bi bi-info-circle"></i> System will automatically assign teachers with "supervise_exams" permission to exam halls. SMS notifications will be sent.
                            </div>
                            <div class="alert alert-warning" id="supervisor_info_custom" style="display:none;">
                                <i class="bi bi-exclamation-triangle"></i> You will need to manually assign supervisors after creating the timetable.
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>

                    <!-- Class Session Timetable Form -->
                    <div id="classSessionTimetableForm" style="display: none;">
                        <!-- Existing Definition Display -->
                        <div id="existingDefinitionDisplay" style="display: none;">
                            <h5 class="mb-3"><i class="bi bi-calendar-check"></i> Existing Timetable Definition</h5>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div id="definitionTableContainer"></div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary-custom" id="editDefinitionBtn">
                                            <i class="bi bi-pencil"></i> Edit Definition
                                        </button>
                                        <button type="button" class="btn btn-success" id="continueWithDefinitionBtn">
                                            <i class="bi bi-arrow-right"></i> Continue to Add Class Sessions
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Timetable Definition -->
                        <div id="sessionDefinitionStep">
                            <h5 class="mb-3"><i class="bi bi-gear"></i> Step 1: Define Timetable Settings</h5>
                            
                            <!-- Session Start & End Time -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="session_start_time">Session Start Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="session_start_time" name="session_start_time" required>
                                        <small class="text-muted">When daily sessions start</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="session_end_time">Session End Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="session_end_time" name="session_end_time" required>
                                        <small class="text-muted">When daily sessions end</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Prepo (Optional) -->
                            <div class="form-group">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="has_prepo" name="has_prepo">
                                    <label class="form-check-label" for="has_prepo">
                                        Has Prepo Session
                                    </label>
                                </div>
                                <div id="prepoTimeSection" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="prepo_start_time">Prepo Start Time <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="prepo_start_time" name="prepo_start_time">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="prepo_end_time">Prepo End Time <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="prepo_end_time" name="prepo_end_time">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Break Time Count -->
                            <div class="form-group">
                                <label for="break_time_count">How many break times per day? <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="break_time_count" name="break_time_count" 
                                       min="0" max="5" value="0" required>
                                <small class="text-muted">Number of break times throughout the day</small>
                            </div>

                            <!-- Dynamic Break Times -->
                            <div id="breakTimesContainer"></div>

                            <!-- Session Types -->
                            <div class="form-group">
                                <label>Session Types <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-primary-custom" id="addSessionTypeBtn">
                                        <i class="bi bi-plus"></i> Add Session Type
                                    </button>
                                </div>
                                <div id="sessionTypesContainer">
                                    <!-- Session types will be added dynamically -->
                                </div>
                                <small class="text-muted">Define different session durations (e.g., Single 60min, Double 120min)</small>
                            </div>

                            <!-- Save Definition Button -->
                            <div class="form-group">
                                <button type="button" class="btn btn-primary-custom" id="saveDefinitionBtn">
                                    <i class="bi bi-save"></i> Save Timetable Definition
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Create Timetable for Classes -->
                        <div id="sessionTimetableStep" style="display: none;">
                            <h5 class="mb-3"><i class="bi bi-calendar-week"></i> Step 2: Create Timetable for Classes</h5>
                            <div id="classTimetableForms">
                                <!-- Class timetable forms will be added here -->
                            </div>
                            <button type="button" class="btn btn-primary-custom mt-3" id="addNewClassBtn">
                                <i class="bi bi-plus"></i> Add New Class
                            </button>
                            <button type="button" class="btn btn-success mt-3" id="saveAllTimetablesBtn" style="display: none;">
                                <i class="bi bi-save"></i> Save All Timetables
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Create Timetable
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Store subclasses with timetables globally
let subclassesWithTimetables = [];

// Load all subclasses with timetables
function loadSubclassesWithTimetables() {
    $.ajax({
        url: '/admin/get-all-subclasses-with-timetables',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.subclassIDs) {
                subclassesWithTimetables = response.subclassIDs;
                // Disable subclasses with timetables in all dropdowns
                disableSubclassesWithTimetables();
            }
        },
        error: function(xhr) {
            console.error('Error loading subclasses with timetables:', xhr);
        }
    });
}

// Disable subclasses with timetables in all subclass dropdowns
function disableSubclassesWithTimetables() {
    $('.subclass-select').each(function() {
        const select = $(this);
        subclassesWithTimetables.forEach(function(subclassID) {
            const option = select.find(`option[value="${subclassID}"]`);
            if (option.length > 0) {
                option.prop('disabled', true);
                let currentText = option.text();
                if (!currentText.includes('(Has Timetable)')) {
                    option.text(currentText + ' (Has Timetable)');
                }
            }
        });
    });
}

$(document).ready(function() {
    // Load subclasses with timetables on page load
    loadSubclassesWithTimetables();
    
    // Open create timetable modal
    $('#createTimetableBtn').on('click', function() {
        $('#createTimetableModal').modal('show');
        // Reload subclasses with timetables when modal opens
        loadSubclassesWithTimetables();
        // Ensure required attributes are set correctly based on selected category
        const category = $('input[name="timetable_category"]:checked').val();
        if (category === 'exam') {
            $('#session_start_time, #session_end_time, #prepo_start_time, #prepo_end_time').removeAttr('required').prop('disabled', true);
        } else {
            $('#session_start_time, #session_end_time').prop('disabled', false).attr('required', 'required');
            $('#prepo_start_time, #prepo_end_time').prop('disabled', false);
        }
    });

    // Load Session Timetable
    $('#loadSessionTimetableBtn').on('click', function() {
        const subclassID = $('#view_session_subclass_select').val();
        
        if (!subclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select Class',
                    text: 'Please select a class/subclass first'
                });
            } else {
                alert('Please select a class/subclass first');
            }
            return;
        }

        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Loading...');

        $.ajax({
            url: '/admin/get-session-timetable',
            method: 'GET',
            data: { subclassID: subclassID },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    displaySessionTimetable(response, subclassID);
                    $('#sessionTimetableDisplay').show();
                    $('#sessionTimetableEmpty').hide();
                    $('#timetableActions').show();
                } else {
                    // Check if it's "no timetable" error
                    if (response.has_timetable === false) {
                        $('#sessionTimetableDisplay').hide();
                        $('#sessionTimetableEmpty').html(
                            '<div class="text-center py-5">' +
                            '<i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>' +
                            '<p class="text-muted mt-3">No timetable defined for this class</p>' +
                            '<p class="text-muted"><small>Please create a timetable for this class first</small></p>' +
                            '</div>'
                        ).show();
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error || 'Failed to load timetable'
                            });
                        } else {
                            alert(response.error || 'Failed to load timetable');
                        }
                        $('#sessionTimetableDisplay').hide();
                        $('#sessionTimetableEmpty').show();
                    }
                }
                btn.prop('disabled', false).html(originalHtml);
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || 'Failed to load timetable';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                } else {
                    alert(errorMsg);
                }
                btn.prop('disabled', false).html(originalHtml);
                $('#sessionTimetableDisplay').hide();
                $('#sessionTimetableEmpty').show();
            }
        });
    });

    // Store current subclass ID for edit/delete
    let currentSubclassID = null;
    // Store current timetable data for export
    let currentTimetableData = null;

    // Display Session Timetable
    function displaySessionTimetable(data, subclassID) {
        currentSubclassID = subclassID;
        currentTimetableData = data; // Store for export
        currentTimetableData = data; // Store for export
        // Display definition info
        let definitionHtml = '<div class="row">';
        definitionHtml += '<div class="col-md-3"><strong>Session Time:</strong><br>' + data.definition.session_start_time + ' - ' + data.definition.session_end_time + '</div>';
        
        if (data.definition.has_prepo) {
            definitionHtml += '<div class="col-md-3"><strong>Prepo Time:</strong><br>' + data.definition.prepo_start_time + ' - ' + data.definition.prepo_end_time + '</div>';
        }
        
        if (data.break_times && data.break_times.length > 0) {
            definitionHtml += '<div class="col-md-6"><strong>Break Times:</strong><br>';
            data.break_times.forEach(function(bt, index) {
                definitionHtml += 'Break ' + (index + 1) + ': ' + bt.start_time + ' - ' + bt.end_time;
                if (index < data.break_times.length - 1) definitionHtml += ', ';
            });
            definitionHtml += '</div>';
        }
        definitionHtml += '</div>';
        $('#definitionInfoContent').html(definitionHtml);

        // Group sessions by day and time
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        const sessionsByDay = {};
        
        days.forEach(function(day) {
            sessionsByDay[day] = {};
        });

        // Group sessions - separate regular and prepo sessions
        const regularSessionsByDay = {};
        const prepoSessionsByDay = {};
        
        days.forEach(function(day) {
            regularSessionsByDay[day] = {};
            prepoSessionsByDay[day] = {};
        });
        
        const processedSessions = new Set();
        data.sessions.forEach(function(session) {
            // Create unique key to avoid duplicates
            const uniqueKey = session.day + '-' + session.start_time + '-' + session.end_time + '-' + (session.class_subjectID || session.subjectID || '') + '-' + session.teacherID;
            
            if (processedSessions.has(uniqueKey)) {
                return; // Skip duplicate
            }
            processedSessions.add(uniqueKey);
            
            const day = session.day;
            const timeKey = session.start_time + '-' + session.end_time;
            const isPrepo = session.is_prepo == 1 || session.is_prepo === true;
            
            const targetSessions = isPrepo ? prepoSessionsByDay[day] : regularSessionsByDay[day];
            
            if (!targetSessions[timeKey]) {
                targetSessions[timeKey] = [];
            }
            
            // Check if this is a free session
            const isFreeSession = session.is_free === true || 
                                 (session.session_type && session.session_type.toLowerCase() === 'free') ||
                                 (session.subject_name && session.subject_name.toUpperCase() === 'FREE');
            
            targetSessions[timeKey].push({
                subject: isFreeSession ? 'FREE' : (session.subject_name || 'N/A'),
                teacher: isFreeSession ? 'FREE' : (session.teacher_name || 'N/A'),
                class: (session.class_name || '') + ' ' + (session.subclass_name || ''),
                is_prepo: isPrepo,
                is_free: isFreeSession
            });
        });

        // Get all unique time slots for regular sessions including break times
        const regularTimeSlots = new Set();
        days.forEach(function(day) {
            Object.keys(regularSessionsByDay[day]).forEach(function(timeKey) {
                regularTimeSlots.add(timeKey);
            });
        });
        
        // Add break times as time slots
        if (data.break_times && data.break_times.length > 0) {
            data.break_times.forEach(function(bt) {
                regularTimeSlots.add(bt.start_time + '-' + bt.end_time + '-BREAK');
            });
        }
        
        // Get all unique time slots for prepo sessions
        const prepoTimeSlots = new Set();
        if (data.definition.has_prepo) {
            days.forEach(function(day) {
                Object.keys(prepoSessionsByDay[day]).forEach(function(timeKey) {
                    prepoTimeSlots.add(timeKey);
                });
            });
        }
        
        // Sort time slots chronologically
        const sortedRegularTimeSlots = Array.from(regularTimeSlots).sort(function(a, b) {
            const aStart = a.replace('-BREAK', '').split('-')[0];
            const bStart = b.replace('-BREAK', '').split('-')[0];
            return aStart.localeCompare(bStart);
        });
        
        const sortedPrepoTimeSlots = Array.from(prepoTimeSlots).sort(function(a, b) {
            const aStart = a.split('-')[0];
            const bStart = b.split('-')[0];
            return aStart.localeCompare(bStart);
        });

        // Build table
        let tableHtml = '';
        
        const hasRegularSessions = sortedRegularTimeSlots.length > 0;
        const hasPrepoSessions = sortedPrepoTimeSlots.length > 0;
        
        if (!hasRegularSessions && !hasPrepoSessions) {
            tableHtml = '<tr><td colspan="6" class="text-center text-muted">No sessions found for this class</td></tr>';
        } else {
            // Display Regular Sessions First
            if (hasRegularSessions) {
                sortedRegularTimeSlots.forEach(function(timeSlot) {
                    // Check if this is a break time
                    if (timeSlot.includes('-BREAK')) {
                        const [startTime, endTime] = timeSlot.replace('-BREAK', '').split('-');
                        tableHtml += '<tr class="table-warning">';
                        tableHtml += '<td class="fw-bold text-center">' + startTime + '<br>' + endTime + '</td>';
                        tableHtml += '<td colspan="5" class="text-center fw-bold" style="background-color: #fff3cd; color: #856404; padding: 20px 10px;">';
                        tableHtml += '<i class="bi bi-clock"></i> <span style="letter-spacing: 3px;">BREAK TIME</span>';
                        tableHtml += '</td>';
                        tableHtml += '</tr>';
                    } else {
                        const [startTime, endTime] = timeSlot.split('-');
                        tableHtml += '<tr>';
                        tableHtml += '<td class="fw-bold">' + startTime + '<br>' + endTime + '</td>';
                        
                        days.forEach(function(day) {
                            const sessions = regularSessionsByDay[day][timeSlot] || [];
                            if (sessions.length > 0) {
                                let cellHtml = '';
                                // Remove duplicates within same time slot
                                const uniqueSessions = [];
                                const seenSubjects = new Set();
                                
                                sessions.forEach(function(session) {
                                    const sessionKey = session.subject + '-' + session.teacher + '-' + session.class;
                                    if (!seenSubjects.has(sessionKey)) {
                                        seenSubjects.add(sessionKey);
                                        uniqueSessions.push(session);
                                    }
                                });
                                
                                uniqueSessions.forEach(function(session) {
                                    if (session.is_free) {
                                        // Display FREE session with special styling
                                        cellHtml += '<div class="mb-1"><strong class="text-primary" style="font-size: 1.1em;">FREE</strong><br>';
                                        cellHtml += '<small class="text-muted">Free Session</small></div>';
                                    } else {
                                    cellHtml += '<div class="mb-1"><strong>' + session.subject + '</strong><br>';
                                    cellHtml += '<small class="text-muted">' + session.teacher + '</small><br>';
                                    cellHtml += '<small class="text-info">' + session.class + '</small></div>';
                                    }
                                });
                                tableHtml += '<td>' + cellHtml + '</td>';
                            } else {
                                tableHtml += '<td class="bg-light"></td>';
                            }
                        });
                        
                        tableHtml += '</tr>';
                    }
                });
            }
            
            // Display Prepo Sessions Separately (if exists)
            if (data.definition.has_prepo && hasPrepoSessions) {
                // Add separator row for Prepo sessions
                tableHtml += '<tr class="table-success" style="border-top: 3px solid #198754;">';
                tableHtml += '<td class="fw-bold text-center" style="background-color: #d1e7dd;">' + data.definition.prepo_start_time + '<br>' + data.definition.prepo_end_time + '</td>';
                tableHtml += '<td colspan="5" class="text-center fw-bold" style="background-color: #d1e7dd; color: #0f5132; padding: 20px 10px;">';
                tableHtml += '<i class="bi bi-clock-history"></i> <span style="letter-spacing: 3px;">PREPO SESSION</span>';
                tableHtml += '</td>';
                tableHtml += '</tr>';
                
                // Display prepo sessions
                sortedPrepoTimeSlots.forEach(function(timeSlot) {
                    const [startTime, endTime] = timeSlot.split('-');
                    tableHtml += '<tr>';
                    tableHtml += '<td class="fw-bold" style="background-color: #f8f9fa;">' + startTime + '<br>' + endTime + '</td>';
                    
                    days.forEach(function(day) {
                        const sessions = prepoSessionsByDay[day][timeSlot] || [];
                        if (sessions.length > 0) {
                            let cellHtml = '';
                            // Remove duplicates within same time slot
                            const uniqueSessions = [];
                            const seenSubjects = new Set();
                            
                            sessions.forEach(function(session) {
                                const sessionKey = session.subject + '-' + session.teacher + '-' + session.class;
                                if (!seenSubjects.has(sessionKey)) {
                                    seenSubjects.add(sessionKey);
                                    uniqueSessions.push(session);
                                }
                            });
                            
                            uniqueSessions.forEach(function(session) {
                                if (session.is_free) {
                                    // Display FREE session with special styling
                                    cellHtml += '<div class="mb-1"><span class="badge bg-success">Prepo</span> <strong class="text-primary" style="font-size: 1.1em;">FREE</strong><br>';
                                    cellHtml += '<small class="text-muted">Free Session</small></div>';
                                } else {
                                cellHtml += '<div class="mb-1"><span class="badge bg-success">Prepo</span> <strong>' + session.subject + '</strong><br>';
                                cellHtml += '<small class="text-muted">' + session.teacher + '</small><br>';
                                cellHtml += '<small class="text-info">' + session.class + '</small></div>';
                                }
                            });
                            tableHtml += '<td style="background-color: #f8f9fa;">' + cellHtml + '</td>';
                        } else {
                            tableHtml += '<td class="bg-light" style="background-color: #f8f9fa;"></td>';
                        }
                    });
                    
                    tableHtml += '</tr>';
                });
            }
        }

        $('#sessionTimetableBody').html(tableHtml);
    }

    // Handle Export to Excel button
    $('#exportTimetableExcelBtn').on('click', function() {
        if (!currentTimetableData || !currentSubclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Timetable',
                    text: 'Please load a timetable first'
                });
            }
            return;
        }
        exportTimetableToExcel(currentTimetableData, currentSubclassID);
    });

    // Handle Export to PDF button
    $('#exportTimetablePdfBtn').on('click', function() {
        if (!currentTimetableData || !currentSubclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Timetable',
                    text: 'Please load a timetable first'
                });
            }
            return;
        }
        exportTimetableToPdf(currentTimetableData, currentSubclassID);
    });

    // Export Timetable to Excel
    function exportTimetableToExcel(data, subclassID) {
        if (typeof XLSX === 'undefined') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Excel export library not loaded'
                });
            }
            return;
        }

        try {
            // Get subclass name
            const subclassSelect = $('#view_session_subclass_select');
            const subclassName = subclassSelect.find('option:selected').text() || 'Class';
            
            // Get school name
            const schoolName = '{{ $school_details->school_name ?? "School" }}';
            
            // Create workbook
            const wb = XLSX.utils.book_new();
            
            // Prepare data for regular sessions
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            const regularSessionsByDay = {};
            const prepoSessionsByDay = {};
            
            days.forEach(function(day) {
                regularSessionsByDay[day] = {};
                prepoSessionsByDay[day] = {};
            });
            
            data.sessions.forEach(function(session) {
                const day = session.day;
                const timeKey = session.start_time + '-' + session.end_time;
                const isPrepo = session.is_prepo == 1 || session.is_prepo === true;
                
                const targetSessions = isPrepo ? prepoSessionsByDay[day] : regularSessionsByDay[day];
                
                if (!targetSessions[timeKey]) {
                    targetSessions[timeKey] = [];
                }
                
                targetSessions[timeKey].push({
                    subject: session.subject_name || 'N/A',
                    teacher: session.teacher_name || 'N/A',
                    class: (session.class_name || '') + ' ' + (session.subclass_name || ''),
                    is_prepo: isPrepo
                });
            });
            
            // Get all unique time slots
            const regularTimeSlots = new Set();
            days.forEach(function(day) {
                Object.keys(regularSessionsByDay[day]).forEach(function(timeKey) {
                    regularTimeSlots.add(timeKey);
                });
            });
            
            if (data.break_times && data.break_times.length > 0) {
                data.break_times.forEach(function(bt) {
                    regularTimeSlots.add(bt.start_time + '-' + bt.end_time + '-BREAK');
                });
            }
            
            const sortedRegularTimeSlots = Array.from(regularTimeSlots).sort(function(a, b) {
                const aStart = a.replace('-BREAK', '').split('-')[0];
                const bStart = b.replace('-BREAK', '').split('-')[0];
                return aStart.localeCompare(bStart);
            });
            
            // Build Excel data
            const excelData = [];
            
            // Header row
            excelData.push(['TIME', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            
            // Regular sessions
            sortedRegularTimeSlots.forEach(function(timeSlot) {
                if (timeSlot.includes('-BREAK')) {
                    const [startTime, endTime] = timeSlot.replace('-BREAK', '').split('-');
                    const row = [startTime + ' - ' + endTime, 'BREAK TIME', 'BREAK TIME', 'BREAK TIME', 'BREAK TIME', 'BREAK TIME'];
                    excelData.push(row);
                } else {
                    const [startTime, endTime] = timeSlot.split('-');
                    const row = [startTime + ' - ' + endTime];
                    
                    days.forEach(function(day) {
                        const sessions = regularSessionsByDay[day][timeSlot] || [];
                        if (sessions.length > 0) {
                            let cellText = '';
                            const uniqueSessions = [];
                            const seenSubjects = new Set();
                            
                            sessions.forEach(function(session) {
                                const sessionKey = session.subject + '-' + session.teacher;
                                if (!seenSubjects.has(sessionKey)) {
                                    seenSubjects.add(sessionKey);
                                    uniqueSessions.push(session);
                                }
                            });
                            
                            uniqueSessions.forEach(function(session, index) {
                                if (index > 0) cellText += '\n';
                                cellText += session.subject + '\n' + session.teacher + '\n' + session.class;
                            });
                            row.push(cellText);
                        } else {
                            row.push('');
                        }
                    });
                    excelData.push(row);
                }
            });
            
            // Prepo sessions if any
            const prepoTimeSlots = new Set();
            if (data.definition.has_prepo) {
                days.forEach(function(day) {
                    Object.keys(prepoSessionsByDay[day]).forEach(function(timeKey) {
                        prepoTimeSlots.add(timeKey);
                    });
                });
            }
            
            const sortedPrepoTimeSlots = Array.from(prepoTimeSlots).sort(function(a, b) {
                const aStart = a.split('-')[0];
                const bStart = b.split('-')[0];
                return aStart.localeCompare(bStart);
            });
            
            if (sortedPrepoTimeSlots.length > 0) {
                // Prepo header
                excelData.push([]);
                excelData.push(['PREPO SESSION', '', '', '', '', '']);
                
                sortedPrepoTimeSlots.forEach(function(timeSlot) {
                    const [startTime, endTime] = timeSlot.split('-');
                    const row = [startTime + ' - ' + endTime];
                    
                    days.forEach(function(day) {
                        const sessions = prepoSessionsByDay[day][timeSlot] || [];
                        if (sessions.length > 0) {
                            let cellText = '';
                            const uniqueSessions = [];
                            const seenSubjects = new Set();
                            
                            sessions.forEach(function(session) {
                                const sessionKey = session.subject + '-' + session.teacher;
                                if (!seenSubjects.has(sessionKey)) {
                                    seenSubjects.add(sessionKey);
                                    uniqueSessions.push(session);
                                }
                            });
                            
                            uniqueSessions.forEach(function(session, index) {
                                if (index > 0) cellText += '\n';
                                cellText += 'Prepo ' + session.subject + '\n' + session.teacher + '\n' + session.class;
                            });
                            row.push(cellText);
                        } else {
                            row.push('');
                        }
                    });
                    excelData.push(row);
                });
            }
            
            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(excelData);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 15 }, // Time column
                { wch: 25 }, // Monday
                { wch: 25 }, // Tuesday
                { wch: 25 }, // Wednesday
                { wch: 25 }, // Thursday
                { wch: 25 }  // Friday
            ];
            
            // Style header row
            const headerRange = XLSX.utils.decode_range(ws['!ref']);
            for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
                const cellAddress = XLSX.utils.encode_cell({ r: 0, c: col });
                if (!ws[cellAddress]) continue;
                ws[cellAddress].s = {
                    font: { bold: true, color: { rgb: 'FFFFFF' } },
                    fill: { fgColor: { rgb: '940000' } },
                    alignment: { horizontal: 'center', vertical: 'center' }
                };
            }
            
            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Timetable');
            
            // Generate filename
            const fileName = 'Session_Timetable_' + subclassName.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.xlsx';
            
            // Save file
            XLSX.writeFile(wb, fileName);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Exported!',
                    text: 'Timetable exported to Excel successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            console.error('Error exporting to Excel:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Failed to export timetable to Excel: ' + error.message
                });
            }
        }
    }

    // Export Timetable to PDF
    function exportTimetableToPdf(data, subclassID) {
        if (typeof window.jspdf === 'undefined') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'PDF export library not loaded'
                });
            }
            return;
        }

        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape', 'mm', 'a4');
            
            // Get subclass name
            const subclassSelect = $('#view_session_subclass_select');
            const subclassName = subclassSelect.find('option:selected').text() || 'Class';
            
            // Get school name
            const schoolName = '{{ $school_details->school_name ?? "School" }}';
            
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const centerX = pageWidth / 2;
            
            // Header
            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text(schoolName.toUpperCase(), centerX, 15, { align: 'center' });
            
            doc.setFontSize(14);
            doc.setFont('helvetica', 'normal');
            doc.text('Session Timetable - ' + subclassName, centerX, 22, { align: 'center' });
            
            // Definition info
            let yPos = 30;
            doc.setFontSize(10);
            doc.text('Session Time: ' + data.definition.session_start_time + ' - ' + data.definition.session_end_time, 14, yPos);
            
            if (data.definition.has_prepo) {
                yPos += 5;
                doc.text('Prepo Time: ' + data.definition.prepo_start_time + ' - ' + data.definition.prepo_end_time, 14, yPos);
            }
            
            if (data.break_times && data.break_times.length > 0) {
                yPos += 5;
                let breakText = 'Break Times: ';
                data.break_times.forEach(function(bt, index) {
                    breakText += 'Break ' + (index + 1) + ': ' + bt.start_time + ' - ' + bt.end_time;
                    if (index < data.break_times.length - 1) breakText += ', ';
                });
                doc.text(breakText, 14, yPos);
            }
            
            yPos += 10;
            
            // Prepare table data (same logic as display)
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            const regularSessionsByDay = {};
            const prepoSessionsByDay = {};
            
            days.forEach(function(day) {
                regularSessionsByDay[day] = {};
                prepoSessionsByDay[day] = {};
            });
            
            data.sessions.forEach(function(session) {
                const day = session.day;
                const timeKey = session.start_time + '-' + session.end_time;
                const isPrepo = session.is_prepo == 1 || session.is_prepo === true;
                
                const targetSessions = isPrepo ? prepoSessionsByDay[day] : regularSessionsByDay[day];
                
                if (!targetSessions[timeKey]) {
                    targetSessions[timeKey] = [];
                }
                
                targetSessions[timeKey].push({
                    subject: session.subject_name || 'N/A',
                    teacher: session.teacher_name || 'N/A',
                    class: (session.class_name || '') + ' ' + (session.subclass_name || ''),
                    is_prepo: isPrepo
                });
            });
            
            // Get all unique time slots
            const regularTimeSlots = new Set();
            days.forEach(function(day) {
                Object.keys(regularSessionsByDay[day]).forEach(function(timeKey) {
                    regularTimeSlots.add(timeKey);
                });
            });
            
            if (data.break_times && data.break_times.length > 0) {
                data.break_times.forEach(function(bt) {
                    regularTimeSlots.add(bt.start_time + '-' + bt.end_time + '-BREAK');
                });
            }
            
            const sortedRegularTimeSlots = Array.from(regularTimeSlots).sort(function(a, b) {
                const aStart = a.replace('-BREAK', '').split('-')[0];
                const bStart = b.replace('-BREAK', '').split('-')[0];
                return aStart.localeCompare(bStart);
            });
            
            // Build table data
            const tableData = [];
            
            sortedRegularTimeSlots.forEach(function(timeSlot) {
                if (timeSlot.includes('-BREAK')) {
                    const [startTime, endTime] = timeSlot.replace('-BREAK', '').split('-');
                    tableData.push([startTime + ' - ' + endTime, 'BREAK TIME', 'BREAK TIME', 'BREAK TIME', 'BREAK TIME', 'BREAK TIME']);
                } else {
                    const [startTime, endTime] = timeSlot.split('-');
                    const row = [startTime + ' - ' + endTime];
                    
                    days.forEach(function(day) {
                        const sessions = regularSessionsByDay[day][timeSlot] || [];
                        if (sessions.length > 0) {
                            let cellText = '';
                            const uniqueSessions = [];
                            const seenSubjects = new Set();
                            
                            sessions.forEach(function(session) {
                                const sessionKey = session.subject + '-' + session.teacher;
                                if (!seenSubjects.has(sessionKey)) {
                                    seenSubjects.add(sessionKey);
                                    uniqueSessions.push(session);
                                }
                            });
                            
                            uniqueSessions.forEach(function(session, index) {
                                if (index > 0) cellText += '\n';
                                cellText += session.subject + '\n' + session.teacher + '\n' + session.class;
                            });
                            row.push(cellText);
                        } else {
                            row.push('');
                        }
                    });
                    tableData.push(row);
                }
            });
            
            // Prepo sessions if any
            const prepoTimeSlots = new Set();
            if (data.definition.has_prepo) {
                days.forEach(function(day) {
                    Object.keys(prepoSessionsByDay[day]).forEach(function(timeKey) {
                        prepoTimeSlots.add(timeKey);
                    });
                });
            }
            
            const sortedPrepoTimeSlots = Array.from(prepoTimeSlots).sort(function(a, b) {
                const aStart = a.split('-')[0];
                const bStart = b.split('-')[0];
                return aStart.localeCompare(bStart);
            });
            
            if (sortedPrepoTimeSlots.length > 0) {
                tableData.push([]);
                tableData.push(['PREPO SESSION', '', '', '', '', '']);
                
                sortedPrepoTimeSlots.forEach(function(timeSlot) {
                    const [startTime, endTime] = timeSlot.split('-');
                    const row = [startTime + ' - ' + endTime];
                    
                    days.forEach(function(day) {
                        const sessions = prepoSessionsByDay[day][timeSlot] || [];
                        if (sessions.length > 0) {
                            let cellText = '';
                            const uniqueSessions = [];
                            const seenSubjects = new Set();
                            
                            sessions.forEach(function(session) {
                                const sessionKey = session.subject + '-' + session.teacher;
                                if (!seenSubjects.has(sessionKey)) {
                                    seenSubjects.add(sessionKey);
                                    uniqueSessions.push(session);
                                }
                            });
                            
                            uniqueSessions.forEach(function(session, index) {
                                if (index > 0) cellText += '\n';
                                cellText += 'Prepo ' + session.subject + '\n' + session.teacher + '\n' + session.class;
                            });
                            row.push(cellText);
                        } else {
                            row.push('');
                        }
                    });
                    tableData.push(row);
                });
            }
            
            // Add table
            doc.autoTable({
                startY: yPos,
                head: [['TIME', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']],
                body: tableData,
                theme: 'striped',
                headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255], fontStyle: 'bold' },
                styles: { fontSize: 8, cellPadding: 2 },
                columnStyles: {
                    0: { cellWidth: 30, fontStyle: 'bold' },
                    1: { cellWidth: 50 },
                    2: { cellWidth: 50 },
                    3: { cellWidth: 50 },
                    4: { cellWidth: 50 },
                    5: { cellWidth: 50 }
                },
                didDrawPage: function (data) {
                    // Footer
                    const pageHeight = doc.internal.pageSize.getHeight();
                    doc.setFontSize(8);
                    doc.setTextColor(128, 128, 128);
                    doc.text('Generated on ' + new Date().toLocaleString(), centerX, pageHeight - 10, { align: 'center' });
                    doc.setTextColor(148, 0, 0);
                    doc.setFont('helvetica', 'bold');
                    doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 5, { align: 'center' });
                }
            });
            
            // Generate filename
            const fileName = 'Session_Timetable_' + subclassName.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf';
            
            // Save PDF
            doc.save(fileName);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Exported!',
                    text: 'Timetable exported to PDF successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            console.error('Error exporting to PDF:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Failed to export timetable to PDF: ' + error.message
                });
            }
        }
    }

    // Handle Shuffle Session Timetable button
    $('#shuffleSessionTimetableBtn').on('click', function() {
        if (!currentSubclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Class Selected',
                    text: 'Please select a class first'
                });
            }
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'question',
                title: 'Shuffle Sessions?',
                text: 'This will randomly redistribute all sessions across days and times. This action cannot be undone.',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Shuffle',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    shuffleSessionTimetable(currentSubclassID);
                }
            });
        } else {
            if (confirm('Shuffle all sessions for this class? This action cannot be undone.')) {
                shuffleSessionTimetable(currentSubclassID);
            }
        }
    });

    // Handle Swap Sessions button
    $('#swapSessionsBtn').on('click', function() {
        if (!currentSubclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Class Selected',
                    text: 'Please select a class first'
                });
            }
            return;
        }

        // Load sessions for swap selection
        loadSessionsForSwap(currentSubclassID);
    });

    // Shuffle session timetable
    function shuffleSessionTimetable(subclassID) {
        const btn = $('#shuffleSessionTimetableBtn');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Shuffling...');

        $.ajax({
            url: '/admin/shuffle-session-timetable',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { subclassID: subclassID },
            success: function(response) {
                btn.prop('disabled', false).html(originalHtml);
                
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Shuffled!',
                            text: response.message || 'Sessions shuffled successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            // Reload the timetable display
                            $('#view_session_subclass_select').val(currentSubclassID);
                            $('#loadSessionTimetableBtn').click();
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Failed to shuffle sessions'
                        });
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalHtml);
                let errorMsg = xhr.responseJSON?.error || 'Failed to shuffle sessions';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            }
        });
    }

    // Load sessions for swap
    function loadSessionsForSwap(subclassID) {
        $.ajax({
            url: '/admin/get-session-timetable',
            method: 'GET',
            data: { subclassID: subclassID },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.sessions && response.sessions.length >= 2) {
                    showSwapSessionsModal(response.sessions);
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Not Enough Sessions',
                            text: 'You need at least 2 sessions to swap'
                        });
                    }
                }
            },
            error: function(xhr) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load sessions'
                    });
                }
            }
        });
    }

    // Show swap sessions modal
    function showSwapSessionsModal(sessions) {
        // Separate prepo and regular sessions
        const prepoSessions = [];
        const regularSessions = [];
        
        sessions.forEach(function(session) {
            const isPrepo = session.is_prepo == 1 || session.is_prepo === true;
            const sessionData = {
                id: session.session_timetableID || session.id,
                subject: session.subject_name || 'N/A',
                teacher: session.teacher_name || 'N/A',
                time: session.start_time + ' - ' + session.end_time,
                day: session.day,
                is_prepo: isPrepo
            };
            
            if (isPrepo) {
                prepoSessions.push(sessionData);
            } else {
                regularSessions.push(sessionData);
            }
        });

        // Group sessions by day for regular sessions
        const regularSessionsByDay = {};
        regularSessions.forEach(function(session) {
            const day = session.day;
            if (!regularSessionsByDay[day]) {
                regularSessionsByDay[day] = [];
            }
            regularSessionsByDay[day].push(session);
        });

        // Group sessions by day for prepo sessions
        const prepoSessionsByDay = {};
        prepoSessions.forEach(function(session) {
            const day = session.day;
            if (!prepoSessionsByDay[day]) {
                prepoSessionsByDay[day] = [];
            }
            prepoSessionsByDay[day].push(session);
        });

        // Combine regular and prepo sessions by day for display
        const allSessionsByDay = {};
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        days.forEach(function(day) {
            allSessionsByDay[day] = [];
            if (regularSessionsByDay[day]) {
                allSessionsByDay[day] = allSessionsByDay[day].concat(regularSessionsByDay[day]);
            }
            if (prepoSessionsByDay[day]) {
                allSessionsByDay[day] = allSessionsByDay[day].concat(prepoSessionsByDay[day]);
            }
        });

        let modalHtml = '<div class="swap-sessions-modal-content">';
        modalHtml += '<p class="text-muted">Select exactly 2 sessions to swap their days and times:</p>';
        modalHtml += '<div class="row mt-3" style="max-height: 400px; overflow-y: auto;">';
        
        days.forEach(function(day) {
            if (allSessionsByDay[day] && allSessionsByDay[day].length > 0) {
                modalHtml += '<div class="col-md-6 mb-3">';
                modalHtml += '<h6><strong>' + day + '</strong></h6>';
                modalHtml += '<div class="list-group">';
                
                allSessionsByDay[day].forEach(function(session) {
                    const prepoBadge = session.is_prepo ? '<span class="badge bg-success">Prepo</span> ' : '';
                    const sessionType = session.is_prepo ? 'prepo' : 'regular';
                    modalHtml += '<label class="list-group-item swap-session-item" style="cursor: pointer;">';
                    modalHtml += '<div class="d-flex justify-content-between align-items-center">';
                    modalHtml += '<div>';
                    modalHtml += prepoBadge + '<strong>' + session.subject + '</strong><br>';
                    modalHtml += '<small class="text-muted">' + session.teacher + '</small><br>';
                    modalHtml += '<small class="text-info">' + session.time + '</small>';
                    modalHtml += '</div>';
                    modalHtml += '<input type="checkbox" class="session-checkbox" data-session-id="' + session.id + '" data-session-type="' + sessionType + '" style="cursor: pointer;">';
                    modalHtml += '</div>';
                    modalHtml += '</label>';
                });
                
                modalHtml += '</div>';
                modalHtml += '</div>';
            }
        });
        
        modalHtml += '</div>';
        modalHtml += '<div class="mt-3 text-center">';
        modalHtml += '<button type="button" class="btn btn-primary" id="confirmSwapBtn" disabled>';
        modalHtml += '<i class="bi bi-arrow-left-right"></i> Swap Selected Sessions';
        modalHtml += '</button>';
        modalHtml += '<button type="button" class="btn btn-secondary ms-2" id="cancelSwapBtn">Cancel</button>';
        modalHtml += '</div>';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Swap Sessions',
                html: modalHtml,
                width: '900px',
                showCancelButton: false,
                showConfirmButton: false,
                didOpen: () => {
                    let selectedSessions = [];

                    // Handle checkbox clicks
                    $('.session-checkbox').on('change', function() {
                        const sessionID = $(this).data('session-id');
                        const sessionType = $(this).data('session-type');
                        const isChecked = $(this).is(':checked');
                        
                        if (isChecked) {
                            // Check if we already have 2 sessions selected
                            if (selectedSessions.length >= 2) {
                                $(this).prop('checked', false);
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Maximum 2 Sessions',
                                    text: 'Please select only 2 sessions to swap',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                return;
                            }
                            
                            // If we already have 1 session selected, check if types match
                            if (selectedSessions.length === 1) {
                                const firstSessionType = selectedSessions[0].type;
                                if (firstSessionType !== sessionType) {
                                    $(this).prop('checked', false);
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Session Type Mismatch',
                                        text: 'You can only swap sessions of the same type (Prepo with Prepo, Regular with Regular)',
                                        timer: 3000,
                                        showConfirmButton: false
                                    });
                                    return;
                                }
                            }
                            
                            selectedSessions.push({ id: sessionID, type: sessionType });
                        } else {
                            selectedSessions = selectedSessions.filter(s => s.id !== sessionID);
                        }
                        
                        // Update swap button state
                        $('#confirmSwapBtn').prop('disabled', selectedSessions.length !== 2);
                    });

                    // Handle confirm swap
                    $('#confirmSwapBtn').on('click', function() {
                        if (selectedSessions.length === 2) {
                            // Ensure both sessions are of the same type
                            if (selectedSessions[0].type !== selectedSessions[1].type) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Invalid Selection',
                                    text: 'Cannot swap sessions of different types'
                                });
                                return;
                            }
                            swapSessions(currentSubclassID, selectedSessions[0].id, selectedSessions[1].id);
                            Swal.close();
                        }
                    });

                    // Handle cancel
                    $('#cancelSwapBtn').on('click', function() {
                        Swal.close();
                    });
                }
            });
        }
    }

    // Swap two sessions
    function swapSessions(subclassID, session1ID, session2ID) {
        $.ajax({
            url: '/admin/swap-session-timetable',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                subclassID: subclassID,
                session1ID: session1ID,
                session2ID: session2ID
            },
            success: function(response) {
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Swapped!',
                            text: response.message || 'Sessions swapped successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            // Reload the timetable display
                            $('#view_session_subclass_select').val(currentSubclassID);
                            $('#loadSessionTimetableBtn').click();
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Failed to swap sessions'
                        });
                    }
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || 'Failed to swap sessions';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            }
        });
    }

    // Handle Edit Timetable button
    $('#editTimetableBtn').on('click', function() {
        if (!currentSubclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Class Selected',
                    text: 'Please select a class first'
                });
            }
            return;
        }

        // Load existing timetable data for editing
        loadTimetableForEditing(currentSubclassID);
    });

    // Store editing state
    let isEditingMode = false;
    let editingSubclassID = null;

    // Load timetable data for editing
    function loadTimetableForEditing(subclassID) {
        editingSubclassID = subclassID;
        isEditingMode = true;
        
        $.ajax({
            url: '/admin/get-session-timetable',
            method: 'GET',
            data: { subclassID: subclassID },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Open create modal and switch to class session timetable
                    $('#createTimetableModal').modal('show');
                    $('input[name="timetable_category"][value="class"]').prop('checked', true).trigger('change');
                    
                    // Wait for form to be ready
                    setTimeout(function() {
                        // Check if definition exists - if not, show error
                        if (!response.definition) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Timetable definition not found. Please create definition first.'
                                });
                            }
                            return;
                        }
                        
                        // Store definition globally
                        timetableDefinition = {
                            definitionID: response.definition.definitionID || null,
                            session_start_time: response.definition.session_start_time,
                            session_end_time: response.definition.session_end_time,
                            has_prepo: response.definition.has_prepo,
                            prepo_start_time: response.definition.prepo_start_time || '',
                            prepo_end_time: response.definition.prepo_end_time || '',
                            break_times: response.break_times || [],
                            session_types: response.session_types ? (Array.isArray(response.session_types) ? response.session_types : Object.values(response.session_types)) : []
                        };
                        
                        // Ensure Step 2 is shown (skip definition if exists)
                        $('#existingDefinitionDisplay').show();
                        $('#sessionDefinitionStep').hide();
                        $('#sessionTimetableStep').show();
                        
                        // Hide "Add New Class" button in edit mode
                        $('#addNewClassBtn').hide();
                        
                        // Hide "Save All" and show only "Update Timetable" button in edit mode
                        $('#saveAllTimetablesBtn').hide();
                        if ($('#updateTimetableBtn').length === 0) {
                            $('#saveAllTimetablesBtn').after('<button type="button" class="btn btn-success mt-3" id="updateTimetableBtn" style="display: none;"><i class="bi bi-check-circle"></i> Update Timetable</button>');
                        }
                        $('#updateTimetableBtn').show();
                        
                        // Clear existing forms
                        $('#classTimetableForms').empty();
                        classTimetableCounter = 0;
                        
                        // Add class form
                        addClassTimetableForm();
                        
                        const classIndex = $('.class-timetable-form').first().data('class-index');
                        
                        // Select subclass - this will trigger loading of subjects
                        const subclassSelect = $(`.subclass-select[data-class-index="${classIndex}"]`);
                        subclassSelect.val(subclassID).attr('disabled', true); // Disable in edit mode
                        
                        // Trigger change to load subjects and initialize days
                        subclassSelect.trigger('change');
                        
                        // Wait for subjects and days to initialize, then load sessions
                        setTimeout(function() {
                            loadExistingSessionsIntoForm(classIndex, subclassID, response.sessions || [], response.definition);
                            
                            // Highlight the form
                            $(`.class-timetable-form[data-class-index="${classIndex}"]`).css('border', '2px solid #ffc107');
                            
                            // Scroll to form
                            $('html, body').animate({
                                scrollTop: $(`.class-timetable-form[data-class-index="${classIndex}"]`).offset().top - 100
                            }, 500);
                        }, 1500);
                    }, 500);
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'No timetable data found for editing'
                        });
                    }
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || 'Failed to load timetable for editing';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            }
        });
    }

    // Handle Update Timetable button
    $(document).on('click', '#updateTimetableBtn', function() {
        if (!editingSubclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Error',
                    text: 'No class selected for editing'
                });
            }
            return;
        }

        const classIndex = $('.class-timetable-form').first().data('class-index');
        if (!classIndex) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Error',
                    text: 'No timetable form found'
                });
            }
            return;
        }

        // Collect timetable data (same as save all, but for one class)
        const timetables = [];
        let hasErrors = false;
        
        // Clear all previous validation errors
        $('.session-validation-error').hide().html('');
        $('.form-control').removeClass('is-invalid');
        $('.form-select').removeClass('is-invalid');
        $('.day-timetable').removeClass('border-danger');

        // Get subclass
        const subclassSelect = $(`.subclass-select[data-class-index="${classIndex}"]`);
        const subclassID = subclassSelect.val();
        
        if (!subclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Error',
                    text: 'Please select a class'
                });
            }
            return;
        }

        // Collect sessions for this class
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        days.forEach(function(day) {
            // Regular sessions
            $(`#sessions-${classIndex}-${day} .session-form-row`).each(function() {
                const row = $(this);
                const subjectID = row.find('.session-subject').val();
                const sessionTypeID = row.find('.session-type-select').val();
                const sessionTypeName = row.find('.session-type-select option:selected').text().toLowerCase();
                const isFreeSession = sessionTypeName.includes('free');
                const startTime = row.find('.session-start-time').val();
                const endTime = row.find('.session-end-time').val();
                const teacherID = row.find('.session-teacher-id').val();

                // For free sessions, subject and teacher are not required
                if (isFreeSession) {
                    if (sessionTypeID && startTime && endTime) {
                        timetables.push({
                            subclassID: subclassID,
                            class_subjectID: null,
                            subjectID: null,
                            teacherID: null,
                            session_typeID: sessionTypeID,
                            day: day,
                            start_time: startTime,
                            end_time: endTime,
                            is_prepo: 0
                        });
                    } else {
                        hasErrors = true;
                    }
                } else {
                    // For non-free sessions, subject and teacher are required
                    if (subjectID && sessionTypeID && startTime && endTime && teacherID) {
                        timetables.push({
                            subclassID: subclassID,
                            class_subjectID: subjectID,
                            subjectID: null,
                            teacherID: teacherID,
                            session_typeID: sessionTypeID,
                            day: day,
                            start_time: startTime,
                            end_time: endTime,
                            is_prepo: 0
                        });
                    } else {
                        hasErrors = true;
                    }
                }
            });

            // Prepo sessions
            $(`#prepo-${classIndex}-${day} .session-form-row`).each(function() {
                const row = $(this);
                const subjectID = row.find('.session-subject').val();
                const sessionTypeID = row.find('.session-type-select').val();
                const sessionTypeName = row.find('.session-type-select option:selected').text().toLowerCase();
                const isFreeSession = sessionTypeName.includes('free');
                const startTime = row.find('.session-start-time').val();
                const endTime = row.find('.session-end-time').val();
                const teacherID = row.find('.session-teacher-id').val();

                // For free sessions, subject and teacher are not required
                if (isFreeSession) {
                    if (sessionTypeID && startTime && endTime) {
                        timetables.push({
                            subclassID: subclassID,
                            class_subjectID: null,
                            subjectID: null,
                            teacherID: null,
                            session_typeID: sessionTypeID,
                            day: day,
                            start_time: startTime,
                            end_time: endTime,
                            is_prepo: 1
                        });
                    } else {
                        hasErrors = true;
                    }
                } else {
                    // For non-free sessions, subject and teacher are required
                    if (subjectID && sessionTypeID && startTime && endTime && teacherID) {
                        timetables.push({
                            subclassID: subclassID,
                            class_subjectID: subjectID,
                            subjectID: null,
                            teacherID: teacherID,
                            session_typeID: sessionTypeID,
                            day: day,
                            start_time: startTime,
                            end_time: endTime,
                            is_prepo: 1
                        });
                    } else {
                        hasErrors = true;
                    }
                }
            });
        });

        if (hasErrors || timetables.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please fill all required fields for all sessions'
                });
            }
            return;
        }

        // Show loading
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

        // Send update request
        $.ajax({
            url: '/admin/save-class-session-timetables',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                timetables: timetables
            },
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message || 'Timetable updated successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            $('#createTimetableModal').modal('hide');
                            resetEditMode();
                            
                            // Reload the timetable display
                            if (currentSubclassID) {
                                // Set the select to current subclass and trigger load
                                $('#view_session_subclass_select').val(currentSubclassID);
                                $('#loadSessionTimetableBtn').click();
                            }
                        });
                    } else {
                        $('#createTimetableModal').modal('hide');
                        resetEditMode();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Failed to update timetable'
                        });
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                let errorMsg = xhr.responseJSON?.error || 'Failed to update timetable';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            }
        });
    });

    // Reset edit mode
    function resetEditMode() {
        isEditingMode = false;
        editingSubclassID = null;
        $('#updateTimetableBtn').hide();
        $('#saveAllTimetablesBtn').show();
        $('#addNewClassBtn').show();
        $('.subclass-select').prop('disabled', false);
    }

    // Reset edit mode when modal is closed (add to existing handler)
    $('#createTimetableModal').on('hidden.bs.modal', function() {
        resetEditMode();
        // Also reset form
        $('#createTimetableForm')[0].reset();
        $('#timetableFormErrors').hide();
        $('#class_subjectID').html('<option value="">Select Class First</option>');
        $('#days_timetable').html('');
        $('#days_info').hide();
        // Reset required attributes based on selected category
        const category = $('input[name="timetable_category"]:checked').val();
        if (category === 'exam') {
            $('#session_start_time, #session_end_time, #prepo_start_time, #prepo_end_time').removeAttr('required').prop('disabled', true);
        } else {
            $('#session_start_time, #session_end_time').prop('disabled', false).attr('required', 'required');
            $('#prepo_start_time, #prepo_end_time').prop('disabled', false);
        }
    });

    // Load existing sessions into form
    function loadExistingSessionsIntoForm(classIndex, subclassID, sessions, definition) {
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Clear any existing sessions first to prevent duplicates
        days.forEach(function(day) {
            $(`#sessions-${classIndex}-${day}`).empty();
            $(`#prepo-${classIndex}-${day}`).empty();
        });
        
        // Wait a bit more for subjects to be loaded
        let attempts = 0;
        const maxAttempts = 25; // 5 seconds max (25 * 200ms)
        
        // Track loaded sessions to prevent duplicates
        const loadedSessions = new Set();
        
        const checkSubjects = setInterval(function() {
            attempts++;
            const subjects = $(`.class-timetable-form[data-class-index="${classIndex}"]`).data('subjects') || [];
            const sessionTypes = timetableDefinition ? timetableDefinition.session_types : [];
            
            if (subjects.length === 0 && attempts < maxAttempts) {
                // Subjects not loaded yet, wait more
                return;
            }
            
            clearInterval(checkSubjects);
            
            if (subjects.length === 0) {
                console.error('Subjects not loaded after maximum attempts');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load subjects. Please try again.'
                    });
                }
                return;
            }
            
            // Get subjects map for quick lookup
            const subjectsMap = {};
            subjects.forEach(function(subj) {
                const subjectID = subj.class_subjectID || subj.subjectID;
                subjectsMap[subjectID] = subj;
            });
            
            // Get session types map
            const sessionTypesMap = {};
            if (sessionTypes && sessionTypes.length > 0) {
                sessionTypes.forEach(function(st) {
                    const typeID = st.session_typeID || st.id;
                    sessionTypesMap[typeID] = st;
                });
            }
            
            // Load sessions for each day - use unique key to prevent duplicates
            sessions.forEach(function(session) {
                // Create unique key for this session
                const sessionKey = `${session.day}-${session.start_time}-${session.end_time}-${session.class_subjectID || session.subjectID}-${session.teacherID}-${session.is_prepo ? 'prepo' : 'regular'}`;
                
                // Skip if already loaded
                if (loadedSessions.has(sessionKey)) {
                    console.log('Skipping duplicate session:', sessionKey);
                    return;
                }
                
                loadedSessions.add(sessionKey);
                
                const day = session.day;
                const isPrepo = session.is_prepo == 1 || session.is_prepo === true;
                
                // Get subject ID
                const subjectID = session.class_subjectID || session.subjectID;
                const subject = subjectsMap[subjectID];
                
                if (!subject) {
                    console.warn('Subject not found for session:', session);
                    return; // Skip if subject not found
                }
                
                // Get session type
                const sessionTypeID = session.session_typeID;
                const sessionType = sessionTypesMap[sessionTypeID];
                
                if (!sessionType) {
                    console.warn('Session type not found for session:', session);
                    return; // Skip if session type not found
                }
                
                // Add session form with data
                const sessionCounter = Date.now() + Math.random() + Math.random(); // More unique
                renderSessionFormWithData(classIndex, day, isPrepo, subjects, sessionTypes, definition, sessionCounter, {
                    subjectID: subjectID,
                    sessionTypeID: sessionTypeID,
                    startTime: session.start_time,
                    endTime: session.end_time,
                    teacherID: session.teacherID,
                    teacherName: session.teacher_name || session.teacherName || ''
                });
            });
        }, 200);
        
        // Timeout after 5 seconds
        setTimeout(function() {
            clearInterval(checkSubjects);
        }, 5000);
    }

    // Render session form with pre-filled data
    function renderSessionFormWithData(classIndex, day, isPrepo, subjects, sessionTypes, definition, sessionCounter, sessionData) {
        const sessionTypeOptions = sessionTypes.map(st => 
            `<option value="${st.session_typeID || st.id}" data-minutes="${st.minutes}">${st.name || st.type} (${st.minutes} min)</option>`
        ).join('');

        // Note: Subjects can now be selected multiple times (no restrictions)

        const subjectOptions = subjects.length > 0 ? subjects.map(subj => {
            const subjectID = subj.class_subjectID || subj.subjectID;
            const selected = subjectID == sessionData.subjectID ? 'selected' : '';
            return `<option value="${subjectID}" ${selected} data-teacher-id="${subj.teacherID || ''}" data-teacher-name="${subj.teacher_name || 'Not Assigned'}">${subj.subject_name || subj.name}</option>`;
        }).join('') : '<option value="">No subjects available</option>';

        // Get session start/end times from definition
        const sessionStart = definition ? definition.session_start_time : '08:00';
        const sessionEnd = definition ? definition.session_end_time : '15:00';
        const prepoStart = definition && definition.has_prepo ? definition.prepo_start_time : '';
        const prepoEnd = definition && definition.has_prepo ? definition.prepo_end_time : '';

        // Use provided session data or defaults
        const initialStartTime = sessionData.startTime || (isPrepo ? prepoStart : sessionStart);
        const initialEndTime = sessionData.endTime || (isPrepo ? prepoEnd : sessionEnd);
        
        const sessionHtml = `
            <div class="card mb-2 session-form-row" data-session-id="${sessionCounter}" data-is-prepo="${isPrepo}">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Subject <span class="text-danger">*</span></label>
                            <select class="form-control session-subject" required>
                                <option value="">Select Subject</option>
                                ${subjectOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Session Type <span class="text-danger">*</span></label>
                            <select class="form-control session-type-select" required>
                                <option value="">Select Type</option>
                                ${sessionTypeOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control session-start-time" 
                                   value="${initialStartTime}" required>
                        </div>
                        <div class="col-md-2">
                            <label>End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control session-end-time" 
                                   value="${initialEndTime}" required>
                        </div>
                        <div class="col-md-2">
                            <label>Teacher</label>
                            <input type="hidden" class="session-teacher-id" value="${sessionData.teacherID || ''}">
                            <input type="text" class="form-control session-teacher-display" readonly 
                                   placeholder="Select subject first" value="${sessionData.teacherName || ''}">
                            <small class="text-muted session-teacher-info"></small>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-danger remove-session" data-class-index="${classIndex}" data-day="${day}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-2 session-validation-error" style="display: none;"></div>
                </div>
            </div>
        `;

        const container = isPrepo ? 
            $(`#prepo-${classIndex}-${day}`) : 
            $(`#sessions-${classIndex}-${day}`);
        
        container.append(sessionHtml);

        const sessionRow = container.find(`.session-form-row[data-session-id="${sessionCounter}"]`);

        // Set selected values immediately (DOM is ready after append)
        if (sessionData.subjectID) {
            sessionRow.find('.session-subject').val(sessionData.subjectID);
        }
        
        if (sessionData.sessionTypeID) {
            sessionRow.find('.session-type-select').val(sessionData.sessionTypeID);
        }
        
        // Set teacher info immediately
        if (sessionData.teacherID) {
            sessionRow.find('.session-teacher-id').val(sessionData.teacherID);
        }
        if (sessionData.teacherName) {
            sessionRow.find('.session-teacher-display').val(sessionData.teacherName);
            sessionRow.find('.session-teacher-info').text('Assigned to this subject');
        }
        
        // Set times immediately
        if (sessionData.startTime) {
            sessionRow.find('.session-start-time').val(sessionData.startTime);
        }
        if (sessionData.endTime) {
            sessionRow.find('.session-end-time').val(sessionData.endTime);
        }

        // Attach event handlers first
        attachSessionEventHandlers(sessionRow, classIndex, day, isPrepo, definition);
        
        // Then trigger change events after handlers are attached (small delay to ensure handlers are ready)
        setTimeout(function() {
            // Check if this is a free session first
            const sessionTypeName = sessionRow.find('.session-type-select option:selected').text().toLowerCase();
            const isFreeSession = sessionTypeName.includes('free');
            
            if (isFreeSession) {
                // For free sessions, hide subject and teacher fields
                const rowElement = sessionRow.find('.row').first();
                const subjectCol = rowElement.children('.col-md-3').first();
                const teacherCol = rowElement.children('.col-md-2').eq(3);
                subjectCol.hide();
                teacherCol.hide();
                sessionRow.find('.session-subject').removeAttr('required');
                sessionRow.find('.session-teacher-id').removeAttr('required');
            }
            
            if (sessionData.subjectID && !isFreeSession) {
                sessionRow.find('.session-subject').trigger('change');
            }
            if (sessionData.sessionTypeID) {
                sessionRow.find('.session-type-select').trigger('change');
            }
        }, 50);
    }

    // Attach event handlers for session form
    function attachSessionEventHandlers(sessionRow, classIndex, day, isPrepo, definition) {
        // Auto-display teacher when subject is selected
        sessionRow.find('.session-subject').off('change').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const teacherID = selectedOption.data('teacher-id');
            const teacherName = selectedOption.data('teacher-name');
            
            const currentSessionRow = $(this).closest('.session-form-row');
            
            if (teacherID && teacherName) {
                currentSessionRow.find('.session-teacher-id').val(teacherID);
                currentSessionRow.find('.session-teacher-display').val(teacherName);
                currentSessionRow.find('.session-teacher-info').text('Assigned to this subject');
                
                const classIdx = currentSessionRow.closest('.day-timetable').data('class-index');
                const dayName = currentSessionRow.closest('.day-timetable').data('day');
                validateTeacherConflict(currentSessionRow, classIdx, dayName);
            } else {
                currentSessionRow.find('.session-teacher-id').val('');
                currentSessionRow.find('.session-teacher-display').val('');
                currentSessionRow.find('.session-teacher-info').text('');
            }
            
            const classIdx = currentSessionRow.closest('.day-timetable').data('class-index');
            updateAllSubjectDropdowns(classIdx);
        });

        // Auto-calculate end time when session type changes
        sessionRow.find('.session-type-select').off('change').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const sessionTypeName = selectedOption.text().toLowerCase();
            const isFreeSession = sessionTypeName.includes('free');
            
            // Show/hide subject and teacher fields based on session type
            // Subject is in first col-md-3, Teacher is in col-md-2 after Session Type, Start Time, End Time
            const rowElement = sessionRow.find('.row').first();
            const subjectCol = rowElement.children('.col-md-3').first();
            const teacherCol = rowElement.children('.col-md-2').eq(3);
            
            if (isFreeSession) {
                // Hide subject and teacher for free sessions
                subjectCol.hide();
                teacherCol.hide();
                // Remove required attribute
                sessionRow.find('.session-subject').removeAttr('required');
                sessionRow.find('.session-teacher-id').removeAttr('required');
                // Clear values
                sessionRow.find('.session-subject').val('');
                sessionRow.find('.session-teacher-id').val('');
                sessionRow.find('.session-teacher-display').val('');
            } else {
                // Show subject and teacher for non-free sessions
                subjectCol.show();
                teacherCol.show();
                // Add required attribute
                sessionRow.find('.session-subject').attr('required', 'required');
            }
            
            const startTime = sessionRow.find('.session-start-time').val();
            const minutes = selectedOption.data('minutes');
            if (startTime && minutes) {
                const endTime = calculateEndTime(startTime, minutes);
                sessionRow.find('.session-end-time').val(endTime);
                validateSession(sessionRow, classIndex, day);
            }
        });

        // Validate on time change
        sessionRow.find('.session-start-time, .session-end-time').off('change').on('change', function() {
            validateSession(sessionRow, classIndex, day);
            recalculateSubsequentSessions(classIndex, day, isPrepo);
        });
    }

    // Handle Delete Timetable button
    $('#deleteTimetableBtn').on('click', function() {
        if (!currentSubclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Class Selected',
                    text: 'Please select a class first'
                });
            }
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Delete Timetable?',
                text: 'Are you sure you want to delete the timetable for this class? This action cannot be undone.',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteClassTimetable(currentSubclassID);
                }
            });
        } else {
            if (confirm('Are you sure you want to delete the timetable for this class?')) {
                deleteClassTimetable(currentSubclassID);
            }
        }
    });

    // Delete class timetable
    function deleteClassTimetable(subclassID) {
        $.ajax({
            url: '/admin/delete-class-session-timetable',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { subclassID: subclassID },
            success: function(response) {
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Timetable deleted successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    // Clear display
                    $('#sessionTimetableDisplay').hide();
                    $('#sessionTimetableEmpty').html(
                        '<div class="text-center py-5">' +
                        '<i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>' +
                        '<p class="text-muted mt-3">No timetable defined for this class</p>' +
                        '<p class="text-muted"><small>Please create a timetable for this class first</small></p>' +
                        '</div>'
                    ).show();
                    $('#timetableActions').hide();
                    currentSubclassID = null;
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Failed to delete timetable'
                        });
                    }
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || 'Failed to delete timetable';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            }
        });
    }

    // Handle timetable category radio button change
    $('input[name="timetable_category"]').on('change', function() {
        const category = $(this).val();
        if (category === 'exam') {
            $('#examTimetableForm').show();
            $('#classSessionTimetableForm').hide();
            // Remove required attribute from hidden fields to prevent validation errors
            $('#session_start_time, #session_end_time, #prepo_start_time, #prepo_end_time').removeAttr('required').prop('disabled', true);
        } else if (category === 'class') {
            $('#examTimetableForm').hide();
            $('#classSessionTimetableForm').show();
            // Re-enable and add required attribute back when form is shown
            $('#session_start_time, #session_end_time').prop('disabled', false).attr('required', 'required');
            // Prepo fields only required if has_prepo is checked
            $('#prepo_start_time, #prepo_end_time').prop('disabled', false);
            // Check if definition exists
            checkExistingDefinition();
        }
    });

    // Check for existing timetable definition
    function checkExistingDefinition() {
        $.ajax({
            url: '/admin/get-session-timetable-definition',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.definition) {
                    // Display existing definition
                    displayExistingDefinition(response.definition);
                    $('#existingDefinitionDisplay').show();
                    $('#sessionDefinitionStep').hide();
                    $('#sessionTimetableStep').hide();
                } else {
                    // No definition exists, show form
                    $('#existingDefinitionDisplay').hide();
                    $('#sessionDefinitionStep').show();
                    $('#sessionTimetableStep').hide();
                }
            },
            error: function(xhr) {
                console.error('Error checking definition:', xhr);
                // On error, show form to create new
                $('#existingDefinitionDisplay').hide();
                $('#sessionDefinitionStep').show();
                $('#sessionTimetableStep').hide();
            }
        });
    }

    // Display existing definition in table
    function displayExistingDefinition(definition) {
        let html = '<table class="table table-bordered">';
        html += '<thead class="table-light"><tr><th>Setting</th><th>Value</th></tr></thead>';
        html += '<tbody>';
        
        // Session Times
        html += '<tr><td><strong>Session Start Time</strong></td><td>' + definition.session_start_time + '</td></tr>';
        html += '<tr><td><strong>Session End Time</strong></td><td>' + definition.session_end_time + '</td></tr>';
        
        // Prepo
        if (definition.has_prepo) {
            html += '<tr><td><strong>Has Prepo</strong></td><td>Yes</td></tr>';
            html += '<tr><td><strong>Prepo Start Time</strong></td><td>' + definition.prepo_start_time + '</td></tr>';
            html += '<tr><td><strong>Prepo End Time</strong></td><td>' + definition.prepo_end_time + '</td></tr>';
        } else {
            html += '<tr><td><strong>Has Prepo</strong></td><td>No</td></tr>';
        }
        
        // Break Times
        if (definition.break_times && definition.break_times.length > 0) {
            html += '<tr><td><strong>Break Times</strong></td><td>';
            definition.break_times.forEach(function(bt, index) {
                html += 'Break ' + (index + 1) + ': ' + bt.start_time + ' - ' + bt.end_time + '<br>';
            });
            html += '</td></tr>';
        } else {
            html += '<tr><td><strong>Break Times</strong></td><td>None</td></tr>';
        }
        
        // Session Types
        if (definition.session_types && definition.session_types.length > 0) {
            html += '<tr><td><strong>Session Types</strong></td><td>';
            definition.session_types.forEach(function(st) {
                html += '<span class="badge bg-primary me-1">' + st.name + ' (' + st.minutes + ' min)</span>';
            });
            html += '</td></tr>';
        }
        
        html += '</tbody></table>';
        $('#definitionTableContainer').html(html);
    }

    // Handle Edit Definition button
    $('#editDefinitionBtn').on('click', function() {
        $('#existingDefinitionDisplay').hide();
        $('#sessionDefinitionStep').show();
        
        // Load existing definition into form
        $.ajax({
            url: '/admin/get-session-timetable-definition',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.definition) {
                    const def = response.definition;
                    
                    // Load session times
                    $('#session_start_time').val(def.session_start_time);
                    $('#session_end_time').val(def.session_end_time);
                    
                    // Load prepo
                    if (def.has_prepo == 1 || def.has_prepo === true || def.has_prepo == '1') {
                        $('#has_prepo').prop('checked', true).trigger('change');
                        $('#prepo_start_time').val(def.prepo_start_time);
                        $('#prepo_end_time').val(def.prepo_end_time);
                    } else {
                        // Ensure checkbox is unchecked if has_prepo is false
                        $('#has_prepo').prop('checked', false).trigger('change');
                        $('#prepo_start_time').val('');
                        $('#prepo_end_time').val('');
                    }
                    
                    // Load break times
                    if (def.break_times && def.break_times.length > 0) {
                        $('#break_time_count').val(def.break_times.length).trigger('change');
                        setTimeout(function() {
                            def.break_times.forEach(function(bt, index) {
                                $(`#break_${index + 1}_start_time`).val(bt.start_time);
                                $(`#break_${index + 1}_end_time`).val(bt.end_time);
                            });
                        }, 100);
                    }
                    
                    // Load session types
                    if (def.session_types && def.session_types.length > 0) {
                        def.session_types.forEach(function(st) {
                            addSessionTypeRow();
                            setTimeout(function() {
                                const lastRow = $('.session-type-row').last();
                                lastRow.find('.session-type-select').val(st.type);
                                if (st.type === 'custom') {
                                    lastRow.find('.session-type-select').trigger('change');
                                    const index = lastRow.find('.session-type-select').data('index');
                                    $(`#custom-name-${index}`).val(st.name);
                                }
                                lastRow.find('.session-minutes').val(st.minutes);
                            }, 50);
                        });
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading definition:', xhr);
            }
        });
    });

    // Handle Continue with Definition button
    $('#continueWithDefinitionBtn').on('click', function() {
        loadDefinitionData();
        $('#existingDefinitionDisplay').hide();
        $('#sessionDefinitionStep').hide();
        $('#sessionTimetableStep').show();
    });

    // Store definition data globally
    let timetableDefinition = null;
    let classTimetableCounter = 0;
    let selectedClasses = [];

    // Load definition data when Step 2 is shown
    function loadDefinitionData() {
        $.ajax({
            url: '/admin/get-session-timetable-definition',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.definition) {
                    timetableDefinition = response.definition;
                    // Load session types with IDs
                    loadSessionTypes();
                }
            },
            error: function(xhr) {
                console.error('Error loading definition:', xhr);
            }
        });
    }

    // Load session types from database
    function loadSessionTypes(callback) {
        if (!timetableDefinition || !timetableDefinition.id) {
            if (callback) callback([]);
            return;
        }

        $.ajax({
            url: '/admin/get-session-types',
            method: 'GET',
            data: { definitionID: timetableDefinition.id },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.types) {
                    if (callback) callback(response.types);
                } else {
                    if (callback) callback([]);
                }
            },
            error: function(xhr) {
                console.error('Error loading session types:', xhr);
                if (callback) callback([]);
            }
        });
    }

    // Handle Add New Class button
    $('#addNewClassBtn').on('click', function() {
        addClassTimetableForm();
    });

    // Add class timetable form
    function addClassTimetableForm() {
        classTimetableCounter++;
        const classFormHtml = `
            <div class="card mb-3 class-timetable-form" data-class-index="${classTimetableCounter}">
                <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Class Timetable ${classTimetableCounter}</h6>
                    <button type="button" class="btn btn-sm btn-light remove-class-form">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="card-body">
                    <!-- Subclass Selection -->
                    <div class="form-group mb-3">
                        <label>Select Class (Subclass) <span class="text-danger">*</span></label>
                        <select class="form-control subclass-select" data-class-index="${classTimetableCounter}" required>
                            <option value="">Select Class...</option>
                            @foreach($subclasses ?? [] as $subclass)
                                @php
                                    $className = $subclass->class->class_name ?? $subclass->class_name ?? '';
                                    $subclassName = trim($subclass->subclass_name ?? '');
                                    $displayName = $subclassName ? $className . ' ' . $subclassName : $className;
                                @endphp
                                <option value="{{ $subclass->subclassID }}" 
                                        data-class-name="{{ $className }}"
                                        data-subclass-name="{{ $subclassName }}">
                                    {{ $displayName }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the class/subclass for this timetable</small>
                    </div>

                    <!-- Days Container -->
                    <div class="days-container" id="days-container-${classTimetableCounter}">
                        <!-- Days will be added dynamically -->
                    </div>
                </div>
            </div>
        `;
        
        $('#classTimetableForms').append(classFormHtml);
        $('#saveAllTimetablesBtn').show();
        
        // Disable subclasses with timetables in the new dropdown
        setTimeout(function() {
            disableSubclassesWithTimetables();
        }, 100);
        
        // Initialize days when subclass is selected
        $(`.subclass-select[data-class-index="${classTimetableCounter}"]`).on('change', function() {
            const subclassID = $(this).val();
            const classIndex = $(this).data('class-index');
            if (subclassID) {
                // Check if subclass already has timetable
                checkSubclassTimetableStatus(subclassID, classIndex);
                initializeDaysForClass(classIndex, subclassID);
                
                // Disable other subclasses that have sessions
                updateSubclassDropdowns(classIndex);
            } else {
                $(`#days-container-${classIndex}`).empty();
            }
        });
    }

    // Update subclass dropdowns to disable classes with sessions
    function updateSubclassDropdowns(excludeClassIndex) {
        $('.class-timetable-form').each(function() {
            const classIndex = $(this).data('class-index');
            const subclassID = $(this).find('.subclass-select').val();
            
            if (!subclassID) return;
            
            // Check if this subclass has any sessions
            const hasSessions = checkClassHasSessions(classIndex);
            
            // Update all subclass dropdowns
            $('.subclass-select').each(function() {
                const parentClassIndex = $(this).data('class-index');
                const option = $(this).find(`option[value="${subclassID}"]`);
                
                if (option.length > 0) {
                    if (hasSessions && parentClassIndex != classIndex) {
                        // Disable in other forms
                        option.prop('disabled', true);
                        let currentText = option.text();
                        if (!currentText.includes('(Has Sessions)')) {
                            option.text(currentText.replace(' (Has Timetable)', '') + ' (Has Sessions)');
                        }
                    } else if (parentClassIndex == classIndex) {
                        // Enable in current form
                        option.prop('disabled', false);
                    }
                }
            });
        });
    }

    // Check if a class has any sessions
    function checkClassHasSessions(classIndex) {
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        let hasSessions = false;
        
        days.forEach(function(day) {
            if ($(`#sessions-${classIndex}-${day} .session-form-row`).length > 0) {
                hasSessions = true;
            }
            if ($(`#prepo-${classIndex}-${day} .session-form-row`).length > 0) {
                hasSessions = true;
            }
        });
        
        return hasSessions;
    }

    // Check if subclass has timetable and disable if it does
    function checkSubclassTimetableStatus(subclassID, classIndex) {
        $.ajax({
            url: '/admin/check-subclass-has-timetable',
            method: 'GET',
            data: { subclassID: subclassID },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.hasTimetable) {
                    const select = $(`.subclass-select[data-class-index="${classIndex}"]`);
                    select.prop('disabled', true);
                    select.after('<small class="text-warning d-block mt-1">This class already has a timetable. Please edit existing timetable instead.</small>');
                }
            },
            error: function(xhr) {
                console.error('Error checking timetable status:', xhr);
            }
        });
    }

    // Remove class form
    $(document).on('click', '.remove-class-form', function() {
        $(this).closest('.class-timetable-form').remove();
        if ($('.class-timetable-form').length === 0) {
            $('#saveAllTimetablesBtn').hide();
        }
    });

    // Initialize days for a class
    function initializeDaysForClass(classIndex, subclassID) {
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        const container = $(`#days-container-${classIndex}`);
        container.empty();

        // Get break times from definition
        const breakTimes = timetableDefinition && timetableDefinition.break_times ? timetableDefinition.break_times : [];
        
        // Sort break times by start time
        const sortedBreakTimes = [...breakTimes].sort((a, b) => {
            return a.start_time.localeCompare(b.start_time);
        });

        days.forEach(function(day) {
            // Build break times display HTML
            let breakTimesHtml = '';
            if (sortedBreakTimes.length > 0) {
                breakTimesHtml = '<div class="break-times-section mb-3 p-2" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">';
                breakTimesHtml += '<h6 class="small mb-2"><i class="bi bi-clock"></i> Break Times</h6>';
                sortedBreakTimes.forEach(function(breakTime, index) {
                    breakTimesHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2 break-time-item" 
                             style="background-color: #fff; padding: 8px; border-radius: 4px;">
                            <div>
                                <strong>Break ${index + 1}:</strong> ${breakTime.start_time} - ${breakTime.end_time}
                            </div>
                            <button type="button" class="btn btn-sm btn-warning add-session-after-break-btn" 
                                    data-class-index="${classIndex}" 
                                    data-day="${day}" 
                                    data-break-end="${breakTime.end_time}"
                                    data-is-prepo="0">
                                <i class="bi bi-plus-circle"></i> Add Session After Break
                            </button>
                        </div>
                    `;
                });
                breakTimesHtml += '</div>';
            }

            const dayHtml = `
                <div class="card mb-2 day-timetable" data-day="${day}" data-class-index="${classIndex}">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">${day}</h6>
                    </div>
                    <div class="card-body">
                        <!-- Prepo Session (if enabled) - BEFORE regular sessions -->
                        ${timetableDefinition && (timetableDefinition.has_prepo == 1 || timetableDefinition.has_prepo === true || timetableDefinition.has_prepo == '1') ? `
                        <div class="prepo-session mb-3" id="prepo-${classIndex}-${day}">
                            <h6 class="small mb-2"><i class="bi bi-clock-history"></i> Prepo Session</h6>
                            <!-- Prepo sessions will be added here -->
                        </div>
                        <!-- Add Prepo Session Button - Below prepo sessions -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-success add-prepo-btn" 
                                    data-class-index="${classIndex}" data-day="${day}" data-is-prepo="1">
                                <i class="bi bi-plus"></i> Add Prepo Session
                            </button>
                        </div>
                        ` : ''}
                        
                        <!-- Regular Sessions -->
                        <div class="regular-sessions mb-3" id="sessions-${classIndex}-${day}">
                            <h6 class="small mb-2"><i class="bi bi-calendar-check"></i> Regular Sessions</h6>
                            <!-- Sessions will be added here -->
                        </div>
                        <!-- Add Session Button - Below sessions -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-primary-custom add-session-btn" 
                                    data-class-index="${classIndex}" data-day="${day}" data-is-prepo="0">
                                <i class="bi bi-plus"></i> Add Session
                            </button>
                        </div>

                        <!-- Break Times Display with "After Break" buttons -->
                        ${breakTimesHtml}
                    </div>
                </div>
            `;
            container.append(dayHtml);
        });

        // Load subjects for this subclass
        loadSubjectsForSubclass(classIndex, subclassID);
    }

    // Load subjects for subclass
    function loadSubjectsForSubclass(classIndex, subclassID) {
        $.ajax({
            url: '/get_subclass_subjects_timetable',
            method: 'GET',
            data: { subclassID: subclassID },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.subjects) {
                    // Store subjects with teacher info for this class index
                    const subjectsData = response.subjects.map(subj => ({
                        class_subjectID: subj.class_subjectID,
                        subjectID: subj.subjectID,
                        name: subj.subject_name,
                        subject_name: subj.subject_name,
                        teacherID: subj.teacherID,
                        teacher_name: subj.teacher_name
                    }));
                    $(`.class-timetable-form[data-class-index="${classIndex}"]`).data('subjects', subjectsData);
                    $(`.class-timetable-form[data-class-index="${classIndex}"]`).data('subclassID', subclassID);
                    
                    // Load already selected subjects for this subclass to disable them
                    loadSelectedSubjectsForSubclass(classIndex, subclassID);
                    
                    // Update all existing session subject dropdowns for this class
                    updateAllSubjectDropdowns(classIndex);
                }
            },
            error: function(xhr) {
                console.error('Error loading subjects:', xhr);
            }
        });
    }

    // Get live selected subjects (from current form, not DB)
    function getLiveSelectedSubjects(classIndex, day, isPrepo, excludeSessionID) {
        const selectedSubjects = [];
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        days.forEach(function(checkDay) {
            // Check regular sessions
            $(`#sessions-${classIndex}-${checkDay} .session-form-row`).each(function() {
                const sessionID = $(this).data('session-id');
                if (sessionID && sessionID != excludeSessionID) {
                    const subjectID = $(this).find('.session-subject').val();
                    if (subjectID) {
                        selectedSubjects.push(subjectID);
                    }
                }
            });

            // Check prepo sessions
            $(`#prepo-${classIndex}-${checkDay} .session-form-row`).each(function() {
                const sessionID = $(this).data('session-id');
                if (sessionID && sessionID != excludeSessionID) {
                    const subjectID = $(this).find('.session-subject').val();
                    if (subjectID) {
                        selectedSubjects.push(subjectID);
                    }
                }
            });
        });
        
        return selectedSubjects;
    }

    // Update all subject dropdowns for a class (when new session is added or subject is changed)
    // Note: Subjects can now be selected multiple times (even on same day) - no restrictions
    function updateAllSubjectDropdowns(classIndex) {
        const subjects = $(`.class-timetable-form[data-class-index="${classIndex}"]`).data('subjects') || [];
        
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        days.forEach(function(day) {
            // Update regular session dropdowns
            $(`#sessions-${classIndex}-${day} .session-subject`).each(function() {
                const currentValue = $(this).val();
                
                // Rebuild options (no restrictions - subjects can be selected multiple times)
                let options = '<option value="">Select Subject</option>';
                subjects.forEach(function(subj) {
                    const subjectID = subj.class_subjectID || subj.subjectID;
                    const selected = subjectID == currentValue ? 'selected' : '';
                    options += `<option value="${subjectID}" ${selected} data-teacher-id="${subj.teacherID || ''}" data-teacher-name="${subj.teacher_name || 'Not Assigned'}">${subj.subject_name || subj.name}</option>`;
                });
                
                $(this).html(options);
            });

            // Update prepo session dropdowns
            $(`#prepo-${classIndex}-${day} .session-subject`).each(function() {
                const currentValue = $(this).val();
                
                // Rebuild options (no restrictions - subjects can be selected multiple times)
                let options = '<option value="">Select Subject</option>';
                subjects.forEach(function(subj) {
                    const subjectID = subj.class_subjectID || subj.subjectID;
                    const selected = subjectID == currentValue ? 'selected' : '';
                    options += `<option value="${subjectID}" ${selected} data-teacher-id="${subj.teacherID || ''}" data-teacher-name="${subj.teacher_name || 'Not Assigned'}">${subj.subject_name || subj.name}</option>`;
                });
                
                const wasSelected = $(this).val(); // Store current selection
                $(this).html(options);
                
                // Restore selection and trigger teacher display if subject was selected
                if (wasSelected) {
                    $(this).val(wasSelected);
                    // Trigger change to display teacher
                    $(this).trigger('change');
                }
            });
        });
    }

    // Load already selected subjects for subclass to disable them
    function loadSelectedSubjectsForSubclass(classIndex, subclassID) {
        $.ajax({
            url: '/admin/get-selected-subjects-for-subclass',
            method: 'GET',
            data: { subclassID: subclassID },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.selectedSubjects) {
                    $(`.class-timetable-form[data-class-index="${classIndex}"]`).data('selectedSubjects', response.selectedSubjects);
                }
            },
            error: function(xhr) {
                console.error('Error loading selected subjects:', xhr);
            }
        });
    }

    // Handle Add Session button
    $(document).on('click', '.add-session-btn, .add-prepo-btn, .add-session-after-break-btn', function() {
        const classIndex = $(this).data('class-index');
        const day = $(this).data('day');
        const isPrepo = $(this).data('is-prepo') == 1;
        const breakEndTime = $(this).data('break-end'); // For "After Break" button
        
        const subclassSelect = $(`.subclass-select[data-class-index="${classIndex}"]`);
        const subclassID = subclassSelect.val();
        
        if (!subclassID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select Class First',
                    text: 'Please select a class/subclass first before adding sessions'
                });
            } else {
                alert('Please select a class/subclass first');
            }
            return;
        }

        // Get subjects for this subclass
        const subjects = $(`.class-timetable-form[data-class-index="${classIndex}"]`).data('subjects') || [];
        
        // Get session types from definition
        const sessionTypes = timetableDefinition ? timetableDefinition.session_types : [];
        
        // Create session form with optional break end time
        addSessionForm(classIndex, day, isPrepo, subjects, sessionTypes, timetableDefinition, breakEndTime);
    });

    // Add session form
    function addSessionForm(classIndex, day, isPrepo, subjects, sessionTypes, definition, breakEndTime) {
        const sessionCounter = Date.now();
        
        // Load session types from database if not already loaded
        if (!sessionTypes || sessionTypes.length === 0) {
            loadSessionTypes(function(types) {
                renderSessionForm(classIndex, day, isPrepo, subjects, types, definition, sessionCounter, breakEndTime);
            });
            return;
        }
        
        renderSessionForm(classIndex, day, isPrepo, subjects, sessionTypes, definition, sessionCounter, breakEndTime);
    }

    // Render session form
    function renderSessionForm(classIndex, day, isPrepo, subjects, sessionTypes, definition, sessionCounter, breakEndTime) {
        const sessionTypeOptions = sessionTypes.map(st => 
            `<option value="${st.session_typeID}" data-minutes="${st.minutes}">${st.name} (${st.minutes} min)</option>`
        ).join('');

        // Get selected subjects for this subclass from DB
        const selectedSubjects = $(`.class-timetable-form[data-class-index="${classIndex}"]`).data('selectedSubjects') || [];
        const selectedSubjectIDs = selectedSubjects.map(s => s.class_subjectID || s.subjectID);

        // Note: Subjects can now be selected multiple times (no restrictions)

        const subjectOptions = subjects.length > 0 ? subjects.map(subj => {
            const subjectID = subj.class_subjectID || subj.subjectID;
            return `<option value="${subjectID}" data-teacher-id="${subj.teacherID || ''}" data-teacher-name="${subj.teacher_name || 'Not Assigned'}">${subj.subject_name || subj.name}</option>`;
        }).join('') : '<option value="">No subjects available</option>';

        // Get session start/end times from definition
        const sessionStart = definition ? definition.session_start_time : '08:00';
        const sessionEnd = definition ? definition.session_end_time : '15:00';
        const prepoStart = definition && definition.has_prepo ? definition.prepo_start_time : '';
        const prepoEnd = definition && definition.has_prepo ? definition.prepo_end_time : '';

        // Determine initial start time - if breakEndTime is provided, use it
        const initialStartTime = breakEndTime ? breakEndTime : (isPrepo ? prepoStart : sessionStart);
        
        const sessionHtml = `
            <div class="card mb-2 session-form-row" data-session-id="${sessionCounter}" data-is-prepo="${isPrepo}" data-break-end-time="${breakEndTime || ''}">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Subject <span class="text-danger">*</span></label>
                            <select class="form-control session-subject" required>
                                <option value="">Select Subject</option>
                                ${subjectOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Session Type <span class="text-danger">*</span></label>
                            <select class="form-control session-type-select" required>
                                <option value="">Select Type</option>
                                ${sessionTypeOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control session-start-time" 
                                   value="${initialStartTime}" required>
                        </div>
                        <div class="col-md-2">
                            <label>End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control session-end-time" 
                                   value="${isPrepo ? prepoEnd : sessionEnd}" required>
                        </div>
                        <div class="col-md-2">
                            <label>Teacher</label>
                            <input type="hidden" class="session-teacher-id" value="">
                            <input type="text" class="form-control session-teacher-display" readonly 
                                   placeholder="Select subject first" value="">
                            <small class="text-muted session-teacher-info"></small>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-danger remove-session" data-class-index="${classIndex}" data-day="${day}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${breakEndTime ? '<small class="text-info"><i class="bi bi-info-circle"></i> Session after break</small>' : ''}
                    <div class="alert alert-danger mt-2 session-validation-error" style="display: none;"></div>
                </div>
            </div>
        `;

        const container = isPrepo ? 
            $(`#prepo-${classIndex}-${day}`) : 
            $(`#sessions-${classIndex}-${day}`);
        
        container.append(sessionHtml);

        const sessionRow = container.find(`.session-form-row[data-session-id="${sessionCounter}"]`);

        // Auto-display teacher when subject is selected - use event delegation to ensure it works for all sessions
        sessionRow.find('.session-subject').off('change').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const teacherID = selectedOption.data('teacher-id');
            const teacherName = selectedOption.data('teacher-name');
            
            // Get the session row for this specific subject select
            const currentSessionRow = $(this).closest('.session-form-row');
            
            if (teacherID && teacherName) {
                currentSessionRow.find('.session-teacher-id').val(teacherID);
                currentSessionRow.find('.session-teacher-display').val(teacherName);
                currentSessionRow.find('.session-teacher-info').text('Assigned to this subject');
                
                // Validate teacher conflicts
                const classIdx = currentSessionRow.closest('.day-timetable').data('class-index');
                const dayName = currentSessionRow.closest('.day-timetable').data('day');
                validateTeacherConflict(currentSessionRow, classIdx, dayName);
            } else {
                currentSessionRow.find('.session-teacher-id').val('');
                currentSessionRow.find('.session-teacher-display').val('');
                currentSessionRow.find('.session-teacher-info').text('');
            }
            
            // Update all subject dropdowns to reflect new selection
            const classIdx = currentSessionRow.closest('.day-timetable').data('class-index');
            updateAllSubjectDropdowns(classIdx);
        });

        // Auto-calculate start time based on previous sessions or break end time
        if (breakEndTime && !isPrepo) {
            // If adding after break, start from break end time
            sessionRow.find('.session-start-time').val(breakEndTime);
        } else {
            calculateSessionStartTime(sessionRow, classIndex, day, isPrepo, definition);
        }

        // Auto-calculate end time when session type changes
        sessionRow.find('.session-type-select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const sessionTypeName = selectedOption.text().toLowerCase();
            const isFreeSession = sessionTypeName.includes('free');
            
            // Show/hide subject and teacher fields based on session type
            // Subject is in first col-md-3, Teacher is in col-md-2 after Session Type, Start Time, End Time
            const rowElement = sessionRow.find('.row').first();
            const subjectCol = rowElement.children('.col-md-3').first();
            const teacherCol = rowElement.children('.col-md-2').eq(3);
            
            if (isFreeSession) {
                // Hide subject and teacher for free sessions
                subjectCol.hide();
                teacherCol.hide();
                // Remove required attribute
                sessionRow.find('.session-subject').removeAttr('required');
                sessionRow.find('.session-teacher-id').removeAttr('required');
                // Clear values
                sessionRow.find('.session-subject').val('');
                sessionRow.find('.session-teacher-id').val('');
                sessionRow.find('.session-teacher-display').val('');
            } else {
                // Show subject and teacher for non-free sessions
                subjectCol.show();
                teacherCol.show();
                // Add required attribute
                sessionRow.find('.session-subject').attr('required', 'required');
            }
            
            // Check if this is an "after break" session
            const breakEndTime = sessionRow.data('break-end-time');
            
            // Get the correct start time - use break end time if available, otherwise use current start time
            let startTime;
            if (breakEndTime) {
                // Force use break end time for "after break" sessions - NEVER use previous session end time
                startTime = breakEndTime;
                // Always set start time to break end time for "after break" sessions
                sessionRow.find('.session-start-time').val(breakEndTime);
            } else {
                // Normal session - use calculated start time from previous sessions
                startTime = sessionRow.find('.session-start-time').val();
            }
            
            const minutes = selectedOption.data('minutes');
            if (startTime && minutes) {
                const endTime = calculateEndTime(startTime, minutes);
                sessionRow.find('.session-end-time').val(endTime);
                // Validate after calculating
                validateSession(sessionRow, classIndex, day);
            }
        });
        
        // Also handle when start time is manually changed - but preserve break end time for "after break" sessions
        sessionRow.find('.session-start-time').on('change', function() {
            const breakEndTime = sessionRow.data('break-end-time');
            if (breakEndTime) {
                // Always force break end time for "after break" sessions
                const newValue = $(this).val();
                if (newValue !== breakEndTime) {
                    // User tried to change it, force it back to break end time
                    $(this).val(breakEndTime);
                }
                
                // If session type is selected, recalculate end time using break end time
                const sessionTypeSelect = sessionRow.find('.session-type-select');
                const selectedOption = sessionTypeSelect.find('option:selected');
                if (selectedOption.length > 0 && selectedOption.val()) {
                    const minutes = selectedOption.data('minutes');
                    if (minutes) {
                        const endTime = calculateEndTime(breakEndTime, minutes);
                        sessionRow.find('.session-end-time').val(endTime);
                    }
                }
            } else {
                // Normal session - calculate based on current start time
                const sessionTypeSelect = sessionRow.find('.session-type-select');
                const selectedOption = sessionTypeSelect.find('option:selected');
                if (selectedOption.length > 0 && selectedOption.val()) {
                    const minutes = selectedOption.data('minutes');
                    const currentStartTime = $(this).val();
                    if (minutes && currentStartTime) {
                        const endTime = calculateEndTime(currentStartTime, minutes);
                        sessionRow.find('.session-end-time').val(endTime);
                    }
                }
            }
            validateSession(sessionRow, classIndex, day);
        });
        
        // Recalculate next sessions when this session changes - BUT skip for "after break" sessions
        sessionRow.find('.session-start-time, .session-end-time, .session-type-select').on('change', function() {
            validateSession(sessionRow, classIndex, day);
            
            // Don't recalculate subsequent sessions for "after break" sessions
            // They should remain independent
            const breakEndTime = sessionRow.data('break-end-time');
            if (!breakEndTime) {
                // Only recalculate for normal sessions
                recalculateSubsequentSessions(classIndex, day, isPrepo);
            }
        });
        
        // Update all dropdowns when session is added
        updateAllSubjectDropdowns(classIndex);
    }

    // Calculate session start time based on previous sessions
    function calculateSessionStartTime(sessionRow, classIndex, day, isPrepo, definition) {
        const container = isPrepo ? 
            $(`#prepo-${classIndex}-${day}`) : 
            $(`#sessions-${classIndex}-${day}`);
        
        const allSessions = container.find('.session-form-row').toArray();
        const currentIndex = allSessions.indexOf(sessionRow[0]);
        
        if (currentIndex === 0) {
            // First session - use definition start time or prepo start time
            const startTime = isPrepo ? 
                (definition && definition.has_prepo ? definition.prepo_start_time : '') :
                (definition ? definition.session_start_time : '08:00');
            sessionRow.find('.session-start-time').val(startTime);
        } else {
            // Get end time of previous session
            const previousSession = $(allSessions[currentIndex - 1]);
            const previousEndTime = previousSession.find('.session-end-time').val();
            if (previousEndTime) {
                sessionRow.find('.session-start-time').val(previousEndTime);
            }
        }
    }

    // Recalculate subsequent sessions - but skip "after break" sessions
    function recalculateSubsequentSessions(classIndex, day, isPrepo) {
        const container = isPrepo ? 
            $(`#prepo-${classIndex}-${day}`) : 
            $(`#sessions-${classIndex}-${day}`);
        
        const allSessions = container.find('.session-form-row').toArray();
        
        for (let i = 1; i < allSessions.length; i++) {
            const currentSession = $(allSessions[i]);
            
            // Skip "after break" sessions - they should remain independent
            const breakEndTime = currentSession.data('break-end-time');
            if (breakEndTime) {
                continue; // Skip this session, it's an "after break" session
            }
            
            const previousSession = $(allSessions[i - 1]);
            
            // Also skip if previous session is "after break" - don't chain from it
            const prevBreakEndTime = previousSession.data('break-end-time');
            if (prevBreakEndTime) {
                continue; // Skip this session, previous one is "after break"
            }
            
            const previousEndTime = previousSession.find('.session-end-time').val();
            
            if (previousEndTime) {
                currentSession.find('.session-start-time').val(previousEndTime);
                
                // Recalculate end time if session type is selected
                const sessionTypeSelect = currentSession.find('.session-type-select');
                const minutes = sessionTypeSelect.find('option:selected').data('minutes');
                if (minutes) {
                    const newEndTime = calculateEndTime(previousEndTime, minutes);
                    currentSession.find('.session-end-time').val(newEndTime);
                    validateSession(currentSession, classIndex, day);
                }
            }
        }
    }

    // Validate teacher conflict - only check same day and overlapping times
    function validateTeacherConflict(sessionRow, classIndex, day) {
        const teacherID = sessionRow.find('.session-teacher-id').val();
        const startTime = sessionRow.find('.session-start-time').val();
        const endTime = sessionRow.find('.session-end-time').val();
        const errorDiv = sessionRow.find('.session-validation-error');
        
        if (!teacherID || !startTime || !endTime) {
            return;
        }

        // Only check conflicts on the SAME DAY
        let hasConflict = false;
        let conflictMessages = [];

        // Check regular sessions on the same day
        $(`#sessions-${classIndex}-${day} .session-form-row`).each(function() {
            if ($(this)[0] === sessionRow[0]) return; // Skip self
            
            const otherTeacherID = $(this).find('.session-teacher-id').val();
            const otherStart = $(this).find('.session-start-time').val();
            const otherEnd = $(this).find('.session-end-time').val();
            
            if (otherTeacherID == teacherID && otherStart && otherEnd) {
                // Check if times overlap
                if ((startTime >= otherStart && startTime < otherEnd) ||
                    (endTime > otherStart && endTime <= otherEnd) ||
                    (startTime <= otherStart && endTime >= otherEnd)) {
                    hasConflict = true;
                    const conflictMsg = `Teacher has a session on ${day} from ${otherStart} to ${otherEnd}`;
                    // Only add if not already in array (avoid duplicates)
                    if (!conflictMessages.includes(conflictMsg)) {
                        conflictMessages.push(conflictMsg);
                    }
                }
            }
        });

        // Check prepo sessions on the same day
        $(`#prepo-${classIndex}-${day} .session-form-row`).each(function() {
            if ($(this)[0] === sessionRow[0]) return; // Skip self
            
            const otherTeacherID = $(this).find('.session-teacher-id').val();
            const otherStart = $(this).find('.session-start-time').val();
            const otherEnd = $(this).find('.session-end-time').val();
            
            if (otherTeacherID == teacherID && otherStart && otherEnd) {
                // Check if times overlap
                if ((startTime >= otherStart && startTime < otherEnd) ||
                    (endTime > otherStart && endTime <= otherEnd) ||
                    (startTime <= otherStart && endTime >= otherEnd)) {
                    hasConflict = true;
                    const conflictMsg = `Teacher has a prepo session on ${day} from ${otherStart} to ${otherEnd}`;
                    // Only add if not already in array (avoid duplicates)
                    if (!conflictMessages.includes(conflictMsg)) {
                        conflictMessages.push(conflictMsg);
                    }
                }
            }
        });

        // Also check other classes on the same day (teacher might teach multiple classes)
        $('.class-timetable-form').each(function() {
            const otherClassIndex = $(this).data('class-index');
            if (otherClassIndex == classIndex) return; // Skip current class
            
            // Check regular sessions
            $(`#sessions-${otherClassIndex}-${day} .session-form-row`).each(function() {
                const otherTeacherID = $(this).find('.session-teacher-id').val();
                const otherStart = $(this).find('.session-start-time').val();
                const otherEnd = $(this).find('.session-end-time').val();
                
                if (otherTeacherID == teacherID && otherStart && otherEnd) {
                    // Check if times overlap
                    if ((startTime >= otherStart && startTime < otherEnd) ||
                        (endTime > otherStart && endTime <= otherEnd) ||
                        (startTime <= otherStart && endTime >= otherEnd)) {
                        hasConflict = true;
                        const otherSubclass = $(`.subclass-select[data-class-index="${otherClassIndex}"] option:selected`).text();
                        const conflictMsg = `Teacher has a session in ${otherSubclass} on ${day} from ${otherStart} to ${otherEnd}`;
                        // Only add if not already in array (avoid duplicates)
                        if (!conflictMessages.includes(conflictMsg)) {
                            conflictMessages.push(conflictMsg);
                        }
                    }
                }
            });

            // Check prepo sessions
            $(`#prepo-${otherClassIndex}-${day} .session-form-row`).each(function() {
                const otherTeacherID = $(this).find('.session-teacher-id').val();
                const otherStart = $(this).find('.session-start-time').val();
                const otherEnd = $(this).find('.session-end-time').val();
                
                if (otherTeacherID == teacherID && otherStart && otherEnd) {
                    // Check if times overlap
                    if ((startTime >= otherStart && startTime < otherEnd) ||
                        (endTime > otherStart && endTime <= otherEnd) ||
                        (startTime <= otherStart && endTime >= otherEnd)) {
                        hasConflict = true;
                        const otherSubclass = $(`.subclass-select[data-class-index="${otherClassIndex}"] option:selected`).text();
                        const conflictMsg = `Teacher has a prepo session in ${otherSubclass} on ${day} from ${otherStart} to ${otherEnd}`;
                        // Only add if not already in array (avoid duplicates)
                        if (!conflictMessages.includes(conflictMsg)) {
                            conflictMessages.push(conflictMsg);
                        }
                    }
                }
            });
        });

        // Get unique conflict messages (avoid duplicates)
        const uniqueConflictMessages = [...new Set(conflictMessages)];
        
        if (hasConflict && uniqueConflictMessages.length > 0) {
            // Get current HTML to preserve non-conflict messages
            let currentHtml = errorDiv.html() || '';
            
            // Split by <br> and filter out old teacher conflict messages
            const lines = currentHtml.split('<br>').filter(function(line) {
                const trimmed = line.trim();
                return trimmed && !trimmed.includes('Teacher has a session') && !trimmed.includes('Teacher has a prepo session');
            });
            
            // Combine non-conflict messages with new conflict messages
            const allMessages = [...lines, ...uniqueConflictMessages];
            const finalHtml = allMessages.join('<br>');
            
            // Only update if content actually changed (prevent infinite loop)
            if (errorDiv.html() !== finalHtml) {
                errorDiv.html(finalHtml).show();
            }
        } else {
            // Remove teacher conflict messages if no conflict
            let currentHtml = errorDiv.html();
            if (currentHtml) {
                const filtered = currentHtml.split('<br>').filter(function(line) {
                    const trimmed = line.trim();
                    return trimmed && !trimmed.includes('Teacher has a session') && !trimmed.includes('Teacher has a prepo session');
                }).join('<br>');
                
                // Only update if content actually changed (prevent infinite loop)
                if (errorDiv.html() !== filtered) {
                    if (filtered.trim()) {
                        errorDiv.html(filtered).show();
                    } else {
                        errorDiv.hide();
                    }
                }
            }
        }
    }

    // Calculate end time from start time and minutes
    function calculateEndTime(startTime, minutes) {
        const [hours, mins] = startTime.split(':').map(Number);
        const totalMinutes = hours * 60 + mins + minutes;
        const endHours = Math.floor(totalMinutes / 60);
        const endMins = totalMinutes % 60;
        return `${String(endHours).padStart(2, '0')}:${String(endMins).padStart(2, '0')}`;
    }

    // Remove session
    $(document).on('click', '.remove-session', function() {
        const sessionRow = $(this).closest('.session-form-row');
        const classIndex = $(this).data('class-index') || $(this).closest('.class-timetable-form').data('class-index');
        const day = $(this).data('day') || $(this).closest('.card').data('day') || $(this).closest('[data-day]').data('day');
        const isPrepo = sessionRow.data('is-prepo') == 1;
        
        sessionRow.remove();
        
        // Update all subject dropdowns after removal
        if (classIndex) {
            updateAllSubjectDropdowns(classIndex);
            // Recalculate subsequent sessions
            if (day) {
                recalculateSubsequentSessions(classIndex, day, isPrepo);
            }
            // Update subclass dropdowns
            updateSubclassDropdowns(classIndex);
        }
    });

    // Validate session (check break times, conflicts, etc.)
    function validateSession(sessionRow, classIndex, day) {
        const startTime = sessionRow.find('.session-start-time').val();
        const endTime = sessionRow.find('.session-end-time').val();
        const errorDiv = sessionRow.find('.session-validation-error');
        
        if (!startTime || !endTime) {
            return;
        }

        let errors = [];

        // Check if end time is after start time
        if (startTime >= endTime) {
            errors.push('End time must be after start time');
        }

        // Check break times conflict
        if (timetableDefinition && timetableDefinition.break_times) {
            timetableDefinition.break_times.forEach(function(bt) {
                if ((startTime >= bt.start_time && startTime < bt.end_time) ||
                    (endTime > bt.start_time && endTime <= bt.end_time) ||
                    (startTime <= bt.start_time && endTime >= bt.end_time)) {
                    errors.push(`Time for this session meets with break time (${bt.start_time} - ${bt.end_time}). Please select another session type or update session type time.`);
                }
            });
        }

        // Check session times within definition bounds
        if (timetableDefinition) {
            const isPrepo = sessionRow.data('is-prepo') == 1;
            if (!isPrepo) {
                if (startTime < timetableDefinition.session_start_time) {
                    errors.push(`Start time must be after ${timetableDefinition.session_start_time}`);
                }
                if (endTime > timetableDefinition.session_end_time) {
                    errors.push(`End time must be before ${timetableDefinition.session_end_time}`);
                }
            }
        }

        // Validate teacher conflict
        validateTeacherConflict(sessionRow, classIndex, day);

        // Display errors
        if (errors.length > 0) {
            const currentHtml = errorDiv.html();
            errorDiv.html((currentHtml ? currentHtml + '<br>' : '') + errors.join('<br>')).show();
        } else {
            // Only hide if no teacher conflict errors
            const teacherConflict = errorDiv.html().includes('Teacher has a session');
            if (!teacherConflict) {
                errorDiv.hide();
            }
        }
    }

    // Handle Save All Timetables button
    $('#saveAllTimetablesBtn').on('click', function() {
        saveAllClassTimetables();
    });

    // Save all class timetables
    function saveAllClassTimetables() {
        const timetables = [];
        let hasErrors = false;
        
        // Clear all previous validation errors
        $('.session-validation-error').hide().html('');
        $('.form-control').removeClass('is-invalid');
        $('.form-select').removeClass('is-invalid');
        $('.subclass-select').removeClass('is-invalid');
        $('.day-timetable').removeClass('border-danger');

        $('.class-timetable-form').each(function() {
            const classIndex = $(this).data('class-index');
            const subclassSelect = $(this).find('.subclass-select');
            const subclassID = subclassSelect.val();
            
            // Validate subclass selection
            if (!subclassID) {
                hasErrors = true;
                subclassSelect.addClass('is-invalid');
                subclassSelect.closest('.form-group').find('.invalid-feedback').remove();
                subclassSelect.after('<div class="invalid-feedback d-block">Please select a class/subclass</div>');
                
                // Scroll to first error
                $('html, body').animate({
                    scrollTop: subclassSelect.offset().top - 100
                }, 500);
                return;
            }

            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            let sessionsByDay = {};
            
            days.forEach(function(day) {
                sessionsByDay[day] = [];
                
                // Collect regular sessions
                $(`#sessions-${classIndex}-${day} .session-form-row`).each(function() {
                    const session = collectSessionData($(this), subclassID, day, false);
                    if (session) {
                        timetables.push(session);
                        sessionsByDay[day].push(session);
                    } else {
                        hasErrors = true;
                        validateSessionInputs($(this), classIndex, day);
                    }
                });
                
                // Collect prepo sessions
                $(`#prepo-${classIndex}-${day} .session-form-row`).each(function() {
                    const session = collectSessionData($(this), subclassID, day, true);
                    if (session) {
                        timetables.push(session);
                        sessionsByDay[day].push(session);
                    } else {
                        hasErrors = true;
                        validateSessionInputs($(this), classIndex, day);
                    }
                });
                
                // Validate: Each day must have at least one session
                if (sessionsByDay[day].length === 0) {
                    hasErrors = true;
                    const dayCard = $(`[data-day="${day}"][data-class-index="${classIndex}"]`);
                    dayCard.addClass('border-danger');
                    dayCard.find('.card-header').append('<small class="text-danger d-block mt-1"><i class="bi bi-exclamation-triangle"></i> Please add at least one session for ' + day + '</small>');
                } else {
                    // Validate: Sessions end time should not exceed definition end time
                    if (timetableDefinition) {
                        let maxEndTime = null;
                        sessionsByDay[day].forEach(function(session) {
                            if (!session.is_prepo) { // Only check regular sessions, not prepo
                                if (!maxEndTime || session.end_time > maxEndTime) {
                                    maxEndTime = session.end_time;
                                }
                            }
                        });
                        
                        // Check if max end time exceeds definition end time
                        if (maxEndTime && timetableDefinition.session_end_time && maxEndTime > timetableDefinition.session_end_time) {
                            hasErrors = true;
                            const dayCard = $(`[data-day="${day}"][data-class-index="${classIndex}"]`);
                            dayCard.addClass('border-danger');
                            const existingError = dayCard.find('.card-header .text-danger');
                            if (existingError.length === 0) {
                                dayCard.find('.card-header').append('<small class="text-danger d-block mt-1"><i class="bi bi-exclamation-triangle"></i> Sessions end time (' + maxEndTime + ') exceeds definition end time (' + timetableDefinition.session_end_time + ')</small>');
                            }
                        }
                        
                        // Check if sessions end before definition end time (optional warning, not blocking)
                        if (maxEndTime && timetableDefinition.session_end_time && maxEndTime < timetableDefinition.session_end_time) {
                            // This is just a warning, not blocking
                            // User can still submit if they want
                        }
                    }
                }
            });
        });

        if (hasErrors || timetables.length === 0) {
            // Scroll to first error field or day card
            const firstError = $('.is-invalid').first();
            const firstDayError = $('.day-timetable.border-danger').first();
            
            if (firstError.length > 0) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            } else if (firstDayError.length > 0) {
                $('html, body').animate({
                    scrollTop: firstDayError.offset().top - 100
                }, 500);
            }
            return;
        }

        const btn = $('#saveAllTimetablesBtn');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

        $.ajax({
            url: '/admin/save-class-session-timetables',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { timetables: timetables },
            success: function(response) {
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'All timetables saved successfully!'
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Failed to save timetables'
                        });
                    }
                }
                btn.prop('disabled', false).html(originalHtml);
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || 'Failed to save timetables';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
                }
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    // Validate session inputs and show inline errors
    function validateSessionInputs(row, classIndex, day) {
        const subjectSelect = row.find('.session-subject');
        const sessionTypeSelect = row.find('.session-type-select');
        const sessionTypeName = sessionTypeSelect.find('option:selected').text().toLowerCase();
        const isFreeSession = sessionTypeName.includes('free');
        const startTimeInput = row.find('.session-start-time');
        const endTimeInput = row.find('.session-end-time');
        const teacherInput = row.find('.session-teacher-id');
        const errorDiv = row.find('.session-validation-error');
        
        let errors = [];
        
        // Clear previous validation classes
        subjectSelect.removeClass('is-invalid');
        sessionTypeSelect.removeClass('is-invalid');
        startTimeInput.removeClass('is-invalid');
        endTimeInput.removeClass('is-invalid');
        teacherInput.removeClass('is-invalid');
        row.find('.session-teacher-display').removeClass('is-invalid');
        
        // Validate each field
        // For free sessions, subject and teacher are not required
        if (!isFreeSession) {
            if (!subjectSelect.val()) {
                subjectSelect.addClass('is-invalid');
                errors.push('Subject is required');
            }
            
            if (!teacherInput.val()) {
                row.find('.session-teacher-display').addClass('is-invalid');
                errors.push('Teacher is required (select subject first)');
            }
        }
        
        if (!sessionTypeSelect.val()) {
            sessionTypeSelect.addClass('is-invalid');
            errors.push('Session type is required');
        }
        
        if (!startTimeInput.val()) {
            startTimeInput.addClass('is-invalid');
            errors.push('Start time is required');
        }
        
        if (!endTimeInput.val()) {
            endTimeInput.addClass('is-invalid');
            errors.push('End time is required');
        }
        
        // Show errors
        if (errors.length > 0) {
            errorDiv.html('<strong>Please fill in:</strong><br>' + errors.join('<br>')).show();
        } else {
            errorDiv.hide();
        }
    }

    // Collect session data
    function collectSessionData(row, subclassID, day, isPrepo) {
        const subjectID = row.find('.session-subject').val();
        const sessionTypeID = row.find('.session-type-select').val();
        const sessionTypeName = row.find('.session-type-select option:selected').text().toLowerCase();
        const isFreeSession = sessionTypeName.includes('free');
        const startTime = row.find('.session-start-time').val();
        const endTime = row.find('.session-end-time').val();
        const teacherID = row.find('.session-teacher-id').val(); // Changed from session-teacher

        // For free sessions, subject and teacher are not required
        if (isFreeSession) {
            if (!sessionTypeID || !startTime || !endTime) {
                return null;
            }
            return {
                subclassID: subclassID,
                class_subjectID: null,
                subjectID: null,
                session_typeID: sessionTypeID,
                day: day,
                start_time: startTime,
                end_time: endTime,
                teacherID: null,
                is_prepo: isPrepo ? 1 : 0
            };
        } else {
            // For non-free sessions, subject and teacher are required
            if (!subjectID || !sessionTypeID || !startTime || !endTime || !teacherID) {
                return null;
            }
            return {
                subclassID: subclassID,
                class_subjectID: subjectID,
                subjectID: null,
                session_typeID: sessionTypeID,
                day: day,
                start_time: startTime,
                end_time: endTime,
                teacherID: teacherID,
                is_prepo: isPrepo ? 1 : 0
            };
        }
    }

    // Initialize: show exam form by default
    $('#examTimetableForm').show();
    $('#classSessionTimetableForm').hide();

    // Handle prepo checkbox
    $('#has_prepo').on('change', function() {
        if ($(this).is(':checked')) {
            $('#prepoTimeSection').show();
            // Only add required if class session timetable is selected
            if ($('input[name="timetable_category"]:checked').val() === 'class') {
                $('#prepo_start_time, #prepo_end_time').prop('required', true).prop('disabled', false);
            }
        } else {
            $('#prepoTimeSection').hide();
            $('#prepo_start_time, #prepo_end_time').prop('required', false);
        }
    });

    // Handle break time count change
    $('#break_time_count').on('change', function() {
        const count = parseInt($(this).val()) || 0;
        generateBreakTimeInputs(count);
    });

    // Generate break time inputs
    function generateBreakTimeInputs(count) {
        const container = $('#breakTimesContainer');
        container.empty();
        
        for (let i = 1; i <= count; i++) {
            const breakHtml = `
                <div class="card mb-2 break-time-row" data-break-index="${i}">
                    <div class="card-body">
                        <h6 class="card-title">Break Time ${i}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="break_${i}_start_time">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control break-start-time" 
                                       id="break_${i}_start_time" 
                                       name="break_${i}_start_time" required>
                            </div>
                            <div class="col-md-6">
                                <label for="break_${i}_end_time">End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control break-end-time" 
                                       id="break_${i}_end_time" 
                                       name="break_${i}_end_time" required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(breakHtml);
        }
    }

    // Session type counter
    let sessionTypeCounter = 0;

    // Handle add session type button
    $('#addSessionTypeBtn').on('click', function() {
        addSessionTypeRow();
    });

    // Add session type row
    function addSessionTypeRow() {
        sessionTypeCounter++;
        const sessionHtml = `
            <div class="card mb-2 session-type-row" data-session-index="${sessionTypeCounter}">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Session Type <span class="text-danger">*</span></label>
                            <select class="form-control session-type-select" required data-index="${sessionTypeCounter}">
                                <option value="">Select Type</option>
                                <option value="single">Single</option>
                                <option value="double">Double</option>
                                <option value="triple">Triple</option>
                                <option value="free">Free</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="custom-name-container-${sessionTypeCounter}" style="display: none;">
                            <label>Custom Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control session-custom-name" 
                                   id="custom-name-${sessionTypeCounter}"
                                   placeholder="e.g., Extended Session">
                        </div>
                        <div class="col-md-3">
                            <label>Minutes <span class="text-danger">*</span></label>
                            <input type="number" class="form-control session-minutes" 
                                   min="1" step="1" required placeholder="e.g., 60">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-session-type">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#sessionTypesContainer').append(sessionHtml);
    }

    // Handle session type select change (show/hide custom name input)
    $(document).on('change', '.session-type-select', function() {
        const selectedValue = $(this).val();
        const index = $(this).data('index');
        const customNameContainer = $(`#custom-name-container-${index}`);
        const customNameInput = $(`#custom-name-${index}`);
        
        if (selectedValue === 'custom') {
            customNameContainer.show();
            customNameInput.prop('required', true);
        } else {
            customNameContainer.hide();
            customNameInput.prop('required', false);
            customNameInput.val(''); // Clear the value
        }
    });

    // Remove session type
    $(document).on('click', '.remove-session-type', function() {
        $(this).closest('.session-type-row').remove();
    });

    // Handle Save Timetable Definition button
    $('#saveDefinitionBtn').on('click', function(e) {
        e.preventDefault();
        
        // Validate required fields
        const sessionStartTime = $('#session_start_time').val();
        const sessionEndTime = $('#session_end_time').val();
        const breakTimeCount = parseInt($('#break_time_count').val()) || 0;
        
        if (!sessionStartTime || !sessionEndTime) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in session start time and end time'
                });
            } else {
                alert('Please fill in session start time and end time');
            }
            return;
        }

        // Validate session end time is after start time
        if (sessionStartTime >= sessionEndTime) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Session end time must be after start time'
                });
            } else {
                alert('Session end time must be after start time');
            }
            return;
        }

        // Validate prepo times if enabled
        if ($('#has_prepo').is(':checked')) {
            const prepoStart = $('#prepo_start_time').val();
            const prepoEnd = $('#prepo_end_time').val();
            
            if (!prepoStart || !prepoEnd) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fill in prepo start and end times'
                    });
                } else {
                    alert('Please fill in prepo start and end times');
                }
                return;
            }

            if (prepoStart >= prepoEnd) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Prepo end time must be after start time'
                    });
                } else {
                    alert('Prepo end time must be after start time');
                }
                return;
            }
        }

        // Validate break times
        let breakTimesValid = true;
        const breakTimes = [];
        for (let i = 1; i <= breakTimeCount; i++) {
            const breakStart = $(`#break_${i}_start_time`).val();
            const breakEnd = $(`#break_${i}_end_time`).val();
            
            if (!breakStart || !breakEnd) {
                breakTimesValid = false;
                break;
            }
            
            if (breakStart >= breakEnd) {
                breakTimesValid = false;
                break;
            }

            breakTimes.push({
                start_time: breakStart,
                end_time: breakEnd
            });
        }

        if (breakTimeCount > 0 && !breakTimesValid) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all break times correctly'
                });
            } else {
                alert('Please fill in all break times correctly');
            }
            return;
        }

        // Validate session types
        const sessionTypes = [];
        let hasSessionTypes = false;
        $('.session-type-row').each(function() {
            const typeSelect = $(this).find('.session-type-select');
            const minutes = $(this).find('.session-minutes').val();
            const selectedType = typeSelect.val();
            
            if (selectedType && minutes) {
                hasSessionTypes = true;
                let typeName = selectedType;
                
                // If custom, get the custom name
                if (selectedType === 'custom') {
                    const index = typeSelect.data('index');
                    const customName = $(`#custom-name-${index}`).val().trim();
                    if (!customName) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: 'Please provide a custom name for custom session type'
                            });
                        } else {
                            alert('Please provide a custom name for custom session type');
                        }
                        return false; // Break the loop
                    }
                    typeName = customName;
                }

                sessionTypes.push({
                    type: selectedType === 'custom' ? 'custom' : selectedType,
                    name: typeName,
                    minutes: parseInt(minutes)
                });
            }
        });

        if (!hasSessionTypes) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please add at least one session type'
                });
            } else {
                alert('Please add at least one session type');
            }
            return;
        }

        // Prepare data for saving
        // Explicitly set has_prepo to true or false (not just when checked)
        const hasPrepoChecked = $('#has_prepo').is(':checked');
        const definitionData = {
            session_start_time: sessionStartTime,
            session_end_time: sessionEndTime,
            has_prepo: hasPrepoChecked ? '1' : '0', // Explicitly send as string '1' or '0'
            prepo_start_time: hasPrepoChecked ? $('#prepo_start_time').val() : null,
            prepo_end_time: hasPrepoChecked ? $('#prepo_end_time').val() : null,
            break_times: breakTimes,
            session_types: sessionTypes
        };

        // Disable button and show loading
        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

        // Save via AJAX
        $.ajax({
            url: '/admin/save-session-timetable-definition',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: definitionData,
            success: function(response) {
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Timetable definition saved successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            // Load definition data for Step 2
                            loadDefinitionData();
                            // Hide definition step and show timetable step
                            $('#sessionDefinitionStep').hide();
                            $('#sessionTimetableStep').show();
                            btn.prop('disabled', false).html(originalHtml);
                        });
                    } else {
                        alert(response.message || 'Timetable definition saved successfully!');
                        $('#sessionDefinitionStep').hide();
                        $('#sessionTimetableStep').show();
                        btn.prop('disabled', false).html(originalHtml);
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Failed to save timetable definition'
                        });
                    } else {
                        alert(response.error || 'Failed to save timetable definition');
                    }
                    btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to save timetable definition';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = 'Validation errors: ' + JSON.stringify(xhr.responseJSON.errors);
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                } else {
                    alert(errorMsg);
                }
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Handle timetable type change
    $('#timetable_type').on('change', function() {
        const type = $(this).val();
        if (type === 'class_specific') {
            $('#class_specific_fields').show();
            $('#school_wide_fields').hide();
            $('#creation_method_group').hide();
            $('#method_info_custom, #method_info_automatic').hide();
            // Set required attributes
            $('#subclassID, #class_subjectID, #exam_date, #start_time, #end_time, #teacherID').prop('required', true);
            $('#exam_start_date, #exam_end_date').prop('required', false);
        } else {
            $('#class_specific_fields').hide();
            $('#school_wide_fields').show();
            $('#creation_method_group').show();
            // Trigger creation method change to show appropriate fields
            $('#creation_method').trigger('change');
            // Set required attributes
            $('#subclassID, #class_subjectID, #exam_date, #start_time, #end_time, #teacherID').prop('required', false);
            $('#exam_start_date, #exam_end_date').prop('required', true);
            
            // If exam is already selected, load its dates
            const examID = $('#examID').val();
            if (examID) {
                // Fetch exam details to auto-populate dates
                $.ajax({
                    url: '/get_exam_details_timetable',
                    method: 'GET',
                    data: { examID: examID },
                    success: function(response) {
                        if (response.success && response.exam) {
                            // Auto-populate exam dates
                            if (response.exam.start_date) {
                                $('#exam_start_date').val(response.exam.start_date);
                            }
                            if (response.exam.end_date) {
                                $('#exam_end_date').val(response.exam.end_date);
                            }
                            // Generate days timetable
                            generateDaysTimetable();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading exam details:', xhr);
                    }
                });
            }
            
            // Set up date change handlers
            $('#exam_start_date, #exam_end_date').off('change').on('change', generateDaysTimetable);
        }
    });

    // Handle timetable creation method change
    $('#creation_method').on('change', function() {
        const method = $(this).val();
        if (method === 'custom') {
            $('#method_info_custom').show();
            $('#method_info_automatic').hide();
            $('#custom_timetable_section').show();
            $('#automatic_timetable_section').hide();
            // If dates are already selected, generate days
            if ($('#exam_start_date').val() && $('#exam_end_date').val()) {
                generateDaysTimetable();
            }
        } else {
            $('#method_info_custom').hide();
            $('#method_info_automatic').show();
            $('#custom_timetable_section').hide();
            $('#automatic_timetable_section').show();
        }
    });

    // Handle supervisor assignment method change
    $('#supervisor_assignment_method').on('change', function() {
        const method = $(this).val();
        if (method === 'automatic') {
            $('#supervisor_info_automatic').show();
            $('#supervisor_info_custom').hide();
        } else {
            $('#supervisor_info_automatic').hide();
            $('#supervisor_info_custom').show();
        }
    });

    // Load exam dates when exam is selected
    $('#examID').on('change', function() {
        const examID = $(this).val();
        if (!examID) {
            // Clear dates if no exam selected
            $('#exam_start_date, #exam_end_date').val('');
            $('#days_timetable').html('');
            $('#days_info').hide();
            return;
        }

        // Fetch exam details to auto-populate dates
        $.ajax({
            url: '/get_exam_details_timetable',
            method: 'GET',
            data: { examID: examID },
            success: function(response) {
                if (response.success && response.exam) {
                    // Auto-populate exam dates
                    if (response.exam.start_date) {
                        $('#exam_start_date').val(response.exam.start_date);
                    }
                    if (response.exam.end_date) {
                        $('#exam_end_date').val(response.exam.end_date);
                    }

                    // If school-wide is selected, generate days timetable
                    if ($('#timetable_type').val() === 'school_wide') {
                        generateDaysTimetable();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading exam details:', xhr);
                // Don't show error to user, just log it
            }
        });

        if ($('#timetable_type').val() === 'school_wide') {
            // Set up date change handlers
            $('#exam_start_date, #exam_end_date').off('change').on('change', generateDaysTimetable);
        }
    });

    // Generate days timetable based on date range
    function generateDaysTimetable() {
        const startDate = $('#exam_start_date').val();
        const endDate = $('#exam_end_date').val();

        if (!startDate || !endDate) {
            $('#days_info').show();
            $('#days_timetable').html('');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Range',
                text: 'Start date must be before end date'
            });
            return;
        }

        $('#days_info').hide();

        // Generate days from start to end (excluding weekends)
        const days = [];
        const start = new Date(startDate);
        const end = new Date(endDate);
        const current = new Date(start);

        while (current <= end) {
            const dayOfWeek = current.getDay(); // 0 = Sunday, 6 = Saturday
            if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Skip weekends
                const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const dateStr = current.toISOString().split('T')[0];
                const dayName = dayNames[dayOfWeek];
                
                days.push({
                    date: dateStr,
                    day: dayName
                });
            }
            current.setDate(current.getDate() + 1);
        }

        if (days.length === 0) {
            $('#days_timetable').html('<div class="alert alert-warning">No weekdays found in the selected date range.</div>');
            return;
        }

        // Generate HTML for each day
        let daysHtml = '';
        days.forEach(function(dayData, index) {
            daysHtml += generateDayCard(dayData, index);
        });

        $('#days_timetable').html(daysHtml);
    }

    // Generate day card HTML
    function generateDayCard(dayData, dayIndex) {
        const subjectOptions = getSubjectOptions();
        
        return `
            <div class="card mb-3 day-card" data-date="${dayData.date}" data-day="${dayData.day}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-day"></i> ${dayData.day} (${formatDateDisplay(dayData.date)})
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary add-subject-btn" data-day-index="${dayIndex}">
                        <i class="bi bi-plus-circle"></i> Add Subject
                        </button>
                    </div>
                <div class="card-body">
                    <div class="subjects-container" data-day-index="${dayIndex}">
                        <!-- Subjects will be added here -->
                            </div>
                    <div class="text-center mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary add-subject-btn" data-day-index="${dayIndex}">
                            <i class="bi bi-plus-circle"></i> Add Subject
                        </button>
                        </div>
                            </div>
                        </div>
        `;
    }

    // Get subject options HTML
    function getSubjectOptions() {
        let options = '<option value="">Select Subject</option>';
        @if(isset($schoolSubjects))
            @foreach($schoolSubjects ?? [] as $subject)
                options += `<option value="{{ $subject->subjectID }}">{{ $subject->subject_name }}</option>`;
            @endforeach
        @endif
        return options;
    }

    // Format date for display
    function formatDateDisplay(dateStr) {
        const date = new Date(dateStr);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Add subject to a day
    $(document).on('click', '.add-subject-btn', function() {
        const dayIndex = $(this).data('day-index');
        const dayCard = $(this).closest('.day-card');
        const subjectsContainer = dayCard.find('.subjects-container');
        const subjectCounter = subjectsContainer.children().length;
        
        const subjectHtml = `
            <div class="row mb-2 subject-row" data-day-index="${dayIndex}" data-subject-index="${subjectCounter}">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Subject <span class="text-danger">*</span></label>
                        <select class="form-control day-subject" data-day-index="${dayIndex}" data-subject-index="${subjectCounter}" required>
                            ${getSubjectOptions()}
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Start Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control day-start-time" data-day-index="${dayIndex}" data-subject-index="${subjectCounter}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>End Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control day-end-time" data-day-index="${dayIndex}" data-subject-index="${subjectCounter}" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-danger w-100 remove-subject-btn" data-day-index="${dayIndex}" data-subject-index="${subjectCounter}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        subjectsContainer.append(subjectHtml);
    });

    // Remove subject from a day
    $(document).on('click', '.remove-subject-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Find the closest subject-row and remove it
        const subjectRow = $(this).closest('.subject-row');
        if (subjectRow.length > 0) {
            subjectRow.remove();
        } else {
            // Fallback: try using data attributes
            const dayIndex = $(this).data('day-index');
            const subjectIndex = $(this).data('subject-index');
            if (dayIndex !== undefined && subjectIndex !== undefined) {
                $(`.subject-row[data-day-index="${dayIndex}"][data-subject-index="${subjectIndex}"]`).remove();
            }
        }
    });

    // Initialize date change handlers
    $('#exam_start_date, #exam_end_date').on('change', function() {
        if ($('#timetable_type').val() === 'school_wide') {
            generateDaysTimetable();
        }
    });



    // Select all classes
    $('#selectAllClassesBtn').on('click', function() {
        $('.class-checkbox').prop('checked', true);
        updateClassesTeachersList();
    });

    // Deselect all classes
    $('#deselectAllClassesBtn').on('click', function() {
        $('.class-checkbox').prop('checked', false);
        updateClassesTeachersList();
    });

    // Handle class checkbox change
    $(document).on('change', '.class-checkbox', function() {
        updateClassesTeachersList();
    });

    // Update classes and teachers list
    function updateClassesTeachersList() {
        const selectedClasses = $('.class-checkbox:checked');
        const html = $('#classes_teachers_list');

        if (selectedClasses.length === 0) {
            html.html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> Please select classes first, then assign teachers for each class.</div>');
            return;
        }

        let listHtml = '';
        selectedClasses.each(function() {
            const subclassID = $(this).val();
            const subclassName = $(this).data('subclass-name');

            // Build teacher checkboxes
            let teacherCheckboxes = '';
            @foreach($teachers ?? [] as $teacher)
                teacherCheckboxes += `
                    <div class="form-check mb-2">
                        <input class="form-check-input teacher-checkbox" type="checkbox"
                               value="{{ $teacher->id }}"
                               id="teacher_${subclassID}_{{ $teacher->id }}"
                               data-subclass-id="${subclassID}">
                        <label class="form-check-label" for="teacher_${subclassID}_{{ $teacher->id }}">
                            {{ $teacher->first_name }} {{ $teacher->last_name }}
                            @if($teacher->employee_number)
                                ({{ $teacher->employee_number }})
                            @endif
                        </label>
                    </div>
                `;
            @endforeach

            listHtml += `
                <div class="card mb-3 class-teacher-card" data-subclass-id="${subclassID}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong>${subclassName}</strong>
                        <div>
                            <button type="button" class="btn btn-sm btn-secondary btn-sm select-all-teachers" data-subclass-id="${subclassID}">
                                <i class="bi bi-check-all"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary btn-sm deselect-all-teachers" data-subclass-id="${subclassID}">
                                <i class="bi bi-x-square"></i> Deselect All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <label>Supervising Teachers <span class="text-danger">*</span></label>
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            ${teacherCheckboxes}
                        </div>
                    </div>
                </div>
            `;
        });

        html.html(listHtml);
    }

    // Select all teachers for a class
    $(document).on('click', '.select-all-teachers', function() {
        const subclassID = $(this).data('subclass-id');
        $(`.teacher-checkbox[data-subclass-id="${subclassID}"]`).prop('checked', true);
    });

    // Deselect all teachers for a class
    $(document).on('click', '.deselect-all-teachers', function() {
        const subclassID = $(this).data('subclass-id');
        $(`.teacher-checkbox[data-subclass-id="${subclassID}"]`).prop('checked', false);
    });

    // Load subjects when subclass is selected
    $('#subclassID').on('change', function() {
        const subclassID = $(this).val();
        if (subclassID && $('#timetable_type').val() === 'class_specific') {
            loadSubclassSubjects(subclassID);
        }
    });

    // Load subjects when exam is selected (to get date range)
    $('#examID').on('change', function() {
        const examID = $(this).val();
        if (examID) {
            // Get exam details to set date range
            const examOption = $(this).find('option:selected');
            // Extract dates from option text or make AJAX call
            // For now, we'll set min/max based on exam selection
        }
    });

    // Load subclass subjects
    function loadSubclassSubjects(subclassID) {
        $.ajax({
            url: '/get_subclass_subjects_timetable',
            method: 'GET',
            data: { subclassID: subclassID },
            success: function(response) {
                if (response.success && response.subjects) {
                    let options = '<option value="">Select Subject</option>';
                    response.subjects.forEach(function(subject) {
                        options += `<option value="${subject.class_subjectID}">${subject.subject_name}${subject.subject_code ? ' (' + subject.subject_code + ')' : ''}</option>`;
                    });
                    $('#class_subjectID').html(options);
                } else {
                    $('#class_subjectID').html('<option value="">No subjects found</option>');
                }
            },
            error: function(xhr) {
                $('#class_subjectID').html('<option value="">Error loading subjects</option>');
                console.error('Error loading subjects:', xhr);
            }
        });
    }

    // Load school subjects
    function loadSchoolSubjects() {
        $.ajax({
            url: '/get_school_subjects_timetable',
            method: 'GET',
            success: function(response) {
                if (response.success && response.subjects) {
                    let options = '<option value="">Select Subject</option>';
                    response.subjects.forEach(function(subject) {
                        options += `<option value="${subject.subjectID}">${subject.subject_name}${subject.subject_code ? ' (' + subject.subject_code + ')' : ''}</option>`;
                    });
                    $('#subjectID').html(options);
                } else {
                    $('#subjectID').html('<option value="">No subjects found</option>');
                }
            },
            error: function(xhr) {
                $('#subjectID').html('<option value="">Error loading subjects</option>');
                console.error('Error loading subjects:', xhr);
            }
        });
    }

    // Handle view type change
    $('#view_type_select').on('change', function() {
        const viewType = $(this).val();
        if (viewType === 'class_specific') {
            $('#class_select_group').show();
        } else {
            $('#class_select_group').hide();
        }
    });

    // Load timetable
    $('#loadTimetableBtn').on('click', function() {
        const examID = $('#view_exam_select').val();
        const viewType = $('#view_type_select').val();
        const classID = $('#view_class_select').val();

        if (!examID) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select an examination'
            });
            return;
        }

        if (viewType === 'class_specific' && !classID) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select a class'
            });
            return;
        }

        loadExamTimetable(examID, viewType, classID);
    });

    // Load exam timetable
    function loadExamTimetable(examID, viewType, classID) {
        $('#timetableDisplay').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-3">Loading timetable...</p></div>');

        $.ajax({
            url: '/get_exam_timetables',
            method: 'GET',
            data: {
                examID: examID,
                view_type: viewType,
                classID: classID
            },
            success: function(response) {
                if (response.success && response.timetables) {
                    displayTimetable(response.timetables, viewType, examID);
                } else {
                    $('#timetableDisplay').html('<div class="alert alert-info">No timetable found for the selected criteria.</div>');
                }
            },
            error: function(xhr) {
                $('#timetableDisplay').html('<div class="alert alert-danger">Error loading timetable.</div>');
                console.error('Error loading timetable:', xhr);
            }
        });
    }

    // Display timetable
    function displayTimetable(timetables, viewType, examID) {
        if (timetables.length === 0) {
            $('#timetableDisplay').html('<div class="alert alert-info">No timetable entries found.</div>');
            return;
        }

        // Helper function to format date as DD/MM/YYYY
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        // Helper function to get day name
        function getDayName(dateStr) {
            const date = new Date(dateStr);
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            return days[date.getDay()];
        }

        if (viewType === 'school_wide') {
            // School-wide timetable display
            // Group by date and day
            const groupedByDate = {};
            timetables.forEach(function(tt) {
                const date = tt.exam_date;
                const day = tt.day || getDayName(date);
                
                if (!groupedByDate[date]) {
                    groupedByDate[date] = {
                        day: day,
                        subjects: []
                    };
                }
                
                const subjectName = tt.subject?.subject_name || 'N/A';
                // Format time properly - handle both string and datetime formats
                let startTime = 'N/A';
                let endTime = 'N/A';
                
                if (tt.start_time) {
                    if (typeof tt.start_time === 'string') {
                        // If it's already in HH:mm format, use it directly
                        if (tt.start_time.match(/^\d{2}:\d{2}/)) {
                            startTime = tt.start_time.substring(0, 5);
                        } else {
                            // Try to parse as datetime
                            const timeObj = new Date(tt.start_time);
                            if (!isNaN(timeObj.getTime())) {
                                startTime = String(timeObj.getHours()).padStart(2, '0') + ':' + String(timeObj.getMinutes()).padStart(2, '0');
                            }
                        }
                    } else if (tt.start_time.time) {
                        // If it's an object with time property
                        startTime = tt.start_time.time.substring(0, 5);
                    }
                }
                
                if (tt.end_time) {
                    if (typeof tt.end_time === 'string') {
                        // If it's already in HH:mm format, use it directly
                        if (tt.end_time.match(/^\d{2}:\d{2}/)) {
                            endTime = tt.end_time.substring(0, 5);
                        } else {
                            // Try to parse as datetime
                            const timeObj = new Date(tt.end_time);
                            if (!isNaN(timeObj.getTime())) {
                                endTime = String(timeObj.getHours()).padStart(2, '0') + ':' + String(timeObj.getMinutes()).padStart(2, '0');
                            }
                        }
                    } else if (tt.end_time.time) {
                        // If it's an object with time property
                        endTime = tt.end_time.time.substring(0, 5);
                    }
                }
                
                groupedByDate[date].subjects.push({
                    subjectID: tt.subjectID,
                    subject_name: subjectName,
                    start_time: startTime,
                    end_time: endTime,
                    exam_timetableID: tt.exam_timetableID
                });
            });

            // Get exam details
            const firstEntry = timetables[0];
            const examStartDate = firstEntry.examination?.start_date;
            const examEndDate = firstEntry.examination?.end_date;

            let html = `
                <div class="card mb-3">
                    <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-range"></i> School Wide Exam Timetable
                            ${examStartDate && examEndDate ? `(${formatDate(examStartDate)} - ${formatDate(examEndDate)})` : ''}
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-light me-2" id="downloadTimetablePdf" data-exam-name="${firstEntry.examination?.exam_name || 'Exam'}" data-exam-start="${examStartDate || ''}" data-exam-end="${examEndDate || ''}">
                                <i class="bi bi-download"></i> Download PDF
                            </button>
                            <button class="btn btn-sm btn-warning me-2" id="shuffleTimetableBtn" data-exam-id="${examID}">
                                <i class="bi bi-shuffle"></i> Shuffle Timetable
                            </button>
                            <button class="btn btn-sm btn-danger" id="deleteAllTimetableBtn" data-exam-id="${examID}">
                                <i class="bi bi-trash-fill"></i> Delete ALL Timetable
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" id="timetableTableContent">
                            <table class="table table-bordered timetable-table" id="timetableTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Subject</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Hall Supervisors</th>
                                        <th>Subject Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            // Sort dates
            const sortedDates = Object.keys(groupedByDate).sort();
            
            sortedDates.forEach(function(dateStr) {
                const dateData = groupedByDate[dateStr];
                const formattedDate = formatDate(dateStr);
                const dayName = dateData.day;
                
                // First row with date and day, spanning all subjects
                if (dateData.subjects.length > 0) {
                    const firstSubject = dateData.subjects[0];
                    html += `
                        <tr>
                            <td rowspan="${dateData.subjects.length}">${formattedDate}</td>
                            <td rowspan="${dateData.subjects.length}">${dayName}</td>
                            <td><strong>${firstSubject.subject_name}</strong></td>
                            <td>${firstSubject.start_time}</td>
                            <td>${firstSubject.end_time}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-subject-supervisors-btn" 
                                    data-timetable-id="${firstSubject.exam_timetableID}" 
                                    data-subject-name="${firstSubject.subject_name}"
                                    data-start-time="${firstSubject.start_time}"
                                    data-end-time="${firstSubject.end_time}">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-subject-timetable-btn" 
                                    data-timetable-id="${firstSubject.exam_timetableID}"
                                    data-subject-id="${firstSubject.subjectID}"
                                    data-subject-name="${firstSubject.subject_name}"
                                    data-start-time="${firstSubject.start_time}"
                                    data-end-time="${firstSubject.end_time}"
                                    data-date="${dateStr}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-info swap-subject-btn me-1" 
                                    data-timetable-id="${firstSubject.exam_timetableID}"
                                    data-subject-name="${firstSubject.subject_name}"
                                    data-date="${dateStr}"
                                    data-start-time="${firstSubject.start_time}"
                                    data-end-time="${firstSubject.end_time}">
                                    <i class="bi bi-arrow-left-right"></i> Swap
                                </button>
                                <button class="btn btn-sm btn-danger delete-subject-timetable-btn" 
                                    data-timetable-id="${firstSubject.exam_timetableID}"
                                    data-subject-name="${firstSubject.subject_name}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
                    
                    // Additional rows for more subjects on the same day
                    for (let i = 1; i < dateData.subjects.length; i++) {
                        const subject = dateData.subjects[i];
                        html += `
                            <tr>
                                <td><strong>${subject.subject_name}</strong></td>
                                <td>${subject.start_time}</td>
                                <td>${subject.end_time}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-subject-supervisors-btn" 
                                        data-timetable-id="${subject.exam_timetableID}" 
                                        data-subject-name="${subject.subject_name}"
                                        data-start-time="${subject.start_time}"
                                        data-end-time="${subject.end_time}">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-subject-timetable-btn" 
                                        data-timetable-id="${subject.exam_timetableID}"
                                        data-subject-id="${subject.subjectID}"
                                        data-subject-name="${subject.subject_name}"
                                        data-start-time="${subject.start_time}"
                                        data-end-time="${subject.end_time}"
                                        data-date="${dateStr}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-info swap-subject-btn me-1" 
                                        data-timetable-id="${subject.exam_timetableID}"
                                        data-subject-name="${subject.subject_name}"
                                        data-date="${dateStr}"
                                        data-start-time="${subject.start_time}"
                                        data-end-time="${subject.end_time}">
                                        <i class="bi bi-arrow-left-right"></i> Swap
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-subject-timetable-btn" 
                                        data-timetable-id="${subject.exam_timetableID}"
                                        data-subject-name="${subject.subject_name}">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                }
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            $('#timetableDisplay').html(html);
        } else {
            // Class-specific timetable display (existing logic)
            const firstEntry = timetables[0];
            const examStartDate = firstEntry.examination?.start_date;
            const examEndDate = firstEntry.examination?.end_date;

        let html = `
            <div class="card mb-3">
                <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-range"></i> Class Timetable
                        ${examStartDate && examEndDate ? `(${formatDate(examStartDate)} - ${formatDate(examEndDate)})` : ''}
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-warning me-2" id="shuffleTimetableBtn" data-exam-id="${examID}">
                            <i class="bi bi-shuffle"></i> Shuffle Timetable
                        </button>
                        <button class="btn btn-sm btn-danger" id="deleteAllTimetableBtn" data-exam-id="${examID}">
                            <i class="bi bi-trash-fill"></i> Delete ALL Timetable
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered timetable-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Subject</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Supervising Teacher</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        timetables.forEach(function(entry) {
            const dateStr = entry.exam_date;
            const dayName = getDayName(dateStr);
            const formattedDate = formatDate(dateStr);
                const subjectName = entry.class_subject?.subject?.subject_name || 'N/A';
            const teacherName = entry.teacher ? (entry.teacher.first_name + ' ' + entry.teacher.last_name) : 'N/A';
            const startTime = entry.start_time ? entry.start_time.substring(0, 5) : 'N/A';
            const endTime = entry.end_time ? entry.end_time.substring(0, 5) : 'N/A';

            html += `
                <tr>
                    <td>${formattedDate}</td>
                    <td>${dayName}</td>
                    <td>${subjectName}</td>
                    <td>${startTime}</td>
                    <td>${endTime}</td>
                    <td>${teacherName}</td>
                    <td>
                        <button class="btn btn-sm btn-info swap-subject-btn me-1" 
                            data-timetable-id="${entry.exam_timetableID}"
                            data-subject-name="${subjectName}"
                            data-date="${dateStr}"
                            data-start-time="${startTime}"
                            data-end-time="${endTime}">
                            <i class="bi bi-arrow-left-right"></i> Swap
                        </button>
                        <button class="btn btn-sm btn-danger delete-timetable-btn" data-timetable-id="${entry.exam_timetableID}">
                            <i class="bi bi-trash"></i>
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

        $('#timetableDisplay').html(html);
        }
    }

    // Submit create timetable form
    $('#createTimetableForm').on('submit', function(e) {
        e.preventDefault();

        // Remove required from hidden fields before validation
        const category = $('input[name="timetable_category"]:checked').val();
        if (category === 'exam') {
            // Ensure session fields are not required when exam timetable is selected
            $('#session_start_time, #session_end_time, #prepo_start_time, #prepo_end_time').removeAttr('required').prop('disabled', true);
        }

        const timetableType = $('#timetable_type').val();
        let requestData = {};

        if (timetableType === 'class_specific') {
            // Class specific - simple form data
            requestData = {
                examID: $('#examID').val(),
                subclassID: $('#subclassID').val(),
                exam_date: $('#exam_date').val(),
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val(),
                teacherID: $('#teacherID').val(),
                timetable_type: timetableType,
                class_subjectID: $('#class_subjectID').val(),
                notes: $('#notes').val()
            };
        } else {
            // School wide - check creation method
            const creationMethod = $('#creation_method').val();
            const supervisorMethod = $('#supervisor_assignment_method').val();
            const examStartDate = $('#exam_start_date').val();
            const examEndDate = $('#exam_end_date').val();

            if (!examStartDate || !examEndDate) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select exam start and end dates.',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            if (creationMethod === 'automatic') {
                // Automatic generation - send settings
                requestData = {
                    examID: $('#examID').val(),
                    timetable_type: timetableType,
                    creation_method: 'automatic',
                    exam_start_date: examStartDate,
                    exam_end_date: examEndDate,
                    exam_duration: $('#exam_duration').val() || 120,
                    daily_start_time: $('#daily_start_time').val() || '08:00',
                    daily_end_time: $('#daily_end_time').val() || '12:00',
                    max_exams_per_day: $('#max_exams_per_day').val() || 2,
                    break_start_time: $('#break_start_time').val() || null,
                    break_duration: $('#break_duration').val() || 15,
                    supervisor_assignment_method: supervisorMethod,
                    notes: $('#notes').val()
                };
            } else {
                // Custom/manual entry - collect days with subjects
                const days = [];
                $('.day-card').each(function() {
                const dayCard = $(this);
                const date = dayCard.data('date');
                const day = dayCard.data('day');
                const subjects = [];

                // Collect subjects for this day and check for duplicates
                const seenSubjectsForDay = {}; // Track seen subjects to avoid duplicates
                dayCard.find('.subject-row').each(function() {
                    const subjectID = $(this).find('.day-subject').val();
                    const startTime = $(this).find('.day-start-time').val();
                    const endTime = $(this).find('.day-end-time').val();

                    if (subjectID && startTime && endTime) {
                        // Validate end time is after start time
                        if (startTime >= endTime) {
                            Swal.fire({
                                title: 'Error!',
                                text: `End time must be after start time for ${day}.`,
                                icon: 'error',
                                confirmButtonColor: '#940000'
                            });
                            return false;
                        }

                        // Create unique key for this subject/time combination
                        const subjectKey = `${subjectID}_${startTime}_${endTime}`;
                        
                        // Check if this subject/time combination already exists for this day
                        if (!seenSubjectsForDay[subjectKey]) {
                            seenSubjectsForDay[subjectKey] = true;
                            subjects.push({
                                subjectID: subjectID,
                                start_time: startTime,
                                end_time: endTime
                            });
                        }
                    }
                });

                if (subjects.length > 0) {
                    days.push({
                        date: date,
                        day: day,
                        subjects: subjects
                    });
                }
            });

                if (days.length === 0) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please add at least one subject to at least one day.',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return;
                }

                requestData = {
                    examID: $('#examID').val(),
                    timetable_type: timetableType,
                    creation_method: 'custom',
                    days: days,
                    supervisor_assignment_method: supervisorMethod,
                    notes: $('#notes').val()
                };
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Show progress bar
        const submitBtn = $('#createTimetableForm button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Creating Timetable...');
        
        // Add progress bar to modal
        if ($('#timetableProgressBar').length === 0) {
            $('#createTimetableModal .modal-body').prepend(`
                <div id="timetableProgressBar" class="progress mb-3" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                        Creating Timetable...
                    </div>
                </div>
            `);
        }
        $('#timetableProgressBar').show();

        $.ajax({
            url: '/store_exam_timetable',
            method: 'POST',
            data: JSON.stringify(requestData),
            contentType: 'application/json',
            success: function(response) {
                $('#timetableProgressBar').hide();
                submitBtn.prop('disabled', false).html(originalBtnText);
                $('#createTimetableModal').modal('hide');
                Swal.fire({
                    title: 'Success!',
                    text: response.message || 'Timetable created successfully',
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    $('#createTimetableForm')[0].reset();
                    $('.class-checkbox').prop('checked', false);
                    updateClassesTeachersList();
                    // Reload timetable if viewing
                    const examID = $('#view_exam_select').val();
                    const viewType = $('#view_type_select').val();
                    const classID = $('#view_class_select').val();
                    if (examID) {
                        loadExamTimetable(examID, viewType, classID);
                    }
                });
            },
            error: function(xhr) {
                $('#timetableProgressBar').hide();
                submitBtn.prop('disabled', false).html(originalBtnText);
                console.error('Error creating timetable:', xhr);
                let errorMessage = '';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul class="mb-0">';
                    for (let field in errors) {
                        if (Array.isArray(errors[field])) {
                            errors[field].forEach(function(msg) {
                                errorHtml += '<li>' + msg + '</li>';
                            });
                        } else {
                            errorHtml += '<li>' + errors[field] + '</li>';
                        }
                    }
                    errorHtml += '</ul>';
                    errorMessage = errorHtml;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else {
                    errorMessage = 'Failed to create timetable. Please try again.';
                    if (xhr.status === 0) {
                        errorMessage += ' (Network error - please check your connection)';
                    } else if (xhr.status === 500) {
                        errorMessage += ' (Server error - please check the logs)';
                    }
                }
                
                // Show error in both the form and as an alert
                $('#timetableFormErrors').html(errorMessage).show();
                
                // Also show in SweetAlert for better visibility
                Swal.fire({
                    title: 'Error!',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#940000',
                    width: '600px'
                });
                
                setTimeout(function() {
                    $('#timetableFormErrors').hide();
                }, 15000);
            }
        });
    });

    // Delete timetable
    $(document).on('click', '.delete-timetable-btn', function() {
        const timetableID = $(this).data('timetable-id');

        Swal.fire({
            title: 'Delete Timetable?',
            text: 'Are you sure you want to delete this timetable entry?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/delete_exam_timetable/' + timetableID,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message || 'Timetable deleted successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Reload timetable
                            const examID = $('#view_exam_select').val();
                            const viewType = $('#view_type_select').val();
                            const classID = $('#view_class_select').val();
                            if (examID) {
                                loadExamTimetable(examID, viewType, classID);
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to delete timetable',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Delete all timetable for an exam
    $(document).on('click', '#deleteAllTimetableBtn', function() {
        const examID = $(this).data('exam-id');

        Swal.fire({
            title: '⚠️ Delete ALL Timetable?',
            html: `
                <div class="text-start">
                    <p class="text-danger fw-bold mb-3">This will permanently delete:</p>
                    <ul class="text-start">
                        <li>✘ ALL School-Wide timetable entries</li>
                        <li>✘ ALL Class-Specific timetable entries</li>
                        <li>✘ ALL assigned supervising teachers</li>
                        <li>✘ ALL hall supervisor assignments</li>
                    </ul>
                    <p class="text-warning fw-bold mt-3">⚠️ This action CANNOT be undone!</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, DELETE EVERYTHING!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn-lg',
                cancelButton: 'btn-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/delete_all_exam_timetable/' + examID,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message || 'All timetable entries deleted successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Reload timetable
                            const viewType = $('#view_type_select').val();
                            const classID = $('#view_class_select').val();
                            if (examID) {
                                loadExamTimetable(examID, viewType, classID);
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to delete all timetable entries',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // View supervisors for school-wide timetable
    $(document).on('click', '.view-supervisors-btn', function() {
        const timetableID = $(this).data('timetable-id');
        const date = $(this).data('date');
        const examID = $('#view_exam_select').val(); // Get examID from the view select
        
        // Make AJAX call to get supervisors for all subjects on this date
        $.ajax({
            url: '/get_exam_supervisors',
            method: 'GET',
            data: {
                date: date,
                examID: examID // Pass examID to get all subjects for this date
            },
            success: function(response) {
                if (response.success) {
                    let supervisorHtml = '';
                    
                    // Display date information if available
                    if (response.date) {
                        const displayDate = new Date(response.date);
                        const day = String(displayDate.getDate()).padStart(2, '0');
                        const month = String(displayDate.getMonth() + 1).padStart(2, '0');
                        const year = displayDate.getFullYear();
                        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const dayName = dayNames[displayDate.getDay()];
                        
                        supervisorHtml += '<div class="alert alert-info mb-3">';
                        supervisorHtml += `<strong><i class="bi bi-calendar"></i> Date:</strong> ${dayName}, ${day}/${month}/${year}`;
                        supervisorHtml += '</div>';
                    }
                    
                    if (response.supervisors && response.supervisors.length > 0) {
                        // Display by subject and halls
                        response.supervisors.forEach(function(subject) {
                            // Format time display
                            const startTime = subject.start_time || 'N/A';
                            const endTime = subject.end_time || 'N/A';
                            let timeDisplay = 'N/A';
                            if (startTime !== 'N/A' && endTime !== 'N/A') {
                                // Extract time part if it's a full datetime string
                                const start = startTime.toString().substring(0, 5);
                                const end = endTime.toString().substring(0, 5);
                                timeDisplay = start + ' - ' + end;
                            }
                            
                            supervisorHtml += `<div class="card mb-3">`;
                            supervisorHtml += `<div class="card-header bg-primary-custom text-white">`;
                            supervisorHtml += `<h6 class="mb-0"><i class="bi bi-book"></i> ${subject.subject_name} (${timeDisplay})</h6>`;
                            supervisorHtml += `</div>`;
                            supervisorHtml += `<div class="card-body">`;
                            
                            if (subject.halls && subject.halls.length > 0) {
                                subject.halls.forEach(function(hall) {
                                    supervisorHtml += `<div class="mb-3 pb-2 border-bottom">`;
                                    supervisorHtml += `<strong><i class="bi bi-door-open"></i> ${hall.hall_name}</strong>`;
                                    supervisorHtml += ` <span class="badge bg-secondary">${hall.class_name}</span>`;
                                    supervisorHtml += ` <small class="text-muted">Capacity: ${hall.capacity}, Gender: ${hall.gender_allowed}</small>`;
                                    
                                    if (hall.supervisors && hall.supervisors.length > 0) {
                                        supervisorHtml += `<ul class="mt-2">`;
                                        hall.supervisors.forEach(function(teacher) {
                                            supervisorHtml += `<li>${teacher.teacher_name}`;
                                            if (teacher.teacher_phone) {
                                                supervisorHtml += ` <small class="text-muted">(${teacher.teacher_phone})</small>`;
                                            }
                                            supervisorHtml += `</li>`;
                                        });
                                        supervisorHtml += `</ul>`;
                                    } else {
                                        supervisorHtml += `<p class="text-muted mt-2">No supervisors assigned</p>`;
                                    }
                                    
                                    supervisorHtml += `</div>`;
                                });
                            } else {
                                supervisorHtml += `<p class="text-muted">No halls assigned for this subject</p>`;
                            }
                            
                            supervisorHtml += `</div></div>`;
                        });
                        
                        supervisorHtml += '';
                        
                        // Store data for editing
                        window.supervisorData = {
                            supervisors: response.supervisors,
                            all_teachers: response.all_teachers || [],
                            all_subclasses: response.all_subclasses || []
                        };
                    } else {
                        supervisorHtml += '<div class="alert alert-info">No supervising teachers assigned yet for this date.</div>';
                    }

                    Swal.fire({
                        title: 'Supervising Teachers',
                        html: supervisorHtml,
                        width: '800px',
                        confirmButtonColor: '#940000',
                        didOpen: function() {
                            // Attach edit button handlers
                            $('.edit-supervisor-btn').on('click', function() {
                                const superviseID = $(this).data('supervise-id');
                                const currentTeacherID = $(this).data('teacher-id');
                                const currentSubclassID = $(this).data('subclass-id');
                                
                                showEditSupervisorModal(superviseID, currentTeacherID, currentSubclassID);
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'No Supervisors',
                        text: 'No supervising teachers assigned yet.',
                        icon: 'info',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to load supervising teachers.',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    });

    // Show edit supervisor modal
    function showEditSupervisorModal(superviseID, currentTeacherID, currentSubclassID) {
        const data = window.supervisorData;
        if (!data) {
            Swal.fire({
                title: 'Error!',
                text: 'Data not available. Please refresh and try again.',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            return;
        }

        // Build teacher options
        let teacherOptions = '';
        data.all_teachers.forEach(function(teacher) {
            const selected = teacher.id == currentTeacherID ? 'selected' : '';
            teacherOptions += `<option value="${teacher.id}" ${selected}>${teacher.name}${teacher.employee_number ? ' (' + teacher.employee_number + ')' : ''}</option>`;
        });

        // Build subclass options - show only subclass name
        let subclassOptions = '';
        data.all_subclasses.forEach(function(subclass) {
            const selected = subclass.subclassID == currentSubclassID ? 'selected' : '';
            subclassOptions += `<option value="${subclass.subclassID}" ${selected}>${subclass.subclass_name}</option>`;
        });

        Swal.fire({
            title: 'Edit Supervising Teacher',
            html: `
                <form id="editSupervisorForm">
                    <div class="form-group">
                        <label>Teacher <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_teacher_id" required>
                            ${teacherOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Class <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_subclass_id" required>
                            ${subclassOptions}
                        </select>
                    </div>
                </form>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Update',
            cancelButtonText: 'Cancel',
            preConfirm: function() {
                const teacherID = $('#edit_teacher_id').val();
                const subclassID = $('#edit_subclass_id').val();
                
                if (!teacherID || !subclassID) {
                    Swal.showValidationMessage('Please select both teacher and class');
                    return false;
                }
                
                return {
                    exam_supervise_teacherID: superviseID,
                    teacherID: teacherID,
                    subclassID: subclassID
                };
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                // Update supervisor
                $.ajax({
                    url: '/update_supervise_teacher',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: result.value,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Supervising teacher updated successfully',
                                icon: 'success',
                                confirmButtonColor: '#940000'
                            }).then(function() {
                                // Reload supervisors
                                $('.view-supervisors-btn').first().click();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to update supervising teacher',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    }

    // Edit timetable (placeholder for now)
    $(document).on('click', '.edit-timetable-btn', function() {
        const date = $(this).data('date');
        Swal.fire({
            title: 'Edit Timetable',
            text: 'Edit functionality will be implemented soon.',
            icon: 'info',
            confirmButtonColor: '#940000'
        });
    });

    // Reset form when modal is closed
    $('#createTimetableModal').on('hidden.bs.modal', function() {
        $('#createTimetableForm')[0].reset();
        $('#timetableFormErrors').hide();
        $('#class_subjectID').html('<option value="">Select Class First</option>');
        $('#days_timetable').html('');
        $('#days_info').hide();
        $('#timetable_type').val('class_specific');
        $('#class_specific_fields').show();
        $('#school_wide_fields').hide();
    });

    // View hall supervisors for a specific subject
    $(document).on('click', '.view-subject-supervisors-btn', function() {
        const timetableID = $(this).data('timetable-id');
        const subjectName = $(this).data('subject-name');
        const startTime = $(this).data('start-time');
        const endTime = $(this).data('end-time');
        
        // Show loading
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching hall supervisors',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fetch supervisors for this specific timetable entry
        $.ajax({
            url: '/get_subject_hall_supervisors',
            method: 'GET',
            data: {
                exam_timetableID: timetableID
            },
            success: function(response) {
                Swal.close();
                
                if (response.success && response.supervisors && response.supervisors.length > 0) {
                    // Display in modal
                    let html = `
                        <div class="modal fade" id="subjectSupervisorModal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title">
                                            <i class="bi bi-people"></i> Hall Supervisors
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <strong>Subject:</strong> ${subjectName}<br>
                                            <strong>Time:</strong> ${startTime} - ${endTime}
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Teacher Name</th>
                                                        <th>Hall Number</th>
                                                        <th>Class</th>
                                                        <th>Gender</th>
                                                        <th>Capacity</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                    `;
                    
                    response.supervisors.forEach(function(supervisor, index) {
                        const teacherName = supervisor.teacher ? 
                            `${supervisor.teacher.first_name} ${supervisor.teacher.last_name}` : 'N/A';
                        const hallName = supervisor.exam_hall ? supervisor.exam_hall.hall_name : 'N/A';
                        const className = supervisor.exam_hall && supervisor.exam_hall.class ? 
                            supervisor.exam_hall.class.class_name : 'N/A';
                        
                        // Safe gender handling
                        let gender = 'N/A';
                        if (supervisor.exam_hall && supervisor.exam_hall.gender_allowed) {
                            if (supervisor.exam_hall.gender_allowed === 'both') {
                                gender = 'Mixed';
                            } else {
                                gender = supervisor.exam_hall.gender_allowed.charAt(0).toUpperCase() + supervisor.exam_hall.gender_allowed.slice(1);
                            }
                        }
                        
                        const capacity = supervisor.exam_hall ? supervisor.exam_hall.capacity : 'N/A';
                        
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${teacherName}</strong></td>
                                <td>${hallName}</td>
                                <td>${className}</td>
                                <td>${gender}</td>
                                <td>${capacity} students</td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-hall-supervisor-btn" 
                                        data-supervisor-id="${supervisor.exam_hall_supervisorID}"
                                        data-teacher-id="${supervisor.teacherID}"
                                        data-hall-id="${supervisor.exam_hallID}">
                                        <i class="bi bi-pencil"></i> Change
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
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Remove any existing modal
                    $('#subjectSupervisorModal').remove();
                    
                    // Append and show modal
                    $('body').append(html);
                    $('#subjectSupervisorModal').modal('show');
                    
                    // Clean up on close
                    $('#subjectSupervisorModal').on('hidden.bs.modal', function() {
                        $(this).remove();
                    });
                } else {
                    Swal.fire({
                        title: 'No Supervisors',
                        text: 'No hall supervisors have been assigned for this subject yet.',
                        icon: 'info',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to load hall supervisors',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    });

    // Edit Hall Supervisor
    $(document).on('click', '.edit-hall-supervisor-btn', function() {
        const supervisorID = $(this).data('supervisor-id');
        const currentTeacherID = $(this).data('teacher-id');
        const hallID = $(this).data('hall-id');
        
        // Show loading
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching available teachers',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Get list of teachers with supervise_exams permission
        $.ajax({
            url: '/get_supervise_teachers',
            method: 'GET',
            success: function(response) {
                if (response.success && response.teachers && response.teachers.length > 0) {
                    // Build teacher options
                    let teacherOptions = '';
                    response.teachers.forEach(function(teacher) {
                        const selected = teacher.id == currentTeacherID ? 'selected' : '';
                        teacherOptions += `<option value="${teacher.id}" ${selected}>${teacher.first_name} ${teacher.last_name}</option>`;
                    });
                    
                    Swal.fire({
                        title: 'Change Supervisor',
                        html: `
                            <div class="text-left">
                                <div class="form-group">
                                    <label><strong>Select New Supervising Teacher:</strong></label>
                                    <select id="new_teacher_id" class="form-control">
                                        ${teacherOptions}
                                    </select>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <small><i class="bi bi-info-circle"></i> Select any active teacher to supervise this hall.</small>
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonColor: '#940000',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Change Teacher',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const newTeacherID = $('#new_teacher_id').val();
                            
                            if (!newTeacherID) {
                                Swal.showValidationMessage('Please select a teacher');
                                return false;
                            }
                            
                            if (newTeacherID == currentTeacherID) {
                                Swal.showValidationMessage('Please select a different teacher');
                                return false;
                            }
                            
                            return { teacherID: newTeacherID };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Update supervisor
                            $.ajax({
                                url: `/update_hall_supervisor/${supervisorID}`,
                                method: 'PUT',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: JSON.stringify({
                                    teacher_id: result.value.teacherID
                                }),
                                contentType: 'application/json',
                                success: function(response) {
                                    Swal.fire({
                                        title: 'Updated!',
                                        text: 'Supervisor changed successfully',
                                        icon: 'success',
                                        confirmButtonColor: '#940000'
                                    }).then(() => {
                                        // Close and reopen the supervisors modal to refresh data
                                        $('#subjectSupervisorModal').modal('hide');
                                        
                                        // Re-trigger the view supervisors button
                                        setTimeout(function() {
                                            $(`[data-timetable-id="${response.exam_timetableID}"]`).first().trigger('click');
                                        }, 300);
                                    });
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: xhr.responseJSON?.error || 'Failed to update supervisor',
                                        icon: 'error',
                                        confirmButtonColor: '#940000'
                                    });
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'No Teachers Available',
                        text: 'No active teachers found in your school.',
                        icon: 'warning',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load teachers',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    });

    // Edit Subject Timetable (Time/Subject)
    $(document).on('click', '.edit-subject-timetable-btn', function() {
        const timetableID = $(this).data('timetable-id');
        const subjectID = $(this).data('subject-id');
        const subjectName = $(this).data('subject-name');
        const startTime = $(this).data('start-time');
        const endTime = $(this).data('end-time');
        const date = $(this).data('date');
        
        Swal.fire({
            title: 'Edit Subject Time',
            html: `
                <div class="text-left">
                    <p><strong>Subject:</strong> ${subjectName}</p>
                    <p><strong>Date:</strong> ${date}</p>
                    <div class="form-group mt-3">
                        <label>Start Time</label>
                        <input type="time" id="edit_start_time" class="form-control" value="${startTime}">
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" id="edit_end_time" class="form-control" value="${endTime}">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Save Changes',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const newStartTime = $('#edit_start_time').val();
                const newEndTime = $('#edit_end_time').val();
                
                if (!newStartTime || !newEndTime) {
                    Swal.showValidationMessage('Please enter both start and end times');
                    return false;
                }
                
                if (newStartTime >= newEndTime) {
                    Swal.showValidationMessage('End time must be after start time');
                    return false;
                }
                
                return { startTime: newStartTime, endTime: newEndTime };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Update timetable
                $.ajax({
                    url: `/update_exam_timetable_time/${timetableID}`,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify({
                        start_time: result.value.startTime,
                        end_time: result.value.endTime
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        Swal.fire({
                            title: 'Updated!',
                            text: 'Subject time updated successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Reload timetable
                            const examID = $('#view_exam_select').val();
                            const viewType = $('#view_type_select').val();
                            const classID = $('#view_class_select').val();
                            if (examID) {
                                loadExamTimetable(examID, viewType, classID);
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to update time',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Delete Subject from Timetable
    $(document).on('click', '.delete-subject-timetable-btn', function() {
        const timetableID = $(this).data('timetable-id');
        const subjectName = $(this).data('subject-name');
        
        Swal.fire({
            title: 'Delete Subject?',
            text: `Are you sure you want to delete "${subjectName}" from the timetable?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/delete_exam_timetable/${timetableID}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Subject deleted from timetable successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Reload timetable
                            const examID = $('#view_exam_select').val();
                            const viewType = $('#view_type_select').val();
                            const classID = $('#view_class_select').val();
                            if (examID) {
                                loadExamTimetable(examID, viewType, classID);
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to delete subject',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // ============================================
    // SHUFFLE TIMETABLE - Random Redistribution
    // ============================================
    let shuffleInProgress = false;

    $(document).on('click', '#shuffleTimetableBtn', function() {
        if (shuffleInProgress) {
            Swal.fire({
                title: 'Shuffle in Progress',
                text: 'Please wait for the current shuffle operation to complete',
                icon: 'warning',
                confirmButtonColor: '#940000'
            });
            return;
        }

        const examID = $(this).data('exam-id');
        
        if (!examID) {
            Swal.fire({
                title: 'Error!',
                text: 'Exam ID not found. Please reload the page and try again.',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            return;
        }
        
        Swal.fire({
            title: 'Shuffle Timetable?',
            html: `
                <p>This will <strong>randomly redistribute</strong> all subjects across different dates and times.</p>
                <p><strong>⚠️ Hall Supervisors will remain assigned to their subjects.</strong></p>
                <p>Are you sure you want to shuffle the entire timetable?</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Shuffle It!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                shuffleInProgress = true;
                const shuffleBtn = $('#shuffleTimetableBtn');
                shuffleBtn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Shuffling...');

                $.ajax({
                    url: '/shuffle_exam_timetable/' + examID,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        shuffleInProgress = false;
                        shuffleBtn.prop('disabled', false).html('<i class="bi bi-shuffle"></i> Shuffle Timetable');
                        
                        Swal.fire({
                            title: 'Shuffled Successfully!',
                            text: response.message || 'Timetable has been shuffled successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Reload timetable
                            const viewType = $('#view_type_select').val();
                            const classID = $('#view_class_select').val();
                            loadExamTimetable(examID, viewType, classID);
                        });
                    },
                    error: function(xhr) {
                        shuffleInProgress = false;
                        shuffleBtn.prop('disabled', false).html('<i class="bi bi-shuffle"></i> Shuffle Timetable');
                        
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to shuffle timetable',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // ============================================
    // SWAP SUBJECTS - Manual Exchange
    // ============================================
    let firstSelectedSubject = null;

    $(document).on('click', '.swap-subject-btn', function() {
        const timetableID = $(this).data('timetable-id');
        const subjectName = $(this).data('subject-name');
        const date = $(this).data('date');
        const startTime = $(this).data('start-time');
        const endTime = $(this).data('end-time');

        const subjectInfo = {
            timetableID: timetableID,
            subjectName: subjectName,
            date: date,
            startTime: startTime,
            endTime: endTime
        };

        // First click: Select first subject
        if (firstSelectedSubject === null) {
            firstSelectedSubject = subjectInfo;
            
            // Highlight selected subject row
            $('.swap-subject-btn').removeClass('btn-success').addClass('btn-info').html('<i class="bi bi-arrow-left-right"></i> Swap');
            $(this).removeClass('btn-info').addClass('btn-success').html('<i class="bi bi-check2"></i> Selected');
            
            Swal.fire({
                title: 'First Subject Selected',
                html: `
                    <p><strong>${subjectName}</strong></p>
                    <p>📅 ${date} | ⏰ ${startTime} - ${endTime}</p>
                    <p class="text-info mt-3">Now select the <strong>second subject</strong> to swap with.</p>
                `,
                icon: 'info',
                confirmButtonColor: '#940000',
                confirmButtonText: 'OK',
                showCancelButton: true,
                cancelButtonText: 'Cancel Selection'
            }).then((result) => {
                if (!result.isConfirmed) {
                    // User canceled - reset selection
                    firstSelectedSubject = null;
                    $('.swap-subject-btn').removeClass('btn-success').addClass('btn-info').html('<i class="bi bi-arrow-left-right"></i> Swap');
                }
            });
        } 
        // Second click: Perform swap
        else {
            // Prevent swapping with itself
            if (firstSelectedSubject.timetableID === timetableID) {
                Swal.fire({
                    title: 'Invalid Selection',
                    text: 'You cannot swap a subject with itself. Please select a different subject.',
                    icon: 'warning',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            const secondSubject = subjectInfo;

            Swal.fire({
                title: 'Confirm Swap',
                html: `
                    <div class="text-start">
                        <h6 class="text-primary">Subject 1:</h6>
                        <p><strong>${firstSelectedSubject.subjectName}</strong><br>
                        📅 ${firstSelectedSubject.date} | ⏰ ${firstSelectedSubject.startTime} - ${firstSelectedSubject.endTime}</p>
                        
                        <h6 class="text-primary mt-3">Subject 2:</h6>
                        <p><strong>${secondSubject.subjectName}</strong><br>
                        📅 ${secondSubject.date} | ⏰ ${secondSubject.startTime} - ${secondSubject.endTime}</p>
                        
                        <hr>
                        
                        <h6 class="text-success">After Swap:</h6>
                        <p>📘 <strong>${firstSelectedSubject.subjectName}</strong> → ${secondSubject.date} ${secondSubject.startTime}-${secondSubject.endTime}</p>
                        <p>📗 <strong>${secondSubject.subjectName}</strong> → ${firstSelectedSubject.date} ${firstSelectedSubject.startTime}-${firstSelectedSubject.endTime}</p>
                        
                        <p class="text-muted mt-3"><small>⚠️ Hall supervisors will remain with their subjects.</small></p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Swap Them!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform swap via AJAX
                    $.ajax({
                        url: '/swap_exam_subjects',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            timetable1_id: firstSelectedSubject.timetableID,
                            timetable2_id: secondSubject.timetableID
                        },
                        success: function(response) {
                            firstSelectedSubject = null;
                            $('.swap-subject-btn').removeClass('btn-success').addClass('btn-info').html('<i class="bi bi-arrow-left-right"></i> Swap');
                            
                            Swal.fire({
                                title: 'Swapped Successfully!',
                                text: response.message || 'Subjects have been swapped successfully',
                                icon: 'success',
                                confirmButtonColor: '#940000'
                            }).then(() => {
                                // Reload timetable
                                const examID = $('#view_exam_select').val();
                                const viewType = $('#view_type_select').val();
                                const classID = $('#view_class_select').val();
                                if (examID) {
                                    loadExamTimetable(examID, viewType, classID);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.error || 'Failed to swap subjects',
                                icon: 'error',
                                confirmButtonColor: '#940000'
                            });
                        }
                    });
                } else {
                    // Reset selection
                    firstSelectedSubject = null;
                    $('.swap-subject-btn').removeClass('btn-success').addClass('btn-info').html('<i class="bi bi-arrow-left-right"></i> Swap');
                }
            });
        }
    });
});
</script>

@include('includes.footer')
