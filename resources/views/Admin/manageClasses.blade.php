@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<style>
    /* Minimal custom CSS - only for #940000 color scheme */
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
    
    /* Light background for widget cards */
    .class-card .card-header.bg-primary-custom,
    .subclass-card .card-header.bg-primary-custom {
        background-color: rgba(148, 0, 0, 0.08) !important;
        border-bottom: 1px solid rgba(148, 0, 0, 0.15) !important;
    }
    .class-card .card-header.bg-primary-custom,
    .class-card .card-header.bg-primary-custom *,
    .subclass-card .card-header.bg-primary-custom,
    .subclass-card .card-header.bg-primary-custom * {
        color: #940000 !important;
    }

    /* Hide scrollbar for edit subclass modal body */
    #editSubclassModal .modal-body::-webkit-scrollbar {
        display: none;
    }

    /* Ensure edit subclass modal appears on top */
    #editSubclassModal {
        z-index: 1060 !important;
    }
    #editSubclassModal .modal-backdrop {
        z-index: 1059 !important;
    }

    /* Add Subclass Modal - Ensure scrollable body and visible footer */
    #addSubclassModal .modal-dialog {
        max-height: 90vh;
        margin: 1.75rem auto;
    }

    #addSubclassModal .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }

    #addSubclassModal .modal-body {
        overflow-y: auto;
        overflow-x: hidden;
        max-height: calc(90vh - 150px);
        padding: 1rem;
    }

    #addSubclassModal .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
        padding: 0.75rem;
        background-color: #f8f9fa;
    }

    /* Custom scrollbar for add subclass modal */
    #addSubclassModal .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    #addSubclassModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    #addSubclassModal .modal-body::-webkit-scrollbar-thumb {
        background: #940000;
        border-radius: 4px;
    }

    #addSubclassModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #b30000;
    }

    /* Select2 Custom Styles */
    .select2-container {
        z-index: 9999 !important;
    }

    .select2-dropdown {
        z-index: 9999 !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }

        .card-header h4 {
            font-size: 1.1rem;
        }

        .subclass-card .card-header h5 {
            font-size: 1rem;
        }

        .subclass-card .card-body {
            padding: 0.75rem !important;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .action-btn-icon {
            padding: 0.2rem 0.4rem !important;
            font-size: 0.7rem !important;
            min-width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn-icon i {
            font-size: 0.85rem;
        }

        .action-buttons-group {
            gap: 0.25rem !important;
        }

        .modal-dialog {
            margin: 0.5rem;
        }

        .modal-content {
            border-radius: 0.5rem;
        }

        .table-responsive {
            font-size: 0.85rem;
        }

        .table th, .table td {
            padding: 0.5rem 0.25rem;
            white-space: nowrap;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.25em 0.5em;
        }

        .page-header-buttons {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 0.5rem !important;
            justify-content: flex-start;
        }

        .page-header-action-btn {
            flex: 0 0 auto;
            white-space: nowrap;
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .page-header-action-btn i {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .action-btn-icon {
            padding: 0.15rem 0.3rem !important;
            font-size: 0.65rem !important;
            min-width: 28px;
            height: 28px;
        }

        .action-btn-icon i {
            font-size: 0.75rem;
        }

        .action-buttons-group {
            gap: 0.2rem !important;
        }

        .page-header-action-btn {
            padding: 0.3rem 0.6rem;
            font-size: 0.7rem;
        }

        .page-header-action-btn i {
            font-size: 0.85rem;
        }

        .page-header-buttons {
            gap: 0.4rem !important;
        }

        .subclass-card .card-footer {
            padding: 0.5rem !important;
        }

        .subclass-card .card-footer .d-flex {
            gap: 0.5rem !important;
        }

        .modal-dialog {
            margin: 0.25rem;
        }

        .modal-body {
            padding: 1rem;
        }

        .form-label {
            font-size: 0.9rem;
        }

        .table {
            font-size: 0.8rem;
        }

        .table th, .table td {
            padding: 0.4rem 0.2rem;
        }
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* Ensure Select2 dropdown is visible and search box shows */
    .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 99999 !important;
    }
    .select2-container--bootstrap-5 {
        z-index: 9999 !important;
    }
    .select2-search--dropdown {
        display: block !important;
        padding: 8px !important;
    }
    .select2-search__field {
        width: 100% !important;
        padding: 6px !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
    }
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--bootstrap-5 .select2-selection--single {
        height: 38px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 8px;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Success/Error Messages -->
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
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary-custom text-white rounded">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <h4 class="mb-2 mb-md-0">
                    <i class="bi bi-building"></i> <span class="d-none d-sm-inline">Manage Classes & Subclasses</span><span class="d-sm-none">Classes</span>
                </h4>
                <div class="d-flex flex-row flex-wrap gap-2 page-header-buttons">
                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('view_all_class'))
                    <button class="btn btn-light text-primary-custom fw-bold btn-sm page-header-action-btn" id="viewClassesBtn" type="button">
                        <i class="bi bi-grid-3x3-gap"></i> <span class="d-none d-md-inline">View Classes</span><span class="d-md-none">Classes</span>
                    </button>
                    @endif
                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('view_students'))
                    <button class="btn btn-light text-primary-custom fw-bold btn-sm page-header-action-btn" id="viewClassGradingBtn" type="button">
                        <i class="bi bi-award"></i> <span class="d-none d-md-inline">View Class Grading</span><span class="d-md-none">Grading</span>
                    </button>
                    @endif
                    @if($school_details->school_type == 'Secondary')
                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('view_combies'))
                    <button class="btn btn-light text-primary-custom fw-bold btn-sm page-header-action-btn" id="viewCombiesBtn" type="button" data-toggle="modal" data-target="#viewCombiesModal">
                        <i class="bi bi-eye"></i> <span class="d-none d-md-inline">View Combies</span><span class="d-md-none">Combies</span>
                    </button>
                    @endif
                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('create_combie'))
                    <button class="btn btn-light text-primary-custom fw-bold btn-sm page-header-action-btn" id="addCombieBtn" type="button" data-toggle="modal" data-target="#addCombieModal">
                        <i class="bi bi-layers"></i> <span class="d-none d-md-inline">Add Combies</span><span class="d-md-none">Add</span>
                    </button>
                    @endif
                    @endif
                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('create_subclass'))
                    <button class="btn btn-light text-primary-custom fw-bold btn-sm page-header-action-btn" id="addSubclassBtn" type="button" data-toggle="modal" data-target="#addSubclassModal">
                        <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Add Subclass</span><span class="d-md-none">Subclass</span>
                    </button>
                    @endif
                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('create_class'))
                    <button class="btn btn-light text-primary-custom fw-bold btn-sm page-header-action-btn" id="addClassBtn" type="button" data-toggle="modal" data-target="#addClassModal">
                        <i class="bi bi-plus-square"></i> <span class="d-none d-md-inline">Add Class</span><span class="d-md-none">Class</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Grid -->
    <div class="row g-3 g-md-4">
        @if(count($classes) > 0)
            @foreach ($classes as $class)
                <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 class-card">
                        <div class="card-header bg-primary-custom text-white text-center py-3">
                            <div class="d-flex justify-content-center align-items-center mb-2">
                                <i class="bi bi-mortarboard-fill" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="mb-1 fw-bold">{{ $class['class_name'] }}</h5>
                            <div class="mt-2">
                                <span class="badge {{ ($class['status'] ?? 'Inactive') == 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $class['status'] ?? 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <!-- Quick Stats Row -->
                            <div class="row g-2 mb-3">
                                <!-- Subclasses -->
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <i class="bi bi-layers-fill text-primary-custom d-block mb-1" style="font-size: 1.5rem;"></i>
                                        <div class="fw-bold text-dark">{{ $class['subclass_count'] ?? 0 }}</div>
                                        <small class="text-muted">Subclasses</small>
                                    </div>
                                </div>
                                <!-- Students -->
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <i class="bi bi-people-fill text-success d-block mb-1" style="font-size: 1.5rem;"></i>
                                        <div class="fw-bold text-dark">{{ $class['student_count'] ?? 0 }}</div>
                                        <small class="text-muted">Students</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Coordinator - Compact -->
                            @if($class['coordinator'])
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted small"><i class="bi bi-person-badge"></i> Coordinator:</span>
                                    <span class="badge bg-info text-white">{{ $class['coordinator'] }}</span>
                                </div>
                            </div>
                            @else
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted small"><i class="bi bi-person-badge"></i> Coordinator:</span>
                                    <span class="badge bg-secondary">Not Assigned</span>
                                </div>
                            </div>
                            @endif

                            <!-- Description if available -->
                            @if($class['description'])
                            <div class="mb-2">
                                <small class="text-muted">{{ Str::limit($class['description'], 50) }}</small>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-light p-2">
                            <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                                <!-- View Subclasses Button -->
                                <button class="btn btn-sm btn-primary-custom text-white view-class-subclasses-btn" 
                                        data-class-id="{{ $class['classID'] }}"
                                        data-class-name="{{ $class['class_name'] }}">
                                    <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">View Subclasses</span><span class="d-sm-none">Subclasses</span>
                                </button>

                                <!-- Action Buttons -->
                                <div class="d-flex flex-row gap-1 action-buttons-group">
                                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('edit_class'))
                                    <button class="btn btn-sm btn-warning text-dark edit-class-btn action-btn-icon"
                                            data-class-id="{{ $class['classID'] }}"
                                            title="Edit Class">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @endif
                                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('activate_class'))
                                    <button class="btn btn-sm {{ ($class['status'] ?? 'Inactive') == 'Active' ? 'btn-secondary' : 'btn-success' }} text-white activate-class-btn action-btn-icon"
                                            data-class-id="{{ $class['classID'] }}"
                                            data-class-name="{{ $class['class_name'] }}"
                                            data-current-status="{{ $class['status'] ?? 'Inactive' }}"
                                            title="{{ ($class['status'] ?? 'Inactive') == 'Active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ ($class['status'] ?? 'Inactive') == 'Active' ? 'x-circle' : 'check-circle' }}-fill"></i>
                                    </button>
                                    @endif
                                    @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('delete_class'))
                                    <button class="btn btn-sm btn-danger text-white delete-class-btn action-btn-icon"
                                            data-class-id="{{ $class['classID'] }}"
                                            data-class-name="{{ $class['class_name'] }}"
                                            title="Delete Class">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 64px; color: #940000;"></i>
                        <h5 class="mt-3 mb-2">No Classes Found</h5>
                        <p class="text-muted mb-4">Start by adding a class with at least one subclass.</p>
                        <button class="btn btn-primary-custom" data-toggle="modal" data-target="#addClassModal">
                            <i class="bi bi-plus-square"></i> Add Your First Class
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="addClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addClassModalLabel">
                    <i class="bi bi-plus-square"></i> Add New Class with Subclasses
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClassForm">
                @csrf
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Class Information Section -->
                    <div class="card mb-3 border-primary-custom">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-info-circle text-primary-custom"></i> Class Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Class Name <span class="text-danger">*</span></label>
                                <select name="class_name" id="add_class_name_select" class="form-control" required>
                                    <option value="">Select Class</option>
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                        <option value="FORM ONE" {{ in_array('FORM ONE', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>FORM ONE @if(in_array('FORM ONE', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="FORM TWO" {{ in_array('FORM TWO', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>FORM TWO @if(in_array('FORM TWO', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="FORM THREE" {{ in_array('FORM THREE', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>FORM THREE @if(in_array('FORM THREE', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="FORM FOUR" {{ in_array('FORM FOUR', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>FORM FOUR @if(in_array('FORM FOUR', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="FORM FIVE" {{ in_array('FORM FIVE', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>FORM FIVE @if(in_array('FORM FIVE', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="FORM SIX" {{ in_array('FORM SIX', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>FORM SIX @if(in_array('FORM SIX', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                    @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                        <option value="baby_class" {{ in_array('baby_class', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>baby_class @if(in_array('baby_class', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="NURSERY1" {{ in_array('NURSERY1', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>NURSERY1 @if(in_array('NURSERY1', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="NURSERY2" {{ in_array('NURSERY2', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>NURSERY2 @if(in_array('NURSERY2', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="STANDARD1" {{ in_array('STANDARD1', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>STANDARD1 @if(in_array('STANDARD1', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="STANDARD2" {{ in_array('STANDARD2', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>STANDARD2 @if(in_array('STANDARD2', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="STANDARD3" {{ in_array('STANDARD3', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>STANDARD3 @if(in_array('STANDARD3', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="STANDARD4" {{ in_array('STANDARD4', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>STANDARD4 @if(in_array('STANDARD4', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="STANDARD5" {{ in_array('STANDARD5', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>STANDARD5 @if(in_array('STANDARD5', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="STANDARD6" {{ in_array('STANDARD6', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>STANDARD6 @if(in_array('STANDARD6', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                        <option value="STANDARD7" {{ in_array('STANDARD7', $existingClassNames ?? []) ? 'disabled style="background-color: #e9ecef; color: #6c757d;"' : '' }}>STANDARD7 @if(in_array('STANDARD7', $existingClassNames ?? [])) (Already Exists) @endif</option>
                                    @endif
                                </select>
                                <small class="text-muted">Select the class name. Classes that already exist are disabled (grayed out).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Coordinator (Teacher)</label>
                                <select name="teacherID" class="form-select">
                                    <option value="">Select Coordinator (Optional)</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">
                                            {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="has_subclasses" id="has_subclasses_checkbox" value="1" checked>
                                    <label class="form-check-label fw-bold" for="has_subclasses_checkbox">
                                        Are there subclasses?
                                    </label>
                                </div>
                                <small class="text-muted">If unchecked, a default subclass will be created automatically. Students will be referenced to the main class.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Subclasses Section -->
                    <div class="card border-primary-custom" id="subclassesSection">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-layers text-primary-custom"></i> Subclasses <span class="text-danger">*</span></h6>
                            <button type="button" class="btn btn-sm btn-primary-custom" id="addSubclassRowBtn">
                                <i class="bi bi-plus-circle"></i> Add Subclass
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">You must add at least one subclass for this class.</p>
                            <div id="subclassesContainer">
                                <!-- Subclass rows will be added here dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom" id="saveClassBtn">
                        <i class="bi bi-save"></i> Save Class & Subclasses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Subclass Modal -->
<div class="modal fade" id="addSubclassModal" tabindex="-1" aria-labelledby="addSubclassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-height: 90vh;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header bg-primary-custom text-white" style="flex-shrink: 0;">
                <h5 class="modal-title" id="addSubclassModalLabel">
                    <i class="bi bi-plus-circle"></i> Add New Subclass
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSubclassForm" style="display: flex; flex-direction: column; height: 100%;">
                @csrf
                <div class="modal-body" style="overflow-y: auto; flex: 1 1 auto; max-height: calc(90vh - 150px);">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Class <span class="text-danger">*</span></label>
                        <select name="classID" id="subclass_class_select" class="form-select" required>
                            <option value="">Choose a class...</option>
                            @foreach($classesForDropdown as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subclass Name <span class="text-danger">*</span></label>
                        <input type="text" name="subclass_name" class="form-control" placeholder="e.g., A, B, C, D" required maxlength="50">
                        <small class="text-muted">Enter a single letter or identifier for this subclass (e.g., A, B, 1, 2)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Class Teacher</label>
                        <select name="teacherID" id="subclass_teacher_select" class="form-select">
                            <option value="">Select Class Teacher (Optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Combination</label>
                        <select name="combieID" class="form-select">
                            <option value="">Select Combination (Optional)</option>
                            @foreach($combies as $combie)
                                <option value="{{ $combie->combieID }}">
                                    {{ $combie->combie_name }} @if($combie->combie_code)({{ $combie->combie_code }})@endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Optional: Assign this subclass to a combination (e.g., Science, Arts, PCM, PGM)</small>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-bold">Grade Range (Optional)</label>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <label class="form-label small">First Grade (Starting Grade)</label>
                                <select name="first_grade" id="subclass_first_grade" class="form-control division-select">
                                    <option value="">Select Starting Grade (Optional)</option>
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                        <optgroup label="A-LEVEL Divisions">
                                            <option value="I.15">I.15</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.12">I.12</option>
                                            <option value="II.11">II.11</option>
                                            <option value="II.10">II.10</option>
                                            <option value="II.9">II.9</option>
                                            <option value="III.8">III.8</option>
                                            <option value="III.7">III.7</option>
                                            <option value="III.6">III.6</option>
                                            <option value="IV.5">IV.5</option>
                                            <option value="IV.4">IV.4</option>
                                            <option value="IV.3">IV.3</option>
                                            <option value="0.2">0.2</option>
                                            <option value="0.1">0.1</option>
                                            <option value="0.0">0.0</option>
                                        </optgroup>
                                        <optgroup label="O-LEVEL Divisions">
                                            <option value="I.7">I.7</option>
                                            <option value="I.8">I.8</option>
                                            <option value="I.9">I.9</option>
                                            <option value="I.10">I.10</option>
                                            <option value="I.11">I.11</option>
                                            <option value="I.12">I.12</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.15">I.15</option>
                                            <option value="I.16">I.16</option>
                                            <option value="I.17">I.17</option>
                                            <option value="II.18">II.18</option>
                                            <option value="II.19">II.19</option>
                                            <option value="II.20">II.20</option>
                                            <option value="II.21">II.21</option>
                                            <option value="III.22">III.22</option>
                                            <option value="III.23">III.23</option>
                                            <option value="III.24">III.24</option>
                                            <option value="III.25">III.25</option>
                                            <option value="IV.26">IV.26</option>
                                            <option value="IV.27">IV.27</option>
                                            <option value="IV.28">IV.28</option>
                                            <option value="IV.29">IV.29</option>
                                            <option value="IV.30">IV.30</option>
                                            <option value="IV.31">IV.31</option>
                                            <option value="IV.32">IV.32</option>
                                            <option value="IV.33">IV.33</option>
                                            <option value="0.34">0.34</option>
                                            <option value="0.35">0.35</option>
                                            <option value="0.36">0.36</option>
                                            <option value="0.37">0.37</option>
                                            <option value="0.38">0.38</option>
                                            <option value="0.39">0.39</option>
                                            <option value="0.40+">0.40+</option>
                                        </optgroup>
                                    @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                    @endif
                                </select>
                                <small class="text-muted">Select starting grade/division for this subclass</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small">Final Grade (Ending Grade)</label>
                                <select name="final_grade" id="subclass_final_grade" class="form-control division-select">
                                    <option value="">Select Ending Grade (Optional)</option>
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                        <optgroup label="A-LEVEL Divisions">
                                            <option value="I.15">I.15</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.12">I.12</option>
                                            <option value="II.11">II.11</option>
                                            <option value="II.10">II.10</option>
                                            <option value="II.9">II.9</option>
                                            <option value="III.8">III.8</option>
                                            <option value="III.7">III.7</option>
                                            <option value="III.6">III.6</option>
                                            <option value="IV.5">IV.5</option>
                                            <option value="IV.4">IV.4</option>
                                            <option value="IV.3">IV.3</option>
                                            <option value="0.2">0.2</option>
                                            <option value="0.1">0.1</option>
                                            <option value="0.0">0.0</option>
                                        </optgroup>
                                        <optgroup label="O-LEVEL Divisions">
                                            <option value="I.7">I.7</option>
                                            <option value="I.8">I.8</option>
                                            <option value="I.9">I.9</option>
                                            <option value="I.10">I.10</option>
                                            <option value="I.11">I.11</option>
                                            <option value="I.12">I.12</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.15">I.15</option>
                                            <option value="I.16">I.16</option>
                                            <option value="I.17">I.17</option>
                                            <option value="II.18">II.18</option>
                                            <option value="II.19">II.19</option>
                                            <option value="II.20">II.20</option>
                                            <option value="II.21">II.21</option>
                                            <option value="III.22">III.22</option>
                                            <option value="III.23">III.23</option>
                                            <option value="III.24">III.24</option>
                                            <option value="III.25">III.25</option>
                                            <option value="IV.26">IV.26</option>
                                            <option value="IV.27">IV.27</option>
                                            <option value="IV.28">IV.28</option>
                                            <option value="IV.29">IV.29</option>
                                            <option value="IV.30">IV.30</option>
                                            <option value="IV.31">IV.31</option>
                                            <option value="IV.32">IV.32</option>
                                            <option value="IV.33">IV.33</option>
                                            <option value="0.34">0.34</option>
                                            <option value="0.35">0.35</option>
                                            <option value="0.36">0.36</option>
                                            <option value="0.37">0.37</option>
                                            <option value="0.38">0.38</option>
                                            <option value="0.39">0.39</option>
                                            <option value="0.40+">0.40+</option>
                                        </optgroup>
                                    @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                    @endif
                                </select>
                                <small class="text-muted">Select ending grade/division for this subclass</small>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Define grade range for automatic student placement. Students with grades within this range will be automatically assigned to this subclass.</small>
                    </div>
                </div>
                <div class="modal-footer" style="flex-shrink: 0; border-top: 1px solid #dee2e6;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save Subclass
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Combies Modal -->
<div class="modal fade" id="addCombieModal" tabindex="-1" aria-labelledby="addCombieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addCombieModalLabel">
                    <i class="bi bi-layers"></i> Add New Combination
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCombieForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Combination <span class="text-danger">*</span></label>
                        <select name="combie_name" id="combie_name_select" class="form-control" required>
                            <option value="">Select or Enter Custom Combination</option>
                            <optgroup label="Basic Combinations">
                                <option value="Science" data-code="SCI">Science (SCI)</option>
                                <option value="Arts" data-code="ART">Arts (ART)</option>
                                <option value="Business" data-code="BUS">Business (BUS)</option>
                            </optgroup>
                            <optgroup label="A-Level Combinations">
                                <option value="PCM" data-code="PCM">PCM - Physics, Chemistry, Mathematics</option>
                                <option value="PGM" data-code="PGM">PGM - Physics, Geography, Mathematics</option>
                                <option value="PCB" data-code="PCB">PCB - Physics, Chemistry, Biology</option>
                                <option value="HKL" data-code="HKL">HKL - History, Kiswahili, Literature</option>
                                <option value="HGE" data-code="HGE">HGE - History, Geography, Economics</option>
                                <option value="HGL" data-code="HGL">HGL - History, Geography, Literature</option>
                                <option value="HGK" data-code="HGK">HGK - History, Geography, Kiswahili</option>
                                <option value="CBG" data-code="CBG">CBG - Chemistry, Biology, Geography</option>
                                <option value="CBA" data-code="CBA">CBA - Chemistry, Biology, Agriculture</option>
                                <option value="EGM" data-code="EGM">EGM - Economics, Geography, Mathematics</option>
                                <option value="EKN" data-code="EKN">EKN - Economics, Commerce, Accounts</option>
                                <option value="DST" data-code="DST">DST - Divinity, Swahili, Literature</option>
                                <option value="HGEB" data-code="HGEB">HGEB - History, Geography, Economics, Bible Knowledge</option>
                                <option value="HGLB" data-code="HGLB">HGLB - History, Geography, Literature, Bible Knowledge</option>
                                <option value="PCMB" data-code="PCMB">PCMB - Physics, Chemistry, Mathematics, Biology</option>
                                <option value="PCBG" data-code="PCBG">PCBG - Physics, Chemistry, Biology, Geography</option>
                            </optgroup>
                            <option value="custom">-- Enter Custom Combination --</option>
                        </select>
                        <small class="text-muted">Select a common combination or choose "Enter Custom Combination" to add your own</small>
                    </div>
                    <div class="mb-3" id="customCombieNameGroup" style="display: none;">
                        <label class="form-label fw-bold">Custom Combination Name <span class="text-danger">*</span></label>
                        <input type="text" name="custom_combie_name" id="custom_combie_name" class="form-control" placeholder="e.g., Custom Combination Name" maxlength="100">
                        <small class="text-muted">Enter your custom combination name</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Combination Code</label>
                        <input type="text" name="combie_code" id="combie_code_input" class="form-control" placeholder="e.g., SCI, ART, BUS, PCM, PGM" maxlength="20">
                        <small class="text-muted">Optional: Short code for this combination (auto-filled when selecting from list)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional description of the combination"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save Combination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Combies Modal -->
<div class="modal fade" id="viewCombiesModal" tabindex="-1" aria-labelledby="viewCombiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 90%; width: 90%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewCombiesModalLabel">
                    <i class="bi bi-layers"></i> All Combinations
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(count($combies) > 0)
                    <div class="row g-4">
                        @foreach($combies as $combie)
                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-primary-custom text-white text-center">
                                        <h5 class="mb-0">
                                            <i class="bi bi-bookmark-fill"></i> {{ $combie->combie_name }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if($combie->combie_code)
                                            <div class="mb-2">
                                                <strong><i class="bi bi-code-square"></i> Code:</strong>
                                                <span class="badge bg-secondary">{{ $combie->combie_code }}</span>
                                            </div>
                                        @endif
                                        @if($combie->description)
                                            <div class="mb-2">
                                                <strong><i class="bi bi-info-circle"></i> Description:</strong>
                                                <p class="mb-0 small text-muted">{{ Str::limit($combie->description, 80) }}</p>
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <strong><i class="bi bi-circle-fill"></i> Status:</strong>
                                            <span class="badge {{ $combie->status == 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $combie->status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i> {{ $combie->created_at->format('M d, Y') }}
                                            </small>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-warning text-dark edit-combie-btn"
                                                        data-combie-id="{{ $combie->combieID }}"
                                                        data-combie-name="{{ $combie->combie_name }}"
                                                        data-combie-code="{{ $combie->combie_code }}"
                                                        data-combie-description="{{ $combie->description }}"
                                                        data-combie-status="{{ $combie->status }}"
                                                        title="Edit Combination">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-combie-btn"
                                                        data-combie-id="{{ $combie->combieID }}"
                                                        data-combie-name="{{ $combie->combie_name }}"
                                                        title="Delete Combination">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 64px; color: #940000;"></i>
                        <h5 class="mt-3 text-muted">No Combinations Found</h5>
                        <p class="text-muted">Click 'Add Combies' to create your first combination.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Combie Modal -->
<div class="modal fade" id="editCombieModal" tabindex="-1" aria-labelledby="editCombieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editCombieModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Combination
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCombieForm">
                @csrf
                <input type="hidden" name="combieID" id="edit_combieID">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Combination <span class="text-danger">*</span></label>
                        <select name="combie_name" id="edit_combie_name_select" class="form-control" required>
                            <option value="">Select or Enter Custom Combination</option>
                            <optgroup label="Basic Combinations">
                                <option value="Science" data-code="SCI">Science (SCI)</option>
                                <option value="Arts" data-code="ART">Arts (ART)</option>
                                <option value="Business" data-code="BUS">Business (BUS)</option>
                            </optgroup>
                            <optgroup label="A-Level Combinations">
                                <option value="PCM" data-code="PCM">PCM - Physics, Chemistry, Mathematics</option>
                                <option value="PGM" data-code="PGM">PGM - Physics, Geography, Mathematics</option>
                                <option value="PCB" data-code="PCB">PCB - Physics, Chemistry, Biology</option>
                                <option value="HKL" data-code="HKL">HKL - History, Kiswahili, Literature</option>
                                <option value="HGE" data-code="HGE">HGE - History, Geography, Economics</option>
                                <option value="HGL" data-code="HGL">HGL - History, Geography, Literature</option>
                                <option value="HGK" data-code="HGK">HGK - History, Geography, Kiswahili</option>
                                <option value="CBG" data-code="CBG">CBG - Chemistry, Biology, Geography</option>
                                <option value="CBA" data-code="CBA">CBA - Chemistry, Biology, Agriculture</option>
                                <option value="EGM" data-code="EGM">EGM - Economics, Geography, Mathematics</option>
                                <option value="EKN" data-code="EKN">EKN - Economics, Commerce, Accounts</option>
                                <option value="DST" data-code="DST">DST - Divinity, Swahili, Literature</option>
                                <option value="HGEB" data-code="HGEB">HGEB - History, Geography, Economics, Bible Knowledge</option>
                                <option value="HGLB" data-code="HGLB">HGLB - History, Geography, Literature, Bible Knowledge</option>
                                <option value="PCMB" data-code="PCMB">PCMB - Physics, Chemistry, Mathematics, Biology</option>
                                <option value="PCBG" data-code="PCBG">PCBG - Physics, Chemistry, Biology, Geography</option>
                            </optgroup>
                            <option value="custom">-- Enter Custom Combination --</option>
                        </select>
                        <small class="text-muted">Select a common combination or choose "Enter Custom Combination" to edit</small>
                    </div>
                    <div class="mb-3" id="editCustomCombieNameGroup" style="display: none;">
                        <label class="form-label fw-bold">Custom Combination Name <span class="text-danger">*</span></label>
                        <input type="text" name="custom_combie_name" id="edit_custom_combie_name" class="form-control" placeholder="e.g., Custom Combination Name" maxlength="100">
                        <small class="text-muted">Enter your custom combination name</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Combination Code</label>
                        <input type="text" name="combie_code" id="edit_combie_code" class="form-control" placeholder="e.g., SCI, ART, BUS, PCM, PGM" maxlength="20">
                        <small class="text-muted">Optional: Short code for this combination (auto-filled when selecting from list)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="edit_combie_description" class="form-control" rows="3" placeholder="Optional description of the combination"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" id="edit_combie_status" class="form-select">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Combination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subclass Modal -->
<div class="modal fade" id="editSubclassModal" tabindex="-1" aria-labelledby="editSubclassModalLabel" aria-hidden="true" data-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95%; width: 95%; z-index: 1061;">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editSubclassModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Subclass
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editSubclassForm">
                @csrf
                <input type="hidden" name="subclassID" id="edit_subclassID">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                        <input type="text" id="edit_subclass_class_display" class="form-control" readonly style="background-color: #e9ecef;">
                        <input type="hidden" name="classID" id="edit_subclass_class_select">
                        <small class="text-muted">Class cannot be changed. This subclass belongs to the selected class.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subclass Name <span class="text-danger">*</span></label>
                        <input type="text" name="subclass_name" id="edit_subclass_name" class="form-control" placeholder="e.g., A, B, C, D" required maxlength="50">
                        <small class="text-muted">Enter a single letter or identifier for this subclass</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Class Teacher</label>
                        <select name="teacherID" id="edit_subclass_teacher_select" class="form-select">
                            <option value="">Select Class Teacher (Optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Combination</label>
                        <select name="combieID" id="edit_subclass_combie_select" class="form-select">
                            <option value="">Select Combination (Optional)</option>
                            @foreach($combies as $combie)
                                <option value="{{ $combie->combieID }}">
                                    {{ $combie->combie_name }} @if($combie->combie_code)({{ $combie->combie_code }})@endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Optional: Assign this subclass to a combination</small>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-bold">Grade Range (Optional)</label>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <label class="form-label small">First Grade (Starting Grade)</label>
                                <select name="first_grade" id="edit_subclass_first_grade" class="form-control division-select">
                                    <option value="">Select Starting Grade (Optional)</option>
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                        <optgroup label="A-LEVEL Divisions">
                                            <option value="I.15">I.15</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.12">I.12</option>
                                            <option value="II.11">II.11</option>
                                            <option value="II.10">II.10</option>
                                            <option value="II.9">II.9</option>
                                            <option value="III.8">III.8</option>
                                            <option value="III.7">III.7</option>
                                            <option value="III.6">III.6</option>
                                            <option value="IV.5">IV.5</option>
                                            <option value="IV.4">IV.4</option>
                                            <option value="IV.3">IV.3</option>
                                            <option value="0.2">0.2</option>
                                            <option value="0.1">0.1</option>
                                            <option value="0.0">0.0</option>
                                        </optgroup>
                                        <optgroup label="O-LEVEL Divisions">
                                            <option value="I.7">I.7</option>
                                            <option value="I.8">I.8</option>
                                            <option value="I.9">I.9</option>
                                            <option value="I.10">I.10</option>
                                            <option value="I.11">I.11</option>
                                            <option value="I.12">I.12</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.15">I.15</option>
                                            <option value="I.16">I.16</option>
                                            <option value="I.17">I.17</option>
                                            <option value="II.18">II.18</option>
                                            <option value="II.19">II.19</option>
                                            <option value="II.20">II.20</option>
                                            <option value="II.21">II.21</option>
                                            <option value="III.22">III.22</option>
                                            <option value="III.23">III.23</option>
                                            <option value="III.24">III.24</option>
                                            <option value="III.25">III.25</option>
                                            <option value="IV.26">IV.26</option>
                                            <option value="IV.27">IV.27</option>
                                            <option value="IV.28">IV.28</option>
                                            <option value="IV.29">IV.29</option>
                                            <option value="IV.30">IV.30</option>
                                            <option value="IV.31">IV.31</option>
                                            <option value="IV.32">IV.32</option>
                                            <option value="IV.33">IV.33</option>
                                            <option value="0.34">0.34</option>
                                            <option value="0.35">0.35</option>
                                            <option value="0.36">0.36</option>
                                            <option value="0.37">0.37</option>
                                            <option value="0.38">0.38</option>
                                            <option value="0.39">0.39</option>
                                            <option value="0.40+">0.40+</option>
                                        </optgroup>
                                    @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                    @endif
                                </select>
                                <small class="text-muted">Select starting grade/division for this subclass</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small">Final Grade (Ending Grade)</label>
                                <select name="final_grade" id="edit_subclass_final_grade" class="form-control division-select">
                                    <option value="">Select Ending Grade (Optional)</option>
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                        <optgroup label="A-LEVEL Divisions">
                                            <option value="I.15">I.15</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.12">I.12</option>
                                            <option value="II.11">II.11</option>
                                            <option value="II.10">II.10</option>
                                            <option value="II.9">II.9</option>
                                            <option value="III.8">III.8</option>
                                            <option value="III.7">III.7</option>
                                            <option value="III.6">III.6</option>
                                            <option value="IV.5">IV.5</option>
                                            <option value="IV.4">IV.4</option>
                                            <option value="IV.3">IV.3</option>
                                            <option value="0.2">0.2</option>
                                            <option value="0.1">0.1</option>
                                            <option value="0.0">0.0</option>
                                        </optgroup>
                                        <optgroup label="O-LEVEL Divisions">
                                            <option value="I.7">I.7</option>
                                            <option value="I.8">I.8</option>
                                            <option value="I.9">I.9</option>
                                            <option value="I.10">I.10</option>
                                            <option value="I.11">I.11</option>
                                            <option value="I.12">I.12</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.15">I.15</option>
                                            <option value="I.16">I.16</option>
                                            <option value="I.17">I.17</option>
                                            <option value="II.18">II.18</option>
                                            <option value="II.19">II.19</option>
                                            <option value="II.20">II.20</option>
                                            <option value="II.21">II.21</option>
                                            <option value="III.22">III.22</option>
                                            <option value="III.23">III.23</option>
                                            <option value="III.24">III.24</option>
                                            <option value="III.25">III.25</option>
                                            <option value="IV.26">IV.26</option>
                                            <option value="IV.27">IV.27</option>
                                            <option value="IV.28">IV.28</option>
                                            <option value="IV.29">IV.29</option>
                                            <option value="IV.30">IV.30</option>
                                            <option value="IV.31">IV.31</option>
                                            <option value="IV.32">IV.32</option>
                                            <option value="IV.33">IV.33</option>
                                            <option value="0.34">0.34</option>
                                            <option value="0.35">0.35</option>
                                            <option value="0.36">0.36</option>
                                            <option value="0.37">0.37</option>
                                            <option value="0.38">0.38</option>
                                            <option value="0.39">0.39</option>
                                            <option value="0.40+">0.40+</option>
                                        </optgroup>
                                    @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                    @endif
                                </select>
                                <small class="text-muted">Select ending grade/division for this subclass</small>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Define grade range for automatic student placement. Students with grades within this range will be automatically assigned to this subclass.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Subclass
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Classes Modal -->
<div class="modal fade" id="viewClassesModal" tabindex="-1" aria-labelledby="viewClassesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewClassesModalLabel">
                    <i class="bi bi-grid-3x3-gap"></i> All Classes
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="classesContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading classes...</p>
                    </div>
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

<!-- Edit Class Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editClassModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Class
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editClassForm">
                @csrf
                <input type="hidden" name="classID" id="edit_classID">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Class Name <span class="text-danger">*</span></label>
                        <select name="class_name" id="edit_class_name" class="form-control" required>
                            <option value="">Select Class</option>
                            @if(isset($school_details) && $school_details->school_type == 'Secondary')
                            <option value="FORM ONE" data-original-value="FORM ONE">FORM ONE</option>
                                <option value="FORM TWO" data-original-value="FORM TWO">FORM TWO</option>
                                <option value="FORM THREE" data-original-value="FORM THREE">FORM THREE</option>
                                <option value="FORM FOUR" data-original-value="FORM FOUR">FORM FOUR</option>
                                <option value="FORM FIVE" data-original-value="FORM FIVE">FORM FIVE</option>
                                <option value="FORM SIX" data-original-value="FORM SIX">FORM SIX</option>
                            @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                <option value="baby_class" data-original-value="baby_class">baby_class</option>
                                <option value="NURSERY1" data-original-value="NURSERY1">NURSERY1</option>
                                <option value="NURSERY2" data-original-value="NURSERY2">NURSERY2</option>
                                <option value="STANDARD1" data-original-value="STANDARD1">STANDARD1</option>
                                <option value="STANDARD2" data-original-value="STANDARD2">STANDARD2</option>
                                <option value="STANDARD3" data-original-value="STANDARD3">STANDARD3</option>
                                <option value="STANDARD4" data-original-value="STANDARD4">STANDARD4</option>
                                <option value="STANDARD5" data-original-value="STANDARD5">STANDARD5</option>
                                <option value="STANDARD6" data-original-value="STANDARD6">STANDARD6</option>
                                <option value="STANDARD7" data-original-value="STANDARD7">STANDARD7</option>
                            @endif
                        </select>
                        <small class="text-muted">You can only change to a class name that doesn't already exist.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="edit_class_description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Coordinator (Teacher)</label>
                        <select name="teacherID" id="edit_class_teacher_select" class="form-select">
                            <option value="">Select Coordinator (Optional)</option>
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
                        <i class="bi bi-save"></i> Update Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Subjects Modal -->
<div class="modal fade" id="viewSubjectsModal" tabindex="-1" aria-labelledby="viewSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewSubjectsModalLabel">
                    <i class="bi bi-book-fill"></i> Subjects in <span id="modalSubclassNameSubjects"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="subjectsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading subjects...</p>
                    </div>
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

<!-- View Class Grading Modal -->
<div class="modal fade" id="viewClassGradingModal" tabindex="-1" aria-labelledby="viewClassGradingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewClassGradingModalLabel">
                    <i class="bi bi-award"></i> Class Grading Requirements
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Search Box -->
                <div class="mb-3" id="classGradingSearchContainer" style="display: none;">
                    <div class="input-group">
                        <span class="input-group-text bg-primary-custom text-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="classGradingSearchInput" placeholder="Search by class, subclass, stream code, combination, or grade...">
                        <button class="btn btn-outline-secondary" type="button" id="clearClassGradingSearch" title="Clear Search">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
                <div id="classGradingContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading class grading information...</p>
                    </div>
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

<!-- Edit Grade Range Modal -->
<div class="modal fade" id="editGradeRangeModal" tabindex="-1" aria-labelledby="editGradeRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editGradeRangeModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Grade Range
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editGradeRangeForm">
                @csrf
                <input type="hidden" name="subclassID" id="edit_grade_subclassID">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subclass</label>
                        <input type="text" class="form-control" id="edit_grade_subclass_name" readonly style="background-color: #e9ecef;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Grade Range (Optional)</label>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <label class="form-label small">First Grade (Starting Grade)</label>
                                <select name="first_grade" id="edit_grade_first_grade" class="form-control division-select">
                                    <option value="">Select Starting Grade (Optional)</option>
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                        <optgroup label="A-LEVEL Divisions">
                                            <option value="I.15">I.15</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.12">I.12</option>
                                            <option value="II.11">II.11</option>
                                            <option value="II.10">II.10</option>
                                            <option value="II.9">II.9</option>
                                            <option value="III.8">III.8</option>
                                            <option value="III.7">III.7</option>
                                            <option value="III.6">III.6</option>
                                            <option value="IV.5">IV.5</option>
                                            <option value="IV.4">IV.4</option>
                                            <option value="IV.3">IV.3</option>
                                            <option value="0.2">0.2</option>
                                            <option value="0.1">0.1</option>
                                            <option value="0.0">0.0</option>
                                        </optgroup>
                                        <optgroup label="O-LEVEL Divisions">
                                            <option value="I.7">I.7</option>
                                            <option value="I.8">I.8</option>
                                            <option value="I.9">I.9</option>
                                            <option value="I.10">I.10</option>
                                            <option value="I.11">I.11</option>
                                            <option value="I.12">I.12</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.15">I.15</option>
                                            <option value="I.16">I.16</option>
                                            <option value="I.17">I.17</option>
                                            <option value="II.18">II.18</option>
                                            <option value="II.19">II.19</option>
                                            <option value="II.20">II.20</option>
                                            <option value="II.21">II.21</option>
                                            <option value="III.22">III.22</option>
                                            <option value="III.23">III.23</option>
                                            <option value="III.24">III.24</option>
                                            <option value="III.25">III.25</option>
                                            <option value="IV.26">IV.26</option>
                                            <option value="IV.27">IV.27</option>
                                            <option value="IV.28">IV.28</option>
                                            <option value="IV.29">IV.29</option>
                                            <option value="IV.30">IV.30</option>
                                            <option value="IV.31">IV.31</option>
                                            <option value="IV.32">IV.32</option>
                                            <option value="IV.33">IV.33</option>
                                            <option value="0.34">0.34</option>
                                            <option value="0.35">0.35</option>
                                            <option value="0.36">0.36</option>
                                            <option value="0.37">0.37</option>
                                            <option value="0.38">0.38</option>
                                            <option value="0.39">0.39</option>
                                            <option value="0.40+">0.40+</option>
                                        </optgroup>
                                    @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                    @endif
                                </select>
                                <small class="text-muted">Select starting grade/division for this subclass</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small">Final Grade (Ending Grade)</label>
                                <select name="final_grade" id="edit_grade_final_grade" class="form-control division-select">
                                    <option value="">Select Ending Grade (Optional)</option>
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                        <optgroup label="A-LEVEL Divisions">
                                            <option value="I.15">I.15</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.12">I.12</option>
                                            <option value="II.11">II.11</option>
                                            <option value="II.10">II.10</option>
                                            <option value="II.9">II.9</option>
                                            <option value="III.8">III.8</option>
                                            <option value="III.7">III.7</option>
                                            <option value="III.6">III.6</option>
                                            <option value="IV.5">IV.5</option>
                                            <option value="IV.4">IV.4</option>
                                            <option value="IV.3">IV.3</option>
                                            <option value="0.2">0.2</option>
                                            <option value="0.1">0.1</option>
                                            <option value="0.0">0.0</option>
                                        </optgroup>
                                        <optgroup label="O-LEVEL Divisions">
                                            <option value="I.7">I.7</option>
                                            <option value="I.8">I.8</option>
                                            <option value="I.9">I.9</option>
                                            <option value="I.10">I.10</option>
                                            <option value="I.11">I.11</option>
                                            <option value="I.12">I.12</option>
                                            <option value="I.13">I.13</option>
                                            <option value="I.14">I.14</option>
                                            <option value="I.15">I.15</option>
                                            <option value="I.16">I.16</option>
                                            <option value="I.17">I.17</option>
                                            <option value="II.18">II.18</option>
                                            <option value="II.19">II.19</option>
                                            <option value="II.20">II.20</option>
                                            <option value="II.21">II.21</option>
                                            <option value="III.22">III.22</option>
                                            <option value="III.23">III.23</option>
                                            <option value="III.24">III.24</option>
                                            <option value="III.25">III.25</option>
                                            <option value="IV.26">IV.26</option>
                                            <option value="IV.27">IV.27</option>
                                            <option value="IV.28">IV.28</option>
                                            <option value="IV.29">IV.29</option>
                                            <option value="IV.30">IV.30</option>
                                            <option value="IV.31">IV.31</option>
                                            <option value="IV.32">IV.32</option>
                                            <option value="IV.33">IV.33</option>
                                            <option value="0.34">0.34</option>
                                            <option value="0.35">0.35</option>
                                            <option value="0.36">0.36</option>
                                            <option value="0.37">0.37</option>
                                            <option value="0.38">0.38</option>
                                            <option value="0.39">0.39</option>
                                            <option value="0.40+">0.40+</option>
                                        </optgroup>
                                    @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                    @endif
                                </select>
                                <small class="text-muted">Select ending grade/division for this subclass</small>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Define grade range for automatic student placement. Students with grades within this range will be automatically assigned to this subclass.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Grade Range
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Class Subclasses Modal -->
<div class="modal fade" id="viewClassSubclassesModal" tabindex="-1" aria-labelledby="viewClassSubclassesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewClassSubclassesModalLabel">
                    <i class="bi bi-layers"></i> Subclasses in <span id="modalClassName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="classSubclassesContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subclasses...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
                <button type="button" class="btn btn-primary-custom" id="addNewSubclassToClassBtn" style="display: none;">
                    <i class="bi bi-plus-circle"></i> Add New Subclass
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Students Modal -->
<div class="modal fade" id="viewStudentsModal" tabindex="-1" aria-labelledby="viewStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewStudentsModalLabel">
                    <i class="bi bi-people-fill"></i> Students in <span id="modalSubclassName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="studentsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading students...</p>
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

@include('includes.footer')

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Wait for jQuery and SweetAlert to be loaded
    (function($) {
        'use strict';

        // Initialize Select2 on all select elements
        function initializeSelect2() {
            $('select.form-select, select.form-control, select.division-select').each(function() {
                // Skip if already initialized or if inside a modal (will be initialized when modal opens)
                if ($(this).hasClass('select2-hidden-accessible') || $(this).closest('.modal').length > 0) {
                    return;
                }
                var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                $(this).select2({
                    theme: 'bootstrap-5',
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%',
                    minimumResultsForSearch: 0 // Always show search box
                });
            });
        }

        $(document).ready(function() {
            // Initialize Select2 on all select elements with search
            initializeSelect2();
        });

        // Re-initialize Select2 when modals are shown
        $('#addSubclassModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                // Initialize division selects first
                $('#subclass_first_grade, #subclass_final_grade').each(function() {
                    // Destroy existing Select2 if it exists
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        try {
                            $(this).select2('destroy');
                        } catch(e) {
                            // Ignore errors if already destroyed
                        }
                    }
                    var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                    var $select = $(this);

                    // Ensure select is visible before initializing
                    if (!$select.is(':visible')) {
                        $select.show();
                    }

                    $select.select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#addSubclassModal'),
                        minimumResultsForSearch: 0, // Always show search box
                        language: {
                            noResults: function() {
                                return "No results found";
                            },
                            searching: function() {
                                return "Searching...";
                            }
                        }
                    });
                });

                // Initialize other selects
                $('#subclass_class_select, #subclass_teacher_select, #addSubclassForm select[name="combieID"]').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        try {
                            $(this).select2('destroy');
                        } catch(e) {}
                    }
                    var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#addSubclassModal'),
                        minimumResultsForSearch: 0
                    });
                });
            }, 200);
        });

        $('#editSubclassModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                $('#edit_subclass_first_grade, #edit_subclass_final_grade, #edit_subclass_class_select, #edit_subclass_teacher_select, #editSubclassForm select[name="combieID"]').each(function() {
                    // Destroy existing Select2 if it exists
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#editSubclassModal'),
                        minimumResultsForSearch: 0 // Always show search box
                    });
                });
            }, 100);
        });

        $('#editGradeRangeModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                $('#edit_grade_first_grade, #edit_grade_final_grade').each(function() {
                    // Destroy existing Select2 if it exists
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#editGradeRangeModal'),
                        minimumResultsForSearch: 0 // Always show search box
                    });
                });
            }, 100);
        });

        // Initialize Select2 on other modals
        $('#addClassModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                $('#addClassForm select').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#addClassModal'),
                        minimumResultsForSearch: 0
                    });
                });
            }, 100);
        });

        $('#editClassModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                $('#editClassForm select').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#editClassModal'),
                        minimumResultsForSearch: 0
                    });
                });
            }, 100);
        });

        $('#addCombieModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                $('#addCombieForm select').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#addCombieModal'),
                        minimumResultsForSearch: 0
                    });
                });
            }, 100);
        });

        $('#editCombieModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                $('#editCombieForm select').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    var placeholder = $(this).find('option[value=""]').text() || 'Select...';
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#editCombieModal'),
                        minimumResultsForSearch: 0
                    });
                });
            }, 100);
        });

        // Destroy Select2 when modals are hidden to prevent conflicts
        $('#addSubclassModal').on('hidden.bs.modal', function() {
            $('#addSubclassForm select').select2('destroy');
        });

        $('#editSubclassModal').on('hidden.bs.modal', function() {
            $('#editSubclassForm select').select2('destroy');
        });

        $('#editGradeRangeModal').on('hidden.bs.modal', function() {
            $('#editGradeRangeForm select').select2('destroy');
        });

        $('#addClassModal').on('hidden.bs.modal', function() {
            $('#addClassForm select').select2('destroy');
        });

        $('#editClassModal').on('hidden.bs.modal', function() {
            $('#editClassForm select').select2('destroy');
            // Reset all class name options to enabled state
            $('#edit_class_name option').each(function() {
                $(this).prop('disabled', false)
                    .css('background-color', '')
                    .css('color', '');
                var text = $(this).text();
                if (text.indexOf('(Already Exists)') !== -1) {
                    $(this).text(text.replace(' (Already Exists)', ''));
                }
            });
            window.currentEditingClassName = null;
        });

        $('#addCombieModal').on('hidden.bs.modal', function() {
            $('#addCombieForm select').select2('destroy');
        });

        $('#editCombieModal').on('hidden.bs.modal', function() {
            $('#editCombieForm select').select2('destroy');
        });

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

        // Store user permissions for JavaScript checks
        var userPermissions = @json($teacherPermissions ?? collect());
        var userType = @json($user_type ?? '');
        var existingClassNames = @json($existingClassNames ?? []);

        // Helper function to check permission
        function hasPermission(permissionName) {
            if (userType === 'Admin') {
                return true;
            }
            return userPermissions.includes(permissionName);
        }

        // Check if forms exist
        console.log('Add Class Form exists:', $('#addClassForm').length > 0);
        console.log('Add Subclass Form exists:', $('#addSubclassForm').length > 0);

        // Subclass row counter
        var subclassRowCounter = 0;

        // Function to generate subclass row HTML
        function generateSubclassRow(index) {
            var rowHtml = `
                <div class="subclass-row mb-3 p-3 border rounded" data-row-index="${index}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="bi bi-layers text-primary-custom"></i> Subclass ${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-subclass-row" data-row-index="${index}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Subclass Name <span class="text-danger">*</span></label>
                            <input type="text" name="subclasses[${index}][subclass_name]" class="form-control form-control-sm" placeholder="e.g., A, B, C" required maxlength="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Class Teacher</label>
                            <select name="subclasses[${index}][teacherID]" class="form-select form-select-sm">
                                <option value="">Select Teacher (Optional)</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(isset($school_details) && $school_details->school_type == 'Secondary')
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Combination</label>
                            <select name="subclasses[${index}][combieID]" class="form-select form-select-sm">
                                <option value="">Select Combination (Optional)</option>
                                @foreach($combies as $combie)
                                    <option value="{{ $combie->combieID }}">{{ $combie->combie_name }} @if($combie->combie_code)({{ $combie->combie_code }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">First Grade (Optional)</label>
                            <select name="subclasses[${index}][first_grade]" class="form-select form-select-sm division-select">
                                <option value="">Select Starting Grade</option>
                                @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                    <optgroup label="A-LEVEL Divisions">
                                        <option value="I.15">I.15</option>
                                        <option value="I.14">I.14</option>
                                        <option value="I.13">I.13</option>
                                        <option value="I.12">I.12</option>
                                        <option value="II.11">II.11</option>
                                        <option value="II.10">II.10</option>
                                        <option value="II.9">II.9</option>
                                        <option value="III.8">III.8</option>
                                        <option value="III.7">III.7</option>
                                        <option value="III.6">III.6</option>
                                        <option value="IV.5">IV.5</option>
                                        <option value="IV.4">IV.4</option>
                                        <option value="IV.3">IV.3</option>
                                        <option value="0.2">0.2</option>
                                        <option value="0.1">0.1</option>
                                        <option value="0.0">0.0</option>
                                    </optgroup>
                                    <optgroup label="O-LEVEL Divisions">
                                        <option value="I.7">I.7</option>
                                        <option value="I.8">I.8</option>
                                        <option value="I.9">I.9</option>
                                        <option value="I.10">I.10</option>
                                        <option value="I.11">I.11</option>
                                        <option value="I.12">I.12</option>
                                        <option value="I.13">I.13</option>
                                        <option value="I.14">I.14</option>
                                        <option value="I.15">I.15</option>
                                        <option value="I.16">I.16</option>
                                        <option value="I.17">I.17</option>
                                        <option value="II.18">II.18</option>
                                        <option value="II.19">II.19</option>
                                        <option value="II.20">II.20</option>
                                        <option value="II.21">II.21</option>
                                        <option value="III.22">III.22</option>
                                        <option value="III.23">III.23</option>
                                        <option value="III.24">III.24</option>
                                        <option value="III.25">III.25</option>
                                        <option value="IV.26">IV.26</option>
                                        <option value="IV.27">IV.27</option>
                                        <option value="IV.28">IV.28</option>
                                        <option value="IV.29">IV.29</option>
                                        <option value="IV.30">IV.30</option>
                                        <option value="IV.31">IV.31</option>
                                        <option value="IV.32">IV.32</option>
                                        <option value="IV.33">IV.33</option>
                                        <option value="0.34">0.34</option>
                                        <option value="0.35">0.35</option>
                                        <option value="0.36">0.36</option>
                                        <option value="0.37">0.37</option>
                                        <option value="0.38">0.38</option>
                                        <option value="0.39">0.39</option>
                                        <option value="0.40+">0.40+</option>
                                    </optgroup>
                                @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Final Grade (Optional)</label>
                            <select name="subclasses[${index}][final_grade]" class="form-select form-select-sm division-select">
                                <option value="">Select Ending Grade</option>
                                @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                    <optgroup label="A-LEVEL Divisions">
                                        <option value="I.15">I.15</option>
                                        <option value="I.14">I.14</option>
                                        <option value="I.13">I.13</option>
                                        <option value="I.12">I.12</option>
                                        <option value="II.11">II.11</option>
                                        <option value="II.10">II.10</option>
                                        <option value="II.9">II.9</option>
                                        <option value="III.8">III.8</option>
                                        <option value="III.7">III.7</option>
                                        <option value="III.6">III.6</option>
                                        <option value="IV.5">IV.5</option>
                                        <option value="IV.4">IV.4</option>
                                        <option value="IV.3">IV.3</option>
                                        <option value="0.2">0.2</option>
                                        <option value="0.1">0.1</option>
                                        <option value="0.0">0.0</option>
                                    </optgroup>
                                    <optgroup label="O-LEVEL Divisions">
                                        <option value="I.7">I.7</option>
                                        <option value="I.8">I.8</option>
                                        <option value="I.9">I.9</option>
                                        <option value="I.10">I.10</option>
                                        <option value="I.11">I.11</option>
                                        <option value="I.12">I.12</option>
                                        <option value="I.13">I.13</option>
                                        <option value="I.14">I.14</option>
                                        <option value="I.15">I.15</option>
                                        <option value="I.16">I.16</option>
                                        <option value="I.17">I.17</option>
                                        <option value="II.18">II.18</option>
                                        <option value="II.19">II.19</option>
                                        <option value="II.20">II.20</option>
                                        <option value="II.21">II.21</option>
                                        <option value="III.22">III.22</option>
                                        <option value="III.23">III.23</option>
                                        <option value="III.24">III.24</option>
                                        <option value="III.25">III.25</option>
                                        <option value="IV.26">IV.26</option>
                                        <option value="IV.27">IV.27</option>
                                        <option value="IV.28">IV.28</option>
                                        <option value="IV.29">IV.29</option>
                                        <option value="IV.30">IV.30</option>
                                        <option value="IV.31">IV.31</option>
                                        <option value="IV.32">IV.32</option>
                                        <option value="IV.33">IV.33</option>
                                        <option value="0.34">0.34</option>
                                        <option value="0.35">0.35</option>
                                        <option value="0.36">0.36</option>
                                        <option value="0.37">0.37</option>
                                        <option value="0.38">0.38</option>
                                        <option value="0.39">0.39</option>
                                        <option value="0.40+">0.40+</option>
                                    </optgroup>
                                @elseif(isset($school_details) && $school_details->school_type == 'Primary')
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            `;
            return rowHtml;
        }

        // Handle checkbox change for has_subclasses
        $(document).on('change', '#has_subclasses_checkbox', function() {
            var hasSubclasses = $(this).is(':checked');
            if (hasSubclasses) {
                $('#subclassesSection').slideDown();
                // Add first subclass row if none exists
                if ($('#subclassesContainer .subclass-row').length === 0) {
                    var firstRow = generateSubclassRow(subclassRowCounter);
                    $('#subclassesContainer').append(firstRow);
                    subclassRowCounter++;
                    // Initialize Select2 for division selects
                    $('#subclassesContainer .division-select').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#addClassModal'),
                        minimumResultsForSearch: 0
                    });
                }
            } else {
                $('#subclassesSection').slideUp();
                $('#subclassesContainer').empty();
                subclassRowCounter = 0;
            }
        });

        // Show subclasses section when class is selected (only if checkbox is checked)
        $(document).on('change', '#add_class_name_select', function() {
            var selectedClass = $(this).val();
            var hasSubclasses = $('#has_subclasses_checkbox').is(':checked');
            if (selectedClass && hasSubclasses) {
                $('#subclassesSection').slideDown();
                // Add first subclass row if none exists
                if ($('#subclassesContainer .subclass-row').length === 0) {
                    var firstRow = generateSubclassRow(subclassRowCounter);
                    $('#subclassesContainer').append(firstRow);
                    subclassRowCounter++;
                    // Initialize Select2 for division selects
                    $('#subclassesContainer .division-select').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#addClassModal'),
                        minimumResultsForSearch: 0
                    });
                }
            } else if (!hasSubclasses) {
                $('#subclassesSection').slideUp();
            } else {
                $('#subclassesSection').slideUp();
                $('#subclassesContainer').empty();
                subclassRowCounter = 0;
            }
        });

        // Add subclass row
        $(document).on('click', '#addSubclassRowBtn', function(e) {
            e.preventDefault();
            var newRow = generateSubclassRow(subclassRowCounter);
            $('#subclassesContainer').append(newRow);
            subclassRowCounter++;
            // Initialize Select2 for new division selects
            $('#subclassesContainer .division-select').last().select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addClassModal'),
                minimumResultsForSearch: 0
            });
        });

        // Remove subclass row
        $(document).on('click', '.remove-subclass-row', function(e) {
            e.preventDefault();
            var rowIndex = $(this).data('row-index');
            $('.subclass-row[data-row-index="' + rowIndex + '"]').remove();
            // Re-number remaining rows
            $('#subclassesContainer .subclass-row').each(function(index) {
                $(this).find('h6').html('<i class="bi bi-layers text-primary-custom"></i> Subclass ' + (index + 1));
            });
        });

        // Handle Add Class Form Submission
        $(document).on('submit', '#addClassForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('create_class')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to create classes.'
                });
                return false;
            }

            console.log('Add Class Form submitted');

            // Validate class name is selected
            var className = $('#add_class_name_select').val();
            if (!className) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a class name.'
                });
                return false;
            }
            
            // Check if selected class name is disabled (already exists)
            var selectedOption = $('#add_class_name_select option:selected');
            if (selectedOption.prop('disabled')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'This class name already exists. Please select a different class name.'
                });
                return false;
            }

            // Check if class has subclasses
            var hasSubclasses = $('#has_subclasses_checkbox').is(':checked');
            
            // Validate at least one subclass only if has_subclasses is true
            var subclasses = [];
            if (hasSubclasses) {
                var subclassCount = $('#subclassesContainer .subclass-row').length;
                if (subclassCount === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please add at least one subclass for this class.'
                    });
                    return false;
                }

                // Collect subclass data
                $('#subclassesContainer .subclass-row').each(function() {
                    var subclassName = $(this).find('input[name*="[subclass_name]"]').val();
                    if (subclassName && subclassName.trim() !== '') {
                        subclasses.push({
                            subclass_name: subclassName,
                            teacherID: $(this).find('select[name*="[teacherID]"]').val() || null,
                            combieID: $(this).find('select[name*="[combieID]"]').val() || null,
                            first_grade: $(this).find('select[name*="[first_grade]"]').val() || null,
                            final_grade: $(this).find('select[name*="[final_grade]"]').val() || null
                        });
                    }
                });

                if (subclasses.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please add at least one subclass with a valid name.'
                    });
                    return false;
                }
            }

            var formData = {
                class_name: className,
                description: $('textarea[name="description"]').val(),
                teacherID: $('select[name="teacherID"]').val(),
                has_subclasses: hasSubclasses ? 1 : 0,
                subclasses: subclasses,
                _token: $('input[name="_token"]').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_class') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    console.log('AJAX request sending...');
                },
                success: function(response) {
                    console.log('Success Response:', response); // Debug
                    $submitBtn.prop('disabled', false).html(originalText);

                    // Hide modal first
                    $('#addClassModal').modal('hide');

                    // Check if response has success property
                    if (response && typeof response.success !== 'undefined') {
                        // Show success alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success || 'Class and subclasses created successfully!',
                            timer: 2500,
                            showConfirmButton: false,
                            confirmButtonColor: '#940000',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        });

                        // Reset form
                        $('#addClassForm')[0].reset();
                        $('#subclassesSection').hide();
                        $('#subclassesContainer').empty();
                        subclassRowCounter = 0;

                        // Reload after alert
                        setTimeout(function() {
                            window.location.reload();
                        }, 2600);
                    } else if (response && response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        // Default success if no explicit response structure
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Class and subclasses created successfully!',
                            timer: 2500,
                            showConfirmButton: false,
                            confirmButtonColor: '#940000'
                        });
                        $('#addClassForm')[0].reset();
                        $('#subclassesSection').hide();
                        $('#subclassesContainer').empty();
                        subclassRowCounter = 0;
                        setTimeout(function() {
                            window.location.reload();
                        }, 2600);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', {xhr: xhr, status: status, error: error});
                    console.log('Response Text:', xhr.responseText);
                    console.log('Response JSON:', xhr.responseJSON);
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorList = '';
                        if (Object.keys(errors).length > 0) {
                            Object.values(errors).forEach(err => {
                                if (Array.isArray(err)) {
                                    errorList += err[0] + '\n';
                                } else {
                                    errorList += err + '\n';
                                }
                            });
                        } else {
                            errorList = 'Validation failed. Please check your input.';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorList,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 500) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Server error occurred. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 0 || status === 'error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection or try again.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong. Please try again.');
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

        // Handle Combie Name Select Change
        $(document).on('change', '#combie_name_select', function() {
            var selectedValue = $(this).val();
            var $customGroup = $('#customCombieNameGroup');
            var $codeInput = $('#combie_code_input');

            if (selectedValue === 'custom') {
                // Show custom input field
                $customGroup.show();
                $('#custom_combie_name').prop('required', true);
                $('#combie_name_select').prop('required', false);
                $codeInput.val('');
            } else if (selectedValue && selectedValue !== '') {
                // Hide custom input field
                $customGroup.hide();
                $('#custom_combie_name').prop('required', false);
                $('#combie_name_select').prop('required', true);

                // Auto-fill code from data attribute
                var selectedOption = $(this).find('option:selected');
                var code = selectedOption.data('code');
                if (code) {
                    $codeInput.val(code);
                } else {
                    $codeInput.val('');
                }
            } else {
                // Empty selection
                $customGroup.hide();
                $('#custom_combie_name').prop('required', false);
                $('#combie_name_select').prop('required', true);
                $codeInput.val('');
            }
        });

        // Handle Add Combie Form Submission
        $(document).on('submit', '#addCombieForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('create_combie')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to create combinations.'
                });
                return false;
            }

            console.log('Add Combie Form submitted');

            // Get combie name - either from select or custom input
            var combieName = '';
            if ($('#combie_name_select').val() === 'custom') {
                combieName = $('#custom_combie_name').val();
                if (!combieName || combieName.trim() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a custom combination name.'
                    });
                    return false;
                }
            } else {
                combieName = $('#combie_name_select').val();
                if (!combieName || combieName === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a combination.'
                    });
                    return false;
                }
            }

            var formData = {
                combie_name: combieName,
                combie_code: $('#combie_code_input').val(),
                description: $('textarea[name="description"]').val(),
                status: $('select[name="status"]').val(),
                _token: $('input[name="_token"]').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_combie') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    console.log('AJAX request sending...');
                },
                success: function(response) {
                    console.log('Success Response:', response); // Debug
                    $submitBtn.prop('disabled', false).html(originalText);

                    // Hide modal first
                    $('#addCombieModal').modal('hide');

                    // Check if response has success property
                    if (response && typeof response.success !== 'undefined') {
                        // Show success alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success || 'Combination created successfully!',
                            timer: 2500,
                            showConfirmButton: false,
                            confirmButtonColor: '#940000',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        });

                        // Reset form
                        $('#addCombieForm')[0].reset();

                        // Reload after alert
                        setTimeout(function() {
                            window.location.reload();
                        }, 2600);
                    } else if (response && response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        // Default success if no explicit response structure
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Combination created successfully!',
                            timer: 2500,
                            showConfirmButton: false,
                            confirmButtonColor: '#940000'
                        });
                        $('#addCombieForm')[0].reset();
                        setTimeout(function() {
                            window.location.reload();
                        }, 2600);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', {xhr: xhr, status: status, error: error});
                    console.log('Response Text:', xhr.responseText);
                    console.log('Response JSON:', xhr.responseJSON);
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorList = '';
                        if (Object.keys(errors).length > 0) {
                            Object.values(errors).forEach(err => {
                                if (Array.isArray(err)) {
                                    errorList += err[0] + '\n';
                                } else {
                                    errorList += err + '\n';
                                }
                            });
                        } else {
                            errorList = 'Validation failed. Please check your input.';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorList,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 500) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Server error occurred. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 0 || status === 'error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection or try again.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong. Please try again.');
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

        // Handle Edit Combie Name Select Change
        $(document).on('change', '#edit_combie_name_select', function() {
            var selectedValue = $(this).val();
            var $customGroup = $('#editCustomCombieNameGroup');
            var $codeInput = $('#edit_combie_code');

            if (selectedValue === 'custom') {
                // Show custom input field
                $customGroup.show();
                $('#edit_custom_combie_name').prop('required', true);
                $('#edit_combie_name_select').prop('required', false);
            } else if (selectedValue && selectedValue !== '') {
                // Hide custom input field
                $customGroup.hide();
                $('#edit_custom_combie_name').prop('required', false);
                $('#edit_combie_name_select').prop('required', true);

                // Auto-fill code from data attribute
                var selectedOption = $(this).find('option:selected');
                var code = selectedOption.data('code');
                if (code) {
                    $codeInput.val(code);
                }
            } else {
                // Empty selection
                $customGroup.hide();
                $('#edit_custom_combie_name').prop('required', false);
                $('#edit_combie_name_select').prop('required', true);
            }
        });

        // Handle Edit Combie Button Click
        $(document).on('click', '.edit-combie-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('edit_combie')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit combinations.'
                });
                return false;
            }

            var combieID = $(this).data('combie-id');
            var combieName = $(this).data('combie-name');
            var combieCode = $(this).data('combie-code') || '';
            var combieDescription = $(this).data('combie-description') || '';
            var combieStatus = $(this).data('combie-status') || 'Active';

            // Populate edit form
            $('#edit_combieID').val(combieID);
            $('#edit_combie_code').val(combieCode);
            $('#edit_combie_description').val(combieDescription);
            $('#edit_combie_status').val(combieStatus);

            // Check if combie name exists in dropdown options
            var $select = $('#edit_combie_name_select');
            var optionExists = $select.find('option[value="' + combieName + '"]').length > 0;

            if (optionExists) {
                // Set dropdown value
                $select.val(combieName);
                $('#editCustomCombieNameGroup').hide();
                $('#edit_custom_combie_name').prop('required', false);
                $('#edit_combie_name_select').prop('required', true);

                // Auto-fill code if available
                var selectedOption = $select.find('option[value="' + combieName + '"]');
                var code = selectedOption.data('code');
                if (code && !combieCode) {
                    $('#edit_combie_code').val(code);
                }
            } else {
                // Use custom input
                $select.val('custom');
                $('#editCustomCombieNameGroup').show();
                $('#edit_custom_combie_name').val(combieName);
                $('#edit_custom_combie_name').prop('required', true);
                $('#edit_combie_name_select').prop('required', false);
            }

            // Close view modal and open edit modal
            $('#viewCombiesModal').modal('hide');
            $('#editCombieModal').modal('show');
        });

        // Handle Edit Combie Form Submission
        $(document).on('submit', '#editCombieForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('edit_combie')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit combinations.'
                });
                return false;
            }

            console.log('Edit Combie Form submitted');

            // Get combie name - either from select or custom input
            var combieName = '';
            if ($('#edit_combie_name_select').val() === 'custom') {
                combieName = $('#edit_custom_combie_name').val();
                if (!combieName || combieName.trim() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a custom combination name.'
                    });
                    return false;
                }
            } else {
                combieName = $('#edit_combie_name_select').val();
                if (!combieName || combieName === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a combination.'
                    });
                    return false;
                }
            }

            var formData = {
                combieID: $('#edit_combieID').val(),
                combie_name: combieName,
                combie_code: $('#edit_combie_code').val(),
                description: $('#edit_combie_description').val(),
                status: $('#edit_combie_status').val(),
                _token: $('input[name="_token"]').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_combie') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    console.log('AJAX request sending...');
                },
                success: function(response) {
                    console.log('Success Response:', response);
                    $submitBtn.prop('disabled', false).html(originalText);

                    // Hide modal first
                    $('#editCombieModal').modal('hide');

                    // Check if response has success property
                    if (response && typeof response.success !== 'undefined') {
                        // Show success alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success || 'Combination updated successfully!',
                            timer: 2500,
                            showConfirmButton: false,
                            confirmButtonColor: '#940000',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        });

                        // Reset form
                        $('#editCombieForm')[0].reset();

                        // Reload after alert
                        setTimeout(function() {
                            window.location.reload();
                        }, 2600);
                    } else if (response && response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', {xhr: xhr, status: status, error: error});
                    console.log('Response Text:', xhr.responseText);
                    console.log('Response JSON:', xhr.responseJSON);
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorList = '';
                        if (Object.keys(errors).length > 0) {
                            Object.values(errors).forEach(err => {
                                if (Array.isArray(err)) {
                                    errorList += err[0] + '\n';
                                } else {
                                    errorList += err + '\n';
                                }
                            });
                        } else {
                            errorList = 'Validation failed. Please check your input.';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorList,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 500) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Server error occurred. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 0 || status === 'error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection or try again.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong. Please try again.');
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

        // Handle Delete Combie Button Click
        $(document).on('click', '.delete-combie-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('delete_combie')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to delete combinations.'
                });
                return false;
            }

            var combieID = $(this).data('combie-id');
            var combieName = $(this).data('combie-name');

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete combination "' + combieName + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('delete_combie', ':id') }}".replace(':id', combieID),
                        type: "DELETE",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.success || 'Combination deleted successfully!',
                                timer: 2000,
                                showConfirmButton: false,
                                confirmButtonColor: '#940000'
                            }).then(function() {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                                ? xhr.responseJSON.error
                                : 'Failed to delete combination. Please try again.';
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

        // Handle Add Subclass Form Submission
        $(document).on('submit', '#addSubclassForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('create_subclass')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to create subclasses.'
                });
                return false;
            }

            console.log('Add Subclass Form submitted');

            var teacherID = $('#subclass_teacher_select').val() || $('select[name="teacherID"]', '#addSubclassForm').val();
            var combieID = $('select[name="combieID"]', '#addSubclassForm').val();
            var firstGrade = $('#subclass_first_grade').val() || '';
            var finalGrade = $('#subclass_final_grade').val() || '';

            var formData = {
                classID: $('select[name="classID"]', '#addSubclassForm').val(),
                subclass_name: $('input[name="subclass_name"]', '#addSubclassForm').val(),
                teacherID: teacherID,
                combieID: combieID,
                first_grade: firstGrade,
                final_grade: finalGrade,
                _token: $('input[name="_token"]', '#addSubclassForm').val()
            };

            console.log('TeacherID value:', teacherID);
            console.log('CombieID value:', combieID);

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_sub_lass') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    console.log('AJAX request sending...');
                },
                success: function(response) {
                    console.log('Success Response:', response); // Debug
                    $submitBtn.prop('disabled', false).html(originalText);

                    // Hide modal first
                    $('#addSubclassModal').modal('hide');

                    // Check if response has success property
                    if (response && typeof response.success !== 'undefined') {
                        // Show success alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success || 'Subclass created successfully!',
                            timer: 2500,
                            showConfirmButton: false,
                            confirmButtonColor: '#940000',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        });

                        // Reset form
                        $('#addSubclassForm')[0].reset();

                        // Reload after alert
                        setTimeout(function() {
                            window.location.reload();
                        }, 2600);
                    } else if (response && response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        // Default success if no explicit response structure
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Subclass created successfully!',
                            timer: 2500,
                            showConfirmButton: false,
                            confirmButtonColor: '#940000'
                        });
                        $('#addSubclassForm')[0].reset();
                        setTimeout(function() {
                            window.location.reload();
                        }, 2600);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', {xhr: xhr, status: status, error: error});
                    console.log('Response Text:', xhr.responseText);
                    console.log('Response JSON:', xhr.responseJSON);
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorList = '';
                        if (Object.keys(errors).length > 0) {
                            Object.values(errors).forEach(err => {
                                if (Array.isArray(err)) {
                                    errorList += err[0] + '\n';
                                } else {
                                    errorList += err + '\n';
                                }
                            });
                        } else {
                            errorList = 'Validation failed. Please check your input.';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorList,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 500) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Server error occurred. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 0 || status === 'error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection or try again.',
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 400) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Bad request. Please check your input.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong. Please try again.');
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

        // Handle View Students Button Click
        $(document).on('click', '.view-students-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('view_students')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to view students.'
                });
                return false;
            }

            var subclassID = $(this).data('subclass-id');
            var subclassName = $(this).data('subclass-name');

            $('#modalSubclassName').text(subclassName);
            $('#studentsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading students...</p></div>');

            $('#viewStudentsModal').modal('show');

            $.ajax({
                url: "{{ route('get_subclass_students', ':id') }}".replace(':id', subclassID),
                type: "GET",
                success: function(response) {
                    if (response.students && response.students.length > 0) {
                        var html = '<div class="table-responsive"><table class="table table-hover align-middle"><thead class="bg-primary-custom text-white"><tr><th>Photo</th><th>Name</th><th>Admission Number</th><th>Gender</th><th>Date of Birth</th><th>Parent</th><th>Status</th></tr></thead><tbody>';

                        // Function to generate random color based on student name (deterministic)
                        function getColorFromName(name) {
                            if (!name) return '#940000';
                            var colors = [
                                '#940000', '#007bff', '#28a745', '#ffc107', '#dc3545',
                                '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997',
                                '#6610f2', '#6c757d', '#343a40', '#f8f9fa', '#212529',
                                '#1abc9c', '#3498db', '#9b59b6', '#e74c3c', '#f39c12',
                                '#16a085', '#2980b9', '#8e44ad', '#c0392b', '#d35400',
                                '#27ae60', '#34495e', '#e67e22', '#95a5a6', '#2c3e50'
                            ];
                            var hash = 0;
                            for (var i = 0; i < name.length; i++) {
                                hash = name.charCodeAt(i) + ((hash << 5) - hash);
                            }
                            return colors[Math.abs(hash) % colors.length];
                        }

                        response.students.forEach(function(student) {
                            var baseUrl = '{{ asset("") }}';
                            var photoUrl = '';

                            // Get first letter of student name
                            var firstName = student.first_name || '';
                            var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';

                            // Generate color based on student name
                            var studentFullName = (student.first_name || '') + ' ' + (student.last_name || '');
                            var placeholderColor = getColorFromName(studentFullName);

                            // Determine photo URL
                            if (student.photo) {
                                photoUrl = baseUrl + 'userImages/' + student.photo;
                            } else {
                                photoUrl = '';
                            }

                            // Create photo HTML with placeholder
                            var photoHtml = '';
                            if (photoUrl) {
                                photoHtml = '<div style="position: relative; display: inline-block;">' +
                                    '<img src="' + photoUrl + '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #940000;" alt="Student" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                                    '<div class="rounded-circle d-none align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>' +
                                    '</div>';
                            } else {
                                photoHtml = '<div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>';
                            }

                            var parentName = student.parent ? student.parent.first_name + ' ' + student.parent.last_name : 'N/A';
                            
                            // Add red alarm icon if student has health conditions
                            var healthAlarmIcon = '';
                            if ((student.is_disabled && student.is_disabled == 1) || 
                                (student.has_epilepsy && student.has_epilepsy == 1) || 
                                (student.has_allergies && student.has_allergies == 1)) {
                                healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                            }

                            html += '<tr>';
                            html += '<td>' + photoHtml + '</td>';
                            html += '<td><strong>' + student.first_name + ' ' + (student.middle_name || '') + ' ' + student.last_name + healthAlarmIcon + '</strong></td>';
                            html += '<td>' + student.admission_number + '</td>';
                            html += '<td>' + student.gender + '</td>';
                            html += '<td>' + (student.date_of_birth ? new Date(student.date_of_birth).toLocaleDateString() : 'N/A') + '</td>';
                            html += '<td>' + parentName + '</td>';
                            html += '<td><span class="badge bg-' + (student.status == 'Active' ? 'success' : 'secondary') + '">' + student.status + '</span></td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                        $('#studentsContent').html(html);
                    } else {
                        $('#studentsContent').html('<div class="text-center py-5"><i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i><p class="mt-3 mb-0 text-muted">No students found in this subclass.</p></div>');
                    }
                },
                error: function(xhr) {
                    $('#studentsContent').html('<div class="alert alert-danger">Failed to load students. Please try again.</div>');
                }
            });
        });

        // Handle Edit Subclass Button Click
        $(document).on('click', '.edit-subclass-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('edit_subclass')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit subclasses.'
                });
                return false;
            }

            var subclassID = $(this).data('subclass-id');

            console.log('Edit Subclass clicked:', subclassID);

            // Close view subclasses modal if open
            if ($('#viewClassSubclassesModal').hasClass('show')) {
                $('#viewClassSubclassesModal').modal('hide');
            }

            // Show loading state
            setTimeout(function() {
                $('#editSubclassModal').modal('show');
            }, 300);
            $('#editSubclassForm input, #editSubclassForm select').prop('disabled', true);

            // Fetch subclass data
            $.ajax({
                url: "{{ route('get_subclass', ':id') }}".replace(':id', subclassID),
                type: "GET",
                success: function(response) {
                    console.log('Subclass data received:', response);

                    if (response.success && response.subclass) {
                        var subclass = response.subclass;

                        // Populate form fields
                        $('#edit_subclassID').val(subclass.subclassID);
                        $('#edit_subclass_class_select').val(subclass.classID);
                        
                        // Set class display (readonly) - class name should be in response
                        if (subclass.class_name) {
                            $('#edit_subclass_class_display').val(subclass.class_name);
                        } else if (subclass.class && subclass.class.class_name) {
                            $('#edit_subclass_class_display').val(subclass.class.class_name);
                        } else {
                            // Fallback: fetch class name via AJAX
                            $.ajax({
                                url: "{{ route('get_class', ':id') }}".replace(':id', subclass.classID),
                                type: "GET",
                                success: function(classResponse) {
                                    if (classResponse.success && classResponse.class) {
                                        $('#edit_subclass_class_display').val(classResponse.class.class_name);
                                    }
                                }
                            });
                        }
                        
                        $('#edit_subclass_name').val(subclass.subclass_name);

                        // Set teacher
                        if (subclass.teacherID) {
                            $('#edit_subclass_teacher_select').val(subclass.teacherID);
                        } else {
                            $('#edit_subclass_teacher_select').val('');
                        }

                        // Set combie (if available)
                        if (subclass.combieID && $('#edit_subclass_combie_select').length > 0) {
                            $('#edit_subclass_combie_select').val(subclass.combieID);
                        } else if ($('#edit_subclass_combie_select').length > 0) {
                            $('#edit_subclass_combie_select').val('');
                        }

                        // Set grade range
                        if (subclass.first_grade) {
                            $('#edit_subclass_first_grade').val(subclass.first_grade).trigger('change');
                        } else {
                            $('#edit_subclass_first_grade').val('').trigger('change');
                        }

                        if (subclass.final_grade) {
                            $('#edit_subclass_final_grade').val(subclass.final_grade).trigger('change');
                        } else {
                            $('#edit_subclass_final_grade').val('').trigger('change');
                        }

                        // Enable form fields
                        $('#editSubclassForm input, #editSubclassForm select').prop('disabled', false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load subclass data.',
                            confirmButtonColor: '#940000'
                        });
                        $('#editSubclassModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching subclass:', xhr);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : 'Failed to load subclass data. Please try again.';

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#940000'
                    });
                    $('#editSubclassModal').modal('hide');
                }
            });
        });

        // Handle Edit Subclass Form Submission
        $(document).on('submit', '#editSubclassForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('edit_subclass')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit subclasses.'
                });
                return false;
            }

            console.log('Edit Subclass Form submitted');

            var teacherID = $('#edit_subclass_teacher_select').val();
            var combieID = $('#edit_subclass_combie_select').length > 0 ? $('#edit_subclass_combie_select').val() : '';
            var firstGrade = $('#edit_subclass_first_grade').val() || '';
            var finalGrade = $('#edit_subclass_final_grade').val() || '';

            var formData = {
                subclassID: $('#edit_subclassID').val(),
                classID: $('#edit_subclass_class_select').val(),
                subclass_name: $('#edit_subclass_name').val(),
                teacherID: teacherID,
                combieID: combieID,
                first_grade: firstGrade,
                final_grade: finalGrade,
                _token: $('input[name="_token"]', '#editSubclassForm').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_subclass') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    console.log('Update response:', response);

                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000
                        }).then(function() {
                            $('#editSubclassModal').modal('hide');
                            location.reload();
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
                    console.error('Update error:', xhr);
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        // Validation errors
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

        // Handle View Class Subclasses Button Click
        $(document).on('click', '.view-class-subclasses-btn', function(e) {
            e.preventDefault();

            var classID = $(this).data('class-id');
            var className = $(this).data('class-name');

            $('#modalClassName').text(className);
            $('#classSubclassesContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading subclasses...</p></div>');
            $('#addNewSubclassToClassBtn').data('class-id', classID).data('class-name', className).show();
            $('#viewClassSubclassesModal').modal('show');

            $.ajax({
                url: "{{ route('get_class_subclasses', ':id') }}".replace(':id', classID),
                type: "GET",
                success: function(response) {
                    if (response.success && response.subclasses && response.subclasses.length > 0) {
                        var html = '<div class="row g-3">';
                        response.subclasses.forEach(function(subclass) {
                            html += '<div class="col-12 col-sm-6 col-md-4 col-lg-3">';
                            html += '<div class="card border shadow-sm h-100">';
                            html += '<div class="card-header bg-primary-custom text-white text-center py-2">';
                            html += '<h6 class="mb-0">' + (subclass.display_name || subclass.stream_code || (subclass.class_name + ' ' + subclass.subclass_name)) + '</h6>';
                            html += '</div>';
                            html += '<div class="card-body p-2">';
                            html += '<div class="mb-2">';
                            html += '<small class="text-muted"><i class="bi bi-people"></i> Students:</small> ';
                            html += '<strong>' + (subclass.student_count || 0) + '</strong>';
                            html += '</div>';
                            if (subclass.teacher_name) {
                                html += '<div class="mb-2">';
                                html += '<small class="text-muted"><i class="bi bi-person-badge"></i> Teacher:</small> ';
                                html += '<span class="badge bg-info">' + subclass.teacher_name + '</span>';
                                html += '</div>';
                            }
                            if (subclass.combie_name) {
                                html += '<div class="mb-2">';
                                html += '<small class="text-muted"><i class="bi bi-layers"></i> Combination:</small> ';
                                html += '<span class="badge bg-secondary">' + subclass.combie_name;
                                if (subclass.combie_code) {
                                    html += ' (' + subclass.combie_code + ')';
                                }
                                html += '</span>';
                                html += '</div>';
                            }
                            html += '<div class="mb-2">';
                            html += '<span class="badge ' + (subclass.status === 'Active' ? 'bg-success' : 'bg-secondary') + '">' + subclass.status + '</span>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="card-footer bg-light p-2">';
                            html += '<div class="d-flex gap-1 justify-content-end">';
                            if (hasPermission('edit_subclass')) {
                                html += '<button class="btn btn-sm btn-warning edit-subclass-btn" data-subclass-id="' + subclass.subclassID + '" title="Edit Subclass">';
                                html += '<i class="bi bi-pencil-square"></i>';
                                html += '</button>';
                            }
                            if (hasPermission('view_students')) {
                                var displayName = subclass.display_name || subclass.stream_code || (subclass.class_name + ' ' + subclass.subclass_name);
                                html += '<button class="btn btn-sm btn-info view-students-btn" data-subclass-id="' + subclass.subclassID + '" data-subclass-name="' + displayName + '" title="View Students">';
                                html += '<i class="bi bi-eye"></i>';
                                html += '</button>';
                            }
                            if (hasPermission('delete_subclass')) {
                                var displayName = subclass.display_name || subclass.stream_code || (subclass.class_name + ' ' + subclass.subclass_name);
                                html += '<button class="btn btn-sm btn-danger delete-subclass-btn" data-subclass-id="' + subclass.subclassID + '" data-subclass-name="' + displayName + '" title="Delete Subclass">';
                                html += '<i class="bi bi-trash"></i>';
                                html += '</button>';
                            }
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        });
                        html += '</div>';
                        $('#classSubclassesContent').html(html);
                    } else {
                        $('#classSubclassesContent').html(
                            '<div class="text-center py-5">' +
                            '<i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>' +
                            '<p class="mt-3 mb-0 text-muted">No subclasses found for this class.</p>' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching subclasses:', xhr);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : 'Failed to load subclasses. Please try again.';
                    $('#classSubclassesContent').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> ' + errorMsg +
                        '</div>'
                    );
                }
            });
        });

        // Handle Add New Subclass to Class Button
        $(document).on('click', '#addNewSubclassToClassBtn', function(e) {
            e.preventDefault();
            var classID = $(this).data('class-id');
            var className = $(this).data('class-name');
            
            // Close the view modal
            $('#viewClassSubclassesModal').modal('hide');
            
            // Open add subclass modal and pre-select the class
            setTimeout(function() {
                $('#addSubclassModal').modal('show');
                $('#subclass_class_select').val(classID).trigger('change');
            }, 300);
        });

        // Handle View Classes Button Click
        $(document).on('click', '#viewClassesBtn', function(e) {
            e.preventDefault();

            if (!hasPermission('view_all_class')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to view classes.'
                });
                return false;
            }

            console.log('View Classes clicked');

            $('#viewClassesModal').modal('show');
            $('#classesContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading classes...</p></div>');

            $.ajax({
                url: "{{ route('get_classes') }}",
                type: "GET",
                success: function(response) {
                    console.log('Classes data received:', response);

                    if (response.success && response.classes && response.classes.length > 0) {
                        var html = '<div class="row g-4">';

                        response.classes.forEach(function(classItem) {
                            var classId = 'class-' + classItem.classID;

                            html += '<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">';
                            html += '<div class="card border-0 shadow-sm h-100 class-card">';

                            // Card Header with Icon
                            html += '<div class="card-header bg-primary-custom text-white text-center py-3">';
                            html += '<div class="d-flex justify-content-center align-items-center mb-2">';
                            html += '<i class="bi bi-mortarboard-fill" style="font-size: 2.5rem;"></i>';
                            html += '</div>';
                            html += '<h5 class="mb-1 fw-bold">' + classItem.class_name + '</h5>';
                            var classStatus = classItem.status || 'Inactive';
                            var statusBadgeClass = classStatus === 'Active' ? 'bg-success' : 'bg-secondary';
                            html += '<div class="mt-2">';
                            html += '<span class="badge ' + statusBadgeClass + '">' + classStatus + '</span>';
                            html += '</div>';
                            html += '</div>';

                            // Card Body - Compact Layout
                            html += '<div class="card-body p-3">';

                            // Quick Stats Row
                            html += '<div class="row g-2 mb-3">';
                            // Students
                            html += '<div class="col-6">';
                            html += '<div class="text-center p-2 bg-light rounded">';
                            html += '<i class="bi bi-people-fill text-success d-block mb-1" style="font-size: 1.5rem;"></i>';
                            html += '<div class="fw-bold text-dark">' + classItem.total_students + '</div>';
                            html += '<small class="text-muted">Students</small>';
                            html += '</div>';
                            html += '</div>';
                            // Subclasses
                            html += '<div class="col-6">';
                            html += '<div class="text-center p-2 bg-light rounded">';
                            html += '<i class="bi bi-layers-fill text-primary-custom d-block mb-1" style="font-size: 1.5rem;"></i>';
                            html += '<div class="fw-bold text-dark">' + classItem.subclass_count + '</div>';
                            html += '<small class="text-muted">Subclasses</small>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>'; // End row

                            // Coordinator - Compact
                            html += '<div class="mb-3">';
                            html += '<div class="d-flex align-items-center justify-content-between">';
                            html += '<span class="text-muted small"><i class="bi bi-person-badge"></i> Coordinator:</span>';
                            if (classItem.coordinator_name && classItem.coordinator_name !== 'Not Assigned') {
                                html += '<span class="badge bg-info text-white">' + classItem.coordinator_name + '</span>';
                            } else {
                                html += '<span class="badge bg-secondary">Not Assigned</span>';
                            }
                            html += '</div>';
                            html += '</div>';

                            // Hidden Details Section (View More)
                            html += '<div class="class-details-' + classItem.classID + '" style="display: none;">';

                            // Subclasses List
                            if (classItem.subclasses && classItem.subclasses.length > 0) {
                                html += '<div class="mb-3">';
                                html += '<div class="d-flex align-items-center mb-2">';
                                html += '<i class="bi bi-list-ul text-primary-custom me-2"></i>';
                                html += '<strong class="small">Subclass List:</strong>';
                                html += '</div>';
                                html += '<div class="ms-3">';
                                classItem.subclasses.forEach(function(subclass) {
                                    html += '<div class="mb-1">';
                                    html += '<span class="badge bg-light text-dark border small">';
                                    html += '<i class="bi bi-mortarboard"></i> ' + (subclass.stream_code || subclass.subclass_name);
                                    html += ' <small class="text-muted">(' + subclass.student_count + ')</small>';
                                    html += '</span>';
                                    html += '</div>';
                                });
                                html += '</div>';
                                html += '</div>';
                            }

                            // Description if available
                            if (classItem.description) {
                                html += '<div class="mb-2">';
                                html += '<div class="d-flex align-items-center mb-1">';
                                html += '<i class="bi bi-info-circle text-primary-custom me-2"></i>';
                                html += '<strong class="small">Description:</strong>';
                                html += '</div>';
                                html += '<small class="text-muted ms-4">' + classItem.description + '</small>';
                                html += '</div>';
                            }

                            html += '</div>'; // End hidden details

                            html += '</div>'; // End card-body

                            // Card Footer with Actions
                            html += '<div class="card-footer bg-light p-2">';
                            html += '<div class="d-flex justify-content-between align-items-center">';

                            // View More/Less Button
                            html += '<button class="btn btn-sm btn-outline-primary view-more-btn" data-class-id="' + classItem.classID + '" data-expanded="false">';
                            html += '<i class="bi bi-chevron-down"></i> View More';
                            html += '</button>';

                            // Action Buttons
                            html += '<div class="d-flex gap-1">';
                            var classStatus = classItem.status || 'Inactive';
                            var activateBtnClass = classStatus === 'Active' ? 'btn-secondary' : 'btn-success';
                            var activateIcon = classStatus === 'Active' ? 'x-circle' : 'check-circle';
                            var activateTitle = classStatus === 'Active' ? 'Deactivate' : 'Activate';
                            html += '<button class="btn btn-sm ' + activateBtnClass + ' text-white activate-class-btn" data-class-id="' + classItem.classID + '" data-class-name="' + classItem.class_name + '" data-current-status="' + classStatus + '" title="' + activateTitle + ' Class">';
                            html += '<i class="bi bi-' + activateIcon + '-fill"></i>';
                            html += '</button>';
                            html += '<button class="btn btn-sm btn-warning text-dark edit-class-btn" data-class-id="' + classItem.classID + '" data-class-name="' + classItem.class_name + '" title="Edit Class">';
                            html += '<i class="bi bi-pencil-square"></i>';
                            html += '</button>';
                            html += '<button class="btn btn-sm btn-danger text-white delete-class-btn" data-class-id="' + classItem.classID + '" data-class-name="' + classItem.class_name + '" title="Delete Class">';
                            html += '<i class="bi bi-trash-fill"></i>';
                            html += '</button>';
                            html += '</div>';

                            html += '</div>'; // End flex
                            html += '</div>'; // End card-footer

                            html += '</div>'; // End card
                            html += '</div>'; // End col
                        });

                        html += '</div>'; // End row
                        $('#classesContent').html(html);
                    } else {
                        $('#classesContent').html(
                            '<div class="text-center py-5">' +
                            '<i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>' +
                            '<p class="mt-3 mb-0 text-muted">No classes found. Click "Add Class" to create a new class.</p>' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching classes:', xhr);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : 'Failed to load classes. Please try again.';

                    $('#classesContent').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> ' + errorMsg +
                        '</div>'
                    );
                }
            });
        });

        // Handle View Class Grading Button Click
        $(document).on('click', '#viewClassGradingBtn', function(e) {
            e.preventDefault();

            if (!hasPermission('view_students')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to view class grading.'
                });
                return false;
            }

            console.log('View Class Grading clicked');

            $('#viewClassGradingModal').modal('show');
            $('#classGradingContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading class grading information...</p></div>');

            $.ajax({
                url: "{{ route('get_class_grading') }}",
                type: "GET",
                success: function(response) {
                    console.log('Class grading data received:', response);

                    if (response.success && response.subclasses && response.subclasses.length > 0) {
                        var isSecondary = response.school_type === 'Secondary';
                        var headerRow = '<tr><th>#</th><th>Class</th><th>Subclass</th><th>Stream Code</th>';
                        if (isSecondary) {
                            headerRow += '<th>Combination</th>';
                        }
                        headerRow += '<th>First Grade</th><th>Final Grade</th><th>Grade Range</th><th>Actions</th></tr>';
                        var html = '<div class="table-responsive"><table class="table table-hover table-striped"><thead class="bg-primary-custom text-white">' + headerRow + '</thead><tbody>';

                        response.subclasses.forEach(function(subclass, index) {
                            var gradeRange = '';
                            if (subclass.first_grade && subclass.final_grade) {
                                gradeRange = subclass.first_grade + ' - ' + subclass.final_grade;
                            } else if (subclass.first_grade) {
                                gradeRange = subclass.first_grade + ' (only)';
                            } else if (subclass.final_grade) {
                                gradeRange = 'Up to ' + subclass.final_grade;
                            } else {
                                gradeRange = '<span class="text-muted">No grade requirement</span>';
                            }

                            var combieDisplay = '';
                            if (isSecondary) {
                                if (subclass.combie_name) {
                                    combieDisplay = '<span class="badge bg-info text-white">' + subclass.combie_name;
                                    if (subclass.combie_code) {
                                        combieDisplay += ' (' + subclass.combie_code + ')';
                                    }
                                    combieDisplay += '</span>';
                                } else {
                                    combieDisplay = '<span class="text-muted">Not Assigned</span>';
                                }
                            }

                            html += '<tr>';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td><strong>' + subclass.class_name + '</strong></td>';
                            html += '<td>' + subclass.subclass_name + '</td>';
                            html += '<td>' + (subclass.stream_code || '-') + '</td>';
                            if (isSecondary) {
                                html += '<td>' + combieDisplay + '</td>';
                            }
                            html += '<td>' + (subclass.first_grade || '<span class="text-muted">-</span>') + '</td>';
                            html += '<td>' + (subclass.final_grade || '<span class="text-muted">-</span>') + '</td>';
                            html += '<td>' + gradeRange + '</td>';
                            html += '<td>';
                            if (hasPermission('edit_subclass')) {
                                html += '<button class="btn btn-sm btn-warning edit-grade-range-btn" data-subclass-id="' + subclass.subclassID + '" data-subclass-name="' + (subclass.stream_code || subclass.subclass_name) + '" data-first-grade="' + (subclass.first_grade || '') + '" data-final-grade="' + (subclass.final_grade || '') + '" title="Edit Grade Range"><i class="bi bi-pencil-square"></i></button>';
                            }
                            html += '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                        $('#classGradingContent').html(html);

                        // Show search box
                        $('#classGradingSearchContainer').show();

                        // Initialize search functionality
                        initializeClassGradingSearch();
                    } else {
                        $('#classGradingContent').html(
                            '<div class="text-center py-5">' +
                            '<i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>' +
                            '<p class="mt-3 mb-0 text-muted">No subclasses found.</p>' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching class grading:', xhr);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : 'Failed to load class grading information. Please try again.';

                    $('#classGradingContent').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> ' + errorMsg +
                        '</div>'
                    );
                }
            });
        });

        // Initialize Class Grading Search Functionality
        function initializeClassGradingSearch() {
            var $searchInput = $('#classGradingSearchInput');
            var $clearBtn = $('#clearClassGradingSearch');
            var $table = $('#classGradingContent table');

            if ($table.length === 0) return;

            // Search functionality
            $searchInput.on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                var $rows = $table.find('tbody tr');

                if (searchTerm === '') {
                    $rows.show();
                    $clearBtn.prop('disabled', true);
                } else {
                    $clearBtn.prop('disabled', false);
                    $rows.each(function() {
                        var $row = $(this);
                        var text = $row.text().toLowerCase();
                        if (text.indexOf(searchTerm) > -1) {
                            $row.show();
                        } else {
                            $row.hide();
                        }
                    });

                    // Show message if no results
                    var visibleRows = $rows.filter(':visible').length;
                    if (visibleRows === 0) {
                        if ($table.next('.no-results-message').length === 0) {
                            $table.after('<div class="no-results-message alert alert-info mt-3"><i class="bi bi-info-circle"></i> No results found matching "' + searchTerm + '"</div>');
                        }
                    } else {
                        $table.next('.no-results-message').remove();
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
        $('#viewClassGradingModal').on('hidden.bs.modal', function() {
            $('#classGradingSearchInput').val('');
            $('#classGradingSearchContainer').hide();
            $('#classGradingContent table').next('.no-results-message').remove();
        });

        // Handle Edit Grade Range Button Click
        $(document).on('click', '.edit-grade-range-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('edit_subclass')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit grade ranges.'
                });
                return false;
            }

            var subclassID = $(this).data('subclass-id');
            var subclassName = $(this).data('subclass-name');
            var firstGrade = $(this).data('first-grade') || '';
            var finalGrade = $(this).data('final-grade') || '';

            $('#edit_grade_subclassID').val(subclassID);
            $('#edit_grade_subclass_name').val(subclassName);
            $('#edit_grade_first_grade').val(firstGrade).trigger('change');
            $('#edit_grade_final_grade').val(finalGrade).trigger('change');

            $('#editGradeRangeModal').modal('show');
        });

        // Handle Edit Grade Range Form Submission
        $(document).on('submit', '#editGradeRangeForm', function(e) {
            e.preventDefault();

            if (!hasPermission('edit_subclass')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit grade ranges.'
                });
                return false;
            }

            var subclassID = $('#edit_grade_subclassID').val();
            var firstGrade = $('#edit_grade_first_grade').val() || '';
            var finalGrade = $('#edit_grade_final_grade').val() || '';

            var formData = {
                subclassID: subclassID,
                first_grade: firstGrade,
                final_grade: finalGrade,
                _token: $('input[name="_token"]', '#editGradeRangeForm').val()
            };

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_grade_range') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    console.log('Update response:', response);

                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000
                        }).then(function() {
                            $('#editGradeRangeModal').modal('hide');
                            // Refresh the class grading view
                            $('#viewClassGradingBtn').click();
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
                    console.error('Update error:', xhr);
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

        // Handle View More/Less Button Click (Classes)
        $(document).on('click', '.view-more-btn', function(e) {
            e.preventDefault();
            var classID = $(this).data('class-id');
            var isExpanded = $(this).data('expanded');
            var $details = $('.class-details-' + classID);
            var $btn = $(this);

            if (isExpanded) {
                // Collapse
                $details.slideUp(300);
                $btn.html('<i class="bi bi-chevron-down"></i> View More');
                $btn.data('expanded', false);
            } else {
                // Expand
                $details.slideDown(300);
                $btn.html('<i class="bi bi-chevron-up"></i> View Less');
                $btn.data('expanded', true);
            }
        });

        // Handle View More/Less Button Click (Subclasses)
        $(document).on('click', '.view-more-subclass-btn', function(e) {
            e.preventDefault();
            var subclassID = $(this).data('subclass-id');
            var isExpanded = $(this).data('expanded');
            var $details = $('.subclass-details-' + subclassID);
            var $btn = $(this);

            if (isExpanded) {
                // Collapse
                $details.slideUp(300);
                $btn.html('<i class="bi bi-chevron-down"></i> View More');
                $btn.data('expanded', false);
            } else {
                // Expand
                $details.slideDown(300);
                $btn.html('<i class="bi bi-chevron-up"></i> View Less');
                $btn.data('expanded', true);

                // Load subjects if not already loaded
                var $subjectsContainer = $('#subjects-list-' + subclassID);
                if ($subjectsContainer.length > 0 && $subjectsContainer.html().trim() === '') {
                    loadSubclassSubjects(subclassID);
                }
            }
        });

        // Function to load subjects for a subclass
        function loadSubclassSubjects(subclassID) {
            var $container = $('#subjects-list-' + subclassID);
            $container.html('<div class="text-center py-2"><small class="text-muted"><i class="bi bi-hourglass-split"></i> Loading...</small></div>');

            $.ajax({
                url: "{{ route('get_subclass_subjects', ':id') }}".replace(':id', subclassID),
                type: "GET",
                success: function(response) {
                    if (response.success && response.subjects && response.subjects.length > 0) {
                        var html = '<div class="row g-2">';
                        response.subjects.forEach(function(subject) {
                            html += '<div class="col-6 col-md-4">';
                            html += '<div class="p-2 bg-light rounded border">';
                            html += '<div class="d-flex align-items-center mb-1">';
                            html += '<i class="bi bi-book text-primary-custom me-1"></i>';
                            html += '<strong class="small">' + subject.subject_name + '</strong>';
                            html += '</div>';
                            if (subject.subject_code) {
                                html += '<small class="text-muted">Code: ' + subject.subject_code + '</small><br>';
                            }
                            html += '<small class="text-muted">';
                            html += '<i class="bi bi-person"></i> ' + subject.teacher_name;
                            html += '</small>';
                            html += '</div>';
                            html += '</div>';
                        });
                        html += '</div>';
                        $container.html(html);
                    } else {
                        $container.html('<div class="text-center py-2"><small class="text-muted">No subjects assigned</small></div>');
                    }
                },
                error: function(xhr) {
                    $container.html('<div class="text-center py-2"><small class="text-danger">Failed to load subjects</small></div>');
                }
            });
        }

        // Handle View Subjects Button Click (from View More section)
        $(document).on('click', '.view-subjects-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('view_subjects')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to view subjects.'
                });
                return false;
            }

            var subclassID = $(this).data('subclass-id');
            var subclassName = $(this).data('subclass-name');

            $('#modalSubclassNameSubjects').text(subclassName);
            $('#subjectsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading subjects...</p></div>');

            $('#viewSubjectsModal').modal('show');

            $.ajax({
                url: "{{ route('get_subclass_subjects', ':id') }}".replace(':id', subclassID),
                type: "GET",
                success: function(response) {
                    if (response.success && response.subjects && response.subjects.length > 0) {
                        var html = '<div class="row g-3">';
                        response.subjects.forEach(function(subject) {
                            html += '<div class="col-12 col-sm-6 col-md-6 col-lg-4">';
                            html += '<div class="card border shadow-sm h-100">';
                            html += '<div class="card-body p-3">';
                            html += '<div class="d-flex align-items-center mb-2">';
                            html += '<i class="bi bi-book-fill text-primary-custom me-2" style="font-size: 1.5rem;"></i>';
                            html += '<div>';
                            html += '<h6 class="mb-0 fw-bold">' + subject.subject_name + '</h6>';
                            if (subject.subject_code) {
                                html += '<small class="text-muted">Code: ' + subject.subject_code + '</small>';
                            }
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="mt-2">';
                            html += '<div class="d-flex align-items-center">';
                            html += '<i class="bi bi-person-badge text-info me-2"></i>';
                            html += '<small class="text-muted">Teacher: <strong>' + subject.teacher_name + '</strong></small>';
                            html += '</div>';
                            html += '<div class="mt-1">';
                            html += '<span class="badge ' + (subject.status === 'Active' ? 'bg-success' : 'bg-secondary') + '">';
                            html += subject.status;
                            html += '</span>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        });
                        html += '</div>';
                        $('#subjectsContent').html(html);
                    } else {
                        $('#subjectsContent').html(
                            '<div class="text-center py-5">' +
                            '<i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>' +
                            '<p class="mt-3 mb-0 text-muted">No subjects found for this subclass.</p>' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    $('#subjectsContent').html('<div class="alert alert-danger">Failed to load subjects. Please try again.</div>');
                }
            });
        });

        // Handle Edit Class Button Click (from View Classes Modal)
        $(document).on('click', '.edit-class-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('edit_class')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit classes.'
                });
                return false;
            }

            var classID = $(this).data('class-id');

            console.log('Edit Class clicked:', classID);

            // Show loading state
            $('#editClassModal').modal('show');
            $('#editClassForm input, #editClassForm select, #editClassForm textarea').prop('disabled', true);

            // Fetch class data
            $.ajax({
                url: "{{ route('get_class', ':id') }}".replace(':id', classID),
                type: "GET",
                success: function(response) {
                    console.log('Class data received:', response);

                    if (response.success && response.class) {
                        var classItem = response.class;

                        // Populate form fields
                        $('#edit_classID').val(classItem.classID);
                        // Get the actual class name from database (may have spaces or underscores)
                        var currentClassName = classItem.class_name || '';
                        // Convert class_name from database format to select value format
                        var classValue = currentClassName.replace(/\s+/g, '_');
                        // If conversion doesn't match, try direct match
                        if (!$('#edit_class_name option[value="' + classValue + '"]').length) {
                            // Try direct match with original name
                            if ($('#edit_class_name option[value="' + currentClassName + '"]').length) {
                                classValue = currentClassName;
                            }
                        }
                        
                        // Disable all existing class names except the current one
                        $('#edit_class_name option').each(function() {
                            var optionValue = $(this).val();
                            var optionOriginalValue = $(this).attr('data-original-value') || optionValue;
                            var optionDbValue = optionValue.replace(/_/g, ' ');
                            
                            // Normalize current class name for comparison
                            var currentNormalized = currentClassName.replace(/\s+/g, '_');
                            
                            // Check if this class name exists in the existing classes array
                            var exists = false;
                            for (var i = 0; i < existingClassNames.length; i++) {
                                var existingName = existingClassNames[i];
                                var existingNormalized = existingName.replace(/\s+/g, '_');
                                
                                if (optionValue === existingName || 
                                    optionValue === existingNormalized ||
                                    optionOriginalValue === existingName ||
                                    optionDbValue === existingName ||
                                    optionValue.replace(/_/g, ' ') === existingName) {
                                    exists = true;
                                    break;
                                }
                            }
                            
                            // Disable if exists and is not the current class being edited
                            var isCurrentClass = (optionValue === currentClassName || 
                                                 optionValue === currentNormalized ||
                                                 optionOriginalValue === currentClassName ||
                                                 optionDbValue === currentClassName);
                            
                            if (exists && !isCurrentClass) {
                                $(this).prop('disabled', true)
                                    .css('background-color', '#e9ecef')
                                    .css('color', '#6c757d');
                                if ($(this).text().indexOf('(Already Exists)') === -1) {
                                    $(this).text($(this).text() + ' (Already Exists)');
                                }
                            } else {
                                $(this).prop('disabled', false)
                                    .css('background-color', '')
                                    .css('color', '');
                                var text = $(this).text();
                                if (text.indexOf('(Already Exists)') !== -1) {
                                    $(this).text(text.replace(' (Already Exists)', ''));
                                }
                            }
                        });
                        
                        $('#edit_class_name').val(classValue);
                        $('#edit_class_description').val(classItem.description || '');

                        // Set coordinator
                        if (classItem.teacherID) {
                            $('#edit_class_teacher_select').val(classItem.teacherID);
                        } else {
                            $('#edit_class_teacher_select').val('');
                        }

                        // Enable form fields
                        $('#editClassForm input, #editClassForm select, #editClassForm textarea').prop('disabled', false);
                        
                        // Store current class name for comparison
                        window.currentEditingClassName = currentClassName;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load class data.',
                            confirmButtonColor: '#940000'
                        });
                        $('#editClassModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching class:', xhr);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : 'Failed to load class data. Please try again.';

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#940000'
                    });
                    $('#editClassModal').modal('hide');
                }
            });
        });

        // Handle Edit Class Form Submission
        $(document).on('submit', '#editClassForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('edit_class')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit classes.'
                });
                return false;
            }
            
            // Validate class name is selected
            var className = $('#edit_class_name').val();
            if (!className) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a class name.'
                });
                return false;
            }
            
            // Check if selected class name is disabled (already exists and not current class)
            var selectedOption = $('#edit_class_name option:selected');
            if (selectedOption.prop('disabled')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'This class name already exists. Please select a different class name or keep the current one.'
                });
                return false;
            }

            console.log('Edit Class Form submitted');

            var formData = {
                classID: $('#edit_classID').val(),
                class_name: $('#edit_class_name').val(),
                description: $('#edit_class_description').val(),
                teacherID: $('#edit_class_teacher_select').val(),
                _token: $('input[name="_token"]', '#editClassForm').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_class') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    console.log('Update response:', response);

                    if (response && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000
                        }).then(function() {
                            $('#editClassModal').modal('hide');
                            $('#viewClassesModal').modal('hide');
                            // Reload the page to refresh data
                            location.reload();
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
                    console.error('Update error:', xhr);
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        // Validation errors
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

        // Handle Delete Class Button Click (from View Classes Modal)
        $(document).on('click', '.delete-class-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('delete_class')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to delete classes.'
                });
                return false;
            }

            var classID = $(this).data('class-id');
            var className = $(this).data('class-name');

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to delete class: " + className + "? This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('delete_class', ':id') }}".replace(':id', classID),
                        type: "DELETE",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.success || 'Class deleted successfully!',
                                confirmButtonColor: '#940000',
                                timer: 2000
                            }).then(function() {
                                $('#viewClassesModal').modal('hide');
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                                ? xhr.responseJSON.error
                                : 'Failed to delete class. Please try again.';

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

        // Handle Delete Subclass Button Click
        $(document).on('click', '.delete-subclass-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('delete_subclass')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to delete subclasses.'
                });
                return false;
            }

            var subclassID = $(this).data('subclass-id');
            var subclassName = $(this).data('subclass-name');

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to delete subclass: " + subclassName + "?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('delete_subclass', ':id') }}".replace(':id', subclassID),
                        type: "DELETE",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.success || 'Subclass deleted successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to delete subclass. Please try again.'
                            });
                        }
                    });
                }
            });
        });

        // Handle Activate Subclass Button Click
        $(document).on('click', '.activate-subclass-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('activate_class')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to activate subclasses.'
                });
                return false;
            }

            var subclassID = $(this).data('subclass-id');
            var subclassName = $(this).data('subclass-name');
            var currentStatus = $(this).data('current-status');
            var action = currentStatus === 'Active' ? 'deactivate' : 'activate';

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to ' + action + ' subclass "' + subclassName + '"?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + action + ' it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('activate_subclass', ':id') }}".replace(':id', subclassID),
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.success || 'Subclass status updated successfully!',
                                timer: 2000,
                                showConfirmButton: false,
                                confirmButtonColor: '#940000'
                            }).then(function() {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                                ? xhr.responseJSON.error
                                : 'Failed to update subclass status. Please try again.';
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

        // Handle Activate Class Button Click (from View Classes Modal)
        $(document).on('click', '.activate-class-btn', function(e) {
            e.preventDefault();

            if (!hasPermission('activate_class')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to activate classes.'
                });
                return false;
            }

            var classID = $(this).data('class-id');
            var className = $(this).data('class-name');
            var currentStatus = $(this).data('current-status');
            var action = currentStatus === 'Active' ? 'deactivate' : 'activate';

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to ' + action + ' class "' + className + '"?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + action + ' it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('activate_class', ':id') }}".replace(':id', classID),
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.success || 'Class status updated successfully!',
                                timer: 2000,
                                showConfirmButton: false,
                                confirmButtonColor: '#940000'
                            }).then(function() {
                                // Reload the classes view
                                $('#viewClassesBtn').click();
                            });
                        },
                        error: function(xhr) {
                            let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                                ? xhr.responseJSON.error
                                : 'Failed to update class status. Please try again.';
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

        // Reset forms when modals are closed
        $('#addClassModal').on('hidden.bs.modal', function() {
            $('#addClassForm')[0].reset();
        });

        $('#addSubclassModal').on('hidden.bs.modal', function() {
            $('#addSubclassForm')[0].reset();
        });

        $('#addCombieModal').on('hidden.bs.modal', function() {
            $('#addCombieForm')[0].reset();
            $('#customCombieNameGroup').hide();
            $('#custom_combie_name').prop('required', false);
            $('#combie_name_select').prop('required', true);
        });

        $('#editCombieModal').on('hidden.bs.modal', function() {
            $('#editCombieForm')[0].reset();
            $('#editCustomCombieNameGroup').hide();
            $('#edit_custom_combie_name').prop('required', false);
            $('#edit_combie_name_select').prop('required', true);
        });

        // Alternative Bootstrap 4 event handlers
        $('#addClassModal').on('hidden', function() {
            $('#addClassForm')[0].reset();
        });

        $('#addSubclassModal').on('hidden', function() {
            $('#addSubclassForm')[0].reset();
        });
        });
    })(jQuery);
</script>
