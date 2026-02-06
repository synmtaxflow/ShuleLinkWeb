@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
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
    .spinner-border.text-primary-custom {
        color: #940000 !important;
    }
    .subject-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(148, 0, 0, 0.2) !important;
    }
    .badge {
        font-weight: 600;
    }
    .card-header {
        border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    }
    /* Hover effect for class subject cards */
    #classSubjectsContent .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15) !important;
    }
    
    /* Light background for subject widget cards */
    .subject-card .card-header.bg-primary-custom {
        background-color: rgba(148, 0, 0, 0.08) !important;
        border-bottom: 1px solid rgba(148, 0, 0, 0.15) !important;
    }
    .subject-card .card-header.bg-primary-custom,
    .subject-card .card-header.bg-primary-custom * {
        color: #940000 !important;
    }
    /* Ensure edit modal appears above view modal */
    #editClassSubjectModal {
        z-index: 1060 !important;
    }
    #editClassSubjectModal .modal-backdrop {
        z-index: 1059 !important;
    }
    /* Ensure view modal has lower z-index */
    #viewClassSubjectsModal {
        z-index: 1055 !important;
    }
    #viewClassSubjectsModal .modal-backdrop {
        z-index: 1054 !important;
    }
    /* Scrollbar for Add Class Subject Modal */
    #addClassSubjectModal .modal-body {
        overflow-y: scroll !important;
        max-height: 70vh;
        scrollbar-width: thin;
        -ms-overflow-style: scrollbar;
    }
    #addClassSubjectModal .modal-body::-webkit-scrollbar {
        width: 12px;
        display: block !important;
    }
    #addClassSubjectModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    #addClassSubjectModal .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 6px;
    }
    #addClassSubjectModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
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
                            <i class="bi bi-book"></i> Manage Subjects
                        </h4>
                        <div class="d-flex gap-2">
                            @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('view_class_subjects'))
                            <button class="btn btn-light text-primary-custom fw-bold" id="viewClassSubjectsBtn" type="button">
                                <i class="bi bi-eye"></i> View Class Subjects
                            </button>
                            @endif
                            @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('create_class_subject'))
                            <button class="btn btn-light text-primary-custom fw-bold" id="addClassSubjectBtn" type="button" data-toggle="modal" data-target="#addClassSubjectModal">
                                <i class="bi bi-plus-circle"></i> Add Class Subject
                            </button>
                            @endif
                            @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('create_subject'))
                            <button class="btn btn-light text-primary-custom fw-bold" id="addSchoolSubjectBtn" type="button" data-toggle="modal" data-target="#addSchoolSubjectModal">
                                <i class="bi bi-plus-square"></i> Add School Subject
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- School Subjects Grid -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-book-fill"></i> School Subjects
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @if(count($schoolSubjects) > 0)
                            @foreach ($schoolSubjects as $subject)
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="card border-0 shadow-sm h-100 subject-card" style="transition: transform 0.2s;">
                                        <div class="card-header bg-primary-custom text-white text-center py-3">
                                            <div class="d-flex justify-content-center align-items-center mb-2">
                                                <i class="bi bi-book-fill" style="font-size: 2.5rem; color: #ffffff;"></i>
                                            </div>
                                            <h5 class="mb-0 fw-bold text-white">{{ $subject->subject_name }}</h5>
                                        </div>
                                        <div class="card-body p-3">
                                            @if($subject->subject_code)
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                                    <span class="text-muted small fw-bold">
                                                        <i class="bi bi-code-square text-primary-custom me-1"></i> Code:
                                                    </span>
                                                    <span class="badge bg-info text-white fw-bold">{{ $subject->subject_code }}</span>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="mb-2">
                                                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                                    <span class="text-muted small fw-bold">
                                                        <i class="bi bi-circle-fill text-primary-custom me-1"></i> Status:
                                                    </span>
                                                    <span class="badge {{ $subject->status == 'Active' ? 'bg-success' : 'bg-secondary' }} fw-bold">
                                                        {{ $subject->status }}
                                                    </span>
                                                </div>
                                            </div>
                                            @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('activate_subject'))
                                            <div class="mt-3 d-flex gap-2">
                                                @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('edit_subject'))
                                                <button class="btn btn-sm btn-warning text-dark edit-school-subject-btn flex-fill"
                                                        data-subject-id="{{ $subject->subjectID }}"
                                                        data-subject-name="{{ $subject->subject_name }}"
                                                        data-subject-code="{{ $subject->subject_code }}"
                                                        title="Edit Subject">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                @endif
                                                @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('delete_subject'))
                                                <button class="btn btn-sm btn-danger delete-school-subject-btn flex-fill"
                                                        data-subject-id="{{ $subject->subjectID }}"
                                                        data-subject-name="{{ $subject->subject_name }}"
                                                        title="Delete Subject">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                                @endif
                                            </div>
                                            <div class="mt-2 text-center">
                                                <button class="btn btn-sm {{ $subject->status == 'Active' ? 'btn-secondary' : 'btn-success' }} text-white activate-subject-btn w-100"
                                                        data-subject-id="{{ $subject->subjectID }}"
                                                        data-subject-name="{{ $subject->subject_name }}"
                                                        data-current-status="{{ $subject->status }}"
                                                        title="{{ $subject->status == 'Active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi bi-{{ $subject->status == 'Active' ? 'x-circle' : 'check-circle' }}-fill"></i>
                                                    {{ $subject->status == 'Active' ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 64px; color: #940000;"></i>
                                    <p class="mt-3 mb-0 text-muted">No school subjects found. Click "Add School Subject" to create a new subject.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add School Subject Modal -->
<div class="modal fade" id="addSchoolSubjectModal" tabindex="-1" aria-labelledby="addSchoolSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addSchoolSubjectModalLabel">
                    <i class="bi bi-plus-square"></i> Add New School Subjects
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSchoolSubjectForm">
                @csrf
                <div class="modal-body">
                    <!-- Dynamic Subjects Container -->
                    <div id="schoolSubjectsContainer">
                        <!-- First Subject Row -->
                        <div class="subject-row mb-4 p-3 border rounded" data-row-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-primary-custom">
                                    <i class="bi bi-book"></i> Subject <span class="subject-number">1</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-danger remove-subject-row" style="display: none;">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" name="subjects[0][subject_name]" class="form-control subject-name" placeholder="e.g., Mathematics, English, Kiswahili" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                                <input type="text" name="subjects[0][subject_code]" class="form-control subject-code" placeholder="e.g., MAT, ENG, KIS" maxlength="20">
                        <small class="text-muted">Optional: Enter a short code for this subject</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select name="subjects[0][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                            </div>
                        </div>
                    </div>

                    <!-- Add Another Subject Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-primary-custom" id="addAnotherSchoolSubjectBtn">
                            <i class="bi bi-plus-circle"></i> Add Another Subject
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save All Subjects
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit School Subject Modal -->
<div class="modal fade" id="editSchoolSubjectModal" tabindex="-1" aria-labelledby="editSchoolSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editSchoolSubjectModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit School Subject
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editSchoolSubjectForm">
                @csrf
                <input type="hidden" name="subjectID" id="edit_subjectID">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="subject_name" id="edit_subject_name" class="form-control" placeholder="e.g., Mathematics, English, Kiswahili" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                        <input type="text" name="subject_code" id="edit_subject_code" class="form-control" placeholder="e.g., MAT, ENG, KIS" maxlength="20">
                        <small class="text-muted">Optional: Enter a short code for this subject</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Update Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Class Subject Modal -->
<div class="modal fade" id="addClassSubjectModal" tabindex="-1" aria-labelledby="addClassSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addClassSubjectModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Class Subject
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClassSubjectForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Subclass <span class="text-danger">*</span></label>
                        <select name="subclassID" id="class_subject_subclass_select" class="form-select" required>
                            <option value="">Choose a subclass...</option>
                            @foreach($subclasses as $subclass)
                                <option value="{{ $subclass->subclassID }}">
                                    {{ $subclass->display_name ?? ($subclass->stream_code ?? ($subclass->class_name . ' ' . $subclass->subclass_name)) }}
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary' && $subclass->combie_name)
                                        - {{ $subclass->combie_name }}
                                        @if($subclass->combie_code)
                                            ({{ $subclass->combie_code }})
                                        @endif
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                <i class="bi bi-info-circle"></i> Showing subclass name with combination
                            @else
                                <i class="bi bi-info-circle"></i> Showing subclass name
                            @endif
                        </small>
                    </div>
                    <div class="alert alert-info mb-3" id="subclassSelectedAlert" style="display: none;">
                        <i class="bi bi-info-circle"></i> <strong>Note:</strong> Subjects that are already added to this subclass will be disabled (grayed out).
                    </div>

                    <!-- Dynamic Subjects Container -->
                    <div id="classSubjectsContainer">
                        <!-- First Subject Row -->
                        <div class="subject-row mb-4 p-3 border rounded" data-row-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-primary-custom">
                                    <i class="bi bi-book"></i> Subject <span class="subject-number">1</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-danger remove-subject-row" style="display: none;">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Subject <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="subjects[0][subjectID]" class="form-select subject-select" required>
                                        <option value="">Choose a subject...</option>
                                        @foreach($schoolSubjects as $subject)
                                            @if($subject->status == 'Active')
                                                <option value="{{ $subject->subjectID }}">
                                                    {{ $subject->subject_name }} @if($subject->subject_code)({{ $subject->subject_code }})@endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <small class="text-muted">Only active subjects are shown</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Teacher</label>
                                <select name="subjects[0][teacherID]" class="form-select teacher-select">
                                    <option value="">Select Teacher (Optional)</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">
                                            {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select name="subjects[0][status]" class="form-select status-select" required>
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Student Status</label>
                                <select name="subjects[0][student_status]" class="form-select student-status-select">
                                    <option value="">Select Student Status (Optional)</option>
                                    <option value="Required">Required - All students must take this subject</option>
                                    <option value="Optional">Optional - Students can choose to take this subject</option>
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> This determines if this subject is mandatory or optional for students. If Optional, students can elect to take it.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Add Another Subject Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-primary-custom" id="addAnotherSubjectBtn">
                            <i class="bi bi-plus-circle"></i> Add Another Subject
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save All Subjects
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Class Subject Modal -->
<div class="modal fade" id="editClassSubjectModal" tabindex="-1" aria-labelledby="editClassSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editClassSubjectModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Subject Teacher
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editClassSubjectForm">
                @csrf
                <input type="hidden" name="class_subjectID">
                <input type="hidden" name="subclassID">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject_name" id="edit_subject_name" class="form-control" readonly style="background-color: #e9ecef; font-weight: 600; cursor: not-allowed;" tabindex="-1">
                        <small class="text-muted">Subject name cannot be changed</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Teacher</label>
                        <select name="teacherID" id="edit_teacher_select" class="form-select">
                            <option value="">Select Teacher (Optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select a teacher to assign or leave empty to unassign</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Teacher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Class Subjects Modal -->
<div class="modal fade" id="viewClassSubjectsModal" tabindex="-1" aria-labelledby="viewClassSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewClassSubjectsModalLabel">
                    <i class="bi bi-book-fill"></i> Class Subjects
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Search Box -->
                <div class="mb-3" id="classSubjectsSearchContainer" style="display: none;">
                    <div class="input-group">
                        <span class="input-group-text bg-primary-custom text-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="classSubjectsSearchInput" placeholder="Search by subclass name...">
                        <button class="btn btn-outline-secondary" type="button" id="clearClassSubjectsSearch" title="Clear Search">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
                <div id="classSubjectsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading class subjects...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeViewClassSubjectsModal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Subclass Subjects Modal -->
<div class="modal fade" id="viewSubclassSubjectsModal" tabindex="-1" aria-labelledby="viewSubclassSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewSubclassSubjectsModalLabel">
                    <i class="bi bi-mortarboard-fill"></i> <span id="subclassModalTitle">Subclass Subjects</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="subclassSubjectsModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subjects...</p>
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

<!-- Subject Election Modal -->
<div class="modal fade" id="subjectElectionModal" tabindex="-1" aria-labelledby="subjectElectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="subjectElectionModalLabel">
                    <i class="bi bi-person-check"></i> <span id="electionSubjectTitle">Subject Election</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="electionStudentsContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading students...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary-custom" id="saveElectionBtn">
                    <i class="bi bi-save"></i> Save Election
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Class Subject Modal -->
<div class="modal fade" id="editClassSubjectModal" tabindex="-1" aria-labelledby="editClassSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editClassSubjectModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Subject Teacher
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editClassSubjectForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="class_subjectID" id="edit_class_subject_id">
                    <input type="hidden" name="subclassID" id="edit_subclass_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject</label>
                        <input type="text" class="form-control" id="edit_subject_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Teacher</label>
                        <select name="teacherID" id="edit_teacher_id" class="form-select">
                            <option value="">Select Teacher (Optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Wait for jQuery and SweetAlert to be loaded
    (function($) {
        'use strict';

        // Check if jQuery is available
        if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }

        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded!');
        }

        $(document).ready(function() {
            console.log('Document ready, jQuery version:', $.fn.jquery);
            console.log('SweetAlert2 loaded:', typeof Swal !== 'undefined');
            console.log('Add School Subject Form exists:', $('#addSchoolSubjectForm').length > 0);
            console.log('Add Class Subject Form exists:', $('#addClassSubjectForm').length > 0);

            // Store user permissions for JavaScript checks
            var userPermissions = @json($teacherPermissions ?? collect());
            var userType = @json($user_type ?? '');

            // Helper function to check permission
            function hasPermission(permissionName) {
                if (userType === 'Admin') {
                    return true;
                }
                return userPermissions.includes(permissionName);
            }

        // Add Another School Subject Row
        let schoolSubjectRowCount = 0;
        $(document).on('click', '#addAnotherSchoolSubjectBtn', function() {
            schoolSubjectRowCount++;
            const rowIndex = schoolSubjectRowCount;
            const subjectNumber = schoolSubjectRowCount + 1;
            
            const newRow = `
                <div class="subject-row mb-4 p-3 border rounded" data-row-index="${rowIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-primary-custom">
                            <i class="bi bi-book"></i> Subject <span class="subject-number">${subjectNumber}</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger remove-subject-row">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="subjects[${rowIndex}][subject_name]" class="form-control subject-name" placeholder="e.g., Mathematics, English, Kiswahili" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                        <input type="text" name="subjects[${rowIndex}][subject_code]" class="form-control subject-code" placeholder="e.g., MAT, ENG, KIS" maxlength="20">
                        <small class="text-muted">Optional: Enter a short code for this subject</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="subjects[${rowIndex}][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            `;
            
            $('#schoolSubjectsContainer').append(newRow);
            
            // Update remove button visibility
            updateRemoveButtonsVisibility();
        });

        // Remove Subject Row
        $(document).on('click', '.remove-subject-row', function() {
            $(this).closest('.subject-row').remove();
            updateSubjectNumbers();
            updateRemoveButtonsVisibility();
        });

        // Function to update subject numbers
        function updateSubjectNumbers() {
            $('.subject-row').each(function(index) {
                $(this).find('.subject-number').text(index + 1);
                $(this).attr('data-row-index', index);
            });
        }

        // Function to update remove button visibility
        function updateRemoveButtonsVisibility() {
            const rowCount = $('.subject-row').length;
            $('.remove-subject-row').each(function() {
                if (rowCount > 1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            }

        // Handle Add School Subject Form Submission
        $(document).on('submit', '#addSchoolSubjectForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('create_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to create subjects.'
                });
                return false;
            }

            // Collect all subjects data - scope to school subjects container only
            const subjects = [];
            let hasError = false;
            let errorMessage = '';

            $('#schoolSubjectsContainer .subject-row').each(function(index) {
                const $row = $(this);
                const subjectName = ($row.find('.subject-name').val() || '').trim();
                const subjectCode = ($row.find('.subject-code').val() || '').trim();
                const status = $row.find('.status-select').val() || 'Active';

                console.log('Processing school subject row:', index + 1, {
                    subjectName: subjectName,
                    subjectCode: subjectCode,
                    status: status
                });

                // Validation
                if (!subjectName) {
                    hasError = true;
                    errorMessage = `Please enter subject name for Subject ${index + 1}.`;
                    $row.find('.subject-name').addClass('is-invalid');
                    return false;
                }

                subjects.push({
                    subject_name: subjectName,
                    subject_code: subjectCode || null,
                    status: status
                });
            });

            console.log('Total school subjects collected:', subjects.length, subjects);

            if (hasError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMessage
                });
                return false;
            }

            if (subjects.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please add at least one subject.'
                });
                return false;
            }

            console.log('Add School Subject Form submitted');

            var formData = {
                subjects: subjects,
                _token: $('input[name="_token"]', '#addSchoolSubjectForm').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_school_subject') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || formData._token
                },
                success: function(response) {
                    console.log('Success Response:', response);

                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000,
                            showConfirmButton: true
                        }).then(function() {
                            $('#addSchoolSubjectModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 100);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        xhr: xhr,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorMsg = 'Validation errors:\n';
                        for (let field in errors) {
                            errorMsg += '- ' + errors[field] + '\n';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 0 || xhr.status === 500) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection or try again.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Something went wrong. Please try again.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });

        // Handle Edit School Subject Button Click
        $(document).on('click', '.edit-school-subject-btn', function() {
            const subjectID = $(this).data('subject-id');
            const subjectName = $(this).data('subject-name');
            const subjectCode = $(this).data('subject-code') || '';

            $('#edit_subjectID').val(subjectID);
            $('#edit_subject_name').val(subjectName);
            $('#edit_subject_code').val(subjectCode);
            
            // Get current status from the activate button
            const currentStatus = $(this).closest('.card').find('.activate-subject-btn').data('current-status');
            $('#edit_status').val(currentStatus);

            $('#editSchoolSubjectModal').modal('show');
        });

        // Handle Edit School Subject Form Submission
        $(document).on('submit', '#editSchoolSubjectForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('edit_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit subjects.'
                });
                return false;
            }

            const formData = {
                subjectID: $('#edit_subjectID').val(),
                subject_name: $('#edit_subject_name').val(),
                subject_code: $('#edit_subject_code').val(),
                status: $('#edit_status').val(),
                _token: $('input[name="_token"]', '#editSchoolSubjectForm').val()
            };

            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_school_subject') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || formData._token
                },
                success: function(response) {
                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000,
                            showConfirmButton: true
                        }).then(function() {
                            $('#editSchoolSubjectModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 100);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).html(originalText);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : (xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Something went wrong. Please try again.');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Handle Delete School Subject Button Click
        $(document).on('click', '.delete-school-subject-btn', function() {
            const subjectID = $(this).data('subject-id');
            const subjectName = $(this).data('subject-name');

            Swal.fire({
                title: 'Delete Subject?',
                html: 'Are you sure you want to delete <strong>' + subjectName + '</strong>?<br><br>This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        html: 'Please wait while we delete the subject.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Get CSRF token
                    const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
                    
                    if (!csrfToken) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'CSRF token not found. Please refresh the page and try again.',
                            confirmButtonColor: '#940000'
                        });
                        return;
                    }
                    
                    $.ajax({
                        url: "{{ route('delete_school_subject', ':id') }}".replace(':id', subjectID),
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        data: {
                            _token: csrfToken
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.success,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.error || 'Failed to delete subject',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('Delete error:', xhr);
                            let errorMsg = 'Failed to delete subject';
                            
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.error) {
                                    errorMsg = xhr.responseJSON.error;
                                } else if (xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                            } else if (xhr.responseText) {
                                try {
                                    const parsed = JSON.parse(xhr.responseText);
                                    errorMsg = parsed.error || parsed.message || errorMsg;
                                } catch (e) {
                                    errorMsg = xhr.responseText || errorMsg;
                                }
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg,
                                confirmButtonColor: '#940000',
                                width: '600px'
                            });
                        }
                    });
                }
            });
        });

        // Handle Subclass Selection Change - Disable already added subjects
        $(document).on('change', '#class_subject_subclass_select', function() {
            const subclassID = $(this).val();
            
            if (!subclassID) {
                $('#subclassSelectedAlert').hide();
                // Reset all subject selects
                $('.subject-select').each(function() {
                    $(this).find('option').prop('disabled', false).css({
                        'background-color': '',
                        'color': ''
                    });
                });
                return;
            }
            
            $('#subclassSelectedAlert').show();
            
            // Fetch already added subjects for this subclass
            $.ajax({
                url: "{{ route('get_class_subjects_by_subclass', ':id') }}".replace(':id', subclassID),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.subjects) {
                        addedSubjectIDsCache = response.subjects.map(s => s.subjectID.toString());
                        
                        // Disable already added subjects in all subject selects
                        $('.subject-select').each(function() {
                            disableAlreadyAddedSubjects($(this), addedSubjectIDsCache);
                        });
                    } else {
                        addedSubjectIDsCache = [];
                    }
                },
                error: function() {
                    console.error('Failed to fetch already added subjects');
                    addedSubjectIDsCache = [];
                }
            });
        });

        // Function to disable already added subjects in a select element
        function disableAlreadyAddedSubjects($selectElement, addedSubjectIDs) {
            $selectElement.find('option').each(function() {
                const optionValue = $(this).val();
                if (optionValue && optionValue !== '') {
                    if (addedSubjectIDs.includes(optionValue)) {
                        $(this).prop('disabled', true)
                            .css({
                                'background-color': '#e9ecef',
                                'color': '#6c757d'
                            });
                        
                        let optionText = $(this).text();
                        if (optionText.indexOf('(Already Added)') === -1) {
                            $(this).text(optionText + ' (Already Added)');
                        }
                    } else {
                        $(this).prop('disabled', false)
                            .css({
                                'background-color': '',
                                'color': ''
                            });
                        
                        let optionText = $(this).text();
                        if (optionText.indexOf('(Already Added)') !== -1) {
                            $(this).text(optionText.replace(' (Already Added)', ''));
                        }
                    }
                }
            });
        }

        // Add Another Subject Row
        let subjectRowCount = 0;
        let addedSubjectIDsCache = [];
        
        $(document).on('click', '#addAnotherSubjectBtn', function() {
            subjectRowCount++;
            const rowIndex = subjectRowCount;
            const subjectNumber = subjectRowCount + 1;
            
            const newRow = `
                <div class="subject-row mb-4 p-3 border rounded" data-row-index="${rowIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-primary-custom">
                            <i class="bi bi-book"></i> Subject <span class="subject-number">${subjectNumber}</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger remove-subject-row">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Subject <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="subjects[${rowIndex}][subjectID]" class="form-select subject-select" required>
                                <option value="">Choose a subject...</option>
                                @foreach($schoolSubjects as $subject)
                                    @if($subject->status == 'Active')
                                        <option value="{{ $subject->subjectID }}">
                                            {{ $subject->subject_name }} @if($subject->subject_code)({{ $subject->subject_code }})@endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">Only active subjects are shown</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Teacher</label>
                        <select name="subjects[${rowIndex}][teacherID]" class="form-select teacher-select">
                            <option value="">Select Teacher (Optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="subjects[${rowIndex}][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student Status</label>
                        <select name="subjects[${rowIndex}][student_status]" class="form-select student-status-select">
                            <option value="">Select Student Status (Optional)</option>
                            <option value="Required">Required - All students must take this subject</option>
                            <option value="Optional">Optional - Students can choose to take this subject</option>
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> This determines if this subject is mandatory or optional for students. If Optional, students can elect to take it.
                        </small>
                    </div>
                </div>
            `;
            
            $('#classSubjectsContainer').append(newRow);
            updateSubjectNumbers();
        });

        // Remove Subject Row
        $(document).on('click', '.remove-subject-row', function() {
            $(this).closest('.subject-row').remove();
            updateSubjectNumbers();
        });

        // Update Subject Numbers
        function updateSubjectNumbers() {
            $('.subject-row').each(function(index) {
                $(this).find('.subject-number').text(index + 1);
                // Show remove button if more than one row
                if ($('.subject-row').length > 1) {
                    $(this).find('.remove-subject-row').show();
                } else {
                    $(this).find('.remove-subject-row').hide();
                }
            });
        }


        // Reset form when modal is closed
        $('#addClassSubjectModal').on('hidden.bs.modal', function() {
            $('#addClassSubjectForm')[0].reset();
            $('#subclassSelectedAlert').hide();
            addedSubjectIDsCache = [];
            $('#classSubjectsContainer').html(`
                <div class="subject-row mb-4 p-3 border rounded" data-row-index="0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-primary-custom">
                            <i class="bi bi-book"></i> Subject <span class="subject-number">1</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger remove-subject-row" style="display: none;">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Subject <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="subjects[0][subjectID]" class="form-select subject-select" required>
                                <option value="">Choose a subject...</option>
                                @foreach($schoolSubjects as $subject)
                                    @if($subject->status == 'Active')
                                        <option value="{{ $subject->subjectID }}">
                                            {{ $subject->subject_name }} @if($subject->subject_code)({{ $subject->subject_code }})@endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">Only active subjects are shown</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Teacher</label>
                        <select name="subjects[0][teacherID]" class="form-select teacher-select">
                            <option value="">Select Teacher (Optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="subjects[0][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student Status</label>
                        <select name="subjects[0][student_status]" class="form-select student-status-select">
                            <option value="">Select Student Status (Optional)</option>
                            <option value="Required">Required - All students must take this subject</option>
                            <option value="Optional">Optional - Students can choose to take this subject</option>
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> This determines if this subject is mandatory or optional for students. If Optional, students can elect to take it.
                        </small>
                    </div>
                </div>
            `);
            subjectRowCount = 0;
        });

        // Handle Add Class Subject Form Submission
        $(document).on('submit', '#addClassSubjectForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('Add Class Subject Form submitted');

            if (!hasPermission('create_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to create class subjects.'
                });
                return false;
            }

            const subclassID = $('#class_subject_subclass_select').val();
            console.log('Subclass ID:', subclassID);
            
            if (!subclassID) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a subclass first.'
                });
                return false;
            }

            // Collect all subjects data - scope to class subjects container only
            const subjects = [];
            let hasError = false;
            let errorMessage = '';

            $('#classSubjectsContainer .subject-row').each(function(index) {
                const $row = $(this);
                const subjectID = $row.find('.subject-select').val() || '';
                const teacherID = $row.find('.teacher-select').val() || '';
                const status = $row.find('.status-select').val() || 'Active';
                const studentStatus = $row.find('.student-status-select').val() || null;

                console.log('Processing subject row:', index + 1, {
                    subjectID: subjectID,
                    status: status,
                    studentStatus: studentStatus
                });

                // Validation
                if (!subjectID) {
                    hasError = true;
                    errorMessage = `Please select a subject for Subject ${index + 1}.`;
                    return false; // This breaks the each loop, not the function
                }

                subjects.push({
                    subjectID: subjectID,
                    teacherID: teacherID || null,
                    status: status,
                    student_status: studentStatus
                });
            });

            console.log('Total subjects collected:', subjects.length, subjects);

            if (hasError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMessage
                });
                return false;
            }

            if (subjects.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please add at least one subject.'
                });
                return false;
            }

            console.log('Add Class Subject Form submitted');

            var formData = {
                subclassID: subclassID,
                subjects: subjects,
                _token: $('input[name="_token"]', '#addClassSubjectForm').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_class_subject') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || formData._token
                },
                success: function(response) {
                    console.log('Success Response:', response);

                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000,
                            showConfirmButton: true
                        }).then(function() {
                            $('#addClassSubjectModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 100);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        xhr: xhr,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorMsg = 'Validation errors:\n';
                        for (let field in errors) {
                            errorMsg += '- ' + errors[field] + '\n';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 0 || xhr.status === 500) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection or try again.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Something went wrong. Please try again.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });

        // Handle View Class Subjects Button Click
        $(document).on('click', '#viewClassSubjectsBtn', function(e) {
            e.preventDefault();

            if (!hasPermission('view_class_subjects')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to view class subjects.'
                });
                return false;
            }

            console.log('View Class Subjects clicked');

            $('#viewClassSubjectsModal').modal('show');
            $('#classSubjectsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading class subjects...</p></div>');

            $.ajax({
                url: "{{ route('get_class_subjects') }}",
                type: "GET",
                success: function(response) {
                    console.log('Class subjects data received:', response);

                    if (response.success && response.subclasses && response.subclasses.length > 0) {
                        var html = '<div class="row">';

                        response.subclasses.forEach(function(subclass) {
                            html += '<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">';
                            html += '<div class="card shadow-sm h-100">';

                            // Card Header
                            var subclassDisplayName = subclass.display_name || subclass.stream_code || (subclass.class_name + ' ' + subclass.subclass_name);
                            html += '<div class="card-header bg-primary-custom text-white text-center">';
                            html += '<div class="d-flex justify-content-center align-items-center mb-2">';
                            html += '<i class="bi bi-mortarboard-fill" style="font-size: 2rem;"></i>';
                            html += '</div>';
                            html += '<h6 class="mb-0 fw-bold text-white subclass-name" data-subclass-name="' + subclassDisplayName.toLowerCase() + '">' + subclassDisplayName + '</h6>';
                            html += '</div>';

                            // Card Body
                            html += '<div class="card-body">';

                            // Subject Count - Using Bootstrap utilities
                            html += '<div class="row mb-3">';
                            html += '<div class="col-12">';
                            html += '<div class="text-center p-3 bg-light rounded border border-primary-custom">';
                            html += '<i class="bi bi-book-fill text-primary-custom d-block mb-2" style="font-size: 1.5rem;"></i>';
                            html += '<div class="h4 mb-1 fw-bold">' + subclass.subject_count + '</div>';
                            html += '<small class="text-muted fw-bold">Subjects</small>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';

                            // Hidden Details Section (View More)
                            html += '<div class="subclass-subjects-details-' + (subclass.subclassID || subclass.id) + '" style="display: none;">';

                            // Subjects List - Using Bootstrap utilities
                            if (subclass.subjects && subclass.subjects.length > 0) {
                                html += '<div class="mt-3">';
                                html += '<div class="d-flex align-items-center mb-2">';
                                html += '<i class="bi bi-list-ul text-primary-custom me-2"></i>';
                                html += '<strong class="small">Subject List:</strong>';
                                html += '</div>';
                                html += '<div class="list-group">';
                                subclass.subjects.forEach(function(subject) {
                                    html += '<div class="list-group-item">';
                                    html += '<div class="d-flex align-items-start justify-content-between">';
                                    html += '<div class="d-flex align-items-start flex-grow-1">';
                                    html += '<i class="bi bi-book-fill text-primary-custom me-2 mt-1"></i>';
                                    html += '<div class="flex-grow-1">';
                                    html += '<div class="d-flex align-items-center justify-content-between mb-1">';
                                    html += '<div class="fw-bold">' + subject.subject_name + '</div>';
                                    // Student Status Badge
                                    if (subject.student_status) {
                                        var statusBadgeClass = subject.student_status === 'Required' ? 'bg-warning text-dark' : 'bg-info text-white';
                                        html += '<span class="badge ' + statusBadgeClass + ' ms-2">' + subject.student_status + '</span>';
                                    }
                                    html += '</div>';
                                    // Election Stats for Optional subjects
                                    if (subject.student_status === 'Optional') {
                                        var electedCount = subject.elected_count || 0;
                                        var nonElectedCount = subject.non_elected_count || 0;
                                        var totalStudents = subject.total_students || 0;
                                        html += '<div class="mt-1 mb-2">';
                                        html += '<small class="text-muted">';
                                        html += '<span class="badge bg-success me-1">Elected: ' + electedCount + '</span>';
                                        html += '<span class="badge bg-secondary">Not Elected: ' + nonElectedCount + '</span>';
                                        html += ' <span class="text-muted">(Total: ' + totalStudents + ')</span>';
                                        html += '</small>';
                                        html += '</div>';
                                    }
                                    if (subject.subject_code) {
                                        html += '<div class="mb-1">';
                                        html += '<small class="text-muted d-block">';
                                        html += '<i class="bi bi-code-square text-primary-custom me-1"></i>Code: ' + subject.subject_code;
                                        html += '</small>';
                                        html += '</div>';
                                    }
                                    if (subject.teacher_name && subject.teacher_name !== 'Not Assigned') {
                                        html += '<div>';
                                        html += '<small class="text-muted d-block">';
                                        html += '<i class="bi bi-person-badge text-info me-1"></i>Teacher: ' + subject.teacher_name;
                                        html += '</small>';
                                        html += '</div>';
                                    }
                                    html += '</div>';
                                    html += '</div>';
                                    // Action buttons
                                    html += '<div class="d-flex gap-1 ms-2 flex-column">';
                                    // Election button for optional subjects
                                    if (subject.student_status === 'Optional') {
                                        html += '<button class="btn btn-sm btn-primary text-white election-subject-btn mb-1" ';
                                        html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                        html += 'data-subject-name="' + subject.subject_name + '" ';
                                        html += 'data-subclass-id="' + (subclass.subclassID || subclass.id) + '" ';
                                        html += 'title="Manage Subject Election">';
                                        html += '<i class="bi bi-person-check"></i> Election';
                                        html += '</button>';
                                    }
                                    html += '<div class="d-flex gap-1">';
                                    var canEdit = hasPermission('update_class_subject');
                                    var canDelete = hasPermission('delete_class_subject');
                                    var canActivate = hasPermission('activate_class_subject');
                                    var subjectStatus = subject.status || 'Inactive';
                                    if (canActivate) {
                                        var activateBtnClass = subjectStatus === 'Active' ? 'btn-secondary' : 'btn-success';
                                        var activateIcon = subjectStatus === 'Active' ? 'x-circle' : 'check-circle';
                                        var activateTitle = subjectStatus === 'Active' ? 'Deactivate' : 'Activate';
                                        html += '<button class="btn btn-sm ' + activateBtnClass + ' text-white activate-class-subject-btn" ';
                                        html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                        html += 'data-subject-name="' + subject.subject_name + '" ';
                                        html += 'data-current-status="' + subjectStatus + '" ';
                                        html += 'title="' + activateTitle + ' Subject">';
                                        html += '<i class="bi bi-' + activateIcon + '-fill"></i>';
                                        html += '</button>';
                                    }
                                    if (canEdit) {
                                        html += '<button class="btn btn-sm btn-warning text-dark edit-class-subject-btn" ';
                                        html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                        html += 'data-subject-name="' + subject.subject_name + '" ';
                                        html += 'data-teacher-id="' + (subject.teacher_id || '') + '" ';
                                        html += 'data-subclass-id="' + (subclass.subclassID || subclass.id) + '" ';
                                        html += 'title="Edit Teacher">';
                                        html += '<i class="bi bi-pencil-square"></i>';
                                        html += '</button>';
                                    }
                                    if (canDelete) {
                                        html += '<button class="btn btn-sm btn-danger text-white delete-class-subject-btn" ';
                                        html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                        html += 'data-subject-name="' + subject.subject_name + '" ';
                                        html += 'title="Delete Subject">';
                                        html += '<i class="bi bi-trash-fill"></i>';
                                        html += '</button>';
                                    }
                                    html += '</div>'; // End action buttons flex
                                    html += '</div>'; // End main flex column
                                    html += '</div>';
                                    html += '</div>';
                                });
                                html += '</div>';
                                html += '</div>';
                            } else {
                                html += '<div class="text-center py-3">';
                                html += '<i class="bi bi-inbox text-muted" style="font-size: 1.5rem;"></i>';
                                html += '<p class="mt-2 mb-0 small text-muted">No subjects assigned</p>';
                                html += '</div>';
                            }

                            html += '</div>'; // End hidden details
                            html += '</div>'; // End card-body

                            // Card Footer with View More Button
                            html += '<div class="card-footer bg-white">';
                            html += '<button class="btn btn-sm btn-outline-primary view-more-subject-btn w-100" data-subclass-id="' + (subclass.subclassID || subclass.id) + '" data-expanded="false">';
                            html += '<i class="bi bi-chevron-down"></i> View More';
                            html += '</button>';
                            html += '</div>'; // End card-footer

                            html += '</div>'; // End card
                            html += '</div>'; // End col
                        });

                        html += '</div>'; // End row
                        $('#classSubjectsContent').html(html);
                        
                        // Show search box
                        $('#classSubjectsSearchContainer').show();
                        
                        // Initialize search functionality
                        initializeClassSubjectsSearch();
                    } else {
                        $('#classSubjectsContent').html(
                            '<div class="text-center py-5">' +
                            '<i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>' +
                            '<p class="mt-3 mb-0 text-muted">No class subjects found. Click "Add Class Subject" to assign subjects to subclasses.</p>' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching class subjects:', xhr);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : 'Failed to load class subjects. Please try again.';

                    $('#classSubjectsContent').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> ' + errorMsg +
                        '</div>'
                    );
                }
            });
        });

        // Initialize Class Subjects Search Functionality
        function initializeClassSubjectsSearch() {
            var $searchInput = $('#classSubjectsSearchInput');
            var $clearBtn = $('#clearClassSubjectsSearch');
            var $container = $('#classSubjectsContent');
            
            if ($container.length === 0) return;
            
            // Search functionality
            $searchInput.on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                var $cards = $container.find('.card');
                
                if (searchTerm === '') {
                    $cards.closest('.col-12, .col-sm-6, .col-md-4, .col-lg-3').show();
                    $clearBtn.prop('disabled', true);
                } else {
                    $clearBtn.prop('disabled', false);
                    var visibleCount = 0;
                    
                    $cards.each(function() {
                        var $card = $(this);
                        var $col = $card.closest('.col-12, .col-sm-6, .col-md-4, .col-lg-3');
                        // Search only in subclass name (from card header)
                        var subclassName = $card.find('.subclass-name').data('subclass-name') || $card.find('.subclass-name').text().toLowerCase();
                        
                        if (subclassName && subclassName.indexOf(searchTerm) > -1) {
                            $col.show();
                            visibleCount++;
                        } else {
                            $col.hide();
                        }
                    });
                    
                    // Show message if no results
                    if (visibleCount === 0) {
                        if ($container.find('.no-results-message').length === 0) {
                            $container.append('<div class="no-results-message alert alert-info mt-3"><i class="bi bi-info-circle"></i> No results found matching "' + searchTerm + '"</div>');
                        }
                    } else {
                        $container.find('.no-results-message').remove();
                    }
                }
            });
            
            // Clear search
            $clearBtn.on('click', function() {
                $searchInput.val('');
                $searchInput.trigger('keyup');
                $searchInput.focus();
            });
            
            // Enable clear button on input
            $searchInput.on('input', function() {
                if ($(this).val().length > 0) {
                    $clearBtn.prop('disabled', false);
                } else {
                    $clearBtn.prop('disabled', true);
                }
            });
        }
        
        // Clear search when modal is closed
        $('#viewClassSubjectsModal').on('hidden.bs.modal', function() {
            $('#classSubjectsSearchInput').val('');
            $('#classSubjectsSearchContainer').hide();
            $('#classSubjectsContent').find('.no-results-message').remove();
            // Clear content to free memory
            $('#classSubjectsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading class subjects...</p></div>');
        });

        // Handle close button click
        $(document).on('click', '#closeViewClassSubjectsModal', function() {
            $('#viewClassSubjectsModal').modal('hide');
        });

        // Handle View More Button Click (Class Subjects) - Open Modal
        $(document).on('click', '.view-more-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var subclassID = $(this).data('subclass-id');
            var $card = $(this).closest('.card');
            var subclassName = $card.find('.subclass-name').text() || 'Subclass';

            console.log('View More clicked for subclassID:', subclassID, 'subclassName:', subclassName);

            // Set modal title
            $('#subclassModalTitle').html('<i class="bi bi-mortarboard-fill"></i> ' + subclassName);

            // Show loading
            $('#subclassSubjectsModalBody').html(
                '<div class="text-center py-4">' +
                '<div class="spinner-border text-primary-custom" role="status">' +
                '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '<p class="mt-2">Loading subjects...</p>' +
                '</div>'
            );

            // Open modal
            $('#viewSubclassSubjectsModal').modal('show');

            // Find the details section to get subjects data
            var $details = $('.subclass-subjects-details-' + subclassID);
            if ($details.length === 0) {
                $details = $card.find('[class*="subclass-subjects-details"]');
            }

            if ($details.length > 0) {
                // Get the list-group content from the hidden section
                var $listGroup = $details.find('.list-group');
                if ($listGroup.length > 0) {
                    // Clone the list-group items
                    var subjectsHtml = $listGroup.html();
                    $('#subclassSubjectsModalBody').html(
                        '<div class="mt-3">' +
                        '<div class="d-flex align-items-center mb-3">' +
                        '<i class="bi bi-list-ul text-primary-custom me-2"></i>' +
                        '<strong>Subject List:</strong>' +
                        '</div>' +
                        '<div class="list-group">' +
                        subjectsHtml +
                        '</div>' +
                        '</div>'
                    );
                } else {
                    // If no subjects, show message from hidden section
                    var noSubjectsMsg = $details.find('.text-center').html() || 
                        '<div class="text-center py-3">' +
                        '<i class="bi bi-inbox text-muted" style="font-size: 1.5rem;"></i>' +
                        '<p class="mt-2 mb-0 small text-muted">No subjects assigned</p>' +
                        '</div>';
                    $('#subclassSubjectsModalBody').html(noSubjectsMsg);
                }
            } else {
                // If not found, show message
                $('#subclassSubjectsModalBody').html(
                    '<div class="alert alert-info text-center">' +
                    '<i class="bi bi-info-circle"></i> No subjects found for this subclass.' +
                    '</div>'
                );
            }
        });

        // Refresh view class subjects modal after actions in subclass modal
        $('#viewSubclassSubjectsModal').on('hidden.bs.modal', function() {
            // Refresh the main view class subjects modal if it's open
            if ($('#viewClassSubjectsModal').hasClass('show')) {
                $('#viewClassSubjectsBtn').click();
            }
        });

        // Handle Election Button Click
        $(document).on('click', '.election-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');
            var subclassID = $(this).data('subclass-id');

            console.log('Election clicked for classSubjectID:', classSubjectID, 'subjectName:', subjectName);

            // Set modal title
            $('#electionSubjectTitle').text(subjectName + ' Election');

            // Show loading
            $('#electionStudentsContainer').html(
                '<div class="text-center py-4">' +
                '<div class="spinner-border text-primary-custom" role="status">' +
                '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '<p class="mt-2">Loading students...</p>' +
                '</div>'
            );

            // Store classSubjectID for save button
            $('#saveElectionBtn').data('class-subject-id', classSubjectID);
            $('#saveElectionBtn').data('subclass-id', subclassID);

            // Open modal
            $('#subjectElectionModal').modal('show');

            // Fetch students for this subclass
            $.ajax({
                url: "{{ route('get_subclass_students', ':id') }}".replace(':id', subclassID),
                type: "GET",
                dataType: 'json',
                success: function(response) {
                    console.log('Students data received:', response);

                    if (response.success && response.students && response.students.length > 0) {
                        // Fetch already elected students
                        var electorsUrl = "{{ url('get_subject_electors') }}/" + classSubjectID;
                        console.log('Fetching electors from:', electorsUrl);
                        
                        $.ajax({
                            url: electorsUrl,
                            type: "GET",
                            dataType: 'json',
                            success: function(electorsResponse) {
                                console.log('Electors data received:', electorsResponse);
                                
                                var electedStudentIDs = [];
                                if (electorsResponse.success && electorsResponse.electors) {
                                    electedStudentIDs = electorsResponse.electors.map(function(e) {
                                        return e.studentID;
                                    });
                                }

                                // Build table
                                var html = '<div class="table-responsive">';
                                html += '<table class="table table-hover table-bordered" id="electionStudentsTable">';
                                html += '<thead class="bg-primary-custom text-white">';
                                html += '<tr>';
                                html += '<th style="width: 50px;">#</th>';
                                html += '<th>Student Name</th>';
                                html += '<th>Admission Number</th>';
                                html += '<th style="width: 100px; text-align: center;">Election</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';

                                response.students.forEach(function(student, index) {
                                    var isElected = electedStudentIDs.includes(student.studentID);
                                    html += '<tr data-student-id="' + student.studentID + '">';
                                    html += '<td>' + (index + 1) + '</td>';
                                    html += '<td>' + student.first_name + ' ' + student.last_name + '</td>';
                                    html += '<td>' + (student.admission_number || 'N/A') + '</td>';
                                    html += '<td style="text-align: center;">';
                                    if (isElected) {
                                        // Show deselect button for already elected students
                                        html += '<button class="btn btn-sm btn-danger deselect-student-btn" ';
                                        html += 'data-student-id="' + student.studentID + '" ';
                                        html += 'data-class-subject-id="' + classSubjectID + '" ';
                                        html += 'data-student-name="' + student.first_name + ' ' + student.last_name + '" ';
                                        html += 'title="Deselect Student">';
                                        html += '<i class="bi bi-x-circle"></i> Deselect';
                                        html += '</button>';
                                    } else {
                                        // Show checkbox for non-elected students
                                        html += '<input type="checkbox" class="form-check-input election-checkbox" ';
                                        html += 'data-student-id="' + student.studentID + '" ';
                                        html += 'value="' + student.studentID + '" ';
                                        html += '>';
                                    }
                                    html += '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody>';
                                html += '</table>';
                                html += '</div>';

                                $('#electionStudentsContainer').html(html);

                                // Initialize DataTable
                                if ($.fn.DataTable) {
                                    $('#electionStudentsTable').DataTable({
                                        "pageLength": 25,
                                        "order": [[1, "asc"]],
                                        "language": {
                                            "search": "Search students:"
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.error('Error fetching electors:', xhr);
                                console.error('Response:', xhr.responseJSON);
                                console.error('Status:', xhr.status);
                                
                                // If error, just continue with empty electors list (no electors yet)
                                var electedStudentIDs = [];
                                
                                // Build table
                                var html = '<div class="table-responsive">';
                                html += '<table class="table table-hover table-bordered" id="electionStudentsTable">';
                                html += '<thead class="bg-primary-custom text-white">';
                                html += '<tr>';
                                html += '<th style="width: 50px;">#</th>';
                                html += '<th>Student Name</th>';
                                html += '<th>Admission Number</th>';
                                html += '<th style="width: 100px; text-align: center;">Election</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';

                                response.students.forEach(function(student, index) {
                                    var isElected = electedStudentIDs.includes(student.studentID);
                                    html += '<tr data-student-id="' + student.studentID + '">';
                                    html += '<td>' + (index + 1) + '</td>';
                                    html += '<td>' + student.first_name + ' ' + student.last_name + '</td>';
                                    html += '<td>' + (student.admission_number || 'N/A') + '</td>';
                                    html += '<td style="text-align: center;">';
                                    if (isElected) {
                                        // Show deselect button for already elected students
                                        html += '<button class="btn btn-sm btn-danger deselect-student-btn" ';
                                        html += 'data-student-id="' + student.studentID + '" ';
                                        html += 'data-class-subject-id="' + classSubjectID + '" ';
                                        html += 'data-student-name="' + student.first_name + ' ' + student.last_name + '" ';
                                        html += 'title="Deselect Student">';
                                        html += '<i class="bi bi-x-circle"></i> Deselect';
                                        html += '</button>';
                                    } else {
                                        // Show checkbox for non-elected students
                                        html += '<input type="checkbox" class="form-check-input election-checkbox" ';
                                        html += 'data-student-id="' + student.studentID + '" ';
                                        html += 'value="' + student.studentID + '" ';
                                        html += '>';
                                    }
                                    html += '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody>';
                                html += '</table>';
                                html += '</div>';

                                $('#electionStudentsContainer').html(html);

                                // Initialize DataTable
                                if ($.fn.DataTable) {
                                    $('#electionStudentsTable').DataTable({
                                        "pageLength": 25,
                                        "order": [[1, "asc"]],
                                        "language": {
                                            "search": "Search students:"
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        $('#electionStudentsContainer').html(
                            '<div class="alert alert-info text-center">' +
                            '<i class="bi bi-info-circle"></i> No students found in this subclass.' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching students:', xhr);
                    $('#electionStudentsContainer').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> Failed to load students. Please try again.' +
                        '</div>'
                    );
                }
            });
        });

        // Handle Save Election Button Click
        $(document).on('click', '#saveElectionBtn', function(e) {
            e.preventDefault();

            var classSubjectID = $(this).data('class-subject-id');
            var subclassID = $(this).data('subclass-id');

            if (!classSubjectID || !subclassID) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Missing required data. Please try again.',
                    confirmButtonColor: '#940000'
                });
                return false;
            }

            // Collect selected students (all checked checkboxes - exclude those with deselect buttons)
            var selectedStudents = [];
            $('.election-checkbox:checked').each(function() {
                // Only include if row doesn't have deselect button (meaning not already elected)
                var $row = $(this).closest('tr');
                if ($row.find('.deselect-student-btn').length === 0) {
                    selectedStudents.push($(this).val());
                }
            });

            console.log('Saving election:', {
                classSubjectID: classSubjectID,
                subclassID: subclassID,
                selectedStudents: selectedStudents
            });

            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_subject_election') }}",
                type: "POST",
                data: {
                    classSubjectID: classSubjectID,
                    subclassID: subclassID,
                    selectedStudents: selectedStudents,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);

                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000
                        }).then(function() {
                            $('#subjectElectionModal').modal('hide');
                            // Refresh view class subjects modal if open
                            if ($('#viewClassSubjectsModal').hasClass('show')) {
                                $('#viewClassSubjectsBtn').click();
                            }
                        });
            } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorMsg = 'Validation errors:\n';
                        for (let field in errors) {
                            if (Array.isArray(errors[field])) {
                                errorMsg += '- ' + errors[field][0] + '\n';
                            } else {
                                errorMsg += '- ' + errors[field] + '\n';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Something went wrong. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });

        // Handle Deselect Student Button Click
        $(document).on('click', '.deselect-student-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            var studentID = $btn.data('student-id');
            var classSubjectID = $btn.data('class-subject-id');
            var studentName = $btn.data('student-name');

            Swal.fire({
                title: 'Deselect Student?',
                html: 'Are you sure you want to deselect <strong>' + studentName + '</strong> from this subject election?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, deselect!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    var originalText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Removing...');

                    $.ajax({
                        url: "{{ route('deselect_student') }}",
                        type: "POST",
                        data: {
                            classSubjectID: classSubjectID,
                            studentID: studentID,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response && response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.success,
                                    confirmButtonColor: '#940000',
                                    timer: 1500
                                }).then(function() {
                                    // Replace deselect button with checkbox
                                    var $row = $btn.closest('tr');
                                    var $td = $row.find('td:last');
                                    $td.html(
                                        '<input type="checkbox" class="form-check-input election-checkbox" ' +
                                        'data-student-id="' + studentID + '" ' +
                                        'value="' + studentID + '">'
                                    );
                                    
                                    // Remove from DataTable if initialized
                                    if ($.fn.DataTable && $('#electionStudentsTable').hasClass('dataTable')) {
                                        var table = $('#electionStudentsTable').DataTable();
                                        table.draw(false); // Redraw without resetting paging
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Unexpected response format.',
                                    confirmButtonColor: '#940000'
                                });
                                $btn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html(originalText);

                            let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                                ? xhr.responseJSON.error
                                : 'Something went wrong. Please try again.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg,
                                confirmButtonColor: '#940000'
                            });
                        }
                    });
                }
            });
        });

        // Handle Edit Class Subject Button Click
        $(document).on('click', '.edit-class-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('update_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to update class subjects.'
                });
                return false;
            }

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');
            var teacherID = $(this).data('teacher-id');
            var subclassID = $(this).data('subclass-id');

            console.log('Edit clicked:', {
                classSubjectID: classSubjectID,
                subjectName: subjectName,
                teacherID: teacherID,
                subclassID: subclassID
            });

            // Populate edit modal
            $('#editClassSubjectModal').find('input[name="class_subjectID"]').val(classSubjectID);
            $('#editClassSubjectModal').find('input[name="subclassID"]').val(subclassID);
            $('#editClassSubjectModal').find('#edit_subject_name').val(subjectName);
            $('#editClassSubjectModal').find('#edit_teacher_select').val(teacherID || '');

            // Show modal
            $('#editClassSubjectModal').modal('show');
        });

        // Handle Edit Class Subject Form Submission
        $(document).on('submit', '#editClassSubjectForm', function(e) {
            e.preventDefault();

            if (!hasPermission('update_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to update class subjects.'
                });
                return false;
            }

            var formData = {
                class_subjectID: $('input[name="class_subjectID"]', '#editClassSubjectForm').val(),
                subclassID: $('input[name="subclassID"]', '#editClassSubjectForm').val(),
                teacherID: $('select[name="teacherID"]', '#editClassSubjectForm').val(),
                _token: $('input[name="_token"]', '#editClassSubjectForm').val()
            };

            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_class_subject') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                success: function(response) {
                    // Always re-enable button on success
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000
                        }).then(function() {
                            $('#editClassSubjectModal').modal('hide');
                            // Refresh the view class subjects modal if it's open
                            if ($('#viewClassSubjectsModal').hasClass('show')) {
                            $('#viewClassSubjectsBtn').click();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    // Always re-enable button on error
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorMsg = 'Validation errors:\n';
                        for (let field in errors) {
                            errorMsg += '- ' + errors[field] + '\n';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Something went wrong. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });

        // Handle Delete Class Subject Button Click
        $(document).on('click', '.delete-class-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('delete_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to delete class subjects.'
                });
                return false;
            }

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to remove \"" + subjectName + "\" from this subclass? This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('delete_class_subject', ':id') }}".replace(':id', classSubjectID),
                        type: "DELETE",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.success || 'Subject removed successfully!',
                                confirmButtonColor: '#940000',
                                timer: 2000
                            }).then(function() {
                                // Refresh the view class subjects modal if it's open
                                if ($('#viewClassSubjectsModal').hasClass('show')) {
                                $('#viewClassSubjectsBtn').click();
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to remove subject. Please try again.'
                            });
                        }
                    });
                }
            });
        });

        // Handle Activate Subject Button Click
        $(document).on('click', '.activate-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('activate_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to activate subjects.'
                });
                return false;
            }

            var subjectID = $(this).data('subject-id');
            var subjectName = $(this).data('subject-name');
            var currentStatus = $(this).data('current-status');
            var action = currentStatus === 'Active' ? 'deactivate' : 'activate';

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to ' + action + ' "' + subjectName + '"?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + action + ' it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('activate_subject', ':id') }}".replace(':id', subjectID),
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.success,
                                confirmButtonColor: '#940000',
                                timer: 2000
                            }).then(function() {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to update subject status. Please try again.'
                            });
                        }
                    });
                }
            });
        });

        // Handle Activate Class Subject Button Click
        $(document).on('click', '.activate-class-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('activate_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to activate class subjects.'
                });
                return false;
            }

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');
            var currentStatus = $(this).data('current-status');
            var action = currentStatus === 'Active' ? 'deactivate' : 'activate';

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to ' + action + ' "' + subjectName + '"?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + action + ' it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('activate_class_subject', ':id') }}".replace(':id', classSubjectID),
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.success,
                                confirmButtonColor: '#940000',
                                timer: 2000
                            }).then(function() {
                                // Refresh the view class subjects modal if it's open
                                if ($('#viewClassSubjectsModal').hasClass('show')) {
                                $('#viewClassSubjectsBtn').click();
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to update class subject status. Please try again.'
                            });
                        }
                    });
                }
            });
        });

        // Reset forms when modals are closed
        // Function to reset school subject form
        function resetSchoolSubjectForm() {
            $('#addSchoolSubjectForm')[0].reset();
            $('#schoolSubjectsContainer').html(`
                <div class="subject-row mb-4 p-3 border rounded" data-row-index="0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-primary-custom">
                            <i class="bi bi-book"></i> Subject <span class="subject-number">1</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger remove-subject-row" style="display: none;">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="subjects[0][subject_name]" class="form-control subject-name" placeholder="e.g., Mathematics, English, Kiswahili" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                        <input type="text" name="subjects[0][subject_code]" class="form-control subject-code" placeholder="e.g., MAT, ENG, KIS" maxlength="20">
                        <small class="text-muted">Optional: Enter a short code for this subject</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="subjects[0][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            `);
            schoolSubjectRowCount = 0;
            $('.is-invalid').removeClass('is-invalid');
        }

        $('#addSchoolSubjectModal').on('hidden.bs.modal', function() {
            resetSchoolSubjectForm();
        });

        $('#addClassSubjectModal').on('hidden.bs.modal', function() {
            $('#addClassSubjectForm')[0].reset();
        });

        $('#editClassSubjectModal').on('hidden.bs.modal', function() {
            $('#editClassSubjectForm')[0].reset();
        });

        // Alternative Bootstrap 4 event handlers
        $('#addSchoolSubjectModal').on('hidden', function() {
            resetSchoolSubjectForm();
        });

        $('#addClassSubjectModal').on('hidden', function() {
            $('#addClassSubjectForm')[0].reset();
        });

        $('#editClassSubjectModal').on('hidden', function() {
            $('#editClassSubjectForm')[0].reset();
        });
        });
    })(jQuery);
</script>
