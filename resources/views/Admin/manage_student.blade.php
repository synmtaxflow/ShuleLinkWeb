@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
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

    /* Academic Widget Styles */
    .academic-widget {
        transition: all 0.3s ease;
    }
    
    .academic-widget.disabled {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .academic-widget .card-header {
        user-select: none;
        cursor: pointer;
    }
    
    .academic-widget .card-header:hover {
        opacity: 0.9;
    }
    
    .academic-widget .widget-content {
        display: none;
    }
    
    .academic-widget[data-expanded="true"] .widget-content {
        display: block !important;
    }
    
    .widget-toggle-icon {
        transition: transform 0.3s ease;
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

    /* Custom Modal Width and Scroll Fix */
    @media (min-width: 1200px) {
        .modal-xl {
            max-width: 95% !important;
        }
    }
    .modal-body-scrollable {
        max-height: 80vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
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

    /* Student Widgets */
    .students-widget-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .students-widget-grid .student-widget {
        flex: 1 1 calc(33.333% - 20px);
        max-width: calc(33.333% - 20px);
    }

    @media (max-width: 1200px) {
        .students-widget-grid .student-widget {
            flex: 1 1 calc(50% - 20px);
            max-width: calc(50% - 20px);
        }
    }

    @media (max-width: 768px) {
        .students-widget-grid .student-widget {
            flex: 1 1 100%;
            max-width: 100%;
        }
    }

    .student-widget {
        width: 320px;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        font-family: "Century Gothic", Arial, sans-serif;
    }

    .student-widget-header {
        background: #940000;
        padding: 20px;
        text-align: center;
    }

    .student-widget-header img {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 4px solid #fff;
        object-fit: cover;
    }

    .student-widget-body {
        padding: 15px;
        text-align: center;
    }

    .student-widget-body h3 {
        color: #940000;
        margin-bottom: 5px;
        font-size: 18px;
        font-weight: 700;
    }

    .student-widget-body .form {
        font-weight: bold;
        color: #444;
        margin-bottom: 10px;
    }

    .student-widget-body .info p {
        margin: 6px 0;
        font-size: 14px;
    }

    .student-widget-actions {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 10px;
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

    .search-input-wrapper #studentSearchInput {
        padding-left: 40px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
        height: 45px;
        font-size: 0.95rem;
    }

    .search-input-wrapper #studentSearchInput:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
        outline: none;
    }

    .search-input-wrapper #studentSearchInput::placeholder {
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
                    <button class="btn btn-outline-primary-custom fw-bold" id="addStudentBtn" type="button" data-bs-toggle="modal" data-bs-target="#classSelectorModal">
                        <i class="bi bi-person-plus"></i> Register New Student
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <!-- Filters Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-2">Class</label>
                    <select class="form-select" id="classFilter">
                        <option value="">-- All Classes --</option>
                        <!-- Will be loaded via AJAX -->
                    </select>
                    </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-2">Subclass</label>
                    <select class="form-select" id="subclassFilter">
                        <option value="">-- All Subclasses --</option>
                        <!-- Will be loaded via AJAX -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">Gender</label>
                    <select class="form-select" id="genderFilter">
                        <option value="">-- All Genders --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">Health Condition</label>
                    <select class="form-select" id="healthFilter">
                        <option value="">-- All --</option>
                        <option value="good">Good Health</option>
                        <option value="bad">Bad Health</option>
                    </select>
            </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="Active">Active</option>
                        <option value="Applied">Applied</option>
                        <option value="Graduated">Graduated</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Transferred">Transferred</option>
                    </select>
        </div>
    </div>

            <!-- Statistics Display -->
            <div class="row g-3" id="statisticsSection">
                <div class="col-md-2">
                    <div class="text-center p-3 bg-primary text-white rounded">
                        <h4 class="mb-0" id="statTotalStudents">0</h4>
                        <small>Total Students</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3 bg-info text-white rounded">
                        <h4 class="mb-0" id="statMaleCount">0</h4>
                        <small>Male</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3 bg-danger text-white rounded">
                        <h4 class="mb-0" id="statFemaleCount">0</h4>
                        <small>Female</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3 bg-success text-white rounded">
                        <h4 class="mb-0" id="statGoodHealth">0</h4>
                        <small>Good Health</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3 bg-warning text-dark rounded">
                        <h4 class="mb-0" id="statBadHealth">0</h4>
                        <small>Bad Health</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2 h-100 align-items-end">
                        <button class="btn btn-outline-danger btn-sm w-100" id="exportPdfBtn" title="Export to PDF">
                            <i class="bi bi-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-outline-success btn-sm w-100" id="exportExcelBtn" title="Export to Excel">
                            <i class="bi bi-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
            </div>
                    </div>
                </div>

    <!-- Students Widgets Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Search Input (Moved here) -->
             <div class="mb-4">
                 <div class="search-input-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" id="studentSearchInput" placeholder="Search by Admission No, Name, Class...">
                </div>
            </div>
            <div id="studentsWidgetGrid" class="students-widget-grid">
                <div class="no-data-message">
                    <i class="bi bi-people fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Loading students...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addStudentModalLabel">
                    <i class="bi bi-person-plus"></i> Register New Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStudentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body modal-body-scrollable">
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

                    <!-- Additional Particulars Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-person-vcard"></i> Additional Particulars</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="birth_certificate_number" class="form-label">Birth Certificate No.</label>
                            <input type="text" class="form-control" id="birth_certificate_number" name="birth_certificate_number">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text" class="form-control" id="religion" name="religion">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" class="form-control" id="nationality" name="nationality" value="Tanzanian">
                        </div>
                    </div>

                    <!-- Sponsorship Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-handshake-fill"></i> Sponsorship Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_type" class="form-label">Payment Type</label>
                            <select class="form-select" id="payment_type" name="payment_type">
                                <option value="own">Own Payment</option>
                                <option value="sponsor">Sponsor Payment</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="sponsorSelectContainer" style="display: none;">
                            <label for="sponsor_id" class="form-label">Select Sponsor</label>
                            <select class="form-select" id="sponsor_id" name="sponsor_id">
                                <option value="">Choose a sponsor...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="sponsorshipPercentageContainer" style="display: none;">
                        <label for="sponsorship_percentage" class="form-label">Sponsorship Percentage (%)</label>
                        <input type="number" class="form-control" id="sponsorship_percentage" name="sponsorship_percentage" min="0" max="100" step="any" placeholder="e.g., 50.00">
                    </div>

                    <!-- Health Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-heart-pulse"></i> Health Information</h6>
                    <div class="mb-3">
                        <label for="general_health_condition" class="form-label">General Health Condition</label>
                        <textarea class="form-control" id="general_health_condition" name="general_health_condition" rows="2" placeholder="Healthy, Good, etc."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_disability" name="has_disability" value="1">
                                <label class="form-check-label" for="has_disability">Has Disability</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_chronic_illness" name="has_chronic_illness" value="1">
                                <label class="form-check-label" for="has_chronic_illness">Has Chronic Illness</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_disabled" name="is_disabled" value="1">
                                <label class="form-check-label" for="is_disabled">Disabled</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_epilepsy" name="has_epilepsy" value="1">
                                <label class="form-check-label" for="has_epilepsy">Epilepsy</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_allergies" name="has_allergies" value="1">
                                <label class="form-check-label" for="has_allergies">Allergies</label>
                            </div>
                        </div>
                    </div>
                    <div id="addHealthDetailsSection">
                        <div class="mb-3" id="disabilityDetailsContainer" style="display: none;">
                            <label for="disability_details" class="form-label">Disability Details</label>
                            <textarea class="form-control" id="disability_details" name="disability_details" rows="2"></textarea>
                        </div>
                        <div class="mb-3" id="chronicIllnessDetailsContainer" style="display: none;">
                            <label for="chronic_illness_details" class="form-label">Chronic Illness Details</label>
                            <textarea class="form-control" id="chronic_illness_details" name="chronic_illness_details" rows="2"></textarea>
                        </div>
                        <div class="mb-3" id="allergiesDetailsContainer" style="display: none;">
                            <label for="allergies_details" class="form-label">Allergies Details</label>
                            <textarea class="form-control" id="allergies_details" name="allergies_details" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="immunization_details" class="form-label">Immunization Details</label>
                        <textarea class="form-control" id="immunization_details" name="immunization_details" rows="2"></textarea>
                    </div>

                    <!-- Emergency Contact Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-telephone"></i> Emergency Contact</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="emergency_contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="emergency_contact_relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emergency_contact_relationship" name="emergency_contact_relationship" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="emergency_contact_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" required>
                        </div>
                    </div>

                    <!-- Official Use Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-person-badge"></i> Official Use & Declaration</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="declaration_date" class="form-label">Declaration Date</label>
                            <input type="date" class="form-control" id="declaration_date" name="declaration_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="registering_officer_name" class="form-label">Registering Officer</label>
                            <input type="text" class="form-control" id="registering_officer_name" name="registering_officer_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="registering_officer_title" class="form-label">Officer Title</label>
                            <input type="text" class="form-control" id="registering_officer_title" name="registering_officer_title">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="bi bi-check-circle"></i> Register Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Student Details Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 95%; width: 95%;">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header bg-primary-custom text-white" style="border-radius: 0;">
                <h5 class="modal-title" id="viewStudentModalLabel">
                    <i class="bi bi-person-badge"></i> Student Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="studentDetailsContent" style="min-height: 70vh;">
                <!-- Content will be loaded dynamically with tabs -->
            </div>
            <div class="modal-footer" style="border-radius: 0;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editStudentModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStudentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body modal-body-scrollable">
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
                    
                    <!-- Photo Section -->
                    <div class="mb-3">
                        <label for="edit_photo" class="form-label">Update Photo</label>
                        <input type="file" class="form-control" id="edit_photo" name="photo" accept="image/*">
                        <small class="text-muted">Max size: 2MB (jpg, jpeg, png). Leave empty to keep current photo.</small>
                    </div>

                    <!-- Additional Particulars Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-person-vcard"></i> Additional Particulars</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_birth_certificate_number" class="form-label">Birth Certificate Number</label>
                            <input type="text" class="form-control" id="edit_birth_certificate_number" name="birth_certificate_number">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_religion" class="form-label">Religion</label>
                            <input type="text" class="form-control" id="edit_religion" name="religion">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_nationality" class="form-label">Nationality</label>
                            <input type="text" class="form-control" id="edit_nationality" name="nationality">
                        </div>
                    </div>
                    
                    <!-- Sponsorship Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-handshake-fill"></i> Sponsorship Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_payment_type" class="form-label">Payment Type</label>
                            <select class="form-select" id="edit_payment_type" name="payment_type">
                                <option value="own">Own Payment</option>
                                <option value="sponsor">Sponsor Payment</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="editSponsorSelectContainer" style="display: none;">
                            <label for="edit_sponsor_id" class="form-label">Select Sponsor</label>
                            <select class="form-select" id="edit_sponsor_id" name="sponsor_id">
                                <option value="">Choose a sponsor...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="editSponsorshipPercentageContainer" style="display: none;">
                        <label for="edit_sponsorship_percentage" class="form-label">Sponsorship Percentage (%)</label>
                        <input type="number" class="form-control" id="edit_sponsorship_percentage" name="sponsorship_percentage" min="0" max="100" step="any" onfocus="this.select()" placeholder="e.g., 50.00">
                        <small class="text-muted">Enter the percentage of fees covered by the sponsor (0-100)</small>
                    </div>

                    <!-- Health Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-heart-pulse"></i> Health Information</h6>
                    <div class="mb-3">
                        <label for="edit_general_health_condition" class="form-label">General Health Condition</label>
                        <textarea class="form-control" id="edit_general_health_condition" name="general_health_condition" rows="2" placeholder="Describe general health condition"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_disability" name="has_disability" value="1">
                                <label class="form-check-label" for="edit_has_disability">Has Disability</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_chronic_illness" name="has_chronic_illness" value="1">
                                <label class="form-check-label" for="edit_has_chronic_illness">Has Chronic Illness</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_disabled" name="is_disabled" value="1">
                                <label class="form-check-label" for="edit_is_disabled">Disabled</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_epilepsy" name="has_epilepsy" value="1">
                                <label class="form-check-label" for="edit_has_epilepsy">Epilepsy</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_allergies" name="has_allergies" value="1">
                                <label class="form-check-label" for="edit_has_allergies">Allergies</label>
                            </div>
                        </div>
                    </div>

                    <div id="editHealthDetailsSection">
                        <div class="mb-3" id="editDisabilityDetailsContainer" style="display: none;">
                            <label for="edit_disability_details" class="form-label">Disability Details</label>
                            <textarea class="form-control" id="edit_disability_details" name="disability_details" rows="2"></textarea>
                        </div>
                        <div class="mb-3" id="editChronicIllnessDetailsContainer" style="display: none;">
                            <label for="edit_chronic_illness_details" class="form-label">Chronic Illness Details</label>
                            <textarea class="form-control" id="edit_chronic_illness_details" name="chronic_illness_details" rows="2"></textarea>
                        </div>
                        <div class="mb-3" id="editAllergiesDetailsContainer" style="display: none;">
                            <label for="edit_allergies_details" class="form-label">Allergies Details</label>
                            <textarea class="form-control" id="edit_allergies_details" name="allergies_details" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_immunization_details" class="form-label">Immunization Details</label>
                        <textarea class="form-control" id="edit_immunization_details" name="immunization_details" rows="2"></textarea>
                    </div>
                    
                    <!-- Emergency Contact Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-telephone"></i> Emergency Contact</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_emergency_contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_emergency_contact_name" name="emergency_contact_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_emergency_contact_relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_emergency_contact_relationship" name="emergency_contact_relationship" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_emergency_contact_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_emergency_contact_phone" name="emergency_contact_phone" required>
                        </div>
                    </div>

                    <!-- Official Use Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-person-badge"></i> Official Use & Declaration</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_declaration_date" class="form-label">Declaration Date</label>
                            <input type="date" class="form-control" id="edit_declaration_date" name="declaration_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_registering_officer_name" class="form-label">Registering Officer</label>
                            <input type="text" class="form-control" id="edit_registering_officer_name" name="registering_officer_name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_registering_officer_title" class="form-label">Officer Title</label>
                            <input type="text" class="form-control" id="edit_registering_officer_title" name="registering_officer_title">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-check-circle"></i> Save Changes
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



{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- JsBarcode Library for ID Card --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

{{-- html2canvas Library for ID Card Download --}}
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

{{-- jsPDF and AutoTable for PDF Export --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('includes.footer')

<script>
    // Permissions from PHP (for JavaScript use)
    const canCreate = {{ ($canCreate ?? false) ? 'true' : 'false' }};
    const canUpdate = {{ ($canUpdate ?? false) ? 'true' : 'false' }};
    const canDelete = {{ ($canDelete ?? false) ? 'true' : 'false' }};

    // Ensure $ is bound to jQuery and detect if select2 is available
    if (typeof jQuery !== 'undefined') {
        window.$ = window.jQuery = jQuery;
    }

    $(document).ready(function() {
        // Safety check for Select2
        function safeInitSelect2(selector, options) {
            const $el = $(selector);
            if ($el.length === 0) return;
            
            if (typeof $.fn.select2 === 'function') {
                $el.select2(options);
            } else {
                console.warn('Select2 not yet loaded for ' + selector + ', retrying in 200ms...');
                setTimeout(function() {
                    safeInitSelect2(selector, options);
                }, 200);
            }
        }

        let currentStatus = 'Active';
        let currentStudentsData = [];

        // Handle Register New Student Button Click
        $('#addStudentBtn').on('click', function(e) {
            e.preventDefault();
            const modal = document.getElementById('classSelectorModal');
            if (modal) {
                // Try Bootstrap 5 modal
                if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                    const bsModal = new bootstrap.Modal(modal, {
                        backdrop: 'static',
                        keyboard: true
                    });
                    bsModal.show();
                } else if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                    // Fallback to jQuery Bootstrap modal
                    jQuery(modal).modal('show');
                } else {
                    // Manual fallback
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'classSelectorBackdrop';
                    document.body.appendChild(backdrop);
                }
            } else {
                console.error('classSelectorModal not found');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Class selector modal not found. Please refresh the page.',
                    confirmButtonColor: '#940000'
                });
            }
        });

        // Show/hide allergies details based on checkbox
        $('#has_allergies').on('change', function() {
            if ($(this).is(':checked')) {
                $('#allergiesDetailsContainer').slideDown();
            } else {
                $('#allergiesDetailsContainer').slideUp();
                $('#allergies_details').val('');
            }
        });

        // Show/hide health details for edit form
        $('#edit_has_disability').on('change', function() {
            if ($(this).is(':checked')) {
                $('#editDisabilityDetailsContainer').slideDown();
            } else {
                $('#editDisabilityDetailsContainer').slideUp();
                $('#edit_disability_details').val('');
            }
        });
        
        $('#edit_has_chronic_illness').on('change', function() {
            if ($(this).is(':checked')) {
                $('#editChronicIllnessDetailsContainer').slideDown();
            } else {
                $('#editChronicIllnessDetailsContainer').slideUp();
                $('#edit_chronic_illness_details').val('');
            }
        });
        
        $('#edit_has_allergies').on('change', function() {
            if ($(this).is(':checked')) {
                $('#editAllergiesDetailsContainer').slideDown();
            } else {
                $('#editAllergiesDetailsContainer').slideUp();
                $('#edit_allergies_details').val('');
            }
        });

        // Load subclasses and parents for registration form
        function loadFormData(preSelectSubclassID = null) {
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
                        
                        // Filter subclasses if preSelectSubclassID is provided (for teacher view)
                        let filteredSubclasses = response.subclasses;
                        if (preSelectSubclassID) {
                            filteredSubclasses = response.subclasses.filter(function(subclass) {
                                return subclass.subclassID == preSelectSubclassID;
                            });
                        }
                        
                        filteredSubclasses.forEach(function(subclass) {
                            // Display display_name (class_name + subclass_name) e.g., "Form Four A"
                            const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                            subclassSelect.append('<option value="' + subclass.subclassID + '">' + displayName + '</option>');
                        });

                        // Initialize Select2 for class select with search
                        if (typeof subclassSelect.select2 === 'function') {
                            subclassSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: preSelectSubclassID ? 'Class (Pre-selected)' : 'Search and select a class...',
                                allowClear: !preSelectSubclassID, // Disable clear if pre-selected
                                width: '100%',
                                dropdownParent: $('#addStudentModal')
                            });
                        }
                        
                        // Pre-select subclass if provided
                        if (preSelectSubclassID) {
                            subclassSelect.val(preSelectSubclassID).trigger('change');
                            subclassSelect.prop('disabled', true); // Disable selection for teacher
                            
                            // Trigger event to notify that form is ready
                            $(document).trigger('subclassPreSelected', [preSelectSubclassID]);
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

                            if (typeof parentSelect.select2 === 'function') {
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
                }
            });

            // Load sponsors
            $.ajax({
                url: '{{ route("get_sponsors") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let sponsorSelect = $('#sponsor_id');
                        sponsorSelect.html('<option value="">Choose a sponsor...</option>');
                        response.sponsors.forEach(function(sponsor) {
                            sponsorSelect.append('<option value="' + sponsor.sponsorID + '">' + sponsor.sponsor_name + '</option>');
                        });
                    }
                }
            });
        }

        // Handle payment type change
        $('#payment_type').on('change', function() {
            if ($(this).val() === 'sponsor') {
                $('#sponsorSelectContainer').slideDown();
                $('#sponsorshipPercentageContainer').slideDown();
            } else {
                $('#sponsorSelectContainer').slideUp();
                $('#sponsorshipPercentageContainer').slideUp();
                $('#sponsor_id').val('');
                $('#sponsorship_percentage').val('');
            }
        });

        $('#edit_payment_type').on('change', function() {
            if ($(this).val() === 'sponsor') {
                $('#editSponsorSelectContainer').slideDown();
                $('#editSponsorshipPercentageContainer').slideDown();
            } else {
                $('#editSponsorSelectContainer').slideUp();
                $('#editSponsorshipPercentageContainer').slideUp();
                $('#edit_sponsor_id').val('');
                $('#edit_sponsorship_percentage').val('');
            }
        });

        // Load students with filters
        function loadStudents() {
            console.log('Loading students with filters');

            // Get filter values
            let status = $('#statusFilter').val() || 'Active';
            let classID = $('#classFilter').val() || '';
            let subclassID = $('#subclassFilter').val() || '';
            let gender = $('#genderFilter').val() || '';
            let health = $('#healthFilter').val() || '';

            // Update currentStatus
            currentStatus = status;

            $('#studentsWidgetGrid').html('<div class="no-data-message text-center py-5"><i class="bi bi-hourglass-split" style="font-size: 3rem; color: #6c757d;"></i><p class="text-muted mt-2">Loading students...</p></div>');

            $.ajax({
                url: '{{ route("get_students_list") }}',
                type: 'GET',
                data: {
                    status: status,
                    classID: classID,
                    subclassID: subclassID,
                    gender: gender,
                    health: health
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response received:', response);

                    if (response.success) {
                        if (response.students && response.students.length > 0) {
                            currentStudentsData = response.students;
                            renderStudentWidgets(response.students, status);
                        } else {
                            currentStudentsData = [];
                            $('#studentsWidgetGrid').html('<div class="no-data-message text-center py-5"><i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i><h5 class="mt-3 text-muted">No Data Available</h5><p class="text-muted">No students found matching the selected filters.</p></div>');
                        }

                        loadStatistics();
                    } else {
                        $('#studentsWidgetGrid').html('<div class="no-data-message text-center py-5"><i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i><h5 class="mt-3 text-muted">No Data Available</h5><p class="text-muted">Failed to load students.</p></div>');
                        loadStatistics();
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
                    } else if (xhr.responseText) {
                        try {
                            let errorData = JSON.parse(xhr.responseText);
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            // If not JSON, use response text
                            if (xhr.responseText.length < 200) {
                                errorMessage = xhr.responseText;
                            }
                        }
                    }

                    let noDataHtml = '<div class="no-data-message text-center py-5">' +
                        '<i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #dc3545;"></i>' +
                        '<h5 class="mt-3 text-danger">Error Loading Data</h5>' +
                        '<p class="text-muted">' + errorMessage + '</p>' +
                        '<button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">Refresh Page</button>' +
                        '</div>';
                    $('#studentsWidgetGrid').html(noDataHtml);
                    
                    // Load statistics even on error
                    loadStatistics();
                }
            });
        }

        function renderStudentWidgets(students, status) {
            let html = '';
            // currentStudentsData = students || []; // Removed to prevent overwriting master list during search
            students.forEach(function(student) {
                let fingerprintIdHtml = '';
                if (student.fingerprint_id) {
                    fingerprintIdHtml = '<span class="badge bg-success"><i class="bi bi-fingerprint"></i> ' + student.fingerprint_id + '</span>';
                } else {
                    fingerprintIdHtml = '<span class="badge bg-secondary"><i class="bi bi-dash"></i> No ID</span>';
                }

                let actionsHtml = '<div class="student-widget-actions">' +
                        '<button class="btn btn-sm btn-info view-student-btn" data-student-id="' + student.studentID + '" title="View More Details">' +
                        '<i class="bi bi-eye"></i>' +
                        '</button>';

                if (canUpdate && status === 'Active') {
                        actionsHtml += '<button class="btn btn-sm btn-warning edit-student-btn" data-student-id="' + student.studentID + '" title="Edit Student">' +
                            '<i class="bi bi-pencil-square"></i>' +
                            '</button>';
                    }

                if (status === 'Active') {
                    if (!student.fingerprint_id) {
                        actionsHtml += '<a href="#" class="btn btn-sm btn-success text-white send-student-to-fingerprint-btn" data-student-id="' + student.studentID + '" data-student-name="' + (student.first_name || student.full_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '" title="Send to Fingerprint Device">' +
                            '<i class="bi bi-fingerprint"></i>' +
                            '</a>';
                    } else if (!student.sent_to_device) {
                        actionsHtml += '<button class="btn btn-sm btn-success text-white register-student-to-device-btn" data-student-id="' + student.studentID + '" data-student-name="' + (student.first_name || student.full_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '" title="Register to Device">' +
                            '<i class="bi bi-device-hdd"></i>' +
                            '</button>';
                    }
                    actionsHtml += '<button class="btn btn-sm btn-primary generate-id-btn" data-student-id="' + student.studentID + '" title="Generate Student ID Card">' +
                        '<i class="bi bi-card-text"></i>' +
                        '</button>';
                }

                if (canDelete && status === 'Active') {
                        actionsHtml += '<button class="btn btn-sm btn-danger delete-student-btn" data-student-id="' + student.studentID + '" data-student-name="' + student.full_name + '" title="Delete Student">' +
                            '<i class="bi bi-trash"></i>' +
                            '</button>';
                    }

                actionsHtml += '</div>';

                let healthAlarmIcon = '';
                let hasHealthCondition = false;
                if ((student.is_disabled === true || student.is_disabled == 1 || student.is_disabled === "1") ||
                    (student.has_epilepsy === true || student.has_epilepsy == 1 || student.has_epilepsy === "1") ||
                    (student.has_allergies === true || student.has_allergies == 1 || student.has_allergies === "1") ||
                    (student.has_disability === true || student.has_disability == 1 || student.has_disability === "1") ||
                    (student.has_chronic_illness === true || student.has_chronic_illness == 1 || student.has_chronic_illness === "1")) {
                    hasHealthCondition = true;
                }
                if (hasHealthCondition) {
                    healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                }

                html += '<div class="student-widget">' +
                        '<div class="student-widget-header">' +
                            '<img src="' + student.photo + '" alt="' + student.full_name + '">' +
                        '</div>' +
                        '<div class="student-widget-body">' +
                            '<h3>' + (student.full_name || '-') + healthAlarmIcon + '</h3>' +
                            '<p class="form">' + (student.class || '-') + '</p>' +
                            '<div class="info">' +
                                '<p><strong>Admission:</strong> ' + (student.admission_number || '-') + '</p>' +
                                '<p><strong>Gender:</strong> ' + (student.gender || '-') + '</p>' +
                                '<p><strong>Parent:</strong> ' + (student.parent_name || '-') + '</p>' +
                                '<p><strong>Fingerprint:</strong> ' + fingerprintIdHtml + '</p>' +
                            '</div>' +
                            actionsHtml +
                        '</div>' +
                    '</div>';
            });

            $('#studentsWidgetGrid').html(html);
        }

        // Client-side Search Implementation
        $('#studentSearchInput').on('keyup', function() {
            let searchTerm = $(this).val().toLowerCase().trim();
            
            if (searchTerm.length === 0) {
                // If search is empty, show all students from current context (master list)
                if (currentStudentsData.length > 0) {
                    renderStudentWidgets(currentStudentsData, currentStatus);
                } else {
                     $('#studentsWidgetGrid').html('<div class="no-data-message text-center py-5"><i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i><h5 class="mt-3 text-muted">No Data Available</h5><p class="text-muted">No students found matching the selected filters.</p></div>');
                }
                return;
            }
            
            let filteredStudents = currentStudentsData.filter(function(student) {
                // Construct search string
                let textToSearch = [
                    student.full_name || '',
                    student.admission_number || '',
                    student.class || '',
                    student.parent_name || '',
                    student.fingerprint_id || '',
                    student.gender || ''
                ].join(' ').toLowerCase();
                
                return textToSearch.includes(searchTerm);
            });
            
            if (filteredStudents.length > 0) {
                renderStudentWidgets(filteredStudents, currentStatus);
            } else {
                 $('#studentsWidgetGrid').html('<div class="no-data-message text-center py-5"><i class="bi bi-search" style="font-size: 3rem; color: #6c757d;"></i><h5 class="mt-3 text-muted">No Matches Found</h5><p class="text-muted">No students found matching "' + $(this).val() + '"</p></div>');
            }
        });


        // Function to load statistics
        function loadStatistics() {
            // Get status from filter
            let status = $('#statusFilter').val() || 'Active';
            
            let classID = $('#classFilter').val() || '';
            let subclassID = $('#subclassFilter').val() || '';
            let gender = $('#genderFilter').val() || '';
            let health = $('#healthFilter').val() || '';

            // Show loading state on statistics
            $('#statTotalStudents, #statMaleCount, #statFemaleCount, #statGoodHealth, #statBadHealth')
                .html('<i class="bi bi-hourglass-split"></i>');

            $.ajax({
                url: '{{ route("get_student_statistics") }}',
                type: 'GET',
                data: {
                    status: status,
                    classID: classID,
                    subclassID: subclassID,
                    gender: gender,
                    health: health
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.statistics) {
                        $('#statTotalStudents').text(response.statistics.total_students || 0);
                        $('#statMaleCount').text(response.statistics.male_count || 0);
                        $('#statFemaleCount').text(response.statistics.female_count || 0);
                        $('#statGoodHealth').text(response.statistics.good_health_count || 0);
                        $('#statBadHealth').text(response.statistics.bad_health_count || 0);
                    } else {
                        // Reset to 0 if error
                        $('#statTotalStudents').text('0');
                        $('#statMaleCount').text('0');
                        $('#statFemaleCount').text('0');
                        $('#statGoodHealth').text('0');
                        $('#statBadHealth').text('0');
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load statistics:', xhr);
                    // Reset to 0 on error
                    $('#statTotalStudents').text('0');
                    $('#statMaleCount').text('0');
                    $('#statFemaleCount').text('0');
                    $('#statGoodHealth').text('0');
                    $('#statBadHealth').text('0');
                }
            });
        }

        // Function to reload data and statistics
        function reloadData() {
            loadStudents();
            loadStatistics();
        }

        // Filter change handlers
        let filterTimeout;
        function handleFilterChange() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                reloadData();
            }, 300);
        }

        // Status filter
        $('#statusFilter').on('change', function() {
            let status = $(this).val() || 'Active';
            currentStatus = status;
            reloadData();
        });

        // Class filter
        $('#classFilter').on('change', function() {
            let classID = $(this).val();
            
            // Reload subclasses based on selected class
            if (classID) {
                loadSubclassesForFilter(classID);
            } else {
                // Reset subclass filter and load all subclasses
                $('#subclassFilter').html('<option value="">-- All Subclasses --</option>');
                loadAllSubclassesForFilter();
            }
            
            handleFilterChange();
        });

        // Subclass, Gender, Health filters
        $('#subclassFilter, #genderFilter, #healthFilter').on('change', function() {
            handleFilterChange();
        });

        // View Student Details
        $(document).on('click', '.view-student-btn', function() {
            let studentID = $(this).data('student-id');
            let currentStudentID = studentID; // Store for use in other functions

            $.ajax({
                url: '{{ route("get_student_details", ":id") }}'.replace(':id', studentID),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let student = response.student;
                        
                        // Create tab structure
                        let html = '<ul class="nav nav-tabs mb-3" id="studentDetailsTabs" role="tablist" style="border-radius: 0;">';
                        html += '<li class="nav-item" role="presentation">';
                        html += '<button class="nav-link active" id="particulars-tab" data-bs-toggle="tab" data-bs-target="#particulars-pane" type="button" role="tab" aria-controls="particulars-pane" aria-selected="true">';
                        html += '<i class="bi bi-person-vcard"></i> Student Particulars';
                        html += '</button>';
                        html += '</li>';
                        html += '<li class="nav-item" role="presentation">';
                        html += '<button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic-pane" type="button" role="tab" aria-controls="academic-pane" aria-selected="false">';
                        html += '<i class="bi bi-book"></i> Academic Details';
                        html += '</button>';
                        html += '</li>';
                        html += '</ul>';
                        
                        html += '<div class="tab-content" id="studentDetailsTabContent">';
                        
                        // Tab 1: Student Particulars
                        html += '<div class="tab-pane fade show active" id="particulars-pane" role="tabpanel" aria-labelledby="particulars-tab">';
                        html += '<div class="school-details-card" style="border-radius: 0;">';
                        html += '<div class="school-header">';
                        html += '<div class="d-flex align-items-center">';
                        html += '<div class="school-logo-preview me-3">';
                        html += '<img src="' + student.photo + '" alt="' + student.full_name + '">';
                        html += '</div>';
                        html += '<div>';
                        // Check for bad health conditions - support both boolean and integer values
                        let hasBadHealth = false;
                        let healthConditions = [];
                        
                        // Check is_disabled (can be true, 1, or "1")
                        if (student.is_disabled === true || student.is_disabled == 1 || student.is_disabled === "1") {
                            hasBadHealth = true;
                            healthConditions.push('Disabled');
                        }
                        
                        // Check has_epilepsy (can be true, 1, or "1")
                        if (student.has_epilepsy === true || student.has_epilepsy == 1 || student.has_epilepsy === "1") {
                            hasBadHealth = true;
                            healthConditions.push('Epilepsy/Seizure Disorder');
                        }
                        
                        // Check has_allergies (can be true, 1, or "1")
                        if (student.has_allergies === true || student.has_allergies == 1 || student.has_allergies === "1") {
                            hasBadHealth = true;
                            healthConditions.push('Allergies');
                        }
                        
                        // Check has_disability (can be true, 1, or "1")
                        if (student.has_disability === true || student.has_disability == 1 || student.has_disability === "1") {
                            hasBadHealth = true;
                            if (!healthConditions.includes('Disabled')) {
                                healthConditions.push('Disability');
                            }
                        }
                        
                        // Check has_chronic_illness (can be true, 1, or "1")
                        if (student.has_chronic_illness === true || student.has_chronic_illness == 1 || student.has_chronic_illness === "1") {
                            hasBadHealth = true;
                            healthConditions.push('Chronic Illness');
                        }
                        
                        let healthAlertIcon = '';
                        if (hasBadHealth) {
                            healthAlertIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1.2rem;" title="Health Condition Alert - This student has health conditions: ' + healthConditions.join(', ') + '"></i>';
                        }
                        
                        html += '<h3 class="school-title">' + student.full_name + healthAlertIcon + '</h3>';
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

                        // Health Information Section - Rebuild health conditions array
                        let healthConditionsList = [];
                        
                        // Check is_disabled (can be true, 1, or "1")
                        if (student.is_disabled === true || student.is_disabled == 1 || student.is_disabled === "1") {
                            let disabledText = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Disabled</span>';
                            if (student.disability_details) {
                                disabledText += '<br><small class="text-danger mt-1 d-block">Details: ' + student.disability_details + '</small>';
                            }
                            healthConditionsList.push(disabledText);
                        }
                        
                        // Check has_disability (can be true, 1, or "1")
                        if (student.has_disability === true || student.has_disability == 1 || student.has_disability === "1") {
                            let disabilityText = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Disability</span>';
                            if (student.disability_details) {
                                disabilityText += '<br><small class="text-danger mt-1 d-block">Details: ' + student.disability_details + '</small>';
                            }
                            healthConditionsList.push(disabilityText);
                        }
                        
                        // Check has_epilepsy (can be true, 1, or "1")
                        if (student.has_epilepsy === true || student.has_epilepsy == 1 || student.has_epilepsy === "1") {
                            healthConditionsList.push('<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Epilepsy/Seizure Disorder</span>');
                        }
                        
                        // Check has_allergies (can be true, 1, or "1")
                        if (student.has_allergies === true || student.has_allergies == 1 || student.has_allergies === "1") {
                            let allergiesText = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Allergies</span>';
                            if (student.allergies_details) {
                                allergiesText += '<br><small class="text-danger mt-1 d-block">Details: ' + student.allergies_details + '</small>';
                            }
                            healthConditionsList.push(allergiesText);
                        }
                        
                        // Check has_chronic_illness (can be true, 1, or "1")
                        if (student.has_chronic_illness === true || student.has_chronic_illness == 1 || student.has_chronic_illness === "1") {
                            let chronicText = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Chronic Illness</span>';
                            if (student.chronic_illness_details) {
                                chronicText += '<br><small class="text-danger mt-1 d-block">Details: ' + student.chronic_illness_details + '</small>';
                            }
                            healthConditionsList.push(chronicText);
                        }
                        
                        // Check general_health_condition - separate good health from bad health
                        let generalHealthCondition = '';
                        let isGoodHealth = false;
                        if (student.general_health_condition && student.general_health_condition !== 'N/A' && student.general_health_condition.trim() !== '') {
                            let healthConditionLower = student.general_health_condition.toLowerCase().trim();
                            // Check if it's a positive/good health condition
                            if (healthConditionLower === 'good' || healthConditionLower === 'excellent' || healthConditionLower === 'fine' || healthConditionLower === 'healthy') {
                                isGoodHealth = true;
                                generalHealthCondition = student.general_health_condition;
                            } else {
                                // Bad health condition - add to alert list
                            healthConditionsList.push('<span class="badge bg-warning text-dark"><i class="bi bi-info-circle-fill"></i> General Health: ' + student.general_health_condition + '</span>');
                            }
                        }
                        
                        // Display health conditions alert if there are bad conditions
                        if (healthConditionsList.length > 0) {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #fff5f5; border: 2px solid #dc3545; border-radius: 0; padding: 15px;">';
                            html += '<i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="color: #dc3545; font-weight: 700; font-size: 0.9rem;">HEALTH CONDITIONS ALERT</div>';
                            html += '<div class="info-item-value mt-2">' + healthConditionsList.join('<br>') + '</div>';
                            html += '</div>';
                            html += '</div>';
                        } else {
                            // Display good health status (green) ONLY if there are NO bad health conditions
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #f0f9f0; border: 2px solid #28a745; border-radius: 0; padding: 15px;">';
                            html += '<i class="bi bi-heart-pulse-fill text-success" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="color: #28a745; font-weight: 700; font-size: 0.9rem;">HEALTH STATUS</div>';
                            if (isGoodHealth && generalHealthCondition) {
                                html += '<div class="info-item-value mt-2"><span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> ' + generalHealthCondition + '</span></div>';
                            } else {
                                html += '<div class="info-item-value mt-2"><span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Good Health</span></div>';
                            }
                            html += '</div>';
                            html += '</div>';
                        }

                        // Parent Information
                        if (student.parent) {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6;">';
                            html += '<i class="bi bi-person-heart text-primary-custom" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="font-weight: 700; font-size: 0.9rem; color: #940000;">PARENT INFORMATION</div>';
                            html += '<div class="row mt-2">';
                            html += '<div class="col-md-6"><div class="info-item-label">Name</div><div class="info-item-value">' + student.parent.full_name + '</div></div>';
                            html += '<div class="col-md-6"><div class="info-item-label">Phone</div><div class="info-item-value">' + student.parent.phone + '</div></div>';
                            html += '<div class="col-md-6"><div class="info-item-label">Email</div><div class="info-item-value">' + student.parent.email + '</div></div>';
                            html += '<div class="col-md-6"><div class="info-item-label">Occupation</div><div class="info-item-value">' + student.parent.occupation + '</div></div>';
                            if (student.parent.relationship) {
                                html += '<div class="col-md-6"><div class="info-item-label">Relationship</div><div class="info-item-value">' + student.parent.relationship + '</div></div>';
                            }
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        // Sponsorship Information
                        if (student.sponsor_id) {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #fff9e6; padding: 15px; border: 1px solid #ffd633;">';
                            html += '<i class="bi bi-handshake-fill text-warning" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="font-weight: 700; font-size: 0.9rem; color: #856404;">SPONSORSHIP INFORMATION</div>';
                            html += '<div class="row mt-2">';
                            html += '<div class="col-md-4"><div class="info-item-label">Sponsor</div><div class="info-item-value">' + (student.sponsor_name || 'Assigned') + '</div></div>';
                            html += '<div class="col-md-4"><div class="info-item-label">Sponsor ID</div><div class="info-item-value">' + student.sponsor_id + '</div></div>';
                            html += '<div class="col-md-4"><div class="info-item-label">Percentage Cover</div><div class="info-item-value">' + (student.sponsorship_percentage || 0) + '%</div></div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        // Emergency Contact (Next of Kin)
                        if (student.emergency_contact_name && student.emergency_contact_name !== 'N/A') {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6;">';
                            html += '<i class="bi bi-telephone-forward text-warning" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="font-weight: 700; font-size: 0.9rem; color: #940000;">EMERGENCY CONTACT (NEXT OF KIN)</div>';
                            html += '<div class="row mt-2">';
                            html += '<div class="col-md-4"><div class="info-item-label">Name</div><div class="info-item-value">' + (student.emergency_contact_name || 'N/A') + '</div></div>';
                            html += '<div class="col-md-4"><div class="info-item-label">Relationship</div><div class="info-item-value">' + (student.emergency_contact_relationship || 'N/A') + '</div></div>';
                            html += '<div class="col-md-4"><div class="info-item-label">Phone</div><div class="info-item-value">' + (student.emergency_contact_phone || 'N/A') + '</div></div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        // Registration Information
                        if (student.registering_officer_name && student.registering_officer_name !== 'N/A') {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6;">';
                            html += '<i class="bi bi-person-check text-info" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="font-weight: 700; font-size: 0.9rem; color: #940000;">REGISTRATION INFORMATION</div>';
                            html += '<div class="row mt-2">';
                            html += '<div class="col-md-6"><div class="info-item-label">Registered By</div><div class="info-item-value">' + (student.registering_officer_name || 'N/A') + '</div></div>';
                            html += '<div class="col-md-6"><div class="info-item-label">Registration Date</div><div class="info-item-value">' + (student.declaration_date || 'N/A') + '</div></div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        // Additional Student Details
                        html += '<div class="info-item"><i class="bi bi-card-text"></i><div class="info-item-content"><div class="info-item-label">Birth Certificate Number</div><div class="info-item-value">' + (student.birth_certificate_number || 'N/A') + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-heart"></i><div class="info-item-content"><div class="info-item-label">Religion</div><div class="info-item-value">' + (student.religion || 'N/A') + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-globe"></i><div class="info-item-content"><div class="info-item-label">Nationality</div><div class="info-item-value">' + (student.nationality || 'N/A') + '</div></div></div>';
                        if (student.immunization_details && student.immunization_details !== 'N/A') {
                            html += '<div class="info-item" style="grid-column: 1 / -1;"><i class="bi bi-shield-check"></i><div class="info-item-content"><div class="info-item-label">Immunization Details</div><div class="info-item-value">' + student.immunization_details + '</div></div></div>';
                        }

                        html += '</div></div>'; // Close school-details-card
                        html += '</div>'; // Close particulars-pane
                        
                        // Tab 2: Academic Details
                        html += '<div class="tab-pane fade" id="academic-pane" role="tabpanel" aria-labelledby="academic-tab">';
                        html += '<div class="p-4" style="border-radius: 0;">';
                        html += '<div class="mb-4">';
                        html += '<label class="form-label fw-bold mb-2"><i class="bi bi-calendar"></i> Academic Year</label>';
                        html += '<select class="form-select" id="academicYearSelector" data-student-id="' + studentID + '" style="border-radius: 0;">';
                        html += '<option value="">Loading...</option>';
                        html += '</select>';
                        html += '</div>';
                        
                        // Class Widget (Expandable)
                        html += '<div class="card mb-3 academic-widget" id="classWidget" style="border-radius: 0; cursor: pointer; border: 2px solid #dee2e6;" data-expanded="false">';
                        html += '<div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center" style="border-radius: 0;">';
                        html += '<div><i class="bi bi-book"></i> <strong>Class</strong></div>';
                        html += '<i class="bi bi-chevron-down widget-toggle-icon"></i>';
                        html += '</div>';
                        html += '<div class="card-body widget-content" style="display: none;">';
                        html += '<div id="classWidgetContent">';
                        html += '<p class="text-muted text-center py-3">Select Academic Year first</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        
                        // Results Widget (Expandable)
                        html += '<div class="card mb-3 academic-widget" id="resultsWidget" style="border-radius: 0; cursor: pointer; border: 2px solid #dee2e6;" data-expanded="false">';
                        html += '<div class="card-header bg-info text-white d-flex justify-content-between align-items-center" style="border-radius: 0;">';
                        html += '<div><i class="bi bi-file-earmark-text"></i> <strong>Results</strong></div>';
                        html += '<i class="bi bi-chevron-down widget-toggle-icon"></i>';
                        html += '</div>';
                        html += '<div class="card-body widget-content" style="display: none;">';
                        html += '<div id="resultsWidgetContent">';
                        html += '<p class="text-muted text-center py-3">Select Academic Year first</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        
                        // Attendance Widget (Expandable)
                        html += '<div class="card mb-3 academic-widget" id="attendanceWidget" style="border-radius: 0; cursor: pointer; border: 2px solid #dee2e6;" data-expanded="false">';
                        html += '<div class="card-header bg-success text-white d-flex justify-content-between align-items-center" style="border-radius: 0;">';
                        html += '<div><i class="bi bi-calendar-check"></i> <strong>Attendance</strong></div>';
                        html += '<i class="bi bi-chevron-down widget-toggle-icon"></i>';
                        html += '</div>';
                        html += '<div class="card-body widget-content" style="display: none;">';
                        html += '<div id="attendanceWidgetContent">';
                        html += '<p class="text-muted text-center py-3">Select Academic Year first</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        
                        // Payments Widget (Expandable)
                        html += '<div class="card mb-3 academic-widget" id="paymentsWidget" style="border-radius: 0; cursor: pointer; border: 2px solid #dee2e6;" data-expanded="false">';
                        html += '<div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center" style="border-radius: 0;">';
                        html += '<div><i class="bi bi-cash-coin"></i> <strong>Payments</strong></div>';
                        html += '<i class="bi bi-chevron-down widget-toggle-icon"></i>';
                        html += '</div>';
                        html += '<div class="card-body widget-content" style="display: none;">';
                        html += '<div id="paymentsWidgetContent">';
                        html += '<p class="text-muted text-center py-3">Select Academic Year first</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        
                        // Debits (Madeni) Widget (Expandable)
                        html += '<div class="card mb-3 academic-widget" id="debitsWidget" style="border-radius: 0; cursor: pointer; border: 2px solid #dee2e6;" data-expanded="false">';
                        html += '<div class="card-header bg-danger text-white d-flex justify-content-between align-items-center" style="border-radius: 0;">';
                        html += '<div><i class="bi bi-exclamation-triangle"></i> <strong>Debts (Madeni)</strong></div>';
                        html += '<i class="bi bi-chevron-down widget-toggle-icon"></i>';
                        html += '</div>';
                        html += '<div class="card-body widget-content" style="display: none;">';
                        html += '<div id="debitsWidgetContent">';
                        html += '<p class="text-muted text-center py-3">Select Academic Year first</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        
                        html += '</div>'; // Close p-4
                        html += '</div>'; // Close academic-pane
                        
                        html += '</div>'; // Close tab-content

                        $('#studentDetailsContent').html(html);
                        showModal('viewStudentModal');
                        
                        // Load academic years for this student (non-blocking)
                        try {
                            setTimeout(function() {
                                loadAcademicYearsForStudent(studentID);
                            }, 100);
                        } catch (e) {
                            console.error('Error loading academic years:', e);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading student details:', error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status:', status);
                    let errorMessage = 'Failed to load student details';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            let errorData = JSON.parse(xhr.responseText);
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            // Not JSON, use response text if short
                            if (xhr.responseText.length < 200) {
                                errorMessage = xhr.responseText;
                            }
                        }
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        footer: 'Status: ' + xhr.status + ' | Error: ' + error
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

                        // Set additional particulars
                        $('#edit_birth_certificate_number').val(student.birth_certificate_number || '');
                        $('#edit_religion').val(student.religion || '');
                        $('#edit_nationality').val(student.nationality || '');
                        
                        // Set health information fields
                        $('#edit_general_health_condition').val(student.general_health_condition || '');
                        $('#edit_has_disability').prop('checked', student.has_disability == 1 || student.has_disability === true);
                        $('#edit_disability_details').val(student.disability_details || '');
                        $('#edit_has_chronic_illness').prop('checked', student.has_chronic_illness == 1 || student.has_chronic_illness === true);
                        $('#edit_chronic_illness_details').val(student.chronic_illness_details || '');
                        $('#edit_immunization_details').val(student.immunization_details || '');
                        $('#edit_is_disabled').prop('checked', student.is_disabled == 1 || student.is_disabled === true);
                        $('#edit_has_epilepsy').prop('checked', student.has_epilepsy == 1 || student.has_epilepsy === true);
                        $('#edit_has_allergies').prop('checked', student.has_allergies == 1 || student.has_allergies === true);
                        $('#edit_allergies_details').val(student.allergies_details || '');
                        
                        $('#edit_emergency_contact_name').val(student.emergency_contact_name || '');
                        $('#edit_emergency_contact_relationship').val(student.emergency_contact_relationship || '');
                        $('#edit_emergency_contact_phone').val(student.emergency_contact_phone || '');
                        
                        // Official Use fields
                        $('#edit_declaration_date').val(student.declaration_date || '');
                        $('#edit_registering_officer_name').val(student.registering_officer_name || '');
                        $('#edit_registering_officer_title').val(student.registering_officer_title || '');
                        
                        // Set sponsorship fields
                        if (student.sponsor_id) {
                            $('#edit_payment_type').val('sponsor').trigger('change');
                            window.pendingEditSponsorID = student.sponsor_id; // Store for after sponsors load
                            $('#edit_sponsor_id').val(student.sponsor_id);
                            $('#edit_sponsorship_percentage').val(student.sponsorship_percentage);
                            $('#editSponsorSelectContainer').show();
                            $('#editSponsorshipPercentageContainer').show();
                        } else {
                            $('#edit_payment_type').val('own').trigger('change');
                            window.pendingEditSponsorID = null;
                            $('#edit_sponsor_id').val('');
                            $('#edit_sponsorship_percentage').val('');
                            $('#editSponsorSelectContainer').hide();
                            $('#editSponsorshipPercentageContainer').hide();
                        }

                        // Show/hide details containers based on checkboxes
                        if (student.has_disability == 1 || student.has_disability === true) {
                            $('#editDisabilityDetailsContainer').show();
                        } else {
                            $('#editDisabilityDetailsContainer').hide();
                        }
                        
                        if (student.has_chronic_illness == 1 || student.has_chronic_illness === true) {
                            $('#editChronicIllnessDetailsContainer').show();
                        } else {
                            $('#editChronicIllnessDetailsContainer').hide();
                        }
                        
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
                        loadEditFormData(student.subclassID, student.parentID);

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
        function loadEditFormData(targetSubclassID = null, targetParentID = null) {
            // Load subclasses
            $.ajax({
                url: '{{ route("get_subclasses_for_school") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#edit_subclassID');
                        
                        // Destroy existing Select2 if it exists
                        if (subclassSelect.hasClass('select2-hidden-accessible')) {
                            subclassSelect.select2('destroy');
                        }

                        subclassSelect.html('<option value="">Choose a class...</option>');
                        response.subclasses.forEach(function(subclass) {
                            const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                            let selected = (subclass.subclassID == targetSubclassID) ? 'selected' : '';
                            subclassSelect.append('<option value="' + subclass.subclassID + '" ' + selected + '>' + displayName + '</option>');
                        });

                        if (typeof subclassSelect.select2 === 'function') {
                            subclassSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Search and select a class...',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#editStudentModal')
                            });
                        }

                        if (targetSubclassID) {
                            subclassSelect.val(targetSubclassID).trigger('change');
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
                        
                        if (parentSelect.hasClass('select2-hidden-accessible')) {
                            parentSelect.select2('destroy');
                        }

                        parentSelect.html('<option value="">Choose a parent...</option>');
                        response.parents.forEach(function(parent) {
                            let fullName = (parent.first_name || '') + ' ' + (parent.middle_name || '') + ' ' + (parent.last_name || '');
                            fullName = fullName.trim().replace(/\s+/g, ' ');
                            let displayText = fullName + (parent.phone ? ' (' + parent.phone + ')' : '');
                            let selected = (parent.parentID == targetParentID) ? 'selected' : '';
                            parentSelect.append('<option value="' + parent.parentID + '" ' + selected + '>' + displayText + '</option>');
                        });

                        if (typeof parentSelect.select2 === 'function') {
                            parentSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Search and select a parent...',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#editStudentModal')
                            });
                        }

                        if (targetParentID) {
                            parentSelect.val(targetParentID).trigger('change');
                        }
                    }
                }
            });

            // Load sponsors for edit modal
            $.ajax({
                url: '{{ route("get_sponsors") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let sponsorSelect = $('#edit_sponsor_id');
                        sponsorSelect.html('<option value="">Choose a sponsor...</option>');
                        response.sponsors.forEach(function(sponsor) {
                            sponsorSelect.append('<option value="' + sponsor.sponsorID + '">' + sponsor.sponsor_name + '</option>');
                        });
                        
                        // If we have a pending sponsor ID, set it now
                        if (window.pendingEditSponsorID) {
                            sponsorSelect.val(window.pendingEditSponsorID);
                            window.pendingEditSponsorID = null;
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
                    
                    // Check if required data exists
                    if (!student || !school) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Missing student or school data'
                        });
                        hideModal('studentIdCardModal');
                        return;
                    }
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
            }).fail(function(xhr, status, error) {
                console.error('Error loading ID card:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', status);
                let errorMessage = 'Failed to load ID card data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        if (xhr.responseText.length < 200) {
                            errorMessage = xhr.responseText;
                        }
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    footer: 'Status: ' + xhr.status + ' | Error: ' + error
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
                                            loadStudents();
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

        function generateAdmissionNumber() {
            const now = new Date();
            const pad = (value, length = 2) => String(value).padStart(length, '0');
            const timestamp = [
                now.getFullYear(),
                pad(now.getMonth() + 1),
                pad(now.getDate()),
                pad(now.getHours()),
                pad(now.getMinutes()),
                pad(now.getSeconds()),
                pad(now.getMilliseconds(), 3)
            ].join('');
            const random = Math.floor(100 + Math.random() * 900);
            return `${timestamp}${random}`;
        }

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

            if (!admission_number) {
                admission_number = generateAdmissionNumber();
                $('#admission_number').val(admission_number);
            }

            let formData = new FormData(this);
            formData.set('admission_number', admission_number);

            // Debug: Log form data to console for review
            console.log('--- Registering Student - Reviewing Form Data ---');
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            console.log('------------------------------------------------');
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
                            if (typeof $('#parentID').select2 === 'function') {
                                $('#parentID').select2('destroy');
                            }
                        }
                        if ($('#subclassID').hasClass('select2-hidden-accessible')) {
                            if (typeof $('#subclassID').select2 === 'function') {
                                $('#subclassID').select2('destroy');
                            }
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
                                if (typeof $('#edit_parentID').select2 === 'function') {
                                    $('#edit_parentID').select2('destroy');
                                }
                            }
                            if ($('#edit_subclassID').hasClass('select2-hidden-accessible')) {
                                if (typeof $('#edit_subclassID').select2 === 'function') {
                                    $('#edit_subclassID').select2('destroy');
                                }
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
                                        if (typeof $('#newSubclassID').select2 === 'function') {
                                            $('#newSubclassID').select2('destroy');
                                        }
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
            if (!modalId) return;
            
            let $modal = $('#' + modalId);
            if ($modal.length === 0) return;
            
            // Try Bootstrap modal first if available
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
                try {
                    let bsModal = new bootstrap.Modal($modal[0], {
                        backdrop: true,
                        keyboard: true
                    });
                    bsModal.show();
                    return;
                } catch (e) {
                    console.log('Bootstrap modal not available, using manual show');
                }
            }
            
            // Manual show
            $('body').addClass('modal-open');
            $modal.removeClass('fade').css({
                'display': 'block',
                'z-index': 1050
            }).addClass('show');
            
            // Add backdrop if it doesn't exist
            if ($('.modal-backdrop').length === 0) {
                $('body').append('<div class="modal-backdrop fade show" style="z-index: 1040; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.5);"></div>');
            }
        }

        // Function to hide modal manually
        function hideModal(modalId) {
            if (!modalId) return;
            
            let $modal = $('#' + modalId);
            if ($modal.length === 0) return;
            
            // Try Bootstrap modal first if available
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
                try {
                    let bsModal = bootstrap.Modal.getInstance($modal[0]);
                    if (bsModal) {
                        bsModal.hide();
                        return;
                    }
                } catch (e) {
                    console.log('Bootstrap modal not available, using manual close');
                }
            }
            
            // Manual close
            $modal.removeClass('show fade').css({
                'display': 'none',
                'z-index': ''
            });
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            
            // Remove padding from body if no other modals are open
            if ($('.modal.show').length === 0) {
                $('body').css('padding-right', '');
            }

            // Destroy Select2 instances in the modal to prevent conflicts
            $('#' + modalId + ' select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    if (typeof $(this).select2 === 'function') {
                        $(this).select2('destroy');
                    }
                }
            });
        }

        // Close modal handlers for all modals
        $(document).on('click', '.modal .btn-close, .modal .btn-secondary[data-bs-dismiss="modal"], .modal button[data-bs-dismiss="modal"], .modal-footer .btn-secondary', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let modal = $(this).closest('.modal');
            if (modal.length) {
                let modalId = modal.attr('id');
                if (modalId) {
                    hideModal(modalId);
                }
            }
        });

        // Close modal when clicking backdrop
        $(document).on('click', '.modal-backdrop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.modal.show').each(function() {
                let modalId = $(this).attr('id');
                if (modalId) {
                    hideModal(modalId);
                }
            });
        });
        
        // Also handle Bootstrap modal close events
        $(document).on('hidden.bs.modal', '.modal', function() {
            let modalId = $(this).attr('id');
            if (modalId) {
                hideModal(modalId);
            }
        });

        // Helper function for safe Select2 initialization
        function safeInitSelect2(element, options) {
            let $element = $(element);
            if (typeof $element.select2 === 'function') {
                $element.select2(options);
            } else {
                console.warn('Select2 is not available for element:', element);
            }
        }

        // Load classes and subclasses for filters
        function loadClassesForFilter() {
            $.ajax({
                url: '{{ route("get_subclasses_for_school") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let classSelect = $('#classFilter');
                        classSelect.html('<option value="">-- All Classes --</option>');
                        
                        // Extract unique classes from subclasses
                        let classes = {};
                        if (response.subclasses && Array.isArray(response.subclasses)) {
                            response.subclasses.forEach(function(subclass) {
                                // Make sure classID exists and is valid
                                let classID = subclass.classID;
                                if (classID && subclass.class_name && !classes[classID]) {
                                    classes[classID] = subclass.class_name;
                                    classSelect.append('<option value="' + classID + '">' + subclass.class_name + '</option>');
                                }
                            });
                        } else {
                            console.warn('No subclasses in response or invalid format:', response);
                        }
                        
                        // Initialize Select2
                        if (!classSelect.hasClass('select2-hidden-accessible')) {
                            safeInitSelect2(classSelect, {
                                theme: 'bootstrap-5',
                                placeholder: 'Select class...',
                                allowClear: true,
                                width: '100%'
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load classes:', error);
                    console.error('Response:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load classes. Please refresh the page.',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        }

        function loadAllSubclassesForFilter() {
            $.ajax({
                url: '{{ route("get_subclasses_for_school") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#subclassFilter');
                        subclassSelect.html('<option value="">-- All Subclasses --</option>');
                        
                        if (response.subclasses && Array.isArray(response.subclasses)) {
                            response.subclasses.forEach(function(subclass) {
                                const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                                subclassSelect.append('<option value="' + subclass.subclassID + '" data-class-id="' + (subclass.classID || '') + '">' + displayName + '</option>');
                            });
                        }
                        
                        // Initialize Select2
                        if (!subclassSelect.hasClass('select2-hidden-accessible')) {
                            if (typeof subclassSelect.select2 === 'function') {
                                subclassSelect.select2({
                                    theme: 'bootstrap-5',
                                    placeholder: 'Select subclass...',
                                    allowClear: true,
                                    width: '100%'
                                });
                            }
                        }
                    }
                }
            });
        }

        function loadSubclassesForFilter(classID) {
            $.ajax({
                url: '{{ route("get_subclasses_for_school") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#subclassFilter');
                        subclassSelect.html('<option value="">-- All Subclasses --</option>');
                        
                        if (response.subclasses && Array.isArray(response.subclasses)) {
                            response.subclasses.forEach(function(subclass) {
                                if (subclass.classID == classID) {
                                    const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                                    subclassSelect.append('<option value="' + subclass.subclassID + '" data-class-id="' + (subclass.classID || '') + '">' + displayName + '</option>');
                                }
                            });
                        }
                        
                        // Update Select2
                        if (subclassSelect.hasClass('select2-hidden-accessible')) {
                            if (typeof subclassSelect.select2 === 'function') {
                                subclassSelect.trigger('change');
                            }
                        }
                    }
                }
            });
        }

        // Export PDF using JavaScript
        $('#exportPdfBtn').on('click', function() {
            // Check if jsPDF is loaded
            if (typeof window.jspdf === 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'PDF library not loaded. Please refresh the page.',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            const { jsPDF } = window.jspdf;
            let status = $('#statusFilter').val() || 'Active';
            let classID = $('#classFilter').val() || '';
            let subclassID = $('#subclassFilter').val() || '';
            let gender = $('#genderFilter').val() || '';
            let health = $('#healthFilter').val() || '';

            if (!currentStudentsData || currentStudentsData.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'No students data to export. Please load students first.',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            let tableData = currentStudentsData.map(function(student) {
                return [
                    student.admission_number || '',
                    student.full_name || '',
                    student.class || '',
                    student.gender || '',
                    student.parent_name || '',
                    student.fingerprint_id || ''
                ];
            });

            // Show loading
            Swal.fire({
                title: 'Generating PDF...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                // Create new PDF document
                const doc = new jsPDF('l', 'mm', 'a4'); // landscape orientation

                // Add title
                doc.setFontSize(16);
                doc.setTextColor(148, 0, 0); // #940000
                doc.text('Students Report', 14, 15);

                // Add filter info
                doc.setFontSize(10);
                doc.setTextColor(0, 0, 0);
                let yPos = 22;
                let filterText = 'Filters: ';
                let filters = [];
                if (status) filters.push('Status: ' + status);
                if (classID) {
                    let classOption = $('#classFilter option:selected').text();
                    if (classOption !== '-- All Classes --') filters.push('Class: ' + classOption);
                }
                if (subclassID) {
                    let subclassOption = $('#subclassFilter option:selected').text();
                    if (subclassOption !== '-- All Subclasses --') filters.push('Subclass: ' + subclassOption);
                }
                if (gender) filters.push('Gender: ' + gender);
                if (health) filters.push('Health: ' + (health === 'good' ? 'Good Health' : 'Bad Health'));
                
                if (filters.length > 0) {
                    doc.text(filterText + filters.join(', '), 14, yPos);
                    yPos += 5;
                }

                // Add date
                let currentDate = new Date().toLocaleDateString();
                doc.text('Generated on: ' + currentDate, 14, yPos);
                yPos += 8;

                // Prepare table data
                let tableRows = [];
                
                // Helper function to extract text from HTML
                function extractText(html) {
                    if (!html) return '';
                    if (typeof html === 'string') {
                        let $temp = $('<div>').html(html);
                        return $temp.text().trim().replace(/⚠️/g, '').replace(/\s+/g, ' ');
                    }
                    return String(html).trim();
                }

                tableData.forEach(function(row, index) {
                    // Row is an array of data from currentStudentsData
                    let rowData = Array.isArray(row) ? row : [];

                    let admissionNumber = extractText(rowData[0] || '');
                    let fullName = extractText(rowData[1] || '');
                    let className = extractText(rowData[2] || '');
                    let genderVal = extractText(rowData[3] || '');
                    let parentName = extractText(rowData[4] || '');
                    let fingerprintId = extractText(rowData[5] || '');

                    tableRows.push([
                        index + 1,
                        admissionNumber || 'N/A',
                        fullName || 'N/A',
                        className || 'N/A',
                        genderVal || 'N/A',
                        parentName || 'N/A',
                        fingerprintId || 'N/A'
                    ]);
                });

                // Add table using autoTable
                doc.autoTable({
                    head: [['#', 'Admission No', 'Full Name', 'Class', 'Gender', 'Parent', 'Fingerprint ID']],
                    body: tableRows,
                    startY: yPos,
                    theme: 'grid',
                    headStyles: {
                        fillColor: [148, 0, 0], // #940000
                        textColor: [255, 255, 255],
                        fontStyle: 'bold'
                    },
                    styles: {
                        fontSize: 8,
                        cellPadding: 2
                    },
                    columnStyles: {
                        0: { cellWidth: 15 },
                        1: { cellWidth: 40 },
                        2: { cellWidth: 60 },
                        3: { cellWidth: 40 },
                        4: { cellWidth: 25 },
                        5: { cellWidth: 50 },
                        6: { cellWidth: 35 }
                    },
                    margin: { top: yPos }
                });

                // Add statistics at the end
                let finalY = doc.lastAutoTable.finalY + 10;
                doc.setFontSize(10);
                doc.setTextColor(148, 0, 0);
                doc.text('Statistics:', 14, finalY);
                finalY += 5;

                doc.setFontSize(9);
                doc.setTextColor(0, 0, 0);
                doc.text('Total Students: ' + $('#statTotalStudents').text(), 14, finalY);
                finalY += 5;
                doc.text('Male: ' + $('#statMaleCount').text() + ' | Female: ' + $('#statFemaleCount').text(), 14, finalY);
                finalY += 5;
                doc.text('Good Health: ' + $('#statGoodHealth').text() + ' | Bad Health: ' + $('#statBadHealth').text(), 14, finalY);

                // Generate filename
                let filename = 'Students_Report_' + status + '_' + new Date().toISOString().split('T')[0] + '.pdf';

                // Save PDF
                doc.save(filename);

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'PDF exported successfully',
                    confirmButtonColor: '#940000',
                    timer: 2000,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error('Error generating PDF:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to generate PDF: ' + error.message,
                    confirmButtonColor: '#940000'
                });
            }
        });

        // Export Excel
        $('#exportExcelBtn').on('click', function() {
            let status = $('#statusFilter').val() || currentStatus;
            let classID = $('#classFilter').val() || '';
            let subclassID = $('#subclassFilter').val() || '';
            let gender = $('#genderFilter').val() || '';
            let health = $('#healthFilter').val() || '';

            let url = '{{ route("export_students_excel") }}' + '?status=' + encodeURIComponent(status) +
                '&classID=' + encodeURIComponent(classID) +
                '&subclassID=' + encodeURIComponent(subclassID) +
                '&gender=' + encodeURIComponent(gender) +
                '&health=' + encodeURIComponent(health);

            window.open(url, '_blank');
        });

        // Initialize Select2 for filters
        $('#classFilter, #subclassFilter, #genderFilter, #healthFilter, #statusFilter').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                safeInitSelect2(this, {
                    theme: 'bootstrap-5',
                    placeholder: 'Select...',
                    allowClear: true,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            }
        });

        // Check if subclassID is provided in URL (for teacher view)
        const urlParams = new URLSearchParams(window.location.search);
        const subclassID = urlParams.get('subclassID');
        
        // Initialize on page load
        loadFormData(subclassID || null);
        loadClassesForFilter();
        loadAllSubclassesForFilter();
        
        // If subclassID is provided, auto-open registration modal
        if (subclassID) {
            console.log('SubclassID found in URL:', subclassID);
            
            // Listen for when subclass is pre-selected
            $(document).on('subclassPreSelected', function(event, selectedSubclassID) {
                console.log('Subclass pre-selected:', selectedSubclassID);
                if (selectedSubclassID == subclassID) {
                    // Wait a bit more for Select2 to initialize
                    setTimeout(function() {
                        openRegistrationModal();
                    }, 300);
                }
            });
            
            // Also try to open after form data loads (fallback)
            setTimeout(function() {
                if ($('#subclassID').val() == subclassID) {
                    openRegistrationModal();
                }
            }, 2000);
            
            function openRegistrationModal() {
                const modal = document.getElementById('addStudentModal');
                if (modal) {
                    console.log('Opening registration modal...');
                    // Use Bootstrap 5 modal
                    if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                        const bsModal = new bootstrap.Modal(modal, {
                            backdrop: 'static',
                            keyboard: true
                        });
                        bsModal.show();
                        console.log('Modal opened successfully');
                    } else if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                        // Fallback to jQuery Bootstrap modal
                        jQuery(modal).modal('show');
                        console.log('Modal opened with jQuery');
                    } else {
                        // Manual fallback
                        modal.style.display = 'block';
                        modal.classList.add('show');
                        document.body.classList.add('modal-open');
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                        console.log('Modal opened manually');
                    }
                } else {
                    console.error('Modal not found');
                }
            }
        }

        // Load initial data
        setTimeout(function() {
            loadStudents();
            loadStatistics();
        }, 500);
    });

    // Function to load academic years for student
    function loadAcademicYearsForStudent(studentID) {
        if (!studentID) {
            console.error('Student ID is required');
            $('#academicYearSelector').html('<option value="">Error: Student ID missing</option>');
            return;
        }
        
        $.ajax({
            url: '{{ route("get_student_academic_years", ":id") }}'.replace(':id', studentID),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.years) {
                    let selector = $('#academicYearSelector');
                    if (selector.length === 0) {
                        console.error('Academic year selector not found');
                        return;
                    }
                    
                    selector.html('<option value="">Select Academic Year</option>');
                    
                    response.years.forEach(function(year) {
                        let selected = year.is_active ? 'selected' : '';
                        let statusBadge = year.status === 'Active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Closed</span>';
                        selector.append('<option value="' + year.academic_yearID + '" data-status="' + year.status + '" ' + selected + '>' + year.year_name + ' ' + statusBadge + '</option>');
                    });
                    
                    // Enable widgets if active year is selected by default
                    if (response.years.some(y => y.is_active)) {
                        $('.academic-widget').removeClass('disabled').css('opacity', '1');
                        let activeYear = response.years.find(y => y.is_active);
                        if (activeYear) {
                            loadAcademicDataForYear(studentID, activeYear.academic_yearID, activeYear.status);
                        }
                    }
                } else {
                    console.error('Invalid response:', response);
                    $('#academicYearSelector').html('<option value="">No academic years found</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading academic years:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', status);
                $('#academicYearSelector').html('<option value="">Error loading years</option>');
            }
        });
    }
    
    // Handle academic year selection change
    $(document).on('change', '#academicYearSelector', function() {
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).val();
        let status = $(this).find('option:selected').data('status');
        
        if (academicYearID) {
            // Enable widgets
            $('.academic-widget').removeClass('disabled').css('opacity', '1');
            loadAcademicDataForYear(studentID, academicYearID, status);
        } else {
            // Reset widgets
            $('.academic-widget').addClass('disabled').css('opacity', '0.6');
            $('#classWidgetContent, #resultsWidgetContent, #attendanceWidgetContent').html('<p class="text-muted text-center py-3">Select Academic Year first</p>');
            // Collapse all widgets
            $('.academic-widget').attr('data-expanded', 'false');
            $('.widget-content').slideUp();
            $('.widget-toggle-icon').removeClass('bi-chevron-up').addClass('bi-chevron-down');
        }
    });
    
    // Handle widget click to expand/collapse
    $(document).on('click', '.academic-widget .card-header', function(e) {
        e.stopPropagation();
        e.preventDefault();
        let widget = $(this).closest('.academic-widget');
        let isExpanded = widget.attr('data-expanded') === 'true';
        let content = widget.find('.widget-content');
        let icon = widget.find('.widget-toggle-icon');
        let widgetId = widget.attr('id');
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        // Check if academic year is selected
        if (!academicYearID) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Academic Year',
                text: 'Please select an academic year first'
            });
            return;
        }
        
        if (isExpanded) {
            // Collapse
            content.slideUp(300, function() {
                widget.attr('data-expanded', 'false');
            });
            icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        } else {
            // Expand first
            content.slideDown(300, function() {
                widget.attr('data-expanded', 'true');
            });
            icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
            
            // Load data if not loaded yet
            let contentText = widget.find('.widget-content').text().trim();
            let needsLoading = contentText.includes('Click to load') || 
                              contentText.includes('Select Academic Year') || 
                              contentText.includes('Loading...') ||
                              contentText === '' ||
                              contentText.length < 50; // If content is too short, probably needs loading
            
            console.log('Widget clicked:', widgetId, 'Needs loading:', needsLoading, 'Content:', contentText);
            
            if (needsLoading && academicYearID) {
                if (widgetId === 'classWidget') {
                    console.log('Loading class data...');
                    loadClassDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'resultsWidget') {
                    console.log('Loading results data...');
                    loadResultsDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'attendanceWidget') {
                    console.log('Loading attendance data...');
                    loadAttendanceDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'paymentsWidget') {
                    console.log('Loading payments data...');
                    loadPaymentsDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'debitsWidget') {
                    console.log('Loading debts data...');
                    loadDebitsDataForWidget(studentID, academicYearID, yearStatus);
                }
            }
        }
    });
    
    // Function to load academic data for selected year (prepare widgets)
    function loadAcademicDataForYear(studentID, academicYearID, yearStatus) {
        // Just prepare widgets - data will load when clicked
        // Make sure widgets are visible and ready
        $('.academic-widget').removeClass('disabled').css('opacity', '1');
        $('#classWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load class data...</p>');
        $('#resultsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load results...</p>');
        $('#attendanceWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load attendance...</p>');
        $('#paymentsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load payments...</p>');
        $('#debitsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load debts...</p>');
        
        // Collapse all widgets initially
        $('.academic-widget').attr('data-expanded', 'false');
        $('.widget-content').hide();
        $('.widget-toggle-icon').removeClass('bi-chevron-up').addClass('bi-chevron-down');
    }
    
    // Function to load class data for widget
    function loadClassDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#classWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#classWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading classes...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_classes_for_year") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Class data response:', response);
                if (response.success && response.classes && response.classes.length > 0) {
                    let content = '<div class="list-group" style="border-radius: 0;">';
                    response.classes.forEach(function(cls) {
                        content += '<div class="list-group-item class-item" data-class-id="' + cls.class_id + '" data-class-name="' + cls.class_name + '" style="border-radius: 0; cursor: pointer;">';
                        content += '<div class="d-flex justify-content-between align-items-center">';
                        content += '<div><i class="bi bi-book text-primary-custom"></i> <strong>' + cls.class_name + '</strong></div>';
                        content += '<i class="bi bi-chevron-right"></i>';
                        content += '</div>';
                        content += '<div class="subclasses-list mt-2" style="display: none;"></div>';
                        content += '</div>';
                    });
                    content += '</div>';
                    $('#classWidgetContent').html(content);
                } else {
                    $('#classWidgetContent').html('<p class="text-muted text-center py-3">No classes found for this academic year</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading classes:', error, xhr.responseText);
                $('#classWidgetContent').html('<p class="text-danger text-center py-3">Error loading classes. Please try again.</p>');
            }
        });
    }
    
    // Function to load results data for widget
    function loadResultsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#resultsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#resultsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading results...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_terms_for_year") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Results/terms response:', response);
                if (response.success && response.terms && response.terms.length > 0) {
                    let content = '<div class="mb-3">';
                    content += '<label class="form-label fw-bold">Select Term:</label>';
                    content += '<select class="form-select term-selector" style="border-radius: 0;">';
                    content += '<option value="">Select Term</option>';
                    response.terms.forEach(function(term) {
                        content += '<option value="' + term.term + '">' + term.term_name + '</option>';
                    });
                    content += '</select>';
                    content += '</div>';
                    content += '<div id="termResultsOptions" style="display: none;"></div>';
                    $('#resultsWidgetContent').html(content);
                } else {
                    $('#resultsWidgetContent').html('<p class="text-muted text-center py-3">No terms/results found for this academic year</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading results:', error);
                console.error('Response:', xhr.responseText);
                let errorMessage = 'Error loading results. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // Not JSON, use default message
                    }
                }
                $('#resultsWidgetContent').html('<p class="text-danger text-center py-3">' + errorMessage + '</p>');
            }
        });
    }
    
    // Function to load attendance data for widget
    function loadAttendanceDataForWidget(studentID, academicYearID, yearStatus) {
        let content = '<div class="mb-3">';
        content += '<label class="form-label fw-bold">Filter Type:</label>';
        content += '<select class="form-select attendance-filter-type" style="border-radius: 0;">';
        content += '<option value="">Select Filter Type</option>';
        content += '<option value="date_range">Filter by Date Range</option>';
        content += '<option value="month">Filter by Month</option>';
        content += '</select>';
        content += '</div>';
        content += '<div id="attendanceFilterOptions" style="display: none;"></div>';
        content += '<div id="attendanceResults" class="mt-3"></div>';
        $('#attendanceWidgetContent').html(content);
    }
    
    // Function to load payments data for widget
    function loadPaymentsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#paymentsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#paymentsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading payments...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_payments_for_year") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Payments response:', response);
                if (response.success) {
                    let content = '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Date</th><th>Control Number</th><th>Fee Type</th><th>Amount</th><th>Payment Method</th><th>Receipt</th></tr></thead><tbody>';
                    
                    if (response.payments && response.payments.length > 0) {
                        response.payments.forEach(function(payment) {
                            content += '<tr>';
                            content += '<td>' + (payment.date || 'N/A') + '</td>';
                            content += '<td>' + (payment.control_number || 'N/A') + '</td>';
                            content += '<td>' + (payment.fee_type || 'N/A') + '</td>';
                            content += '<td>' + (payment.amount || 0) + ' TZS</td>';
                            content += '<td>' + (payment.payment_method || 'N/A') + '</td>';
                            content += '<td>' + (payment.receipt_number || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                    } else {
                        content += '<tr><td colspan="6" class="text-center text-muted">No payments found for this academic year</td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    $('#paymentsWidgetContent').html(content);
                } else {
                    $('#paymentsWidgetContent').html('<p class="text-muted text-center py-3">No payments found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading payments:', error);
                console.error('Response:', xhr.responseText);
                let errorMessage = 'Error loading payments. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // Not JSON, use default message
                    }
                }
                $('#paymentsWidgetContent').html('<p class="text-danger text-center py-3">' + errorMessage + '</p>');
            }
        });
    }
    
    // Function to load debits (debts) data for widget
    function loadDebitsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#debitsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#debitsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading debts...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_debts_for_year") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Debts response:', response);
                if (response.success) {
                    let content = '<div class="alert alert-warning" style="border-radius: 0;"><strong>Total Outstanding Debt: ' + (response.total_debt || 0) + ' TZS</strong></div>';
                    content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Fee Type</th><th>Required Amount</th><th>Paid Amount</th><th>Outstanding</th></tr></thead><tbody>';
                    
                    if (response.debts && response.debts.length > 0) {
                        response.debts.forEach(function(debt) {
                            content += '<tr>';
                            content += '<td>' + (debt.fee_type || 'N/A') + '</td>';
                            content += '<td>' + (debt.required_amount || 0) + ' TZS</td>';
                            content += '<td>' + (debt.paid_amount || 0) + ' TZS</td>';
                            content += '<td class="text-danger"><strong>' + (debt.outstanding || 0) + ' TZS</strong></td>';
                            content += '</tr>';
                        });
                    } else {
                        content += '<tr><td colspan="4" class="text-center text-success"><strong>No outstanding debts</strong></td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    
                    // Add library records section (books not returned)
                    if (response.library_records && response.library_records.length > 0) {
                        content += '<div class="mt-4"><h6 class="fw-bold mb-3"><i class="bi bi-book"></i> Library Books Not Returned</h6>';
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Borrow Date</th><th>Book Title</th><th>Subject</th><th>Class</th></tr></thead><tbody>';
                        
                        response.library_records.forEach(function(record) {
                            content += '<tr>';
                            content += '<td>' + (record.borrow_date || 'N/A') + '</td>';
                            content += '<td>' + (record.book_title || 'N/A') + '</td>';
                            content += '<td>' + (record.subject_name || 'N/A') + '</td>';
                            content += '<td>' + (record.class_name || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div></div>';
                    } else {
                        content += '<div class="mt-4"><h6 class="fw-bold mb-3"><i class="bi bi-book"></i> Library Books Not Returned</h6>';
                        content += '<p class="text-muted text-center py-2">No books borrowed or all books have been returned</p></div>';
                    }
                    
                    $('#debitsWidgetContent').html(content);
                } else {
                    $('#debitsWidgetContent').html('<p class="text-muted text-center py-3">No debts found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading debts:', error);
                $('#debitsWidgetContent').html('<p class="text-danger text-center py-3">Error loading debts</p>');
            }
        });
    }
    
    // Handle class item click to show subclasses
    $(document).on('click', '.class-item', function() {
        let classID = $(this).data('class-id');
        let className = $(this).data('class-name');
        let subclassesList = $(this).find('.subclasses-list');
        
        if (subclassesList.is(':visible')) {
            subclassesList.slideUp();
            return;
        }
        
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        subclassesList.html('<div class="text-center py-2"><div class="spinner-border spinner-border-sm" role="status"></div></div>').slideDown();
        
        $.ajax({
            url: '{{ route("get_student_subclasses_for_class") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                class_id: classID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.subclasses && response.subclasses.length > 0) {
                    let html = '<ul class="list-group list-group-flush" style="border-radius: 0;">';
                    response.subclasses.forEach(function(subclass) {
                        html += '<li class="list-group-item" style="border-radius: 0;"><i class="bi bi-layers text-info"></i> ' + subclass.subclass_name + '</li>';
                    });
                    html += '</ul>';
                    subclassesList.html(html);
                } else {
                    subclassesList.html('<p class="text-muted text-center py-2">No subclasses found</p>');
                }
            },
            error: function() {
                subclassesList.html('<p class="text-danger text-center py-2">Error loading subclasses</p>');
            }
        });
    });
    
    // Handle term selection for results widget
    $(document).on('change', '.term-selector', function() {
        let term = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (term && academicYearID) {
            // Show filter options for exam results or term report
            let content = '<div class="mb-3">';
            content += '<label class="form-label fw-bold">View Type:</label>';
            content += '<select class="form-select" id="resultViewType" style="border-radius: 0;">';
            content += '<option value="">Select View Type</option>';
            content += '<option value="exam_results">Exam Results</option>';
            content += '<option value="term_report">Term Report</option>';
            content += '</select>';
            content += '</div>';
            content += '<div id="examFilterContainer" style="display: none;" class="mb-3">';
            content += '<label class="form-label fw-bold">Select Exam:</label>';
            content += '<select class="form-select" id="examSelector" style="border-radius: 0;">';
            content += '<option value="">Loading...</option>';
            content += '</select>';
            content += '</div>';
            content += '<div id="resultsDisplayArea"></div>';
            
            $('#termResultsOptions').html(content).slideDown();
            
            // Load exams for this term
            loadExamsForTerm(studentID, academicYearID, term, yearStatus);
        } else {
            $('#termResultsOptions').slideUp().html('');
        }
    });
    
    // Handle result view type change
    $(document).on('change', '#resultViewType', function() {
        let viewType = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let term = $('.term-selector').val(); // Get term from term selector
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (viewType === 'exam_results') {
            $('#examFilterContainer').show();
        } else if (viewType === 'term_report') {
            $('#examFilterContainer').hide();
            // Load term report
            if (term) {
                loadTermReport(studentID, academicYearID, term, yearStatus);
            }
        } else {
            $('#examFilterContainer').hide();
            $('#resultsDisplayArea').html('');
        }
    });
    
    // Handle exam selection change
    $(document).on('change', '#examSelector', function() {
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let term = $('.term-selector').val(); // Get term from term selector
        let examID = $(this).val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        console.log('Exam selected:', examID, 'Term:', term, 'Year:', academicYearID, 'Status:', yearStatus);
        
        if (examID && term && academicYearID) {
            $('#resultsDisplayArea').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading results...</p></div>');
            loadExamResults(studentID, academicYearID, term, examID, yearStatus);
        } else {
            $('#resultsDisplayArea').html('<p class="text-warning text-center py-3">Please select term and exam</p>');
        }
    });
    
    // Function to load exams for term
    function loadExamsForTerm(studentID, academicYearID, term, yearStatus) {
        $.ajax({
            url: '{{ route("get_exams_for_term") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let examSelector = $('#examSelector');
                    examSelector.html('<option value="">Select Exam</option>');
                    
                    if (response.exams && response.exams.length > 0) {
                        response.exams.forEach(function(exam) {
                            examSelector.append('<option value="' + exam.exam_id + '">' + exam.exam_name + '</option>');
                        });
                    } else {
                        examSelector.append('<option value="">No exams found</option>');
                    }
                }
            }
        });
    }
    
    // Function to load exam results
    function loadExamResults(studentID, academicYearID, term, examID, yearStatus) {
        if (!studentID || !academicYearID || !term || !examID) {
            $('#resultsDisplayArea').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $.ajax({
            url: '{{ route("get_student_exam_results") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                exam_id: examID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Exam results response:', response);
                if (response.success) {
                    let content = '';
                    
                    // Position summary
                    if (response.position && response.total_students) {
                        content += '<div class="alert alert-info mb-3" style="border-radius: 0;">';
                        content += '<strong><i class="bi bi-trophy"></i> Position: ' + response.position + ' out of ' + response.total_students + ' students</strong>';
                        content += '</div>';
                    }
                    
                    content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Remark</th></tr></thead><tbody>';
                    
                    if (response.results && response.results.length > 0) {
                        response.results.forEach(function(result) {
                            content += '<tr><td>' + (result.subject_name || 'N/A') + '</td><td>' + (result.marks || 0) + '</td><td>' + (result.grade || 'N/A') + '</td><td>' + (result.remark || '') + '</td></tr>';
                        });
                    } else {
                        content += '<tr><td colspan="4" class="text-center text-muted">No results found for this exam</td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    if (response.results && response.results.length > 0) {
                        content += '<div class="mt-3"><button class="btn btn-primary export-exam-pdf-btn" data-student-id="' + studentID + '" data-year-id="' + academicYearID + '" data-term="' + term + '" data-exam-id="' + examID + '" data-year-status="' + yearStatus + '" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button></div>';
                    }
                    
                    $('#resultsDisplayArea').html(content);
                } else {
                    $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No results found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading exam results:', error);
                console.error('Response:', xhr.responseText);
                $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No results found for this exam</p>');
            }
        });
    }
    
    // Function to load term report
    function loadTermReport(studentID, academicYearID, term, yearStatus) {
        if (!studentID || !academicYearID || !term) {
            $('#resultsDisplayArea').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#resultsDisplayArea').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading term report...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_term_report") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Term report response:', response);
                if (response.success) {
                    let content = '';
                    
                    // Student Information Section
                    content += '<div class="card mb-3" style="border-radius: 0;">';
                    content += '<div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-person"></i> Student Information</h5></div>';
                    content += '<div class="card-body">';
                    content += '<div class="row">';
                    content += '<div class="col-md-6"><strong>Student Name:</strong> ' + (response.student_name || 'N/A') + '</div>';
                    content += '<div class="col-md-3"><strong>Term:</strong> ' + (response.term || 'N/A') + '</div>';
                    content += '<div class="col-md-3"><strong>Year:</strong> ' + (response.year || 'N/A') + '</div>';
                    content += '</div>';
                    if (response.position && response.total_students) {
                        content += '<div class="row mt-2">';
                        content += '<div class="col-md-6"><strong>Position:</strong> ' + response.position + ' out of ' + response.total_students + '</div>';
                        content += '<div class="col-md-3"><strong>Overall Average:</strong> ' + (response.overall_average || 0) + '</div>';
                        content += '<div class="col-md-3"><strong>Grade:</strong> ' + (response.overall_grade || 'N/A') + '</div>';
                        content += '</div>';
                    } else {
                        content += '<div class="row mt-2">';
                        content += '<div class="col-md-6"><strong>Overall Average:</strong> ' + (response.overall_average || 0) + '</div>';
                        content += '<div class="col-md-6"><strong>Grade:</strong> ' + (response.overall_grade || 'N/A') + '</div>';
                        content += '</div>';
                    }
                    content += '</div></div>';
                    
                    // Examinations Table
                    if (response.examinations && response.examinations.length > 0) {
                        content += '<div class="card mb-3" style="border-radius: 0;">';
                        content += '<div class="card-header bg-info text-white"><h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Examinations</h6></div>';
                        content += '<div class="card-body">';
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Examination</th><th>Average</th><th>Grade</th></tr></thead><tbody>';
                        
                        response.examinations.forEach(function(exam) {
                            content += '<tr>';
                            content += '<td>' + (exam.exam_name || 'N/A') + '</td>';
                            content += '<td>' + (exam.average || 0) + '</td>';
                            content += '<td>' + (exam.grade || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div>';
                        content += '</div></div>';
                    }
                    
                    // Subject Results Table
                    if (response.report && response.report.length > 0) {
                        content += '<div class="card mb-3" style="border-radius: 0;">';
                        content += '<div class="card-header bg-success text-white"><h6 class="mb-0"><i class="bi bi-book"></i> Subject Results</h6></div>';
                        content += '<div class="card-body">';
                        
                        // Build table header with exam names
                        let examNames = [];
                        if (response.examinations && response.examinations.length > 0) {
                            response.examinations.forEach(function(exam) {
                                examNames.push(exam.exam_name);
                            });
                        }
                        
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Subject</th>';
                        examNames.forEach(function(examName) {
                            content += '<th>' + examName + '</th>';
                        });
                        content += '<th>Average</th><th>Grade</th></tr></thead><tbody>';
                        
                        response.report.forEach(function(item) {
                            content += '<tr>';
                            content += '<td>' + (item.subject_name || 'N/A') + '</td>';
                            
                            // Add marks for each exam
                            examNames.forEach(function(examName) {
                                if (item.exam_marks && item.exam_marks[examName]) {
                                    let examMark = item.exam_marks[examName];
                                    content += '<td>' + examMark.marks + '-' + examMark.grade + '</td>';
                                } else {
                                    content += '<td>-</td>';
                                }
                            });
                            
                            content += '<td>' + (item.average || 0) + '</td>';
                            content += '<td>' + (item.grade || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div>';
                        content += '</div></div>';
                    } else {
                        content += '<div class="alert alert-info" style="border-radius: 0;">No report data found for this term</div>';
                    }
                    
                    if (response.report && response.report.length > 0) {
                        // Store response data in a global variable for PDF export
                        window.termReportData = response;
                        content += '<div class="mt-3"><button class="btn btn-primary export-term-pdf-btn" data-student-id="' + studentID + '" data-year-id="' + academicYearID + '" data-term="' + term + '" data-year-status="' + yearStatus + '" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button></div>';
                    }
                    
                    $('#resultsDisplayArea').html(content);
                } else {
                    $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No report data found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading term report:', error);
                console.error('Response:', xhr.responseText);
                $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No report data found for this term</p>');
            }
        });
    }
    
    // Handle PDF export for exam results
    $(document).on('click', '.export-exam-pdf-btn', function() {
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).data('year-id');
        let term = $(this).data('term');
        let examID = $(this).data('exam-id');
        let yearStatus = $(this).data('year-status');
        
        // Get results data from table
        let results = [];
        $('#resultsDisplayArea table tbody tr').each(function() {
            let cells = $(this).find('td');
            if (cells.length >= 3) {
                results.push({
                    subject: $(cells[0]).text().trim(),
                    marks: $(cells[1]).text().trim(),
                    grade: $(cells[2]).text().trim(),
                    remark: $(cells[3]).text().trim() || ''
                });
            }
        });
        
        if (results.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'No results to export'
            });
            return;
        }
        
        // Get student name
        let studentName = $('#viewStudentModal .school-title').text().trim() || 'Student';
        
        // Generate PDF using jsPDF
        if (typeof jsPDF === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'PDF library not loaded'
            });
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Header
        doc.setFontSize(16);
        doc.setTextColor(148, 0, 0);
        doc.text('EXAM RESULTS', 105, 15, { align: 'center' });
        
        // Student info
        doc.setFontSize(12);
        doc.setTextColor(0, 0, 0);
        doc.text('Student: ' + studentName, 14, 25);
        doc.text('Term: ' + term.replace('_', ' ').toUpperCase(), 14, 32);
        
        // Table
        doc.autoTable({
            startY: 40,
            head: [['Subject', 'Marks', 'Grade', 'Remark']],
            body: results.map(r => [r.subject, r.marks, r.grade, r.remark]),
            theme: 'grid',
            headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
            styles: { fontSize: 9 }
        });
        
        // Add footer on all pages
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(128, 128, 128);
            doc.text('Powered by: EmCa Technologies LTD', 105, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
        }
        
        // Save
        let filename = 'Exam_Results_' + studentName.replace(/\s+/g, '_') + '_' + term + '.pdf';
        doc.save(filename);
        
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'PDF exported successfully',
            timer: 2000,
            showConfirmButton: false
        });
    });
    
    // Handle PDF export for term report
    $(document).on('click', '.export-term-pdf-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Export PDF button clicked');
        
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).data('year-id');
        let term = $(this).data('term');
        let yearStatus = $(this).data('year-status');
        
        console.log('Button data:', { studentID, academicYearID, term, yearStatus });
        console.log('Stored termReportData:', window.termReportData);
        
        // Get report data from stored response
        let response = window.termReportData;
        
        if (!response) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Report data not found. Please reload the term report first.'
            });
            return;
        }
        
        if (!response.report || response.report.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'No report data to export'
            });
            return;
        }
        
        // Get student name and other info from response
        let studentName = response.student_name || 'Student';
        let termName = response.term || term.replace('_', ' ').toUpperCase();
        let yearName = response.year || 'N/A';
        let overallAvg = response.overall_average || 0;
        let overallGrade = response.overall_grade || 'N/A';
        let position = response.position || null;
        let totalStudents = response.total_students || null;
        
        // Generate PDF using jsPDF
        if (typeof window.jspdf === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'PDF library not loaded. Please refresh the page.'
            });
            console.error('jsPDF library not found');
            return;
        }
        
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            // Header
            doc.setFontSize(18);
            doc.setTextColor(148, 0, 0);
            doc.text(studentName.toUpperCase() + ' - TERM REPORT DETAILS', 105, 15, { align: 'center' });
            
            // Student Information Section
            let yPos = 30;
            doc.setFontSize(12);
            doc.setTextColor(148, 0, 0);
            doc.text('Student Information', 14, yPos);
            yPos += 8;
            
            doc.setFontSize(10);
            doc.setTextColor(0, 0, 0);
            doc.text('Student Name: ' + studentName, 14, yPos);
            yPos += 6;
            doc.text('Term: ' + termName, 14, yPos);
            yPos += 6;
            doc.text('Year: ' + yearName, 14, yPos);
            yPos += 6;
            if (position && totalStudents) {
                doc.text('Position: ' + position + ' out of ' + totalStudents, 14, yPos);
                yPos += 6;
            }
            doc.text('Overall Average: ' + overallAvg, 14, yPos);
            yPos += 6;
            doc.text('Grade: ' + overallGrade, 14, yPos);
            yPos += 10;
            
            // Examinations Table
            if (response.examinations && response.examinations.length > 0) {
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.text('Examinations', 14, yPos);
                yPos += 8;
                
                let examTableData = response.examinations.map(exam => [
                    exam.exam_name || 'N/A',
                    exam.average || 0,
                    exam.grade || 'N/A'
                ]);
                
                doc.autoTable({
                    startY: yPos,
                    head: [['Examination', 'Average', 'Grade']],
                    body: examTableData,
                    theme: 'grid',
                    headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
                    styles: { fontSize: 9 },
                    margin: { left: 14 }
                });
                
                yPos = doc.lastAutoTable.finalY + 10;
            }
            
            // Subject Results Table
            if (response.report && response.report.length > 0) {
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.text('Subject Results', 14, yPos);
                yPos += 8;
                
                // Build table header with exam names
                let examNames = [];
                if (response.examinations && response.examinations.length > 0) {
                    response.examinations.forEach(function(exam) {
                        examNames.push(exam.exam_name);
                    });
                }
                
                let tableHead = ['Subject'];
                examNames.forEach(function(examName) {
                    tableHead.push(examName);
                });
                tableHead.push('Average');
                tableHead.push('Grade');
                
                let tableBody = [];
                response.report.forEach(function(item) {
                    let row = [item.subject_name || 'N/A'];
                    
                    // Add marks for each exam
                    examNames.forEach(function(examName) {
                        if (item.exam_marks && item.exam_marks[examName]) {
                            let examMark = item.exam_marks[examName];
                            row.push(examMark.marks + '-' + examMark.grade);
                        } else {
                            row.push('-');
                        }
                    });
                    
                    row.push(item.average || 0);
                    row.push(item.grade || 'N/A');
                    tableBody.push(row);
                });
                
                doc.autoTable({
                    startY: yPos,
                    head: [tableHead],
                    body: tableBody,
                    theme: 'grid',
                    headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
                    styles: { fontSize: 8 },
                    margin: { left: 14 },
                    columnStyles: {
                        0: { cellWidth: 50 }, // Subject column
                    }
                });
            }
            
            // Add footer on all pages
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(128, 128, 128);
                doc.text('Powered by: EmCa Technologies LTD', 105, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
            }
            
            // Save
            let filename = 'Term_Report_' + studentName.replace(/\s+/g, '_') + '_' + term + '.pdf';
            doc.save(filename);
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'PDF exported successfully',
                timer: 2000,
                showConfirmButton: false
            });
        } catch (error) {
            console.error('Error generating PDF:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to generate PDF: ' + (error.message || 'Unknown error')
            });
        }
    });
    
    // Handle attendance filter type selection
    $(document).on('change', '.attendance-filter-type', function() {
        let filterType = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (filterType && academicYearID) {
            let content = '';
            
            if (filterType === 'date_range') {
                content += '<div class="row g-3 mb-3">';
                content += '<div class="col-md-6"><label class="form-label fw-bold">From Date</label><input type="date" class="form-control" id="attendanceFromDate" style="border-radius: 0;"></div>';
                content += '<div class="col-md-6"><label class="form-label fw-bold">To Date</label><input type="date" class="form-control" id="attendanceToDate" style="border-radius: 0;"></div>';
                content += '</div>';
                content += '<button class="btn btn-primary load-attendance-btn" data-filter-type="date_range" style="border-radius: 0;"><i class="bi bi-search"></i> Load Attendance</button>';
            } else if (filterType === 'month') {
                content += '<div class="row g-3 mb-3">';
                content += '<div class="col-md-6"><label class="form-label fw-bold">Month</label><input type="month" class="form-control" id="attendanceMonth" style="border-radius: 0;"></div>';
                content += '</div>';
                content += '<button class="btn btn-primary load-attendance-btn" data-filter-type="month" style="border-radius: 0;"><i class="bi bi-search"></i> Load Attendance</button>';
            }
            
            $('#attendanceFilterOptions').html(content).slideDown();
        } else {
            $('#attendanceFilterOptions').slideUp().html('');
            $('#attendanceResults').html('');
        }
    });
    
    // Handle load attendance button click
    $(document).on('click', '.load-attendance-btn', function() {
        let filterType = $(this).data('filter-type');
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (filterType === 'date_range') {
            loadAttendanceByDateRange(studentID, academicYearID, yearStatus);
        } else if (filterType === 'month') {
            loadAttendanceByMonth(studentID, academicYearID, yearStatus);
        }
    });
    
    // Function to load attendance by date range
    function loadAttendanceByDateRange(studentID, academicYearID, yearStatus) {
        let fromDate = $('#attendanceFromDate').val();
        let toDate = $('#attendanceToDate').val();
        
        if (!fromDate || !toDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select both from and to dates'
            });
            return;
        }
        
        $.ajax({
            url: '{{ route("get_student_attendance") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                from_date: fromDate,
                to_date: toDate,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayAttendanceData(response.attendance, studentID, academicYearID, fromDate, toDate);
                }
            }
        });
    }
    
    // Function to load attendance by month
    function loadAttendanceByMonth(studentID, academicYearID, yearStatus) {
        let month = $('#attendanceMonth').val();
        
        if (!month) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a month'
            });
            return;
        }
        
        $.ajax({
            url: '{{ route("get_student_attendance") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                month: month,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayAttendanceData(response.attendance, studentID, academicYearID, null, null, month);
                }
            }
        });
    }
    
    // Function to display attendance data
    function displayAttendanceData(attendance, studentID, academicYearID, fromDate, toDate, month) {
        let content = '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Date</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Remark</th></tr></thead><tbody>';
        
        if (attendance && attendance.length > 0) {
            attendance.forEach(function(record) {
                content += '<tr><td>' + record.date + '</td><td><span class="badge bg-' + (record.status === 'Present' ? 'success' : 'danger') + '">' + record.status + '</span></td><td>' + (record.check_in || 'N/A') + '</td><td>' + (record.check_out || 'N/A') + '</td><td>' + (record.remark || '') + '</td></tr>';
            });
        } else {
            content += '<tr><td colspan="5" class="text-center">No attendance records found</td></tr>';
        }
        
        content += '</tbody></table></div>';
        content += '<div class="mt-3">';
        content += '<button class="btn btn-primary me-2" onclick="exportAttendancePDF(\'' + studentID + '\', \'' + academicYearID + '\', \'' + fromDate + '\', \'' + toDate + '\', \'' + month + '\')" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button>';
        content += '<button class="btn btn-success" onclick="exportAttendanceExcel(\'' + studentID + '\', \'' + academicYearID + '\', \'' + fromDate + '\', \'' + toDate + '\', \'' + month + '\')" style="border-radius: 0;"><i class="bi bi-file-excel"></i> Export Excel</button>';
        content += '</div>';
        
        $('#attendanceResults').html(content);
    }
    
    // Export functions (to be implemented)
    function exportResultsPDF(studentID, academicYearID, term, examID, type) {
        // Implementation for PDF export
        window.location.href = '{{ route("export_student_results_pdf") }}?student_id=' + studentID + '&academic_year_id=' + academicYearID + '&term=' + term + '&exam_id=' + examID + '&type=' + type;
    }
    
    function exportAttendancePDF(studentID, academicYearID, fromDate, toDate, month) {
        // Implementation for PDF export
        window.location.href = '{{ route("export_student_attendance_pdf") }}?student_id=' + studentID + '&academic_year_id=' + academicYearID + '&from_date=' + fromDate + '&to_date=' + toDate + '&month=' + month;
    }
    
    function exportAttendanceExcel(studentID, academicYearID, fromDate, toDate, month) {
        // Implementation for Excel export
        window.location.href = '{{ route("export_student_attendance_excel") }}?student_id=' + studentID + '&academic_year_id=' + academicYearID + '&from_date=' + fromDate + '&to_date=' + toDate + '&month=' + month;
    }

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

<!-- Include Class Selector Modal -->
@include('student_registration.class-selector')

<!-- Include Registration Modal -->
@include('student_registration.modal')

