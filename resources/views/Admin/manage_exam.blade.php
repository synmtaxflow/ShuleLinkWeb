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
    .exam-card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1), 0 4px 16px rgba(148, 0, 0, 0.08) !important;
        border: 1px solid rgba(148, 0, 0, 0.1) !important;
    }
    .exam-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        transition: all 0.3s ease;
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
    .badge-status-wait-approval {
        background-color: #dc3545;
        color: white;
    }
    .badge-status-scheduled {
        background-color: #6c757d;
        color: white;
    }
    .badge-status-ongoing {
        background-color: #28a745;
        color: white;
    }
    .badge-status-awaiting-results {
        background-color: #ffc107;
        color: #000;
    }
    .badge-status-results-available {
        background-color: #17a2b8;
        color: white;
    }
    .select-item {
        position: relative;
        margin-bottom: 10px;
    }
    .remove-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        background-color: #dc3545;
        color: white !important;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all 0.2s;
    }
    .remove-btn:hover {
        background-color: #c82333;
        color: white !important;
        transform: translateY(-50%) scale(1.1);
    }
    .remove-btn i {
        color: white !important;
        font-size: 14px;
    }
    /* Dropdown menu styling */
    .dropdown-menu {
        z-index: 1050;
        min-width: 180px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(148, 0, 0, 0.2);
    }
    .dropdown-item {
        padding: 0.5rem 1rem;
        transition: all 0.2s;
    }
    .dropdown-item:hover {
        background-color: #940000;
        color: white !important;
    }
    .dropdown-item i {
        margin-right: 8px;
    }
    .dropdown-toggle::after {
        display: none;
    }
    /* Modal z-index fix */
    .modal {
        z-index: 1055 !important;
    }
    .modal-backdrop {
        z-index: 1054 !important;
    }
    .modal-dialog {
        z-index: 1056 !important;
    }
    .modal-content {
        z-index: 1057 !important;
        pointer-events: auto !important;
    }
    .modal-body,
    .modal-footer,
    .modal-header {
        pointer-events: auto !important;
    }
    /* Ensure form elements are clickable */
    #approveRejectExamPaperModal {
        z-index: 1065 !important;
    }
    #approveRejectExamPaperModal .modal-dialog {
        z-index: 1066 !important;
        pointer-events: auto !important;
    }
    #approveRejectExamPaperModal .modal-content {
        z-index: 1067 !important;
        pointer-events: auto !important;
    }
    #approveRejectExamPaperModal .form-control,
    #approveRejectExamPaperModal button,
    #approveRejectExamPaperModal textarea,
    #approveRejectExamPaperModal input,
    #approveRejectExamPaperModal label {
        pointer-events: auto !important;
        z-index: 1068 !important;
        position: relative;
    }
    /* Ensure parent modal doesn't block interactions */
    #viewExamPapersModal.show ~ #approveRejectExamPaperModal,
    body.modal-open #viewExamPapersModal:not(.show) {
        pointer-events: none !important;
    }
    body.modal-open #viewExamPapersModal:not(.show) .modal-content {
        pointer-events: none !important;
    }
    /* SweetAlert2 custom styles for textarea */
    .swal2-popup-custom .swal2-html-container textarea,
    .swal2-popup-custom textarea.form-control {
        pointer-events: auto !important;
        z-index: 9999 !important;
        position: relative !important;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

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
                            <i class="bi bi-clipboard-check"></i> Manage Examinations
                            @if($totalExams ?? 0 > 0)
                            <small class="ml-2" style="font-size: 0.85rem; opacity: 0.9;">
                                ({{ $totalExams ?? 0 }} {{ $totalExams == 1 ? 'Exam' : 'Exams' }})
                            </small>
                            @endif
                        </h4>
                        @php
                            // Check examination permissions - New format: examination_create, examination_update, examination_delete, examination_read_only
                            $canCreate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_create');
                            $canUpdate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_update');
                            $canDelete = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_delete');
                            $canRead = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_read_only');
                        @endphp
                        @if($canCreate)
                        <button class="btn btn-light text-primary-custom fw-bold" type="button" data-toggle="modal" data-target="#createExamModal">
                            <i class="bi bi-plus-circle"></i> Create New Examination
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form id="searchExamForm" method="GET" action="{{ route('manageExamination') }}">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="filter_exam_category" class="form-label">
                                    <i class="bi bi-grid"></i> Filter by Category
                                </label>
                                <select class="form-control" id="filter_exam_category" name="exam_category">
                                    <option value="">All Categories</option>
                                    <option value="school_exams" {{ ($examCategoryFilter ?? '') == 'school_exams' ? 'selected' : '' }}>School Exams</option>
                                    <option value="test" {{ ($examCategoryFilter ?? '') == 'test' ? 'selected' : '' }}>Test</option>
                                    <option value="special_exams" {{ ($examCategoryFilter ?? '') == 'special_exams' ? 'selected' : '' }}>Special Exams</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_term" class="form-label">
                                    <i class="bi bi-calendar-week"></i> Filter by Term
                                </label>
                                <select class="form-control" id="filter_term" name="term">
                                    <option value="">All Terms</option>
                                    <option value="first_term" {{ ($termFilter ?? '') == 'first_term' ? 'selected' : '' }}>First Term</option>
                                    <option value="second_term" {{ ($termFilter ?? '') == 'second_term' ? 'selected' : '' }}>Second Term</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_year" class="form-label">
                                    <i class="bi bi-calendar"></i> Filter by Year
                                </label>
                                <select class="form-control" id="filter_year" name="year">
                                    <option value="">All Years</option>
                                    @foreach($availableYears ?? [] as $year)
                                        <option value="{{ $year }}" {{ ($yearFilter ?? '') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary-custom w-100" title="Search Examinations">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>
                        @if(!empty($examCategoryFilter) || !empty($yearFilter) || !empty($termFilter))
                        <div class="row">
                            <div class="col-12">
                                <a href="{{ route('manageExamination') }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-x-circle"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Examinations Tabs -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="examTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="school-exams-tab" data-toggle="tab" href="#school-exams" role="tab">
                                <i class="bi bi-building"></i> School Exams
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="test-tab" data-toggle="tab" href="#test" role="tab">
                                <i class="bi bi-clipboard-check"></i> Test
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="special-exams-tab" data-toggle="tab" href="#special-exams" role="tab">
                                <i class="bi bi-star"></i> Special Exams
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="examTabsContent">
                        <!-- School Exams -->
                        <div class="tab-pane fade show active" id="school-exams" role="tabpanel">
                            <div class="row" id="schoolExamsContainer">
                                @if(isset($examinationsGrouped['school_exams']) && $examinationsGrouped['school_exams']->count() > 0)
                                    @foreach($examinationsGrouped['school_exams'] as $exam)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            @include('Admin.partials.exam_widget', ['exam' => $exam])
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="bi bi-info-circle"></i> No school examinations found.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Test -->
                        <div class="tab-pane fade" id="test" role="tabpanel">
                            <div class="row" id="testExamsContainer">
                                @if(isset($examinationsGrouped['test']) && $examinationsGrouped['test']->count() > 0)
                                    @foreach($examinationsGrouped['test'] as $exam)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            @include('Admin.partials.exam_widget', ['exam' => $exam])
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="bi bi-info-circle"></i> No test examinations found.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Special Exams -->
                        <div class="tab-pane fade" id="special-exams" role="tabpanel">
                            <div class="row" id="specialExamsContainer">
                                @if(isset($examinationsGrouped['special_exams']) && $examinationsGrouped['special_exams']->count() > 0)
                                    @foreach($examinationsGrouped['special_exams'] as $exam)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            @include('Admin.partials.exam_widget', ['exam' => $exam])
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="bi bi-info-circle"></i> No special examinations found.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Create Examination Modal -->
<div class="modal fade" id="createExamModal" tabindex="-1" role="dialog" aria-labelledby="createExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="createExamModalLabel">
                    <i class="bi bi-plus-circle"></i> Create New Examination
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createExamForm">
                <div class="modal-body">
                    <div id="examFormErrors" class="alert alert-danger" style="display: none;"></div>

                    <!-- Basic Information -->
                    <div class="form-group">
                        <label for="exam_category">Exam Category <span class="text-danger">*</span></label>
                        <select class="form-control" id="exam_category" name="exam_category" required>
                            <option value="">Select Exam Category</option>
                            <option value="school_exams">School Exams</option>
                            <option value="test">Test</option>
                            <option value="special_exams">Special Exams</option>
                        </select>
                    </div>

                    <!-- Test Type Selection (shown when Test category is selected) -->
                    <div class="form-group" id="test_type_group" style="display: none;">
                        <label for="test_type">Test Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="test_type" name="test_type">
                            <option value="">Select Test Type</option>
                            <option value="weekly_test">Weekly Test</option>
                            <option value="monthly_test">Monthly Test</option>
                            <option value="other_test">Other Test</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Select the type of test you want to create.
                        </small>
                    </div>

                    <div class="form-group" id="exam_name_text_group" style="display: none;">
                        <label for="exam_name">Examination Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="exam_name" name="exam_name" placeholder="Enter examination name">
                    </div>

                    <div class="form-group" id="exam_name_select_group" style="display: none;">
                        <label for="exam_name_type">Examination Name <span class="text-danger">*</span></label>
                        <select class="form-control" id="exam_name_type" name="exam_name_type">
                            <option value="">Select Examination Type</option>
                            <option value="Midterm">Midterm</option>
                            <option value="Terminal">Terminal</option>
                            <option value="Annual Exam">Annual Exam</option>
                        </select>
                    </div>

                    <div class="form-group" id="term_group" style="display: none;">
                        <label for="term">Term (Muhula) <span class="text-danger">*</span></label>
                        <select class="form-control" id="term" name="term">
                            <option value="">Select Term</option>
                            <option value="first_term" {{ in_array(1, $closedTerms ?? []) ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '' }}>
                                First Term @if(in_array(1, $closedTerms ?? [])) (Closed) @endif
                            </option>
                            <option value="second_term" {{ in_array(2, $closedTerms ?? []) ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '' }}>
                                Second Term @if(in_array(2, $closedTerms ?? [])) (Closed) @endif
                            </option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Select which term this examination is for. Closed terms are disabled.
                        </small>
                    </div>

                    <div class="row" id="date_fields_group">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                                <small class="form-text text-muted" id="start_date_help" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="start_date_help_text"></span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                                <small class="form-text text-muted" id="end_date_help" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="end_date_help_text"></span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="year">Year <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="year" name="year" value="{{ $currentYear }}" min="2000" max="2100" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="details">Details (Optional)</label>
                        <textarea class="form-control" id="details" name="details" rows="3"></textarea>
                    </div>

                    <!-- Exam Halls -->
                    <div class="form-group" id="exam_halls_group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0"><strong>Exam Halls</strong> <small class="text-muted">(Define halls, class, capacity & gender)</small></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add_exam_hall_btn">
                                <i class="bi bi-plus-circle"></i> Add Hall
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle" id="exam_halls_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hall Name</th>
                                        <th>Main Class</th>
                                        <th>Class Students</th>
                                        <th>Capacity</th>
                                        <th>Gender</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="exam_halls_body">
                                    <tr class="text-center text-muted" id="exam_halls_empty_row">
                                        <td colspan="6">No hall added yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block">Total capacity per class must not be less than total students (and per gender if gender-specific).</small>
                        <div id="exam_halls_errors" class="alert alert-danger mt-2" style="display:none;"></div>
                    </div>

                    <!-- Except Classes (For School Exams) -->
                    <div class="form-group" id="except_classes_group" style="display: none;">
                        <label>Except Classes (Madarasa yasiyofanya mtihani) <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="use_except" name="use_except" value="1">
                            <label class="form-check-label" for="use_except">
                                Exclude certain classes from this examination
                            </label>
                        </div>
                        <div id="except_classes_selection" style="display: none;">
                            <small class="form-text text-muted mb-2 d-block">
                                <i class="bi bi-info-circle"></i> Select main classes that will NOT participate in this examination (e.g., Form Four for Annual Exam as they do NECTA instead).
                            </small>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($classes ?? [] as $class)
                                <div class="form-check">
                                    <input class="form-check-input except-class-checkbox" type="checkbox" name="except_class_ids[]" value="{{ $class->classID }}" id="except_class_{{ $class->classID }}">
                                    <label class="form-check-label" for="except_class_{{ $class->classID }}">
                                        {{ $class->class_name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Include Classes (For Special Exams) -->
                    <div class="form-group" id="include_classes_group" style="display: none;">
                        <label>Include Classes (Madarasa yatakayofanya mtihani) <span class="text-danger">*</span></label>
                        <small class="form-text text-muted mb-2 d-block">
                            <i class="bi bi-info-circle"></i> Select main classes that will participate in this special examination.
                        </small>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach($classes ?? [] as $class)
                            <div class="form-check">
                                <input class="form-check-input include-class-checkbox" type="checkbox" name="include_class_ids[]" value="{{ $class->classID }}" id="include_class_{{ $class->classID }}">
                                <label class="form-check-label" for="include_class_{{ $class->classID }}">
                                    {{ $class->class_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Student Shifting Status (Only for School Exams) -->
                    <div class="form-group" id="student_shifting_status_group" style="display: none;">
                        <label for="student_shifting_status">Student Shifting Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="student_shifting_status" name="student_shifting_status">
                            <option value="none">None - No shifting allowed</option>
                            <option value="internal">Internal - Allow shifting within same class level (e.g., Form Four A to Form Four B)</option>
                            <option value="external">External - Allow shifting to different class level (e.g., Form Three B to Form Four A)</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> This status determines the type of student shifting allowed for this examination. Only available for School Exams.
                        </small>
                    </div>

                    <!-- Result Approval Chain -->
                    <div class="form-group" id="result_approval_group">
                        <label>
                            <input type="checkbox" id="use_result_approval" name="use_result_approval" value="1">
                            <strong>Enable Result Approval Chain</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> If enabled, results must be approved by selected roles in order. First approver must approve before second can see, and so on.
                        </small>
                        <div id="result_approval_fields" style="display: none;">
                            <div class="form-group">
                                <label for="number_of_approvals">Number of Approvals <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="number_of_approvals" name="number_of_approvals" min="1" max="{{ (count($roles ?? []) + 2) }}" value="1">
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle"></i> Maximum: {{ count($roles ?? []) }} regular roles + 2 special roles (Class Teacher, Coordinator) = {{ (count($roles ?? []) + 2) }} total. Each role can only be selected once.
                                </small>
                            </div>
                            <div id="approval_role_selections">
                                <!-- Dynamic approval role selects will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Exam Attendance Tracking -->
                    <div class="form-group" id="exam_attendance_group">
                        <label>
                            <input type="checkbox" id="enable_exam_attendance" name="enable_exam_attendance" value="1">
                            <strong>Enable Exam Attendance Tracking</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> If enabled, all students whose classes are participating in this examination will be automatically added to the exam attendance list for tracking purposes.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Create Examination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Examination Modal -->
<div class="modal fade" id="rejectExamModal" tabindex="-1" role="dialog" aria-labelledby="rejectExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectExamModalLabel">
                    <i class="bi bi-x-circle"></i> Reject Examination
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectExamForm">
                @csrf
                <input type="hidden" name="exam_id" id="reject_exam_id">
                <input type="hidden" name="approval_status" value="Rejected">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Examination Name</label>
                        <input type="text" class="form-control" id="reject_exam_name" readonly style="background-color: #e9ecef;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" placeholder="Please provide a reason for rejecting this examination..." required maxlength="500"></textarea>
                        <small class="text-muted">Maximum 500 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Reject Examination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Exam Details Modal -->
<div class="modal fade" id="viewExamDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewExamDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" role="document" style="max-width: 95%; width: 95%; margin: 1.75rem auto;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #e9ecef; padding: 20px 25px;">
                <h5 class="modal-title" id="viewExamDetailsModalLabel" style="font-weight: 600; font-size: 1.25rem;">
                    <i class="bi bi-clipboard-check me-2"></i>Examination Details
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="examDetailsContent" style="padding: 25px; max-height: calc(100vh - 200px); overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">Loading examination details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Examination Modal -->
<div class="modal fade" id="editExamModal" tabindex="-1" role="dialog" aria-labelledby="editExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editExamModalLabel">
                    <i class="bi bi-pencil"></i> Edit Examination
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editExamForm">
                <input type="hidden" id="edit_examID" name="examID">
                <div class="modal-body">
                    <div id="editExamFormErrors" class="alert alert-danger" style="display: none;"></div>

                    <!-- Exam Category -->
                    <div class="form-group">
                        <label for="edit_exam_category">Exam Category <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_exam_category" name="exam_category" required>
                            <option value="">Select Exam Category</option>
                            <option value="school_exams">School Exams</option>
                            <option value="test">Test</option>
                            <option value="special_exams">Special Exams</option>
                        </select>
                    </div>

                    <!-- Test Type Selection -->
                    <div class="form-group" id="edit_test_type_group" style="display: none;">
                        <label for="edit_test_type">Test Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_test_type" name="test_type">
                            <option value="">Select Test Type</option>
                            <option value="weekly_test">Weekly Test</option>
                            <option value="monthly_test">Monthly Test</option>
                            <option value="other_test">Other Test</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Select the type of test you want to create.
                        </small>
                    </div>

                    <!-- Exam Name -->
                    <div class="form-group" id="edit_exam_name_text_group" style="display: none;">
                        <label for="edit_exam_name">Examination Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_exam_name" name="exam_name" placeholder="Enter examination name">
                    </div>

                    <div class="form-group" id="edit_exam_name_select_group" style="display: none;">
                        <label for="edit_exam_name_type">Examination Name <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_exam_name_type" name="exam_name_type">
                            <option value="">Select Examination Type</option>
                            <option value="Midterm">Midterm</option>
                            <option value="Terminal">Terminal</option>
                            <option value="Annual Exam">Annual Exam</option>
                        </select>
                    </div>

                    <div class="form-group" id="edit_term_group" style="display: none;">
                        <label for="edit_term">Term (Muhula) <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_term" name="term">
                            <option value="">Select Term</option>
                            <option value="first_term" {{ in_array(1, $closedTerms ?? []) ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '' }}>
                                First Term @if(in_array(1, $closedTerms ?? [])) (Closed) @endif
                            </option>
                            <option value="second_term" {{ in_array(2, $closedTerms ?? []) ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '' }}>
                                Second Term @if(in_array(2, $closedTerms ?? [])) (Closed) @endif
                            </option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Select which term this examination is for. Closed terms are disabled.
                        </small>
                    </div>

                    <div class="row" id="edit_date_fields_group">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                                <small class="form-text text-muted" id="edit_start_date_help" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="edit_start_date_help_text"></span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_end_date">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                                <small class="form-text text-muted" id="edit_end_date_help" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="edit_end_date_help_text"></span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_year">Year <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_year" name="year" min="2000" max="2100" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_details">Details (Optional)</label>
                        <textarea class="form-control" id="edit_details" name="details" rows="3"></textarea>
                    </div>

                    <!-- Edit Exam Halls -->
                    <div class="form-group" id="edit_exam_halls_group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0"><strong>Exam Halls</strong> <small class="text-muted">(Define halls, class, capacity & gender)</small></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="edit_add_exam_hall_btn">
                                <i class="bi bi-plus-circle"></i> Add Hall
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle" id="edit_exam_halls_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hall Name</th>
                                        <th>Main Class</th>
                                        <th>Class Students</th>
                                        <th>Capacity</th>
                                        <th>Gender</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="edit_exam_halls_body">
                                    <tr class="text-center text-muted" id="edit_exam_halls_empty_row">
                                        <td colspan="6">No hall added yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block">Total capacity per class must not be less than total students (and per gender if gender-specific).</small>
                        <div id="edit_exam_halls_errors" class="alert alert-danger mt-2" style="display:none;"></div>
                    </div>

                    <!-- Except Classes (For School Exams/Test) -->
                    <div class="form-group" id="edit_except_classes_group" style="display: none;">
                        <label>Except Classes (Madarasa yasiyofanya mtihani) <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="edit_use_except" name="use_except" value="1">
                            <label class="form-check-label" for="edit_use_except">
                                Exclude certain classes from this examination
                            </label>
                        </div>
                        <div id="edit_except_classes_selection" style="display: none;">
                            <small class="form-text text-muted mb-2 d-block">
                                <i class="bi bi-info-circle"></i> Select main classes that will NOT participate in this examination.
                            </small>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($classes ?? [] as $class)
                                <div class="form-check">
                                    <input class="form-check-input edit-except-class-checkbox" type="checkbox" name="except_class_ids[]" value="{{ $class->classID }}" id="edit_except_class_{{ $class->classID }}">
                                    <label class="form-check-label" for="edit_except_class_{{ $class->classID }}">
                                        {{ $class->class_name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Include Classes (For Special Exams) -->
                    <div class="form-group" id="edit_include_classes_group" style="display: none;">
                        <label>Include Classes (Madarasa yatakayofanya mtihani) <span class="text-danger">*</span></label>
                        <small class="form-text text-muted mb-2 d-block">
                            <i class="bi bi-info-circle"></i> Select main classes that will participate in this special examination.
                        </small>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach($classes ?? [] as $class)
                            <div class="form-check">
                                <input class="form-check-input edit-include-class-checkbox" type="checkbox" name="include_class_ids[]" value="{{ $class->classID }}" id="edit_include_class_{{ $class->classID }}">
                                <label class="form-check-label" for="edit_include_class_{{ $class->classID }}">
                                    {{ $class->class_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Student Shifting Status -->
                    <div class="form-group" id="edit_student_shifting_status_group" style="display: none;">
                        <label for="edit_student_shifting_status">Student Shifting Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_student_shifting_status" name="student_shifting_status">
                            <option value="none">None - No shifting allowed</option>
                            <option value="internal">Internal - Allow shifting within same class level (e.g., Form Four A to Form Four B)</option>
                            <option value="external">External - Allow shifting to different class level (e.g., Form Three B to Form Four A)</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Only available for School Exams and Tests.
                        </small>
                    </div>

                    <!-- Result Approval Chain -->
                    <div class="form-group" id="edit_result_approval_group">
                        <label>
                            <input type="checkbox" id="edit_use_result_approval" name="use_result_approval" value="1">
                            <strong>Enable Result Approval Chain</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> If enabled, results must be approved by selected roles in order.
                        </small>
                        <div id="edit_result_approval_fields" style="display: none;">
                            <div class="form-group">
                                <label for="edit_number_of_approvals">Number of Approvals <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_number_of_approvals" name="number_of_approvals" min="1" value="1">
                            </div>
                            <div id="edit_approval_role_selections"></div>
                        </div>
                    </div>

                    <!-- Exam Attendance Tracking -->
                    <div class="form-group" id="edit_exam_attendance_group">
                        <label>
                            <input type="checkbox" id="edit_enable_exam_attendance" name="enable_exam_attendance" value="1">
                            <strong>Enable Exam Attendance Tracking</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> If enabled, all participating students will be added to the attendance list.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Update Examination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Re-initialize tooltips after dynamic content is loaded
    $(document).on('shown.bs.dropdown', function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Initialize Bootstrap dropdowns manually to ensure they work
    $(document).on('click', '[data-toggle="dropdown"]', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $dropdown = $(this).closest('.dropdown');
        const $menu = $dropdown.find('.dropdown-menu');
        const isOpen = $menu.hasClass('show');

        // Close all other dropdowns
        $('.dropdown-menu').not($menu).removeClass('show');
        $('[data-toggle="dropdown"]').not(this).attr('aria-expanded', 'false');

        // Toggle current dropdown
        if (!isOpen) {
            $menu.addClass('show');
            $(this).attr('aria-expanded', 'true');
        } else {
            $menu.removeClass('show');
            $(this).attr('aria-expanded', 'false');
        }
    });

    // Close dropdown when clicking on dropdown items
    $(document).on('click', '.dropdown-item', function(e) {
        const $dropdown = $(this).closest('.dropdown');
        const $menu = $dropdown.find('.dropdown-menu');
        const $toggle = $dropdown.find('[data-toggle="dropdown"]');
        
        // Close dropdown after a short delay to allow onclick to execute
        setTimeout(function() {
            $menu.removeClass('show');
            $toggle.attr('aria-expanded', 'false');
        }, 100);
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
            $('[data-toggle="dropdown"]').attr('aria-expanded', 'false');
        }
    });

    // Prevent dropdown from closing when clicking inside menu items
    $('.dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });

    let subclassCount = 0;
    let subjectCount = 0;
    let hallRowId = 0;
    const classStudentCache = {};

    function resetHalls(scope = 'create') {
        const tbody = scope === 'create' ? $('#exam_halls_body') : $('#edit_exam_halls_body');
        const emptyRow = scope === 'create' ? $('#exam_halls_empty_row') : $('#edit_exam_halls_empty_row');
        const errorBox = scope === 'create' ? $('#exam_halls_errors') : $('#edit_exam_halls_errors');
        tbody.find('tr').not(emptyRow).remove();
        emptyRow.show();
        errorBox.hide().html('');
    }

    function renderHallRow(scope = 'create', data = {}) {
        hallRowId++;
        const tbody = scope === 'create' ? $('#exam_halls_body') : $('#edit_exam_halls_body');
        const emptyRow = scope === 'create' ? $('#exam_halls_empty_row') : $('#edit_exam_halls_empty_row');
        emptyRow.hide();
        const prefix = scope === 'create' ? '' : 'edit_';
        const rowId = `${prefix}hall_row_${hallRowId}`;
        const row = $(`
            <tr id="${rowId}">
                <td><input type="text" class="form-control hall-name-input" name="${prefix}hall_name[]" value="${data.hall_name || ''}" placeholder="e.g., Hall A" required></td>
                <td>
                    <select class="form-control hall-class-select" name="${prefix}hall_class_id[]" required>
                        <option value="">Select Class</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center hall-class-count text-muted" style="min-width: 130px;">--</td>
                <td><input type="number" class="form-control hall-capacity-input" name="${prefix}hall_capacity[]" min="1" value="${data.capacity || ''}" placeholder="Capacity" required></td>
                <td>
                    <select class="form-control hall-gender-select" name="${prefix}hall_gender[]" required>
                        <option value="both" ${data.gender_allowed === 'both' ? 'selected' : ''}>Both</option>
                        <option value="male" ${data.gender_allowed === 'male' ? 'selected' : ''}>Male</option>
                        <option value="female" ${data.gender_allowed === 'female' ? 'selected' : ''}>Female</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-hall-btn"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `);

        tbody.append(row);

        if (data.classID) {
            row.find('.hall-class-select').val(data.classID).trigger('change');
        }
        if (data.capacity) {
            row.find('.hall-capacity-input').val(data.capacity);
        }
        if (data.gender_allowed) {
            row.find('.hall-gender-select').val(data.gender_allowed);
        }
    }

    function fetchClassCounts(classID, cb) {
        if (!classID) {
            cb(null);
            return;
        }
        if (classStudentCache[classID]) {
            cb(classStudentCache[classID]);
            return;
        }
        $.get('/get_class_student_counts/' + classID, function(res) {
            if (res && res.success) {
                classStudentCache[classID] = res.counts;
                cb(res.counts);
            } else {
                cb(null);
            }
        }).fail(function() {
            cb(null);
        });
    }

    function validateHallCapacities(scope = 'create') {
        const errorBox = scope === 'create' ? $('#exam_halls_errors') : $('#edit_exam_halls_errors');
        errorBox.hide().html('');

        const rows = scope === 'create' ? $('#exam_halls_body tr') : $('#edit_exam_halls_body tr');
        const totalsByClass = {};

        let hasRow = false;
        rows.each(function() {
            const classId = $(this).find('.hall-class-select').val();
            const capacity = parseInt($(this).find('.hall-capacity-input').val(), 10) || 0;
            const gender = $(this).find('.hall-gender-select').val() || 'both';
            if (!classId || capacity <= 0) return;
            hasRow = true;
            if (!totalsByClass[classId]) {
                totalsByClass[classId] = { both: 0, male: 0, female: 0 };
            }
            totalsByClass[classId].both += capacity;
            if (gender === 'male') totalsByClass[classId].male += capacity;
            else if (gender === 'female') totalsByClass[classId].female += capacity;
            else {
                totalsByClass[classId].male += capacity;
                totalsByClass[classId].female += capacity;
            }
        });

        const checks = [];
        const promises = Object.keys(totalsByClass).map(classId => new Promise(resolve => {
            fetchClassCounts(classId, counts => {
                if (!counts) {
                    checks.push(`Failed to fetch counts for selected class.`);
                    return resolve();
                }
                const needTotal = totalsByClass[classId].both;
                if (needTotal < counts.total) {
                    checks.push(`Class ${classId}: capacity (${needTotal}) is less than total students (${counts.total}).`);
                }
                if (totalsByClass[classId].male < counts.male) {
                    checks.push(`Class ${classId}: male capacity (${totalsByClass[classId].male}) is less than male students (${counts.male}).`);
                }
                if (totalsByClass[classId].female < counts.female) {
                    checks.push(`Class ${classId}: female capacity (${totalsByClass[classId].female}) is less than female students (${counts.female}).`);
                }
                resolve();
            });
        }));

        return Promise.all(promises).then(() => {
            if (checks.length > 0) {
                errorBox.html('<ul class="mb-0"><li>' + checks.join('</li><li>') + '</li></ul>').show();
                return false;
            }
            errorBox.hide().html('');
            return hasRow ? true : true; // allow zero halls for now
        });
    }

    // Add hall buttons
    $('#add_exam_hall_btn').on('click', function() {
        renderHallRow('create');
    });
    $('#edit_add_exam_hall_btn').on('click', function() {
        renderHallRow('edit');
    });

    // Remove hall row
    $(document).on('click', '.remove-hall-btn', function() {
        const row = $(this).closest('tr');
        const tbody = row.closest('tbody');
        row.remove();
        const emptyRow = tbody.find('tr[id$="_empty_row"]');
        if (tbody.find('tr').length === 1) {
            emptyRow.show();
        }
        validateHallCapacities(tbody.attr('id') === 'exam_halls_body' ? 'create' : 'edit');
    });

    // On class change, fetch counts and update cell
    $(document).on('change', '.hall-class-select', function() {
        const row = $(this).closest('tr');
        const classId = $(this).val();
        const cell = row.find('.hall-class-count');
        cell.text('...');
        fetchClassCounts(classId, counts => {
            if (counts) {
                cell.html(`<div>Total: ${counts.total}<br><small>♂ ${counts.male} / ♀ ${counts.female}</small></div>`);
            } else {
                cell.text('--');
            }
            validateHallCapacities(row.closest('tbody').attr('id') === 'exam_halls_body' ? 'create' : 'edit');
        });
    });

    // On capacity or gender change, revalidate
    $(document).on('input change', '.hall-capacity-input, .hall-gender-select', function() {
        validateHallCapacities($(this).closest('tbody').attr('id') === 'exam_halls_body' ? 'create' : 'edit');
    });

    // Handle exam category change
    $('#exam_category').on('change', function() {
        const examCategory = $(this).val();
        
        // Reset all fields
        $('#exam_name').val('');
        $('#exam_name_type').val('');
        $('#test_type').val('');
        $('#term').val('');
        $('#use_except').prop('checked', false);
        $('.except-class-checkbox').prop('checked', false);
        $('.include-class-checkbox').prop('checked', false);
        $('#except_classes_selection').hide();
        $('#start_date_help').hide();
        $('#end_date_help').hide();
        $('#test_type_group').hide();
        $('#date_fields_group').show();
        $('#start_date').prop('required', true);
        $('#end_date').prop('required', true);
        
        if (examCategory === 'school_exams') {
            // Show select dropdown for exam name, hide text input
            $('#exam_name_text_group').hide();
            $('#exam_name').prop('required', false);
            $('#exam_name_select_group').show();
            $('#exam_name_type').prop('required', true);
            $('#exam_name_type').val(''); // Reset selection
            
            // Show term selection
            $('#term_group').show();
            $('#term').prop('required', true);
            
            // Show except classes option, hide include classes
            $('#except_classes_group').show();
            $('#include_classes_group').hide();
            $('.include-class-checkbox').prop('required', false);
            
            // Show student shifting status
            $('#student_shifting_status_group').show();
            $('#student_shifting_status').prop('required', true);
        } else if (examCategory === 'test') {
            // Show test type selection dropdown
            $('#test_type_group').show();
            $('#test_type').prop('required', true);
            
            // Hide exam name fields initially (will be shown based on test type)
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#exam_name').prop('required', false);
            $('#exam_name_type').prop('required', false);
            
            // Show term selection
            $('#term_group').show();
            $('#term').prop('required', true);
            
            // Show except classes option, hide include classes
            $('#except_classes_group').show();
            $('#include_classes_group').hide();
            $('.include-class-checkbox').prop('required', false);
            
            // Show student shifting status
            $('#student_shifting_status_group').show();
            $('#student_shifting_status').prop('required', true);
        } else if (examCategory === 'special_exams') {
            // Show text input for exam name, hide select dropdown
            $('#exam_name_select_group').hide();
            $('#exam_name_type').prop('required', false);
            $('#exam_name_text_group').show();
            $('#exam_name').prop('required', true);
            $('#exam_name').attr('placeholder', 'Enter examination name');
            
            // Show term selection for special exams
            $('#term_group').show();
            $('#term').prop('required', true);
            
            // Show include classes option, hide except classes
            $('#include_classes_group').show();
            $('#except_classes_group').hide();
            $('.except-class-checkbox').prop('required', false);
            
            // Hide student shifting status
            $('#student_shifting_status_group').hide();
            $('#student_shifting_status').prop('required', false);
        } else {
            // Hide all
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#test_type_group').hide();
            $('#term_group').hide();
            $('#except_classes_group').hide();
            $('#include_classes_group').hide();
            $('#student_shifting_status_group').hide();
            $('#exam_name').prop('required', false);
            $('#exam_name_type').prop('required', false);
            $('#term').prop('required', false);
        }
    });

    // Handle test type change (when Test category is selected)
    $('#test_type').on('change', function() {
        const testType = $(this).val();
        
        // Reset exam name fields
        $('#exam_name').val('');
        $('#exam_name_type').val('');
        
        if (testType === 'weekly_test') {
            // For weekly test, set exam name automatically and hide date fields
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#exam_name').val('Weekly Test');
            $('#exam_name').prop('required', false);
            
            // Hide date fields (every week, no need for start/end date)
            $('#date_fields_group').hide();
            $('#start_date').prop('required', false);
            $('#end_date').prop('required', false);
            $('#start_date_help').hide();
            $('#end_date_help').hide();
        } else if (testType === 'monthly_test') {
            // For monthly test, set exam name automatically and hide date fields
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#exam_name').val('Monthly Test');
            $('#exam_name').prop('required', false);
            
            // Hide date fields (every month, no need for start/end date)
            $('#date_fields_group').hide();
            $('#start_date').prop('required', false);
            $('#end_date').prop('required', false);
            $('#start_date_help').hide();
            $('#end_date_help').hide();
        } else if (testType === 'other_test') {
            // For other test, show text input for custom exam name and show date fields
            $('#exam_name_text_group').show();
            $('#exam_name_select_group').hide();
            $('#exam_name').prop('required', true);
            $('#exam_name').val(''); // Clear value for user input
            $('#exam_name').attr('placeholder', 'Enter test name (e.g., Quiz, Assessment, etc.)');
            
            // Show date fields
            $('#date_fields_group').show();
            $('#start_date').prop('required', true);
            $('#end_date').prop('required', true);
            $('#start_date_help').hide();
            $('#end_date_help').hide();
        } else {
            // Reset to default
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#exam_name').prop('required', false);
            $('#date_fields_group').show();
            $('#start_date').prop('required', true);
            $('#end_date').prop('required', true);
        }
    });

    // Handle use_except checkbox
    $('#use_except').on('change', function() {
        if ($(this).is(':checked')) {
            $('#except_classes_selection').show();
        } else {
            $('#except_classes_selection').hide();
            $('.except-class-checkbox').prop('checked', false);
        }
    });

    // Store roles data for JavaScript
    const roles = @json($roles ?? []);
    // Hard-coded special roles: class_teacher and coordinator
    const specialRoles = [
        { id: 'class_teacher', name: 'Class Teacher', role_name: 'Class Teacher' },
        { id: 'coordinator', name: 'Coordinator', role_name: 'Coordinator' }
    ];
    // Max approvals = regular roles + 2 special roles
    const maxApprovals = roles.length + 2;

    // Handle result approval checkbox
    $('#use_result_approval').on('change', function() {
        if ($(this).is(':checked')) {
            $('#result_approval_fields').show();
            $('#number_of_approvals').prop('required', true);
            generateApprovalRoleSelects();
        } else {
            $('#result_approval_fields').hide();
            $('#number_of_approvals').prop('required', false);
            $('#approval_role_selections').empty();
        }
    });

    // Handle number of approvals change
    $('#number_of_approvals').on('change', function() {
        const numApprovals = parseInt($(this).val()) || 0;
        if (numApprovals > maxApprovals) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Number',
                text: 'Number of approvals cannot exceed ' + maxApprovals + ' (number of available roles + 2 special roles).'
            });
            $(this).val(maxApprovals);
            generateApprovalRoleSelects();
        } else if (numApprovals > 0) {
            generateApprovalRoleSelects();
        }
    });

    // Generate approval role selects
    function generateApprovalRoleSelects() {
        const numApprovals = parseInt($('#number_of_approvals').val()) || 0;
        const container = $('#approval_role_selections');
        container.empty();

        // Check if we have roles or special roles available
        if (numApprovals <= 0 || (roles.length === 0 && specialRoles.length === 0)) {
            return;
        }

        for (let i = 1; i <= numApprovals; i++) {
            const selectId = 'approval_role_' + i;
            const selectHtml = `
                <div class="form-group">
                    <label for="${selectId}">Approval ${i} - Select Role <span class="text-danger">*</span></label>
                    <select class="form-control approval-role-select" id="${selectId}" name="approval_role_ids[]" required>
                        <option value="">Select Role</option>
                    </select>
                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle"></i> Role that will approve at step ${i}
                    </small>
                </div>
            `;
            container.append(selectHtml);

            // Populate options for this select
            const select = $('#' + selectId);
            
            // Add special roles first (hard-coded)
            specialRoles.forEach(function(role) {
                select.append($('<option>', {
                    value: role.id,
                    text: role.name || role.role_name,
                    'data-is-special': 'true'
                }));
            });
            
            // Add regular roles from database
            roles.forEach(function(role) {
                select.append($('<option>', {
                    value: role.id,
                    text: role.name || role.role_name,
                    'data-is-special': 'false'
                }));
            });
        }

        // Add change handlers to prevent duplicates
        $('.approval-role-select').on('change', function() {
            const currentSelect = $(this);
            const currentValue = currentSelect.val();
            const currentId = currentSelect.attr('id');

            // Remove this value from all other selects
            $('.approval-role-select').not(currentSelect).each(function() {
                const otherSelect = $(this);
                // Remove previously selected option
                otherSelect.find('option[data-selected="true"]').prop('selected', false).removeAttr('data-selected');
                
                // If this select had the same value, reset it
                if (otherSelect.val() === currentValue) {
                    otherSelect.val('');
                }

                // Re-enable all options
                otherSelect.find('option').prop('disabled', false);

                // Disable the selected value in other selects
                if (currentValue) {
                    otherSelect.find('option[value="' + currentValue + '"]').prop('disabled', true);
                }
            });

            // Update disabled states based on all selected values
            const selectedValues = [];
            $('.approval-role-select').each(function() {
                const val = $(this).val();
                if (val) {
                    selectedValues.push(val);
                }
            });

            $('.approval-role-select').each(function() {
                const thisSelect = $(this);
                const thisValue = thisSelect.val();
                
                // Re-enable all options first
                thisSelect.find('option').prop('disabled', false);
                
                // Disable selected values from other selects
                selectedValues.forEach(function(selectedVal) {
                    if (selectedVal !== thisValue) {
                        thisSelect.find('option[value="' + selectedVal + '"]').prop('disabled', true);
                    }
                });
            });
        });
    }


    function addSubclassField() {
        subclassCount++;
        const fieldHtml = `
            <div class="form-group select-item" id="subclass-group-${subclassCount}">
                <label>Select Class <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center">
                    <select class="form-control subclass-select" name="subclass_ids[]" required>
                        <option value="">Select Class</option>
                        @foreach($subclasses as $subclass)
                            <option value="{{ $subclass->subclassID }}">
                                {{ $subclass->subclass_name }}
                                @if($subclass->stream_code)
                                    ({{ $subclass->stream_code }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-btn" onclick="removeSubclassField(${subclassCount})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        `;
        $('#dynamicFields').append(fieldHtml);
    }

    // Make addSubclassField globally accessible for onclick handlers
    window.addSubclassField = function() {
        addSubclassField();
    };

    function addSubjectField(subclassIds = []) {
        subjectCount++;
        const fieldId = `subject-group-${subjectCount}`;
        const fieldHtml = `
            <div class="form-group select-item" id="${fieldId}">
                <label>Select Subject <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center">
                    <select class="form-control subject-select" name="class_subject_ids[]" id="subject-select-${subjectCount}" required>
                        <option value="">Loading subjects...</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-btn" onclick="removeSubjectField(${subjectCount})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        `;
        $('#dynamicFields').append(fieldHtml);

        // Load subjects based on selected subclasses
        loadSubjectsForField(subjectCount, subclassIds);
    }

    function loadSubjectsForField(fieldId, subclassIds = []) {
        const selectElement = $(`#subject-select-${fieldId}`);
        selectElement.html('<option value="">Loading subjects...</option>');

        const examType = $('#exam_type').val();

        // For school_wide_specific_subjects, load all subjects for the school (no subclass filter needed)
        if (examType === 'school_wide_specific_subjects') {
            // Pass empty array to get all subjects for the school
            subclassIds = [];
        } else {
            // For specific_classes_specific_subjects, get selected subclass IDs if not provided
        if (subclassIds.length === 0) {
            subclassIds = [];
            $('.subclass-select').each(function() {
                if ($(this).val()) {
                    subclassIds.push($(this).val());
                }
            });
        }

            // For specific_classes_specific_subjects, require at least one class to be selected
        if (subclassIds.length === 0) {
            selectElement.html('<option value="">Please select a class first</option>');
            return;
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '/get_class_subjects_by_subclass',
            method: 'POST',
            data: { subclass_ids: subclassIds },
            success: function(response) {
                if (response.success && response.class_subjects) {
                    let options = '<option value="">Select Subject</option>';
                    response.class_subjects.forEach(function(subject) {
                        const subjectName = subject.subject_name || 'N/A';
                        const className = subject.class_name || 'N/A';
                        const subclassName = subject.subclass_name || 'N/A';
                        options += `<option value="${subject.class_subjectID}" data-subclass="${subject.subclassID || ''}">
                            ${subjectName} (${subclassName || className})
                        </option>`;
                    });
                    selectElement.html(options);
                } else {
                    selectElement.html('<option value="">No subjects found</option>');
                }
            },
            error: function(xhr) {
                selectElement.html('<option value="">Error loading subjects</option>');
                console.error('Error loading subjects:', xhr);
            }
        });
    }

    window.removeSubclassField = function(id) {
        $('#subclass-group-' + id).remove();
    };

    window.removeSubjectField = function(id) {
        $('#subject-group-' + id).remove();
    };


    // Store user permissions for JavaScript checks - Make globally accessible
    window.userPermissions = @json($teacherPermissions ?? collect());
    window.userType = @json($user_type ?? '');

    // Helper function to check permission - Make globally accessible
    // Note: Admin has ALL permissions by default, other users need explicit permissions
    window.hasPermission = function(permissionName) {
        // Admin has all permissions automatically
        if (window.userType === 'Admin') {
            return true;
        }
        // For non-admin users, check if they have the specific permission
        if (!window.userPermissions || !Array.isArray(window.userPermissions)) {
            return false;
        }
        return window.userPermissions.includes(permissionName);
    };

    // Form submission
    $('#createExamForm').on('submit', function(e) {
        e.preventDefault();

        // Check create permission - New format: examination_create
        if (!hasPermission('examination_create')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to create examinations. You need examination_create permission.'
            });
            return false;
        }

        const formData = new FormData(this);
        const examCategory = $('#exam_category').val();
        const testType = $('#test_type').val();

        // Remove exam_name_type if not needed (to avoid validation errors)
        formData.delete('exam_name_type');

        // Handle exam name based on exam category
        if (examCategory === 'school_exams') {
            // For school exams, use exam_name_type value as exam_name
            const examNameType = $('#exam_name_type').val();
            if (examNameType) {
                formData.set('exam_name', examNameType);
                formData.set('exam_name_type', examNameType); // Required for validation
            }
            // Note: exam_type is set automatically by backend controller
        } else if (examCategory === 'test') {
            // Remove exam_name_type for test category (not needed and causes validation error)
            formData.delete('exam_name_type');
            
            // For test category, check the test type
            // Make sure test_type is included in form data
            if (testType) {
                formData.set('test_type', testType);
            }
            
            if (testType === 'weekly_test') {
                // For weekly test, exam name is Weekly Test
                formData.set('exam_name', 'Weekly Test');
                // Set valid dates that pass validation (backend will convert to "every_week")
                // Use the year from the form or current year
                const year = $('#year').val() || new Date().getFullYear();
                const validDate = year + '-01-01'; // Use a valid date format (Jan 1st of the year)
                formData.set('start_date', validDate);
                formData.set('end_date', validDate);
                // Also set a flag for backend to know it's weekly
                formData.set('is_weekly_test', '1');
                // Note: exam_type is set automatically by backend controller
            } else if (testType === 'monthly_test') {
                // For monthly test, exam name is Monthly Test
                formData.set('exam_name', 'Monthly Test');
                // Set valid dates that pass validation (backend will convert to "every_month")
                // Use the year from the form or current year
                const year = $('#year').val() || new Date().getFullYear();
                const validDate = year + '-01-01'; // Use a valid date format (Jan 1st of the year)
                formData.set('start_date', validDate);
                formData.set('end_date', validDate);
                // Also set a flag for backend to know it's monthly
                formData.set('is_monthly_test', '1');
                // Note: exam_type is set automatically by backend controller
            } else if (testType === 'other_test') {
                // For other test, use the text input value
                const otherTestName = $('#exam_name').val();
                if (!otherTestName || otherTestName.trim() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a test name for Other Test.'
                    });
                    return false;
                }
                formData.set('exam_name', otherTestName.trim());
                // Keep start_date and end_date for other test (they are already in formData from the date inputs)
                // Validate that dates are provided
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                if (!startDate || !endDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select start date and end date for Other Test.'
                    });
                    return false;
                }
                // Note: exam_type is set automatically by backend controller
            } else {
                // No test type selected
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a test type.'
                });
                return false;
            }
        } else if (examCategory === 'special_exams') {
        // For special exams, exam_name is already set from text input
            // Remove exam_name_type for special exams
            formData.delete('exam_name_type');
            // Note: exam_type is set automatically by backend controller
        }

        // Collect except class IDs (for School Exams and Test)
        const exceptClassIds = [];
        if ((examCategory === 'school_exams' || examCategory === 'test') && $('#use_except').is(':checked')) {
            $('.except-class-checkbox:checked').each(function() {
                exceptClassIds.push($(this).val());
            });
        }
        formData.delete('except_class_ids[]');
        exceptClassIds.forEach((id) => {
            formData.append('except_class_ids[]', id);
        });

        // Collect include class IDs (for Special Exams)
        const includeClassIds = [];
        if (examCategory === 'special_exams') {
            $('.include-class-checkbox:checked').each(function() {
                includeClassIds.push($(this).val());
            });
            // Validate at least one class is selected
            if (includeClassIds.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select at least one class for this special examination.'
                });
                return false;
            }
        }
        formData.delete('include_class_ids[]');
        includeClassIds.forEach((id) => {
            formData.append('include_class_ids[]', id);
        });

        // Handle result approval chain
        if ($('#use_result_approval').is(':checked')) {
            formData.append('use_result_approval', '1');
            const numApprovals = parseInt($('#number_of_approvals').val()) || 0;
            formData.append('number_of_approvals', numApprovals);
            
            // Collect approval role IDs
            const approvalRoleIds = [];
            $('.approval-role-select').each(function() {
                const roleId = $(this).val();
                if (roleId) {
                    approvalRoleIds.push(roleId);
                }
            });
            
            // Validate that all roles are selected
            if (approvalRoleIds.length !== numApprovals) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a role for all approval steps.'
                });
                return false;
            }
            
            formData.delete('approval_role_ids[]');
            approvalRoleIds.forEach((id) => {
                formData.append('approval_role_ids[]', id);
            });
        } else {
            formData.delete('use_result_approval');
            formData.delete('number_of_approvals');
            formData.delete('approval_role_ids[]');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        validateHallCapacities('create').then(valid => {
            if (!valid) return;
            
            // Show progress bar
            const submitBtn = $('#createExamForm button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Creating Examination...');
            
            // Add progress bar to modal
            if ($('#examProgressBar').length === 0) {
                $('#createExamModal .modal-body').prepend(`
                    <div id="examProgressBar" class="progress mb-3" style="display: none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                            Creating Examination...
                        </div>
                    </div>
                `);
            }
            $('#examProgressBar').show();
            
            $.ajax({
                url: '{{ route("store_examination") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#examProgressBar').hide();
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    $('#createExamModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: response.success,
                        icon: 'success',
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    $('#examProgressBar').hide();
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    let errorMessage = '';

                    // Check for validation errors
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
                    }
                    // Check for general error message
                    else if (xhr.responseJSON && xhr.responseJSON.error) {
                        let errorText = xhr.responseJSON.error;
                        if (xhr.responseJSON.details && xhr.responseJSON.details.message) {
                            errorText += '<br><small class="text-muted">' + xhr.responseJSON.details.message + '</small>';
                        }
                        errorMessage = '<p class="mb-0"><strong>' + errorText + '</strong></p>';
                    }
                    else if (xhr.status === 403) {
                        errorMessage = '<p class="mb-0"><strong>Access Denied:</strong> You do not have permission to perform this action.</p>';
                    }
                    else if (xhr.status === 422) {
                        errorMessage = '<p class="mb-0"><strong>Validation Error:</strong> Please check your input and try again.</p>';
                    }
                    else {
                        errorMessage = '<p class="mb-0"><strong>Error:</strong> Failed to create examination. Please try again.</p>';
                    }

                    if (errorMessage) {
                        $('#examFormErrors').html(errorMessage).show();
                    } else {
                        $('#examFormErrors').html('<p class="mb-0"><strong>Error:</strong> An unexpected error occurred. Please try again.</p>').show();
                    }
                }
            });
        });
    });

    // Reset form when modal is closed
    $('#createExamModal').on('hidden.bs.modal', function() {
        $('#createExamForm')[0].reset();
        $('#dynamicFields').empty();
        $('#examFormErrors').hide();
        subclassCount = 0;
        subjectCount = 0;
        // Hide all conditional fields
        $('#test_type_group').hide();
        $('#exam_name_text_group').hide();
        $('#exam_name_select_group').hide();
        $('#term_group').hide();
        $('#except_classes_group').hide();
        $('#include_classes_group').hide();
        $('#student_shifting_status_group').hide();
        $('#date_fields_group').show();
        $('#start_date').prop('required', true);
        $('#end_date').prop('required', true);
        
        // Reset result approval fields
        $('#use_result_approval').prop('checked', false);
        $('#result_approval_fields').hide();
        $('#number_of_approvals').val(1).prop('required', false);
        $('#approval_role_selections').empty();
        
        // Reset exam attendance checkbox
        $('#enable_exam_attendance').prop('checked', false);

        // Reset halls
        resetHalls('create');
    });

    // Change exam status
    // Approve Exam
    $(document).on('click', '.approve-exam-btn', function(e) {
        e.preventDefault();
        
        // Check permission
        if (!hasPermission('approve_exam')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to approve examinations.'
            });
            return;
        }
        
        var examID = $(this).data('exam-id');
        var examName = $(this).data('exam-name');

        Swal.fire({
            title: 'Approve Examination?',
            text: 'Do you want to approve "' + examName + '"?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/approve_exam/' + examID,
                    method: 'POST',
                    data: {
                        approval_status: 'Approved',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Approved!',
                            text: response.success,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to approve exam.';
                        if (xhr.status === 403) {
                            errorMsg = xhr.responseJSON?.error || 'You do not have permission to approve examinations.';
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // View Report
    function viewReport(examID) {
        window.open('/view_exam_results/' + examID, '_blank');
    }

    // View Exam More - Detailed view like manage_attendance
    window.viewExamMore = function(examID) {
        $('#viewExamDetailsModal').modal('show');
        $('#examDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3">Loading examination details...</p>
            </div>
        `);

        $.ajax({
            url: '/get_exam_details/' + examID,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    displayExamMoreDetails(response);
                } else {
                    $('#examDetailsContent').html('<div class="alert alert-danger">Error loading examination details</div>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error loading examination details';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#examDetailsContent').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            }
        });
    }

    function displayExamMoreDetails(data) {
        const exam = data.exam;
        const classes = data.classes;
        const attendance = data.attendance || null;
        const examHasEnded = exam.has_ended || false;
        
        // Check if this is a Weekly Test or Monthly Test
        const isWeeklyTest = exam.exam_name === 'Weekly Test' || exam.start_date === 'every_week' || exam.end_date === 'every_week';
        const isMonthlyTest = exam.exam_name === 'Monthly Test' || exam.start_date === 'every_month' || exam.end_date === 'every_month';
        
        // Format dates or use text for weekly/monthly tests
        let dateDisplay = '';
        if (isWeeklyTest) {
            dateDisplay = '<small class="d-block">Every week</small>';
        } else if (isMonthlyTest) {
            dateDisplay = '<small class="d-block">Every month in a term</small>';
        } else {
            try {
        const startDate = new Date(exam.start_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const endDate = new Date(exam.end_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                dateDisplay = `<small class="d-block">${startDate}</small><small class="d-block">to ${endDate}</small>`;
            } catch (e) {
                dateDisplay = '<small class="d-block">Date not available</small>';
            }
        }
        
        let html = `
            <div class="mb-4">
                <h4 class="mb-3" style="color: #940000; font-weight: 700; font-size: 1.5rem;">
                    <i class="bi bi-clipboard-check"></i> ${exam.exam_name}
                </h4>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Students</h6>
                                        <h3 class="mb-0 fw-bold">${data.total_students}</h3>
                                    </div>
                                    <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Classes</h6>
                                        <h3 class="mb-0 fw-bold">${data.total_classes}</h3>
                                    </div>
                                    <i class="bi bi-building" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Subjects</h6>
                                        <h3 class="mb-0 fw-bold">${data.total_subjects}</h3>
                                    </div>
                                    <i class="bi bi-book" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.85rem; opacity: 0.9;">Exam Period</h6>
                                        ${dateDisplay}
                                    </div>
                                    <i class="bi bi-calendar-range" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${examHasEnded && attendance ? `
                <!-- Exam Attendance Statistics (Only shown after exam ends) -->
                <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #940000 !important;">
                    <div class="card-header bg-primary-custom text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i> Exam Attendance Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body text-center">
                                        <h4 class="mb-1 fw-bold">${attendance.expected || 0}</h4>
                                        <p class="mb-0">Walio Takwa Kufanya</p>
                                        <small>(Expected Students)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body text-center">
                                        <h4 class="mb-1 fw-bold">${attendance.present || 0}</h4>
                                        <p class="mb-0">Walio Fanya</p>
                                        <small>(Present)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-danger text-white h-100">
                                    <div class="card-body text-center">
                                        <h4 class="mb-1 fw-bold">${attendance.absent || 0}</h4>
                                        <p class="mb-0">Wasio Fanya</p>
                                        <small>(Absent)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${attendance.by_subclass && attendance.by_subclass.length > 0 ? `
                        <hr>
                        <h6 class="mb-3"><i class="bi bi-list-ul"></i> Attendance by Class</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Class</th>
                                        <th class="text-center">Expected</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                    </tr>
                                </thead>
                                <tbody>
                        ` : ''}
                        ${attendance.by_subclass ? attendance.by_subclass.map(subclass => `
                                    <tr>
                                        <td><strong>${subclass.class_name} - ${subclass.subclass_name}</strong></td>
                                        <td class="text-center">${subclass.expected || 0}</td>
                                        <td class="text-center text-success"><strong>${subclass.present || 0}</strong></td>
                                        <td class="text-center text-danger"><strong>${subclass.absent || 0}</strong></td>
                                    </tr>
                        `).join('') : ''}
                        ${attendance.by_subclass && attendance.by_subclass.length > 0 ? `
                                </tbody>
                            </table>
                        </div>
                        ` : ''}
                    </div>
                </div>
                ` : ''}

                <!-- Classes Details -->
                <div class="row">
        `;

        classes.forEach((classData, index) => {
            const isCompleted = classData.students_with_marks !== undefined;
            html += `
                <div class="col-md-6 mb-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-building"></i> ${classData.class_name} - ${classData.subclass_name}
                                ${classData.stream_code ? '<span class="badge bg-secondary ms-2">' + classData.stream_code + '</span>' : ''}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Students</small>
                                    <strong>${classData.students_count}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Subjects</small>
                                    <strong>${classData.subjects_count}</strong>
                                </div>
                            </div>
                            ${isCompleted ? `
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-success d-block">Completed</small>
                                        <strong class="text-success">${classData.students_with_marks || 0}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-warning d-block">Not Completed</small>
                                        <strong class="text-warning">${classData.students_without_marks || 0}</strong>
                                    </div>
                                </div>
                            ` : ''}
                            <div class="mb-2">
                                <small class="text-muted d-block mb-2"><strong>Subjects:</strong></small>
                                <div class="d-flex flex-wrap gap-1">
            `;
            
            classData.subjects.forEach(subject => {
                html += `<span class="badge bg-primary">${subject.subject_name}</span>`;
            });
            
            html += `
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;

        $('#examDetailsContent').html(html);
    }

    // Toggle Enter Result
    $(document).on('click', '.toggle-enter-result-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        // Use attr() to get the raw string value, then convert to boolean
        const currentValueStr = $(this).attr('data-current-value');
        const currentValue = currentValueStr === 'true' || currentValueStr === true;
        const newValue = !currentValue;
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';

        Swal.fire({
            title: newValue ? 'Allow Enter Result?' : 'Disallow Enter Result?',
            text: newValue 
                ? `Allow teachers to enter results for "${examName}"?`
                : `Disallow result entry for "${examName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading message
                const loadingTitle = newValue ? 'Enabling Enter Result...' : 'Disabling Enter Result...';
                const loadingText = newValue 
                    ? 'Please wait while we enable result entry and notify teachers via SMS. This may take a few minutes...'
                    : 'Please wait while we disable result entry and notify teachers via SMS. This may take a few minutes...';
                
                Swal.fire({
                    title: loadingTitle,
                    html: loadingText,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/toggle_enter_result/' + examID,
                    method: 'POST',
                    data: {
                        enter_result: newValue ? 1 : 0,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let successMessage = response.success || 'Result entry status updated successfully';
                        if (response.sms_sent_count !== undefined && response.sms_sent_count > 0) {
                            successMessage += ' SMS zimetumwa kwa walimu ' + response.sms_sent_count + '.';
                        }
                        
                        Swal.fire({
                            title: 'Success!',
                            text: successMessage,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to update';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        } else if (xhr.status === 403) {
                            errorMsg = 'You do not have permission to perform this action';
                        } else if (xhr.status === 404) {
                            errorMsg = 'Examination not found';
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Toggle Publish Result
    $(document).on('click', '.toggle-publish-result-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        // Use attr() to get the raw string value, then convert to boolean
        const currentValueStr = $(this).attr('data-current-value');
        const currentValue = currentValueStr === 'true' || currentValueStr === true;
        const newValue = !currentValue;
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';

        Swal.fire({
            title: newValue ? 'Publish Result?' : 'Unpublish Result?',
            text: newValue 
                ? `Publish results for "${examName}"? This will make results visible to all.`
                : `Unpublish results for "${examName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading message, especially for publishing (SMS sending)
                if (newValue) {
                    Swal.fire({
                        title: 'Publishing Results...',
                        html: 'Tunachapisha matokeo na kutuma SMS kwa wazazi. Tafadhali subiri...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/toggle_publish_result/' + examID,
                    method: 'POST',
                    data: {
                        publish_result: newValue ? 1 : 0,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let successMessage = response.success;
                        if (newValue && response.sms_sent_count !== undefined && response.sms_sent_count > 0) {
                            successMessage += ' SMS zimetumwa kwa wazazi ' + response.sms_sent_count + ' wanaofunzi.';
                        }
                        
                        Swal.fire({
                            title: 'Success!',
                            text: successMessage,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to update',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Toggle Upload Paper
    $(document).on('click', '.toggle-upload-paper-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        // Use attr() to get the raw string value, then convert to boolean
        const currentValueStr = $(this).attr('data-current-value');
        const currentValue = currentValueStr === 'true' || currentValueStr === true;
        const newValue = !currentValue;
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';

        Swal.fire({
            title: newValue ? 'Allow Upload Paper?' : 'Disallow Upload Paper?',
            text: newValue 
                ? `Allow teachers to upload exam papers for "${examName}"?`
                : `Disallow exam paper upload for "${examName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/toggle_upload_paper/' + examID,
                    method: 'POST',
                    data: {
                        upload_paper: newValue ? 1 : 0,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.success || 'Upload paper status updated successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to update';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        } else if (xhr.status === 403) {
                            errorMsg = 'You do not have permission to perform this action';
                        } else if (xhr.status === 404) {
                            errorMsg = 'Examination not found';
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Auto Shift Students
    $(document).on('click', '.auto-shift-students-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        const shiftingStatus = $(this).data('shifting-status');
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';
        const statusText = shiftingStatus === 'internal' ? 'darasa moja kwenda jengine (mfano: Form One A kwenda Form One B)' : 'darasa moja kwenda jengine (mfano: Form Three kwenda Form Four)';

        Swal.fire({
            title: 'Auto Shift Students?',
            text: `Je, unataka kuhamisha wanafunzi kwa automatik kutoka ${statusText} kwa ajili ya "${examName}"? Hii itaangalia matokeo na kuhamisha wanafunzi kulingana na alama zao.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ndiyo, Hamisha',
            cancelButtonText: 'Ghairi'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Inahamisha Wanafunzi...',
                    html: 'Tunahamisha wanafunzi na kutuma SMS kwa wazazi. Tafadhali subiri...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/auto_shift_students/' + examID,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Imekamilika!',
                            text: response.success,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Kosa!',
                            text: xhr.responseJSON?.error || 'Imeshindwa kuhamisha wanafunzi',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Unshift Students
    $(document).on('click', '.unshift-students-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';

        Swal.fire({
            title: 'Rudisha Wanafunzi?',
            text: `Je, unataka kurudisha wanafunzi waliohamishwa kurudi darasa la zamani kwa ajili ya "${examName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ndiyo, Rudisha',
            cancelButtonText: 'Ghairi'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Inarudisha Wanafunzi...',
                    html: 'Tunarudisha wanafunzi darasa la zamani. Tafadhali subiri...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/unshift_students/' + examID,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Imekamilika!',
                            text: response.success,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Kosa!',
                            text: xhr.responseJSON?.error || 'Imeshindwa kurudisha wanafunzi',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Reject Exam
    $(document).on('click', '.reject-exam-btn', function(e) {
        e.preventDefault();
        
        // Check permission
        if (!hasPermission('reject_exam')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to reject examinations.'
            });
            return;
        }
        
        var examID = $(this).data('exam-id');
        var examName = $(this).data('exam-name');

        // Populate reject modal
        $('#reject_exam_id').val(examID);
        $('#reject_exam_name').val(examName);
        $('#rejection_reason').val('');

        // Show reject modal
        $('#rejectExamModal').modal('show');
    });

    // Handle Reject Exam Form Submission
    $(document).on('submit', '#rejectExamForm', function(e) {
        e.preventDefault();

        // Check permission
        if (!hasPermission('reject_exam')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to reject examinations.'
            });
            $('#rejectExamModal').modal('hide');
            return false;
        }

        var examID = $('#reject_exam_id').val();
        var rejectionReason = $('#rejection_reason').val();

        if (!rejectionReason || rejectionReason.trim() === '') {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please provide a reason for rejecting this examination.'
            });
            return false;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '/approve_exam/' + examID,
            method: 'POST',
            data: {
                approval_status: 'Rejected',
                rejection_reason: rejectionReason,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    title: 'Rejected!',
                    text: response.success,
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    $('#rejectExamModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                var errorMsg = 'Failed to reject exam.';
                if (xhr.status === 403) {
                    errorMsg = xhr.responseJSON?.error || 'You do not have permission to reject examinations.';
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire({
                    title: 'Error!',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    });



    // Delete exam (from button click)
    $(document).on('click', '.delete-exam-btn', function(e) {
        e.preventDefault();
        const examID = $(this).data('exam-id');
        const examName = $(this).data('exam-name');
        deleteExam(examID, examName);
    });

    // Delete exam
    window.deleteExam = function(examID, examName) {
        // Check delete permission - New format: examination_delete
        if (!hasPermission('examination_delete')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to delete examinations. You need examination_delete permission.'
            });
            return false;
        }

        Swal.fire({
            title: 'Delete Examination?',
            html: 'Are you sure you want to delete <strong>"' + examName + '"</strong>?<br><br>This will also delete all related results where examID = ' + examID + '.<br><span style="color: #dc3545; font-weight: bold;">This action cannot be undone!</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the examination and all related results.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/delete_examination/' + examID,
                    method: 'DELETE',
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.success || 'Examination and all related results deleted successfully!',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to delete exam',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    };

    // See More button handler
    $(document).on('click', '.see-more-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const examType = btn.data('exam-type');
        const currentPage = btn.data('page');
        const nextPage = currentPage + 1;

        // Disable button and show loading
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Loading...');

        // Get search filters
        const search = $('#search_exam_name').val() || '';
        const year = $('#filter_year').val() || '';
        const status = $('#filter_status').val() || '';
        const approval = $('#filter_approval').val() || '';
        const examTypeFilter = $('#filter_exam_type').val() || '';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '{{ route("search_examinations") }}',
            method: 'POST',
            data: {
                page: nextPage,
                per_page: 12,
                search: search,
                year: year,
                status: status,
                approval_status: approval,
                exam_type: examTypeFilter
            },
            success: function(response) {
                if (response.html && response.html[examType]) {
                    // Append new exams
                    const containerMap = {
                        'school_wide_all_subjects': 'schoolWideAllExams',
                        'specific_classes_all_subjects': 'specificClassesAllExams',
                        'school_wide_specific_subjects': 'schoolWideSpecificExams',
                        'specific_classes_specific_subjects': 'specificClassesSpecificExams'
                    };
                    const container = $('#' + containerMap[examType]);
                    container.append(response.html[examType]);

                    // Update button
                    if (response.has_more) {
                        btn.prop('disabled', false).data('page', nextPage).html('<i class="bi bi-arrow-down-circle"></i> See More');
                    } else {
                        btn.remove();
                    }
                } else {
                    btn.remove();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to load more exams',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
                btn.prop('disabled', false).html('<i class="bi bi-arrow-down-circle"></i> See More');
            }
        });
    });

    // -------------------------
    // Edit examination helpers
    // -------------------------
    function resetEditFormState() {
        $('#editExamForm')[0].reset();
        $('#editExamFormErrors').hide().html('');
        $('#edit_exam_name_text_group').hide();
        $('#edit_exam_name_select_group').hide();
        $('#edit_test_type_group').hide();
        $('#edit_term_group').hide();
        $('#edit_except_classes_group').hide();
        $('#edit_except_classes_selection').hide();
        $('#edit_include_classes_group').hide();
        $('#edit_student_shifting_status_group').hide();
        $('#edit_result_approval_fields').hide();
        $('#edit_approval_role_selections').empty();
        $('#edit_date_fields_group').show();
        $('#edit_start_date').prop('required', true);
        $('#edit_end_date').prop('required', true);
        $('#edit_number_of_approvals').val(1).prop('required', false);
        $('#edit_use_except').prop('checked', false);
        $('#edit_use_result_approval').prop('checked', false);
        $('#edit_enable_exam_attendance').prop('checked', false);
        $('.edit-except-class-checkbox, .edit-include-class-checkbox').prop('checked', false);
        resetHalls('edit');
    }

    function applyEditCategorySettings(examCategory) {
        // Reset category-specific fields
        $('#edit_exam_name').val('');
        $('#edit_exam_name_type').val('');
        $('#edit_test_type').val('');
        $('#edit_term').val('');
        $('#edit_use_except').prop('checked', false);
        $('.edit-except-class-checkbox, .edit-include-class-checkbox').prop('checked', false);
        $('#edit_except_classes_selection').hide();
        $('#edit_start_date_help, #edit_end_date_help').hide();
        $('#edit_test_type_group').hide();
        $('#edit_date_fields_group').show();
        $('#edit_start_date').prop('required', true);
        $('#edit_end_date').prop('required', true);

        if (examCategory === 'school_exams') {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name').prop('required', false);
            $('#edit_exam_name_select_group').show();
            $('#edit_exam_name_type').prop('required', true);

            $('#edit_term_group').show();
            $('#edit_term').prop('required', true);

            $('#edit_except_classes_group').show();
            $('#edit_include_classes_group').hide();
            $('.edit-include-class-checkbox').prop('required', false);

            $('#edit_student_shifting_status_group').show();
            $('#edit_student_shifting_status').prop('required', true);
        } else if (examCategory === 'test') {
            $('#edit_test_type_group').show();
            $('#edit_test_type').prop('required', true);

            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').prop('required', false);
            $('#edit_exam_name_type').prop('required', false);

            $('#edit_term_group').show();
            $('#edit_term').prop('required', true);

            $('#edit_except_classes_group').show();
            $('#edit_include_classes_group').hide();
            $('.edit-include-class-checkbox').prop('required', false);

            $('#edit_student_shifting_status_group').show();
            $('#edit_student_shifting_status').prop('required', true);
        } else if (examCategory === 'special_exams') {
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name_type').prop('required', false);
            $('#edit_exam_name_text_group').show();
            $('#edit_exam_name').prop('required', true);
            $('#edit_exam_name').attr('placeholder', 'Enter examination name');

            $('#edit_term_group').show();
            $('#edit_term').prop('required', true);

            $('#edit_include_classes_group').show();
            $('#edit_except_classes_group').hide();
            $('.edit-except-class-checkbox').prop('required', false);

            $('#edit_student_shifting_status_group').hide();
            $('#edit_student_shifting_status').prop('required', false);
        } else {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_test_type_group').hide();
            $('#edit_term_group').hide();
            $('#edit_except_classes_group').hide();
            $('#edit_include_classes_group').hide();
            $('#edit_student_shifting_status_group').hide();
            $('#edit_exam_name').prop('required', false);
            $('#edit_exam_name_type').prop('required', false);
            $('#edit_term').prop('required', false);
        }
    }

    function applyEditTestTypeSettings(testType) {
        $('#edit_exam_name').val('');
        $('#edit_exam_name_type').val('');

        if (testType === 'weekly_test') {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').val('Weekly Test');
            $('#edit_exam_name').prop('required', false);

            $('#edit_date_fields_group').hide();
            $('#edit_start_date').prop('required', false);
            $('#edit_end_date').prop('required', false);
            $('#edit_start_date_help, #edit_end_date_help').hide();
        } else if (testType === 'monthly_test') {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').val('Monthly Test');
            $('#edit_exam_name').prop('required', false);

            $('#edit_date_fields_group').hide();
            $('#edit_start_date').prop('required', false);
            $('#edit_end_date').prop('required', false);
            $('#edit_start_date_help, #edit_end_date_help').hide();
        } else if (testType === 'other_test') {
            $('#edit_exam_name_text_group').show();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').prop('required', true);
            $('#edit_exam_name').attr('placeholder', 'Enter test name (e.g., Quiz, Assessment, etc.)');

            $('#edit_date_fields_group').show();
            $('#edit_start_date').prop('required', true);
            $('#edit_end_date').prop('required', true);
            $('#edit_start_date_help, #edit_end_date_help').hide();
        } else {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').prop('required', false);
            $('#edit_date_fields_group').show();
            $('#edit_start_date').prop('required', true);
            $('#edit_end_date').prop('required', true);
        }
    }

    function generateEditApprovalRoleSelects(selectedRoles = []) {
        const numApprovals = parseInt($('#edit_number_of_approvals').val()) || 0;
        const container = $('#edit_approval_role_selections');
        container.empty();

        if (numApprovals <= 0 || (roles.length === 0 && specialRoles.length === 0)) {
            return;
        }

        for (let i = 1; i <= numApprovals; i++) {
            const selectId = 'edit_approval_role_' + i;
            const selectHtml = `
                <div class="form-group">
                    <label for="${selectId}">Approval ${i} - Select Role <span class="text-danger">*</span></label>
                    <select class="form-control edit-approval-role-select" id="${selectId}" name="approval_role_ids[]" required>
                        <option value="">Select Role</option>
                    </select>
                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle"></i> Role that will approve at step ${i}
                    </small>
                </div>
            `;
            container.append(selectHtml);

            const select = $('#' + selectId);

            specialRoles.forEach(function(role) {
                select.append($('<option>', {
                    value: role.id,
                    text: role.name || role.role_name,
                    'data-is-special': 'true'
                }));
            });

            roles.forEach(function(role) {
                select.append($('<option>', {
                    value: role.id,
                    text: role.name || role.role_name,
                    'data-is-special': 'false'
                }));
            });

            if (selectedRoles[i - 1]) {
                select.val(selectedRoles[i - 1]);
            }
        }

        const updateDisabledOptions = () => {
            const selectedValues = [];
            $('.edit-approval-role-select').each(function() {
                const val = $(this).val();
                if (val) {
                    selectedValues.push(val);
                }
            });

            $('.edit-approval-role-select').each(function() {
                const currentSelect = $(this);
                const currentVal = currentSelect.val();
                currentSelect.find('option').prop('disabled', false);

                selectedValues.forEach(function(value) {
                    if (value !== currentVal) {
                        currentSelect.find('option[value="' + value + '"]').prop('disabled', true);
                    }
                });
            });
        };

        $('.edit-approval-role-select').on('change', updateDisabledOptions);
        updateDisabledOptions();
    }

    // Edit exam dynamic handlers
    $('#edit_exam_category').on('change', function() {
        applyEditCategorySettings($(this).val());
    });

    $('#edit_test_type').on('change', function() {
        applyEditTestTypeSettings($(this).val());
    });

    $('#edit_use_except').on('change', function() {
        if ($(this).is(':checked')) {
            $('#edit_except_classes_selection').show();
        } else {
            $('#edit_except_classes_selection').hide();
            $('.edit-except-class-checkbox').prop('checked', false);
        }
    });

    $('#edit_use_result_approval').on('change', function() {
        if ($(this).is(':checked')) {
            $('#edit_result_approval_fields').show();
            $('#edit_number_of_approvals').prop('required', true);
            generateEditApprovalRoleSelects();
        } else {
            $('#edit_result_approval_fields').hide();
            $('#edit_number_of_approvals').prop('required', false);
            $('#edit_approval_role_selections').empty();
        }
    });

    $('#edit_number_of_approvals').on('change', function() {
        const numApprovals = parseInt($(this).val()) || 0;
        if (numApprovals > maxApprovals) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Number',
                text: 'Number of approvals cannot exceed ' + maxApprovals + ' (number of available roles + 2 special roles).'
            });
            $(this).val(maxApprovals);
        }

        const currentSelections = [];
        $('.edit-approval-role-select').each(function() {
            const val = $(this).val();
            if (val) {
                currentSelections.push(val);
            }
        });
        generateEditApprovalRoleSelects(currentSelections);
    });

    // Edit exam
    window.editExam = function(examID) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '/get_exam/' + examID,
            method: 'GET',
            success: function(response) {
                if (response.success && response.exam) {
                    const exam = response.exam;

                    resetEditFormState();
                    hallRowId = 0;

                    $('#edit_examID').val(exam.examID);
                    $('#edit_exam_category').val(exam.exam_category || '');
                    applyEditCategorySettings(exam.exam_category || '');

                    $('#edit_term').val(exam.term || '');
                    $('#edit_start_date').val(exam.start_date || '');
                    $('#edit_end_date').val(exam.end_date || '');
                    $('#edit_year').val(exam.year || new Date().getFullYear());
                    $('#edit_details').val(exam.details || '');

                    if (exam.exam_category === 'school_exams') {
                        $('#edit_exam_name_type').val(exam.exam_name_type || exam.exam_name || '');
                    } else if (exam.exam_category === 'test') {
                        $('#edit_test_type').val(exam.test_type || '');
                        applyEditTestTypeSettings(exam.test_type || '');

                        if (exam.test_type === 'other_test') {
                            $('#edit_exam_name').val(exam.exam_name || '');
                        }
                    } else if (exam.exam_category === 'special_exams') {
                        $('#edit_exam_name').val(exam.exam_name || '');
                    }

                    // Load halls
                    if (Array.isArray(exam.exam_halls) && exam.exam_halls.length > 0) {
                        exam.exam_halls.forEach(h => {
                            renderHallRow('edit', {
                                hall_name: h.hall_name,
                                classID: h.classID,
                                capacity: h.capacity,
                                gender_allowed: h.gender_allowed,
                            });
                        });
                    } else {
                        resetHalls('edit');
                    }

                    if (exam.exam_category === 'school_exams' || exam.exam_category === 'test') {
                        $('#edit_student_shifting_status_group').show();
                        $('#edit_student_shifting_status').prop('required', true).val(exam.student_shifting_status || 'none');
                    } else {
                        $('#edit_student_shifting_status_group').hide();
                        $('#edit_student_shifting_status').prop('required', false).val('none');
                    }

                    $('.edit-except-class-checkbox').prop('checked', false);
                    if ((exam.exam_category === 'school_exams' || exam.exam_category === 'test') && Array.isArray(exam.except_class_ids) && exam.except_class_ids.length > 0) {
                        $('#edit_use_except').prop('checked', true);
                        $('#edit_except_classes_selection').show();
                        exam.except_class_ids.forEach(function(id) {
                            $('#edit_except_class_' + id).prop('checked', true);
                        });
                    }

                    $('.edit-include-class-checkbox').prop('checked', false);
                    if (exam.exam_category === 'special_exams' && Array.isArray(exam.include_class_ids) && exam.include_class_ids.length > 0) {
                        exam.include_class_ids.forEach(function(id) {
                            $('#edit_include_class_' + id).prop('checked', true);
                        });
                    }

                    $('#edit_enable_exam_attendance').prop('checked', !!exam.has_exam_attendance);

                    const approvalRoles = exam.approval_role_ids || [];
                    const approvalsCount = exam.number_of_approvals || approvalRoles.length || 1;
                    $('#edit_use_result_approval').prop('checked', approvalRoles.length > 0);
                    if (approvalRoles.length > 0) {
                        $('#edit_result_approval_fields').show();
                        $('#edit_number_of_approvals').val(approvalsCount);
                        generateEditApprovalRoleSelects(approvalRoles);
                    } else {
                        $('#edit_result_approval_fields').hide();
                        $('#edit_approval_role_selections').empty();
                    }

                    $('#editExamFormErrors').hide().html('');
                    $('#editExamModal').modal('show');
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load exam data',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to load exam',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    };

    // View results
    window.viewResults = function(examID) {
        Swal.fire({
            title: 'View Results',
            text: 'This feature will be available soon!',
            icon: 'info',
            confirmButtonColor: '#940000'
        });
        // Uncomment when route is ready:
        // window.location.href = '/view_exam_results/' + examID;
    };

    // Edit exam form submission
    $('#editExamForm').on('submit', function(e) {
        e.preventDefault();

        // Check update permission - New format: examination_update
        if (!hasPermission('examination_update')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to update examinations. You need examination_update permission.'
            });
            return false;
        }

        const examID = $('#edit_examID').val();
        const formData = new FormData(this);
        const examCategory = $('#edit_exam_category').val();
        const testType = $('#edit_test_type').val();

        formData.delete('exam_name_type');

        if (examCategory === 'school_exams') {
            const examNameType = $('#edit_exam_name_type').val();
            if (examNameType) {
                formData.set('exam_name', examNameType);
                formData.set('exam_name_type', examNameType);
            }
        } else if (examCategory === 'test') {
            formData.delete('exam_name_type');
            if (testType) {
                formData.set('test_type', testType);
            }

            if (testType === 'weekly_test') {
                formData.set('exam_name', 'Weekly Test');
                const year = $('#edit_year').val() || new Date().getFullYear();
                const validDate = year + '-01-01';
                formData.set('start_date', validDate);
                formData.set('end_date', validDate);
                formData.set('is_weekly_test', '1');
            } else if (testType === 'monthly_test') {
                formData.set('exam_name', 'Monthly Test');
                const year = $('#edit_year').val() || new Date().getFullYear();
                const validDate = year + '-01-01';
                formData.set('start_date', validDate);
                formData.set('end_date', validDate);
                formData.set('is_monthly_test', '1');
            } else if (testType === 'other_test') {
                const otherTestName = $('#edit_exam_name').val();
                if (!otherTestName || otherTestName.trim() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a test name for Other Test.'
                    });
                    return false;
                }
                formData.set('exam_name', otherTestName.trim());

                const startDate = $('#edit_start_date').val();
                const endDate = $('#edit_end_date').val();
                if (!startDate || !endDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select start date and end date for Other Test.'
                    });
                    return false;
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a test type.'
                });
                return false;
            }
        } else if (examCategory === 'special_exams') {
            formData.delete('exam_name_type');
        }

        const exceptClassIds = [];
        if ((examCategory === 'school_exams' || examCategory === 'test') && $('#edit_use_except').is(':checked')) {
            $('.edit-except-class-checkbox:checked').each(function() {
                exceptClassIds.push($(this).val());
            });
        }
        formData.delete('except_class_ids[]');
        exceptClassIds.forEach((id) => formData.append('except_class_ids[]', id));

        const includeClassIds = [];
        if (examCategory === 'special_exams') {
            $('.edit-include-class-checkbox:checked').each(function() {
                includeClassIds.push($(this).val());
            });
            if (includeClassIds.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select at least one class for this special examination.'
                });
                return false;
            }
        }
        formData.delete('include_class_ids[]');
        includeClassIds.forEach((id) => formData.append('include_class_ids[]', id));

        if ($('#edit_use_result_approval').is(':checked')) {
            formData.set('use_result_approval', '1');
            const numApprovals = parseInt($('#edit_number_of_approvals').val()) || 0;
            formData.set('number_of_approvals', numApprovals);

            const approvalRoleIds = [];
            $('.edit-approval-role-select').each(function() {
                const roleId = $(this).val();
                if (roleId) {
                    approvalRoleIds.push(roleId);
                }
            });

            if (approvalRoleIds.length !== numApprovals) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a role for all approval steps.'
                });
                return false;
            }

            formData.delete('approval_role_ids[]');
            approvalRoleIds.forEach((id) => formData.append('approval_role_ids[]', id));
        } else {
            formData.delete('use_result_approval');
            formData.delete('number_of_approvals');
            formData.delete('approval_role_ids[]');
        }

        if ($('#edit_enable_exam_attendance').is(':checked')) {
            formData.set('enable_exam_attendance', '1');
        } else {
            formData.delete('enable_exam_attendance');
        }

        validateHallCapacities('edit').then(valid => {
            if (!valid) return;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while we update the examination.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/update_examination/' + examID,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#editExamModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: response.success || 'Examination updated successfully!',
                        icon: 'success',
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    if (Object.keys(errors).length > 0) {
                        let errorHtml = '<ul>';
                        for (let field in errors) {
                            errorHtml += '<li>' + errors[field] + '</li>';
                        }
                        errorHtml += '</ul>';
                        $('#editExamFormErrors').html(errorHtml).show();
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to update exam',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });
    });

    // Reset edit form when modal is closed
    $('#editExamModal').on('hidden.bs.modal', function() {
        resetEditFormState();
    });
});

    // Initialize Bootstrap tooltips on page load and after dynamic content updates
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Re-initialize tooltips after AJAX content updates
    $(document).ajaxComplete(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // View Exam Papers
    // View Exam Halls
    $(document).on('click', '.view-exam-halls-btn', function(e) {
        e.preventDefault();
        const examID = $(this).data('exam-id');
        const examName = $(this).data('exam-name');
        
        if (!examID) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Exam ID not found.'
            });
            return;
        }
        
        // Show modal
        $('#viewExamHallsModalLabel').html('<i class="bi bi-building"></i> Exam Halls - ' + examName);
        $('#viewExamHallsModal').modal('show');
        $('#examHallsContent').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        // Load exam halls
        $.ajax({
            url: '/admin/get-exam-halls/' + examID,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.halls) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-hover"><thead class="thead-light"><tr><th>Hall Name</th><th>Class</th><th>Capacity</th><th>Gender Allowed</th><th>Supervisors</th></tr></thead><tbody>';
                    
                    if (response.halls.length === 0) {
                        html += '<tr><td colspan="5" class="text-center text-muted py-4">No exam halls found for this examination.</td></tr>';
                    } else {
                        response.halls.forEach(function(hall) {
                            const supervisors = hall.supervisors && hall.supervisors.length > 0 
                                ? hall.supervisors.map(s => s.teacher_name).join(', ') 
                                : '<span class="text-muted">No supervisors assigned</span>';
                            
                            html += `<tr>
                                <td><strong>${hall.hall_name || 'N/A'}</strong></td>
                                <td>${hall.class_name || 'N/A'}</td>
                                <td>${hall.capacity || 0}</td>
                                <td><span class="badge badge-info">${hall.gender_allowed ? hall.gender_allowed.charAt(0).toUpperCase() + hall.gender_allowed.slice(1) : 'N/A'}</span></td>
                                <td>${supervisors}</td>
                            </tr>`;
                        });
                    }
                    
                    html += '</tbody></table></div>';
                    $('#examHallsContent').html(html);
                } else {
                    $('#examHallsContent').html('<div class="alert alert-info text-center">No exam halls found for this examination.</div>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load exam halls.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#examHallsContent').html(`<div class="alert alert-danger text-center">${errorMsg}</div>`);
            }
        });
    });
    
    $(document).on('click', '.view-exam-papers-btn', function(e) {
        e.preventDefault();

        // Check permission
        if (!hasPermission('view_exam_papers')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to view exam papers.'
            });
            return;
        }

        const examID = $(this).data('exam-id');
        const examName = $(this).data('exam-name');
        viewExamPapers(examID, examName);
    });

    // View Exam Details
    $(document).on('click', '.view-exam-details-btn', function(e) {
        e.preventDefault();
        var examID = $(this).data('exam-id');
        var examName = $(this).data('exam-name');

        // Show modal
        $('#viewExamDetailsModal').modal('show');

        // Reset content
        $('#examDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary-custom" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3">Loading examination details...</p>
            </div>
        `);

        // Fetch exam details
        $.ajax({
            url: '/get_exam_details/' + examID,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Check if this is a Weekly Test or Monthly Test
                    const isWeeklyTest = response.exam.exam_name === 'Weekly Test' || response.exam.start_date === 'every_week' || response.exam.end_date === 'every_week';
                    const isMonthlyTest = response.exam.exam_name === 'Monthly Test' || response.exam.start_date === 'every_month' || response.exam.end_date === 'every_month';
                    
                    // Format dates or use text for weekly/monthly tests
                    let startDateDisplay = '';
                    let endDateDisplay = '';
                    if (isWeeklyTest) {
                        startDateDisplay = 'Every week';
                        endDateDisplay = 'Every week';
                    } else if (isMonthlyTest) {
                        startDateDisplay = 'Every month in a term';
                        endDateDisplay = 'Every month in a term';
                    } else {
                        try {
                            startDateDisplay = new Date(response.exam.start_date).toLocaleDateString();
                            endDateDisplay = new Date(response.exam.end_date).toLocaleDateString();
                        } catch (e) {
                            startDateDisplay = 'Not available';
                            endDateDisplay = 'Not available';
                        }
                    }
                    
                    var html = `
                        <div class="mb-3">
                            <h4 class="mb-3" style="color: #940000; font-weight: 700; font-size: 1.5rem;">
                                <i class="bi bi-file-earmark-text"></i> ${response.exam.exam_name}
                            </h4>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="p-2 border rounded">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Exam Type</small>
                                        <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">${response.exam.exam_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Start Date</small>
                                        <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">${startDateDisplay}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">End Date</small>
                                        <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">${endDateDisplay}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Total Classes</small>
                                        <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">${response.total_classes}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="card shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #c50000 100%); border: none;">
                                        <div class="card-body text-center p-3 text-white">
                                            <i class="bi bi-building" style="font-size: 1.5rem; opacity: 0.9; margin-bottom: 0.3rem;"></i>
                                            <h4 class="mb-1" style="font-weight: 700; font-size: 1.5rem;">${response.total_classes}</h4>
                                            <small style="font-weight: 500; font-size: 0.85rem;">Classes</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #c50000 100%); border: none;">
                                        <div class="card-body text-center p-3 text-white">
                                            <i class="bi bi-people" style="font-size: 1.5rem; opacity: 0.9; margin-bottom: 0.3rem;"></i>
                                            <h4 class="mb-1" style="font-weight: 700; font-size: 1.5rem;">${response.total_students}</h4>
                                            <small style="font-weight: 500; font-size: 0.85rem;">Total Students</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #c50000 100%); border: none;">
                                        <div class="card-body text-center p-3 text-white">
                                            <i class="bi bi-book" style="font-size: 1.5rem; opacity: 0.9; margin-bottom: 0.3rem;"></i>
                                            <h4 class="mb-1" style="font-weight: 700; font-size: 1.5rem;">${response.total_subjects}</h4>
                                            <small style="font-weight: 500; font-size: 0.85rem;">Total Subjects</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <h5 class="mb-3" style="color: #940000; font-size: 1.25rem;"><i class="bi bi-list-ul"></i> Classes and Subjects</h5>
                        <div class="accordion" id="examClassesAccordion">
                    `;

                    response.classes.forEach(function(classItem, index) {
                        var subjectsList = '';
                        classItem.subjects.forEach(function(subject) {
                            var teacherName = '';
                            if (subject.teacher_first_name && subject.teacher_last_name) {
                                var middleName = subject.teacher_middle_name ? ' ' + subject.teacher_middle_name : '';
                                teacherName = subject.teacher_first_name + middleName + ' ' + subject.teacher_last_name;
                            } else {
                                teacherName = 'Not Assigned';
                            }

                            subjectsList += `
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left: 4px solid #940000; border-top: 1px solid #dee2e6; border-right: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="mr-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #940000 0%, #c50000 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-book text-white" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1" style="color: #940000; font-weight: 600;">
                                                        <strong>${subject.subject_name}</strong>
                                                    </h6>
                                                    ${subject.subject_code ? '<p class="mb-0"><small class="text-muted"><i class="bi bi-tag"></i> ' + subject.subject_code + '</small></p>' : ''}
                                                </div>
                                            </div>
                                            <hr class="my-2" style="border-color: #e9ecef;">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle text-primary mr-2" style="font-size: 1.2rem;"></i>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Teacher</small>
                                                    <strong style="color: #940000; font-size: 0.9rem;">${teacherName}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light" id="heading${index}">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed p-3 toggle-subjects-btn" type="button" data-toggle="collapse" data-target="#collapse${index}" aria-expanded="false" aria-controls="collapse${index}" style="text-decoration: none; font-size: 1.1rem;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="bi bi-building" style="color: #940000; font-size: 1.3rem; margin-right: 0.5rem;"></i>
                                                    <strong style="color: #940000;">${classItem.class_name}</strong> -
                                                    <span class="text-dark">${classItem.subclass_name}</span>
                                                    ${classItem.stream_code ? '<span class="badge ml-2" style="background-color: #940000; color: white; padding: 0.25rem 0.5rem;">' + classItem.stream_code + '</span>' : ''}
                                                    <i class="bi bi-chevron-down ml-2 toggle-icon" style="color: #940000; font-size: 1rem; transition: transform 0.3s;"></i>
                                                </span>
                                                <div>
                                                    <span class="badge mr-2" style="background-color: #940000; color: white; padding: 0.4rem 0.8rem; font-size: 0.9rem;"><i class="bi bi-book"></i> ${classItem.subjects_count} Subjects</span>
                                                    <span class="badge" style="background-color: #940000; color: white; padding: 0.4rem 0.8rem; font-size: 0.9rem;"><i class="bi bi-people"></i> ${classItem.students_count} Students</span>
                                                </div>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapse${index}" class="collapse" aria-labelledby="heading${index}" data-parent="#examClassesAccordion">
                                    <div class="card-body p-4">
                                        <div class="mb-3 p-3 rounded" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #940000;">
                                            <h6 class="mb-0">
                                                <i class="bi bi-person-circle" style="color: #940000; font-size: 1.2rem;"></i>
                                                <span style="color: #6c757d;">Class Teacher:</span>
                                                <strong style="color: #940000;">${classItem.teacher_name}</strong>
                                            </h6>
                                        </div>
                                        <hr style="border-color: #dee2e6;">
                                        <h5 class="mb-3" style="color: #940000;">
                                            <i class="bi bi-book"></i> Subjects (${classItem.subjects_count})
                                        </h5>
                                        <div class="row">
                                            ${subjectsList || '<div class="col-12"><p class="text-muted">No subjects found</p></div>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div>`;
                    $('#examDetailsContent').html(html);

                    // Handle toggle icon rotation
                    $('#examClassesAccordion').on('show.bs.collapse', function (e) {
                        $(e.target).prev().find('.toggle-icon').removeClass('bi-chevron-down').addClass('bi-chevron-up');
                    });

                    $('#examClassesAccordion').on('hide.bs.collapse', function (e) {
                        $(e.target).prev().find('.toggle-icon').removeClass('bi-chevron-up').addClass('bi-chevron-down');
                    });
                } else {
                    $('#examDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Failed to load examination details.
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                var errorMsg = 'Failed to load examination details.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#examDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${errorMsg}
                    </div>
                `);
            }
    });

});
</script>

<script>
// Global functions for status management (outside document.ready for onclick handlers)
window.handleStatusChange = function(examID, status, permission) {
    // Close dropdown
    if (typeof $ !== 'undefined') {
        $('.dropdown-menu').removeClass('show');
        $('.dropdown-toggle').attr('aria-expanded', 'false');
    }

    // Map status to display names with better terminology
    var statusNames = {
        'not_allowed': 'Not Allow Results Entry',
        'allowed': 'Allow Results Entry',
        'under_review': 'Set Results Under Review',
        'approved': 'Approve for Public View'
    };

    // Map status to descriptive messages
    var statusMessages = {
        'not_allowed': 'This will not allow teachers to enter results',
        'allowed': 'This will allow teachers to add or edit results',
        'under_review': 'This will make the results under review',
        'approved': 'This will allow the results to be seen by public, parents and students'
    };

    var statusName = statusNames[status] || status;
    var statusMessage = statusMessages[status] || '';
    window.updateResultsStatus(examID, permission, status, statusName, statusMessage);
};

window.updateResultsStatus = function(examID, permission, status, statusName, statusMessage) {
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('SweetAlert2 is not loaded. Please refresh the page.');
        console.error('SweetAlert2 is not available');
        return;
    }

    // Build confirmation message
    var confirmText = 'Are you sure you want to ' + statusName.toLowerCase() + ' for this examination?';
    if (statusMessage) {
        confirmText += '\n\n' + statusMessage;
    }

    Swal.fire({
        title: 'Confirm Status Change',
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading with special message for approved status (SMS sending)
            var loadingTitle = 'Updating...';
            var loadingText = 'Please wait while we update the status.';
            if (status === 'approved') {
                loadingTitle = 'Sending Results to Parents';
                loadingText = 'Sending results to parents via SMS. This will take a few minutes to complete. Please wait.';
            }

            Swal.fire({
                title: loadingTitle,
                text: loadingText,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ url("update_results_status") }}/' + examID,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    permission: permission,
                    status: status
                },
                dataType: 'json',
                       success: function(response) {
                           if (response.success) {
                               var message = response.message || 'Results status updated to "' + statusName + '" successfully.';
                               if (response.transferred_count && response.transferred_count > 0) {
                                   message += '\n\n' + response.transferred_count + ' student(s) transferred automatically based on their results.';
                               }
                               Swal.fire({
                                   title: 'Success!',
                                   text: message,
                                   icon: 'success',
                                   confirmButtonColor: '#940000'
                               }).then(function() {
                                   location.reload();
                               });
                           } else {
                               Swal.fire({
                                   title: 'Error!',
                                   text: response.message || 'Failed to update results status.',
                                   icon: 'error',
                                   confirmButtonColor: '#940000'
                               });
                           }
                       },
                error: function(xhr) {
                    var errorMsg = 'Failed to update results status.';
                    if (xhr.status === 403) {
                        errorMsg = 'You are not allowed to perform this action.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMsg = 'Examination not found.';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Server error occurred. Please try again.';
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        }
    });
};
</script>

<!-- View Exam Papers Modal -->
<div class="modal fade" id="viewExamPapersModal" tabindex="-1" role="dialog" aria-labelledby="viewExamPapersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%; width: 95%; margin: 1.75rem auto;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h4 class="modal-title" id="viewExamPapersModalLabel">
                    <i class="bi bi-file-earmark-text"></i> Exam Papers
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto; padding: 2rem;">
                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="search_exam_papers" placeholder="Search by subject, class, or teacher...">
                    </div>
                    <div class="col-md-6">
                        <select class="form-control" id="filter_exam_paper_status">
                            <option value="">All Status</option>
                            <option value="wait_approval">Waiting Approval</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div id="examPapersContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-3">Loading exam papers...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approve/Reject Exam Paper Modal -->
<div class="modal fade" id="approveRejectExamPaperModal" tabindex="-1" role="dialog" aria-labelledby="approveRejectExamPaperModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="approveRejectExamPaperModalLabel">
                    <i class="bi bi-check-circle"></i> Approve/Reject Exam Paper
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="approveRejectExamPaperForm">
                <input type="hidden" id="approve_reject_paper_id">
                <input type="hidden" id="approve_reject_action" name="action">
                <div class="modal-body">
                    <div class="form-group" id="approval_comment_group">
                        <label for="approval_comment" id="approval_comment_label">Approval Comment (Optional)</label>
                        <textarea class="form-control" id="approval_comment" name="approval_comment" rows="4" placeholder="Enter approval comment (optional)..."></textarea>
                    </div>
                    <div class="form-group" id="rejection_reason_group" style="display: none;">
                        <label for="rejection_reason_text">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason_text" name="rejection_reason" rows="4" placeholder="Enter rejection reason..." required></textarea>
                        <small class="text-muted">Please provide a reason for rejecting this exam paper.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="submitApproveReject">
                        <i class="bi bi-check-circle"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Exam Halls Modal -->
<div class="modal fade" id="viewExamHallsModal" tabindex="-1" role="dialog" aria-labelledby="viewExamHallsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h4 class="modal-title" id="viewExamHallsModalLabel">
                    <i class="bi bi-building"></i> Exam Halls
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="examHallsContent" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Exam Paper Modal -->
<div class="modal fade" id="viewExamPaperModal" tabindex="-1" role="dialog" aria-labelledby="viewExamPaperModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
<h5 class="modal-title" id="viewExamPaperModalLabel">
                    <i class="bi bi-file-earmark-text"></i> View Exam Paper
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewExamPaperContent" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// View Exam Papers - Make it globally accessible
window.viewExamPapers = function(examID, examName) {
    // Use jQuery ready to ensure DOM is loaded
    (function($) {
        $(document).ready(function() {
            // Check permission
            if (typeof window.hasPermission === 'function') {
                if (!window.hasPermission('view_exam_papers')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Access Denied',
                            text: 'You do not have permission to view exam papers.'
                        });
                    } else {
                        alert('You do not have permission to view exam papers.');
                    }
                    return;
                }
            } else {
                // Fallback: check userType directly
                if (typeof window.userType === 'undefined' || window.userType !== 'Admin') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Access Denied',
                            text: 'You do not have permission to view exam papers.'
                        });
                    } else {
                        alert('You do not have permission to view exam papers.');
                    }
                    return;
                }
            }

            // Check if modal exists
            if ($('#viewExamPapersModal').length === 0) {
                console.error('View Exam Papers Modal not found');
                alert('Error: Modal not found. Please refresh the page.');
                return;
            }

            $('#viewExamPapersModalLabel').html('<i class="bi bi-file-earmark-text"></i> Exam Papers - ' + examName);
            $('#viewExamPapersModal').modal('show');

            // Reset content
            $('#examPapersContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">Loading exam papers...</p>
                </div>
            `);

            // Store exam ID for filtering
            $('#viewExamPapersModal').data('exam-id', examID);

            // Load exam papers
            if (typeof loadExamPapers === 'function') {
                loadExamPapers(examID);
            } else {
                console.error('loadExamPapers function is not defined');
                $('#examPapersContent').html('<div class="alert alert-danger">Error: Failed to load exam papers. Please refresh the page.</div>');
            }
        });
    })(typeof jQuery !== 'undefined' ? jQuery : window.$);
};

function loadExamPapers(examID, search = '', status = '') {
    $.ajax({
        url: '/get_exam_papers/' + examID,
        method: 'GET',
        data: {
            search: search,
            status: status
        },
        success: function(response) {
            if (response.success && response.exam_papers) {
                let html = '';

                if (response.exam_papers.length === 0) {
                    html = '<div class="alert alert-info text-center"><i class="bi bi-info-circle"></i> No exam papers found for this examination.</div>';
                } else {
                    // Group exam papers by subclass
                    const groupedBySubclass = {};

                    response.exam_papers.forEach(function(paper) {
                        const subclassID = paper.class_subject?.subclass?.subclassID || 'no_subclass';
                        const subclassName = paper.class_subject?.subclass?.subclass_name || 'No Subclass';
                        const className = paper.class_subject?.class?.class_name || 'N/A';
                        const displayName = subclassName !== 'No Subclass' ? subclassName : className;

                        if (!groupedBySubclass[subclassID]) {
                            groupedBySubclass[subclassID] = {
                                subclassID: subclassID,
                                subclassName: subclassName,
                                className: className,
                                displayName: displayName,
                                papers: []
                            };
                        }
                        groupedBySubclass[subclassID].papers.push(paper);
                    });

                    // Display grouped by subclass
                    Object.keys(groupedBySubclass).forEach(function(subclassID) {
                        const group = groupedBySubclass[subclassID];

                        // Calculate statistics
                        const totalPapers = group.papers.length;
                        const totalSubjects = new Set(group.papers.map(p => p.class_subjectID)).size;
                        const approvedCount = group.papers.filter(p => p.status === 'approved').length;
                        const pendingCount = group.papers.filter(p => p.status === 'wait_approval').length;
                        const rejectedCount = group.papers.filter(p => p.status === 'rejected').length;

                        // Subclass header with statistics
                        html += `
                            <div class="card mb-4 border-primary-custom">
                                <div class="card-header bg-primary-custom text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bi bi-folder"></i> ${group.displayName}
                                        </h5>
                                        <button class="btn btn-sm btn-light toggle-subclass-btn" data-subclass-id="${subclassID}">
                                            <i class="bi bi-chevron-down"></i> View Papers
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="card bg-info" style="background-color: #17a2b8 !important;">
                                                <div class="card-body text-center" style="color: #ffffff !important;">
                                                    <h4 style="color: #ffffff !important; font-weight: bold;">${totalPapers}</h4>
                                                    <p class="mb-0" style="color: #ffffff !important; font-weight: 500;">Total Papers</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-primary" style="background-color: #007bff !important;">
                                                <div class="card-body text-center" style="color: #ffffff !important;">
                                                    <h4 style="color: #ffffff !important; font-weight: bold;">${totalSubjects}</h4>
                                                    <p class="mb-0" style="color: #ffffff !important; font-weight: 500;">Subjects</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-success" style="background-color: #28a745 !important;">
                                                <div class="card-body text-center" style="color: #ffffff !important;">
                                                    <h4 style="color: #ffffff !important; font-weight: bold;">${approvedCount}</h4>
                                                    <p class="mb-0" style="color: #ffffff !important; font-weight: 500;">Approved</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-warning" style="background-color: #ffc107 !important;">
                                                <div class="card-body text-center" style="color: #000000 !important;">
                                                    <h4 style="color: #000000 !important; font-weight: bold;">${pendingCount}</h4>
                                                    <p class="mb-0" style="color: #000000 !important; font-weight: 500;">Pending</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="subclass-papers" id="subclass-papers-${subclassID}" style="display: none;">
                        `;

                        // Display papers for this subclass
                        group.papers.forEach(function(paper) {
                            const subjectName = paper.class_subject?.subject?.subject_name || 'N/A';
                        const teacherName = paper.teacher ? (paper.teacher.first_name + ' ' + paper.teacher.last_name) : 'N/A';

                        let statusBadge = '';
                        if (paper.status === 'wait_approval') {
                            statusBadge = '<span class="badge badge-warning"><i class="bi bi-clock-history"></i> Waiting Approval</span>';
                        } else if (paper.status === 'approved') {
                            statusBadge = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Approved</span>';
                        } else {
                            statusBadge = '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> Rejected</span>';
                        }

                        html += `
                            <div class="card mb-3 exam-paper-item" data-paper-id="${paper.exam_paperID}">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="card-title text-primary-custom">
                                                <i class="bi bi-file-earmark-text"></i> ${subjectName}
                                            </h5>
                                            <p class="mb-2">
                                                <strong>Teacher:</strong> ${teacherName}<br>
                                                <strong>Type:</strong> <span class="badge badge-info">${paper.upload_type === 'upload' ? 'File Upload' : 'Created'}</span><br>
                                                <strong>Status:</strong> ${statusBadge}
                                            </p>
                                            ${paper.rejection_reason ? `
                                                <div class="alert alert-danger mt-2">
                                                    <strong><i class="bi bi-exclamation-triangle"></i> Rejection Reason:</strong>
                                                    <p class="mb-0">${paper.rejection_reason}</p>
                                                </div>
                                            ` : ''}
                                            ${paper.approval_comment ? `
                                                <div class="alert alert-success mt-2">
                                                    <strong><i class="bi bi-check-circle"></i> Approval Comment:</strong>
                                                    <p class="mb-0">${paper.approval_comment}</p>
                                                </div>
                                            ` : ''}
                                            <p class="mb-0 text-muted">
                                                <small><i class="bi bi-calendar"></i> Uploaded: ${new Date(paper.created_at).toLocaleString()}</small>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            ${hasPermission('view_exam_papers') ? `
                                                <button class="btn btn-sm btn-info view-paper-btn mb-2" data-paper-id="${paper.exam_paperID}" data-exam-id="${examID}" title="View Exam Paper">
                                                    <i class="bi bi-eye"></i> View
                                                </button><br>
                                            ` : ''}
                                            ${paper.upload_type === 'upload' && paper.file_path ? `
                                                <a href="/download_exam_paper/${paper.exam_paperID}" class="btn btn-sm btn-primary-custom mb-2" title="Download">
                                                    <i class="bi bi-download"></i> Download
                                                </a><br>
                                            ` : ''}
                                            ${paper.status === 'wait_approval' ? `
                                                <div class="approve-reject-actions" data-paper-id="${paper.exam_paperID}">
                                                    ${hasPermission('approve_exam_paper') ? `
                                                        <button class="btn btn-sm btn-success show-approve-form-btn mb-2" data-paper-id="${paper.exam_paperID}" title="Approve">
                                                            <i class="bi bi-check-circle"></i> Approve
                                                        </button><br>
                                                    ` : ''}
                                                    ${hasPermission('reject_exam_paper') ? `
                                                        <button class="btn btn-sm btn-danger show-reject-form-btn mb-2" data-paper-id="${paper.exam_paperID}" title="Reject">
                                                            <i class="bi bi-x-circle"></i> Reject
                                                        </button>
                                                    ` : ''}
                                                    <div class="approve-form mt-2" id="approve-form-${paper.exam_paperID}" style="display: none;">
                                                        <textarea class="form-control mb-2" rows="3" placeholder="Approval comment (optional)..." id="approval-comment-${paper.exam_paperID}"></textarea>
                                                        <button class="btn btn-sm btn-success submit-approve-btn" data-paper-id="${paper.exam_paperID}">
                                                            <i class="bi bi-check-circle"></i> Submit Approval
                                                        </button>
                                                        <button class="btn btn-sm btn-secondary cancel-approve-btn" data-paper-id="${paper.exam_paperID}">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                    <div class="reject-form mt-2" id="reject-form-${paper.exam_paperID}" style="display: none;">
                                                        <textarea class="form-control mb-2" rows="3" placeholder="Rejection reason (required)..." id="rejection-reason-${paper.exam_paperID}" required></textarea>
                                                        <button class="btn btn-sm btn-danger submit-reject-btn" data-paper-id="${paper.exam_paperID}">
                                                            <i class="bi bi-x-circle"></i> Submit Rejection
                                                        </button>
                                                        <button class="btn btn-sm btn-secondary cancel-reject-btn" data-paper-id="${paper.exam_paperID}">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            ` : ''}
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }

                $('#examPapersContent').html(html);
            } else {
                $('#examPapersContent').html('<div class="alert alert-danger">Failed to load exam papers.</div>');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error loading exam papers.';
            if (xhr.status === 403) {
                // Permission denied
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else {
                    errorMsg = 'You are not allowed to perform this action. You need the view_exam_papers permission to view exam papers.';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            $('#examPapersContent').html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ' + errorMsg + '</div>');
        }
    });
}

// Search exam papers
$('#search_exam_papers').on('input', function() {
    const examID = $('#viewExamPapersModal').data('exam-id');
    const search = $(this).val();
    const status = $('#filter_exam_paper_status').val();
    if (examID) {
        loadExamPapers(examID, search, status);
    }
});

// Filter by status
$('#filter_exam_paper_status').on('change', function() {
    const examID = $('#viewExamPapersModal').data('exam-id');
    const search = $('#search_exam_papers').val();
    const status = $(this).val();
    if (examID) {
        loadExamPapers(examID, search, status);
    }
});

// Show approve form
$(document).on('click', '.show-approve-form-btn', function() {
    const paperID = $(this).data('paper-id');
    $('#approve-form-' + paperID).slideDown();
    $('#reject-form-' + paperID).slideUp();
    $(this).closest('.approve-reject-actions').find('.show-reject-form-btn').hide();
    $(this).hide();
    $('#approval-comment-' + paperID).focus();
});

// Cancel approve form
$(document).on('click', '.cancel-approve-btn', function() {
    const paperID = $(this).data('paper-id');
    $('#approve-form-' + paperID).slideUp();
    $('#approval-comment-' + paperID).val('');
    $(this).closest('.approve-reject-actions').find('.show-approve-form-btn').show();
    $(this).closest('.approve-reject-actions').find('.show-reject-form-btn').show();
});

// Submit approve
$(document).on('click', '.submit-approve-btn', function() {
    const paperID = $(this).data('paper-id');
    const approvalComment = $('#approval-comment-' + paperID).val().trim();

    // Check permission
    if (!hasPermission('approve_exam_paper')) {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'You do not have permission to approve exam papers.'
        });
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we approve the exam paper.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Submit approval
    $.ajax({
        url: '/approve_reject_exam_paper/' + paperID,
        method: 'POST',
        data: {
            action: 'approve',
            approval_comment: approvalComment,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message || 'Exam paper approved successfully!',
                confirmButtonColor: '#940000'
            }).then(() => {
                // Reload exam papers
                const examID = $('#viewExamPapersModal').data('exam-id');
                const search = $('#search_exam_papers').val();
                const status = $('#filter_exam_paper_status').val();
                if (examID) {
                    loadExamPapers(examID, search, status);
                }
            });
        },
        error: function(xhr) {
            let errorMsg = 'Failed to approve exam paper.';
            if (xhr.status === 403) {
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else {
                    errorMsg = 'You are not allowed to perform this action.';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMsg,
                confirmButtonColor: '#940000'
            });
        }
    });
});

// Show reject form
$(document).on('click', '.show-reject-form-btn', function() {
    const paperID = $(this).data('paper-id');
    $('#reject-form-' + paperID).slideDown();
    $('#approve-form-' + paperID).slideUp();
    $(this).closest('.approve-reject-actions').find('.show-approve-form-btn').hide();
    $(this).hide();
    $('#rejection-reason-' + paperID).focus();
});

// Cancel reject form
$(document).on('click', '.cancel-reject-btn', function() {
    const paperID = $(this).data('paper-id');
    $('#reject-form-' + paperID).slideUp();
    $('#rejection-reason-' + paperID).val('');
    $(this).closest('.approve-reject-actions').find('.show-approve-form-btn').show();
    $(this).closest('.approve-reject-actions').find('.show-reject-form-btn').show();
});

// Submit reject
$(document).on('click', '.submit-reject-btn', function() {
    const paperID = $(this).data('paper-id');
    const rejectionReason = $('#rejection-reason-' + paperID).val().trim();

    // Check permission
    if (!hasPermission('reject_exam_paper')) {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'You do not have permission to reject exam papers.'
        });
        return;
    }

    // Validate rejection reason
    if (!rejectionReason) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please provide a rejection reason.'
        });
        $('#rejection-reason-' + paperID).focus();
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we reject the exam paper.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Submit rejection
    $.ajax({
        url: '/approve_reject_exam_paper/' + paperID,
        method: 'POST',
        data: {
            action: 'reject',
            rejection_reason: rejectionReason,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message || 'Exam paper rejected successfully!',
                confirmButtonColor: '#940000'
            }).then(() => {
                // Reload exam papers
                const examID = $('#viewExamPapersModal').data('exam-id');
                const search = $('#search_exam_papers').val();
                const status = $('#filter_exam_paper_status').val();
                if (examID) {
                    loadExamPapers(examID, search, status);
                }
            });
        },
        error: function(xhr) {
            let errorMsg = 'Failed to reject exam paper.';
            if (xhr.status === 403) {
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else {
                    errorMsg = 'You are not allowed to perform this action.';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMsg,
                confirmButtonColor: '#940000'
            });
        }
    });
});

// Toggle subclass papers view
$(document).on('click', '.toggle-subclass-btn', function() {
    const subclassID = $(this).data('subclass-id');
    const papersDiv = $('#subclass-papers-' + subclassID);
    const icon = $(this).find('i');

    if (papersDiv.is(':visible')) {
        papersDiv.slideUp();
        icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        $(this).html('<i class="bi bi-chevron-down"></i> View Papers');
    } else {
        papersDiv.slideDown();
        icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
        $(this).html('<i class="bi bi-chevron-up"></i> Hide Papers');
    }
});

// View exam paper
$(document).on('click', '.view-paper-btn', function() {
    // Check permission
    if (!hasPermission('view_exam_papers')) {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'You do not have permission to view exam papers.'
        });
        return;
    }

    const paperID = $(this).data('paper-id');
    // Try to get examID from button first, then from modal
    let examID = $(this).data('exam-id') || $('#viewExamPapersModal').data('exam-id');

    if (!examID) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Exam ID not found. Please close and reopen the exam papers modal.'
        });
        return;
    }

    $('#viewExamPaperModal').modal('show');
    $('#viewExamPaperContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

    $.ajax({
        url: '/get_exam_papers/' + examID,
        method: 'GET',
        success: function(response) {
            if (response.success && response.exam_papers) {
                const paper = response.exam_papers.find(p => p.exam_paperID == paperID);

                if (paper) {
                    const subjectName = paper.class_subject?.subject?.subject_name || 'N/A';
                    const className = paper.class_subject?.class?.class_name || 'N/A';
                    const subclassName = paper.class_subject?.subclass?.subclass_name || '';
                    const teacherName = paper.teacher ? (paper.teacher.first_name + ' ' + paper.teacher.last_name) : 'N/A';

                    let statusBadge = '';
                    if (paper.status === 'wait_approval') {
                        statusBadge = '<span class="badge badge-warning"><i class="bi bi-clock-history"></i> Waiting Approval</span>';
                    } else if (paper.status === 'approved') {
                        statusBadge = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Approved</span>';
                    } else {
                        statusBadge = '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> Rejected</span>';
                    }

                    let html = `
                        <div class="card mb-3">
                            <div class="card-header bg-primary-custom text-white">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> ${subjectName}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>Class:</strong> ${subclassName || className}</p>
                                        <p><strong>Teacher:</strong> ${teacherName}</p>
                                        <p><strong>Type:</strong> <span class="badge badge-info">${paper.upload_type === 'upload' ? 'File Upload' : 'Created'}</span></p>
                                        <p><strong>Status:</strong> ${statusBadge}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Uploaded:</strong> ${new Date(paper.created_at).toLocaleString()}</p>
                                        ${paper.upload_type === 'upload' && paper.file_path ? `
                                            <a href="/download_exam_paper/${paper.exam_paperID}" class="btn btn-primary-custom" target="_blank">
                                                <i class="bi bi-download"></i> Download Exam Paper
                                            </a>
                                        ` : ''}
                                    </div>
                                </div>
                    `;

                    if (paper.upload_type === 'upload' && paper.file_path) {
                        // Display PDF/DOC viewer if possible
                        const fileExtension = paper.file_path.split('.').pop().toLowerCase();
                        if (fileExtension === 'pdf') {
                            html += `
                                <div class="mt-3">
                                    <h6><i class="bi bi-file-pdf"></i> Exam Paper Preview</h6>
                                    <div class="border rounded p-2" style="background-color: #f8f9fa;">
                                        <iframe src="/download_exam_paper/${paper.exam_paperID}?inline=1"
                                                type="application/pdf"
                                                style="width: 100%; height: 700px; border: none;"
                                                id="pdf-viewer-${paper.exam_paperID}"
                                                frameborder="0">
                                        </iframe>
                                        <p class="text-muted text-center mt-2">
                                            <small>If PDF doesn't display, <a href="/download_exam_paper/${paper.exam_paperID}" target="_blank">click here to download</a></small>
                                        </p>
                                    </div>
                                </div>
                            `;
                        } else {
                            html += `
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> This file format (${fileExtension.toUpperCase()}) cannot be previewed. Please download to view.
                                </div>
                            `;
                        }
                    } else if (paper.question_content) {
                        html += `
                            <div class="mt-3">
                                <h6><i class="bi bi-pencil"></i> Exam Questions Content</h6>
                                <div class="border p-3" style="background-color: #f8f9fa; white-space: pre-wrap; font-family: monospace;">${paper.question_content}</div>
                            </div>
                        `;
                    }

                    if (paper.rejection_reason) {
                        html += `
                            <div class="alert alert-danger mt-3">
                                <strong><i class="bi bi-exclamation-triangle"></i> Rejection Reason:</strong>
                                <p class="mb-0">${paper.rejection_reason}</p>
                            </div>
                        `;
                    }

                    if (paper.approval_comment) {
                        html += `
                            <div class="alert alert-success mt-3">
                                <strong><i class="bi bi-check-circle"></i> Approval Comment:</strong>
                                <p class="mb-0">${paper.approval_comment}</p>
                            </div>
                        `;
                    }

                    html += `
                            </div>
                        </div>
                    `;

                    $('#viewExamPaperContent').html(html);
                } else {
                    $('#viewExamPaperContent').html('<div class="alert alert-danger">Exam paper not found</div>');
                }
            } else {
                $('#viewExamPaperContent').html('<div class="alert alert-danger">Failed to load exam paper details</div>');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error loading exam paper';
            if (xhr.status === 403) {
                // Permission denied
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else {
                    errorMsg = 'You are not allowed to perform this action. You need the view_exam_papers permission to view exam papers.';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            $('#viewExamPaperContent').html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ' + errorMsg + '</div>');
        }
    });
});
// Old form submission handler removed - now using SweetAlert2 directly
</script>

@include('includes.footer')
