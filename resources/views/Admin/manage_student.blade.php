@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

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
    .btn-outline-primary-custom {
        color: #940000;
        border-color: #940000;
    }
    .btn-outline-primary-custom:hover {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    
    /* School Details Card Styles (like manage_school) */
    .school-details-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 25px;
    }
    
    .school-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .school-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }
    
    .school-logo-preview {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid #e9ecef;
    }
    
    .school-logo-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }
    
    .school-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .info-item i {
        color: #6c757d;
        margin-right: 10px;
        margin-top: 3px;
        font-size: 18px;
        width: 20px;
    }
    
    .info-item-content {
        flex: 1;
    }
    
    .info-item-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }
    
    .info-item-value {
        font-size: 0.95rem;
        color: #212529;
        font-weight: 500;
    }
    
    /* No Data Available Message Styles */
    .no-data-message {
        min-height: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .no-data-message i {
        opacity: 0.5;
    }
    
    /* Manual Modal Styles (when Bootstrap JS is not loaded) */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1050;
        display: none;
        width: 100%;
        height: 100%;
        overflow-x: hidden;
        overflow-y: auto;
        outline: 0;
    }
    
    .modal.show {
        display: block !important;
    }
    
    .modal-dialog {
        position: relative;
        width: auto;
        margin: 0.5rem;
        pointer-events: none;
    }
    
    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 0.3rem;
        outline: 0;
    }
    
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: #000;
    }
    
    .modal-backdrop.show {
        opacity: 0.5;
    }
    
    @media (min-width: 576px) {
        .modal-dialog {
            max-width: 500px;
            margin: 1.75rem auto;
        }
    }
    
    @media (min-width: 992px) {
        .modal-lg {
            max-width: 800px;
        }
        
        .modal-xl {
            max-width: 1140px;
        }
    }
    
    /* Select2 Custom Styles */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 9999;
    }
    
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    /* Ensure Select2 dropdown is visible in modals */
    .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 10000 !important;
    }
    
    /* Search Input Styles */
    .search-input-wrapper {
        position: relative;
    }
    
    .search-input-wrapper i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 1;
    }
    
    .search-input-wrapper #searchInput {
        padding-left: 40px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
        height: 45px;
        font-size: 0.95rem;
    }
    
    .search-input-wrapper #searchInput:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
        outline: none;
    }
    
    .search-input-wrapper #searchInput::placeholder {
        color: #adb5bd;
        font-style: italic;
    }
    
    /* Status Filter Select Styles */
    #statusFilter {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
        height: 45px;
        font-size: 0.95rem;
    }
    
    #statusFilter:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
        outline: none;
    }
    
    /* Student ID Card Styles */
    .id-card-container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 20px;
        min-height: 500px;
        width: 100%;
    }
    
    .student-id-card {
        width: 100%;
        max-width: 450px;
        min-height: 270px;
        max-height: 20000px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #940000;
        border-radius: 0;
        padding: 18px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: visible;
        transform: scale(1.2);
        transform-origin: center;
        display: flex;
        flex-direction: column;
    }
    
    .student-id-card {
        --card-color: #940000;
    }
    
    .student-id-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-color, #940000);
    }
    
    .id-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .id-card-school-logo {
        width: 60px;
        height: 60px;
        border-radius: 0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    
    .id-card-school-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .id-card-school-info {
        flex: 1;
        margin-left: 15px;
    }
    
    .id-card-school-name {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }
    
    .id-card-school-reg {
        font-size: 0.75rem;
        color: #6c757d;
        margin: 3px 0 0 0;
    }
    
    .id-card-body {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        flex: 0 1 auto;
        min-height: 0;
    }
    
    .id-card-photo-section {
        flex-shrink: 0;
    }
    
    .id-card-photo {
        width: 110px;
        height: 130px;
        border-radius: 0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid #940000;
    }
    
    .id-card-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .id-card-details {
        flex: 1;
    }
    
    .id-card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #212529;
        margin: 0 0 10px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .id-card-info-row {
        display: flex;
        margin-bottom: 6px;
        align-items: center;
    }
    
    .id-card-info-label {
        font-weight: 600;
        color: #495057;
        width: 100px;
        font-size: 0.75rem;
    }
    
    .id-card-info-value {
        color: #212529;
        font-size: 0.8rem;
        flex: 1;
    }
    
    .id-card-footer {
        text-align: center;
        margin-top: 15px;
        padding-top: 12px;
        border-top: 1px solid #e9ecef;
        flex-shrink: 0;
        width: 100%;
        display: block;
    }
    
    .id-card-footer-text {
        font-size: 0.7rem;
        color: #6c757d;
        font-style: italic;
        margin: 0;
        display: block;
    }
    
    .id-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e9ecef;
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        
        .student-id-card,
        .student-id-card *,
        .id-card-container {
            visibility: visible !important;
        }
        
        .id-card-container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 420px;
            height: 270px;
        }
        
        .id-card-container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 450px;
            max-width: 100%;
        }
        
        .student-id-card {
            position: relative;
            width: 100%;
            max-width: 450px;
            min-height: 270px;
            max-height: 20000px;
            box-shadow: none;
            border: 2px solid #940000;
            transform: scale(1);
            margin: 0;
            page-break-after: always;
        }
        
        .modal-header,
        .modal-footer,
        #printIdCardBtn,
        #downloadIdCardBtn,
        .id-card-color-picker,
        .modal-backdrop,
        body > *:not(.id-card-container):not(.student-id-card) {
            display: none !important;
            visibility: hidden !important;
        }
        
        .modal,
        .modal-dialog,
        .modal-content,
        .modal-body {
            position: static !important;
            display: block !important;
            visibility: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            background: white !important;
        }
        
        @page {
            size: A4 landscape;
            margin: 0;
        }
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<div class="container-fluid mt-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary-custom">
                    <i class="bi bi-people-fill"></i> Manage Students
                </h4>
                <div class="d-flex gap-2">
                    @php
                        $canCreate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('student_create');
                        $canUpdate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('student_update');
                        $canDelete = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('student_delete');
                    @endphp
                    @if($canCreate)
                    <button class="btn btn-outline-primary-custom fw-bold" id="addStudentBtn" type="button" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-person-plus"></i> Register New Student
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">Search by Admission Number or Name</label>
                    <div class="search-input-wrapper">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by admission number or student name...">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">Filter by Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">-- All Statuses --</option>
                        <option value="Active">Active Students</option>
                        <option value="Applied">Applied Students</option>
                        <option value="Graduated">Graduated Students</option>
                        <option value="Inactive">Inactive Students</option>
                        <option value="Transferred">Transferred Students</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

            <!-- Tabs Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="studentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="true" data-status="Active">
                        <i class="bi bi-check-circle"></i> Active Students
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="applied-tab" data-bs-toggle="tab" data-bs-target="#applied" type="button" role="tab" aria-controls="applied" aria-selected="false" data-status="Applied">
                        <i class="bi bi-file-earmark-person"></i> Applied Students
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="graduated-tab" data-bs-toggle="tab" data-bs-target="#graduated" type="button" role="tab" aria-controls="graduated" aria-selected="false" data-status="Graduated">
                        <i class="bi bi-mortarboard"></i> Graduated Students
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inactive-tab" data-bs-toggle="tab" data-bs-target="#inactive" type="button" role="tab" aria-controls="inactive" aria-selected="false" data-status="Inactive">
                        <i class="bi bi-x-circle"></i> Inactive Students
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="transferred-tab" data-bs-toggle="tab" data-bs-target="#transferred" type="button" role="tab" aria-controls="transferred" aria-selected="false" data-status="Transferred">
                        <i class="bi bi-arrow-right-circle"></i> Transferred Students
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="studentTabsContent">
                <!-- Active Students Tab -->
                <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                    <div class="table-responsive">
                        <table id="activeStudentsTable" class="table table-hover table-striped align-middle mb-0" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Admission Number</th>
                                    <th>Full Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Parent</th>
                                    <th>Fingerprint ID</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Applied Students Tab -->
                <div class="tab-pane fade" id="applied" role="tabpanel" aria-labelledby="applied-tab">
                    <div class="table-responsive">
                        <table id="appliedStudentsTable" class="table table-hover table-striped align-middle mb-0" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Admission Number</th>
                                    <th>Full Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Parent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Graduated Students Tab -->
                <div class="tab-pane fade" id="graduated" role="tabpanel" aria-labelledby="graduated-tab">
                    <div class="table-responsive">
                        <table id="graduatedStudentsTable" class="table table-hover table-striped align-middle mb-0" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Admission Number</th>
                                    <th>Full Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Parent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Inactive Students Tab -->
                <div class="tab-pane fade" id="inactive" role="tabpanel" aria-labelledby="inactive-tab">
                    <div class="table-responsive">
                        <table id="inactiveStudentsTable" class="table table-hover table-striped align-middle mb-0" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Admission Number</th>
                                    <th>Full Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Parent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Transferred Students Tab -->
                <div class="tab-pane fade" id="transferred" role="tabpanel" aria-labelledby="transferred-tab">
                    <div class="table-responsive">
                        <table id="transferredStudentsTable" class="table table-hover table-striped align-middle mb-0" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Admission Number</th>
                                    <th>Full Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Parent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addStudentModalLabel">
                    <i class="bi bi-person-plus"></i> Register New Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStudentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="admission_date" class="form-label">Admission Date</label>
                            <input type="date" class="form-control" id="admission_date" name="admission_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subclassID" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="subclassID" name="subclassID" required>
                                <option value="">Choose a class...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="parentID" class="form-label">Parent</label>
                            <select class="form-select" id="parentID" name="parentID">
                                <option value="">Choose a parent...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admission_number" class="form-label">Admission Number</label>
                            <input type="text" class="form-control" id="admission_number" name="admission_number" placeholder="Leave empty to auto-generate">
                            <small class="text-muted">If left empty, admission number will be auto-generated</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <small class="text-muted">Max size: 2MB (jpg, jpeg, png)</small>
                    </div>
                    
                    <!-- Health Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-heart-pulse"></i> Health Information</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_disabled" name="is_disabled" value="1">
                                <label class="form-check-label" for="is_disabled">
                                    Disabled
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_epilepsy" name="has_epilepsy" value="1">
                                <label class="form-check-label" for="has_epilepsy">
                                    Epilepsy/Seizure Disorder
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_allergies" name="has_allergies" value="1">
                                <label class="form-check-label" for="has_allergies">
                                    Allergies
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="allergiesDetailsContainer" style="display: none;">
                        <label for="allergies_details" class="form-label">Allergies Details</label>
                        <textarea class="form-control" id="allergies_details" name="allergies_details" rows="2" placeholder="Please specify the allergies"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Register Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Student Details Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewStudentModalLabel">
                    <i class="bi bi-person-badge"></i> Student Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editStudentModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStudentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="editStudentID" name="studentID">
                    
                    <!-- Photo Preview -->
                    <div class="mb-3 text-center" id="editPhotoPreview"></div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="edit_middle_name" name="middle_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_admission_date" class="form-label">Admission Date</label>
                            <input type="date" class="form-control" id="edit_admission_date" name="admission_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_subclassID" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_subclassID" name="subclassID" required>
                                <option value="">Choose a class...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_parentID" class="form-label">Parent</label>
                            <select class="form-select" id="edit_parentID" name="parentID">
                                <option value="">Choose a parent...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_admission_number" class="form-label">Admission Number</label>
                            <input type="text" class="form-control" id="edit_admission_number" name="admission_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Graduated">Graduated</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Applied">Applied</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="edit_photo" name="photo" accept="image/*">
                        <small class="text-muted">Max size: 2MB (jpg, jpeg, png). Leave empty to keep current photo.</small>
                    </div>
                    
                    <!-- Health Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-heart-pulse"></i> Health Information</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_disabled" name="is_disabled" value="1">
                                <label class="form-check-label" for="edit_is_disabled">
                                    Disabled
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_epilepsy" name="has_epilepsy" value="1">
                                <label class="form-check-label" for="edit_has_epilepsy">
                                    Epilepsy/Seizure Disorder
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_allergies" name="has_allergies" value="1">
                                <label class="form-check-label" for="edit_has_allergies">
                                    Allergies
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="editAllergiesDetailsContainer" style="display: none;">
                        <label for="edit_allergies_details" class="form-label">Allergies Details</label>
                        <textarea class="form-control" id="edit_allergies_details" name="allergies_details" rows="2" placeholder="Please specify the allergies"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Student ID Card Modal -->
<div class="modal fade" id="studentIdCardModal" tabindex="-1" aria-labelledby="studentIdCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="studentIdCardModalLabel">
                    <i class="bi bi-card-text"></i> Student Identity Card
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="studentIdCardContent" class="id-card-container">
                    <!-- ID Card will be loaded here via AJAX -->
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-success" id="printIdCardBtn">
                        <i class="bi bi-printer"></i> Print ID Card
                    </button>
                    <button type="button" class="btn btn-primary" id="downloadIdCardBtn">
                        <i class="bi bi-download"></i> Download ID Card
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- jQuery - Load first -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Bootstrap 5 JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- JsBarcode Library for ID Card --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

{{-- html2canvas Library for ID Card Download --}}
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('includes.footer')

<script>
    // Permissions from PHP (for JavaScript use)
    const canCreate = {{ ($canCreate ?? false) ? 'true' : 'false' }};
    const canUpdate = {{ ($canUpdate ?? false) ? 'true' : 'false' }};
    const canDelete = {{ ($canDelete ?? false) ? 'true' : 'false' }};
    
    $(document).ready(function() {
        let activeTable, appliedTable, graduatedTable, inactiveTable, transferredTable;
        let currentStatus = 'Active';
        
        // Show/hide allergies details based on checkbox
        $('#has_allergies').on('change', function() {
            if ($(this).is(':checked')) {
                $('#allergiesDetailsContainer').slideDown();
            } else {
                $('#allergiesDetailsContainer').slideUp();
                $('#allergies_details').val('');
            }
        });
        
        // Show/hide allergies details for edit form
        $('#edit_has_allergies').on('change', function() {
            if ($(this).is(':checked')) {
                $('#editAllergiesDetailsContainer').slideDown();
            } else {
                $('#editAllergiesDetailsContainer').slideUp();
                $('#edit_allergies_details').val('');
            }
        });

        // Load subclasses and parents for registration form
        function loadFormData() {
            // Load subclasses
            $.ajax({
                url: '{{ route("get_subclasses_for_school") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#subclassID');
                        
                        // Destroy existing Select2 if it exists
                        if (subclassSelect.hasClass('select2-hidden-accessible')) {
                            subclassSelect.select2('destroy');
                        }
                        
                        subclassSelect.html('<option value="">Choose a class...</option>');
                        response.subclasses.forEach(function(subclass) {
                            // Display display_name (class_name + subclass_name) e.g., "Form Four A"
                            const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                            subclassSelect.append('<option value="' + subclass.subclassID + '">' + displayName + '</option>');
                        });
                        
                        // Initialize Select2 for class select with search
                        subclassSelect.select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Search and select a class...',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#addStudentModal')
                        });
                    }
                }
            });

            // Load parents
            $.ajax({
                url: '{{ route("get_parents") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let parentSelect = $('#parentID');
                        parentSelect.html('<option value="">Choose a parent...</option>');
                        response.parents.forEach(function(parent) {
                            // Build full name from first_name, middle_name, last_name
                            let fullName = (parent.first_name || '') + ' ' + (parent.middle_name || '') + ' ' + (parent.last_name || '');
                            fullName = fullName.trim().replace(/\s+/g, ' '); // Clean up extra spaces
                            let displayText = fullName + (parent.phone ? ' (' + parent.phone + ')' : '');
                            parentSelect.append('<option value="' + parent.parentID + '">' + displayText + '</option>');
                        });
                        
                        // Initialize Select2 for parent select with search
                        if (parentSelect.length) {
                            // Destroy existing Select2 if it exists
                            if (parentSelect.hasClass('select2-hidden-accessible')) {
                                parentSelect.select2('destroy');
                            }
                            
                            parentSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Search and select a parent...',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#addStudentModal')
                            });
                        }
                    }
                }
            });
        }

        // Load students for a specific status
        function loadStudents(status, tableId) {
            console.log('Loading students with status:', status, 'for table:', tableId);
            
            // Show loading state
            let tableContainer = $('#' + tableId).closest('.table-responsive');
            let existingNoData = tableContainer.find('.no-data-message');
            if (existingNoData.length) {
                existingNoData.remove();
            }
            
            $.ajax({
                url: '{{ route("get_students_list") }}',
                type: 'GET',
                data: {
                    status: status,
                    search: $('#searchInput').val()
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response received:', response);
                    
                    if (response.success) {
                        let table = $('#' + tableId).DataTable();
                        if (!table) {
                            console.error('Table not found:', tableId);
                            return;
                        }
                        
                        table.clear();
                        
                        if (response.students && response.students.length > 0) {
                            console.log('Adding', response.students.length, 'students to table');
                            response.students.forEach(function(student) {
                                let imageHtml = '<img src="' + student.photo + '" alt="' + student.full_name + '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">';
                                
                                // Fingerprint ID display
                                let fingerprintIdHtml = '';
                                if (student.fingerprint_id) {
                                    fingerprintIdHtml = '<span class="badge bg-success"><i class="bi bi-fingerprint"></i> ' + student.fingerprint_id + '</span>';
                                } else {
                                    fingerprintIdHtml = '<span class="badge bg-secondary"><i class="bi bi-dash"></i> No ID</span>';
                                }

                                // Actions buttons - only show for Active students
                                let actionsHtml = '';
                                if (status === 'Active') {
                                    actionsHtml = '<div class="btn-group" role="group" style="gap: 5px;">' +
                                        '<button class="btn btn-sm btn-info view-student-btn" data-student-id="' + student.studentID + '" title="View More Details" style="padding: 5px 10px;">' +
                                        '<i class="bi bi-eye"></i>' +
                                        '</button>';
                                    
                                    // Edit button - only if user has update permission
                                    if (canUpdate) {
                                        actionsHtml += '<button class="btn btn-sm btn-warning edit-student-btn" data-student-id="' + student.studentID + '" title="Edit Student" style="padding: 5px 10px;">' +
                                            '<i class="bi bi-pencil-square"></i>' +
                                            '</button>';
                                    }
                                    
                                    // Only show fingerprint button if student doesn't have fingerprint_id
                                    if (!student.fingerprint_id) {
                                        actionsHtml += '<a href="#" class="btn btn-sm btn-success text-white send-student-to-fingerprint-btn" data-student-id="' + student.studentID + '" data-student-name="' + (student.first_name || student.full_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '" title="Send to Fingerprint Device" style="padding: 5px 10px;">' +
                                            '<i class="bi bi-fingerprint"></i>' +
                                            '</a>';
                                    } else if (!student.sent_to_device) {
                                        // Show "Register to Device" button if fingerprint_id exists but not sent to device
                                        actionsHtml += '<button class="btn btn-sm btn-success text-white register-student-to-device-btn" data-student-id="' + student.studentID + '" data-student-name="' + (student.first_name || student.full_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '" title="Register to Device" style="padding: 5px 10px;">' +
                                            '<i class="bi bi-device-hdd"></i>' +
                                            '</button>';
                                    }
                                    
                                    actionsHtml += '<button class="btn btn-sm btn-primary generate-id-btn" data-student-id="' + student.studentID + '" title="Generate Student ID Card" style="padding: 5px 10px;">' +
                                        '<i class="bi bi-card-text"></i>' +
                                        '</button>';
                                    
                                    // Delete button - only if user has delete permission
                                    if (canDelete) {
                                        actionsHtml += '<button class="btn btn-sm btn-danger delete-student-btn" data-student-id="' + student.studentID + '" data-student-name="' + student.full_name + '" title="Delete Student" style="padding: 5px 10px;">' +
                                            '<i class="bi bi-trash"></i>' +
                                            '</button>';
                                    }
                                    
                                    actionsHtml += '</div>';
                                } else {
                                    // For other statuses, only show view button
                                    actionsHtml = '<div class="btn-group" role="group">' +
                                        '<button class="btn btn-sm btn-info view-student-btn" data-student-id="' + student.studentID + '" title="View More Details" style="padding: 5px 10px;">' +
                                        '<i class="bi bi-eye"></i>' +
                                        '</button>' +
                                        '</div>';
                                }

                                // Add red alarm icon if student has health conditions
                                let healthAlarmIcon = '';
                                if ((student.is_disabled && student.is_disabled == 1) || 
                                    (student.has_epilepsy && student.has_epilepsy == 1) || 
                                    (student.has_allergies && student.has_allergies == 1)) {
                                    healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                                }
                                
                                table.row.add([
                                    imageHtml,
                                    student.admission_number,
                                    student.full_name + healthAlarmIcon,
                                    student.class,
                                    student.gender,
                                    student.parent_name,
                                    fingerprintIdHtml,
                                    actionsHtml
                                ]);
                            });
                            
                            // Remove any existing no-data message
                            tableContainer.find('.no-data-message').remove();
                        } else {
                            console.log('No students found for status:', status);
                            
                            // Show "No data available" message
                            let statusText = status === 'Applied' ? 'Applied Students' : 
                                           status === 'Graduated' ? 'Graduated Students' :
                                           status === 'Transferred' ? 'Transferred Students' :
                                           status === 'Inactive' ? 'Inactive Students' :
                                           'Students';
                            
                            let noDataHtml = '<div class="no-data-message text-center py-5">' +
                                '<i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>' +
                                '<h5 class="mt-3 text-muted">No Data Available</h5>' +
                                '<p class="text-muted">No ' + statusText.toLowerCase() + ' found' + 
                                (status === 'Graduated' ? ' where status = Graduated' : '') + 
                                (status === 'Inactive' ? ' where status = Inactive' : '') +
                                (status === 'Transferred' ? ' where status = Transferred' : '') +
                                (status === 'Applied' ? ' where status = Applied' : '') + 
                                '.</p>' +
                                '</div>';
                            
                            tableContainer.find('.no-data-message').remove();
                            tableContainer.append(noDataHtml);
                        }
                        
                        table.draw();
                    } else {
                        let table = $('#' + tableId).DataTable();
                        if (table) {
                            table.clear();
                            table.draw();
                        }
                        
                        // Show no data message
                        let tableContainer = $('#' + tableId).closest('.table-responsive');
                        let noDataHtml = '<div class="no-data-message text-center py-5">' +
                            '<i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>' +
                            '<h5 class="mt-3 text-muted">No Data Available</h5>' +
                            '<p class="text-muted">Failed to load students.</p>' +
                            '</div>';
                        tableContainer.find('.no-data-message').remove();
                        tableContainer.append(noDataHtml);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading students:', error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('XHR:', xhr);
                    
                    let errorMessage = 'Failed to load students';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    // Clear table on error
                    let table = $('#' + tableId).DataTable();
                    let tableContainer = $('#' + tableId).closest('.table-responsive');
                    
                    if (table) {
                        table.clear();
                        table.draw();
                    }
                    
                    // Show no data message
                    let noDataHtml = '<div class="no-data-message text-center py-5">' +
                        '<i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #dc3545;"></i>' +
                        '<h5 class="mt-3 text-danger">Error Loading Data</h5>' +
                        '<p class="text-muted">' + errorMessage + '</p>' +
                        '</div>';
                    tableContainer.find('.no-data-message').remove();
                    tableContainer.append(noDataHtml);
                }
            });
        }

        // Initialize DataTables
        function initializeTables() {
            activeTable = $('#activeStudentsTable').DataTable({
                "order": [[2, "asc"]],
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "autoWidth": false,
                "responsive": false,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ records per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ records",
                    "infoEmpty": "No records available",
                    "infoFiltered": "(filtered from _MAX_ total records)",
                    "zeroRecords": "No matching records found"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });

            appliedTable = $('#appliedStudentsTable').DataTable({
                "order": [[2, "asc"]],
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "autoWidth": false,
                "responsive": false,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ records per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ records",
                    "infoEmpty": "No records available",
                    "infoFiltered": "(filtered from _MAX_ total records)",
                    "zeroRecords": "No matching records found"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });

            graduatedTable = $('#graduatedStudentsTable').DataTable({
                "order": [[2, "asc"]],
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "autoWidth": false,
                "responsive": false,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ records per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ records",
                    "infoEmpty": "No records available",
                    "infoFiltered": "(filtered from _MAX_ total records)",
                    "zeroRecords": "No matching records found"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });

            inactiveTable = $('#inactiveStudentsTable').DataTable({
                "order": [[2, "asc"]],
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "autoWidth": false,
                "responsive": false,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ records per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ records",
                    "infoEmpty": "No records available",
                    "infoFiltered": "(filtered from _MAX_ total records)",
                    "zeroRecords": "No matching records found"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });

            transferredTable = $('#transferredStudentsTable').DataTable({
                "order": [[2, "asc"]],
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "autoWidth": false,
                "responsive": false,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ records per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ records",
                    "infoEmpty": "No records available",
                    "infoFiltered": "(filtered from _MAX_ total records)",
                    "zeroRecords": "No matching records found"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });
        }

        // Manual tab switching function (works without Bootstrap JS)
        function switchTab(tabButtonId, tabPaneId, status, tableId) {
            // Remove active class from all tabs
            $('#studentTabs .nav-link').removeClass('active').attr('aria-selected', 'false');
            $('#studentTabsContent .tab-pane').removeClass('show active');
            
            // Add active class to clicked tab
            $('#' + tabButtonId).addClass('active').attr('aria-selected', 'true');
            $('#' + tabPaneId).addClass('show active');
            
            // Load data
            console.log('Switching to tab:', tabPaneId, 'Status:', status);
            loadStudents(status, tableId);
        }
        
        // Click handlers for each tab - manual switching without Bootstrap JS
        $('#active-tab').off('click').on('click', function(e) {
            e.preventDefault();
            switchTab('active-tab', 'active', 'Active', 'activeStudentsTable');
        });

        $('#applied-tab').off('click').on('click', function(e) {
            e.preventDefault();
            switchTab('applied-tab', 'applied', 'Applied', 'appliedStudentsTable');
        });

        $('#graduated-tab').off('click').on('click', function(e) {
            e.preventDefault();
            switchTab('graduated-tab', 'graduated', 'Graduated', 'graduatedStudentsTable');
        });

        $('#inactive-tab').off('click').on('click', function(e) {
            e.preventDefault();
            switchTab('inactive-tab', 'inactive', 'Inactive', 'inactiveStudentsTable');
        });

        $('#transferred-tab').off('click').on('click', function(e) {
            e.preventDefault();
            switchTab('transferred-tab', 'transferred', 'Transferred', 'transferredStudentsTable');
        });

        // Search functionality - by admission number or student name
        let searchTimeout;
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            let searchValue = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                let activeTabButton = $('#studentTabs button.nav-link.active');
                let status = activeTabButton.data('status') || 'Active';
                let activeTab = activeTabButton.data('bs-target').replace('#', '');
                
                console.log('Search triggered. Status:', status, 'Tab:', activeTab, 'Search:', searchValue);
                
                let tableMap = {
                    'active': 'activeStudentsTable',
                    'applied': 'appliedStudentsTable',
                    'graduated': 'graduatedStudentsTable',
                    'inactive': 'inactiveStudentsTable',
                    'transferred': 'transferredStudentsTable'
                };
                
                if (tableMap[activeTab]) {
                    loadStudents(status, tableMap[activeTab]);
                }
            }, 500); // Debounce search by 500ms
        });

        // Status filter - AJAX search (works with Select2)
        $('#statusFilter').on('change', function() {
            let status = $(this).val();
            
            if (!status) {
                // If "All Statuses" is selected, show Active tab
                status = 'Active';
            }
            
            // Switch to the appropriate tab
            let tabMap = {
                'Active': 'active-tab',
                'Applied': 'applied-tab',
                'Graduated': 'graduated-tab',
                'Inactive': 'inactive-tab',
                'Transferred': 'transferred-tab'
            };
            
            if (tabMap[status]) {
                // Manually switch tab
                $('#studentTabs button.nav-link').removeClass('active');
                $('.tab-pane').removeClass('active show');
                
                $('#' + tabMap[status]).addClass('active');
                $('#' + tabMap[status].replace('-tab', '')).addClass('active show');
                
                // Load students for the selected status
                let tableMap = {
                    'Active': 'activeStudentsTable',
                    'Applied': 'appliedStudentsTable',
                    'Graduated': 'graduatedStudentsTable',
                    'Inactive': 'inactiveStudentsTable',
                    'Transferred': 'transferredStudentsTable'
                };
                
                if (tableMap[status]) {
                    loadStudents(status, tableMap[status]);
                }
            }
        });
        
        // Also handle Select2 change event
        $('#statusFilter').on('select2:select', function() {
            $(this).trigger('change');
        });

        // View Student Details
        $(document).on('click', '.view-student-btn', function() {
            let studentID = $(this).data('student-id');
            
            $.ajax({
                url: '{{ route("get_student_details", ":id") }}'.replace(':id', studentID),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let student = response.student;
                        let html = '<div class="school-details-card">';
                        html += '<div class="school-header">';
                        html += '<div class="d-flex align-items-center">';
                        html += '<div class="school-logo-preview me-3">';
                        html += '<img src="' + student.photo + '" alt="' + student.full_name + '">';
                        html += '</div>';
                        html += '<div>';
                        html += '<h3 class="school-title">' + student.full_name + '</h3>';
                        html += '<small class="text-muted">Admission: ' + student.admission_number + '</small>';
                        html += '</div>';
                        html += '</div>';
                        html += '<span class="badge bg-' + (student.status === 'Active' ? 'success' : 'secondary') + '">' + student.status + '</span>';
                        html += '</div>';
                        
                        html += '<div class="school-info-grid">';
                        html += '<div class="info-item"><i class="bi bi-gender-ambiguous"></i><div class="info-item-content"><div class="info-item-label">Gender</div><div class="info-item-value">' + student.gender + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-calendar-event"></i><div class="info-item-content"><div class="info-item-label">Date of Birth</div><div class="info-item-value">' + student.date_of_birth + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-calendar-check"></i><div class="info-item-content"><div class="info-item-label">Admission Date</div><div class="info-item-value">' + student.admission_date + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-book"></i><div class="info-item-content"><div class="info-item-label">Class</div><div class="info-item-value">' + student.class + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-geo-alt"></i><div class="info-item-content"><div class="info-item-label">Address</div><div class="info-item-value">' + student.address + '</div></div></div>';
                        
                        if (student.parent) {
                            html += '<div class="info-item"><i class="bi bi-person-heart"></i><div class="info-item-content"><div class="info-item-label">Parent Name</div><div class="info-item-value">' + student.parent.full_name + '</div></div></div>';
                            html += '<div class="info-item"><i class="bi bi-telephone"></i><div class="info-item-content"><div class="info-item-label">Parent Phone</div><div class="info-item-value">' + student.parent.phone + '</div></div></div>';
                            html += '<div class="info-item"><i class="bi bi-envelope"></i><div class="info-item-content"><div class="info-item-label">Parent Email</div><div class="info-item-value">' + student.parent.email + '</div></div></div>';
                            html += '<div class="info-item"><i class="bi bi-briefcase"></i><div class="info-item-content"><div class="info-item-label">Parent Occupation</div><div class="info-item-value">' + student.parent.occupation + '</div></div></div>';
                        }
                        
                        html += '</div></div>';
                        
                        $('#studentDetailsContent').html(html);
                        showModal('viewStudentModal');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load student details'
                    });
                }
            });
        });

        // Edit Student Button Click
        $(document).on('click', '.edit-student-btn', function() {
            let studentID = $(this).data('student-id');
            
            // Load student data
            $.ajax({
                url: '{{ route("get_student", ":id") }}'.replace(':id', studentID),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.student) {
                        let student = response.student;
                        
                        // Set form values
                        $('#editStudentID').val(student.studentID);
                        $('#edit_first_name').val(student.first_name || '');
                        $('#edit_middle_name').val(student.middle_name || '');
                        $('#edit_last_name').val(student.last_name || '');
                        $('#edit_gender').val(student.gender || '');
                        $('#edit_date_of_birth').val(student.date_of_birth || '');
                        $('#edit_admission_date').val(student.admission_date || '');
                        $('#edit_admission_number').val(student.admission_number || '');
                        $('#edit_address').val(student.address || '');
                        $('#edit_status').val(student.status || 'Active');
                        $('#edit_parentID').val(student.parentID || '');
                        $('#edit_subclassID').val(student.subclassID || '');
                        
                        // Set health information fields
                        $('#edit_is_disabled').prop('checked', student.is_disabled == 1 || student.is_disabled === true);
                        $('#edit_has_epilepsy').prop('checked', student.has_epilepsy == 1 || student.has_epilepsy === true);
                        $('#edit_has_allergies').prop('checked', student.has_allergies == 1 || student.has_allergies === true);
                        $('#edit_allergies_details').val(student.allergies_details || '');
                        
                        // Show/hide allergies details container based on checkbox
                        if (student.has_allergies == 1 || student.has_allergies === true) {
                            $('#editAllergiesDetailsContainer').show();
                        } else {
                            $('#editAllergiesDetailsContainer').hide();
                        }
                        
                        // Show photo preview
                        let photoPreview = '';
                        if (student.photo) {
                            photoPreview = '<img src="' + student.photo + '" alt="Current Photo" class="img-fluid rounded" style="max-width: 150px; max-height: 150px; border: 2px solid #e9ecef;">';
                        }
                        $('#editPhotoPreview').html(photoPreview);
                        
                        // Load subclasses and parents
                        loadEditFormData();
                        
                        // Show modal
                        showModal('editStudentModal');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load student data'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load student data'
                    });
                }
            });
        });
        
        // Load form data for edit modal
        function loadEditFormData() {
            // Load subclasses
            $.ajax({
                url: '{{ route("get_subclasses_for_school") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#edit_subclassID');
                        let currentSubclassID = subclassSelect.val();
                        
                        // Destroy existing Select2 if it exists
                        if (subclassSelect.hasClass('select2-hidden-accessible')) {
                            subclassSelect.select2('destroy');
                        }
                        
                        subclassSelect.html('<option value="">Choose a class...</option>');
                        response.subclasses.forEach(function(subclass) {
                            // Display display_name (class_name + subclass_name) e.g., "Form Four A"
                            const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                            let selected = (subclass.subclassID == currentSubclassID) ? 'selected' : '';
                            subclassSelect.append('<option value="' + subclass.subclassID + '" ' + selected + '>' + displayName + '</option>');
                        });
                        
                        // Initialize Select2 for class select with search
                        subclassSelect.select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Search and select a class...',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#editStudentModal')
                        });
                        
                        // Set the selected value after initialization
                        if (currentSubclassID) {
                            subclassSelect.val(currentSubclassID).trigger('change');
                        }
                    }
                }
            });
            
            // Load parents
            $.ajax({
                url: '{{ route("get_parents") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let parentSelect = $('#edit_parentID');
                        let currentParentID = parentSelect.val();
                        
                        // Destroy existing Select2 if it exists
                        if (parentSelect.hasClass('select2-hidden-accessible')) {
                            parentSelect.select2('destroy');
                        }
                        
                        parentSelect.html('<option value="">Choose a parent...</option>');
                        response.parents.forEach(function(parent) {
                            // Build full name from first_name, middle_name, last_name
                            let fullName = (parent.first_name || '') + ' ' + (parent.middle_name || '') + ' ' + (parent.last_name || '');
                            fullName = fullName.trim().replace(/\s+/g, ' '); // Clean up extra spaces
                            let displayText = fullName + (parent.phone ? ' (' + parent.phone + ')' : '');
                            let selected = (parent.parentID == currentParentID) ? 'selected' : '';
                            parentSelect.append('<option value="' + parent.parentID + '" ' + selected + '>' + displayText + '</option>');
                        });
                        
                        // Initialize Select2 for parent select with search
                        parentSelect.select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Search and select a parent...',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#editStudentModal')
                        });
                        
                        // Set the selected value after initialization
                        if (currentParentID) {
                            parentSelect.val(currentParentID).trigger('change');
                        }
                    }
                }
            });
        }


        // Generate ID Card Button Click
        $(document).on('click', '.generate-id-btn', function() {
            let studentID = $(this).data('student-id');
            
            if (!studentID) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Student ID not found'
                });
                return;
            }
            
            // Show loading
            $('#studentIdCardContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Loading ID Card...</p></div>');
            showModal('studentIdCardModal');
            
            // Load student details and school details
            $.when(
                $.ajax({
                    url: '{{ route("get_student_details", ":id") }}'.replace(':id', studentID),
                    type: 'GET',
                    dataType: 'json'
                }),
                $.ajax({
                    url: '{{ route("get_school_details") }}',
                    type: 'GET',
                    dataType: 'json'
                })
            ).done(function(studentResponse, schoolResponse) {
                if (studentResponse[0].success && schoolResponse[0].success) {
                    let student = studentResponse[0].student;
                    let school = schoolResponse[0].school;
                    let cardColor = $('#idCardColorPicker').val() || '#940000';
                    
                    // Generate barcode value (using admission number)
                    let barcodeValue = student.admission_number || student.studentID.toString();
                    
                    // Build ID Card HTML with dynamic color
                    let idCardHtml = '<div class="student-id-card" data-card-color="' + cardColor + '" style="border-color: ' + cardColor + '; --card-color: ' + cardColor + ';">' +
                        '<div class="id-card-header" style="border-bottom-color: ' + cardColor + ';">' +
                        '<div class="id-card-school-logo">';
                    
                    if (school.school_logo) {
                        idCardHtml += '<img src="' + school.school_logo + '" alt="School Logo">';
                    } else {
                        idCardHtml += '<i class="bi bi-building" style="font-size: 30px; color: #6c757d;"></i>';
                    }
                    
                    idCardHtml += '</div>' +
                        '<div class="id-card-school-info">' +
                        '<h4 class="id-card-school-name" style="color: ' + cardColor + ';">' + school.school_name + '</h4>' +
                        '<p class="id-card-school-reg">Reg. No: ' + (school.registration_number || 'N/A') + '</p>' +
                        '</div>' +
                        '</div>' +
                        '<div class="id-card-body">' +
                        '<div class="id-card-photo-section">' +
                        '<div class="id-card-photo" style="border-color: ' + cardColor + ';">' +
                        '<img src="' + student.photo + '" alt="' + student.full_name + '">' +
                        '</div>' +
                        '</div>' +
                        '<div class="id-card-details">' +
                        '<h3 class="id-card-title">Student Identity Card</h3>' +
                        '<div class="id-card-info-row">' +
                        '<span class="id-card-info-label">Name:</span>' +
                        '<span class="id-card-info-value">' + student.full_name + '</span>' +
                        '</div>' +
                        '<div class="id-card-info-row">' +
                        '<span class="id-card-info-label">Admission No:</span>' +
                        '<span class="id-card-info-value">' + student.admission_number + '</span>' +
                        '</div>' +
                        '<div class="id-card-info-row">' +
                        '<span class="id-card-info-label">Class:</span>' +
                        '<span class="id-card-info-value">' + student.class + '</span>' +
                        '</div>' +
                        '<div class="id-card-info-row">' +
                        '<span class="id-card-info-label">Gender:</span>' +
                        '<span class="id-card-info-value">' + student.gender + '</span>' +
                        '</div>';
                    
                    // Extract only class name (not subclass name)
                    let className = 'N/A';
                    if (student.class && student.class !== 'N/A') {
                        // Split by space and take first part (class name)
                        let classParts = student.class.split(' ');
                        className = classParts[0] || student.class;
                    }
                    
                    idCardHtml += '</div>' +
                        '</div>' +
                        '<div class="id-card-footer" style="border-top-color: ' + cardColor + ';">' +
                        '<p class="id-card-footer-text">This card is the property of ' + school.school_name + '</p>' +
                        '</div>' +
                        '</div>';
                    
                    // Update class value in the HTML
                    idCardHtml = idCardHtml.replace(
                        '<span class="id-card-info-value">' + student.class + '</span>',
                        '<span class="id-card-info-value">' + className + '</span>'
                    );
                    
                    $('#studentIdCardContent').html(idCardHtml);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load student or school details'
                    });
                    hideModal('studentIdCardModal');
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load ID card data'
                });
                hideModal('studentIdCardModal');
            });
        });
        
        // Color Picker Change Handler
        $('#idCardColorPicker').on('change', function() {
            let newColor = $(this).val();
            let idCard = $('.student-id-card');
            if (idCard.length) {
                idCard.css({
                    'border-color': newColor
                });
                idCard.find('.id-card-header').css('border-bottom-color', newColor);
                idCard.find('.id-card-school-name').css('color', newColor);
                idCard.find('.id-card-photo').css('border-color', newColor);
                idCard.find('.id-card-footer').css('border-top-color', newColor);
                idCard.attr('data-card-color', newColor);
                
                // Update ::before pseudo-element background
                idCard[0].style.setProperty('--card-color', newColor);
            }
        });
        
        // Print ID Card
        $('#printIdCardBtn').on('click', function() {
            window.print();
        });
        
        // Download ID Card (as image)
        $('#downloadIdCardBtn').on('click', function() {
            // Use html2canvas to convert ID card to image
            if (typeof html2canvas === 'undefined') {
                // Load html2canvas library
                let script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
                script.onload = function() {
                    downloadIdCard();
                };
                document.head.appendChild(script);
            } else {
                downloadIdCard();
            }
        });
        
        function downloadIdCard() {
            let idCard = document.querySelector('.student-id-card');
            if (!idCard) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'ID card not found'
                });
                return;
            }
            
            html2canvas(idCard, {
                backgroundColor: '#ffffff',
                scale: 2,
                logging: false,
                useCORS: true,
                allowTaint: false,
                width: idCard.offsetWidth,
                height: idCard.offsetHeight
            }).then(function(canvas) {
                let link = document.createElement('a');
                link.download = 'student-id-card.png';
                link.href = canvas.toDataURL('image/png', 1.0);
                link.click();
            }).catch(function(error) {
                console.error('Error generating image:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to download ID card. Please try printing instead.'
                });
            });
        }
        
        // Register Student to Device Button Click (similar to sample project)
        $(document).on('click', '.register-student-to-device-btn', function() {
            let studentId = $(this).data('student-id');
            let studentName = $(this).data('student-name');
            let fingerprintId = $(this).data('fingerprint-id');
            
            // Show warning about firmware compatibility issue (similar to sample project)
            const warning = `⚠️ DIRECT REGISTRATION WARNING

Your device (UF200-S firmware 6.60) may have firmware compatibility issues with direct registration.

✅ RECOMMENDED METHOD:
1. Register student directly on device (User Management → Add User)
2. Then click "Sync Users from Device" button
3. Student will appear automatically!

Would you like to try direct registration anyway, or use the manual method?`;
            
            Swal.fire({
                title: 'Register Student to Device?',
                html: warning,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Try Direct Registration',
                cancelButtonText: 'Use Manual Method',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ask for device IP and port
                    Swal.fire({
                        title: 'Device Settings',
                        html: '<input id="swal-ip" class="swal2-input" placeholder="Device IP (e.g., 192.168.100.108)" value="192.168.100.108">' +
                              '<input id="swal-port" class="swal2-input" placeholder="Port (e.g., 4370)" value="4370">',
                        showCancelButton: true,
                        confirmButtonText: 'Register',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#28a745',
                        preConfirm: () => {
                            return {
                                ip: document.getElementById('swal-ip').value,
                                port: document.getElementById('swal-port').value
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            let ip = result.value.ip;
                            let port = result.value.port;
                            
                            // Show loading
                            Swal.fire({
                                title: 'Registering...',
                                html: 'Please wait while we register the student to the device.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Make AJAX call
                            $.ajax({
                                url: '{{ route("students.register-device", ":id") }}'.replace(':id', studentId),
                                type: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    'Accept': 'application/json'
                                },
                                data: {
                                    ip: ip,
                                    port: port
                                },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: response.message || 'Student registered to device successfully!',
                                            confirmButtonColor: '#28a745'
                                        }).then(() => {
                                            // Reload students table
                                            loadStudents('Active', 'activeStudentsTable');
                                        });
                                    } else {
                                        let errorMsg = '✗ ' + (response.message || 'Registration Failed');
                                        
                                        // Show troubleshooting guide if provided
                                        if (response.troubleshooting) {
                                            errorMsg += '\n\n' + response.troubleshooting;
                                        }
                                        
                                        // Show quick solution
                                        if (response.quick_solution) {
                                            errorMsg += '\n\n💡 QUICK SOLUTION:\n' + response.quick_solution;
                                        }
                                        
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Registration Failed',
                                            html: '<pre style="text-align: left; white-space: pre-wrap; font-size: 0.9em;">' + errorMsg + '</pre>',
                                            confirmButtonColor: '#dc3545',
                                            width: '600px'
                                        });
                                        
                                        // If user might be registered, offer to sync
                                        if (response.might_be_registered) {
                                            Swal.fire({
                                                title: 'Sync Users?',
                                                text: 'The device responded. Would you like to sync users from device to check if student was added?',
                                                icon: 'question',
                                                showCancelButton: true,
                                                confirmButtonText: 'Yes, Sync',
                                                cancelButtonText: 'No'
                                            }).then((syncResult) => {
                                                if (syncResult.isConfirmed) {
                                                    // Redirect to fingerprint device settings page or call sync function
                                                    window.location.href = '{{ route("fingerprint_device_settings") }}';
                                                }
                                            });
                                        }
                                    }
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Error: ' + (xhr.responseJSON?.message || 'Unknown error occurred');
                                    if (xhr.responseJSON?.troubleshooting) {
                                        errorMsg += '\n\n' + xhr.responseJSON.troubleshooting;
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: '<pre style="text-align: left; white-space: pre-wrap; font-size: 0.9em;">' + errorMsg + '</pre>',
                                        confirmButtonColor: '#dc3545',
                                        width: '600px'
                                    });
                                }
                            });
                        }
                    });
                } else {
                    // User chose manual method - show instructions
                    Swal.fire({
                        title: 'Manual Registration Instructions',
                        html: `<div style="text-align: left;">
                            <p><strong>1. On Device (192.168.100.108):</strong></p>
                            <ul>
                                <li>Press MENU → User Management → Add User</li>
                                <li>Enter Enroll ID: <strong>${fingerprintId}</strong></li>
                                <li>Enter Name: <strong>${studentName}</strong></li>
                                <li>Save</li>
                            </ul>
                            <p><strong>2. On This Page:</strong></p>
                            <ul>
                                <li>Go to "Fingerprint Device Settings" page</li>
                                <li>Click "Sync Users from Device" button</li>
                                <li>Student will appear automatically!</li>
                            </ul>
                            <p><strong>3. Enroll Fingerprint (optional):</strong></p>
                            <ul>
                                <li>On device: User Management → Enroll Fingerprint</li>
                                <li>Enter Enroll ID: <strong>${fingerprintId}</strong></li>
                                <li>Place finger 3 times</li>
                            </ul>
                        </div>`,
                        icon: 'info',
                        confirmButtonColor: '#17a2b8',
                        width: '600px'
                    });
                }
            });
        });

        // Delete Student Button Click
        $(document).on('click', '.delete-student-btn', function() {
            let studentID = $(this).data('student-id');
            let studentName = $(this).data('student-name');
            
            Swal.fire({
                title: 'Delete Student?',
                html: 'Are you sure you want to delete <strong>' + studentName + '</strong>?<br><br>This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("delete_student", ":id") }}'.replace(':id', studentID),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message || 'Student deleted successfully',
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    // Reload active students table
                                    loadStudents('Active', 'activeStudentsTable');
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to delete student'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to delete student';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        }
                    });
                }
            });
        });

        // Register Student Form
        $('#addStudentForm').on('submit', function(e) {
            e.preventDefault();
            
            // Client-side validation
            let first_name = $('#first_name').val().trim();
            let last_name = $('#last_name').val().trim();
            let gender = $('#gender').val();
            let subclassID = $('#subclassID').val();
            let admission_number = $('#admission_number').val().trim();
            
            // Clear previous error messages
            $('.text-danger.validation-error').remove();
            $('.form-control, .form-select').removeClass('is-invalid');
            
            let hasErrors = false;
            
            // Validate required fields
            if (!first_name) {
                $('#first_name').addClass('is-invalid').after('<div class="text-danger validation-error small">First name is required</div>');
                hasErrors = true;
            }
            
            if (!last_name) {
                $('#last_name').addClass('is-invalid').after('<div class="text-danger validation-error small">Last name is required</div>');
                hasErrors = true;
            }
            
            if (!gender) {
                $('#gender').addClass('is-invalid').after('<div class="text-danger validation-error small">Gender is required</div>');
                hasErrors = true;
            }
            
            if (!subclassID) {
                $('#subclassID').addClass('is-invalid').after('<div class="text-danger validation-error small">Class is required</div>');
                hasErrors = true;
            }
            
            if (hasErrors) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields',
                    confirmButtonColor: '#940000'
                });
                return;
            }
            
            let formData = new FormData(this);
            let submitBtn = $(this).find('button[type="submit"]');
            let originalBtnText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Registering...');
            
            $.ajax({
                url: '{{ route("save_student") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    // Show loading overlay
                    $('body').append('<div id="formLoadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 30px; border-radius: 10px; text-align: center;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Registering student...</p></div></div>');
                },
                success: function(response) {
                    $('#formLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    
                    if (response.success) {
                        // Close the registration modal first
                        if ($('#parentID').hasClass('select2-hidden-accessible')) {
                            $('#parentID').select2('destroy');
                        }
                        if ($('#subclassID').hasClass('select2-hidden-accessible')) {
                            $('#subclassID').select2('destroy');
                        }
                        hideModal('addStudentModal');
                        $('#addStudentForm')[0].reset();
                        $('.is-invalid').removeClass('is-invalid');
                        $('.validation-error').remove();
                        
                        // Show simple SweetAlert message with fingerprintID
                        var fingerprintId = response.fingerprint_id || '';
                        Swal.fire({
                            title: 'Student Registered Successfully!',
                            html: '<div class="text-center">' +
                                  '<p class="mb-3">Student registered successfully</p>' +
                                  '<p class="mb-0">Please continue register user in fingerprint device ID <strong style="font-size: 1.2rem; color: #940000;">' + fingerprintId + '</strong></p>' +
                                  '</div>',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#940000',
                            width: '500px'
                        }).then(() => {
                            loadStudents('Active', 'activeStudentsTable');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to register student',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    $('#formLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    
                    let errors = xhr.responseJSON?.errors || {};
                    let errorMessage = xhr.responseJSON?.message || 'Failed to register student';
                    
                    // Handle admission number duplicate error
                    if (xhr.responseJSON?.errors?.admission_number) {
                        $('#admission_number').addClass('is-invalid').after('<div class="text-danger validation-error small">' + xhr.responseJSON.errors.admission_number[0] + '</div>');
                    }
                    
                    if (Object.keys(errors).length > 0) {
                        let errorList = Object.entries(errors).map(([field, msg]) => {
                            if (Array.isArray(msg)) {
                                return msg[0];
                            }
                            return msg;
                        }).join('<br>');
                        errorMessage = errorList;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error!',
                        html: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Edit Student Form
        $('#editStudentForm').on('submit', function(e) {
            e.preventDefault();
            
            // Client-side validation
            let first_name = $('#edit_first_name').val().trim();
            let last_name = $('#edit_last_name').val().trim();
            let gender = $('#edit_gender').val();
            let subclassID = $('#edit_subclassID').val();
            let admission_number = $('#edit_admission_number').val().trim();
            
            // Clear previous error messages
            $('#editStudentModal .text-danger.validation-error').remove();
            $('#editStudentModal .form-control, #editStudentModal .form-select').removeClass('is-invalid');
            
            let hasErrors = false;
            
            // Validate required fields
            if (!first_name) {
                $('#edit_first_name').addClass('is-invalid').after('<div class="text-danger validation-error small">First name is required</div>');
                hasErrors = true;
            }
            
            if (!last_name) {
                $('#edit_last_name').addClass('is-invalid').after('<div class="text-danger validation-error small">Last name is required</div>');
                hasErrors = true;
            }
            
            if (!gender) {
                $('#edit_gender').addClass('is-invalid').after('<div class="text-danger validation-error small">Gender is required</div>');
                hasErrors = true;
            }
            
            if (!subclassID) {
                $('#edit_subclassID').addClass('is-invalid').after('<div class="text-danger validation-error small">Class is required</div>');
                hasErrors = true;
            }
            
            if (!admission_number) {
                $('#edit_admission_number').addClass('is-invalid').after('<div class="text-danger validation-error small">Admission number is required</div>');
                hasErrors = true;
            }
            
            if (hasErrors) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields',
                    confirmButtonColor: '#940000'
                });
                return;
            }
            
            let formData = new FormData(this);
            let submitBtn = $(this).find('button[type="submit"]');
            let originalBtnText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');
            
            $.ajax({
                url: '{{ route("update_student") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                beforeSend: function() {
                    // Show loading overlay
                    $('body').append('<div id="editFormLoadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 30px; border-radius: 10px; text-align: center;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Updating student...</p></div></div>');
                },
                success: function(response) {
                    $('#editFormLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Student updated successfully',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Destroy Select2 before hiding modal
                            if ($('#edit_parentID').hasClass('select2-hidden-accessible')) {
                                $('#edit_parentID').select2('destroy');
                            }
                            if ($('#edit_subclassID').hasClass('select2-hidden-accessible')) {
                                $('#edit_subclassID').select2('destroy');
                            }
                            hideModal('editStudentModal');
                            $('#editStudentForm')[0].reset();
                            $('.is-invalid').removeClass('is-invalid');
                            $('.validation-error').remove();
                            // Reload active students table
                            loadStudents('Active', 'activeStudentsTable');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to update student',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    $('#editFormLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    
                    let errors = xhr.responseJSON?.errors || {};
                    let errorMessage = xhr.responseJSON?.message || 'Failed to update student';
                    
                    // Handle admission number duplicate error
                    if (xhr.responseJSON?.errors?.admission_number) {
                        $('#edit_admission_number').addClass('is-invalid').after('<div class="text-danger validation-error small">' + xhr.responseJSON.errors.admission_number[0] + '</div>');
                    }
                    
                    if (Object.keys(errors).length > 0) {
                        let errorList = Object.entries(errors).map(([field, msg]) => {
                            if (Array.isArray(msg)) {
                                return msg[0];
                            }
                            return msg;
                        }).join('<br>');
                        errorMessage = errorList;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Transfer Student Form
        $('#transferStudentForm').on('submit', function(e) {
            e.preventDefault();
            
            let formData = {
                studentID: $('#transferStudentID').val(),
                new_subclassID: $('#newSubclassID').val()
            };
            
            if (!formData.new_subclassID) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please select a class to transfer to',
                    confirmButtonColor: '#940000'
                });
                return;
            }
            
            Swal.fire({
                title: 'Transfer Student?',
                html: 'Are you sure you want to transfer this student to the selected class?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, transfer!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("transfer_student") }}',
                        type: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message || 'Student transferred successfully',
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    // Destroy Select2 before hiding modal
                                    if ($('#newSubclassID').hasClass('select2-hidden-accessible')) {
                                        $('#newSubclassID').select2('destroy');
                                    }
                                    hideModal('transferStudentModal');
                                    $('#transferStudentForm')[0].reset();
                                    // Reload active students table
                                    loadStudents('Active', 'activeStudentsTable');
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to transfer student',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON?.message || 'Failed to transfer student';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        }
                    });
                }
            });
        });

        // Function to show modal manually
        function showModal(modalId) {
            $('#' + modalId).removeClass('fade').css({
                'display': 'block',
                'z-index': 1050
            }).addClass('show');
            $('body').append('<div class="modal-backdrop fade show" style="z-index: 1040;"></div>');
        }
        
        // Function to hide modal manually
        function hideModal(modalId) {
            $('#' + modalId).removeClass('show').css('display', 'none');
            $('.modal-backdrop').remove();
            
            // Destroy Select2 instances in the modal to prevent conflicts
            $('#' + modalId + ' select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
        }
        
        // Close modal handlers for all modals
        $(document).on('click', '.modal .btn-close, .modal .btn-secondary[data-bs-dismiss="modal"], .modal button[data-bs-dismiss="modal"]', function() {
            let modal = $(this).closest('.modal');
            if (modal.length) {
                hideModal(modal.attr('id'));
            }
        });
        
        // Close modal when clicking backdrop
        $(document).on('click', '.modal-backdrop', function() {
            $('.modal.show').each(function() {
                hideModal($(this).attr('id'));
            });
        });
        
        // Initialize Select2 for Status Filter
        $('#statusFilter').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select status...',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0 // Always show search box
        });
        
        // Initialize on page load
        loadFormData();
        initializeTables();
        
        // Load initial data for active tab
        setTimeout(function() {
            loadStudents('Active', 'activeStudentsTable');
        }, 500);
    });

    // Handle Send Student to Fingerprint Device Button Click
    $(document).on('click', '.send-student-to-fingerprint-btn', function(e) {
        e.preventDefault();
        var studentId = $(this).data('student-id');
        var studentName = $(this).data('student-name');
        var fingerprintId = $(this).data('fingerprint-id');
        var $btn = $(this);
        var originalHtml = $btn.html();

        // Check if student already has fingerprint_id
        if (fingerprintId && fingerprintId.trim() !== '') {
            Swal.fire({
                icon: 'info',
                title: 'Fingerprint ID Already Assigned',
                html: 'Student <strong>' + studentName + '</strong> already has a fingerprint ID: <strong>' + fingerprintId + '</strong>',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Send to Fingerprint Device?',
            html: 'Are you sure you want to send <strong>' + studentName + '</strong> to the fingerprint device?<br><br><small class="text-muted">This will generate a unique fingerprint ID and register the student to the biometric device.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-fingerprint"></i> Yes, Send',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');
                
                return $.ajax({
                    url: "{{ route('send_student_to_fingerprint') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        student_id: studentId
                    },
                    success: function(response) {
                        return response;
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to send student to fingerprint device.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.showValidationMessage(errorMsg);
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                var response = result.value;
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: 'Student <strong>' + studentName + '</strong> has been successfully sent to the fingerprint device.<br><br><small class="text-muted">Fingerprint ID: <strong>' + (response.fingerprint_id || 'N/A') + '</strong></small>',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page to show updated data
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Partial Success',
                        html: response.message || 'Student was processed but there may have been issues with the fingerprint device.',
                        confirmButtonText: 'OK'
                    });
                    $btn.prop('disabled', false).html(originalHtml);
                }
            } else {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
</script>

